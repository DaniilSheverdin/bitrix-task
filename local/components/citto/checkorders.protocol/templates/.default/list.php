<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$this->SetViewTarget('above_pagetitle');
$APPLICATION->IncludeComponent(
    'bitrix:main.interface.buttons',
    '',
    [
        'ID' => 'menuId',
        'ITEMS' => $arResult['MENU_ITEMS'],
    ],
    false
);
$this->EndViewTarget();

$this->SetViewTarget('inside_pagetitle', 100);?>
<div class="pagetitle-container pagetitle-flexible-space">
<?php
$APPLICATION->IncludeComponent(
    'bitrix:main.ui.filter',
    '',
    [
        'FILTER_ID'          => $arResult['LIST_ID'] . '_filter',
        'GRID_ID'            => $arResult['LIST_ID'],
        'FILTER'             => $arResult['FILTER'],
        'ENABLE_LIVE_SEARCH' => true,
        'ENABLE_LABEL'       => true,
    ]
);?>
</div>
<?php
$this->EndViewTarget();

$this->SetViewTarget('inside_pagetitle', 200);
?>
<div class="pagetitle-container pagetitle-align-right-container">
<a class="ui-btn ui-btn-success ui-btn-icon-add mr-3" href="/control-orders/protocol/?id=0">Добавить</a>
</div>
<?php
$this->EndViewTarget();

$APPLICATION->IncludeComponent(
    'bitrix:main.ui.grid',
    'modified',
    [
        'GRID_ID'                   => $arResult['LIST_ID'],
        'COLUMNS'                   => $arResult['COLUMNS'],
        'ROWS'                      => $arResult['ROWS'],
        'SHOW_ROW_CHECKBOXES'       => true,
        'NAV_OBJECT'                => $arResult['NAV'],
        'AJAX_MODE'                 => 'N',
        'AJAX_ID'                   => CAjax::GetComponentID('bitrix:main.ui.grid', 'modified', ''),
        'PAGE_SIZES'                => [
            ['NAME' => '20', 'VALUE' => '20'],
            ['NAME' => '50', 'VALUE' => '50'],
            ['NAME' => '100', 'VALUE' => '100'],
        ],
        'AJAX_OPTION_JUMP'          => 'N',
        'SHOW_CHECK_ALL_CHECKBOXES' => true,
        'SHOW_ROW_ACTIONS_MENU'     => true,
        'ACTION_PANEL'              => [],
        'SHOW_GRID_SETTINGS_MENU'   => true,
        'SHOW_NAVIGATION_PANEL'     => true,
        'SHOW_PAGINATION'           => true,
        'SHOW_SELECTED_COUNTER'     => true,
        'SHOW_TOTAL_COUNTER'        => true,
        'SHOW_PAGESIZE'             => true,
        'SHOW_ACTION_PANEL'         => true,
        'ALLOW_COLUMNS_SORT'        => true,
        'ALLOW_COLUMNS_RESIZE'      => true,
        'ALLOW_HORIZONTAL_SCROLL'   => true,
        'ALLOW_SORT'                => true,
        'ALLOW_PIN_HEADER'          => true,
        'AJAX_OPTION_HISTORY'       => 'N',
    ]
);
