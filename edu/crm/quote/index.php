<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/crm/quote/index.php");
$APPLICATION->SetTitle(GetMessage("CRM_TITLE"));
?><?$APPLICATION->IncludeComponent(
	"bitrix:crm.quote",
	"",
	Array(
		"SEF_MODE" => "Y",
		"PATH_TO_CONTACT_SHOW" => "/edu/crm/contact/show/#contact_id#/",
		"PATH_TO_CONTACT_EDIT" => "/edu/crm/contact/edit/#contact_id#/",
		"PATH_TO_COMPANY_SHOW" => "/edu/crm/company/show/#company_id#/",
		"PATH_TO_COMPANY_EDIT" => "/edu/crm/company/edit/#company_id#/",
		"PATH_TO_DEAL_SHOW" => "/edu/crm/deal/show/#deal_id#/",
		"PATH_TO_DEAL_EDIT" => "/edu/crm/deal/edit/#deal_id#/",
		"PATH_TO_INVOICE_SHOW" => "/edu/crm/invoice/show/#invoice_id#/",
		"PATH_TO_INVOICE_EDIT" => "/edu/crm/invoice/edit/#invoice_id#/",
		"PATH_TO_LEAD_SHOW" => "/edu/crm/lead/show/#lead_id#/",
		"PATH_TO_LEAD_EDIT" => "/edu/crm/lead/edit/#lead_id#/",
		"PATH_TO_LEAD_CONVERT" => "/edu/crm/lead/convert/#lead_id#/",
		"PATH_TO_PRODUCT_EDIT" => "/edu/crm/product/edit/#product_id#/",
		"PATH_TO_PRODUCT_SHOW" => "/edu/crm/product/show/#product_id#/",
		"PATH_TO_USER_PROFILE" => "/company/personal/user/#user_id#/",
		"ELEMENT_ID" => $_REQUEST["quote_id"],
		"SEF_FOLDER" => "/edu/crm/quote/",
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