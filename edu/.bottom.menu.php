<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/.bottom.menu.php");

$aMenuLinks = Array(
	Array(
		GetMessage("BOTTOM_MENU_PRINT_VERSION"),
		"#print", 
		Array(),
		Array(),
		""
	),
	Array(
		GetMessage("BOTTOM_MENU_CONTACTS"),
		"/edu/about/company/contacts.php", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		GetMessage("BOTTOM_MENU_SEARCH"),
		"/edu/search/", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		GetMessage("BOTTOM_MENU_MAP"),
		"/edu/search/map.php", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		GetMessage("BOTTOM_MENU_HELP"),
		"/edu/services/help/", 
		Array(), 
		Array(), 
		"" 
	),
);
?>