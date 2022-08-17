<?php

/*
Экспорт из МФЦ, потом скачать файл.

CModule::IncludeModule('iblock');
$objTree = CIBlockSection::GetTreeList(
    [
        "IBLOCK_ID" => 5,
    ],
    ["*", 'UF_*']
);

$arList = [];
while ($depItem = $objTree->GetNext(false, false)) {
    $arList[] = $depItem;
}
file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/local/structure.json', json_encode($arList));

*/
require $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";
require $_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php";
CModule::IncludeModule('iblock');

function get_created()
{
    $cache = \Bitrix\Main\Data\Cache::createInstance();
    if ($cache->initCache(60*60, 'get_created', '/mfc/')) {
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
        while ($depItem = $objTree->GetNext()) {
            if (!in_array($depItem['ID'], $arrSecIds)) {
                continue;
            }
            $arList[ $depItem['XML_ID'] ] = [
                'ID' => $depItem['ID'],
                'UF_HEAD' => $depItem['UF_HEAD'],
            ];
        }

        $cache->endDataCache($arList);
    }

    return $arList;
}

function get_created_users()
{
    $cache = \Bitrix\Main\Data\Cache::createInstance();
    if ($cache->initCache(300, 'get_created_users3', '/mfc/')) {
        $arList = $cache->getVars();
    } elseif ($cache->startDataCache()) {
        $rsUsers = CUser::GetList(($by = "personal_country"), ($order = "desc"), ['ACTIVE' => 'Y'], ['SELECT' => ['*', 'UF_*']]);
        while ($arUser = $rsUsers->GetNext(false, false)) {
            if (empty($arUser['ADMIN_NOTES'])) {
                continue;
            }
            $arList[ $arUser['ADMIN_NOTES'] ] = $arUser;
        }
        $cache->endDataCache($arList);
    }

    return $arList;
}

$arUsers = get_created_users();

$arStructure = json_decode(file_get_contents(__DIR__ . '/structure.json'), true);
foreach ($arStructure as $k => $v) {
    $v['XML_ID'] = 'MFC_' . $v['ID'];
    unset($v['ID'], $v['TIMESTAMP_X'], $v['MODIFIED_BY'], $v['DATE_CREATE'], $v['CREATED_BY'], $v['LEFT_MARGIN'], $v['RIGHT_MARGIN'], $v['DEPTH_LEVEL'], $v['DESCRIPTION'], $v['DESCRIPTION_TYPE'], $v['SEARCHABLE_CONTENT'], $v['CODE']);
    unset($v['TMP_ID'], $v['DETAIL_PICTURE'], $v['SOCNET_GROUP_ID'], $v['LIST_PAGE_URL'], $v['SECTION_PAGE_URL'], $v['EXTERNAL_ID'], $v['UF_GUID'], $v['UF_ORG_ID'], $v['UF_PARENT'], $v['UF_PROCENT_KZAY'], $v['UF_PROCENT_DOLI'], $v['UF_DEP_CONTACTS']);
    unset($v['UF_FORMULA'], $v['UF_TIMEMAN'], $v['UF_LAST_REPORT_DATE'], $v['UF_SETTING_DATE'], $v['PICTURE'], $v['IBLOCK_TYPE_ID'], $v['IBLOCK_CODE'], $v['IBLOCK_EXTERNAL_ID'], $v['GLOBAL_ACTIVE']);

    $arStructure[ $k ] = $v;
}

$bs = new CIBlockSection();
foreach ($arStructure as $row) {
    $arCreated = get_created();
    if (!isset($arCreated[ 'MFC_' . $row['IBLOCK_SECTION_ID'] ])) {
        continue;
    }
    if (!isset($arCreated[ $row['XML_ID'] ])) {
        $row['IBLOCK_SECTION_ID'] = $arCreated[ 'MFC_' . $row['IBLOCK_SECTION_ID'] ]['ID'];
        $row['NAME'] = str_replace('&quot;', '"', $row['NAME']);
        pre('ADD');
        pre($row);
        $bs->Add($row);
        BXClearCache(true, '/mfc/');
    } else {
        if (isset($arUsers[ 'MFC_' . $row['UF_HEAD'] ]) && $arCreated[ $row['XML_ID'] ]['UF_HEAD'] != $arUsers[ 'MFC_' . $row['UF_HEAD'] ]['ID']) {
            $arUpdate = [
                'UF_HEAD' => $arUsers[ 'MFC_' . $row['UF_HEAD'] ]['ID']
            ];
            pre('UPDATE');
            pre($arCreated[ $row['XML_ID'] ]);
            pre($arUpdate);
            // $bs->Update($arCreated[ $row['XML_ID'] ]['ID'], $arUpdate);
        }
    }
}
