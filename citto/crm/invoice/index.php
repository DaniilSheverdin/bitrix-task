<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/crm/invoice/index.php");
$APPLICATION->SetTitle(GetMessage("CRM_TITLE"));
?><?$APPLICATION->IncludeComponent(
	"bitrix:crm.invoice",
	"",
	Array(
		"SEF_MODE" => "Y",
		"PATH_TO_CONTACT_SHOW" => "/citto/crm/contact/show/#contact_id#/",
		"PATH_TO_CONTACT_EDIT" => "/citto/crm/contact/edit/#contact_id#/",
		"PATH_TO_COMPANY_SHOW" => "/citto/crm/company/show/#company_id#/",
		"PATH_TO_COMPANY_EDIT" => "/citto/crm/company/edit/#company_id#/",
		"PATH_TO_QUOTE_LIST" => "/citto/crm/quote/list/",
		"PATH_TO_QUOTE_SHOW" => "/citto/crm/quote/show/#quote_id#/",
		"PATH_TO_QUOTE_EDIT" => "/citto/crm/quote/edit/#quote_id#/",
		"PATH_TO_DEAL_SHOW" => "/citto/crm/deal/show/#deal_id#/",
		"PATH_TO_DEAL_EDIT" => "/citto/crm/deal/edit/#deal_id#/",
		"PATH_TO_LEAD_SHOW" => "/citto/crm/lead/show/#lead_id#/",
		"PATH_TO_LEAD_EDIT" => "/citto/crm/lead/edit/#lead_id#/",
		"PATH_TO_LEAD_CONVERT" => "/citto/crm/lead/convert/#lead_id#/",
		"PATH_TO_PRODUCT_EDIT" => "/citto/crm/product/edit/#product_id#/",
		"PATH_TO_PRODUCT_SHOW" => "/citto/crm/product/show/#product_id#/",
		"PATH_TO_USER_PROFILE" => "/citto/company/personal/user/#user_id#/",
		"ELEMENT_ID" => $_REQUEST["invoice_id"],
		"SEF_FOLDER" => "/citto/crm/invoice/",
		"SEF_URL_TEMPLATES" => Array(
			"index" => "index.php",
			"list" => "list/",
			"edit" => "edit/#invoice_id#/",
			"show" => "show/#invoice_id#/"
		),
		"VARIABLE_ALIASES" => Array(
			"index" => Array(),
			"list" => Array(),
			"edit" => Array(),
			"show" => Array(),
		)
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>