<<<<<<< HEAD
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/company/vis_structure.php");
$APPLICATION->SetTitle(GetMessage("COMPANY_TITLE"));
$APPLICATION->AddChainItem(GetMessage("COMPANY_TITLE"), "vis_structure.php");
?>
<?
$APPLICATION->IncludeComponent("bitrix:intranet.structure.visual", "new_vis", array(
	"DETAIL_URL" => "/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#",
	"PROFILE_URL" => "/company/personal/user/#ID#/",
	"PM_URL" => "/company/personal/messages/chat/#ID#/",
	"NAME_TEMPLATE" => "",
	"USE_USER_LINK" => "Y",
	"MAX_DEPTH"=>"10"
	),
	false
);
?>
=======
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/company/vis_structure.php");
$APPLICATION->SetTitle(GetMessage("COMPANY_TITLE"));
$APPLICATION->AddChainItem(GetMessage("COMPANY_TITLE"), "vis_structure.php");
?>
<?
$APPLICATION->IncludeComponent("bitrix:intranet.structure.visual", "new_vis", array(
	"DETAIL_URL" => "/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#",
	"PROFILE_URL" => "/company/personal/user/#ID#/",
	"PM_URL" => "/company/personal/messages/chat/#ID#/",
	"NAME_TEMPLATE" => "",
	"USE_USER_LINK" => "Y",
	"MAX_DEPTH"=>"10"
	),
	false
);
?>
>>>>>>> e0a0eba79 (init)
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>