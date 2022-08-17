<<<<<<< HEAD
<?
require($_SERVER["DOCUMENT_ROOT"]."/mobile/headers.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

?>
<?$APPLICATION->IncludeComponent("bitrix:mobile.help", ".default", array(
	),
	false,
	Array("HIDE_ICONS" => "Y")
);?>
=======
<?
require($_SERVER["DOCUMENT_ROOT"]."/mobile/headers.php");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

?>
<?$APPLICATION->IncludeComponent("bitrix:mobile.help", ".default", array(
	),
	false,
	Array("HIDE_ICONS" => "Y")
);?>
>>>>>>> e0a0eba79 (init)
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php")?>