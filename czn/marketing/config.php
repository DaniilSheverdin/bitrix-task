<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->IncludeComponent("bitrix:sender.config.limits", ".default", array(
	'SEF_FOLDER' => '/czn/marketing/config/',
));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");