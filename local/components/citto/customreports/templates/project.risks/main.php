<?php

use CUser;
use CIBlockElement;
use Bitrix\Main\UI;
use Bitrix\Disk\File;
use Bitrix\Main\Grid;
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Disk\AttachedObject;
use Citto\Tasks\ProjectInitiative;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) {
    die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

Loader::includeModule('iblock');
Loader::includeModule('disk');

$gridOptions = new Grid\Options($arResult['LIST_ID']);
$arSort = $gridOptions->getSorting(
    [
        'sort' => [
            'ID' => 'DESC',
        ],
        'vars' => [
            'by'    => 'by',
            'order' => 'order'
        ]
    ]
);

$arNavParams = $gridOptions->GetNavParams();

$obNav = new UI\PageNavigation($arResult['LIST_ID']);
$obNav->allowAllRecords(true)
    ->setPageSize($arNavParams['nPageSize'])
    ->initFromUri();

if ($obNav->allRecordsShown()) {
    $arNavParams = false;
} else {
    $arNavParams['iNumPage'] = $obNav->getCurrentPage();
}

$arFilter = [
    'IBLOCK_ID' => $arResult['IBLOCK_ID'],
    'ACTIVE' => 'Y',
    'PROPERTY_RISK_GROUP' => $arParams['GROUP_ID']
];

$res = CIBlockElement::GetList(
    $arSort['sort'],
    $arFilter,
    false,
    $arNavParams,
    [
        'ID',
        'NAME',
        'PROPERTY_IS_RISK',
        'PROPERTY_RISK_TYPE',
        'PROPERTY_RISK_ACTION',
    ]
);

$totalCount = $res->SelectedRowsCount();
$obNav->setRecordCount($totalCount);

while ($row = $res->Fetch()) {
    $arData = [
        'data'    => [
            'ID'            => $row['ID'],
            'NAME'          => $row['NAME'],
            'IS_RISK'       => $row['PROPERTY_IS_RISK_VALUE'],
            'RISK_TYPE'       => $row['PROPERTY_RISK_TYPE_VALUE'],
            'RISK_ACTION'       => $row['PROPERTY_RISK_ACTION_VALUE'],
            'RISK_IS_ACTION'       => ($row['PROPERTY_RISK_ACTION_VALUE']) ? 'Да' : 'Нет',
        ],
        'class' => 'alert-white',
        'actions' => [
            [
                'text'    => 'Удалить',
                'onclick' => "document.location.href='?ACTION=DELETE&ID={$row['ID']}'"
            ]
        ],
    ];

    $arResult['ROWS'][] = $arData;
}
$arResult['NAV'] = $obNav;
$arResult['COLUMNS'] = [
    [
        'id'        => 'NAME',
        'name'      => 'Наименование риска',
        'sort'      => 'name',
        'default'   => true
    ],
    [
        'id'        => 'IS_RISK',
        'name'      => 'Риск наступил',
        'sort'      => 'name',
        'default'   => true
    ],
    [
        'id'        => 'RISK_TYPE',
        'name'      => 'Тип риска',
        'sort'      => false,
        'default'   => true
    ],
    [
        'id'        => 'RISK_ACTION',
        'name'      => 'Какие меры будем предпринимать',
        'sort'      => false,
        'default'   => true
    ],
    [
        'id'        => 'RISK_IS_ACTION',
        'name'      => 'Меры приняты',
        'sort'      => false,
        'default'   => true
    ]
];
?>
<form method="post" class="m-3">
    <div class="row">
        <div class="col-2">
            <b>Наименование риска:</b><br/>
            <div class="ui-ctl ui-ctl-textbox">
                <input
                        class="ui-ctl-element"
                        name="RISK_NAME"
                        value=""
                        placeholder="Наименование риска"
                        required />
            </div>
        </div>

        <div class="col-2">
            <b>Риск наступил:</b><br/>
            <div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown">
                <div class="ui-ctl-after ui-ctl-icon-angle"></div>
                <select class="ui-ctl-element" name="IS_RISK" required>
                    <option value="Y">Да</option>
                    <option value="N">Нет</option>
                </select>
            </div>
        </div>

        <div class="col-2">
            <b>Тип риска:</b><br/>
            <div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown">
                <div class="ui-ctl-after ui-ctl-icon-angle"></div>
                <select class="ui-ctl-element" name="RISK_TYPE" required>
                    <option value="TECHNICAL">технический</option>
                    <option value="ORGANIZATIONAL">организационный</option>
                    <option value="EXTERNAL">внешний</option>
                </select>
            </div>
        </div>

        <div class="col-5">
            <b>Какие меры будем предпринимать:</b><br/>
            <div class="ui-ctl ui-ctl-textarea">
                <textarea class="ui-ctl-element" name="RISK_ACTION"></textarea>
            </div>
        </div>

        <div class="col-1">
            <br/>
            <input class="ui-btn ui-btn-primary" name="ADD_RISK" value="Добавить" type="submit">
        </div>
    </div>
</form>
<?php
$APPLICATION->IncludeComponent(
    'bitrix:main.ui.grid',
    'modified',
    [
        'GRID_ID'                   => $arResult['LIST_ID'],
        'COLUMNS'                   => $arResult['COLUMNS'],
        'ROWS'                      => $arResult['ROWS'],
        'SHOW_ROW_CHECKBOXES'       => false,
        'NAV_OBJECT'                => $arResult['NAV'],
        'AJAX_MODE'                 => 'N',
        'AJAX_ID'                   => CAjax::GetComponentID('bitrix:main.ui.grid', 'modified', ''),
        'PAGE_SIZES'                => [
            ['NAME' => '20', 'VALUE' => '20'],
            ['NAME' => '50', 'VALUE' => '50'],
            ['NAME' => '100', 'VALUE' => '100'],
        ],
        'AJAX_OPTION_JUMP'          => 'N',
        'SHOW_CHECK_ALL_CHECKBOXES' => false,
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
        'TOTAL_ROWS_COUNT'          => $totalCount,
    ]
);
