<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (SITE_ID == \Citto\Mfc::$siteId && $arParams['arUserField']['SETTINGS']['IBLOCK_ID'] == 5) {
    $arParams['arUserField']['USER_TYPE']['FIELDS'] = array_merge(
        ['' => 'Нет'],
        \Citto\Mfc::getDepartmentNames()
    );
}
if (isset($arParams['SKIP_EMPTY_XML_ID']) && $arParams['SKIP_EMPTY_XML_ID'] == 'Y') {
    CModule::IncludeModule('iblock');
    $arFilter = [
        'IBLOCK_ID' => $arParams['arUserField']['SETTINGS']['IBLOCK_ID'],
        'ACTIVE'    => 'Y',
    ];
    $arXmlId = [];
    $rsSect   = CIBlockSection::GetList(['SORT' => 'asc', 'TIMESTAMP_X' => 'desc'], $arFilter);
    while ($arSect = $rsSect->GetNext()) {
        if (false !== mb_strpos($arSect['XML_ID'], 'MFC_')) {
            $arSect['XML_ID'] = '';
        }
        if (false !== mb_strpos($arSect['XML_ID'], 'control_poruch_')) {
            $arSect['XML_ID'] = '';
        }
        $arXmlId[ $arSect['ID'] ] = trim($arSect['XML_ID']);
    }
    $arXmlId['53'] = 53;
    foreach ($arParams["arUserField"]["USER_TYPE"]["FIELDS"] as $key => $val) {
        if (isset($arXmlId[ (int)$key ]) && empty($arXmlId[ (int)$key ])) {
            unset($arParams["arUserField"]["USER_TYPE"]["FIELDS"][ $key ]);
        }
    }
}
