<?php

/*
Экспорт из МФЦ, потом скачать файл.

$arList = [];
$rsUsers = CUser::GetList(($by = "ID"), ($order = "asc"), [], ['SELECT' => ['*', 'UF_*']]);
while ($arUser = $rsUsers->GetNext(false, false)) {
    $arList[] = $arUser;
}
file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/local/users.json', json_encode($arList));

*/
require $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";
require $_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php";
CModule::IncludeModule('iblock');

function get_created_deps()
{
    $cache = \Bitrix\Main\Data\Cache::createInstance();
    if ($cache->initCache(300, 'get_created_deps', '/mfc/')) {
        $arList = $cache->getVars();
    } elseif ($cache->startDataCache()) {
        $arrSecIds = CIntranetUtils::GetDeparmentsTree(58, true);
        $objTree = CIBlockSection::GetTreeList(
            [
                "IBLOCK_ID" => 5,
            ],
            ["*", 'UF_*']
        );

        $arList = [
            'MFC_155' => 58
        ];
        while ($depItem = $objTree->GetNext(false, false)) {
            if (!in_array($depItem['ID'], $arrSecIds)) {
                continue;
            }
            $arList[ $depItem['XML_ID'] ] = $depItem['ID'];
        }

        $cache->endDataCache($arList);
    }

    return $arList;
}

function get_created_users()
{
    $cache = \Bitrix\Main\Data\Cache::createInstance();
    if ($cache->initCache(300, 'get_created_users2', '/mfc/')) {
        $arList = $cache->getVars();
    } elseif ($cache->startDataCache()) {
        $rsUsers = CUser::GetList(($by = "personal_country"), ($order = "desc"), ['ACTIVE' => 'Y'], ['SELECT' => ['*', 'UF_*']]);
        while ($arUser = $rsUsers->GetNext(false, false)) {
            $arList[ mb_strtolower($arUser['LOGIN']) ] = $arUser;
        }
        $cache->endDataCache($arList);
    }

    return $arList;
}

$arDeps = get_created_deps();

$arUsers = json_decode(file_get_contents(__DIR__ . '/users.json'), true);
foreach ($arUsers as $k => $v) {
    if (in_array($v['ID'], [1, 12971, 13428, 13456, 13502, 13525, 13719, 5240, 13661, 13662, 13663, 13610, 13530,])) {
        unset($arUsers[ $k ]);
        continue;
    }

    if (empty($v['EXTERNAL_AUTH_ID'])) {
        $v['EXTERNAL_AUTH_ID'] = 'LDAP#1';
    }

    $v['LID'] = 'gi';
    $v['ADMIN_NOTES'] = 'MFC_' . $v['ID'];
    $v['XML_ID'] = $v['LOGIN'];
    $v['GROUP_ID'] = [2,120];

    if (empty($v['EMAIL'])) {
        $v['EMAIL'] = $v['LOGIN'] . "@tularegion.ru";
    }

    foreach ($v['UF_DEPARTMENT'] as $k => $dep) {
        if (isset($arDeps[ 'MFC_' . $dep ])) {
            $v['UF_DEPARTMENT'][ $k ] = $arDeps[ 'MFC_' . $dep ];
        } else {
            $v['UF_DEPARTMENT'][ $k ] = 58;
        }
    }

    unset($v['ID'], $v['TIMESTAMP_X'], $v['PASSWORD'], $v['CHECKWORD'], $v['LAST_LOGIN'], $v['DATE_REGISTER']);
    unset($v['PERSONAL_PHOTO'], $v['STORED_HASH'], $v['CHECKWORD_TIME'], $v['CONFIRM_CODE'], $v['LOGIN_ATTEMPTS'], $v['LAST_ACTIVITY_DATE']);
    unset($v['AUTO_TIME_ZONE'], $v['TIME_ZONE'], $v['TIME_ZONE_OFFSET'], $v['TITLE'], $v['IS_ONLINE']);
    unset($v['UF_FACEBOOK'], $v['UF_ORG_ID'], $v['UF_GUID'], $v['UF_FORMULA'], $v['UF_AD_LOGIN']);
    unset($v['UF_SETTING_DATE'], $v['UF_1C'], $v['UF_DISTRICT'], $v['UF_PUBLIC'], $v['UF_VI_NAME']);
    unset($v['UF_VI_PASSWORD'], $v['UF_VI_BACKPHONE'], $v['UF_OBS_ID'], $v['UF_USER_DATA_DATE'], $v['UF_USERLIB']);
    unset($v['UF_HR'], $v['UF_HR2'], $v['UF_DELAY_TIME'], $v['UF_LAST_REPORT_DATE']);

    $arUsers[ $k ] = $v;
}

$obUser = new CUser();
$i = 0;
BXClearCache(true, '/mfc/');
$arCreated = get_created_users();
$arCheckFields = [
    'NAME',
    'LAST_NAME',
    'SECOND_NAME',
    'ACTIVE',
    'WORK_POSITION',
    'UF_DEPARTMENT',
    'LID',
    // 'ADMIN_NOTES',
    // 'EXTERNAL_AUTH_ID',
    'XML_ID',
    'LOGIN',
    'EMAIL',
];
foreach ($arUsers as $row) {
    if (isset($arCreated[ mb_strtolower($row['LOGIN']) ])) {
        $arFinded = $arCreated[ mb_strtolower($row['LOGIN']) ];
        if ($arFinded['LID'] != $row['LID']) {
            continue;
        }
        foreach ($arCheckFields as $key) {
            if (md5($arFinded[ $key ]) != md5($row[ $key ])) {
                pre('UPDATE ' . $arFinded['ID'] . ' ' . $key . ': OLD: "' . print_r($arFinded[ $key ], true) . '" NEW = "' . print_r($row[ $key ], true) . '"');
            }
        }
        continue;
    }
    // if ($i > 100) {
    //     die;
    // }
    // if (empty($row['UF_DEPARTMENT'])) {
    //     continue;
    // }
    // $obUser->Add($row);
    // pre('ADD');
    // pre($row);
    // if ($error = $obUser->LAST_ERROR) {
    //     pre($error);
    // }
    // $i++;
}
