<?php

namespace Citto\ControlOrders\Import;

use CFile;
use CJSCore;
use Exception;
use Bitrix\Main\UI;
use CIBlockElement;
use CBitrixComponent;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Page\Asset;
use Citto\Integration\Delo;
use Bitrix\Main\LoaderException;
use Citto\ControlOrders\Settings;
use Bitrix\Main\Grid\Panel\Actions;
use Bitrix\Main\Grid\Panel\DefaultValue;
use Bitrix\Main\Grid\Panel\Snippet\Button;
use Bitrix\Main\Grid\Panel\Snippet\Onchange;
use Citto\ControlOrders\Protocol\Component as Protocols;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * Класс для импорта поручений из АСЭД Дело
 */
class Component extends CBitrixComponent
{
    /**
     * Запуск компонента
     *
     * @return void
     *
     * @throws LoaderException Ошибка при загрузке модуля.
     */
    public function executeComponent()
    {
        try {
            global $APPLICATION;
            Loader::includeModule('iblock');
            Loader::includeModule('citto.integration');
            CBitrixComponent::includeComponentClass('citto:checkorders.protocol');
            $obProtocol = new Protocols();
            UI\Extension::load(
                [
                    'ui.buttons',
                    'ui.buttons.icons',
                    'ui.dialogs',
                    'ui.dialogs.messagebox'
                ]
            );
            CJSCore::Init(['jquery3', 'popup', 'ui']);

            $arCss = [
                '/bitrix/templates/.default/bootstrap.min.css',
                '/local/js/jstree/themes/default/style.min.css',
                '/bitrix/css/main/grid/webform-button.css',
                '/local/js/adminlte/css/AdminLTE.min.css',
                '/local/js/adminlte/css/skins/_all-skins.min.css',
            ];
            array_walk(
                $arCss,
                static function ($path) {
                    Asset::getInstance()
                        ->addCss($path);
                }
            );

            $sBodyClass = $APPLICATION->GetPageProperty('BodyClass', '');
            $arBodyClass = explode(' ', $sBodyClass);
            $arBodyClass[] = 'pagetitle-toolbar-field-view';
            $APPLICATION->SetPageProperty(
                'BodyClass',
                implode(' ', $arBodyClass)
            );

            $this->arResult['ISPOLNITELS'] = $obProtocol->getExecutors();
            $this->arResult['EXECUTORS_ID'] = [];
            $this->arResult['EXECUTORS_NAME'] = [];
            foreach ($this->arResult['ISPOLNITELS'] as $key => $value) {
                $this->arResult['EXECUTORS_NAME'][ $key ] = $value['~NAME'];
                $this->arResult['EXECUTORS_ID'][ $value['~NAME'] ] = $key;
                if ($key == 7811) {
                    $value['~NAME'] = str_replace(' (Гостехнадзор)', '', $value['~NAME']);
                    $this->arResult['EXECUTORS_NAME'][ $key ] = $value['~NAME'];
                    $this->arResult['EXECUTORS_ID'][ $value['~NAME'] ] = $key;
                }
                if ($key == 7828) {
                    $value['~NAME'] = str_replace(' в Тульской области', '', $value['~NAME']);
                    $this->arResult['EXECUTORS_NAME'][ $key ] = $value['~NAME'];
                    $this->arResult['EXECUTORS_ID'][ $value['~NAME'] ] = $key;
                }
            }

            $iStep = $_REQUEST['step'] ?? 1;

            $arCustomFields = [];
            $sCustomFile = $_SERVER['DOCUMENT_ROOT'] .
                            '/upload/checkorders.import/' .
                            $_REQUEST['hash'] . '_custom.json';
            if (file_exists($sCustomFile)) {
                $arCustomFields = Json::decode(file_get_contents($sCustomFile));
            }

            switch ($iStep) {
                case 2:
                    if (!isset($_REQUEST['hash']) || empty($_REQUEST['hash'])) {
                        if (empty($_REQUEST['NUMBER'])) {
                            $this->arResult['ERROR'] = 'Не указан номер протокола';
                            $iStep = 1;
                        } elseif (empty($_REQUEST['DATE'])) {
                            $this->arResult['ERROR'] = 'Не указана дата протокола';
                            $iStep = 1;
                        } else {
                            /*
                             * В параметрах не пришёл md5 файла, нужно обменяться
                             */
                            $obDelo = new Delo();
                            $arProtocol = $obDelo->getData(
                                $_REQUEST['NUMBER'] ?? '',
                                $_REQUEST['DATE'] ?? '',
                                $_REQUEST['ISN'] ?? ''
                            );
                            if (!empty($arProtocol['error'])) {
                                $this->arResult['ERROR'] = $arProtocol['error'];
                                if (!empty($arProtocol['result'])) {
                                    $this->arResult['RESULT'] = $arProtocol['result'];
                                }
                                $iStep = 1;
                            } else {
                                /*
                                 * Файлик создался, редиректнуть
                                 */
                                $arRedirParams = [
                                    'step' => 2,
                                    'hash' => $arProtocol['result']
                                ];
                                $sRedirUrl = $APPLICATION->GetCurPageParam(
                                    http_build_query($arRedirParams),
                                    array_keys($arRedirParams)
                                );
                                LocalRedirect($sRedirUrl);
                            }
                        }
                        break;
                    }

                    try {
                        $arProtocolData = $this->getDataFromFile($_REQUEST['hash']);
                    } catch (Exception $e) {
                        $this->arResult['ERROR'] = $e->getMessage();
                        $iStep = 1;
                        break;
                    }

                    /*
                     * editSelectedSave присылает данные из формы, положить в сессию
                     */
                    if (isset($_REQUEST['grid_action'])) {
                        foreach ($_REQUEST['FIELDS'] as $isn => $arFields) {
                            foreach ($arFields as $key => $value) {
                                $arCustomFields[ $isn ][ $key ] = $value;
                            }
                        }
                        file_put_contents($sCustomFile, Json::encode($arCustomFields));
                    }

                    $this->arResult['CREATED'] = $this->getCreated();

                    $this->arResult['ROWS'] = [];
                    foreach ($arProtocolData['resols'] as $k => $arResol) {
                        $sExecutorName = $arResol['EXECUTORS'][0]['EXECUTOR']['PARENT']['NAME'];
                        $arExecutors = [];

                        foreach ($arResol['EXECUTORS'] as $execRow) {
                            $strExecutor = '';
                            if ($execRow['MAIN']) {
                                $sExecutorName = $execRow['EXECUTOR']['PARENT']['NAME'];
                                $strExecutor .= '[Главный] ';
                            }
                            $strExecutor .= $execRow['EXECUTOR']['SURNAME'];
                            $strExecutor .= ' (' . $execRow['EXECUTOR']['PARENT']['NAME'] . ')';

                            $arExecutors[] = $strExecutor;
                        }

                        /*
                         * исполнитель есть в сессии, перетираем
                         */
                        if (isset($arCustomFields[ $arResol['ISN'] ]['ISPOLNITEL'])) {
                            $sExecutorName = $this->arResult['EXECUTORS_NAME'][
                                $arCustomFields[ $arResol['ISN'] ]['ISPOLNITEL']
                            ];
                        }

                        $iExecutorId = $this->arResult['EXECUTORS_ID'][ $sExecutorName ] ?? 0;

                        if ($iExecutorId <= 0 && false !== mb_strpos($sExecutorName, ' муниципального образования ')) {
                            $sNewVal = str_replace(' муниципального образования ', ' МО ', $sExecutorName);
                            $iExecutorId = $this->arResult['EXECUTORS_ID'][ $sNewVal ] ?? $iExecutorId;
                        }

                        if ($iExecutorId <= 0 && false !== mb_strpos($sExecutorName, ' муниципального образования город ')) {
                            $sNewVal = str_replace(' муниципального образования город ', ' МО г. ', $sExecutorName);
                            $iExecutorId = $this->arResult['EXECUTORS_ID'][ $sNewVal ] ?? $iExecutorId;
                        }

                        $arData = [
                            'id'        => $arResol['ISN'],
                            'data'      => [
                                'ID'                => $arResol['ISN'],
                                'NAME'              => $arProtocolData['ANNOTAT'],
                                'NUMBER'            => $arProtocolData['FREE_NUM'],
                                'TEXT'              => $arResol['ANNOTAT'],
                                'FINDED'            => [],
                                'DATE_ISPOLN'       => $arResol['PLANDATE'],
                                'DATE_CREATE'       => $arProtocolData['DOC_DATE'],
                                'ISPOLNITEL'        => $iExecutorId,
                                'ISPOLNITEL_DELO'   => implode('<br/>', $arExecutors),
                            ],
                            'columns'   => [
                                'ISPOLNITEL'        => $iExecutorId > 0 ?
                                                        $sExecutorName :
                                                        '<font color="red" title="Не найдено соответствие">' . $sExecutorName . ' (?)</font>'
                            ]
                        ];

                        $arData['editable'] = $arData['data'];

                        if (isset($this->arResult['CREATED'][ $arResol['ISN'] ])) {
                            $i = 1;
                            foreach ($this->arResult['CREATED'][ $arResol['ISN'] ] as $val) {
                                $arData['data']['FINDED'][] = '<a href="/control-orders/?detail=' . $val . '" target="_blank">Поручение ' . ($i++) . '</a>';
                            }
                        }

                        $arData['data']['FINDED'] = implode('<br/>', $arData['data']['FINDED']);

                        $this->arResult['ROWS'][] = $arData;
                    }

                    $this->arResult['LIST_ID'] = 'checkorders_import_step2';
                    $this->arResult['COLUMNS'] = $this->getTableColumns();
                    $this->arResult['ACTION_PANEL'] = $this->getActionPanel();
                    $this->arResult['PROTOCOL'] = $arProtocolData;
                    break;
                case 3:
                    try {
                        $arProtocolData = $this->getDataFromFile($_REQUEST['hash']);
                    } catch (Exception $e) {
                        $this->arResult['ERROR'] = $e->getMessage();
                        $iStep = 1;
                        break;
                    }

                    $this->arResult['LIST_ID'] = 'checkorders_import_step3';
                    $this->arResult['COLUMNS'] = $this->getTableColumns();
                    $this->arResult['PROTOCOL'] = $arProtocolData;

                    if (empty($_REQUEST['ID'])) {
                        $this->arResult['ERROR'] = 'Не выбрано поручений для создания';
                        break;
                    }

                    $arResols = [];
                    foreach ($arProtocolData['resols'] as $arRow) {
                        $arResols[ $arRow['ISN'] ] = $arRow;
                    }

                    $obElement = new CIBlockElement();

                    /*
                     * Куратор = Александр Бибиков.
                     * Контролер = Дмитрий Сафронов.
                     * Статус = На исполнении.
                     * Состояние = Черновик.
                     * Категория = Поручения Губернатора.
                     */
                    $arAllProps = [
                        'POST'      => 1112,
                        'CONTROLER' => 1151,
                        'STATUS'    => 1141,
                        'ACTION'    => 1134,
                        'CATEGORY'  => 1279,
                    ];
                    $arFiles = [];
                    if (!empty($arProtocolData['files'])) {
                        foreach ($arProtocolData['files'] as $file) {
                            $arFiles[] = [
                                'name' => $file['NAME'],
                                'MODULE_ID' => 'iblock',
                                'tmp_name' => $_SERVER['DOCUMENT_ROOT'] . $file['LOCAL']
                            ];
                        }
                    }

                    $arCreatedOrders = [];
                    $errors = [];
                    foreach ($_REQUEST['ID'] as $iIsn) {
                        if (!isset($arResols[ $iIsn ])) {
                            $errors[] = 'Поручение с ISN ' . $iIsn .' не найдено.';
                            continue;
                        }

                        $arResolution = $arResols[ $iIsn ];

                        $arProps = $arAllProps;
                        $arProps['DATE_ISPOLN'] = $arResolution['PLANDATE'];
                        $arProps['DATE_CREATE'] = $arProtocolData['DOC_DATE'];
                        $arProps['NUMBER'] = $arProtocolData['FREE_NUM'];

                        $sExecutorName = $arResolution['EXECUTORS'][0]['EXECUTOR']['PARENT']['NAME'];
                        $arExecutors = [];

                        foreach ($arResolution['EXECUTORS'] as $execRow) {
                            $strExecutor = '';
                            if ($execRow['MAIN']) {
                                $sExecutorName = $execRow['EXECUTOR']['PARENT']['NAME'];
                                $strExecutor .= '[Главный] ';
                            }
                            $strExecutor .= $execRow['EXECUTOR']['SURNAME'];
                            $strExecutor .= ' (' . $execRow['EXECUTOR']['PARENT']['NAME'] . ')';

                            $arExecutors[] = $strExecutor;
                        }

                        /*
                         * исполнитель есть в сессии, перетираем
                         */
                        if (isset($arCustomFields[ $arResolution['ISN'] ]['ISPOLNITEL'])) {
                            $sExecutorName = $this->arResult['EXECUTORS_NAME'][
                                $arCustomFields[ $arResolution['ISN'] ]['ISPOLNITEL']
                            ];
                        }

                        $iExecutorId = $this->arResult['EXECUTORS_ID'][ $sExecutorName ] ?? 0;

                        if ($iExecutorId <= 0 && false !== mb_strpos($sExecutorName, ' муниципального образования ')) {
                            $sNewVal = str_replace(' муниципального образования ', ' МО ', $sExecutorName);
                            $iExecutorId = $this->arResult['EXECUTORS_ID'][ $sNewVal ] ?? $iExecutorId;
                        }

                        if ($iExecutorId <= 0 && false !== mb_strpos($sExecutorName, ' муниципального образования город ')) {
                            $sNewVal = str_replace(' муниципального образования город ', ' МО г. ', $sExecutorName);
                            $iExecutorId = $this->arResult['EXECUTORS_ID'][ $sNewVal ] ?? $iExecutorId;
                        }

                        $arProps['ISPOLNITEL'] = $iExecutorId;

                        $arLoadFields = [
                            'PROP'          => $arProps,
                            'NAME'          => $arProtocolData['ANNOTAT'],
                            'DETAIL_TEXT'   => $arResolution['ANNOTAT'],
                            'XML_ID'        => $arResolution['ISN'],
                            'EXTERNAL_ID'   => $arResolution['ISN'],
                        ];

                        $arIspolnitel = explode('_', $arProps['ISPOLNITEL']);
                        $arCreated = [];
                        if ($arIspolnitel[0] == 'all') {
                            foreach ($this->arResult['ISPOLNITELS'] as $executor) {
                                if ($executor['PROPERTY_TYPE_ENUM_ID'] === $arIspolnitel[1]) {
                                    $arLoadFields['PROP']['ISPOLNITEL'] = $executor['ID'];
                                    if (!empty($arFiles)) {
                                        $arLoadFields['PROP']['DOCS'] = [];
                                        foreach ($arFiles as $arFile) {
                                            $iFileId = CFile::SaveFile($arFile, 'iblock');
                                            if ($iFileId > 0) {
                                                $arLoadFields['PROP']['DOCS'][] = $iFileId;
                                            }
                                        }
                                    }
                                    try {
                                        $arCreated[] = [
                                            'ID' => $obProtocol->setOrder(0, $arLoadFields),
                                            'EXECUTOR' => $executor['NAME']
                                        ];
                                    } catch (Exception $e) {
                                        foreach ($arLoadFields['PROP']['DOCS'] as $iFileId) {
                                            CFile::Delete($iFileId);
                                        }
                                        $errors[] = 'Поручение с ISN ' . $iIsn .' не создано: ' . $e->getMessage();
                                    }
                                }
                            }
                        } else {
                            if (!empty($arFiles)) {
                                $arLoadFields['PROP']['DOCS'] = [];
                                foreach ($arFiles as $arFile) {
                                    $iFileId = CFile::SaveFile($arFile, 'iblock');
                                    if ($iFileId > 0) {
                                        $arLoadFields['PROP']['DOCS'][] = $iFileId;
                                    }
                                }
                            }
                            try {
                                $iCreatedOrder = $obProtocol->setOrder(0, $arLoadFields);
                                if ($arResolution['SUMMARY'] != '') {
                                    $arLoadSummary = array(
                                        'MODIFIED_BY'       => $GLOBALS['USER']->GetID(),
                                        'IBLOCK_SECTION_ID' => false,
                                        'IBLOCK_ID'         => 510,
                                        'PROPERTY_VALUES'   => [
                                            'PORUCH'    => $iCreatedOrder,
                                            'USER'      => $arProps['CONTROLER'],
                                            'TYPE'      => 1132,
                                        ],
                                        'NAME'              => $arProps['CONTROLER'] . '-' . $iCreatedOrder . '-' . date('d-m-Y_h:i:s'),
                                        'ACTIVE'            => 'Y',
                                        'DETAIL_TEXT'       => $arResolution['SUMMARY'],
                                    );
                                    $obElement->Add($arLoadSummary);
                                }
                                $arCreated[] = [
                                    'ID'        => $iCreatedOrder,
                                    'EXECUTOR'  => $this->arResult['ISPOLNITELS'][ $arProps['ISPOLNITEL'] ]['NAME']
                                ];
                            } catch (Exception $e) {
                                foreach ($arLoadFields['PROP']['DOCS'] as $iFileId) {
                                    CFile::Delete($iFileId);
                                }
                                $errors[] = 'Поручение с ISN ' . $iIsn .' не создано: ' . $e->getMessage();
                            }
                        }
                        if (!empty($arCreated)) {
                            foreach ($arCreated as $arRow) {
                                $text = '<a href="/control-orders/?detail=' . $arRow['ID'] . '" target="_blank">' . $arResolution['ANNOTAT'] . '</a>';
                                $arData = [
                                    'data'      => [
                                        'ID'                => $arResolution['ISN'],
                                        'NAME'              => $arProtocolData['ANNOTAT'],
                                        'NUMBER'            => $arProtocolData['FREE_NUM'],
                                        'TEXT'              => $text,
                                        'DATE_ISPOLN'       => $arResolution['PLANDATE'],
                                        'DATE_CREATE'       => $arProtocolData['DOC_DATE'],
                                        'ISPOLNITEL'        => $arRow['EXECUTOR'],
                                        'ISPOLNITEL_DELO'   => implode('<br/>', $arExecutors),
                                    ],
                                ];
                                $arCreatedOrders[ $arRow['ID'] ] = $arRow['ID'];
                                $this->arResult['ROWS'][] = $arData;
                            }
                        }
                    }

                    if (!empty($arCreatedOrders)) {
                        foreach ($arCreatedOrders as $iOrderId) {
                            CIBlockElement::SetPropertyValuesEx(
                                $iOrderId,
                                $obProtocol->ordersIblockId,
                                [
                                    'PORUCH' => array_diff($arCreatedOrders, [$iOrderId])
                                ]
                            );
                        }
                    }

                    if (!empty($errors)) {
                        $this->arResult['ERROR'] = implode('<br/>', $errors);
                    }
                    break;
            }

            if ($iStep == 1) {
                $this->arResult['NUMBER'] = $_REQUEST['NUMBER'] ?? '';
                $this->arResult['DATE'] = $_REQUEST['DATE'] ?? date('d.m.Y');
            }

            $this->arResult['STEP'] = $iStep;

            $this->includeComponentTemplate();
        } catch (Exception $e) {
            ShowError($e->getMessage());
        }
    }

    /**
     * Получить данные протокола из сохранённого файла.
     *
     * @param string $hash Хэш для поиска файла.
     *
     * @return array
     *
     * @throws Exception Ошибка доступа или чтения файла.
     */
    public function getDataFromFile(string $hash = ''): array
    {
        $sUploadDir = '/upload/checkorders.import/';
        $sDocPath = $_SERVER['DOCUMENT_ROOT'] . $sUploadDir;
        $sFile = $sDocPath . $hash . '.json';
        if (!file_exists($sFile)) {
            throw new Exception('Ошибка доступа');
        }

        try {
            $sContent = file_get_contents($sFile);
            $arProtocolData = Json::decode($sContent)['RC'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        /*
         * Есть файлы в протоколе, сохранить локально для следущих шагов
         */
        if (!empty($arProtocolData['files'])) {
            foreach ($arProtocolData['files'] as $id => $file) {
                $sFileExtension = pathinfo($file['NAME'], PATHINFO_EXTENSION);
                $sNewFileName = $hash . '-' . $id . '.' . $sFileExtension;
                file_put_contents(
                    $sDocPath . $sNewFileName,
                    base64_decode($arProtocolData['file']['ANNOTAT'])
                );
                $arProtocolData['files'][ $id ]['LOCAL'] = $sUploadDir . $sNewFileName;
            }
        }

        return $arProtocolData;
    }

    /**
     * Кнопки действия для 2 шага импорта
     *
     * @return array
     */
    public function getActionPanel(): array
    {
        $onchangeAdd = new Onchange();
        $onchangeAdd->addAction(
            [
                'ACTION' => Actions::CALLBACK,
                'DATA'   => [
                    [
                        'JS' => 'step3(self.parent)'
                    ]
                ]
            ]
        );

        $removeButtons = [
            'ACTION' => Actions::REMOVE,
            'DATA' => [
                [
                    'ID' => DefaultValue::SAVE_BUTTON_ID
                ],
                [
                    'ID' => DefaultValue::CANCEL_BUTTON_ID
                ]
            ]
        ];

        $onChangeSave = new Onchange();
        $onChangeSave->addAction(
            [
                'ACTION' => Actions::CALLBACK,
                'DATA' => [
                    [
                        'JS' => 'Grid.editSelectedSave()'
                    ]
                ]
            ]
        );
        $onChangeSave->addAction($removeButtons);

        $saveButton = new Button();
        $saveButton->setClass(DefaultValue::SAVE_BUTTON_CLASS);
        $saveButton->setText('Сохранить');
        $saveButton->setId(DefaultValue::SAVE_BUTTON_ID);
        $saveButton->setOnchange($onChangeSave);

        $onChangeCancel = new Onchange();
        $onChangeCancel->addAction(
            [
                'ACTION' => Actions::CALLBACK,
                'DATA' => [
                    [
                        'JS' => 'Grid.editSelectedCancel()'
                    ]
                ]
            ]
        );
        $onChangeCancel->addAction($removeButtons);

        $cancelButton = new Button();
        $cancelButton->setClass(DefaultValue::CANCEL_BUTTON_CLASS);
        $cancelButton->setText('Отменить');
        $cancelButton->setId(DefaultValue::CANCEL_BUTTON_ID);
        $cancelButton->setOnchange($onChangeCancel);

        $onchangeEdit = new Onchange();
        $onchangeEdit->addAction(
            [
                'ACTION' => Actions::CALLBACK,
                'DATA'   => [
                    [
                        'JS' => 'Grid.editSelected()'
                    ]
                ]
            ]
        );
        $onchangeEdit->addAction(
            [
                'ACTION' => Actions::CREATE,
                'DATA'   => [
                    $saveButton->toArray(),
                    $cancelButton->toArray(),
                ]
            ]
        );
        return [
            'GROUPS' => [
                'TYPE' => [
                    'ITEMS' => [
                        [
                            'ID'       => 'add',
                            'TYPE'     => 'BUTTON',
                            'TEXT'     => 'Добавить поручения',
                            'CLASS'    => DefaultValue::SAVE_BUTTON_CLASS,
                            'ONCHANGE' => $onchangeAdd->toArray(),
                        ],
                        [
                            'ID'       => 'edit',
                            'TYPE'     => 'BUTTON',
                            'TEXT'     => 'Редактировать',
                            'CLASS'    => DefaultValue::EDIT_BUTTON_CLASS,
                            'ONCHANGE' => $onchangeEdit->toArray(),
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Колонки для грида с поручениями
     *
     * @return array
     */
    public function getTableColumns(): array
    {
        return [
            [
                'id' => 'ID',
                'name' => 'Номер ID',
                'sort' => 'ID',
                'default' => false
            ],
            [
                'id' => 'NAME',
                'name' => 'Название',
                'sort' => 'NAME',
                'default' => true,
            ],
            [
                'id' => 'TEXT',
                'name' => 'Содержание поручения',
                'sort' => 'TEXT',
                'default' => true,
            ],
            [
                'id' => 'FINDED',
                'name' => 'Найденные поручения',
                'sort' => false,
                'default' => true,
            ],
            [
                'id' => 'NUMBER',
                'name' => 'Номер',
                'sort' => 'NUMBER',
                'default' => true,
            ],
            [
                'id' => 'DATE_CREATE',
                'name' => 'Дата поручения',
                'sort' => 'DATE_CREATE',
                'default' => true,
            ],
            [
                'id' => 'DATE_ISPOLN',
                'name' => 'Дата исполнения',
                'sort' => 'DATE_ISPOLN',
                'default' => true,
            ],
            [
                'id' => 'ISPOLNITEL',
                'name' => 'Исполнитель',
                'sort' => 'ISPOLNITEL',
                'type' => 'list',
                'items' => $this->arResult['EXECUTORS_NAME'],
                'editable' => [
                    'TYPE' => 'DROPDOWN',
                    'items' => $this->arResult['EXECUTORS_NAME'],
                ],
                'default' => true,
            ],
            [
                'id' => 'ISPOLNITEL_DELO',
                'name' => 'Исполнители из Дело',
                'sort' => false,
                'default' => true,
            ],
        ];
    }

    /**
     * Список созданных поручений в системе
     *
     * @return array
     */
    public function getCreated(): array
    {
        $arResult = [];
        $res = CIBlockElement::GetList(
            false,
            [
                'ACTIVE'    => 'Y',
                'IBLOCK_ID' => Settings::$iblockId['ORDERS'],
            ],
            false,
            false,
            [
                'ID',
                'XML_ID',
                'PROPERTY_ISPOLNITEL',
            ]
        );
        while ($row = $res->GetNext()) {
            if ($row['XML_ID'] != $row['ID']) {
                $arResult[ $row['XML_ID'] ][ $row['PROPERTY_ISPOLNITEL_VALUE'] ] = $row['ID'];
            }
        }
        return $arResult;
    }
}
