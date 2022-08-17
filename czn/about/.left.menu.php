<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/about/.left.menu.php");
$aMenuLinks = Array(
	Array(
		GetMessage("ABOUT_MENU_OFFICIAL"),
		"/czn/about/index.php", 
		Array("/czn/about/official.php"), 
		Array(), 
		"" 
	),
	Array(
		GetMessage("ABOUT_MENU_LIFE"),
		"/czn/about/life.php", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		GetMessage("ABOUT_MENU_ABOUT"),
		"/czn/about/company/", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		GetMessage("ABOUT_MENU_PHOTO"),
		"/czn/about/gallery/", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('CompanyPhoto')" 
	),
	Array(
		GetMessage("ABOUT_MENU_VIDEO"),
		"/czn/about/media.php", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('CompanyVideo')" 
	),
	Array(
		GetMessage("ABOUT_MENU_CAREER"),
		"/czn/about/career.php", 
		Array("/czn/about/resume.php"), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('CompanyCareer')" 
	),
	// Array(
	// 	GetMessage("ABOUT_MENU_NEWS"),
	// 	"/czn/about/business_news.php", 
	// 	Array(), 
	// 	Array(), 
	// 	"" 
	// ),
);
?>