<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/crm/configs/mailtemplate/index.php");
$APPLICATION->SetTitle(GetMessage("CRM_TITLE"));
$APPLICATION->IncludeComponent(
	'bitrix:crm.mail_template', 
	'', 
	array(
		"SEF_MODE" => "Y",
		"SEF_FOLDER" => "/gusc/crm/configs/mailtemplate/",
	),
	false
); 
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>