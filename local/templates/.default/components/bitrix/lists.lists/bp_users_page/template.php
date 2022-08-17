<?php

use Bitrix\Main\Web\Json;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI\Extension;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

global $results;
$results = $arResult;
$results["PROCEED_URL"] = $arParams["PROCEED_URL"];
CJSCore::Init(['lists']);
Extension::load([
    'ui.hint',
    'ui.buttons'
]);

Asset::getInstance()->addJs('/bitrix/templates/.default/bootstrap.min.js');
Asset::getInstance()->addCss('/bitrix/templates/.default/bootstrap.min.css');

function showBpCard(array $item, $url): string
{
    $itemUrl = str_replace("#IBLOCK_ID#", $item['ID'], $url);
    
    return '<div class="bp-bx-application bp-bx-application--' . $item['ID'] . '">
        <span class="bp-bx-application-link">
            <a
                href="' . $itemUrl . '"
                target="_blank"
                class="bp-bx-application-icon">' . $item["IMAGE"] . '</a>
            <span class="bp-bx-application-title-wrapper">
                <a
                    href="' . $itemUrl . '"
                    target="_blank"
                    class="bp-bx-application-title">
                    ' . $item["NAME"] . '
                    ' . (!empty($item['DESCRIPTION']) ?
                    '<span data-hint="' . str_replace(['"', '<br>', '<br/>', '<br />'], ['\"', "\r\n", "\r\n", "\r\n"], $item['DESCRIPTION']) . '"></span>' :
                    '') .
                '</a>
            </span>
        </span>
    </div>';
}

function showBpCategory(string $catName = '', string $parent = 'bizproc-accordion'): string
{
    global $results;
    $arIds = $results['CATEGORIES'][ $catName ];

    $return  = '<div class="card"></div><div class="card">
        <div class="card-header" id="heading-' . crc32($catName) . '">
            <h2 class="mb-0">
                <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse-' . crc32($catName) . '" aria-expanded="false" aria-controls="collapse-' . crc32($catName) . '">' . $catName . '</button>
            </h2>
        </div>
        <div id="collapse-' . crc32($catName) . '" class="collapse" aria-labelledby="heading-' . crc32($catName) . '" data-parent="#' . $parent . '">
            <div class="card-body">
            ';

    if (isset($results['CATEGORIES_TREE'][ $catName ])) {
        foreach ($results['CATEGORIES_TREE'][ $catName ] as $subCat) {
            $return .= showBpCategory($subCat, 'heading-' . crc32($catName));
        }
    }

    foreach ($arIds as $id) {
        $return  .= showBpCard($results["ITEMS"][ $id ], $results["PROCEED_URL"]);
        unset($results["ITEMS"][ $id ]);
    }
    $return  .= '
            </div>
        </div>
    </div><div class="card"></div>';

    return $return;
}



?>
<div class="bp_users_page_container">
<div class="accordion" id="bizproc-accordion">
<?
foreach ($results['CATEGORIES_TREE']['-1'] as $catName) {
    echo showBpCategory($catName);
}
/*
foreach ($arResult['CATEGORIES'] as $catName => $arIds) {
    $arIds = array_unique($arIds);
    foreach ($arIds as $key => $value) {
        if (!isset($arResult["ITEMS"][ $value ])) {
            unset($arIds[ $key ]);
        }
    }
    if (empty($arIds)) {
        continue;
    }
    ?>
    <div class="card">
        <div class="card-header" id="heading-<?=crc32($catName)?>">
            <h2 class="mb-0">
                <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse-<?=crc32($catName)?>" aria-expanded="false" aria-controls="collapse-<?=crc32($catName)?>"><?=$catName?></button>
            </h2>
        </div>
        <div id="collapse-<?=crc32($catName)?>" class="collapse" aria-labelledby="heading-<?=crc32($catName)?>" data-parent="#bizproc-accordion">
            <div class="card-body">
            <?
            foreach ($arIds as $id) {
                if (!isset($arResult["ITEMS"][ $id ])) {
                    continue;
                }

                echo showBpCard($arResult["ITEMS"][ $id ], $arParams["PROCEED_URL"]);
                unset($arResult["ITEMS"][ $id ]);
            }
            ?>
            </div>
        </div>
    </div>
    <?php
}
*/
?>
</div>
<?
if (!empty($results["ITEMS"])) {
    echo '<br/><br/>';
    $return = [];
    foreach ($results["ITEMS"] as $item) {
        $return[ trim($item['NAME']) . $item['ID'] ] = showBpCard($item, $arParams["PROCEED_URL"]);
    }
    ksort($return);
    echo implode("\r\n", $return);
}
?>
</div>
