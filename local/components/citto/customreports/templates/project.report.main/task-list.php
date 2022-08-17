<?php

use CJSCore;
use Bitrix\Main\Loader;
use Bitrix\Main\UI\Extension;
use Citto\Tasks\ProjectInitiative;
use Bitrix\Tasks\Internals\Task\LogTable;
use Bitrix\Main\Grid\Options as GridOptions;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) {
    die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass.' ' : '').'no-all-paddings no-background');
Extension::load(array("ui.buttons", "ui.alerts", "ui.tooltip", "ui.hint"));
CJSCore::Init("loader");
CJSCore::Init("sidepanel");
Loader::includeModule('tasks');
// $arTaskIds = CUtil::JsObjectToPhp($_REQUEST['id']);
$arTaskIds = explode(',', $_REQUEST['ids']);
$arTaskIds = array_unique(array_filter($arTaskIds));
if (empty($arTaskIds)) {
    die('Нет задач');
}
$MESS["TASKS_STATUS_1"] = "Новая";
$MESS["TASKS_STATUS_2"] = "Ждет выполнения";
$MESS["TASKS_STATUS_3"] = "Выполняется";
$MESS["TASKS_STATUS_4"] = "Ждет контроля";
$MESS["TASKS_STATUS_5"] = "Завершена";
$MESS["TASKS_STATUS_6"] = "Отложена";
$MESS["TASKS_STATUS_7"] = "Отклонена";
$arColumns   = [
    ['id' => 'ID', 'name' => 'ID', 'sort' => 'ID', 'default' => true],
    ['id' => 'NAME', 'name' => 'Название задачи', 'sort' => 'NAME', 'default' => true],
    ['id' => 'TEXT', 'name' => 'Описание задачи', 'sort' => 'TEXT', 'default' => false],
    ['id' => 'ISPOLNITEL', 'name' => 'Исполнитель', 'sort' => 'ISPOLNITEL', 'default' => true],
    ['id' => 'DEADLINE', 'name' => 'Крайний срок', 'sort' => 'DEADLINE_TS', 'default' => true],
    ['id' => 'CONTROL', 'name' => 'Дата перевода на контроль', 'sort' => 'CONTROL_TS', 'default' => true],
    ['id' => 'STATUS', 'name' => 'Статус', 'sort' => 'STATUS_ID', 'default' => true],
];
$list = [];
$resTasks = CTasks::GetList(
    [],
    [
        'ID' => $arTaskIds
    ]
);
while ($arTask = $resTasks->Fetch()) {
    // Достать из истории изменения статуса
    $oTaskLog = LogTable::getList([
        'filter' => [
            'TASK_ID'   => $arTask['ID'],
            'FIELD'     => 'STATUS',
        ],
        'order' => [
            'ID' => 'ASC'
        ]
    ]);
    $arStatusHistory = [];
    while ($rowLog = $oTaskLog->fetch()) {
        $arStatusHistory[ $rowLog['TO_VALUE'] ] = $rowLog['CREATED_DATE']->format('d.m.Y H:i:s');
    }

    $dateComplete = null;
    if (isset($arStatusHistory[4])) {
        $dateComplete = $arStatusHistory[4];
    } elseif (isset($arStatusHistory[5])) {
        $dateComplete = $arStatusHistory[5];
    }

    $link = '/company/personal/user/'.$USER->GetID().'/tasks/task/view/' . $arTask['ID'] . '/';
    $arrData = [
        'data'    => [
            "ID"            => $arTask['ID'],
            "NAME"          => '<a href="' . $link . '" target="_blank">' . $arTask['TITLE'] . '</a>',
            "TEXT"          => $arTask['DESCRIPTION'],
            "ISPOLNITEL"    => $arTask['RESPONSIBLE_LAST_NAME'] . ' ' . $arTask['RESPONSIBLE_NAME'],
            "DEADLINE"      => $arTask['DEADLINE'],
            "DEADLINE_TS"   => strtotime($arTask['DEADLINE']),
            "CONTROL"       => $dateComplete,
            "CONTROL_TS"    => strtotime($dateComplete),
            "STATUS"        => $MESS['TASKS_STATUS_' . $arTask['STATUS'] ],
            "STATUS_ID"     => $arTask['STATUS'],
        ],
    ];
    $list[] = $arrData;
}

$list_id = 'reporting_task_list';
$grid_options = new GridOptions($list_id);

function build_sorter($key, $order)
{
    return function ($a, $b) use ($key, $order) {
        return $order=='asc' ?
            strnatcmp('str-' . $a['data'][ $key ], 'str-' . $b['data'][ $key ]) :
            strnatcmp('str-' . $b['data'][ $key ], 'str-' . $a['data'][ $key ]);
    };
}
$sort = $grid_options->GetSorting(
    [
        'sort' => [
            'ID'   => 'asc',
        ],
        'vars' => [
            'by'    => 'by',
            'order' => 'order'
        ]
    ]
);

$by = array_keys($sort['sort'])[0];
$order = $sort['sort'][ $by ];

usort(
    $list,
    build_sorter($by, $order)
);

$cntRows = count($list);

$navParams = $grid_options->GetNavParams();
$obList = new CDBResult();
$obList->InitFromArray($list);
$obList->NavStart($navParams['nPageSize']);
$arList = [];
while ($arrData = $obList->Fetch()) {
    $arList[] = $arrData;
}
?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="ru" class="bx-core" lang="ru"><head>
    <script type="text/javascript">
        if(window == window.top)
        {
            window.location = "<?=CUtil::JSEscape($APPLICATION->GetCurPageParam('', array('IFRAME'))); ?>";
        }
    </script>
    <link rel="stylesheet" type="text/css" href="/bitrix/components/bitrix/ui.sidepanel.wrapper/templates/.default/template.css">
    <?$APPLICATION->ShowHead();?>
    <style type="text/css">
        .main-grid-table {
            background: #fff;
        }
    </style>
</head>
<body class="ui-page-slider-wrapper ui-page-slider-padding template-bitrix24 ui-page-slider-wrapper-default-theme task-iframe-popup page-one-column top-menu-mode pagetitle-toolbar-field-view tasks-pagetitle-view grid-mode">
<div class="ui-slider-page">
    <div id="ui-page-slider-content">
        <div class="ui-side-panel-wrap-title-wrap" style="">
            <div class="ui-side-panel-wrap-title-inner-container">
                <div class="ui-side-panel-wrap-title">
                    <span id="pagetitle" class="ui-side-panel-wrap-title-item ui-side-panel-wrap-title-name">Список задач</span>
                </div>
            </div>
        </div>
        <?$APPLICATION->IncludeComponent(
            'bitrix:main.ui.grid',
            '',
            [
                'GRID_ID'                   => $list_id,
                'COLUMNS'                   => $arColumns,
                'ROWS'                      => $arList,
                'SHOW_ROW_CHECKBOXES'       => false,
                'NAV_OBJECT'                => $obList,
                'AJAX_MODE'                 => 'N',
                'AJAX_ID'                   => CAjax::getComponentID('bitrix:main.ui.grid', '', ''),
                'PAGE_SIZES'                => [
                    ['NAME' => '20', 'VALUE' => '20'],
                    ['NAME' => '50', 'VALUE' => '50'],
                    ['NAME' => '100', 'VALUE' => '100'],
                ],
                'AJAX_OPTION_JUMP'          => 'N',
                'SHOW_CHECK_ALL_CHECKBOXES' => false,
                'SHOW_ROW_ACTIONS_MENU'     => true,
                'ACTION_PANEL'              => false,
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
                'TOTAL_ROWS_COUNT'          => $cntRows,
            ]
        );?>
    </div>
</div>
</body>
</html>
<?

if ($_REQUEST['IFRAME'] == 'Y') {
    die;
}
