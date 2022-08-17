<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/docs/.left.menu.php");

$aMenuLinks = Array(
    Array(
		GetMessage("DOCS_MENU_ALL_DOCS"),
		"/czn/docs/index.php",
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('CommonDocuments')"
	),
	Array(
		GetMessage("DOCS_MENU_SHARED"),
		"/czn/docs/shared/",
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('CommonDocuments')"
	),
	Array(
		GetMessage("DOCS_MENU_SALE"),
		"/czn/docs/sale/",
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('CommonDocuments')"
	),
	Array(
		GetMessage("DOCS_MENU_MANAGE"),
		"/czn/docs/manage/",
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('CommonDocuments')"
	)
);
?>
