<<<<<<< HEAD
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/services/help/novice.php");
$APPLICATION->SetTitle(GetMessage("SERVICES_TITLE"));
?>
<?=GetMessage("SERVICES_INFO", array("#SITE#" => "/citto/"))?>
=======
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/services/help/novice.php");
$APPLICATION->SetTitle(GetMessage("SERVICES_TITLE"));
?>
<?=GetMessage("SERVICES_INFO", array("#SITE#" => "/citto/"))?>
>>>>>>> e0a0eba79 (init)
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>