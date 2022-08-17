<?php
use Bitrix\Main\Localization\Loc;
$APPLICATION->SetTitle(Loc::getMessage('TITLE_SET_SALARY'));
?>
<div class="row">
  <div class="col-12">
    <div class="kpi_rules">
      <div class="row mt-5"></div>

      <? if ($arResult['WORK_POSITIONS']): ?>
        <? foreach ($arResult['WORK_POSITIONS'] as $key => $value): ?>
          <div class="row ml-5 mt-2">
            <div class="col-5">
              <div class="link"><?=$value['NAME']?></div>
            </div>
            <div class="col-2">
              <div class="ui-ctl ui-ctl-textbox ui-ctl-xs">
                <input type="number" data-id="<?=$key?>" class="ui-ctl-element" name="WP_SALARY" value="<?=$value['SALARY']?>">
              </div>
            </div>
          </div>
        <?endforeach;?>
      <?endif;?>

    </div>
    <div id="actions-work-positions-salary" class="actions"></div>
    <div class="actions-messages">
      <div>Изменения сохранены</div>
    </div>
  </div>
</div>
