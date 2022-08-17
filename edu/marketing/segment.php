<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->IncludeComponent("bitrix:sender.segment", ".default", array(
	'SEF_FOLDER' => '/edu/marketing/segment/',
	'SEF_MODE' => 'Y',
	'PATH_TO_CONTACT_LIST' => '/edu/marketing/contact/list/',
	'PATH_TO_CONTACT_IMPORT' => '/edu/marketing/contact/import/',
	'ONLY_CONNECTOR_FILTERS' => true,
));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>