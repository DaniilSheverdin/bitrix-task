<?php
use Bitrix\Main\Localization\Loc;
$APPLICATION->SetTitle(Loc::getMessage('TITLE_NOTIFY'));
?>

<div class="row">
  <div class="col-12">
    <div class="kpi_rules">
      <form id="notify-form">

        <? foreach ($arResult['NOTIFIES'] as $section):  ?>
          <div class="row mt-5 ">
            <div class="col-md-12 mb-3">
              <div class="small"><h2><?=$section['NAME']?></h2></div>
            </div>
            <? foreach ($section['ITEMS'] as $id => $notify): ?>
            <?$intCount = 0?>
            <div class="col-md-8 mt-3"><?=$notify['NAME']?></div>
            <div class="col-md-4 my-3">
              <?if ($notify['NOTIFIES']['DEADLINE']):?>
              <div class="d-flex align-items-center">
                <div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-w30">
                  <div class="ui-ctl-after ui-ctl-icon-angle"></div>
                  <select class="ui-ctl-element" data-id="<?=$id?>" name="ATT_DEADLINE_DAY">
                    <? foreach ($notify['NOTIFIES']['DEADLINE']['ALL_VALUES'] as $value): ?>
                      <option <?=$notify['NOTIFIES']['DEADLINE']['VALUE'] == $value ? 'selected' : ''?> value="<?=$value?>"><?=$value?></option>
                    <?endforeach;?>
                  </select>
                </div>
                <div class="ml-2">Число каждого месяца</div>
              </div>
              <?endif;?>
              <?foreach ($notify['NOTIFIES']['VALUES'] as $key => $value):?>
              <div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-w100">
                <div class="ui-ctl-after ui-ctl-icon-angle"></div>
                <select class="ui-ctl-element" data-count="<?=$intCount?>" data-id="<?=$id?>" name="ATT_NOTIFY">
                  <? foreach ($notify['NOTIFIES']['VALUES_LIST'] as $keyAll => $valueAll): ?>
                    <option <?=$key == $keyAll ? 'selected' : ''?> value="<?=$keyAll?>"><?=$valueAll?></option>
                  <?endforeach;?>

                </select>
              </div>

                  <?$intCount++?>
              <?endforeach;?>
                <? if ($notify['NOTIFIES']['MULTIPLE'] == 'Y'): ?>
                  <div class="js-add-select add-select">Добавить</div>
                <?endif;?>
            </div>
            <?endforeach;?>
          </div>
        <?endforeach;?>
      </form>

    </div>
  </div>
  <div id="notifies" class="actions"></div>
  <div class="actions-messages">
    <div>Изменения сохранены</div>
  </div>
</div>
