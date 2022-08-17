<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (SITE_TEMPLATE_ID !== "bitrix24")
	return;

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/.left.menu_ext.php");
	
if (!CModule::IncludeModule("socialnetwork"))
	return;

$arUserActiveFeatures = CSocNetFeatures::GetActiveFeatures(SONET_ENTITY_USER, $GLOBALS["USER"]->GetID());
GLOBAL $USER;
$USER_ID = $USER->GetID();

$aMenuB24 = array();
	
$aMenuB24[] = Array(
	GetMessage("LEFT_MENU_LIVE_FEED"),
	"/index.php",
	Array(),
	Array("name" => "live_feed", "counter_id" => "live-feed", "menu_item_id"=>"menu_live_feed"),
	""
);
	
if ($GLOBALS["USER"]->IsAuthorized())
{
	$arSocNetFeaturesSettings = CSocNetAllowed::GetAllowedFeatures();

	if (CModule::IncludeModule("im") && SITE_TEMPLATE_ID == "bitrix24")
		$aMenuB24[] = Array(
			GetMessage("LEFT_MENU_IM_MESSENGER"),
"/online/",
			Array(),
			Array("counter_id" => "im-message", "menu_item_id"=>"menu_im_messenger"),
			"CBXFeatures::IsFeatureEnabled('WebMessenger')"
		);

	if (
		array_key_exists("tasks", $arSocNetFeaturesSettings)
		&& array_key_exists("allowed", $arSocNetFeaturesSettings["tasks"])
		&& in_array(SONET_ENTITY_USER, $arSocNetFeaturesSettings["tasks"]["allowed"])
		&& in_array("tasks", $arUserActiveFeatures)
	)
		$aMenuB24[] = Array(
			GetMessage("LEFT_MENU_TASKS"),
			"/company/personal/user/".$USER_ID."/tasks/",
			Array(),
			Array("name" => "tasks", "counter_id" => "tasks_total", "menu_item_id" => "menu_tasks"),
			"CBXFeatures::IsFeatureEnabled('Tasks')"
		);
	if (
		array_key_exists("calendar", $arSocNetFeaturesSettings)	
		&& array_key_exists("allowed", $arSocNetFeaturesSettings["calendar"])
		&& in_array(SONET_ENTITY_USER, $arSocNetFeaturesSettings["calendar"]["allowed"])
		&& in_array("calendar", $arUserActiveFeatures)
	)
		$aMenuB24[] = Array(
			GetMessage("LEFT_MENU_CALENDAR"),
			"/company/personal/user/".$USER_ID."/calendar/",
			Array(),
			array("menu_item_id"=>"menu_calendar", "counter_id" => "calendar"),
			"CBXFeatures::IsFeatureEnabled('Calendar')"
		);
	if (
		CModule::IncludeModule("disk") && $GLOBALS["USER"]->IsAuthorized()
		&& array_key_exists("files", $arSocNetFeaturesSettings)	
		&& array_key_exists("allowed", $arSocNetFeaturesSettings["files"])
		&& in_array(SONET_ENTITY_USER, $arSocNetFeaturesSettings["files"]["allowed"])
		&& in_array("files", $arUserActiveFeatures)
	)
		$aMenuB24[] = Array(
			GetMessage("LEFT_MENU_DISC"),
			"/company/personal/user/".$USER_ID."/disk/path/",
			Array(),
			Array(),
			"CBXFeatures::IsFeatureEnabled('PersonalFiles')"
		);
	if (
		CModule::IncludeModule("photogallery") 
		&& array_key_exists("photo", $arSocNetFeaturesSettings)	
		&& array_key_exists("allowed", $arSocNetFeaturesSettings["photo"])
		&& in_array(SONET_ENTITY_USER, $arSocNetFeaturesSettings["photo"]["allowed"])
		&& in_array("photo", $arUserActiveFeatures)	
	)
		$aMenuB24[] = Array(
			GetMessage("LEFT_MENU_PHOTO"),
			"/company/personal/user/".$USER_ID."/photo/",
			Array(),
			Array(),
			"CBXFeatures::IsFeatureEnabled('PersonalPhoto')"
		);
	if (
		CModule::IncludeModule("blog") 
		&& array_key_exists("blog", $arSocNetFeaturesSettings)
		&& array_key_exists("allowed", $arSocNetFeaturesSettings["blog"])
		&& in_array(SONET_ENTITY_USER, $arSocNetFeaturesSettings["blog"]["allowed"])
		&& in_array("blog", $arUserActiveFeatures)	
	)
		$aMenuB24[] = Array(
			GetMessage("LEFT_MENU_BLOG"),
			"/company/personal/user/".$USER_ID."/blog/",
			Array(),
			Array("counter_id" => "blog_post"),
			""
		);
	if (CModule::IncludeModule("intranet") && CIntranetUtils::IsExternalMailAvailable())
		$aMenuB24[] = Array(
			GetMessage("LEFT_MENU_MAIL"),
			"/company/personal/mail/",
			Array(),
			Array("counter_id" => "mail_unseen", "warning_link" => SITE_DIR.'company/personal/mail/?page=home', "warning_title" => GetMessage("LEFT_MENU_MAIL_SETTING"), "menu_item_id"=>"menu_external_mail"),
			""
		);
	if (CModule::IncludeModule("bizproc"))
		$aMenuB24[] = Array(
			GetMessage("LEFT_MENU_BP"),
			"/company/personal/bizproc/",
			Array(),
			Array("counter_id" => "bp_tasks"),
			"CBXFeatures::IsFeatureEnabled('BizProc')"
		);
	if (IsModuleInstalled("lists") && COption::GetOptionString("lists", "turnProcessesOn") == "Y")
		$aMenuB24[] = Array(
			GetMessage("LEFT_MENU_MY_PROCESS"),
			"/company/personal/processes/",
			Array(),
			Array("menu_item_id"=>"menu_my_processes"),
			""
		);
	if (CModule::IncludeModule("crm") && CCrmPerms::IsAccessEnabled())
		$aMenuB24[] = Array(
			GetMessage("LEFT_MENU_CRM"),
			"/crm/stream/",
			Array(),
			Array("counter_id" => "crm_cur_act", "menu_item_id"=>"menu_crm_favorite"),
			""
		);
}
$aMenuLinks = array_merge($aMenuLinks, $aMenuB24);
?><?
//htmls.docdesigner - start
if(IsModuleInstalled("htmls.docdesigner")){
	$aMenuDD[] = Array(
			'Конструктор документов',
			SITE_DIR."apps/htmls.docdesigner/",
			Array(),
			Array(),
			""
	);
	$aMenuLinks = array_merge($aMenuLinks, $aMenuDD);
}
//htmls.docdesigner - end
?>