<?php

namespace Citto\Indicators;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
use CUser;
use CTasks;
use PHPExcel;
use CPHPCache;
use Exception;
use CSocNetGroup;
use CIBlockElement;
use CIBlockSection;
use CBitrixComponent;
use PHPExcel_IOFactory;
use Bitrix\Main\Loader;
use CIBlockPropertyEnum;
use PHPExcel_Style_Fill;
use PHPExcel_Style_Border;
use Bitrix\Main\Application;
use PHPExcel_Style_Alignment;
use PHPExcel_Worksheet_PageSetup;

Loader::includeModule('tasks');
Loader::includeModule('iblock');
Loader::includeModule('socialnetwork');
Loader::includeModule('nkhost.phpexcel');

global $APPLICATION;
$APPLICATION->SetAdditionalCSS('/bitrix/templates/.default/bootstrap.min.css');

class MainComponent extends CBitrixComponent
{
    public function onPrepareComponentParams($arParams)
    {
        $arParams['CACHE_TIME'] = (int) $arParams['CACHE_TIME'];

        return $arParams;
    }

    public function getUserFullName($id = 0)
    {
        if ($id <= 0) {
            return '';
        }
        $userName = '';
        $obCache = new CPHPCache();
        if ($obCache->InitCache(86400, __METHOD__ . $id, '/citto/indicators/')) {
            $userName = $obCache->GetVars();
        } elseif ($obCache->StartDataCache()) {
            $rsUser = CUser::GetByID($id);
            $arUser = $rsUser->Fetch();
            $userName = $arUser['NAME']||$arUser['LAST_NAME'] ? trim($arUser['LAST_NAME'] . ' ' . $arUser['NAME'] . ' ' . $arUser['SECOND_NAME']) : $arUser['LOGIN'];
            $userName = trim(str_replace('  ', ' ', $userName));
            $obCache->EndDataCache($userName);
        }

        return $userName;
    }

    public function UserGetFullName($sUserId)
    {
        $rsUser = CUser::GetByID($sUserId);
        $arUser = $rsUser->Fetch();
        return $arUser['LAST_NAME'] . ' ' . $arUser['NAME'].' '.$arUser['SECOND_NAME'];
    }

    public function calcPassport(
        $groupId = 0,
        $title = '',
        $taskId = 0
    ) {
        $parentId = false;
        if ($taskId > 0) {
            $parentId = $taskId;
        } else {
            $arFilter = [
                'GROUP_ID'  => $groupId,
                'PARENT_ID' => false,
            ];
            $resTask = CTasks::GetList(
                ['DEADLINE' => 'ASC'],
                $arFilter,
                [
                    'ID',
                    'TITLE',
                ]
            );
            $arAllTasks = [];
            while ($arTask = $resTask->Fetch()) {
                $arAllTasks[ mb_substr($arTask['TITLE'], 0, 200) ] = $arTask['ID'];
            }
            if (isset($arAllTasks[ mb_substr($title, 0, 200) ])) {
                $parentId = $arAllTasks[ mb_substr($title, 0, 200) ];
            }
        }
        $arFilter = [
            'GROUP_ID'  => $groupId,
            'PARENT_ID' => $parentId,
        ];
        $resTask = CTasks::GetList(
            ['DEADLINE' => 'ASC'],
            $arFilter,
            [
                'ID',
                'TITLE',
                'PARENT_ID',
                'GROUP_ID',
                'STATUS',
                'REAL_STATUS',
                'CREATED_DATE',
                'CLOSED_DATE',
                'DEADLINE',
                'RESPONSIBLE_ID',
                'TAGS',
            ]
        );
        $arTasks = [];
        $arClosedTasks = [];
        $arDeadLines = [];
        $arOpenDeadLines = [];
        while ($arTask = $resTask->Fetch()) {
            $arTasks[ $arTask['ID'] ] = $arTask;
            $arDeadLines[ $arTask['ID'] ] = strtotime($arTask['DEADLINE']);
            if (!empty($arTask['DEADLINE'])) {
                if ($arTask['STATUS'] >= 4) {
                    $arClosedTasks[ $arTask['ID'] ] = $arTask;
                } else {
                    $arOpenDeadLines[ $arTask['ID'] ] = strtotime($arTask['DEADLINE']);
                }
            }
        }

        if (empty($arClosedTasks) && empty($deadLine)) {
            $deadLine = min($arDeadLines);
        } else {
            $deadLine = min($arOpenDeadLines);
        }

        $percent = 0;
        if (!empty($arClosedTasks) && !empty($arTasks)) {
            $percent = (count($arClosedTasks)*100) / count($arTasks);
        }

        return [
            'PERCENT'       => $percent ?? 0,
            'PERCENT_FMT'   => $percent ? number_format($percent, 2, ',', '') : 0,
            'DEADLINE'      => $deadLine ? date('d.m.Y', $deadLine) : '',
        ];
    }

    public function apiGetList($arRequst = [])
    {
        $arResult = [];
        $arIndicatorTypes = [];
        $resEnums = CIBlockPropertyEnum::GetList(
            ['DEF' => 'DESC', 'SORT' => 'ASC'],
            ['IBLOCK_ID' => IBLOCK_ID_INDICATORS_CATALOG, 'CODE' => 'TYPE']
        );
        while ($arEnum = $resEnums->GetNext()) {
            $arIndicatorTypes[ $arEnum['ID'] ] = $arEnum['XML_ID'];
        }
        $arSelect = [
            'ID',
            'XML_ID',
            'IBLOCK_ID',
            'NAME',
            'PREVIEW_TEXT',
            'IBLOCK_SECTION_ID',
            'DATE_ACTIVE_FROM',
            'PROPERTY_THEME',
            'PROPERTY_STRUCTURE',
            'PROPERTY_TARGET_VALUE',
            'PROPERTY_MONTHLY_TARGET_VALUE',
            'PROPERTY_TARGET_VALUE_MIN',
            'PROPERTY_TYPE',
            'PROPERTY_THEME_STAT',
            'PROPERTY_AFFILIATION',
            'PROPERTY_INVERTED',
            'PROPERTY_PASSPOPT_LNPA',
            'PROPERTY_PASSPOPT_GROUP',
            'PROPERTY_PASSPOPT_TASKID',
            'PROPERTY_PASSPOPT_USER',
            'PROPERTY_SHORT_NAME',
        ];
        $arFilter = [
            'IBLOCK_ID'             => IBLOCK_ID_INDICATORS_CATALOG,
            'ACTIVE_DATE'           => 'Y',
            'ACTIVE'                => 'Y',
            'INCLUDE_SUBSECTION'    => 'Y',
        ];

        if (isset($arRequst['department'])) {
            $arFilter['PROPERTY_STRUCTURE'] = $arRequst['department'];
        }
        if (isset($arRequst['program'])) {
            $arFilter['IBLOCK_SECTION_ID'] = $arRequst['program'];
        }
        $res = CIBlockElement::GetList(['SORT' => 'ASC'], $arFilter, false, false, $arSelect);
        $arGroupData = [];
        $connection = Application::getConnection('base_for_bi');
        $arDepartments = [];
        while ($arFields = $res->GetNext()) {
            if (
                $arFields['PROPERTY_STRUCTURE_VALUE'] > 0 &&
                !isset($arDepartments[ $arFields['PROPERTY_STRUCTURE_VALUE'] ])
            ) {
                $arDepList = CIBlockSection::GetNavChain(false, $arFields['PROPERTY_STRUCTURE_VALUE'], ['ID'], true);
                $arDepartments[ $arFields['PROPERTY_STRUCTURE_VALUE'] ] = array_map(function($e){return (int)$e['ID'];}, $arDepList);
            }

            if (
                isset($arRequst['departments']) &&
                !in_array($arRequst['departments'], $arDepartments[ $arFields['PROPERTY_STRUCTURE_VALUE'] ])
            ) {
                continue;
            }

            $arData = [
                'id'            => (int)$arFields['ID'],
                'program'       => (int)$arFields['IBLOCK_SECTION_ID'],
                'type'          => $arIndicatorTypes[ $arFields['PROPERTY_TYPE_ENUM_ID'] ],
                'name'          => $arFields['NAME'],
                'short_name'    => $arFields['PROPERTY_SHORT_NAME_VALUE'] ?? $arFields['NAME'],
                'description'   => $arFields['PREVIEW_TEXT'],
                'department'    => (int)$arFields['PROPERTY_STRUCTURE_VALUE'],
                'theme'         => (int)$arFields['PROPERTY_THEME_VALUE'],
                'sort'          => (int)$arFields['SORT'],
                'xml_id'        => $arFields['XML_ID'],
                'bi_id'         => 0,
                'plan'          => $arFields['PROPERTY_TARGET_VALUE_VALUE']??'',
                'min_plan'      => $arFields['PROPERTY_TARGET_VALUE_MIN_VALUE']??'',
                'month_plan'    => $arFields['PROPERTY_MONTHLY_TARGET_VALUE_VALUE']??'',
                'fact'          => '',
                'percent'       => '',
                'date'          => '',
                'author'        => '',
                'comment'       => '',
                'npa'           => '',
                'milestone'     => '',
                'project'       => [],
                'responsible'   => [],
                'statuses'      => [],
                'filters'       => [
                    'program'       => (int)$arFields['IBLOCK_SECTION_ID'],
                    'department'    => (int)$arFields['PROPERTY_STRUCTURE_VALUE'],
                    'departments'   => $arDepartments[ $arFields['PROPERTY_STRUCTURE_VALUE'] ] ?? [],
                    'theme'         => (int)$arFields['PROPERTY_THEME_VALUE'],
                    'theme_stat'    => (int)$arFields['PROPERTY_THEME_STAT_ENUM_ID'],
                    'type'          => $arIndicatorTypes[ $arFields['PROPERTY_TYPE_ENUM_ID'] ],
                    'affiliation'   => (int)$arFields['PROPERTY_AFFILIATION_ENUM_ID'],
                ],
            ];

            if (!empty($arFields['PROPERTY_PASSPOPT_USER_VALUE'])) {
                $arData['responsible'] = [
                    'id'    => (int)$arFields['PROPERTY_PASSPOPT_USER_VALUE'],
                    'name'  => $this->getUserFullName($arFields['PROPERTY_PASSPOPT_USER_VALUE']),
                ];
            }

            // if ($arData['type'] !== 'passport')
            {
                $data = $connection->query("SELECT MAX(`id`) AS `id`, `bi_id` FROM `bi` WHERE `bi_id` IN (" . $arFields['XML_ID'] . ") GROUP BY `bi_id`");
                while ($arDataID = $data->fetch()) {
                    $resBiData = $connection->query("SELECT * FROM `bi` WHERE `id` = '" . $arDataID['id'] . "'");
                    if ($arBiData = $resBiData->fetch()) {
                        $arData['bi_id']    = (int)$arBiData['id'];
                        $arData['fact']     = $arBiData['state_value'];
                        $arData['percent']  = $arBiData['percent_exec'];
                        $arData['date']     = $arBiData['date']->format('d.m.Y');
                        $arData['author']   = $arBiData['fio'];
                        $arData['comment']  = $arBiData['comment'];
                    }
                }
            }

            if (!empty($arFields['PROPERTY_PASSPOPT_LNPA_VALUE'])) {
                $arData['npa'] = (int)$arFields['PROPERTY_PASSPOPT_LNPA_VALUE'];
            }

            if (!empty($arFields['PROPERTY_PASSPOPT_GROUP_VALUE'])) {
                $arData['type'] = 'passport';
                $arData['filters']['type'] = 'passport';
                $arPassport = $this->calcPassport(
                    $arFields['PROPERTY_PASSPOPT_GROUP_VALUE'],
                    $arFields['NAME'],
                    $arFields['PROPERTY_PASSPOPT_TASKID_VALUE']??0
                );
                $arData['percent'] = $arPassport['PERCENT_FMT'] ?? 0;
                $arData['milestone'] = $arPassport['DEADLINE'] ?? '';
                if (!empty($arData['milestone'])) {
                    if (strtotime($arData['milestone'] . ' 00:00:00') < time()) {
                        $arData['statuses'][] = 'expired';
                    }
                }

                if (!isset($arGroupData[ $arFields['PROPERTY_PASSPOPT_GROUP_VALUE'] ])) {
                    $arGroupData[ $arFields['PROPERTY_PASSPOPT_GROUP_VALUE'] ] = CSocNetGroup::GetByID($arFields['PROPERTY_PASSPOPT_GROUP_VALUE']);
                }
                $arData['project'] = [
                    'id'    => (int)$arFields['PROPERTY_PASSPOPT_GROUP_VALUE'],
                    'name'  => $arGroupData[ $arFields['PROPERTY_PASSPOPT_GROUP_VALUE'] ]['NAME'],
                ];
            }

            if (
                $arData['type'] == 'main' &&
                (int)$arData['percent'] < 30
            ) {
                $arData['statuses'][] = 'expired';
            }
            
            if ($arData['type'] == 'stat' && $arData['fact'] == '') {
                $arData['fact'] = $arData['plan'];
            }

            $arResult[ $arFields['ID'] ] = $arData;
        }

        return $arResult;
    }

    public function apiGetPrograms()
    {
        $arResult = [];
        $arFilter = [
            'IBLOCK_ID'     => IBLOCK_ID_INDICATORS_CATALOG,
            'SECTION_ID'    => false,
        ];
        $arSelect = [
            'ID',
            'NAME',
        ];
        $rsSections = CIBlockSection::GetList([], $arFilter, false, $arSelect);
        while ($arSection = $rsSections->Fetch()) {
            $arResult[ (int)$arSection['ID'] ] = [
                'id'        => (int)$arSection['ID'],
                'name'      => trim($arSection['NAME']),
                'parent'    => 0,
            ];
            $arFilterSub = [
                'IBLOCK_ID'     => IBLOCK_ID_INDICATORS_CATALOG,
                'ACTIVE'        => 'Y',
                'SECTION_ID'    => $arSection['ID'],
            ];
            $rsSectionsSub = CIBlockSection::GetList([], $arFilterSub, false, $arSelect);
            while ($arSectionSub = $rsSectionsSub->Fetch()) {
                $arResult[ (int)$arSectionSub['ID'] ] = [
                    'id'        => (int)$arSectionSub['ID'],
                    'name'      => trim($arSectionSub['NAME']),
                    'parent'    => (int)$arSection['ID'],
                ];
            }
        }

        return $arResult;
    }

    public function apiGetDepartments(int $parentId = 0, int $setParent = 0)
    {
        CBitrixComponent::includeComponentClass('citto:indicators.edit');
        $obComponent = new EditConponent();

        $arIndicatorsDepartment = $obComponent->getIndicatorDepartments();

        if ($parentId <= 0) {
            $parentId = SECTION_ID_CITTO_STRUCTURE;
        }

        $arResult = [];
        $arFilter = [
            'IBLOCK_ID'     => IBLOCK_ID_STRUCTURE,
            'SECTION_ID'    => $parentId
        ];
        $arSelect = [
            'ID',
            'NAME',
        ];
        $rsSections = CIBlockSection::GetList([], $arFilter, false, $arSelect);
        while ($arSection = $rsSections->Fetch()) {
            $arResult[ (int)$arSection['ID'] ] = [
                'id'        => (int)$arSection['ID'],
                'name'      => trim($arSection['NAME']),
                'parent'    => $setParent,
            ];
            if ($setParent <= 0) {
                $setParentNew = (int)$arSection['ID'];
            } else {
                $setParentNew = $setParent;
            }

            $arSubSections = $this->apiGetDepartments($arSection['ID'], $setParentNew);
            foreach ($arSubSections as $id => $arSectionSub) {
                $arResult[ $id ] = $arSectionSub;
            }
        }

        if ($parentId == SECTION_ID_CITTO_STRUCTURE) {
            foreach ($arResult as $key => $value) {
                if (!in_array($key, $arIndicatorsDepartment) && $value['parent'] > 0) {
                    unset($arResult[ $key ]);
                }
            }
        }

        return $arResult;
    }

    public function apiGetTheme()
    {
        $arResult = [];
        $arFilter = [
            'IBLOCK_ID' => IBLOCK_ID_INDICATORS_THEMES
        ];
        $arSelect = [
            'ID',
            'NAME',
        ];
        $res = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
        while ($arElement = $res->GetNext()) {
            $arResult[ (int)$arElement['ID'] ] =[
                'id'    => (int)$arElement['ID'],
                'name'  => trim($arElement['NAME']),
            ];
        }
        return $arResult;
    }

    public function apiGetThemeStat()
    {
        $arResult = [];
        $resEnums = CIBlockPropertyEnum::GetList(
            ['DEF' => 'DESC', 'SORT' => 'ASC'],
            ['IBLOCK_ID' => IBLOCK_ID_INDICATORS_CATALOG, 'CODE' => 'THEME_STAT']
        );
        while ($arEnum = $resEnums->GetNext()) {
            $arResult[ $arEnum['ID'] ] = [
                'id'    => (int)$arEnum['ID'],
                'name'  => $arEnum['VALUE'],
            ];
        }
        return $arResult;
    }

    public function apiGetAffiliation()
    {
        $arResult = [];
        $resEnums = CIBlockPropertyEnum::GetList(
            ['DEF' => 'DESC', 'SORT' => 'ASC'],
            ['IBLOCK_ID' => IBLOCK_ID_INDICATORS_CATALOG, 'CODE' => 'AFFILIATION']
        );
        while ($arEnum = $resEnums->GetNext()) {
            $arResult[ $arEnum['ID'] ] = [
                'id'    => (int)$arEnum['ID'],
                'name'  => $arEnum['VALUE'],
            ];
        }
        return $arResult;
    }

    public function apiGetHistory($id = 0)
    {
        $arResult = [];
        global $DB;
        $connection = Application::getConnection('base_for_bi');
		$res = $connection->query("SELECT * FROM `bi` WHERE `bi_id` LIKE '" . $DB->ForSQL($id) . "' ORDER BY `date`");
        $arPrev = [];
        $arReplace = [
            'from'  => [' ', ',', '%', 'шт.', 'шт', 'ТБ', 'Тб'],
            'to'    => ['', '.', '', '', '', '', ''],
        ];
        while ($arData = $res->fetch()) {
            $value = str_replace($arReplace['from'], $arReplace['to'], $arData['state_value']);
            if ($arPrev['value'] != '' && $value == '') {
                continue;
            }
            $minTarget = 0;
            $target = str_replace($arReplace['from'], $arReplace['to'], $arData['target_value']);
            if (false !== mb_strpos($target, '-')) {
                $arTarget = explode('-', $target);
                $target = $arTarget[1];
                $minTarget = $arTarget[0];
            }
            $color = '#8FBF04';
            if (intval($arData['percent_exec']) < 30) {
                $color = '#F04E40';
            } elseif (
                intval($arData['percent_exec']) > 30 &&
                intval($arData['percent_exec']) < 90
            ) {
                $color = '#EABE24';
            }

            if (empty($target)) {
                $target = 0;
            }
            $arArray = [
                'bi_id'     => $arData['bi_id'],
                'target'    => $target,
                'targetMin' => $minTarget,
                'value'     => $value,
                'percent'   => $arData['percent_exec'],
                'date'      => $arData['date']->format('Y-m-d'),
                'fio'       => $arData['fio'],
                'lineColor' => $color,
            ];
            $arPrev = $arArray;
            $arResult[ $arData['id'] ] = $arArray;
        }
        return $arResult;
    }

    /**
     * Get results
     *
     * @return array
     */
    protected function getResult()
    {
        global $APPLICATION;

        $arResult['THEMES'] = $this->apiGetTheme();
        $arResult['INDICATOR_THEMES_NAMES'] = [];
        foreach ($arResult['THEMES'] as $theme) {
            $arResult['INDICATOR_THEMES_NAMES'][ $theme['name'] ] = $theme['id'];
        }

        $arResult['ALL_DEPARTMENTS'] = $this->apiGetDepartments();
        $arResult['DEPARTMENTS'] = [];
        foreach ($arResult['ALL_DEPARTMENTS'] as $dep) {
            if ($dep['parent'] <= 0) {
                $arResult['DEPARTMENTS'][ $dep['id'] ] = [
                    'ID'    => $dep['id'],
                    'NAME'  => $dep['name'],
                    'CHILD' => [],
                ];
            } else {
                $arResult['DEPARTMENTS'][ $dep['parent'] ]['CHILD'][ $dep['id'] ] = [
                    'ID'    => $dep['id'],
                    'NAME'  => $dep['name'],
                    'CHILD' => [],
                ];
                $arResult['DEPARTMENTS_IDS'][ $dep['parent'] ][] = $dep['id'];
            }
        }

        $arSelect = [
            'ID',
            'XML_ID',
            'IBLOCK_ID',
            'NAME',
            'PREVIEW_TEXT',
            'IBLOCK_SECTION_ID',
            'DATE_ACTIVE_FROM',
            'PROPERTY_THEME',
            'PROPERTY_STRUCTURE',
            'PROPERTY_TARGET_VALUE',
            'PROPERTY_MONTHLY_TARGET_VALUE',
            'PROPERTY_TARGET_VALUE_MIN',
            'PROPERTY_TYPE',
            'PROPERTY_THEME_STAT',
            'PROPERTY_AFFILIATION',
            'PROPERTY_INVERTED',
            'PROPERTY_PASSPOPT_LNPA',
            'PROPERTY_PASSPOPT_GROUP',
            'PROPERTY_PASSPOPT_TASKID',
            'PROPERTY_PASSPOPT_USER',
        ];
        $arFilter = [
            'IBLOCK_ID'             => IBLOCK_ID_INDICATORS_CATALOG,
            'ACTIVE_DATE'           => 'Y',
            'ACTIVE'                => 'Y',
            'INCLUDE_SUBSECTION'    =>'Y',
        ];
        if ($_REQUEST['filter']['TYPE']!='') {
            $arFilter['PROPERTY_TYPE'] = $_REQUEST['filter']['TYPE'];
        }
        $res = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);

        while ($arFields = $res->GetNext()) {
            if (!in_array($arFields['PROPERTY_STRUCTURE_VALUE'], $arResult['INDICATORS_DEPARTMENT'])) {
                $arResult['INDICATORS_DEPARTMENT'][] = $arFields['PROPERTY_STRUCTURE_VALUE'];
            }
        }

        foreach ($arResult['DEPARTMENTS'] as $sKey => $arValue) {
            $isActive = false;
            foreach ($arValue['CHILD'] as $sKey2 => $arValue2) {
                if (in_array($arValue2['ID'], $arResult['INDICATORS_DEPARTMENT'])) {
                    $isActive = true;
                } else {
                    unset($arResult['DEPARTMENTS'][ $sKey ]['CHILD'][ $sKey2 ]);
                }
            }
            if (in_array($arValue['ID'], $arResult['INDICATORS_DEPARTMENT'])) {
                $isActive = true;
                $arResult['DEPARTMENTS'][ $sKey ]['ACTIVE_INDICATOR']=true;
            }
            if (!$isActive) {
                unset($arResult['DEPARTMENTS'][ $sKey ]);
            }
        }
        $arFilter = [
            'IBLOCK_ID'     => IBLOCK_ID_INDICATORS_CATALOG,
            'SECTION_ID'    => false,
        ];
        $arSelect = [
            'ID',
            'NAME',
        ];
        $rsSections = CIBlockSection::GetList([], $arFilter, false, $arSelect);
        while ($arSection = $rsSections->Fetch()) {
            $arResult['CATEGORY'][ $arSection['ID'] ] = $arSection;
            $arResult['CATEGORY_NAMES'][ $arSection['ID'] ] = $arSection['NAME'];
        }
        $arResult['SUBCATEGORY_TES']=[];

        foreach ($arResult['CATEGORY'] as $sKey => $arValue) {
            $arFilter = [
                'IBLOCK_ID'     => IBLOCK_ID_INDICATORS_CATALOG,
                'SECTION_ID'    => $sKey,
            ];
            $arSelect = [
                'ID',
                'NAME',
            ];
            $rsSections = CIBlockSection::GetList([], $arFilter, false, $arSelect);
            while ($arSection = $rsSections->Fetch()) {
                $arResult['SUBCATEGORY_TES'][ $arSection['ID'] ] = $sKey;
                $arResult['CATEGORY'][ $sKey ]['CHILD'][ $arSection['ID'] ] = $arSection;
                $arResult['CATEGORY_NAMES'][ $arSection['ID'] ] = $arSection['NAME'];
            }
        }
        $property_enums = CIBlockPropertyEnum::GetList(
            ['DEF'=>'DESC', 'SORT'=>'ASC'],
            ['IBLOCK_ID'=>IBLOCK_ID_INDICATORS_CATALOG, 'CODE'=>'TYPE']
        );
        while ($arEnum = $property_enums->GetNext()) {
            $arResult['TYPES_NAME'][ $arEnum['VALUE'] ] = $arEnum;
        }
        global $arrFilter;

        $arResult['INDICATORS_BI'] = [];
        $arSelect = [
            'ID',
            'XML_ID',
            'IBLOCK_ID',
            'NAME',
            'PREVIEW_TEXT',
            'IBLOCK_SECTION_ID',
            'DATE_ACTIVE_FROM',
            'PROPERTY_THEME',
            'PROPERTY_STRUCTURE',
            'PROPERTY_TARGET_VALUE',
            'PROPERTY_MONTHLY_TARGET_VALUE',
            'PROPERTY_TARGET_VALUE_MIN',
            'PROPERTY_TYPE',
            'PROPERTY_THEME_STAT',
            'PROPERTY_AFFILIATION',
            'PROPERTY_INVERTED',
            'PROPERTY_PASSPOPT_LNPA',
            'PROPERTY_PASSPOPT_GROUP',
            'PROPERTY_PASSPOPT_TASKID',
            'PROPERTY_PASSPOPT_USER',
        ];
        $arFilter = [
            'IBLOCK_ID'             => IBLOCK_ID_INDICATORS_CATALOG,
            'ACTIVE_DATE'           => 'Y',
            'ACTIVE'                => 'Y',
            'INCLUDE_SUBSECTION'    =>'Y',
        ];
        $arFilter = array_merge($arFilter, $arrFilter);
        if (in_array($_REQUEST['show'], ['list', 'passport'])) {
            if (count($_REQUEST['filter']['THEME']) > 0) {
                $arFilter['PROPERTY_THEME'] = $_REQUEST['filter']['THEME'];
            }
            if (count($_REQUEST['filter']['CATEGORY']) > 0) {
                $arFilter['IBLOCK_SECTION_ID'] = $_REQUEST['filter']['CATEGORY'];
            }
            if ($_REQUEST['filter']['UPRAV'] != '') {
                $arFilter['PROPERTY_STRUCTURE'] = $arResult['DEPARTMENTS_IDS'][ $_REQUEST['filter']['UPRAV'] ];
            }
            if (count($_REQUEST['filter']['DEPARTMENT']) > 0) {
                $arFilter['PROPERTY_STRUCTURE'] = $_REQUEST['filter']['DEPARTMENT'];
            }

            if (isset($_REQUEST['del_filter'])) {
                LocalRedirect('/citto/indicators/?show=list&set_filter=y');
            }

            if ($_REQUEST['search'] != '') {
                $arFilter['NAME'] = '%' . $_REQUEST['search'] . '%';
            }
        }
        $res = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);

        while ($arFields = $res->GetNext()) {
            if ($arFields['XML_ID']!='') {
                $arResult['INDICATORS_BI'][] = $arFields['XML_ID'];
                $arResult['INDICATORS_BI_XML_ID'][ $arFields['XML_ID'] ] = $arFields['ID'];
            }
            $arResult['INDICATORS_SECTIONS'][ $arFields['IBLOCK_SECTION_ID'] ][] = $arFields['ID'];
            $arResult['INDICATORS'][ $arFields['ID'] ] = $arFields;
        }

        if (count($arResult['INDICATORS_BI'])>0) {
            $connection = Application::getConnection('base_for_bi');
            $data = $connection->query("SELECT MAX(`id`) AS `id`, `bi_id` FROM `bi` WHERE `bi_id` IN (".implode(',', $arResult['INDICATORS_BI']).") GROUP BY `bi_id`");
            while ($arDataID = $data->fetch()) {
                $arData = $connection->query("SELECT * FROM `bi` WHERE `id` = '".$arDataID['id']."'")->fetch();
                $arResult['DATA_BI'][ $arData['bi_id'] ] = $arData;
            }

            if (in_array($_REQUEST['show'], ['list', 'passport'])) {
                $arResult['BI_DATA'] = [];
                $data2 = $connection->query("SELECT * FROM `bi` WHERE `bi_id` IN (".implode(',', $arResult['INDICATORS_BI']).") ORDER BY `date`");
                $arPrev = [];
                $arReplace = [
                    'from'  => [' ', ',', '%', 'шт.', 'шт', 'ТБ', 'Тб'],
                    'to'    => ['', '.', '', '', '', '', ''],
                ];
                while ($arData = $data2->fetch()) {
                    $value = str_replace($arReplace['from'], $arReplace['to'], $arData['state_value']);
                    if ($arPrev['value'] != '' && $value == '') {
                        continue;
                    }
                    $minTarget = 0;
                    $target = str_replace($arReplace['from'], $arReplace['to'], $arData['target_value']);
                    if (false !== mb_strpos($target, '-')) {
                        $arTarget = explode('-', $target);
                        $target = $arTarget[1];
                        $minTarget = $arTarget[0];
                    }
                    $color = '#8FBF04';
                    if (intval($arData['percent_exec']) < 30) {
                        $color = '#F04E40';
                    } elseif (
                        intval($arData['percent_exec']) > 30 &&
                        intval($arData['percent_exec']) < 90
                    ) {
                        $color = '#EABE24';
                    }

                    if (empty($target)) {
                        $target = 0;
                    }
                    $arArray = [
                        'target'    => $target,
                        'targetMin' => $minTarget,
                        'value'     => $value,
                        'date'      => $arData['date']->format('Y-m-d'),
                        'fio'       => $arData['fio'],
                        'lineColor' => $color,
                    ];
                    $arPrev = $arArray;
                    $arResult['BI_DATA'][ $arData['bi_id'] ][] = $arArray;
                }
            }

            foreach ($arResult['INDICATORS'] as $sKey => $arValue) {
                $arResult['INDICATORS'][ $sKey ]['BI_DATA'] = $arResult['DATA_BI'][ $arValue['XML_ID'] ];
            }

            foreach ($arResult['CATEGORY'] as $sKey => $arValue) {
                if (count($arValue['CHILD']) > 0) {
                    $sSummaTree = 0;
                    $nIndicatorTree = 0;
                    foreach ($arValue['CHILD'] as $sKey2 => $arValue2) {
                        $bPassport = false;
                        $sSumma = 0;
                        $nIndicator = 0;

                        if (in_array($arValue2['ID'], [2849, 2851, 3022])) {
                            $bPassport = true;
                        }

                        foreach ($arResult['INDICATORS_SECTIONS'][ $arValue2['ID'] ] as $sKey3 => $sValue) {
                            if ($arResult['INDICATORS'][ $sValue ]['PROPERTY_TYPE_ENUM_ID'] == $arResult['TYPES_NAME']['Динамические']['ID']) {
                                if ($arResult['INDICATORS'][ $sValue ]['BI_DATA']['percent_exec'] != '') {
                                    if (!empty($arResult['INDICATORS'][ $sValue ]['PROPERTY_PASSPOPT_GROUP_VALUE'])) {
                                        $bPassport = true;
                                    } elseif (!$bPassport) {
                                        $nIndicator++;
                                        $sSumma += intval($arResult['INDICATORS'][ $sValue ]['BI_DATA']['percent_exec']);
                                    }
                                }

                                if (
                                    $bPassport &&
                                    !empty($arResult['INDICATORS'][ $sValue ]['PROPERTY_PASSPOPT_GROUP_VALUE'])
                                ) {
                                    $arPassport = $this->calcPassport(
                                        $arResult['INDICATORS'][ $sValue ]['PROPERTY_PASSPOPT_GROUP_VALUE'],
                                        $arResult['INDICATORS'][ $sValue ]['NAME'],
                                        $arResult['INDICATORS'][ $sValue ]['PROPERTY_PASSPOPT_TASKID_VALUE']??0
                                    );
                                    $nIndicator++;
                                    $sSumma += $arPassport['PERCENT'];
                                }
                            }
                        }

                        $arResult['CATEGORY'][ $sKey ]['CHILD'][ $sKey2 ]['PERCENT'] = $sSumma > 0 ? intval($sSumma / $nIndicator) : 0;
                        $arResult['CATEGORY'][ $sKey ]['CHILD'][ $sKey2 ]['PASSPORT'] = $bPassport;
                        $sSummaTree += $sSumma > 0 ? intval($sSumma / $nIndicator) : 0;
                        $nIndicatorTree++;
                    }
                    $arResult['CATEGORY'][ $sKey ]['PERCENT'] = intval($sSummaTree / $nIndicatorTree);
                } else {
                    $sSumma = 0;
                    $nIndicator = 0;

                    foreach ($arResult['INDICATORS_SECTIONS'][ $arValue['ID'] ] as $sKey2 => $sValue) {
                        if ($arResult['INDICATORS'][ $sValue ]['PROPERTY_TYPE_ENUM_ID'] == $arResult['TYPES_NAME']['Динамические']['ID']) {
                             $nIndicator++;

                             $sSumma += intval($arResult['INDICATORS'][ $sValue ]['BI_DATA']['percent_exec']);
                        }
                    }
                    $arResult['CATEGORY'][ $sKey ]['PERCENT'] = intval($sSumma/$nIndicator);
                }
            }
        }

        return $arResult;
    }

    /**
     * Set cache tag from params
     */
    public function setCacheTag()
    {
        if (
            defined('BX_COMP_MANAGED_CACHE') &&
            !empty($this->arParams['CACHE_TAGS']) &&
            is_object($GLOBALS['CACHE_MANAGER'])
        ) {
            foreach ($this->arParams['CACHE_TAGS'] as $tag) {
                $GLOBALS['CACHE_MANAGER']->RegisterTag($tag);
            }
        }
    }

    public function executeComponent()
    {
        try {
            if ($this->StartResultCache($this->arParams['CACHE_TIME'], $addCacheParams)) {
                $this->setCacheTag();
                $this->arResult = $this->getResult();
                $tpl = '';
                if (isset($_REQUEST['export'])) {
                    $tpl = 'export';
                }
                $this->includeComponentTemplate($tpl);
            }
        } catch (Exception $e) {
            ShowError($e->getMessage());
        }
    }

    /**
     * Выгрузка в ексель
     *
     * @param array $arResult
     *
     * @return void
     *
     * @todo Вынести это в единое место, откуда использовать
     */
    public function exportExcel(array $arResult): void
    {
        global $PHPEXCELPATH, $APPLICATION;
        require_once $PHPEXCELPATH . '/PHPExcel/IOFactory.php';
        $obExcel = new PHPExcel();
        $obExcel->setActiveSheetIndex(0);
        $sheet = $obExcel->getActiveSheet();
        $sheet->getPageSetup()
            ->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE)
            ->SetPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4)
            ->setFitToPage(true)
            ->setFitToWidth(1)
            ->setFitToHeight(0);

        $sheet->setTitle($arResult['TITLE']);

        $letters = range('A', 'Z');
        $rowIndex = 1;
        $i = 0;
        foreach ($arResult['HEADERS'] as $header) {
            $cellIndex = $letters[ $i ] . $rowIndex;
            $sheet->setCellValue($cellIndex, $header['NAME']);

            $sheet->getRowDimension($rowIndex)
                ->setRowHeight(20);

            if (isset($header['WIDTH'])) {
                $sheet->getColumnDimension($letters[ $i ])
                    ->setWidth($header['WIDTH']);
            }

            $sheet->getStyle($cellIndex)
                ->applyFromArray(
                    [
                        'borders' => [
                            'outline' => [
                                'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                                'color' => ['rgb' => '000000']
                            ]
                        ]
                    ]
                )
                ->getFont()
                ->setBold(true);

            $sheet->getStyle($cellIndex)
                ->getAlignment()
                ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
                ->setWrapText(true);
            $i++;
        }
        $rowIndex = 2;
        foreach ($arResult['ROWS'] as $row) {
            $i = 0;
            foreach (array_keys($arResult['HEADERS']) as $header) {
                $cellIndex = $letters[ $i ] . $rowIndex;
                $sheet->setCellValue($cellIndex, $row[ $header ]['VALUE']);
                if (isset($row[ $header ]['LINK'])) {
                    $link = new PHPExcel_Cell_Hyperlink($row[ $header ]['LINK']);
                    $sheet->setHyperlink($cellIndex, $link);
                }
                $sheet->getStyle($cellIndex)
                    ->applyFromArray(
                        [
                            'borders' => [
                                'outline' => [
                                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                                    'color' => ['rgb' => '000000']
                                ]
                            ]
                        ]
                    );

                $sheet->getStyle($cellIndex)
                    ->getAlignment()
                    ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);

                $color = '000000';
                if (isset($row[ $header ]['COLOR'])) {
                    $color = $row[ $header ]['COLOR'];
                }

                $sheet->getStyle($cellIndex)
                    ->applyFromArray(
                        [
                            'font'    => [
                                'color'     => [
                                    'rgb' => $color,
                                ],
                            ],
                        ]
                    );

                $bgColor = 'FFFFFF';
                if (isset($row[ $header ]['BGCOLOR'])) {
                    $bgColor = $row[ $header ]['BGCOLOR'];
                }
                $sheet->getStyle($cellIndex)
                    ->applyFromArray(
                        [
                            'fill' => [
                                'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => [
                                    'rgb' => $bgColor,
                                ],
                            ],
                        ]
                    );

                $i++;
            }
            $rowIndex++;
        }

        $APPLICATION->RestartBuffer();
        header('Expires: Mon, 1 Apr 1974 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D,d M YH:i:s') . ' GMT');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Content-type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $arResult['FILENAME'] . '.xls"');
        $obWriter = PHPExcel_IOFactory::createWriter($obExcel, 'Excel5');
        $obWriter->save('php://output');
    }
}
