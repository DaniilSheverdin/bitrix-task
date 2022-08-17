<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/timeman/index.php");
$APPLICATION->SetTitle(GetMessage("COMPANY_TITLE"));
?><?$APPLICATION->IncludeComponent(
	"bitrix:intranet.absence.calendar",
	".default",
	array(
		"FILTER_NAME"				=> "absence",
		"FILTER_SECTION_CURONLY"	=> "N",
		"SITE_ID"					=> SITE_ID,
		"DEPARTMENT"				=> 58,
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>