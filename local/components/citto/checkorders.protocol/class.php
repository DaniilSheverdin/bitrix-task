<?php

namespace Citto\ControlOrders\Protocol;

use CFile;
use Closure;
use CJSCore;
use CDBResult;
use Exception;
use CIBlockElement;
use CBitrixComponent;
use RuntimeException;
use CIBlockPropertyEnum;
use Citto\Integration\Delo;
use Bitrix\DocumentGenerator;
use Bitrix\Main\Grid;
use Bitrix\Main\IO;
use Bitrix\Main\Loader;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI;
use Bitrix\Main\Web\Json;
use Bitrix\DocumentGenerator\DataProvider\ArrayDataProvider;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

require $_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php";

/**
 * Основной компонент работы с протоколами
 */
class Component extends CBitrixComponent
{
    /**
     * Инфоблок исполнителей
     *
     * @var integer
     */
    public $ispolnitelIblockId = 508;

    /**
     * Инфоблок поручений
     *
     * @var integer
     */
    public $ordersIblockId = 509;

    /**
     * Инфоблок протоколов
     *
     * @var integer
     */
    public $protocolsIblockId = 561;

    /**
     * Свойства для фиксации изменений
     *
     * @var array
     */
    public $props = ['NUMBER', 'DATE', 'EXECUTOR', 'VISAS', 'SIGNER'];

    /**
     * Подготовка параметров
     *
     * @param array $arParams Параметры компонента.
     *
     * @return array Обновлённые параметры.
     */
    public function onPrepareComponentParams(array $arParams): array
    {
        $arParams['CACHE_TIME'] = $arParams['CACHE_TIME'] ?? 3600;

        if (isset($arParams['IBLOCK_ID_ISPOLNITEL'])) {
            $this->ispolnitelIblockId = $arParams['IBLOCK_ID_ISPOLNITEL'];
        }

        if (isset($arParams['IBLOCK_ID_ORDERS'])) {
            $this->ordersIblockId = $arParams['IBLOCK_ID_ORDERS'];
        }

        if (isset($arParams['IBLOCK_ID_PROTOCOLS'])) {
            $this->protocolsIblockId = $arParams['IBLOCK_ID_PROTOCOLS'];
        }

        return $arParams;
    }

    /**
     * Настройки меню
     *
     * @return void
     */
    public function setMenu(): void
    {
        $arFilter = [
            'IBLOCK_ID' => $this->protocolsIblockId,
            'ACTIVE' => 'Y',
        ];
        $res = CIBlockElement::GetList(
            ['SORT' => 'ASC'],
            $arFilter,
            false,
            false,
            ['ID']
        );
        $this->arResult['MENU_ITEMS'][] = [
            'TEXT'      => 'Все',
            'URL'       => './',
            'ID'        => 'status_all',
            'COUNTER'   => $res->SelectedRowsCount(),
            'IS_ACTIVE' => empty($_REQUEST['status_filter'])
        ];

        foreach ($this->arResult['STATUS_LIST'] as $arStatus) {
            $arFilter['PROPERTY_STATUS'] = $arStatus['ID'];
            $res = CIBlockElement::GetList(
                ['SORT' => 'ASC'],
                $arFilter,
                false,
                false,
                ['ID']
            );
            $this->arResult['MENU_ITEMS'][] = [
                'TEXT'      => $arStatus['VALUE'],
                'URL'       => './?status_filter=' . $arStatus['ID'],
                'ID'        => 'status_' . $arStatus['ID'],
                'COUNTER'   => $res->SelectedRowsCount(),
                'IS_ACTIVE' => ($_REQUEST['status_filter'] == $arStatus['ID']),
            ];
        }
    }

    /**
     * Создание поручения
     *
     * @param integer $id     ID поручения.
     * @param array   $arData Массив данных.
     *
     * @return integer
     */
    public function setOrder(int $id = 0, array $arData = []): int
    {
        Loader::includeModule('iblock');
        $obIblockEl = new CIBlockElement();

        $arFields = [
            'IBLOCK_ID'         => $this->ordersIblockId,
            'IBLOCK_SECTION_ID' => false,
            'PROPERTY_VALUES'   => $arData['PROP'],
            'ACTIVE'            => 'Y',
            'DETAIL_TEXT'       => $arData['DETAIL_TEXT'],
            'XML_ID'            => $arData['XML_ID'],
            'EXTERNAL_ID'       => $arData['EXTERNAL_ID'],
        ];

        if (isset($arData['NAME'])) {
            $arFields['NAME'] = trim($arData['NAME']);
        }

        if ($id > 0) {
            $obIblockEl->Update($id, $arFields);
        } else {
            $id = $obIblockEl->Add($arFields);
            if ($id <= 0) {
                throw new Exception($obIblockEl->LAST_ERROR);
            }
        }

        return $id;
    }

    /**
     * Доступно ли сейчас редактирование?
     *
     * @param integer $iProtocolId ID протокола.
     * @param boolean $bEdit       Включено ли сейчас редактирование.
     *
     * @return boolean
     */
    public function isEdit(int $iProtocolId = 0, bool $bEdit = false): bool
    {
        $arProps = [];
        CIBlockElement::GetPropertyValuesArray(
            $arProps,
            $this->protocolsIblockId,
            ['ID' => $iProtocolId],
            [
                'CODE' => [
                    'DELO_ISN',
                    'DELO_STATUS'
                ]
            ]
        );

        if (!empty($arProps[ $iProtocolId ]['DELO_ISN']['VALUE'])) {
            $arDeloStatuses = (new Delo())->getBitrixStatusList();

            $iStatus = $arProps[ $iProtocolId ]['DELO_STATUS']['VALUE_ENUM_ID'];
            foreach ($arDeloStatuses as $status) {
                if ($status['ID'] == $iStatus) {
                    if ($status['XML_ID'] == Delo::DELO_STATUS_SIGNED) {
                        return false;
                    }
                }
            }
        } else {
            return true;
        }

        return $bEdit;
    }

    /**
     * Список протоколов
     *
     * @return void
     */
    public function getData(): void
    {
        $this->setMenu();
        $this->arResult['LIST_ID'] = 'checkorders_protocol_list';

        $gridOptions = new Grid\Options($this->arResult['LIST_ID']);
        $arSort = $gridOptions->getSorting(
            [
                'sort' => [
                    'DATE_CREATE' => 'ASC',
                ],
                'vars' => [
                    'by' => 'by',
                    'order' => 'order'
                ]
            ]
        );

        $arNavParams = $gridOptions->GetNavParams();

        $obNav = new UI\PageNavigation($this->arResult['LIST_ID']);
        $obNav->allowAllRecords(true)
            ->setPageSize($arNavParams['nPageSize'])
            ->initFromUri();

        if ($obNav->allRecordsShown()) {
            $arNavParams = false;
        } else {
            $arNavParams['iNumPage'] = $obNav->getCurrentPage();
        }

        $arStatusList = [];
        foreach ($this->arResult['STATUS_LIST'] as $arStatus) {
            $arStatusList[ $arStatus['ID'] ] = $arStatus['VALUE'];
        }

        $this->arResult['FILTER'] = [
            [
                'id' => 'NUMBER',
                'name' => 'Номер',
                'type' => 'text',
                'default' => true
            ],
            [
                'id' => 'DATE',
                'name' => 'Дата протокола',
                'type' => 'date',
                'default' => true
            ],
        ];

        if (!$_REQUEST['status_filter']) {
            $this->arResult['FILTER'][] = [
                'id' => 'STATUS',
                'name' => 'Статус',
                'type' => 'list',
                'items' => $arStatusList,
                'default' => true
            ];
        }

        $this->arResult['COLUMNS'] = [
            [
                'id' => 'NUMBER',
                'name' => 'Номер протокола',
                'sort' => 'property_NUMBER',
                'default' => true
            ],
            [
                'id' => 'NAME',
                'name' => 'Название',
                'sort' => 'name',
                'default' => true
            ],
            [
                'id' => 'DATE',
                'name' => 'Дата протокола',
                'sort' => 'property_DATE',
                'default' => true
            ],
            [
                'id' => 'STATUS',
                'name' => 'Статус',
                'sort' => 'propertysort_STATUS',
                'default' => true
            ],
            [
                'id' => 'EXECUTOR',
                'name' => 'Исполнитель',
                'sort' => false,
                'default' => true
            ],
            [
                'id' => 'VISAS',
                'name' => 'Визы',
                'sort' => false,
                'default' => true
            ],
            [
                'id' => 'SIGNER',
                'name' => 'Подписант',
                'sort' => false,
                'default' => true
            ],
            [
                'id' => 'COUNT',
                'name' => 'Поручений',
                'sort' => false,
                'default' => true
            ],
        ];

        $filterId = $this->arResult['LIST_ID'] . '_filter';
        $arfilterUI = (new UI\Filter\Options($filterId))->getFilter([]);

        $arFilter = [
            'IBLOCK_ID' => $this->protocolsIblockId,
            'ACTIVE' => 'Y'
        ];

        if ($arfilterUI['FIND'] != '') {
            $arFilter['%NAME'] = $arfilterUI['FIND'];
        }

        if ($arfilterUI['NUMBER'] != '') {
            $arFilter['PROPERTY_NUMBER'] = $arfilterUI['NUMBER'];
        }

        if ($_REQUEST['status_filter']) {
            $arFilter['PROPERTY_STATUS'] = $_REQUEST['status_filter'];
        } elseif ($arfilterUI['STATUS'] != '') {
            $arFilter['PROPERTY_STATUS'] = $arfilterUI['STATUS'];
        }

        if (isset($arfilterUI['DATE_from'])) {
            $arFilter[] = [
                'LOGIC'=>'AND',
                '>=PROPERTY_DATE' => $arfilterUI['DATE_from'],
                '<=PROPERTY_DATE' => $arfilterUI['DATE_to']
            ];
        }
        $res = CIBlockElement::GetList(
            $arSort['sort'],
            $arFilter,
            false,
            $arNavParams,
            [
                'ID',
                'NAME',
                'PROPERTY_STATUS',
                'PROPERTY_EXECUTOR',
                'PROPERTY_VISAS',
                'PROPERTY_SIGNER',
                'PROPERTY_NUMBER',
                'PROPERTY_DATE',
                'PROPERTY_*',
            ]
        );
        $obNav->setRecordCount($res->SelectedRowsCount());
        while ($row = $res->GetNext()) {
            $arOrders = $this->getOrders($row['ID']);

            $sStatus = '';
            if ($row['PROPERTY_STATUS_ENUM_ID'] > 0) {
                $sStatus = $row['PROPERTY_STATUS_VALUE'];
            }

            $sExecutor = '';
            if ($row['PROPERTY_EXECUTOR_VALUE'] > 0) {
                $uId = $row['PROPERTY_EXECUTOR_VALUE'];
                $sExecutor = $this->arResult['DELO_USERS'][ $uId ]['UF_NAME'];
            }

            $arVisas = [];
            foreach ($row['PROPERTY_VISAS_VALUE'] as $uId) {
                $arVisas[] = $this->arResult['DELO_USERS'][ $uId ]['UF_NAME'];
            }

            $sSigner = '';
            if ($row['PROPERTY_SIGNER_VALUE'] > 0) {
                $uId = $row['PROPERTY_SIGNER_VALUE'];
                $sSigner = $this->arResult['DELO_USERS'][ $uId ]['UF_NAME'];
            }

            $arData = [
                'data'    => [
                    'ID' => $row['ID'],
                    'NAME' => $row['NAME'],
                    'NUMBER' => $row['PROPERTY_NUMBER_VALUE'],
                    'DATE' => $row['PROPERTY_DATE_VALUE'],
                    'STATUS' => $sStatus,
                    'EXECUTOR' => $sExecutor,
                    'VISAS' => implode(', ', $arVisas),
                    'SIGNER' => $sSigner,
                    'COUNT' => count($arOrders)
                ],
                'class' => 'alert-white',
                'actions' => [
                    [
                        'text'    => 'Просмотр',
                        'default' => true,
                        'onclick' => 'location.href=\'?id=' . $row['ID'] . '\''
                    ]
                ]
            ];

            $this->arResult['ROWS'][] = $arData;
        }

        $this->arResult['NAV'] = $obNav;
    }

    /**
     * Информация о протоколе
     *
     * @param integer $iProtocolId ID протокола.
     * @param boolean $bGetOrders  Получать информацию о поручениях.
     *
     * @return array
     */
    public function getDetailData(int $iProtocolId = 0, bool $bGetOrders = true): array
    {
        if ($iProtocolId <= 0) {
            $iDeloUserId = 0;
            $iUserId = (int)$GLOBALS['USER']->GetID();
            // @crunch на локалке - Лямин
            if ($_SERVER['SERVER_NAME'] == 'localhost') {
                $iUserId = 570;
            }
            if ($_REQUEST['EXECUTOR'] > 0) {
                $iDeloUserId = $_REQUEST['EXECUTOR'];
            } else {
                foreach ($this->arResult['DELO_USERS'] as $arDeloUser) {
                    if (!empty($arDeloUser['UF_USER_ESTIMATE'])) {
                        $arEstimate = explode(',', $arDeloUser['UF_USER_ESTIMATE']);
                        foreach ($arEstimate as $uId) {
                            if ((int)$uId === $iUserId) {
                                $iDeloUserId = $arDeloUser['ID'];
                                break 2;
                            }
                        }
                    }
                }
            }

            return [
                'ALLOW_EDIT' => true,
                'NAME' => $_REQUEST['NAME'] ?? '',
                'NUMBER' => $_REQUEST['NUMBER'] ?? '',
                'DATE' => $_REQUEST['DATE'] ?? '',
                'STATUS' => $this->arResult['STATUS_LIST']['DRAFT']['ID'],
                'EXECUTOR' => $iDeloUserId,
                'VISAS' => $_REQUEST['VISAS'] ?? '',
                'SIGNER' => $_REQUEST['SIGNER'] ?? '',
            ];
        }

        $res = CIBlockElement::GetList(
            [
                'SORT' => 'ASC'
            ],
            [
                'IBLOCK_ID' => $this->protocolsIblockId,
                'ID' => $iProtocolId
            ],
            false,
            false,
            [
                'ID',
                'NAME',
                'DETAIL_TEXT',
                'PROPERTY_STATUS',
                'PROPERTY_NUMBER',
                'PROPERTY_DATE',
                'PROPERTY_EXECUTOR',
                'PROPERTY_VISAS',
                'PROPERTY_SIGNER',
                'PROPERTY_CHANGELOG',
                'PROPERTY_FILES',
                'PROPERTY_DELO_ISN',
                'PROPERTY_DELO_STATUS',
                'PROPERTY_DELO_NUMBER',
                'PROPERTY_DELO_DATE',
                'PROPERTY_ORDERS',
            ]
        );
        while ($row = $res->GetNext()) {
            $row['STATUS'] = $row['PROPERTY_STATUS_ENUM_ID'];
            foreach ($this->props as $prop) {
                $row[ $prop ] = $row['PROPERTY_' . $prop  . '_VALUE'];
            }
            $row['FULL_CHANGELOG'] = [];
            foreach ($row['~PROPERTY_CHANGELOG_VALUE'] as $changeRow) {
                $arChangeLog = Json::decode($changeRow);

                if (in_array($arChangeLog['FIELD_CODE'], ['EXECUTOR', 'VISAS', 'SIGNER'])) {
                    $arOldValues = [];
                    if (!is_array($arChangeLog['OLD'])) {
                        $arChangeLog['OLD'] = [$arChangeLog['OLD']];
                    }
                    foreach ($arChangeLog['OLD'] as $uId) {
                        $arOldValues[] = $this->arResult['DELO_USERS'][ $uId ]['UF_NAME'];
                    }
                    $arChangeLog['OLD'] = implode(', ', $arOldValues);

                    $arNewValues = [];
                    if (!is_array($arChangeLog['NEW'])) {
                        $arChangeLog['NEW'] = [$arChangeLog['NEW']];
                    }
                    foreach ($arChangeLog['NEW'] as $uId) {
                        $arNewValues[] = $this->arResult['DELO_USERS'][ $uId ]['UF_NAME'];
                    }
                    $arChangeLog['NEW'] = implode(', ', $arNewValues);
                }

                $arChangeLog['DATE_TS'] = strtotime($arChangeLog['DATE']);

                $row['FULL_CHANGELOG'][] = $arChangeLog;
            }
            usort(
                $row['FULL_CHANGELOG'],
                function ($a, $b) {
                    return strnatcmp($b['DATE_TS'], $a['DATE_TS']);
                }
            );

            $iLogSize = 10;
            if (count($row['FULL_CHANGELOG']) > $iLogSize) {
                $resChangeLog = new CDBResult();
                $resChangeLog->InitFromArray($row['FULL_CHANGELOG']);
                $resChangeLog->NavStart($iLogSize, false);
                $navComponentObject = false;
                $row['CHANGELOG_NAV'] = $resChangeLog->GetPageNavStringEx(
                    $navComponentObject,
                    'Изменения'
                );
                $row['CHANGELOG'] = [];
                while ($rowChangeLog = $resChangeLog->Fetch()) {
                    $row['CHANGELOG'][] = $rowChangeLog;
                }
            } else {
                $row['CHANGELOG'] = $row['FULL_CHANGELOG'];
                $row['CHANGELOG_NAV'] = '';
            }
            unset($row['FULL_CHANGELOG']);

            $row['FILES'] = [];
            if (!empty($row['PROPERTY_FILES_VALUE'])) {
                $obDeloSync = new Delo\Sync();
                foreach ($row['PROPERTY_FILES_VALUE'] as $iFileId) {
                    $arFileData = CFile::GetByID($iFileId)->Fetch();
                    $arFileData['src'] = CFile::GetPath($iFileId);
                    try {
                        $arDesc = $arFileData['DESCRIPTION'] ?
                            Json::decode($arFileData['DESCRIPTION']) :
                            [];
                        $arFileData = array_merge(
                            $arFileData,
                            $arDesc
                        );
                    } catch (Exception $e) {
                        ShowError($e->getMessage());
                    } catch (ArgumentException $e) {
                        ShowError($e->getMessage());
                    }

                    if (!empty($arFileData['ISN'])) {
                        $arSyncData = $obDeloSync->getLastChanges($row['ID'], $arFileData['ISN']);
                        if (!empty($arSyncData['UF_JSON'])) {
                            try {
                                $syncJson = Json::decode($arSyncData['UF_JSON']);
                            } catch (Exception | ArgumentException $e) {
                                ShowError($e->getMessage());
                                $syncJson = [
                                    'VISA_SIGN' => [],
                                    'PROT' => []
                                ];
                            }
                            $arFileData['SIGN_DATA'] = [];
                            foreach ($syncJson['VISA_SIGN'] as $arSign) {
                                if ($arSign['VISA_TYPE_ISN'] > 0) {
                                    $arFileData['SIGN_DATA'][ $arSign['ISN_PERSON'] ] = [
                                        'TEXT' => $arSign['VISA_TYPE_NAME'],
                                        'COMMENT' => $arSign['REP_TEXT'],
                                    ];
                                }
                            }
                            $arFileData['CHANGELOG_DATE'] = $arSyncData['UF_DATE']->format('d.m.Y H:i:s');
                            $arFileData['CHANGELOG'] = [];
                            $arSkipComments = [
                                'Статус проекта отправлен на портал'
                            ];
                            foreach ($syncJson['PROT'] as $arChange) {
                                if (in_array($arChange['COMMENT'], $arSkipComments)) {
                                    continue;
                                }
                                $arFileData['CHANGELOG'][] = $arChange['DESCRIBTION'] . ' ' . $arChange['COMMENT'];
                            }

                            $arFileData['CHANGELOG'] = array_map(
                                function ($a) {
                                    return str_replace(
                                        [
                                            'Действие процесса. ',
                                            'fillProt failed. ',
                                        ],
                                        '',
                                        $a
                                    );
                                },
                                $arFileData['CHANGELOG']
                            );
                        }
                    }
                    $row['FILES'][] = $arFileData;
                }
            }
            usort(
                $row['FILES'],
                function ($a, $b) {
                    return strnatcmp($b['DATE'], $a['DATE']);
                }
            );

            $bEdit = true;
            $bSync = true;
            if (!empty($row['PROPERTY_DELO_ISN_VALUE'])) {
                $bEdit = false;
                if (isset($_REQUEST['mode']) && $_REQUEST['mode'] == 'edit') {
                    $bEdit = true;
                }
                $iStatus = $row['PROPERTY_DELO_STATUS_ENUM_ID'];
                $arDeloStatuses = (new Delo())->getBitrixStatusList();
                foreach ($arDeloStatuses as $status) {
                    if ($status['ID'] == $iStatus) {
                        if ($status['XML_ID'] == Delo::DELO_STATUS_SIGNED) {
                            $bSync = false;
                        }
                    }
                }
            }

            $row['ALLOW_EDIT'] = $this->isEdit($row['ID'], $bEdit);
            $row['ALLOW_SYNC'] = $bSync;

            if ($bGetOrders) {
                $row['ROWS'] = $this->getOrders($row['ID']);
                $row['ORDERS_TABLE'] = $this->getOrdersHtmlTable($row['ID'], $bEdit);
                $row['REAL_ORDERS_TABLE'] = $this->getRealOrdersHtmlTable($row['ID']);
            }

            return $row;
        }

        return [];
    }

    /**
     * Информация о созданном поручении.
     *
     * @param integer $iOrderId ID поручения.
     *
     * @return array
     */
    public function getRealOrder(int $iOrderId = 0): array
    {
        $arFilter = [
            'IBLOCK_ID' => $this->ordersIblockId,
            'ACTIVE' => 'Y',
            'ID' => $iOrderId
        ];
        $arSelect = [
            'ID',
            'NAME',
            'IBLOCK_ID',
            'DETAIL_TEXT',
            'DETAIL_PAGE_URL',
            'PROPERTY_ISPOLNITEL',
            'PROPERTY_DATE_ISPOLN',
            'PROPERTY_STATUS',
            'PROPERTY_ACTION',
        ];
        $resOrders = CIBlockElement::GetList(
            [],
            $arFilter,
            false,
            ['nTopCount' => 1],
            $arSelect
        );
        if ($arFields = $resOrders->GetNext()) {
            return [
                'ID'            => $arFields['ID'],
                'NAME'          => $arFields['NAME'],
                'TEXT'          => $arFields['~DETAIL_TEXT'],
                'SROK'          => $arFields['PROPERTY_DATE_ISPOLN_VALUE'],
                'ACTION'        => $arFields['PROPERTY_ACTION_VALUE'],
                'ACTION_ENUM'   => $arFields['PROPERTY_ACTION_ENUM_ID'],
                'STATUS'        => $arFields['PROPERTY_STATUS_VALUE'],
                'STATUS_ENUM'   => $arFields['PROPERTY_STATUS_ENUM_ID'],
                'EXECUTOR'      => $arFields['PROPERTY_ISPOLNITEL_VALUE'],
                'URL'           => '/control-orders/?detail=' . $arFields['ID'],
            ];
        }

        return [];
    }
    /**
     * Получить список поручений из протокола.
     *
     * @param integer $iProtocolId ID протокола.
     *
     * @return array
     *
     * @todo Перенести на D7
     */
    public function getOrders(int $iProtocolId = 0): array
    {
        if ($iProtocolId <= 0) {
            return [];
        }

        $arOrdersList = [];
        CIBlockElement::GetPropertyValuesArray(
            $arOrdersList,
            $this->protocolsIblockId,
            ['ID' => $iProtocolId],
            ['CODE' => 'ORDERS']
        );

        $arOrdersTmp = [];
        $arOrdersData = $arOrdersList[ $iProtocolId ]['ORDERS'];
        foreach ($arOrdersData['~VALUE'] as $id => $order) {
            $text = Json::decode($order['TEXT']);
            $text['PROP_VALUE_ID'] = $arOrdersData['PROPERTY_VALUE_ID'][ $id ];
            $text['PROP_DESCRIPTION'] = $arOrdersData['DESCRIPTION'][ $id ];
            $arOrdersTmp[ $text['HASH'] ] = $text;
        }
        uasort($arOrdersTmp, $this->buildSorter('SORT'));

        $arReturn = [];
        $arTmpRows = [];
        $iLastId = 0;
        $strLastName = '';
        foreach ($arOrdersTmp as $data) {
            if ($strLastName != $data['NAME']) {
                $iLastId++;
                $strLastName = $data['NAME'];
            }
            $data['TITLE'] = $data['NAME'] . ' ' . $data['TEXT'];
            $data['TITLE'] .= '<br/>Срок - ' . $data['SROK'];
            $data['EXECUTOR'] = $data['PROP']['ISPOLNITEL'];
            $arTmpRows[ $iLastId ][] = $data;
        }

        $index1 = 1;
        foreach ($arTmpRows as $rows) {
            $index2 = 1;
            foreach ($rows as $row) {
                $row['FIRST'] = $index1;
                $row['SECOND'] = $index2;
                $index2++;
                $arReturn[ $row['HASH'] ] = $row;
            }
            $index1++;
        }

        $arNumbers = [];
        foreach ($arReturn as $row) {
            $arNumbers[ $row['FIRST'] ][ $row['SECOND'] ] = 1;
        }

        foreach ($arReturn as $id => $row) {
            $arReturn[ $id ]['IS_SINGLE'] = (count($arNumbers[ $row['FIRST'] ]) === 1);
        }

        return $arReturn;
    }

    /**
     * Генерация HTML-таблицы для списка поручений
     *
     * @param integer $iProtocolId ID протокола.
     * @param boolean $bEdit       Включено ли сейчас редактирование.
     *
     * @return string
     *
     * @todo вынести генерацию html
     */
    public function getOrdersHtmlTable(int $iProtocolId = 0, bool $bEdit = false): string
    {
        if ($iProtocolId <= 0) {
            return '';
        }
        $arOrders = $this->getOrders($iProtocolId);
        $arExecutors = $this->getExecutors();

        $isEdit = $this->isEdit($iProtocolId, $bEdit);

        $strReturn = '
            <table
                class="table table-bordered table-sorting js-orders-list"
                id="sort-table">
                <thead class="thead-light">
                    <tr>
                        <th scope="col" colspan="' . ($isEdit ? 3 : 2) . '">Содержание</th>
                        <th scope="col">Исполнитель</th>
                    </tr>
                </thead>
                <tbody>';
        foreach ($arOrders as $row) {
            $strReturn .= '
                <tr>
                    <td class="' . ($isEdit ? 'sorter' : '') . '" width="50" align="center">
                        ' . $row['FIRST'] .
                        (!$row['IS_SINGLE'] ? '.' . $row['SECOND'] : '') . '
                        <input
                            type="hidden"
                            name="sort[]"
                            value="' . $row['HASH'] . '">
                    </td>
                    ' . ($isEdit ? '
                    <td width="50" align="center">
                        <a
                            href="javascript:void(0);"
                            class="ui-btn ui-btn-primary ui-btn-icon-remove js-remove-order"
                            data-protocol="' .  $iProtocolId . '"
                            data-hash="' .  $row['HASH'] . '"></a>
                    </td>
                    ' : '') . '
                    <td>
                        <a
                            href="javascript:void(0);"
                            data-protocol="' .  $iProtocolId . '"
                            data-hash="' .  $row['HASH'] . '"
                            class="' . ($isEdit ? 'js-edit-order' : '') . '">
                                ' . $row['TITLE'] . '
                        </a>
                    </td>
                    <td>
                        ' . $arExecutors[ $row['EXECUTOR'] ]['NAME'] . '
                    </td>
                </tr>';
        }

        $strReturn .= '</tbody></table>';

        return $strReturn;
    }

    /**
     * Генерация HTML-таблицы для созданных поручений
     *
     * @param integer $iProtocolId ID протокола.
     *
     * @return string
     *
     * @todo вынести генерацию html
     */
    public function getRealOrdersHtmlTable(int $iProtocolId = 0): string
    {
        if ($iProtocolId <= 0) {
            return '';
        }
        $arOrders = $this->getOrders($iProtocolId);
        $arExecutors = $this->getExecutors();

        $realOrders = [];
        foreach ($arOrders as $arOrder) {
            if (empty($arOrder['ORDERS'])) {
                continue;
            }
            foreach ($arOrder['ORDERS'] as $realId) {
                $arRealOrder = $this->getRealOrder($realId);
                if (!empty($arRealOrder)) {
                    $realOrders[] = $arRealOrder;
                }
            }

            foreach ($arOrder['SUBORDERS'] as $realId) {
                $arRealOrder = $this->getRealOrder($realId);
                if (!empty($arRealOrder)) {
                    $realOrders[] = $arRealOrder;
                }
            }
        }

        $strReturn = '
            <table class="table table-bordered js-real-orders">
                <thead class="thead-light">
                    <tr>
                        <th scope="col" width="35%">Название поручения</th>
                        <th scope="col" width="30%">Исполнитель</th>
                        <th scope="col" width="15%">Срок исполнения</th>
                        <th scope="col" width="10%">Состояние</th>
                        <th scope="col" width="10%">Статус</th>
                    </tr>
                </thead>
                <tbody>';
        foreach ($realOrders as $row) {
            $strReturn .= '
                <tr>
                    <td>
                        <a href="' . $row['URL'] . '" target="_blank">
                            ' . $row['TEXT'] . '
                        </a>
                    </td>
                    <td>' . $arExecutors[ $row['EXECUTOR'] ]['NAME'] . '</td>
                    <td>' . ($row['SROK']!='31.12.2099'?$row['SROK']:'Без срока') . '</td>
                    <td>' . $row['ACTION'] . '</td>
                    <td>' . $row['STATUS'] . '</td>
                </tr>';
        }

        $strReturn .= '</tbody></table>';

        return $strReturn;
    }

    /**
     * Получить список исполниетелй
     *
     * @return array
     *
     * @todo Перенести на D7
     */
    public function getExecutors(): array
    {
        $arResult = [];
        $res = CIBlockPropertyEnum::GetList(
            ['DEF' => 'DESC', 'SORT' => 'ASC'],
            ['IBLOCK_ID' => $this->ispolnitelIblockId, 'CODE' => 'TYPE']
        );
        while ($row = $res->GetNext()) {
            $row['NAME'] = 'Все ' . $row['VALUE'];
            $row['~NAME'] = 'Все ' . $row['VALUE'];
            $row['COUNT'] = 0;
            $arResult[ 'all_' . $row['ID'] ] = $row;
        }

        $arSelect = [
            'ID', 'NAME',
            'DATE_ACTIVE_FROM', 'PROPERTY_RUKOVODITEL',
            'PROPERTY_ZAMESTITELI', 'PROPERTY_ISPOLNITELI',
            'PROPERTY_TYPE'
        ];
        $arFilter = [
            'IBLOCK_ID' => $this->ispolnitelIblockId,
            'ACTIVE_DATE' => 'Y',
            'ACTIVE' => 'Y'
        ];
        $res = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
        while ($arFields = $res->GetNext()) {
            $arFields['COUNT'] = 1;
            $arResult[ $arFields['ID'] ] = $arFields;

            if ($arFields['PROPERTY_TYPE_ENUM_ID'] != '') {
                $arResult[ 'all_' . $arFields['PROPERTY_TYPE_ENUM_ID'] ]['COUNT']++;
            }
        }

        $arResult = array_filter(
            $arResult,
            static function ($k) {
                return $k['COUNT'] > 0;
            }
        );

        return $arResult;
    }

    /**
     * Список статусов протокола
     *
     * @return array
     *
     * @todo переписать на D7
     */
    public function getStatusList(): array
    {
        $arResult = [];
        $res = CIBlockPropertyEnum::GetList(
            ['DEF' => 'DESC', 'SORT' => 'ASC'],
            ['IBLOCK_ID' => $this->protocolsIblockId, 'CODE' => 'STATUS']
        );
        while ($row = $res->GetNext()) {
            $arResult[ $row['XML_ID'] ] = $row;
        }

        return $arResult;
    }

    /**
     * Сортировка любого массива.
     *
     * @param string $key Поле для сортировки.
     *
     * @return Closure
     */
    public function buildSorter(string $key): callable
    {
        return static function ($a, $b) use ($key) {
            return strnatcmp($a[ $key ], $b[ $key ]);
        };
    }

    /**
     * Изменение\добавление протокола
     *
     * @return boolean|null
     *
     * @todo Перенести на D7
     */
    protected function setData(): ?bool
    {
        $obIblockEl = new CIBlockElement();
        $iProtocolId = (int)$_REQUEST['id'];
        $arFields = [
            'ACTIVE' => 'Y',
            'IBLOCK_ID' => $this->protocolsIblockId,
            'NAME' => $_REQUEST['NAME']
        ];

        if ($iProtocolId > 0) {
            $arCurrentValue = $this->getDetailData($iProtocolId);
            $res = $obIblockEl->Update($iProtocolId, $arFields, true);
            if ($res) {
                if ($arCurrentValue['NAME'] !== $arFields['NAME']) {
                    $this->log(
                        $iProtocolId,
                        [
                            'FIELD' => 'Название',
                            'FIELD_CODE' => 'NAME',
                            'OLD' => $arCurrentValue['NAME'],
                            'NEW' => $arFields['NAME']
                        ]
                    );
                }

                $arPropNames = [];
                $res = CIBlockElement::GetProperty($this->protocolsIblockId, 0);
                while ($prop = $res->GetNext()) {
                    $arPropNames[ $prop['CODE'] ] = $prop['NAME'];
                }

                foreach ($this->props as $prop) {
                    $arNewProp = $_REQUEST[ $prop ];
                    CIBlockElement::SetPropertyValuesEx(
                        $iProtocolId,
                        $this->protocolsIblockId,
                        [
                            $prop => $arNewProp
                        ]
                    );

                    $arCurrentProp = $arCurrentValue['PROPERTY_' . $prop . '_VALUE'];
                    if (serialize($arCurrentProp) !== serialize($arNewProp)) {
                        $this->log(
                            $iProtocolId,
                            [
                                'FIELD' => $arPropNames[ $prop ],
                                'FIELD_CODE' => $prop,
                                'OLD' => $arCurrentProp,
                                'NEW' => $arNewProp
                            ]
                        );
                    }
                }
            }
        } else {
            $arFields['PROPERTY_VALUES'] = [
                'STATUS' => $this->arResult['STATUS_LIST']['DRAFT']['ID'],
            ];
            foreach ($this->props as $prop) {
                $arFields['PROPERTY_VALUES'][ $prop ] = $_REQUEST[ $prop ];
            }
            $iProtocolId = $obIblockEl->Add($arFields, true);
            $res = ($iProtocolId > 0);
            if ($res) {
                $this->log(
                    $iProtocolId,
                    [
                        'FIELD' => 'Создан новый протокол'
                    ]
                );
            }
        }

        if ($res) {
            LocalRedirect('/control-orders/protocol/?id=' . $iProtocolId);
            return true;
        }

        echo '<div class="alert alert-danger" role="alert">';
        echo $obIblockEl->LAST_ERROR;
        echo '</div>';

        return false;
    }

    /**
     * Логгирование изменений протокола.
     *
     * @param integer $iProtocolId ID протокола.
     * @param array   $arData      Массив с данными.
     *
     * @return boolean
     */
    public function log(int $iProtocolId = 0, array $arData = []): bool
    {
        global $USER;
        if ($iProtocolId <= 0) {
            return false;
        }

        $arCurrent = [];
        $res = CIBlockElement::GetProperty(
            $this->protocolsIblockId,
            $iProtocolId,
            'sort',
            'asc',
            ['CODE' => 'CHANGELOG']
        );
        while ($row = $res->GetNext()) {
            $arCurrent[] = $row['~VALUE'];
        }

        $iUserId = $USER->GetID();
        $strUserName = $USER->GetFullName();
        if ($iUserId <= 1) {
            $strUserName = 'Синхронизация';
        }

        $arNewData = [
            'USER_ID' => $iUserId,
            'USER_NAME' => $strUserName,
            'DATE' => date('d.m.Y H:i:s'),
            'FIELD' => $arData['FIELD'] ?? '',
            'FIELD_CODE' => $arData['FIELD_CODE'] ?? '',
            'OLD' => $arData['OLD'] ?? '',
            'NEW' => $arData['NEW'] ?? '',
        ];

        $arCurrent[] = Json::encode($arNewData);
        CIBlockElement::SetPropertyValuesEx(
            $iProtocolId,
            false,
            [
                'CHANGELOG' => $arCurrent
            ]
        );
        return true;
    }

    /**
     * Генерация документа для протокола
     *
     * @param integer $iProtocolId ID протокола.
     *
     * @return string
     *
     * @throws IO\FileNotFoundException Файл не найден.
     * @throws RuntimeException         Ошибка создания папки.
     *
     * @todo Перенести на D7
     */
    public function generateDocument(int $iProtocolId = 0)
    {
        Loader::includeModule('documentgenerator');

        $file = new IO\File(__DIR__ . '/template.docx');
        $body = new DocumentGenerator\Body\Docx($file->getContents());
        $body->normalizeContent();

        $arFileName = [];

        $arData = $this->getDetailData($iProtocolId);
        $arTmpRows = [];
        $arContentItems = [];
        $iLastId = 0;
        $strLastName = '';

        foreach ($arData['ROWS'] as $row) {
            $row['TEXT'] = strip_tags($row['TEXT']);
            $row['TEXT'] .= '|||Срок - ' . strip_tags($row['SROK']);
            if ($strLastName != $row['NAME']) {
                $iLastId++;
                $strLastName = $row['NAME'];
            }
            $arTmpRows[ $iLastId ][] = $row;
            $arFileName[] = $row['TIMESTAMP_X'];
        }

        foreach ($arTmpRows as $rows) {
            foreach ($rows as $rowId => $row) {
                $indexText = $row['FIRST'] . '. ' . $row['NAME'];
                if ($rowId === 0 && !$row['IS_SINGLE']) {
                    if (false === mb_strpos($indexText, ':')) {
                        $indexText .= ': ';
                    }
                    $arContentItems[] = [
                        'Content' => $indexText
                    ];
                }
                $textArray = explode('|||', $row['TEXT']);
                $bShowInd = false;
                foreach ($textArray as $ind => $strRow) {
                    $rowText = trim(strip_tags($strRow));
                    if (count($rows) <= 1 && $ind === 0) {
                        $rowText = $indexText . ' ' . $rowText;
                        $bShowInd = true;
                    } elseif (!$bShowInd) {
                        $rowText = $row['SECOND'] . ') ' . $rowText;
                        $bShowInd = true;
                    }
                    if (!empty($rowText)) {
                        $arContentItems[] = [
                            'Content' => $rowText
                        ];
                    }
                }
            }
        }

        $arDeloUsers = (new Delo\Users())->getList();

        $arNames = [
            [
                'Content' => 'Постановление №' . $arData['NUMBER']
            ]
        ];
        $arName = explode(PHP_EOL, $arData['NAME']);
        foreach ($arName as $sName) {
            $arNames[] = [
                'Content' => $sName
            ];
        }

        $arDocument = [
            'ID' => $arData['NUMBER'],
            'DATE_ORIGINAL' => date('d.m.Y', strtotime($arData['DATE'])),
            'DATE_FORMAT' => FormatDate('d F Y года', strtotime($arData['DATE'])),
            'Names' => new ArrayDataProvider(
                $arNames,
                [
                    'ITEM_NAME' => 'Name',
                    'ITEM_PROVIDER' => ArrayDataProvider::class
                ]
            ),
            'NamesContent' => 'Names.Name.Content',

            'SIGNER_POST' => $arDeloUsers[ $arData['SIGNER'] ]['UF_DUTY'],
            'SIGNER_NAME' => $arDeloUsers[ $arData['SIGNER'] ]['UF_NAME'],

            'AUTHOR_NAME' => $arDeloUsers[ $arData['EXECUTOR'] ]['UF_NAME'],
            'AUTHOR_CONTACTS' => '',

            'Items' => new ArrayDataProvider(
                $arContentItems,
                [
                    'ITEM_NAME' => 'Item',
                    'ITEM_PROVIDER' => ArrayDataProvider::class
                ]
            ),
            'ItemsContent' => 'Items.Item.Content',
        ];

        $body->setValues($arDocument);
        $body->process();
        $strContent = $body->getContent();
        $docPath = '/upload/checkorders.protocol/';
        $strFileName = $iProtocolId . '_' . crc32(serialize($arFileName)) . '.docx';
        $path = $_SERVER['DOCUMENT_ROOT'] . $docPath;
        if (!mkdir($path, 0775, true) && !is_dir($path)) {
            throw new RuntimeException('Directory "' . $docPath . '" was not created');
        }
        file_put_contents($path . $strFileName, $strContent);

        return $docPath . $strFileName;
    }

    /**
     * В АСЭД Дело при переводе пользователя
     * старый деактивируется и создается новый
     *
     * Этим методом обновит пользователей (если найдет)
     *
     * @param integer $iProtocolId ID протокола.
     *
     * @return string
     *
     * @todo Нужно потестить на реальных данных
     * @todo Логировать ли изменение?
     */
    public function syncUsers(int $iProtocolId = 0): string
    {
        Loader::includeModule('iblock');
        Loader::includeModule('citto.integration');
        $arDeloUsers = (new Delo\Users())->getList(false);

        $arFilter = [
            'IBLOCK_ID' => $this->protocolsIblockId,
            'ACTIVE' => 'Y'
        ];
        if ($iProtocolId > 0) {
            $arFilter['ID'] = $iProtocolId;
        }

        /**
         * Найти активного пользователя по полю "Предполагаемый пользователь"
         *
         * @param string $estimate Предполагаемый пользователь.
         *
         * @return integer
         */
        function findUserByEstimateId(string $estimate = ''): int
        {
            if (!empty($estimate)) {
                $arDeloUsers = (new Delo\Users())->getList();
                foreach ($arDeloUsers as $curRow) {
                    if (empty($curRow['UF_USER_ESTIMATE'])) {
                        continue;
                    }
                    if ($curRow['UF_USER_ESTIMATE'] == $estimate) {
                        return $curRow['ID'];
                    }
                }
            }

            return 0;
        }

        $res = CIBlockElement::GetList(
            [],
            $arFilter,
            false,
            false,
            [
                'ID',
                'NAME',
                'PROPERTY_EXECUTOR',
                'PROPERTY_VISAS',
                'PROPERTY_SIGNER',
                'PROPERTY_*',
            ]
        );
        $userFields = ['EXECUTOR', 'VISAS', 'SIGNER'];
        while ($row = $res->GetNext()) {
            foreach ($userFields as $prop) {
                if (!empty($row['PROPERTY_' . $prop . '_VALUE'])) {
                    $value = $row['PROPERTY_' . $prop . '_VALUE'];

                    if (is_array($value)) {
                        $newValue = [];
                        foreach ($value as $valueId => $iDeloUser) {
                            $newValue[ $valueId ] = $iDeloUser;
                            $selected = $arDeloUsers[ $iDeloUser ];
                            if ($selected['UF_ACTIVE'] == 0) {
                                $newUser = findUserByEstimateId($selected['UF_USER_ESTIMATE']);
                                if ($newUser > 0) {
                                    $newValue[ $valueId ] = $newUser;
                                }
                            }
                        }
                        if (serialize($value) !== serialize($newValue)) {
                            echo '[' . $row['ID'] . '] Update ' . $prop .
                                ' from ' . print_r($value, true) .
                                ' to ' . print_r($newValue, true) . '<br/><br/>';
                        }
                    } else {
                        if ($arDeloUsers[ $value ]['UF_ACTIVE'] == 0) {
                            $newUser = findUserByEstimateId($arDeloUsers[ $value ]['UF_USER_ESTIMATE']);
                            if ($newUser > 0) {
                                echo '[' . $row['ID'] . '] Update ' . $prop .
                                    ' from ' . print_r($value, true) .
                                    ' to ' . print_r($newUser, true) . '<br/><br/>';
                            }
                        }
                    }
                }
            }
        }

        return __METHOD__ . '();';
    }

    /**
     * Запуск компонента
     *
     * @return void
     */
    public function executeComponent()
    {
        try {
            global $APPLICATION, $USER;
            Loader::includeModule('iblock');
            Loader::includeModule('citto.integration');
            UI\Extension::load(
                [
                    'ui.buttons',
                    'ui.buttons.icons',
                    'ui.dialogs',
                    'ui.dialogs.messagebox'
                ]
            );
            CJSCore::Init(['jquery3', 'popup', 'ui']);

            $arUserGroups = $USER->GetUserGroupArray();
            $bHasAccess = false;

            /*
             * Группа "Контроль поручений – Управление протокола"
             */
            if (in_array(114, $arUserGroups)) {
                $bHasAccess = true;
            }

            if ($USER->IsAdmin()) {
                $bHasAccess = true;
            }

            if ($bHasAccess) {
                $css = [
                    '/bitrix/templates/.default/bootstrap.min.css',
                    '/local/js/jstree/themes/default/style.min.css',
                    '/bitrix/css/main/grid/webform-button.css',
                    '/local/js/adminlte/css/AdminLTE.min.css',
                    '/local/js/adminlte/css/skins/_all-skins.min.css',
                ];
                array_walk(
                    $css,
                    static function ($path) {
                        Asset::getInstance()
                            ->addCss($path);
                    }
                );
                $js = [
                    '/local/js/jstree/jstree.min.js',
                    '/local/js/jquery.ui.1.12.1.js',
                ];
                array_walk(
                    $js,
                    static function ($path) {
                        Asset::getInstance()
                            ->addString('<script src="' . $path . '"></script>');
                    }
                );

                $strBodyClass = $APPLICATION->GetPageProperty('BodyClass', '');
                $arBodyClass = explode(' ', $strBodyClass);
                $arBodyClass[] = 'pagetitle-toolbar-field-view';
                $APPLICATION->SetPageProperty(
                    'BodyClass',
                    implode(' ', $arBodyClass)
                );

                $this->arResult['EXECUTORS'] = $this->getExecutors();
                $this->arResult['STATUS_LIST'] = $this->getStatusList();
                $this->arResult['DELO_USERS'] = (new Delo\Users())->getList();

                $template = 'list';
                if (isset($_REQUEST['do']) && $_REQUEST['do'] == 'update') {
                    $this->setData();
                    $this->arResult['DETAIL'] = $this->getDetailData($_REQUEST['id']);
                    $template = 'detail';
                } elseif ($_REQUEST['id'] != '') {
                    $this->arResult['DETAIL'] = $this->getDetailData($_REQUEST['id']);
                    $template = 'detail';
                } else {
                    $this->getData();
                }

                $this->includeComponentTemplate($template);
            } else {
                ShowError('Доступ запрещён');
            }
        } catch (Exception $e) {
            ShowError($e->getMessage());
        }
    }
}
