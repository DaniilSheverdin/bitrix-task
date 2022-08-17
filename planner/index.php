<?
require($_SERVER['DOCUMENT_ROOT']."/bitrix/header.php");
?><?$APPLICATION->IncludeComponent(
    "citto:holiday.list",
    "",
    Array(
        "COUNT_DAYS" => "Y",
        "HR_GROUP_ID" => "0",
        "IBLOCK_ID" => $_REQUEST["ID"],
        "IBLOCK_TYPE" => "news",
        "MANAGER_ADD_DAYS" => "Y",
        "SHOW_ALL" => "N",
        "SHOW_TIME" => "N"
    )
);?><?
require($_SERVER['DOCUMENT_ROOT']."/bitrix/footer.php");
?>
