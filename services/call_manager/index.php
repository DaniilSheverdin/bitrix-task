<?php
define('NO_MB_CHECK', true);
define('NOT_CHECK_PERMISSIONS', true);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Звонки Губернатору ТО");

include_once $_SERVER['DOCUMENT_ROOT'] . '/local/vendor/autoload.php';
?>
<?
$APPLICATION->IncludeComponent(
    "citto:gubernator.edit",
    "edit",
    Array(
        "CACHE_TYPE" => "N",
        "IS_AJAX" => $_REQUEST['gub']['confirm'] == '1' ? "Y" : "N"
    )
);?>

<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
//require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_after.php");
?>