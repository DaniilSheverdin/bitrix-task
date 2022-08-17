<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Свободность услуг");
?>
<? $APPLICATION->IncludeComponent(
    "mfc:services.free",
    "",
    array()
); ?>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
