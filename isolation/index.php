<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
?>

<div class="migrations">
<?$APPLICATION->IncludeComponent(
    "serg:super.component",
    "migration.docs",
    Array(),
    false
);
?>

</div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
