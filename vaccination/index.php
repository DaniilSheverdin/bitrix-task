<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<div class="vaccination-wrapper">
    <?$APPLICATION->IncludeComponent(
      "citto:vaccination",
      "main",
      Array(

      ),
      false
    );
    ?>
</div>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
