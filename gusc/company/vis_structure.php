<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/company/vis_structure.php");
$APPLICATION->SetTitle(GetMessage("COMPANY_TITLE"));
$APPLICATION->AddChainItem(GetMessage("COMPANY_TITLE"), "vis_structure.php");
?>
<?
$APPLICATION->IncludeComponent("bitrix:intranet.structure.visual", ".default", array(
	"DETAIL_URL" => "/gusc/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#",
	"PROFILE_URL" => "/gusc/company/personal/user/#ID#/",
	"PM_URL" => "/gusc/company/personal/messages/chat/#ID#/",
	"NAME_TEMPLATE" => "",
	"USE_USER_LINK" => "Y",
	"DEPARTMENT" => 3080,
	"REMOVE_DEPTH_LEVEL" => 3,
	),
	false
);
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>