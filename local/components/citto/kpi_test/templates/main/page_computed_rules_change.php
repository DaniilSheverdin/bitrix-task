<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Grid\Panel\Snippet;

$snippet = new Snippet();
?>
<? if ($arResult['ACCESS_DEPARTMENT']): ?>
<? if (isset($_REQUEST['work_position']) && intval($_REQUEST['work_position']) > 0): ?>
<div class="row">
  <div class="col-12">
    <div class="kpi_change">
      <div class="row ml-5">
        <div class="col-12">
          <div class="title mt-5"><h3><?=Loc::getMessage('TITLE_CHANGE_RULES')?></h3></div>
        </div>
      </div>
      <div class="row align-items-center">
        <div class="col-12">
          <div class="row mt-5">
            <div class="col-md-3 mb-3">
              <div class="small"><h2><?=Loc::getMessage('TITLE_WORK_POSITION_NAME')?></h2></div>
            </div>
            <div class="col-md-6">
              <div class="ui-ctl ui-ctl-textbox ui-ctl-xs">
                <input class="ui-ctl-element" name="WP_NAME_CHANGE" type="text" value="<?=$arResult['DATA_WP']['NAME']?>" >
              </div>
            </div>
          </div>

          <div class="title mt-5 mb-3 color-grey"><?=Loc::getMessage('TITLE_GRID_CHANGE_RULES')?></div>


          <?  $APPLICATION->IncludeComponent('bitrix:main.ui.grid', '', [
            'GRID_ID' => $arResult['grid_id'],
            'COLUMNS' => $arResult['columns'],
            'ROWS' => $arResult['list'],
            'SHOW_ROW_CHECKBOXES' => true,
            'NAV_OBJECT' => $arResult['nav'],
            'AJAX_MODE' => 'Y',
            'AJAX_ID' => \CAjax::getComponentID('bitrix:main.ui.grid', '', ''),
            'PAGE_SIZES' =>  [
              ['NAME' => '5', 'VALUE' => '5'],
              ['NAME' => '10', 'VALUE' => '10'],
              ['NAME' => '20', 'VALUE' => '20'],
              ['NAME' => '50', 'VALUE' => '50']
            ],
            'AJAX_OPTION_JUMP'          => 'N',
            'SHOW_CHECK_ALL_CHECKBOXES' => true,
            'SHOW_ROW_ACTIONS_MENU'     => true,
            'SHOW_GRID_SETTINGS_MENU'   => true,
            'SHOW_NAVIGATION_PANEL'     => true,
            'SHOW_PAGINATION'           => true,
            'SHOW_SELECTED_COUNTER'     => true,
            'SHOW_TOTAL_COUNTER'        => true,
            'SHOW_PAGESIZE'             => true,
            'TOTAL_ROWS_COUNT'          => $arResult['nav']->getRecordCount(),
            'SHOW_ACTION_PANEL'         => true,
            'ACTION_PANEL'              => [
              'GROUPS' => [
                'TYPE' => [
                  'ITEMS' => [
                    [
                      'ID'       => 'edit',
                      'TYPE'     => 'BUTTON',
                      'TEXT'        => 'Редактировать',
                      'CLASS'        => 'icon edit',
                      'ONCHANGE' => $arResult['onchange2']->toArray()
                    ],
                    $snippet->getRemoveButton(),


                  ],
                ]
              ],
            ],
            'ALLOW_COLUMNS_SORT'        => true,
            'ALLOW_COLUMNS_RESIZE'      => true,
            'ALLOW_HORIZONTAL_SCROLL'   => true,
            'ALLOW_SORT'                => true,
            'ALLOW_PIN_HEADER'          => true,
            'AJAX_OPTION_HISTORY'       => 'N'
          ]);?>

          <div id="add-button-rc"></div>

<!--          <div class="formula row align-items-center">-->
<!--            <div class="col-auto"><label for="formula_indicators">--><?
// =Loc::getMessage('TITLE_FORMULA_INDICATORS')?><!--</label></div>-->
<!--            <div class="col"><select id="formula_indicators" multiple placeholder="..."></select></div>-->
<!--          </div>-->

          <div class="formula row align-items-center">
            <div class="col-auto"><label for="formula"><?=Loc::getMessage('TITLE_FORMULA')?></label></div>
            <div class="col"><select id="formula" multiple placeholder="..."></select></div>
          </div>

          <div id="actions-button-rc" class="actions"></div>
          <div class="actions-messages">
            <div>Изменения сохранены</div>
          </div>

        </div>
      </div>
    </div>
  </div>
</div>
<?else:?>
<div class="row m-5">
  <div class="col-12">
    <div class="error_message"><?=Loc::getMessage('ERROR_MESSAGE_PARAMS_REQUIRED')?></div>
  </div>
</div>
<?endif;?>

<div style="display: none;" id="add-kpi-content">
  <form id="form-kpi-add" action="" class="form-kpi-add">
    <div class="row">
      <div class="col-12"><div class="title text-center">Добавление KPI #<span id="next_kpi"></span></div></div>
    </div>
    <div class="row mt-4 align-items-center">
      <div class="col-md-4"><label for=""><?=Loc::getMessage('FORM_KPI_ADD_LABEL_NAME')?></label></div>
      <div class="col-md-8">
        <div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
          <input type="text" class="ui-ctl-element" name="NAME">
          <input type="hidden" name="ATT_LABEL" id="input_label">
          <input type="hidden" name="ATT_WORK_POSITION" value="<?=$_REQUEST['work_position']?>">
        </div>
      </div>
    </div>
    <div class="row mt-4 align-items-center">
      <div class="col-md-4"><label for=""><?=Loc::getMessage('FORM_KPI_ADD_LABEL_DATA_SOURCE')?></label></div>
      <div class="col-md-8">
        <div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown ui-ctl-w100">
          <div class="ui-ctl-after ui-ctl-icon-angle"></div>
          <select class="ui-ctl-element" name="ATT_DATA_SOURCE">
            <? foreach ($arResult['columns'] as $column): ?>
              <? if($column['id'] == 'ATT_DATA_SOURCE'): ?>
                <? foreach ($column['editable']['items'] as $id => $item): ?>
                  <option value="<?=$id?>"><?=$item?></option>
                <?endforeach;?>
              <?endif;?>
            <?endforeach;?>
          </select>
        </div>
      </div>
    </div>

    <div class="row mt-3 align-items-center">
      <div class="col-md-4"><label for=""><?=Loc::getMessage('FORM_KPI_ADD_LABEL_WEIGHT')?></label></div>
      <div class="col-md-8">
        <div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
          <input type="number" max="100" maxlength="3" class="ui-ctl-element" name="ATT_WEIGHT">
        </div>
      </div>
    </div>
    <div class="row mt-3 align-items-center">
      <div class="col-md-4"><label for=""><?=Loc::getMessage('FORM_KPI_ADD_LABEL_TARGET_VALUE')?></label></div>
      <div class="col-md-8">
        <div class="ui-ctl ui-ctl-textbox ui-ctl-w100">
          <input type="number" max="100" maxlength="3" class="ui-ctl-element" name="ATT_TARGET_VALUE">
        </div>
      </div>
    </div>
    <div class="row mt-3 align-items-center">
      <div class="col-md-4"></div>
      <div class="col-md-8">
        <label class="ui-ctl ui-ctl-checkbox">
          <input type="checkbox" class="ui-ctl-element" name="ATT_MANUAL_INPUT">
          <div class="ui-ctl-label-text"><?=Loc::getMessage('FORM_KPI_ADD_LABEL_MANUAL_INPUT')?></div>
        </label>
      </div>


    </div>
    <div class="row mt-4 align-items-center justify-content-center">
      <div id="kpi_actions"></div>
    </div>

  </form>
</div>
<?else:?>
  <div class="row"><div class="col-12"><h2 class="mt-5">Доступ запрещен</h2></div></div>
<?endif;?>
