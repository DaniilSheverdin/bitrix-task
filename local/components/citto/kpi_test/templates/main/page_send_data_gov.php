<?use Bitrix\Main\Localization\Loc;
$APPLICATION->SetTitle(Loc::getMessage('TITLE_SEND_DATA'));?>
<? if ($arResult['ACCESS_GOVERNMENT']): ?>
<div class="mt-5"></div>
<? foreach ($arResult['GOVERNMENT_DATA']['DEPARTMENT_DATA'] as $keyID => $DEPARTMENT): ?>


<div class="row js-send-data-gov">
  <div class="col-12">
    <div class="kpi_rules">
      <div class="row">
        <div class="col-12 d-flex">
          <div class="button_work_position js-switch-icon" type="button" data-toggle="collapse" data-target="#collapseExample<?=$keyID?>" aria-expanded="false" aria-controls="collapse">
            <?=$arResult['GOVERNMENT_DATA']['CURRENT_DEPARTMENT'][$keyID]['NAME']?>
          </div>
          <div class="arrow_work_position"><img src="<?=$templatePath?>/icons/angle-up-solid.svg" alt=""></div>
        </div>
      </div>
      <div>
        <? foreach ($DEPARTMENT['RESULT'] as $id => $data): ?>
          <div class="row">
            <div class="col-12">
              <div class="collapse" id="collapseExample<?=$keyID?>">
                <div class="my-3"><h3><?=$DEPARTMENT['WP_NAMES'][$id]?></h3></div>
                <div class="mb-4">
                  <b><?=Loc::getMessage('TITLE_FORMULA_STATE')?>:</b> <?=$DEPARTMENT['WP_FORMULAS'][$id]?>
                </div>

                <div class="js-staff-form" style="overflow-x: scroll;">
                  <div class="kpi_table_head">
                    <div class="column edit" style="min-width: 50px;">
                      <input type="checkbox" data-select="all" data-select-id="<?=$id?>">
                    </div>
                    <div class="column">ФИО</div>
                    <div class="column width-50">Ставка</div>
                    <? foreach($DEPARTMENT['KPI_LABELS'][$id] as $label): ?>
                      <div class="column"><?=$label['NAME'] . ' / ' . $label['TARGET']?></div>
                    <?endforeach;?>
                    <? if (count($DEPARTMENT['KPI_EXT']) > 0): ?>
                      <div class="column">Критический KPI</div>
                    <?endif;?>
                    <div class="column">KPI развития</div>
                    <div class="column">Комментарий</div>
                    <div class="column">Итоговое значение KPI</div>
                  </div>
                  <? foreach ($data as $userID => $user): ?>
                    <form id="<?=$userID?>" data-actions-id="<?=$id?>" class="kpi_table_body js-staff-form">
                      <div data-actions-id="<?=$id?>" class="column edit">
                        <input type="checkbox" data-select="user" data-select-id="<?=$id?>">
                      </div>
                      <div class="column"><?=$user['NAME']?></div>
                      <div class="column width-50"><?=$user['ATT_SALARY']?></div>
                      <? if ($user['ATT_VALUE_KPI']): ?>
                        <? foreach ($user['ATT_VALUE_KPI'] as $kpiValue): ?>
                          <div data-type="array" data-name="ATT_VALUE_KPI" data-id="<?=$kpiValue['ID']?>" data-editable="N" class="column <?=$kpiValue['IS_RED'] == 'Y' ? 'red' : ''?>"><?=$kpiValue['VALUE']?></div>
                        <?endforeach;?>
                      <?else:?>
                        <? foreach($arResult['DEPARTMENT_DATA']['KPI_LABELS'][$id] as $label): ?>
                          <div data-type="array" data-name="ATT_VALUE_KPI" data-id="<?=$label['ID']?>" data-editable="<?=$label['EDITABLE'] == 'Y' ? 'Y' : 'N'?>" class="column"></div>
                        <?endforeach;?>
                      <?endif;?>
                      <? if (count($DEPARTMENT['KPI_EXT']) > 0): ?>
                      <div class="column">
                        <div data-name="ATT_KPI_CRITICAL"
                             data-type="checkbox"
                             data-value="<?=$user['ATT_KPI_CRITICAL']?>"
                             data-editable="Y">
                          <? if (intval($user['ATT_KPI_CRITICAL']) > 0): ?>
                            <label class="ui-ctl ui-ctl-checkbox">
                              <input disabled name="ATT_KPI_CRITICAL" type="checkbox" class="ui-ctl-element" <?=intval($user['ATT_KPI_CRITICAL']) > 0 ? 'checked' : ''?>>
                              <div class="ui-ctl-label-text"><?=intval($user['ATT_KPI_CRITICAL']) > 0 ? 'Активирован '.$user['ATT_KPI_CRITICAL'].'/'.count($DEPARTMENT['KPI_EXT']) : ''?></div>
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

    </div>
  </div>
</div>

<?endforeach;?>

<div class="row">
  <div class="col-12">
    <div id="actions-all-staff" class="actions"></div>
    <div class="actions-messages with-btn">
      <div>Изменения сохранены</div>
    </div>
  </div>
</div>
<?else:?>
  <div class="row"><div class="col-12"><h2 class="mt-5">Доступ запрещен</h2></div></div>
<?endif;?>
