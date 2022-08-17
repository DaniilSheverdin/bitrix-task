<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->IncludeComponent("bitrix:sender.letter", ".default", array(
	'SEF_FOLDER' => '/czn/marketing/letter/',
	'PATH_TO_SEGMENT_ADD' => '/czn/marketing/segment/edit/0/',
	'PATH_TO_SEGMENT_EDIT' => '/czn/marketing/segment/edit/#id#/',
));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");