<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/services/help/novice.php");
$APPLICATION->SetTitle(GetMessage("SERVICES_TITLE"));
?>
<?=GetMessage("SERVICES_INFO", array("#SITE#" => "/mfc/"))?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>