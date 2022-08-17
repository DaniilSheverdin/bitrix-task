<?php


/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

if ($arResult['ACCESS']):?>

    <div class="page_title">
        <span><?=GetMessage('BUTTON_VIOLATORS_CCMIS_TABLE')?></span>

    </div>
    <div class="action_row">
        <div class="grid_filter">
            <?$APPLICATION->IncludeComponent('bitrix:main.ui.filter', '', [
                'FILTER_ID' => $arResult['grid_id'],
                'GRID_ID' => $arResult['grid_id'],
                'FILTER' => $arResult['UI_FILTER'],
                'ENABLE_LIVE_SEARCH' => true,
                'ENABLE_LABEL' => true
            ]);?>
        </div>

    </div>


    <? $APPLICATION->IncludeComponent('bitrix:main.ui.grid', '', [
        'GRID_ID' => $arResult['grid_id'],
        'COLUMNS' => $arResult['columns'],
        'ROWS' => $arResult['list'],
        'SHOW_ROW_CHECKBOXES' => true,
        'NAV_OBJECT' => $arResult['nav'],
        'AJAX_MODE' => 'Y',
        'AJAX_ID' => \CAjax::getComponentID('serg:main.ui.grid', '', ''),
        'PAGE_SIZES' =>  [
            ['NAME' => '5', 'VALUE' => '5'],
            ['NAME' => '10', 'VALUE' => '10'],
            ['NAME' => '20', 'VALUE' => '20'],
            ['NAME' => '50', 'VALUE' => '50']
        ],
        'AJAX_OPTION_JUMP'          => 'N',
        'SHOW_CHECK_ALL_CHECKBOXES' => false,
        'SHOW_ROW_ACTIONS_MENU'     => false,
        'SHOW_GRID_SETTINGS_MENU'   => true,
        'SHOW_NAVIGATION_PANEL'     => true,
        'SHOW_PAGINATION'           => true,
        'SHOW_SELECTED_COUNTER'     => true,
        'SHOW_TOTAL_COUNTER'        => true,
        'SHOW_PAGESIZE'             => true,
        'SHOW_ACTION_PANEL'         => false,
        'ALLOW_COLUMNS_SORT'        => true,
        'ALLOW_COLUMNS_RESIZE'      => true,
        'ALLOW_HORIZONTAL_SCROLL'   => true,
        'ALLOW_SORT'                => true,
        'ALLOW_PIN_HEADER'          => true,
        'AJAX_OPTION_HISTORY'       => 'N'
    ]);?>

    <form method="post" action="/isolation/?PAGE=ADD&TYPE=VIOLATORS_CCMIS" enctype="multipart/form-data">
        <h3><?=GetMessage('TITLE_ADD_PEOPLES')?></h3>
        <input name="file" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel, .xls" type="file">
        <button type="submit" class="button"><?=GetMessage('BUTTON_UPLOAD_XLS')?></button>
        <div class="message"><span class="message_success"><?=GetMessage('FILES_TYPE')?></span></div>
    </form>



<?else:

    echo GetMessage('ACCESS_ERROR');

endif;?>





