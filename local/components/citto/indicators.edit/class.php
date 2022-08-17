<?php

namespace Citto\Indicators;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use CUser;
use CGroup;
use DateTime;
use Exception;
use CIBlockElement;
use CIBlockSection;
use CIntranetUtils;
use CBitrixComponent;
use Bitrix\Main\Loader;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Application;

Loader::includeModule('iblock');
Loader::includeModule('intranet');
global $APPLICATION;
$APPLICATION->SetAdditionalCSS('/bitrix/templates/.default/bootstrap.min.css');

class EditConponent extends CBitrixComponent
{
    public function apiGetDepartments(int $userId = 0)
    {
        global $USER;
        $obUsers = CUser::GetList($by, $order, ['ID' => $USER->GetID()], ['SELECT' => ['UF_INDICATORS_DEP', 'UF_DEPARTMENT']]);
        $arFieldsUser = $obUsers->getNext();
        $arIndicatorsChecks = $arFieldsUser['UF_INDICATORS_DEP'];

        $bIsAdmin = $USER->IsAdmin() || in_array($userId, [570, 2737]);
        $arResult = [];
        $arMyDepartments = $arIndicatorsChecks;//$this->getMyDepartments($userId);
        $arDepartments = $this->getDepartmentsList(SECTION_ID_CITTO_STRUCTURE);
        $arIndicatorsDepartment = $this->getIndicatorDepartments();
        $arAllIndicatorsDepartment = $arIndicatorsDepartment;
        foreach ($arDepartments as $sKey => $arValue) {
            foreach ($arValue['CHILD'] as $sKey2 => $arValue2) {
                if (in_array($arValue2['ID'], $arIndicatorsDepartment)) {
                    $arDepartments[ $sKey ]['CHILD'][ $sKey2 ]['ACTIVE_INDICATOR'] = true;
                    unset($arIndicatorsDepartment[ $arValue2['ID'] ]);
                }
                foreach ($arValue2['CHILD'] as $sKey3 => $arValue3) {
                    if (in_array($arValue3['ID'], $arIndicatorsDepartment)) {
                        $arDepartments[ $sKey ]['CHILD'][ $sKey2 ]['ACTIVE_INDICATOR'] = true;
                        $arDepartments[ $sKey ]['CHILD'][ $sKey2 ]['CHILD'][ $sKey3 ]['ACTIVE_INDICATOR'] = true;
                        unset($arIndicatorsDepartment[ $arValue3['ID'] ]);
                    }
                }
            }
            if (in_array($arValue['ID'], $arIndicatorsDepartment)) {
                $arDepartments[ $sKey ]['ACTIVE_INDICATOR'] = true;
                unset($arIndicatorsDepartment[ $arValue['ID'] ]);
            }
        }

        if (!empty($arIndicatorsDepartment)) {
            $arDepartments[-1] = [
                'ID' => -1,
                'NAME' => 'Другие',
                'CHILD' => [],
            ];
            foreach ($arIndicatorsDepartment as $findDep) {
                $arDepartments[-1]['CHILD'][ $findDep ] = array_merge(
                    $this->getDepartmentByID($findDep),
                    ['ACTIVE_INDICATOR' => true]
                );
            }
        }

        if ($bIsAdmin || !empty($arMyDepartments)) {
            foreach ($arDepartments as $arValue) {
                if (count($arValue['CHILD']) > 0) {
                    $bAddParent = false;
                    $arResult[ (int)$arValue['ID'] ] = [
                        'SELECT'    => false,
                        'ID'        => (int)$arValue['ID'],
                        'NAME'      => $arValue['NAME'],
                        'PARENT'    => 0,
                    ];

                    $arSubDepartment = [];
                    if (
                        $arValue['ACTIVE_INDICATOR'] &&
                        ($bIsAdmin || in_array($arValue['ID'], $arMyDepartments))
                    ) {
                        $bAddParent = true;
                        $arSubDepartment = [
                            'SELECT'    => true,
                            'ID'        => (int)$arValue['ID'],
                            'NAME'      => 'Без отдела',
                            'PARENT'    => (int)$arValue['ID'],
                        ];
                    }
                    $bAddSubHtml = false;
                    foreach ($arValue['CHILD'] as $arValue2) {
                        if ($bIsAdmin || in_array($arValue2['ID'], $arMyDepartments)) {
                            if (!$bAddSubHtml && !empty($arSubDepartment)) {
                                $bAddParent = true;
                                $arResult[ 'DEP_' . (int)$arSubDepartment['ID'] ] = $arSubDepartment;
                                $bAddSubHtml = true;
                            }
                            $bAddParent = true;
                            $arResult[ (int)$arValue2['ID'] ] = [
                                'SELECT'    => true,
                                'ID'        => (int)$arValue2['ID'],
                                'NAME'      => $arValue2['NAME'],
                                'PARENT'    => (int)$arValue['ID'],
                            ];
                        }
                        foreach ($arValue2['CHILD'] as $arValue3) {
                            if ($bIsAdmin || in_array($arValue3['ID'], $arMyDepartments)) {
                                $bAddParent = true;
                                $arResult[ (int)$arValue2['ID'] ] = [
                                    'SELECT'    => true,
                                    'ID'        => (int)$arValue2['ID'],
                                    'NAME'      => $arValue2['NAME'],
                                    'PARENT'    => (int)$arValue['ID'],
                                ];
                                $arResult[ (int)$arValue3['ID'] ] = [
                                    'SELECT'    => true,
                                    'ID'        => (int)$arValue3['ID'],
                                    'NAME'      => $arValue3['NAME'],
                                    'PARENT'    => (int)$arValue['ID'],
                                ];
                            }
                            foreach ($arValue3['CHILD'] as $arValue4) {
                                if ($bIsAdmin || in_array($arValue4['ID'], $arMyDepartments)) {
                                    $bAddParent = true;
                                    $arResult[ (int)$arValue3['ID'] ] = [
                                        'SELECT'    => true,
                                        'ID'        => (int)$arValue3['ID'],
                                        'NAME'      => $arValue3['NAME'],
                                        'PARENT'    => (int)$arValue['ID'],
                                    ];
                                    $arResult[ (int)$arValue4['ID'] ] = [
                                        'SELECT'    => true,
                                        'ID'        => (int)$arValue4['ID'],
                                        'NAME'      => $arValue4['NAME'],
                                        'PARENT'    => (int)$arValue['ID'],
                                    ];
                                }
                            }
                        }
                    }

                    if (!$bAddSubHtml && !empty($arSubDepartment)) {
                        $arResult[ 'DEP_' . (int)$arSubDepartment['ID'] ] = $arSubDepartment;
                    }

                    if (!$bAddParent) {
                        unset($arResult[ (int)$arValue['ID'] ]);
                    }
                } else {
                    if ($bIsAdmin || in_array($arValue['ID'], $arMyDepartments)) {
                        $arResult[ (int)$arValue['ID'] ] = [
                            'SELECT'    => true,
                            'ID'        => (int)$arValue['ID'],
                            'NAME'      => $arValue['NAME'],
                            'PARENT'    => 0,
                        ];
                    }
                }
            }
        }
        foreach ($arResult as $key => $value) {

            if (
                !in_array($key, $arAllIndicatorsDepartment) &&
                $value['PARENT'] > 0 &&
                $value['PARENT'] != $value['ID']
            ) {
                unset($arResult[$key]);
            } else {
                $arResult[$key] = [
                    'id' => (int)$value['ID'],
                    'name' => trim($value['NAME']),
                    'parent' => $value['PARENT'],
                ];
            }
        }
        foreach ($arResult as $key =>$value) {
            if (!in_array($key, $arMyDepartments) && $value['name'] != 'Без отдела') {
                unset($arResult[$key]);
            }
        }
        return $arResult;
    }

    public function apiSetData(int $departmentId = 0, array $arData = [])
    {
        global $USER, $APPLICATION;
        $arResult['DEPARTMENTS'] = $this->getDepartmentsList(SECTION_ID_CITTO_STRUCTURE);
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
            'INCLUDE_SUBSECTION'    => 'Y',
        ];
        $res = CIBlockElement::GetList(['SORT' => 'ASC', 'ID' => 'ASC'], $arFilter, false, false, $arSelect);
        while ($arFields = $res->GetNext()) {
            if (!in_array($arFields['PROPERTY_STRUCTURE_VALUE'], $arResult['INDICATORS_DEPARTMENT'])) {
                $arResult['INDICATORS_DEPARTMENT'][ $arFields['PROPERTY_STRUCTURE_VALUE'] ] = $arFields['PROPERTY_STRUCTURE_VALUE'];
            }
        }
        $arResult['INDICATORS_DEPARTMENT'] = array_filter($arResult['INDICATORS_DEPARTMENT']);
        $arResult['FIND_DEPARTMENT'] = $arResult['INDICATORS_DEPARTMENT'];

        foreach ($arResult['DEPARTMENTS'] as $sKey => $arValue) {
            $isActive = false;
            foreach ($arValue['CHILD'] as $sKey2 => $arValue2) {
                if (in_array($arValue2['ID'], $arResult['INDICATORS_DEPARTMENT'])) {
                    $isActive = true;
                    $arResult['DEPARTMENTS'][ $sKey ]['CHILD'][ $sKey2 ]['ACTIVE_INDICATOR'] = true;
                    unset($arResult['FIND_DEPARTMENT'][ $arValue2['ID'] ]);
                }
                foreach ($arValue2['CHILD'] as $sKey3 => $arValue3) {
                    if (in_array($arValue3['ID'], $arResult['INDICATORS_DEPARTMENT'])) {
                        $isActive = true;
                        $arResult['DEPARTMENTS'][ $sKey ]['CHILD'][ $sKey2 ]['CHILD'][ $sKey3 ]['ACTIVE_INDICATOR'] = true;
                        unset($arResult['FIND_DEPARTMENT'][ $arValue3['ID'] ]);
                    }
                }
            }
            if (in_array($arValue['ID'], $arResult['INDICATORS_DEPARTMENT'])) {
                $isActive = true;
                $arResult['DEPARTMENTS'][ $sKey ]['ACTIVE_INDICATOR'] = true;
                unset($arResult['FIND_DEPARTMENT'][ $arValue['ID'] ]);
            }
            if (!$isActive) {
                unset($arResult['DEPARTMENTS'][ $sKey ]);
            }
        }

        if (!empty($arResult['FIND_DEPARTMENT'])) {
            $arResult['DEPARTMENTS'][-1] = [
                'ID' => -1,
                'NAME' => 'Другие',
                'CHILD' => [],
            ];
            foreach ($arResult['FIND_DEPARTMENT'] as $findDep) {
                $arResult['DEPARTMENTS'][-1]['CHILD'][ $findDep ] = $this->getDepartmentByID($findDep);
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

        foreach ($arResult['CATEGORY'] as $sKey => $arValue) {
            $arFilter = array(
                'IBLOCK_ID'     => IBLOCK_ID_INDICATORS_CATALOG,
                'SECTION_ID'    => $sKey,
            );
            $arSelect = array(
                'ID',
                'NAME',
            );
            $rsSections = CIBlockSection::GetList([], $arFilter, false, $arSelect);
            while ($arSection = $rsSections->Fetch()) {
                $arResult['CATEGORY'][ $sKey ]['CHILD'][ $arSection['ID'] ] = $arSection;
                $arResult['CATEGORY_NAMES'][ $arSection['ID'] ] = $arSection['NAME'];
            }
        }
        $arResult['MY_DEPARTMENTS'] = $this->getMyDepartments();

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
            'INCLUDE_SUBSECTION'    => 'Y',
        ];
        if ($departmentId > 0) {
            $arFilter[] = [
                // 'LOGIC'                     => 'OR',
                // 'PROPERTY_STRUCTURE'        => $departmentId,
                // 'PROPERTY_PASSPOPT_USER'    => $GLOBALS['USER']->GetID(),
            ];
        }

        $res = CIBlockElement::GetList(['SORT' => 'ASC', 'ID' => 'ASC'], $arFilter, false, false, $arSelect);
        while ($arFields = $res->GetNext()) {
            if ($arFields['XML_ID'] != '') {
                $arResult['INDICATORS_BI'][] = $arFields['XML_ID'];
                $arResult['INDICATORS_BI_XML_ID'][ $arFields['XML_ID'] ] = $arFields['ID'];
            }
            $arResult['INDICATORS_SECTIONS'][ $arFields['IBLOCK_SECTION_ID'] ][] = $arFields['ID'];
            $arResult['INDICATORS'][ $arFields['ID'] ] = $arFields;
        }

        $arResult['PERIODS'] = self::getWeekPeriod(date('01.m.Y', strtotime('-6 MONTHS')), date('t.m.Y', strtotime(
            '+7 DAYS')));
        $arResult['CURRENT_PERIOD'] = [];
        $now = time();
        foreach ($arResult['PERIODS'] as $period) {
            if (strtotime($period['from']) <= $now && strtotime($period['to']) >= $now) {
                $arResult['CURRENT_PERIOD'] = $period;
            }
        }

        if (empty($arResult['CURRENT_PERIOD'])) {
            $arResult['CURRENT_PERIOD']['mid'] = date('d.m.Y H:i:s');
        }

        $queryDate = ' AND `date` BETWEEN "' . date('Y-m-d H:i:s', strtotime($arResult['CURRENT_PERIOD']['from'])) . '" AND "' . date('Y-m-d H:i:s', strtotime($arResult['CURRENT_PERIOD']['to'])) . '" ';

        $connection = Application::getConnection('base_for_bi');
        $sqlHelper = $connection->getSqlHelper();
        $arResult['USER_NAME'] = $this->UserGetFullName($GLOBALS['USER']->GetID());

        foreach ($arData as $sKey => $arValue) {
            $arValue = array_map('trim', $arValue);
            $arFields = [
                'full_name'     => $arValue['full_name'],
                'bi_id'         => (int)$arValue['bi_id'],
                'short_name'    => $arValue['short_name'],
                'base_set'      => '',
                'target_value'  => $arValue['target_value'],
                'state_value'   => $arValue['state_value'],
                'percent_exec'  => (int)$arValue['percent_exec'],
                'comment'       => $arValue['comment'],
                'date'          => Date::createFromPhp(new DateTime()),
                'control'       => '',
                'department'    => '',
                'fio'           => $arResult['USER_NAME'],
            ];
            if (!empty($arValue['target_value_min'])) {
                $arFields['target_value'] = $arValue['target_value_min'] . '-' . $arValue['target_value'];
            }
            $curIndicator = $arResult['INDICATORS'][ $arValue['id'] ];
            $arFields['base_set'] = $arResult['CATEGORY_NAMES'][ $curIndicator['IBLOCK_SECTION_ID'] ];
            if ($arResult['DEPARTMENTS'][ $curIndicator['PROPERTY_STRUCTURE_VALUE'] ]['NAME'] != '') {
                $arFields['control'] = $arResult['DEPARTMENTS'][ $curIndicator['PROPERTY_STRUCTURE_VALUE'] ]['NAME'];
            } else {
                $arFields['control'] = $arResult['DEPARTMENTS'][ $arResult['SUBDEPARTMENTS_IDS'][ $curIndicator['PROPERTY_STRUCTURE_VALUE'] ] ]['NAME'];
                $arFields['department'] = $arResult['DEPARTMENTS'][ $arResult['SUBDEPARTMENTS_IDS'][ $curIndicator['PROPERTY_STRUCTURE_VALUE'] ] ]['CHILD'][ $curIndicator['PROPERTY_STRUCTURE_VALUE'] ]['NAME'];
            }

            $data = $connection->query("SELECT MAX(`id`) AS `id`, `bi_id`
                    FROM `bi`
                    WHERE bi_id = '" . $arValue['bi_id'] . "' " . $queryDate . "
                    GROUP BY `bi_id`");

            if ($arDataID = $data->fetch()) {
                $arFields['create_date'] = \Bitrix\Main\Type\DateTime::createFromPhp(new DateTime(date('d.m.Y H:i:s')));
                $arFieldsSQL = $sqlHelper->prepareUpdate('bi', $arFields);
                $connection->queryExecute('UPDATE bi SET ' . $arFieldsSQL[0] . ' WHERE `id`= "' . $arDataID['id'] . '"');
            } else {
                $arFieldsSQL = $sqlHelper->prepareInsert('bi', $arFields);
                $connection->queryExecute('INSERT INTO `bi` (' . $arFieldsSQL[0] . ') ' . ' VALUES (' . $arFieldsSQL[1] . ')');
            }
        }

    }

    public function getMyDepartments(int $userId = 0)
    {
        global $USER;
        $arResult = [];
        if (!$USER->IsAdmin()) {
            if ($userId <= 0) {
                $userId = $USER->GetID();
            }
            $rsUser = CUser::GetByID($userId);
            $arUser = $rsUser->Fetch();
            $arMySubordinate = CIntranetUtils::GetSubordinateDepartments();
            $arResult = array_merge(
                $arUser['UF_DEPARTMENT'],
                $arMySubordinate
            );
            foreach ($arMySubordinate as $depId) {
                $arResult = array_merge(
                    $arResult,
                    CIntranetUtils::GetDeparmentsTree(
                        $depId,
                        true
                    )
                );
            }

            $arResult = array_unique($arResult);
        }

        return $arResult;
    }

    public function onPrepareComponentParams($arParams)
    {
        $arParams['CACHE_TIME'] = (int)$arParams['CACHE_TIME'];

        return $arParams;
    }

    public function UserGetFullName($sUserId)
    {
        $rsUser = CUser::GetByID($sUserId);
        $arUser = $rsUser->Fetch();
        return $arUser['LAST_NAME'] . ' ' . $arUser['NAME'] . ' ' . $arUser['SECOND_NAME'];
    }

    public static function getWeekPeriod($from, $to)
    {
        $weeks = [];
        $from = strtotime($from);
        $to = strtotime($to);

        while ($from < $to) {
            $fromDay = date('N', $from);
            if ($fromDay < 7) {
                $daysToSun = 7 - $fromDay;
                $end = strtotime("+ $daysToSun day", $from);
                if (date('n', $from) != date('n', $end)) {
                    $end = strtotime('last day of this month', $from);
                }

                $week = [
                    'from' => date('d.m.Y 00:00:00', $from),
                    'to' => date('d.m.Y 23:59:59', $end)
                ];
                $from = $end;
            } else {
                $week = [
                    'from' => date('d.m.Y 00:00:00', $from),
                    'to' => date('d.m.Y 23:59:59', $from)
                ];
            }

            $mid = strtotime($week['from']) + ((strtotime($week['to']) - strtotime($week['from'])) / 2);
            $week['mid'] = date('d.m.Y 00:00:00', $mid);
            $week['edited'] = false;
            $weeks[] = $week;
            $from = strtotime('+1 day', $from);
        }

        $weeks = array_reverse($weeks);

        return $weeks;
    }

    protected function getDepartmentByID($id = 0)
    {
        $arFilter = array(
            'IBLOCK_ID' => IBLOCK_ID_STRUCTURE,
            'ID'        => $id,
        );
        $arSelect = array(
            'ID',
            'NAME',
            'UF_HEAD',
            'DEPTH_LEVEL',
        );
        $arResult = [];
        $rsSections = CIBlockSection::GetList([], $arFilter, false, $arSelect);
        while ($arSection = $rsSections->Fetch()) {
            $arSection['CHILD'] = $this->getDepartmentsList($arSection['ID']);
            $arResult[ $arSection['ID'] ] = $arSection;
        }

        return $arResult[ $id ];
    }

    public function getIndicatorDepartments()
    {
        $arSelect = [
            'ID',
            'PROPERTY_STRUCTURE',
        ];
        $arFilter = [
            'IBLOCK_ID'             => IBLOCK_ID_INDICATORS_CATALOG,
            'ACTIVE_DATE'           => 'Y',
            'ACTIVE'                => 'Y',
            'INCLUDE_SUBSECTION'    => 'Y',
        ];
        $arIndicatorsDepartment = [];
        $res = CIBlockElement::GetList(['SORT' => 'ASC', 'ID' => 'ASC'], $arFilter, false, false, $arSelect);
        while ($arFields = $res->GetNext()) {
            if (!in_array($arFields['PROPERTY_STRUCTURE_VALUE'], $arIndicatorsDepartment)) {
                $arIndicatorsDepartment[ $arFields['PROPERTY_STRUCTURE_VALUE'] ] = $arFields['PROPERTY_STRUCTURE_VALUE'];
            }
        }
        $arIndicatorsDepartment = array_filter($arIndicatorsDepartment);

        return $arIndicatorsDepartment;
    }

    public function getDepartmentsList($id = 0)
    {
        $arFilter = array(
            'IBLOCK_ID'     => IBLOCK_ID_STRUCTURE,
            'SECTION_ID'    => $id,
        );
        $arSelect = array(
            'ID',
            'NAME',
            'UF_HEAD',
            'DEPTH_LEVEL',
        );
        $arResult = [];
        $rsSections = CIBlockSection::GetList([], $arFilter, false, $arSelect);
        while ($arSection = $rsSections->Fetch()) {
            $arSection['ACTIVE_INDICATOR'] = false;
            $arSection['CHILD'] = $this->getDepartmentsList($arSection['ID']);
            $arResult[ $arSection['ID'] ] = $arSection;
        }

        $arIndicatorsDepartment = $this->getIndicatorDepartments();

        foreach ($arResult as $sKey => $arValue) {
            foreach ($arValue['CHILD'] as $sKey2 => $arValue2) {
                if (in_array($arValue2['ID'], $arIndicatorsDepartment)) {
                    $arResult[ $sKey ]['CHILD'][ $sKey2 ]['ACTIVE_INDICATOR'] = true;
                }
                foreach ($arValue2['CHILD'] as $sKey3 => $arValue3) {
                    if (in_array($arValue3['ID'], $arIndicatorsDepartment)) {
                        $arResult[ $sKey ]['CHILD'][ $sKey2 ]['CHILD'][ $sKey3 ]['ACTIVE_INDICATOR'] = true;
                    }
                }
            }
            if (in_array($arValue['ID'], $arIndicatorsDepartment)) {
                $arResult[ $sKey ]['ACTIVE_INDICATOR'] = true;
            }
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
        global $USER, $APPLICATION;
        $APPLICATION->SetTitle('Заполнение');
        $arResult['DEPARTMENTS'] = $this->getDepartmentsList(SECTION_ID_CITTO_STRUCTURE);
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
            'INCLUDE_SUBSECTION'    => 'Y',
        ];
        $res = CIBlockElement::GetList(['SORT' => 'ASC', 'ID' => 'ASC'], $arFilter, false, false, $arSelect);
        while ($arFields = $res->GetNext()) {
            if (!in_array($arFields['PROPERTY_STRUCTURE_VALUE'], $arResult['INDICATORS_DEPARTMENT'])) {
                $arResult['INDICATORS_DEPARTMENT'][ $arFields['PROPERTY_STRUCTURE_VALUE'] ] = $arFields['PROPERTY_STRUCTURE_VALUE'];
            }
        }
        $arResult['INDICATORS_DEPARTMENT'] = array_filter($arResult['INDICATORS_DEPARTMENT']);
        $arResult['FIND_DEPARTMENT'] = $arResult['INDICATORS_DEPARTMENT'];

        foreach ($arResult['DEPARTMENTS'] as $sKey => $arValue) {
            $isActive = false;
            foreach ($arValue['CHILD'] as $sKey2 => $arValue2) {
                if (in_array($arValue2['ID'], $arResult['INDICATORS_DEPARTMENT'])) {
                    $isActive = true;
                    $arResult['DEPARTMENTS'][ $sKey ]['CHILD'][ $sKey2 ]['ACTIVE_INDICATOR'] = true;
                    unset($arResult['FIND_DEPARTMENT'][ $arValue2['ID'] ]);
                }
                foreach ($arValue2['CHILD'] as $sKey3 => $arValue3) {
                    if (in_array($arValue3['ID'], $arResult['INDICATORS_DEPARTMENT'])) {
                        $isActive = true;
                        $arResult['DEPARTMENTS'][ $sKey ]['CHILD'][ $sKey2 ]['CHILD'][ $sKey3 ]['ACTIVE_INDICATOR'] = true;
                        unset($arResult['FIND_DEPARTMENT'][ $arValue3['ID'] ]);
                    }
                }
            }
            if (in_array($arValue['ID'], $arResult['INDICATORS_DEPARTMENT'])) {
                $isActive = true;
                $arResult['DEPARTMENTS'][ $sKey ]['ACTIVE_INDICATOR'] = true;
                unset($arResult['FIND_DEPARTMENT'][ $arValue['ID'] ]);
            }
            if (!$isActive) {
                unset($arResult['DEPARTMENTS'][ $sKey ]);
            }
        }

        if (!empty($arResult['FIND_DEPARTMENT'])) {
            $arResult['DEPARTMENTS'][-1] = [
                'ID' => -1,
                'NAME' => 'Другие',
                'CHILD' => [],
            ];
            foreach ($arResult['FIND_DEPARTMENT'] as $findDep) {
                $arResult['DEPARTMENTS'][-1]['CHILD'][ $findDep ] = $this->getDepartmentByID($findDep);
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

        foreach ($arResult['CATEGORY'] as $sKey => $arValue) {
            $arFilter = array(
                'IBLOCK_ID'     => IBLOCK_ID_INDICATORS_CATALOG,
                'SECTION_ID'    => $sKey,
            );
            $arSelect = array(
                'ID',
                'NAME',
            );
            $rsSections = CIBlockSection::GetList([], $arFilter, false, $arSelect);
            while ($arSection = $rsSections->Fetch()) {
                $arResult['CATEGORY'][ $sKey ]['CHILD'][ $arSection['ID'] ] = $arSection;
                $arResult['CATEGORY_NAMES'][ $arSection['ID'] ] = $arSection['NAME'];
            }
        }
        $arResult['MY_DEPARTMENTS'] = $this->getMyDepartments();
        if (!$USER->IsAdmin()) {
            $_REQUEST['show'] = 'list';
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
            'INCLUDE_SUBSECTION'    => 'Y',
        ];
        if (
            $_REQUEST['show'] == 'list' &&
            count($_REQUEST['filter']['DEPARTMENT']) > 0 &&
            $_REQUEST['filter']['DEPARTMENT'][0] != ''
        ) {
            $arFilter[] = [
                'LOGIC'                     => 'OR',
                'PROPERTY_STRUCTURE'        => $_REQUEST['filter']['DEPARTMENT'],
                'PROPERTY_PASSPOPT_USER'    => $GLOBALS['USER']->GetID(),
            ];

            $res = CIBlockElement::GetList(['SORT' => 'ASC', 'ID' => 'ASC'], $arFilter, false, false, $arSelect);
            while ($arFields = $res->GetNext()) {
                if ($arFields['XML_ID'] != '') {
                    $arResult['INDICATORS_BI'][] = $arFields['XML_ID'];
                    $arResult['INDICATORS_BI_XML_ID'][ $arFields['XML_ID'] ] = $arFields['ID'];
                }
                $arResult['INDICATORS_SECTIONS'][ $arFields['IBLOCK_SECTION_ID'] ][] = $arFields['ID'];
                $arResult['INDICATORS'][ $arFields['ID'] ] = $arFields;
            }
        }

        $arResult['PERIODS'] = self::getWeekPeriod(date('01.m.Y', strtotime('-6 MONTHS')), date('t.m.Y', strtotime(
            '+7 DAYS')));
        $arResult['CURRENT_PERIOD'] = [];
        $now = time();
        if (isset($_REQUEST['CURRENT_PERIOD'])) {
            $now = $_REQUEST['CURRENT_PERIOD'];
        }
        foreach ($arResult['PERIODS'] as $period) {
            if (strtotime($period['from']) <= $now && strtotime($period['to']) >= $now) {
                $arResult['CURRENT_PERIOD'] = $period;
            }
        }

        if (empty($arResult['CURRENT_PERIOD'])) {
            $arResult['CURRENT_PERIOD']['mid'] = date('d.m.Y H:i:s');
        }

        $queryDate = ' AND `date` BETWEEN "' . date('Y-m-d H:i:s', strtotime($arResult['CURRENT_PERIOD']['from'])) . '" AND "' . date('Y-m-d H:i:s', strtotime($arResult['CURRENT_PERIOD']['to'])) . '" ';

        $connection = Application::getConnection('base_for_bi');
        $sqlHelper = $connection->getSqlHelper();
        if ($_REQUEST['send']) {
            $arResult['USER_NAME'] = $this->UserGetFullName($GLOBALS['USER']->GetID());
            foreach ($_REQUEST['INDICATORS'] as $sKey => $arValue) {
                $arValue = array_map('trim', $arValue);
                $arFields = [
                    'full_name'     => $arValue['full_name'],
                    'bi_id'         => (int)$arValue['bi_id'],
                    'short_name'    => $arValue['short_name'],
                    'base_set'      => '',
                    'target_value'  => $arValue['target_value'],
                    'state_value'   => $arValue['state_value'],
                    'percent_exec'  => (int)$arValue['percent_exec'],
                    'comment'       => $arValue['comment'],
                    'date'          => Date::createFromPhp(new DateTime()),
                    'control'       => '',
                    'department'    => '',
                    'fio'           => $arResult['USER_NAME'],
                ];
                if (!empty($arValue['target_value_min'])) {
                    $arFields['target_value'] = $arValue['target_value_min'] . '-' . $arValue['target_value'];
                }
                $curIndicator = $arResult['INDICATORS'][ $arValue['id'] ];
                $arFields['base_set'] = $arResult['CATEGORY_NAMES'][ $curIndicator['IBLOCK_SECTION_ID'] ];
                if ($arResult['DEPARTMENTS'][ $curIndicator['PROPERTY_STRUCTURE_VALUE'] ]['NAME'] != '') {
                    $arFields['control'] = $arResult['DEPARTMENTS'][ $curIndicator['PROPERTY_STRUCTURE_VALUE'] ]['NAME'];
                } else {
                    $arFields['control'] = $arResult['DEPARTMENTS'][ $arResult['SUBDEPARTMENTS_IDS'][ $curIndicator['PROPERTY_STRUCTURE_VALUE'] ] ]['NAME'];
                    $arFields['department'] = $arResult['DEPARTMENTS'][ $arResult['SUBDEPARTMENTS_IDS'][ $curIndicator['PROPERTY_STRUCTURE_VALUE'] ] ]['CHILD'][ $curIndicator['PROPERTY_STRUCTURE_VALUE'] ]['NAME'];
                }

                $data = $connection->query("SELECT MAX(`id`) AS `id`, `bi_id`
                        FROM `bi`
                        WHERE bi_id = '" . $arValue['bi_id'] . "' " . $queryDate . "
                        GROUP BY `bi_id`");

                if ($arDataID = $data->fetch()) {
                    $arFields['create_date'] = \Bitrix\Main\Type\DateTime::createFromPhp(new DateTime(date('d.m.Y H:i:s')));
                    $arFieldsSQL = $sqlHelper->prepareUpdate('bi', $arFields);
                    $connection->queryExecute('UPDATE bi SET ' . $arFieldsSQL[0] . ' WHERE `id`= "' . $arDataID['id'] . '"');
                } else {
                    $arFieldsSQL = $sqlHelper->prepareInsert('bi', $arFields);
                    $connection->queryExecute('INSERT INTO `bi` (' . $arFieldsSQL[0] . ') ' . ' VALUES (' . $arFieldsSQL[1] . ')');
                }
            }
        }

        $arResult['EDITED'] = [];
        $arResult['DATA_BI'] = [];

        if (count($arResult['INDICATORS_BI']) > 0) {
            $data = $connection->query("SELECT MAX(`id`) AS `id`, `bi_id`
                    FROM `bi`
                    WHERE `bi_id` IN (" . implode(',', $arResult['INDICATORS_BI']) . ") " . $queryDate . "
                    GROUP BY `bi_id`");
            while ($arDataID = $data->fetch()) {
                $arData = $connection->query("SELECT * FROM `bi` WHERE `id` = '" . $arDataID['id'] . "'")->fetch();
                $arResult['EDITED']['UPDATE'] = $arData['create_date']->format('d.m.Y H:i:s');
                $arResult['EDITED']['DATE'] = $arData['date']->format('d.m.Y H:i:s');
                $arResult['EDITED']['FIO'] = $arData['fio'];
                $arResult['EDITED']['PREV_DATA'] = false;
                $arResult['EDITED']['PERIOD'] = $arResult['CURRENT_PERIOD'];
                $arResult['DATA_BI'][ $arData['bi_id'] ] = $arData;
            }

            if (empty($arResult['EDITED']) && empty($arResult['DATA_BI'])) {
                $arResult['EDITED']['PREV_DATA'] = true;
                $data = $connection->query("SELECT MAX(`date`) AS `date`
                        FROM `bi`
                        WHERE bi_id IN (" . implode(',', $arResult['INDICATORS_BI']) . ")
                            AND `date` <= '" . date('Y-m-d H:i:s', strtotime($arResult['CURRENT_PERIOD']['from'])) . "'
                        GROUP BY `bi_id` LIMIT 1");
                while ($arDataID = $data->fetch()) {
                    $curPeriod = [];
                    $now = $arDataID['date']->getTimestamp();
                    foreach ($arResult['PERIODS'] as $period) {
                        if (strtotime($period['from']) <= $now && strtotime($period['to']) >= $now) {
                            $curPeriod = $period;
                        }
                    }
                    if (!empty($curPeriod)) {
                        $queryDate = ' AND `date` BETWEEN "' . date('Y-m-d H:i:s', strtotime($curPeriod['from'])) . '" AND "' . date('Y-m-d H:i:s', strtotime($curPeriod['to'])) . '" ';
                        $data = $connection->query("SELECT MAX(`id`) AS `id`, `bi_id`
                                FROM `bi`
                                WHERE bi_id IN (" . implode(',', $arResult['INDICATORS_BI']) . ") " . $queryDate . "
                                GROUP BY `bi_id`");
                        while ($arDataID = $data->fetch()) {
                            $arData = $connection->query("SELECT * FROM `bi` WHERE `id` = '" . $arDataID['id'] . "'")->fetch();
                            $arResult['EDITED']['UPDATE'] = $arData['create_date']->format('d.m.Y H:i:s');
                            $arResult['EDITED']['DATE'] = $arData['date']->format('d.m.Y H:i:s');
                            $arResult['EDITED']['FIO'] = $arData['fio'];
                            $arResult['EDITED']['PERIOD'] = $curPeriod;
                            $arResult['DATA_BI'][ $arData['bi_id'] ] = $arData;
                        }
                    }
                }
            }

            $data = $connection->query("SELECT DISTINCT `date`
                    FROM `bi`
                    WHERE `bi_id` IN (" . implode(',', $arResult['INDICATORS_BI']) . ")");
            while ($arData = $data->fetch()) {
                $arResult['EDITED']['DATES'][] = $arData['date']->format('d.m.Y');
            }

            foreach ($arResult['INDICATORS'] as $sKey => $arValue) {
               $arResult['INDICATORS'][ $sKey ]['BI_DATA'] = $arResult['DATA_BI'][ $arValue['XML_ID'] ];
            }
        }

        if (isset($arResult['EDITED']['DATES'])) {
            foreach ($arResult['EDITED']['DATES'] as $date) {
                $ts = strtotime($date);
                foreach ($arResult['PERIODS'] as $id => $period) {
                    if (strtotime($period['from']) <= $ts && strtotime($period['to']) >= $ts) {
                        $arResult['PERIODS'][ $id ]['edited'] = true;
                    }
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
                $this->includeComponentTemplate();
            }
        } catch (Exception $e) {
            ShowError($e->getMessage());
        }
    }
}
