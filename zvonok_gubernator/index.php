<?
define('NO_MB_CHECK', true);
define('NOT_CHECK_PERMISSIONS', true);

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
?>

<?$APPLICATION->IncludeComponent(
    "citto:gubernator.view",
    "call",
    Array(
        "CACHE_TYPE" => "N",
        "IS_AJAX" => isset($_REQUEST['IS_AJAX']) ? "Y" : "N"
    )
);?>
<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>
