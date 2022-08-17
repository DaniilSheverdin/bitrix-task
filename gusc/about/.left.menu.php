<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/about/.left.menu.php");
$aMenuLinks = Array(
	Array(
		GetMessage("ABOUT_MENU_OFFICIAL"),
		"/gusc/about/index.php", 
		Array("/gusc/about/official.php"), 
		Array(), 
		"" 
	),
	Array(
		GetMessage("ABOUT_MENU_LIFE"),
		"/gusc/about/life.php", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		GetMessage("ABOUT_MENU_ABOUT"),
		"/gusc/about/company/", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		GetMessage("ABOUT_MENU_PHOTO"),
		"/gusc/about/gallery/", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('CompanyPhoto')" 
	),
	Array(
		GetMessage("ABOUT_MENU_VIDEO"),
		"/gusc/about/media.php", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('CompanyVideo')" 
	),
	Array(
		GetMessage("ABOUT_MENU_CAREER"),
		"/gusc/about/career.php", 
		Array("/gusc/about/resume.php"), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('CompanyCareer')" 
	),
	// Array(
	// 	GetMessage("ABOUT_MENU_NEWS"),
	// 	"/gusc/about/business_news.php", 
	// 	Array(), 
	// 	Array(), 
	// 	"" 
	// ),
);
?>