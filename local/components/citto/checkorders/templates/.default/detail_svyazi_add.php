<?php

use Bitrix\Main\Grid\Panel\Actions;
use Bitrix\Main\Grid\Panel\Snippet\Onchange;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$filterData = [];

global $USER;

$filterData['IBLOCK_ID'] = $arParams['IBLOCK_ID_ORDERS'];
$filterData['ACTIVE']    = 'Y';

$filterPermission = ['LOGIC' => 'OR'];
if ($arPerm['ispolnitel']) {
    $filterPermission['PROPERTY_ISPOLNITEL'] = $arPerm['ispolnitel_data']['ID'];
}
if ($arPerm['controler']) {
    $filterPermission['PROPERTY_CONTROLER'] = $USER->GetID();
}
if ($arPerm['kurator']) {
    $filterPermission['PROPERTY_POST'] = [1112, $USER->GetID()];
}
$filterData[] = $filterPermission;
if ($_REQUEST['action_filter']) {
    $filterData['PROPERTY_ACTION'] = $_REQUEST['action_filter'];
}
if ($_REQUEST['resh']) {
    $filterData['PROPERTY_CONTROLER_RESH'] = $_REQUEST['resh'];
}
$columns   = [];
$columns[] = ['id' => 'ID', 'name' => 'ID', 'sort' => 'ID', 'default' => false];
$columns[] = ['id' => 'NAME', 'name' => 'Наименование', 'sort' => 'NAME', 'default' => false];
$columns[] = ['id' => 'TEXT', 'name' => 'Содержание поручения', 'sort' => 'NAME', 'default' => true];
$columns[] = ['id' => 'NUMBER', 'name' => 'Номер', 'sort' => 'NUMBER', 'default' => true];
$columns[] = ['id' => 'DATE_CREATE', 'name' => 'Дата поручения', 'sort' => 'DATE_CREATE_TIMESTAMP', 'default' => true];
$columns[] = ['id' => 'DATE_ISPOLN', 'name' => 'Срок исполнения - план', 'sort' => 'DATE_ISPOLN_TIMESTAMP', 'default' => true];
$columns[] = ['id' => 'ISPOLNITEL', 'name' => 'Исполнитель', 'default' => true];
if (!$_REQUEST['action_filter']) {
    $columns[] = ['id' => 'STATUS', 'name' => 'Состояние поручения', 'default' => true];
} else {
    $columns[] = ['id' => 'STATUS', 'name' => 'Состояние поручения', 'default' => false];
}

if (($arPerm['controler'] && $_REQUEST['action_filter'] == 1137) || ($arPerm['kurator'] && $_REQUEST['action_filter'] == 1138)) {
    $columns[] = ['id' => 'ISPOLN_DATA', 'name' => 'Ход исполнения поручения', 'default' => true, 'editable' => true];
} else {
    $columns[] = ['id' => 'ISPOLN_DATA', 'name' => 'Ход исполнения поручения', 'default' => true];
}
$res = CIBlockElement::GetList(
    $sort['sort'],
    $filterData,
    false,
    false,
    [
        'ID',
        'IBLOCK_ID',
        'NAME',
        'CODE',
        'PROPERTY_POST',
        'PROPERTY_ISPOLNITEL',
        'DATE_CREATE',
        'DETAIL_TEXT',
        'PROPERTY_CONTROLER',
        'PROPERTY_DATE_ISPOLN',
        'PROPERTY_DATE_CREATE',
        'PROPERTY_NUMBER',
        'PROPERTY_ACTION',
        'PROPERTY_STATUS',
        'PROPERTY_VIEWS',
    ]
);

global $arDays;
$arDays = [];
while ($row = $res->GetNext()) {
    $res2 = CIBlockElement::GetList(
        [
            'DATE_CREATE' => 'DESC'
        ],
        [
            'IBLOCK_ID'         => (int)$arParams['IBLOCK_ID_ORDERS_COMMENT'],
            'PROPERTY_PORUCH'   => (int)$row['ID'],
            'PROPERTY_TYPE'     => 1132
        ],
        false,
        [
            'nPageSize' => 1
        ],
        [
            'ID',
            'IBLOCK_ID',
            'NAME',
            'DATE_CREATE',
            'PREVIEW_TEXT',
            'DETAIL_TEXT',
            'PROPERTY_TYPE',
            'PROPERTY_USER'
        ]
    );
    if ($arIspoln = $res2->GetNext()) {
        if (empty($arIspoln['DETAIL_TEXT'])) {
            $arIspoln['DETAIL_TEXT'] = $arIspoln['PREVIEW_TEXT'];
            $arIspoln['~DETAIL_TEXT'] = $arIspoln['~PREVIEW_TEXT'];
        }
    }
    $arrData = [
        'data'    => [
            'ID'                    => $row['ID'],
            'NAME'                  => $row['NAME'],
            'TEXT'                  => $row['~DETAIL_TEXT'],
            'NUMBER'                => $row['PROPERTY_NUMBER_VALUE'],
            'DATE_ISPOLN'           => $row['PROPERTY_DATE_ISPOLN_VALUE'] != $this->__component->disableSrokDate ?
                                        $row['PROPERTY_DATE_ISPOLN_VALUE'] :
                                        'Без срока',
            'DATE_ISPOLN_TIMESTAMP' => $row['PROPERTY_DATE_ISPOLN_VALUE'] != $this->__component->disableSrokDate ?
                                        strtotime($row['PROPERTY_DATE_ISPOLN_VALUE']) :
                                        0,
            'DATE_CREATE'           => $row['PROPERTY_DATE_CREATE_VALUE'],
            'DATE_CREATE_TIMESTAMP' => strtotime($row['PROPERTY_DATE_CREATE_VALUE']),
            'ISPOLNITEL'            => $arResult['ISPOLNITELS'][$row['PROPERTY_ISPOLNITEL_VALUE']]['NAME'],
            'STATUS'                => $row['PROPERTY_ACTION_VALUE'],
            'ISPOLN_DATA'           => $arIspoln['~DETAIL_TEXT'],
        ],
        'class'   => '',
        'actions' => [
            [
                'text'    => 'Выбрать',
                'default' => true,
                'onclick' => 'document.location.href="?detail=' . $_REQUEST['detail'] . '&view=svyazi&subaction=add&add=' . $row['ID'] . '"',
            ],
        ],
    ];
    $date_ispoln = strtotime($row['PROPERTY_DATE_ISPOLN_VALUE']);
    $date_now    = time();
    if (substr_count($row['PROPERTY_VIEWS_VALUE'], ',' . $USER->GetID() . ',') == 0) {
        $arrData['class'] = 'text-primary ';
    }
    if ($row['PROPERTY_ACTION_ENUM_ID'] != 1140 && $date_now > $date_ispoln && $arIspoln['ID'] != '') {
        $arrData['class'] .= 'alert-info';
    } elseif ($row['PROPERTY_ACTION_ENUM_ID'] != 1140 && $date_now > $date_ispoln) {
        $arrData['class'] .= 'alert-danger';
    } elseif ($row['PROPERTY_ACTION_ENUM_ID'] == 1140) {
        $arrData['class'] .= 'alert-success';
    }

    $list[] = $arrData;
}

$onchange = new Onchange();
$onchange->addAction(
    [
        'ACTION' => Actions::CALLBACK,
        'DATA'   => [['JS' => 'add_action_svyzai(self.parent)']],
    ]
);
$ACTION_PANEL = [
    'GROUPS' => [
        'TYPE' => [
            'ITEMS' => [
                [
                    'ID'       => 'accept',
                    'TYPE'     => 'BUTTON',
                    'TEXT'     => 'Добавить поручения',
                    'CLASS'    => 'save',
                    'ONCHANGE' => $onchange->toArray(),
                ],
            ],
        ],
    ],
];

?>
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Добавление поручений</h3>
    </div>

    <div class="box-body box-profile">
        <?$APPLICATION->IncludeComponent(
            'bitrix:main.ui.filter',
            '',
            [
                'FILTER_ID'          => $list_id . '_filter',
                'GRID_ID'            => $list_id,
                'FILTER'             => [],
                'ENABLE_LIVE_SEARCH' => true,
                'ENABLE_LABEL'       => true,
            ]
        );?>
        <?
        $APPLICATION->IncludeComponent(
            'bitrix:main.ui.grid',
            'modified',
            [
                'GRID_ID'                   => 'control-orders-list',
                'COLUMNS'                   => $columns,
                'ROWS'                      => $list,
                'SHOW_ROW_CHECKBOXES'       => true,
                'NAV_OBJECT'                => $nav,
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
                'ACTION_PANEL'              => $ACTION_PANEL,
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
        );?>
        <form id="form_add" method="post"></form>
    </div>
</div>
