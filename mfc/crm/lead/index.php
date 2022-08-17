<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/crm/lead/index.php");
$APPLICATION->SetTitle(GetMessage("CRM_TITLE"));
?><?$APPLICATION->IncludeComponent(
	"bitrix:crm.lead",
	".default",
	Array(
		"SEF_MODE" => "Y",
		"PATH_TO_CONTACT_SHOW" => "/mfc/crm/contact/show/#contact_id#/",
		"PATH_TO_CONTACT_EDIT" => "/mfc/crm/contact/edit/#contact_id#/",
		"PATH_TO_COMPANY_SHOW" => "/mfc/crm/company/show/#company_id#/",
		"PATH_TO_COMPANY_EDIT" => "/mfc/crm/company/edit/#company_id#/",
		"PATH_TO_DEAL_SHOW" => "/mfc/crm/deal/show/#deal_id#/",
		"PATH_TO_DEAL_EDIT" => "/mfc/crm/deal/edit/#deal_id#/",
		"PATH_TO_USER_PROFILE" => "/mfc/company/personal/user/#user_id#/",
		"PATH_TO_PRODUCT_EDIT" => "/mfc/crm/product/edit/#product_id#/",
		"PATH_TO_PRODUCT_SHOW" => "/mfc/crm/product/show/#product_id#/",
		"ELEMENT_ID" => $_REQUEST["lead_id"],
		"SEF_FOLDER" => "/mfc/crm/lead/",
		"SEF_URL_TEMPLATES" => Array(
			"index" => "index.php",
			"list" => "list/",
			"edit" => "edit/#lead_id#/",
			"show" => "show/#lead_id#/",
			"convert" => "convert/#lead_id#/",
			"import" => "import/",
			"service" => "service/",
			"dedupe" => "dedupe/"
		),
		"VARIABLE_ALIASES" => Array(
			"index" => Array(),
			"list" => Array(),
			"edit" => Array(),
			"show" => Array(),
			"convert" => Array(),
			"import" => Array(),
			"service" => Array(),
			"dedupe" => Array()
		)
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>