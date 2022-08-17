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

$tsStart     = strtotime($_REQUEST['DATE_START'] ?? 'this week monday -1 week');
$tsFinish    = strtotime($_REQUEST['DATE_FINISH'] ?? 'this week sunday -1 week');

$obDateStart    = new DateTimeImmutable(date('d.m.Y 00:00:00', $tsStart));
$obDateFinish   = new DateTimeImmutable(date('d.m.Y 23:59:59', $tsFinish));
$obDateNext     = $obDateFinish->add($obDateStart->diff($obDateFinish));

$arAllGroupIds = ProjectInitiative::$arAllGroupIds;

$arFilter = [
    'IBLOCK_ID' => ProjectInitiative::$bizProcId,
    '!PROPERTY_GROUP_ID' => false,
    'ACTIVE' => 'Y'
];
$resInititive = CIBlockElement::GetList(
    [],
    $arFilter,
    false,
    false,
    [
        'ID',
        'PROPERTY_GROUP_ID',
    ]
);
while ($arInitiative = $resInititive->GetNext()) {
    $iGroupID = $arInitiative['PROPERTY_GROUP_ID_VALUE'];
    if ($iGroupID > 0 && !in_array($iGroupID, $arAllGroupIds)) {
        $arAllGroupIds[] = $iGroupID;
    }
}
sort($arAllGroupIds);

/*
 * @see https://corp.tularegion.local/company/personal/user/2440/tasks/task/view/67010/
 */
$arSkip = [
    617,
    618,
    619,
    620,
    626,
    644,
    672,
    673,
    682,
];
$arGroupInfo = [];
$resGroup = CSocNetGroup::GetList(
    ['DATE_CREATE' => 'ASC'],
    [
        'ACTIVE' => 'Y',
        '!UF_COMPLEXITY' => null,
        '>PROJECT_DATE_FINISH' => date('d.m.Y H:m:s')
    ],
    false,
    false,
    ['*', 'UF_*']
);
while ($arGroup = $resGroup->Fetch()) {
    if (in_array($arGroup['ID'], $arSkip)) {
        continue;
    }

    $arGroupInfo[ $arGroup['ID'] ] = $arGroup;
}

$arResult = [
    'TITLE'     => 'Отчёт по проектам',
    'FILENAME'  => 'Отчёт по проектам (' .
                    $obDateStart->format('d.m.y') .
                    '-' .
                    $obDateFinish->format('d.m.y') .
                    ')',
    'HEADERS'   => [
        'PROJECT' => [
            'NAME'  => 'Проект',
            'WIDTH' => 20,
        ],
        'OWNER' => [
            'NAME'  => 'Руководитель',
            'WIDTH' => 20,
        ],
        'TAGS' => [
            'NAME'  => 'Теги',
            'WIDTH' => 20,
        ],
        'PERCENT' => [
            'NAME'  => 'Процент выполнения',
            'WIDTH' => 20,
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
        'COMMENTS' => [
            'NAME'  => 'Комментарии',
            'WIDTH' => 50,
        ],
        'JUSTIFICATION' => [
            'NAME'  => 'Обоснование',
            'WIDTH' => 50,
        ],
    ],
    'ROWS' => [],
];

foreach ($arAllGroupIds as $groupId) {
    if (!isset($arGroupInfo[ $groupId ])) {
        continue;
    }
    $arTasksPercent = ProjectInitiative::calcTasksPercent($groupId);

    $arClosed   = [];
    $arNext     = [];
    $arExpired  = [];
    $arComments = [];
    $arPercent  = [
        0 => 0
    ];

    foreach ($arTasksPercent as $arTask) {
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

    $iPercent = number_format(array_sum($arPercent), 2, ',', '');

    if ($iPercent >= 100) {
        continue;
    }

    $resPosts = CBlogPost::GetList(
        ['DATE_PUBLISH' => 'ASC'],
        [
            'PUBLISH_STATUS' => BLOG_PUBLISH_STATUS_PUBLISH,
            'SOCNET_GROUP_ID' => $groupId
        ]
    );
    while ($arPost = $resPosts->Fetch()) {
        $obPublish = new DateTimeImmutable($arPost['DATE_PUBLISH']);
        if ($obPublish >= $obDateStart && $obPublish <= $obDateFinish) {
            $text = '[' . $arPost['DATE_PUBLISH'] . '] ';
            $arAuthor = CUser::GetById($arPost['AUTHOR_ID'])->Fetch();
            $text .= implode(' ', [$arAuthor['LAST_NAME'], $arAuthor['NAME']]) . ': ';
            $text .= $arPost['DETAIL_TEXT'];
            $arComments[] = $text;
        }
    }

    $arOwner = CUser::GetById($arGroupInfo[ $groupId ]['OWNER_ID'])->Fetch();

    $arKeywords = explode(',', $arGroupInfo[ $groupId ]['KEYWORDS']);
    $arKeywords = array_filter($arKeywords);

    $arData = [
        'PROJECT'   => [
            'VALUE' => $arGroupInfo[ $groupId ]['NAME'],
            'LINK'  => 'https://' . $_SERVER['SERVER_NAME'] . '/workgroups/group/' . $groupId . '/'
        ],
        'OWNER'     => [
            'VALUE' => implode(' ', [$arOwner['LAST_NAME'], $arOwner['NAME']]),
        ],
        'TAGS'      => [
            'VALUE' => implode(', ', $arKeywords),
        ],
        'PERCENT'   => [
            'VALUE' => $iPercent . '%',
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
            'VALUE' => $arGroupInfo[ $groupId ]['UF_JUSTIFICATION'],
        ],
    ];

    $arResult['ROWS'][] = $arData;
}

exportExcel($arResult);
exit;
