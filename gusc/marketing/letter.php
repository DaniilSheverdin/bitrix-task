<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->IncludeComponent("bitrix:sender.letter", ".default", array(
	'SEF_FOLDER' => '/gusc/marketing/letter/',
	'PATH_TO_SEGMENT_ADD' => '/gusc/marketing/segment/edit/0/',
	'PATH_TO_SEGMENT_EDIT' => '/gusc/marketing/segment/edit/#id#/',
));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");