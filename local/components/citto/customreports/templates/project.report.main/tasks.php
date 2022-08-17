<?php

use Bitrix\Main\Loader;
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
Loader::includeModule('socialnetwork');
Loader::includeModule('tasks');
Loader::IncludeModule('forum');

$tsStart     = strtotime($_REQUEST['DATE_START'] ?? 'this week monday -1 week');
$tsFinish    = strtotime($_REQUEST['DATE_FINISH'] ?? 'this week sunday -1 week');

$obDateStart    = new DateTimeImmutable(date('d.m.Y 00:00:00', $tsStart));
$obDateFinish   = new DateTimeImmutable(date('d.m.Y 23:59:59', $tsFinish));
$obDateNext     = $obDateFinish->add($obDateStart->diff($obDateFinish));

$forumId    = \Bitrix\Tasks\Integration\Forum\Task\Comment::getForumId();
$parser     = new forumTextParser();

$arTaskIds = [
    34229,
    34234,
    34932,
    34946,
    39565,
    41464,
    44585,
    45522
];
$arFilter = [
    'IBLOCK_ID' => ProjectInitiative::$bizProcId,
    'ACTIVE'    => 'Y'
];
$resInititive = CIBlockElement::GetList(
    [],
    $arFilter,
    false,
    false,
    [
        'ID',
        'PROPERTY_TASK_ID',
    ]
);
while ($arInitiative = $resInititive->GetNext()) {
    if ($arInitiative['PROPERTY_TASK_ID_VALUE'] > 0) {
        $arTaskIds[] = $arInitiative['PROPERTY_TASK_ID_VALUE'];
    }
}

$arResult = [
    'TITLE'     => 'Отчёт по задачам',
    'FILENAME'  => 'Отчёт по задачам (' .
                    $obDateStart->format('d.m.y') .
                    '-' .
                    $obDateFinish->format('d.m.y') .
                    ')',
    'HEADERS'   => [
        'PROJECT' => [
            'NAME'  => 'Проектная инициатива',
            'WIDTH' => 25,
        ],
        'OWNER' => [
            'NAME'  => 'Руководитель',
            'WIDTH' => 25,
        ],
        'PERCENT' => [
            'NAME'  => 'Процент выполнения',
            'WIDTH' => 25,
        ],
        'DONE' => [
            'NAME'  => 'Что сделано за период',
            'WIDTH' => 50,
        ],
        'PLAN' => [
            'NAME'  => 'Планы',
            'WIDTH' => 50,
        ],
        'EXPIRED' => [
            'NAME'  => 'Просрочены',
            'WIDTH' => 50,
        ],
        'COMMENTS'  => [
            'NAME' => 'Комментарии',
            'WIDTH' => 50,
        ],
        'JUSTIFICATION' => [
            'NAME'  => 'Обоснование',
            'WIDTH' => 50,
        ],
    ],
    'ROWS'      => [],
];

foreach ($arTaskIds as $taskId) {
    $arTasksPercent = ProjectInitiative::calcTasksPercent(0, $taskId);
    if (count($arTasksPercent) == 2) {
        foreach (array_keys($arTasksPercent) as $key) {
            $arTasksPercent[ $key ]['MAX_PERCENT'] = 50;
        }
    }

    if (!isset($arTasksPercent[ $taskId ])) {
        continue;
    }

    $arClosed   = [];
    $arNext     = [];
    $arExpired  = [];
    $arComments = [];
    $arPercent  = [
        0 => 0
    ];

    foreach ($arTasksPercent as $arTask) {
        $sTaskLink  = makeTaskLink($arTask);
        $feed       = new \Bitrix\Forum\Comments\Feed(
            $forumId,
            [
                'type'      => 'TK',
                'id'        => $arTask['ID'],
                'xml_id'    => 'TASK_' . $arTask['ID']
            ]
        );
        $resComments = CForumMessage::GetList(
            [],
            ['TOPIC_ID' => $feed->getTopic()['ID']]
        );
        while ($rowComment = $resComments->Fetch()) {
            if (empty($rowComment['POST_MESSAGE'])) {
                continue;
            }
            if ($rowComment['POST_MESSAGE'] == 'commentAuxTaskInfo') {
                continue;
            }
            if ($rowComment['NEW_TOPIC'] == 'Y') {
                continue;
            }
            $obPostDate = new DateTimeImmutable($rowComment['POST_DATE']);
            if ($obPostDate >= $obDateStart && $obPostDate <= $obDateFinish) {
                $text = '<a href="' . $sTaskLink . '?MID=' . $rowComment['ID'] . '#com' . $rowComment['ID'] . '">';
                $text .= '[' . $rowComment['POST_DATE'] . '] ';
                $text .= $rowComment['AUTHOR_NAME'] . '</a>: ';
                $text .= $parser->convert($rowComment['POST_MESSAGE']);
                $text = str_replace(
                    ['&quot;', '&lt;', '&gt;'],
                    ['"', '<', '>'],
                    strip_tags($text)
                );
                $arComments[] = $text;
            }
        }

        if ($arTask['ID'] == $taskId) {
            continue;
        }

        // Если задача закрыта раньше конца интервала
        if (!is_null($arTask['CLOSED_DATE_DT']) && $arTask['CLOSED_DATE_DT'] <= $obDateFinish) {
            // Сохранить её процент
            $arPercent[ $arTask['ID'] ] = $arTask['MAX_PERCENT'];
            // Если задача закрыта позже начала интервала
            if ($arTask['CLOSED_DATE_DT'] >= $obDateStart) {
                $arClosed[] = $arTask['TITLE'];
            }
        } elseif (!is_null($arTask['DEADLINE_DT']) && $arTask['DEADLINE_DT'] <= $obDateNext) {
            $arNext[] = $arTask['TITLE'];
        }

        if ($arTask["STATUS"] == CTasks::METASTATE_EXPIRED) {
            $arExpired[] = $arTask['TITLE'];
        }
    }

    if (empty($arClosed) && empty($arNext) && empty($arComments) && in_array($arTasksPercent[ $taskId ]['REAL_STATUS'], [5,6])) {
        continue;
    }

    $arOwner = CUser::GetById($arTasksPercent[ $taskId ]['RESPONSIBLE_ID'])->Fetch();

    $arData = [
        'PROJECT'   => [
            'VALUE' => $arTasksPercent[ $taskId ]['~TITLE'],
            'LINK'  => makeTaskLink($arTasksPercent[ $taskId ])
        ],
        'OWNER'     => [
            'VALUE' => implode(' ', [$arOwner['LAST_NAME'], $arOwner['NAME']]),
        ],
        'PERCENT'   => [
            'VALUE' => number_format(array_sum($arPercent), 2, ',', '') . '%',
        ],
        'DONE'      => [
            'VALUE' => implode(PHP_EOL . PHP_EOL, $arClosed),
        ],
        'PLAN'      => [
            'VALUE' => implode(PHP_EOL . PHP_EOL, $arNext),
        ],
        'EXPIRED'      => [
            'VALUE' => implode(PHP_EOL . PHP_EOL, $arExpired),
        ],
        'COMMENTS'  => [
            'VALUE' => implode(PHP_EOL . PHP_EOL, $arComments)
        ],
        'JUSTIFICATION'  => [
            'VALUE' => $arTasksPercent[ $taskId ]['~UF_JUSTIFICATION'],
        ],
    ];

    $arResult['ROWS'][] = $arData;
}

exportExcel($arResult);
exit;
