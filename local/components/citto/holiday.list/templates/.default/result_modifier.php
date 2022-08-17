<?php
use Bitrix\Main\Page\Asset;

Asset::getInstance()->addCss("/local/css/bootstrap-plugin/bootstrap-select.min.css");
//Asset::getInstance()->addCss($arResult['TEMPLATE'] . "/css/bootstrap.css");
Asset::getInstance()->addCss('/bitrix/templates/.default/bootstrap.min.css');
Asset::getInstance()->addCss($arResult['TEMPLATE'] . "/css/datatables.css");
Asset::getInstance()->addCss($arResult['TEMPLATE'] . "/css/jquery-ui.css");

Asset::getInstance()->addJs('/bitrix/templates/.default/jquery.min.js');
Asset::getInstance()->addJs($arResult['TEMPLATE'] . "/js/jquery-ui.js");
Asset::getInstance()->addJs($arResult['TEMPLATE'] . "/js/modal.js");
Asset::getInstance()->addJs($arResult['TEMPLATE'] . "/js/datatables.js");
Asset::getInstance()->addJs($arResult['TEMPLATE'] . "/js/greensock.js");

Asset::getInstance()->addJs("/local/js/bootstrap-plugin/popper.min.js");
Asset::getInstance()->addJs('/bitrix/templates/.default/bootstrap.min.js');
Asset::getInstance()->addJs('/local/activities/custom/docsignactivity/js/es6-promise.min.js');
Asset::getInstance()->addJs('/local/activities/custom/docsignactivity/js/ie_eventlistner_polyfill.js');
Asset::getInstance()->addJs("/local/activities/custom/docsignactivity/js/cadesplugin_api.js");
Asset::getInstance()->addJs("/local/activities/custom/docsignactivity/js/plugin.js");
Asset::getInstance()->addJs($arResult['TEMPLATE'] . "/js/docsignactivity.js");
Asset::getInstance()->addJs("/local/js/bootstrap-plugin/bootstrap-select.min.js");

$arResult['TYPES'] = [
    'VACATION' => $arResult['TYPES']['VACATION'],
    'VACATION_ADD' => $arResult['TYPES']['VACATION_ADD']
];

$arResult['monthsList'] = array(
    "1" => "Январь", "2" => "Февраль", "3" => "Март",
    "4" => "Апрель", "5" => "Май", "6" => "Июнь",
    "7" => "Июль", "8" => "Август", "9" => "Сентябрь",
    "10" => "Октябрь", "11" => "Ноябрь", "12" => "Декабрь");

$arResult['daysList'] = [];
