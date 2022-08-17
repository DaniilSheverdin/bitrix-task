<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle('ОМНИ статистика');
?>
<?$APPLICATION->IncludeComponent(
	"serg:omni.tracker", "",
	Array(
		"AJAX_MODE" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"AJAX_OPTION_HISTORY" => "N",
	),
	false
);
?>
  <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
