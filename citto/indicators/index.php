<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
$APPLICATION->SetTitle('Показатели');
?>
<?$APPLICATION->IncludeComponent(
    "bitrix:catalog.smart.filter",
    "none_view",
    array(
        "COMPONENT_TEMPLATE" => "none_view",
        "IBLOCK_TYPE" => "indicators",
        "IBLOCK_ID" => IBLOCK_ID_INDICATORS_CATALOG,
        "SECTION_ID" => $_REQUEST['filter']['CATEGORY'][0],
        "SECTION_CODE" => "",
        "FILTER_NAME" => "arrFilter",
        "HIDE_NOT_AVAILABLE" => "N",
        "TEMPLATE_THEME" => "blue",
        "FILTER_VIEW_MODE" => "vertical",
        "DISPLAY_ELEMENT_COUNT" => "Y",
        "SEF_MODE" => "N",
        "CACHE_TYPE" => "A",
        "CACHE_TIME" => "36000000",
        "CACHE_GROUPS" => "Y",
        "SAVE_IN_SESSION" => "N",
        "INSTANT_RELOAD" => "N",
        "PAGER_PARAMS_NAME" => "arrPager",
        "PRICE_CODE" => array(
            0 => "BASE",
        ),
        "CONVERT_CURRENCY" => "Y",
        "XML_EXPORT" => "N",
        "SECTION_TITLE" => "-",
        "SECTION_DESCRIPTION" => "-",
        "POPUP_POSITION" => "left",
        "SEF_RULE" => "/examples/books/#SECTION_ID#/filter/#SMART_FILTER_PATH#/apply/",
        "SECTION_CODE_PATH" => "",
        "SMART_FILTER_PATH" => $_REQUEST["SMART_FILTER_PATH"],
        "CURRENCY_ID" => "RUB"
    ),
    false
);?>
<?$APPLICATION->IncludeComponent(
    'citto:indicators',
    '',
    [
        'IBLOCK_ID_ISPOLNITEL'      => IBLOCK_ID_ISPOLNITEL,
        'IBLOCK_ID_ORDERS'          => IBLOCK_ID_ORDERS,
        'IBLOCK_ID_ORDERS_COMMENT'  => IBLOCK_ID_ORDERS_COMMENT,
        'IBLOCK_ID_ORDERS_OBJECT'   => IBLOCK_ID_ORDERS_OBJECT,
        'IBLOCK_ID_ORDERS_THEME'    => IBLOCK_ID_ORDERS_THEME,
    ]
);?>
<?
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
