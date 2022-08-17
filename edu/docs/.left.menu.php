<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/docs/.left.menu.php");

$aMenuLinks = Array(
    Array(
		GetMessage("DOCS_MENU_ALL_DOCS"),
		"/edu/docs/index.php", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('CommonDocuments')"
	),
	Array(
		GetMessage("DOCS_MENU_SHARED"),
		"/edu/docs/shared/", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('CommonDocuments')"
	),
	Array(
		GetMessage("DOCS_MENU_SALE"),
		"/edu/docs/sale/", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('CommonDocuments')"
	),
	Array(
		GetMessage("DOCS_MENU_MANAGE"),
		"/edu/docs/manage/", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('CommonDocuments')"
	)
);
?>
