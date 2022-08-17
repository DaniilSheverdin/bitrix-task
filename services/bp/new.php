<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Новый бизнес-процесс");
?><?$APPLICATION->IncludeComponent(
        "bitrix:crm.config.bp.types",
        "",
        Array(
                "BP_LIST_URL" => "?entity_id=#entity_id#",
                "BP_EDIT_URL" => "?entity_id=#entity_id#&bp_id=#bp_id#"
        )
);?><br><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>

