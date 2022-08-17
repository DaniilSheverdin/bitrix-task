<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/services/.left.menu.php");

$aMenuLinks = Array(
	Array(
		GetMessage("SERVICES_MENU_MEETING_ROOM"),
		"/gusc/services/index.php",
		Array("/gusc/services/res_c.php"),
		Array(),
		"CBXFeatures::IsFeatureEnabled('MeetingRoomBookingSystem')"
	),
	Array(
		GetMessage("SERVICES_MENU_IDEA"),
		"/gusc/services/idea/",
		Array(),
		Array(),
		"CBXFeatures::IsFeatureEnabled('Idea')"
	),
	Array(
		GetMessage("SERVICES_MENU_LISTS"),
		"/gusc/services/lists/",
		Array(),
		Array(),
		"CBXFeatures::IsFeatureEnabled('Lists')"
	),
	Array(
		GetMessage("SERVICES_MENU_REQUESTS"),
		"/gusc/services/requests/",
		Array(),
		Array(),
		(!IsModuleInstalled("form"))?"false":"CBXFeatures::IsFeatureEnabled('Requests')"
	),
	Array(
		GetMessage("SERVICES_MENU_LEARNING"),
		"/gusc/services/learning/",
		Array("/services/course.php"),
		Array(),
		"CBXFeatures::IsFeatureEnabled('Learning')"
	),
	Array(
		GetMessage("SERVICES_MENU_WIKI"),
		"/gusc/services/wiki/",
		Array(),
		Array(),
		"CBXFeatures::IsFeatureEnabled('Wiki')"
	),
	Array(
		GetMessage("SERVICES_MENU_CONTACT_CENTER"),
		"/gusc/services/contact_center/",
		Array(),
		Array(),
		""
	),
	Array(
		GetMessage("SERVICES_MENU_FAQ"),
		"/gusc/services/faq/",
		Array(),
		Array(),
		""
	),
	Array(
		GetMessage("SERVICES_MENU_VOTE"),
		"/gusc/services/votes.php",
		Array("/gusc/services/vote_new.php", "/gusc/services/vote_result.php"),
		Array(),
		"CBXFeatures::IsFeatureEnabled('Vote')"
	),
	Array(
		GetMessage("SERVICES_MENU_SUPPORT"),
		"/gusc/services/support.php?show_wizard=Y",
		Array("/gusc/services/support.php"),
		Array(),
		(!IsModuleInstalled("support"))?"false":"CBXFeatures::IsFeatureEnabled('Support')"
	),
	Array(
		GetMessage("SERVICES_MENU_LINKS"),
		"/gusc/services/links.php",
		Array(),
		Array(),
		"CBXFeatures::IsFeatureEnabled('WebLink')"
	),
	Array(
		GetMessage("SERVICES_MENU_SUBSCR"),
		"/gusc/services/subscr_edit.php",
		Array(),
		Array(),
		"CBXFeatures::IsFeatureEnabled('Subscribe')"
	),
	Array(
		GetMessage("SERVICES_MENU_EVENTLIST"),
		"/gusc/services/event_list.php",
		Array(),
		Array(),
		"CBXFeatures::IsFeatureEnabled('EventList')"
	),
	Array(
		GetMessage("SERVICES_MENU_SALARY"),
		"/gusc/services/salary/",
		Array(),
		Array(),
		"LANGUAGE_ID == 'ru' && CBXFeatures::IsFeatureEnabled('Salary')"
	),
	Array(
		GetMessage("SERVICES_MENU_BOARD"),
		"/gusc/services/board/",
		Array(),
		Array(),
		"CBXFeatures::IsFeatureEnabled('Board')"
	),
	Array(
		GetMessage("SERVICES_MENU_TELEPHONY"),
		"/gusc/services/telephony/",
		Array(),
		Array(),
		'CModule::IncludeModule("voximplant") && SITE_TEMPLATE_ID !== "bitrix24" && Bitrix\Voximplant\Security\Helper::isMainMenuEnabled()'
	),
	Array(
		GetMessage("SERVICES_MENU_OPENLINES"),
		"/gusc/services/openlines/",
		Array(),
		Array(),
		'CModule::IncludeModule("imopenlines") && SITE_TEMPLATE_ID !== "bitrix24" && Bitrix\ImOpenlines\Security\Helper::isMainMenuEnabled()'
	),
);
?>