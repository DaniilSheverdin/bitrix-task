<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION;
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/crm/stream/index.php");
$APPLICATION->SetTitle(GetMessage("CRM_TITLE"));

$APPLICATION->SetPageProperty("BodyClass", " page-one-column");
if(CModule::IncludeModule("crm") && CCrmPerms::IsAccessEnabled()):

	$currentUserPerms = CCrmPerms::GetCurrentUserPermissions();
	$canEdit = CCrmLead::CheckUpdatePermission(0, $currentUserPerms)
		|| CCrmContact::CheckUpdatePermission(0, $currentUserPerms)
		|| CCrmCompany::CheckUpdatePermission(0, $currentUserPerms)
		|| CCrmDeal::CheckUpdatePermission(0, $currentUserPerms);

	$APPLICATION->IncludeComponent(
		"bitrix:crm.control_panel",
		"",
		array(
			"ID" => "STREAM",
			"ACTIVE_ITEM_ID" => "STREAM",
			"PATH_TO_COMPANY_LIST" => "/citto/crm/company/",
			"PATH_TO_COMPANY_EDIT" => "/citto/crm/company/edit/#company_id#/",
			"PATH_TO_CONTACT_LIST" => "/citto/crm/contact/",
			"PATH_TO_CONTACT_EDIT" => "/citto/crm/contact/edit/#contact_id#/",
			"PATH_TO_DEAL_LIST" => "/citto/crm/deal/",
			"PATH_TO_DEAL_EDIT" => "/citto/crm/deal/edit/#deal_id#/",
			"PATH_TO_QUOTE_LIST" => "/citto/crm/quote/",
			"PATH_TO_QUOTE_EDIT" => "/citto/crm/quote/edit/#quote_id#/",
			"PATH_TO_INVOICE_LIST" => "/citto/crm/invoice/",
			"PATH_TO_INVOICE_EDIT" => "/citto/crm/invoice/edit/#invoice_id#/",
			"PATH_TO_LEAD_LIST" => "/citto/crm/lead/",
			"PATH_TO_LEAD_EDIT" => "/citto/crm/lead/edit/#lead_id#/",
			"PATH_TO_REPORT_LIST" => "/citto/crm/reports/report/",
			"PATH_TO_DEAL_FUNNEL" => "/citto/crm/reports/",
			"PATH_TO_EVENT_LIST" => "/citto/crm/events/",
			"PATH_TO_PRODUCT_LIST" => "/citto/crm/product/",
			"PATH_TO_SETTINGS" => "/citto/crm/configs/",
			"PATH_TO_SEARCH_PAGE" => "/citto/search/index.php?where=crm"
		)
	);

	// --> IMPORT RESPONSIBILITY SUBSCRIPTIONS
	$currentUserID = CCrmSecurityHelper::GetCurrentUserID();
	if($currentUserID > 0)
	{
		CCrmSonetSubscription::EnsureAllResponsibilityImported($currentUserID);
	}
	// <-- IMPORT RESPONSIBILITY SUBSCRIPTIONS
	$APPLICATION->IncludeComponent("bitrix:crm.entity.livefeed",
		"",
		array(
			"DATE_TIME_FORMAT" => "d.m.Y H:i:s",
			"CAN_EDIT" => $canEdit,
			"FORM_ID" => "",
			"PATH_TO_USER_PROFILE" => "/citto/company/personal/user/#user_id#/",
			"PATH_TO_GROUP" => "/citto/workgroups/group/#group_id#/",
			"PATH_TO_CONPANY_DEPARTMENT" => "/citto/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#"
		),
		null,
		array("HIDE_ICONS" => "Y")
	);
endif;
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>