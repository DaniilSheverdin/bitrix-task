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

$arResult = [
    'LIST_ID'   => 'project_report_main',
    'FILTER'    => [],
];

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
    'IBLOCK_ID' => ProjectInitiative::$bizProcId,
    'ACTIVE' => 'Y'
];
$res = CIBlockElement::GetList(
    $arSort['sort'],
    $arFilter,
    false,
    $arNavParams,
    [
        'ID',
        'NAME',
        'DATE_CREATE',
        'PROPERTY_TARGET',
        'PROPERTY_TERMS',
        'PROPERTY_AUDIENCE',
        'PROPERTY_FREQ',
        'PROPERTY_EXP_RESULT',
        'PROPERTY_RISKS',
        'PROPERTY_FZ',
        'PROPERTY_USER',
        'PROPERTY_FILES',
        'PROPERTY_TASK_ID',
        'PROPERTY_SUBTASK1_ID',
        'PROPERTY_SUBTASK2_ID',
        'PROPERTY_FILE_CONCEPT',
        'PROPERTY_FILE_TZ',
        'PROPERTY_FILE_USTAV',
        'PROPERTY_GROUP_ID',
    ]
);
$arFileFields = [
    'FILE_CONCEPT',
    'FILE_TZ',
    'FILE_USTAV',
];
$totalCount = $res->SelectedRowsCount();
$obNav->setRecordCount($totalCount);
$driver = \Bitrix\Disk\Driver::getInstance();
$urlManager = $driver->getUrlManager();
while ($row = $res->Fetch()) {
    if (empty($row['PROPERTY_GROUP_ID_VALUE'])) {
        if (empty($row['PROPERTY_TASK_ID_VALUE'])) {
            continue;
        }
        $arTasksPercent = ProjectInitiative::calcTasksPercent(0, $row['PROPERTY_TASK_ID_VALUE']);
        $type = 'Проектная инициатива';
        $open = 'Открыть проектную инициативу';
        $link = '/workgroups/group/' . ProjectInitiative::$groupId . '/tasks/task/view/' . $row['PROPERTY_TASK_ID_VALUE'] . '/';
        $deviation = '-';
    } else {
        $arTasksPercent = ProjectInitiative::calcTasksPercent($row['PROPERTY_GROUP_ID_VALUE']);
        $type = 'Проект';
        $open = 'Открыть проект';
        $link = '/workgroups/group/' . $row['PROPERTY_GROUP_ID_VALUE'] . '/';
        $deviation = ProjectInitiative::calcProjectDeviation($row['PROPERTY_GROUP_ID_VALUE']);
    }

    $percent = 0;
    foreach ($arTasksPercent as $arTask) {
        if ($arTask['STATUS'] == 5) {
            $percent += $arTask['MAX_PERCENT'];
        }
    }

    $arUsers = [];
    if (!empty($row['PROPERTY_USER_VALUE'])) {
        foreach ($row['PROPERTY_USER_VALUE'] as $uId) {
            $arUser = CUser::GetById($uId)->Fetch();
            $arUsers[] = implode(' ', [$arUser['LAST_NAME'], $arUser['NAME']]);
        }
    }

    $arData = [
        'data'    => [
            'ID'            => $row['ID'],
            'DATE_CREATE'   => $row['DATE_CREATE'],
            'TYPE'          => $type,
            'NAME'          => $row['NAME'],
            'PERCENT'       => number_format($percent, 2, ',', '') . '%',
            'DEVIATION'     => $deviation['sum'],
            'TARGET'        => $row['PROPERTY_TARGET_VALUE'],
            'TERMS'         => $row['PROPERTY_TERMS_VALUE'],
            'AUDIENCE'      => $row['PROPERTY_AUDIENCE_VALUE'],
            'FREQ'          => $row['PROPERTY_FREQ_VALUE'],
            'EXP_RESULT'    => $row['PROPERTY_EXP_RESULT_VALUE'],
            'RISKS'         => $row['PROPERTY_RISKS_VALUE'],
            'FZ'            => $row['PROPERTY_FZ_VALUE'],
            'USER'          => implode(', ', $arUsers),
        ],
        'class' => 'alert-white',
        'actions' => [
            [
                'text'      => $open,
                'default'   => true,
                'onclick'   => 'window.open(\'' . $link . '\');'
            ]
        ]
    ];
    if (!is_array($row['PROPERTY_FILES_VALUE'])) {
        $row['PROPERTY_FILES_VALUE'] = [
            $row['PROPERTY_FILES_VALUE']
        ];
    }
    $arFiles = [];
    foreach ($row['PROPERTY_FILES_VALUE'] as $fileId) {
        $arFiles[] = CFile::ShowFile($fileId, 1);
    }
    $arData['data']['FILES'] = implode('<br/>', $arFiles);

    /**
     * Из аттачей вытащить реальные файлы
     */
    foreach ($arFileFields as $sField) {
        $arFiles = [];
        $row['PROPERTY_' . $sField . '_VALUE'] = explode(',', $row['PROPERTY_' . $sField . '_VALUE']);
        $row['PROPERTY_' . $sField . '_VALUE'] = array_filter($row['PROPERTY_' . $sField . '_VALUE']);

        foreach ($row['PROPERTY_' . $sField . '_VALUE'] as $fileId) {
            if ($obFileID = \Bitrix\Disk\AttachedObject::loadById($fileId)) {
                $obFile = $obFileID->getObject();
                if ($obFile) {
                    $arFile     = $obFile->getFile();
                    $link       = $urlManager::getUrlUfController('download', array('attachedId' => (int)$fileId));
                    $arFiles[]  = '<a href="' . $link . '">' . $arFile['ORIGINAL_NAME'] . '<a/>';
                }
            }
        }

        $arData['data'][ $sField ] = implode('<br/>', $arFiles);
    }

    $arTasks = [
        $row['PROPERTY_TASK_ID_VALUE'],
    ];
    /**
     * Собрать ID подзадач любого уровня
     */
    $arTasks = array_merge(
        $arTasks,
        \CTasks::getTaskSubTree($row['PROPERTY_TASK_ID_VALUE'])
    );
    $arTaskData = [];
    $resTask = CTasks::GetList(
        ['ID' => 'ASC'],
        ['ID' => $arTasks],
        ['ID', 'TITLE', 'GROUP_ID']
    );
    while ($arTask = $resTask->GetNext()) {
        $arTaskData[ $arTask['ID'] ] = '<a href="' . makeTaskLink($arTask) . '" target="_blank">' . $arTask['TITLE'] . '</a>';
    }
    $arData['data']['TASKS'] = implode('<br/>', $arTaskData);

    $arResult['ROWS'][] = $arData;
}
$arResult['NAV'] = $obNav;
$arResult['COLUMNS'] = [
    [
        'id'        => 'TYPE',
        'name'      => 'Тип',
        'sort'      => false,
        'default'   => true
    ],
    [
        'id'        => 'NAME',
        'name'      => 'Название',
        'sort'      => 'name',
        'default'   => true
    ],
    [
        'id'        => 'DATE_CREATE',
        'name'      => 'Дата создания',
        'sort'      => 'name',
        'default'   => true
    ],
    [
        'id'        => 'PERCENT',
        'name'      => '% завершения',
        'sort'      => false,
        'default'   => true
    ],
    [
        'id'        => 'DEVIATION',
        'name'      => 'Отклонение от плана',
        'sort'      => false,
        'default'   => true
    ],
    [
        'id'        => 'TARGET',
        'name'      => 'Цель',
        'sort'      => false,
        'default'   => true
    ],
    [
        'id'        => 'TERMS',
        'name'      => 'Требуемые сроки реализации',
        'sort'      => false,
        'default'   => true
    ],
    [
        'id'        => 'AUDIENCE',
        'name'      => 'Целевая аудитория',
        'sort'      => false,
        'default'   => true
    ],
    [
        'id'        => 'FREQ',
        'name'      => 'Частота использования',
        'sort'      => false,
        'default'   => true
    ],
    [
        'id'        => 'EXP_RESULT',
        'name'      => 'Ожидаемый результат',
        'sort'      => false,
        'default'   => true
    ],
    [
        'id'        => 'RISKS',
        'name'      => 'Риски',
        'sort'      => false,
        'default'   => true
    ],
    [
        'id'        => 'FZ',
        'name'      => 'Функциональный Заказчик',
        'sort'      => 'PROPERTY_FZ',
        'default'   => true
    ],
    [
        'id'        => 'USER',
        'name'      => 'Контакты ответственного сотрудника со стороны ФЗ',
        'sort'      => false,
        'default'   => true
    ],
    [
        'id'        => 'FILES',
        'name'      => 'Прикреплённые файлы',
        'sort'      => false,
        'default'   => true
    ],
    [
        'id'        => 'FILE_CONCEPT',
        'name'      => 'Концепт',
        'sort'      => false,
        'default'   => true
    ],
    [
        'id'        => 'FILE_TZ',
        'name'      => 'ТЗ',
        'sort'      => false,
        'default'   => true
    ],
    [
        'id'        => 'FILE_USTAV',
        'name'      => 'Устав',
        'sort'      => false,
        'default'   => true
    ],
    [
        'id'        => 'TASKS',
        'name'      => 'Задачи',
        'sort'      => false,
        'default'   => true
    ],
];
?>
<form method="get" class="m-3">
    <b>Выгрузить отчёт</b><br/>
    <div class="row">
        <div class="col-2 ui-ctl ui-ctl-textbox ui-ctl-inline">
            <input
                class="ui-ctl-element"
                name="DATE_START"
                value="<?=date('d.m.Y', strtotime('this week monday -1 week'));?>"
                placeholder="Начало интервала"
                onclick="BX.calendar({node:this,field:this,bTime:true});"
                required />
        </div>
        <div class="col-2 ui-ctl ui-ctl-textbox ui-ctl-inline">
            <input
                class="ui-ctl-element"
                name="DATE_FINISH"
                value="<?=date('d.m.Y', strtotime('this week sunday -1 week'));?>"
                placeholder="Конец интервала"
                onclick="BX.calendar({node:this,field:this,bTime:true});"
                required />
        </div>
        <div class="col-7">
            <input
                class="ui-btn ui-btn-primary"
                name="tasks"
                value="Задачи"
                type="submit" />
            &nbsp;&nbsp;
            <input
                class="ui-btn ui-btn-primary"
                name="projects"
                value="Проекты"
                type="submit" />
            &nbsp;&nbsp;
            <input
                class="ui-btn ui-btn-primary"
                name="dates"
                value="Сроки"
                type="submit" />
        </div>
        <div class="col-12 pt-4">
            <input
                class="ui-btn ui-btn-primary"
                name="managers"
                value="По руководителям проектов"
                type="submit" />
            &nbsp;&nbsp;
            <input
                class="ui-btn ui-btn-primary"
                name="utilisation"
                value="Утилизация (ШЕ)"
                type="submit" />
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
