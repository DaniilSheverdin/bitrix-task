<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/about/.left.menu.php");
$aMenuLinks = Array(
	Array(
		GetMessage("ABOUT_MENU_OFFICIAL"),
		"/mfc/about/index.php", 
		Array("/mfc/about/official.php"), 
		Array(), 
		"" 
	),
	Array(
		GetMessage("ABOUT_MENU_LIFE"),
		"/mfc/about/life.php", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		GetMessage("ABOUT_MENU_ABOUT"),
		"/mfc/about/company/", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		GetMessage("ABOUT_MENU_PHOTO"),
		"/mfc/about/gallery/", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('CompanyPhoto')" 
	),
	Array(
		GetMessage("ABOUT_MENU_VIDEO"),
		"/mfc/about/media.php", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('CompanyVideo')" 
	),
	Array(
		GetMessage("ABOUT_MENU_CAREER"),
		"/mfc/about/career.php", 
		Array("/mfc/about/resume.php"), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('CompanyCareer')" 
	),
	// Array(
	// 	GetMessage("ABOUT_MENU_NEWS"),
	// 	"/mfc/about/business_news.php", 
	// 	Array(), 
	// 	Array(), 
	// 	"" 
	// ),
);
?>