<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->IncludeComponent("bitrix:sender.config.role", ".default", array(
	'SEF_FOLDER' => '/mfc/marketing/config/role/',
));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");