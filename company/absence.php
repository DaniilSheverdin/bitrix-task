<<<<<<< HEAD
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/company/absence.php");
$APPLICATION->SetTitle(GetMessage("COMPANY_TITLE"));
?><?$APPLICATION->IncludeComponent("bitrix:intranet.absence.calendar", ".default", Array(
	"FILTER_NAME"	=>	"absence",
	"FILTER_SECTION_CURONLY"	=>	"N"
	)
=======
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/company/absence.php");
$APPLICATION->SetTitle(GetMessage("COMPANY_TITLE"));
?><?$APPLICATION->IncludeComponent("bitrix:intranet.absence.calendar", ".default", Array(
	"FILTER_NAME"	=>	"absence",
	"FILTER_SECTION_CURONLY"	=>	"N"
	)
>>>>>>> e0a0eba79 (init)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>