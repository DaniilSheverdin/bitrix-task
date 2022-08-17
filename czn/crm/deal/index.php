<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/crm/deal/index.php");
$APPLICATION->SetTitle(GetMessage("CRM_TITLE"));
?><?$APPLICATION->IncludeComponent(
	"bitrix:crm.deal",
	"",
	Array(
		"SEF_MODE" => "Y",
		"PATH_TO_CONTACT_SHOW" => "/czn/crm/contact/show/#contact_id#/",
		"PATH_TO_CONTACT_EDIT" => "/czn/crm/contact/edit/#contact_id#/",
		"PATH_TO_COMPANY_SHOW" => "/czn/crm/company/show/#company_id#/",
		"PATH_TO_COMPANY_EDIT" => "/czn/crm/company/edit/#company_id#/",
		"PATH_TO_INVOICE_SHOW" => "/czn/crm/invoice/show/#invoice_id#/",
		"PATH_TO_INVOICE_EDIT" => "/czn/crm/invoice/edit/#invoice_id#/",
		"PATH_TO_LEAD_SHOW" => "/czn/crm/lead/show/#lead_id#/",
		"PATH_TO_LEAD_EDIT" => "/czn/crm/lead/edit/#lead_id#/",
		"PATH_TO_LEAD_CONVERT" => "/czn/crm/lead/convert/#lead_id#/",
		"PATH_TO_USER_PROFILE" => "/czn/company/personal/user/#user_id#/",
		"PATH_TO_PRODUCT_EDIT" => "/czn/crm/product/edit/#product_id#/",
		"PATH_TO_PRODUCT_SHOW" => "/czn/crm/product/show/#product_id#/",
		"ELEMENT_ID" => $_REQUEST["deal_id"],
		"SEF_FOLDER" => "/czn/crm/deal/",
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