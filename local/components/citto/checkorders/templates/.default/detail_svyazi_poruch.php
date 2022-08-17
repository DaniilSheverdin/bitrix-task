<?php

use Citto\Controlorders\Orders;
use Citto\Controlorders\Settings;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

global $USER;

?>
<div class="box-header with-border">
    <h3 class="box-title">Связанные поручения</h3>
    <?php
    if ($arResult['PERMISSIONS']['controler'] || $arResult['PERMISSIONS']['kurator'] || $GLOBALS['USER']->IsAdmin()) {
        ?>
        <a class="ui-btn-success ui-btn ui-btn-icon-add float-right" href="?detail=<?=$_REQUEST['detail']?>&view=svyazi&subaction=add&back_url=<?=$backUrl?>">Добавить поручение</a>
        <?php
    }
    ?>
</div>

<div class="box-body box-profile">
<?php

$filterData = [
    $this->__component->getPermisionFilter($USER->GetID()),
    'IBLOCK_ID' => Settings::$iblockId['ORDERS'],
    'ACTIVE'    => 'Y',
    'ID'        => $arResult['DETAIL_DATA']['ELEMENT']['PROPERTY_PORUCH_VALUE'],
];

$obRes = CIBlockElement::GetList(
    ['id' => 'asc'],
    [
        $this->__component->getPermisionFilter($USER->GetID()),
        'IBLOCK_ID'         => Settings::$iblockId['ORDERS'],
        'ACTIVE'            => 'Y',
        'PROPERTY_PORUCH'   => $arResult['DETAIL_DATA']['ELEMENT']['ID'],
    ],
    false,
    false,
    ['ID']
);
while ($arRow = $obRes->GetNext()) {
    $filterData['ID'][] = $arRow['ID'];
}

$arColumns   = [];
$arColumns[] = ['id' => 'ID', 'name' => 'ID', 'sort' => 'ID', 'default' => false];
$arColumns[] = ['id' => 'NAME', 'name' => 'Наименование', 'sort' => 'NAME', 'default' => false];
$arColumns[] = ['id' => 'TEXT', 'name' => 'Содержание поручения', 'sort' => false, 'default' => true];
$arColumns[] = ['id' => 'NUMBER', 'name' => 'Номер', 'sort' => 'NUMBER', 'default' => true];
$arColumns[] = ['id' => 'DATE_CREATE', 'name' => 'Дата поручения', 'sort' => 'DATE_CREATE_TIMESTAMP', 'default' => true];
$arColumns[] = ['id' => 'DATE_ISPOLN', 'name' => 'Срок исполнения - план', 'sort' => 'DATE_ISPOLN_TIMESTAMP', 'default' => true];
$arColumns[] = ['id' => 'ISPOLNITEL', 'name' => 'Исполнитель', 'default' => true];
$arColumns[] = ['id' => 'STATUS', 'name' => 'Состояние поручения', 'default' => true];

if (
    ($arResult['PERMISSIONS']['controler'] && $_REQUEST['action_filter'] == 1137) ||
    ($arResult['PERMISSIONS']['kurator'] && $_REQUEST['action_filter'] == 1138)
) {
    $arColumns[] = ['id' => 'ISPOLN_DATA', 'name' => 'Ход исполнения поручения', 'default' => true, 'editable' => true];
} else {
    $arColumns[] = ['id' => 'ISPOLN_DATA', 'name' => 'Ход исполнения поручения', 'default' => true];
}

if (count($arResult['DETAIL_DATA']['ELEMENT']['PROPERTY_PORUCH_VALUE']) > 0) {
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

    while ($row = $res->GetNext()) {
        $res2 = CIBlockElement::GetList(
            ['DATE_CREATE' => 'DESC'],
            [
                'IBLOCK_ID'         => (int)$arParams['IBLOCK_ID_ORDERS_COMMENT'],
                'PROPERTY_PORUCH'   => (int)$row['ID'],
                'PROPERTY_TYPE'     => 1132
            ],
            false,
            ['nPageSize' => 1],
            [
                'ID',
                'IBLOCK_ID',
                'NAME',
                'DATE_CREATE',
                'PREVIEW_TEXT',
                'DETAIL_TEXT',
                'PROPERTY_TYPE',
                'PROPERTY_USER',
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
            'class_info'   => (new Orders())->getColor($row['ID']),
            'actions' => [
                [
                    'text'    => 'Просмотр',
                    'default' => true,
                    'onclick' => 'document.location.href="?detail=' . $row['ID'] . '"',
                ],
            ],
        ];
        if (substr_count($row['PROPERTY_VIEWS_VALUE'], ',' . $USER->GetID() . ',') == 0) {
            $arrData['class'] = 'font-weight-bold';
        }

        $list[] = $arrData;
    }
}
?>
<?$APPLICATION->IncludeComponent(
    'bitrix:main.ui.grid',
    'modified',
    [
        'GRID_ID'                   => 'list_svyazi',
        'COLUMNS'                   => $arColumns,
        'ROWS'                      => $list,
        'SHOW_ROW_CHECKBOXES'       => false,
        'NAV_OBJECT'                => $nav,
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
        'ACTION_PANEL'              => $ACTION_PANEL,
        'SHOW_GRID_SETTINGS_MENU'   => true,
        'SHOW_NAVIGATION_PANEL'     => true,
        'SHOW_PAGINATION'           => true,
        'SHOW_SELECTED_COUNTER'     => false,
        'SHOW_TOTAL_COUNTER'        => true,
        'SHOW_PAGESIZE'             => true,
        'SHOW_ACTION_PANEL'         => false,
        'ALLOW_COLUMNS_SORT'        => true,
        'ALLOW_COLUMNS_RESIZE'      => true,
        'ALLOW_HORIZONTAL_SCROLL'   => true,
        'ALLOW_SORT'                => true,
        'ALLOW_PIN_HEADER'          => true,
        'AJAX_OPTION_HISTORY'       => 'N',
    ]
);?>
</div>