<?php

use CSocNetGroup;
use CIBlockElement;
use DateTimeImmutable;
use Bitrix\Main\Loader;
use Citto\Tasks\ProjectInitiative;
use Bitrix\Tasks\Internals\Task\LogTable;

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
Loader::includeModule('socialnetwork');
Loader::includeModule('tasks');
Loader::IncludeModule('forum');

global $userFields;

$forumId = \Bitrix\Tasks\Integration\Forum\Task\Comment::getForumId();
$parser = new forumTextParser();

$arResult = [
    'TITLE'     => 'Отчёт по срокам (' . (new DateTimeImmutable())->format('d.m.y') . ')',
    'FILENAME'  => 'Отчёт по срокам (' . (new DateTimeImmutable())->format('d.m.y') . ')',
    'HEADERS'   => [
        'PROJECT' => [
            'NAME'  => 'Проект',
            'WIDTH' => 20,
        ],
        'OWNER' => [
            'NAME'  => 'Ответственный',
            'WIDTH' => 20,
        ],
        'CONCEPT' => [
            'NAME'  => 'Концепт',
            'WIDTH' => 20,
        ],
        'TZ' => [
            'NAME'  => 'ТЗ',
            'WIDTH' => 20,
        ],
        'ZAKUP' => [
            'NAME'  => 'Объявление закупки',
            'WIDTH' => 20,
        ],
        'DOGOVOR' => [
            'NAME'  => 'Договор',
            'WIDTH' => 20,
        ],
        'PRIEMKA' => [
            'NAME'  => 'Приемка',
            'WIDTH' => 20,
        ],
    ],
    'ROWS' => [],
];

$arFilter = [
    'IBLOCK_ID' => ProjectInitiative::$bizProcId,
    'ACTIVE' => 'Y'
];
$res = CIBlockElement::GetList(
    ['ID' => 'ASC'],
    $arFilter,
    false,
    false,
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
while ($row = $res->GetNext()) {
    if (empty($row['PROPERTY_TASK_ID_VALUE'])) {
        continue;
    }
    $data = [
        'PROJECT'   => [
            'VALUE' => $row['NAME'],
            'LINK'  => 'https://' .
                        $_SERVER['SERVER_NAME'] .
                        SITE_DIR .
                        'workgroups/group/' .
                        ProjectInitiative::$groupId .
                        '/tasks/task/view/' .
                        $row['PROPERTY_TASK_ID_VALUE'] .
                        '/'
        ],
        'OWNER'     => [
            'VALUE' => ''
        ],
        'CONCEPT'   => [
            'VALUE' => ''
        ],
        'TZ'        => [
            'VALUE' => ''
        ],
        'ZAKUP'     => [
            'VALUE' => ''
        ],
        'DOGOVOR'   => [
            'VALUE' => ''
        ],
        'PRIEMKA'   => [
            'VALUE' => ''
        ],
    ];

    try {
        $oTaskItem = \CTaskItem::getInstance($row['PROPERTY_TASK_ID_VALUE'], 1);
        $arTask = $oTaskItem->getData();
        $userData = $userFields($arTask['RESPONSIBLE_ID']);
        $data['OWNER']['VALUE'] = $userData['FIO_INIC_REV'];
    } catch (\Exception $e) {
        $data['OWNER']['VALUE'] = '';
    }

    $arTasks = [
        'CONCEPT'   => (int)$row['PROPERTY_SUBTASK1_ID_VALUE'],
        'TZ'        => (int)$row['PROPERTY_SUBTASK2_ID_VALUE'],
        'ZAKUP'     => 0,
        'DOGOVOR'   => 0,
        'PRIEMKA'   => 0,
    ];

    if ($row['PROPERTY_GROUP_ID_VALUE'] > 0) {
        $resGroup = CSocNetGroup::GetList(
            ['DATE_CREATE' => 'ASC'],
            ['ID' => $row['PROPERTY_GROUP_ID_VALUE']]
        );
        if ($arGroup = $resGroup->Fetch()) {
            $data['PROJECT']['VALUE'] = $arGroup['NAME'];
            $data['PROJECT']['LINK'] = 'https://' .
                                        $_SERVER['SERVER_NAME'] .
                                        SITE_DIR .
                                        'workgroups/group/' .
                                        ProjectInitiative::$groupId .
                                        '/';

            $userData = $userFields($arGroup['OWNER_ID']);
            $data['OWNER']['VALUE'] = $userData['FIO_INIC_REV'];
        }

        $arTasksPercent = ProjectInitiative::calcTasksPercent($row['PROPERTY_GROUP_ID_VALUE']);
        foreach ($arTasksPercent as $taskRow) {
            if ($taskRow['TITLE'] == 'Проведение закупочных процедур') {
                $arTasks['ZAKUP'] = $taskRow['ID'];
            } elseif ($taskRow['TITLE'] == 'Заключение договора') {
                $arTasks['DOGOVOR'] = $taskRow['ID'];
            } elseif ($taskRow['TITLE'] == 'Приемка') {
                $arTasks['PRIEMKA'] = $taskRow['ID'];
            }
        }
    } elseif ($arTask['REAL_STATUS'] == 5) {
        // В отчете по срокам нужно не отображать те проектные инициативы,
        // которые полностью закрылись, но на их основании не был создан проект.
        // Если они возобновляются, то опять попадают в отчет.
        continue;
    }

    if (empty($data['OWNER']['VALUE'])) {
        continue;
    }

    $MESS = [];
    $MESS['TASKS_STATUS_1'] = 'Новая';
    $MESS['TASKS_STATUS_2'] = 'Ждет выполнения';
    $MESS['TASKS_STATUS_3'] = 'Выполняется';
    $MESS['TASKS_STATUS_4'] = 'Ждет контроля';
    $MESS['TASKS_STATUS_5'] = 'Завершена';
    $MESS['TASKS_STATUS_6'] = 'Отложена';
    $MESS['TASKS_STATUS_7'] = 'Отклонена';

    foreach ($arTasks as $field => $id) {
        if ((int)$id <= 0) {
            continue;
        }

        try {
            $oTaskItem = \CTaskItem::getInstance($id, 1);
            $arTask = $oTaskItem->getData();

            // Достать из истории изменения статуса
            $oTaskLog = LogTable::getList([
                'filter' => [
                    'TASK_ID' => $id,
                    'FIELD' => 'STATUS',
                ],
                'order' => [
                    'ID' => 'ASC'
                ]
            ]);
            $arStatusHostory = [];
            while ($rowLog = $oTaskLog->fetch()) {
                $arStatusHostory[ $rowLog['TO_VALUE'] ] = $rowLog['CREATED_DATE']->format('d.m.Y H:i:s');
            }

            $dateComplete = null;
            if (isset($arStatusHostory[4])) {
                $dateComplete = new DateTimeImmutable($arStatusHostory[4]);
            } elseif (isset($arStatusHostory[5])) {
                $dateComplete = new DateTimeImmutable($arStatusHostory[5]);
            }

            $data[ $field ]['LINK'] = 'https://' .
                                        $_SERVER['SERVER_NAME'] .
                                        SITE_DIR .
                                        'workgroups/group/' .
                                        $arTask['GROUP_ID'] .
                                        '/tasks/task/view/' .
                                        $arTask['ID'] .
                                        '/';

            if (!empty($arTask['DEADLINE'])) {
                $deadline = new DateTimeImmutable($arTask['DEADLINE']);
                $value = $deadline->format('d.m.Y');

                if (is_null($dateComplete)) {
                    // Задача еще в работе
                    $date = new DateTimeImmutable();
                } else {
                    $date = $dateComplete;
                }

                if ($date > $deadline) {
                    $data[ $field ]['BGCOLOR'] = 'ff0000'; // Красный
                } elseif ($deadline > (new DateTimeImmutable())) {
                    $data[ $field ]['BGCOLOR'] = '90ee90'; // Светло-Зелёный
                } else {
                    $data[ $field ]['BGCOLOR'] = '00a550'; // Зелёный
                }
            } else {
                $value = 'Без крайнего срока';
            }

            $data[ $field ]['VALUE'] = $value;
        } catch (\Exception $e) {
            $data[ $field ]['VALUE'] = '';
        }
    }

    $arResult['ROWS'][] = $data;
}

exportExcel($arResult);
exit;
