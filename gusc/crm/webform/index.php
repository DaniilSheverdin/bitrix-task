<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/crm/webform/index.php");
$APPLICATION->SetTitle(GetMessage("CRM_TITLE"));
?><?$APPLICATION->IncludeComponent(
	"bitrix:crm.webform",
	".default",
	Array(
		"SEF_MODE" => "Y",
		"PATH_TO_CONTACT_SHOW" => "/gusc/crm/contact/show/#contact_id#/",
		"PATH_TO_CONTACT_EDIT" => "/gusc/crm/contact/edit/#contact_id#/",
		"PATH_TO_COMPANY_SHOW" => "/gusc/crm/company/show/#company_id#/",
		"PATH_TO_COMPANY_EDIT" => "/gusc/crm/company/edit/#company_id#/",
		"PATH_TO_DEAL_SHOW" => "/gusc/crm/deal/show/#deal_id#/",
		"PATH_TO_DEAL_EDIT" => "/gusc/crm/deal/edit/#deal_id#/",
		"PATH_TO_USER_PROFILE" => "/gusc/company/personal/user/#user_id#/",
		"PATH_TO_WEB_FORM_FILL" => "/pub/form/#form_code#/#form_sec#/",
		"ELEMENT_ID" => $_REQUEST["id"],
		"SEF_FOLDER" => "/gusc/crm/webform/",
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