<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Шаг 3 - Результат импорта</h3>
    </div>
    <?php
    if (!empty($arResult['ERROR'])) {
        ?>
    <div class="alert alert-danger" role="alert">
        <?=$arResult['ERROR'];?>
    </div>
        <?php
    }
    ?>
    <div class="box-body">
        <?$APPLICATION->IncludeComponent(
            'bitrix:main.ui.grid',
            'modified',
            [
                'GRID_ID'                   => $arResult['LIST_ID'],
                'COLUMNS'                   => $arResult['COLUMNS'],
                'ROWS'                      => $arResult['ROWS'],
                'SHOW_ROW_CHECKBOXES'       => false,
                'NAV_OBJECT'                => false,
                'AJAX_MODE'                 => 'N',
                'SHOW_CHECK_ALL_CHECKBOXES' => false,
                'SHOW_ROW_ACTIONS_MENU'     => false,
                'ACTION_PANEL'              => [],
                'SHOW_GRID_SETTINGS_MENU'   => true,
                'SHOW_NAVIGATION_PANEL'     => false,
                'SHOW_PAGINATION'           => false,
                'SHOW_SELECTED_COUNTER'     => false,
                'SHOW_TOTAL_COUNTER'        => false,
                'SHOW_PAGESIZE'             => false,
                'SHOW_ACTION_PANEL'         => false,
                'ALLOW_COLUMNS_SORT'        => true,
                'ALLOW_COLUMNS_RESIZE'      => true,
                'ALLOW_HORIZONTAL_SCROLL'   => true,
                'ALLOW_SORT'                => true,
                'ALLOW_PIN_HEADER'          => true,
                'ALLOW_INLINE_EDIT'         => false,
                'ALLOW_INLINE_EDIT_ALL'     => false,
                'ALLOW_EDIT'                => false,
                'ALLOW_EDIT_ALL'            => false,
                'SHOW_GROUP_EDIT_BUTTON'    => false,
                'EDITABLE'                  => false,
                'AJAX_OPTION_HISTORY'       => 'N',
            ]
        );?>
    </div>
</div>
