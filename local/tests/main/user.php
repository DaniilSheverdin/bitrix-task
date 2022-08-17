<?php

namespace Citto\Tests\Main;

use CSite;
use CUser;
use Citto\Mfc;
use CIBlockSection;
use CIntranetUtils;
use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;

/**
 * Пользователи
 */
class User
{
    public static $arSkipGroups = [
        85,     // ГАУ ТО "ЦИТ": Сотрудники
        120,    // ГБУ ТО "МФЦ": Сотрудники
        96,     // Контроль поручений - Исполнитель
        67,     // Комитет по делам ЗАГС: Сотрудники
        122,    // Подведы министерства образования
    ];

    /**
     * Проверка привязки к сайту
     * @dataProvider providerTestWrongSiteId
     * @responsible 54
     * @run hourly
     */
    public static function testWrongSiteId(
        $siteId,
        $arDepartments,
        $groupId,
        $arSkipUsers = [],
        $arSkipGroups = [],
        $arFilter = []
    ) {
        $arSite = CSite::GetByID($siteId)->Fetch();
        $mess = '<i>' . $arSite['NAME'] . '</i><br/>';

        $arUsers = [];
        $arUsersFilter = ['ACTIVE' => 'Y'];
        if (!empty($arFilter)) {
            $arUsersFilter = array_merge($arUsersFilter, $arFilter);
        }
        $orm = UserTable::getList([
            'select'    => ['ID', 'LID', 'UF_DEPARTMENT', 'NAME', 'LAST_NAME', 'EXTERNAL_AUTH_ID'],
            'filter'    => $arUsersFilter
        ]);
        while ($arUser = $orm->fetch()) {
            if (in_array($arUser['ID'], $arSkipUsers)) {
                continue;
            }
            if (empty($arUser['EXTERNAL_AUTH_ID'])) {
                continue;
            }
            $arGroups = CUser::GetUserGroup($arUser['ID']);
            foreach ($arSkipGroups as $gId) {
                if (in_array($gId, $arGroups)) {
                    continue(2);
                }
            }
            $arDiff = array_intersect($arUser['UF_DEPARTMENT'], $arDepartments);
            if (
                (!empty($arDiff) || in_array($groupId, $arGroups)) &&
                $arUser['LID'] != $siteId
            ) {
                $arUsers[ $arUser['ID'] ] = $arUser['ID'] . ' ' . $arUser['NAME'] . ' ' . $arUser['LAST_NAME'] . ': LID (' . $arUser['LID'] . ' != ' . $siteId . ')' . (!in_array($groupId, $arGroups) ? ' ГРУППА ' . $groupId . ' НЕ УСТАНОВЛЕНА' : '');
            } elseif (
                $groupId > 0 &&
                !in_array($groupId, $arGroups) &&
                $arUser['LID'] == $siteId
            ) {
                $arUsers[ $arUser['ID'] ] = $arUser['ID'] . ' ' . $arUser['NAME'] . ' ' . $arUser['LAST_NAME'] . ': ГРУППА ' . $groupId . ' НЕ УСТАНОВЛЕНА';
            }
        }

        if (!empty($arUsers)) {
            return assert(false, $mess . implode("<br/>", $arUsers));
        } else {
            return assert(true);
        }
    }

    /**
     * Провайдер данных для теста testWrongSiteId
     * @return array
     */
    public function providerTestWrongSiteId()
    {
        Loader::includeModule('iblock');
        Loader::includeModule('intranet');
        $arAllDepartments = CIntranetUtils::GetDeparmentsTree(53, true);
        $arAllDepartments[] = 53;
        $arCitDepartments = CIntranetUtils::GetDeparmentsTree(57, true);
        $arCitDepartments[] = 57;
        $arSitcDepartments = CIntranetUtils::GetDeparmentsTree(3080, true);
        $arSitcDepartments[] = 3080;
        $arTCURDepartments = CIntranetUtils::GetDeparmentsTree(3099, true);
        $arTCURDepartments[] = 3099;
        $arMfcDepartments = Mfc::getDepartmentList();

        $arMinObrDepartments = [];
        $objTree = CIBlockSection::GetTreeList(
            [
                'IBLOCK_ID' => 5,
                'ACTIVE' => 'Y',
                'SECTION_ID' => 463,
            ],
            ['ID', 'NAME', 'DEPTH_LEVEL', 'UF_PODVED', 'XML_ID']
        );
        while ($depItem = $objTree->GetNext()) {
            if (!empty($depItem['XML_ID'])) {
                continue;
            }

            if (in_array(
                $depItem['ID'],
                [
                    2234, // ГУ ТО "ЦТНЭЗиСУО"
                    2605, // ГОУ ДПО ТО "ИПК ИППРО ТО"
                ]
            )) {
                continue;
            }

            $arMinObrDepartments[] = $depItem['ID'];
            $arMinObrDepartments = array_merge(
                $arMinObrDepartments,
                CIntranetUtils::GetDeparmentsTree($depItem['ID'], true)
            );
        }

        $arAllDepartments = array_diff($arAllDepartments, $arCitDepartments);
        $arAllDepartments = array_diff($arAllDepartments, $arMfcDepartments);
        $arAllDepartments = array_diff($arAllDepartments, $arMinObrDepartments);
        $arAllDepartments = array_diff($arAllDepartments, $arSitcDepartments);
        $arAllDepartments = array_diff($arAllDepartments, $arTCURDepartments);
        sort($arAllDepartments);

        return [
            [
                Mfc::$siteId,
                $arMfcDepartments,
                120,
                [597],
                [],
                [],
            ],
            [
                'nh',
                $arCitDepartments,
                85,
                [1913, 14, 1801],
                [],
                [],
            ],
            [
                's1',
                $arAllDepartments,
                0,
                [2551, 4200],
                [85, 120, 122, 138],
                [],
            ],
            [
                'hy',
                $arMinObrDepartments,
                122,
                [2748],
                [],
                [],
            ],
            [
                'sc',
                $arSitcDepartments,
                138,
                [],
                [],
                [],
            ],
            [
                's1',
                $arTCURDepartments,
                142,
                [],
                [],
                ['UF_DEPARTMENT' => $arTCURDepartments],
            ],
        ];
    }

    /**
     * Проверка дублей UF_SID
     * @responsible 54
     * @run hourly
     */
    public static function testDoubleSid()
    {
        $arUsers = [];
        $orm = UserTable::getList([
            'select'    => ['ID', 'UF_SID', 'NAME', 'LAST_NAME'],
            'filter'    => ['ACTIVE' => 'Y']
        ]);
        while ($arUser = $orm->fetch()) {
            if (empty($arUser['UF_SID'])) {
                continue;
            }
            $arUsers[ $arUser['UF_SID'] ][ $arUser['ID'] ] = $arUser['ID'] . ': ' . $arUser['NAME'] . ' ' . $arUser['LAST_NAME'];
        }

        foreach ($arUsers as $sid => $users) {
            if (count($users) <= 1) {
                unset($arUsers[ $sid ]);
            } else {
                $arUsers[ $sid ] = implode("<br/>", $users);
            }
        }

        if (!empty($arUsers)) {
            return assert(false, implode("<br/>", $arUsers));
        } else {
            return assert(true);
        }
    }

    /**
     * Проверка пустых UF_SID
     * @responsible 54
     * @run hourly
     */
    public static function testEmptySid()
    {
        $arUsers = [];
        $orm = UserTable::getList([
            'select'    => ['ID', 'EMAIL', 'UF_SID', 'NAME', 'LAST_NAME', 'UF_DEPARTMENT', 'EXTERNAL_AUTH_ID', 'XML_ID'],
            'filter'    => ['ACTIVE' => 'Y']
        ]);
        while ($arUser = $orm->fetch()) {
            if (empty($arUser['XML_ID'])) {
                continue;
            }
            if (empty($arUser['EXTERNAL_AUTH_ID'])) {
                continue;
            }
            if (empty($arUser['UF_DEPARTMENT'])) {
                continue;
            }
            if (false !== mb_strpos($arUsers['EMAIL'], 'tularegion.org')) {
                continue;
            }
            if (empty($arUser['UF_SID'])) {
                $arGroups = CUser::GetUserGroup($arUser['ID']);
                foreach (self::$arSkipGroups as $skip) {
                    if (in_array($skip, $arGroups)) {
                        continue(2);
                    }
                }

                $arUsers[ $arUser['ID'] ] = $arUser['ID'] . ': ' . $arUser['NAME'] . ' ' . $arUser['LAST_NAME'];
            }
        }

        if (!empty($arUsers)) {
            return assert(false, implode("<br/>", $arUsers));
        } else {
            return assert(true);
        }
    }

    /**
     * Проверка пользователей с UF_SID, которые не обновлялись больше 2 суток
     * @responsible 54
     * @run hourly
     */
    public static function testNotUpdateWithSid()
    {
        $arUsers = [];
        $orm = UserTable::getList([
            'select'    => ['ID', 'UF_SID', 'NAME', 'LAST_NAME', 'TIMESTAMP_X', 'XML_ID'],
            'filter'    => ['ACTIVE' => 'Y']
        ]);

        while ($arUser = $orm->fetch()) {
            if (empty($arUser['XML_ID'])) {
                continue;
            }
            if (!empty($arUser['UF_SID'])) {
                $arGroups = CUser::GetUserGroup($arUser['ID']);
                foreach (self::$arSkipGroups as $skip) {
                    if (in_array($skip, $arGroups)) {
                        continue(2);
                    }
                }
                if ((time() - $arUser['TIMESTAMP_X']->getTimestamp()) > 172800) {
                    $arUsers[ $arUser['ID'] ] = $arUser['ID'] . ': ' . $arUser['NAME'] . ' ' . $arUser['LAST_NAME'];
                }
            }
        }

        if (!empty($arUsers)) {
            return assert(false, implode("\r\n", $arUsers));
        } else {
            return assert(true);
        }
    }
}
