<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/crm/company/index.php");
$APPLICATION->SetTitle(GetMessage("CRM_TITLE"));
?><?$APPLICATION->IncludeComponent(
	"bitrix:crm.company",
	"",
	Array(
		"SEF_MODE" => "Y",
		"PATH_TO_LEAD_SHOW" => "/mfc/crm/lead/show/#lead_id#/",
		"PATH_TO_LEAD_EDIT" => "/mfc/crm/lead/edit/#lead_id#/",
		"PATH_TO_LEAD_CONVERT" => "/mfc/crm/lead/convert/#lead_id#/",		
		"PATH_TO_CONTACT_SHOW" => "/mfc/crm/contact/show/#contact_id#/",
		"PATH_TO_CONTACT_EDIT" => "/mfc/crm/contact/edit/#contact_id#/",
		"PATH_TO_DEAL_SHOW" => "/mfc/crm/deal/show/#deal_id#/",
		"PATH_TO_DEAL_EDIT" => "/mfc/crm/deal/edit/#deal_id#/",
		"PATH_TO_INVOICE_SHOW" => "/mfc/crm/invoice/show/#invoice_id#/",
		"PATH_TO_INVOICE_EDIT" => "/mfc/crm/invoice/edit/#invoice_id#/",
		"PATH_TO_USER_PROFILE" => "/mfc/company/personal/user/#user_id#/",
		"ELEMENT_ID" => $_REQUEST["company_id"],
		"SEF_FOLDER" => "/mfc/crm/company/",
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