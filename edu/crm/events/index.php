<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/crm/events/index.php");
$APPLICATION->SetTitle(GetMessage("CRM_TITLE"));
?><?$APPLICATION->IncludeComponent(
	"bitrix:crm.event.view",
	"",
	Array(
		"ENTITY_ID" => "",
		"EVENT_COUNT" => "20",
		"EVENT_ENTITY_LINK" => "Y",
		"PATH_TO_DEAL_SHOW" => "/edu/crm/deal/show/#deal_id#/",
		"PATH_TO_QUOTE_SHOW" => "/edu/crm/quote/show/#quote_id#/",
		"PATH_TO_CONTACT_SHOW" => "/edu/crm/contact/show/#contact_id#/",
		"PATH_TO_COMPANY_SHOW" => "/edu/crm/company/show/#company_id#/",
		"PATH_TO_LEAD_SHOW" => "/edu/crm/lead/show/#lead_id#/",
		"PATH_TO_USER_PROFILE" => "/edu/company/personal/user/#user_id#/"
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>