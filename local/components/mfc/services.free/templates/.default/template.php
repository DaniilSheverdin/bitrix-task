<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<?

use Bitrix\Main\Page\Asset;

$APPLICATION->SetTitle("Свободность услуг");
Asset::getInstance()->addCss('/bitrix/templates/.default/bootstrap.min.css');
?>

<?
$APPLICATION->IncludeComponent(
    "bitrix:main.ui.grid",
    "",
    array(
        "GRID_ID"                   => 'SERVICES_FREE',
        "COLUMNS"                   => $arResult['COLUMNS'],
        "ROWS"                      => $arResult["SERVICES"],
        "NAV_STRING"                => $arResult["NAV_STRING"],
        "TOTAL_ROWS_COUNT"          => $arResult["RECORDS"]['COUNT'],
        "PAGE_SIZES"                => $arResult["GRID_PAGE_SIZES"],
        "AJAX_MODE"                 => "Y",
        "AJAX_ID"                   => CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
        "ENABLE_NEXT_PAGE"          => $arResult["GRID_ENABLE_NEXT_PAGE"],
        "SHOW_ACTION_PANEL"         => true,
        "SHOW_CHECK_ALL_CHECKBOXES" => false,
        "SHOW_ROW_CHECKBOXES"       => false,
        "SHOW_ROW_ACTIONS_MENU"     => true,
        "SHOW_GRID_SETTINGS_MENU"   => true,
        "SHOW_NAVIGATION_PANEL"     => false,
        "SHOW_PAGINATION"           => false,
        "SHOW_SELECTED_COUNTER"     => true,
        "SHOW_TOTAL_COUNTER"        => true,
        "SHOW_PAGESIZE"             => true,
        "ALLOW_COLUMNS_SORT"        => true,
        "ALLOW_COLUMNS_RESIZE"      => true,
        "ALLOW_HORIZONTAL_SCROLL"   => true,
        "ALLOW_SORT"                => true,
        "ALLOW_PIN_HEADER"          => true,
    ),
    $component,
    array("HIDE_ICONS" => "Y")
);
?>
