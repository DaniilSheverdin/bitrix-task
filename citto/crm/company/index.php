<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/crm/company/index.php");
$APPLICATION->SetTitle(GetMessage("CRM_TITLE"));
?><?$APPLICATION->IncludeComponent(
	"bitrix:crm.company",
	"",
	Array(
		"SEF_MODE" => "Y",
		"PATH_TO_LEAD_SHOW" => "/citto/crm/lead/show/#lead_id#/",
		"PATH_TO_LEAD_EDIT" => "/citto/crm/lead/edit/#lead_id#/",
		"PATH_TO_LEAD_CONVERT" => "/citto/crm/lead/convert/#lead_id#/",		
		"PATH_TO_CONTACT_SHOW" => "/citto/crm/contact/show/#contact_id#/",
		"PATH_TO_CONTACT_EDIT" => "/citto/crm/contact/edit/#contact_id#/",
		"PATH_TO_DEAL_SHOW" => "/citto/crm/deal/show/#deal_id#/",
		"PATH_TO_DEAL_EDIT" => "/citto/crm/deal/edit/#deal_id#/",
		"PATH_TO_INVOICE_SHOW" => "/citto/crm/invoice/show/#invoice_id#/",
		"PATH_TO_INVOICE_EDIT" => "/citto/crm/invoice/edit/#invoice_id#/",
		"PATH_TO_USER_PROFILE" => "/citto/company/personal/user/#user_id#/",
		"ELEMENT_ID" => $_REQUEST["company_id"],
		"SEF_FOLDER" => "/citto/crm/company/",
		"SEF_URL_TEMPLATES" => Array(
			"index" => "index.php",
			"list" => "list/",
			"import" => "import/",
			"edit" => "edit/#company_id#/",
			"show" => "show/#company_id#/",
			"dedupe" => "dedupe/"
		),
		"VARIABLE_ALIASES" => Array(
			"index" => Array(),
			"list" => Array(),
			"import" => Array(),
			"edit" => Array(),
			"show" => Array(),
			"dedupe" => Array()
		)
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>