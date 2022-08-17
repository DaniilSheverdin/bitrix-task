<?
$aMenuLinks = Array(
	Array(
		"CRM", 
		"/mfc/crm/", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('crm') && CModule::IncludeModule('crm') && CCrmPerms::IsAccessEnabled()" 
	)
);
?>