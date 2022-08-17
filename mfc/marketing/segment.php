<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->IncludeComponent("bitrix:sender.segment", ".default", array(
	'SEF_FOLDER' => '/mfc/marketing/segment/',
	'PATH_TO_CONTACT_LIST' => '/mfc/marketing/contact/list/',
	'PATH_TO_CONTACT_IMPORT' => '/mfc/marketing/contact/import/',
	'ONLY_CONNECTOR_FILTERS' => true,
));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>