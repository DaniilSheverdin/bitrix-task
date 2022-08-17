<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->IncludeComponent("bitrix:sender.contact", ".default", array(
	'SEF_FOLDER' => '/mfc/marketing/contact/',
));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");