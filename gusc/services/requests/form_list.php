<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Список заявок");
?>
<?$APPLICATION->IncludeComponent("bitrix:form.result.list", "intranet", array(
	"WEB_FORM_ID" => $_REQUEST["WEB_FORM_ID"],
	"SEF_MODE" => "N",
	"SEF_FOLDER" => "/gusc/services/requests/",
	"VIEW_URL" => "form_view.php",
	"EDIT_URL" => "form_edit.php",
	"NEW_URL" => "index.php",
	"SHOW_ADDITIONAL" => "N",
	"SHOW_ANSWER_VALUE" => "N",
	"SHOW_STATUS" => "Y",
	"NOT_SHOW_FILTER" => "",
	"NOT_SHOW_TABLE" => "",
	"CHAIN_ITEM_TEXT" => "",
	"CHAIN_ITEM_LINK" => ""
	),
	false
);?>
<?
  $aPerm = CForm::GetPermission($_REQUEST["WEB_FORM_ID"]);
?>
<?
if($aPerm >= 20) {
?>
<p><a target="_blank" href="<?=$APPLICATION->GetCurDir()?>xls_list.php?WEB_FORM_ID=<?=$_REQUEST["WEB_FORM_ID"]?>">Скачать отчет по заявкам</a></p>
<?php } ?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>