<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->IncludeComponent("bitrix:sender.template", ".default", array(
	'SEF_FOLDER' => '/mfc/marketing/template/',
));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>