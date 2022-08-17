<?php
use Bitrix\Main\Localization\Loc;
$APPLICATION->SetTitle(Loc::getMessage('DATA_NAME'));?>
<? if ($arResult['ACCESS']): ?>

<?php //pre($arResult) ?>

<div class="row">
  <div class="col-12 head">
    <div class="grid_filter">
        <?$APPLICATION->IncludeComponent('bitrix:main.ui.filter', '', [
            'FILTER_ID' => $arResult['grid_id'],
            'GRID_ID' => $arResult['grid_id'],
            'FILTER' => $arResult['UI_FILTER'],
            'ENABLE_LIVE_SEARCH' => true,
            'ENABLE_LABEL' => true
        ]);?>
    </div>
    <div id="js-button"></div>
  </div>
</div>
<div class="row">
  <div class="col-12 px-0">
      <?  $APPLICATION->IncludeComponent('bitrix:main.ui.grid', '', [
          'GRID_ID' => $arResult['grid_id'],
          'COLUMNS' => $arResult['columns'],
          'ROWS' => $arResult['list'],
          'SHOW_ROW_CHECKBOXES' => false,
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
</div>
<?else:?>
  Доступ запрещен
<?endif;?>


<script>
  let filter = <?php echo json_encode($arResult['FILTER_DATA']); ?>;

  BX.addCustomEvent('BX.Main.Filter:apply', BX.delegate(function (grid_id) {

    filter = {};

    BX.Main.filterManager.data[grid_id].params.PRESETS[0].FIELDS.map(function (el) {

      if (el.NAME === "ATT_VAC_DATE" && el.VALUES._from && el.VALUES._to) {

        filter['>=PROPERTY_ATT_VAC_DATE'] = el.VALUES._from;
        filter['<=PROPERTY_ATT_VAC_DATE'] = el.VALUES._to

      }

      if (el.VALUES._from === el.VALUES._to) {
        filter = {};
        filter['PROPERTY_ATT_VAC_DATE'] = el.VALUES._from;

      }

    });

    console.log('filter', filter);

  }));
</script>
