<<<<<<< HEAD
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/company/events.php");
$APPLICATION->SetTitle(GetMessage("COMPANY_TITLE"));
?>
<?$APPLICATION->IncludeComponent("bitrix:intranet.structure.events", ".default", Array(
	"PM_URL"	=>	"/citto/company/personal/messages/chat/#USER_ID#/",
	"STRUCTURE_PAGE"	=>	"/citto/company/structure.php",
	"PATH_TO_CONPANY_DEPARTMENT" => "/citto/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#",
	"PATH_TO_VIDEO_CALL" => "/citto/company/personal/video/#USER_ID#/",
	"STRUCTURE_FILTER"	=>	"structure",
	"NUM_USERS"	=>	"25",
	"NAV_TITLE"	=>	GetMessage("COMPANY_NAV_TITLE"),
	"SHOW_NAV_TOP"	=>	"N",
	"SHOW_NAV_BOTTOM"	=>	"Y",
	"USER_PROPERTY"	=>	array(
		0	=>	"PERSONAL_PHONE",
		1	=>	"UF_DEPARTMENT",		
		2	=>	"UF_PHONE_INNER",
		3	=>	"UF_SKYPE",
		4	=>	"PERSONAL_PHOTO",
	),
	"SHOW_FILTER"	=>	"Y"
	)
=======
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/company/events.php");
$APPLICATION->SetTitle(GetMessage("COMPANY_TITLE"));
?>
<?$APPLICATION->IncludeComponent("bitrix:intranet.structure.events", ".default", Array(
	"PM_URL"	=>	"/citto/company/personal/messages/chat/#USER_ID#/",
	"STRUCTURE_PAGE"	=>	"/citto/company/structure.php",
	"PATH_TO_CONPANY_DEPARTMENT" => "/citto/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#",
	"PATH_TO_VIDEO_CALL" => "/citto/company/personal/video/#USER_ID#/",
	"STRUCTURE_FILTER"	=>	"structure",
	"NUM_USERS"	=>	"25",
	"NAV_TITLE"	=>	GetMessage("COMPANY_NAV_TITLE"),
	"SHOW_NAV_TOP"	=>	"N",
	"SHOW_NAV_BOTTOM"	=>	"Y",
	"USER_PROPERTY"	=>	array(
		0	=>	"PERSONAL_PHONE",
		1	=>	"UF_DEPARTMENT",		
		2	=>	"UF_PHONE_INNER",
		3	=>	"UF_SKYPE",
		4	=>	"PERSONAL_PHOTO",
	),
	"SHOW_FILTER"	=>	"Y"
	)
>>>>>>> e0a0eba79 (init)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>