<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Голосование");
?>
<? $APPLICATION->IncludeComponent(
    "citto:votes",
    "",
    array(
        "AJAX_MODE"           => "Y",
        "AJAX_OPTION_JUMP"    => "N",
        "AJAX_OPTION_STYLE"   => "Y",
        "AJAX_OPTION_HISTORY" => "N",
    )
); ?>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
