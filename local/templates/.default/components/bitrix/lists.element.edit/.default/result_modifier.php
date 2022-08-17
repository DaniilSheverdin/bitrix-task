<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Loader;

require_once getenv("DOCUMENT_ROOT") . '/local/vendor/autoload.php';
Loader::includeModule('iblock');

$arSites = [];
$res = CIBlock::GetSite($arResult['IBLOCK']['ID']);
while ($row = $res->Fetch()) {
    $arSites[] = $row['LID'];
}

if (!in_array(SITE_ID, $arSites)) {
    ShowError('Нет прав для просмотра и редактирования списка.');
    return;
}

foreach ($arResult['FIELDS'] as &$field) {
    if ($field['PROPERTY_TYPE'] != "L") {
        continue;
    }
    $field['VALUES'] = [];
    $db_enum_list = CIBlockProperty::GetPropertyEnum(
        $field['ID'],
        ['SORT' => "ASC"],
        ['IBLOCK_ID' => $field['IBLOCK_ID']]
    );
    while ($ar_enum_list = $db_enum_list->fetch()) {
        $field['VALUES'][$ar_enum_list['ID']] = $ar_enum_list;
    }
}
unset($field);