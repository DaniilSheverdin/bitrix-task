<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->IncludeComponent("bitrix:sender.ads", ".default", array(
	'SEF_FOLDER' => '/czn/marketing/ads/',
	'PATH_TO_SEGMENT_ADD' => '/czn/marketing/segment/edit/0/',
	'PATH_TO_SEGMENT_EDIT' => '/czn/marketing/segment/edit/#id#/',
	'IS_ADS' => 'Y',
));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");