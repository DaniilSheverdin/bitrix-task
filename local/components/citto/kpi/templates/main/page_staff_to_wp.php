<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Grid\Panel\Snippet;
$APPLICATION->SetTitle(Loc::getMessage('TITLE_SET_STAFF_TO_WP'));
$snippet = new Snippet();
?>
<? if ($arResult['ACCESS_DEPARTMENT']): ?>
<div class="row">
  <div class="col-12">
    <div class="kpi_rules">


      <div class="row mt-5">
          <? if (count($arResult['ACCESS']) > 0): ?>
            <div class="col-4 my-5">
              <select id="change_department_access" placeholder="..." name="department">
                <option></option>
                  <? foreach ($arResult['ACCESS'] as $id => $name): ?>
                    <option <?=$_REQUEST['department'] == $id ? 'selected' : ''?> value="<?=$id?>"><?=$name?></option>
                  <?endforeach;?>
              </select>
            </div>
          <?endif;?>

        <div class="col-12">

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
        </div>
        <div class="col-md-12 mb-3">
          <div class="small"><h2><?=Loc::getMessage('SELECT_USER_FROM_OTHER_DEPARTMENT')?></h2></div>
        </div>
        <div class="col-md-6">
          <div class="select-department">
            <select id="select-user" placeholder="..." name="user">
              <option></option>
              <? foreach ($arResult['ALL_USERS'] as $key => $value): ?>
                <option <?=$value['DISABLED'] ? 'disabled' : ''?> value="<?=$key?>"><?=$value['FULL_NAME']?> <?=$value['DISABLED'] ? '('.$value['DEPARTMENT_NAME'].')' : ''?></option>
              <?endforeach;?>
            </select>
          </div>

        </div>
        <div class="col-md-3">
          <? if (isset($_REQUEST['department']) && intval($_REQUEST['department']) > 0): ?>
            <div id="cont_btn_add_user" data-department-id="<?=intval($_REQUEST['department'])?>"></div>
          <?else:?>
            <div id="cont_btn_add_user" data-department-id="<?=$arResult['CURRENT_DEPARTMENT']['ID']?>"></div>
          <?endif;?>
        </div>
      </div>
      <div id="actions-work-positions" class="actions"></div>
      <div class="actions-messages">
        <div>Изменения сохранены</div>
      </div>


    </div>
  </div>
</div>
<?else:?>
  <div class="row"><div class="col-12"><h2 class="mt-5">Доступ запрещен</h2></div></div>
<?endif;?>
