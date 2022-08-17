<?
define("NOT_CHECK_PERMISSIONS",true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Регистрация для Контроля поручений");
$APPLICATION->IncludeComponent(
	"citto:checkorders.register",
	"",
	Array(
		'DEPARTMENT_ID_OMSU'=>2331,
		'DEPARTMENT_ID_OTHER'=>2332,
		'IBLOCK_ID_ISPOLNITEL'=>508,


	));
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
