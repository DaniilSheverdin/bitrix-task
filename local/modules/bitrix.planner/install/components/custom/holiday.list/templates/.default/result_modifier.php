<?php
use Bitrix\Main\Page\Asset;
$pathForScripts = explode($_SERVER["DOCUMENT_ROOT"] ,__DIR__)[1];
$pathForScripts = "/local/components/citto/holiday.list/templates/.default";

Asset::getInstance()->addCss($pathForScripts . "/css/bootstrap.css");
Asset::getInstance()->addCss($pathForScripts . "/css/datatables.css");
Asset::getInstance()->addCss($pathForScripts . "/css/jquery-ui.css");

Asset::getInstance()->addJs($pathForScripts . "/js/jquery-ui.js");
Asset::getInstance()->addJs($pathForScripts . "/js/modal.js");
Asset::getInstance()->addJs($pathForScripts . "/js/datatables.js");
Asset::getInstance()->addJs($pathForScripts . "/js/greensock.js");

Asset::getInstance()->addJs('/local/activities/custom/docsignactivity/js/es6-promise.min.js');
Asset::getInstance()->addJs('/local/activities/custom/docsignactivity/js/ie_eventlistner_polyfill.js');
Asset::getInstance()->addJs($pathForScripts . "/js/ruscripto.js");

$arResult['TYPES'] = ['VACATION' => $arResult['TYPES']['VACATION']];

$arResult['monthsList'] = array(
    "1" => "Январь", "2" => "Февраль", "3" => "Март",
    "4" => "Апрель", "5" => "Май", "6" => "Июнь",
    "7" => "Июль", "8" => "Август", "9" => "Сентябрь",
    "10" => "Октябрь", "11" => "Ноябрь", "12" => "Декабрь");

$arResult['daysList'] = [];

$obUserField = new CUserTypeEntity();
$aUserFields    = array(
    'ENTITY_ID'         => 'IBLOCK_'.COption::GetOptionInt('intranet', 'iblock_structure').'_SECTION',
    'FIELD_NAME'        => 'UF_HIDEDEP',
    'USER_TYPE_ID'      => 'boolean',
    'SORT'              => 500,
    'MANDATORY'         => 'N',
    'SHOW_FILTER'       => 'N',
    'SHOW_IN_LIST'      => '',
    'EDIT_IN_LIST'      => '',
    'IS_SEARCHABLE'     => 'N',
    'EDIT_FORM_LABEL'   => array(
        'ru'    => 'Не показывать в графике отпусков',
        'en'    => '',
    ),
    'LIST_COLUMN_LABEL' => array(
        'ru'    => 'Не показывать в графике отпусков',
        'en'    => '',
    ),
);
$iUserFieldId = $obUserField->Add($aUserFields);

$arFields = Array(
    "ENTITY_ID" => 'USER',
    "FIELD_NAME" => "UF_THIS_HEADS",
    "USER_TYPE_ID" => "string",
    "EDIT_FORM_LABEL" => Array("ru" => "Руководители", "en" => "UF_THIS_HEADS")
);
$obUserField->Add($arFields);

$arFields = Array(
    "ENTITY_ID" => 'USER',
    "FIELD_NAME" => "UF_SUBORDINATE",
    "USER_TYPE_ID" => "string",
    "EDIT_FORM_LABEL" => Array("ru" => "Подчинённые", "en" => "UF_SUBORDINATE")
);
$obUserField->Add($arFields);

if(!CIBlockProperty::GetByID("UF_WHO_APPROVE", COption::GetOptionInt('intranet', 'iblock_absence'))->GetNext()) {
    $arFields = Array(
        "NAME" => "Лица, утвердившие отпуск",
        "ACTIVE" => "Y",
        "SORT" => "600",
        "CODE" => "UF_WHO_APPROVE",
        "PROPERTY_TYPE" => "S",
        "IBLOCK_ID" => COption::GetOptionInt('intranet', 'iblock_absence'),
    );
    $ibp = new CIBlockProperty;
    $ibp->Add($arFields);
}
