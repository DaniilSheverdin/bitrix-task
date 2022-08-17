<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/crm/webform/index.php");
$APPLICATION->SetTitle(GetMessage("CRM_TITLE"));
?><?$APPLICATION->IncludeComponent(
	"bitrix:crm.webform",
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
		"PATH_TO_WEB_FORM_FILL" => "/pub/form/#form_code#/#form_sec#/",
		"ELEMENT_ID" => $_REQUEST["id"],
		"SEF_FOLDER" => "/mfc/crm/webform/",
		"SEF_URL_TEMPLATES" => Array(
			"list" => "list/",
			"edit" => "edit/#id#/",
		),
		"VARIABLE_ALIASES" => Array(
			"list" => Array(),
			"edit" => Array(),
		)
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>