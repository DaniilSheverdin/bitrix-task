<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
global $USER, $arrFilter, $userFields;
?>
<main class="main">
    <section class="table-section">
        <div class="wrapper">
            <div class="row">
                <div class="col-xl-9 order-1 order-xl-0">
                    <div class="table-section__table table-section__table--equal-p table-responsive" id="table-normal">
                        <a href="<?=$APPLICATION->GetCurPageParam('export=Y', ['export'])?>" class="btn btn-primary">Выгрузить</a>
                        <table class="table table-bordered mt-4 mb-0">
                            <thead class="table__head--dark">
                                <tr>
                                    <th scope="col" class="border-left-0 border-top-0 border-bottom-0">№</th>
                                    <th scope="col" class="border-top-0 border-bottom-0">Показатель</th>
                                    <th scope="col" class="border-top-0 border-bottom-0">Месячный план</th>
                                    <th scope="col" class="border-top-0 border-bottom-0">Годовой план</th>
                                    <th scope="col" class="border-top-0 border-bottom-0">Факт</th>
                                    <th scope="col" class="border-top-0 border-bottom-0">Достижение (%)</th>
                                    <th scope="col" class="border-right-0 border-top-0 border-bottom-0">Комментарий</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?
                                $n = 0;
                                foreach ($arResult['INDICATORS'] as $sKey => $arValue) {
                                    if ($arValue['PROPERTY_TYPE_VALUE'] == 'Статистические') {
                                        continue;
                                    }
                                    if (!empty($arValue['PROPERTY_PASSPOPT_GROUP_VALUE'])) {
                                        continue;
                                    }
                                    $n++;

                                    $bShowChart = isset($arResult['BI_DATA'][ $arValue['XML_ID'] ]);
                                    ?>
                                    <tr id="table-row-<?=$n?>">
                                        <td scope="row" class="border-left-0 text-left"><?=$n?></td>
                                        <td scope="row" class="text-left <?=$bShowChart?'charts_open':''?>" data-id="<?=$arValue['XML_ID']?>">
                                            <?if ($bShowChart) : ?>
                                            <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M8.87934 8.75397H6.1219C5.89115 8.75397 5.7041 8.94101 5.7041 9.17176V14.5822C5.7041 14.8129 5.89115 15 6.1219 15H8.87934C9.11009 15 9.29713 14.8129 9.29713 14.5822V9.17176C9.29713 8.94101 9.11009 8.75397 8.87934 8.75397ZM8.46154 14.1644H6.53969V9.58956H8.46154V14.1644Z" fill="#F04E40"/>
                                                <path d="M14.3524 5.4743H11.574C11.3433 5.4743 11.1562 5.66135 11.1562 5.8921V14.5822C11.1562 14.813 11.3433 15 11.574 15H14.3524C14.5831 15 14.7702 14.813 14.7702 14.5822V5.8921C14.7702 5.66139 14.5831 5.4743 14.3524 5.4743ZM13.9346 14.1644H11.9918V6.30989H13.9346V14.1644Z" fill="#F04E40"/>
                                                <path d="M3.42758 10.7803H0.64924C0.418491 10.7803 0.231445 10.9673 0.231445 11.1981V14.5822C0.231445 14.813 0.418491 15 0.64924 15H3.42758C3.65833 15 3.84537 14.813 3.84537 14.5822V11.1981C3.84537 10.9674 3.65833 10.7803 3.42758 10.7803ZM3.00978 14.1644H1.06703V11.6159H3.00978V14.1644Z" fill="#F04E40"/>
                                                <path d="M14.8122 0.418989C14.7487 0.331423 14.6487 0.277605 14.5407 0.272767L10.7387 0.00119372C10.508 -0.0161194 10.3069 0.156932 10.2896 0.387641C10.2723 0.61839 10.4453 0.819471 10.676 0.836784L13.3668 1.01951L8.41995 4.88942L4.63893 1.92308C4.47747 1.79521 4.24696 1.80408 4.09578 1.94395L0.210314 5.66228C0.0451393 5.81898 0.0358629 6.07911 0.189442 6.24721C0.263973 6.34393 0.380807 6.39847 0.502799 6.39343C0.612995 6.39187 0.718114 6.34681 0.795243 6.26808L4.40918 2.80037L8.14842 5.74581C8.30104 5.868 8.51803 5.868 8.67066 5.74581L14.0185 1.58433L13.8095 4.17914C13.8085 4.40601 13.9806 4.59622 14.2064 4.61781H14.2273C14.4427 4.61889 14.6235 4.45608 14.6451 4.2418L14.9167 0.711433C14.932 0.602716 14.893 0.493399 14.8122 0.418989Z" fill="#F04E40"/>
                                            </svg>
                                            <?endif;?>
                                            <b><?=$arValue['NAME']?></b>
                                            <br>
                                            <i class="mt-1 mb-0"><?=$arResult['CATEGORY_NAMES'][$arValue['IBLOCK_SECTION_ID']] ?></i>
                                            <p class="mt-1 mb-0 text-right">
                                                <?
                                                if ($arValue['BI_DATA']['date']!='') {
                                                    ?>
                                                    <i><?=$arValue['BI_DATA']['date'] ?> (<?=$arValue['BI_DATA']['fio']?>)</i>
                                                    <?
                                                }
                                                ?>
                                            </p>
                                        </td>
                                        <td class="js-monthly-target"><?=$arValue['PROPERTY_MONTHLY_TARGET_VALUE_VALUE'] ?? '-'?></td>
                                        <td><?=$arValue['BI_DATA']['target_value']?></td>
                                        <td class="js-fact"><?=$arValue['BI_DATA']['state_value']?></td>
                                        <?
                                        $className = 'success';
                                        if (intval($arValue['BI_DATA']['percent_exec']) < 30) {
                                            $className = 'failed';
                                        } elseif (
                                            intval($arValue['BI_DATA']['percent_exec']) > 30 &&
                                            intval($arValue['BI_DATA']['percent_exec']) < 90
                                        ) {
                                            $className = 'normal';
                                        }
                                        ?>
                                        <td class="table__indicator js-percent-exec_view table__indicator--<?=$className?>"><?=$arValue['BI_DATA']['percent_exec']?>%</td>
                                        <td class="border-right-0"><?=$arValue['BI_DATA']['comment']?></td>
                                    </tr>
                                    <?
                                    if ($bShowChart) {
                                        $bIntervalValue = mb_strpos($arValue['BI_DATA']['target_value'], '-');
                                        ?>
                                        <tr class="table-section__chart-row" id="chartTableRow<?=$arValue['XML_ID']?>">
                                            <td colspan="5">
                                                <div class="table-section__chart-container" id="chartContainer">
                                                    <script type="text/javascript">
                                                    var chart = AmCharts.makeChart(
                                                        "chartDiv<?=$arValue['ID']?>",
                                                        {
                                                            "language": "ru",
                                                            "type": "serial",
                                                            "theme": "light",
                                                            "dataDateFormat": "YYYY-MM-DD",
                                                            "valueAxes": [
                                                                {
                                                                    "id": "v1",
                                                                    "position": "left",
                                                                    "ignoreAxisWidth": false
                                                                },
                                                                {
                                                                    "id": "v2",
                                                                    "gridAlpha": 0,
                                                                    "position": "left",
                                                                    "recalculateToPercents": true,
                                                                    "ignoreAxisWidth": false
                                                                }
                                                            ],
                                                            "graphs": [
                                                                {
                                                                    "id": "g1",
                                                                    "bullet": "none",
                                                                    "lineThickness": 2,
                                                                    "title": "Плановое значение",
                                                                    "valueField": "target",
                                                                    "valueAxis": "v1",
                                                                    "dashLength": 5,
                                                                    "strokeWidth": 2,
                                                                    "showBalloon": false,
                                                                },
                                                                <?if ($bIntervalValue) : ?>
                                                                {
                                                                    "fillAlphas": 0.2,
                                                                    "fillToGraph": "g1",
                                                                    "title": "Плановое значение (мин)",
                                                                    "lineAlpha": 0,
                                                                    "showBalloon": false,
                                                                    "valueField": "targetMin"
                                                                },
                                                                <?endif;?>
                                                                {
                                                                    "id": "g2",
                                                                    "bullet": "round",
                                                                    "lineThickness": 2,
                                                                    "title": "Фактическое значение",
                                                                    "valueField": "value",
                                                                    "valueAxis": "v1",
                                                                    "strokeWidth": 2,
                                                                    // "fillColorsField": "lineColor",
                                                                    // "lineColorField": "lineColor",
                                                                    "balloonText": "<div style='margin:5px;text-align:left'><b>Плановое&nbsp;значение:</b>&nbsp;<?=($bIntervalValue)?'[[targetMin]]&nbsp;-&nbsp;':''?>[[target]]<br><b>Фактическое&nbsp;значение:</b>&nbsp;[[value]]</div>",
                                                                }
                                                            ],
                                                            "legend": {
                                                                "valueText": ""
                                                            },
                                                            "chartCursor": {
                                                                "valueLineEnabled": true,
                                                                "valueLineBalloonEnabled": true,
                                                                "cursorAlpha": 1,
                                                                "cursorColor": "#258cbb",
                                                                "limitToGraph": "g1",
                                                                "valueLineAlpha": 0.2,
                                                            },
                                                            "chartScrollbar": {
                                                                "graph": "g2",
                                                                "scrollbarHeight": 50,
                                                                "backgroundAlpha": 0,
                                                                "selectedBackgroundAlpha": 0.1,
                                                                "selectedBackgroundColor": "#888888",
                                                                "graphFillAlpha": 0,
                                                                "graphLineAlpha": 0.5,
                                                                "selectedGraphFillAlpha": 0,
                                                                "selectedGraphLineAlpha": 1,
                                                                "autoGridCount": true,
                                                                "color": "#AAAAAA"
                                                            },
                                                            "categoryField": "date",
                                                            "categoryAxis": {
                                                                "startOnAxis": true,
                                                                "parseDates": true,
                                                            },
                                                            "dataProvider": <?=json_encode($arResult['BI_DATA'][ $arValue['XML_ID'] ])?>,
                                                            "export": {
                                                                "enabled": true,
                                                                "menu": [
                                                                    {
                                                                        "class": "export-main",
                                                                        "menu": [
                                                                            {
                                                                                "label": "Download",
                                                                                "menu": [
                                                                                    "PNG",
                                                                                    "JPG",
                                                                                    "CSV"
                                                                                ]
                                                                            },
                                                                            {
                                                                                "label": "Annotate",
                                                                                "action": "draw",
                                                                                "menu": [
                                                                                    {
                                                                                        "class": "export-drawing",
                                                                                        "menu": [
                                                                                            "PNG",
                                                                                            "JPG"
                                                                                        ]
                                                                                    }
                                                                                ]
                                                                            }
                                                                        ]
                                                                    }
                                                                ]
                                                            }
                                                        }
                                                    );
                                                    </script>
                                                    <div id="chartDiv<?=$arValue['ID']?>" style="height:600px;"></div>
                                                </div>
                                            </td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        <?
                                    }
                                }

                                if ($n <= 0) {
                                    ?>
                                    <tr>
                                        <td colspan="6">
                                            <b>Нет данных</b>
                                            <script type="text/javascript">
                                                $('#table-normal').hide();
                                            </script>
                                        </td>
                                    </tr>
                                    <?
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="table-section__table table-section__table--equal-p table-responsive mt-4" id="table-passport">
                        <table class="table table-bordered mb-0">
                            <thead class="table__head--dark">
                                <tr>
                                    <th scope="col" class="border-left-0 border-top-0 border-bottom-0">&nbsp;№&nbsp;</th>
                                    <th scope="col" class="border-top-0 border-bottom-0">&nbsp;Показатель&nbsp;</th>
                                    <th scope="col" class="border-top-0 border-bottom-0">&nbsp;НПА&nbsp;</th>
                                    <th scope="col" class="border-top-0 border-bottom-0">&nbsp;Месячный план&nbsp;</th>
                                    <th scope="col" class="border-top-0 border-bottom-0">&nbsp;Годовой план&nbsp;</th>
                                    <th scope="col" class="border-top-0 border-bottom-0">&nbsp;Факт&nbsp;</th>
                                    <th scope="col" class="border-top-0 border-bottom-0">&nbsp;Достижение (%)&nbsp;</th>
                                    <th scope="col" class="border-top-0 border-bottom-0">Следующая веха (Дата)</th>
                                    <th scope="col" class="border-top-0 border-bottom-0">План проекта</th>
                                    <th scope="col" class="border-top-0 border-bottom-0">&nbsp;Ответственный&nbsp;</th>
                                    <th scope="col" class="border-right-0 border-top-0 border-bottom-0">&nbsp;Комментарий&nbsp;</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?
                                $n = 0;
                                foreach ($arResult['INDICATORS'] as $sKey => $arValue) {
                                    if ($arValue['PROPERTY_TYPE_VALUE'] == 'Статистические') {
                                        continue;
                                    }
                                    if (empty($arValue['PROPERTY_PASSPOPT_GROUP_VALUE'])) {
                                        continue;
                                    }
                                    $n++;
                                    $arPassport = [
                                        'TASKS'     => [],
                                        'PERCENT'   => '0,00',
                                        'DEADLINE'  => '',
                                    ];
                                    if (!empty($arValue['PROPERTY_PASSPOPT_GROUP_VALUE'])) {
                                        $arPassport = $this->__component->calcPassport(
                                            $arValue['PROPERTY_PASSPOPT_GROUP_VALUE'],
                                            $arValue['NAME']
                                        );
                                    }
                                    $bShowChart = isset($arResult['BI_DATA'][ $arValue['XML_ID'] ]);
                                    ?>
                                    <tr id="table-row-<?=$n?>">
                                        <td scope="row" class="border-left-0 text-left" ><?=$n?></td>
                                        <td scope="row" class="text-left <?=$bShowChart?'charts_open':''?>" data-id="<?=$arValue['XML_ID']?>">
                                            <?if ($bShowChart) : ?>
                                            <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M8.87934 8.75397H6.1219C5.89115 8.75397 5.7041 8.94101 5.7041 9.17176V14.5822C5.7041 14.8129 5.89115 15 6.1219 15H8.87934C9.11009 15 9.29713 14.8129 9.29713 14.5822V9.17176C9.29713 8.94101 9.11009 8.75397 8.87934 8.75397ZM8.46154 14.1644H6.53969V9.58956H8.46154V14.1644Z" fill="#F04E40"/>
                                                <path d="M14.3524 5.4743H11.574C11.3433 5.4743 11.1562 5.66135 11.1562 5.8921V14.5822C11.1562 14.813 11.3433 15 11.574 15H14.3524C14.5831 15 14.7702 14.813 14.7702 14.5822V5.8921C14.7702 5.66139 14.5831 5.4743 14.3524 5.4743ZM13.9346 14.1644H11.9918V6.30989H13.9346V14.1644Z" fill="#F04E40"/>
                                                <path d="M3.42758 10.7803H0.64924C0.418491 10.7803 0.231445 10.9673 0.231445 11.1981V14.5822C0.231445 14.813 0.418491 15 0.64924 15H3.42758C3.65833 15 3.84537 14.813 3.84537 14.5822V11.1981C3.84537 10.9674 3.65833 10.7803 3.42758 10.7803ZM3.00978 14.1644H1.06703V11.6159H3.00978V14.1644Z" fill="#F04E40"/>
                                                <path d="M14.8122 0.418989C14.7487 0.331423 14.6487 0.277605 14.5407 0.272767L10.7387 0.00119372C10.508 -0.0161194 10.3069 0.156932 10.2896 0.387641C10.2723 0.61839 10.4453 0.819471 10.676 0.836784L13.3668 1.01951L8.41995 4.88942L4.63893 1.92308C4.47747 1.79521 4.24696 1.80408 4.09578 1.94395L0.210314 5.66228C0.0451393 5.81898 0.0358629 6.07911 0.189442 6.24721C0.263973 6.34393 0.380807 6.39847 0.502799 6.39343C0.612995 6.39187 0.718114 6.34681 0.795243 6.26808L4.40918 2.80037L8.14842 5.74581C8.30104 5.868 8.51803 5.868 8.67066 5.74581L14.0185 1.58433L13.8095 4.17914C13.8085 4.40601 13.9806 4.59622 14.2064 4.61781H14.2273C14.4427 4.61889 14.6235 4.45608 14.6451 4.2418L14.9167 0.711433C14.932 0.602716 14.893 0.493399 14.8122 0.418989Z" fill="#F04E40"/>
                                            </svg>
                                            <?endif;?>
                                            <b><?=$arValue['NAME']?></b>
                                            <br>
                                            <i class="mt-1 mb-0"><?=$arResult['CATEGORY_NAMES'][$arValue['IBLOCK_SECTION_ID']] ?></i>
                                            <p class="mt-1 mb-0 text-right">
                                                <?
                                                if ($arValue['BI_DATA']['date'] != '') {
                                                    ?>
                                                    <i><?=$arValue['BI_DATA']['date'] ?> (<?=$arValue['BI_DATA']['fio']?>)</i>
                                                    <?
                                                }
                                                ?>
                                            </p>
                                        </td>
                                        <td>
                                            <?
                                            if (!empty($arValue['PROPERTY_PASSPOPT_LNPA_VALUE'])) {
                                                ?>
                                                <a href="/lpa/?page=detail_view&id=<?=$arValue['PROPERTY_PASSPOPT_LNPA_VALUE']?>" target="_blank">Перейти</a>
                                                <?
                                            } else {
                                                ?>
                                                -
                                                <?
                                            }
                                            ?>
                                        </td>
                                        <td class="js-monthly-target"><?=$arValue['PROPERTY_MONTHLY_TARGET_VALUE_VALUE'] ?? '-'?></td>
                                        <td><?=$arValue['BI_DATA']['target_value']?></td>
                                        <td><?=$arValue['BI_DATA']['state_value']?></td>
                                        <?
                                        $className = 'success';
                                        if ($arPassport['PERCENT'] < 30) {
                                            $className = 'failed';
                                        } elseif ($arPassport['PERCENT'] > 30 && $arPassport['PERCENT'] < 90) {
                                            $className = 'normal';
                                        }
                                        ?>
                                        <td class="table__indicator table__indicator--<?=$className?>">
                                            <?=$arPassport['PERCENT_FMT']?>%
                                        </td>
                                        <td>
                                            <?=$arPassport['DEADLINE']?>
                                        </td>
                                        <td>
                                            <?
                                            if (!empty($arValue['PROPERTY_PASSPOPT_GROUP_VALUE'])) {
                                                ?>
                                                <a href="/workgroups/group/<?=$arValue['PROPERTY_PASSPOPT_GROUP_VALUE']?>/tasks/" target="_blank">Перейти</a>
                                                <?
                                            } else {
                                                ?>
                                                -
                                                <?
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <?
                                            if (!empty($arValue['PROPERTY_PASSPOPT_USER_VALUE'])) {
                                                $arUserData = $userFields($arValue['PROPERTY_PASSPOPT_USER_VALUE']);
                                                ?>
                                                <a href="/company/personal/user/<?=$arUserData['ID']?>/" target="_blank">
                                                    <?=$arUserData['FIO'];?>
                                                </a>
                                                <?
                                            } else {
                                                ?>
                                                -
                                                <?
                                            }
                                            ?>
                                        </td>
                                        <td class="border-right-0"><?=$arValue['BI_DATA']['comment']?></td>
                                    </tr>
                                    <?
                                    if ($bShowChart) {
                                        $bIntervalValue = mb_strpos($arValue['BI_DATA']['target_value'], '-');
                                        ?>
                                        <tr class="table-section__chart-row" id="chartTableRow<?=$arValue['XML_ID']?>">
                                            <td colspan="9">
                                                <div class="table-section__chart-container" id="chartContainer">
                                                    <script type="text/javascript">
                                                    var chart = AmCharts.makeChart(
                                                        "chartDiv<?=$arValue['ID']?>",
                                                        {
                                                            "language": "ru",
                                                            "type": "serial",
                                                            "theme": "light",
                                                            "dataDateFormat": "YYYY-MM-DD",
                                                            "valueAxes": [
                                                                {
                                                                    "id": "v1",
                                                                    "position": "left",
                                                                    "ignoreAxisWidth":false
                                                                },
                                                                {
                                                                    "id": "v2",
                                                                    "gridAlpha": 0,
                                                                    "position": "left",
                                                                    "recalculateToPercents": true,
                                                                    "ignoreAxisWidth":false
                                                                }
                                                            ],
                                                            "graphs": [
                                                                {
                                                                    "id": "g1",
                                                                    "bullet": "none",
                                                                    "lineThickness": 2,
                                                                    "title": "Плановое значение",
                                                                    "valueField": "target",
                                                                    "valueAxis": "v1",
                                                                    "dashLength": 5,
                                                                    "strokeWidth": 2,
                                                                    "showBalloon": false,
                                                                },
                                                                <?if ($bIntervalValue) : ?>
                                                                {
                                                                    "fillAlphas": 0.2,
                                                                    "fillToGraph": "g1",
                                                                    "title": "Плановое значение (мин)",
                                                                    "lineAlpha": 0,
                                                                    "showBalloon": false,
                                                                    "valueField": "targetMin"
                                                                },
                                                                <?endif;?>
                                                                {
                                                                    "id": "g2",
                                                                    "bullet": "round",
                                                                    "lineThickness": 2,
                                                                    "title": "Фактическое значение",
                                                                    "valueField": "value",
                                                                    "valueAxis": "v1",
                                                                    "strokeWidth": 2,
                                                                    // "fillColorsField": "lineColor",
                                                                    // "lineColorField": "lineColor",
                                                                    "balloonText": "<div style='margin:5px;text-align:left'><b>Плановое&nbsp;значение:</b>&nbsp;<?=($bIntervalValue)?'[[targetMin]]&nbsp;-&nbsp;':''?>[[target]]<br><b>Фактическое&nbsp;значение:</b>&nbsp;[[value]]</div>",
                                                                }
                                                            ],
                                                            "legend": {
                                                                "valueText": ""
                                                            },
                                                            "chartCursor": {
                                                                "valueLineEnabled": true,
                                                                "valueLineBalloonEnabled": true,
                                                                "cursorAlpha": 1,
                                                                "cursorColor": "#258cbb",
                                                                "limitToGraph": "g1",
                                                                "valueLineAlpha": 0.2,
                                                            },
                                                            "chartScrollbar": {
                                                                "graph": "g2",
                                                                "scrollbarHeight": 50,
                                                                "backgroundAlpha": 0,
                                                                "selectedBackgroundAlpha": 0.1,
                                                                "selectedBackgroundColor": "#888888",
                                                                "graphFillAlpha": 0,
                                                                "graphLineAlpha": 0.5,
                                                                "selectedGraphFillAlpha": 0,
                                                                "selectedGraphLineAlpha": 1,
                                                                "autoGridCount": true,
                                                                "color": "#AAAAAA"
                                                            },
                                                            "categoryField": "date",
                                                            "categoryAxis": {
                                                                "startOnAxis": true,
                                                                "parseDates": true,
                                                            },
                                                            "dataProvider": <?=json_encode($arResult['BI_DATA'][ $arValue['XML_ID'] ])?>,
                                                            "export": {
                                                                "enabled": true,
                                                                "menu": [
                                                                    {
                                                                        "class": "export-main",
                                                                        "menu": [
                                                                            {
                                                                                "label": "Download",
                                                                                "menu": [
                                                                                    "PNG",
                                                                                    "JPG",
                                                                                    "CSV"
                                                                                ]
                                                                            },
                                                                            {
                                                                                "label": "Annotate",
                                                                                "action": "draw",
                                                                                "menu": [
                                                                                    {
                                                                                        "class": "export-drawing",
                                                                                        "menu": [
                                                                                            "PNG",
                                                                                            "JPG"
                                                                                        ]
                                                                                    }
                                                                                ]
                                                                            }
                                                                        ]
                                                                    }
                                                                ]
                                                            }
                                                        }
                                                    );
                                                    </script>
                                                    <div id="chartDiv<?=$arValue['ID']?>" style="height:600px;"></div>
                                                </div>
                                            </td>
                                            <td>&nbsp;</td>
                                        </tr>
                                        <?
                                    }
                                }

                                if ($n <= 0) {
                                    ?>
                                    <tr>
                                        <td colspan="10">
                                            <b>Нет данных</b>
                                            <script type="text/javascript">
                                                $('#table-passport').hide();
                                            </script>
                                        </td>
                                    </tr>
                                    <?
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="table-section__table table-section__table--equal-p table-responsive mt-4" id="table-stat">
                        <table class="table table-bordered mb-0">
                            <thead class="table__head--dark">
                                <tr>
                                    <th scope="col" class="border-left-0 border-top-0 border-bottom-0">№</th>
                                    <th scope="col" class="border-top-0 border-bottom-0">Показатель</th>
                                    <th scope="col" class="border-top-0 border-bottom-0">Факт</th>
                                    <th scope="col" class="border-right-0 border-top-0 border-bottom-0">Комментарий</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?
                                $n = 0;
                                foreach ($arResult['INDICATORS'] as $sKey => $arValue) {
                                    if ($arValue['PROPERTY_TYPE_VALUE'] != 'Статистические') {
                                        continue;
                                    }
                                    $n++;
                                    ?>
                                    <tr id="table-row-<?=$n?>">
                                        <td scope="row" class="border-left-0 text-left" ><?=$n?></td>
                                        <td scope="row" class="text-left">
                                            <b><?=$arValue['NAME']?></b>
                                            <p class="mt-1 mb-0"><i><?=$arValue['PROPERTY_THEME_STAT_VALUE'] ?> <?=$arValue['PROPERTY_AFFILIATION_VALUE'] ?></i></p>
                                        </td>
                                        <td><?=$arValue['BI_DATA']['state_value'] ?? $arValue['PROPERTY_TARGET_VALUE_VALUE']?></td>
                                        <td><?=$arValue['BI_DATA']['comment']?></td>
                                    </tr>
                                    <?
                                }

                                if ($n <= 0) {
                                    ?>
                                    <tr>
                                        <td colspan="4">
                                            <b>Нет данных</b>
                                            <script type="text/javascript">
                                                $('#table-stat').hide();
                                            </script>
                                        </td>
                                    </tr>
                                    <?
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-xl-3 order-0 order-xl-1">
                    <div class="table-section__filter">
                        <?$APPLICATION->IncludeComponent(
                            "bitrix:catalog.smart.filter",
                            "bootstrap_v4",
                            array(
                                "COMPONENT_TEMPLATE" => "bootstrap_v4",
                                "IBLOCK_TYPE" => "indicators",
                                "IBLOCK_ID" => IBLOCK_ID_INDICATORS_CATALOG,
                                "SECTION_ID" => $_REQUEST['filter']['CATEGORY'][0],
                                "SECTION_CODE" => "",
                                "FILTER_NAME" => "arrFilter",
                                "HIDE_NOT_AVAILABLE" => "N",
                                "TEMPLATE_THEME" => "blue",
                                "FILTER_VIEW_MODE" => "vertical",
                                "DISPLAY_ELEMENT_COUNT" => "Y",
                                "SEF_MODE" => "N",
                                "CACHE_TYPE" => "A",
                                "CACHE_TIME" => "36000000",
                                "CACHE_GROUPS" => "Y",
                                "SAVE_IN_SESSION" => "N",
                                "INSTANT_RELOAD" => "N",
                                "PAGER_PARAMS_NAME" => "arrPager",
                                "PRICE_CODE" => array(
                                    0 => "BASE",
                                ),
                                "CONVERT_CURRENCY" => "Y",
                                "XML_EXPORT" => "N",
                                "SECTION_TITLE" => "-",
                                "SECTION_DESCRIPTION" => "-",
                                "POPUP_POSITION" => "left",
                                "SEF_RULE" => "/examples/books/#SECTION_ID#/filter/#SMART_FILTER_PATH#/apply/",
                                "SECTION_CODE_PATH" => "",
                                "SMART_FILTER_PATH" => $_REQUEST["SMART_FILTER_PATH"],
                                "CURRENCY_ID" => "RUB"
                            ),
                            false
                        );?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
<?
if ($_REQUEST['id'] != '') {
    ?>
    <div class="wrapper">
        <div class="container-fluid">
            <section class="table-section">
                <h1 class="table-section__header"><?=$arResult['DEFAULT_DEPARTMENT']['NAME']?></h1>

                <nav class="table-section__breadcrumbs" aria-label="breadcrumb">
                    <ol class="breadcrumb breadcrumbs__breadcrumb">
                        <li class="breadcrumb-item breadcrumbs__item">
                            <a class="breadcrumbs__link" href="./">Главная</a>
                        </li>
                        <li class="breadcrumb-item breadcrumbs__item">
                            <a class="breadcrumbs__link" href="?show=departments">Отделы</a>
                        </li>
                        <?
                        if ($arResult['DEFAULT_DEPARTMENT']['IBLOCK_SECTION_ID']!='') {?>
                            <li class="breadcrumb-item breadcrumbs__item">
                            <a class="breadcrumbs__link" href="?show=departments&id=<?=$arResult['DEFAULT_DEPARTMENT']['IBLOCK_SECTION_ID']?>"><?=$arResult['DEPARTMENTS'][$arResult['DEFAULT_DEPARTMENT']['IBLOCK_SECTION_ID']]['NAME']?></a>
                        </li>
                        <?
                        }
                        ?>
                        <li class="breadcrumb-item breadcrumbs__item active" aria-current="page"><?=$arResult['DEFAULT_DEPARTMENT']['NAME']?></li>
                    </ol>
                </nav>
                <div class="row">
                    <?if ($arResult['DEFAULT_DEPARTMENT']['IBLOCK_SECTION_ID']!='') {?>
                        <div class="col-md-4">
                            <div class="table-section__select-group">
                                <label for="time-select">Отдел</label>
                                <div class="input-group">
                                    <select class="custom-select select-group__select" id="time-select2">
                                        <?
                                        foreach ($arResult['DEPARTMENTS'][$arResult['DEFAULT_DEPARTMENT']['IBLOCK_SECTION_ID']]['SUBS'] as $sKey => $sValue) {?>
                                           <option <?=($_REQUEST['id']==$sValue['ID'])?'selected':'' ?> value="?show=departments&id=<?=$sValue['ID']?>"><?=$sValue['NAME']?></option>
                                        <?}?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    <?}?>
                    <div class="col-md-4">
                        <div class="table-section__select-group">
                            <label for="time-select">Временной период</label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">
                                        <svg width="34" height="25" viewBox="0 0 34 25" fill="none"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <rect x="8.86963" width="7.3913" height="7.35294" fill="#C4C4C4"/>
                                            <rect x="17.7393" width="7.3913" height="7.35294" fill="#C4C4C4"/>
                                            <rect x="26.6089" width="7.3913" height="7.35294" fill="#C4C4C4"/>
                                            <rect y="8.82355" width="7.3913" height="7.35294" fill="#C4C4C4"/>
                                            <rect x="8.86963" y="8.82349" width="7.3913" height="7.35294"
                                                  fill="#C4C4C4"/>
                                            <rect x="17.7393" y="8.82349" width="7.3913" height="7.35294"
                                                  fill="#C4C4C4"/>
                                            <rect y="17.647" width="7.3913" height="7.35294" fill="#C4C4C4"/>
                                            <rect x="8.86963" y="17.6471" width="7.3913" height="7.35294"
                                                  fill="#C4C4C4"/>
                                            <rect x="17.7393" y="17.6471" width="7.3913" height="7.35294"
                                                  fill="#C4C4C4"/>
                                        </svg>
                                    </span>
                                </div>
                                <select class="custom-select select-group__select border-left-0" id="time-select">
                                    <?
                                    foreach ($arResult['DATEPERIODS'] as $sKey => $sValue) {?>
                                       <option <?=($arResult['SELECTED_PERIOD']==$sValue)?'selected':'' ?> value="?show=departments&id=<?=$_REQUEST['id']?>&period=<?=$sValue?>"><?=$sValue?></option>
                                    <?}?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 d-flex align-items-end">
                        <form class="table-section__search-form">
                            <div class="input-group">
                                <input type="search"
                                       class="form-control search-form__input"
                                       placeholder="Поиск по показателям..."
                                       aria-label="Поиск по показателям..."
                                >
                                <div class="input-group-append">
                                    <button class="btn search-form__button" type="button">
                                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none"
                                             xmlns="http://www.w3.org/2000/svg">
                                            <path d="M19.6959 18.2168L14.7656 13.2662C16.0332 11.8113 16.7278 9.98069 16.7278 8.07499C16.7278 3.62251 12.9757 0 8.36391 0C3.75212 0 0 3.62251 0 8.07499C0 12.5275 3.75212 16.15 8.36391 16.15C10.0952 16.15 11.7451 15.6458 13.1557 14.6888L18.1235 19.677C18.3311 19.8852 18.6104 20 18.9097 20C19.193 20 19.4617 19.8957 19.6657 19.7061C20.0992 19.3034 20.113 18.6357 19.6959 18.2168ZM8.36391 2.10652C11.7727 2.10652 14.5459 4.78391 14.5459 8.07499C14.5459 11.3661 11.7727 14.0435 8.36391 14.0435C4.95507 14.0435 2.18189 11.3661 2.18189 8.07499C2.18189 4.78391 4.95507 2.10652 8.36391 2.10652Z"
                                                  fill="#12183C"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-12">
                    <div class="table-section__table table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead>
                            <tr>
                                <th scope="col" class="border-left-0 border-top-0 border-bottom-0">Показатель (%)
                                </th>
                                <th scope="col" class="border-top-0 border-bottom-0">План</th>
                                <th scope="col" class="border-top-0 border-bottom-0">Факт</th>
                                <th scope="col" class="border-top-0 border-bottom-0">Достижение (%)</th>
                                <th scope="col" class="border-right-0 border-top-0 border-bottom-0">Комментарий</th>
                            </tr>
                            </thead>
                            <tbody>
                                <?foreach ($arResult['INDICATORS']['BI_ID'] as $sKey => $arValue) {?>
                                    <tr>
                                        <td class="border-left-0">
                                            <?=trim($arResult['INDICATORS']['FULL_NAME'][$sKey]['TEXT'])?>
                                        </td>
                                        <td>
                                            <?=$arResult['DATEACTUAL'][$arResult['SELECTED_PERIOD']][$arValue]['target_value']?>
                                        </td>
                                        <td>
                                            <?=$arResult['DATEACTUAL'][$arResult['SELECTED_PERIOD']][$arValue]['state_value']?>
                                        </td>
                                        <td>
                                            <?=$arResult['DATEACTUAL'][$arResult['SELECTED_PERIOD']][$arValue]['percent_exec']?>
                                        </td>
                                        <td class="border-right-0">
                                            <?=$arResult['DATEACTUAL'][$arResult['SELECTED_PERIOD']][$arValue]['comment']?>
                                        </td>
                                    </tr>
                                <?}?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-md-2">
                    <a href="#" class="btn table-section__download-button" role="button" download="#">Скачать</a>
                </div>
            </section>
        </div>
    </div>
    <?
}
