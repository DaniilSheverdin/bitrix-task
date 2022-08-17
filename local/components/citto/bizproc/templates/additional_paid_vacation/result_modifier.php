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
$arResult['IBLOCK_CODE'] = "additional_paid_vacation";


$APPLICATION->SetTitle("Ежегодный дополнительный оплачиваемый отпуск (ЧАЭС)");

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

$arUser = $arUserFields($USER->GetID());
global $mb_lcfirst;

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

        $arProperties[$arEnum['PROPERTY_ID']]['ENUMS'][$arEnum['ID']] = $arEnum;
        $arProperties[$arEnum['PROPERTY_CODE']][$arEnum['ID']] = $arEnum['VALUE'];

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

        $arOIV = $arUserFields($arRequest['LEAD_OIV']);
        $sFIO = empty($arUser['FIO']) ? $arUser['LAST_NAME'] . ' ' . $arUser['NAME'] . ' ' . $arUser['MIDDLE_NAME'] : $arUser['FIO'];
        $sFIO = $morphFunct($sFIO, 'Р');
        $sTemplate = file_get_contents( __DIR__.'/statement_template.html');

        $sContent = str_replace(
            [
                '#EMPLOYEE_FIO_ROD#',
                '#EMPLOYEE_POSITION_ROD#',
                '#REASON#',
                '#DAYS#',
                '#YEAR#',
                '#DATE#',
                '#DATE_SIGN_USER#',
                '#OIV_FIO#',
                '#OIV_POSITION#'
            ],
            [
                $sFIO,
                $mb_lcfirst($morphFunct($arRequest['POSITION'], 'Р')),
                $arResult['FIELDS']['REASON'][$arRequest['REASON']],
                $arResult['FIELDS']['NUM_DAYS'][$arRequest['NUM_DAYS']],
                $arRequest['YEAR'],
                date_format(date_create($arRequest['DATE_FROM']), 'd.m.Y'),
                date("d.m.Y"),
                empty($arOIV['FIO']) ? $arOIV['LAST_NAME'] . ' ' . $arOIV['NAME'] . ' ' . $arOIV['MIDDLE_NAME'] : $arOIV['FIO'],
                empty($arOIV['DOLZHNOST']) ? $arOIV['WORK_POSITION'] : $arOIV['DOLZHNOST']
            ],
            $sTemplate
        );

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
        if (!$boolDocumentid) {
            throw new Exception($objEl->LAST_ERROR);
        }

        $msg = '';
        $docGenId = 0;
        if (!$GLOBALS['setElementPDFValue']($boolDocumentid, 'STATEMENT', $sContent, "Заявление на доп. оплачиваемый отпуск (ЧАЭС) " . $sFIO, $msg, $docGenId)) {
            CIBlockElement::Delete($boolDocumentid);
            throw new Exception("Не удалось создать файл");
        }

        $arResult['ajaxid'] = $strIDajax;
        $arResult['file_id'] = $docGenId;
        $arResult['code'] = "ReadySign";
        $arResult['message'] = 'Ready to sign';
        $arResult['sessid'] = bitrix_sessid();
        $arResult['documentid'] = $boolDocumentid;

    } catch (Exception $exc) {
        $arResult['message'] = $exc->getMessage();
    }
} elseif ($arRequest[$arResult['IBLOCK_CODE']] && $arRequest[$arResult['IBLOCK_CODE']] == 'signed') {
    try {
        $boolDocumentid = $arRequest['documentid'];

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
    $arResult['FIO'] = empty($arUser['FIO']) ? $arUser['LAST_NAME'] . ' ' . $arUser['NAME'] . ' ' . $arUser['MIDDLE_NAME'] : $arUser['FIO'];
    $arResult['POSITION'] = empty($arUser['DOLZHNOST']) ? $arUser['WORK_POSITION'] : $arUser['DOLZHNOST'];
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
