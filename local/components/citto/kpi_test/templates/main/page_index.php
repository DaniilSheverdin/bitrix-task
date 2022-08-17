<?php
use Bitrix\Main\Localization\Loc;
$APPLICATION->SetTitle('KPI');?>
<div class="row">
  <div class="col-md-6 offset-md-3">
    <div class="kpi_menu">
      <div class="item disabled"><a href=""><p><?=Loc::getMessage('TITLE_SELF')?></p></a></div>
        <? if ($arResult['ACCESS_DEPARTMENT']): ?>
        <div class="item"><a href="<?=SITE_DIR?>test-kpi/computed_rules"><p><?=Loc::getMessage('TITLE_SET_RULES')?></p></a></div>
        <div class="item"><a href="<?=SITE_DIR?>test-kpi/staff_to_wp"><p><?=Loc::getMessage('TITLE_SET_STAFF_TO_WP')?></p></a></div>
        <div class="item"><a href="<?=SITE_DIR?>test-kpi/insert_data_dep"><p><?=Loc::getMessage('TITLE_SET_DATA')?></p></a></div>
        <?endif;?>
        <? if ($arResult['ACCESS_GOVERNMENT']): ?>
        <div class="item"><a href="<?=SITE_DIR?>test-kpi/send_data_gov"><p><?=Loc::getMessage('TITLE_SEND_DATA')?></p></a></div>
        <?endif;?>
      <div class="item"><a href="<?=SITE_DIR?>test-kpi/show_kpi"><p><?=Loc::getMessage('TITLE_INDICATORS')?></p></a></div>
      <div class="item"><a href="<?=SITE_DIR?>test-kpi/notify"><p><?=Loc::getMessage('TITLE_SET_NOTIFIES')?></p></a></div>
      <div class="item"><a href="<?=SITE_DIR?>test-kpi/access"><p><?=Loc::getMessage('TITLE_TRUST')?></p></a></div>
    </div>
  </div>
</div>