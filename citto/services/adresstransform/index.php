<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Нормализатор по МО");
?>

<?$APPLICATION->IncludeComponent("citto:adrestransform", "", array(
	"CACHE_TYPE"	=>	"N",
	"CACHE_TIME"	=>	"86400",
	),
	false
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>