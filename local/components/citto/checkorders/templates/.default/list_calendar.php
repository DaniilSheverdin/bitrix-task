<?php

use Citto\Controlorders\Orders;
use Citto\Controlorders\Settings;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if (empty($GLOBALS['arCalendarFilter'])) {
    return;
}

$curUserId = $GLOBALS['USER']->GetID();

$GLOBALS['arCalendarFilter'][] = [
    '!PROPERTY_ACTION' => [
        Settings::$arActions['CONTROL'],
        Settings::$arActions['READY'],
        Settings::$arActions['ARCHIVE'],
    ]
];

$GLOBALS['arCalendarFilter'][] = [
    '!ID' => CIBlockElement::SubQuery(
        'PROPERTY_PORUCH',
        [
            'ACTIVE'        => 'Y',
            'IBLOCK_ID'     => Settings::$iblockId['ORDERS_COMMENT'],
            'PROPERTY_TYPE' => 1719,
            'PROPERTY_USER' => $curUserId,
        ]
    ),
];

if (isset($_REQUEST['debug_calendar'])) {
    pre($GLOBALS['arCalendarFilter']);
}

$obRes = CIBlockElement::GetList(
    ['PROPERTY_DATE_ISPOLN' => 'ASC'],
    $GLOBALS['arCalendarFilter'],
    false,
    false,
    [
        'ID',
        'NAME',
        'DETAIL_TEXT',
        'PROPERTY_ISPOLNITEL',
        'PROPERTY_DELEGATE_USER',
        'PROPERTY_DATE_ISPOLN',
        'PROPERTY_SUBEXECUTOR',
        'PROPERTY_ACCOMPLICES',
        'PROPERTY_SUBEXECUTOR_DATE',
    ]
);
global $arDays;
$arDays = [];
$curMonth = date('Y-m-01');
$arMonths = [
    strtotime($curMonth) => $curMonth
];
$arMyIspolnitels = $arResult['PERMISSIONS']['ispolnitel_ids'];
$arPermisions = [];
foreach ($arMyIspolnitels as $executorId) {
    $arExecutor = $this->ispolnitels[ $executorId ];
    $arPermisions = array_merge(
        $arPermisions,
        [$arExecutor['PROPERTY_RUKOVODITEL_VALUE']],
        $arExecutor['PROPERTY_ZAMESTITELI_VALUE']??[],
        $arExecutor['PROPERTY_ISPOLNITELI_VALUE']??[],
        $arExecutor['PROPERTY_IMPLEMENTATION_VALUE']??[]
    );
}
$obOrders = new Orders();
while ($arRow = $obRes->GetNext()) {
    $arRow['PROPERTY_SUBEXECUTOR_IDS'] = [];
    $arRow['PROPERTY_SUBEXECUTOR_USERS'] = [];
    foreach ($arRow['PROPERTY_SUBEXECUTOR_VALUE'] as $key => $value) {
        if (false !== mb_strpos($value, ':')) {
            $value = explode(':', $value);
            $ispId = $value[0];
            $userId = $value[1];
        } else {
            $ispId = $value;
            $userId = $arRow['PROPERTY_SUBEXECUTOR_DESCRIPTION'][ $key ] ?? 0;
        }
        $arRow['PROPERTY_SUBEXECUTOR_VALUE'][ $key ] = $ispId . ':' . $userId;
        $arRow['PROPERTY_SUBEXECUTOR_IDS'][ $key ] = $ispId;
        $arRow['PROPERTY_SUBEXECUTOR_USERS'][ $key ] = $userId;
    }
    $findField = 'PROPERTY_DATE_ISPOLN_VALUE';
    $mySubExecutorId = 0;
    foreach ($arMyIspolnitels as $executorId) {
        if (in_array($executorId, $arRow['PROPERTY_SUBEXECUTOR_IDS'])) {
            $mySubExecutorId = $executorId;

            if (!empty($arRow['PROPERTY_SUBEXECUTOR_DATE_VALUE'])) {
                $findField = 'PROPERTY_SUBEXECUTOR_DATE_VALUE';
            }
        }
    }

    if ($arRow[ $findField ] == $this->__component->disableSrokDate) {
        continue;
    }

    /*
     * Я - соисполнитель, найти отчёты коллег.
     */
    if (in_array($curUserId, $arRow['PROPERTY_ACCOMPLICES_VALUE'])) {
        $arCommentsFilter = [
            'ACTIVE'            => 'Y',
            'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
            'PROPERTY_TYPE'     => 1719,
            'PROPERTY_PORUCH'   => $arRow['ID'],
            'PROPERTY_USER'     => $arPermisions,
        ];
        $counter = CIBlockElement::GetList([], $arCommentsFilter, [], false, []);
        if ($counter > 0) {
            continue;
        }
    } elseif ($mySubExecutorId > 0) {
        $arCommentsFilter = [
            'ACTIVE'            => 'Y',
            'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
            'PROPERTY_TYPE'     => 1719,
            'PROPERTY_PORUCH'   => $arRow['ID'],
            'PROPERTY_USER'     => array_merge(
                [$this->ispolnitels[ $mySubExecutorId ]['PROPERTY_RUKOVODITEL_VALUE']],
                $this->ispolnitels[ $mySubExecutorId ]['PROPERTY_ZAMESTITELI_VALUE']??[],
                $this->ispolnitels[ $mySubExecutorId ]['PROPERTY_ISPOLNITELI_VALUE']??[],
                $this->ispolnitels[ $mySubExecutorId ]['PROPERTY_IMPLEMENTATION_VALUE']??[]
            ),
        ];
        $counter = CIBlockElement::GetList([], $arCommentsFilter, [], false, []);
        if ($counter > 0) {
            continue;
        }
    }

    $strSelectedDate = $arRow[ $findField ];

    if (
        $curUserId == $arRow['PROPERTY_DELEGATE_USER_VALUE'] &&
        $arRow['PROPERTY_ACTION_VALUE'] != Settings::$arActions['NEW']
    ) {
        $strSelectedDate = $obOrders->getSrok($arRow['ID'], (int)$arRow['PROPERTY_DELEGATE_USER_VALUE']);
    }

    $strDate = ConvertDateTime($strSelectedDate, 'YYYY-MM-DD', 'ru');
    $strMonth = ConvertDateTime($strSelectedDate, 'YYYY-MM-01', 'ru');
    $arDays[ $strDate ][] = [
        'ID'    => $arRow['ID'],
        'NAME'  => $arRow['NAME'],
        'TEXT'  => $arRow['~DETAIL_TEXT'],
    ];
    $arMonths[ strtotime($strMonth) ] = $strMonth;
}
ksort($arMonths);

/**
 * Сформировать календарь
 * @param integer $currentYear  Год.
 * @param integer $currentMonth Месяц.
 * @return array
 */
function makeCal(int $currentYear, int $currentMonth)
{
    global $arDays;
    $date = mktime(0, 0, 0, $currentMonth, 1, $currentYear);
    $iMonthStart =  date('w', $date) - 1;

    if ($iMonthStart < 0) {
        $iMonthStart += 7;
    }

    $arResult = [];
    $bBreak = false;
    for ($i = 0; $i < 6; $i++) {
        $arWeek = [];
        $arRow = $i * 7;
        for ($j = 0; $j < 7; $j++) {
            $arDay = [];

            $date = mktime(0, 0, 0, $currentMonth, (1 + $arRow + $j) - $iMonthStart, $currentYear);
            $y = (int)date('Y', $date);
            $m = (int)date('n', $date);
            $d = (int)date('j', $date);
            $itm = date('w', $date);

            if ($i > 0 && $j == 0 && $currentMonth != $m) {
                $bBreak = true;
                break;
            }

            $dayClassName = 'ControlCalendarDay';
            if ($currentMonth != $m) {
                $defaultClassName = 'bx-calendar-date-hidden';
                $dayClassName = 'bx-calendar-date-hidden';
            } elseif ($itm == 0 || $itm == 6) {
                $defaultClassName = 'bx-calendar-date-hidden';
            } else {
                $defaultClassName = 'ControlCalendarDefault';
            }

            $tmpDate = date('Y-m-d', mktime(0, 0, 0, $m, $d, $y));
            $arDay['date'] = $tmpDate;
            $arDay['day'] = $d;
            $arDay['td_class'] = $defaultClassName;
            $arDay['day_class'] = $dayClassName;
            $arDay['events'] = [];

            if (is_set($arDays[ $tmpDate ])) {
                foreach ($arDays[ $tmpDate ] as $dayNews) {
                    $arDay['events'][] = [
                        'date'      => $tmpDate,
                        'url'       => '?detail='.$dayNews['ID'],
                        'title'     => $dayNews['NAME'],
                        'preview'   => $dayNews['TEXT'],
                    ];
                }
            }
            $arWeek[] = $arDay;
        }
        if ($bBreak) {
            break;
        }
        $arResult[] = $arWeek;
    }
    return $arResult;
}

$arResult['MONTHS'] = [];
foreach ($arMonths as $ts => $date) {
    $now = getdate($ts);
    $arResult['MONTHS'][ $date ] = makeCal($now['year'], $now['mon']);
}

$arResult['WEEK_DAYS'] = [
    'Пн',
    'Вт',
    'Ср',
    'Чт',
    'Пт',
    'Сб',
    'Вс',
];

$prevDate = '';
$nextDate = '';
?>
<div class="sidebar-widget sidebar-widget-tasks">
    <div class="sidebar-widget-top">
        <div class="sidebar-widget-top-title">Календарь</div>
    </div>
    <div class="calendar-right-block">
        <?foreach ($arResult['MONTHS'] as $date => $arMonth) : ?>
        <div
            class="bx-calendar <?=($curMonth != $date ? 'd-none' : '')?>"
            data-month="<?=$date?>">
            <div class="bx-calendar-header d-flex justify-content-between align-items-center">
                <button class="ui-btn ui-btn-xs ui-btn-link js-calendar-prev"><</button>
                <span><?=FormatDate('f Y', strtotime($date))?></span>
                <button class="ui-btn ui-btn-xs ui-btn-link js-calendar-next">></button>
            </div>
            <div class="bx-calendar-name-day-wrap">
            <?foreach ($arResult['WEEK_DAYS'] as $WDay) :?>
                <span align="center" class="bx-calendar-name-day"><?=$WDay?></span>
            <?endforeach?>
            </div>
            <div class="bx-calendar-cell-block">
            <?foreach ($arMonth as $arWeek) : ?>
                <div class="bx-calendar-range">
                    <?foreach ($arWeek as $week_id => $arDay) : ?>
                        <?if (count($arDay['events'])>0) : ?>
                            <a
                                title="<?=count($arDay['events']) ?>"
                                href="javascript:void();"
                                data-date="<?=$arDay['date']?>"
                                class="js-show-events bx-calendar-cell <?=$arDay['day_class']?>">
                                <span class="items-count"><?=count($arDay['events']) ?></span>
                                <?=$arDay['day']?>
                            </a>
                        <?else : ?>
                            <a 
                                href="javascript:void();"
                                class="js-hide-event-block bx-calendar-cell <?=$arDay['day_class']?>">
                                <?=$arDay['day']?>
                            </a>
                        <?endif;?>
                    <?endforeach?>
                </div>
            <?endforeach?>
            </div>
        </div>
        <?endforeach;?>
    </div>
    <div class="days-info">
        <?php
        foreach ($arResult['MONTHS'] as $date => $arMonth) {
            foreach ($arMonth as $arWeek) {
                foreach ($arWeek as $week_id => $arDay) {
                    if (count($arDay['events']) > 0) {
                        ?>
                        <div class="day-info" id="<?=$arDay['date']?>">
                            <div class="sidebar-widget-top">
                                <div class="sidebar-widget-top-title">
                                    Поручения (<?=date('d.m.y', strtotime($arDay['date']))?>)
                                </div>
                            </div>
                            <?foreach ($arDay['events'] as $arEvent) : ?>
                            <div class="day-info-event sidebar-widget-item <?=$arEvent['class']?>">
                                <a href="<?=$arEvent['url']?>" target="_blank">
                                    <?=$arEvent['preview']?>
                                </a>
                            </div>            
                            <?endforeach;?>
                            <div class="day-info-event sidebar-widget-item hide-event-item">
                                <a href="#" class="js-hide-event-block">Скрыть</a>
                            </div>
                        </div>
                        <?php
                    }
                }
            }
        }
        ?>
    </div>
</div>