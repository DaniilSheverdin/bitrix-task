<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/docs/.left.menu.php");

$aMenuLinks = Array(
    Array(
		GetMessage("DOCS_MENU_ALL_DOCS"),
		"/mfc/docs/index.php", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('CommonDocuments')"
	),
	Array(
		GetMessage("DOCS_MENU_SHARED"),
		"/mfc/docs/shared/", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('CommonDocuments')"
	),
	Array(
		GetMessage("DOCS_MENU_SALE"),
		"/mfc/docs/sale/", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('CommonDocuments')"
	),
	Array(
		GetMessage("DOCS_MENU_MANAGE"),
		"/mfc/docs/manage/", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('CommonDocuments')"
	)
);
?>
