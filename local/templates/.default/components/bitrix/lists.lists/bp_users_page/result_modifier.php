<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}

$defaultImg = "<img src=\"/bitrix/images/lists/nopic_list_150.png\" width=\"36\" height=\"30\" border=\"0\" alt=\"\" />";
if ($arParams["IBLOCK_TYPE_ID"] == COption::GetOptionString("lists", "livefeed_iblock_type_id")) {
    $defaultImg = "<img src=\"/bitrix/images/lists/default.png\" width=\"36\" height=\"30\" border=\"0\" alt=\"\" />";
}

$arHideBPs = [
    589, // Создание проекта из инициативы
    590, // Переход на ЭТК
    591, // Переход на ЭТК (массовый)
    594, // Перенос отпуска 2022 год
];

if (SITE_ID == 'gi' && !$GLOBALS['USER']->IsAdmin()) {
    $arSkip = [58, 2698];
    $arSpecialDeps = CIntranetUtils::GetDeparmentsTree(2698, true);
    $arSkip = array_merge($arSkip, $arSpecialDeps);
    $arDeps = array_diff(\Citto\Mfc::getDepartmentList(), $arSkip);
    $arMfcManagers = array_keys(CIntranetUtils::GetDepartmentManager($arDeps));
    if (!in_array($USER->GetID(), $arMfcManagers)) {
        $arHideBPs[] = 636; // Изменение графика работы отделения или УРМа
    }

    if (!in_array(
        $USER->GetID(), [
        2547, // Солтанова Екатерина
        3614, // Мысов Андрей
        2548, // Грудинин Евгений
        2822  // Сумина Марина
    ])
    ) {
        $arHideBPs[] = 588; // БП Проектная инифицатива
    }
}

foreach ($arResult["ITEMS"] as $key => $item) {
    if (in_array($item['ID'], $arHideBPs)) {
        unset($arResult["ITEMS"][ $key ]);
        continue;
    }
    if ($item["PICTURE"] > 0) {
        $imageFile = CFile::GetFileArray($item["PICTURE"]);
        if ($imageFile !== false) {
            $imageFile = CFile::ResizeImageGet(
                $imageFile,
                array("width" => 36, "height" => 30),
                BX_RESIZE_IMAGE_PROPORTIONAL,
                false
            );
            $arResult["ITEMS"][$key]["IMAGE"] = CFile::ShowImage($imageFile['src'], 36, 30, 'border=0');
        }
    }

    if (!$arResult["ITEMS"][$key]["IMAGE"]) {
        $arResult["ITEMS"][$key]["IMAGE"] = $defaultImg;
    }

    if ($arParams["IBLOCK_TYPE_ID"] == COption::GetOptionString("lists", "livefeed_iblock_type_id")) {
        $arResult["ITEMS"][$key]["SHOW_LIVE_FEED"] = CLists::getLiveFeed($item['ID']);
    }
}

$arResult['CATEGORIES'] = [];
$arResult['CATEGORIES_TREE'] = [];
$arResult['LIST'] = [];

foreach ($arResult["ITEMS"] as $item) {
    $strCategory = '';
    if (preg_match('/\[\[(.*)\]\](.*)/si', $item['DESCRIPTION'], $arMatches)) {
        $strCategory = $arMatches[1];
        $item['DESCRIPTION'] = trim(str_replace('[[' . $strCategory . ']]', '', $item['DESCRIPTION']));
    }
    if ($strCategory != '') {
        $arCategories = explode('|', $strCategory);
        $prev = '';
        foreach ($arCategories as $id => $cat) {
            if ($id == count($arCategories)-1) {
                $arResult['CATEGORIES'][ $cat ][] = $item['ID'];
            }
            if ($prev != '') {
                $arResult['CATEGORIES_TREE'][ $prev ][ $cat ] = $cat;
            } else {
                $arResult['CATEGORIES_TREE']['-1'][ $cat ] = $cat;
            }
            $prev = $cat;
        }
    }

    $arResult['LIST'][ $item['ID'] ] = $item;
}
$arResult["ITEMS"] = $arResult['LIST'];

$arTemp = $arResult['CATEGORIES']['Другое'];
unset($arResult['CATEGORIES']['Другое']);
ksort($arResult['CATEGORIES']);
$arResult['CATEGORIES']['Другое'] = $arTemp;
$arResult['CATEGORIES_TREE'][-1]['Другое'] = 'Другое';
