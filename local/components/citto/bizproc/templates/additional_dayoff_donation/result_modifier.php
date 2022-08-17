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
$arResult['IBLOCK_CODE'] = "additional_dayoff_donation";


$APPLICATION->SetTitle("Дополнительный день отдыха (ст. 186 ТКРФ)");

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
            $arProperties[$arEnum['PROPERTY_CODE']][$arEnum['ID']]=$arEnum['VALUE'];
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

        $OIV=$arUserFields($arRequest['OIV']);
        $strFIO = empty($arUser['FIO']) ? $arUser['LAST_NAME'] . ' ' . $arUser['NAME'] . ' ' . $arUser['MIDDLE_NAME'] : $arUser['FIO'];
        $strFIO = $morphFunct($strFIO, 'Р');
        $strDOLZHNOST = empty($arUser['DOLZHNOST']) ? $arUser['WORK_POSITION'] : $arUser['DOLZHNOST'];

        $strSHABLON = "<!DOCTYPE html><html><head><title>Дополнительный день отдыха</title><meta charset='UTF-8'></head>
<body style='font-size:14px; width: 500px;'><table>
<tr><td style='width: 250px;'></td>
<td style='width: 250px; text-align: center;'>Заместителю Губернатора<br>Тульской области – руководителю<br>аппарата правительства Тульской<br>
области – начальнику главного<br>управления государственной<br>службы и кадров аппарата<br>
правительства<br>Тульской области<br><br>Г.И. Якушкиной<br><br><span>#EMPLOYEE_FIO_ROD#,</span><br><span>#EMPLOYEE_POSITION_ROD#</span></td></tr></table>
<div style='text-align: center;'>заявление.</div><br><br><div style='text-align: justify; text-indent: 30px'>
<span>Прошу предоставить #DAYS# отдыха c #DATE# года c сохранением #PAYMENT# в соответствии со статьей 186 Трудового кодекса Российской Федерации.</span></div>
<div style='text-align: justify; text-indent: 30px'><span>Приложение: справка о донации (форма № 402у) от #SPRAVKA_DATE# № #SPRAVKA_CODE# на #SPRAVKA_PAGES#.</span></div>
<table><tr><td style='width: 250px'>#DATE_SIGN_USER#</td><td style='width: 250px; text-align: right;'>#SIGN_USER#</td></tr></table>
<table><tr><td style='text-align: center;'>СОГЛАСОВАНО</td><td></td></tr><tr><td style='width: 250px; text-align: center;'>
<b>#OIV_POSITION#</b></td><td style='width: 250px; text-align: right;'><b>#OIV_FIO#</b></td></tr><tr><td style='width: 250px; text-align: center;'>#OIV_SIGN#</td><td></td></tr></table></body></html>";

        $strContent = str_replace(
            [
                '#EMPLOYEE_FIO_ROD#',
                '#EMPLOYEE_POSITION_ROD#',
                '#DAYS#',
                '#DATE#',
                '#PAYMENT#',
                '#SPRAVKA_DATE#',
                '#SPRAVKA_CODE#',
                '#SPRAVKA_PAGES#',
                '#DATE_SIGN_USER#',
                '#OIV_FIO#',
                '#OIV_POSITION#'
            ],
            [
                $strFIO,
                $mb_lcfirst($strDOLZHNOST),
                $arResult['FIELDS']['DAYS'][$arRequest['DAYS']]==1?"1 дополнительный выходной день":"2 дополнительных выходных дня",
                date_format(date_create($arRequest['DATE']), 'd.m.Y'),
                $arResult['FIELDS']['PAYMENT'][$arRequest['PAYMENT']]=="Среднего заработка (для не госслужащих)"?
                "среднего заработка":"денежного содержания",
                date_format(date_create($arRequest['SPRAVKA_DATE']), 'd.m.Y'),
                $arRequest['SPRAVKA_CODE'],
                $arRequest['SPRAVKA_PAGES']%10==1? $arRequest['SPRAVKA_PAGES']." листе":$arRequest['SPRAVKA_PAGES']." листах",
                date("d.m.Y"),
                empty($OIV['FIO']) ? $OIV['LAST_NAME'] . ' ' . $OIV['NAME'] . ' ' . $OIV['MIDDLE_NAME'] : $OIV['FIO'],
                empty($OIV['DOLZHNOST']) ? $OIV['WORK_POSITION'] : $OIV['DOLZHNOST']
            ],
            $strSHABLON
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
        if (!$GLOBALS['setElementPDFValue']($boolDocumentid, 'ZAYAVLENIE', $strContent, "Заявление на дополнительный день отдыха " . $strFIO, $msg, $docGenId)) {
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
}
elseif ($arRequest[$arResult['IBLOCK_CODE']] && $arRequest[$arResult['IBLOCK_CODE']] == 'signed') {
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
}
else
 {
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
