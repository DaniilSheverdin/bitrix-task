<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/crm/product/index.php");
global $APPLICATION;
$APPLICATION->SetTitle(GetMessage("CRM_TITLE"));
$APPLICATION->IncludeComponent(
	"bitrix:crm.product", 
	".default", 
	array(
		"SEF_MODE" => "Y",
		"SEF_FOLDER" => "/gusc/crm/product/"
	),
	false
);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");