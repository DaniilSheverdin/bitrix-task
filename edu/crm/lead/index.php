<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/crm/lead/index.php");
$APPLICATION->SetTitle(GetMessage("CRM_TITLE"));
?><?$APPLICATION->IncludeComponent(
	"bitrix:crm.lead",
	".default",
	Array(
		"SEF_MODE" => "Y",
		"PATH_TO_CONTACT_SHOW" => "/edu/crm/contact/show/#contact_id#/",
		"PATH_TO_CONTACT_EDIT" => "/edu/crm/contact/edit/#contact_id#/",
		"PATH_TO_COMPANY_SHOW" => "/edu/crm/company/show/#company_id#/",
		"PATH_TO_COMPANY_EDIT" => "/edu/crm/company/edit/#company_id#/",
		"PATH_TO_DEAL_SHOW" => "/edu/crm/deal/show/#deal_id#/",
		"PATH_TO_DEAL_EDIT" => "/edu/crm/deal/edit/#deal_id#/",
		"PATH_TO_USER_PROFILE" => "/edu/company/personal/user/#user_id#/",
		"PATH_TO_PRODUCT_EDIT" => "/edu/crm/product/edit/#product_id#/",
		"PATH_TO_PRODUCT_SHOW" => "/edu/crm/product/show/#product_id#/",
		"ELEMENT_ID" => $_REQUEST["lead_id"],
		"SEF_FOLDER" => "/edu/crm/lead/",
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