<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/auth/.left.menu.php");

$aMenuLinks = Array(
	Array(
		GetMessage("AUTH_LOGIN"),
		"/edu/auth/index.php?login=yes", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		GetMessage("AUTH_REG"),
		"/edu/auth/index.php?register=yes", 
		Array(), 
		Array(), 
		"COption::GetOptionString(\"main\", \"new_user_registration\") == \"Y\"" 
	),
	Array(
		GetMessage("AUTH_FORGOT_PASS"),
		"/edu/auth/index.php?forgot_password=yes", 
		Array(), 
		Array(), 
		"" 
	)
);
?>