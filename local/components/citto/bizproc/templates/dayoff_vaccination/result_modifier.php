<?php

use Monolog\Logger;
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use CIBlockElement;
use Bitrix\Bizproc\WorkflowInstanceTable;
use Monolog\Handler\RotatingFileHandler;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

global $APPLICATION, $USER, $userFields, $morphFunct;

$arUserFields = $userFields;
$arRequest = $arResult['REQUEST'];
Loader::includeModule("iblock");
Loader::includeModule('citto.filesigner');
Loader::includeModule('workflow');
CJSCore::Init(["date"]);
$arResult['IBLOCK_CODE'] = "dayoff_vaccination";


$APPLICATION->SetTitle("Отгул при вакцинации");

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
    $obProperties = CIBlockProperty::GetList(array("sort" => "asc"), array("ACTIVE" => "Y", "IBLOCK_ID" => $iBlockID));
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

if ($arRequest[$arResult['IBLOCK_CODE']] && $arRequest[$arResult['IBLOCK_CODE']] == 'add') {
    try {
        Loader::includeModule("workflow");
        Loader::includeModule("bizproc");

        $strIDajax = $arRequest['bxajaxid'];
        $intMODULEID = 'bizproc';

        if (!check_bitrix_sessid()) {
            throw new Exception('Проблема с сессией, обновите страницу');
        }

        $arProps = [];

        foreach ($arResult['FIELDS'] as $arField) {
            $sCode = $arField['CODE'];
            if ($arField['IS_REQUIRED'] == 'Y') {
                if (empty($arRequest[$sCode]) && empty($_FILES[$sCode]['name'][0])) {
                    throw new Exception("Заполните поле: '{$arField['NAME']}'");
                }
            }

            $sValue = $arRequest[$sCode];

            if ($arField['TYPE'] == 'DATE' && $sValue) {
                $arProps[$sCode] = (new DateTime($sValue))->format('d.m.Y');
            } elseif ($arField['TYPE'] == 'FILE' && $_FILES[$sCode]['name'][0]) {
                $arFilesInfo = $_FILES[$sCode];
                $arProps[$sCode] = $_FILES[$sCode];
            } else {
                $arProps[$sCode] = $sValue;
            }
        }

        $arLoadProductArray = [
            'MODIFIED_BY' => $USER->GetId(),
            'IBLOCK_SECTION_ID' => false,
            'IBLOCK_ID' => $arParams['ID_BIZPROC'],
            'PROPERTY_VALUES' => $arProps,
            'NAME' => $APPLICATION->GetTitle(),
            'ACTIVE' => "Y",
            'PREVIEW_TEXT' => '',
        ];

        $objEl = new CIBlockElement();
        $boolDocumentid = $objEl->Add($arLoadProductArray);

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
} else {
    $oblistUsersAll = (new CUser())->GetList($by = "LAST_NAME", $order = "ASC", ["ACTIVE" => 'Y', "!LAST_NAME" => null, "!NAME" => null], ['SELECT' => ['UF_WORK_POSITION']]);
    while ($arRuc = $oblistUsersAll->Fetch()) {
        if (!empty($arRuc['LAST_NAME'])) {
            $sWorkPosition = mb_substr($arRuc['UF_WORK_POSITION'], 0, 60);
            $sUserInfo = "{$arRuc['LAST_NAME']} {$arRuc['NAME']} {$arRuc['SECOND_NAME']}";
            if (!empty($sWorkPosition)) {
                $sUserInfo = "$sUserInfo ($sWorkPosition...)";
            }
            $arRuc['USER_INFO'] = $sUserInfo;
            $arResult['USERS'][] = $arRuc;
        }
    }
}

$arResult["__TEMPLATE_FOLDER"] = $this->__folder;
$this->__component->arResult = $arResult;
