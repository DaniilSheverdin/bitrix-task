<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/about/career.php");
$APPLICATION->SetTitle(GetMessage("ABOUT_TITLE"));
?><?$APPLICATION->IncludeComponent(
	"bitrix:news",
	"web20",
	Array(
		"ADD_SECTIONS_CHAIN" => "Y",
		"AJAX_MODE" => "N",
		"AJAX_OPTION_HISTORY" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_SHADOW" => "Y",
		"AJAX_OPTION_STYLE" => "Y",
		"CACHE_FILTER" => "N",
		"CACHE_TIME" => "36000000",
		"CACHE_TYPE" => "A",
		"CHECK_DATES" => "Y",
		"COLOR_NEW" => "3E74E6",
		"COLOR_OLD" => "C0C0C0",
		"DETAIL_ACTIVE_DATE_FORMAT" => (LANGUAGE_ID=="en")?"F j, Y":((LANGUAGE_ID=="de")?"j F Y":"d.m.Y"),
		"DETAIL_DISPLAY_BOTTOM_PAGER" => "Y",
		"DETAIL_DISPLAY_TOP_PAGER" => "N",
		"DETAIL_FIELD_CODE" => array(),
		"DETAIL_PAGER_TEMPLATE" => "",
		"DETAIL_PAGER_TITLE" => GetMessage("ABOUT_DET_PAGE_TITLE"),
		"DETAIL_PROPERTY_CODE" => array(0=>"office",1=>"department"),
		"DISPLAY_AS_RATING" => "rating",
		"DISPLAY_BOTTOM_PAGER" => "Y",
		"DISPLAY_DATE" => "N",
		"DISPLAY_NAME" => "Y",
		"DISPLAY_PANEL" => "Y",
		"DISPLAY_PICTURE" => "N",
		"DISPLAY_PREVIEW_TEXT" => "Y",
		"DISPLAY_TOP_PAGER" => "N",
		"FONT_MAX" => "50",
		"FONT_MIN" => "10",
		"HIDE_LINK_WHEN_NO_DETAIL" => "Y",
		"IBLOCK_ID" => IBLOCK_ID_SERVICES_VACANCY,
		"IBLOCK_TYPE" => "services",
		"INCLUDE_IBLOCK_INTO_CHAIN" => "Y",
		"LIST_ACTIVE_DATE_FORMAT" => (LANGUAGE_ID=="en")?"F j, Y":((LANGUAGE_ID=="de")?"j F Y":"d.m.Y"),
		"LIST_FIELD_CODE" => array(),
		"LIST_PROPERTY_CODE" => array(),
		"META_DESCRIPTION" => "-",
		"META_KEYWORDS" => "-",
		"NEWS_COUNT" => "20",
		"PAGER_DESC_NUMBERING" => "N",
		"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000000",
		"PAGER_SHOW_ALWAYS" => "N",
		"PAGER_TEMPLATE" => "",
		"PAGER_TITLE" => GetMessage("ABOUT_PAGE_TITLE"),
		"PERIOD_NEW_TAGS" => "",
		"PREVIEW_TRUNCATE_LEN" => "",
		"SEF_MODE" => "N",
		"SET_TITLE" => "N",
		"SORT_BY1" => "ACTIVE_FROM",
		"SORT_BY2" => "SORT",
		"SORT_ORDER1" => "DESC",
		"SORT_ORDER2" => "ASC",
		"TAGS_CLOUD_ELEMENTS" => "150",
		"TAGS_CLOUD_WIDTH" => "100%",
		"USE_CATEGORIES" => "N",
		"USE_FILTER" => "N",
		"USE_PERMISSIONS" => "N",
		"USE_RATING" => "N",
		"USE_REVIEW" => "N",
		"USE_RSS" => "N",
		"USE_SEARCH" => "N",
		"VARIABLE_ALIASES" => Array("SECTION_ID"=>"SECTION_ID","ELEMENT_ID"=>"ID")
	)
);?>
<hr><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>