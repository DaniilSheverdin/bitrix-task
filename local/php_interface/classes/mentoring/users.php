<?php

namespace Citto\Mentoring;

use CPHPCache;
use CIntranetUtils;
use CIBlockElement;
use CBitrixComponent;
use Bitrix\Main\Loader;
use CIBlockPropertyEnum;
use Bitrix\Main\UserTable;
use Citto\ControlOrders\Main\Component as MainComponent;

class Users
{
    private static $arPositionsForMentors = [
        "Главный советник",
        "Заместитель начальника отдела",
        "Главный консультант",
        "Консультант",
        "Главный специалист-эксперт",
        "Ведущий специалист-эксперт",
        "Ведущий специалист 1 разряда",
        "Ведущий специалист 2 разряда",
        "Ведущий специалист 3 разряда",
        "Старший специалист 1 разряда",
        "Старший специалист 2 разряда",
        "Старший специалист 3 разряда",
        "Специалист 1 разряда",
        "Главный государственный инспектор",
        "Старший государственный инспектор",
        "Государственный инспектор",
        "Бухгалтер",
        "Старший государственный ветеринарный инспектор",
        "Государственный ветеринарный инспектор",
        "Ведущий инженер",
        "Инженер",
        "Главный инспектор",
        "Старший инспектор",
        "Инспектор 1 категории",
        "Инспектор 2 категории",
        "Старший референт",
        "Референт",
        "Секретарь-референт",
        "Специалист",
        "Юрисконсульт",
        "Делопроизводитель",
        "Корректор"
    ];

    /**
     * @param $iUsersID
     * @return bool
     * Подходит ли должность пользователя для участия в менторстве в качестве наставляемого
     */
    public static function isPositionForMentors($iUserID)
    {
        global $userFields;

        $bResult = false;
        $arUser = $userFields($iUserID);
        $sUserPosition = $arUser['UF_WORK_POSITION'];
        if ($sUserPosition) {
            foreach (self::$arPositionsForMentors as $sPosition) {
                if (mb_stripos($sPosition, $sUserPosition) !== false) {
                    $bResult = true;
                }
            }
        }

        return $bResult;
    }

    /**
     * @param array $arUserIDs
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * Пользователь + роль его подразделения в структуре.
     * Является подразделение ОИВом или подведом.
     */
    public static function getUsersWithStrcuture($arUserIDs = [])
    {
        $arDepartments = [];
        $arUsers = [];
        $arExcludesDepartments = CIntranetUtils::GetIBlockSectionChildren([2237, 2228, 2137, 2970]);
        $obDepartments = \CIBlockSection::GetList([], ["IBLOCK_ID" => 5, 'ACTIVE' => 'Y', '>DEPTH_LEVEL' => 1, ''], false,
            ['ID', 'NAME', 'IBLOCK_SECTION_ID', 'DEPTH_LEVEL', 'UF_PODVED']);

        function recDepartment($iID, $arDepartments)
        {
            $iParentID = $arDepartments[$iID]['PARENT_ID'];
            $iDepth = $arDepartments[$iID]['DEPTH'];

            if ($iDepth == 4) {
                return $arDepartments[$iID];
            } else {
                if ($iDepth > 4) {
                    return recDepartment($iParentID, $arDepartments);
                } else {
                    return $arDepartments[$iID];
                }
            }
        }

        while ($arDep = $obDepartments->GetNext()) {
            $iID = $arDep['ID'];
            if (!in_array($iID, $arExcludesDepartments)) {
                $sName = $arDep['NAME'];
                $bPodved = ($arDep['UF_PODVED'] == 1) ? true : false;
                $arDepartments[$iID] = [
                    'NAME' => $sName,
                    'PODVED' => $bPodved,
                    'DEPTH' => (int )$arDep['DEPTH_LEVEL'],
                    'PARENT_ID' => $arDep['IBLOCK_SECTION_ID']
                ];
            }
        }

        foreach ($arDepartments as $iDepID => $arDepartment) {
            if ($arDepartment['DEPTH'] > 4) {
                $arRecDepartment = recDepartment($iDepID, $arDepartments);
                $arDepartments[$iDepID]['NAME'] = $arRecDepartment['NAME'];
                $arDepartments[$iDepID]['PODVED'] = $arRecDepartment['PODVED'];
            } else {
                if ($arDepartment['DEPTH'] == 4 && !$arDepartment['PODVED']) {
                    $iParentID = $arDepartment['PARENT_ID'];
                    $arDepartments[$iDepID]['NAME'] = $arDepartments[$iParentID]['NAME'];
                }
            }

            unset($arDepartments[$iDepID]['PARENT_ID'], $arDepartments[$iDepID]['DEPTH']);
        }

        $arUserFilter = [];
        $arUserFilter['=ACTIVE'] = 'Y';

        if ($arUserIDs) {
            $arUserFilter['ID'] = $arUserIDs;
        }
        $obUsers = UserTable::getList([
            'select' => ['ID', 'ACTIVE', 'LOGIN', 'XML_ID', 'UF_SID', 'EMAIL', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'UF_DEPARTMENT', 'UF_WORK_POSITION', 'UF_GOV_EMPLOYEE'],
            'filter' => $arUserFilter
        ]);

        while ($arUser = $obUsers->fetch()) {
            $iDepartment = current($arUser['UF_DEPARTMENT']);
            if (!empty($arDepartments[$iDepartment])) {
                $sSID = (!empty($arUser['UF_SID'])) ? $arUser['UF_SID'] : $arUser['XML_ID'];
                $arUsers[$arUser['ID']] = [
                    'ID' => $arUser ['ID'],
                    'LOGIN' => $arUser ['LOGIN'],
                    'SID' => $sSID,
                    'EMAIL' => $arUser ['EMAIL'],
                    'NAME' => $arUser ['NAME'],
                    'LAST_NAME' => $arUser ['LAST_NAME'],
                    'SECOND_NAME' => $arUser ['SECOND_NAME'],
                    'DEPARTMENT' => [
                        'NAME' => $arDepartments[$iDepartment]['NAME'],
                        'PODVED' => ($arDepartments[$iDepartment]['PODVED']) ? 'Y' : 'N',
                    ],
                    'POSITION' => $arUser ['UF_WORK_POSITION'],
                    'GOVERMENT' => (empty($arUser['UF_GOV_EMPLOYEE'])) ? 'N' : 'Y',
                    'ACTIVE' => $arUser['ACTIVE']
                ];
            }
        }

        return $arUsers;
    }

    /**
     * @param $iUserID
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     * Определяем главу ОИВ и его замов у конкретного пользователя
     */
    public static function getHeads($iUserID)
    {
        $arDepartments = [];
        $obDepartments = \CIBlockSection::GetList([], ["IBLOCK_ID" => 5, 'ACTIVE' => 'Y', '>DEPTH_LEVEL' => 1, ''], false,
            ['ID', 'NAME', 'IBLOCK_SECTION_ID', 'DEPTH_LEVEL', 'UF_HEAD', 'UF_HEAD2', 'UF_PODVED', 'UF_HEAD_HELPERS']);

        function recDepartment($iID, $arDepartments)
        {
            $iParentID = $arDepartments[$iID]['PARENT_ID'];
            $iDepth = $arDepartments[$iID]['DEPTH'];

            if ($iDepth == 3) {
                return $arDepartments[$iID];
            } else {
                if ($iDepth > 3) {

                    return recDepartment($iParentID, $arDepartments);
                } else {
                    return $arDepartments[$iID];
                }
            }
        }

        while ($arDep = $obDepartments->GetNext()) {
            $iID = $arDep['ID'];
            $sName = $arDep['NAME'];
            $arDepartments[$iID] = [
                'NAME' => $sName,
                'UF_HEAD' => $arDep['UF_HEAD'],
                'UF_HEAD2' => $arDep['UF_HEAD2'],
                'UF_HEAD_HELPERS' => $arDep['UF_HEAD_HELPERS'],
                'DEPTH' => (int )$arDep['DEPTH_LEVEL'],
                'PARENT_ID' => $arDep['IBLOCK_SECTION_ID']
            ];
        }

        foreach ($arDepartments as $iDepID => $arDepartment) {
            if ($arDepartment['DEPTH'] > 3) {
                $arRecDepartment = recDepartment($iDepID, $arDepartments);
                $arDepartments[$iDepID]['NAME'] = $arRecDepartment['NAME'];
                $arDepartments[$iDepID]['UF_HEAD'] = $arRecDepartment['UF_HEAD'];
                $arDepartments[$iDepID]['UF_HEAD2'] = $arRecDepartment['UF_HEAD2'];
                $arDepartments[$iDepID]['UF_HEAD_HELPERS'] = $arRecDepartment['UF_HEAD_HELPERS'];

            }

            unset($arDepartments[$iDepID]['PARENT_ID'], $arDepartments[$iDepID]['DEPTH']);
        }

        $obUsers = UserTable::getList([
            'select' => ['ID', 'LOGIN', 'XML_ID', 'UF_SID', 'EMAIL', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'UF_DEPARTMENT', 'UF_WORK_POSITION',],
            'filter' => ['ID' => $iUserID]
        ]);

        $arUsersOIV = [];
        while ($arUser = $obUsers->fetch()) {
            $iDepartment = current($arUser['UF_DEPARTMENT']);
            if (!empty($arDepartments[$iDepartment])) {
                $sHead = ($arDepartments[$iDepartment]['UF_HEAD']);
                $arHeplers = ($arDepartments[$iDepartment]['UF_HEAD_HELPERS']);

                $arUsersOIV[$arUser['ID']] = [
                    'HEAD' => 0,
                    'HELPERS' => []
                ];
                if ($sHead) {
                    $arUsersOIV[$arUser['ID']]['HEAD'] = $sHead;
                }

                if ($arHeplers) {
                    foreach ($arHeplers as $sHelper) {
                        array_push($arUsersOIV[$arUser['ID']]['HELPERS'], $sHelper);
                    }
                }
            }
        }

        return [
            'HEAD' => $arUsersOIV[$iUserID]['HEAD'],
            'HELPERS' => $arUsersOIV[$iUserID]['HELPERS']
        ];
    }
}
