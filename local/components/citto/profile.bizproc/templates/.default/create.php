<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}

$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass.' ' : '').'no-all-paddings no-background');
\Bitrix\Main\UI\Extension::load(array("ui.buttons", "ui.alerts", "ui.tooltip", "ui.hint"));
\CJSCore::Init("loader");
?>
<div class="my-process-container">
<?$APPLICATION->IncludeComponent(
    "bitrix:lists.lists",
    "bp_users_page",
    array(
        "IBLOCK_TYPE_ID"        => "bitrix_processes",
        "CACHE_TYPE"            => "N",
        "SET_TITLE"             => "N",
        'USER_ID'               => $arParams['ID'],
        "CACHE_TIME"            => 0,
        "LINE_ELEMENT_COUNT"    => "3",
        'PROCEED_URL'           => SITE_DIR."bizproc/processes/?livefeed=y&list_id=#IBLOCK_ID#&element_id=0",
        'BP_S'                  => []
    ),
    $component,
    array("HIDE_ICONS" => "Y")
);?>
</div>
