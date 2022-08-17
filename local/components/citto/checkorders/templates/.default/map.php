<?php

use Bitrix\Main\Page\Asset;
use Citto\Controlorders\Orders;
use Citto\ControlOrders\Settings;
use Bitrix\Main\UI\Filter\Options;
use Bitrix\Main\Grid\Panel\Actions;
use Bitrix\Main\Grid\Options as GridOptions;
use Citto\ControlOrders\Main\AjaxController;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
        die();
}

$list_id = 'control-orders-list-map';

$grid_options = new GridOptions($list_id);
$arUsedColumns = $grid_options->getUsedColumns();

$arUiFilter = [
    ['id' => 'NUMBER', 'name' => 'Номер', 'type' => 'text', 'default' => true],
    ['id' => 'DATE_CREATE', 'name' => 'Дата поручения', 'type' => 'date', 'default' => true],
    ['id' => 'DATE_ISPOLN', 'name' => 'Дата исполнения', 'type' => 'date', 'default' => true],
];

if (
    $arResult['PERMISSIONS']['controler'] ||
    $arResult['PERMISSIONS']['kurator'] ||
    $arResult['PERMISSIONS']['protocol'] ||
    $arResult['PERMISSIONS']['full_access']
) {
    $arIspolnitelItems = [];
    foreach ($arResult['ISPOLNITELTYPES'] as $sKey => $sValue) {
        $arIspolnitelItems[ 'all-' . $sValue['ID'] ] = $sValue['VALUE'];
        foreach ($arResult['ISPOLNITELS'] as $k => $v) {
            if ($v['PROPERTY_TYPE_ENUM_ID'] != $sValue['ID']) {
                continue;
            }
            $arIspolnitelItems[ $v['ID'] ] = $v['NAME'];
        }
    }

    $arUiFilter[] = [
        'id'        => 'ISPOLNITEL',
        'name'      => 'Исполнитель',
        'type'      => 'list',
        'params'    => ['multiple' => 'Y'],
        'items'     => $arIspolnitelItems,
    ];
    $arUiFilter[] = [
        'id'        => 'SUBEXECUTOR',
        'name'      => 'Соисполнитель',
        'type'      => 'list',
        'params'    => ['multiple' => 'Y'],
        'items'     => $arIspolnitelItems,
    ];
}

if ($arResult['PERMISSIONS']['ispolnitel']) {
    $arUiFilter[] = [
        'id'    => 'ISPOLNITEL_TYPE',
        'name'  => 'Мой статус',
        'type'  => 'list',
        'items' => [
            'MAIN'  => 'Исполнитель',
            'SUB'   => 'Соисполнитель',
        ]
    ];
}

$type_filter = ['id' => 'TYPE', 'name' => 'Тип поручения', 'type' => 'list', 'params' => ['multiple' => 'Y'], 'default' => true];
foreach ($arResult['TYPES_DATA'] as $key => $value) {
    $type_filter['items'][ $key ] = $value['UF_NAME'];
}
$arUiFilter[] = $type_filter;

if (!empty($arResult['PERMISSIONS']['ispolnitel_delegate_users'])) {
    $arUiFilter[] = [
        'id'        => 'DELEGATE',
        'name'      => 'Делегирование',
        'type'      => 'list',
        'params'    => ['multiple' => 'Y'],
        'default'   => true,
        'items'     => $arResult['PERMISSIONS']['ispolnitel_delegate_users']
    ];
}

$arUiFilter[] = [
    'id'        => 'STATUS',
    'name'      => 'Состояние поручения',
    'type'      => 'list',
    'default'   => true,
    'items'     => [
        1135 => 'Новое',
        1136 => 'На исполнении',
        1137 => 'Ждет контроля',
        1138 => 'Ждет решения',
        1140 => 'Архив',
    ]
];
$arUiFilter[] = [
    'id'        => 'ARCHIVE',
    'name'      => 'С учетом архива',
    'type'      => 'list',
    'default'   => true,
    'items'     => [
        'Y' => 'Да',
        'N' => 'Нет',
    ]
];

$curUserId = $GLOBALS['USER']->GetID();
$arTags = $this->__component->getAvailableTags();

if (!empty($arTags)) {
    $arUiFilter[] = [
        'id'        => 'TAGS',
        'name'      => 'Теги',
        'type'      => 'list',
        'default'   => true,
        'params'    => ['multiple' => 'Y'],
        'items'     => $arTags
    ];
}

require($_SERVER['DOCUMENT_ROOT'] . $this->__component->__path . '/ajax.php');
CBitrixComponent::includeComponentClass('citto:checkorders');

$arThemes = [];
$arThemesTree = AjaxController::classificatorTreeAction(false)[0]['children'];
foreach ($arThemesTree as $category) {
    if ($category['delete_node']) {
        continue;
    }
    $arThemes[ $category['id'] ] = $category['text'];
    foreach ($category['children'] as $theme) {
        if ($theme['delete_node']) {
            continue;
        }
        $arThemes[ $theme['id'] ] = $category['text'] . ' \ ' . $theme['text'];
    }
}

$arUiFilter[] = [
    'id'        => 'THEME',
    'name'      => 'Тематика',
    'type'      => 'list',
    'params'    => ['multiple' => 'Y'],
    'default'   => true,
    'items'     => $arThemes
];

$arUiFilter[] = [
    'id'        => 'WORK_STATUS',
    'name'      => 'Промежуточный статус',
    'type'      => 'list',
    'default'   => true,
    'items'     => [
        '' => 'Все',
        $arParams['ENUM']['WORK_INTER_STATUS']['TOVISA']['ID'] => 'На визировании',
        $arParams['ENUM']['WORK_INTER_STATUS']['TOSIGN']['ID'] => 'На подписи',
    ]
];

$arAdditional = [
    'controler_reject'  => 'Отклоненные контролерами',
    'soon'              => 'Подходит срок',
    'expired'           => 'Просрочено',
];

if (
    $arResult['PERMISSIONS']['controler'] ||
    $arResult['PERMISSIONS']['kurator'] ||
    $arResult['PERMISSIONS']['protocol'] ||
    $GLOBALS['USER']->IsAdmin()
) {
    $arAdditional['vote'] = 'Опрос заявителя';
}

$arUiFilter[] = [
    'id'        => 'ADDITIONAL',
    'name'      => 'Требует внимания',
    'type'      => 'list',
    'default'   => true,
    'items'     => $arAdditional,
];

$sFilterViewTarget = $arParams['RENDER_FILTER_INTO_VIEW'] ?? 'inside_pagetitle';
$this->SetViewTarget($sFilterViewTarget, 10);
?><div class="pagetitle-container pagetitle-flexible-space">
<?$APPLICATION->IncludeComponent(
    'bitrix:main.ui.filter',
    '',
    [
        'FILTER_ID'          => $list_id . '_filter',
        'GRID_ID'            => $list_id,
        'FILTER'             => $arUiFilter,
        'ENABLE_LIVE_SEARCH' => true,
        'ENABLE_LABEL'       => true,
    ]
);?>
</div>
<a class="ui-btn ui-btn-primary" href="/control-orders/?enums=object">Список объектов</a>
<?
$this->EndViewTarget();
?>
<style type="text/css">
    .workarea-content-paddings {
        padding: 0!important;
    }
</style>

<div class="box box-primary">
    <div class="box-body box-profile">
        <div id="map" style="height:660px; width:100%;"></div>
    </div>
</div>

<script type="text/javascript" src="<?=$this->__component->__template->__folder?>/polygons.js"></script>
<script type="text/javascript" src="https://api-maps.yandex.ru/2.1/?lang=ru_RU&coordorder=longlat&apikey=04958d7f-754d-417b-b602-904f940bf94a"></script>
<script type="text/javascript" src="https://yastatic.net/s3/mapsapi-jslibs/area/0.0.1/util.calculateArea.min.js"></script>
<script type="text/javascript" src="https://yastatic.net/s3/mapsapi-jslibs/polylabeler/1.0.2/polylabel.min.js"></script>

<script>

ymaps.ready(['polylabel.create']).then(init);

var myMap,
    geoObjects,
    myCollection,
    myCollectionObj;

function init() {
    myCollection = new ymaps.GeoObjectCollection();
    myCollectionObj = new ymaps.GeoObjectCollection();

    myMap = new ymaps.Map("map", {
        center: [36.24342222, 53.91111424],
        zoom: 8,
        controls: ['fullscreenControl']}, {
            maxAnimationZoomDifference: Infinity,
            searchControlProvider: 'yandex#search'
        }
    );

    clusterer = new ymaps.Clusterer({
        maxZoom: 14,
        gridSize: 180,
        minClusterSize: 2,
        groupByCoordinates: false,
        clusterDisableClickZoom: true,
        clusterIconLayout: 'default#pieChart',
        clusterIconPieChartRadius: 25,
        clusterIconPieChartCoreRadius: 10,
        clusterIconPieChartStrokeWidth: 3,
    });

    var zoomControl = new ymaps.control.ZoomControl({
        options: {
            position: {
                right: 10,
                top: 45
            }
        }
    });
    myMap.margin.setDefaultMargin([0, 0, 0, 0]);
    myMap.controls.add(zoomControl);

    var polygon = [];
    for (var i in mapPolygons) {
        polygon[ i ] = new ymaps.GeoObject(mapPolygons[ i ][0], mapPolygons[ i ][1]);
        myCollectionObj.add(polygon[ i ]);
    }

    myMap.geoObjects.add(myCollectionObj);
    var polylabel = new ymaps.polylabel.create(myMap, myCollectionObj);
    myMap.setBounds(myMap.geoObjects.getBounds(), {
        checkZoomRange: true,
        zoomMargin: [0, 0, 0, 0]
    });

    BX.addCustomEvent('BX.Main.Filter:apply', BX.delegate(function(obj) {
        var waiter = BX.showWait('map');
        request = BX.ajax.runComponentAction(
            'citto:checkorders',
            'getOrdersMap',
            {
                mode: 'ajax',
                json: {
                    action: 'getOrdersMap'
                }
            }
        );
        request.then(function(result) {
            myCollection.removeAll();
            clusterer.removeAll();
            var geoObjects = [];
            var elem = result.data;
            for (var i = 0, len = elem.length; i < len; i++) {
                geoObjects[i] = new ymaps.GeoObject({
                    geometry: {
                        type: 'Point',
                        coordinates: elem[i]['coord'],
                    },
                    properties: {
                        clusterCaption: elem[i]['name'],
                        balloonContentBody: elem[i]['desc'],
                    },
                }, {
                    preset: elem[i]['preset'],
                    zIndex: 750
                });
            }

            clusterer.add(geoObjects);
            myMap.geoObjects.add(clusterer);
            BX.closeWait('map', waiter);
            BX.UI.Notification.Center.notify({
                content: 'Загружено элементов карты: ' + elem.length
            });
        });
    }, this));

    BX.onCustomEvent('BX.Main.Filter:apply');
}
</script>