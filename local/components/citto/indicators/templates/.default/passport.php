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
                <div class="col-xl-12 order-1 order-xl-0">
                    <div class="table-section__table table-section__table--equal-p table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="table__head--dark">
                                <tr>
                                    <th scope="col" class="border-left-0 border-top-0 border-bottom-0">№</th>
                                    <th scope="col" class="border-top-0 border-bottom-0">Показатель</th>
                                    <th scope="col" class="border-top-0 border-bottom-0">НПА</th>
                                    <th scope="col" class="border-top-0 border-bottom-0">Месячный план</th>
                                    <th scope="col" class="border-top-0 border-bottom-0">Годовой план</th>
                                    <th scope="col" class="border-top-0 border-bottom-0">Факт</th>
                                    <th scope="col" class="border-top-0 border-bottom-0">Достижение (%)</th>
                                    <th scope="col" class="border-top-0 border-bottom-0">Следующая веха (Дата)</th>
                                    <th scope="col" class="border-top-0 border-bottom-0">План проекта</th>
                                    <th scope="col" class="border-top-0 border-bottom-0">Ответственный</th>
                                    <th scope="col" class="border-right-0 border-top-0 border-bottom-0">Комментарий</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?
                                $n = 0;
                                foreach ($arResult['INDICATORS'] as $sKey => $arValue) {
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
                                    ?>
                                    <tr id="table-row-1">
                                        <td scope="row" class="border-left-0 text-left" ><?=$n?></td>
                                        <td scope="row" class="text-left charts_open" data-id="<?=$arValue['XML_ID']?>">
                                            <svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                <path d="M8.87934 8.75397H6.1219C5.89115 8.75397 5.7041 8.94101 5.7041 9.17176V14.5822C5.7041 14.8129 5.89115 15 6.1219 15H8.87934C9.11009 15 9.29713 14.8129 9.29713 14.5822V9.17176C9.29713 8.94101 9.11009 8.75397 8.87934 8.75397ZM8.46154 14.1644H6.53969V9.58956H8.46154V14.1644Z" fill="#F04E40"/>
                                                <path d="M14.3524 5.4743H11.574C11.3433 5.4743 11.1562 5.66135 11.1562 5.8921V14.5822C11.1562 14.813 11.3433 15 11.574 15H14.3524C14.5831 15 14.7702 14.813 14.7702 14.5822V5.8921C14.7702 5.66139 14.5831 5.4743 14.3524 5.4743ZM13.9346 14.1644H11.9918V6.30989H13.9346V14.1644Z" fill="#F04E40"/>
                                                <path d="M3.42758 10.7803H0.64924C0.418491 10.7803 0.231445 10.9673 0.231445 11.1981V14.5822C0.231445 14.813 0.418491 15 0.64924 15H3.42758C3.65833 15 3.84537 14.813 3.84537 14.5822V11.1981C3.84537 10.9674 3.65833 10.7803 3.42758 10.7803ZM3.00978 14.1644H1.06703V11.6159H3.00978V14.1644Z" fill="#F04E40"/>
                                                <path d="M14.8122 0.418989C14.7487 0.331423 14.6487 0.277605 14.5407 0.272767L10.7387 0.00119372C10.508 -0.0161194 10.3069 0.156932 10.2896 0.387641C10.2723 0.61839 10.4453 0.819471 10.676 0.836784L13.3668 1.01951L8.41995 4.88942L4.63893 1.92308C4.47747 1.79521 4.24696 1.80408 4.09578 1.94395L0.210314 5.66228C0.0451393 5.81898 0.0358629 6.07911 0.189442 6.24721C0.263973 6.34393 0.380807 6.39847 0.502799 6.39343C0.612995 6.39187 0.718114 6.34681 0.795243 6.26808L4.40918 2.80037L8.14842 5.74581C8.30104 5.868 8.51803 5.868 8.67066 5.74581L14.0185 1.58433L13.8095 4.17914C13.8085 4.40601 13.9806 4.59622 14.2064 4.61781H14.2273C14.4427 4.61889 14.6235 4.45608 14.6451 4.2418L14.9167 0.711433C14.932 0.602716 14.893 0.493399 14.8122 0.418989Z" fill="#F04E40"/>
                                            </svg>
                                            <b><?=$arValue['NAME']?></b>
                                            <br>
                                            <i class="mt-1 mb-0"><?=$arResult['CATEGORY_NAMES'][$arValue['IBLOCK_SECTION_ID']] ?></i>
                                            <p class="mt-1 mb-0 text-right"><?if ($arValue['BI_DATA']['date']!='') {?>
                                                <i><?=$arValue['BI_DATA']['date'] ?> (<?=$arValue['BI_DATA']['fio']?>)</i>
                                            <?}?></p>
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
                                        <td class="js-fact"><?=$arValue['BI_DATA']['state_value']?></td>
                                        <?
                                        $className = 'success';
                                        if ($arPassport['PERCENT'] < 30) {
                                            $className = 'failed';
                                        } elseif ($arPassport['PERCENT'] > 30 && $arPassport['PERCENT'] < 90) {
                                            $className = 'normal';
                                        }
                                        ?>
                                        <td class="table__indicator js-percent-exec_view table__indicator--<?=$className?>">
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
                                    <tr class="table-section__chart-row" id="chartTableRow<?=$arValue['XML_ID']?>">
                                        <td colspan="9">
                                            <div class="table-section__chart-container" id="chartContainer">
                                                <script type="text/javascript">
                                                var chart = AmCharts.makeChart(
                                                    "chartDiv<?=$arValue['ID']?>",
                                                    {
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
                                                                "bullet": "round",
                                                                "lineThickness": 2,
                                                                "title": "Плановое значение",
                                                                "valueField": "target",
                                                                "valueAxis": "v1",
                                                                "dashLength": 5,
                                                                "strokeWidth": 2,
                                                            },
                                                            {
                                                                "id": "g2",
                                                                "bullet": "none",
                                                                "lineThickness": 2,
                                                                "title": "Фактическое значение",
                                                                "valueField": "value",
                                                                "valueAxis": "v1",
                                                                "strokeWidth": 2,
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
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
