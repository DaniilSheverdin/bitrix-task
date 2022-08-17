<?

use Bitrix\Main\Loader;
use Citto\ControlOrders\Settings;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
global $APPLICATION;

$APPLICATION->SetTitle('Редактирование списков - Объекты поручений');
$list_id = $_REQUEST['enums'];

$this->SetViewTarget('inside_pagetitle', 10);
?>

<div class="pagetitle-container pagetitle-flexible-space">
<!--Будущий фильтр-->
</div>

<?php
$this->EndViewTarget();
$this->SetViewTarget('inside_pagetitle', 10);
?>
<a class="ui-btn ui-btn-primary ui-btn-icon-add js-add-new-object ml-auto" href="javascript:void(0)">Добавить объект</a>
<?
$this->EndViewTarget();

Loader::includeModule('iblock');

$arOrders = [];
$arFilter = $this->__component->getListFilter();
$arFilter['!PROPERTY_ACTION'] = Settings::$arActions['ARCHIVE'];
$arFilter['PROPERTY_POSITION_TO'] = false;

$res = CIBlockElement::GetList(
    false,
    $arFilter,
    false,
    false,
    [
        'ID',
        'NAME',
        'DETAIL_TEXT',
        'PROPERTY_NUMBER',
        'PROPERTY_OBJECT',
        'PROPERTY_DATE_CREATE',
    ]
);
while ($arFields = $res->GetNext()) {
    $text = trim(strip_tags($arFields['~DETAIL_TEXT']));
    $defText = $arFields['NAME'] . ' № ' . $arFields['PROPERTY_NUMBER_VALUE'] . ' от ' . $arFields['PROPERTY_DATE_CREATE_VALUE'];
    if (empty($text)) {
        $text = $defText;
    } else {
        $subText = mb_substr($text, 0, 150);
        if ($text != $subText) {
            $text = '<span title="' . ($defText . "\r\n\r\n" . $text) . '">' . $subText . '...</span>';
        }
    }
    $arCurItem = [
        'text'          => $text,
        'text_short'    => '[' . $arFields['ID'] . '] ' . ($subText ?: $defText),
        'id'            => $arFields['ID'],
        'link'          => '/control-orders/?detail=' . $arFields['ID'],
    ];
    $arOrders[ $arFields['ID'] ] = $arCurItem;
}
?>
<script type="text/javascript">
    var arOrders = <?=json_encode($arOrders, JSON_UNESCAPED_UNICODE)?>;
</script>
<?
$res = CIBlockElement::GetList(
    false,
    ['IBLOCK_ID' => 511, 'ACTIVE' => 'Y',],
    false,
    false,
    ['ID', 'NAME', 'DETAIL_TEXT',]
);
$arObjects = [];
while ($arFields = $res->GetNext()) {
    $arFields['DADATA'] = json_decode($arFields['~DETAIL_TEXT'], true);
    $arFields['ITEMS'] = [];
    $arObjects[ $arFields['ID'] ] = $arFields;
}

$arFilter = $this->__component->getListFilter();
$arFilter['!PROPERTY_OBJECT'] = false;
$arFilter['PROPERTY_POSITION_TO'] = false;
$res = CIBlockElement::GetList(
    false,
    $arFilter,
    false,
    false,
    [
        'ID',
        'NAME',
        'DETAIL_TEXT',
        'PROPERTY_NUMBER',
        'PROPERTY_OBJECT',
        'PROPERTY_DATE_CREATE',
    ]
);
while ($arFields = $res->GetNext()) {
    foreach ($arFields['PROPERTY_OBJECT_VALUE'] as $object) {
        $arObjects[ $object ]['ITEMS'][] = $arFields['ID'];
    }
}

$arItems = [];
$counterDadata = 0;
foreach ($arObjects as $object) {
    if (!isset($object['DADATA']['data']['fias_id'])) {
        if (
            $counterDadata < 5 &&
            $oCurl = curl_init('http://suggestions.dadata.ru/suggestions/api/4_1/rs/geolocate/address')
        ) {
            $requestData = [
                'lat' => $object['DADATA']['data']['geo_lat'],
                'lon' => $object['DADATA']['data']['geo_lon'],
            ];
            curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($oCurl, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Token 697e4e53b055f8cbb596f79570f2cbfd118a4a68'
            ]);
            curl_setopt($oCurl, CURLOPT_POST, 1);
            curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode($requestData));
            $sResult = curl_exec($oCurl);
            $arDadata = json_decode($sResult, true);
            if (isset($arDadata['suggestions'][0])) {
                $object['DADATA'] = $arDadata['suggestions'][0];
                $object['DADATA']['data']['geo_lat'] = $requestData['lat'];
                $object['DADATA']['data']['geo_lon'] = $requestData['lon'];
                (new CIBlockElement())->Update(
                    $object['ID'],
                    [
                        'DETAIL_TEXT' => json_encode($object['DADATA'], JSON_UNESCAPED_UNICODE)
                    ]
                );
            }
            curl_close($oCurl);
            $counterDadata++;
        }
    }

    $area = $object['DADATA']['data']['area_with_type'] ?? '-';
    $city = $object['DADATA']['data']['city'] ?? $object['DADATA']['data']['settlement'] ?? '-';
    $street = $object['DADATA']['data']['street'] ?? 'другая улица';
    $arItems[ $area ][ $city ][ $street ][] = $object;
}

echo '<table class="table table-sm table-bordered table-hover">';
ksort($arItems);
foreach ($arItems as $area => $arCities) {
    if ($area != '-') {
        echo '<tr><th colspan="2">' . $area . '</th></tr>';
    }
    ksort($arCities);
    foreach ($arCities as $city => $streets) {
        if ($city != '-') {
            echo '<tr><th colspan="2">' . $city . '</th></tr>';
        }
        ksort($streets);
        foreach ($streets as $objects) {
            foreach ($objects as $object) {
                $bHasItems = !empty($object['ITEMS']);
                ?>
                <tr>
                    <td width="70%"><?=$object['NAME']?> (<?=$object['DADATA']['value']?>)</td>
                    <td width="30%">
                        <a
                            class="ui-btn ui-btn-xs ui-btn-primary ui-btn-icon-edit js-edit-object"
                            data-id="<?=$object['ID']?>"
                            data-coords="[<?=$object['DADATA']['data']['geo_lon']?>, <?=$object['DADATA']['data']['geo_lat']?>]"
                            data-name="<?=$object['NAME']?>"
                            data-addr="<?=$object['DADATA']['value']?>"
                            data-items='<?=json_encode($object['ITEMS'], JSON_UNESCAPED_UNICODE)?>'
                            data-fias='<?=json_encode($object['DADATA'], JSON_UNESCAPED_UNICODE)?>'
                            title="Изменить объект"
                            ></a>
                        <?
                        if ($bHasItems) {
                            ?>
                            <a
                                class="ui-btn ui-btn-xs ui-btn-danger ui-btn-icon-remove ui-btn-disabled ml-2"
                                title="У объекта есть поручения"
                                ></a>
                            <a
                                target="_blank"
                                href="/control-orders/?objectId=<?=$object['ID']?>"
                                class="ui-btn ui-btn-xs ui-btn-danger ui-btn-icon-list ml-2"
                                title="Поручения по объекту"
                                ></a>
                            <?
                        } else {
                            ?>
                            <a
                                class="ui-btn ui-btn-xs ui-btn-danger ui-btn-icon-remove js-delete-object ml-2"
                                data-id="<?=$object['ID']?>"
                                title="Удалить объект"
                                ></a>
                            <a
                                class="ui-btn ui-btn-xs ui-btn-danger ui-btn-icon-list ui-btn-disabled ml-2"
                                title="Нет поручений"
                                ></a>
                            <?
                        }
                        ?>
                    </td>
                </tr>
                <?
            }
        }
    }
}
?>
</table>

<?$this->SetViewTarget('topblock', 1000);?>
<div
    class="modal"
    id="modalObject"
    tabindex="-1"
    role="dialog"
    aria-labelledby="modalObject"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Объект поручения</h5>
                <button
                    type="button"
                    class="close"
                    data-dismiss="modal"
                    aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body pt-2">
                <form class="js-object-add-simple">
                    <div class="row">
                        <div class="col-12">
                            <b>Название объекта</b>
                            <br/>
                            <input
                                class="form-control js-find-object"
                                name="NAME"
                                list="object-list"
                                placeholder="Название объекта" />
                            <datalist id="object-list">
                            </datalist>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <b>Адрес объекта</b>
                            <br/>
                            <div class="d-flex">
                                <input
                                    class="form-control js-find-address"
                                    name="ADDRESS"
                                    placeholder="Адрес объекта"
                                    required />
                                <a class="ui-btn ui-btn-success ui-btn-icon-place ml-3 js-open-map"></a>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <div id="map-container" style="display:none;width:100%;height:400px;"></div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <table width="100%" class="table table-sm table-bordered table-hover w-100 js-items-table">
                                <tbody></tbody>
                                <tfoot></tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-12">
                            <input
                                type="hidden"
                                name="ID" />
                            <input
                                type="hidden"
                                class="js-fias-simple"
                                name="FIAS" />
                            <input
                                type="hidden"
                                name="RELOAD"
                                value="Y" />
                            <input
                                class="ui-btn ui-btn-primary js-modal-btn"
                                type="submit"
                                value="Изменить"
                                data-add="Добавить"
                                data-edit="Изменить"
                                />
                        </div>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>
<?
$this->EndViewTarget();
?>

<link href="<?=$this->GetFolder()?>/suggestions.min.css" type="text/css"  rel="stylesheet" />
<script type="text/javascript" src="<?=$this->GetFolder()?>/polygons.js"></script>
<script type="text/javascript" src="<?=$this->GetFolder()?>/jquery.suggestions.js"></script>
<script type="text/javascript" src="/bitrix/templates/.default/bootstrap.min.js"></script>
<script type="text/javascript" src="https://api-maps.yandex.ru/2.1/?lang=ru_RU&coordorder=longlat&apikey=04958d7f-754d-417b-b602-904f940bf94a"></script>
<script type="text/javascript" src="https://yastatic.net/s3/mapsapi-jslibs/area/0.0.1/util.calculateArea.min.js"></script>
<script type="text/javascript" src="https://yastatic.net/s3/mapsapi-jslibs/polylabeler/1.0.2/polylabel.min.js"></script>
<script>
ymaps.ready(['polylabel.create']).then(initObjectsMap);
var myMap,
    myPlacemark,
    json;

var modalId = '#modalObject',
    modalEdit = $(modalId);

function initObjectsMap() {
    myMap = new ymaps.Map("map-container", {
        center: [37.617348, 54.193122],
        zoom: 8,
        controls: ['fullscreenControl']}, {
            maxAnimationZoomDifference: Infinity,
            searchControlProvider: 'yandex#search'
        }
    );

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

    myMap.events.add('click', function (e) {
        var coords = e.get('coords');

        if (myPlacemark) {
            myPlacemark.geometry.setCoordinates(coords);
        } else {
            myPlacemark = new ymaps.Placemark(coords, {
                iconCaption: 'поиск...'
            }, {
                preset: 'islands#blueCircleDotIcon',
                draggable: true
            });
            myMap.geoObjects.add(myPlacemark);
            myPlacemark.events.add('dragend', function () {
                getAddress(myPlacemark.geometry.getCoordinates());
            });
        }
        getAddress(coords);
    });
}

// Определяем адрес по координатам (обратное геокодирование).
function getAddress(coords) {
    myPlacemark.properties.set('iconCaption', 'поиск...');
    ymaps.geocode(coords).then(function (res) {
        var firstGeoObject = res.geoObjects.get(0);
        json = {
            value : firstGeoObject.getAddressLine(),
            data: {
                geo_lon: coords[0],
                geo_lat: coords[1],
                city: [
                    firstGeoObject.getLocalities().length ? firstGeoObject.getLocalities() : firstGeoObject.getAdministrativeAreas()
                ].filter(Boolean).join(', '),
                street: [
                    firstGeoObject.getThoroughfare() || firstGeoObject.getPremise()
                ].filter(Boolean).join(', '),
            }
        };
        modalEdit.find('[name=FIAS]').val(JSON.stringify(json));
        if (json.value) {
            modalEdit.find('[name=ADDRESS]').val(json.value);
        }
        myPlacemark.properties
            .set({
                iconCaption: [
                    firstGeoObject.getLocalities().length ? firstGeoObject.getLocalities() : firstGeoObject.getAdministrativeAreas(),
                    firstGeoObject.getThoroughfare() || firstGeoObject.getPremise()
                ].filter(Boolean).join(', '),

                balloonContent: firstGeoObject.getAddressLine()
            });
    });
}

$(document).ready(function() {
    function renderOrdersTable(items) {
        let itemsTable = modalEdit.find('.js-items-table tbody');
        function renderRow(id, table) {
            let row = arOrders[ id ];
            let tr = $('<tr>').appendTo(table);
            let td1 = $('<td>').appendTo(tr);
            $('<a>', {
                href: row['link'],
                html: row['text'],
                target: '_blank'
            }).appendTo(td1);

            let td2 = $('<td>', {
                width: '1%',
            }).appendTo(tr);

            let input = $('<input/>', {
                type: 'hidden',
                class: 'js-item-orders',
                name: 'ORDERS[]',
                value: row['id'],
            }).appendTo(td2);

            $('<a>', {
                'class': 'ui-btn ui-btn-xs ui-btn-danger ui-btn-icon-remove',
                click: function(e) {
                    tr.toggleClass('table-danger');
                    input.val(input.val()*-1);
                }
            }).appendTo(td2);
        }
        if (itemsTable.length) {
            itemsTable.html('');
            for (let i in items) {
                renderRow(items[i], itemsTable)
            }

            modalEdit.find('.js-items-table tfoot').html('');
            let tr = $('<tr>').appendTo(modalEdit.find('.js-items-table tfoot'));
            let td1 = $('<td>', {
                width: '99%',
            }).appendTo(tr);
            let select = $('<select>', {
                class: 'select2-order',
            }).appendTo(td1);

            $('<option>').appendTo(select);

            for (let i in arOrders) {
                $('<option>', {
                    value: i,
                    text: arOrders[ i ]['text_short'],
                }).appendTo(select);
            }

            let td2 = $('<td>', {
                width: '1%',
            }).appendTo(tr);

            $('<a>', {
                'class': 'ui-btn ui-btn-xs ui-btn-success ui-btn-icon-add',
                click: function(e) {
                    let selected = parseInt(select.val());
                    if (selected > 0) {
                        renderRow(selected, itemsTable)
                        select.val('').trigger('change');
                    }
                }
            }).appendTo(td2);
        }

        $('.select2-order').select2({
            width: '550px',
            dropdownParent: '#modalObject',
        });
    }
    $('.js-edit-object').on('click', function(e){
        let $this = $(this),
            id = $this.data('id'),
            coords = $this.data('coords'),
            name = $this.data('name'),
            fias = $this.data('fias'),
            addr = $this.data('addr'),
            items = $this.data('items');

        modalEdit.find('[name=ID]').val(id);
        modalEdit.find('[name=NAME]').val(name);
        modalEdit.find('[name=ADDRESS]').val(addr);
        modalEdit.find('[name=FIAS]').val(JSON.stringify(fias));
        modalEdit.find('.js-modal-btn').val(modalEdit.find('.js-modal-btn').data('edit'));
        renderOrdersTable(items);
        modalEdit.modal();
        modalEdit.on('hide.bs.modal', function (e) {
            $('#map-container').hide();
        });

        $('body').on('click', '.js-open-map', function(e) {
            $('#map-container').show();
            myMap.setCenter(coords);
            myMap.setZoom(16);
            json = [];

            if (myPlacemark) {
                myPlacemark.geometry.setCoordinates(coords);
                myPlacemark.properties.set('iconCaption', 'поиск...');
            } else {
                myPlacemark = new ymaps.Placemark(coords, {
                    iconCaption: 'поиск...'
                }, {
                    preset: 'islands#blueCircleDotIcon',
                    draggable: true
                });
                myMap.geoObjects.add(myPlacemark);
            }

            myPlacemark.events.add('dragend', function () {
                getAddress(myPlacemark.geometry.getCoordinates());
            });
            getAddress(coords);
        });

        $('.js-find-address').suggestions({
            token: '697e4e53b055f8cbb596f79570f2cbfd118a4a68',
            type: 'ADDRESS',
            onSelect: function(suggestion) {
                $(this).val(suggestion.value);
                $('.js-fias-simple').val(JSON.stringify(suggestion));
            },
            onSelectNothing: function(suggestion) {
                $(this).val('');
                $('.js-fias-simple').val('');
            }
        });

        e.preventDefault();
        return false;
    });

    $('.js-add-new-object').on('click', function(e){

        request = BX.ajax.runComponentAction(
            'citto:checkorders',
            'getObject',
            {
                mode: 'ajax',
                json: {}
            }
        );

        request.then(function(ret) {
            let arDataObjects = [];

            for (let i in ret.data) {
                arDataObjects.push(ret.data[ i ].name);
            }

            $('.js-find-object').keyup(function () {
                var value = this.value.toLowerCase().trim();

                $('#object-list').empty();

                arDataObjects.forEach(function (item) {
                    if (item.toLowerCase().trim().indexOf(value) != -1) {
                        $('#object-list').append(
                            `<option value="${item}">`
                        );
                    }
                });
            });
        }, function (ret) {
            // BX.UI.Dialogs.MessageBox.alert(ret.errors[0].message);
            alert(ret.errors[0].message);
        });

        let $this = $(this),
            coords = [36.24342222, 53.91111424];

        modalEdit.find('[name=ID]').val('');
        modalEdit.find('[name=NAME]').val('');
        modalEdit.find('[name=ADDRESS]').val('');
        modalEdit.find('[name=FIAS]').val('');
        modalEdit.find('.js-modal-btn').val(modalEdit.find('.js-modal-btn').data('add'));
        renderOrdersTable([]);
        modalEdit.modal();
        modalEdit.on('hide.bs.modal', function (e) {
            $('#map-container').hide();
        });

        $('body').on('click', '.js-open-map', function(e) {
            $('#map-container').show();
            myMap.setCenter([37.617674, 54.193109]);
            myMap.setZoom(8);
            json = [];
        });

        $('.js-find-address').suggestions({
            token: '697e4e53b055f8cbb596f79570f2cbfd118a4a68',
            type: 'ADDRESS',
            onSelect: function(suggestion) {
                $(this).val(suggestion.value);
                $('.js-fias-simple').val(JSON.stringify(suggestion));
            },
            onSelectNothing: function(suggestion) {
                $(this).val('');
                $('.js-fias-simple').val('');
            }
        });

        e.preventDefault();
        return false;
    });

});
</script>