<?
$aMenuLinks = Array(
	Array(
		"CRM", 
		"/gusc/crm/", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('crm') && CModule::IncludeModule('crm') && CCrmPerms::IsAccessEnabled()" 
	)
);
?>