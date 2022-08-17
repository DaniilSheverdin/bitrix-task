<?php

/**
 * Контроль поручений
 */

namespace Citto\ControlOrders\Main;

use CFile;
use CUser;
use CTasks;
use CPHPCache;
use Exception;
use CStatEvent;
use CUserOptions;
use CIBlockElement;
use CIBlockSection;
use CIntranetUtils;
use CUserFieldEnum;
use Monolog\Logger;
use CIBlockProperty;
use CBitrixComponent;
use RuntimeException;
use DateTimeImmutable;
use CIBlockPropertyEnum;
use Bitrix\Main\IO\File;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Page\Asset;
use Citto\Filesigner\Signer;
use \Bitrix\Main\Data\Cache;
use Bitrix\Main\UI\Extension;
use Citto\ControlOrders\Orders;
use Citto\ControlOrders\Notify;
use Citto\ControlOrders\Settings;
use Citto\ControlOrders\Executors;
use Monolog\Handler\StreamHandler;
use Bitrix\Main\UI\Filter\Options;
use Bitrix\Main\Engine\ActionFilter;
use Citto\ControlOrders\GroupExecutors;
use Bitrix\DocumentGenerator\Body\Docx;
use Sprint\Migration\Helpers\HlblockHelper;
use Citto\ControlOrders\Main\AjaxController;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Highloadblock\HighloadBlockTable as HLTable;
use Bitrix\DocumentGenerator\DataProvider\ArrayDataProvider;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

Loader::includeModule('tasks');
Loader::includeModule('iblock');
Loader::includeModule('intranet');
Loader::includeModule('highloadblock');
Loader::includeModule('citto.filesigner');
Loader::includeModule('sprint.migration');
Loader::includeModule('documentgenerator');

Asset::getInstance()->addCss('/bitrix/templates/.default/bootstrap.min.css');
Asset::getInstance()->addCss('/bitrix/templates/bitrix24/css/sidebar.css');
Asset::getInstance()->addCss('/local/js/adminlte/css/AdminLTE.min.css');
Asset::getInstance()->addCss('/local/js/adminlte/css/skins/_all-skins.min.css');
Asset::getInstance()->addCss('/local/css/select2.css');
Asset::getInstance()->addJs('/local/js/select2.min.js');
Asset::getInstance()->addJs('/local/js/jquery.ui.1.12.1.js');

Extension::load([
    'ui.buttons.icons',
    'ui.dialogs.messagebox',
    'ui.tooltip',
    'ui.forms',
]);

require_once $_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php";

/**
 * Контроль поручений
 */
class Component extends CBitrixComponent implements Controllerable
{
    /**
     * Типа "Без срока".
     * Чтобы не переделывать всю логику на чекбокс, сделал дату в далёкое будущее
     *
     * @var string
     */
    public $disableSrokDate      = '31.12.2099';

    /** @var array */
    public $hlblock              = [];

    /** @var array */
    public $arResolutionStatus   = [];

    /** @var array */
    private $permissions         = [];

    /** @var array */
    private $menu_items          = [];

    /** @var array */
    private $ispolnitels         = [];

    /** @var array */
    private $DetailData          = [];

    /** @var array */
    private $TypeData            = [];

    /** @var array */
    private $Classificator       = [];

    /** @var array */
    private $Category            = [];

    /** @var array */
    private $arDopStatuses       = [];

    /** @var array */
    private $arControlerStatuses = [];

    /** @var array */
    private $arVoteResult        = [];

    /** @var array */
    public $arActions = [
        'DRAFT'     => 1134,
        'NEW'       => 1135,
        'WORK'      => 1136,
        'CONTROL'   => 1137,
        'READY'     => 1138,
        'RESOLUT'   => 1139,
        'ARCHIVE'   => 1140,
    ];

    /** @var array */
    public $iblockId = [
        'ISPOLNITEL'        => 508,
        'ORDERS'            => 509,
        'ORDERS_COMMENT'    => 510,
        'ORDERS_OBJECT'     => 511,
        'ORDERS_THEME'      => 483,
        'PROTOCOLS'         => 561,
    ];

    /** @var array */
    public $arOrderFields = [
        'ID',
        'NAME',
        'TAGS',
        'ACTIVE',
        'XML_ID',
        'IBLOCK_ID',
        'DATE_CREATE',
        'DETAIL_TEXT',
        'PROPERTY_POST', /* Куратор. */
        'PROPERTY_ISPOLNITEL', /* Исполнитель. */
        'PROPERTY_CONTROLER', /* Контролер. */
        'PROPERTY_NUMBER', /* Номер обращения. */
        'PROPERTY_OBJECT', /* Обьект. */
        'PROPERTY_DATE_CREATE', /* Дата поручения. */
        'PROPERTY_DATE_ISPOLN', /* Срок исполнения. */
        'PROPERTY_DATE_FACT', /* Дата снятия с контроля - проверить нужно ли поле. */
        'PROPERTY_ACTION', /* Состояние. */
        'PROPERTY_STATUS', /* Статус. */
        'PROPERTY_TYPE', /* Тип поручения. */
        'PROPERTY_DOCUMENT', /* Документ основания - проверить нужно ли поле. */
        'PROPERTY_THEME', /* Тема поручения. */
        'PROPERTY_HISTORY_SROK', /* История изменения срока исполнения. */
        'PROPERTY_DSP', /* Для служебного пользования - проверить нужно ли поле. */
        'PROPERTY_OST_VIEW', /* Отметка о выезде - проверить нужно ли поле. */
        'PROPERTY_DOCS', /* Документы. */
        'PROPERTY_TASKS', /* Задачи. */
        'PROPERTY_PORUCH', /* Связанные поручения. */
        'PROPERTY_VIEWS', /* Отметки о просмотре. */
        'PROPERTY_POST_RESH', /* Решение Куратора. */
        'PROPERTY_CAT_THEME', /* Категория классификатора. */
        'PROPERTY_CONTROLER_RESH', /* Решение контролера. */
        'PROPERTY_CATEGORY', /* Категория поручения. */
        'PROPERTY_DATE_FACT_SNYAT', /* Дата фактического снятия с контроля. */
        'PROPERTY_DATE_FACT_ISPOLN', /* Дата отчета. */
        'PROPERTY_DOPSTATUS', /* Дополнительный статус. */
        'PROPERTY_NEWISPOLNITEL', /* Новый исполнитель. */
        'PROPERTY_OLD_PORUCH', /* Старое поручение. УЖЕ НЕ ИСПОЛЬЗУЕТСЯ, СКОРО МОЖНО ВЫПИЛИТЬ */
        'PROPERTY_NEW_PORUCH', /* Новое поручение. */
        'PROPERTY_DATE_ISPOLN_HIST', /* Сроки исполнения. */
        'PROPERTY_NEW_DATE_ISPOLN', /* Новый срок исполнения. */
        'PROPERTY_POSITION_TO', /* Поручения для позиции. */
        'PROPERTY_POSITION_FROM', /* Позиция по поручению. */
        'PROPERTY_CONTROLER_STATUS', /* Статус контролера. */
        'PROPERTY_DELEGATE_USER', /* Делегирование пользователю. */
        'PROPERTY_PROTOCOL_ID', /* Протокол. */
        'PROPERTY_DELEGATE_HISTORY', /* История делегирования. */
        'PROPERTY_NOT_STATS', /* Не учитывать в нарушениях. */
        'PROPERTY_CONTROL_REJECT', /* Возвраты от контролёра. */
        'PROPERTY_ACCOMPLICES', /* Соисполнители. */
        'PROPERTY_ACTION_DATE', /* Дата изменения состояния. */
        'PROPERTY_SUBEXECUTOR', /* Соисполнитель - привязка к ИБ Исполнителям. */
        'PROPERTY_SUBEXECUTOR_DATE', /* Срок для соисполнителя. */
        'PROPERTY_DELEGATION', /* Делегирование. */
        'PROPERTY_POSITION_ISPOLN', /* Передача на позицию. */
        'PROPERTY_DATE_REAL_ISPOLN', /* Дата исполнения. */
        'PROPERTY_DATE_ISPOLN_BAD', /* Сроки исполнения (не выполнено). */
        'PROPERTY_WORK_INTER_STATUS', /* Промежуточный статус. */
        'PROPERTY_REQUIRED_VISA', /* Нужна виза. */
        'PROPERTY_NEW_SUBEXECUTOR_DATE', /* Новый срок соисполнителя. */
        'PROPERTY_FIRST_EXECUTOR', /* Первый исполнитель. */
        'PROPERTY_POSITION_ISPOLN_REQS', /* Требования для позиции. */
    ];

    /** @var array */
    public $arReportFields = [
        'ID',
        'NAME',
        'ACTIVE',
        'XML_ID',
        'IBLOCK_ID',
        'DATE_CREATE',
        'DETAIL_TEXT',
        'PREVIEW_TEXT',
        'PROPERTY_PORUCH', /* Поручение */
        'PROPERTY_USER', /* Пользователь */
        'PROPERTY_TYPE', /* Тип комментария */
        'PROPERTY_DOCS', /* Документы */
        'PROPERTY_ECP', /* Подпись ЭЦП */
        'PROPERTY_DATE_VOTE', /* Дата опроса */
        'PROPERTY_RESULT_VOTE', /* Результат опроса */
        'PROPERTY_FILE_ECP', /* Файл для подписи */
        'PROPERTY_VISA', /* Визирование */
        'PROPERTY_CURRENT_USER', /* Текущий пользователь */
        'PROPERTY_COMMENT', /* Комментарий главного исполнителя */
        'PROPERTY_BROKEN_SROK', /* Срок нарушен */
        'PROPERTY_VISA_TYPE', /* Тип визирования */
        'PROPERTY_DATE_FACT', /* Дата фактического исполнения */
        'PROPERTY_CONTROLER_COMMENT', /* Комментарий контролера */
        'PROPERTY_SIGNER', /* Кто подписывает */
        'PROPERTY_STATUS', /* Статус */
        'PROPERTY_CHANGES', /* Изменения */
    ];

    /**
     * Конфигурация AJAX-методов
     *
     * @todo Вынести в AJAX
     *
     * @return array
     */
    public function configureActions()
    {
        return [
            'ActionFromList' => [
                'prefilters'  => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST]
                    ),
                ],
                'postfilters' => [],
            ],
            'pdfGenerate'     => [
                'prefilters'  => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST]
                    ),
                ],
                'postfilters' => [],
            ],
            'getDocsForPoruch' => [
                'prefilters'  => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_GET, ActionFilter\HttpMethod::METHOD_POST]
                    ),
                ],
                'postfilters' => [],
            ],
            'setVisa' => [
                'prefilters'  => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\ContentType(
                        [ActionFilter\ContentType::JSON]
                    ),
                    new ActionFilter\Csrf(),
                ],
                'postfilters' => [],
            ],
            'updateOrders' => [
                'prefilters'  => [
                    new ActionFilter\Authentication(),
                    new ActionFilter\HttpMethod(
                        [ActionFilter\HttpMethod::METHOD_POST]
                    ),
                    new ActionFilter\ContentType(
                        [ActionFilter\ContentType::JSON]
                    ),
                    new ActionFilter\Csrf(),
                ],
                'postfilters' => [],
            ],
        ];
    }

    /**
     * Генерация документа с поручениями
     *
     * @todo Вынести в AJAX
     *
     * @param string $ids Список ID поручений.
     *
     * @return string
     *
     * @throws RuntimeException Невозможно создать папку.
     */
    public function getDocsForPoruchAction(string $ids = '')
    {
        $arIspolnitels = Executors::getList();
        $arDocsFilter = [
            'IBLOCK_ID' => Settings::$iblockId['ORDERS'],
            'ID'        => explode(',', $ids),
            'ACTIVE'    => 'Y',
        ];
        $res = CIBlockElement::GetList(
            [
                'DATE_CREATE' => 'DESC'
            ],
            $arDocsFilter,
            false,
            false,
            [
                'ID',
                'NAME',
                'DETAIL_TEXT',
                'PROPERTY_NUMBER',
                'PROPERTY_DATE_CREATE',
                'PROPERTY_ISPOLNITEL',
                'PROPERTY_POSITION_ISPOLN_REQS',
            ]
        );
        $arContentItems = [];
        while ($arFields = $res->GetNext()) {
            $sName = $arFields['NAME'];
            $sName .= ' № ' . $arFields['PROPERTY_NUMBER_VALUE'];
            $sName .= ' от ' . $arFields['PROPERTY_DATE_CREATE_VALUE'];

            $arReportFilter = [
                'ACTIVE'            => 'Y',
                'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
                'PROPERTY_PORUCH'   => $arFields['ID'] ,
                'PROPERTY_TYPE'     => 1132
            ];
            $resReport = CIBlockElement::GetList(
                [
                    'DATE_CREATE' => 'DESC'
                ],
                $arReportFilter,
                false,
                [
                    'nTopCount' => 1
                ],
                $this->arReportFields
            );
            $sReport = '';
            while ($arReport = $resReport->GetNext()) {
                if (empty($arReport['DETAIL_TEXT'])) {
                    $arReport['DETAIL_TEXT'] = $arReport['PREVIEW_TEXT'];
                    $arReport['~DETAIL_TEXT'] = $arReport['~PREVIEW_TEXT'];
                }

                if ($arReport['PROPERTY_TYPE_ENUM_ID'] == 1132) {
                    $sReport = $arReport['~DETAIL_TEXT'];
                }
            }

            $arContentItems[ $arFields['ID'] ] = [
                'Name'      => $sName,
                'Content'   => $arFields['~DETAIL_TEXT'],
                'Executor'  => $arIspolnitels[ $arFields['PROPERTY_ISPOLNITEL_VALUE'] ]['NAME'],
                'Report'    => $sReport,
            ];
        }

        $file = new File($_SERVER['DOCUMENT_ROOT'] . '/local/templates_docx/checkorders/position.docx');
        $body = new Docx($file->getContents());
        $body->normalizeContent();
        $params = [
            'Items' => new ArrayDataProvider(
                $arContentItems,
                [
                    'ITEM_NAME' => 'Item',
                    'ITEM_PROVIDER' => ArrayDataProvider::class
                ]
            ),
            'ItemsName' => 'Items.Item.Name',
            'ItemsContent' => 'Items.Item.Content',
            'ItemsExecutor' => 'Items.Item.Executor',
            'ItemsReport' => 'Items.Item.Report',
        ];

        $body->setValues($params);
        $body->process();
        $strContent = $body->getContent();
        $docPath = '/upload/checkorders/';
        $strFileName = 'position_' . time() . '.docx';
        $path = $_SERVER['DOCUMENT_ROOT'] . $docPath;
        if (!mkdir($path, 0775, true) && !is_dir($path)) {
            throw new RuntimeException('Directory ' . $docPath . ' was not created');
        }
        file_put_contents($path . $strFileName, $strContent);

        return $docPath . $strFileName;
    }

    /**
     * ActionFromListAction
     *
     * @todo Вынести в AJAX
     *
     * @param string $sAction         Действие.
     * @param array  $arrayOfIds      Массив ID поручений.
     * @param array  $arrayOfFilesIds Массив ID файлов.
     *
     * @return array
     */
    public function ActionFromListAction(
        string $sAction,
        array $arrayOfIds = [],
        array $arrayOfFilesIds = []
    ) {
        global $USER;
        $arProducts = [];
        $arErrors = [];
        $obOrders = new Orders();
        switch ($sAction) {
            case 'accept_kurator':
                foreach ($arrayOfIds as $sKey => $sId) {
                    $el = new CIBlockElement();

                    $arProp = [
                        'PORUCH'    => $sId,
                        'USER'      => $USER->GetID(),
                        'TYPE'      => 1133,
                        'ECP'       => $USER->GetID(),
                        'FILE_ECP'  => $arrayOfFilesIds[ $sKey ],
                    ];

                    $arLoadProductArray = [
                        'MODIFIED_BY'       => $USER->GetID(),
                        'IBLOCK_SECTION_ID' => false,
                        'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
                        'PROPERTY_VALUES'   => $arProp,
                        'NAME'              => $USER->GetID() . '-' . $sId . '-' . date('d-m-Y_h:i:s'),
                        'ACTIVE'            => 'Y',
                        'PREVIEW_TEXT'      => 'Снято с контроля',
                        'DETAIL_TEXT'       => 'Снято с контроля',
                    ];

                    if ($PRODUCT_ID = $el->Add($arLoadProductArray)) {
                        CIBlockElement::SetPropertyValuesEx(
                            $sId,
                            false,
                            [
                                'VIEWS'             => false,
                                'ACTION'            => Settings::$arActions['ARCHIVE'],
                                'STATUS'            => 1142,
                                'POST_RESH'         => 1204,
                                'DATE_FACT_SNYAT'   => date('d.m.Y'),
                                'WORK_INTER_STATUS' => false,
                            ]
                        );
                        self::fixSrokNarush($sId);

                        $this->log(
                            $sId,
                            'Снято с контроля',
                            [
                                'METHOD'    => __METHOD__,
                                'REQUEST'   => $_REQUEST,
                            ]
                        );
                        $arPositionFrom = $obOrders->getProperty($sId, 'POSITION_TO', true);
                        if (!empty($arPositionFrom)) {
                            foreach ($arPositionFrom as $value) {
                                $this->log(
                                    $value,
                                    'Позиция по поручению принята куратором',
                                    [
                                        'METHOD'    => __METHOD__,
                                        'REQUEST'   => $_REQUEST,
                                    ]
                                );
                                $this->addToLog(
                                    'Позиция по поручению принята куратором',
                                    strip_tags($arLoadProductArray['DETAIL_TEXT']),
                                    $value
                                );
                            }
                        }
                        $arProducts[] = $PRODUCT_ID;
                        $this->addToLog(
                            'Снято с контроля',
                            strip_tags($arLoadProductArray['DETAIL_TEXT']),
                            $sId
                        );
                    } else {
                        $arErrors[] = $el->LAST_ERROR;
                    }
                }
                break;
            case 'reject_kurator':
                foreach ($arrayOfIds as $sKey => $sId) {
                    $el = new CIBlockElement();

                    $arProp = [
                        'PORUCH'    => $sId,
                        'USER'      => $USER->GetID(),
                        'TYPE'      => 1133,
                        'ECP'       => $USER->GetID(),
                        'FILE_ECP'  => $arrayOfFilesIds[ $sKey ],
                    ];

                    $arLoadProductArray = [
                        'MODIFIED_BY'       => $USER->GetID(),
                        'IBLOCK_SECTION_ID' => false,
                        'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
                        'PROPERTY_VALUES'   => $arProp,
                        'NAME'              => $USER->GetID() . '-' . $sId . '-' . date('d-m-Y_h:i:s'),
                        'ACTIVE'            => 'Y',
                        'PREVIEW_TEXT'      => 'Отправлено на дополнительный контроль',
                        'DETAIL_TEXT'       => 'Отправлено на дополнительный контроль',
                    ];

                    if ($PRODUCT_ID = $el->Add($arLoadProductArray)) {
                        $arDopstatus = $obOrders->getProperty($sId, 'DOPSTATUS');
                        $arPositionFrom = $obOrders->getProperty($sId, 'POSITION_TO', true);
                        $arPositionIspoln = $obOrders->getProperty($sId, 'POSITION_ISPOLN');
                        $arNewDate = $obOrders->getProperty($sId, 'NEW_DATE_ISPOLN');
                        $arNewSubDate = $obOrders->getProperty($sId, 'NEW_SUBEXECUTOR_DATE');
                        $arOldDate = $obOrders->getProperty($sId, 'DATE_ISPOLN');
                        $arDates = $obOrders->getProperty($sId, 'DATE_ISPOLN_HIST', true);
                        $arDatesBad = $obOrders->getProperty($sId, 'DATE_ISPOLN_BAD', true);
                        $arTypes = $obOrders->getProperty($sId, 'TYPE', true);

                        $this->log(
                            $sId,
                            'Отправлено на дополнительный контроль',
                            [
                                'METHOD'    => __METHOD__,
                                'REQUEST'   => $_REQUEST,
                            ]
                        );
                        if ($arNewDate['VALUE'] != '') {
                            $arDates[] = $arOldDate['VALUE'];

                            $bSendEmailSrok = false;
                            $arDelegateHistory = [];
                            $arElement = $obOrders->getById($sId);
                            foreach ($arElement['~PROPERTY_DELEGATE_HISTORY_VALUE'] as $history) {
                                $history = json_decode($history, true);
                                if ($history['DELEGATE'] == $arElement['PROPERTY_DELEGATE_USER_VALUE']) {
                                    $history['SROK'] = $arNewDate['VALUE'];
                                    $bSendEmailSrok = true;
                                }

                                $arDelegateHistory[] = json_encode($history, JSON_UNESCAPED_UNICODE);
                            }

                            if ($bSendEmailSrok) {
                                $arSendUsers = $this->ispolnitels[ $arElement['PROPERTY_ISPOLNITEL_VALUE'] ]['PROPERTY_IMPLEMENTATION_VALUE'];
                                if (!empty($arSendUsers)) {
                                    Notify::send(
                                        [$_REQUEST['edit']],
                                        'NEW_SROK',
                                        $arSendUsers
                                    );
                                }
                            }

                            CIBlockElement::SetPropertyValuesEx(
                                $sId,
                                false,
                                [
                                    'DATE_ISPOLN'       => $arNewDate['VALUE'],
                                    'DATE_ISPOLN_HIST'  => $arDates,
                                    'DELEGATE_HISTORY'  => $arDelegateHistory,
                                    'NEW_DATE_ISPOLN'   => false,
                                ]
                            );
                            if ($arDopstatus['VALUE_XML_ID'] == 'change_srok') {
                                $arDatesBad[] = $arOldDate['VALUE'];
                                CIBlockElement::SetPropertyValuesEx(
                                    $sId,
                                    false,
                                    [
                                        'DATE_ISPOLN_BAD' => $arDatesBad,
                                    ]
                                );
                            }
                            $this->log(
                                $sId,
                                'Изменен срок исполнения',
                                [
                                    'METHOD'    => __METHOD__,
                                    'REQUEST'   => $_REQUEST,
                                ]
                            );
                            $this->addToLog(
                                'Изменен срок исполнения',
                                $arNewDate['VALUE'],
                                $sId
                            );

                            if ($arNewSubDate['VALUE'] != '') {
                                $newSubDate = $arNewSubDate['VALUE'];
                                if ($newSubDate == $this->disableSrokDate) {
                                    $newSubDate = $arNewDate['VALUE'];
                                }
                                CIBlockElement::SetPropertyValuesEx(
                                    $sId,
                                    false,
                                    [
                                        'SUBEXECUTOR_DATE'      => $newSubDate,
                                        'NEW_SUBEXECUTOR_DATE'  => false,
                                    ]
                                );

                                $this->log(
                                    $sId,
                                    'Изменен срок соисполнителя',
                                    [
                                        'METHOD'    => __METHOD__,
                                        'REQUEST'   => $_REQUEST,
                                    ]
                                );
                                $this->addToLog('Изменен срок соисполнителя', $newSubDate);
                            }
                        }

                        $arNewIspoln = $obOrders->getProperty($sId, 'NEWISPOLNITEL');
                        if ($arNewIspoln['VALUE'] != '') {
                            $arElement = $obOrders->getById($sId);
                            $arSetFields = [
                                'STATUS'                => 1141,
                                'ACTION'                => Settings::$arActions['NEW'],
                                'ISPOLNITEL'            => $arNewIspoln['VALUE'],
                                'VIEWS'                 => false,
                                'POST_RESH'             => false,
                                'DOPSTATUS'             => false,
                                'DELEGATE_USER'         => false,
                                'POSITION_FROM'         => false,
                                'NEWISPOLNITEL'         => false,
                                'CONTROLER_RESH'        => false,
                                'DATE_FACT_SNYAT'       => false,
                                'NEW_DATE_ISPOLN'       => false,
                                'CONTROLER_STATUS'      => false,
                                'DATE_FACT_ISPOLN'      => false,
                                'WORK_INTER_STATUS'     => false,
                                'NEW_SUBEXECUTOR_DATE'  => false,
                            ];
                            if (empty($arElement['PROPERTY_FIRST_EXECUTOR_VALUE'])) {
                                $arSetFields['FIRST_EXECUTOR'] = $arElement['PROPERTY_ISPOLNITEL_VALUE'];
                            }
                            if (!empty($arElement['PROPERTY_DELEGATION_VALUE'])) {
                                $arDelegation = [];
                                foreach ($arElement['PROPERTY_DELEGATION_VALUE'] as $key => $value) {
                                    if ($value == $arElement['PROPERTY_ISPOLNITEL_VALUE']) {
                                        $arDelegation[] = [
                                            'VALUE'         => $arNewIspoln['VALUE'],
                                            'DESCRIPTION'   => '',
                                        ];
                                    } else {
                                        $arDelegation[] = [
                                            'VALUE'         => $value,
                                            'DESCRIPTION'   => $arElement['PROPERTY_DELEGATION_DESCRIPTION'][ $key ],
                                        ];
                                    }
                                }
                                $arSetFields['DELEGATION'] = $arDelegation;
                            }
                            CIBlockElement::SetPropertyValuesEx(
                                $sId,
                                false,
                                $arSetFields
                            );

                            $logText = 'Передано на исполнение новому исполнителю';
                            $this->log(
                                $sId,
                                $logText,
                                [
                                    'METHOD'    => __METHOD__,
                                    'REQUEST'   => $_REQUEST,
                                ]
                            );
                            $arExecutors = Executors::getList();
                            $this->addToLog(
                                $logText,
                                $arExecutors[ $arNewIspoln['VALUE'] ]['NAME'] ?? '',
                                $sId
                            );
                        }

                        if ($arPositionIspoln['VALUE'] != '') {
                            $this->addPositionFromExist($sId, $arPositionIspoln['VALUE']);
                        } else {
                            if (!empty($arPositionFrom)) {
                                CIBlockElement::SetPropertyValuesEx(
                                    $sId,
                                    false,
                                    [
                                        'ACTION'    => Settings::$arActions['NEW'],
                                        'DOPSTATUS' => false,
                                    ]
                                );
                                foreach ($arPositionFrom as $value) {
                                    $this->log(
                                        $value,
                                        'Отправка на позицию принята куратором, ожидание позиции',
                                        [
                                            'METHOD'    => __METHOD__,
                                            'REQUEST'   => $_REQUEST,
                                        ]
                                    );
                                    $this->addToLog(
                                        'Отправка на позицию принята куратором, ожидание позиции',
                                        ($_REQUEST['DETAIL_TEXT']?strip_tags($_REQUEST['DETAIL_TEXT']):''),
                                        $value
                                    );
                                }
                            } else {
                                CIBlockElement::SetPropertyValuesEx(
                                    $sId,
                                    false,
                                    [
                                        'ACTION'            => Settings::$arActions['NEW'],
                                        'POST_RESH'         => 1203,
                                        'DATE_FACT_ISPOLN'  => false,
                                        'DOPSTATUS'         => false,
                                        'WORK_INTER_STATUS' => false,
                                    ]
                                );
                                $arComFilter = [
                                    'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
                                    'ACTIVE'            => 'Y',
                                    'PROPERTY_TYPE'     => 1131,
                                    'PROPERTY_PORUCH'   => $sId,
                                ];
                                $resCom = CIBlockElement::GetList(
                                    [
                                        'DATE_CREATE' => 'DESC'
                                    ],
                                    $arComFilter,
                                    false,
                                    [
                                        'nPageSize' => 1
                                    ],
                                    $this->arReportFields
                                );
                                while ($arComFields = $resCom->GetNext()) {
                                    CIBlockElement::SetPropertyValuesEx(
                                        $arComFields['ID'],
                                        false,
                                        [
                                            'CURRENT_USER'      => false,
                                            'STATUS'            => false,
                                            'CONTROLER_COMMENT' => '-',
                                        ]
                                    );
                                }
                            }
                            $this->addToLog(
                                'Отправлено на дополнительный контроль',
                                ($_REQUEST['DETAIL_TEXT']?strip_tags($_REQUEST['DETAIL_TEXT']):''),
                                $sId
                            );
                            $arTypes[] = 'dopcontrol';
                        }

                        CIBlockElement::SetPropertyValuesEx(
                            $sId,
                            false,
                            [
                                'VIEWS' => false,
                                'TYPE'  => $arTypes,
                            ]
                        );
                    } else {
                        $arErrors[] = $el->LAST_ERROR;
                    }
                }
                break;
            default:
                break;
        }
        return [
            'arrayOfIds'    => $arrayOfIds,
            'IDS'           => $arProducts,
            'ERRORS'        => $arErrors
        ];
    }

    /**
     * pdfGenerateAction
     *
     * @param string  $data        Текст отчёта.
     * @param string  $action_head Тип выгружаемого отчета.
     * @param integer $id          ID протокола.
     * @param string  $visa        Список людей, кем согласован отчет.
     * @param integer $file_id     ID существующего файла.
     *
     * @return array
     * @todo Вынести в AJAX
     *
     */
    public function pdfGenerateAction(
        string $data = '',
        string $action_head = '',
        int $id = 0,
        string $visa = '',
        int $file_id = 0
    ) {
        $fileName = $id . '_' . $file_id . '_' . time();
        if ($file_id > 0) {
            $arFileData = Signer::getFiles([$file_id])[ $file_id ];

            $path = $_SERVER['DOCUMENT_ROOT'] . $arFileData['SRC'];
            file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/upload/checkorders/' . $fileName . '.pdf', file_get_contents($path));

            $pdf = CFile::MakeFileArray($_SERVER['DOCUMENT_ROOT'] . '/upload/checkorders/' . $fileName . '.pdf');
            $newFileId = CFile::SaveFile($pdf, 'checkorders');

            foreach ($arFileData['SIGNS'] as $userId => $userData) {
                foreach ($userData['SIGNS'] as $arSign) {
                    $arFileArray            = CFile::GetFileArray($arSign['ID']);
                    $arFile                 = CFile::MakeFileArray($_SERVER['DOCUMENT_ROOT'] . $arSign['SRC']);
                    $arFile['MODULE_ID']    = 'pdf_file';
                    $arFile['name']         = $arFileArray['ORIGINAL_NAME'];
                    $arFile['type']         = 'application/x-pkcs7-certreqresp';
                    $arFile['description']  = $arFileArray['DESCRIPTION'];
                    $arFile['external_id']  = str_replace($file_id, $newFileId, $arFileArray['EXTERNAL_ID']);

                    CFile::SaveFile($arFile, 'pdfile', true);
                }
            }

            return [
                'sessid'    => bitrix_sessid(),
                'file_id'   => $newFileId,
            ];
        }
        $html = $data;
        if ($id > 0) {
            $arResult['ISPOLNITELS'] = Executors::getList();
            $res = CIBlockElement::GetList(
                [
                    'DATE_CREATE' => 'DESC'
                ],
                [
                    'IBLOCK_ID' => Settings::$iblockId['ORDERS'],
                    'ID'        => $id,
                    'ACTIVE'    => 'Y',
                ],
                false,
                [
                    'nPageSize' => 1
                ],
                $this->arOrderFields
            );

            if ($arFields = $res->GetNext()) {
                switch ($action_head) {
                    case 'ispolnitel_main':
                        $html = '<h1>Отчет об исполнении поручения</h1><br><br>';
                        $html .= '<u><b>Документ:</b></u> ' . $arFields['NAME'] . ' № ' . $arFields['PROPERTY_NUMBER_VALUE'] . ' от ' . $arFields['PROPERTY_DATE_CREATE_VALUE'] . '<br><br>';
                        $html .= '<u><b>Исполнитель:</b></u> ' . $arResult['ISPOLNITELS'][ $arFields['PROPERTY_ISPOLNITEL_VALUE'] ]['NAME'] . '<br><br>';
                        $html .= '<u><b>Срок исполнения:</b></u> ' . ($arFields['PROPERTY_DATE_ISPOLN_VALUE'] != $this->disableSrokDate ?
                                $arFields['PROPERTY_DATE_ISPOLN_VALUE'] :
                                'Без срока') . '<br><br>';
                        $html .= '<u><b>Дата предоставления отчета:</b></u> ' . date('d.m.Y') . '<br><br>';

                        $html .= '<u><b>Содержание поручения:</b></u> ' . $arFields['~DETAIL_TEXT'] . '<br><br>';
                        $html .= '<u><b>Содержание отчета:</b></u> ' . $data;
                        if (!empty($visa)) {
                            $html .= '<u><b>Согласовано:</b></u> ' . $visa . '<br><br>';
                        }
                        break;
                    case 'controler':
                        $html = '<h1>Отчет контролера</h1><br><br>';
                        $html .= $data;
                        break;
                    case 'kurator':
                        $html = '<h1>Заметка Куратора</h1><br><br>';
                        $html .= $data;
                        break;
                    default:
                        $html = $data;
                        break;
                }
            }
        } else {
            switch ($action_head) {
                case 'ispolnitel_main':
                    $html = '<h1>Отчет исполнителя</h1><br><br>';
                    $html .= $data;
                    break;
                case 'controler':
                    $html = '<h1>Отчет контролера</h1><br><br>';
                    $html .= $data;
                    break;
                case 'kurator':
                    $html = '<h1>Заметка Куратора</h1><br><br>';
                    $html .= $data;
                    break;
                default:
                    $html = $data;
                    break;
            }
        }

        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/upload/checkorders/' . $fileName . '.html', $html);
        $doc_file_pdf_data = shell_exec('/usr/bin/php72 -f ' . $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/libs/mpdf/converter.php' . ' ' . $_SERVER['DOCUMENT_ROOT'] . '/upload/checkorders/' . $fileName . '.html');

        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/upload/checkorders/' . $fileName . '.pdf', $doc_file_pdf_data);

        $pdf = CFile::MakeFileArray($_SERVER['DOCUMENT_ROOT'] . '/upload/checkorders/' . $fileName . '.pdf');
        $file_id = CFile::SaveFile($pdf, 'checkorders');
        return [
            'sessid'    => bitrix_sessid(),
            'file_id'   => $file_id,
            'filename'  => $fileName,
        ];
    }

    /**
     * Добавить визу к комментарию
     *
     * @todo Вынести в AJAX
     *
     * @param array $data Массив с данными о визе.
     *
     * @return boolean
     *
     * @throws Exception При недостатке параметров или если не найден элемент.
     */
    public function setVisaAction(array $data = [])
    {
        if (empty($data['id']) || empty($data['user']) || empty($data['value'])) {
            throw new Exception('Не переданы обязательные параметры');
        }

        if ($data['value'] == 'N' && empty($data['comment'])) {
            throw new Exception('Введите комментарий');
        }

        $obOrders = new Orders();

        $res = CIBlockElement::GetList(
            [
                'DATE_CREATE' => 'DESC'
            ],
            [
                'IBLOCK_ID' => Settings::$iblockId['ORDERS_COMMENT'],
                'ID'        => $data['id']
            ],
            false,
            false,
            $this->arReportFields
        );
        if ($arFields = $res->GetNext()) {
            foreach ($arFields['PROPERTY_VISA_VALUE'] as $key => $visaRow) {
                [$userId, $status, $comment, $date] = explode(':', $visaRow, 4);
                if (0 === mb_strpos($data['user'], 'SIGN')) {
                    continue;
                }
                if ((int)$userId === $data['user']) {
                    $arVisa = [
                        $data['user'],
                        $data['value'],
                        $data['comment'],
                        date('d.m.Y H:i:s')
                    ];
                    $arFields['PROPERTY_VISA_VALUE'][ $key ] = implode(':', $arVisa);
                }
            }

            $arParams['ENUM'] = $this->getEnums(Settings::$iblockId['ORDERS']);
            $arParams['COMMENT_ENUM'] = $this->getEnums(Settings::$iblockId['ORDERS_COMMENT']);

            $commentText = 'Визирование';
            if ($arFields['PROPERTY_TYPE_ENUM_ID'] == $arParams['COMMENT_ENUM']['TYPE']['accomplience']['ID']) {
                $commentText = 'Согласование';
            }

            $logText = 'Добавлена виза к комментарию исполнителя';
            if ($arFields['PROPERTY_TYPE_ENUM_ID'] == $arParams['COMMENT_ENUM']['TYPE']['accomplience']['ID']) {
                $logText = 'Добавлена виза к комментарию соисполнителя';
            }
            $comment = '';
            if ($data['value'] == 'N') {
                $comment = 'Не согласовано<br/><br/>Комментарий: ' . $data['comment'];
            } elseif ($data['value'] == 'A') {
                $logText = '[' . $commentText . '] ' . $this->getUserFullName($data['user']) . ' отсутствует';
            } elseif ($data['value'] == 'E') {
                $logText = '[' . $commentText . '] ' . $this->getUserFullName($data['user']) . ' убрано отсутствие';
            } else {
                $comment = 'Согласовано';
            }
            $this->addToLog(
                $logText,
                $comment,
                $arFields['PROPERTY_PORUCH_VALUE']
            );
            $this->log(
                $arFields['PROPERTY_PORUCH_VALUE'],
                $logText,
                [
                    'METHOD'    => __METHOD__,
                    'data'      => $data,
                ]
            );

            CIBlockElement::SetPropertyValuesEx(
                $data['id'],
                false,
                [
                    'VISA' => $arFields['PROPERTY_VISA_VALUE'],
                ]
            );

            $visaTypeCode = $arParams['COMMENT_ENUM']['VISA_TYPE'][ $arFields['PROPERTY_VISA_TYPE_ENUM_ID'] ]['EXTERNAL_ID'];
            $bSendEvent = true;

            /*
             * Если проставлены все визы, то перенести на вкладку На подпись.
             */
            $signUser = 0;
            $cntToVisa = count($arFields['PROPERTY_VISA_VALUE']);
            foreach ($arFields['PROPERTY_VISA_VALUE'] as $key => $visaRow) {
                [$userId, $status, $comment, $date] = explode(':', $visaRow, 4);
                if (in_array($status, ['Y', 'N', 'A'])) {
                    if ($status == 'Y') {
                        $cntToVisa--;
                    } elseif ($status == 'A') {
                        $cntToVisa--;
                    }
                } elseif (
                    $visaTypeCode == 'after' &&
                    $bSendEvent
                ) {
                    if (
                        0 === mb_strpos($userId, 'SIGN') &&
                        $status != 'Y'
                    ) {
                        $signUser = $userId;
                        $cntToVisa = 0;
                        break;
                    }
                    $nextEventUser = (int)mb_substr($visaRow, 0, mb_strpos($visaRow, ':S:'));

                    if ($nextEventUser > 0) {
                        $arFields['PROPERTY_VISA_VALUE'][ $key ] = str_replace(':S:', ':E:', $visaRow);
                        CIBlockElement::SetPropertyValuesEx(
                            $data['id'],
                            false,
                            [
                                'VISA' => $arFields['PROPERTY_VISA_VALUE'],
                            ]
                        );

                        Notify::send(
                            [$arFields['PROPERTY_PORUCH_VALUE']],
                            'VISA',
                            [$nextEventUser]
                        );
                        $bSendEvent = false;
                    }
                }
            }

            $link = 'otchet_ispolnitel';
            if ($arFields['PROPERTY_TYPE_ENUM_ID'] == $arParams['COMMENT_ENUM']['TYPE']['accomplience']['ID']) {
                // Если добавили отчет соисполнителя - отправить на контроль
                if (
                    $obOrders->isExternal($arFields['PROPERTY_PORUCH_VALUE']) &&
                    $cntToVisa <= 0
                ) {
                    Notify::send(
                        [$arFields['PROPERTY_PORUCH_VALUE']],
                        'ACCOMPLICES_REPORT'
                    );
                    $this->log(
                        $arFields['PROPERTY_PORUCH_VALUE'],
                        'Отправлено на контроль',
                        [
                            'METHOD'    => __METHOD__,
                            'REQUEST'   => $_REQUEST,
                        ]
                    );
                    $this->addToLog('Отправлено на контроль', '', $arFields['PROPERTY_PORUCH_VALUE']);
                    CIBlockElement::SetPropertyValuesEx(
                        $arFields['PROPERTY_PORUCH_VALUE'],
                        false,
                        [
                            'ACTION'            => Settings::$arActions['CONTROL'],
                            'DATE_FACT_ISPOLN'  => date('d.m.Y'),
                            'CONTROLER_STATUS'  => $arParams['ENUM']['CONTROLER_STATUS']['on_beforing']['ID'],
                            'WORK_INTER_STATUS' => false,
                        ]
                    );
                }

                $cntToVisa = 999; /* crunch */
                $link = 'otchet_accomplience';
            }

            /*
             * Отправить уведомление автору отчёта
             */
            if ($data['value'] == 'N') {
                global $userFields;

                $res2 = CIBlockElement::GetList(
                    [
                        'DATE_CREATE' => 'DESC'
                    ],
                    [
                        'IBLOCK_ID' => Settings::$iblockId['ORDERS'],
                        'ID'        => $arFields['PROPERTY_PORUCH_VALUE'],
                        'ACTIVE'    => 'Y',
                    ],
                    false,
                    false,
                    [
                        'ID',
                        'NAME',
                        'PROPERTY_NUMBER',
                        'PROPERTY_DATE_CREATE'
                    ]
                );
                if ($arPoruchFields = $res2->GetNext()) {
                    $title = $arPoruchFields['NAME'];
                    $title .= ' № ' . $arPoruchFields['PROPERTY_NUMBER_VALUE'];
                    $title .= ' от ' . $arPoruchFields['PROPERTY_DATE_CREATE_VALUE'];
                    $userData = $userFields($data['user']);
                    $message = $title;
                    $message .= '#BR##BR#Не согласовано: ' . $userData['FIO'];
                    $message .= '#BR#Комментарий: ' . $data['comment'];
                    $message .= '#BR##BR#[URL=https://' . $_SERVER['SERVER_NAME'] . '/control-orders/?detail=' . $arPoruchFields['ID'] . '&view=' . $link . ']Перейти к отчёту[/URL]';
                    Notify::send(
                        [$arPoruchFields['ID']],
                        'VISA_NO_FOR_AUTHOR',
                        [$arFields['PROPERTY_USER_VALUE']],
                        $message
                    );
                }

                CIBlockElement::SetPropertyValuesEx(
                    $data['id'],
                    false,
                    [
                        'CURRENT_USER'  => false,
                        'STATUS'        => false,
                    ]
                );
                CIBlockElement::SetPropertyValuesEx(
                    $arFields['PROPERTY_PORUCH_VALUE'],
                    false,
                    [
                        'WORK_INTER_STATUS' => false,
                    ]
                );
            } elseif ($cntToVisa <= 0) {
                $this->log(
                    $arFields['PROPERTY_PORUCH_VALUE'],
                    'Отправлено на подпись, визы все проставлены',
                    [
                        'METHOD'    => __METHOD__,
                        'REQUEST'   => $_REQUEST,
                    ]
                );
                CIBlockElement::SetPropertyValuesEx(
                    $arFields['PROPERTY_PORUCH_VALUE'],
                    false,
                    [
                        'WORK_INTER_STATUS' => $arParams['ENUM']['WORK_INTER_STATUS']['TOSIGN']['ID'],
                    ]
                );
                CIBlockElement::SetPropertyValuesEx(
                    $arFields['ID'],
                    false,
                    [
                        'STATUS' => $arParams['COMMENT_ENUM']['STATUS']['TOSIGN']['ID'],
                    ]
                );
                if ($signUser > 0) {
                    CIBlockElement::SetPropertyValuesEx(
                        $arFields['ID'],
                        false,
                        [
                            'CURRENT_USER' => $signUser,
                        ]
                    );
                }

                $arCurrentOrder = $obOrders->getById($arFields['PROPERTY_PORUCH_VALUE']);
                $arDelegator = [];
                if (
                    !empty($arCurrentOrder['PROPERTY_DELEGATION_VALUE']) &&
                    (int)$arCurrentOrder['PROPERTY_DELEGATION_VALUE'][0] > 0
                ) {
                    $arDelegator = $this->ispolnitels[ $arCurrentOrder['PROPERTY_DELEGATION_VALUE'][0] ];
                }
                if (!empty($arDelegator) && !empty($arDelegator['PROPERTY_IMPLEMENTATION_VALUE'])) {
                    $arSendMsgUsers = $arDelegator['PROPERTY_IMPLEMENTATION_VALUE'];

                    $arSendDelegId = [
                        250900, /* Якушкина Г.И. */
                        250902, /* Гремякова О.П. */
                    ];
                    if (in_array($arDelegator['ID'], $arSendDelegId)) {
                        $arSendMsgUsers[] = $arDelegator['PROPERTY_RUKOVODITEL_VALUE'];
                    }
                    Notify::send(
                        [$arFields['PROPERTY_PORUCH_VALUE']],
                        'SIGN',
                        $arSendMsgUsers
                    );
                }
            }

            self::fixEvent('setVisa', $data['id'] . $data['user'] . $data['value'], false);

            return true;
        }

        throw new Exception('Отчёт не найден');
    }

    /**
     * Изменить поручения
     *
     * @param array $data Массив данных объекта.
     *
     * @return boolean
     *
     * @throws Exception При недостающих данных.
     * @todo Вынести в AJAX
     *
     */
    public function updateOrdersAction(array $data = [])
    {
        if (empty($data['ids'])) {
            throw new Exception('Не переданы поручения');
        }
        if (empty($data['prop'])) {
            throw new Exception('Не переданы изменения');
        }

        $obOrders = new Orders();
        $arProperties = [];

        $rProperties = CIBlockProperty::GetList(
            [
                'sort' => 'asc',
                'name' => 'asc'
            ],
            [
                'ACTIVE'    => 'Y',
                'IBLOCK_ID' => Settings::$iblockId['ORDERS']
            ]
        );
        while ($arFields = $rProperties->GetNext()) {
            $arProperties[ $arFields['CODE'] ] = $arFields;
        }

        foreach ($data['ids'] as $id) {
            $sTextChange = '';
            foreach ($data['prop'] as $prop => $value) {
                if ($prop == 'ACTION') {
                    $value = Settings::$arActions[ $value ];
                }
                $arElement = $obOrders->getById($id);
                CIBlockElement::SetPropertyValuesEx(
                    $id,
                    false,
                    [
                        $prop => $value,
                    ]
                );
                if (in_array($prop, ['POST', 'CONTROLER'])) {
                    $sTextChange .= '<b>' . $arProperties[ $prop ]['NAME'] . ':</b> ' . $this->getUserFullName($arElement['PROPERTY_' . $prop . '_VALUE'], true) . ' &rarr; ' . $this->getUserFullName($value, true) . '<br>';
                } elseif (array_key_exists($prop, $this->arParams['ENUM'])) {
                    $sTextChange .= '<b>' . $arProperties[ $prop ]['NAME'] . ':</b> ' . $arElement['PROPERTY_' . $prop . '_VALUE'] . ' &rarr; ' . $this->arParams['ENUM'][ $prop ][ $value ]['VALUE'] . '<br>';
                } else {
                    $sTextChange .= '<b>' . $arProperties[ $prop ]['NAME'] . ':</b> ' . $arElement['PROPERTY_' . $prop . '_VALUE'] . ' &rarr; ' . $value . '<br>';
                }
            }

            $this->log(
                $id,
                'Изменены данные поручения',
                [
                    'METHOD'    => __METHOD__,
                    'data'      => $data,
                ]
            );
            $this->addToLog(
                'Изменены данные поручения',
                $sTextChange,
                $id
            );
        }

        return true;
    }

    /**
     * Отрисовать HTML для объекта
     *
     * @todo Вынести в AJAX
     *
     * @param integer $id    ID объекта.
     * @param boolean $bEdit Если это страница редактирования.
     *
     * @return string
     */
    public function renderObject(int $id = 0, bool $bEdit = false)
    {
        $arEl = CIBlockElement::GetByID($id)->Fetch();
        $arEl['DETAIL_TEXT'] = $arEl['DETAIL_TEXT'] ? Json::decode($arEl['DETAIL_TEXT']) : [];
        $return = '
        <div class="order-tag">
            <span title=" ' . ($arEl['DETAIL_TEXT']['value'] ?? '') . '">' . $arEl['NAME'] . '</span>';
        if ($bEdit) {
            $return .= '
            <i class="js-order-tag-remove ml-1 cursor-pointer" title="Удалить объект">&times;</i>
            <input name="PROP[OBJECT][]" type="hidden" value="' . $id . '" />
            ';
        }
        $return .= '</div>';

        return $return;
    }

    /**
     * Добавить запись в историю изменений
     *
     * @param string  $sText   Текст.
     * @param string  $sData   Описание.
     * @param integer $iId     ID элемента.
     * @param string  $sType   Тип.
     * @param string  $sGrants Доступы.
     *
     * @return boolean
     */
    public function addToLog(
        string $sText,
        string $sData = '',
        int $iId = 0,
        string $sType = 'normal',
        string $sGrants = 'all'
    ) {
        global $USER;
        if ($iId <= 0) {
            $iId = $_REQUEST['detail'];
        }
        $arValues = [];
        $res = CIBlockElement::GetProperty(
            Settings::$iblockId['ORDERS'],
            $iId,
            'sort',
            'asc',
            [
                'CODE' => 'HISTORY_SROK'
            ]
        );
        while ($ob = $res->GetNext()) {
            $val = $ob['~VALUE'];
            if (false !== mb_strpos($val, '\u')) {
                $val = json_decode($val, true);
                $val = json_encode($val, JSON_UNESCAPED_UNICODE);
            }
            $arValues[] = $val;
        }

        $arData = [
            'TEXT'      => $sText,
            'DATE'      => date('d.m.Y H:i:s'),
            'DATE_TIME' => time(),
            'USER_ID'   => $USER->GetID(),
            'USER_NAME' => $USER->GetFullName(),
            'TYPE'      => $sType,
            'GRANT'     => $sGrants,
            'DATA'      => $sData,
        ];

        if (!empty($this->permissions['ispolnitel_data'])) {
            $arData['EXECUTOR'] = $this->permissions['ispolnitel_data']['ID'];
        }

        $arValues[] = json_encode($arData, JSON_UNESCAPED_UNICODE);
        CIBlockElement::SetPropertyValuesEx(
            $iId,
            false,
            [
                'HISTORY_SROK'  => $arValues,
                'VIEWS'         => false,
            ]
        );
        return true;
    }

    /**
     * Залогировать в файл.
     *
     * @param integer $id   ID поручения.
     * @param string  $msg  Сообщение.
     * @param array   $data Данные для дебага.
     *
     * @return void
     */
    public function log(
        int $id = 0,
        string $msg = '',
        array $data = []
    ) {
        $logger = new Logger('default');
        $logger->pushHandler(
            new StreamHandler(
                $_SERVER['DOCUMENT_ROOT'] . '/../newcorp_arch/controlorders/' . intval($id/1000) . '/' . $id . '.log'
            )
        );
        if (isset($data['DETAIL_TEXT'])) {
            unset($data['DETAIL_TEXT']);
        }
        if (isset($data['REQUEST']['DETAIL_TEXT'])) {
            unset($data['REQUEST']['DETAIL_TEXT']);
        }
        $uData = ' [' . $GLOBALS['USER']->GetID() . ' ' . $GLOBALS['USER']->GetFullName() . ']';
        $logger->info($msg . $uData, $data);
    }

    /**
     * Проверка доступов пользователя
     *
     * @param integer $id ID требуемого исполнителя.
     *
     * @return void
     */
    private function initPermissions($id = 0)
    {
        global $USER;
        $userGroups = $USER->GetUserGroupArray();
        $curUserId  = $USER->GetID();
        $obOrders   = new Orders();

        $this->permissions['ispolnitel']                = (in_array(96, $userGroups));
        $this->permissions['controler']                 = (in_array(97, $userGroups));
        $this->permissions['kurator']                   = (in_array(98, $userGroups));
        $this->permissions['protocol']                  = (in_array(114, $userGroups));
        $this->permissions['full_access']               = (in_array(128, $userGroups));
        $this->permissions['ispolnitel_main']           = false;
        $this->permissions['ispolnitel_submain']        = false;
        $this->permissions['ispolnitel_employee']       = false;
        $this->permissions['ispolnitel_implementation'] = false;
        $this->permissions['ispolnitel_data']           = [];
        $this->permissions['ispolnitel_ids']            = [];

        if ($this->permissions['controler']) {
            $arUser = CUser::GetByID($curUserId)->Fetch();
            if ($arUser['UF_CONTROLER_HEAD'] != '') {
                $this->permissions['controler_head'] = $arUser['UF_CONTROLER_HEAD'];
            } else {
                $this->permissions['main_controler'] = true;
                if ($curUserId != 1151) {
                    $this->permissions['controler_head'] = 1151;
                }
            }
        }

        $this->ispolnitels = Executors::getList();
        $arMyIspolnitels = [];
        foreach ($this->ispolnitels as $arFields) {
            $bCheckPerm = true;
            if ($id > 0 && $id != $arFields['ID']) {
                $bCheckPerm = false;
            }
            if ($curUserId == $arFields['PROPERTY_RUKOVODITEL_VALUE']) {
                if ($bCheckPerm) {
                    $this->permissions['ispolnitel_main'] = true;
                    $this->permissions['ispolnitel_data'] = $arFields;
                }
                $arMyIspolnitels[ $arFields['ID'] ] = $arFields['ID'];
            } elseif (in_array($curUserId, $arFields['PROPERTY_ZAMESTITELI_VALUE'])) {
                if ($bCheckPerm) {
                    $this->permissions['ispolnitel_submain']    = true;
                    $this->permissions['ispolnitel_data']       = $arFields;
                }
                $arMyIspolnitels[ $arFields['ID'] ] = $arFields['ID'];
            } elseif (in_array($curUserId, $arFields['PROPERTY_ISPOLNITELI_VALUE'])) {
                if ($bCheckPerm) {
                    $this->permissions['ispolnitel_employee']   = true;
                    $this->permissions['ispolnitel_data']       = $arFields;
                }
                $arMyIspolnitels[ $arFields['ID'] ] = $arFields['ID'];
            }

            if (in_array($curUserId, $arFields['PROPERTY_IMPLEMENTATION_VALUE'])) {
                if ($bCheckPerm) {
                    $this->permissions['ispolnitel_implementation'] = true;
                    $this->permissions['ispolnitel_employee']       = true;
                    $this->permissions['ispolnitel_data']           = $arFields;
                }
                $arMyIspolnitels[ $arFields['ID'] ] = $arFields['ID'];
            }
        }

        $this->permissions['ispolnitel_ids'] = $arMyIspolnitels;

        /*
         * Если нужна виза исполнителя из другого ОИВ (а вдруг), то убрать у него роль исполнителя
         * Проверить, этот костыль мог быть исправлен проверкой выше ($bCheckPerm)
         */
        if (
            $this->permissions['ispolnitel'] &&
            isset($_REQUEST['detail']) &&
            !empty($this->permissions['ispolnitel_data'])
        ) {
            $executor = $obOrders->getProperty($_REQUEST['detail'], 'ISPOLNITEL')['VALUE'];
            $arSubexecutors = $obOrders->getProperty($_REQUEST['detail'], 'SUBEXECUTOR', true);
            $arDelegate = $obOrders->getProperty($_REQUEST['detail'], 'DELEGATION', true);

            $arSubexecutors = array_map(function ($e) {
                return explode(':', $e)[0];
            }, $arSubexecutors);

            if (
                $this->permissions['ispolnitel_data']['ID'] != $executor &&
                !in_array($this->permissions['ispolnitel_data']['ID'], $arSubexecutors) &&
                !in_array($this->permissions['ispolnitel_data']['ID'], $arDelegate)
            ) {
                $this->permissions['ispolnitel'] = false;
            }
        }

        $this->permissions['ispolnitel_delegated'] = [];
        $this->permissions['ispolnitel_delegate_users'] = [];
        $arUserFields = [
            'PROPERTY_ISPOLNITELI_VALUE',
            'PROPERTY_ZAMESTITELI_VALUE',
            'PROPERTY_IMPLEMENTATION_VALUE',
        ];
        foreach ($arUserFields as $fName) {
            foreach ($this->permissions['ispolnitel_data'][ $fName ] as $sValue) {
                $this->permissions['ispolnitel_delegate_users'][ $sValue ] = $this->getUserFullName($sValue);

                if (
                    $this->permissions['ispolnitel_main'] ||
                    $this->permissions['ispolnitel_submain'] ||
                    $this->permissions['ispolnitel_implementation']
                ) {
                    $this->permissions['ispolnitel_delegated'][ $sValue ] = $this->getUserFullName($sValue);
                }
            }
        }

        if (
            $this->permissions['ispolnitel_employee'] &&
            !empty($this->permissions['ispolnitel_data'])
        ) {
            $arFindUsers = [
                $curUserId,
                $this->permissions['ispolnitel_data']['PROPERTY_RUKOVODITEL_VALUE']
            ];
            foreach ($arFindUsers as $findUser) {
                $arUserData = CUser::GetByID($findUser)->Fetch();
                $arSecIds = CIntranetUtils::GetDeparmentsTree($arUserData['UF_DEPARTMENT'][0], true);
                $arSecIds[] = $arUserData['UF_DEPARTMENT'][0];
                $arSecIds = $this->unsetPodvedDeps($arSecIds);
                $sortUserBy = 'ID';
                $sortUserOrder = 'asc';
                $res = CUser::GetList(
                    $sortUserBy,
                    $sortUserOrder,
                    [
                        'ACTIVE' => 'Y',
                        'UF_DEPARTMENT' => $arSecIds
                    ],
                    [
                        'FIELDS' => [
                            'ID',
                        ]
                    ]
                );
                while ($row = $res->Fetch()) {
                    $this->permissions['ispolnitel_delegated'][ $row['ID'] ] = $this->getUserFullName($row['ID']);
                }
            }
        }

        if ($this->permissions['ispolnitel_main']) {
            $this->permissions['ispolnitel_delegated'][ $this->permissions['ispolnitel_data']['PROPERTY_RUKOVODITEL_VALUE'] ] = $this->getUserFullName($this->permissions['ispolnitel_data']['PROPERTY_RUKOVODITEL_VALUE']);
            $arUserData = CUser::GetByID($USER->GetID())->Fetch();
            $arSecIds = CIntranetUtils::GetDeparmentsTree($arUserData['UF_DEPARTMENT'][0], true);
            $arSecIds[] = $arUserData['UF_DEPARTMENT'][0];
            $arSecIds = $this->unsetPodvedDeps($arSecIds);
            $sortUserBy = 'ID';
            $sortUserOrder = 'asc';
            $res = CUser::GetList(
                $sortUserBy,
                $sortUserOrder,
                [
                    'ACTIVE' => 'Y',
                    'UF_DEPARTMENT' => $arSecIds
                ],
                [
                    'FIELDS' => [
                        'ID',
                    ]
                ]
            );
            while ($row = $res->Fetch()) {
                $this->permissions['ispolnitel_delegated'][ $row['ID'] ] = $this->getUserFullName($row['ID']);
            }
        }

        asort($this->permissions['ispolnitel_delegated']);
    }

    /**
     * Собрать дополнительные поля поручений.
     *
     * @return void
     */
    public function initFields()
    {
        $arParams['ENUM'] = $this->getEnums(Settings::$iblockId['ORDERS']);
        $arParams['COMMENT_ENUM'] = $this->getEnums(Settings::$iblockId['ORDERS_COMMENT']);

        $helper = new HlblockHelper();

        $this->hlblock = [
            'TypeData'      => $helper->getHlblockId('Tipporucheniya'),
            'Resolution'    => $helper->getHlblockId('ControlOrdersResolution'),
        ];

        $hlblock           = HLTable::getById($this->hlblock['TypeData'])->fetch();
        $entity            = HLTable::compileEntity($hlblock);
        $entity_data_class = $entity->getDataClass();

        $rsData   = $entity_data_class::getList([
            'order'  => ['UF_SORT' => 'ASC'],
        ]);
        while ($arRes = $rsData->fetch()) {
            $this->TypeData[ $arRes['UF_XML_ID'] ] = $arRes;
        }

        $arFilter = [
            'IBLOCK_ID' => Settings::$iblockId['ORDERS_THEME'],
            'ACTIVE'    => 'Y',
        ];
        $rsSect = CIBlockSection::GetList(['SORT' => 'asc', 'TIMESTAMP_X' => 'desc'], $arFilter);
        while ($arSect = $rsSect->GetNext()) {
            $arSect['THEMES'] = [];
            $this->Classificator[ $arSect['ID'] ] = $arSect;
        }

        $arSelect = [
            'ID',
            'NAME',
            'DATE_ACTIVE_FROM',
            'IBLOCK_SECTION_ID',
        ];
        $arFilter = [
            'IBLOCK_ID'     => Settings::$iblockId['ORDERS_THEME'],
            'ACTIVE_DATE'   => 'Y',
            'ACTIVE'        => 'Y'
        ];
        $res = CIBlockElement::GetList(['SORT' => 'asc', 'TIMESTAMP_X' => 'desc'], $arFilter, false, false, $arSelect);
        while ($arFields = $res->GetNext()) {
            if (!isset($this->Classificator[ $arFields['IBLOCK_SECTION_ID'] ])) {
                continue;
            }
            $this->Classificator[ $arFields['IBLOCK_SECTION_ID'] ]['THEMES'][ $arFields['ID'] ] = $arFields;
        }

        foreach ($arParams['ENUM']['CATEGORY'] as $arEnumData) {
            $this->Category[ $arEnumData['ID'] ] = $arEnumData;
        }

        foreach ($arParams['ENUM']['DOPSTATUS'] as $arEnumData) {
            $this->arDopStatuses[ $arEnumData['XML_ID'] ] = $arEnumData;
        }

        foreach ($arParams['ENUM']['CONTROLER_STATUS'] as $arEnumData) {
            $this->arControlerStatuses[ $arEnumData['XML_ID'] ] = $arEnumData;
        }

        foreach ($arParams['COMMENT_ENUM']['RESULT_VOTE'] as $arEnumData) {
            $this->arVoteResult[ $arEnumData['XML_ID'] ] = $arEnumData;
        }

        $arHLFields = $GLOBALS['USER_FIELD_MANAGER']->GetUserFields('HLBLOCK_' . $this->hlblock['Resolution']);
        $res = CUserFieldEnum::GetList([], ['USER_FIELD_ID' => $arHLFields['UF_APPROVE']['ID']]);
        while ($arEnum = $res->GetNext()) {
            $this->arResolutionStatus[ $arEnum['XML_ID'] ] = $arEnum;
        }
    }

    /**
     * Получить массив доступов для поручения.
     *
     * @param integer $id ID поручения.
     *
     * @return array
     */
    public function getPermisions(int $id = 0)
    {
        $this->initPermissions($id);

        return $this->permissions;
    }

    /**
     * Prepare params
     *
     * @param array|object $arParams Параметры компонента.
     *
     * @return array|object
     */
    public function onPrepareComponentParams($arParams)
    {
        $arParams['ENUM'] = $this->getEnums(Settings::$iblockId['ORDERS']);
        $arParams['COMMENT_ENUM'] = $this->getEnums(Settings::$iblockId['ORDERS_COMMENT']);

        return $arParams;
    }

    /**
     * Get results
     *
     * @return array
     *
     * @todo Уменьшить запросы, брать енамки из getEnums().
     * @see  getEnums()
     */
    protected function getResult()
    {
        global $USER;
        $curUserId = $USER->GetID();
        $obOrders  = new Orders();

        $arResult['PERMISSIONS']        = $this->permissions;
        $arResult['ISPOLNITELS']        = $this->ispolnitels;
        $arResult['TYPES_DATA']         = $this->TypeData;
        $arResult['NOT_STATS']          = $this->arParams['ENUM']['NOT_STATS'];
        $arResult['CLASSIFICATOR']      = $this->Classificator;
        $arResult['CATEGORIES']         = $this->Category;
        $arResult['ISPOLNITELTYPES']    = Executors::getTypesList();
        $arResult['CONTROLER_STATUS']   = $this->arControlerStatuses;
        $arResult['RESULT_VOTE']        = $this->arVoteResult;
        $arResult['DOPSTATUS']          = $this->arDopStatuses;

        if ($_REQUEST['stats']) {
            $arResult['STATS_DATA'] = $this->getStatsData();
        }

        /*
         * Проверить, используется ли этот функционал?
         */
        if ($_REQUEST['action_button_control-orders-list_tablet'] == 'edit') {
            switch ($_REQUEST['view']) {
                case 'list':
                    $arIdsData = [];
                    foreach ($_REQUEST['FIELDS'] as $key => $value) {
                        $arIdsData[] = $key;
                    }
                    $res = CIBlockElement::GetList(
                        [
                            'DATE_CREATE' => 'DESC'
                        ],
                        [
                            'IBLOCK_ID' => Settings::$iblockId['ORDERS'],
                            'ID'        => $arIdsData,
                            'ACTIVE'    => 'Y',
                        ],
                        false,
                        false,
                        $this->arOrderFields
                    );
                    while ($arFields = $res->GetNext()) {
                        if (
                            $arResult['PERMISSIONS']['kurator'] &&
                            in_array($arFields['PROPERTY_POST_VALUE'], [1112, $curUserId])
                        ) {
                            $el = new CIBlockElement();

                            $arProp           = [];
                            $arProp['PORUCH'] = $arFields['ID'];
                            $arProp['USER']   = $curUserId;
                            $arProp['TYPE']   = 1133;

                            $arLoadProductArray = [
                                'MODIFIED_BY'       => $curUserId,
                                'IBLOCK_SECTION_ID' => false,
                                'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
                                'PROPERTY_VALUES'   => $arProp,
                                'NAME'              => $curUserId . '-' . $arFields['ID'] . '-' . date('d-m-Y_h:i:s'),
                                'ACTIVE'            => 'Y',
                                'PREVIEW_TEXT'      => $_REQUEST['FIELDS'][ $arFields['ID'] ]['ISPOLN_DATA'],
                                'DETAIL_TEXT'       => $_REQUEST['FIELDS'][ $arFields['ID'] ]['ISPOLN_DATA'],
                            ];

                            if ($PRODUCT_ID = $el->Add($arLoadProductArray)) {
                                $this->log(
                                    $arFields['ID'],
                                    'Снято с контроля',
                                    [
                                        'METHOD'    => __METHOD__,
                                        'REQUEST'   => $_REQUEST,
                                    ]
                                );
                                $this->addToLog('Снято с контроля');
                                CIBlockElement::SetPropertyValuesEx(
                                    $arFields['ID'],
                                    false,
                                    [
                                        'ACTION'            => Settings::$arActions['ARCHIVE'],
                                        'STATUS'            => 1142,
                                        'WORK_INTER_STATUS' => false,
                                    ]
                                );
                                self::fixSrokNarush($arFields['ID']);
                            } else {
                                echo 'Error: ' . $el->LAST_ERROR;
                            }
                        }
                    }
                    break;
                case 'add':
                    $arIdsData = [];
                    foreach ($_REQUEST['FIELDS'] as $key => $value) {
                        $arIdsData[] = $key;
                    }

                    $res = CIBlockElement::GetList(
                        [
                            'DATE_CREATE' => 'DESC'
                        ],
                        [
                            'IBLOCK_ID' => Settings::$iblockId['ORDERS'],
                            'ID'        => $arIdsData,
                            'ACTIVE'    => 'Y',
                        ],
                        false,
                        false,
                        $this->arOrderFields
                    );
                    while ($arFields = $res->GetNext()) {
                        if (
                            $arResult['PERMISSIONS']['kurator'] &&
                            in_array($arFields['PROPERTY_POST_VALUE'], [1112, $curUserId])
                        ) {
                            $el = new CIBlockElement();

                            $arProp           = [];
                            $arProp['PORUCH'] = $arFields['ID'];
                            $arProp['USER']   = $curUserId;
                            $arProp['TYPE']   = 1133;

                            $arLoadProductArray = [
                                'MODIFIED_BY'       => $curUserId,
                                'IBLOCK_SECTION_ID' => false,
                                'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
                                'PROPERTY_VALUES'   => $arProp,
                                'NAME'              => $curUserId . '-' . $arFields['ID'] . '-' . date('d-m-Y_h:i:s'),
                                'ACTIVE'            => 'Y',
                                'PREVIEW_TEXT'      => $_REQUEST['FIELDS'][ $arFields['ID'] ]['ISPOLN_DATA'],
                                'DETAIL_TEXT'       => $_REQUEST['FIELDS'][ $arFields['ID'] ]['ISPOLN_DATA'],
                            ];

                            if ($PRODUCT_ID = $el->Add($arLoadProductArray)) {
                                $this->log(
                                    $arFields['ID'],
                                    'Добавлен отчет куратора',
                                    [
                                        'METHOD'    => __METHOD__,
                                        'FIELDS'    => $arLoadProductArray,
                                        'REQUEST'   => $_REQUEST,
                                    ]
                                );
                            } else {
                                echo 'Error: ' . $el->LAST_ERROR;
                            }
                        }
                    }
                    break;
                default:
                    break;
            }
        }

        if ($_REQUEST['edit'] != '' && (int)$_REQUEST['edit'] > 0) {
            $arResult['EDIT_DATA'] = $obOrders->getById((int)$_REQUEST['edit']);

            $arResult['ACCES'] = 'N';
            if (
                $arResult['PERMISSIONS']['kurator'] &&
                in_array($arResult['EDIT_DATA']['PROPERTY_POST_VALUE'], [1112, $curUserId])
            ) {
                $arResult['ACCES'] = 'Y';
            }

            if ($arResult['PERMISSIONS']['controler']) {
                if ($arResult['EDIT_DATA']['PROPERTY_CONTROLER_VALUE'] == $curUserId) {
                    $arResult['ACCES'] = 'Y';
                } elseif ($arResult['EDIT_DATA']['PROPERTY_CONTROLER_VALUE'] == $arResult['PERMISSIONS']['controler_head']) {
                    $arResult['ACCES'] = 'Y';
                }
            }
        } elseif ($_REQUEST['edit'] == 0 && $_REQUEST['action'] == 'add_position') {
            if (isset($_REQUEST['add']) && !empty($_REQUEST['add'])) {
                $res = CIBlockElement::GetList(
                    [
                        'DATE_CREATE' => 'DESC'
                    ],
                    [
                        'IBLOCK_ID' => Settings::$iblockId['ORDERS'],
                        'ID'        => $_REQUEST['add'],
                        'ACTIVE'    => 'Y',
                    ],
                    false,
                    false,
                    $this->arOrderFields
                );
                while ($arFields = $res->GetNext()) {
                    $arResult['POSITION_DATA'][] = $arFields;
                }
            }
        } elseif ($_REQUEST['detail'] != '') {
            $arResult['DETAIL_DATA'] = $this->DetailData;
            if (in_array($arResult['DETAIL_DATA']['ELEMENT']['PROPERTY_ISPOLNITEL_VALUE'], $arResult['PERMISSIONS']['ispolnitel_ids'])) {
                $this->initPermissions($arResult['DETAIL_DATA']['ELEMENT']['PROPERTY_ISPOLNITEL_VALUE']);
                $arResult['PERMISSIONS'] = $this->permissions;
            } elseif (!empty($arResult['DETAIL_DATA']['ELEMENT']['PROPERTY_DELEGATION_VALUE'])) {
                if (in_array($arResult['DETAIL_DATA']['ELEMENT']['PROPERTY_DELEGATION_VALUE'][0], $arResult['PERMISSIONS']['ispolnitel_ids'])) {
                    $this->initPermissions($arResult['DETAIL_DATA']['ELEMENT']['PROPERTY_DELEGATION_VALUE'][0]);
                    $arResult['PERMISSIONS'] = $this->permissions;
                }
            } else {
                if (isset($arResult['DETAIL_DATA']['ELEMENT']['PROPERTY_SUBEXECUTOR_IDS'])) {
                    foreach ($arResult['DETAIL_DATA']['ELEMENT']['PROPERTY_SUBEXECUTOR_IDS'] as $subEx) {
                        if (in_array($subEx, $arResult['PERMISSIONS']['ispolnitel_ids'])) {
                            $this->initPermissions($subEx);
                            $arResult['PERMISSIONS'] = $this->permissions;
                        }
                    }
                } else {
                    foreach ($arResult['DETAIL_DATA']['ELEMENT']['PROPERTY_SUBEXECUTOR_VALUE'] as $subEx) {
                        $subEx = explode(':', $subEx)[0];
                        if (in_array($subEx, $arResult['PERMISSIONS']['ispolnitel_ids'])) {
                            $this->initPermissions($subEx);
                            $arResult['PERMISSIONS'] = $this->permissions;
                        }
                    }
                }
            }
            $arCurExecutor = $arResult['PERMISSIONS']['ispolnitel_data'];
            $arResult['ACCES'] = 'N';

            if ($arResult['PERMISSIONS']['ispolnitel']) {
                if (
                    $arResult['DETAIL_DATA']['ELEMENT']['PROPERTY_ISPOLNITEL_VALUE'] == $arCurExecutor['ID'] &&
                    $arResult['DETAIL_DATA']['ELEMENT']['PROPERTY_ACTION_ENUM_ID'] >= Settings::$arActions['NEW']
                ) {
                    $arResult['ACCES'] = 'Y';
                }
                if (
                    in_array($arCurExecutor['ID'], $arResult['DETAIL_DATA']['ELEMENT']['PROPERTY_SUBEXECUTOR_IDS'])
                ) {
                    $arResult['ACCES'] = 'Y';
                }
                if (
                    in_array($arCurExecutor['ID'], $arResult['DETAIL_DATA']['ELEMENT']['PROPERTY_SUBEXECUTOR_VALUE'])
                ) {
                    $arResult['ACCES'] = 'Y';
                }
                if (
                    in_array($arCurExecutor['ID'], $arResult['DETAIL_DATA']['ELEMENT']['PROPERTY_DELEGATION_VALUE'])
                ) {
                    $arResult['ACCES'] = 'Y';
                }
            }

            $arResult['SVYAZI_EXECUTOR'] = false;
            if (
                $arResult['ACCES'] == 'N' &&
                !empty($arResult['DETAIL_DATA']['ELEMENT']['PROPERTY_PORUCH_VALUE'])
            ) {
                $arAccomplices = [];
                $resAccomplice = CIBlockElement::GetList(
                    false,
                    [
                        'IBLOCK_ID' => Settings::$iblockId['ORDERS'],
                        'ACTIVE'    => 'Y',
                        '!ID'       => $_REQUEST['detail'],
                        'ID'        => $arResult['DETAIL_DATA']['ELEMENT']['PROPERTY_PORUCH_VALUE'],
                    ],
                    false,
                    false,
                    [
                        'ID',
                        'IBLOCK_ID',
                        'PROPERTY_DELEGATE_USER',
                    ]
                );
                while ($rowAccomplice = $resAccomplice->GetNext()) {
                    if (!empty($rowAccomplice['PROPERTY_DELEGATE_USER_VALUE'])) {
                        $arAccomplices[] = $rowAccomplice['PROPERTY_DELEGATE_USER_VALUE'];
                    }
                }

                if (in_array($curUserId, $arAccomplices)) {
                    $arResult['SVYAZI_EXECUTOR'] = true;
                    $arResult['ACCES'] = 'Y';
                }
            }

            if (
                $arResult['PERMISSIONS']['kurator'] &&
                in_array($arResult['DETAIL_DATA']['ELEMENT']['PROPERTY_POST_VALUE'], [1112, $curUserId])
            ) {
                $arResult['ACCES'] = 'Y';
            }

            if ($arResult['PERMISSIONS']['controler']) {
                if ($arResult['DETAIL_DATA']['ELEMENT']['PROPERTY_CONTROLER_VALUE'] == $curUserId) {
                    $arResult['ACCES'] = 'Y';
                } elseif ($arResult['DETAIL_DATA']['ELEMENT']['PROPERTY_CONTROLER_VALUE'] == $arResult['PERMISSIONS']['controler_head']) {
                    $arResult['ACCES'] = 'Y';
                }
            }

            if ($arResult['ACCES'] == 'N') {
                foreach ($arResult['DETAIL_DATA']['COMMENTS']['OTCHET_ISPOLNITEL'] as $arComment) {
                    foreach ($arComment['PROPERTY_VISA_VALUE'] as $visaRow) {
                        if (0 === mb_strpos($visaRow, $curUserId . ':')) {
                            $arResult['ACCES'] = 'Y';
                            break(2);
                        }
                        /*
                         * У ответственных за внедрение должен быть доступ
                         * для поручений, где руководитель их органа добавлен визирующим.
                         */
                        if (
                            $arResult['PERMISSIONS']['ispolnitel_implementation'] &&
                            in_array($curUserId, $arCurExecutor['PROPERTY_IMPLEMENTATION_VALUE'])
                        ) {
                            if (0 === mb_strpos($visaRow, $arCurExecutor['PROPERTY_RUKOVODITEL_VALUE'] . ':')) {
                                $arResult['ACCES'] = 'Y';
                                break(2);
                            }
                        }
                    }

                    if (
                        !empty($arResult['DETAIL_DATA']['ELEMENT']['PROPERTY_WORK_INTER_STATUS_ENUM_ID']) &&
                        $arComment['PROPERTY_CURRENT_USER_VALUE'] == $curUserId
                    ) {
                        $arResult['ACCES'] = 'Y';
                    }
                }
                $arResult['VISA_ACCOMPLIENCE'] = false;
                foreach ($arResult['DETAIL_DATA']['COMMENTS']['OTCHET_ACCOMPLIENCE'] as $arComment) {
                    foreach ($arComment['PROPERTY_VISA_VALUE'] as $visaRow) {
                        if (0 === mb_strpos($visaRow, $curUserId . ':')) {
                            $arResult['ACCES'] = 'Y';
                            $arResult['VISA_ACCOMPLIENCE'] = true;
                            break(2);
                        }
                    }
                }
            }
        }

        if ($curUserId == 570) {
            $arResult['ACCES'] = 'Y';
        }

        $filterData     = $this->getListFilter('temp_menu_filter_' . $curUserId);
        $arFilter       = $filterData;
        $arMenuFilter   = $filterData;

        $arCounters = [];
        $obCache = new CPHPCache();
        if ($obCache->InitCache(600, md5('arCounters-' . $curUserId . serialize($filterData)), '/citto/controlorders/MenuCounters/')) {
            $arCounters = $obCache->GetVars();
        } elseif ($obCache->StartDataCache()) {
            unset(
                $arMenuFilter['PROPERTY_ACTION'],
                $arMenuFilter['PROPERTY_CONTROLER_RESH'],
                $arMenuFilter['PROPERTY_CONTROLER_STATUS']
            );
            if (!isset($arMenuFilter['!PROPERTY_ACTION'])) {
                $arMenuFilter['!PROPERTY_ACTION'] = Settings::$arActions['DRAFT'];
            }
            if (!is_array($arMenuFilter['!PROPERTY_ACTION'])) {
                $arMenuFilter['!PROPERTY_ACTION'] = [$arMenuFilter['!PROPERTY_ACTION']];
            }
            $arMenuFilter['!PROPERTY_ACTION'][] = Settings::$arActions['ARCHIVE'];
            $arCounters['FULL'] = CIBlockElement::GetList([], $arMenuFilter, [], false, []);

            foreach (Settings::$arActions as $code => $actionId) {
                $arMenuFilter = array_merge(
                    $filterData,
                    [
                        'PROPERTY_ACTION' => $actionId
                    ]
                );
                if ($code == 'NEW' && !$this->permissions['full_access'] && !$this->permissions['protocol']) {
                    foreach ($arMenuFilter[0][-1]['PROPERTY_DELEGATION'] as $key => $value) {
                        if (in_array($this->ispolnitels[ $value ]['PROPERTY_TYPE_CODE'], ['zampred', 'gubernator'])) {
                            unset($arMenuFilter[0][-1]['PROPERTY_DELEGATION'][ $key ]);
                        }
                    }
                }
                unset(
                    $arMenuFilter['!PROPERTY_ACTION'],
                    $arMenuFilter['PROPERTY_CONTROLER_RESH'],
                    $arMenuFilter['PROPERTY_CONTROLER_STATUS']
                );
                $arCounters[ $actionId ] = CIBlockElement::GetList([], $arMenuFilter, [], false, []);
            }

            $obCache->EndDataCache($arCounters);
        }

        if ($_REQUEST['action_filter'] == Settings::$arActions['NEW']) {
            $arResCounters = [
                'FULL'      => 0,
                'PROJECT'   => 0,
                'REJECT'    => 0,
            ];
            $arFilter['PROPERTY_ACTION'] = Settings::$arActions['NEW'];
            $arResCounters['FULL'] = CIBlockElement::GetList([], $arFilter, [], false, []);

            $arMyFilter = $this->getListFilter('temp_menu_filter_' . $curUserId, 'new_project');
            $arMyFilter['PROPERTY_ACTION'] = Settings::$arActions['NEW'];
            $arResCounters['PROJECT'] = CIBlockElement::GetList([], $arMyFilter, [], false, []);

            $arMyFilter = $this->getListFilter('temp_menu_filter_' . $curUserId, 'new_reject');
            $arMyFilter['PROPERTY_ACTION'] = Settings::$arActions['NEW'];
            $arResCounters['REJECT'] = CIBlockElement::GetList([], $arMyFilter, [], false, []);

            unset($arFilter[0]);
            $arResult['COUNTERS_NEW'] = $arResCounters;
        }

        if (
                $_REQUEST['action_filter'] == Settings::$arActions['WORK'] ||
                $_REQUEST['action_filter'] == Settings::$arActions['CURATOR_COMMENTS']
        ) {
            $arResCounters = [
                'FULL'             => 0,
                'MY'               => 0,
                'SUB'              => 0,
                'SIGN'             => 0,
                'SIGN_MY'          => 0,
                'SIGN_OTHER'       => 0,
                'VISA'             => 0,
                'DELEGATE'         => 0,
                'CURATOR_COMMENTS' => 0,
            ];
            $arFilter['PROPERTY_ACTION'] = Settings::$arActions['WORK'];
            $arResCounters['FULL'] = CIBlockElement::GetList([], $arFilter, [], false, []);

            if (
                $this->permissions['ispolnitel_main'] ||
                (
                    in_array($this->permissions['ispolnitel_data']['PROPERTY_TYPE_CODE'], ['zampred', 'gubernator']) &&
                    $this->permissions['ispolnitel_implementation']
                )
            ) {
                $arSignFilter = $this->getListFilter('temp_menu_filter_' . $curUserId, 'sign');
                $arSignFilter['PROPERTY_ACTION'] = [
                        Settings::$arActions['WORK'],
                        Settings::$arActions['CURATOR_COMMENTS'],
                ];
                $arResCounters['SIGN'] = CIBlockElement::GetList(['ID' => 'DESC'], $arSignFilter, [], false, []);
            }

            if ($this->permissions['ispolnitel_submain']) {
                $arSignFilter = $this->getListFilter('temp_menu_filter_' . $curUserId, 'sign_my');
                $arSignFilter['PROPERTY_ACTION'] = Settings::$arActions['WORK'];
                $arResCounters['SIGN_MY'] = CIBlockElement::GetList(['ID' => 'DESC'], $arSignFilter, [], false, []);
                $arSignFilter = $this->getListFilter('temp_menu_filter_' . $curUserId, 'sign_other');
                $arSignFilter['PROPERTY_ACTION'] = Settings::$arActions['WORK'];
                $arResCounters['SIGN_OTHER'] = CIBlockElement::GetList(['ID' => 'DESC'], $arSignFilter, [], false, []);
            }

            $arMyFilter = $this->getListFilter('temp_menu_filter_' . $curUserId, 'my');
            $arMyFilter['PROPERTY_ACTION'] = Settings::$arActions['WORK'];
            $arResCounters['MY'] = CIBlockElement::GetList([], $arMyFilter, [], false, []);

            $arCuratorCommentsFilter = $this->getListFilter('temp_menu_filter_' . $curUserId, 'curator_comments');
            $arCuratorCommentsFilter['PROPERTY_ACTION'] = Settings::$arActions['CURATOR_COMMENTS'];
            $arResCounters['CURATOR_COMMENTS'] = CIBlockElement::GetList([], $arCuratorCommentsFilter, [], false, []);

            $arMySubFilter = $this->getListFilter('temp_menu_filter_' . $curUserId, 'sub');
            $arMySubFilter['PROPERTY_ACTION'] = Settings::$arActions['WORK'];
            $arResCounters['SUB'] = CIBlockElement::GetList([], $arMySubFilter, [], false, []);

            $arVisaFilter = $this->getListFilter('temp_menu_filter_' . $curUserId, 'visa');
            $arVisaFilter['PROPERTY_ACTION'] = Settings::$arActions['WORK'];
            $arResCounters['VISA'] = CIBlockElement::GetList([], $arVisaFilter, [], false, []);

            $arDelegateFilter = $this->getListFilter('temp_menu_filter_' . $curUserId, 'delegate');
            $arDelegateFilter['PROPERTY_ACTION'] = Settings::$arActions['WORK'];

            if (!empty($arDelegateFilter)) {
                $arResCounters['DELEGATE'] = CIBlockElement::GetList([], $arDelegateFilter, [], false, []);
            }

            unset($arFilter[0]);
            $arResult['COUNTERS_WORK'] = $arResCounters;
        }

        if ($_REQUEST['action_filter'] == Settings::$arActions['CONTROL']) {
            $arResCounters = [];
            $arFilter['PROPERTY_ACTION'] = Settings::$arActions['CONTROL'];
            unset($arFilter['PROPERTY_CONTROLER_STATUS']);
            $arResCounters['FULL'] = CIBlockElement::GetList([], $arFilter, [], false, []);
            $arFilter['PROPERTY_CONTROLER_STATUS'] = $arResult['CONTROLER_STATUS']['on_accepting']['ID'];
            $arResCounters['on_accepting'] = CIBlockElement::GetList([], $arFilter, [], false, []);
            $arFilter['PROPERTY_CONTROLER_STATUS'] = $arResult['CONTROLER_STATUS']['on_position']['ID'];
            $arResCounters['on_position'] = CIBlockElement::GetList([], $arFilter, [], false, []);
            $arFilter['PROPERTY_CONTROLER_STATUS'] = $arResult['CONTROLER_STATUS']['on_beforing']['ID'];
            $arResCounters['on_beforing'] = CIBlockElement::GetList([], $arFilter, [], false, []);
            $arResult['COUNTERS_CONTROLER_STATUS'] = $arResCounters;
        }

        if ($_REQUEST['action_filter'] == Settings::$arActions['READY']) {
            $arResCounters = [];
            $arFilter['PROPERTY_ACTION'] = Settings::$arActions['READY'];
            unset($arFilter['PROPERTY_CONTROLER_RESH']);
            $arResCounters['FULL'] = CIBlockElement::GetList([], $arFilter, [], false, []);
            $arFilter['PROPERTY_CONTROLER_RESH'] = 1276;
            $arResCounters[1276] = CIBlockElement::GetList([], $arFilter, [], false, []);
            $arFilter['PROPERTY_CONTROLER_RESH'] = 1277;
            $arResCounters[1277] = CIBlockElement::GetList([], $arFilter, [], false, []);
            $arFilter['PROPERTY_CONTROLER_RESH'] = 1278;
            $arResCounters[1278] = CIBlockElement::GetList([], $arFilter, [], false, []);
            $arFilter['PROPERTY_CONTROLER_RESH'] = 1307;
            $arResCounters[1307] = CIBlockElement::GetList([], $arFilter, [], false, []);
            $arResult['COUNTERS_RESH'] = $arResCounters;
        }

        $arResult['MENU_ITEMS'] = $this->menu_items;

        $selectedStatus = 0;
        if (isset($_REQUEST['detail']) || isset($_REQUEST['edit'])) {
            $findElId = $_REQUEST['detail'] ?? $_REQUEST['edit'];
            if ($findElId > 0) {
                $resSelectStatus = CIBlockElement::GetList([], ['ID' => $findElId, 'ACTIVE' => 'Y'], false, false, ['ID', 'PROPERTY_ACTION']);
                if ($rowSelectStatus = $resSelectStatus->GetNext()) {
                    $selectedStatus = $rowSelectStatus['PROPERTY_ACTION_ENUM_ID'];
                }
            }
        }

        $arResult['MENU_ITEMS']['general'] = [
            'TEXT'      => 'В работе (' . (int)$arCounters['FULL'] . ')',
            'URL'       => '/control-orders/?all',
            'ID'        => 'control_orders_general',
            'IS_ACTIVE' => (
                $_REQUEST['action_filter'] == '' &&
                $_REQUEST['stats'] == '' &&
                $_REQUEST['enums'] == '' &&
                $selectedStatus == 0
            ),
            'COUNTER_ID'=> 'control_orders_FULL',
        ];

        $arMenuItems = [
            'NEW',
            'WORK',
            'CONTROL',
            'READY',
            'ARCHIVE',
        ];
        if (
            $arResult['PERMISSIONS']['controler'] ||
            $arResult['PERMISSIONS']['kurator'] ||
            $arResult['PERMISSIONS']['full_access'] ||
            $arResult['PERMISSIONS']['protocol']
        ) {
            $arMenuItems[] = 'DRAFT';
        }

        foreach ($arMenuItems as $code) {
            $arResult['MENU_ITEMS'][] = [
                'TEXT'      => $this->arParams['ENUM']['ACTION'][ Settings::$arActions[ $code ] ]['VALUE'] . ' (' . (int)$arCounters[ Settings::$arActions[ $code ] ] . ')',
                'URL'       => '/control-orders/?action_filter=' . Settings::$arActions[ $code ],
                'ID'        => 'control_orders_' . $code,
                'IS_ACTIVE' => (
                    $_REQUEST['action_filter'] == Settings::$arActions[ $code ] ||
                    $selectedStatus == Settings::$arActions[ $code ] ||
                    ($code == 'WORK' && $_REQUEST['action_filter'] == Settings::$arActions['CURATOR_COMMENTS'])
                ),
                'COUNTER_ID'=> 'control_orders_' . $code,
            ];
        }

        $arResult['MENU_ITEMS'][] = [
            'TEXT'      => 'Статистика',
            'URL'       => '/control-orders/?stats=main',
            'ID'        => 'control_orders_stats',
            'IS_ACTIVE' => ($_REQUEST['stats'] != ''),
        ];

        if (
            $arResult['PERMISSIONS']['controler'] ||
            $arResult['PERMISSIONS']['kurator'] ||
            $GLOBALS['USER']->IsAdmin()
        ) {
            $arResult['MENU_ITEMS'][] = [
                'TEXT'      => 'Списки',
                'URL'       => '/control-orders/?enums=view',
                'ID'        => 'control_orders_enums',
                'IS_ACTIVE' => ($_REQUEST['enums'] != ''),
            ];

            $arResult['MENU_ITEMS'][] = [
                'TEXT'      => 'Карта поручений',
                'URL'       => '/control-orders/?page=map',
                'ID'        => 'control_orders_map',
                'IS_ACTIVE' => ($_REQUEST['page'] == 'map'),
            ];
        }

        if ($arResult['PERMISSIONS']['full_access']) {
            $arResult['ACCES'] = 'Y';
        }

        if ($arResult['PERMISSIONS']['protocol']) {
            $arResult['ACCES'] = 'Y';
        }

        return $arResult;
    }

    /**
     * Получить имя пользователя по его ID
     *
     * @param integer $id       ID пользователя.
     * @param boolean $bTooltip Тултип.
     *
     * @return string
     */
    public function getUserFullName(
        $id = 0,
        bool $bTooltip = false
    ) {
        if ($id <= 0) {
            return '';
        }
        $userName = '';
        $obCache = new CPHPCache();
        if ($obCache->InitCache(86400, __METHOD__ . $id, '/citto/controlorders/')) {
            $userName = $obCache->GetVars();
        } elseif ($obCache->StartDataCache()) {
            $rsUser = CUser::GetByID($id);
            $arUser = $rsUser->Fetch();
            $userName = $arUser['NAME']||$arUser['LAST_NAME'] ? trim($arUser['LAST_NAME'] . ' ' . $arUser['NAME']) : $arUser['LOGIN'];
            $userName = trim(str_replace('  ', ' ', $userName));
            $obCache->EndDataCache($userName);
        }

        if ($bTooltip) {
            $userName = '<span class="username" bx-tooltip-user-id="' . $id . '">' . $userName . '</span>';
        }

        return $userName;
    }

    /**
     * Редактирование поручения
     *
     * @return void
     */
    public function getEditData()
    {
        global $USER;
        if (isset($_REQUEST['PROP']['OBJECT'])) {
            $_REQUEST['PROP']['OBJECT'] = array_unique($_REQUEST['PROP']['OBJECT']);
        }

        $redirecturl = '/control-orders/';

        if (isset($_REQUEST['back_url'])) {
            $redirecturl = str_replace('|', '&', rawurldecode($_REQUEST['back_url']));
        }

        $obOrders = new Orders();

        switch ($_REQUEST['action']) {
            case 'add':
                $arTags = $_REQUEST['TAGS'];
                $arTags = array_unique($arTags);
                $arTags = array_filter($arTags);
                $arTags = array_map('trim', $arTags);

                if (isset($_REQUEST['DISABLE_DATE_ISPOLN']) && $_REQUEST['DISABLE_DATE_ISPOLN'] == 'Y') {
                    $_REQUEST['PROP']['DATE_ISPOLN'] = $this->disableSrokDate;
                }

                if (isset($_REQUEST['DISABLE_SUBEXECUTOR_DATE']) && $_REQUEST['DISABLE_SUBEXECUTOR_DATE'] == 'Y') {
                    $_REQUEST['PROP']['SUBEXECUTOR_DATE'] = $this->disableSrokDate;
                }

                if (!empty($_REQUEST['PROP']['SUBEXECUTOR'])) {
                    foreach ($_REQUEST['PROP']['SUBEXECUTOR'] as $keySE => $valueSE) {
                        $SUBEXECUTOR = explode('_', $valueSE);
                        if ($SUBEXECUTOR[0] == 'group') {
                            $arGroupExecutors = (new GroupExecutors())->getExecutorsList($SUBEXECUTOR[1]);
                            unset($_REQUEST['PROP']['SUBEXECUTOR'][ $keySE ]);
                            foreach ($arGroupExecutors as $groupId) {
                                $_REQUEST['PROP']['SUBEXECUTOR'][] = $groupId['ID'];
                            }
                            $_REQUEST['PROP']['SUBEXECUTOR'] = array_unique($_REQUEST['PROP']['SUBEXECUTOR']);
                            unset($arGroupExecutors);
                        }
                    }

                    $arReqVisa = [];
                    foreach ($_REQUEST['PROP']['SUBEXECUTOR'] as $keySE => $valueSE) {
                        $_REQUEST['PROP']['SUBEXECUTOR'][ $keySE ] = [
                            'VALUE'         => $valueSE . ':' . 0,
                            'DESCRIPTION'   => 0,
                        ];

                        if ($_REQUEST['PROP']['REQUIRED_VISA'][ $keySE ] == 'Y') {
                            $arReqVisa[] = 'I' . $valueSE;
                        }
                    }
                    $_REQUEST['PROP']['REQUIRED_VISA'] = $arReqVisa;
                } else {
                    unset($_REQUEST['PROP']['REQUIRED_VISA']);
                    unset($_REQUEST['PROP']['SUBEXECUTOR']);
                }

                if (is_array($_REQUEST['PROP']['ISPOLNITEL'])) {
                    $arProp              = $_REQUEST['PROP'];
                    $arProp['POST']      = $_REQUEST['POST'][0];
                    $arProp['CONTROLER'] = $_REQUEST['CONTROLER'][0];
                    $arProp['STATUS']    = 1141;
                    $arProp['DOCS']      = $_REQUEST['DOCS'];
                    $arPoruchs = [];

                    foreach ($_REQUEST['PROP']['ISPOLNITEL'] as $sKey => $sValue) {
                        $el                    = new CIBlockElement();
                        $arProp['ISPOLNITEL']  = $sValue;
                        $arProp['DATE_ISPOLN'] = $_REQUEST['PROP']['DATE_ISPOLN'][ $sKey ];

                        if (isset($_REQUEST['DISABLE_DATE_ISPOLN']) && $_REQUEST['DISABLE_DATE_ISPOLN'][ $sKey ] == 'Y') {
                            $arProp['DATE_ISPOLN'] = $this->disableSrokDate;
                        }

                        $arLoadProductArray  = [
                            'MODIFIED_BY'       => $USER->GetID(),
                            'IBLOCK_SECTION_ID' => false,
                            'IBLOCK_ID'         => Settings::$iblockId['ORDERS'],
                            'PROPERTY_VALUES'   => $arProp,
                            'NAME'              => $_REQUEST['NAME'],
                            'ACTIVE'            => 'Y',
                            'DETAIL_TEXT'       => $_REQUEST['DETAIL_TEXT'],
                            'TAGS'              => implode(',', $arTags),
                        ];

                        if ($PRODUCT_ID = $el->Add($arLoadProductArray)) {
                            $arPoruchs[] = $PRODUCT_ID;
                            $this->log(
                                $PRODUCT_ID,
                                'Создано поручение',
                                [
                                    'METHOD'    => __METHOD__,
                                    'FIELDS'    => $arLoadProductArray,
                                    'REQUEST'   => $_REQUEST,
                                ]
                            );
                        } else {
                            $errors[] = $el->LAST_ERROR;
                        }
                    }

                    foreach ($arPoruchs as $sKey => $sValue) {
                        CIBlockElement::SetPropertyValuesEx(
                            $sValue,
                            Settings::$iblockId['ORDERS'],
                            [
                                'PORUCH' => array_diff($arPoruchs, [$sValue]),
                            ]
                        );
                    }
                    LocalRedirect($redirecturl);
                } else {
                    $ISPOLNITEL = explode('_', $_REQUEST['PROP']['ISPOLNITEL']);
                    if (count($ISPOLNITEL) > 1) {
                        if ($ISPOLNITEL[0] == 'all') {
                            $errors = [];
                            foreach ($this->ispolnitels as $sNum => $sValue) {
                                if ($sValue['PROPERTY_TYPE_ENUM_ID'] == $ISPOLNITEL[1]) {
                                    $el   = new CIBlockElement();
                                    $arProp = $_REQUEST['PROP'];
                                    $arProp['POST']       = $_REQUEST['POST'][0];
                                    $arProp['CONTROLER']  = $_REQUEST['CONTROLER'][0];
                                    $arProp['STATUS']     = 1141;
                                    $arProp['DOCS']       = $_REQUEST['DOCS'];
                                    $arProp['ISPOLNITEL'] = $sValue['ID'];

                                    $arLoadProductArray = [
                                        'MODIFIED_BY'       => $USER->GetID(),
                                        'IBLOCK_SECTION_ID' => false,
                                        'IBLOCK_ID'         => Settings::$iblockId['ORDERS'],
                                        'PROPERTY_VALUES'   => $arProp,
                                        'NAME'              => $_REQUEST['NAME'],
                                        'ACTIVE'            => 'Y',
                                        'DETAIL_TEXT'       => $_REQUEST['DETAIL_TEXT'],
                                        'TAGS'              => implode(',', $arTags),
                                    ];

                                    if ($PRODUCT_ID = $el->Add($arLoadProductArray)) {
                                        $this->log(
                                            $PRODUCT_ID,
                                            'Создано поручение',
                                            [
                                                'METHOD'    => __METHOD__,
                                                'FIELDS'    => $arLoadProductArray,
                                                'REQUEST'   => $_REQUEST,
                                            ]
                                        );
                                    } else {
                                        $error        = $arLoadProductArray;
                                        $error['STR'] = 'Error: ' . $el->LAST_ERROR;
                                        $errors[]     = $error;
                                    }
                                }
                            }

                            if (count($errors) > 0) {
                                pre($errors);
                            } else {
                                LocalRedirect($redirecturl);
                            }
                        } elseif ($ISPOLNITEL[0] == 'group') {
                            $arGroupExecutors = (new GroupExecutors())->getExecutorsList($ISPOLNITEL[1]);
                            $errors = [];
                            $arPoruchs = [];
                            foreach ($arGroupExecutors as $exValue) {
                                $el = new CIBlockElement();
                                $arProp = $_REQUEST['PROP'];
                                $arProp['POST']       = $_REQUEST['POST'][0];
                                $arProp['CONTROLER']  = $_REQUEST['CONTROLER'][0];
                                $arProp['STATUS']     = 1141;
                                $arProp['DOCS']       = $_REQUEST['DOCS'];
                                $arProp['ISPOLNITEL'] = $exValue['ID'];

                                $arLoadProductArray = [
                                    'MODIFIED_BY'       => $USER->GetID(),
                                    'IBLOCK_SECTION_ID' => false,
                                    'IBLOCK_ID'         => Settings::$iblockId['ORDERS'],
                                    'PROPERTY_VALUES'   => $arProp,
                                    'NAME'              => $_REQUEST['NAME'],
                                    'ACTIVE'            => 'Y',
                                    'DETAIL_TEXT'       => $_REQUEST['DETAIL_TEXT'],
                                    'TAGS'              => implode(',', $arTags),
                                ];

                                if ($PRODUCT_ID = $el->Add($arLoadProductArray)) {
                                    $arPoruchs[] = $PRODUCT_ID;
                                    $this->log(
                                        $PRODUCT_ID,
                                        'Создано поручение',
                                        [
                                            'METHOD'    => __METHOD__,
                                            'FIELDS'    => $arLoadProductArray,
                                            'REQUEST'   => $_REQUEST,
                                        ]
                                    );
                                } else {
                                    $error        = $arLoadProductArray;
                                    $error['STR'] = 'Error: ' . $el->LAST_ERROR;
                                    $errors[]     = $error;
                                }
                            }

                            if (count($errors) > 0) {
                                pre($errors);
                            } else {
                                foreach ($arPoruchs as $sKey => $sValue) {
                                    CIBlockElement::SetPropertyValuesEx(
                                        $sValue,
                                        Settings::$iblockId['ORDERS'],
                                        [
                                            'PORUCH' => array_diff($arPoruchs, [$sValue]),
                                        ]
                                    );
                                }
                                LocalRedirect($redirecturl);
                            }
                        } else {
                            ShowError('Неверный тип');
                        }
                    } else {
                        $el                  = new CIBlockElement();
                        $arProp              = $_REQUEST['PROP'];
                        $arProp['POST']      = $_REQUEST['POST'][0];
                        $arProp['CONTROLER'] = $_REQUEST['CONTROLER'][0];
                        $arProp['STATUS']    = 1141;
                        $arProp['DOCS']      = $_REQUEST['DOCS'];

                        $arLoadProductArray = [
                            'MODIFIED_BY'       => $USER->GetID(),
                            'IBLOCK_SECTION_ID' => false,
                            'IBLOCK_ID'         => Settings::$iblockId['ORDERS'],
                            'PROPERTY_VALUES'   => $arProp,
                            'NAME'              => $_REQUEST['NAME'],
                            'ACTIVE'            => 'Y',
                            'DETAIL_TEXT'       => $_REQUEST['DETAIL_TEXT'],
                            'TAGS'              => implode(',', $arTags),
                        ];

                        if ($PRODUCT_ID = $el->Add($arLoadProductArray)) {
                            $this->log(
                                $PRODUCT_ID,
                                'Создано поручение',
                                [
                                    'METHOD'    => __METHOD__,
                                    'FIELDS'    => $arLoadProductArray,
                                    'REQUEST'   => $_REQUEST,
                                ]
                            );
                            LocalRedirect('/control-orders/?detail='.$PRODUCT_ID);
                        } else {
                            echo 'Error: ' . $el->LAST_ERROR;
                        }
                    }
                }
                break;
            case 'add_position':
                if ($_REQUEST['input'] == 'add') {
                    $arProp                     = $_REQUEST['PROP'];
                    $arProp['POST']             = $_REQUEST['POST'][0];
                    $arProp['CONTROLER']        = $_REQUEST['CONTROLER'][0];
                    $arProp['STATUS']           = 1141;
                    $arProp['ACTION']           = Settings::$arActions['READY'];
                    $arProp['DOPSTATUS']        = $this->arDopStatuses['to_position']['ID'];
                    $arProp['CONTROLER_STATUS'] = $this->arControlerStatuses['on_position']['ID'];
                    $arProp['CONTROLER_RESH']   = 1277;

                    $el = new CIBlockElement();
                    $arLoadProductArray = [
                        'MODIFIED_BY'       => $USER->GetID(),
                        'IBLOCK_SECTION_ID' => false,
                        'IBLOCK_ID'         => Settings::$iblockId['ORDERS'],
                        'PROPERTY_VALUES'   => $arProp,
                        'NAME'              => $_REQUEST['NAME'],
                        'ACTIVE'            => 'Y',
                        'DETAIL_TEXT'       => $_REQUEST['DETAIL_TEXT'],
                    ];
                    if ($POSITION_ID = $el->Add($arLoadProductArray)) {
                        foreach ($_REQUEST['add'] as $value) {
                            $arPosition = $obOrders->getProperty($value, 'POSITION_FROM', true);
                            $arPosition[] = $POSITION_ID;
                            CIBlockElement::SetPropertyValuesEx(
                                $value,
                                false,
                                [
                                    'POST_RESH'         => 1203,
                                    'POSITION_FROM'     => $arPosition,
                                    'DOPSTATUS'         => $this->arDopStatuses['to_position']['ID'],
                                    'CONTROLER_STATUS'  => $this->arControlerStatuses['on_position']['ID'],
                                    'WORK_INTER_STATUS' => false,
                                ]
                            );
                            $this->log(
                                $value,
                                'Запрос на позицию',
                                [
                                    'METHOD'    => __METHOD__,
                                    'REQUEST'   => $_REQUEST,
                                ]
                            );
                            $this->addToLog(
                                'Запрос на позицию',
                                strip_tags($_REQUEST['DETAIL_TEXT']),
                                $value
                            );
                        }
                    }
                    LocalRedirect($redirecturl);
                    die;
                }
                break;
            case 'update':
                if (isset($_REQUEST['DISABLE_DATE_ISPOLN']) && $_REQUEST['DISABLE_DATE_ISPOLN'] == 'Y') {
                    $_REQUEST['PROP']['DATE_ISPOLN'] = $this->disableSrokDate;
                }
                if (isset($_REQUEST['DISABLE_SUBEXECUTOR_DATE']) && $_REQUEST['DISABLE_SUBEXECUTOR_DATE'] == 'Y') {
                    $_REQUEST['PROP']['SUBEXECUTOR_DATE'] = $this->disableSrokDate;
                }

                if (!empty($_REQUEST['PROP']['SUBEXECUTOR'])) {
                    $arSE = [];
                    $resSE = CIBlockElement::GetProperty(
                        Settings::$iblockId['ORDERS'],
                        $_REQUEST['edit'],
                        'sort',
                        'asc',
                        [
                            'CODE' => 'SUBEXECUTOR'
                        ]
                    );
                    while ($rowSE = $resSE->GetNext()) {
                        $depId = $rowSE['VALUE'];
                        $userId = $rowSE['~DESCRIPTION'];
                        if (false !== mb_strpos($rowSE['VALUE'], ':')) {
                            $val = explode(':', $rowSE['VALUE']);
                            $depId = $val[0];
                            $userId = $val[1];
                        }
                        $arSE[ $depId ] = [
                            'VALUE'         => $depId,
                            'DESCRIPTION'   => $userId,
                        ];
                    }

                    $arReqVisa = $obOrders->getProperty($_REQUEST['edit'], 'REQUIRED_VISA', true);
                    foreach ($arReqVisa as $keyRV => $valueRV) {
                        if (0 === mb_strpos($valueRV, 'I')) {
                            unset($arReqVisa[ $keyRV ]);
                        }
                    }

                    foreach ($_REQUEST['PROP']['SUBEXECUTOR'] as $keySE => $valueSE) {
                        $_REQUEST['PROP']['SUBEXECUTOR'][ $keySE ] = [
                            'VALUE'         => $valueSE,
                            'DESCRIPTION'   => $arSE[ $valueSE ]['DESCRIPTION'] ?? 0
                        ];

                        if ($_REQUEST['PROP']['REQUIRED_VISA'][ $keySE ] == 'Y') {
                            $arReqVisa[] = 'I' . $valueSE;
                        }
                    }

                    foreach ($_REQUEST['PROP']['SUBEXECUTOR'] as $key => $value) {
                        if (false === mb_strpos($value['VALUE'], ':')) {
                            $_REQUEST['PROP']['SUBEXECUTOR'][ $key ]['VALUE'] = $value['VALUE'] . ':' . $value['DESCRIPTION'];
                        }
                    }

                    $_REQUEST['PROP']['REQUIRED_VISA'] = $arReqVisa;
                }

                if (empty($_REQUEST['PROP']['REQUIRED_VISA'])) {
                    $_REQUEST['PROP']['REQUIRED_VISA'] = false;
                }

                $el = new CIBlockElement();
                $arProp = $_REQUEST['PROP'];
                $arProp['POST']      = $_REQUEST['POST'][0];
                $arProp['CONTROLER'] = $_REQUEST['CONTROLER'][0];
                $arProp['DOCS']      = $_REQUEST['DOCS'];
                $arProp['VIEWS']     = false;

                $arTags = $_REQUEST['TAGS'];
                $arTags = array_unique($arTags);
                $arTags = array_filter($arTags);
                $arTags = array_map('trim', $arTags);

                $arLoadProductArray = [
                    'MODIFIED_BY'       => $USER->GetID(),
                    'IBLOCK_SECTION_ID' => false,
                    'IBLOCK_ID'         => Settings::$iblockId['ORDERS'],
                    'NAME'              => $_REQUEST['NAME'],
                    'ACTIVE'            => 'Y',
                    'DETAIL_TEXT'       => $_REQUEST['DETAIL_TEXT'],
                    'TAGS'              => implode(',', $arTags)
                ];

                $arElement = $obOrders->getById($_REQUEST['edit']);

                $arProperties = [];
                $rProperties = CIBlockProperty::GetList(
                    [
                        'sort' => 'asc',
                        'name' => 'asc'
                    ],
                    [
                        'ACTIVE'    => 'Y',
                        'IBLOCK_ID' => Settings::$iblockId['ORDERS']
                    ]
                );
                while ($arFields = $rProperties->GetNext()) {
                    $arProperties[ $arFields['CODE'] ] = $arFields;
                }

                $sTextChange = '';
                if ($_REQUEST['NAME'] != $arElement['NAME']) {
                    $sTextChange .= '<b>Наименование поручения:</b> ' . $arElement['NAME'] . ' &rarr; ' . $_REQUEST['NAME'] . '<br>';
                }

                if ($_REQUEST['DETAIL_TEXT'] != $arElement['~DETAIL_TEXT']) {
                    $sTextChange .= '<b>Содержание поручения:</b> ' . $arElement['~DETAIL_TEXT'] . ' &rarr; ' . $_REQUEST['DETAIL_TEXT'] . '<br>';
                }

                $arProp['TYPE'] = array_filter($arProp['TYPE']);
                if (empty($arProp['TYPE'])) {
                    $arProp['TYPE'] = false;
                }

                if ($arElement['PROPERTY_ISPOLNITEL_VALUE'] != $arProp['ISPOLNITEL']) {
                    if ($arProp['ACTION'] != Settings::$arActions['DRAFT']) {
                        $arProp['ACTION'] = Settings::$arActions['NEW'];
                    }
                    $arProp['DATE_FACT'] = false;
                    $arProp['POST_RESH'] = false;
                    $arProp['CONTROLER_RESH'] = false;
                    $arProp['DATE_FACT_SNYAT'] = false;
                    $arProp['DATE_FACT_ISPOLN'] = false;
                    $arProp['DOPSTATUS'] = false;
                    $arProp['NEWISPOLNITEL'] = false;
                    $arProp['DATE_ISPOLN_HIST'] = false;
                    $arProp['DATE_ISPOLN_BAD'] = false;
                    $arProp['NEW_DATE_ISPOLN'] = false;
                    $arProp['NEW_SUBEXECUTOR_DATE'] = false;
                    $arProp['CONTROLER_STATUS'] = false;
                    $arProp['DELEGATE_USER'] = false;
                    $arProp['DELEGATE_HISTORY'] = false;
                    $arProp['CONTROL_REJECT'] = false;
                    $arProp['WORK_INTER_STATUS'] = false;
                    if (!empty($arElement['PROPERTY_DELEGATION_VALUE'])) {
                        $arDelegation = [];
                        foreach ($arElement['PROPERTY_DELEGATION_VALUE'] as $key => $value) {
                            if ($value == $arElement['PROPERTY_ISPOLNITEL_VALUE']) {
                                $arDelegation[] = [
                                    'VALUE'         => $arProp['ISPOLNITEL'],
                                    'DESCRIPTION'   => '',
                                ];
                            } else {
                                $arDelegation[] = [
                                    'VALUE'         => $value,
                                    'DESCRIPTION'   => $arElement['PROPERTY_DELEGATION_DESCRIPTION'][ $key ],
                                ];
                            }
                        }
                        $arProp['DELEGATION'] = $arDelegation;
                    }
                }

                foreach ($arProp as $key => $value) {
                    if (in_array($key, ['VIEWS', 'DELEGATION', 'REQUIRED_VISA', 'DELEGATE_HISTORY', 'DELEGATE_USER'])) {
                        continue;
                    }
                    switch ($arProperties[ $key ]['PROPERTY_TYPE']) {
                        case 'L':
                            if ($value != $arElement['PROPERTY_' . $arProperties[ $key ]['CODE'] . '_ENUM_ID']) {
                                switch ($arProperties[ $key ]['CODE']) {
                                    case 'CATEGORY':
                                        $sTextChange .= '<b>' . $arProperties[ $key ]['NAME'] . ':</b> ' . $this->Category[ $arElement['PROPERTY_' . $arProperties[ $key ]['CODE'] . '_VALUE'][0] ]['NAME'] . ' &rarr; ' . $this->Category[ $value ]['VALUE'] . '<br>';
                                        break;
                                    default:
                                        break;
                                }
                            }
                            break;
                        default:
                            if ($value != $arElement['PROPERTY_' . $arProperties[ $key ]['CODE'] . '_VALUE']) {
                                if (is_array($arElement['PROPERTY_' . $arProperties[ $key ]['CODE'] . '_VALUE'])) {
                                    switch ($arProperties[ $key ]['CODE']) {
                                        case 'TYPE':
                                            $arOldValues = [];
                                            foreach ($arElement['PROPERTY_' . $arProperties[ $key ]['CODE'] . '_VALUE'] as $str) {
                                                $arOldValues[] = trim($this->TypeData[ $str ]['UF_NAME']);
                                            }
                                            $arNewValues = [];
                                            foreach ($value as $str) {
                                                $arNewValues[] = trim($this->TypeData[ $str ]['UF_NAME']);
                                            }
                                            if (md5(serialize($arOldValues)) != md5(serialize($arNewValues))) {
                                                $sTextChange .= '<b>' . $arProperties[ $key ]['NAME'] . ':</b> ' . implode(', ', $arOldValues) . ' &rarr; ' . implode(', ', $arNewValues) . '<br>';
                                            }
                                            break;
                                        case 'SUBEXECUTOR':
                                            $arOldValues = [];
                                            foreach ($arElement['PROPERTY_' . $key . '_VALUE'] as $newSubExec) {
                                                if (false !== mb_strpos($newSubExec, ':')) {
                                                    $newSubExec = explode(':', $newSubExec)[0];
                                                }
                                                $arOldValues[] = $this->ispolnitels[ $newSubExec ]['NAME'];
                                            }
                                            $arNewValues = [];
                                            foreach ($value as $newSubExec) {
                                                if ($newSubExec['VALUE'] > 0) {
                                                    if (false !== mb_strpos($newSubExec['VALUE'], ':')) {
                                                        $newSubExec['VALUE'] = explode(':', $newSubExec['VALUE'])[0];
                                                    }
                                                    $arNewValues[] = $this->ispolnitels[ $newSubExec['VALUE'] ]['NAME'];
                                                }
                                            }
                                            if (md5(serialize($arOldValues)) != md5(serialize($arNewValues))) {
                                                $sTextChange .= '<b>' . $arProperties[ $key ]['NAME'] . ':</b> ' . implode(', ', $arOldValues) . ' &rarr; ' . implode(', ', $arNewValues) . '<br>';
                                            }
                                            break;
                                        default:
                                            if (is_array($arElement['PROPERTY_' . $arProperties[ $key ]['CODE'] . '_VALUE']) && empty($arElement['PROPERTY_' . $arProperties[ $key ]['CODE'] . '_VALUE'])) {
                                                $arElement['PROPERTY_' . $arProperties[ $key ]['CODE'] . '_VALUE'] = '';
                                            }
                                            if (md5(serialize($arElement['PROPERTY_' . $arProperties[ $key ]['CODE'] . '_VALUE'])) != md5(serialize($value))) {
                                                $sTextChange .= '<b>' . $arProperties[ $key ]['NAME'] . ':</b> ' . implode(',', $arElement['PROPERTY_' . $arProperties[ $key ]['CODE'] . '_VALUE']) . ' &rarr; ' . implode(', ', $value) . '<br>';
                                            }
                                            break;
                                    }
                                } else {
                                    switch ($arProperties[ $key ]['CODE']) {
                                        case 'ISPOLNITEL':
                                            $sTextChange .= '<b>' . $arProperties[ $key ]['NAME'] . ':</b> ' . $this->ispolnitels[ $arElement['PROPERTY_' . $arProperties[ $key ]['CODE'] . '_VALUE'] ]['NAME'] . ' &rarr; ' . $this->ispolnitels[ $value ]['NAME'] . '<br>';
                                            break;
                                        case 'POST':
                                        case 'CONTROLER':
                                            $sTextChange .= '<b>' . $arProperties[ $key ]['NAME'] . ':</b> ' . $this->getUserFullName($arElement['PROPERTY_' . $arProperties[ $key ]['CODE'] . '_VALUE']) . ' &rarr; ' . $this->getUserFullName($value) . '<br>';
                                            break;
                                        case 'CAT_THEME':
                                            $arOldValue = $this->Classificator[ $arElement['PROPERTY_CAT_THEME_VALUE'] ]['NAME'];
                                            $arNewValue = $this->Classificator[ $value ]['NAME'];
                                            $sTextChange .= '<b>' . $arProperties[ $key ]['NAME'] . ':</b> ' . $arOldValue . ' &rarr; ' . $arNewValue . '<br>';
                                            break;
                                        case 'THEME':
                                            $arOldValue = $this->Classificator[ $arElement['PROPERTY_CAT_THEME_VALUE'] ]['THEMES'][ $arElement['PROPERTY_THEME_VALUE'] ]['NAME'];
                                            $arNewValue = $this->Classificator[ $arProp['CAT_THEME'] ?: $arElement['PROPERTY_CAT_THEME_VALUE'] ]['THEMES'][ $value ]['NAME'];
                                            $sTextChange .= '<b>' . $arProperties[ $key ]['NAME'] . ':</b> ' . $arOldValue . ' &rarr; ' . $arNewValue . '<br>';
                                            break;
                                        default:
                                            $sTextChange .= '<b>' . $arProperties[ $key ]['NAME'] . ':</b> ' . $arElement['PROPERTY_' . $arProperties[ $key ]['CODE'] . '_VALUE'] . ' &rarr; ' . $value . '<br>';
                                            break;
                                    }
                                }
                            }
                            break;
                    }
                }

                if ($PRODUCT_ID = $el->Update($_REQUEST['edit'], $arLoadProductArray)) {
                    $bSendEmailSrok = false;
                    if (
                        $arProp['DATE_ISPOLN'] != $arElement['PROPERTY_DATE_ISPOLN_VALUE'] &&
                        $arProp['DATE_ISPOLN'] != $this->disableSrokDate
                    ) {
                        $arProp['DELEGATE_HISTORY'] = [];
                        foreach ($arElement['~PROPERTY_DELEGATE_HISTORY_VALUE'] as $history) {
                            $history = json_decode($history, true);
                            if ($history['DELEGATE'] == $arElement['PROPERTY_DELEGATE_USER_VALUE']) {
                                $history['SROK'] = $arProp['DATE_ISPOLN'];
                                $bSendEmailSrok = true;
                            }

                            $arProp['DELEGATE_HISTORY'][] = json_encode($history, JSON_UNESCAPED_UNICODE);
                        }
                    }

                    if ($bSendEmailSrok) {
                        $arSendUsers = $this->ispolnitels[ $arElement['PROPERTY_ISPOLNITEL_VALUE'] ]['PROPERTY_IMPLEMENTATION_VALUE'];
                        if (!empty($arSendUsers)) {
                            Notify::send(
                                [$_REQUEST['edit']],
                                'NEW_SROK',
                                $arSendUsers
                            );
                        }
                    }

                    CIBlockElement::SetPropertyValuesEx(
                        $_REQUEST['edit'],
                        Settings::$iblockId['ORDERS'],
                        $arProp
                    );
                    if (!empty($sTextChange)) {
                        $this->addToLog(
                            'Изменены данные поручения',
                            $sTextChange,
                            $_REQUEST['edit']
                        );
                    }
                    $this->log(
                        $_REQUEST['edit'],
                        'Изменены данные поручения',
                        [
                            'METHOD'    => __METHOD__,
                            'FIELDS'    => $arLoadProductArray,
                            'PROP'      => $arProp,
                            'REQUEST'   => $_REQUEST,
                        ]
                    );
                    LocalRedirect($redirecturl);
                } else {
                    echo 'Error: ' . $el->LAST_ERROR;
                }
                break;
            default:
                break;
        }
    }

    /**
     * Детальная информация о поручении
     *
     * @return void
     */
    public function getDetailData()
    {
        $obOrders = new Orders();
        $arParams['ENUM'] = $this->getEnums(Settings::$iblockId['ORDERS']);
        $arParams['COMMENT_ENUM'] = $this->getEnums(Settings::$iblockId['ORDERS_COMMENT']);
        global $USER;
        $redirecturl = '/control-orders/?detail=' . $_REQUEST['detail'];
        if ($_REQUEST['view']) {
            $redirecturl .= '&view=' . $_REQUEST['view'];
            if ($_REQUEST['view'] == 'list') {
                $redirecturl = '?action_filter=' . $_REQUEST['action_filter'];
            }
        }

        if ($_REQUEST['sub']) {
            $redirecturl .= '&sub=' . $_REQUEST['sub'];
        }

        if ($_REQUEST['back_url']) {
            $redirecturl .= '&back_url=' . $_REQUEST['back_url'];
        }

        if (isset($_REQUEST['from']) && $_REQUEST['from'] == 'list') {
            $redirecturl = str_replace('|', '&', rawurldecode($_REQUEST['back_url']));
        }

        if ($_REQUEST['view'] == 'svyazi' && $_REQUEST['subaction'] == 'add_task' && $_REQUEST['add'] != '') {
            $arTasks = $obOrders->getProperty($_REQUEST['detail'], 'TASKS', true);
            if ((int)$_REQUEST['add'] && !in_array((int)$_REQUEST['add'], $arTasks)) {
                $arTasks[]  = (int)$_REQUEST['add'];
                CIBlockElement::SetPropertyValuesEx(
                    $_REQUEST['detail'],
                    false,
                    [
                        'TASKS' => $arTasks,
                    ]
                );
                $this->log(
                    $_REQUEST['detail'],
                    'Добавлена связь с задачей',
                    [
                        'METHOD'    => __METHOD__,
                        'REQUEST'   => $_REQUEST,
                    ]
                );
                $this->addToLog('Добавлена связь с задачей ' . $_REQUEST['add']);
                LocalRedirect($redirecturl);
            }
        }

        if ($_REQUEST['view'] == 'svyazi' && $_REQUEST['subaction'] == 'add' && $_REQUEST['add'] != '') {
            $arPoruch = $obOrders->getProperty($_REQUEST['detail'], 'PORUCH', true);
            if (is_array($_REQUEST['add'])) {
                foreach ($_REQUEST['add'] as $sKey => $sValue) {
                    $arPoruchAdd = $obOrders->getProperty($sValue, 'PORUCH', true);
                    if (!in_array($_REQUEST['detail'], $arPoruchAdd)) {
                        $arPoruchAdd[] = $_REQUEST['detail'];
                        CIBlockElement::SetPropertyValuesEx(
                            $sValue,
                            false,
                            [
                                'PORUCH' => $arPoruchAdd,
                            ]
                        );
                        $this->log(
                            $sValue,
                            'Добавлена связь из поручения',
                            [
                                'METHOD'    => __METHOD__,
                                'REQUEST'   => $_REQUEST,
                            ]
                        );
                        $this->addToLog('Добавлена связь из поручения', $_REQUEST['detail']);
                    }
                }
                $arPoruch = array_unique(array_merge($arPoruch, $_REQUEST['add']));
            } elseif ((int)$_REQUEST['add']) {
                if (!in_array((int)$_REQUEST['add'], $arPoruch)) {
                    $arPoruch[] = (int)$_REQUEST['add'];
                    $arPoruchAdd = $obOrders->getProperty($_REQUEST['add'], 'PORUCH', true);
                    if (!in_array($_REQUEST['detail'], $arPoruchAdd)) {
                        $arPoruchAdd[] = $_REQUEST['detail'];
                        CIBlockElement::SetPropertyValuesEx(
                            $_REQUEST['add'],
                            false,
                            [
                                'PORUCH' => $arPoruchAdd,
                            ]
                        );
                        $this->log(
                            $_REQUEST['add'],
                            'Добавлена связь из поручения',
                            [
                                'METHOD'    => __METHOD__,
                                'REQUEST'   => $_REQUEST,
                            ]
                        );
                        $this->addToLog('Добавлена связь из поручения', $_REQUEST['detail']);
                    }
                }
            }

            CIBlockElement::SetPropertyValuesEx(
                $_REQUEST['detail'],
                false,
                [
                    'PORUCH' => $arPoruch,
                ]
            );
            $this->log(
                $_REQUEST['detail'],
                'Добавлено связанное поручение',
                [
                    'METHOD'    => __METHOD__,
                    'REQUEST'   => $_REQUEST,
                ]
            );
            $this->addToLog('Добавлено связанное поручение');
            LocalRedirect($redirecturl);
        }

        switch ($_REQUEST['action']) {
            case 'accept_to_work':
                $this->log(
                    $_REQUEST['detail'],
                    'Передано в новое',
                    [
                        'METHOD'    => __METHOD__,
                        'REQUEST'   => $_REQUEST,
                    ]
                );
                CIBlockElement::SetPropertyValuesEx(
                    $_REQUEST['detail'],
                    false,
                    [
                        'ACTION'            => Settings::$arActions['NEW'],
                        'VIEWS'             => false,
                        'WORK_INTER_STATUS' => false,
                    ]
                );
                break;
            case 'accept_to_real_work':
                $this->log(
                    $_REQUEST['detail'],
                    'Передано на исполнение',
                    [
                        'METHOD'    => __METHOD__,
                        'REQUEST'   => $_REQUEST,
                    ]
                );
                CIBlockElement::SetPropertyValuesEx(
                    $_REQUEST['detail'],
                    false,
                    [
                        'ACTION' => Settings::$arActions['WORK'],
                        'VIEWS'             => false,
                        'WORK_INTER_STATUS' => false,
                    ]
                );
                break;
            case 'accepting_ispolnitel':
                $hlblock = HLTable::getById($this->hlblock['Resolution'])->fetch();
                $entity = HLTable::compileEntity($hlblock);
                $entityDataClass = $entity->getDataClass();
                $arExecutors = Executors::getList();
                $arOrder = $obOrders->getById($_REQUEST['detail']);
                $arMessage = [
                    'EXECUTOR' => '',
                    'SUBEXECUTORS' => [],
                ];

                $ispolnitel = $_REQUEST['DELEGATE_USER'];
                $subexecutor = $_REQUEST['ACCOMPLICES']??[];

                if (isset($_REQUEST['DELEGATE_PORUCH'])) {
                    if (mb_substr($_REQUEST['DELEGATE_PORUCH'], 0, 5) == 'user_') {
                        $ispolnitel = str_replace('user_', '', $_REQUEST['DELEGATE_PORUCH']);
                        $arMessage['EXECUTOR'] = $this->getUserFullName($ispolnitel);
                    } else {
                        $ispolnitel = 'DEP' . $_REQUEST['DELEGATE_PORUCH'];
                        $arMessage['EXECUTOR'] = $arExecutors[ $_REQUEST['DELEGATE_PORUCH'] ]['NAME'];
                    }

                    foreach ($_REQUEST['DELEGATE_SUBEXECUTOR'] as $subRow) {
                        if (empty($subRow)) {
                            continue;
                        }
                        if (mb_substr($subRow, 0, 5) == 'user_') {
                            $uId = str_replace('user_', '', $subRow);
                            $subexecutor[] = $uId;
                            $arMessage['SUBEXECUTORS'][] = $this->getUserFullName($uId);
                        } else {
                            $subexecutor[] = 'DEP' . $subRow;
                            $arMessage['SUBEXECUTORS'][] = $arExecutors[ $subRow ]['NAME'];
                        }
                    }
                } else {
                    $arMessage['EXECUTOR'] = $this->getUserFullName($_REQUEST['DELEGATE_USER']);
                    foreach ($subexecutor as $val) {
                        $arMessage['SUBEXECUTORS'][] = $this->getUserFullName($val);
                    }
                }
                $arFields = [
                    'UF_ORDER'          => (int)$_REQUEST['detail'],
                    'UF_AUTHOR'         => $GLOBALS['USER']->GetID(),
                    'UF_DATE'           => date('d.m.Y H:i:s'),
                    'UF_APPROVE'        => $this->arResolutionStatus['E']['ID'],
                    'UF_APPROVE_USER'   => false,
                    'UF_APPROVE_DATE'   => false,
                    'UF_ISPOLNITEL'     => $ispolnitel,
                    'UF_SUBEXECUTOR'    => json_encode($subexecutor),
                    'UF_COMMENT'        => $_REQUEST['DELEGATE_COMMENT'] ?? '',
                    'UF_SROK'           => $_REQUEST['DELEGATE_SROK'] ?? false,
                    'UF_REJECT_COMMENT' => false,
                ];

                if (isset($_REQUEST['edit-resolution']) && $_REQUEST['edit-resolution'] > 0) {
                    $entityDataClass::update((int)$_REQUEST['edit-resolution'], $arFields);
                    $logText = 'Изменён проект резолюции';
                } else {
                    $entityDataClass::add($arFields);
                    $logText = 'Добавлен проект резолюции';
                }
                $this->log(
                    $_REQUEST['detail'],
                    $logText,
                    [
                        'METHOD'    => __METHOD__,
                        '_REQUEST'  => $_REQUEST,
                        'arFields'  => $arFields,
                    ]
                );
                CIBlockElement::SetPropertyValuesEx(
                    $_REQUEST['detail'],
                    false,
                    [
                        'VIEWS' => false,
                    ]
                );

                $message = '[B]Проект резолюции по поручению[/B]#BR##BR#';
                $message .= $arOrder['NAME'];
                $message .= ' № ' . $arOrder['PROPERTY_NUMBER_VALUE'];
                $message .= ' от ' . $arOrder['PROPERTY_DATE_CREATE_VALUE'];
                if (!empty($arMessage['EXECUTOR'])) {
                    $message .= '#BR##BR#[B]Исполнитель[/B]: ' . $arMessage['EXECUTOR'];
                }
                if (!empty($_REQUEST['DELEGATE_SROK'])) {
                    $message .= '#BR#[B]Срок для исполнителя[/B]: ' . $_REQUEST['DELEGATE_SROK'];
                }
                if (!empty($arMessage['SUBEXECUTORS'])) {
                    $message .= '#BR#[B]Соисполнители[/B]: ' . implode(', ', $arMessage['SUBEXECUTORS']);
                }
                if (!empty($_REQUEST['DELEGATE_COMMENT'])) {
                    $message .= '#BR#[B]Комментарий[/B]: ' . $_REQUEST['DELEGATE_COMMENT'];
                }

                $link = 'https://' . $_SERVER['SERVER_NAME'] . '/control-orders/?detail=' . $arOrder['ID'];

                // $message .= '#BR##BR##BUTTONS#';
                $message .= '#BR##BR#Перейти к поручению:#BR#[URL=' . $link . ']' . $arOrder['~DETAIL_TEXT'] . '[/URL]';
                $arSendUsers = [
                    $GLOBALS['USER']->GetID(),
                    /* $arExecutors[ $arOrder['PROPERTY_ISPOLNITEL_VALUE'] ]['PROPERTY_RUKOVODITEL_VALUE'], */
                ];
                /*
                $arSendUsers = array_merge(
                    $arSendUsers,
                    $arExecutors[ $arOrder['PROPERTY_ISPOLNITEL_VALUE'] ]['PROPERTY_ZAMESTITELI_VALUE']
                );
                */

                $sButtons = '<table class="data-table">
                    <tr>
                        <td style="background:#bbed21;" class="left">
                            <a href="' . $link . '" style="display:block;border-width: 5px 15px; border-color:#bbed21;border-style:solid;background:#bbed21;text-decoration:none;color:#535c69;text-transform:uppercase;font-size:11px;font-weight:bold;">Согласовать</a>
                        </td>
                        <td>&nbsp;&nbsp;&nbsp;</td>
                        <td style="background:#f1361a;" class="right">
                            <a href="' . $link . '" style="display:block;border-width: 5px 15px; border-color:#f1361a;border-style:solid;background:#f1361a;text-decoration:none;color:#fff;text-transform:uppercase;font-size:11px;font-weight:bold;">Отклонить</a>
                        </td>
                    </tr>
                </table>';
                Notify::send(
                    [$arOrder['ID']],
                    'ACCEPT_ISPOLNITEL',
                    $arSendUsers,
                    $message,
                    true,
                    []//'BUTTONS' => $sButtons]
                );
                LocalRedirect($redirecturl);
                break;
            case 'accept_ispolnitel':
                $curUserId = $GLOBALS['USER']->GetID();
                if (isset($_REQUEST['DELEGATE_PORUCH'])) {
                    if (mb_substr($_REQUEST['DELEGATE_PORUCH'], 0, 5) == 'user_') {
                        $iDelegate = str_replace('user_', '', $_REQUEST['DELEGATE_PORUCH']);
                        CIBlockElement::SetPropertyValuesEx(
                            $_REQUEST['detail'],
                            false,
                            [
                                'ACTION'            => Settings::$arActions['WORK'],
                                'DELEGATE_USER'     => $iDelegate,
                                'DELEGATE_COMMENT'  => $_REQUEST['DELEGATE_COMMENT']??'',
                                'DELEGATE_SROK'     => $_REQUEST['DELEGATE_SROK']??false,
                            ]
                        );
                        $message = 'Принято на исполнение, делегировано на ' . $this->permissions['ispolnitel_delegated'][ $iDelegate ];
                        $this->log(
                            $_REQUEST['detail'],
                            $message,
                            [
                                'METHOD'    => __METHOD__,
                                'REQUEST'   => $_REQUEST,
                            ]
                        );
                        $this->addToLog($message);
                    } elseif ($_REQUEST['DELEGATE_PORUCH'] > 0) {
                        $newIspolnitel = (int)$_REQUEST['DELEGATE_PORUCH'];

                        $oldIspolnitel = $obOrders->getProperty($_REQUEST['detail'], 'ISPOLNITEL');

                        $resDelegation = CIBlockElement::GetProperty(
                            Settings::$iblockId['ORDERS'],
                            $_REQUEST['detail'],
                            'sort',
                            'asc',
                            [
                                'CODE' => 'DELEGATION'
                            ]
                        );
                        $arDelegation = [];
                        while ($rowDelegation = $resDelegation->GetNext()) {
                            if (empty($rowDelegation['VALUE'])) {
                                continue;
                            }
                            $arDelegation[] = [
                                'VALUE'         => $rowDelegation['VALUE'],
                                'DESCRIPTION'   => $rowDelegation['~DESCRIPTION'],
                            ];
                        }

                        if (empty($arDelegation)) {
                            $arDelegation[] = [
                                'VALUE'         => $oldIspolnitel['VALUE'],
                                'DESCRIPTION'   => '',
                            ];
                        }
                        $arDelegation[] = [
                            'VALUE'         => $newIspolnitel,
                            'DESCRIPTION'   => json_encode([
                                'DATE_ADD'      => date('d.m.Y'),
                                'DATE_ADD_TS'   => time(),
                            ]),
                        ];
                        CIBlockElement::SetPropertyValuesEx(
                            $_REQUEST['detail'],
                            false,
                            [
                                'DELEGATE_USER' => false,
                                'DELEGATION'    => $arDelegation,
                                'ISPOLNITEL'    => $newIspolnitel,
                            ]
                        );
                        $message = 'Делегировано новому исполнителю ' . $this->ispolnitels[ $newIspolnitel ]['NAME'];
                        $subMessage = false;
                        if (!empty($_REQUEST['DELEGATE_COMMENT'])) {
                            $subMessage = 'Комментарий: ' . $_REQUEST['DELEGATE_COMMENT'];
                        }
                        $this->log(
                            $_REQUEST['detail'],
                            $message,
                            [
                                'METHOD'    => __METHOD__,
                                'REQUEST'   => $_REQUEST,
                            ]
                        );
                        $this->addToLog($message, $subMessage, $_REQUEST['detail']);
                    } else {
                        CIBlockElement::SetPropertyValuesEx(
                            $_REQUEST['detail'],
                            false,
                            [
                                'ACTION'            => Settings::$arActions['WORK'],
                                'DELEGATE_USER'     => $curUserId,
                                'DELEGATE_COMMENT'  => $_REQUEST['DELEGATE_COMMENT'],
                                'DELEGATE_SROK'     => $_REQUEST['DELEGATE_SROK']??false,
                            ]
                        );
                        $this->log(
                            $_REQUEST['detail'],
                            'Принято на исполнение',
                            [
                                'METHOD'    => __METHOD__,
                                'REQUEST'   => $_REQUEST,
                            ]
                        );
                        $this->addToLog('Принято на исполнение');
                    }

                    if (!empty($_REQUEST['DELEGATE_SUBEXECUTOR'])) {
                        $arAccomplices = [];
                        $arSubExecutors = [];
                        $resSE = CIBlockElement::GetProperty(
                            Settings::$iblockId['ORDERS'],
                            $_REQUEST['detail'],
                            'sort',
                            'asc',
                            [
                                'CODE' => 'SUBEXECUTOR'
                            ]
                        );
                        while ($rowSE = $resSE->GetNext()) {
                            $depId = $rowSE['VALUE'];
                            $userId = $rowSE['~DESCRIPTION'];
                            if (false !== mb_strpos($rowSE['VALUE'], ':')) {
                                $val = explode(':', $rowSE['VALUE']);
                                $depId = $val[0];
                                $userId = $val[1];
                            }
                            $arSE[ $depId ] = [
                                'VALUE'         => $depId . ':' . $userId,
                                'DESCRIPTION'   => $userId,
                            ];
                        }

                        foreach ($_REQUEST['DELEGATE_SUBEXECUTOR'] as $subRow) {
                            if (empty($subRow)) {
                                continue;
                            }
                            if (mb_substr($subRow, 0, 5) == 'user_') {
                                $arAccomplices[] = str_replace('user_', '', $subRow);
                            } else {
                                $arSubExecutors[] = [
                                    'VALUE'         => $subRow . ':0',
                                    'DESCRIPTION'   => 0
                                ];
                            }
                        }

                        $arAccomplices = array_filter($arAccomplices);
                        $arSubExecutors = array_filter($arSubExecutors);

                        if (!empty($arAccomplices)) {
                            CIBlockElement::SetPropertyValuesEx(
                                $_REQUEST['detail'],
                                false,
                                [
                                    'ACCOMPLICES'       => $arAccomplices,
                                    'DELEGATE_COMMENT'  => $_REQUEST['DELEGATE_COMMENT']??'',
                                    'DELEGATE_SROK'     => $_REQUEST['DELEGATE_SROK']??false,
                                ]
                            );
                            $arNames = [];
                            foreach ($arAccomplices as $uId) {
                                $arNames[] = $this->getUserFullName($uId);
                            }
                            if (!empty($arNames)) {
                                $this->log(
                                    $_REQUEST['detail'],
                                    'Соисполнители: ' . implode(', ', $arNames),
                                    [
                                        'METHOD'    => __METHOD__,
                                        'REQUEST'   => $_REQUEST,
                                    ]
                                );
                                $this->addToLog('Соисполнители: ' . implode(', ', $arNames));
                            }
                        }

                        if (!empty($arSubExecutors)) {
                            CIBlockElement::SetPropertyValuesEx(
                                $_REQUEST['detail'],
                                false,
                                [
                                    'SUBEXECUTOR'       => $arSubExecutors,
                                    'DELEGATE_COMMENT'  => $_REQUEST['DELEGATE_COMMENT']??'',
                                    'DELEGATE_SROK'     => $_REQUEST['DELEGATE_SROK']??false,
                                ]
                            );
                            $arNames = [];
                            foreach ($arSubExecutors as $uId) {
                                $arNames[] = $this->ispolnitels[ $uId ]['NAME'];
                            }

                            if (!empty($arNames)) {
                                $this->log(
                                    $_REQUEST['detail'],
                                    'Соисполнители: ' . implode(', ', $arNames),
                                    [
                                        'METHOD'    => __METHOD__,
                                        'REQUEST'   => $_REQUEST,
                                    ]
                                );
                                $this->addToLog('Соисполнители: ' . implode(', ', $arNames));
                            }
                        }
                    }
                } else {
                    $arOrder = $obOrders->getById($_REQUEST['detail']);
                    CIBlockElement::SetPropertyValuesEx(
                        $_REQUEST['detail'],
                        false,
                        [
                            'ACTION' => Settings::$arActions['WORK'],
                        ]
                    );
                    $arAccomplicesNames = [];
                    $arAccomplices = array_filter($_REQUEST['ACCOMPLICES']);
                    $arAccomplices = array_unique($arAccomplices);
                    $arExecutors   = $this->permissions['ispolnitel_data']['PROPERTY_ISPOLNITELI_VALUE'];
                    $setExecutors  = false;

                    foreach ($arAccomplices as $k => $uId) {
                        if ((int)$_REQUEST['DELEGATE_USER'] > 0 && $uId == $_REQUEST['DELEGATE_USER']) {
                            unset($arAccomplices[ $k ]);
                            continue;
                        }

                        $arAccomplicesNames[ $uId ] = $this->getUserFullName($uId);

                        /*
                         * Добавить выбранного пользователя в исполнителей
                         */
                        // if (!in_array($uId, $arExecutors)) {
                        //     $arGroups = CUser::GetUserGroup($uId);
                        //     $arGroups[] = 96;
                        //     CUser::SetUserGroup($uId, $arGroups);
                        //     $arExecutors[] = $uId;
                        //     $setExecutors = true;
                        // }
                    }

                    if ($setExecutors) {
                        $arExecutors = array_unique($arExecutors);
                        CIBlockElement::SetPropertyValuesEx(
                            $this->permissions['ispolnitel_data']['ID'],
                            false,
                            [
                                'ISPOLNITELI' => $arExecutors,
                            ]
                        );
                    }

                    CIBlockElement::SetPropertyValuesEx(
                        $_REQUEST['detail'],
                        false,
                        [
                            'ACCOMPLICES'       => $arAccomplices,
                            'DELEGATE_COMMENT'  => $_REQUEST['DELEGATE_COMMENT']??'',
                            'DELEGATE_SROK'     => $_REQUEST['DELEGATE_SROK']??false,
                        ]
                    );

                    if ((int)$_REQUEST['DELEGATE_USER'] > 0) {
                        $iDelegate = (int)$_REQUEST['DELEGATE_USER'];
                        if (
                            $iDelegate != $arOrder['PROPERTY_DELEGATE_USER_VALUE'] ||
                            $curUserId != $arOrder['PROPERTY_DELEGATE_USER_VALUE']
                        ) {
                            $message = '';
                            if ($iDelegate != $arOrder['PROPERTY_DELEGATE_USER_VALUE']) {
                                $message .= 'Принято на исполнение, делегировано на ' . $this->permissions['ispolnitel_delegated'][ $iDelegate ] . '.';
                            }

                            if (!empty($_REQUEST['DELEGATE_SROK'])) {
                                $curSrok = $obOrders->getSrok($arOrder['ID'], (int)$arOrder['PROPERTY_DELEGATE_USER_VALUE']);
                                if ($curSrok != $_REQUEST['DELEGATE_SROK']) {
                                    $message .= ' Срок для исполнителя: ' . $_REQUEST['DELEGATE_SROK'];
                                }
                            }
                            $subMessage = false;
                            if (!empty($arAccomplicesNames)) {
                                $subMessage = 'Соисполнители: ' . implode(', ', $arAccomplicesNames);
                            }
                            if (!empty($_REQUEST['DELEGATE_COMMENT'])) {
                                $subMessage .= '<br/><br/>Комментарий: ' . $_REQUEST['DELEGATE_COMMENT'];
                            }
                            CIBlockElement::SetPropertyValuesEx(
                                $_REQUEST['detail'],
                                false,
                                [
                                    'DELEGATE_USER'     => $iDelegate,
                                    'DELEGATE_COMMENT'  => $_REQUEST['DELEGATE_COMMENT']??'',
                                    'DELEGATE_SROK'     => $_REQUEST['DELEGATE_SROK']??false,
                                ]
                            );
                            $this->log(
                                $_REQUEST['detail'],
                                $message,
                                [
                                    'METHOD'    => __METHOD__,
                                    'REQUEST'   => $_REQUEST,
                                ]
                            );
                            $this->addToLog(trim($message), $subMessage);

                            /*
                             * Добавить выбранного пользователя в исполнителей
                             */
                            if (!in_array($iDelegate, $this->permissions['ispolnitel_data']['PROPERTY_ISPOLNITELI_VALUE'])) {
                                $arGroups = CUser::GetUserGroup($iDelegate);
                                $arGroups[] = 96;
                                CUser::SetUserGroup($iDelegate, $arGroups);
                                $arExecutors = $this->permissions['ispolnitel_data']['PROPERTY_ISPOLNITELI_VALUE'];
                                $arExecutors[] = $iDelegate;
                                $arExecutors = array_unique($arExecutors);
                                CIBlockElement::SetPropertyValuesEx(
                                    $this->permissions['ispolnitel_data']['ID'],
                                    false,
                                    [
                                        'ISPOLNITELI' => $arExecutors,
                                    ]
                                );
                            }
                        }
                    } else {
                        CIBlockElement::SetPropertyValuesEx(
                            $_REQUEST['detail'],
                            false,
                            [
                                'DELEGATE_USER' => $curUserId,
                                'DELEGATE_COMMENT'  => $_REQUEST['DELEGATE_COMMENT']??'',
                                'DELEGATE_SROK'     => $_REQUEST['DELEGATE_SROK']??false,
                            ]
                        );
                        $message = 'Принято на исполнение.';
                        if (!empty($_REQUEST['DELEGATE_SROK'])) {
                            $curSrok = $obOrders->getSrok($arOrder['ID'], $arOrder['PROPERTY_DELEGATE_USER_VALUE']);
                            if ($curSrok != $_REQUEST['DELEGATE_SROK']) {
                                $message .= ' Срок для исполнителя: ' . $_REQUEST['DELEGATE_SROK'];
                            }
                        }
                        $this->log(
                            $_REQUEST['detail'],
                            'Принято на исполнение',
                            [
                                'METHOD'    => __METHOD__,
                                'REQUEST'   => $_REQUEST,
                            ]
                        );
                        $this->addToLog($message);
                    }
                }

                $helper = new HlblockHelper();
                $hlId = $helper->getHlblockId('ControlOrdersResolution');
                $hlblock = HLTable::getById($hlId)->fetch();
                $entity = HLTable::compileEntity($hlblock);
                $entityDataClass = $entity->getDataClass();
                $rsData = $entityDataClass::getList([
                    'filter' => [
                        'UF_ORDER'      => $_REQUEST['detail'],
                        'UF_APPROVE'    => $this->arResolutionStatus['E']['ID'],
                    ],
                    'order'  => [
                        'UF_DATE' => 'DESC',
                    ],
                ]);
                while ($arRes = $rsData->fetch()) {
                    $arUpdate = [
                        'UF_APPROVE'        => $this->arResolutionStatus['D']['ID'],
                        'UF_APPROVE_USER'   => $curUserId,
                        'UF_APPROVE_DATE'   => date('d.m.Y H:i:s'),
                    ];

                    $entityDataClass::update($arRes['ID'], $arUpdate);
                }
                CIBlockElement::SetPropertyValuesEx(
                    $_REQUEST['detail'],
                    false,
                    [
                        'VIEWS' => false,
                    ]
                );
                LocalRedirect($redirecturl);
                break;
            case 'delegate_accomplices':
                if (count($_REQUEST['DELEGATE_ACCOMPLICES']) > 0) {
                    $arNewAccomplice = array_filter($_REQUEST['DELEGATE_ACCOMPLICES']);

                    $arAccomplices = [];
                    $res = CIBlockElement::GetProperty(
                        Settings::$iblockId['ORDERS'],
                        $_REQUEST['detail'],
                        'sort',
                        'asc',
                        [
                            'CODE' => 'ACCOMPLICES'
                        ]
                    );
                    while ($row = $res->GetNext()) {
                        $arAccomplices[ $row['VALUE'] ] = $row['VALUE'];
                    }
                    $arAddedNames = [];
                    $arRemovedNames = [];
                    foreach ($arNewAccomplice as $uId) {
                        if ($uId < 0) {
                            $findUid = ($uId*-1);
                            if (array_key_exists($findUid, $arAccomplices)) {
                                unset($arAccomplices[ $findUid ]);
                                $arRemovedNames[] = $this->getUserFullName($findUid);
                            }
                        } elseif (!array_key_exists($uId, $arAccomplices)) {
                            $arAccomplices[ $uId ] = $uId;
                            $arAddedNames[] = $this->getUserFullName($uId);
                            /*
                             * Добавить выбранного пользователя в исполнителей
                             */
                            if (!in_array($uId, $this->permissions['ispolnitel_data']['PROPERTY_ISPOLNITELI_VALUE'])) {
                                $arGroups = CUser::GetUserGroup($uId);
                                $arGroups[] = 96;
                                CUser::SetUserGroup($uId, $arGroups);
                                $arExecutors = $this->permissions['ispolnitel_data']['PROPERTY_ISPOLNITELI_VALUE'];
                                $arExecutors[] = $uId;
                                $arExecutors = array_unique($arExecutors);
                                CIBlockElement::SetPropertyValuesEx(
                                    $this->permissions['ispolnitel_data']['ID'],
                                    false,
                                    [
                                        'ISPOLNITELI' => $arExecutors,
                                    ]
                                );
                            }
                        }
                    }
                    $message = '';
                    if (!empty($arAddedNames)) {
                        $message .= 'Добавлены: ' . implode(', ', $arAddedNames) . '<br/>';
                    }
                    if (!empty($arRemovedNames)) {
                        $message .= 'Удалены: ' . implode(', ', $arRemovedNames);
                    }
                    if (!empty($message)) {
                        $this->log(
                            $_REQUEST['detail'],
                            'Изменены соисполнители',
                            [
                                'METHOD'    => __METHOD__,
                                'MESSAGE'   => $message,
                                'REQUEST'   => $_REQUEST,
                            ]
                        );
                        $this->addToLog('Изменены соисполнители', $message);
                        $arAccomplices = array_filter($arAccomplices);
                        $arAccomplices = array_unique($arAccomplices);
                        CIBlockElement::SetPropertyValuesEx(
                            $_REQUEST['detail'],
                            false,
                            [
                                'ACCOMPLICES'       => $arAccomplices,
                                'DELEGATE_COMMENT'  => $_REQUEST['DELEGATE_COMMENT']??'',
                                'DELEGATE_SROK'     => $_REQUEST['DELEGATE_SROK']??false,
                            ]
                        );
                    }
                }
                LocalRedirect($redirecturl);
                break;
            case 'accept_subexecutor':
                if (count($_REQUEST['SUBEXECUTOR_USER']) > 0) {
                    $arSE = [];
                    $resSE = CIBlockElement::GetProperty(
                        Settings::$iblockId['ORDERS'],
                        $_REQUEST['detail'],
                        'sort',
                        'asc',
                        [
                            'CODE' => 'SUBEXECUTOR'
                        ]
                    );
                    while ($rowSE = $resSE->GetNext()) {
                        $depId = $rowSE['VALUE'];
                        $userId = $rowSE['~DESCRIPTION'];
                        if (false !== mb_strpos($rowSE['VALUE'], ':')) {
                            $val = explode(':', $rowSE['VALUE']);
                            $depId = $val[0];
                            $userId = $val[1];
                        }
                        $arSE[ $depId ] = [
                            'VALUE'         => $depId,
                            'DESCRIPTION'   => $userId,
                        ];
                    }

                    $iDelegate = 0;

                    foreach ($_REQUEST['SUBEXECUTOR_USER'] as $seId => $seUser) {
                        if (isset($arSE[ $seId ])) {
                            if (0 === mb_strpos($seUser, 'DEP')) {
                                $_REQUEST['DELEGATE_ACCOMPLICES'][] = $seUser;
                            } else {
                                $arSE[ $seId ]['DESCRIPTION'] = $seUser;
                                $iDelegate = $seUser;
                            }
                        }
                    }

                    if ($iDelegate > 0) {
                        foreach ($arSE as $key => $value) {
                            if (false === mb_strpos($value['VALUE'], ':')) {
                                $arSE[ $key ]['VALUE'] = $value['VALUE'] . ':' . $value['DESCRIPTION'];
                            }
                        }

                        CIBlockElement::SetPropertyValuesEx(
                            $_REQUEST['detail'],
                            false,
                            [
                                'SUBEXECUTOR'       => $arSE,
                                'DELEGATE_COMMENT'  => $_REQUEST['DELEGATE_COMMENT']??'',
                                'DELEGATE_SROK'     => $_REQUEST['DELEGATE_SROK']??false,
                            ]
                        );
                        $message = 'Делегировано на соисполнителя ' . $this->permissions['ispolnitel_delegated'][ $iDelegate ];
                        $subMessage = false;
                        if (!empty($_REQUEST['DELEGATE_COMMENT'])) {
                            $subMessage .= '<br/><br/>Комментарий: ' . $_REQUEST['DELEGATE_COMMENT'];
                        }
                        $this->log(
                            $_REQUEST['detail'],
                            $message,
                            [
                                'METHOD'    => __METHOD__,
                                'REQUEST'   => $_REQUEST,
                            ]
                        );
                        $this->addToLog($message, $subMessage);
                    }
                }

                if (count($_REQUEST['DELEGATE_ACCOMPLICES']) > 0) {
                    $arNewAccomplice = array_filter($_REQUEST['DELEGATE_ACCOMPLICES']);

                    $arAccomplices = [];
                    $res = CIBlockElement::GetProperty(
                        Settings::$iblockId['ORDERS'],
                        $_REQUEST['detail'],
                        'sort',
                        'asc',
                        [
                            'CODE' => 'ACCOMPLICES'
                        ]
                    );
                    while ($row = $res->GetNext()) {
                        $arAccomplices[ $row['VALUE'] ] = $row['VALUE'];
                    }
                    $arSubExecutors = [];
                    $resSE = CIBlockElement::GetProperty(
                        Settings::$iblockId['ORDERS'],
                        $_REQUEST['detail'],
                        'sort',
                        'asc',
                        [
                            'CODE' => 'SUBEXECUTOR'
                        ]
                    );
                    while ($rowSE = $resSE->GetNext()) {
                        $arSubExecutors[ mb_substr($rowSE['VALUE'], 0, mb_strpos($rowSE['VALUE'], ':')) ] = [
                            'VALUE'         => $rowSE['VALUE'],
                            'DESCRIPTION'   => $rowSE['~DESCRIPTION'],
                        ];
                    }

                    $arAddedNames = [];
                    $arRemovedNames = [];
                    foreach ($arNewAccomplice as $uId) {
                        if (false !== mb_strpos($uId, 'DEP')) {
                            $depId = str_replace('DEP', '', $uId);
                            if ($depId < 0) {
                                $findUid = ($depId*-1);
                                if (array_key_exists($findUid, $arSubExecutors)) {
                                    unset($arSubExecutors[ $findUid ]);
                                    $arRemovedNames[] = $this->ispolnitels[ $findUid ]['NAME'];
                                }
                            } elseif (!array_key_exists($depId, $arAccomplices)) {
                                $arSubExecutors[ $depId ] = [
                                    'VALUE'         => $depId,
                                    'DESCRIPTION'   => 0,
                                ];
                                $arAddedNames[] = $this->ispolnitels[ $depId ]['NAME'];
                            }
                        } else {
                            if ($uId < 0) {
                                $findUid = ($uId*-1);
                                if (array_key_exists($findUid, $arAccomplices)) {
                                    unset($arAccomplices[ $findUid ]);
                                    $arRemovedNames[] = $this->getUserFullName($findUid);
                                }
                            } elseif (!array_key_exists($uId, $arAccomplices)) {
                                $arAccomplices[ $uId ] = $uId;
                                $arAddedNames[] = $this->getUserFullName($uId);
                                /*
                                 * Добавить выбранного пользователя в исполнителей
                                 */
                                if (!in_array($uId, $this->permissions['ispolnitel_data']['PROPERTY_ISPOLNITELI_VALUE'])) {
                                    $arGroups = CUser::GetUserGroup($uId);
                                    $arGroups[] = 96;
                                    CUser::SetUserGroup($uId, $arGroups);
                                    $arExecutors = $this->permissions['ispolnitel_data']['PROPERTY_ISPOLNITELI_VALUE'];
                                    $arExecutors[] = $uId;
                                    $arExecutors = array_unique($arExecutors);
                                    CIBlockElement::SetPropertyValuesEx(
                                        $this->permissions['ispolnitel_data']['ID'],
                                        false,
                                        [
                                            'ISPOLNITELI' => $arExecutors,
                                        ]
                                    );
                                }
                            }
                        }
                    }

                    $message = '';
                    if (!empty($arAddedNames)) {
                        $message .= 'Добавлены: ' . implode(', ', $arAddedNames) . '<br/>';
                    }
                    if (!empty($arRemovedNames)) {
                        $message .= 'Удалены: ' . implode(', ', $arRemovedNames);
                    }
                    if (!empty($message)) {
                        $this->log(
                            $_REQUEST['detail'],
                            'Изменены соисполнители',
                            [
                                'METHOD'    => __METHOD__,
                                'MESSAGE'   => $message,
                                'REQUEST'   => $_REQUEST,
                            ]
                        );
                        $this->addToLog('Изменены соисполнители', $message);
                        $arAccomplices = array_filter($arAccomplices);
                        $arAccomplices = array_unique($arAccomplices);

                        foreach ($arSubExecutors as $key => $value) {
                            if (false === mb_strpos($value['VALUE'], ':')) {
                                $arSubExecutors[ $key ]['VALUE'] = $value['VALUE'] . ':' . $value['DESCRIPTION'];
                            }
                        }

                        CIBlockElement::SetPropertyValuesEx(
                            $_REQUEST['detail'],
                            false,
                            [
                                'ACCOMPLICES'       => $arAccomplices,
                                'SUBEXECUTOR'       => $arSubExecutors,
                                'DELEGATE_COMMENT'  => $_REQUEST['DELEGATE_COMMENT']??'',
                                'DELEGATE_SROK'     => $_REQUEST['DELEGATE_SROK']??false,
                            ]
                        );
                    }
                }
                LocalRedirect($redirecturl);
                break;
            case 'add_comment_ispolnitel':
                $currentUserId = $GLOBALS['USER']->GetID();
                if ($_REQUEST['subaction'] == 'return_work') {
                    if (empty($_REQUEST['RETURN_COMMENT'])) {
                        ShowError('Не указана причина возврата');
                        break;
                    }
                    $arComFilter = [
                        'ID'                => $_REQUEST['RETURN_ID'],
                        'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
                        'ACTIVE'            => 'Y',
                        'PROPERTY_TYPE'     => 1131,
                        'PROPERTY_PORUCH'   => $_REQUEST['detail'],
                    ];
                    $resCom = CIBlockElement::GetList(
                        [
                            'DATE_CREATE' => 'DESC'
                        ],
                        $arComFilter,
                        false,
                        false,
                        $this->arReportFields
                    );
                    if ($arComFields = $resCom->GetNext()) {
                        CIBlockElement::SetPropertyValuesEx(
                            $arComFields['ID'],
                            false,
                            [
                                'COMMENT'       => '(' . date('d.m.Y H:i:s') . ') [' . $this->getUserFullName($currentUserId) . '] ' . $_REQUEST['RETURN_COMMENT'],
                                'CURRENT_USER'  => $arComFields['PROPERTY_USER_VALUE'],
                                'STATUS'        => false,
                            ]
                        );
                        $this->log(
                            $_REQUEST['detail'],
                            'Отчет возвращен исполнителю',
                            [
                                'METHOD'    => __METHOD__,
                                'REQUEST'   => $_REQUEST,
                            ]
                        );
                        CIBlockElement::SetPropertyValuesEx(
                            $_REQUEST['detail'],
                            false,
                            [
                                'WORK_INTER_STATUS' => false,
                                'VIEWS'             => false,
                            ]
                        );
                    }
                } else {
                    $visaTypeCode = $this->arParams['COMMENT_ENUM']['VISA_TYPE'][ $_REQUEST['VISA_TYPE'] ]['EXTERNAL_ID'];
                    $arVisas = [];
                    foreach ($_REQUEST['VISA'] as $key => $value) {
                        if (empty($value)) {
                            unset($_REQUEST['VISA'][ $key ]);
                        } else {
                            $arVisas[] = $this->getUserFullName(str_replace(['UI', 'U'], '', $value));
                            if (false === mb_strpos($value, ':')) {
                                $visaCode = 'E';
                                if ($visaTypeCode == 'after' && $key > 0) {
                                    $visaCode = 'S';
                                }
                                $value = str_replace(['UI', 'U'], '', $value) . ':' . $visaCode . ':';
                            }
                            if ($_REQUEST['subaction'] == 'add_comment') {
                                [$userId, $status, $comment, $date] = explode(':', $value, 4);
                                $newStatus = 'E';
                                if ($visaTypeCode == 'after' && $key > 0) {
                                    $newStatus = 'S';
                                }
                                $value = implode(':', [$userId, $newStatus, $comment, $date]);
                            }
                            $_REQUEST['VISA'][ $key ] = $value;
                        }
                    }
                    $el = new CIBlockElement();

                    $arProp = [
                        'PORUCH'        => $_REQUEST['detail'],
                        'USER'          => $currentUserId,
                        'TYPE'          => 1131,
                        'DOCS'          => $_REQUEST['FILES_ISPOLN'],
                        'VISA'          => $_REQUEST['VISA'],
                        'VISA_TYPE'     => $_REQUEST['VISA_TYPE'],
                        'BROKEN_SROK'   => 'N',
                        'DATE_FACT'     => $_REQUEST['DATE_FACT'],
                        'SIGNER'        => $_REQUEST['SIGNER'] > 0 ? $_REQUEST['SIGNER'] : '',
                    ];

                    $arCurrentOrder = $obOrders->getById($_REQUEST['detail']);

                    if ($_REQUEST['sign_data_id'] != '') {
                        $arProp['ECP']      = $currentUserId;
                        $arProp['FILE_ECP'] = $_REQUEST['sign_data_id'];
                    }

                    $arLoadProductArray = [
                        'MODIFIED_BY'       => $currentUserId,
                        'IBLOCK_SECTION_ID' => false,
                        'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
                        'PROPERTY_VALUES'   => $arProp,
                        'NAME'              => $currentUserId . '-' . $_REQUEST['detail'] . '-' . date('d-m-Y_H:i:s'),
                        'ACTIVE'            => 'Y',
                        'PREVIEW_TEXT'      => $_REQUEST['DETAIL_TEXT'],
                        'DETAIL_TEXT'       => $_REQUEST['DETAIL_TEXT'],
                    ];

                    $bSendToSign = false;
                    if (empty($arLoadProductArray['PROPERTY_VALUES']['VISA'])) {
                        /* Если нет визирующих, отправить в папку на подпись. */
                        $bSendToSign = true;
                        CIBlockElement::SetPropertyValuesEx(
                            $_REQUEST['detail'],
                            false,
                            [
                                'WORK_INTER_STATUS' => $arParams['ENUM']['WORK_INTER_STATUS']['TOSIGN']['ID'],
                            ]
                        );
                        $arLoadProductArray['PROPERTY_VALUES']['STATUS'] = $arParams['COMMENT_ENUM']['STATUS']['TOSIGN']['ID'];
                    }

                    if ($_REQUEST['CURRENT_ID'] > 0) {
                        $resComment = CIBlockElement::GetList(
                            [
                                'DATE_CREATE' => 'DESC'
                            ],
                            [
                                'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
                                'PROPERTY_PORUCH'   => (int)$_REQUEST['detail'],
                                'ID'                => $_REQUEST['CURRENT_ID']
                            ],
                            false,
                            false,
                            $this->arReportFields
                        );
                        if ($arCurrentFields = $resComment->GetNext()) {
                            $arUpdateFields = [
                                'MODIFIED_BY'   => $currentUserId,
                                'PREVIEW_TEXT'  => $_REQUEST['DETAIL_TEXT'],
                                'DETAIL_TEXT'   => $_REQUEST['DETAIL_TEXT'],
                            ];
                            $el->Update($arCurrentFields['ID'], $arUpdateFields);

                            $bDeleteVisa = false;

                            $findField = '~PREVIEW_TEXT';
                            if (!empty($arCurrentFields['~DETAIL_TEXT'])) {
                                $findField = '~DETAIL_TEXT';
                            }
                            if (crc32($arCurrentFields[ $findField ]) != crc32($_REQUEST['DETAIL_TEXT'])) {
                                $bDeleteVisa = true;
                            }

                            CIBlockElement::SetPropertyValuesEx(
                                $arCurrentFields['PROPERTY_PORUCH_VALUE'],
                                false,
                                [
                                    'VIEWS' => false,
                                ]
                            );
                            $updateVisaType = $arCurrentFields['PROPERTY_VISA_TYPE_ENUM_ID'];
                            if (isset($_REQUEST['VISA_TYPE'])) {
                                $updateVisaType = $_REQUEST['VISA_TYPE'];
                            }

                            if ($updateVisaType != $arCurrentFields['PROPERTY_VISA_TYPE_ENUM_ID']) {
                                $bDeleteVisa = true;
                            }

                            $visaTypeCode = $this->arParams['COMMENT_ENUM']['VISA_TYPE'][ $updateVisaType ]['EXTERNAL_ID'];
                            $arSendVisaMsg = [];
                            $arCurrentVisa = [];
                            foreach ($_REQUEST['VISA'] as $visaRow) {
                                [$newuserId, $newstatus, $newcomment, $newdate] = explode(':', $visaRow, 4);
                                foreach ($arCurrentFields['PROPERTY_VISA_VALUE'] as $visaKey => $visaRow2) {
                                    [$userId, $status, $comment, $date] = explode(':', $visaRow2, 4);
                                    if ($userId == $newuserId) {
                                        $visaRow = $visaRow2;
                                    }
                                }
                                $arCurrentVisa[] = $visaRow;
                            }

                            $prevStatus = '';
                            $arEmptyVisa = [];
                            foreach ($arCurrentVisa as $visaKey => $visaRow) {
                                [$userId, $status, $comment, $date] = explode(':', $visaRow, 4);
                                $newStatus = 'E';
                                if (!$bDeleteVisa) {
                                    $newStatus = $status;
                                }
                                if (in_array($status, ['E', 'S'])) {
                                    /*
                                     * Если тип визирования = по порядку,
                                     * то уведомление отправить только 1 визирующему
                                     */
                                    if ($visaTypeCode == 'after') {
                                        if (empty($arSendVisaMsg)) {
                                            $arSendVisaMsg[] = $userId;
                                        }
                                        if ($visaKey > 0) {
                                            $newStatus = 'S';
                                        }
                                        if ($prevStatus == 'Y') {
                                            $newStatus = 'E';
                                        }
                                    }
                                } elseif ($bDeleteVisa && $visaTypeCode == 'after' && $visaKey > 0) {
                                    $newStatus = 'S';
                                } elseif ($bDeleteVisa) {
                                    $arSendVisaMsg[] = $userId;
                                }
                                $arCurrentVisa[ $visaKey ] = implode(':', [$userId, $newStatus, $comment, $date]);
                                $prevStatus = $status;
                                if ($status == 'E') {
                                    $arEmptyVisa[] = $userId;
                                }
                            }

                            if (!empty($arEmptyVisa)) {
                                if (!empty($arSendVisaMsg)) {
                                    Notify::send(
                                        [$arCurrentFields['PROPERTY_PORUCH_VALUE']],
                                        'VISA',
                                        $arSendVisaMsg
                                    );
                                }
                                $setReportStatus = 'TOVISA';
                            } else {
                                $setReportStatus = 'TOSIGN';
                            }

                            CIBlockElement::SetPropertyValuesEx(
                                $arCurrentFields['PROPERTY_PORUCH_VALUE'],
                                false,
                                [
                                    'WORK_INTER_STATUS' => $arParams['ENUM']['WORK_INTER_STATUS'][ $setReportStatus ]['ID'],
                                ]
                            );
                            CIBlockElement::SetPropertyValuesEx(
                                $arCurrentFields['ID'],
                                false,
                                [
                                    'STATUS' => $arParams['COMMENT_ENUM']['STATUS'][ $setReportStatus ]['ID'],
                                ]
                            );

                            if (empty($arCurrentVisa)) {
                                $arCurrentVisa = false;
                            }

                            CIBlockElement::SetPropertyValuesEx(
                                $arCurrentFields['ID'],
                                false,
                                [
                                    'DOCS'      => $_REQUEST['FILES_ISPOLN'],
                                    'VISA'      => $arCurrentVisa,
                                    'DATE_FACT' => $_REQUEST['DATE_FACT'],
                                    'VISA_TYPE' => $updateVisaType,
                                    'SIGNER'    => $_REQUEST['SIGNER'] > 0 ? $_REQUEST['SIGNER'] : false,
                                ]
                            );
                            $findField = '~PREVIEW_TEXT';
                            if (!empty($arCurrentFields['~DETAIL_TEXT'])) {
                                $findField = '~DETAIL_TEXT';
                            }
                            $comment = strip_tags($arCurrentFields[ $findField ]) . ' &rarr; ' . strip_tags($_REQUEST['DETAIL_TEXT']);
                            $this->log(
                                $arCurrentFields['PROPERTY_PORUCH_VALUE'],
                                'Изменен комментарий исполнителя',
                                [
                                    'METHOD'        => __METHOD__,
                                    'REQUEST'       => $_REQUEST,
                                    'SendVisaMsg'   => $arSendVisaMsg,
                                    'CurrentVisa'   => $arCurrentVisa,
                                    'VisaType'      => $updateVisaType,
                                ]
                            );
                            $this->addToLog('Изменен комментарий исполнителя', $comment, $arCurrentFields['PROPERTY_PORUCH_VALUE']);
                            LocalRedirect($redirecturl);
                        }
                        echo 'Error updating comment';
                    } else {
                        /*
                         * Подписывает по-умолчанию руководитель
                         */
                        $iIspolnitelRukl = $this->ispolnitels[ $arCurrentOrder['PROPERTY_ISPOLNITEL_VALUE'] ]['PROPERTY_RUKOVODITEL_VALUE'];
                        $arIspolnitelSigns = array_merge(
                            [$this->ispolnitels[ $arCurrentOrder['PROPERTY_ISPOLNITEL_VALUE'] ]['PROPERTY_RUKOVODITEL_VALUE']],
                            $this->ispolnitels[ $arCurrentOrder['PROPERTY_ISPOLNITEL_VALUE'] ]['PROPERTY_ZAMESTITELI_VALUE']
                        );
                        $iSignUser = $iIspolnitelRukl;
                        $arDelegator = [];

                        /*
                         * Если поручение делегировано сверху, то проверим тип того кто делегировал
                         */
                        if (
                            !empty($arCurrentOrder['PROPERTY_DELEGATION_VALUE']) &&
                            (int)$arCurrentOrder['PROPERTY_DELEGATION_VALUE'][0] > 0
                        ) {
                            $arDelegator = $this->ispolnitels[ $arCurrentOrder['PROPERTY_DELEGATION_VALUE'][0] ];

                            /*
                             * Если зампред - то подписывает сам
                             */
                            if ($arDelegator['PROPERTY_TYPE_CODE'] == 'zampred') {
                                $iSignUser = $arDelegator['PROPERTY_RUKOVODITEL_VALUE'];
                                $arIspolnitelSigns = [
                                    $iSignUser
                                ];
                            }
                        }

                        if ($PRODUCT_ID = $el->Add($arLoadProductArray)) {
                            if (
                                !empty($arDelegator) &&
                                $bSendToSign &&
                                !empty($arDelegator['PROPERTY_IMPLEMENTATION_VALUE'])
                            ) {
                                $arSendMsgUsers = $arDelegator['PROPERTY_IMPLEMENTATION_VALUE'];

                                $arSendDelegId = [
                                    250900, /* Якушкина Г.И. */
                                    250902, /* Гремякова О.П. */
                                ];
                                if (in_array($arDelegator['ID'], $arSendDelegId)) {
                                    $arSendMsgUsers[] = $arDelegator['PROPERTY_RUKOVODITEL_VALUE'];
                                }
                                Notify::send(
                                    [$_REQUEST['detail']],
                                    'SIGN',
                                    $arSendMsgUsers
                                );
                            }

                            $setStatus = 'TOSIGN';
                            if (!empty($arLoadProductArray['PROPERTY_VALUES']['VISA'])) {
                                $arSendVisaMsg = [];
                                foreach ($arLoadProductArray['PROPERTY_VALUES']['VISA'] as $visaRow) {
                                    [$userId, $status] = explode(':', $visaRow, 4);
                                    if (in_array($status, ['E', 'S'])) {
                                        $arSendVisaMsg[] = $userId;
                                        /* Если тип визирования = по порядку, то уведомление отправить только 1 визирующему. */
                                        if ($visaTypeCode == 'after') {
                                            break;
                                        }
                                    }
                                }

                                if (!empty($arSendVisaMsg)) {
                                    $setStatus = 'TOVISA';
                                    Notify::send(
                                        [$arLoadProductArray['PROPERTY_VALUES']['PORUCH']],
                                        'VISA',
                                        $arSendVisaMsg
                                    );
                                } else {
                                    $setStatus = 'TOSIGN';
                                }
                                CIBlockElement::SetPropertyValuesEx(
                                    $arLoadProductArray['PROPERTY_VALUES']['PORUCH'],
                                    false,
                                    [
                                        'WORK_INTER_STATUS' => $arParams['ENUM']['WORK_INTER_STATUS'][ $setStatus ]['ID']
                                    ]
                                );
                                CIBlockElement::SetPropertyValuesEx(
                                    $PRODUCT_ID,
                                    false,
                                    [
                                        'STATUS' => $arParams['COMMENT_ENUM']['STATUS'][ $setStatus ]['ID'],
                                    ]
                                );
                            }

                            $arComFilter = [
                                '!ID'               => $PRODUCT_ID,
                                'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
                                'ACTIVE'            => 'Y',
                                'PROPERTY_TYPE'     => 1131,
                                'PROPERTY_PORUCH'   => $_REQUEST['detail'],
                            ];
                            $resCom = CIBlockElement::GetList(
                                [
                                    'DATE_CREATE' => 'DESC'
                                ],
                                $arComFilter,
                                false,
                                false,
                                $this->arReportFields
                            );
                            while ($arComFields = $resCom->GetNext()) {
                                $arNewVisa = [];
                                foreach ($arComFields['PROPERTY_VISA_VALUE'] as $row) {
                                    $arNewVisa[] = str_replace(':E:', ':S:', $row);
                                }
                                /*
                                 * Убрать визирующих у предыдущих отчётов
                                 * Убрать текущего пользователя у предыдущих отчётов
                                 */
                                CIBlockElement::SetPropertyValuesEx(
                                    $arComFields['ID'],
                                    false,
                                    [
                                        'VISA'          => empty($arNewVisa) ? false : $arNewVisa,
                                        'CURRENT_USER'  => false,
                                        'STATUS'        => false,
                                    ]
                                );
                            }

                            if (
                                $_REQUEST['subaction'] == 'send_to_control' &&
                                (
                                    in_array($currentUserId, $arIspolnitelSigns) ||
                                    $currentUserId == $arDelegator['PROPERTY_RUKOVODITEL_VALUE'] ||
                                    (
                                        $this->permissions['controler'] &&
                                        $obOrders->isExternal($arCurrentOrder['ID'])
                                    )
                                )
                            ) {
                                if (
                                    $arDelegator['PROPERTY_TYPE_CODE'] == 'gubernator' &&
                                    $currentUserId != $arDelegator['PROPERTY_RUKOVODITEL_VALUE']
                                ) {
                                    CIBlockElement::SetPropertyValuesEx(
                                        $_REQUEST['detail'],
                                        false,
                                        [
                                            'WORK_INTER_STATUS' => $arParams['ENUM']['WORK_INTER_STATUS']['TOVISA']['ID'],
                                        ]
                                    );
                                    CIBlockElement::SetPropertyValuesEx(
                                        $PRODUCT_ID,
                                        false,
                                        [
                                            'CURRENT_USER'  => $arDelegator['PROPERTY_RUKOVODITEL_VALUE'],
                                            'STATUS'        => $arParams['COMMENT_ENUM']['STATUS']['TOVISA']['ID'],
                                        ]
                                    );
                                    $arSendMsgUsers = $arDelegator['PROPERTY_IMPLEMENTATION_VALUE'];

                                    $arSendDelegId = [
                                        250900, /* Якушкина Г.И. */
                                        250902, /* Гремякова О.П. */
                                    ];
                                    if (in_array($arDelegator['ID'], $arSendDelegId)) {
                                        $arSendMsgUsers[] = $arDelegator['PROPERTY_RUKOVODITEL_VALUE'];
                                    }
                                    Notify::send(
                                        [$arLoadProductArray['PROPERTY_VALUES']['PORUCH']],
                                        'VISA',
                                        $arSendMsgUsers
                                    );
                                } else {
                                    /*
                                     * Нарушение срока представления отчета фиксируется только
                                     * по главному исполнителю, когда он нажал на кнопку "Отправить на контроль"
                                     */
                                    if (strtotime($_REQUEST['CURRENT_DATE_ISPOLN'] . ' 23:59:59') < time()) {
                                        CIBlockElement::SetPropertyValuesEx(
                                            $PRODUCT_ID,
                                            false,
                                            [
                                                'BROKEN_SROK' => 'Y',
                                            ]
                                        );
                                    }

                                    $this->log(
                                        $_REQUEST['detail'],
                                        'Отправлено на контроль',
                                        [
                                            'METHOD'    => __METHOD__,
                                            'REQUEST'   => $_REQUEST,
                                        ]
                                    );
                                    $this->addToLog('Отправлено на контроль', strip_tags($_REQUEST['DETAIL_TEXT']));
                                    CIBlockElement::SetPropertyValuesEx(
                                        $_REQUEST['detail'],
                                        false,
                                        [
                                            'ACTION'            => Settings::$arActions['CONTROL'],
                                            'DATE_FACT_ISPOLN'  => date('d.m.Y'),
                                            'CONTROLER_STATUS'  => $this->arControlerStatuses['on_beforing']['ID'],
                                            'WORK_INTER_STATUS' => false,
                                        ]
                                    );
                                    $arNewVisa = [];
                                    foreach ($arLoadProductArray['PROPERTY_VALUES']['VISA'] as $row) {
                                        $arNewVisa[] = str_replace(':E:', ':S:', $row);
                                    }
                                    /*
                                     * Убрать текущего пользователя и возможность визирования у последнего отчёта
                                     */
                                    CIBlockElement::SetPropertyValuesEx(
                                        $PRODUCT_ID,
                                        false,
                                        [
                                            'CURRENT_USER'  => false,
                                            'STATUS'        => false,
                                            'VISA'          => empty($arNewVisa) ? false : $arNewVisa,
                                        ]
                                    );
                                }
                            } else {
                                $comment = strip_tags($_REQUEST['DETAIL_TEXT']);
                                if (!empty($arVisas)) {
                                    $comment .= '<br/><br/>Отправлено на визу: ' . implode(', ', $arVisas);
                                }
                                $this->addToLog('Добавлен комментарий исполнителя', $comment);

                                $this->log(
                                    $_REQUEST['detail'],
                                    'Добавлен комментарий исполнителя',
                                    [
                                        'METHOD'    => __METHOD__,
                                        'REQUEST'   => $_REQUEST,
                                    ]
                                );
                                CIBlockElement::SetPropertyValuesEx(
                                    $PRODUCT_ID,
                                    false,
                                    [
                                        'CURRENT_USER'  => $iSignUser,
                                        'ECP'           => false,
                                        'FILE_ECP'      => false,
                                    ]
                                );

                                if (empty($arLoadProductArray['PROPERTY_VALUES']['VISA'])) {
                                    CIBlockElement::SetPropertyValuesEx(
                                        $_REQUEST['detail'],
                                        false,
                                        [
                                            'WORK_INTER_STATUS' => $arParams['ENUM']['WORK_INTER_STATUS']['TOSIGN']['ID'],
                                        ]
                                    );
                                    CIBlockElement::SetPropertyValuesEx(
                                        $PRODUCT_ID,
                                        false,
                                        [
                                            'STATUS' => $arParams['COMMENT_ENUM']['STATUS']['TOSIGN']['ID'],
                                        ]
                                    );
                                } else {
                                    CIBlockElement::SetPropertyValuesEx(
                                        $_REQUEST['detail'],
                                        false,
                                        [
                                            'WORK_INTER_STATUS' => $arParams['ENUM']['WORK_INTER_STATUS'][ $setStatus ]['ID'],
                                        ]
                                    );
                                    CIBlockElement::SetPropertyValuesEx(
                                        $PRODUCT_ID,
                                        false,
                                        [
                                            'STATUS' => $arParams['COMMENT_ENUM']['STATUS'][ $setStatus ]['ID'],
                                        ]
                                    );
                                }
                            }

                            CIBlockElement::SetPropertyValuesEx(
                                $_REQUEST['detail'],
                                false,
                                [
                                    'VIEWS' => false,
                                ]
                            );
                            LocalRedirect($redirecturl);
                        } else {
                            echo 'Error: ' . $el->LAST_ERROR;
                        }
                    }
                }
                break;
            case 'add_comment_ispolnitel_new':
                $bSendToSign = $_REQUEST['send_report_to_sign'] == 1;
                $bSendToControl = $_REQUEST['send_report_to_control'] == 1;
                $currentUserId = $GLOBALS['USER']->GetID();
                $visaTypeCode = $this->arParams['COMMENT_ENUM']['VISA_TYPE'][ $_REQUEST['VISA_TYPE'] ]['EXTERNAL_ID'];
                $arVisas = [];

                if (!isset($_REQUEST['VISA']) && !empty($_REQUEST['VISA1'])) {
                    $_REQUEST['VISA'] = $_REQUEST['VISA1'];
                }

                foreach ($_REQUEST['VISA'] as $key => $value) {
                    if (empty($value)) {
                        unset($_REQUEST['VISA'][ $key ]);
                    } else {
                        $arVisas[] = $this->getUserFullName(str_replace(['UI', 'U'], '', $value));
                        if (false === mb_strpos($value, ':')) {
                            $visaCode = 'E';
                            if ($visaTypeCode == 'after' && $key > 0) {
                                $visaCode = 'S';
                            }
                            $value = str_replace(['UI', 'U'], '', $value) . ':' . $visaCode . ':';
                        }
                        if ($_REQUEST['subaction'] == 'add_comment') {
                            [$userId, $status, $comment, $date] = explode(':', $value, 4);
                            $newStatus = 'E';
                            if ($visaTypeCode == 'after' && $key > 0) {
                                $newStatus = 'S';
                            }
                            $value = implode(':', [$userId, $newStatus, $comment, $date]);
                        }
                        $_REQUEST['VISA'][ $key ] = $value;
                    }
                }

                if (
                    $_REQUEST['VISA_TYPE'] == $this->arParams['COMMENT_ENUM']['VISA_TYPE']['after']['ID'] &&
                    !empty($_REQUEST['VISA2'])
                ) {
                    $_REQUEST['VISA'][] = 'SIGN' . (int)$_REQUEST['SIGNER'] . ':E';
                    foreach ($_REQUEST['VISA2'] as $key => $value) {
                        if (empty($value)) {
                            unset($_REQUEST['VISA2'][ $key ]);
                        } else {
                            $arVisas[] = $this->getUserFullName(str_replace(['UI', 'U'], '', $value));
                            if (false === mb_strpos($value, ':')) {
                                $visaCode = 'E';
                                if ($visaTypeCode == 'after') {
                                    $visaCode = 'S';
                                }
                                $value = str_replace(['UI', 'U'], '', $value) . ':' . $visaCode . ':';
                            }
                            if ($_REQUEST['subaction'] == 'add_comment') {
                                [$userId, $status, $comment, $date] = explode(':', $value, 4);
                                $newStatus = 'E';
                                if ($visaTypeCode == 'after') {
                                    $newStatus = 'S';
                                }
                                $value = implode(':', [$userId, $newStatus, $comment, $date]);
                            }
                            $_REQUEST['VISA'][] = $value;
                        }
                    }
                }

                if (!empty($_REQUEST['DATE_FACT'])) {
                    if (
                        !in_array(
                            (int)date('Y', strtotime($_REQUEST['DATE_FACT'])),
                            [
                                (int)date('Y'),
                                (int)date('Y', strtotime('-1 YEAR')),
                            ]
                        )
                    ) {
                        $_REQUEST['DATE_FACT'] = date('d.m.Y');
                    }
                }

                $el = new CIBlockElement();
                $arLoadProductArray = [
                    'MODIFIED_BY'       => $currentUserId,
                    'IBLOCK_SECTION_ID' => false,
                    'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
                    'PROPERTY_VALUES'   => [
                        'PORUCH'        => $_REQUEST['detail'],
                        'USER'          => $currentUserId,
                        'TYPE'          => 1131,
                        'DOCS'          => $_REQUEST['FILES_ISPOLN'] ?? false,
                        'VISA'          => $_REQUEST['VISA'] ?? false,
                        'VISA_TYPE'     => $_REQUEST['VISA_TYPE'] ?? false,
                        'BROKEN_SROK'   => 'N',
                        'DATE_FACT'     => $_REQUEST['DATE_FACT'],
                        'SIGNER'        => $_REQUEST['SIGNER'] > 0 ? $_REQUEST['SIGNER'] : '',
                        'STATUS'        => $arParams['COMMENT_ENUM']['STATUS']['DRAFT']['ID'],
                        'CURRENT_USER'  => $currentUserId,
                        'ECP'           => false,
                        'FILE_ECP'      => false,
                    ],
                    'NAME'              => $currentUserId . '-' . $_REQUEST['detail'] . '-' . date('d-m-Y_H:i:s'),
                    'ACTIVE'            => 'Y',
                    'PREVIEW_TEXT'      => $_REQUEST['DETAIL_TEXT'],
                    'DETAIL_TEXT'       => $_REQUEST['DETAIL_TEXT'],
                ];

                if ($_REQUEST['CURRENT_ID'] > 0) {
                    $resComment = CIBlockElement::GetList(
                        [
                            'DATE_CREATE' => 'DESC'
                        ],
                        [
                            'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
                            'PROPERTY_PORUCH'   => (int)$_REQUEST['detail'],
                            'ID'                => $_REQUEST['CURRENT_ID']
                        ],
                        false,
                        false,
                        $this->arReportFields
                    );
                    if ($arCurrentFields = $resComment->GetNext()) {
                        $arUpdateFields = [
                            'MODIFIED_BY'   => $currentUserId,
                            'PREVIEW_TEXT'  => $_REQUEST['DETAIL_TEXT'],
                            'DETAIL_TEXT'   => $_REQUEST['DETAIL_TEXT'],
                        ];
                        $el->Update($arCurrentFields['ID'], $arUpdateFields);

                        CIBlockElement::SetPropertyValuesEx(
                            $arCurrentFields['PROPERTY_PORUCH_VALUE'],
                            false,
                            [
                                'VIEWS' => false,
                            ]
                        );
                        $updateVisaType = $arCurrentFields['PROPERTY_VISA_TYPE_ENUM_ID'];
                        if (isset($_REQUEST['VISA_TYPE'])) {
                            $updateVisaType = $_REQUEST['VISA_TYPE'];
                        }

                        $arCurrentVisaUsers = [];
                        $arNewVisaUsers = [];
                        foreach ($arCurrentFields['PROPERTY_VISA_VALUE'] as $visaKey => $visaRow2) {
                            [$userId, $status, $comment, $date] = explode(':', $visaRow2, 4);
                            $arCurrentVisaUsers[] = $userId;
                        }

                        $visaTypeCode = $this->arParams['COMMENT_ENUM']['VISA_TYPE'][ $updateVisaType ]['EXTERNAL_ID'];
                        $arSendVisaMsg = [];
                        $arCurrentVisa = [];

                        foreach ($_REQUEST['VISA'] as $visaRow) {
                            [$newuserId, $newstatus, $newcomment, $newdate] = explode(':', $visaRow, 4);
                            foreach ($arCurrentFields['PROPERTY_VISA_VALUE'] as $visaKey => $visaRow2) {
                                [$userId, $status, $comment, $date] = explode(':', $visaRow2, 4);
                                if ($userId == $newuserId) {
                                    $visaRow = $visaRow2;
                                }
                            }
                            if (!in_array($newuserId, $arCurrentVisaUsers)) {
                                $arNewVisaUsers[] = $newuserId;
                            }
                            $arCurrentVisa[] = $visaRow;
                        }

                        $bDeleteVisa = false;

                        $findField = '~PREVIEW_TEXT';
                        if (!empty($arCurrentFields['~DETAIL_TEXT'])) {
                            $findField = '~DETAIL_TEXT';
                        }
                        if (crc32($arCurrentFields[ $findField ]) != crc32($_REQUEST['DETAIL_TEXT'])) {
                            $bDeleteVisa = true;
                        }

                        $arOrder = (new Orders())->getById($arCurrentFields['PROPERTY_PORUCH_VALUE']);

                        $arMayEditReports = array_merge(
                            [$this->ispolnitels[ $arOrder['PROPERTY_ISPOLNITEL_VALUE'] ]['PROPERTY_RUKOVODITEL_VALUE']],
                            $this->ispolnitels[ $arOrder['PROPERTY_ISPOLNITEL_VALUE'] ]['PROPERTY_ZAMESTITELI_VALUE']
                        );

                        if (in_array($currentUserId, $arMayEditReports)) {
                            $bDeleteVisa = false;
                        }

                        if ($updateVisaType != $arCurrentFields['PROPERTY_VISA_TYPE_ENUM_ID']) {
                            $bDeleteVisa = true;
                        }

                        $prevStatus = '';
                        foreach ($arCurrentVisa as $visaKey => $visaRow) {
                            [$userId, $status, $comment, $date] = explode(':', $visaRow, 4);
                            if ($status == 'N') {
                                $bDeleteVisa = true;
                            }
                        }
                        $arEmptyVisas = [];
                        foreach ($arCurrentVisa as $visaKey => $visaRow) {
                            [$userId, $status, $comment, $date] = explode(':', $visaRow, 4);
                            $newStatus = 'E';
                            if (!$bDeleteVisa) {
                                $newStatus = $status;
                            }

                            if (in_array($status, ['E', 'S'])) {
                                /*
                                 * Если тип визирования = по порядку,
                                 * то уведомление отправить только 1 визирующему
                                 */
                                if ($visaTypeCode == 'after') {
                                    if (empty($arSendVisaMsg)) {
                                        $arSendVisaMsg[] = $userId;
                                    }
                                    if ($visaKey > 0) {
                                        $newStatus = 'S';
                                    }
                                    if ($prevStatus == 'Y') {
                                        $newStatus = 'E';
                                    }
                                } elseif (in_array($userId, $arNewVisaUsers)) {
                                    $arSendVisaMsg[] = $userId;
                                }
                            } elseif ($bDeleteVisa && $visaTypeCode == 'after' && $visaKey > 0) {
                                $newStatus = 'S';
                            } elseif ($bDeleteVisa) {
                                $arSendVisaMsg[] = $userId;
                            } elseif (in_array($userId, $arNewVisaUsers)) {
                                $arSendVisaMsg[] = $userId;
                            }

                            $arCurrentVisa[ $visaKey ] = implode(':', [$userId, $newStatus, $comment, $date]);
                            if (in_array($newStatus, ['E', 'S'])) {
                                $arEmptyVisas[] = $userId;
                            }
                            $prevStatus = $status;
                        }

                        if (empty($arCurrentVisa)) {
                            $arCurrentVisa = false;
                        }

                        $setStatus = $arParams['COMMENT_ENUM']['STATUS'][ $arCurrentFields['PROPERTY_STATUS_ENUM_ID'] ]['XML_ID'];
                        if (!empty($arEmptyVisas)) {
                            $setStatus = 'TOVISA';
                        } elseif ($setStatus != 'DRAFT') {
                            $setStatus = 'TOSIGN';
                        }

                        CIBlockElement::SetPropertyValuesEx(
                            $arCurrentFields['ID'],
                            false,
                            [
                                'DOCS'      => $_REQUEST['FILES_ISPOLN'],
                                'VISA'      => $arCurrentVisa,
                                'DATE_FACT' => $_REQUEST['DATE_FACT'],
                                'VISA_TYPE' => $updateVisaType,
                                'SIGNER'    => $_REQUEST['SIGNER'] > 0 ? $_REQUEST['SIGNER'] : false,
                                'STATUS'    => $arParams['COMMENT_ENUM']['STATUS'][ $setStatus ]['ID'],
                            ]
                        );
                        CIBlockElement::SetPropertyValuesEx(
                            $arCurrentFields['PROPERTY_PORUCH_VALUE'],
                            false,
                            [
                                'WORK_INTER_STATUS' => $arParams['ENUM']['WORK_INTER_STATUS'][ $setStatus ]['ID'],
                            ]
                        );

                        $this->log(
                            $arCurrentFields['PROPERTY_PORUCH_VALUE'],
                            'Изменен комментарий исполнителя',
                            [
                                'METHOD'        => __METHOD__,
                                'REQUEST'       => $_REQUEST,
                                'CurrentVisa'   => $arCurrentVisa,
                                'VisaType'      => $updateVisaType,
                            ]
                        );
                        $reportId = $arCurrentFields['ID'];
                        $orderId = $arCurrentFields['PROPERTY_PORUCH_VALUE'];

                        if (!empty($arSendVisaMsg)) {
                            Notify::send(
                                [$arCurrentFields['PROPERTY_PORUCH_VALUE']],
                                'VISA',
                                $arSendVisaMsg
                            );
                        }

                        if (!$bSendToSign && !$bSendToControl) {
                            LocalRedirect($redirecturl);
                        }
                    }
                    echo 'Error updating comment';
                } else {
                    if ($bSendToControl) {
                        $arLoadProductArray['PROPERTY_VALUES']['STATUS'] = false;
                    }
                    if ($reportId = $el->Add($arLoadProductArray)) {
                        $orderId = (int)$_REQUEST['detail'];
                        if (!$bSendToSign && !$bSendToControl) {
                            LocalRedirect($redirecturl);
                        }
                    } else {
                        echo 'Error: ' . $el->LAST_ERROR;
                        $bSendToSign = false;
                        $bSendToControl = false;
                    }
                }
                if ($bSendToSign && $reportId && $orderId) {
                    require(__DIR__ . '/ajax.php');
                    AjaxController::sendToSignAction($reportId, $orderId);
                    LocalRedirect($redirecturl);
                }
                if ($bSendToControl && $reportId && $orderId) {
                    require(__DIR__ . '/ajax.php');
                    AjaxController::sendToControlAction($orderId, $reportId);
                    LocalRedirect($redirecturl);
                }
                break;
            case 'add_comment_accomplience':
                $el = new CIBlockElement();

                // $arAccomplices = [];
                // $res = CIBlockElement::GetProperty(
                //     Settings::$iblockId['ORDERS'],
                //     $_REQUEST['detail'],
                //     'sort',
                //     'asc',
                //     [
                //         'CODE' => 'ACCOMPLICES'
                //     ]
                // );
                // while ($row = $res->GetNext()) {
                //     $arAccomplices[ $row['VALUE'] ] = $row['VALUE'];
                // }

                foreach ($_REQUEST['VISA'] as $key => $value) {
                    if (empty($value)) {
                        unset($_REQUEST['VISA'][ $key ]);
                    } else {
                        if (false === mb_strpos($value, ':')) {
                            $value = str_replace(['UI', 'U'], '', $value) . ':E:';
                        }
                        if ($_REQUEST['subaction'] == 'add_comment') {
                            [$userId, $status, $comment, $date] = explode(':', $value, 4);
                            $value = implode(':', [$userId, 'E', $comment, $date]);
                        }
                        $_REQUEST['VISA'][ $key ] = $value;
                    }
                }
                if (empty($_REQUEST['VISA'])) {
                    $_REQUEST['VISA'] = false;
                }

                $arProp = [
                    'PORUCH'        => $_REQUEST['detail'],
                    'USER'          => $USER->GetID(),
                    'TYPE'          => $this->arParams['COMMENT_ENUM']['TYPE']['accomplience']['ID'],
                    'DOCS'          => $_REQUEST['FILES_ISPOLN'],
                    'BROKEN_SROK'   => 'N',
                    'VISA'          => $_REQUEST['VISA'],
                ];
                if (
                    isset($_REQUEST['PROPERTY_SUBEXECUTOR_DATE_VALUE']) &&
                    strtotime($_REQUEST['PROPERTY_SUBEXECUTOR_DATE_VALUE'] . ' 23:59:59') < time()
                ) {
                    $arProp['BROKEN_SROK'] = 'Y';
                }

                $arLoadProductArray = [
                    'MODIFIED_BY'       => $USER->GetID(),
                    'IBLOCK_SECTION_ID' => false,
                    'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
                    'PROPERTY_VALUES'   => $arProp,
                    'NAME'              => $USER->GetID() . '-' . $_REQUEST['detail'] . '-' . date('d-m-Y_H:i:s'),
                    'ACTIVE'            => 'Y',
                    'PREVIEW_TEXT'      => $_REQUEST['DETAIL_TEXT'],
                    'DETAIL_TEXT'       => $_REQUEST['DETAIL_TEXT'],
                ];

                if ($_REQUEST['CURRENT_ID'] > 0) {
                    $resComment = CIBlockElement::GetList(
                        [
                            'DATE_CREATE' => 'DESC'
                        ],
                        [
                            'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
                            'PROPERTY_PORUCH'   => (int)$_REQUEST['detail'],
                            'ID'                => $_REQUEST['CURRENT_ID']
                        ],
                        false,
                        false,
                        $this->arReportFields
                    );
                    if ($arCurrentFields = $resComment->GetNext()) {
                        $arUpdateFields = [
                            'MODIFIED_BY'   => $USER->GetID(),
                            'PREVIEW_TEXT'  => $_REQUEST['DETAIL_TEXT'],
                            'DETAIL_TEXT'   => $_REQUEST['DETAIL_TEXT'],
                        ];
                        $el->Update($arCurrentFields['ID'], $arUpdateFields);

                        $arNewVisaUsers = [];
                        if (!empty($_REQUEST['VISA'])) {
                            $arCurrentVisa = [];
                            foreach ($arCurrentFields['PROPERTY_VISA_VALUE'] as $visaRow) {
                                [$userId, $status, $comment, $date] = explode(':', $visaRow, 4);
                                $arCurrentVisa[ $userId ] = [
                                    'STATUS'    => $status,
                                    'ROW'       => $visaRow,
                                ];
                            }
                            foreach ($_REQUEST['VISA'] as $visaRow) {
                                [$userId, $status, $comment, $date] = explode(':', $visaRow, 4);
                                if (!array_key_exists($userId, $arCurrentVisa)) {
                                    $arNewVisaUsers[] = $userId;
                                }
                            }
                        }

                        if ($_REQUEST['FILES_ISPOLN']) {
                            $arDocs = $_REQUEST['FILES_ISPOLN'];
                        } else {
                            $arDocs = $_REQUEST['FILES_COMMENT_CONTROLER'];
                        }

                        CIBlockElement::SetPropertyValuesEx(
                            $arCurrentFields['ID'],
                            false,
                            [
                                'DOCS'  => $arDocs,
                                'VISA'  => $_REQUEST['VISA'],
                            ]
                        );

                        $findField = '~PREVIEW_TEXT';
                        if (!empty($arCurrentFields['~DETAIL_TEXT'])) {
                            $findField = '~DETAIL_TEXT';
                        }

                        $comment = strip_tags($arCurrentFields[ $findField ]) . ' &rarr; ' . strip_tags($_REQUEST['DETAIL_TEXT']);
                        $this->addToLog('Изменен комментарий соисполнителя от ' . $arCurrentFields['DATE_CREATE'], $comment, $arCurrentFields['PROPERTY_PORUCH_VALUE']);
                        $this->log(
                            $arCurrentFields['PROPERTY_PORUCH_VALUE'],
                            'Изменен комментарий соисполнителя',
                            [
                                'METHOD'    => __METHOD__,
                                'REQUEST'   => $_REQUEST,
                            ]
                        );
                        if (!empty($arNewVisaUsers)) {
                            Notify::send(
                                [$_REQUEST['detail']],
                                'VISA_ACCOMPLIENCE',
                                $arNewVisaUsers
                            );
                            // $arAccomplices = array_merge($arAccomplices, $arNewVisaUsers);
                            // $arAccomplices = array_filter($arAccomplices);
                            // $arAccomplices = array_unique($arAccomplices);
                            // CIBlockElement::SetPropertyValuesEx(
                            //     $_REQUEST['detail'],
                            //     false,
                            //     [
                            //         'ACCOMPLICES' => $arAccomplices,
                            //     ]
                            // );
                        }

                        LocalRedirect($redirecturl);
                    }
                    echo 'Error updating comment';
                } elseif ($PRODUCT_ID = $el->Add($arLoadProductArray)) {
                    $message = 'Добавлен комментарий соисполнителя';
                    $logText = strip_tags($_REQUEST['DETAIL_TEXT']);
                    $arVisaUsers = [];
                    foreach ($_REQUEST['VISA'] as $visaRow) {
                        [$userId, $status, $comment, $date] = explode(':', $visaRow, 4);
                        $arVisaUsers[ $userId ] = $this->getUserFullName($userId);
                    }
                    if (!empty($arVisaUsers)) {
                        $logText .= '<br/><br/>Согласующие: ' . implode(', ', $arVisaUsers);
                        Notify::send(
                            [$_REQUEST['detail']],
                            'VISA_ACCOMPLIENCE',
                            array_keys($arVisaUsers)
                        );
                    }
                    $this->log(
                        $_REQUEST['detail'],
                        $message,
                        [
                            'METHOD'    => __METHOD__,
                            'REQUEST'   => $_REQUEST,
                        ]
                    );
                    $this->addToLog($message, $logText);

                    if (
                        !$this->permissions['controler'] &&
                        $obOrders->isExternal($_REQUEST['detail']) &&
                        empty($arVisaUsers)
                    ) {
                        Notify::send(
                            [$_REQUEST['detail']],
                            'ACCOMPLICES_REPORT'
                        );
                        // Если добавили отчет соисполнителя - отправить на контроль
                        $this->log(
                            $_REQUEST['detail'],
                            'Отправлено на контроль',
                            [
                                'METHOD'    => __METHOD__,
                                'REQUEST'   => $_REQUEST,
                            ]
                        );
                        $this->addToLog('Отправлено на контроль', '', $_REQUEST['detail']);
                        CIBlockElement::SetPropertyValuesEx(
                            $_REQUEST['detail'],
                            false,
                            [
                                'ACTION'            => Settings::$arActions['CONTROL'],
                                'DATE_FACT_ISPOLN'  => date('d.m.Y'),
                                'CONTROLER_STATUS'  => $this->arParams['ENUM']['CONTROLER_STATUS']['on_beforing']['ID'],
                                'WORK_INTER_STATUS' => false,
                            ]
                        );
                    }
                    LocalRedirect($redirecturl);
                } else {
                    echo 'Error: ' . $el->LAST_ERROR;
                }
                break;
            case 'reject_from_controler':
                $this->log(
                    $_REQUEST['detail'],
                    'Отозвано контролером',
                    [
                        'METHOD'    => __METHOD__,
                        'REQUEST'   => $_REQUEST,
                    ]
                );
                $this->addToLog('Отозвано контролером', strip_tags($_REQUEST['DETAIL_TEXT']));
                CIBlockElement::SetPropertyValuesEx(
                    $_REQUEST['detail'],
                    false,
                    [
                        'ACTION' => Settings::$arActions['CONTROL'],
                    ]
                );
                LocalRedirect($redirecturl);
                break;
            case 'thesis_from_controler':
                $this->log(
                    $_REQUEST['detail'],
                    'Описана основная суть контролером',
                    [
                        'METHOD'    => __METHOD__,
                        'REQUEST'   => $_REQUEST,
                    ]
                );
                $this->addToLog('Описана основная суть контролером', strip_tags($_REQUEST['DETAIL_TEXT']));
                $arThesis = [
                    'USER_ID'     => $USER->GetID(),
                    'DATE_CREATE' => date('d.m.Y H:i:s'),
                    'THESIS'      => strip_tags($_REQUEST['DETAIL_TEXT'])
                ];
                CIBlockElement::SetPropertyValuesEx(
                    $_REQUEST['detail'],
                    false,
                    [
                        'THESIS' => serialize($arThesis),
                    ]
                );
                LocalRedirect($redirecturl);
                break;
            case 'add_comment_controler':
                $el = new CIBlockElement();

                $arProp           = [];
                $arProp['PORUCH'] = $_REQUEST['detail'];
                $arProp['USER']   = $USER->GetID();
                $arProp['TYPE']   = 1132;
                $arProp['DOCS']   = $_REQUEST['FILES_COMMENT_CONTROLER'];
                if ($_REQUEST['sign_data_id'] != '') {
                    $arProp['ECP'] = $USER->GetID();
                    $arProp['FILE_ECP'] = $_REQUEST['sign_data_id'];
                }
                $arLoadProductArray = [
                    'MODIFIED_BY'       => $USER->GetID(),
                    'IBLOCK_SECTION_ID' => false,
                    'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
                    'PROPERTY_VALUES'   => $arProp,
                    'NAME'              => $USER->GetID() . '-' . $_REQUEST['detail'] . '-' . date('d-m-Y_h:i:s'),
                    'ACTIVE'            => 'Y',
                    'PREVIEW_TEXT'      => $_REQUEST['DETAIL_TEXT'],
                    'DETAIL_TEXT'       => $_REQUEST['DETAIL_TEXT'],
                ];

                if ($_REQUEST['CURRENT_ID'] > 0) {
                    $resComment = CIBlockElement::GetList(
                        [
                            'DATE_CREATE' => 'DESC'
                        ],
                        [
                            'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
                            'PROPERTY_PORUCH'   => (int)$_REQUEST['detail'],
                            'ID'                => $_REQUEST['CURRENT_ID']
                        ],
                        false,
                        false,
                        $this->arReportFields
                    );
                    if ($arCurrentFields = $resComment->GetNext()) {
                        $arUpdateFields = [
                            'MODIFIED_BY'   => $USER->GetID(),
                            'PREVIEW_TEXT'  => $_REQUEST['DETAIL_TEXT'],
                            'DETAIL_TEXT'   => $_REQUEST['DETAIL_TEXT'],
                        ];
                        $el->Update($arCurrentFields['ID'], $arUpdateFields);
                        CIBlockElement::SetPropertyValuesEx(
                            $arCurrentFields['ID'],
                            false,
                            [
                                'DOCS' => $_REQUEST['FILES_COMMENT_CONTROLER'],
                            ]
                        );
                        $findField = '~PREVIEW_TEXT';
                        if (!empty($arCurrentFields['~DETAIL_TEXT'])) {
                            $findField = '~DETAIL_TEXT';
                        }
                        $comment = strip_tags($arCurrentFields[ $findField ]) . ' &rarr; ' . strip_tags($_REQUEST['DETAIL_TEXT']);
                        $this->addToLog('Изменен комментарий контролера от ' . $arCurrentFields['DATE_CREATE'], $comment, $arCurrentFields['PROPERTY_PORUCH_VALUE']);
                        $this->log(
                            $arCurrentFields['PROPERTY_PORUCH_VALUE'],
                            'Изменен комментарий контролера',
                            [
                                'METHOD'    => __METHOD__,
                                'REQUEST'   => $_REQUEST,
                            ]
                        );
                        LocalRedirect($redirecturl);
                    }
                    echo 'Error updating comment';
                } elseif ($PRODUCT_ID = $el->Add($arLoadProductArray)) {
                    $arChanges = [];
                    if (isset($_REQUEST['DISABLE_NEW_SUBEXECUTOR_DATE']) && $_REQUEST['DISABLE_NEW_SUBEXECUTOR_DATE'] == 'Y') {
                        $_REQUEST['NEW_SUBEXECUTOR_DATE'] = $this->disableSrokDate;
                    }

                    if ($_REQUEST['subaction'] == 'close_position') {
                        CIBlockElement::SetPropertyValuesEx(
                            $_REQUEST['detail'],
                            false,
                            [
                                'ACTION'            => Settings::$arActions['ARCHIVE'],
                                'STATUS'            => 1142,
                                'POST_RESH'         => 1204,
                                'DATE_FACT_SNYAT'   => date('d.m.Y'),
                                'WORK_INTER_STATUS' => false,
                            ]
                        );
                        self::fixSrokNarush($_REQUEST['detail']);
                        $arPositionFrom = $obOrders->getProperty($_REQUEST['detail'], 'POSITION_TO', true);
                        if (!empty($arPositionFrom)) {
                            foreach ($arPositionFrom as $value) {
                                $this->log(
                                    $value,
                                    'Позиция по поручению принята контролером',
                                    [
                                        'METHOD'    => __METHOD__,
                                        'REQUEST'   => $_REQUEST,
                                    ]
                                );
                                $this->addToLog('Позиция по поручению принята контролером', strip_tags($_REQUEST['DETAIL_TEXT']), $value);
                            }
                        }
                        $this->log(
                            $_REQUEST['detail'],
                            'Снято с контроля',
                            [
                                'METHOD'    => __METHOD__,
                                'REQUEST'   => $_REQUEST,
                            ]
                        );
                        $this->addToLog('Снято с контроля', $_REQUEST['DETAIL_TEXT']??'');
                    } else {
                        if ($this->permissions['main_controler']) {
                            if ($_REQUEST['OST_VIEW_CHECK'] != '') {
                                $this->log(
                                    $_REQUEST['detail'],
                                    'Добавлена отметка о выезде',
                                    [
                                        'METHOD'    => __METHOD__,
                                        'REQUEST'   => $_REQUEST,
                                    ]
                                );
                                $this->addToLog('Добавлена отметка о выезде', strip_tags($_REQUEST['DETAIL_TEXT']));
                                CIBlockElement::SetPropertyValuesEx(
                                    $_REQUEST['detail'],
                                    false,
                                    [
                                        'OST_VIEW' => 1145,
                                    ]
                                );
                                $arChanges['OST_VIEW'] = 1145;
                            }

                            if ($_REQUEST['CONTROLER_RESH']) {
                                CIBlockElement::SetPropertyValuesEx(
                                    $_REQUEST['detail'],
                                    false,
                                    [
                                        'CONTROLER_RESH' => $_REQUEST['CONTROLER_RESH'],
                                    ]
                                );
                                $arChanges['CONTROLER_RESH'] = $_REQUEST['CONTROLER_RESH'];
                                if ($_REQUEST['CONTROLER_RESH'] == 1276) {
                                    CIBlockElement::SetPropertyValuesEx(
                                        $_REQUEST['detail'],
                                        false,
                                        [
                                            'NEW_DATE_ISPOLN'       => false,
                                            'NEW_SUBEXECUTOR_DATE'  => false,
                                            'NEWISPOLNITEL'         => false,
                                            'POSITION_ISPOLN'       => false,
                                            'DOPSTATUS'             => false,
                                            'DATE_REAL_ISPOLN'      => $_REQUEST['DATE_REAL_ISPOLN'],
                                            'WORK_INTER_STATUS'     => false,
                                        ]
                                    );
                                    $_REQUEST['NEW_DATE_ISPOLN'] = '';
                                    $_REQUEST['DOPSTATUS'] = '';
                                    $arChanges['NEW_DATE_ISPOLN'] = $_REQUEST['NEW_DATE_ISPOLN'];
                                    $arChanges['DOPSTATUS'] = $_REQUEST['DOPSTATUS'];
                                }
                            }

                            if ($_REQUEST['DOPSTATUS'] == 'change_srok') {
                                CIBlockElement::SetPropertyValuesEx(
                                    $_REQUEST['detail'],
                                    false,
                                    [
                                        'NEW_DATE_ISPOLN'       => $_REQUEST['NEW_DATE_ISPOLN'],
                                        'NEW_SUBEXECUTOR_DATE'  => $_REQUEST['NEW_SUBEXECUTOR_DATE'],
                                        'DOPSTATUS'             => $this->arDopStatuses['change_srok']['ID'],
                                        'CONTROLER_RESH'        => 1307,
                                        'WORK_INTER_STATUS'     => false,
                                    ]
                                );
                                $this->log(
                                    $_REQUEST['detail'],
                                    'Запрос на смену срока исполнения',
                                    [
                                        'METHOD'    => __METHOD__,
                                        'REQUEST'   => $_REQUEST,
                                    ]
                                );
                                $this->addToLog('Запрос на смену срока исполнения', $_REQUEST['NEW_DATE_ISPOLN']);
                                $arChanges['NEW_DATE_ISPOLN'] = $_REQUEST['NEW_DATE_ISPOLN'];
                                $arChanges['NEW_SUBEXECUTOR_DATE'] = $_REQUEST['NEW_SUBEXECUTOR_DATE'];
                                $arChanges['DOPSTATUS'] = $this->arDopStatuses['change_srok']['ID'];
                                $arChanges['CONTROLER_RESH'] = 1307;
                            } elseif ($_REQUEST['NEW_DATE_ISPOLN'] != '') {
                                CIBlockElement::SetPropertyValuesEx(
                                    $_REQUEST['detail'],
                                    false,
                                    [
                                        'NEW_DATE_ISPOLN'       => $_REQUEST['NEW_DATE_ISPOLN'],
                                        'NEW_SUBEXECUTOR_DATE'  => $_REQUEST['NEW_SUBEXECUTOR_DATE'],
                                    ]
                                );
                                $this->log(
                                    $_REQUEST['detail'],
                                    'Запрос на смену срока исполнения',
                                    [
                                        'METHOD'    => __METHOD__,
                                        'REQUEST'   => $_REQUEST,
                                    ]
                                );
                                $this->addToLog('Запрос на смену срока исполнения', $_REQUEST['NEW_DATE_ISPOLN']);
                                $arChanges['NEW_DATE_ISPOLN'] = $_REQUEST['NEW_DATE_ISPOLN'];
                                $arChanges['NEW_SUBEXECUTOR_DATE'] = $_REQUEST['NEW_SUBEXECUTOR_DATE'];
                            }

                            if ($_REQUEST['DOPSTATUS'] == 'change_srok_ispoln') {
                                CIBlockElement::SetPropertyValuesEx(
                                    $_REQUEST['detail'],
                                    false,
                                    [
                                        'NEWISPOLNITEL'     => $_REQUEST['NEWISPOLNITEL'],
                                        'DOPSTATUS'         => $this->arDopStatuses['change_srok_ispoln']['ID'],
                                        'WORK_INTER_STATUS' => false,

                                        'NEW_DATE_ISPOLN'       => $_REQUEST['NEW_DATE_ISPOLN'],
                                        'NEW_SUBEXECUTOR_DATE'  => $_REQUEST['NEW_SUBEXECUTOR_DATE'],
                                        'DOPSTATUS'             => $this->arDopStatuses['change_srok']['ID'],
                                        'CONTROLER_RESH'        => 1307,

                                    ]
                                );
                                $this->log(
                                    $_REQUEST['detail'],
                                    'Запрос на смену исполнителя',
                                    [
                                        'METHOD'    => __METHOD__,
                                        'REQUEST'   => $_REQUEST,
                                    ]
                                );
                                $this->addToLog('Запрос на смену исполнителя', $this->ispolnitels[ $_REQUEST['NEWISPOLNITEL'] ]['NAME']);
                                $arChanges['NEWISPOLNITEL'] = $_REQUEST['NEWISPOLNITEL'];
                                $arChanges['DOPSTATUS'] = $this->arDopStatuses['change_srok_ispoln']['ID'];
                                $arChanges['NEW_DATE_ISPOLN'] = $_REQUEST['NEW_DATE_ISPOLN'];
                                $arChanges['NEW_SUBEXECUTOR_DATE'] = $_REQUEST['NEW_SUBEXECUTOR_DATE'];
                                $arChanges['CONTROLER_RESH'] = 1307;

                            }

                            if ($_REQUEST['DOPSTATUS'] == 'change_ispoln') {
                                CIBlockElement::SetPropertyValuesEx(
                                    $_REQUEST['detail'],
                                    false,
                                    [
                                        'NEWISPOLNITEL'     => $_REQUEST['NEWISPOLNITEL'],
                                        'DOPSTATUS'         => $this->arDopStatuses['change_ispoln']['ID'],
                                        'WORK_INTER_STATUS' => false,
                                    ]
                                );
                                $this->log(
                                    $_REQUEST['detail'],
                                    'Запрос на смену исполнителя',
                                    [
                                        'METHOD'    => __METHOD__,
                                        'REQUEST'   => $_REQUEST,
                                    ]
                                );
                                $this->addToLog('Запрос на смену исполнителя', $this->ispolnitels[ $_REQUEST['NEWISPOLNITEL'] ]['NAME']);
                                $arChanges['NEWISPOLNITEL'] = $_REQUEST['NEWISPOLNITEL'];
                                $arChanges['DOPSTATUS'] = $this->arDopStatuses['change_ispoln']['ID'];
                            }

                            if ($_REQUEST['DOPSTATUS'] == 'to_position') {
                                CIBlockElement::SetPropertyValuesEx(
                                    $_REQUEST['detail'],
                                    false,
                                    [
                                        'DOPSTATUS'             => $this->arDopStatuses['to_position']['ID'],
                                        'POSITION_ISPOLN'       => $_REQUEST['POSITION_ISPOLN'],
                                        'POSITION_ISPOLN_REQS'  => [
                                            'VALUE' => [
                                                'TYPE' => 'HTML',
                                                'TEXT' => $_REQUEST['POSITION_ISPOLN_REQS'],
                                            ],
                                        ],
                                        'WORK_INTER_STATUS'     => false,
                                    ]
                                );
                                $arChanges['DOPSTATUS'] = $this->arDopStatuses['to_position']['ID'];
                                $arChanges['POSITION_ISPOLN'] = $_REQUEST['POSITION_ISPOLN'];
                                $arChanges['POSITION_ISPOLN_REQS'] = $_REQUEST['POSITION_ISPOLN_REQS'];
                            } else {
                                CIBlockElement::SetPropertyValuesEx(
                                    $_REQUEST['detail'],
                                    false,
                                    [
                                        'POSITION_ISPOLN'       => false,
                                        'WORK_INTER_STATUS'     => false,
                                    ]
                                );
                            }

                            if ($_REQUEST['subaction'] == 'accept') {
                                $this->log(
                                    $_REQUEST['detail'],
                                    'Принято контролером, отправлено на согласование',
                                    [
                                        'METHOD'    => __METHOD__,
                                        'REQUEST'   => $_REQUEST,
                                    ]
                                );
                                $this->addToLog('Принято контролером, отправлено на согласование', strip_tags($_REQUEST['DETAIL_TEXT']));
                                CIBlockElement::SetPropertyValuesEx(
                                    $_REQUEST['detail'],
                                    false,
                                    [
                                        'ACTION' => Settings::$arActions['READY'],
                                    ]
                                );
                            } elseif ($_REQUEST['subaction'] == 'reject') {
                                $this->log(
                                    $_REQUEST['detail'],
                                    'Отклонено контролером, отправлено на доработку',
                                    [
                                        'METHOD'    => __METHOD__,
                                        'REQUEST'   => $_REQUEST,
                                    ]
                                );
                                $this->addToLog('Отклонено контролером, отправлено на доработку', strip_tags($_REQUEST['DETAIL_TEXT']));
                                CIBlockElement::SetPropertyValuesEx(
                                    $_REQUEST['detail'],
                                    false,
                                    [
                                        'ACTION'            => Settings::$arActions['NEW'],
                                        'DATE_FACT_ISPOLN'  => false,
                                        'DOPSTATUS'         => false,
                                        'CONTROLER_STATUS'  => false,
                                        'WORK_INTER_STATUS' => false,
                                    ]
                                );
                            }
                        } else {
                            if ($_REQUEST['CONTROLER_RESH']) {
                                CIBlockElement::SetPropertyValuesEx(
                                    $_REQUEST['detail'],
                                    false,
                                    [
                                        'CONTROLER_RESH' => $_REQUEST['CONTROLER_RESH'],
                                    ]
                                );
                                $arChanges['CONTROLER_RESH'] = $_REQUEST['CONTROLER_RESH'];
                                if ($_REQUEST['CONTROLER_RESH'] == 1278) {
                                    CIBlockElement::SetPropertyValuesEx(
                                        $_REQUEST['detail'],
                                        false,
                                        [
                                            'DOPSTATUS' => false,
                                        ]
                                    );
                                    $_REQUEST['NEW_DATE_ISPOLN'] = '';
                                    $_REQUEST['DOPSTATUS'] = '';
                                    $arChanges['NEW_DATE_ISPOLN'] = $_REQUEST['NEW_DATE_ISPOLN'];
                                    $arChanges['DOPSTATUS'] = $_REQUEST['DOPSTATUS'];
                                } elseif ($_REQUEST['CONTROLER_RESH'] == 1276) {
                                    CIBlockElement::SetPropertyValuesEx(
                                        $_REQUEST['detail'],
                                        false,
                                        [
                                            'NEW_DATE_ISPOLN'       => false,
                                            'NEW_SUBEXECUTOR_DATE'  => false,
                                            'NEWISPOLNITEL'         => false,
                                            'POSITION_ISPOLN'       => false,
                                            'DOPSTATUS'             => false,
                                            'DATE_REAL_ISPOLN'      => $_REQUEST['DATE_REAL_ISPOLN'],
                                            'WORK_INTER_STATUS'     => false,
                                        ]
                                    );
                                    $_REQUEST['NEW_DATE_ISPOLN'] = '';
                                    $_REQUEST['DOPSTATUS'] = '';
                                    $arChanges['DATE_REAL_ISPOLN'] = $_REQUEST['DATE_REAL_ISPOLN'];
                                    $arChanges['NEW_DATE_ISPOLN'] = $_REQUEST['NEW_DATE_ISPOLN'];
                                    $arChanges['DOPSTATUS'] = $_REQUEST['DOPSTATUS'];
                                } else {
                                    CIBlockElement::SetPropertyValuesEx(
                                        $_REQUEST['detail'],
                                        false,
                                        [
                                            'DATE_REAL_ISPOLN' => false,
                                        ]
                                    );
                                }
                            }

                            if ($_REQUEST['NEW_DATE_ISPOLN'] != '') {
                                CIBlockElement::SetPropertyValuesEx(
                                    $_REQUEST['detail'],
                                    false,
                                    [
                                        'NEW_DATE_ISPOLN'   => $_REQUEST['NEW_DATE_ISPOLN'],
                                        'CONTROLER_RESH'    => 1307,
                                        'WORK_INTER_STATUS' => false,
                                    ]
                                );
                                $arChanges['NEW_DATE_ISPOLN'] = $_REQUEST['NEW_DATE_ISPOLN'];
                                $arChanges['CONTROLER_RESH'] = 1307;
                            } else {
                                CIBlockElement::SetPropertyValuesEx(
                                    $_REQUEST['detail'],
                                    false,
                                    [
                                        'NEW_DATE_ISPOLN' => false,
                                    ]
                                );
                            }

                            if ($_REQUEST['NEW_SUBEXECUTOR_DATE'] != '') {
                                $arUpdate = [
                                    'NEW_SUBEXECUTOR_DATE'  => $_REQUEST['NEW_SUBEXECUTOR_DATE'],
                                    'WORK_INTER_STATUS'     => false,
                                ];
                                if ($_REQUEST['NEW_SUBEXECUTOR_DATE'] != $this->disableSrokDate) {
                                    $arUpdate['CONTROLER_RESH'] = 1307;
                                    $arChanges['CONTROLER_RESH'] = 1307;
                                }
                                CIBlockElement::SetPropertyValuesEx(
                                    $_REQUEST['detail'],
                                    false,
                                    $arUpdate
                                );
                                $arChanges['NEW_SUBEXECUTOR_DATE'] = $_REQUEST['NEW_SUBEXECUTOR_DATE'];
                            } else {
                                CIBlockElement::SetPropertyValuesEx(
                                    $_REQUEST['detail'],
                                    false,
                                    [
                                        'NEW_SUBEXECUTOR_DATE' => false,
                                    ]
                                );
                            }

                            if ($_REQUEST['DOPSTATUS'] == 'change_srok') {
                                CIBlockElement::SetPropertyValuesEx(
                                    $_REQUEST['detail'],
                                    false,
                                    [
                                        'DOPSTATUS'         => $this->arDopStatuses['change_srok']['ID'],
                                        'CONTROLER_RESH'    => 1307,
                                        'WORK_INTER_STATUS' => false,
                                    ]
                                );
                                $arChanges['DOPSTATUS'] = $this->arDopStatuses['change_srok']['ID'];
                                $arChanges['CONTROLER_RESH'] = 1307;
                            }

                            if ($_REQUEST['DOPSTATUS'] == 'change_ispoln') {
                                CIBlockElement::SetPropertyValuesEx(
                                    $_REQUEST['detail'],
                                    false,
                                    [
                                        'NEWISPOLNITEL'         => $_REQUEST['NEWISPOLNITEL'],
                                        'DOPSTATUS'             => $this->arDopStatuses['change_ispoln']['ID'],
                                        'POSITION_ISPOLN'       => false,
                                        'WORK_INTER_STATUS'     => false,
                                    ]
                                );
                                $arChanges['NEWISPOLNITEL'] = $_REQUEST['NEWISPOLNITEL'];
                                $arChanges['DOPSTATUS'] = $this->arDopStatuses['change_ispoln']['ID'];
                            } else {
                                CIBlockElement::SetPropertyValuesEx(
                                    $_REQUEST['detail'],
                                    false,
                                    [
                                        'NEWISPOLNITEL' => false,
                                    ]
                                );
                            }

                            if ($_REQUEST['DOPSTATUS'] == 'dopcontrol') {
                                CIBlockElement::SetPropertyValuesEx(
                                    $_REQUEST['detail'],
                                    false,
                                    [
                                        'CONTROLER_RESH'        => 1277,
                                        'DOPSTATUS'             => false,
                                        'NEWISPOLNITEL'         => false,
                                        'POSITION_ISPOLN'       => false,
                                        'WORK_INTER_STATUS'     => false,
                                    ]
                                );
                                $arChanges['CONTROLER_RESH'] = 1277;
                                if (!isset($_REQUEST['NEW_DATE_ISPOLN']) || $_REQUEST['NEW_DATE_ISPOLN'] == '') {
                                    CIBlockElement::SetPropertyValuesEx(
                                        $_REQUEST['detail'],
                                        false,
                                        [
                                            'NEW_DATE_ISPOLN' => false,
                                        ]
                                    );
                                }
                            }

                            if ($_REQUEST['DOPSTATUS'] == 'to_position') {
                                CIBlockElement::SetPropertyValuesEx(
                                    $_REQUEST['detail'],
                                    false,
                                    [
                                        'DOPSTATUS'             => $this->arDopStatuses['to_position']['ID'],
                                        'POSITION_ISPOLN'       => $_REQUEST['POSITION_ISPOLN'],
                                        'POSITION_ISPOLN_REQS'  => [
                                            'VALUE' => [
                                                'TYPE' => 'HTML',
                                                'TEXT' => $_REQUEST['POSITION_ISPOLN_REQS'],
                                            ],
                                        ],
                                        'WORK_INTER_STATUS'     => false,
                                    ]
                                );
                                $arChanges['DOPSTATUS'] = $this->arDopStatuses['to_position']['ID'];
                                $arChanges['POSITION_ISPOLN'] = $_REQUEST['POSITION_ISPOLN'];
                                $arChanges['POSITION_ISPOLN_REQS'] = $_REQUEST['POSITION_ISPOLN_REQS'];
                            } else {
                                CIBlockElement::SetPropertyValuesEx(
                                    $_REQUEST['detail'],
                                    false,
                                    [
                                        'POSITION_ISPOLN' => false,
                                    ]
                                );
                            }

                            CIBlockElement::SetPropertyValuesEx(
                                $_REQUEST['detail'],
                                false,
                                [
                                    'CONTROLER_STATUS' => $this->arControlerStatuses['on_accepting']['ID'],
                                ]
                            );
                            $arChanges['CONTROLER_STATUS'] = $this->arControlerStatuses['on_accepting']['ID'];
                            if ($_REQUEST['subaction'] == 'reject') {
                                $this->log(
                                    $_REQUEST['detail'],
                                    'Отклонено контролером, отправлено на доработку',
                                    [
                                        'METHOD'    => __METHOD__,
                                        'REQUEST'   => $_REQUEST,
                                    ]
                                );
                                $this->addToLog('Отклонено контролером, отправлено на доработку', strip_tags($_REQUEST['DETAIL_TEXT']));
                                CIBlockElement::SetPropertyValuesEx(
                                    $_REQUEST['detail'],
                                    false,
                                    [
                                        'ACTION'            => Settings::$arActions['NEW'],
                                        'DATE_FACT_ISPOLN'  => false,
                                        'DOPSTATUS'         => false,
                                        'CONTROLER_STATUS'  => false,
                                        'WORK_INTER_STATUS' => false,
                                    ]
                                );
                            } else {
                                $this->log(
                                    $_REQUEST['detail'],
                                    'Добавлен комментарий контролера',
                                    [
                                        'METHOD'    => __METHOD__,
                                        'REQUEST'   => $_REQUEST,
                                    ]
                                );
                                $this->addToLog('Добавлен комментарий контролера ', strip_tags($_REQUEST['DETAIL_TEXT']));
                            }
                        }

                        if ($_REQUEST['subaction'] == 'reject') {
                            /*
                             * Зафиксировать возврат
                             */
                            $arReject = $obOrders->getProperty($_REQUEST['detail'], 'CONTROL_REJECT', true);
                            $arReject[] = implode(':', [$USER->GetID(), time()]);
                            CIBlockElement::SetPropertyValuesEx(
                                $_REQUEST['detail'],
                                false,
                                [
                                    'CONTROL_REJECT' => $arReject,
                                ]
                            );

                            if (!empty($_REQUEST['RETURN_COMMENT'])) {
                                $this->log(
                                    $_REQUEST['detail'],
                                    'Отчет исполнителя возвращен на доработку',
                                    [
                                        'METHOD'    => __METHOD__,
                                        'REQUEST'   => $_REQUEST,
                                    ]
                                );
                                $arComFilter = [
                                    'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
                                    'ACTIVE'            => 'Y',
                                    'PROPERTY_TYPE'     => 1131,
                                    'PROPERTY_PORUCH'   => $_REQUEST['detail'],
                                ];
                                $resCom = CIBlockElement::GetList(
                                    [
                                        'DATE_CREATE' => 'DESC'
                                    ],
                                    $arComFilter,
                                    false,
                                    [
                                        'nPageSize' => 1
                                    ],
                                    $this->arReportFields
                                );
                                while ($arComFields = $resCom->GetNext()) {
                                    CIBlockElement::SetPropertyValuesEx(
                                        $arComFields['ID'],
                                        false,
                                        [
                                            'CONTROLER_COMMENT' => $_REQUEST['RETURN_COMMENT'] ? '(' . date('d.m.Y H:i:s') . ') ' . $_REQUEST['RETURN_COMMENT'] : '-',
                                            'CURRENT_USER'      => false,
                                            'STATUS'            => false,
                                        ]
                                    );
                                }
                            }
                        }
                    }
                    CIBlockElement::SetPropertyValuesEx(
                        $_REQUEST['detail'],
                        false,
                        [
                            'WORK_INTER_STATUS' => false,
                            'VIEWS'             => false,
                        ]
                    );
                    if (!empty($arChanges)) {
                        CIBlockElement::SetPropertyValuesEx(
                            $PRODUCT_ID,
                            false,
                            [
                                'CHANGES' => json_encode($arChanges, JSON_UNESCAPED_UNICODE),
                            ]
                        );
                    }
                    LocalRedirect($redirecturl);
                } else {
                    echo 'Error: ' . $el->LAST_ERROR;
                }
                break;
            case 'add_zametka':
                $el = new CIBlockElement();

                $arProp           = [];
                $arProp['PORUCH'] = $_REQUEST['detail'];
                $arProp['USER']   = $USER->GetID();
                $arProp['TYPE']   = 1130;
                $arProp['DOCS']   = $_REQUEST['FILES_ZAMETKA_CONTROLER'];
                $arProp['RESULT_VOTE'] = $_REQUEST['RESULT_VOTE'];
                $arProp['DATE_VOTE'] = $_REQUEST['DATE_VOTE'];

                if ($_REQUEST['sign_data_id'] != '') {
                    $arProp['ECP'] = $USER->GetID();
                    $arProp['FILE_ECP'] = $_REQUEST['sign_data_id'];
                }

                $arLoadProductArray = [
                    'MODIFIED_BY'       => $USER->GetID(),
                    'IBLOCK_SECTION_ID' => false,
                    'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
                    'PROPERTY_VALUES'   => $arProp,
                    'NAME'              => $USER->GetID() . '-' . $_REQUEST['detail'] . '-' . date('d-m-Y_h:i:s'),
                    'ACTIVE'            => 'Y',
                    'PREVIEW_TEXT'      => $_REQUEST['DETAIL_TEXT'],
                    'DETAIL_TEXT'       => $_REQUEST['DETAIL_TEXT'],
                ];

                if ($_REQUEST['CURRENT_ID'] > 0) {
                    $resComment = CIBlockElement::GetList(
                        [
                            'DATE_CREATE' => 'DESC'
                        ],
                        [
                            'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
                            'PROPERTY_PORUCH'   => (int)$_REQUEST['detail'],
                            'ID'                => $_REQUEST['CURRENT_ID']
                        ],
                        false,
                        false,
                        $this->arReportFields
                    );
                    if ($arCurrentFields = $resComment->GetNext()) {
                        $arUpdateFields = [
                            'MODIFIED_BY'   => $USER->GetID(),
                            'PREVIEW_TEXT'  => $_REQUEST['DETAIL_TEXT'],
                            'DETAIL_TEXT'   => $_REQUEST['DETAIL_TEXT'],
                        ];
                        $el->Update($arCurrentFields['ID'], $arUpdateFields);
                        $this->log(
                            $_REQUEST['detail'],
                            'Изменена заметка',
                            [
                                'METHOD'    => __METHOD__,
                                'REQUEST'   => $_REQUEST,
                            ]
                        );
                        CIBlockElement::SetPropertyValuesEx(
                            $arCurrentFields['ID'],
                            false,
                            [
                                'DOCS'          => $_REQUEST['FILES_COMMENT_CONTROLER'],
                                'DATE_VOTE'     => $_REQUEST['DATE_VOTE'],
                                'RESULT_VOTE'   => $_REQUEST['RESULT_VOTE'],
                            ]
                        );
                        LocalRedirect($redirecturl);
                    }
                    echo 'Error updating comment';
                } elseif ($PRODUCT_ID = $el->Add($arLoadProductArray)) {
                    $this->log(
                        $_REQUEST['detail'],
                        'Добавлена заметка',
                        [
                            'METHOD'    => __METHOD__,
                            'REQUEST'   => $_REQUEST,
                        ]
                    );
                    LocalRedirect($redirecturl);
                } else {
                    echo 'Error: ' . $el->LAST_ERROR;
                }
                break;
            case 'add_kurator_comment':
                $el = new CIBlockElement();

                $arProp = [
                    'PORUCH'    => $_REQUEST['detail'],
                    'USER'      => $USER->GetID(),
                    'TYPE'      => 1133,
                    'DOCS'      => $_REQUEST['FILES_KURATOR'],
                ];

                if ($_REQUEST['sign_data_id'] != '') {
                    $arProp['ECP'] = $USER->GetID();
                    $arProp['FILE_ECP'] = $_REQUEST['sign_data_id'];
                }

                $arLoadProductArray = [
                    'MODIFIED_BY'       => $USER->GetID(),
                    'IBLOCK_SECTION_ID' => false,
                    'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
                    'PROPERTY_VALUES'   => $arProp,
                    'NAME'              => $USER->GetID() . '-' . $_REQUEST['detail'] . '-' . date('d-m-Y_h:i:s'),
                    'ACTIVE'            => 'Y',
                    'PREVIEW_TEXT'      => $_REQUEST['DETAIL_TEXT'],
                    'DETAIL_TEXT'       => $_REQUEST['DETAIL_TEXT'],
                ];

                if ($PRODUCT_ID = $el->Add($arLoadProductArray)) {
                    $arDopstatus = $obOrders->getProperty($_REQUEST['detail'], 'DOPSTATUS');
                    $arPositionFrom = $obOrders->getProperty($_REQUEST['detail'], 'POSITION_TO', true);
                    $arPositionIspoln = $obOrders->getProperty($_REQUEST['detail'], 'POSITION_ISPOLN');
                    $arNewDate = $obOrders->getProperty($_REQUEST['detail'], 'NEW_DATE_ISPOLN');
                    $arNewSubDate = $obOrders->getProperty($_REQUEST['detail'], 'NEW_SUBEXECUTOR_DATE');
                    $arOldDate = $obOrders->getProperty($_REQUEST['detail'], 'DATE_ISPOLN');
                    $arDates = $obOrders->getProperty($_REQUEST['detail'], 'DATE_ISPOLN_HIST', true);
                    $arDatesBad = $obOrders->getProperty($_REQUEST['detail'], 'DATE_ISPOLN_BAD', true);
                    $arTypes = $obOrders->getProperty($_REQUEST['detail'], 'TYPE', true);

                    if ($_REQUEST['subaction'] != 'zamechanie') {
                        if ($arNewDate['VALUE'] != '') {
                            $arDates[] = $arOldDate['VALUE'];

                            $bSendEmailSrok = false;
                            $arDelegateHistory = [];
                            $arElement = $obOrders->getById($_REQUEST['detail']);
                            foreach ($arElement['~PROPERTY_DELEGATE_HISTORY_VALUE'] as $history) {
                                $history = json_decode($history, true);
                                if ($history['DELEGATE'] == $arElement['PROPERTY_DELEGATE_USER_VALUE']) {
                                    $history['SROK'] = $arNewDate['VALUE'];
                                    $bSendEmailSrok = true;
                                }

                                $arDelegateHistory[] = json_encode($history, JSON_UNESCAPED_UNICODE);
                            }

                            if ($bSendEmailSrok) {
                                $arSendUsers = $this->ispolnitels[ $arElement['PROPERTY_ISPOLNITEL_VALUE'] ]['PROPERTY_IMPLEMENTATION_VALUE'];
                                if (!empty($arSendUsers)) {
                                    Notify::send(
                                        [$_REQUEST['edit']],
                                        'NEW_SROK',
                                        $arSendUsers
                                    );
                                }
                            }

                            CIBlockElement::SetPropertyValuesEx(
                                $_REQUEST['detail'],
                                false,
                                [
                                    'DATE_ISPOLN'       => $arNewDate['VALUE'],
                                    'DATE_ISPOLN_HIST'  => $arDates,
                                    'DELEGATE_HISTORY'  => $arDelegateHistory,
                                    'NEW_DATE_ISPOLN'   => false,
                                    'WORK_INTER_STATUS' => false,
                                ]
                            );

                            if ($arDopstatus['VALUE_XML_ID'] == 'change_srok') {
                                $arDatesBad[] = $arOldDate['VALUE'];
                                CIBlockElement::SetPropertyValuesEx(
                                    $_REQUEST['detail'],
                                    false,
                                    [
                                        'DATE_ISPOLN_BAD' => $arDatesBad,
                                    ]
                                );
                            }

                            $this->log(
                                $_REQUEST['detail'],
                                'Изменен срок исполнения',
                                [
                                    'METHOD'    => __METHOD__,
                                    'REQUEST'   => $_REQUEST,
                                ]
                            );
                            $this->addToLog('Изменен срок исполнения', $arNewDate['VALUE']);

                            if ($arNewSubDate['VALUE'] != '') {
                                $newSubDate = $arNewSubDate['VALUE'];
                                if ($newSubDate == $this->disableSrokDate) {
                                    $newSubDate = $arNewDate['VALUE'];
                                }
                                CIBlockElement::SetPropertyValuesEx(
                                    $_REQUEST['detail'],
                                    false,
                                    [
                                        'SUBEXECUTOR_DATE'      => $newSubDate,
                                        'NEW_SUBEXECUTOR_DATE'  => false,
                                    ]
                                );

                                $this->log(
                                    $_REQUEST['detail'],
                                    'Изменен срок соисполнителя',
                                    [
                                        'METHOD'    => __METHOD__,
                                        'REQUEST'   => $_REQUEST,
                                    ]
                                );
                                $this->addToLog('Изменен срок соисполнителя', $newSubDate);
                            }
                        }

                        $arNewIspoln = $obOrders->getProperty($_REQUEST['detail'], 'NEWISPOLNITEL');
                        if ($arNewIspoln['VALUE'] != '') {
                            $arElement = $obOrders->getById($_REQUEST['detail']);
                            $arSetFields = [
                                'STATUS'                => 1141,
                                'ACTION'                => Settings::$arActions['NEW'],
                                'ISPOLNITEL'            => $arNewIspoln['VALUE'],
                                'VIEWS'                 => false,
                                'POST_RESH'             => false,
                                'DOPSTATUS'             => false,
                                'DELEGATE_USER'         => false,
                                'POSITION_FROM'         => false,
                                'NEWISPOLNITEL'         => false,
                                'CONTROLER_RESH'        => false,
                                'DATE_FACT'             => false,
                                'DATE_FACT_SNYAT'       => false,
                                'NEW_DATE_ISPOLN'       => false,
                                'CONTROLER_STATUS'      => false,
                                'DATE_FACT_ISPOLN'      => false,
                                'WORK_INTER_STATUS'     => false,
                                'NEW_SUBEXECUTOR_DATE'  => false,
                            ];
                            if (empty($arElement['PROPERTY_FIRST_EXECUTOR_VALUE'])) {
                                $arSetFields['FIRST_EXECUTOR'] = $arElement['PROPERTY_ISPOLNITEL_VALUE'];
                            }
                            if (!empty($arElement['PROPERTY_DELEGATION_VALUE'])) {
                                $arDelegation = [];
                                foreach ($arElement['PROPERTY_DELEGATION_VALUE'] as $key => $value) {
                                    if ($value == $arElement['PROPERTY_ISPOLNITEL_VALUE']) {
                                        $arDelegation[] = [
                                            'VALUE'         => $arNewIspoln['VALUE'],
                                            'DESCRIPTION'   => '',
                                        ];
                                    } else {
                                        $arDelegation[] = [
                                            'VALUE'         => $value,
                                            'DESCRIPTION'   => $arElement['PROPERTY_DELEGATION_DESCRIPTION'][ $key ],
                                        ];
                                    }
                                }
                                $arSetFields['DELEGATION'] = $arDelegation;
                            }
                            CIBlockElement::SetPropertyValuesEx(
                                $_REQUEST['detail'],
                                false,
                                $arSetFields
                            );
                            $this->log(
                                $_REQUEST['detail'],
                                'Передано на исполнение новому исполнителю',
                                [
                                    'METHOD'    => __METHOD__,
                                    'REQUEST'   => $_REQUEST,
                                ]
                            );
                            $this->addToLog('Передано на исполнение новому исполнителю ', $this->ispolnitels[ $arNewIspoln['VALUE'] ]['NAME']);
                        }

                        if ($arPositionIspoln['VALUE'] != '') {
                            $this->addPositionFromExist($_REQUEST['detail'], $arPositionIspoln['VALUE']);
                        }
                    }

                    if ($_REQUEST['subaction'] == 'accept') {
                        CIBlockElement::SetPropertyValuesEx(
                            $_REQUEST['detail'],
                            false,
                            [
                                'ACTION'            => Settings::$arActions['ARCHIVE'],
                                'STATUS'            => 1142,
                                'POST_RESH'         => 1204,
                                'DATE_FACT_SNYAT'   => date('d.m.Y'),
                                'WORK_INTER_STATUS' => false,
                            ]
                        );
                        self::fixSrokNarush($_REQUEST['detail']);
                        if (!empty($arPositionFrom)) {
                            foreach ($arPositionFrom as $value) {
                                $this->log(
                                    $value,
                                    'Позиция по поручению принята куратором',
                                    [
                                        'METHOD'    => __METHOD__,
                                        'REQUEST'   => $_REQUEST,
                                    ]
                                );
                                $this->addToLog('Позиция по поручению принята куратором', strip_tags($_REQUEST['DETAIL_TEXT']), $value);
                            }
                        }
                        $this->log(
                            $_REQUEST['detail'],
                            'Снято с контроля',
                            [
                                'METHOD'    => __METHOD__,
                                'REQUEST'   => $_REQUEST,
                            ]
                        );
                        $this->addToLog('Снято с контроля', $_REQUEST['DETAIL_TEXT']??'');
                    } elseif ($_REQUEST['subaction'] == 'reject') {
                        if (!empty($arPositionFrom)) {
                            CIBlockElement::SetPropertyValuesEx(
                                $_REQUEST['detail'],
                                false,
                                [
                                    'ACTION'            => Settings::$arActions['NEW'],
                                    'DOPSTATUS'         => false,
                                    'WORK_INTER_STATUS' => false,
                                ]
                            );
                            foreach ($arPositionFrom as $value) {
                                $this->log(
                                    $value,
                                    'Отправка на позицию принята куртором, ожидание позиции',
                                    [
                                        'METHOD'    => __METHOD__,
                                        'REQUEST'   => $_REQUEST,
                                    ]
                                );
                                $this->addToLog('Отправка на позицию принята куртором, ожидание позиции', strip_tags($_REQUEST['DETAIL_TEXT']), $value);
                            }
                        } else {
                            CIBlockElement::SetPropertyValuesEx(
                                $_REQUEST['detail'],
                                false,
                                [
                                    'ACTION'            => Settings::$arActions['NEW'],
                                    'POST_RESH'         => 1203,
                                    'DATE_FACT_ISPOLN'  => false,
                                    'DOPSTATUS'         => false,
                                    'WORK_INTER_STATUS' => false,
                                ]
                            );
                            $arComFilter = [
                                'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
                                'ACTIVE'            => 'Y',
                                'PROPERTY_TYPE'     => 1131,
                                'PROPERTY_PORUCH'   => $_REQUEST['detail'],
                            ];
                            $resCom = CIBlockElement::GetList(
                                [
                                    'DATE_CREATE' => 'DESC'
                                ],
                                $arComFilter,
                                false,
                                [
                                    'nPageSize' => 1
                                ],
                                [
                                    'ID',
                                    'PROPERTY_USER',
                                ]
                            );
                            while ($arComFields = $resCom->GetNext()) {
                                CIBlockElement::SetPropertyValuesEx(
                                    $arComFields['ID'],
                                    false,
                                    [
                                        'CURRENT_USER'      => false,
                                        'STATUS'            => false,
                                        'CONTROLER_COMMENT' => '-',
                                    ]
                                );
                            }
                        }

                        $this->log(
                            $_REQUEST['detail'],
                            'Отправлено на дополнительный контроль',
                            [
                                'METHOD'    => __METHOD__,
                                'REQUEST'   => $_REQUEST,
                            ]
                        );
                        $this->addToLog('Отправлено на дополнительный контроль', strip_tags($_REQUEST['DETAIL_TEXT']));
                        $arTypes[] = 'dopcontrol';
                    } elseif ($_REQUEST['subaction'] == 'zamechanie') {
                        CIBlockElement::SetPropertyValuesEx(
                            $_REQUEST['detail'],
                            false,
                            [
                                'ACTION'    => Settings::$arActions['CURATOR_COMMENTS'],
                                'POST_RESH' => 1203
                            ]
                        );
                        $this->log(
                            $_REQUEST['detail'],
                            'Отправлено на доработку контролеру',
                            [
                                'METHOD'    => __METHOD__,
                                'REQUEST'   => $_REQUEST,
                            ]
                        );
                        $this->addToLog('Отправлено на доработку контролеру', strip_tags($_REQUEST['DETAIL_TEXT']));
                    }
                    CIBlockElement::SetPropertyValuesEx(
                        $_REQUEST['detail'],
                        false,
                        [
                            'VIEWS' => false,
                            'TYPE'  => $arTypes,
                        ]
                    );
                    LocalRedirect($redirecturl);
                } else {
                    echo 'Error: ' . $el->LAST_ERROR;
                }
                break;
            case 'restore_from_archive':
                CIBlockElement::SetPropertyValuesEx(
                    $_REQUEST['detail'],
                    false,
                    [
                        'ACTION'            => Settings::$arActions['CONTROL'],
                        'STATUS'            => 1141,
                        'POST_RESH'         => false,
                        'DATE_FACT_ISPOLN'  => false,
                        'DOPSTATUS'         => false,
                        'CONTROLER_RESH'    => 1276,
                        'CONTROLER_STATUS'  => false,
                        'NEW_DATE_ISPOLN'   => $_REQUEST['DATE_ISPOLN'],
                        'SUBEXECUTOR_DATE'  => $_REQUEST['DATE_ISPOLN'],
                        'WORK_INTER_STATUS' => false,
                    ]
                );
                $arComFilter = [
                    'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
                    'ACTIVE'            => 'Y',
                    'PROPERTY_TYPE'     => 1131,
                    'PROPERTY_PORUCH'   => $_REQUEST['detail'],
                ];
                $resCom = CIBlockElement::GetList(
                    [
                        'DATE_CREATE' => 'DESC'
                    ],
                    $arComFilter,
                    false,
                    ['nPageSize' => 1],
                    $this->arReportFields
                );
                while ($arComFields = $resCom->GetNext()) {
                    CIBlockElement::SetPropertyValuesEx(
                        $arComFields['ID'],
                        false,
                        [
                            'CURRENT_USER'  => false,
                            'STATUS'        => false,
                        ]
                    );
                }
                $this->addToLog('Возвращено на контроль', strip_tags($_REQUEST['DETAIL_TEXT']));

                $this->log(
                    $_REQUEST['detail'],
                    'Возвращено на контроль',
                    [
                        'METHOD'    => __METHOD__,
                        'REQUEST'   => $_REQUEST,
                    ]
                );
                LocalRedirect($redirecturl);
                break;
            case 'restore_from_reject':
                CIBlockElement::SetPropertyValuesEx(
                    $_REQUEST['detail'],
                    false,
                    [
                        'ACTION' => Settings::$arActions['CONTROL'],
                    ]
                );
                $arComFilter = [
                    'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
                    'ACTIVE'            => 'Y',
                    'PROPERTY_TYPE'     => 1131,
                    'PROPERTY_PORUCH'   => $_REQUEST['detail'],
                ];
                $resCom = CIBlockElement::GetList(
                    [
                        'DATE_CREATE' => 'DESC'
                    ],
                    $arComFilter,
                    false,
                    ['nPageSize' => 1],
                    $this->arReportFields
                );
                while ($arComFields = $resCom->GetNext()) {
                    CIBlockElement::SetPropertyValuesEx(
                        $arComFields['ID'],
                        false,
                        [
                            'CURRENT_USER'      => false,
                            'CONTROLER_COMMENT' => false,
                            'STATUS'            => false,
                        ]
                    );
                }
                $this->addToLog('Возвращено на контроль');

                $this->log(
                    $_REQUEST['detail'],
                    'Возвращено на контроль',
                    [
                        'METHOD'    => __METHOD__,
                        'REQUEST'   => $_REQUEST,
                    ]
                );
                LocalRedirect($redirecturl);
                break;
            case 'delete':
                if (
                    $this->permissions['controler'] ||
                    $this->permissions['main_controler'] ||
                    $GLOBALS['USER']->IsAdmin()
                ) {
                    (new CIBlockElement())->Update(
                        $_REQUEST['detail'],
                        [
                            'ACTIVE' => 'N'
                        ]
                    );
                    $this->log(
                        $_REQUEST['detail'],
                        'Поручение отправлено на удаление',
                        [
                            'METHOD'    => __METHOD__,
                            'REQUEST'   => $_REQUEST,
                        ]
                    );
                    $this->addToLog('Поручение отправлено на удаление');
                    LocalRedirect(str_replace('|', '&', rawurldecode($_REQUEST['back_url'])));
                }
                break;
            default:
                break;
        }

        $arDetail = $obOrders->getById($_REQUEST['detail']);

        if (!empty($arDetail)) {
            foreach ($arDetail['PROPERTY_DOCS_VALUE'] as $k => $v) {
                $arDetail['PROPERTY_DOCS_VALUE'][ $k ] = CFile::GetFileArray($v);
            }

            $arDetail['PROPERTY_DELEGATION_VALUE'] = array_map('intval', $arDetail['PROPERTY_DELEGATION_VALUE']);
        }
        $this->DetailData['ELEMENT'] = $arDetail;

        $this->DetailData['TASKS'] = [];
        if (count($arDetail['PROPERTY_TASKS_VALUE']) > 0) {
            $rsTasks = CTasks::GetList(
                ['TITLE' => 'ASC'],
                ['ID' => $arDetail['PROPERTY_TASKS_VALUE']]
            );
            while ($arTaskFields = $rsTasks->GetNext()) {
                $this->DetailData['TASKS'][] = $arTaskFields;
            }
        }

        $this->DetailData['HISTORY'] = $this->getHistory($this->DetailData['ELEMENT'], $GLOBALS['USER']->GetID());

        if ($arDetail['PROPERTY_OLD_PORUCH_VALUE'] != '') {
            $res = CIBlockElement::GetList(
                [
                    'DATE_CREATE' => 'DESC'
                ],
                [
                    'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
                    'PROPERTY_PORUCH'   => (int)$this->DetailData['ELEMENT']['PROPERTY_OLD_PORUCH_VALUE'],
                    'PROPERTY_TYPE'     => 1131
                ],
                false,
                [
                    'nPageSize' => 1
                ],
                $this->arReportFields
            );
            if ($arFields = $res->GetNext()) {
                foreach ($arFields['PROPERTY_DOCS_VALUE'] as $k => $v) {
                    $arFields['PROPERTY_DOCS_VALUE'][ $k ] = CFile::GetFileArray($v);
                }

                if (empty($arFields['DETAIL_TEXT'])) {
                    $arFields['DETAIL_TEXT'] = $arFields['PREVIEW_TEXT'];
                    $arFields['~DETAIL_TEXT'] = $arFields['~PREVIEW_TEXT'];
                }

                $this->DetailData['OLD_OTCHET'] = $arFields;
            }
        }

        $this->DetailData['POSITION_DATA'] = [];

        if (count($this->DetailData['ELEMENT']['PROPERTY_POSITION_TO_VALUE']) > 0) {
            $resPoruchs = CIBlockElement::GetList(
                [
                    'DATE_CREATE' => 'DESC'
                ],
                [
                    'IBLOCK_ID' => Settings::$iblockId['ORDERS'],
                    'ID'        => $this->DetailData['ELEMENT']['PROPERTY_POSITION_TO_VALUE'],
                    'ACTIVE'    => 'Y',
                ],
                false,
                false,
                $this->arOrderFields
            );
            while ($arFields = $resPoruchs->GetNext()) {
                $arReportFilter = [
                    'ACTIVE'            => 'Y',
                    'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
                    'PROPERTY_PORUCH'   => $arFields['ID'] ,
                    'PROPERTY_TYPE'     => 1132
                ];
                $resReport = CIBlockElement::GetList(
                    [
                        'DATE_CREATE' => 'DESC'
                    ],
                    $arReportFilter,
                    false,
                    [
                        'nTopCount' => 1
                    ],
                    $this->arReportFields
                );
                $arFields['REPORT'] = '';
                while ($arReport = $resReport->GetNext()) {
                    if (empty($arReport['DETAIL_TEXT'])) {
                        $arReport['DETAIL_TEXT'] = $arReport['PREVIEW_TEXT'];
                        $arReport['~DETAIL_TEXT'] = $arReport['~PREVIEW_TEXT'];
                    }

                    $arFields['REPORT'] = trim($arReport['~DETAIL_TEXT']);
                    $arFields['REPORT'] = str_replace(
                        ['<p><br /></p>', '<div><br /></div>', '<p></p>'],
                        '',
                        $arFields['REPORT']
                    );
                }

                $this->DetailData['POSITION_DATA'][] = $arFields;
            }
        }

        $this->DetailData['POSITION_FROM'] = [];
        if (!empty($this->DetailData['ELEMENT']['PROPERTY_POSITION_FROM_VALUE'])) {
            $resPoruchs = CIBlockElement::GetList(
                [
                    'DATE_CREATE' => 'DESC'
                ],
                [
                    'IBLOCK_ID' => Settings::$iblockId['ORDERS'],
                    'ID'        => $this->DetailData['ELEMENT']['PROPERTY_POSITION_FROM_VALUE'],
                    'ACTIVE'    => 'Y',
                ],
                false,
                false,
                [
                    'ID',
                    'PROPERTY_ISPOLNITEL',
                    'PROPERTY_ACTION',
                ]
            );
            while ($arFields = $resPoruchs->GetNext()) {
                $arFields['POSITION'] = [];
                $res = CIBlockElement::GetList(
                    [
                        'DATE_CREATE' => 'DESC'
                    ],
                    [
                        'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
                        'PROPERTY_PORUCH'   => $arFields['ID'],
                        'PROPERTY_TYPE'     => 1131
                    ],
                    false,
                    [
                        'nPageSize' => 1
                    ],
                    $this->arReportFields
                );
                if ($arFields2 = $res->GetNext()) {
                    foreach ($arFields2['PROPERTY_DOCS_VALUE'] as $k => $v) {
                        $arFields2['PROPERTY_DOCS_VALUE'][ $k ] = CFile::GetFileArray($v);
                    }

                    if (empty($arFields2['DETAIL_TEXT'])) {
                        $arFields2['DETAIL_TEXT'] = $arFields2['PREVIEW_TEXT'];
                        $arFields2['~DETAIL_TEXT'] = $arFields2['~PREVIEW_TEXT'];
                    }

                    $arFields['POSITION'] = $arFields2;
                }
                $this->DetailData['POSITION_FROM'][] = $arFields;
            }
        }

        $this->DetailData['COMMENTS'] = [
            'COMMENTS_CONTROLER'    => [],
            'OTCHET_ISPOLNITEL'     => [],
            'OTCHET_CONTROLER'      => [],
            'OTCHET_KURATOR'        => [],
            'OTCHET_ACCOMPLIENCE'   => [],
        ];

        $res = CIBlockElement::GetList(
            [
                'DATE_CREATE' => 'DESC'
            ],
            [
                'ACTIVE'            => 'Y',
                'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
                'PROPERTY_PORUCH'   => (int)$_REQUEST['detail']
            ],
            false,
            false,
            $this->arReportFields
        );
        while ($arFields = $res->GetNext()) {
            foreach ($arFields['PROPERTY_DOCS_VALUE'] as $k => $v) {
                $arFields['PROPERTY_DOCS_VALUE'][ $k ] = CFile::GetFileArray($v);
            }
            $arFields['PROPERTY_FILE_ECP_VALUE'] = CFile::GetFileArray($arFields['PROPERTY_FILE_ECP_VALUE']);

            if (empty($arFields['DETAIL_TEXT'])) {
                $arFields['DETAIL_TEXT'] = $arFields['PREVIEW_TEXT'];
                $arFields['~DETAIL_TEXT'] = $arFields['~PREVIEW_TEXT'];
            }

            if ($arFields['PROPERTY_TYPE_ENUM_ID'] == 1130) {
                $this->DetailData['COMMENTS']['COMMENTS_CONTROLER'][] = $arFields;
            } elseif ($arFields['PROPERTY_TYPE_ENUM_ID'] == 1131) {
                $this->DetailData['COMMENTS']['OTCHET_ISPOLNITEL'][] = $arFields;
            } elseif ($arFields['PROPERTY_TYPE_ENUM_ID'] == 1132) {
                $this->DetailData['COMMENTS']['OTCHET_CONTROLER'][] = $arFields;
            } elseif ($arFields['PROPERTY_TYPE_ENUM_ID'] == 1133) {
                $this->DetailData['COMMENTS']['OTCHET_KURATOR'][] = $arFields;
            } elseif ($arFields['PROPERTY_TYPE_ENUM_ID'] == $this->arParams['COMMENT_ENUM']['TYPE']['accomplience']['ID']) {
                $this->DetailData['COMMENTS']['OTCHET_ACCOMPLIENCE'][] = $arFields;
            }
        }
    }

    /**
     * Собрать статистику по поручениям
     *
     * @return array
     *
     * @throws Exception
     */
    public function getStatsData()
    {
        $filterType = $_REQUEST['type_ispoln'];
        $arDataStats['CATEGORY']   = [];
        $arDataStats['ACTION']     = [];
        $arDataStats['CAT_THEMES'] = [];
        $arDataStats['THEMES']     = [];
        $arDataStats['PROBLEMS']   = [];
        $arDataStats['ISPOLN_BAD'] = [];
        $arFilter                  = [
            'IBLOCK_ID'             => Settings::$iblockId['ORDERS'],
            'ACTIVE'                => 'Y',
            '!PROPERTY_ACTION'      => Settings::$arActions['DRAFT'],
        ];
        if ($filterType != 'position') {
            $arFilter['PROPERTY_POSITION_TO'] = false;
        } else {
            $arFilter['!PROPERTY_POSITION_TO'] = false;
            $filterType = '';
        }

        $arDateFilter = [];

        if (!empty($_REQUEST['FROM'])) {
            $obDateFrom = new DateTimeImmutable($_REQUEST['FROM']);
            $arDateFilter['>=PROPERTY_DATE_CREATE'] = $obDateFrom->format('Y-m-d');
        }
        if (!empty($_REQUEST['TO'])) {
            $obDateTo = new DateTimeImmutable($_REQUEST['TO']);
            $arDateFilter['<=PROPERTY_DATE_CREATE'] = $obDateTo->format('Y-m-d');
        }

        if (!empty($arDateFilter)) {
            $arDateFilter['LOGIC'] = 'AND';
            $arFilter[] = $arDateFilter;
        }

        $arFilter['!PROPERTY_ISPOLNITEL'] = [7770, 250530, 251527];

        $res = CIBlockElement::GetList(
            ['ID' => 'ASC'],
            $arFilter,
            false,
            false,
            [
                'ID',
                'NAME',
                'DATE_CREATE',
                'PROPERTY_ISPOLNITEL', /* Исполнитель. */
                'PROPERTY_CONTROLER', /* Контролер. */
                'PROPERTY_NUMBER', /* Номер обращения. */
                'PROPERTY_OBJECT', /* Обьект. */
                'PROPERTY_DATE_CREATE', /* Дата поручения. */
                'PROPERTY_DATE_ISPOLN', /* Срок исполнения. */
                'PROPERTY_ACTION', /* Состояние. */
                'PROPERTY_TYPE', /* Тип поручения. */
                'PROPERTY_THEME', /* Тема поручения. */
                'PROPERTY_HISTORY_SROK', /* История изменения срока исполнения. */
                'PROPERTY_CAT_THEME', /* Категория классификатора. */
                'PROPERTY_CATEGORY', /* Категория поручения. */
                'PROPERTY_DATE_ISPOLN_HIST', /* Сроки исполнения. */
                'PROPERTY_NOT_STATS', /* Не учитывать в нарушениях. */
                'PROPERTY_CONTROL_REJECT', /* Возвраты от контролёра. */
                'PROPERTY_DELEGATION', /* Делегирование. */
                'PROPERTY_DATE_ISPOLN_BAD', /* Сроки исполнения (не выполнено). */
                'PROPERTY_FIRST_EXECUTOR', /* Первый исполнитель. */
            ]
        );
        if (!empty($_REQUEST['ISPOLNITEL']) && false !== mb_strpos($_REQUEST['ISPOLNITEL'], 'all_')) {
            $filterType = str_replace('all_', '', $_REQUEST['ISPOLNITEL']);
        }
        $arKuratorReject = [];
        $dateNight = strtotime(date('d.m.Y 00:00:00'));
        $arOrderIds = [];
        $arOrderIspolnitel = [];
        $arOrderNotStats = [];
        while ($arFields = $res->GetNext()) {
            /*
             * Делегированные поручения засчитывать первому исполнителю.
             */
            if (
                !empty($arFields['PROPERTY_DELEGATION_VALUE']) &&
                $arFields['PROPERTY_DELEGATION_VALUE'][0] > 0
            ) {
                $arFields['PROPERTY_ISPOLNITEL_VALUE'] = $arFields['PROPERTY_DELEGATION_VALUE'][0];
            }

            /*
             *  Статистику строить по полю "Первый исполнитель".
             */
            if (!empty($arFields['PROPERTY_FIRST_EXECUTOR'])) {
                $arFields['PROPERTY_ISPOLNITEL_VALUE'] = $arFields['PROPERTY_FIRST_EXECUTOR'];
            }

            if (
                $filterType != '' &&
                $this->ispolnitels[ $arFields['PROPERTY_ISPOLNITEL_VALUE'] ]['PROPERTY_TYPE_ENUM_ID'] != $filterType
            ) {
                continue;
            }

            if (!isset($arDataStats['ISPOLNITELS_DISCIPLIN'][ $arFields['PROPERTY_ISPOLNITEL_VALUE'] ])) {
                $arDataStats['ISPOLNITELS_DISCIPLIN'][ $arFields['PROPERTY_ISPOLNITEL_VALUE'] ] = [
                    'full'          => 0,
                    'worked'        => 0,
                    'v_srok'        => 0,
                    'srok_narush'   => 0,
                    'no_ispoln'     => 0,
                ];
            }

            $date_ispoln = strtotime($arFields['PROPERTY_DATE_ISPOLN_VALUE']);
            $arDataStats['ISPOLNITELS_DISCIPLIN'][ $arFields['PROPERTY_ISPOLNITEL_VALUE'] ]['full']++;

            $ispDiscKey = '';

            /*
             * «Не выполнено» - те поручения, у которых в разделе «Тип поручения» стоит отметка «Не выполнено»
             * (соответственно, эти поручения не учитываются в статистике по другим категориям поручений –
             * «Выполнено в срок», «Выполнено с нарушением сроков», «На исполнении»).
             */
            if (
                in_array('no_ispoln', $arFields['PROPERTY_TYPE_VALUE']) &&
                empty($arFields['PROPERTY_NOT_STATS_ENUM_ID'])
            ) {
                $ispDiscKey = 'no_ispoln';
            } else {
                /*
                 * «На исполнении» - поручения, которые находятся в работе
                 * (новое, на исполнении, ждет контроля, ждет решения), если в них не стоит отметка «Не выполнено».
                 */
                $ispDiscKey = 'worked';

                if ($arFields['PROPERTY_ACTION_ENUM_ID'] == Settings::$arActions['ARCHIVE']) {
                    /*
                     * «Выполнено в срок» - поручения, которые сняты с контроля без отметки о нарушении срока
                     * (если решим сделать вышеназванную отметку; если нет – то надо думать, как отсекать от выполненных с нарушением сроков).
                     */
                    $ispDiscKey = 'v_srok';

                    /*
                     * «Выполнено с нарушением сроков» - те поручения, в которых фактический срок исполнения
                     * превышает плановый срок, и поручение снято с контроля.
                     */
                    if (in_array('srok_narush', $arFields['PROPERTY_TYPE_VALUE']) && empty($arFields['PROPERTY_NOT_STATS_ENUM_ID'])) {
                        $ispDiscKey = 'srok_narush';
                    }
                }
            }

            $arDataStats['ISPOLNITELS_DISCIPLIN'][ $arFields['PROPERTY_ISPOLNITEL_VALUE'] ][ $ispDiscKey ]++;

            if ($arDataStats['CATEGORY'][ $arFields['PROPERTY_CATEGORY_ENUM_ID'] ] != '') {
                $arDataStats['CATEGORY'][ $arFields['PROPERTY_CATEGORY_ENUM_ID'] ]++;
            } else {
                $arDataStats['CATEGORY'][ $arFields['PROPERTY_CATEGORY_ENUM_ID'] ] = 1;
            }

            if ($arDataStats['THEMES'][ $arFields['PROPERTY_THEME_VALUE'] ] != '') {
                $arDataStats['THEMES'][ $arFields['PROPERTY_THEME_VALUE'] ]++;
            } else {
                $arDataStats['THEMES'][ $arFields['PROPERTY_THEME_VALUE'] ] = 1;
            }

            if ($arDataStats['CAT_THEMES'][ $arFields['PROPERTY_CAT_THEME_VALUE'] ] != '') {
                $arDataStats['CAT_THEMES'][ $arFields['PROPERTY_CAT_THEME_VALUE'] ]++;
            } else {
                $arDataStats['CAT_THEMES'][ $arFields['PROPERTY_CAT_THEME_VALUE'] ] = 1;
            }

            if ($arDataStats['ACTION'][ $arFields['PROPERTY_ACTION_ENUM_ID'] ] != '') {
                $arDataStats['ACTION'][ $arFields['PROPERTY_ACTION_ENUM_ID'] ]++;
            } else {
                $arDataStats['ACTION'][ $arFields['PROPERTY_ACTION_ENUM_ID'] ] = 1;
            }

            if ($arDataStats['ISPOLNITELS_FULL'][ $arFields['PROPERTY_ISPOLNITEL_VALUE'] ] != '') {
                $arDataStats['ISPOLNITELS_FULL'][ $arFields['PROPERTY_ISPOLNITEL_VALUE'] ]++;
            } else {
                $arDataStats['ISPOLNITELS_FULL'][ $arFields['PROPERTY_ISPOLNITEL_VALUE'] ] = 1;
            }

            if ($arDataStats['ISPOLNITELS_ACTION'][ $arFields['PROPERTY_ISPOLNITEL_VALUE'] ][ $arFields['PROPERTY_ACTION_ENUM_ID'] ] != '') {
                $arDataStats['ISPOLNITELS_ACTION'][ $arFields['PROPERTY_ISPOLNITEL_VALUE'] ][ $arFields['PROPERTY_ACTION_ENUM_ID'] ]++;
            } else {
                $arDataStats['ISPOLNITELS_ACTION'][ $arFields['PROPERTY_ISPOLNITEL_VALUE'] ][ $arFields['PROPERTY_ACTION_ENUM_ID'] ] = 1;
            }

            if (count($arFields['PROPERTY_DATE_ISPOLN_HIST_VALUE']) > 0) {
                if ($arDataStats['ISPOLN_HIST'][ $arFields['PROPERTY_ISPOLNITEL_VALUE'] ][ $arFields['PROPERTY_ACTION_ENUM_ID'] ] != '') {
                    $arDataStats['ISPOLN_HIST'][ $arFields['PROPERTY_ISPOLNITEL_VALUE'] ][ $arFields['PROPERTY_ACTION_ENUM_ID'] ]++;
                } else {
                    $arDataStats['ISPOLN_HIST'][ $arFields['PROPERTY_ISPOLNITEL_VALUE'] ][ $arFields['PROPERTY_ACTION_ENUM_ID'] ] = 1;
                }
            }

            if (count($arFields['PROPERTY_DATE_ISPOLN_BAD_VALUE']) > 0) {
                if ($arDataStats['ISPOLN_BAD'][ $arFields['PROPERTY_ISPOLNITEL_VALUE'] ] != '') {
                    $arDataStats['ISPOLN_BAD'][ $arFields['PROPERTY_ISPOLNITEL_VALUE'] ] += count($arFields['PROPERTY_DATE_ISPOLN_BAD_VALUE']);
                } else {
                    $arDataStats['ISPOLN_BAD'][ $arFields['PROPERTY_ISPOLNITEL_VALUE'] ] = count($arFields['PROPERTY_DATE_ISPOLN_BAD_VALUE']);
                }
            }

            if (in_array('7qCIhAcZ', $arFields['PROPERTY_TYPE_VALUE'])) {
                if ($arDataStats['PROBLEMS'][ $arFields['PROPERTY_ISPOLNITEL_VALUE'] ][ $arFields['PROPERTY_ACTION_ENUM_ID'] ] != '') {
                    $arDataStats['PROBLEMS'][ $arFields['PROPERTY_ISPOLNITEL_VALUE'] ][ $arFields['PROPERTY_ACTION_ENUM_ID'] ]++;
                } else {
                    $arDataStats['PROBLEMS'][ $arFields['PROPERTY_ISPOLNITEL_VALUE'] ][ $arFields['PROPERTY_ACTION_ENUM_ID'] ] = 1;
                }
            }

            if (
                empty($arFields['PROPERTY_NOT_STATS_ENUM_ID']) &&
                !empty($arFields['PROPERTY_CONTROL_REJECT_VALUE'])
            ) {
                $cnt = 0;
                $ts = strtotime('01.01.2021');
                foreach ($arFields['PROPERTY_CONTROL_REJECT_VALUE'] as $reject) {
                    $arRej = explode(':', $reject);
                    if ($arRej[1] >= $ts) {
                        $cnt++;
                    }
                }

                if ($cnt > 0) {
                    if (isset($arDataStats['CONTROL_REJECT'][ $arFields['PROPERTY_ISPOLNITEL_VALUE'] ])) {
                        $arDataStats['CONTROL_REJECT'][ $arFields['PROPERTY_ISPOLNITEL_VALUE'] ] += $cnt;
                    } else {
                        $arDataStats['CONTROL_REJECT'][ $arFields['PROPERTY_ISPOLNITEL_VALUE'] ] = $cnt;
                    }
                }
            }

            if (
                empty($arFields['PROPERTY_NOT_STATS_ENUM_ID']) &&
                $arFields['PROPERTY_ACTION_ENUM_ID'] == Settings::$arActions['WORK'] &&
                $date_ispoln < $dateNight
            ) {
                if (isset($arDataStats['EXPIRED'][ $arFields['PROPERTY_ISPOLNITEL_VALUE'] ])) {
                    $arDataStats['EXPIRED'][ $arFields['PROPERTY_ISPOLNITEL_VALUE'] ]++;
                } else {
                    $arDataStats['EXPIRED'][ $arFields['PROPERTY_ISPOLNITEL_VALUE'] ] = 1;
                }
            }

            foreach ($arFields['~PROPERTY_HISTORY_SROK_VALUE'] as $row) {
                $arHistoryRow = json_decode($row, true);
                if (false !== mb_strpos($arHistoryRow['TEXT'], 'Отправлено на доработку контролеру')) {
                    $arKuratorReject[ $arFields['ID'] ][] = $arHistoryRow;
                }
            }

            $arOrderIds[] = $arFields['ID'];
            $arOrderIspolnitel[ $arFields['ID'] ] = $arFields['PROPERTY_ISPOLNITEL_VALUE'];
            if (!empty($arFields['PROPERTY_NOT_STATS_ENUM_ID'])) {
                $arOrderNotStats[] = $arFields['ID'];
            }
        }

        $resBrokenReport = CIBlockElement::GetList(
            [
                'ID' => 'ASC'
            ],
            [
                'IBLOCK_ID'             => Settings::$iblockId['ORDERS_COMMENT'],
                'PROPERTY_TYPE'         => 1131,
                'PROPERTY_BROKEN_SROK'  => 'Y',
                '>=DATE_CREATE'         => '01.01.2021',
            ],
            false,
            false,
            [
                'ID',
                'PROPERTY_PORUCH',
            ]
        );
        while ($rowBrokenReport = $resBrokenReport->Fetch()) {
            if (in_array($rowBrokenReport['PROPERTY_PORUCH_VALUE'], $arOrderNotStats)) {
                continue;
            }
            $execId = $arOrderIspolnitel[ $rowBrokenReport['PROPERTY_PORUCH_VALUE'] ];
            if (isset($arDataStats['BROKEN_SROK'][ $execId ])) {
                $arDataStats['BROKEN_SROK'][ $execId ]++;
            } else {
                $arDataStats['BROKEN_SROK'][ $execId ] = 1;
            }
        }

        if (
            $this->permissions['controler'] ||
            $this->permissions['kurator'] ||
            $GLOBALS['USER']->IsAdmin()
        ) {
            /*
             * Сколько каждый контролер добавил отчетов с пометкой != На снятие с контроля
             */
            $arReportFilter = [
                'ACTIVE'            => 'Y',
                'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
                'PROPERTY_TYPE'     => 1132,
            ];
            $arDateFilter = [];

            if (!empty($_REQUEST['FROM'])) {
                $obDateFrom = new DateTimeImmutable($_REQUEST['FROM']);
                $arDateFilter['>=DATE_CREATE'] = $obDateFrom->format('Y-m-d');
            }
            if (!empty($_REQUEST['TO'])) {
                $obDateTo = new DateTimeImmutable($_REQUEST['TO']);
                $arDateFilter['<=DATE_CREATE'] = $obDateTo->format('Y-m-d');
            }
            if (!empty($arDateFilter)) {
                $arDateFilter['LOGIC'] = 'AND';
                $arReportFilter[] = $arDateFilter;
            }

            $arDataStats['CONTROLER_REJECT'] = [];
            $res = CIBlockElement::GetList(
                [
                    'ID' => 'ASC'
                ],
                $arReportFilter,
                false,
                false,
                [
                    'ID',
                    'DATE_CREATE',
                    'PROPERTY_PORUCH',
                    'PROPERTY_USER',
                    'PROPERTY_CHANGES',
                ]
            );
            $arControlerToOrder = [];
            while ($arFields = $res->GetNext()) {
                if (!in_array($arFields['PROPERTY_PORUCH_VALUE'], $arOrderIds)) {
                    continue;
                }
                $uId = $arFields['PROPERTY_USER_VALUE'];
                if (in_array($uId, [1112, 1151])) {
                    continue;
                }
                $arControlerToOrder[ $arFields['PROPERTY_PORUCH_VALUE'] ][ $uId ][] = $arFields['DATE_CREATE'];
                if (empty($arFields['~PROPERTY_CHANGES_VALUE']['TEXT'])) {
                    continue;
                }
                if (!isset($arDataStats['CONTROLER_REJECT'][ $uId ])) {
                    $arDataStats['CONTROLER_REJECT'][ $uId ] = 0;
                }
                $arChanges = json_decode($arFields['~PROPERTY_CHANGES_VALUE']['TEXT'], true);
                if (
                    isset($arChanges['CONTROLER_RESH']) &&
                    $arChanges['CONTROLER_RESH'] != 1276
                ) {
                    $arDataStats['CONTROLER_REJECT'][ $arFields['PROPERTY_USER_VALUE'] ]++;
                }
            }
            arsort($arDataStats['CONTROLER_REJECT']);

            /*
             * Сколько поручений каждого контролера отклонил Куратор
             */
            $arDataStats['KURATOR_REJECT'] = [];
            foreach ($arKuratorReject as $orderId => $arRejects) {
                foreach ($arRejects as $reject) {
                    $uId = 0;
                    foreach ($arControlerToOrder[ $orderId ] as $userId => $arReports) {
                        foreach ($arReports as $report) {
                            if (strtotime($report) <= $reject['DATE_TIME']) {
                                $uId = $userId;
                            }
                        }
                    }
                    if ($uId > 0) {
                        if (!isset($arDataStats['KURATOR_REJECT'][ $uId ])) {
                            $arDataStats['KURATOR_REJECT'][ $uId ] = 0;
                        }
                        $arDataStats['KURATOR_REJECT'][ $uId ]++;
                    }
                }
            }
            arsort($arDataStats['KURATOR_REJECT']);
            if (empty($arDataStats['KURATOR_REJECT'])) {
                unset($arDataStats['KURATOR_REJECT']);
            }
        }

        return $arDataStats;
    }

    /**
     * Собрать статистику по поручениям для виджета
     *
     * @return array
     *
     * @throws Exception
     */
    public function getWidgetStats(bool $full = false)
    {
        $arResult = [
            'executors' => [],
            'roles'     => [],
            'stats'     => [],
            'count'     => [],
            'settings'  => [],
            'table'     => [
                'ispolnitel' => [
                    'full',
                    'v_srok',
                    'srok_narush',
                    'no_ispoln',
                    'soon',
                    'expired',
                ],
                'status' => [
                    'new',
                    'work',
                    'visa',
                    'sign',
                    'control',
                    'dopcontrol',
                    'archive',
                    'accomplience',
                    'delegate',
                ],
                'rukovoditel' => [
                    'rukl_work',
                    'rukl_accomplience',
                    'rukl_visa',
                    'rukl_sign',
                    'rukl_delegate',
                ],
            ],
        ];

        $curUserId = $GLOBALS['USER']->GetID();

        $arDefaultSettings = [
            'full',
            'v_srok',
            'no_ispoln',
            'soon',
            'expired',
        ];
        $arResult['settings'] = CUserOptions::GetOption('citto:checkorders', 'widget_stats', $arDefaultSettings, $curUserId);

        $obOrders = new Orders();
        $tsNow = strtotime(date('d.m.Y 00:00:00'));
        $tsSoon = ($tsNow - 7*86400);

        $arExecutors = Executors::getList();
        foreach ($arExecutors as $arExecutor) {
            $arCurList = array_merge(
                [$arExecutor['PROPERTY_RUKOVODITEL_VALUE']],
                $arExecutor['PROPERTY_ISPOLNITELI_VALUE'],
                $arExecutor['PROPERTY_ZAMESTITELI_VALUE'],
                $arExecutor['PROPERTY_IMPLEMENTATION_VALUE']
            );

            if (in_array($curUserId, $arCurList)) {
                $arResult['executors'][ $arExecutor['ID'] ] = $arExecutor['NAME'];

                if ($curUserId == $arExecutor['PROPERTY_RUKOVODITEL_VALUE']) {
                    $arResult['roles'][ $arExecutor['ID'] ][] = 'RUKOVODITEL';
                }
                if (in_array($curUserId, $arExecutor['PROPERTY_ISPOLNITELI_VALUE'])) {
                    $arResult['roles'][ $arExecutor['ID'] ][] = 'ISPOLNITEL';
                }
                if (in_array($curUserId, $arExecutor['PROPERTY_ZAMESTITELI_VALUE'])) {
                    $arResult['roles'][ $arExecutor['ID'] ][] = 'ZAMESTITEL';
                }
                if (in_array($curUserId, $arExecutor['PROPERTY_IMPLEMENTATION_VALUE'])) {
                    $arResult['roles'][ $arExecutor['ID'] ][] = 'IMPLEMENTATION';
                }

                $arStats = [
                    'full' => [
                        'title' => 'Всего поручений',
                        'count' => [],
                        'link'  => '/control-orders/?all&widget_ispolnitel=' . $arExecutor['ID'],
                    ],
                    'v_srok' => [
                        'title' => 'Выполнено в срок',
                        'count' => [],
                        'link'  => '/control-orders/?all&widget_ispolnitel=' . $arExecutor['ID'] . '&from_stats=v_srok',
                    ],
                    'srok_narush' => [
                        'title' => 'Выполнено с нарушением сроков',
                        'count' => [],
                        'link'  => '/control-orders/?all&widget_ispolnitel=' . $arExecutor['ID'] . '&from_stats=srok_narush',
                    ],
                    'no_ispoln' => [
                        'title' => 'Не выполнено',
                        'count' => [],
                        'link'  => '/control-orders/?all&widget_ispolnitel=' . $arExecutor['ID'] . '&from_widget=no_ispoln',
                    ],
                    'soon' => [
                        'title' => 'Подходит срок',
                        'count' => [],
                        'link'  => '/control-orders/?all&widget_ispolnitel=' . $arExecutor['ID'] . '&additional=soon',
                    ],
                    'expired' => [
                        'title' => 'Просрочено',
                        'count' => [],
                        'link'  => '/control-orders/?all&widget_ispolnitel=' . $arExecutor['ID'] . '&additional=expired',
                    ],
                    'new' => [
                        'title' => 'Новые',
                        'count' => [],
                        'link'  => '/control-orders/?all&widget_ispolnitel=' . $arExecutor['ID'] . '&action_filter=1135',
                    ],
                    'work' => [
                        'title' => 'На исполнении',
                        'count' => [],
                        'link'  => '/control-orders/?all&widget_ispolnitel=' . $arExecutor['ID'] . '&action_filter=1136',
                    ],
                    'visa' => [
                        'title' => 'На визировании',
                        'count' => [],
                        'link'  => '/control-orders/?all&widget_ispolnitel=' . $arExecutor['ID'] . '&action_filter=1136&spage=visa',
                    ],
                    'sign' => [
                        'title' => 'На подписи',
                        'count' => [],
                        'link'  => '/control-orders/?all&widget_ispolnitel=' . $arExecutor['ID'] . '&action_filter=1136&spage=sign',
                    ],
                    'control' => [
                        'title' => 'Передано на контроль',
                        'count' => [],
                        'link'  => '/control-orders/?all&widget_ispolnitel=' . $arExecutor['ID'] . '&action_filter=1137',
                    ],
                    'dopcontrol' => [
                        'title' => 'Отправлено на доп. контроль',
                        'count' => [],
                        'link'  => '/control-orders/?all&widget_ispolnitel=' . $arExecutor['ID'] . '&from_widget=dopcontrol',
                    ],
                    'archive' => [
                        'title' => 'В архиве',
                        'count' => [],
                        'link'  => '/control-orders/?all&widget_ispolnitel=' . $arExecutor['ID'] . '&action_filter=1140',
                    ],
                    'accomplience' => [
                        'title' => 'Соисполнение',
                        'count' => [],
                        'link'  => '/control-orders/?all&action_filter=1136&spage=sub',
                    ],
                    'delegate' => [
                        'title' => 'Делегировано',
                        'count' => [],
                        'link'  => '/control-orders/?all&action_filter=1136&spage=delegate',
                    ],
                    'rukl_work' => [
                        'title' => 'На исполнении',
                        'count' => [],
                        'link'  => '#',
                    ],
                    'rukl_accomplience' => [
                        'title' => 'Соисполнение',
                        'count' => [],
                        'link'  => '#',
                        'hide'  => true,
                    ],
                    'rukl_visa' => [
                        'title' => 'На визировании',
                        'count' => [],
                        'link'  => '#',
                        'hide'  => true,
                    ],
                    'rukl_sign' => [
                        'title' => 'На подписи',
                        'count' => [],
                        'link'  => '#',
                        'hide'  => true,
                    ],
                    'rukl_delegate' => [
                        'title' => 'Делегировано',
                        'count' => [],
                        'link'  => '#',
                        'hide'  => true,
                    ],
                ];

                $obRes = CIBlockElement::GetList(
                    ['ID' => 'ASC'],
                    [
                        'ACTIVE'                => 'Y',
                        'IBLOCK_ID'             => Settings::$iblockId['ORDERS'],
                        '!PROPERTY_ACTION'      => Settings::$arActions['DRAFT'],
                        'PROPERTY_ISPOLNITEL'   => $arExecutor['ID'],
                    ],
                    false,
                    false,
                    [
                        'ID',
                        'NAME',
                        'PROPERTY_DATE_ISPOLN',
                        'PROPERTY_ACTION',
                        'PROPERTY_NOT_STATS',
                        'PROPERTY_DELEGATE_USER',
                        'PROPERTY_TYPE',
                        'PROPERTY_WORK_INTER_STATUS',
                    ]
                );
                while ($arRow = $obRes->GetNext()) {
                    $tsDateIspoln = strtotime($arRow['PROPERTY_DATE_ISPOLN_VALUE']);

                    /*
                     * Всего поручений
                     */
                    $arStats['full']['count'][ $arRow['ID'] ] = $arRow['ID'];

                    if ($arRow['PROPERTY_ACTION_ENUM_ID'] != Settings::$arActions['ARCHIVE']) {
                        /*
                         * Просрочено
                         */
                        if ($tsDateIspoln <= $tsNow && empty($arRow['PROPERTY_NOT_STATS_ENUM_ID'])) {
                            $arStats['expired']['count'][ $arRow['ID'] ] = $arRow['ID'];
                        }

                        /*
                         * Подходит срок
                         */
                        if ($tsDateIspoln >= $tsSoon && $tsDateIspoln <= $tsNow) {
                            $arStats['soon']['count'][ $arRow['ID'] ] = $arRow['ID'];
                        }

                        /*
                         * Новые
                         */
                        if ($arRow['PROPERTY_ACTION_ENUM_ID'] == Settings::$arActions['NEW']) {
                            $arStats['new']['count'][ $arRow['ID'] ] = $arRow['ID'];
                        }

                        /*
                         * На исполнении
                         */
                        if ($arRow['PROPERTY_ACTION_ENUM_ID'] == Settings::$arActions['WORK']) {
                            $arStats['work']['count'][ $arRow['ID'] ] = $arRow['ID'];

                            /*
                             * Руководитель - На исполнении
                             */
                            if (empty($arRow['PROPERTY_DELEGATE_USER_VALUE'])) {
                                $arStats['rukl_work']['count'][ $arRow['ID'] ] = $arRow['ID'];
                            }
                        }

                        /*
                         * Передано на контроль
                         */
                        if ($arRow['PROPERTY_ACTION_ENUM_ID'] == Settings::$arActions['CONTROL']) {
                            $arStats['control']['count'][ $arRow['ID'] ] = $arRow['ID'];
                        }
                    } else {
                        /*
                         * Выполнено в срок
                         */
                        if (
                            !in_array('srok_narush', $arRow['PROPERTY_TYPE_VALUE']) &&
                            !in_array('no_ispoln', $arRow['PROPERTY_TYPE_VALUE'])
                        ) {
                            $arStats['v_srok']['count'][ $arRow['ID'] ] = $arRow['ID'];
                        }

                        /*
                         * Выполнено с нарушением сроков
                         */
                        if (
                            in_array('srok_narush', $arRow['PROPERTY_TYPE_VALUE']) &&
                            empty($arRow['PROPERTY_NOT_STATS_ENUM_ID'])
                        ) {
                            $arStats['srok_narush']['count'][ $arRow['ID'] ] = $arRow['ID'];
                        }

                        /*
                         * В архиве
                         */
                        if ($arRow['PROPERTY_ACTION_ENUM_ID'] == Settings::$arActions['ARCHIVE']) {
                            $arStats['archive']['count'][ $arRow['ID'] ] = $arRow['ID'];
                        }

                        /*
                         * На визировании
                         */
                        if ($arRow['PROPERTY_WORK_INTER_STATUS_ENUM_ID'] == $arParams['ENUM']['WORK_INTER_STATUS']['TOVISA']['ID']) {
                            $arStats['visa']['count'][ $arRow['ID'] ] = $arRow['ID'];
                        }

                        /*
                         * На подписи
                         */
                        if ($arRow['PROPERTY_WORK_INTER_STATUS_ENUM_ID'] == $arParams['ENUM']['WORK_INTER_STATUS']['TOSIGN']['ID']) {
                            $arStats['visa']['count'][ $arRow['ID'] ] = $arRow['ID'];
                        }

                        /*
                         * Отправлено на доп. контроль
                         */
                        if (in_array('dopcontrol', $arRow['PROPERTY_TYPE_VALUE'])) {
                            $arStats['dopcontrol']['count'][ $arRow['ID'] ] = $arRow['ID'];
                        }
                    }

                    /*
                     * Не выполнено
                     */
                    if (
                        in_array('no_ispoln', $arRow['PROPERTY_TYPE_VALUE']) &&
                        empty($arRow['PROPERTY_NOT_STATS_ENUM_ID'])
                    ) {
                        $arStats['no_ispoln']['count'][ $arRow['ID'] ] = $arRow['ID'];
                    }
                }

                /*
                 * Соисполнение
                 */
                $arAccomplienceFilter = [
                    'ACTIVE'            => 'Y',
                    'IBLOCK_ID'         => Settings::$iblockId['ORDERS'],
                    'PROPERTY_ACTION'   => Settings::$arActions['WORK'],
                    [
                        'LOGIC'                 => 'OR',
                        'PROPERTY_ACCOMPLICES'  => $curUserId,
                        'PROPERTY_SUBEXECUTOR'  => $arExecutor['ID'] . ':' . $curUserId,
                    ]
                ];
                $arStats['accomplience']['count'] = CIBlockElement::GetList([], $arAccomplienceFilter, [], false, []);

                /*
                 * Делегировано
                 */
                $arDelegateFilter = $this->getListFilter('widget_filter_' . $curUserId, 'delegate');
                $arDelegateFilter['PROPERTY_ACTION'] = Settings::$arActions['WORK'];
                $arStats['delegate']['count'] = CIBlockElement::GetList([], $arDelegateFilter, [], false, []);

                /*
                 * rukl_accomplience
                 * Руководитель - Соисполнение
                 *
                 * rukl_visa
                 * Руководитель - На визировании
                 *
                 * rukl_sign
                 * Руководитель - На подписи
                 *
                 * rukl_delegate
                 * Руководитель - Делегировано
                 */

                $sum = 0;
                foreach ($arStats as $key => $value) {
                    $cnt = is_array($value['count']) ? count($value['count']) : (int)$value['count'];
                    $sum += $cnt;
                    $arStats[ $key ]['count'] = $cnt;
                }

                $arResult['stats'][ $arExecutor['ID'] ] = $arStats;
                $arResult['count'][ $arExecutor['ID'] ] = $sum;
            }
        }

        return $arResult;
    }

    /**
     * Список всех заполненных тегов из поручений
     *
     * @param array $arFilter Текущий фильтр грида.
     *
     * @return array
     */
    public function getAvailableTags(array $arFilter = []): array
    {
        if (!isset($arFilter['IBLOCK_ID'])) {
            $arFilter['IBLOCK_ID'] = Settings::$iblockId['ORDERS'];
        }

        $arFilter['ACTIVE'] = 'Y';
        $arFilter['!PROPERTY_ISPOLNITEL'] = [7770, 250530, 251527];
        if (
            $GLOBALS['USER']->GetID() != 1151 &&
            isset($this->permissions['ispolnitel_data']) &&
            in_array($this->permissions['ispolnitel_data']['ID'], [7770, 250530, 251527])
        ) {
            unset($arFilter['!PROPERTY_ISPOLNITEL']);
        }
        $arResult = [];
        $obCache = new CPHPCache();
        if ($obCache->InitCache(300, md5(__METHOD__ . serialize($arFilter)), '/citto/controlorders/')) {
            $arResult = $obCache->GetVars();
        } elseif ($obCache->StartDataCache()) {
            $res = CIBlockElement::GetList(
                false,
                $arFilter,
                false,
                false,
                ['ID', 'TAGS']
            );
            $arResult = [];
            while ($arFields = $res->GetNext()) {
                $arTags = explode(',', $arFields['TAGS']);
                $arTags = array_unique($arTags);
                $arTags = array_filter($arTags);
                $arTags = array_map('trim', $arTags);
                foreach ($arTags as $tag) {
                    $arResult[ $tag ] = $tag;
                }
            }
            $obCache->EndDataCache($arResult);
        }

        return $arResult;
    }

    /**
     * Получить список Enum`ов
     *
     * @param integer $iblockId ID инфоблока.
     *
     * @return array
     */
    public function getEnums(int $iblockId = 0): array
    {
        if ($iblockId <= 0) {
            $iblockId = Settings::$iblockId['ORDERS'];
        }
        $arEnums = [];
        $obCache = new CPHPCache();
        if ($obCache->InitCache(86400, md5(__METHOD__ . $iblockId), '/citto/controlorders/')) {
            $arEnums = $obCache->GetVars();
        } elseif ($obCache->StartDataCache()) {
            $res = CIBlockPropertyEnum::GetList(
                [
                    'DEF'  => 'DESC',
                    'SORT' => 'ASC'
                ],
                [
                    'IBLOCK_ID' => $iblockId
                ]
            );
            while ($row = $res->Fetch()) {
                $arEnums[ $row['PROPERTY_CODE'] ][ $row['ID'] ] = $row;
                $arEnums[ $row['PROPERTY_CODE'] ][ $row['XML_ID'] ] = $row;
            }
            $obCache->EndDataCache($arEnums);
        }
        return $arEnums;
    }

    /**
     * Формирование фильтра для списка поручений
     *
     * @param string $listId ID грида.
     * @param string $page   Тип страницы.
     *
     * @return array
     *
     * @throws Exception
     */
    public function getListFilter(string $listId = '', $page = ''): array
    {
        $curUserId = $GLOBALS['USER']->GetID();
        $this->initPermissions();

        $arUiFilter = (new Options($listId . '_filter'))->getFilter([]);

        if ($this->permissions['full_access']) {
            $arFilter = [
                [[],[],[]],
                'IBLOCK_ID' => Settings::$iblockId['ORDERS'],
                'ACTIVE'    => 'Y',
            ];
        } else {
            $arFilter = [
                $this->getPermisionFilter($curUserId),
                'IBLOCK_ID' => Settings::$iblockId['ORDERS'],
                'ACTIVE'    => 'Y',
            ];
        }

        if ($arUiFilter['FIND'] != '') {
            $arFind = explode('|', $arUiFilter['FIND']);
            $arFindFilter = [
                'LOGIC'         => 'OR',
            ];
            foreach ($arFind as $find) {
                $arFindFilter[] = ['NAME' => '%' . $find . '%'];
                $arFindFilter[] = ['PREVIEW_TEXT' => '%' . $find . '%'];
                $arFindFilter[] = ['DETAIL_TEXT' => '%' . $find . '%'];
            }

            $arFilter[] = $arFindFilter;
        }

        if (isset($arUiFilter['ISPOLNITEL']) && count($arUiFilter['ISPOLNITEL']) > 0) {
            if (
                $this->permissions['controler'] ||
                $this->permissions['kurator'] ||
                $this->permissions['full_access']
            ) {
                $arExecutors = [];
                foreach ($arUiFilter['ISPOLNITEL'] as $execCode) {
                    if (false !== mb_strpos($execCode, 'all-')) {
                        $findExecutor = (int)str_replace('all-', '', $execCode);
                        foreach ($this->ispolnitels as $exec) {
                            if ($exec['PROPERTY_TYPE_ENUM_ID'] == $findExecutor) {
                                $arExecutors[] = $exec['ID'];
                            }
                        }
                    } else {
                        $arExecutors[] = $execCode;
                    }
                }
                $arFilter[] = [
                    'LOGIC'                 => 'OR',
                    'PROPERTY_ISPOLNITEL'   => $arExecutors,
                    'PROPERTY_DELEGATION'   => $arExecutors,
                ];
            }
        }

        if (isset($arUiFilter['SUBEXECUTOR']) && count($arUiFilter['SUBEXECUTOR']) > 0) {
            if (
                $this->permissions['controler'] ||
                $this->permissions['kurator'] ||
                $this->permissions['full_access']
            ) {
                $arSubExecutors = [];
                foreach ($arUiFilter['SUBEXECUTOR'] as $execCode) {
                    if (false !== mb_strpos($execCode, 'all-')) {
                        $findExecutor = (int)str_replace('all-', '', $execCode);
                        foreach ($this->ispolnitels as $exec) {
                            if ($exec['PROPERTY_TYPE_ENUM_ID'] == $findExecutor) {
                                $arSubExecutors[] = $exec['ID'] . ':%';
                            }
                        }
                    } else {
                        $arSubExecutors[] = $execCode . ':%';
                    }
                }
                $arFilter[] = [
                    'PROPERTY_SUBEXECUTOR' => $arSubExecutors,
                ];
            }
        }

        if (isset($arUiFilter['DELEGATE']) && count($arUiFilter['DELEGATE']) > 0) {
            $delegateFilter = [
                'LOGIC' => 'OR',
                'PROPERTY_DELEGATE_USER' => $arUiFilter['DELEGATE'],
                'PROPERTY_ACCOMPLICES' => $arUiFilter['DELEGATE'],
            ];
            foreach ($arUiFilter['DELEGATE'] as $delegUid) {
                $delegateFilter[] = ['PROPERTY_DELEGATE_HISTORY' => '%"CURRENT_USER":"' . $delegUid . '",%"DELEGATE":%'];
                $delegateFilter[] = ['PROPERTY_DELEGATE_HISTORY' => '%"CURRENT_USER":' . $delegUid . ',%"DELEGATE":%'];
            }
            $arFilter[] = $delegateFilter;
        }

        if (isset($arUiFilter['TYPE']) && count($arUiFilter['TYPE']) > 0) {
            $arFilter['PROPERTY_TYPE'] = $arUiFilter['TYPE'];
        }

        if ($arUiFilter['NUMBER'] != '') {
            $arFilter['=PROPERTY_NUMBER'] = $arUiFilter['NUMBER'];
        }

        if ($arUiFilter['ISPOLNITEL_TYPE'] != '') {
            if ($arUiFilter['ISPOLNITEL_TYPE'] == 'MAIN') {
                $arFilter['PROPERTY_DELEGATE_USER'] = $curUserId;
            } elseif ($arUiFilter['ISPOLNITEL_TYPE'] == 'SUB') {
                $arFilter['PROPERTY_ACCOMPLICES'] = $curUserId;
            }
        }

        if ($arUiFilter['TAGS'] != '') {
            $arFilter['%TAGS'] = $arUiFilter['TAGS'];
        }

        if (!empty($arUiFilter['DATE_CREATE_from'])) {
            $obDateFrom = new DateTimeImmutable($arUiFilter['DATE_CREATE_from']);
            $arFilter['>=PROPERTY_DATE_CREATE'] = $obDateFrom->format('Y-m-d');
        }

        if (!empty($arUiFilter['DATE_CREATE_to'])) {
            $obDateTo = new DateTimeImmutable($arUiFilter['DATE_CREATE_to']);
            $arFilter['<=PROPERTY_DATE_CREATE'] = $obDateTo->format('Y-m-d');
        }

        if (!empty($arUiFilter['DATE_ISPOLN_from'])) {
            $obDateFrom = new DateTimeImmutable($arUiFilter['DATE_ISPOLN_from']);
            $arFilter['>=PROPERTY_DATE_ISPOLN'] = $obDateFrom->format('Y-m-d');
        }

        if (!empty($arUiFilter['DATE_ISPOLN_to'])) {
            $obDateTo = new DateTimeImmutable($arUiFilter['DATE_ISPOLN_to']);
            $arFilter['<=PROPERTY_DATE_ISPOLN'] = $obDateTo->format('Y-m-d');
        }

        if (!empty($arUiFilter['OBJECT'])) {
            $arFilter['PROPERTY_OBJECT'] = $arUiFilter['OBJECT'];
        }

        /*
        if ($arUiFilter['OBJECT'] != '') {
            $arObjectFilter = [
                'ACTIVE'        => 'Y',
                'IBLOCK_ID'     => Settings::$iblockId['ORDERS_OBJECT'],
                [
                    'LOGIC'         => 'OR',
                    'NAME'          => '%' . $arUiFilter['OBJECT'] . '%',
                    'PREVIEW_TEXT'  => '%' . $arUiFilter['OBJECT'] . '%',
                    'DETAIL_TEXT'   => '%' . $arUiFilter['OBJECT'] . '%',
                ]
            ];
            $arObjects = [];
            $obObject = CIBlockElement::GetList(
                [],
                $arObjectFilter,
                false,
                false,
                ['ID']
            );
            while ($arObject = $obObject->GetNext()) {
                $arObjects[] = (int)$arObject['ID'];
            }
            if (empty($arObjects)) {
                $arObjects[] = -1;
            }

            $arFilter['PROPERTY_OBJECT'] = $arObjects;
        }
        */

        if ($_REQUEST['cat_theme'] != '') {
            if ($_REQUEST['cat_theme'] == 'without') {
                $arFilter['PROPERTY_CAT_THEME'] = false;
            } else {
                $arFilter['PROPERTY_CAT_THEME'] = $_REQUEST['cat_theme'];
            }
        }

        if ($arUiFilter['THEME']) {
            $arThemes = [];
            foreach ($arUiFilter['THEME'] as $theme) {
                if (0 === mb_strpos($theme, 'element_')) {
                    $arThemes[] = ['PROPERTY_THEME' => str_replace('element_', '', $theme)];
                } elseif (0 === mb_strpos($theme, 'section_')) {
                    $arThemes[] = ['PROPERTY_CAT_THEME' => str_replace('section_', '', $theme)];
                }
            }
            if (!empty($arThemes)) {
                $arFilter[] = array_merge(
                    ['LOGIC' => 'OR'],
                    $arThemes
                );
            }
        }

        if ($_REQUEST['ispolnitel'] != '') {
            $arFilter[] = [
                'LOGIC' => 'OR',
                [
                    '=PROPERTY_ISPOLNITEL'  => (int)$_REQUEST['ispolnitel'],
                    'PROPERTY_DELEGATION'   => false,
                ],
                [
                    '!PROPERTY_ISPOLNITEL'  => (int)$_REQUEST['ispolnitel'],
                    '=PROPERTY_DELEGATION'  => (int)$_REQUEST['ispolnitel'],
                ],
            ];
        }
        if ($_REQUEST['widget_ispolnitel'] != '') {
            $arFilter[] = [
                '=PROPERTY_ISPOLNITEL'  => (int)$_REQUEST['widget_ispolnitel']
            ];
        }
        if ($_REQUEST['objectId'] != '') {
            $arFilter[] = [
                'PROPERTY_OBJECT'  => (int)$_REQUEST['objectId']
            ];
        }

        if ($_REQUEST['action_filter']) {
            $arFilter['PROPERTY_ACTION'] = $_REQUEST['action_filter'];

            if ($_REQUEST['action_filter'] == Settings::$arActions['WORK']) {
                $arFilter['PROPERTY_ACTION'] = [
                    Settings::$arActions['WORK'],
                    Settings::$arActions['CURATOR_COMMENTS']
                ];
            }
        } elseif ($arUiFilter['STATUS'] != '') {
            $arFilter['PROPERTY_ACTION'] = $arUiFilter['STATUS'];
        } elseif ($arUiFilter['ARCHIVE'] == 'N') {
            $arFilter['!PROPERTY_ACTION'] = [
                Settings::$arActions['ARCHIVE'],
                Settings::$arActions['DRAFT'],
            ];
        } else {
            $arFilter['!PROPERTY_ACTION'] = [
                Settings::$arActions['DRAFT'],
            ];
        }

        if ($_REQUEST['resh']) {
            $arFilter['PROPERTY_CONTROLER_RESH'] = $_REQUEST['resh'];
        }

        if ($_REQUEST['action_filter'] == Settings::$arActions['CONTROL'] && $_REQUEST['cont_obr'] != '') {
            $arFilter['PROPERTY_CONTROLER_STATUS'] = $this->arControlerStatuses[ $_REQUEST['cont_obr'] ]['ID'];
        }

        if (isset($_REQUEST['from_stats'])) {
            if (isset($_REQUEST['onlypositions'])) {
                $arFilter['!PROPERTY_POSITION_TO'] = false;
            } else {
                $arFilter['PROPERTY_POSITION_TO'] = false;
            }

            switch ($_REQUEST['from_stats']) {
                case 'problem':
                    $arFilter['PROPERTY_TYPE'] = ['7qCIhAcZ'];
                    break;
                case 'ispoln_hist':
                    $arFilter['!PROPERTY_DATE_ISPOLN_HIST'] = false;
                    break;
                case 'ispoln_bad':
                    $arFilter['!PROPERTY_DATE_ISPOLN_BAD'] = false;
                    break;
                case 'v_srok':
                    $arFilter['PROPERTY_ACTION'] = Settings::$arActions['ARCHIVE'];
                    $arFilter[] = [
                        'LOGIC' => 'OR',
                        [
                            'PROPERTY_TYPE'         => 'no_ispoln',
                            '!PROPERTY_NOT_STATS'   => false,
                        ],
                        [
                            'PROPERTY_TYPE'         => 'srok_narush',
                            '!PROPERTY_NOT_STATS'   => false,
                        ],
                        [
                            '!PROPERTY_TYPE' => ['srok_narush', 'no_ispoln'],
                        ],
                    ];
                    break;
                case 'srok_narush':
                    $arFilter['PROPERTY_NOT_STATS'] = false;
                    $arFilter['PROPERTY_ACTION'] = Settings::$arActions['ARCHIVE'];
                    $arFilter['PROPERTY_TYPE'] = ['srok_narush'];
                    break;
                case 'worked':
                    $arFilter['!PROPERTY_ACTION'] = Settings::$arActions['ARCHIVE'];
                    $arFilter[] = [
                        'LOGIC' => 'OR',
                        [
                            'PROPERTY_TYPE'         => 'no_ispoln',
                            '!PROPERTY_NOT_STATS'   => false,
                        ],
                        [
                            '!PROPERTY_TYPE' => 'no_ispoln',
                        ],
                    ];
                    break;
                case 'no_ispoln':
                    $arFilter['PROPERTY_NOT_STATS'] = false;
                    $arFilter['PROPERTY_TYPE'] = ['no_ispoln'];
                    break;
                case 'only-red':
                    $arFilter['!PROPERTY_ACTION'] = [
                        Settings::$arActions['DRAFT'],
                        Settings::$arActions['ARCHIVE'],
                    ];
                    $arFilter['PROPERTY_TYPE'] = 'no_ispoln';
                    break;
                case 'only-orange':
                    $arFilter['PROPERTY_ACTION'] = Settings::$arActions['WORK'];
                    $arFilter['<PROPERTY_DATE_ISPOLN'] = date('Y-m-d');
                    break;
                case 'expired':
                    $arFilter['PROPERTY_NOT_STATS'] = false;
                    $arFilter['<PROPERTY_DATE_ISPOLN'] = date('Y-m-d');
                    $arFilter['PROPERTY_ACTION'] = Settings::$arActions['WORK'];
                    break;
                case 'brokensrok':
                    $arFilter['PROPERTY_NOT_STATS'] = false;
                    $arFilter[] = [
                        'ID' => CIBlockElement::SubQuery(
                            'PROPERTY_PORUCH',
                            [
                                'ACTIVE'                => 'Y',
                                'IBLOCK_ID'             => Settings::$iblockId['ORDERS_COMMENT'],
                                'PROPERTY_TYPE'         => 1131,
                                'PROPERTY_BROKEN_SROK'  => 'Y',
                                '>=DATE_CREATE'         => '01.01.2021',
                            ]
                        )
                    ];
                    break;
                case 'controlreject':
                    $arFilter['PROPERTY_NOT_STATS'] = false;
                    $arFilter['!PROPERTY_CONTROL_REJECT'] = false;
                    break;
                case 'map_blue':
                    $arFilter['!PROPERTY_TYPE'] = [
                        '7qCIhAcZ',
                        'no_ispoln',
                    ];
                    break;
                case 'map_red':
                    $arFilter[] = [
                        'LOGIC' => 'OR',
                        ['PROPERTY_TYPE' => '7qCIhAcZ'],
                        ['PROPERTY_TYPE' => 'no_ispoln'],
                    ];
                    break;
                default:
                    break;
            }
        }

        if (isset($_REQUEST['from_widget'])) {
            switch ($_REQUEST['from_widget']) {
                case 'no_ispoln':
                    $arFilter['PROPERTY_TYPE'] = ['no_ispoln'];
                    break;
                case 'dopcontrol':
                    $arFilter['PROPERTY_TYPE'] = ['dopcontrol'];
                    break;
                default:
                    break;
            }
        }

        if (isset($_REQUEST['category'])) {
            $arFilter['PROPERTY_CATEGORY'] = $_REQUEST['category'];
        }

        $arParams['ENUM'] = $this->getEnums(Settings::$iblockId['ORDERS']);
        switch ($page) {
            case 'my':
                unset($arFilter[0][0]);
                $arFilter[99999] = [
                    'PROPERTY_DELEGATE_USER' => $curUserId,
                ];
                break;
            case 'sign':
                unset($arFilter[0][0]);
                $arFilter[0] = [
                    'PROPERTY_WORK_INTER_STATUS' => $arParams['ENUM']['WORK_INTER_STATUS']['TOSIGN']['ID'],
                    'ID' => CIBlockElement::SubQuery(
                        'PROPERTY_PORUCH',
                        [
                            'ACTIVE'                => 'Y',
                            'IBLOCK_ID'             => Settings::$iblockId['ORDERS_COMMENT'],
                            'PROPERTY_TYPE'         => 1131,
                            'PROPERTY_CURRENT_USER' => $this->permissions['ispolnitel_data']['PROPERTY_RUKOVODITEL_VALUE'],
                        ]
                    ),
                ];
                break;
            case 'sign_my':
                unset($arFilter[0][0]);
                $arFilter[0] = [
                    'PROPERTY_WORK_INTER_STATUS' => $arParams['ENUM']['WORK_INTER_STATUS']['TOSIGN']['ID'],
                    'ID' => CIBlockElement::SubQuery(
                        'PROPERTY_PORUCH',
                        [
                            'ACTIVE'                => 'Y',
                            'IBLOCK_ID'             => Settings::$iblockId['ORDERS_COMMENT'],
                            'PROPERTY_TYPE'         => 1131,
                            'PROPERTY_CURRENT_USER' => $curUserId,
                            'PROPERTY_SIGNER'       => $curUserId,
                        ]
                    ),
                ];
                break;
            case 'sign_other':
                unset($arFilter[0][0]);
                $arFilter[0] = [
                    'PROPERTY_WORK_INTER_STATUS' => $arParams['ENUM']['WORK_INTER_STATUS']['TOSIGN']['ID'],
                    'ID' => CIBlockElement::SubQuery(
                        'PROPERTY_PORUCH',
                        [
                            'ACTIVE'                => 'Y',
                            'IBLOCK_ID'             => Settings::$iblockId['ORDERS_COMMENT'],
                            'PROPERTY_TYPE'         => 1131,
                            'PROPERTY_CURRENT_USER' => $this->permissions['ispolnitel_data']['PROPERTY_RUKOVODITEL_VALUE'],
                            '!PROPERTY_SIGNER'      => $curUserId,
                        ]
                    ),
                ];
                break;
            case 'sub':
                unset($arFilter[0][0]);
                $arSubFilter = [
                    'LOGIC' => 'OR',
                ];
                foreach ($this->permissions['ispolnitel_ids'] as $ispId) {
                    $arExecutor = $this->ispolnitels[ $ispId ];
                    $arCurPermisions = array_merge(
                        [$arExecutor['PROPERTY_RUKOVODITEL_VALUE']],
                        $arExecutor['PROPERTY_ZAMESTITELI_VALUE'],
                        $arExecutor['PROPERTY_IMPLEMENTATION_VALUE']
                    );
                    if (in_array($curUserId, $arCurPermisions)) {
                        $arSubFilter[] = [
                            'PROPERTY_SUBEXECUTOR' => $ispId,
                        ];
                        $arSubFilter[] = [
                            'PROPERTY_SUBEXECUTOR' => $ispId . ':%',
                        ];
                    }
                }
                $arFilter[99999] = [
                    [
                        'LOGIC' => 'OR',
                        'PROPERTY_ACCOMPLICES' => $curUserId,
                        'PROPERTY_SUBEXECUTOR' => $this->permissions['ispolnitel_data']['ID'] . ':' . $curUserId,
                        $arSubFilter,
                    ],
                ];
                break;
            case 'visa':
                $arVisaFilter = [];
                $arVisaFilter['PROPERTY_WORK_INTER_STATUS'] = $arParams['ENUM']['WORK_INTER_STATUS']['TOVISA']['ID'];
                $arVisaFilter[99999] = [
                    'LOGIC' => 'OR',
                    ['ID' => CIBlockElement::SubQuery(
                        'PROPERTY_PORUCH',
                        [
                            'ACTIVE'            => 'Y',
                            'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
                            '=PROPERTY_TYPE'    => 1131,
                            'PROPERTY_VISA'     => $curUserId . ':E:%',
                        ]
                    )],
                ];
                if (
                    $this->permissions['ispolnitel_data']['PROPERTY_TYPE_CODE'] == 'gubernator' &&
                    (int)$this->permissions['ispolnitel_data']['PROPERTY_RUKOVODITEL_VALUE'] > 0
                ) {
                    $arVisaFilter[99999][] = [
                        'ID' => CIBlockElement::SubQuery(
                            'PROPERTY_PORUCH',
                            [
                                'ACTIVE'                => 'Y',
                                'IBLOCK_ID'             => Settings::$iblockId['ORDERS_COMMENT'],
                                'PROPERTY_TYPE'         => 1131,
                                'PROPERTY_CURRENT_USER' => $this->permissions['ispolnitel_data']['PROPERTY_RUKOVODITEL_VALUE'],
                            ]
                        )
                    ];
                }

                $arFilter[99999] = [
                    'LOGIC' => 'OR',
                    ['ID' => CIBlockElement::SubQuery(
                        'PROPERTY_PORUCH',
                        [
                            'ACTIVE'            => 'Y',
                            'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
                            '=PROPERTY_TYPE'    => $this->arParams['COMMENT_ENUM']['TYPE']['accomplience']['ID'],
                            'PROPERTY_VISA'     => $curUserId . ':E:%',
                        ]
                    )],
                    $arVisaFilter,
                ];
                break;
            case 'delegate':
                $arFilter[] = [
                    '!PROPERTY_DELEGATE_USER' => $curUserId,
                    [
                        'LOGIC' => 'OR',
                        ['PROPERTY_DELEGATE_HISTORY' => '%"CURRENT_USER":"' . $curUserId . '",%"DELEGATE":%'],
                        ['PROPERTY_DELEGATE_HISTORY' => '%"CURRENT_USER":' . $curUserId . ',%"DELEGATE":%'],
                    ],
                ];
                break;
            case 'new_project':
                $hlblock = HLTable::getById($this->hlblock['Resolution'])->fetch();
                $entity = HLTable::compileEntity($hlblock);
                $entityDataClass = $entity->getDataClass();
                $arResolutions = [];
                $rsData   = $entityDataClass::getList([
                    'filter' => [
                        'UF_APPROVE'    => $this->arResolutionStatus['E']['ID'],
                    ],
                    'order'  => [
                        'UF_DATE' => 'DESC',
                    ],
                ]);
                while ($arRes = $rsData->fetch()) {
                    $arResolutions[] = $arRes['UF_ORDER'];
                }
                if (empty($arResolutions)) {
                    $arResolutions = false;
                }
                $arFilter['ID'] = $arResolutions;
                break;
            case 'new_reject':
                $hlblock = HLTable::getById($this->hlblock['Resolution'])->fetch();
                $entity = HLTable::compileEntity($hlblock);
                $entityDataClass = $entity->getDataClass();
                $arResolutions = [];
                $rsData   = $entityDataClass::getList([
                    'filter' => [
                        'UF_APPROVE'    => $this->arResolutionStatus['N']['ID'],
                    ],
                    'order'  => [
                        'UF_DATE' => 'DESC',
                    ],
                ]);
                while ($arRes = $rsData->fetch()) {
                    $arResolutions[] = $arRes['UF_ORDER'];
                }
                if (empty($arResolutions)) {
                    $arResolutions = false;
                }
                $arFilter['ID'] = $arResolutions;
                break;
            case '':
            default:
                break;
        }

        $arFilter['!PROPERTY_ISPOLNITEL'] = [7770, 250530, 251527];
        if (
            $GLOBALS['USER']->GetID() != 1151 &&
            isset($this->permissions['ispolnitel_data']) &&
            in_array($this->permissions['ispolnitel_data']['ID'], [7770, 250530, 251527])
        ) {
            unset($arFilter['!PROPERTY_ISPOLNITEL']);
        }

        if (isset($_REQUEST['from_stats'])) {
            if (
                !isset($arFilter['!PROPERTY_ACTION']) ||
                in_array($_REQUEST['from_stats'], ['map_blue', 'map_red'])
            ) {
                $arFilter['!PROPERTY_ACTION'] = [];
            } elseif (!is_array($arFilter['!PROPERTY_ACTION'])) {
                $arFilter['!PROPERTY_ACTION'] = [$arFilter['!PROPERTY_ACTION']];
            }

            $arFilter['!PROPERTY_ACTION'][] = Settings::$arActions['DRAFT'];
        }

        if ($arUiFilter['ADDITIONAL'] || $_REQUEST['ADDITIONAL']) {
            $additional = $arUiFilter['ADDITIONAL'] ?? $_REQUEST['ADDITIONAL'];
            switch ($additional) {
                // Отклоненные контролерами
                case 'controler_reject':
                    $arFilter['!PROPERTY_CONTROL_REJECT'] = false;
                    break;

                // Опрос заявителя
                case 'vote':
                    $arFilter[] = [
                        'ID' => CIBlockElement::SubQuery(
                            'PROPERTY_PORUCH',
                            [
                                'ACTIVE'                => 'Y',
                                'IBLOCK_ID'             => Settings::$iblockId['ORDERS_COMMENT'],
                                '!PROPERTY_DATE_VOTE'   => false,
                            ]
                        )
                    ];
                    break;

                // Подходит срок
                case 'soon':
                    $arFilter['<PROPERTY_DATE_ISPOLN'] = date('Y-m-d');
                    $arFilter['>=PROPERTY_DATE_ISPOLN'] = date('Y-m-d', strtotime('-7 DAYS'));
                    break;

                // Просрочено
                case 'expired':
                    $arFilter['<PROPERTY_DATE_ISPOLN'] = date('Y-m-d');
                    break;

                default:
                    break;
            }
        }

        return $arFilter;
    }

    /**
     * Фильтр элементов с доступами пользователя
     *
     * @param integer $userId ID пользователя.
     *
     * @return array
     */
    public function getPermisionFilter(int $userId = 0): array
    {
        if ($this->permissions['full_access']) {
            return [];
        }
        if ($this->permissions['protocol']) {
            return [];
        }
        if ($userId <= 0) {
            $userId = $GLOBALS['USER']->GetID();
        }

        $arFilterPermission = [];

        if (!empty($this->permissions['ispolnitel_ids'])) {
            $arIspolnitelIds = $this->permissions['ispolnitel_ids'];
            $arFindIspolnitels = [];
            sort($arIspolnitelIds);
            $arSubFilter = [
                'LOGIC' => 'OR',
            ];

            /*
             * Я - исполнитель или соисполнитель
             */
            $arFilterPermission[999] = [
                'LOGIC' => 'OR',
            ];
            foreach ($arIspolnitelIds as $ispId) {
                $arExecutor = $this->ispolnitels[ $ispId ];
                $arCurPermisions = array_merge(
                    [$arExecutor['PROPERTY_RUKOVODITEL_VALUE']],
                    $arExecutor['PROPERTY_ZAMESTITELI_VALUE'],
                    $arExecutor['PROPERTY_IMPLEMENTATION_VALUE']
                );
                if (in_array($userId, $arCurPermisions)) {
                    $arSubFilter[] = [
                        'PROPERTY_SUBEXECUTOR' => $ispId,
                    ];
                    $arSubFilter[] = [
                        'PROPERTY_SUBEXECUTOR' => $ispId . ':%',
                    ];
                    $arFindIspolnitels[] = $ispId;
                } else {
                    $arSubFilter[] = [
                        'PROPERTY_SUBEXECUTOR' => $ispId . ':' . $userId,
                    ];
                    $arSubFilter[] = [
                        'PROPERTY_ACCOMPLICES' => $userId,
                    ];
                }
                if (count($arIspolnitelIds) > 1 && in_array($userId, $arExecutor['PROPERTY_ISPOLNITELI_VALUE'])) {
                    $arFilterPermission[999][] = [
                        'PROPERTY_ISPOLNITEL'   => $ispId,
                        'PROPERTY_ACTION'       => [1135, 1136],
                        [
                            'LOGIC'                     => 'OR',
                            'PROPERTY_DELEGATE_USER'    => $userId,
                            'PROPERTY_ACCOMPLICES'      => $userId,
                        ]
                    ];
                } else {
                    $arFindIspolnitels[] = $ispId;
                }
            }
            $arFindIspolnitels = array_unique($arFindIspolnitels);
            if (!empty($arFindIspolnitels)) {
                $arFilterPermission[-1] = [
                    'LOGIC'                 => 'OR',
                    'PROPERTY_ISPOLNITEL'   => $arFindIspolnitels,
                    'PROPERTY_DELEGATION'   => $arFindIspolnitels,
                    $arSubFilter,
                ];
            } else {
                $arFilterPermission[-1] = $arSubFilter;
            }

            if (count($arFilterPermission[999]) <= 1) {
                unset($arFilterPermission[999]);
            } elseif (count($arFilterPermission[999]) == 2) {
                $arFilterPermission[999] = $arFilterPermission[999][0];
            }
        }

        /*
         * Я - контролер
         */
        if ($this->permissions['controler']) {
            $arFilterPermission['PROPERTY_CONTROLER'][] = $userId;
            if (!$this->permissions['main_controler']) {
                $arFilterPermission['PROPERTY_CONTROLER'][] = $this->permissions['controler_head'];
            } elseif ($userId != 1151) {
                $arFilterPermission['PROPERTY_CONTROLER'][] = 1151;
            }
        }

        if ($this->permissions['kurator']) {
            $arFilterPermission['PROPERTY_POST'] = [1112, $userId];
        }

        /*
         * У меня виза
         */
        $arFilterPermission[0] = [
            'ID' => CIBlockElement::SubQuery(
                'PROPERTY_PORUCH',
                [
                    'ACTIVE'            => 'Y',
                    'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
                    [
                        'LOGIC' => 'OR',
                        ['=PROPERTY_TYPE' => 1131],
                        ['=PROPERTY_TYPE' => $this->arParams['COMMENT_ENUM']['TYPE']['accomplience']['ID']],
                    ],
                    'PROPERTY_VISA'     => $userId . ':E%',
                ]
            ),
        ];
        foreach ($this->permissions['ispolnitel_ids'] as $ispId) {
            $arExecutor = $this->ispolnitels[ $ispId ];
            $arCurPermisions = array_merge(
                [$arExecutor['PROPERTY_RUKOVODITEL_VALUE']],
                $arExecutor['PROPERTY_ZAMESTITELI_VALUE']
            );
            if (in_array($userId, $arCurPermisions)) {
                $arFilterPermission[] = [
                    'ID' => CIBlockElement::SubQuery(
                        'PROPERTY_PORUCH',
                        [
                            'ACTIVE'                    => 'Y',
                            'IBLOCK_ID'                 => Settings::$iblockId['ORDERS_COMMENT'],
                            'PROPERTY_TYPE'             => 1131,
                            'PROPERTY_CURRENT_USER'     => $arExecutor['PROPERTY_RUKOVODITEL_VALUE']
                        ]
                    ),
                ];
            } else {
                $arFilterPermission[] = [
                    'ID' => CIBlockElement::SubQuery(
                        'PROPERTY_PORUCH',
                        [
                            'ACTIVE'                    => 'Y',
                            'IBLOCK_ID'                 => Settings::$iblockId['ORDERS_COMMENT'],
                            'PROPERTY_TYPE'             => 1131,
                            'PROPERTY_CURRENT_USER'     => $userId,
                        ]
                    ),
                ];
            }
        }

        if (!empty($arFilterPermission)) {
            $arFilterPermission['LOGIC'] = 'OR';
        }

        return $arFilterPermission;
    }

    /**
     * Запуск компонента
     *
     * @return void
     */
    public function executeComponent()
    {
        try {
            $this->initPermissions();
            $this->initFields();

            if ($_REQUEST['edit'] != '') {
                $this->getEditData();
            } elseif ($_REQUEST['detail'] != '') {
                $this->getDetailData();
            }

            $this->arResult = $this->getResult();

            $this->includeComponentTemplate();
        } catch (Exception $e) {
            ShowError($e->getMessage());
        }
    }

    /**
     * Таблица визирования.
     *
     * @param array   $arVisa       Массив виз.
     * @param integer $id           ID комментария.
     * @param integer $orderId      ID поручения.
     * @param string  $visaTypeCode Тип визирования.
     * @param boolean $bEdit        Включено ли редактирование.
     *
     * @return string
     */
    public function showVisaTableEdit(
        $arVisa = [],
        $id = 0,
        $orderId = 0,
        $visaTypeCode = '',
        $bEdit = false,
        $type = 'ISPOLNITEL',
        $salt = ''
    ) {
        $arDefaultLang = [
            'ISPOLNITEL' => [
                'NO_VISA'           => 'Нет визы',
                'REQUIRED_VISA'     => 'Установлено обязательное визирование',
                'BUTTON_R'          => 'Обязательное визирование',
                'BUTTON_Y'          => 'Согласен',
                'BUTTON_Y_TEXT'     => 'Согласен',
                'BUTTON_N'          => 'Не согласен',
                'BUTTON_N_TEXT'     => 'Не согласен',
                'BUTTON_A'          => 'Отсутствует',
                'BUTTON_A_REMOVE'   => 'Убрать отсутствие',
                'BUTTON_S'          => 'Визирование недоступно',
                'BUTTON_S_TITLE'    => 'Отсутствует предыдущее согласование',
                'COMMENT'           => 'Комментарий',
                'ADD_USER'          => 'Добавить визирующего',
                'REMOVE_USER'       => 'Вы уверены, что хотите удалить визирующего из списка?',
            ],
            'ACCOMPLIENCE' => [
                'NO_VISA'           => 'Нет согласования',
                'REQUIRED_VISA'     => 'Установлено обязательное согласование',
                'BUTTON_R'          => 'Обязательное согласование',
                'BUTTON_Y'          => 'Согласовать',
                'BUTTON_Y_TEXT'     => 'Согласовано',
                'BUTTON_N'          => 'Отклонить',
                'BUTTON_N_TEXT'     => 'Отклонено',
                'BUTTON_A'          => 'Отсутствует',
                'BUTTON_A_REMOVE'   => 'Убрать отсутствие',
                'BUTTON_S'          => 'Согласование недоступно',
                'BUTTON_S_TITLE'    => 'Отсутствует предыдущее согласование',
                'COMMENT'           => 'Комментарий',
                'ADD_USER'          => 'Добавить согласующего',
                'REMOVE_USER'       => 'Вы уверены, что хотите удалить согласующего из списка?',
            ],
        ];
        $arLang = $arDefaultLang[ $type ];

        if (is_null($arVisa)) {
            $arVisa = [];
        }
        $curUserId = $GLOBALS['USER']->GetID();
        $arExecutors = Executors::getList();
        $arImplement = [];
        /*
         * Массив обязательных пользователей.
         */
        $arRequiredUsers = [];
        $arRequiredVisa = [];
        $arWorkInterStatus = [];
        if ($orderId > 0) {
            $arRequiredVisa = (new Orders())->getProperty($orderId, 'REQUIRED_VISA', true);
            $arWorkInterStatus = (new Orders())->getProperty($orderId, 'WORK_INTER_STATUS');
        }
        $arRequiredVisa = array_unique(array_filter($arRequiredVisa));
        foreach ($arRequiredVisa as $exId) {
            if (0 === mb_strpos($exId, 'I')) {
                $arRequiredUsers[] = (int)$arExecutors[ mb_substr($exId, 1) ]['PROPERTY_RUKOVODITEL_VALUE'];
            } else {
                $arRequiredUsers[] = (int)$exId;
            }
        }
        $arRequiredUsers = array_unique(array_filter($arRequiredUsers));

        if ($bEdit) {
            /*
             * В поручении есть обязательные визы.
             */
            if ($type == 'ISPOLNITEL' && !empty($arRequiredVisa) && in_array($salt, ['', '2'])) {
                $arNeedHeadTmp = $arRequiredUsers;
                foreach ($arVisa as $visaRow) {
                    [$userId, $status, $comment, $date] = explode(':', $visaRow, 4);
                    foreach ($arNeedHeadTmp as $k => $uId) {
                        if ($uId == $userId) {
                            unset($arNeedHeadTmp[ $k ]);
                        }
                    }
                }
                /*
                 * Собрать массив для обязательных виз, если их еще нет в визирующих.
                 */
                $arNewVisa = [];
                foreach ($arNeedHeadTmp as $uId) {
                    $arNewVisa[] = implode(':', [$uId, 'E', '', '']);
                }
                if (!empty($arNewVisa)) {
                    $arVisa = array_merge(
                        $arVisa,
                        $arNewVisa
                    );
                }
            }
        } else {
            foreach ($arExecutors as $executor) {
                $rukl = $executor['PROPERTY_RUKOVODITEL_VALUE'];
                if (!isset($arImplement[ $rukl ])) {
                    $arImplement[ $rukl ] = [];
                }
                $arImplement[ $rukl ] = array_merge(
                    $arImplement[ $rukl ],
                    $executor['PROPERTY_IMPLEMENTATION_VALUE']
                );
            }
        }
        ob_start();
        ?>
        <script>if (typeof selectedVisa == 'undefined'){var selectedVisa = [];}</script>
        <table class="<?=$bEdit?'visa-container':''?> VISA-<?=$type.$salt?>" border="0" cellpadding="5">
            <tbody>
            <?if ($bEdit) : ?>
                <tr class="d-none visa-row visa-fake VISA_FAKE_<?=$type.$salt?> my-2">
                    <td class="first-visa-col">
                        <a class="label label-danger js-delete-visa" href="javascript:void(0);" data-text="<?=$arLang['REMOVE_USER']?>">&times;</a>
                    </td>
                    <td class="sorter">
                        <input type="hidden" name="VISA<?=$salt?>[]" disabled />
                    </td>
                    <td width="5%">&nbsp;</td>
                    <td>
                        <span class="label label-info"><?=$arLang['NO_VISA']?></span>
                    </td>
                </tr>
            <?endif;?>
            <?
            foreach ($arVisa as $vKey => $visaRow) {
                [$userId, $status, $comment, $date] = explode(':', $visaRow, 4);

                $bSetVisa = true;
                if ($visaTypeCode == 'after') {
                    if ($vKey == 0) {
                        $bSetVisa = true;
                    } else {
                        $bSetVisa = false;
                        [$prevuserId, $prevstatus, $prevcomment, $prevdate] = explode(':', $arVisa[ $vKey-1 ], 4);
                        if (in_array($prevstatus, ['Y', 'N', 'A'])) {
                            $bSetVisa = true;
                        }
                    }
                }
                /*
                 * Обязательных визирующих нельзя удалить.
                 */
                $bAllowEdit = (!in_array($userId, $arRequiredUsers));
                $arClasses = [
                    'my-2',
                ];
                if ($bEdit) {
                    $arClasses[] = 'visa-row';
                }
                if (!$bAllowEdit) {
                    $arClasses[] = 'js-end-table';
                }

                $visaFlag = '&nbsp;';
                $visaFlagText = '';
                if ($visaTypeCode == 'after' && $bSetVisa && $status == 'E') {
                    $visaFlag = '&#9873;';
                    $visaFlagText = 'Текущий визирующий';
                } elseif ($bSetVisa && $status == 'E') {
                    $visaFlag = '&#9873;';
                    $visaFlagText = 'Визирование не установлено';
                }
                ?>
                <tr class="<?=implode(' ', $arClasses)?>" data-user="<?=$userId?>">
                    <?if ($bEdit) : ?>
                        <script>selectedVisa.push('U<?=$userId?>');</script>
                        <td class="first-visa-col">
                            <?if ($bAllowEdit) : ?>
                                <a class="label label-danger js-delete-visa" href="javascript:void(0);" data-user="U<?=$userId?>" data-text="<?=$arLang['REMOVE_USER']?>">&times;</a>
                            <?else : ?>
                                <a class="label label-disabled" href="javascript:void(0);" title="<?=$arLang['REQUIRED_VISA']?>">&times;</a>
                            <?endif;?>
                        </td>
                    <?endif;?>
                    <td <?=($bEdit)?'class="sorter"':''?> bx-tooltip-user-id="<?=$userId?>">
                        <? if (!$bEdit) : ?>
                            <span class="current-visa text-red" title="<?=$visaFlagText?>">
                                <?=$visaFlag?>
                            </span>
                        <? endif; ?>
                        <?=str_replace(' ', '&nbsp;', $this->getUserFullName($userId))?>
                        <input type="hidden" name="VISA<?=$salt?>[]" value="<?=$visaRow ?>" />
                    </td>
                    <td width="5%">&nbsp;</td>
                    <td class="d-flex align-items-center">
                        <?
                        if (in_array($status, ['Y', 'N'])) {
                            if ($status == 'Y') {
                                echo '<span class="label label-success" title="' . (!is_null($date)?$date: '') . '">' . $arLang['BUTTON_Y_TEXT'] . '</span>';
                            } else {
                                echo '<span class="label label-danger" title="' . (!is_null($date)?$date: '') . '">' . $arLang['BUTTON_N_TEXT'] . '</span>';
                            }

                            echo '&nbsp;' . $comment ?? '';
                        } elseif ($status == 'A') {
                            echo '<span class="label label-warning" title="' . (!is_null($date)?$date: '') . '">' . $arLang['BUTTON_A'] . '</span>';
                        } elseif ($userId == $curUserId) {
                            if ($bSetVisa) {
                                if ($status == 'S') {
                                    echo '<span class="label label-info">' . $arLang['BUTTON_S'] . '</span>';
                                } else {
                                    ?>
                                    <textarea name="VISA_COMMENT" placeholder="<?=$arLang['COMMENT']?>" cols="30" rows="2"><?=$comment?></textarea><br/>
                                    <input
                                            class="ui-btn ui-btn-xs ui-btn-success js-set-visa"
                                            type="button"
                                            data-user="<?=$curUserId?>"
                                            data-comment="<?=$id?>"
                                            data-value="Y"
                                            value="<?=$arLang['BUTTON_Y']?>" />
                                    <input
                                            class="ui-btn ui-btn-xs ui-btn-danger js-set-visa"
                                            type="button"
                                            data-user="<?=$curUserId?>"
                                            data-comment="<?=$id?>"
                                            data-value="N"
                                            value="<?=$arLang['BUTTON_N']?>" />
                                    <?
                                }
                            } else {
                                echo '<span class="label label-info" title="' . $arLang['BUTTON_S_TITLE'] . '">' . $arLang['BUTTON_S'] . '</span>';
                            }
                        } else {
                            echo '<span class="label label-info">' . $arLang['NO_VISA'] . '</span>';
                            if (in_array($userId, $arRequiredUsers)) {
                                ?>
                                <span class="label label-warning"><?=$arLang['BUTTON_R']?></span>
                                <?
                            }
                        }

                        if (
                            !in_array($status, ['Y', 'N']) &&
                            in_array($curUserId, $arImplement[ $userId ])
                        ) {
                            ?>
                            <select
                                    class="form-control form-control-sm ml-3 js-set-visa-away"
                                    data-user="<?=$userId?>"
                                    data-comment="<?=$id?>"
                            >
                                <option value="E">Не установлено</option>
                                <option value="A" <?=$status=='A'?'selected':''?>><?=$arLang['BUTTON_A']?></option>
                            </select>
                            <?
                        }

                        ?>
                    </td>
                </tr>
                <?
            }
            ?>
            </tbody>
        </table>
        <?
        if ($bEdit) {
            $GLOBALS['APPLICATION']->IncludeComponent(
                'bitrix:main.user.selector',
                '',
                [
                    'ID' => 'VISA_FAKE_' . $type.$salt,
                    'API_VERSION' => 3,
                    'INPUT_NAME' => 'VISA_FAKE_' . $type.$salt,
                    'USE_SYMBOLIC_ID' => true,
                    'BUTTON_SELECT_CAPTION' => $arLang['ADD_USER'],
                    'SELECTOR_OPTIONS' => [
                        'departmentSelectDisable' => 'Y',
                        'context' => 'VISA'.$salt,
                        'contextCode' => 'U',
                        'enableAll' => 'N',
                        'userSearchArea' => 'I'
                    ]
                ]
            );
        }
        $return = ob_get_contents();
        ob_end_clean();
        return $return;
    }

    /**
     * Зафиксировать нарушение срока.
     *
     * @param integer $id ID поручения.
     *
     * @return void
     *
     * @throws \Bitrix\Main\LoaderException
     */
    public static function fixSrokNarush(int $id = 0)
    {
        $arTypes = [];
        $arSelect = [
            'ID',
            'NAME',
            'PROPERTY_TYPE',
            'PROPERTY_DATE_ISPOLN',
            'PROPERTY_DATE_FACT_ISPOLN',
            'PROPERTY_DATE_REAL_ISPOLN',
        ];
        $arFilter = [
            'IBLOCK_ID'     => Settings::$iblockId['ORDERS'],
            'ACTIVE_DATE'   => 'Y',
            'ACTIVE'        => 'Y',
            'ID'            => $id,
        ];
        $bUpdate = false;
        $res = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
        while ($arFields = $res->GetNext()) {
            $arTypes = $arFields['PROPERTY_TYPE_VALUE'];
            if (!empty($arFields['PROPERTY_DATE_REAL_ISPOLN_VALUE'])) {
                if (strtotime($arFields['PROPERTY_DATE_ISPOLN_VALUE'] . ' 23:59:59') < strtotime($arFields['PROPERTY_DATE_REAL_ISPOLN_VALUE'])) {
                    $bUpdate = true;
                }
            } elseif (!empty($arFields['PROPERTY_DATE_FACT_ISPOLN_VALUE'])) {
                if (strtotime($arFields['PROPERTY_DATE_ISPOLN_VALUE'] . ' 23:59:59') < strtotime($arFields['PROPERTY_DATE_FACT_ISPOLN_VALUE'])) {
                    $bUpdate = true;
                }
            }
        }

        if ($bUpdate) {
            $arTypes[] = 'srok_narush';
            $arTypes = array_unique($arTypes);
            self::log(
                $id,
                'Срок нарушен',
                [
                    'METHOD'    => __METHOD__,
                    'REQUEST'   => $_REQUEST,
                ]
            );
            CIBlockElement::SetPropertyValuesEx(
                $id,
                false,
                [
                    'TYPE' => $arTypes,
                ]
            );
        }
    }

    /**
     * Создать новое поручение для дальнейшей работы с ним.
     *
     * @param integer $id ID существующего поручения.
     *
     * @return integer
     */
    public function addItemFromExist($id = 0)
    {
        $res = CIBlockElement::GetByID($id);
        if ($ob = $res->GetNextElement()) {
            $arFields               = $ob->GetFields();
            $arFields['PROPERTIES'] = $ob->GetProperties();

            $arFieldsCopy = [
                'MODIFIED_BY'       => $GLOBALS['USER']->GetID(),
                'CREATED_BY'        => $GLOBALS['USER']->GetID(),
                'IBLOCK_SECTION_ID' => false,
                'IBLOCK_ID'         => Settings::$iblockId['ORDERS'],
                'NAME'              => $arFields['NAME'],
                'ACTIVE'            => 'Y',
                'DETAIL_TEXT'       => $arFields['~DETAIL_TEXT'],
            ];

            foreach ($arFields['PROPERTIES'] as $property) {
                $arFieldsCopy['PROPERTY_VALUES'][ $property['CODE'] ] = $property['~VALUE'];
                if ($property['PROPERTY_TYPE'] == 'L') {
                    if ($property['MULTIPLE'] == 'Y') {
                        $arFieldsCopy['PROPERTY_VALUES'][ $property['CODE'] ] = [];
                        foreach ($property['VALUE_ENUM_ID'] as $enumID) {
                            $arFieldsCopy['PROPERTY_VALUES'][ $property['CODE'] ][] = [
                                'VALUE' => $enumID,
                            ];
                        }
                    } else {
                        $arFieldsCopy['PROPERTY_VALUES'][ $property['CODE'] ] = $property['VALUE_ENUM_ID'];
                    }
                }
                if ($property['PROPERTY_TYPE'] == 'F') {
                    if ($property['MULTIPLE'] == 'Y') {
                        if (is_array($property['VALUE'])) {
                            foreach ($property['VALUE'] as $key => $arElEnum) {
                                $arFieldsCopy['PROPERTY_VALUES'][ $property['CODE'] ][ $key ] = CFile::CopyFile($arElEnum);
                            }
                        }
                    } else {
                        $arFieldsCopy['PROPERTY_VALUES'][ $property['CODE'] ] = CFile::CopyFile($property['VALUE']);
                    }
                }
            }

            $el = new CIBlockElement();
            $newId = $el->Add($arFieldsCopy);

            if (!$newId) {
                return 0;
            }
            $this->log(
                $newId,
                'Поручение создано',
                [
                    'METHOD'    => __METHOD__,
                    'REQUEST'   => $_REQUEST,
                ]
            );
            CIBlockElement::SetPropertyValuesEx(
                $newId,
                false,
                [
                    'STATUS'                => 1141,
                    'ACTION'                => Settings::$arActions['NEW'],
                    'TYPE'                  => false,
                    'VIEWS'                 => false,
                    'POST_RESH'             => false,
                    'DOPSTATUS'             => false,
                    'POSITION_FROM'         => false,
                    'NEWISPOLNITEL'         => false,
                    'CONTROLER_STATUS'      => false,
                    'DATE_FACT_ISPOLN'      => false,
                    'DELEGATE_HISTORY'      => false,
                    'WORK_INTER_STATUS'     => false,
                    'HISTORY_SROK'          => false,
                    'CONTROLER_RESH'        => false,
                    'DATE_FACT_SNYAT'       => false,
                    'OLD_PORUCH'            => false,
                    'NEW_PORUCH'            => false,
                    'DATE_ISPOLN_HIST'      => false,
                    'NEW_DATE_ISPOLN'       => false,
                    'DELEGATE_USER'         => false,
                    'CONTROL_REJECT'        => false,
                    'ACCOMPLICES'           => false,
                    'SUBEXECUTOR'           => false,
                    'SUBEXECUTOR_USER'      => false,
                    'SUBEXECUTOR_DATE'      => false,
                    'DELEGATION'            => false,
                    'NEW_SUBEXECUTOR_DATE'  => false,
                    'POSITION_ISPOLN'       => false,
                ]
            );

            return $newId;
        }
    }

    /**
     * Создать позицию из поручения
     *
     * @param integer $id       ID поручения.
     * @param integer $executor ID исполнителя.
     *
     * @return void
     */
    public function addPositionFromExist($id = 0, $executor = 0)
    {
        $obOrders = new Orders();
        $arPosition = $obOrders->getProperty($id, 'POSITION_FROM', true);
        $arPosition = array_filter($arPosition);
        $bCreate = true;
        foreach ($arPosition as $posId) {
            $arOrder = $obOrders->getById((int)$posId);

            if (
                $arOrder['PROPERTY_ISPOLNITEL_VALUE'] == $executor &&
                $arOrder['PROPERTY_ACTION_ENUM_ID'] != Settings::$arActions['ARCHIVE']
            ) {
                $bCreate = false;
            }
        }

        if ($bCreate) {
            $newId = $this->addItemFromExist($id);
            if ($newId > 0) {
                $arReqs = (new Orders())->getProperty($id, 'POSITION_ISPOLN_REQS');
                if (!empty($arReqs['~VALUE'])) {
                    (new CIBlockElement())->Update(
                        $newId,
                        [
                            'DETAIL_TEXT' => $arReqs['~VALUE']['TEXT'],
                        ]
                    );
                }
                CIBlockElement::SetPropertyValuesEx(
                    $newId,
                    false,
                    [
                        'POSITION_TO'   => $id,
                        'ISPOLNITEL'    => $executor,
                    ]
                );

                $arPosition[] = $newId;

                $this->log(
                    $id,
                    'Запрос на позицию',
                    [
                        'METHOD'    => __METHOD__,
                        'REQUEST'   => $_REQUEST,
                    ]
                );
                $this->addToLog('Запрос на позицию', strip_tags($_REQUEST['DETAIL_TEXT'] ?? ''), $id);
            }
        }

        $arEnums = $this->getEnums(Settings::$iblockId['ORDERS']);
        CIBlockElement::SetPropertyValuesEx(
            $id,
            false,
            [
                'POST_RESH'         => 1203,
                'POSITION_FROM'     => $arPosition,
                'ACTION'            => Settings::$arActions['CONTROL'],
                'DOPSTATUS'         => $arEnums['DOPSTATUS']['to_position']['ID'],
                'CONTROLER_STATUS'  => $arEnums['CONTROLER_STATUS']['on_position']['ID'],
                'WORK_INTER_STATUS' => false,
                'POSITION_ISPOLN'   => false,
            ]
        );

    }

    /**
     * Сформировать историю поручения для текущего пользователя
     *
     * @param array   $arElement Массив с поручением.
     * @param integer $user      ID пользователя.
     *
     * @return array
     */
    public function getHistory(array $arElement = [], int $user = 0): array
    {
        if ($user <= 0) {
            $user = $GLOBALS['USER']->GetID();
        }
        if (empty($arElement) || $user <= 0) {
            return [];
        }
        $arShowMessages = [
            'Снято с контроля',
            'Изменен срок исполнения',
            'Отправлено на дополнительный контроль',
            'Принято контролером',
            'Отклонено контролером',
        ];
        $arHistory = [];
        foreach ($arElement['~PROPERTY_HISTORY_SROK_VALUE'] as $row) {
            $arHistory[] = json_decode($row, true);
        }

        $arHistory = array_reverse($arHistory);

        if (
            $GLOBALS['USER']->IsAdmin() ||
            $this->permissions['controler'] ||
            $this->permissions['kurator'] ||
            $this->permissions['full_access']
        ) {
            return $arHistory;
        }

        $arResult = [];
        $arPermisions = [];
        foreach ($this->permissions['ispolnitel_ids'] as $executorId) {
            $arExecutor = $this->ispolnitels[ $executorId ];
            $arPermisions = array_merge(
                $arPermisions,
                [$arExecutor['PROPERTY_RUKOVODITEL_VALUE']],
                $arExecutor['PROPERTY_ZAMESTITELI_VALUE'],
                $arExecutor['PROPERTY_ISPOLNITELI_VALUE'],
                $arExecutor['PROPERTY_IMPLEMENTATION_VALUE']
            );
        }
        $arPermisions = array_unique(array_filter($arPermisions));

        foreach ($arHistory as $row) {
            /*
             * Для новой истории внедрено хранение ID исполнителя
             */
            if (
                isset($row['EXECUTOR']) &&
                in_array($row['EXECUTOR'], $this->permissions['ispolnitel_ids'])
            ) {
                $arResult[] = $row;
            } elseif (in_array($row['USER_ID'], $arPermisions)) {
                $arResult[] = $row;
            } else {
                foreach ($arShowMessages as $mess) {
                    if (false !== mb_strpos($row['TEXT'], $mess)) {
                        $arResult[] = $row;
                    }
                }
            }
        }

        return $arResult;
    }

    /**
     * Рендер блока с пользователем для отчетов.
     *
     * @param integer $id      ID пользователя.
     * @param string  $date    Дата отчета.
     * @param array   $arClass Добавить классы для описания.
     *
     * @return string
     */
    public function getUserBlock(
        int $id = 0,
        string $date = '',
        array $arClass = []
    ) {
        if ($id <= 0) {
            $id = $GLOBALS['USER']->GetID();
        }
        if ($date == '') {
            $date = 'Сейчас';
        }
        $html = '<div class="user-blocks">';
        $arUser = CUser::GetByID($id)->Fetch();
        $arUser['PERSONAL_PHOTO'] = CFile::ResizeImageGet(
            $arUser['PERSONAL_PHOTO'],
            [
                'width' => 40,
                'height' => 40,
            ]
        );
        $uName = $this->getUserFullName($arUser['ID'], true);
        if ($arUser['PERSONAL_PHOTO']['src'] != '') {
            $html .= '<img
                        class="img-circle img-bordered-sm"
                        src="' . $arUser['PERSONAL_PHOTO']['src'] . '"
                        alt="' . strip_tags($uName) . '"
                        title="' . strip_tags($uName) . '"
                        />';
        } else {
            $html .= '<div class="comments-userpic"></div>';
        }
        $html .= $uName;
        $html .= '<span class="description ' . ($arClass['description']??'') . '">' . $date . '</span>';
        $html .= '</div>';
        return $html;
    }

    /**
     * Вывод HTML кто подписывает поручение.
     * @param integer $id       ID поручения.
     * @param integer $reportId ID отчёта.
     * @param boolean $bEdit    Редактирование или просмотр.
     *
     * @return string
     */
    public function showSigner(
        int $id = 0,
        int $reportId = 0,
        bool $bEdit = true
    ) {
        $iSignUser = 0;
        $signName = '';
        $arSigners = [
            0 => 'Главные исполнители'
        ];
        $iCurrentUser = 0;
        if ($reportId > 0) {
            $resReport = CIBlockElement::GetList(
                [
                    'DATE_CREATE' => 'DESC'
                ],
                [
                    'ID'            => $reportId,
                    'ACTIVE'        => 'Y',
                    'IBLOCK_ID'     => Settings::$iblockId['ORDERS_COMMENT'],
                    'PROPERTY_TYPE' => 1131
                ],
                false,
                [
                    'nTopCount' => 1
                ],
                $this->arReportFields
            );
            while ($arReport = $resReport->GetNext()) {
                $iSignUser = (int)$arReport['PROPERTY_SIGNER_VALUE'];
                $iCurrentUser = (int)$arReport['PROPERTY_CURRENT_USER_VALUE'];
            }
        }

        $bIsDelegation = false;
        $text = 'Подписывает';
        $subText = '';
        $subSignUser = 0;

        if ($id > 0) {
            $arOrder = (new Orders())->getById($id);
            $arExecutors = Executors::getList();
            $arSigners[0] = $this->getUserFullName($arExecutors[ $arOrder['PROPERTY_ISPOLNITEL_VALUE'] ]['PROPERTY_RUKOVODITEL_VALUE']);

            foreach ($arExecutors[ $arOrder['PROPERTY_ISPOLNITEL_VALUE'] ]['PROPERTY_ZAMESTITELI_VALUE'] as $uId) {
                $arSigners[ $uId ] = $this->getUserFullName($uId);
            }
            $arParams['ENUM'] = $this->getEnums(Settings::$iblockId['ORDERS']);
            if ($arOrder['PROPERTY_WORK_INTER_STATUS_ENUM_ID'] == $arParams['ENUM']['WORK_INTER_STATUS']['TOSIGN']['ID']) {
                $text = 'На подписи';
            }

            if (!empty($arOrder['PROPERTY_DELEGATION_VALUE']) && (int)$arOrder['PROPERTY_DELEGATION_VALUE'][0] > 0) {
                $arDelegator = $arExecutors[ $arOrder['PROPERTY_DELEGATION_VALUE'][0] ];

                if (
                    $arDelegator['PROPERTY_TYPE_CODE'] == 'gubernator' &&
                    $arOrder['PROPERTY_WORK_INTER_STATUS_ENUM_ID'] == $arParams['ENUM']['WORK_INTER_STATUS']['TOVISA']['ID'] &&
                    $iCurrentUser == $arDelegator['PROPERTY_RUKOVODITEL_VALUE']
                ) {
                    $text = 'На визировании';
                    $bIsDelegation = true;
                    $iSignUser = $iCurrentUser;
                    $signName = $this->getUserFullName($iSignUser);
                }

                if (
                    $arDelegator['PROPERTY_TYPE_CODE'] == 'zampred' &&
                    $arOrder['PROPERTY_WORK_INTER_STATUS_ENUM_ID'] == $arParams['ENUM']['WORK_INTER_STATUS']['TOSIGN']['ID'] &&
                    $iCurrentUser == $arDelegator['PROPERTY_RUKOVODITEL_VALUE']
                ) {
                    $text = 'На подписи';
                    $bIsDelegation = true;
                    $iSignUser = $iCurrentUser;
                    $signName = $this->getUserFullName($iSignUser);
                }

                if (
                    $iCurrentUser != $arDelegator['PROPERTY_RUKOVODITEL_VALUE'] &&
                    in_array($arDelegator['PROPERTY_TYPE_CODE'], ['gubernator', 'zampred'])
                ) {
                    if ($arDelegator['PROPERTY_TYPE_CODE'] == 'gubernator') {
                        $subText = 'затем визирует';
                    } else {
                        $subText = 'затем подписывает';
                    }
                    $subSignUser = $arDelegator['PROPERTY_RUKOVODITEL_VALUE'];
                }

                if (
                    $arDelegator['ID'] == 250892 && // Федорищев
                    $GLOBALS['USER']->GetID() == 398 && // Шерин
                    $iCurrentUser == $GLOBALS['USER']->GetID() &&
                    !empty($arOrder['PROPERTY_WORK_INTER_STATUS_ENUM_ID'])
                ) {
                    $subText = '';
                    $subSignUser = '';
                }
            }
        }

        if (!$bIsDelegation) {
            $signName = $arSigners[0];
            if ($iSignUser > 0) {
                $signName = $this->getUserFullName($iSignUser);
            }
        }

        ob_start();
        ?>
        <div class="row">
            <div class="col-12">
                <b><?=$text?>:</b>
                <span class="label label-info js-signer-name" bx-tooltip-user-id="<?=$iSignUser?>"><?=$signName?></span>
                <input type="hidden" name="SIGNER" value="<?=$iSignUser?>" class="js-signer-id" />

                <? if ($bEdit && count($arSigners) > 1) : ?>
                    <a
                            href="javascript:void(0);"
                            class="ui-btn ui-btn-label ui-btn-success ui-btn-icon-business js-set-signer"
                            data-user="<?=$iSignUser?>"
                            title="Изменить"></a>

                    <div class="d-none" id="SELECT_SIGNER">
                        <select class="form-control" name="SELECT_SIGNER">
                            <?
                            foreach ($arSigners as $key => $value) {
                                ?>
                                <option value="<?=$key?>"><?=$value?></option>
                                <?
                            }
                            ?>
                        </select>
                    </div>
                <? endif; ?>

                <? if (!empty($subText) && $subSignUser > 0) : ?>
                    <?=$subText?>
                    <span class="label label-info js-signer-name" bx-tooltip-user-id="<?=$subSignUser?>">
                    <?=$this->getUserFullName($subSignUser)?>
                </span>
                <? endif; ?>
            </div>
        </div>
        <br/>
        <?
        $return = ob_get_contents();
        ob_end_clean();
        return $return;
    }

    /**
     * Новая форма выбора визирующих и подписантов.
     * @param integer $reportId ID отчёта.
     * @param integer $orderId  ID поручения.
     * @param boolean $bEdit    Редактирование или просмотр.
     *
     * @return string
     */
    public function showVisaAndSignTable(
        int $reportId = 0,
        int $orderId = 0,
        bool $bEdit = false
    ) {
        if ($orderId <= 0) {
            return '';
        }
        $arOrder = (new Orders())->getById($orderId);
        $arReport = [];
        if ($reportId > 0) {
            $res = CIBlockElement::GetList(
                [
                    'DATE_CREATE' => 'DESC'
                ],
                [
                    'ID'            => $reportId,
                    'ACTIVE'        => 'Y',
                    'IBLOCK_ID'     => Settings::$iblockId['ORDERS_COMMENT'],
                ],
                false,
                [
                    'nTopCount' => 1
                ],
                $this->arReportFields
            );
            while ($row = $res->GetNext()) {
                $arReport = $row;
            }
        }

        $visaTypeId = $arReport['PROPERTY_VISA_TYPE_ENUM_ID'];
        $visaTypeCode = $this->arParams['COMMENT_ENUM']['VISA_TYPE'][ $visaTypeId ]['EXTERNAL_ID'] ?? '';
        $visaTypeStr = $arReport['PROPERTY_VISA_TYPE_VALUE'];
        foreach ($this->arParams['COMMENT_ENUM']['VISA_TYPE'] as $visaTypeRow) {
            unset($this->arParams['COMMENT_ENUM']['VISA_TYPE'][ $visaTypeRow['ID'] ]);
        }

        $iSignUser = (int)$arReport['PROPERTY_SIGNER_VALUE'];
        $iCurrentUser = (int)$arReport['PROPERTY_CURRENT_USER_VALUE'];
        $iFinalSignUser = 0;

        $arExecutors = Executors::getList();
        $rukl = $arExecutors[ $arOrder['PROPERTY_ISPOLNITEL_VALUE'] ]['PROPERTY_RUKOVODITEL_VALUE'];

        if ($iSignUser <= 0 && $rukl > 0) {
            $iSignUser = $rukl;
        }
        $arSigners[0] = $this->getUserFullName($rukl);

        foreach ($arExecutors[ $arOrder['PROPERTY_ISPOLNITEL_VALUE'] ]['PROPERTY_ZAMESTITELI_VALUE'] as $uId) {
            $arSigners[ $uId ] = $this->getUserFullName($uId);
        }

        $arDelegator = [];
        if (!empty($arOrder['PROPERTY_DELEGATION_VALUE']) && (int)$arOrder['PROPERTY_DELEGATION_VALUE'][0] > 0) {
            $arDelegator = $arExecutors[ $arOrder['PROPERTY_DELEGATION_VALUE'][0] ];

            $iFinalSignUser = $arDelegator['PROPERTY_RUKOVODITEL_VALUE'];
        }

        $arVisas = [
            1 => [],
            2 => [],
        ];
        $currentKey = 1;
        foreach ($arReport['PROPERTY_VISA_VALUE'] as $key => $val) {
            if (0 === mb_strpos($val, 'SIGN')) {
                $currentKey = 2;
            } else {
                $arVisas[ $currentKey ][] = $val;
            }
        }

        $signFlag = '&nbsp;';
        $finalSignFlag = '&nbsp;';
        $signFlagTextT = '';
        $finalSignFlagText = '';

        // foreach ($arVisas[1] as $visaRow) {
        //     [$userId, $status, $comment, $date] = explode(':', $visaRow, 4);
        // }
        // &#9873;
        ob_start();
        ?>
        <div class="row mb-3">
            <?
            if ($bEdit || !empty($arVisas[1])) {
                ?>
                <div class="col-9">
                    <b>Визирующие:</b>
                    <?=$this->showVisaTableEdit(
                        $arVisas[1] ?? [],
                        (int)$arReport['ID'],
                        (int)$arOrder['ID'],
                        $visaTypeCode,
                        (bool)$bEdit,
                        'ISPOLNITEL',
                        '1'
                    );?>
                </div>
                <?
                if ($bEdit) {
                    ?>
                    <div class="col-3">
                        <b>Тип визирования:</b>
                        <br/>
                        <select class="form-control" name="VISA_TYPE">
                            <?
                            foreach ($this->arParams['COMMENT_ENUM']['VISA_TYPE'] as $visaTypeRow) {
                                ?>
                                <option
                                        value="<?=$visaTypeRow['ID'] ?>"
                                    <?=($visaTypeRow['ID']==$visaTypeId?'selected':'')?>
                                >
                                    <?=$visaTypeRow['VALUE'] ?>
                                </option>
                                <?
                            }
                            ?>
                        </select>
                    </div>
                    <?
                } else {
                    ?>
                    <input type="hidden" name="VISA_TYPE" value="<?=$visaTypeId?>" />
                    <?
                }
            }
            ?>
        </div>

        <?
        $bMultiSign = false;
        if ($iFinalSignUser > 0 && !$bEdit && empty($arVisas[2])) {
            $bMultiSign = true;
        }
        ?>

        <div class="row mb-3">
            <div class="col-9">
                <b>Подписант<?=$bMultiSign?'ы':''?>:</b>
                <br/>
                <div class="d-inline-block" style="padding:5px;">
                    <?
                    if (!$bEdit) {
                        ?>
                        <span class="current-visa text-red" title="<?=$signFlagText?>">
                            <?=$signFlag?>
                        </span>
                        <?
                    }
                    ?>
                    <span class="js-signer-name my-2" bx-tooltip-user-id="<?=$iSignUser?>"><?=$this->getUserFullName($iSignUser)?></span>
                </div>

                <?
                if ($bMultiSign) {
                    ?>
                    <br/>
                    <div class="d-inline-block" style="padding:5px;">
                        <?
                        if (!$bEdit) {
                            ?>
                            <span class="current-visa text-red" title="<?=$finalSignFlagText?>">
                                <?=$finalSignFlag?>
                            </span>
                            <?
                        }
                        ?>
                        <span bx-tooltip-user-id="<?=$iFinalSignUser?>"><?=$this->getUserFullName($iFinalSignUser)?></span>
                    </div>
                    <?
                }
                if ($bEdit) {
                    ?>
                    <input type="hidden" name="SIGNER" value="<?=$iSignUser?>" class="js-signer-id" />
                    <br/>

                    <span class="ui-tile-selector-selector-wrap">
                        <span data-role="tile-container" class="ui-tile-selector-selector">
                            <span class="ui-tile-selector-select-container js-set-signer" data-user="<?=$iSignUser?>">
                                <span data-role="tile-select" class="ui-tile-selector-select">
                                    Изменить подписанта
                                </span>
                            </span>
                        </span>
                    </span>
                    <div class="d-none" id="SELECT_SIGNER">
                        <select class="form-control" name="SELECT_SIGNER">
                            <?
                            foreach ($arSigners as $key => $value) {
                                ?>
                                <option value="<?=$key?>"><?=$value?></option>
                                <?
                            }
                            ?>
                        </select>
                    </div>
                    <?
                }
                ?>
            </div>
        </div>

        <?
        if (!$bMultiSign && $iFinalSignUser > 0) {
            if ($bEdit || !empty($arVisas[2])) {
                ?>
                <div class="js-double-visa <?=$visaTypeCode != 'after'?'d-none':''?> row mb-3">
                    <div class="col-9">
                        <b>Визирующие (2 этап):</b>
                        <?=$this->showVisaTableEdit(
                            $arVisas[2] ?? [],
                            (int)$arReport['ID'],
                            (int)$arOrder['ID'],
                            $visaTypeCode,
                            (bool)$bEdit,
                            'ISPOLNITEL',
                            '2'
                        );?>
                    </div>
                </div>
                <?
            }
            ?>

            <div class="row mb-3">
                <div class="col-9">
                    <b>Подписант (2 этап):</b>
                    <br/>
                    <div class="d-inline-block" style="padding:5px;">
                        <?
                        if (!$bEdit) {
                            ?>
                            <span class="current-visa text-red" title="<?=$finalSignFlagText?>">
                                <?=$finalSignFlag?>
                            </span>
                            <?
                        }
                        ?>
                        <span bx-tooltip-user-id="<?=$iFinalSignUser?>"><?=$this->getUserFullName($iFinalSignUser)?></span>
                    </div>
                </div>
            </div>

            <?
        }

        $return = ob_get_contents();
        ob_end_clean();
        return $return;
    }

    /**
     * Сортировка любого массива.
     *
     * @param string $by    Поле для сортировки.
     * @param string $order Направление сортировки.
     *
     * @return Closure
     */
    public function buildSorter(string $by, string $order = 'asc'): callable
    {
        return static function ($a, $b) use ($by, $order) {
            return $order == 'desc' ?
                strnatcmp($b[ $by ], $a[ $by ]) :
                strnatcmp($a[ $by ], $b[ $by ]);
        };
    }

    /**
     * Записать событие в статистику.
     *
     * @param string  $event2 Параметр статистики 2.
     * @param string  $event3 Параметр статистики 3.
     * @param boolean $unique Фиксировать уникальные или нет.
     *
     * @return void
     */
    public function fixEvent(
        string $event2 = '',
        string $event3 = '',
        bool $unique = true
    ) {
        if (Loader::includeModule('statistic')) {
            $event1 = 'controlorders';
            $bAdd = true;
            if ($unique) {
                $bAdd = false;
                $arFilter = [
                    'EVENT1'    => $event1,
                    'EVENT2'    => $event2,
                    'EVENT3'    => $event3,
                    'DATE1'     => date('d.m.Y 00:00:00'),
                    'DATE2'     => date('d.m.Y 23:59:59'),
                ];
                $res = CStatEvent::GetList(
                    ($by = "s_id"),
                    ($order = "desc"),
                    $arFilter,
                    $is_filtered
                );
                if ($res->SelectedRowsCount() <= 0) {
                    $bAdd = true;
                }
            }

            if ($bAdd) {
                CStatEvent::AddCurrent($event1, $event2, $event3);
            }
        }
    }

    /**
     * Убрать из списка отделов подведомственные учреджения
     *
     * @param array $arDeps Массив ID отделов
     *
     * @return array Массив ID отделов
     */
    private function unsetPodvedDeps(array $arDeps = [])
    {
        global $USER_FIELD_MANAGER;
        foreach ($arDeps as $key => $id) {
            $arUserField = $USER_FIELD_MANAGER->GetUserFields(
                'IBLOCK_5_SECTION',
                $id
            );
            if ($arUserField['UF_PODVED']['VALUE']) {
                unset($arDeps[ $key ]);
            }
        }
        return $arDeps;
    }

    /**
     * Получить поручения
     *
     * @return array
     */
    public function getOrders()
    {
        $arData = [];
        $obCache = Cache::createInstance();

        if ($obCache->initCache(86400, "data_orders")) {
            $arCache = $obCache->getVars();
            $arData = $arCache['data'];
        } elseif ($obCache->startDataCache()) {
            $arExecutors = Executors::getList();
            $arEnums = $this->getEnums(Settings::$iblockId['ORDERS']);

            /* Типы поручения */
            $arTypeData = [];
            $helper = new HlblockHelper();
            $hlblock = HLTable::getById($helper->getHlblockId('Tipporucheniya'))->fetch();
            $entity = HLTable::compileEntity($hlblock);
            $entity_data_class = $entity->getDataClass();
            $rsData = $entity_data_class::getList([
                'order' => ['UF_SORT' => 'ASC'],
            ]);
            while ($arRes = $rsData->fetch()) {
                $arTypeData[$arRes['UF_XML_ID']] = $arRes;
            }

            /* Темы поручения */
            $arThemes = [];
            $obThemes = CIBlockElement::GetList(['SORT' => 'asc', 'TIMESTAMP_X' => 'desc'], [
                'IBLOCK_ID' => Settings::$iblockId['ORDERS_THEME'],
                'ACTIVE'    => 'Y',
            ]);
            while ($arTheme = $obThemes->GetNext()) {
                $arThemes[$arTheme['ID']] = $arTheme['NAME'];
            }

            /* Объекты */
            $arObjects = [];
            $obObjects = CIBlockElement::GetList(['SORT' => 'asc', 'TIMESTAMP_X' => 'desc'], [
                'IBLOCK_ID' => Settings::$iblockId['ORDERS_OBJECT'],
                'ACTIVE'    => 'Y',
            ]);
            while ($arObject = $obObjects->GetNext()) {
                $arObjects[$arObject['ID']] = $arObject['NAME'];
            }

            /* Поручения */
            $arSelect = [
                "ID",                   // ID
                "NAME",                 // Наименование поручения
                "DETAIL_TEXT",          // Текст поручения
                "PROPERTY_TYPE",        // Тип поручения
                "PROPERTY_ACTION",      // Состояние
                "DATE_CREATE",          // Дата создания поручения
                "PROPERTY_DATE_CREATE", // Дата поручения
                "PROPERTY_DATE_ISPOLN", // Срок исполнения
                "PROPERTY_CATEGORY",    // Категория поручения
                "PROPERTY_THEME",       // Тема поручения
                "PROPERTY_ISPOLNITEL",  // Исполнитель
                "PROPERTY_OBJECT",      // Объект
                "TAGS",                 // Теги
                "PROPERTY_NUMBER",      // Номер поручения
                "PROPERTY_THESIS"       // Основная суть
            ];
            $arFilter = array("IBLOCK_ID" => Settings::$iblockId['ORDERS'], "ACTIVE_DATE" => "Y", "ACTIVE" => "Y");
            $obOrders = CIBlockElement::GetList(array(), $arFilter, false, [], $arSelect);
            while ($arItem = $obOrders->GetNext()) {
                $arData[] = [
                    'ID'                  => $arItem['ID'],
                    'NAME'                => $arItem['NAME'],
                    'DETAIL'              => $arItem['DETAIL_TEXT'],
                    'TYPE'                => (function () use ($arItem, $arTypeData) {
                        $arTypes = [];
                        foreach ($arItem['PROPERTY_TYPE_VALUE'] as $sProp) {
                            $arTypes[] = $arTypeData[$sProp]['UF_NAME'];
                        }
                        return $arTypes;
                    })(),
                    'ACTION'               => $arItem['PROPERTY_ACTION_VALUE'],
                    'DATE_CREATE'          => $arItem['DATE_CREATE'],
                    'DATE_CREATE_TS'       => strtotime($arItem['DATE_CREATE']),
                    'DATE_CREATE_ORDER'    => $arItem['PROPERTY_DATE_CREATE_VALUE'],
                    'DATE_CREATE_ORDER_TS' => strtotime($arItem['PROPERTY_DATE_CREATE_VALUE']),
                    'DATE_FINISH'          => $arItem['PROPERTY_DATE_ISPOLN_VALUE'],
                    'DATE_FINISH_TS'       => strtotime($arItem['PROPERTY_DATE_ISPOLN_VALUE']),
                    'CATEGORY'             => $arItem['PROPERTY_CATEGORY_VALUE'],
                    'THEME'                => $arThemes[$arItem['PROPERTY_THEME_VALUE']],
                    'EXECUTOR'             => $arExecutors[$arItem['PROPERTY_ISPOLNITEL_VALUE']]['NAME'],
                    'OBJECTS'              => (function () use ($arItem, $arObjects) {
                        $ar = [];
                        foreach ($arItem['PROPERTY_OBJECT_VALUE'] as $sProp) {
                            $ar[] = $arObjects[$sProp];
                        }
                        return $ar;
                    })(),
                    'TAGS'                => $arItem['TAGS'],
                    'NUMBER'              => $arItem['PROPERTY_NUMBER_VALUE'],
                    'THESIS'              => $arItem['PROPERTY_THESIS_VALUE'],
                    'DETAIL_WITHOUT_HTML' => HTMLToTxt($arItem['~DETAIL_TEXT']),
                ];
            }

            $obCache->endDataCache(array("data" => $arData));
        }

        return $arData;
    }
}
