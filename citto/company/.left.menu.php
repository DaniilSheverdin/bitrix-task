<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/company/.left.menu.php");
$aMenuLinks = Array(
	Array(
		GetMessage("COMPANY_MENU_STRUCTURE"),
		"/citto/company/vis_structure.php",
		Array(),
		Array(),
		""
	),
	Array(
		GetMessage("COMPANY_MENU_EMPLOYEES"),
		"/citto/company/index.php",
		Array(),
		Array(),
		""
	),
	Array(
		GetMessage("COMPANY_MENU_TELEPHONES"),
		"/citto/company/telephones.php",
		Array(),
		Array(),
		""
	),
	Array(
		GetMessage("COMPANY_MENU_EVENTS"),
		"/citto/company/events.php",
		Array(),
		Array(),
		"CBXFeatures::IsFeatureEnabled('StaffChanges')"
	),
	Array(
		GetMessage("COMPANY_MENU_REPORT"),
		"/citto/company/report.php",
		Array(),
		Array(),
		"IsModuleInstalled('tasks')"
	),
	Array(
		GetMessage("COMPANY_MENU_LEADERS"),
		"/citto/company/leaders.php",
		Array(),
		Array(),
		""
	),
	Array(
		GetMessage("COMPANY_MENU_BIRTHDAYS"),
		"/citto/company/birthdays.php",
		Array(),
		Array(),
		""
	),
	Array(
		GetMessage("COMPANY_MENU_GALLERY"),
		"/citto/company/gallery/",
		Array(),
		Array(),
		"CBXFeatures::IsFeatureEnabled('Gallery')"
	),
    Array(
        "ОМНИ трекер",
        "/company/omni-tracker.php",
        Array(),
        Array(),
        ""
    )
);
?>