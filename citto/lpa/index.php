<?
require($_SERVER['DOCUMENT_ROOT']."/bitrix/header.php");
?><?$APPLICATION->IncludeComponent(
    "citto:lnpa",
    "",
    Array(
        "HR_GROUP_ID" => "0",
        "IBLOCK_ID" => $_REQUEST["ID"],
    )
);?><?
require($_SERVER['DOCUMENT_ROOT']."/bitrix/footer.php");
?>
