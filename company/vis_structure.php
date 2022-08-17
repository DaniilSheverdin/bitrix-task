<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/company/vis_structure.php");
$APPLICATION->SetTitle(GetMessage("COMPANY_TITLE"));
$APPLICATION->AddChainItem(GetMessage("COMPANY_TITLE"), "vis_structure.php");
$APPLICATION->SetPageProperty("BodyClass", "page-one-column flexible-layout");
?>
<div style="width:1250px; overflow:auto;">
	<img width="1250" id="structure_image" src="/upload/structure_actual.png" />
</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>