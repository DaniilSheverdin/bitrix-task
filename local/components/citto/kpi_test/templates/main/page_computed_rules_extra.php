<?php
use Bitrix\Main\Localization\Loc;
?>
<? if ($arResult['ACCESS_DEPARTMENT']): ?>
<div class="row">
  <div class="col-12">
    <div class="kpi_rules">
      <div class="row ml-5">
        <div class="col-12">
          <div class="title mt-5"><h3><?=Loc::getMessage('TITLE_SET_RULES_EXT')?></h3></div>
        </div>
      </div>
      <div class="row ml-5">
        <div class="col-12">
          <div class="critical">
            <div class="row">
              <div class="col-12">
                <div class="title mt-4 subtitle"><?=Loc::getMessage('TITLE_SET_RULES_EXT_CRITICAL')?></div>
              </div>
            </div>
            <div class="critical_table mt-4">
              <? foreach ($arResult['RULES_EXTRA']['CRITICAL'] as $id => $rules): ?>
              <div class="row align-items-center mt-2">
                <div class="col-1 js-count"><?=$rules['LABEL']?></div>
                <div class="col-6"><?=$rules['NAME']?></div>
                <div class="col-2"><input class="js-critical-value" data-id="<?=$id?>" type="number" step="0.01" value="<?=$rules['VALUE']?>"></div>
              </div>
              <?endforeach;?>
            </div>
            <div class="mt-4" id="critical_add"></div>

            <div class="formula row align-items-center mt-4">
              <div class="col-auto"><label for="formula_critical"><?=Loc::getMessage('TITLE_FORMULA_CRITICAL')?></label></div>
              <div class="col"><select style="display: none" id="formula_critical" multiple placeholder="..."></select></div>
            </div>

          </div>
          <hr class="mt-5">
          <div class="kpi-progress">
            <div class="row">
              <div class="col-12">
                <div class="title mt-4 subtitle"><?=Loc::getMessage('TITLE_SET_RULES_EXT_PROGRESS')?></div>
              </div>
            </div>
            <div class="progress_table mt-4">
              <div class="row align-items-center mt-2">
                <div class="col-6 offset-1"><?=Loc::getMessage('TITLE_SET_RULES_EXT_PROGRESS_K')?></div>
                <div class="col-2"><input class="js-progress-value" type="number" step="0.01" value="<?=$arResult['RULES_EXTRA']['PROGRESS']['VALUE']?>"></div>
              </div>
            </div>

            <div class="formula row align-items-center mt-4">
              <div class="col-auto"><label for="formula_progress"><?=Loc::getMessage('TITLE_FORMULA_PROGRESS')?></label></div>
              <div class="col"><select style="display: none"  id="formula_progress" multiple placeholder="..."></select></div>
            </div>

          </div>

          <div id="actions-button-re" class="actions"></div>
          <div class="actions-messages">
            <div>Изменения сохранены</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>



<div style="display: none;" id="add-critical-content">
  <form id="form-critical-add" action="" class="form-kpi-add">
    <div class="row">
      <div class="col-12"><div class="title text-center">Добавление факта</div></div>
    </div>
    <div class="row mt-4 align-items-center">
      <div class="col-md-4"><label for=""><?=Loc::getMessage('FORM_CRITICAL_ADD_LABEL_NAME')?></label></div>
      <div class="col-md-8">
        <div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
          <input type="text" class="ui-ctl-element" name="NAME">
        </div>
      </div>
    </div>

    <div class="row mt-3 align-items-center">
      <div class="col-md-4"><label for=""><?=Loc::getMessage('FORM_CRITICAL_ADD_LABEL_VALUE')?></label></div>
      <div class="col-md-8">
        <div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
          <input type="number" max="100" maxlength="3" step="0.01" class="ui-ctl-element" name="ATT_FACT">
        </div>
      </div>
    </div>

    <div class="row mt-4 align-items-center justify-content-center">
      <div id="critical_actions"></div>
    </div>
  </form>
</div>
<?else:?>
  <div class="row"><div class="col-12"><h2 class="mt-5">Доступ запрещен</h2></div></div>
<?endif;?>
