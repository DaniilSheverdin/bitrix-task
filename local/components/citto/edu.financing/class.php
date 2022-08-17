<?php

namespace Citto\Edu\Financing;

use CPHPCache;
use Exception;
use CIBlockElement;
use CIBlockSection;
use CBitrixComponent;
use Bitrix\Main\Loader;
use CIBlockPropertyEnum;
use Sprint\Migration\Helpers\IblockHelper;
use Bitrix\Main\Page\Asset;




if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

require $_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php";


use Dompdf\Dompdf;

Loader::includeModule('iblock');
Loader::includeModule('sprint.migration');
Loader::includeModule('citto.filesigner');

Asset::getInstance()->addJs('/local/activities/custom/docsignactivity/js/es6-promise.min.js');
Asset::getInstance()->addJs('/local/activities/custom/docsignactivity/js/ie_eventlistner_polyfill.js');
Asset::getInstance()->addJs("/local/activities/custom/docsignactivity/js/cadesplugin_api.js");
Asset::getInstance()->addJs("/local/activities/custom/docsignactivity/js/plugin.js");

class Component extends CBitrixComponent
{

    public $arFields = [
        'ID',
        'NAME',
        'IBLOCK_ID',
        'DETAIL_PAGE_URL',
        'LIST_PAGE_URL',
        'PROPERTY_YEAR', // Год заявки
        'PROPERTY_NUMBER', // Номер заявки
        'PROPERTY_VERSION', // Версия версий
        'PROPERTY_STATUS', // Статус
        'PROPERTY_PROGRAM', // Программа
        'PROPERTY_EVENT', // Мероприятие
        'PROPERTY_MUNICIPALITY', // Муниципальное образование
        'PROPERTY_ORGAN', // Учреждение
        'PROPERTY_ADDRESS', // Адрес
        'PROPERTY_AMOUNT', // Сумма
        'PROPERTY_FILES', // Файлы
        'PROPERTY_FILES_DESC', // Файлы (описание)
        'PROPERTY_KURATOR', // Согласование куратора
        'PROPERTY_TEHNADZOR', // Согласование технадзора
        'PROPERTY_FINANCE', // Согласование финансирования
        'PROPERTY_DATE', // Дата отправки на согласование
    ];


    public $defaultFilter = [
        [
            'id' => 'DATE_CREATE',
            'name' => 'Дата создания',
            'type' => 'date',
            'default' => true,
            "exclude" => array(
                \Bitrix\Main\UI\Filter\DateType::LAST_7_DAYS,
                \Bitrix\Main\UI\Filter\DateType::LAST_30_DAYS,
                \Bitrix\Main\UI\Filter\DateType::LAST_60_DAYS,
                \Bitrix\Main\UI\Filter\DateType::LAST_90_DAYS,
                \Bitrix\Main\UI\Filter\DateType::CURRENT_MONTH,
                \Bitrix\Main\UI\Filter\DateType::CURRENT_QUARTER,
                \Bitrix\Main\UI\Filter\DateType::CURRENT_WEEK,
                \Bitrix\Main\UI\Filter\DateType::LAST_MONTH,
                \Bitrix\Main\UI\Filter\DateType::LAST_WEEK,
                \Bitrix\Main\UI\Filter\DateType::MONTH,
                \Bitrix\Main\UI\Filter\DateType::NEXT_MONTH,
                \Bitrix\Main\UI\Filter\DateType::NEXT_DAYS,
                \Bitrix\Main\UI\Filter\DateType::YEAR,
                \Bitrix\Main\UI\Filter\DateType::NEXT_WEEK,
                \Bitrix\Main\UI\Filter\DateType::TOMORROW,
                \Bitrix\Main\UI\Filter\DateType::QUARTER,
                \Bitrix\Main\UI\Filter\DateType::YESTERDAY,
                \Bitrix\Main\UI\Filter\DateType::CURRENT_DAY,
                \Bitrix\Main\UI\Filter\DateType::EXACT,
                \Bitrix\Main\UI\Filter\DateType::PREV_DAYS,
            )
        ],
        [
            'id' => 'ATT_PROPERTY_STATUS',
            'name' => 'Статус',
            'type' => 'list',
            'items' => [
                //'DRAFT' => 'Черновик',
                'NEW' => 'Новое',
                'SUCCESS' => 'Рассмотрено',
                'REJECT' => 'Отклонено',
                'AGREED' => 'Согласовано',
                'PROCESS' => 'На рассмотрении',
            ],
            'params' => ['multiple' => 'Y']],
    ];

    /** @var array */
    public $arTranslitParams           =
        [
            "max_len" => "100",
            "change_case" => "L",
            "replace_space" => "_",
            "replace_other" => "_",
            "delete_repeat_replace" => "true",
            "use_google" => "false",
        ];

    public $arPopupOptions = [
        'allowChangeHistory'    => true,
        'cacheable'             => false,
        'requestMethod'         => 'post'
    ];

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
            $iblockId = $this->getIblockId();
        }
        $arEnums = [];
        $obCache = new CPHPCache();
        if ($obCache->InitCache(86400, md5(__METHOD__ . $iblockId), '/citto/edu/financing/')) {
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
                $arEnums[ $row['PROPERTY_CODE'] ][ $row['VALUE'] ] = $row;
            }
            $obCache->EndDataCache($arEnums);
        }
        return $arEnums;
    }





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

        $helper = new IblockHelper();

        $arParams['IBLOCK_ID'] = $this->getIblockId();
        $arParams['IBLOCK_ID_STRUCTURE'] = $this->getIblockId('structure');
        $arParams['IBLOCK_ID_PROGRAMS'] = $this->getIblockId('programs');

        $this->arResult['MUNICIPALITY'] = $this->getSettingsSections($arParams['IBLOCK_ID_STRUCTURE']);
        $this->arResult['ORGAN'] = $this->getSettingsElements($arParams['IBLOCK_ID_STRUCTURE']);

        $this->arResult['PROGRAM'] = $this->getSettingsSections($arParams['IBLOCK_ID_PROGRAMS']);
        $this->arResult['EVENT'] = $this->getSettingsElements($arParams['IBLOCK_ID_PROGRAMS']);

        $this->arResult['TREE'] = [
            'MUNICIPALITY'  => $this->getTreeSections($this->arResult['MUNICIPALITY']),
            'ORGAN'         => $this->getTreeElements($this->arResult['ORGAN']),
            'PROGRAM'       => $this->getTreeSections($this->arResult['PROGRAM']),
            'EVENT'         => $this->getTreeElements($this->arResult['EVENT']),
        ];

        $this->arResult['FILE_TYPES'] = [
            'contract'  => 'Договор',
            'bill'      => 'Счёт',
            'check'     => 'Кассовый, товарный чек или бланк строгой отчётности',
            'waybill'   => 'Накладная',
            'act'       => 'Акт оказания услуг или выполненных работ',
            'invoice'   => 'Счёт-фактура',
            'upd'       => 'УПД',
        ];

        $this->arResult['ENUMS'] = $this->getEnums($arParams['IBLOCK_ID']);

        $this->arResult['ROLES'] = $this->getRoles($GLOBALS['USER']->GetID());

//        pre(['$arParams' => $arParams, 'ENUMS' => $this->arResult['ENUMS']]);

        return $arParams;
    }

    public function getIblockId(string $type = 'main') {
        $helper = new IblockHelper();
        $find = 'edufinancing';
        if ($type == 'main') {
            $find = 'edufinancing';
        } else {
            $find = 'edu' . mb_strtolower($type);
        }
        return (int)$helper->getIblockId($find, 'minobr');
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

    public function getDefaultFilter(): array
    {
        return [
            'ACTIVE' => 'Y',
            'IBLOCK_ID' => $this->getIblockId(),
        ];
    }

    public function getRoles(int $userId = 0): array
    {
        global $USER;
        if ($userId <= 0) {
            $userId = $USER->GetID();
        }

        $arResult = [
            'ADMIN'         => \CSite::InGroup([GROUP_ID_EDU_ADMIN]),
            'OPERATOR'      => \CSite::InGroup([GROUP_ID_EDU_OPERATOR]),
            'KURATOR'       => \CSite::InGroup([GROUP_ID_EDU_KURATOR]),
            'TEHNADZOR'     => \CSite::InGroup([GROUP_ID_EDU_TEHNADZOR]),
            'FINANCE'       => \CSite::InGroup([GROUP_ID_EDU_FINANCE]),
        ];

        return $arResult;
    }

    public function generateNumber(int $documentId = 0): array
    {
        $arOrder = [];
        if ($documentId > 0) {
            $arOrder = $this->getById($documentId, true);
        } else {
            $arFilter = $this->getDefaultFilter();
            $arFilter['PROPERTY_YEAR'] = date('Y');
            $res = CIBlockElement::GetList(
                [
                    'PROPERTY_NUMBER' => 'DESC'
                ],
                $arFilter,
                false,
                ['nTopCount' => 1],
                ['ID']
            );
            while ($arFields = $res->GetNext()) {
                $arOrder = $this->getById($arFields['ID'], true);
            }
        }

        if (!empty($arOrder)) {
            $year = $arOrder['PROPERTY_YEAR_VALUE'];
            $number = $arOrder['PROPERTY_NUMBER_VALUE'];
            $version = $arOrder['PROPERTY_VERSION_VALUE'];
            if ($documentId <= 0) {
                $number++;
                $version = 1;
            } else {
                $version++;
            }
        } else {
            $year = date('Y');
            $number = 1;
            $version = 1;
        }

        return [
            'YEAR'      => $year,
            'NUMBER'    => $number,
            'VERSION'   => $version,
        ];
        // return sprintf('%04d', $year) . '-' . sprintf('%04d', $number) . '/' . sprintf('%03d', $version);
    }

    public function getList(array $arFilter = []): array
    {



        $strName = '';

        $arFilter = array_merge(
            $this->getDefaultFilter(),
            $arFilter
        );

        $arResult = [];
        $res = CIBlockElement::GetList(
            [
                'DATE_CREATE' => 'ASC'
            ],
            $arFilter,
            false,
            false,
            $this->arFields
        );

        $strRole = null;


        foreach ($this->arResult['ROLES'] as $strRoleKey => $value) {
            if ($value) {
                $strRole = $strRoleKey;
            }
        }

        $arFilterPrevious = ['IBLOCK_ID' => $this->getIblockId(), 'ACTIVE' => 'N', 'NAME' => $strName];




        while ($arFields = $res->GetNext()) {

            $bIsCanDecide = true;
            $bIsBothDecide = false;

            $arFilterPrevious['NAME'] = $arFields['NAME'];
            $rsPrevious = CIBlockElement::GetList(['sort' => 'asc'], $arFilterPrevious, false, false, $this->arFields);


            $bKuratorDecide = json_decode(htmlspecialcharsback($arFields['PROPERTY_KURATOR_VALUE']), true)['STATUS'] != $this->arResult['ENUMS']['STATUS']['PROCESS']['VALUE'];
            $bTechDecide = json_decode(htmlspecialcharsback($arFields['PROPERTY_TEHNADZOR_VALUE']), true)['STATUS'] != $this->arResult['ENUMS']['STATUS']['PROCESS']['VALUE'];

            $bIsBothDecide = $bKuratorDecide && $bTechDecide;



            $arFields['EDIT_PAGE_URL'] = str_replace('detail=', 'edit=', $arFields['DETAIL_PAGE_URL']);
            $arFields['~EDIT_PAGE_URL'] = str_replace('detail=', 'edit=', $arFields['~DETAIL_PAGE_URL']);
            $arFields['PROPERTY_FILES_DESC_VALUE'] = json_decode($arFields['~PROPERTY_FILES_DESC_VALUE']['TEXT'], true);
            $arFields['~PROPERTY_FILES_DESC_VALUE'] = $arFields['PROPERTY_FILES_DESC_VALUE'];

            if ($strRole) {
                $arDecision = json_decode(htmlspecialcharsback($arFields['PROPERTY_'.$strRole.'_VALUE']), true);
                if ($arDecision['STATUS'] != $this->arResult['ENUMS']['STATUS']['PROCESS']['VALUE']) {
                    $bIsCanDecide = false;
                }

            }

            $arCan = [
                'view'              => true,
                'edit'              => false,
                'send_to_SUCCESS'   => false,
                'send_to_REJECT'    => false,
            ];

            switch ((int)$arFields['PROPERTY_STATUS_ENUM_ID']) {
                case $this->arResult['ENUMS']['STATUS']['DRAFT']['ID']:
                case $this->arResult['ENUMS']['STATUS']['REJECT']['ID']:
                    $arCan['edit'] =    $this->arResult['ROLES']['ADMIN'] ||
                                        $this->arResult['ROLES']['OPERATOR'];
                    break;
                case $this->arResult['ENUMS']['STATUS']['NEW']['ID']:
                case $this->arResult['ENUMS']['STATUS']['PROCESS']['ID']:
                    $arCan['send_to_SUCCESS'] = $this->arResult['ROLES']['ADMIN'] ||
                                                $this->arResult['ROLES']['TEHNADZOR'] ||
                                                $this->arResult['ROLES']['FINANCE'] ||
                                                $this->arResult['ROLES']['KURATOR'];

                    $arCan['send_to_REJECT'] =  $this->arResult['ROLES']['ADMIN'] ||
                                                $this->arResult['ROLES']['TEHNADZOR'] ||
                                                $this->arResult['ROLES']['FINANCE'] ||
                                                $this->arResult['ROLES']['KURATOR'];

                    $arCan['send_to_REPEAT'] =  $this->arResult['ROLES']['FINANCE'];

                    break;

            }

            $arCan['send_to_SUCCESS'] = $arCan['send_to_SUCCESS'] && $bIsCanDecide;
            $arCan['send_to_REJECT'] = $arCan['send_to_REJECT'] && $bIsCanDecide;
            $arCan['send_to_REPEAT'] = $arCan['send_to_REPEAT'] && $bIsBothDecide;

            if ($arFields['PROPERTY_STATUS_ENUM_ID'] == $this->arResult['ENUMS']['STATUS']['NEW']['ID'] && $this->getRoles()['OPERATOR']) {
                $arCan['edit'] = false; // @crunch
            }



            $arActions = [];
            if ($arCan['view']) {
                $arActions[] = [
                    'text'      => 'Просмотр',
                    'default'   => false,
                    'href'      => $arFields['~DETAIL_PAGE_URL'],
                ];
            }
            if ($arCan['edit']) {
                $arActions[] = [
                    'text'      => 'Редактировать',
                    'default'   => false,
                    'href'      => $arFields['~EDIT_PAGE_URL'],
                ];
            }

            $number = sprintf('%04d', $arFields['PROPERTY_YEAR_VALUE']) . '-' .
                        sprintf('%04d', $arFields['PROPERTY_NUMBER_VALUE']) . '/' .
                        sprintf('%03d', $arFields['PROPERTY_VERSION_VALUE']);

            $arFields['NUMBER'] = $number;

            $arResult[ $arFields['EXTERNAL_ID'] ] = [
                'data'      => [
                    'NUMBER'        => '<a href="' . $arFields['~DETAIL_PAGE_URL'] . '" onclick="return false;">' . $number . '</a>',
                    'STATUS'        => $arFields['~PROPERTY_STATUS_VALUE'],
                    'PROGRAM'       => $this->arResult['PROGRAM'][ $arFields['~PROPERTY_PROGRAM_VALUE'] ]['NAME'] ?? 'Не заполнено',
                    'EVENT'         => $this->arResult['EVENT'][ $arFields['~PROPERTY_EVENT_VALUE'] ]['NAME'] ?? 'Не заполнено',
                    'MUNICIPALITY'  => $this->arResult['MUNICIPALITY'][ $arFields['~PROPERTY_MUNICIPALITY_VALUE'] ]['NAME'] ?? 'Не заполнено',
                    'ORGAN'         => $this->arResult['ORGAN'][ $arFields['~PROPERTY_ORGAN_VALUE'] ]['NAME'] ?? 'Не заполнено',
                    'ADDRESS'       => $arFields['~PROPERTY_ADDRESS_VALUE'],
                    'AMOUNT'        => $arFields['~PROPERTY_AMOUNT_VALUE'],
                ],
                'actions'   => $arActions,
                'raw'       => $arFields,
                'can'       => $arCan,
            ];

            while($arFieldsPrevious = $rsPrevious->GetNext()) {

                $arResult[$arFields['EXTERNAL_ID']]['previous'][$arFieldsPrevious['EXTERNAL_ID']] = [
                    'KURATOR'       => json_decode(htmlspecialcharsback($arFieldsPrevious['PROPERTY_KURATOR_VALUE']), true),
                    'TEHNADZOR'     => json_decode(htmlspecialcharsback($arFieldsPrevious['PROPERTY_TEHNADZOR_VALUE']), true),
                    'FINANCE'       => json_decode(htmlspecialcharsback($arFieldsPrevious['PROPERTY_FINANCE_VALUE']), true),
                    'NUMBER'        => $arFieldsPrevious['PROPERTY_NUMBER_VALUE'],
                    'VERSION'       => $arFieldsPrevious['PROPERTY_VERSION_VALUE'],
                    'DATE'          => $arFieldsPrevious['PROPERTY_DATE_VALUE'],
                ];



            }
        }




        return $arResult;
    }


//    public function getListDataGrid(array $arFilter = [])
//    {
//
//
//        $arStatusEnums = [];
//
//        $propStatusEnums = CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array("IBLOCK_ID" => $this->getIblockId(), 'CODE' => 'STATUS'));
//
//        while($arFieldsEnum = $propStatusEnums->GetNext()) {
//            $arStatusEnums[$arFieldsEnum['ID']] = $arFieldsEnum['VALUE'];
//            asort($arStatusEnums);
//        }
//
//        pre($arStatusEnums);
//
//
//    }





    public function getById(int $id = 0, $bRaw = false): array
    {
        $arResult = [];
        $arFilter = [
            'ID' => $id,
        ];
        $arList = $this->getList($arFilter);
        if (isset($arList[ $id ])) {
            $arResult = $arList[ $id ];

            if ($bRaw) {
                $arResult = $arResult['raw'];
            }
        }

        return $arResult;
    }

    public function getSettingsSections(int $iblockId = 0): array
    {
        $arFilter = [
            'ACTIVE'    => 'Y',
            'IBLOCK_ID' => $iblockId,
        ];
        $arResult = [];
        $res = CIBlockSection::GetList(['NAME' => 'asc', 'SORT' => 'asc'], $arFilter);
        while ($row = $res->GetNext()) {
            $arResult[ $row['ID'] ] = $row;
        }

        return $arResult;
    }

    public function getSettingsElements(int $iblockId = 0): array
    {
        $arFilter = [
            'ACTIVE'    => 'Y',
            'IBLOCK_ID' => $iblockId,
        ];
        $arResult = [];
        $res = CIBlockElement::GetList(
            ['NAME' => 'asc', 'SORT' => 'asc'],
            $arFilter,
            false,
            false
        );
        while ($row = $res->GetNext()) {
            $arResult[ $row['ID'] ] = $row;
        }

        return $arResult;
    }

    public function getTreeElements(array $arData = []): array
    {
        $arResult = [];
        foreach ($arData as $row) {
            $arResult[ (int)$row['IBLOCK_SECTION_ID'] ][ $row['ID'] ] = [
                'id'        => $row['ID'],
                'name'      => $row['NAME'],
                'parent'    => $row['IBLOCK_SECTION_ID'],
            ];
        }

        return $arResult;
    }

    public function getTreeSections(array $arData = [], int $parent = 0): array
    {
        $arResult = [];
        foreach ($arData as $row) {
            if ((int)$row['IBLOCK_SECTION_ID'] == $parent) {
                $arResult[] = [
                    'id'        => $row['ID'],
                    'name'      => $row['NAME'],
                    'parent'    => $row['IBLOCK_SECTION_ID'],
                    'child'     => $this->getTreeSections($arData, $row['ID']),
                ];
            }
        }

        return $arResult;
    }

    public function update($arData = [])
    {
        $bAdd = !(isset($arData['ID']) && $arData['ID'] > 0);

        $arFields = $this->getDefaultFilter();

        $el = new CIBlockElement();
        $elID = 0;
        $propsFields = [
            'PROGRAM'       => $arData['PROGRAM'],
            'EVENT'         => $arData['EVENT'],
            'MUNICIPALITY'  => $arData['MUNICIPALITY'],
            'ORGAN'         => $arData['ORGAN'],
            'ADDRESS'       => $arData['ADDRESS'],
            'AMOUNT'        => $arData['AMOUNT'],
            'FILES'         => $arData['FILES'] ?? [],
            'FILES_DESC'    => [],
        ];


        foreach ($arData['FILES_DESC']['type'] as $key => $value) {
            $propsFields['FILES_DESC'][] = [
                'type'      => $value,
                'number'    => $arData['FILES_DESC']['number'][ $key ],
                'date'      => $arData['FILES_DESC']['date'][ $key ],
                'amount'    => $arData['FILES_DESC']['amount'][ $key ],
            ];
        }
        $propsFields['FILES_DESC'] = json_encode($propsFields['FILES_DESC'], JSON_UNESCAPED_UNICODE);

        if ($bAdd) {
            $arFields['NAME'] = $GLOBALS['USER']->GetID() . '-' . date('d.m.Y H:i:s');
            $arNumber = $this->generateNumber(0);
            $propsFields['YEAR'] = $arNumber['YEAR'];
            $propsFields['NUMBER'] = $arNumber['NUMBER'];
            $propsFields['VERSION'] = $arNumber['VERSION'];
            $propsFields['STATUS'] = $this->arResult['ENUMS']['STATUS']['DRAFT']['ID'];
            $arFields['PROPERTY_VALUES'] = $propsFields;
            if (!$elID = $el->Add($arFields)) {
                throw new Exception($el->LAST_ERROR);
            }
        } else {
            if ($el->Update($arData['ID'], $arFields)) {
                CIBlockElement::SetPropertyValuesEx($arData['ID'], $arFields['IBLOCK_ID'], $propsFields);
            }
        }

        if ($el->LAST_ERROR) {
            throw new Exception($el->LAST_ERROR);
        }

        $arElement = $this->getById($elID, true);
        LocalRedirect($arElement['~DETAIL_PAGE_URL']);
    }

    /**
     * Запуск компонента
     *
     * @return void
     */
    public function executeComponent()
    {
        try {
            if (isset($_REQUEST['do']) && method_exists($this, $_REQUEST['do'])) {
                try {

                    call_user_func_array([$this, $_REQUEST['do']], [$_REQUEST]);
                } catch (Exception $e) {
                    ShowError($e->GetMessage());
                }
            }
        	$this->includeComponentTemplate();
        } catch (Exception $e) {
            ShowError($e->getMessage());
        }
    }
}
