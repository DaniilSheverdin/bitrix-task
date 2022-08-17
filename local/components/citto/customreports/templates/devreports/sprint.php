<?php

use Bitrix\Main\UserTable;
use Citto\Tasks\DevSprints;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) {
    die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

global $userFields;

if (isset($_REQUEST['sprintId'])) {
    $sprintId = (int)$_REQUEST['sprintId'];
    $obSprint = new DevSprints();
    $arSprint = $obSprint->getByID($sprintId);
    $arTags = $arSprint['UF_TAGS'];
    $arTasks = $arSprint['UF_TASKS'];
    $title = 'Отчет по задачам спринта (' . $arSprint['NAME'] . ')';
    $sprintName = $arSprint['NAME'];
} else {
    $tag = base64_decode($_REQUEST['sprint']);
    $title = 'Отчет по задачам тега (' . $tag . ')';
    $sprintName = $tag;
    $arTags = [$tag];
    $arTasks = [];
}

$APPLICATION->SetTitle($title);

$arGroupInfo = [];
$resGroup = CSocNetGroup::GetList(
    ['DATE_CREATE' => 'ASC'],
    [
        'ACTIVE' => 'Y'
    ],
    false,
    false,
    ['*', 'UF_*']
);
while ($arGroup = $resGroup->Fetch()) {
    $arGroupInfo[ $arGroup['ID'] ] = $arGroup;
}

$arTasks = [];
$arUsers = [];
foreach ($arTags as $tag) {
    $res = CTasks::GetList(
        [],
        [
            'TAG' => trim($tag),
        ]
    );
    while ($row = $res->Fetch()) {
        $arUser = $userFields($row['RESPONSIBLE_ID']);
        if (!in_array(438, $arUser['UF_DEPARTMENT'])) {
            continue;
        }
        $arUsers[ $row['RESPONSIBLE_ID'] ] = $row['RESPONSIBLE_LAST_NAME'] . ' ' . $row['RESPONSIBLE_NAME'];
        $arTasks[ $row['RESPONSIBLE_ID'] ][ $row['ID'] ] = $row['ID'];
    }
}
foreach ($arTasks as $taskId) {
    $res = CTasks::GetList(
        [],
        [
            'ID' => $taskId,
        ]
    );
    while ($row = $res->Fetch()) {
        $arUser = $userFields($row['RESPONSIBLE_ID']);
        if (!in_array(438, $arUser['UF_DEPARTMENT'])) {
            continue;
        }
        $arUsers[ $row['RESPONSIBLE_ID'] ] = $row['RESPONSIBLE_LAST_NAME'] . ' ' . $row['RESPONSIBLE_NAME'];
        $arTasks[ $row['RESPONSIBLE_ID'] ][ $row['ID'] ] = $row['ID'];
    }
}

$orm = UserTable::getList([
    'select' => ['ID', 'NAME', 'LAST_NAME', 'UF_DEPARTMENT']
]);
$arAllUsers = [];
while ($arUser = $orm->fetch()) {
    $arAllUsers[ $arUser['ID'] ] = $arUser['LAST_NAME'] . ' ' . $arUser['NAME'];
}

asort($arUsers);

$MESS["TASKS_STATUS_1"] = "Новая";
$MESS["TASKS_STATUS_2"] = "Ждет выполнения";
$MESS["TASKS_STATUS_3"] = "Выполняется";
$MESS["TASKS_STATUS_4"] = "Ждет контроля";
$MESS["TASKS_STATUS_5"] = "Завершена";
$MESS["TASKS_STATUS_6"] = "Отложена";
$MESS["TASKS_STATUS_7"] = "Отклонена";

$arResult = [
    'TITLE'     => $sprintName,
    'FILENAME'  => $title,
    'HEADERS'   => [
        'RESPONSIBLE' => [
            'NAME'  => 'Разработчик',
            'WIDTH' => 20,
            'MULTI' => true,
        ],
        'ACCOMPLICES' => [
            'NAME'  => 'Соисполнители',
            'WIDTH' => 20,
        ],
        'ID' => [
            'NAME'  => 'Номер задачи',
            'WIDTH' => 20,
        ],
        'TITLE' => [
            'NAME'  => 'Название задачи',
            'WIDTH' => 20,
        ],
        'PROJECT' => [
            'NAME'  => 'Проект',
            'WIDTH' => 20,
        ],
        'AUTHOR' => [
            'NAME'  => 'Постановщик',
            'WIDTH' => 20,
        ],
        'ESTIMATE' => [
            'NAME'  => 'Выделенные часы',
            'WIDTH' => 20,
        ],
        'ELAPSED' => [
            'NAME'  => 'Затраченное время',
            'WIDTH' => 20,
        ],
        'STATUS' => [
            'NAME'  => 'Статус (' . date('d.m.y') . ')',
            'WIDTH' => 20,
        ],
        'TAGS' => [
            'NAME'  => 'Теги',
            'WIDTH' => 20,
        ],
    ],
    'ROWS' => [],
];

foreach ($arUsers as $uId => $name) {
    if (!isset($arTasks[ $uId ])) {
        continue;
    }

    $first = true;
    foreach ($arTasks[ $uId ] as $task) {
        $obTask = CTaskItem::getInstance($task, 1);
        $arTask = $obTask->getData();
        $parameters = [
            'MAKE_ACCESS_FILTER' => true,
        ];
        $getListParameters = [
            'select' => [
                'ID',
                'TITLE',
                'CREATED_BY',
                'RESPONSIBLE_ID',
                'AUDITORS',
                'ACCOMPLICES',
                'TIME_ESTIMATE',
            ],
            'legacyFilter' => ['ID' => $task],
        ];
        $mgrResult = Bitrix\Tasks\Manager\Task::getList(1, $getListParameters, $parameters);
        $arTask = array_merge($arTask, $mgrResult['DATA'][ $task ]);
        $res = CTaskElapsedTime::GetList(
            [], 
            ['TASK_ID' => $task]
        );
        $elapsedTime = 0;
        while ($arElapsed = $res->Fetch()) {
            $elapsedTime += $arElapsed["SECONDS"];
        }
        $arAccomplices = [];
        foreach ($arTask['ACCOMPLICES'] as $accId) {
            $arAccomplices[] = $arAllUsers[ $accId ];
        }

        $arRow = [
            'RESPONSIBLE'   => [
                'VALUE' => $arAllUsers[ $uId ],
                'LINK'  => 'https://corp.tularegion.ru/citto/company/personal/user/' . $uId . '/',
                'ROWS'  => count($arTasks[ $uId ])
            ],
            'ACCOMPLICES' => [
                'VALUE' => implode(', ', $arAccomplices),
            ],
            'ID' => [
                'VALUE' => $arTask['ID'],
            ],
            'TITLE' => [
                'VALUE' => $arTask['TITLE'],
                'LINK'  => 'https://corp.tularegion.ru/citto/company/personal/user/' . $uId . '/tasks/task/view/' . $arTask['ID'] . '/',
            ],
            'PROJECT' => ($arTask['GROUP_ID'] > 0) ? [
                'VALUE' => $arGroupInfo[ $arTask['GROUP_ID'] ]['NAME'],
                'LINK'  => 'https://corp.tularegion.ru/citto/workgroups/group/' . $arTask['GROUP_ID'] . '/',
            ] : [
                'VALUE' => 'Без проекта',
            ],
            'AUTHOR' => [
                'VALUE' => $arAllUsers[ $arTask['CREATED_BY'] ],
                'LINK' => 'https://corp.tularegion.ru/citto/company/personal/user/' . $arTask['CREATED_BY'] . '/'
            ],
            'ESTIMATE' => [
                'VALUE' => $arTask['TIME_ESTIMATE'] > 0 ? humanifyTime($arTask['TIME_ESTIMATE']) : '',
            ],
            'ELAPSED' => [
                'VALUE' => $elapsedTime > 0 ? humanifyTime($elapsedTime) : '',
            ],
            'STATUS' => [
                'VALUE' => $MESS['TASKS_STATUS_' . $arTask['REAL_STATUS'] ],
            ],
            'TAGS' => [
                'VALUE' => implode(', ', $obTask->getTags()),
            ],
        ];

        $arResult['ROWS'][] = $arRow;
    }
}

if (isset($_REQUEST['export'])) {
    exportExcel($arResult);
    exit;
}

?>
<a class="ui-btn ui-btn-primary" href="<?=$APPLICATION->GetCurPageParam('export')?>">Выгрузить в Excel</a>
<br/><br/>
<table class="table table-bordered">
    <thead>
        <tr>
            <?foreach ($arResult['HEADERS'] as $header) : ?>
            <th><?=$header['NAME']?></th>
            <?endforeach;?>
        </tr>
    </thead>
    <tbody>
        <?
        $first = '';
        foreach ($arResult['ROWS'] as $row) {
            ?>
            <tr>
                <?
                foreach (array_keys($arResult['HEADERS']) as $header) {
                    if ($arResult['HEADERS'][ $header ]['MULTI']) {
                        if ($first != $row[ $header ]['VALUE']) {
                            $first = $row[ $header ]['VALUE'];
                            ?>
                            <th rowspan="<?=$row[ $header ]['ROWS']?>">
                                <?if (isset($row[ $header ]['LINK'])) : ?>
                                    <a href="<?=$row[ $header ]['LINK']?>" target="_blank">
                                        <?=$row[ $header ]['VALUE']?>
                                    </a>
                                <?else : ?>
                                    <?=$row[ $header ]['VALUE']?>
                                <?endif;?>
                            </th>
                            <?
                        }
                    } else {
                        ?>
                        <td>
                            <?if (isset($row[ $header ]['LINK'])) : ?>
                                <a href="<?=$row[ $header ]['LINK']?>" target="_blank">
                                    <?=$row[ $header ]['VALUE']?>
                                </a>
                            <?else : ?>
                                <?=$row[ $header ]['VALUE']?>
                            <?endif;?>
                        </td>
                        <?
                    }
                }
                ?>
            </tr>
            <?
        }
        ?>
    </tbody>
</table>