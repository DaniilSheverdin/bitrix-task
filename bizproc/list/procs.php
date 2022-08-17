<?
define('IBLOCK_TYPE',"bitrix_processes");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");



$APPLICATION->IncludeComponent("bitrix:lists.lists", "bp_users_page", array(
	"IBLOCK_TYPE_ID" => "bitrix_processes",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "3600",
	"LINE_ELEMENT_COUNT" => "3",
	'PROCEED_URL'=>SITE_DIR."bizproc/processes/?livefeed=y&list_id=#IBLOCK_ID#&element_id=0",
	'BP_S'=> []
	),
	false
);
?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>