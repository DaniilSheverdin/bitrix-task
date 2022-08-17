<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php"); ?>
<div class="container-fluid kpi-wrapper">
  <div class="kpi">
    <?$APPLICATION->IncludeComponent(
      "citto:kpi",
      "main",
      Array(
//        "AJAX_MODE" => 'Y',
//        "AJAX_OPTION_HISTORY" => "Y", //TODO отключить по завершении
//        "AJAX_OPTION_JUMP" => "Y",
//        "AJAX_OPTION_STYLE" => "Y",
      ),
      false
    );
    ?>
  </div>
</div>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
