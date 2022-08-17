<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/crm/quote/index.php");
$APPLICATION->SetTitle(GetMessage("CRM_TITLE"));
?><?$APPLICATION->IncludeComponent(
	"bitrix:crm.quote",
	"",
	Array(
		"SEF_MODE" => "Y",
		"PATH_TO_CONTACT_SHOW" => "/czn/crm/contact/show/#contact_id#/",
		"PATH_TO_CONTACT_EDIT" => "/czn/crm/contact/edit/#contact_id#/",
		"PATH_TO_COMPANY_SHOW" => "/czn/crm/company/show/#company_id#/",
		"PATH_TO_COMPANY_EDIT" => "/czn/crm/company/edit/#company_id#/",
		"PATH_TO_DEAL_SHOW" => "/czn/crm/deal/show/#deal_id#/",
		"PATH_TO_DEAL_EDIT" => "/czn/crm/deal/edit/#deal_id#/",
		"PATH_TO_INVOICE_SHOW" => "/czn/crm/invoice/show/#invoice_id#/",
		"PATH_TO_INVOICE_EDIT" => "/czn/crm/invoice/edit/#invoice_id#/",
		"PATH_TO_LEAD_SHOW" => "/czn/crm/lead/show/#lead_id#/",
		"PATH_TO_LEAD_EDIT" => "/czn/crm/lead/edit/#lead_id#/",
		"PATH_TO_LEAD_CONVERT" => "/czn/crm/lead/convert/#lead_id#/",
		"PATH_TO_PRODUCT_EDIT" => "/czn/crm/product/edit/#product_id#/",
		"PATH_TO_PRODUCT_SHOW" => "/czn/crm/product/show/#product_id#/",
		"PATH_TO_USER_PROFILE" => "/company/personal/user/#user_id#/",
		"ELEMENT_ID" => $_REQUEST["quote_id"],
		"SEF_FOLDER" => "/czn/crm/quote/",
		"SEF_URL_TEMPLATES" => Array(
			"index" => "index.php",
			"list" => "list/",
			"edit" => "edit/#quote_id#/",
			"show" => "show/#quote_id#/"
		),
		"VARIABLE_ALIASES" => Array(
			"index" => Array(),
			"list" => Array(),
			"edit" => Array(),
			"show" => Array(),
		)
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>