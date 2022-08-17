<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$filterType = $_REQUEST['type_ispoln'];

$arStatusInfo = [];
ksort($arResult['STATS_DATA']['ACTION']);

$arStatusColors = [
    1135 => '#ff0f00',
    1136 => '#543cd9',
    1137 => '#ff8433',
    1138 => '#fcdb34',
    1140 => '#bfe43a',
];
foreach ($arResult['STATS_DATA']['ACTION'] as $key => $value) {
    if (isset($arParams['ENUM']['ACTION'][ $key ])) {
        $arStatusInfo[] = [
            'title'     => $arParams['ENUM']['ACTION'][ $key ]['VALUE'],
            'value'     => $value,
            'url'       => './?from_stats=true' . ($filterType == 'position' ? '&onlypositions=true' : '') . '&action_filter=' . $key,
            'color'     => $arStatusColors[ $key ],
        ];
    }
}

$arColors = [
    '#FF6600', '#FCD202', '#B0DE09', '#0D8ECF', '#CD0D74', '#CC0000',
    '#00CC00', '#0000CC', '#DDDDDD', '#999999', '#333333', '#990000',
    '#CD5C5C', '#F08080', '#FA8072', '#E9967A', '#FFA07A', '#DC143C',
    '#FF0000', '#B22222', '#8B0000', '#FFC0CB', '#FFB6C1', '#FF69B4',
    '#FF1493', '#C71585', '#DB7093', '#FF7F50', '#FF6347', '#FF4500',
    '#FF8C00', '#FFA500', '#FFE4B5', '#FFDAB9', '#EEE8AA', '#F0E68C',
    '#BDB76B', '#E6E6FA', '#D8BFD8', '#DDA0DD', '#EE82EE', '#DA70D6',
    '#FF00FF', '#BA55D3', '#9370DB', '#8A2BE2', '#9400D3', '#9932CC',
    '#8B008B', '#800080', '#4B0082', '#6A5ACD', '#483D8B', '#DEB887',
    '#D2B48C', '#BC8F8F', '#F4A460', '#DAA520', '#B8860B', '#CD853F',
    '#D2691E', '#8B4513', '#A0522D', '#A52A2A', '#800000', '#000000',
    '#808080', '#C0C0C0', '#808000', '#00FF00', '#008000', '#00FFFF',
    '#008080', '#0000FF', '#000080', '#ADFF2F', '#7CFC00', '#32CD32',
    '#98FB98', '#90EE90', '#00FA9A', '#00FF7F', '#3CB371', '#2E8B57',
    '#228B22', '#006400', '#9ACD32', '#6B8E23', '#556B2F', '#66CDAA',
    '#8FBC8F', '#20B2AA', '#008B8B', '#E0FFFF', '#AFEEEE', '#7FFFD4',
    '#40E0D0', '#48D1CC', '#00CED1', '#5F9EA0', '#4682B4', '#B0C4DE',
    '#B0E0E6', '#ADD8E6', '#87CEEB', '#87CEFA', '#00BFFF', '#1E90FF',
    '#6495ED', '#7B68EE', '#4169E1', '#00008B', '#191970',
];

$arIspolnitesInfo = [];
$arIspolnitesProblemsInfo = [];
$arIspolnitesHistorySrokInfo = [];
$arIspolnitesDisceplineInfo = [];
$arDisciplineItems = [
    'v_srok',
    'srok_narush',
    'worked',
    'no_ispoln',
];
arsort($arResult['STATS_DATA']['ISPOLNITELS_FULL']);
arsort($arResult['STATS_DATA']['ISPOLN_BAD']);

$arTmpColors = $arColors;
shuffle($arTmpColors);
foreach ($arResult['STATS_DATA']['ISPOLNITELS_FULL'] as $sKey => $sValue) {
    foreach ($arTmpColors as $k => $v) {
        $color = $v;
        unset($arTmpColors[ $k ]);
        break;
    }
    $arValue = $arResult['STATS_DATA']['ISPOLNITELS_ACTION'][ $sKey ];
    if (!empty($arValue)) {
        $arValue['sum'] = array_sum($arValue);
        $arValue['ispolnitel'] = $arResult['ISPOLNITELS'][ $sKey ]['NAME'];
        if (empty($arValue['ispolnitel'])) {
            continue;
        }
        $arValue['url'] = './?from_stats=true' . ($filterType == 'position' ? '&onlypositions=true' : '') . '&ispolnitel=' . $sKey;
        foreach (array_keys($arResult['STATS_DATA']['ACTION']) as $key) {
            $arValue['url-' . $key] = './?from_stats=true' . ($filterType == 'position' ? '&onlypositions=true' : '') . '&ispolnitel=' . $sKey . '&action_filter=' . $key;
            $arValue['realValue-' . $key] = (int)$arValue[ $key ];
            $arValue['areaValue-' . $key] = $arValue[ $key ] > 0 ? 11 + (int)$arValue[ $key ] : 0;
        }
        $arIspolnitesInfo[] = $arValue;
    }

    $arValue = $arResult['STATS_DATA']['PROBLEMS'][ $sKey ];
    if (!empty($arValue)) {
        $arIspolnitesProblemsInfo[] = [
            'title' => $arResult['ISPOLNITELS'][ $sKey ]['NAME'],
            'color' => $color,
            'value' => array_sum($arValue),
            'url'   => './?ispolnitel=' . $sKey . '&from_stats=problem',
        ];
    }

    $arValue = $arResult['STATS_DATA']['ISPOLN_BAD'][ $sKey ];
    if (!empty($arValue)) {
        $arIspolnitesHistorySrokInfo[] = [
            'title' => $arResult['ISPOLNITELS'][ $sKey ]['NAME'],
            'color' => $color,
            'value' => $arValue,
            'url'   => './?ispolnitel=' . $sKey . '&from_stats=ispoln_bad',
        ];
    }

    $arValue = $arResult['STATS_DATA']['ISPOLNITELS_DISCIPLIN'][ $sKey ];
    if (!empty($arValue)) {
        $arValue['ispolnitel'] = $arResult['ISPOLNITELS'][ $sKey ]['NAME'];

        foreach ($arDisciplineItems as $discItem) {
            $arValue['url-' . $discItem] = './?ispolnitel=' . $sKey . '&from_stats=' . $discItem;
            if ($arValue[ $discItem ] > 0) {
                $arValue['realValue-' . $discItem] = (int)$arValue[ $discItem ];
                $arValue['areaValue-' . $discItem] = 11 + (int)$arValue[ $discItem ];
            } else {
                unset($arValue[ $discItem ]);
            }
        }

        $arIspolnitesDisceplineInfo[] = $arValue;
    }
}

usort($arIspolnitesProblemsInfo, $this->__component->buildSorter('value', 'desc'));
usort($arIspolnitesHistorySrokInfo, $this->__component->buildSorter('value', 'desc'));

$arCatThemesInfo = [];
$arCatThemesColor = [];
arsort($arResult['STATS_DATA']['CAT_THEMES']);
$arTmpColors = $arColors;
shuffle($arTmpColors);
foreach ($arResult['STATS_DATA']['CAT_THEMES'] as $key => $value) {
    foreach ($arTmpColors as $k => $v) {
        $color = $v;
        unset($arTmpColors[ $k ]);
        break;
    }
    if ($key != '') {
        $arCatThemesInfo[] = [
            'cat' => $arResult['CLASSIFICATOR'][ $key ]['NAME'],
            'title' => $arResult['CLASSIFICATOR'][ $key ]['NAME'] . ' (' . $value. ')',
            'value' => $value,
            'color' => $color,
            'url'   => './?from_stats&cat_theme=' . $key
        ];
    } else {
        $arCatThemesInfo[] = [
            'cat' => 'Без темы',
            'title' => 'Без темы (' . $value. ')',
            'value' => $value,
            'color' => $color,
            'url'   => './?from_stats&cat_theme=without'
        ];
    }
}

$arCategoryInfo = [];
foreach ($arResult['STATS_DATA']['CATEGORY'] as $key => $value) {
    if ($key != '') {
        $arCategoryInfo[] = [
            'title' => $arResult['CATEGORIES'][ $key ]['VALUE'],
            'value' => $value,
            'url'   => './?from_stats&category=' . $key
        ];
    }
}

usort($arCatThemesInfo, $this->__component->buildSorter('value', 'desc'));
usort($arCategoryInfo, $this->__component->buildSorter('value', 'desc'));

$arExpiredInfo = [];
arsort($arResult['STATS_DATA']['EXPIRED']);
$arTmpColors = $arColors;
shuffle($arTmpColors);
foreach ($arResult['STATS_DATA']['EXPIRED'] as $key => $value) {
    if ($key != '') {
        foreach ($arTmpColors as $k => $v) {
            $color = $v;
            unset($arTmpColors[ $k ]);
            break;
        }
        $arExpiredInfo[] = [
            'cat'   => $arResult['ISPOLNITELS'][ $key ]['NAME'],
            'title' => $arResult['ISPOLNITELS'][ $key ]['NAME'] . ' (' . $value. ')',
            'value' => $value,
            'color' => $color,
            'url'   => '?ispolnitel=' . $key . '&from_stats=expired'
        ];
    }
}

usort($arExpiredInfo, $this->__component->buildSorter('value', 'desc'));

$arBrokenSrokInfo = [];
arsort($arResult['STATS_DATA']['BROKEN_SROK']);
$arTmpColors = $arColors;
shuffle($arTmpColors);
foreach ($arResult['STATS_DATA']['BROKEN_SROK'] as $key => $value) {
    if ($key != '') {
        foreach ($arTmpColors as $k => $v) {
            $color = $v;
            unset($arTmpColors[ $k ]);
            break;
        }
        $arBrokenSrokInfo[] = [
            'cat'   => $arResult['ISPOLNITELS'][ $key ]['NAME'],
            'title' => $arResult['ISPOLNITELS'][ $key ]['NAME'] . ' (' . $value. ')',
            'value' => $value,
            'color' => $color,
            'url'   => '?ispolnitel=' . $key . '&from_stats=brokensrok'
        ];
    }
}

usort($arBrokenSrokInfo, $this->__component->buildSorter('value', 'desc'));

$arControlRejectInfo = [];
arsort($arResult['STATS_DATA']['CONTROL_REJECT']);
$arTmpColors = $arColors;
shuffle($arTmpColors);
foreach ($arResult['STATS_DATA']['CONTROL_REJECT'] as $key => $value) {
    if ($key != '') {
        foreach ($arTmpColors as $k => $v) {
            $color = $v;
            unset($arTmpColors[ $k ]);
            break;
        }
        $arControlRejectInfo[] = [
            'cat'   => $arResult['ISPOLNITELS'][ $key ]['NAME'],
            'title' => $arResult['ISPOLNITELS'][ $key ]['NAME'] . ' (' . $value. ')',
            'value' => $value,
            'color' => $color,
            'url'   => '?ispolnitel=' . $key . '&from_stats=controlreject'
        ];
    }
}
?>
<div class="box box-danger">
    <div class="box-header with-border">
        <h3 class="box-title">Фильтрация по дате</h3>
    </div>
    <div class="box-body">
        <form method="GET">
            <input type="hidden" name="stats" value="main" />
            <input type="hidden" name="type_ispoln" value="<?=$filterType?>" />
            <div class="row">
                <div class="col-md-4">От <input
                    class="form-control"
                    type="text"
                    name="FROM"
                    value="<?=($_REQUEST['FROM']!='')?$_REQUEST['FROM']:'' ?>"
                    onclick="BX.calendar({node: this, field: this, bTime: false});"></div>
                <div class="col-md-4">До <input
                    class="form-control"
                    type="text"
                    name="TO"
                    value="<?=($_REQUEST['TO']!='')?$_REQUEST['TO']:'' ?>"
                    onclick="BX.calendar({node: this, field: this, bTime: false});"></div>
                <div class="col-md-4">
                    <br>
                    <button type="submit" class="ui-btn ui-btn-success">Показать</button>
                    <?if (!empty($_REQUEST['FROM']) || !empty($_REQUEST['TO'])) : ?>
                    <button type="button" class="ui-btn ui-btn-success js-clear-date">Сбросить</button>
                    <?endif;?>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="box box-danger">
    <div class="box-header with-border">
        <h3 class="box-title">Фильтрация по типу исполнителей</h3>
    </div>
    <div class="box-body">
        <form method="GET">
            <?php
            $url = '?stats=main&FROM=' . $_REQUEST['FROM'] . '&TO=' . $_REQUEST['TO'];
            ?>
            <div class="row">
                <div class="col-md-12">
                    <div id="view-switcher-container" class="calendar-view-switcher pagetitle-align-right-container">
                        <div class="view-switcher-list">
                            <a
                                href="<?=$url ?>"
                                <?=($filterType=='')?'class="active"':'' ?>>
                                Все
                            </a>
                            <?php
                            foreach ($arResult['ISPOLNITELTYPES'] as $key => $value) {
                                ?>
                                <a
                                    href="<?=$url ?>&type_ispoln=<?=$key?>"
                                    <?=($filterType==$key)?'class="active"':'' ?>>
                                    <?=str_replace(['Заместители', 'Подведомственные'], ['Зам.', 'Подвед.'], $value['VALUE'])?>
                                </a>
                                <?php
                            }
                            ?>
                            <a
                                href="<?=$url ?>&type_ispoln=position"
                                <?=($filterType=='position')?'class="active"':'' ?>>
                                Позиции
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<? if (isset($arResult['STATS_DATA']['CONTROLER_REJECT']) && !empty($arResult['STATS_DATA']['CONTROLER_REJECT'])) : ?>
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Количество предложений об отклонении</h3>
    </div>

    <div class="box-body">
        <div class="chartdiv" id="chartdiv-controler-reject"></div>
    </div>
    <?
    $arControlerReject = [];
    foreach ($arResult['STATS_DATA']['CONTROLER_REJECT'] as $uId => $count) {
        $arUserData = $GLOBALS['userFields']($uId);
        $arControlerReject[] = [
            'name' => $arUserData['FIO'],
            'title' => $arUserData['FIO'] . ' (' . $count . ')',
            'value' => $count,
            'color' => $arColors[ mt_rand(0, count($arColors)-1) ],
        ];
    }
    ?>
    <script type="text/javascript">
    var chartdiv_controler_reject = AmCharts.makeChart('chartdiv-controler-reject', {
        "theme": "none",
        "type": "serial",
        "dataProvider": <?=json_encode($arControlerReject)?>,
        "valueAxes": [{
            "title": "Количество поручений"
        }],
        "legend": false,
        "graphs": [{
            "balloonText": "[[name]]: [[value]]",
            "fillAlphas": 1,
            "lineAlpha": 0.2,
            "title": "Income",
            "type": "column",
            "colorField": "color",
            // "urlField": "url",
            // "urlTarget": "_blank",
            "valueField": "value"
        }],
        "depth3D": 20,
        "angle": 30,
        "rotate": true,
        "categoryField": "title",
        "marginLeft": 400,
        "categoryAxis": {
            "gridPosition": "start",
            "fillAlpha": 0.05,
            "position": "left",
            "ignoreAxisWidth": true,
            "autoWrap": true,
        },
        "balloon":{
            "disableMouseEvents": true
        },
        "export": {
            "enabled": true
        }
    });
    </script>
</div>
<? endif; ?>

<? if (isset($arResult['STATS_DATA']['KURATOR_REJECT']) && !empty($arResult['STATS_DATA']['KURATOR_REJECT'])) : ?>
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Количество отклонении куратором</h3>
    </div>

    <div class="box-body">
        <div class="chartdiv" id="chartdiv-kurator-reject"></div>
    </div>
    <?
    $arKuratorReject = [];
    foreach ($arResult['STATS_DATA']['KURATOR_REJECT'] as $uId => $count) {
        $arUserData = $GLOBALS['userFields']($uId);
        $arKuratorReject[] = [
            'name' => $arUserData['FIO'],
            'title' => $arUserData['FIO'] . ' (' . $count . ')',
            'value' => $count,
            'color' => $arColors[ mt_rand(0, count($arColors)-1) ],
        ];
    }
    ?>
    <script type="text/javascript">
    var chartdiv_kurator_reject = AmCharts.makeChart('chartdiv-kurator-reject', {
        "theme": "none",
        "type": "serial",
        "dataProvider": <?=json_encode($arKuratorReject)?>,
        "valueAxes": [{
            "title": "Количество поручений"
        }],
        "legend": false,
        "graphs": [{
            "balloonText": "[[name]]: [[value]]",
            "fillAlphas": 1,
            "lineAlpha": 0.2,
            "title": "Income",
            "type": "column",
            "colorField": "color",
            // "urlField": "url",
            // "urlTarget": "_blank",
            "valueField": "value"
        }],
        "depth3D": 20,
        "angle": 30,
        "rotate": true,
        "categoryField": "title",
        "marginLeft": 400,
        "categoryAxis": {
            "gridPosition": "start",
            "fillAlpha": 0.05,
            "position": "left",
            "ignoreAxisWidth": true,
            "autoWrap": true,
        },
        "balloon":{
            "disableMouseEvents": true
        },
        "export": {
            "enabled": true
        }
    });
    </script>
</div>
<? endif; ?>

<? if ($filterType != 'position') : ?>
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">По автору поручений</h3>
    </div>

    <div class="box-body">
        <div class="chartdiv" id="chartdiv-category"></div>
    </div>
    <script type="text/javascript">
    var chartdiv_category = AmCharts.makeChart("chartdiv-category", {
        "type": "pie",
        "startDuration": 0,
        "theme": "none",
        "addClassNames": true,
        "legend": {
            "position": "right",
            "marginRight": 100,
            "autoMargins": false,
            "valueFunction" : function (data) {
                return new Intl.NumberFormat('ru-RU').format(data.value);
            }
        },
        "innerRadius": "30%",
        "defs": {
            "filter": [{
                "id": "shadow",
                "width": "200%",
                "height": "200%",
                "feOffset": {
                    "result": "offOut",
                    "in": "SourceAlpha",
                    "dx": 0,
                    "dy": 0
                },
                "feGaussianBlur": {
                    "result": "blurOut",
                    "in": "offOut",
                    "stdDeviation": 5
                },
                "feBlend": {
                    "in": "SourceGraphic",
                    "in2": "blurOut",
                    "mode": "normal"
                }
            }]
        },
        "dataProvider": <?=json_encode($arCategoryInfo)?>,
        "valueField": "value",
        "titleField": "title",
        "export": {
            "enabled": true
        }
    });

    chartdiv_category.addListener("clickSlice", function(e) {
        if (typeof e.dataItem.dataContext.url !== 'undefined') {
            window.open(e.dataItem.dataContext.url);
        }
    });

    </script>
</div>
<? endif; ?>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">По статусу</h3>
    </div>

    <div class="box-body">
        <div class="chartdiv" id="chartdiv-status"></div>
    </div>
    <script type="text/javascript">
    var chartdiv_status = AmCharts.makeChart('chartdiv-status', {
        "type": "pie",
        "startDuration": 0,
        "theme": "none",
        "addClassNames": true,
        "legend": {
            "position": "right",
            "marginRight": 100,
            "autoMargins": false,
            "valueFunction" : function (data) {
                return new Intl.NumberFormat('ru-RU').format(data.value);
            }
        },
        "innerRadius": "30%",
        "colors": <?=json_encode(array_values($arStatusColors))?>,
        "dataProvider": <?=json_encode($arStatusInfo)?>,
        "valueField": "value",
        "titleField": "title",
        "export": {
            "enabled": true
        }
    });

    chartdiv_status.addListener("init", handleInit);
    chartdiv_status.addListener("rollOverSlice", function(e) {
        handleRollOver(e);
    });
    chartdiv_status.addListener("clickSlice", function(e) {
        if (typeof e.dataItem.dataContext.url !== 'undefined') {
            window.open(e.dataItem.dataContext.url);
        }
    });

    function handleInit(){
        chartdiv_status.legend.addListener("rollOverItem", handleRollOver);
    }

    function handleRollOver(e){
        var wedge = e.dataItem.wedge.node;
        wedge.parentNode.appendChild(wedge);
    }

    </script>
</div>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">По исполнителям</h3>
    </div>
    <?
    $height = 150;
    if (count($arIspolnitesInfo) > 1) {
        $height = 50 + (count($arIspolnitesInfo)*50);
    }
    ?>
    <div class="box-body">
        <div class="chartdiv" id="chartdiv-ispolnitels" style="height:<?=$height?>px;"></div>
    </div>
    <script type="text/javascript">
    var chartdiv_ispolnitels = AmCharts.makeChart("chartdiv-ispolnitels", {
        "type": "serial",
        "theme": "none",
        "legend": {
            "horizontalGap": 10,
            "maxColumns": 1,
            "position": "right",
            "useGraphSettings": true,
            "markerSize": 10
        },
        "dataProvider": <?=json_encode($arIspolnitesInfo)?>,
        "valueAxes": [{
            "stackType": "regular",
            "axisAlpha": 0.5,
            "gridAlpha": 0
        }],
        "graphs": [
            {
                "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[realValue-1135]]</b></span>",
                "fillAlphas": 0.8,
                "labelText": "[[realValue-1135]]",
                "lineAlpha": 0.3,
                "title": "Новое",
                "type": "column",
                "color": "#000000",
                "fillColors": '<?=$arStatusColors[1135]?>',
                "lineColor": '<?=$arStatusColors[1135]?>',
                "urlField": "url-1135",
                "urlTarget": "_blank",
                "valueField": "areaValue-1135"
            }, {
                "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[realValue-1136]]</b></span>",
                "fillAlphas": 0.8,
                "labelText": "[[realValue-1136]]",
                "lineAlpha": 0.3,
                "title": "На исполнении",
                "type": "column",
                "color": "#000000",
                "fillColors": '<?=$arStatusColors[1136]?>',
                "lineColor": '<?=$arStatusColors[1136]?>',
                "urlField": "url-1136",
                "urlTarget": "_blank",
                "valueField": "areaValue-1136"
            }, {
                "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[realValue-1137]]</b></span>",
                "fillAlphas": 0.8,
                "labelText": "[[realValue-1137]]",
                "lineAlpha": 0.3,
                "title": "Ждет контроля",
                "type": "column",
                "color": "#000000",
                "fillColors": '<?=$arStatusColors[1137]?>',
                "lineColor": '<?=$arStatusColors[1137]?>',
                "urlField": "url-1137",
                "urlTarget": "_blank",
                "valueField": "areaValue-1137"
            }, {
                "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[realValue-1138]]</b></span>",
                "fillAlphas": 0.8,
                "labelText": "[[realValue-1138]]",
                "lineAlpha": 0.3,
                "title": "Ждет решения",
                "type": "column",
                "color": "#000000",
                "fillColors": '<?=$arStatusColors[1138]?>',
                "lineColor": '<?=$arStatusColors[1138]?>',
                "urlField": "url-1138",
                "urlTarget": "_blank",
                "valueField": "areaValue-1138"
            }, {
                "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[realValue-1140]]</b></span>",
                "fillAlphas": 0.8,
                "labelText": "[[realValue-1140]]",
                "lineAlpha": 0.3,
                "title": "Архив",
                "type": "column",
                "color": "#000000",
                "fillColors": '<?=$arStatusColors[1140]?>',
                "lineColor": '<?=$arStatusColors[1140]?>',
                "urlField": "url-1140",
                "urlTarget": "_blank",
                "valueField": "areaValue-1140"
            }
        ],
        "rotate": true,
        "categoryField": "ispolnitel",
        "marginLeft": 400,
        "categoryAxis": {
            "gridPosition": "start",
            "axisAlpha": 0,
            "gridAlpha": 0,
            "position": "left",
            "ignoreAxisWidth": true,
            "autoWrap": true,
            "labelFunction": function(value, data) {
                return value + ' (' + data.dataContext.sum + ')';
            },
            "listeners": [
                {
                    "event": "clickItem",
                    "method": function(data) {
                        window.open(data.serialDataItem.dataContext.url);
                    }
                }
            ]
        },
        "export": {
            "enabled": true
        }
    });
    </script>
</div>

<? if ($filterType != 'position') : ?>
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">По теме поручения</h3>
        </div>
        <?
        $height = 150;
        if (count($arCatThemesInfo) > 1) {
            $height = 50 + (count($arCatThemesInfo)*50);
        }
        ?>
        <div class="box-body">
            <div class="chartdiv" id="chartdiv-cat-theme" style="height:<?=$height?>px;"></div>
        </div>
        <script type="text/javascript">
        var chartdiv_cat_theme = AmCharts.makeChart("chartdiv-cat-theme", {
            "theme": "none",
            "type": "serial",
            "dataProvider": <?=json_encode($arCatThemesInfo)?>,
            "valueAxes": [{
                "title": "Количество поручений"
            }],
            "legend": false,
            "graphs": [{
                "balloonText": "[[cat]]: [[value]]",
                "fillAlphas": 1,
                "lineAlpha": 0.2,
                "title": "Income",
                "type": "column",
                "colorField": "color",
                "urlField": "url",
                "urlTarget": "_blank",
                "valueField": "value"
            }],
            "depth3D": 20,
            "angle": 30,
            "rotate": true,
            "categoryField": "title",
            "marginLeft": 400,
            "categoryAxis": {
                "gridPosition": "start",
                "fillAlpha": 0.05,
                "position": "left",
                "ignoreAxisWidth": true,
                "autoWrap": true,
            },
            "balloon":{"disableMouseEvents":true},
            "export": {
                "enabled": true
            }
        });

        </script>
    </div>

    <? if (!empty($arIspolnitesProblemsInfo)) : ?>
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Проблемные поручения</h3>
        </div>
        <?
        $height = 150;
        if (count($arIspolnitesProblemsInfo) > 1) {
            $height = 50 + (count($arIspolnitesProblemsInfo)*50);
        }
        ?>
        <div class="box-body">
            <div class="chartdiv" id="chartdiv-ispolnitelsproblems" style="height:<?=$height?>px;"></div>
        </div>
        <script type="text/javascript">
        var chartdiv_ispolnitelsproblems = AmCharts.makeChart("chartdiv-ispolnitelsproblems", {
            "theme": "none",
            "type": "serial",
            "dataProvider": <?=json_encode($arIspolnitesProblemsInfo)?>,
            "valueAxes": [{
                "title": "Количество поручений"
            }],
            "legend": false,
            "graphs": [{
                "balloonText": "[[category]]: [[value]]",
                "fillAlphas": 1,
                "lineAlpha": 0.2,
                "title": "Income",
                "type": "column",
                "colorField": "color",
                "urlField": "url",
                "urlTarget": "_blank",
                "valueField": "value"
            }],
            "depth3D": 20,
            "angle": 30,
            "rotate": true,
            "categoryField": "title",
            "marginLeft": 400,
            "categoryAxis": {
                "gridPosition": "start",
                "fillAlpha": 0.05,
                "position": "left",
                "ignoreAxisWidth": true,
                "autoWrap": true,
                "labelFunction": function(value, data) {
                    return value + ' (' + data.dataContext.value + ')';
                },
                "listeners": [
                    {
                        "event": "clickItem",
                        "method": function(data) {
                            window.open(data.serialDataItem.dataContext.url);
                        }
                    }
                ]
            },
            "balloon":{"disableMouseEvents":true},
            "export": {
                "enabled": true
            }
        });
        </script>
    </div>
    <? endif; ?>

    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">По исполнительской дисциплине</h3>
        </div>
        <?
        $height = 150;
        if (count($arIspolnitesDisceplineInfo) > 1) {
            $height = 50 + (count($arIspolnitesDisceplineInfo)*50);
        }
        ?>
        <div class="box-body">
            <div class="chartdiv" id="chartdiv-discepline" style="height:<?=$height?>px;"></div>
        </div>
        <script type="text/javascript">
        var chartdiv_disceplineispolnitels = AmCharts.makeChart("chartdiv-discepline", {
            "type": "serial",
            "theme": "none",
            "legend": {
                "horizontalGap": 10,
                "maxColumns": 1,
                "position": "right",
                "useGraphSettings": true,
                "markerSize": 10
            },
            "dataProvider": <?=json_encode($arIspolnitesDisceplineInfo)?>,
            "valueAxes": [{
                "stackType": "regular",
                "axisAlpha": 0.5,
                "gridAlpha": 0
            }],
            "graphs": [
                {
                    "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[realValue-v_srok]]</b></span>",
                    "fillAlphas": 1,
                    "labelText": "[[realValue-v_srok]]",
                    "lineAlpha": 1,
                    "title": "Выполнено в срок",
                    "type": "column",
                    "color": "#000000",
                    "fillColors": '<?=$arStatusColors[1140]?>',
                    "lineColor": '<?=$arStatusColors[1140]?>',
                    "urlField": "url-v_srok",
                    "urlTarget": "_blank",
                    "valueField": "areaValue-v_srok"
                }, {
                    "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[realValue-srok_narush]]</b></span>",
                    "fillAlphas": 1,
                    "labelText": "[[realValue-srok_narush]]",
                    "lineAlpha": 1,
                    "title": "Выполнено с нарушением сроков",
                    "type": "column",
                    "color": "#000000",
                    "fillColors": '<?=$arStatusColors[1137]?>',
                    "lineColor": '<?=$arStatusColors[1137]?>',
                    "urlField": "url-srok_narush",
                    "urlTarget": "_blank",
                    "valueField": "areaValue-srok_narush"
                }, {
                    "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[realValue-worked]]</b></span>",
                    "fillAlphas": 1,
                    "labelText": "[[realValue-worked]]",
                    "lineAlpha": 1,
                    "title": "В работе",
                    "type": "column",
                    "color": "#000000",
                    "fillColors": '<?=$arStatusColors[1136]?>',
                    "lineColor": '<?=$arStatusColors[1136]?>',
                    "urlField": "url-worked",
                    "urlTarget": "_blank",
                    "valueField": "areaValue-worked"
                }, {
                    "balloonText": "<b>[[title]]</b><br><span style='font-size:14px'>[[category]]: <b>[[realValue-no_ispoln]]</b></span>",
                    "fillAlphas": 1,
                    "labelText": "[[realValue-no_ispoln]]",
                    "lineAlpha": 1,
                    "title": "Не выполнено",
                    "type": "column",
                    "color": "#000000",
                    "fillColors": '<?=$arStatusColors[1135]?>',
                    "lineColor": '<?=$arStatusColors[1135]?>',
                    "urlField": "url-no_ispoln",
                    "urlTarget": "_blank",
                    "valueField": "areaValue-no_ispoln"
                }
            ],
            "rotate": true,
            "marginLeft": 400,
            "categoryField": "ispolnitel",
            "categoryAxis": {
                "gridPosition": "start",
                "axisAlpha": 0,
                "gridAlpha": 0,
                "position": "left",
                "ignoreAxisWidth": true,
                "autoWrap": true,
                "labelFunction": function(value, data) {
                    return value + ' (' + data.dataContext.full + ')';
                },
            },
            "export": {
                "enabled": true
            }
        });
        </script>
    </div>

    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">Информация не представлена (срок истек)</h3>
        </div>
        <?
        $height = 150;
        if (count($arExpiredInfo) > 1) {
            $height = 50 + (count($arExpiredInfo)*50);
        }
        ?>
        <div class="box-body">
            <div class="chartdiv" id="chartdiv-expired" style="height:<?=$height?>px;"></div>
        </div>
        <script type="text/javascript">
        var chartdiv_expired = AmCharts.makeChart("chartdiv-expired", {
            "theme": "none",
            "type": "serial",
            "dataProvider": <?=json_encode($arExpiredInfo)?>,
            "valueAxes": [{
                "title": "Количество поручений"
            }],
            "legend": false,
            "graphs": [{
                "balloonText": "<span style='font-size:14px'>[[cat]]: <b>[[value]]</b></span>",
                "fillAlphas": 1,
                "lineAlpha": 0.2,
                "type": "column",
                "colorField": "color",
                "urlField": "url",
                "urlTarget": "_blank",
                "valueField": "value"
            }],
            "depth3D": 20,
            "angle": 30,
            "rotate": true,
            "categoryField": "title",
            "marginLeft": 400,
            "categoryAxis": {
                "gridPosition": "start",
                "fillAlpha": 0.05,
                "position": "left",
                "ignoreAxisWidth": true,
                "autoWrap": true,
            },
            "balloon":{"disableMouseEvents":true},
            "export": {
                "enabled": true
            }
        });
        </script>
    </div>

    <? if (!empty($arBrokenSrokInfo)) : ?>
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">По нарушению срока представления информации</h3>
        </div>
        <?
        $height = 150;
        if (count($arBrokenSrokInfo) > 1) {
            $height = 50 + (count($arBrokenSrokInfo)*50);
        }
        ?>
        <div class="box-body">
            <div class="chartdiv" id="chartdiv-broken-srok" style="height:<?=$height?>px;"></div>
        </div>
        <script type="text/javascript">
        var chartdiv_brokensrok = AmCharts.makeChart("chartdiv-broken-srok", {
            "theme": "none",
            "type": "serial",
            "dataProvider": <?=json_encode($arBrokenSrokInfo)?>,
            "valueAxes": [{
                "title": "Количество поручений"
            }],
            "legend": false,
            "graphs": [{
                "balloonText": "<span style='font-size:14px'>[[cat]]: <b>[[value]]</b></span>",
                "fillAlphas": 1,
                "lineAlpha": 0.2,
                "type": "column",
                "colorField": "color",
                "urlField": "url",
                "urlTarget": "_blank",
                "valueField": "value"
            }],
            "depth3D": 20,
            "angle": 30,
            "rotate": true,
            "categoryField": "title",
            "marginLeft": 400,
            "categoryAxis": {
                "gridPosition": "start",
                "fillAlpha": 0.05,
                "position": "left",
                "ignoreAxisWidth": true,
                "autoWrap": true,
            },
            "balloon":{"disableMouseEvents":true},
            "export": {
                "enabled": true
            }
        });
        </script>
    </div>
    <? endif; ?>

    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">По переносу сроков (не выполнено)</h3>
        </div>
        <?
        $height = 150;
        if (count($arIspolnitesHistorySrokInfo) > 1) {
            $height = 50 + (count($arIspolnitesHistorySrokInfo)*50);
        }
        ?>
        <div class="box-body">
            <div class="chartdiv" id="chartdiv-ispolnitelshistorysrok" style="height:<?=$height?>px;"></div>
        </div>
        <script type="text/javascript">
        var chartdiv_ispolnitelshistorysrok = AmCharts.makeChart("chartdiv-ispolnitelshistorysrok", {
            "theme": "none",
            "type": "serial",
            "dataProvider": <?=json_encode($arIspolnitesHistorySrokInfo)?>,
            "valueAxes": [{
                "title": "Количество поручений"
            }],
            "legend": false,
            "graphs": [{
                "balloonText": "[[category]]: [[value]]",
                "fillAlphas": 1,
                "lineAlpha": 0.2,
                "title": "Income",
                "type": "column",
                "colorField": "color",
                "urlField": "url",
                "urlTarget": "_blank",
                "valueField": "value"
            }],
            "depth3D": 20,
            "angle": 30,
            "rotate": true,
            "categoryField": "title",
            "marginLeft": 400,
            "categoryAxis": {
                "gridPosition": "start",
                "fillAlpha": 0.05,
                "position": "left",
                "ignoreAxisWidth": true,
                "autoWrap": true,
                "labelFunction": function(value, data) {
                    return value + ' (' + data.dataContext.value + ')';
                },
                "listeners": [
                    {
                        "event": "clickItem",
                        "method": function(data) {
                            window.open(data.serialDataItem.dataContext.url);
                        }
                    }
                ]
            },
            "balloon":{"disableMouseEvents":true},
            "export": {
                "enabled": true
            }
        });
        </script>
    </div>

    <? if (!empty($arControlRejectInfo)) : ?>
    <div class="box box-primary">
        <div class="box-header with-border">
            <h3 class="box-title">По возвратам на доработку</h3>
        </div>
        <?
        $height = 150;
        if (count($arControlRejectInfo) > 1) {
            $height = 50 + (count($arControlRejectInfo)*50);
        }
        ?>
        <div class="box-body">
            <div class="chartdiv" id="chartdiv-control-reject" style="height:<?=$height?>px;"></div>
        </div>
        <script type="text/javascript">
        var chartdiv_control_reject = AmCharts.makeChart("chartdiv-control-reject", {
            "theme": "none",
            "type": "serial",
            "dataProvider": <?=json_encode($arControlRejectInfo)?>,
            "valueAxes": [{
                "title": "Количество поручений"
            }],
            "legend": false,
            "graphs": [{
                "balloonText": "<span style='font-size:14px'>[[cat]]: <b>[[value]]</b></span>",
                "fillAlphas": 1,
                "lineAlpha": 0.2,
                "type": "column",
                "colorField": "color",
                "urlField": "url",
                "urlTarget": "_blank",
                "valueField": "value"
            }],
            "depth3D": 20,
            "angle": 30,
            "rotate": true,
            "categoryField": "title",
            "marginLeft": 400,
            "categoryAxis": {
                "gridPosition": "start",
                "fillAlpha": 0.05,
                "position": "left",
                "ignoreAxisWidth": true,
                "autoWrap": true,
            },
            "balloon":{"disableMouseEvents":true},
            "export": {
                "enabled": true
            }
        });
        </script>
    </div>
    <? endif; ?>
<? endif; ?>