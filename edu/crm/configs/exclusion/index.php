<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

/** @var CAllMain $APPLICATION */
$APPLICATION->IncludeComponent(
	"bitrix:crm.exclusion",
	".default",
	['SEF_FOLDER' => '/edu/crm/configs/exclusion/',]
);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");