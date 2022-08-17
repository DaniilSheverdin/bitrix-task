<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/crm/deal/index.php");
$APPLICATION->SetTitle(GetMessage("CRM_TITLE"));
?><?$APPLICATION->IncludeComponent(
	"bitrix:crm.deal",
	"",
	Array(
		"SEF_MODE" => "Y",
		"PATH_TO_CONTACT_SHOW" => "/citto/crm/contact/show/#contact_id#/",
		"PATH_TO_CONTACT_EDIT" => "/citto/crm/contact/edit/#contact_id#/",
		"PATH_TO_COMPANY_SHOW" => "/citto/crm/company/show/#company_id#/",
		"PATH_TO_COMPANY_EDIT" => "/citto/crm/company/edit/#company_id#/",
		"PATH_TO_INVOICE_SHOW" => "/citto/crm/invoice/show/#invoice_id#/",
		"PATH_TO_INVOICE_EDIT" => "/citto/crm/invoice/edit/#invoice_id#/",
		"PATH_TO_LEAD_SHOW" => "/citto/crm/lead/show/#lead_id#/",
		"PATH_TO_LEAD_EDIT" => "/citto/crm/lead/edit/#lead_id#/",
		"PATH_TO_LEAD_CONVERT" => "/citto/crm/lead/convert/#lead_id#/",
		"PATH_TO_USER_PROFILE" => "/citto/company/personal/user/#user_id#/",
		"PATH_TO_PRODUCT_EDIT" => "/citto/crm/product/edit/#product_id#/",
		"PATH_TO_PRODUCT_SHOW" => "/citto/crm/product/show/#product_id#/",
		"ELEMENT_ID" => $_REQUEST["deal_id"],
		"SEF_FOLDER" => "/citto/crm/deal/",
		"SEF_URL_TEMPLATES" => Array(
			"index" => "index.php",
			"list" => "list/",
			"edit" => "edit/#deal_id#/",
			"show" => "show/#deal_id#/"
		),
		"VARIABLE_ALIASES" => Array(
			"index" => Array(),
			"list" => Array(),
			"edit" => Array(),
			"show" => Array(),
		)
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>