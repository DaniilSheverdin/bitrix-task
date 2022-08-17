<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

global $arrFilter;

$arData = [
    'TITLE'     => 'Показатели',
    'FILENAME'  => 'Показатели',
    'HEADERS'   => [],
    'ROWS'      => [],
];

$bIsDynamic = true;

if ($arrFilter['=PROPERTY_2635'][0] == 1661) {
    $arData['HEADERS'] = [
        'NUMBER' => [
            'NAME'  => '№',
            'WIDTH' => 5,
        ],
        'NAME' => [
            'NAME'  => 'Показатель',
            'WIDTH' => 50,
        ],
        'FACT' => [
            'NAME'  => 'Факт',
            'WIDTH' => 15,
        ],
        'COMMENT' => [
            'NAME'  => 'Комментарий',
            'WIDTH' => 50,
        ],
    ];
    $bIsDynamic = false;
} else {
    $arData['HEADERS'] = [
        'NUMBER' => [
            'NAME'  => '№',
            'WIDTH' => 5,
        ],
        'NAME' => [
            'NAME'  => 'Показатель',
            'WIDTH' => 50,
        ],
        'PLAN' => [
            'NAME'  => 'План',
            'WIDTH' => 15,
        ],
        'FACT' => [
            'NAME'  => 'Факт',
            'WIDTH' => 15,
        ],
        'PERCENT' => [
            'NAME'  => 'Достижение (%)',
            'WIDTH' => 15,
        ],
        'COMMENT' => [
            'NAME'  => 'Комментарий',
            'WIDTH' => 50,
        ],
    ];
}

$n=0;
foreach ($arResult['INDICATORS'] as $sKey => $arValue) {
    $n++;
    $bgColor = 'd8e9a7';
    if (intval($arValue['BI_DATA']['percent_exec']) < 30) {
        $bgColor = 'fac1bc';
    } elseif (
        intval($arValue['BI_DATA']['percent_exec']) > 30 &&
        intval($arValue['BI_DATA']['percent_exec']) < 90
    ) {
        $bgColor = 'eabe40';
    }

    $arRow = [
        'NUMBER'    => [
            'VALUE'     => $n,
        ],
        'NAME'      => [
            'VALUE'     => $arValue['NAME'],
        ],
        'PLAN'      => [
            'VALUE'     => $arValue['BI_DATA']['target_value'],
        ],
        'FACT'      => [
            'VALUE'     => $bIsDynamic ? $arValue['BI_DATA']['state_value'] : $arValue['PROPERTY_TARGET_VALUE_VALUE'],
        ],
        'PERCENT'   => [
            'VALUE'     => $arValue['BI_DATA']['percent_exec'] . '%',
            'BGCOLOR'   => $bgColor,
        ],
        'COMMENT'   => [
            'VALUE'     => $arValue['BI_DATA']['comment'],
        ],
    ];

    $arData['ROWS'][] = $arRow;
}
// pre($arData);
// die;
$this->__component->exportExcel($arData);
