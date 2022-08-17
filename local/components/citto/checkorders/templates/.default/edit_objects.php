<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$GLOBALS['APPLICATION']->SetAdditionalCss($this->GetFolder() .'/suggestions.min.css');
$GLOBALS['APPLICATION']->AddHeadScript($this->GetFolder() . '/jquery.suggestions.js');

$arObjects = [];
if (!empty($arResult['EDIT_DATA'])) {
    $arObjects = $arResult['EDIT_DATA']['PROPERTY_OBJECT_VALUE'];
} elseif (!empty($_REQUEST['PROP']['OBJECT'])) {
    $arObjects = $_REQUEST['PROP']['OBJECT'];
}

?>
<div class="my-2">
    <script type="text/javascript" src="/bitrix/templates/.default/bootstrap.min.js"></script>
    <b>Объект поручения:</b> <a href="#" class="ui-btn ui-btn-xs ui-btn-primary ui-btn-icon-add js-add-object" title="Добавить объект"></a>
</div>

<div class="row">
    <div class="col-12 js-objects">
        <?
        foreach ($arObjects as $value) {
            echo $this->__component->renderObject($value, true);
        }
        ?>
    </div>
</div>

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
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item">
                        <a
                            class="nav-link active"
                            id="create-tab"
                            data-toggle="tab"
                            href="#create"
                            role="tab"
                            aria-controls="create"
                            aria-selected="false">Добавить новый</a>
                    </li>
                    <li class="nav-item">
                        <a
                            class="nav-link"
                            id="table-tab"
                            data-toggle="tab"
                            href="#table"
                            role="tab"
                            aria-controls="table"
                            aria-selected="true">Существующие</a>
                    </li>
                </ul>
                <div class="tab-content mt-2" id="myTabContent">
                    <div class="tab-pane fade show active" id="create" role="tabpanel" aria-labelledby="create-tab">
                        <form class="js-object-add-simple">
                            <div class="row">
                                <div class="col-12">
                                    <b>Название объекта</b>
                                    <br/>
                                    <input
                                        class="form-control"
                                        name="NAME"
                                        placeholder="Название объекта" />
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
                            <div class="row mt-4">
                                <div class="col-12">
                                    <input
                                        type="hidden"
                                        class="js-fias-simple"
                                        name="FIAS" />
                                    <input
                                        class="ui-btn ui-btn-primary"
                                        type="submit"
                                        value="Добавить" />
                                </div>
                            </div>
                        </form>


                        <script type="text/javascript" src="https://api-maps.yandex.ru/2.1/?lang=ru_RU&coordorder=longlat&apikey=04958d7f-754d-417b-b602-904f940bf94a"></script>
                        <script type="text/javascript" src="https://yastatic.net/s3/mapsapi-jslibs/area/0.0.1/util.calculateArea.min.js"></script>
                        <script type="text/javascript" src="https://yastatic.net/s3/mapsapi-jslibs/polylabeler/1.0.2/polylabel.min.js"></script>
                        <script>
                        ymaps.ready(['polylabel.create']).then(initObjectsMap);
                        var myMap, myPlacemark;
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
                                    myPlacemark = createPlacemark(coords);
                                    myMap.geoObjects.add(myPlacemark);
                                    myPlacemark.events.add('dragend', function () {
                                        getAddress(myPlacemark.geometry.getCoordinates());
                                    });
                                }
                                getAddress(coords);
                            });

                            // Создание метки.
                            function createPlacemark(coords) {
                                return new ymaps.Placemark(coords, {
                                    iconCaption: 'поиск...'
                                }, {
                                    preset: 'islands#blueCircleDotIcon',
                                    draggable: true
                                });
                            }

                            // Определяем адрес по координатам (обратное геокодирование).
                            function getAddress(coords) {
                                myPlacemark.properties.set('iconCaption', 'поиск...');
                                ymaps.geocode(coords).then(function (res) {
                                    var firstGeoObject = res.geoObjects.get(0);
                                    $('.js-find-address').val(firstGeoObject.getAddressLine());
                                    let json = {
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
                                    $('.js-fias-simple').val(JSON.stringify(json));
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
                        }
                        </script>
                    </div>
                    <div class="tab-pane fade" id="table" role="tabpanel" aria-labelledby="table-tab">
                        <input class="form-control js-find-table" placeholder="Поиск" />
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Название</th>
                                    <th colspan="2">Адрес</th>
                                </tr>
                            </thead>
                            <tbody class="js-object-table"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?
$this->EndViewTarget();
