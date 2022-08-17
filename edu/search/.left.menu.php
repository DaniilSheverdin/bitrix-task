<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/search/.left.menu.php");

$aMenuLinks = Array(
	Array(
		GetMessage("SEARCH_MAIN"),
		"/edu/search/index.php", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		GetMessage("SEARCH_MAP"),
		"/edu/search/map.php", 
		Array(), 
		Array(), 
		"" 
	)
);
?>