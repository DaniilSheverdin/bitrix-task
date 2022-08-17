<?php
use Bitrix\Main\Localization\Loc;
$APPLICATION->SetTitle(Loc::getMessage('TITLE_SET_RULES'));
?>
<? if ($arResult['ACCESS_DEPARTMENT']): ?>
<div class="row">
  <div class="col-12">
    <div class="kpi_rules">
      <div class="row ml-5">
        <div class="col-auto">
          <div class="link mt-4">
            <a href="<?=SITE_DIR?>test-kpi/computed_rules_extra">
              <?=Loc::getMessage('TITLE_SET_COMPUTED_DOP')?>
            </a>
          </div>
        </div>
        <div class="col-auto">
          <div class="link mt-4">
            <?if (isset($_REQUEST['department']) && intval($_REQUEST['department']) > 0):?>
            <a href="<?=SITE_DIR?>test-kpi/set_salary?department=<?=$_REQUEST['department']?>">
              <?=Loc::getMessage('TITLE_SET_SALARY')?>
            </a>
            <?else:?>
              <span class="link-disabled">
                <?=Loc::getMessage('TITLE_SET_SALARY')?>
              </span>
            <?endif;?>
          </div>
        </div>
      </div>
      <div class="row mt-5">
        <div class="col-md-12 mb-3">
          <div class="small"><h2><?=Loc::getMessage('SELECT_DEPARTMENT')?></h2></div>
        </div>
        <div class="col-md-12">
          <div class="select-department">
              <select id="select-department" placeholder="..." name="department">
                <option></option>
                <? foreach ($arResult['DEPARTMENTS'] as $key => $value): ?>
                <option <?=$_REQUEST['department'] == $key ? 'selected' : ''?> value="<?=$key?>"><?=$value?></option>
                <?endforeach;?>
              </select>
          </div>

        </div>
      </div>
      <? if ($arResult['DEPARTMENT_DATA']): ?>
      <div class="data-table">
        <div class="row data-table_header">
          <div class="col-3">Функциональная единица</div>
          <div class="col-3">Количество сотрудников</div>
          <div class="col-4">Текущая формула расчета KPI</div>
        </div>
        <? foreach ($arResult['DEPARTMENT_DATA'] as $key => $value): ?>
          <div class="row data-table_row">
            <div class="col-3">
              <div class="link"><a href="<?=SITE_DIR?>test-kpi/computed_rules_change?department=<?=$_REQUEST['department']?>&work_position=<?=$key?>"><?=$value['NAME']?></a></div>
            </div>
            <div class="col-3"><?=$value['COUNT']?></div>
            <div class="col-4"><?=str_replace(',', ' ', $value['FORMULA'])?></div>
            <div class="col-2 d-flex pr-0">
              <button data-id="<?=$key?>" data-name="<?=$value['NAME']?>" class="js-btn-delete-wp ui-btn ui-btn-danger-light ui-btn-xs ui-btn-no-caps ml-auto outline-none">удалить</button>
            </div>
          </div>
        <?endforeach;?>
      </div>
      <?endif;?>
      <div class="row mt-3 align-items-center">
        <div class="col-md-12 mb-3">
          <div class="small"><h2><?=Loc::getMessage('ADD_WORK_POSITION')?></h2></div>
        </div>
        <div class="col-4">
          <div class="ui-ctl ui-ctl-textbox ui-ctl-xs">
            <input type="text" <?=intval($_REQUEST['department']) > 0 ? '' : 'disabled'?> class="ui-ctl-element" name="WP_NAME">
          </div>
        </div>
        <div class="col-6">
          <div id="add_work_position">
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?else:?>
<div class="row"><div class="col-12"><h2 class="mt-5">Доступ запрещен</h2></div></div>
<?endif;?>

