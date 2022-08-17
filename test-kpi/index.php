<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<div class="container-fluid kpi-wrapper">
  <div class="kpi">
    <?$APPLICATION->IncludeComponent(
      "citto:kpi_test",
      "main",
      Array(

      ),
      false
    );
    ?>
  </div>
</div>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
