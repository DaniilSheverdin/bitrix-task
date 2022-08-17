<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UserTable, CIntranetUtils;
use JetBrains\PhpStorm\ExpectedValues;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

global $APPLICATION, $USER, $userFields, $morphFunct;

$arUserFields = $userFields;
$arRequest = $arResult['REQUEST'];
Loader::includeModule("iblock");
Loader::includeModule('citto.filesigner');
CJSCore::Init(["date"]);

$APPLICATION->SetTitle("Заявка на рекламу ГосУслуги71");

Asset::getInstance()->addJs('/bitrix/templates/.default/jquery.min.js');
Asset::getInstance()->addJs("/local/js/bootstrap-plugin/popper.min.js");
Asset::getInstance()->addJs('/bitrix/templates/.default/bootstrap.min.js');
Asset::getInstance()->addJs('/' . basename(__DIR__) . '/index.js');

Asset::getInstance()->addJs('/local/activities/custom/docsignactivity/js/es6-promise.min.js');
Asset::getInstance()->addJs('/local/activities/custom/docsignactivity/js/ie_eventlistner_polyfill.js');
Asset::getInstance()->addJs("/local/activities/custom/docsignactivity/js/cadesplugin_api.js");
Asset::getInstance()->addJs("/local/activities/custom/docsignactivity/js/plugin.js");
Asset::getInstance()->addJs("/local/components/citto/bizproc/js/docsignactivity.js");
Asset::getInstance()->addJs('/local/js/bootstrap-plugin/bootstrap-select.min.js');

$APPLICATION->SetAdditionalCSS('/local/css/bootstrap-plugin/bootstrap-select.min.css');
$APPLICATION->SetAdditionalCSS('/bitrix/templates/.default/bootstrap.min.css');
$APPLICATION->SetAdditionalCSS('/' . basename(__DIR__) . '/main.css');

function getProperties($iBlockID)
{
    $obListWorkflow = new CList($iBlockID);
    $arListWorkflow = $obListWorkflow->getFields();

    $arProperties = [];
    $obProperties = CIBlockProperty::GetList(array("sort" => "asc", "name" => "asc"), array("ACTIVE" => "Y", "IBLOCK_ID" => $iBlockID));
    while ($arField = $obProperties->GetNext()) {
        if ($arField['USER_TYPE']) {
            $sType = mb_strtoupper($arField['USER_TYPE']);
        } elseif ($arField['PROPERTY_TYPE'] == 'L') {
            $sType = 'LIST';
        } elseif ($arField['PROPERTY_TYPE'] == 'S') {
            $sType = 'STRING';
        } elseif ($arField['PROPERTY_TYPE'] == 'F') {
            $sType = 'FILE';
        } elseif ($arField['PROPERTY_TYPE'] == 'N') {
            $sType = 'NUMBER';
        }

        if ($arWFSettins = $arListWorkflow["PROPERTY_" . $arField['ID']]['SETTINGS']) {
            $arField['SHOW'] = $arWFSettins['SHOW_ADD_FORM'];
        }

        if ($sType) {
            $arField['TYPE'] = $sType;
            $arProperties[$arField['ID']] = $arField;
        }
    }

    $obEnums = CIBlockPropertyEnum::GetList(array("DEF" => "DESC", "SORT" => "ASC"), array("IBLOCK_ID" => $iBlockID));
    while ($arEnum = $obEnums->GetNext()) {
        if ($arProperties[$arEnum['PROPERTY_ID']]) {
            $arProperties[$arEnum['PROPERTY_ID']]['ENUMS'][$arEnum['ID']] = $arEnum;
        }
    }

    return $arProperties;
}

$arResult['FIELDS'] = getProperties($arParams['ID_BIZPROC']);

if ($arRequest['advert_gu71'] && $arRequest['advert_gu71'] == 'add') {
    try {
        Loader::includeModule("workflow");
        Loader::includeModule("bizproc");

        $srtIDajax = $arRequest['bxajaxid'];
        $intMODULEID = 'bizproc';

        if (!check_bitrix_sessid()) {
            throw new Exception('Проблема с сессией, обновите страницу');
        }

        $sName = $arRequest['NAZVANIE_USLUGI'];
        $sDesc = $arRequest['OPISANIE_USLUGI'];
        $sLink = $arRequest['SSYLKA_NA_USLUGU'];
        $sVid = $arRequest['KAKAYA_ETO_USLUGA'];
        $sDate = $arRequest['PREDPOLAGAEMAYA_DATA'];

        if (empty($sName)) {
            throw new Exception('Введите название услуги');
        }
        if (empty($sDesc)) {
            throw new Exception('Введите описание услуги');
        }
        if (empty($sLink)) {
            throw new Exception('Введите ссылку на услугу');
        }
        if (empty($sVid)) {
            throw new Exception('Выберите вид услуги');
        }
        if (empty($sDate)) {
            throw new Exception('Введите дату выполнения');
        }

        $arLoadProductArray =
            [
                'CREATED_BY' => $USER->GetId(),
                'MODIFIED_BY' => $USER->GetId(),
                'IBLOCK_SECTION_ID' => false,
                'IBLOCK_ID' => $arParams['ID_BIZPROC'],
                'PROPERTY_VALUES' => [
                    'NAZVANIE_USLUGI' => $sName,
                    'OPISANIE_USLUGI' => $sDesc,
                    'SSYLKA_NA_USLUGU' => $sLink,
                    'KAKAYA_ETO_USLUGA' => $sVid,
                    'PREDPOLAGAEMAYA_DATA' => date('d.m.Y', strtotime($sDate)),

                ],
                'NAME' => $APPLICATION->GetTitle(),
                'ACTIVE' => "Y",
                'PREVIEW_TEXT' => '',
            ];

        $objEl = new CIBlockElement();
        $boolDocumentid = $objEl->Add($arLoadProductArray);
        if (!$boolDocumentid) {
            throw new Exception($objEl->LAST_ERROR);
        }

        $arErrorsTmp = [];

        $strWfId = CBPDocument::StartWorkflow(
            $arParams['ID_TEMPLEATE'],
            ["lists", "BizprocDocument", $boolDocumentid],
            ['TargetUser' => "user_" . $USER->GetID()],
            $arErrorsTmp
        );

        if (count($arErrorsTmp) > 0) {
            throw new Exception(
                array_reduce(
                    $arErrorsTmp,
                    function ($strCarry, $arItem) {
                        return $strCarry . "." . $arItem['message'];
                    },
                    ''
                )
            );
        }


        $arResult['code'] = "OK";
        $arResult['message'] = "<p>Бизнес-процесс запущен!</p>";
    } catch (Exception $exc) {
        $arResult['message'] = $exc->getMessage();
    }
}

$objVUslug = CIBlockPropertyEnum::GetList(
    [
        "SORT" => "ASC",
        "VALUE" => "ASC"
    ],
    [
        "IBLOCK_ID" => $arParams['ID_BIZPROC'],
        "CODE" => "KAKAYA_ETO_USLUGA"
    ]
);

while ($arVal = $objVUslug->Fetch()){
    $arResult['KAKAYA_ETO_USLUGA'][$arVal['ID']] = $arVal;
}

$arResult["__TEMPLATE_FOLDER"] = $this->__folder;
$this->__component->arResult = $arResult;
