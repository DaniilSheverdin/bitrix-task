<?use Bitrix\Main\Localization\Loc;
$APPLICATION->SetTitle(Loc::getMessage('TITLE_SET_DATA'));?>
<? if ($arResult['ACCESS_DEPARTMENT']): ?>
<div class="row">
  <div class="col-12">
    <div class="kpi_rules">
      <div class="row ml-5">

          <? if (count($arResult['ACCESS']) > 0): ?>
          <div class="col-4 mt-5">
            <select id="change_department_access" placeholder="..." name="department">
                <option></option>
                <? foreach ($arResult['ACCESS'] as $id => $name): ?>
                  <option <?=$_REQUEST['department'] == $id ? 'selected' : ''?> value="<?=$id?>"><?=$name?></option>
                <?endforeach;?>
            </select>


          </div>
          <?endif;?>

        <div class="col-12">
          <? if (!isset($_REQUEST['department'])): ?>
          <div class="title mt-5"><h1><?=$arResult['CURRENT_DEPARTMENT']['NAME']?></h1></div>

          <?else:?>
            <div class="title mt-5"><h1><?=$arResult['ACCESS'][$_REQUEST['department']]?></h1></div>
          <?endif;?>
        </div>
      </div>

      <div class="mt-5">
        <? foreach ($arResult['DEPARTMENT_DATA']['RESULT'] as $id => $data): ?>
        <div class="row">
          <div class="col-12 d-flex">
            <div class="button_work_position js-switch-icon" type="button" data-toggle="collapse" data-target="#collapseExample<?=$id?>" aria-expanded="true" aria-controls="collapse">
              <?=$arResult['DEPARTMENT_DATA']['WP_NAMES'][$id]?>
            </div>
            <div class="arrow_work_position"><img src="<?=$templatePath?>/icons/angle-up-solid.svg" alt=""></div>
           </div>
          <div class="col-12">
            <div class="collapse show" id="collapseExample<?=$id?>">
              <div class="mb-4">
                <b><?=Loc::getMessage('TITLE_FORMULA_STATE')?>:</b> <?=$arResult['DEPARTMENT_DATA']['WP_FORMULAS'][$id]?>
              </div>
              <div class="js-staff-form" style="overflow-x: scroll;">
                <div class="kpi_table_head">
                  <div class="column edit" style="min-width: 50px;">
                    <input type="checkbox" data-select="all" data-select-id="<?=$id?>">
                  </div>
                  <div class="column">ФИО</div>
                  <div class="column width-50">Ставка</div>
                  <? foreach($arResult['DEPARTMENT_DATA']['KPI_LABELS'][$id] as $label): ?>
                    <div class="column"><?=$label['NAME'] . ' / ' . $label['TARGET']?></div>
                  <?endforeach;?>
                  <? if (count($arResult['DEPARTMENT_DATA']['KPI_EXT']) > 0): ?>
                  <div class="column">Критический KPI</div>
                  <?endif;?>
                  <div class="column">KPI развития</div>
                  <div class="column">Комментарий</div>
                  <div class="column">Итоговое значение KPI</div>
                </div>
                <? foreach ($data as $userID => $user): ?>
                  <form id="<?=$userID?>" data-actions-id="<?=$id?>" class="kpi_table_body js-staff-form">
                    <div data-actions-id="<?=$id?>" class="column edit ">
                      <input type="checkbox" data-select="user" data-select-id="<?=$id?>">
                    </div>
                    <div class="column"><?=$user['NAME']?></div>
                    <div class="column width-50"><?=$user['ATT_SALARY']?></div>
                    <? if ($user['ATT_VALUE_KPI']): ?>
                      <? foreach ($user['ATT_VALUE_KPI'] as $kpiValue): ?>
                        <div data-type="array" data-name="ATT_VALUE_KPI" data-id="<?=$kpiValue['ID']?>" data-editable="<?=$kpiValue['EDITABLE'] == 'Y' ? 'Y' : 'N'?>" class="column <?=$kpiValue['IS_RED'] == 'Y' ? 'red' : ''?>"><?=$kpiValue['VALUE']?></div>
                      <?endforeach;?>
                    <?else:?>
                      <? foreach($arResult['DEPARTMENT_DATA']['KPI_LABELS'][$id] as $label): ?>
                        <div data-type="array" data-name="ATT_VALUE_KPI" data-id="<?=$label['ID']?>" data-editable="<?=$label['EDITABLE'] == 'Y' ? 'Y' : 'N'?>" class="column"></div>
                      <?endforeach;?>
                    <?endif;?>
                    <? if (count($arResult['DEPARTMENT_DATA']['KPI_EXT']) > 0): ?>
                    <div class="column">
                      <div data-name="ATT_KPI_CRITICAL"
                           data-type="checkbox"
                           data-value="<?=$user['ATT_KPI_CRITICAL']?>"
                           data-editable="Y">
                        <? if (intval($user['ATT_KPI_CRITICAL']) > 0): ?>
                          <label class="ui-ctl ui-ctl-checkbox">
                            <input disabled name="ATT_KPI_CRITICAL" type="checkbox" class="ui-ctl-element" <?=intval($user['ATT_KPI_CRITICAL']) > 0 ? 'checked' : ''?>>
                            <div class="ui-ctl-label-text"><?=intval($user['ATT_KPI_CRITICAL']) > 0 ? 'Активирован '.$user['ATT_KPI_CRITICAL'].'/'.count($arResult['DEPARTMENT_DATA']['KPI_EXT']) : ''?></div>
                          </label>


                        <?endif;?>
                      </div>
                    </div>
                    <?endif;?>
                    <div class="column">
                      <div data-name="ATT_KPI_PROGRESS"
                           data-type="checkbox"
                           data-value="<?=$user['ATT_KPI_PROGRESS']?>"
                           data-editable="Y"><?=$user['ATT_KPI_PROGRESS'] == 'Y' ? 'Активирован' : ''?>
                      </div>
                    </div>
                    <div data-type="text" data-name="ATT_COMMENT" data-editable="Y" class="column"><?=$user['ATT_COMMENT']?></div>
                    <div class="column"><?=$user['ATT_RESULT_KPI']?></div>
                  </form>
                <?endforeach;?>
              </div>
              <div class="actions-row mt-3" id="<?=$id?>"></div>

            </div>
          </div>
        </div>
        <?endforeach;?>
      </div>
      <div id="actions-all-staff" class="actions"></div>
      <div class="actions-messages">
        <div>Изменения сохранены</div>
      </div>

    </div>
  </div>
</div>
<?else:?>
  <div class="row"><div class="col-12"><h2 class="mt-5">Доступ запрещен</h2></div></div>
<?endif;?>