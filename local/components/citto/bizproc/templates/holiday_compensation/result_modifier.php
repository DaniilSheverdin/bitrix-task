<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Citto\Filesigner\ShablonyTable;

global $APPLICATION, $USER, $userFields, $morphFunct;

$arUserFields = $userFields;
$arRequest = $arResult['REQUEST'];
Loader::includeModule("iblock");
Loader::includeModule('citto.filesigner');
require_once __DIR__ . '/constants/index.php';
CJSCore::Init(["date"]);

$APPLICATION->SetTitle("Компенсация отпуска");

Asset::getInstance()->addJs('/bitrix/templates/.default/jquery.min.js');
Asset::getInstance()->addJs("/local/js/bootstrap-plugin/popper.min.js");
Asset::getInstance()->addJs('/bitrix/templates/.default/bootstrap.min.js');
Asset::getInstance()->addJs('/' . basename(__DIR__) . '/index.js');

Asset::getInstance()->addJs('/local/activities/custom/docsignactivity/js/es6-promise.min.js');
Asset::getInstance()->addJs('/local/activities/custom/docsignactivity/js/ie_eventlistner_polyfill.js');
Asset::getInstance()->addJs("/local/activities/custom/docsignactivity/js/cadesplugin_api.js");
Asset::getInstance()->addJs("/local/activities/custom/docsignactivity/js/plugin.js");
Asset::getInstance()->addJs("/local/components/citto/bizproc/js/docsignactivity.js");

$APPLICATION->SetAdditionalCSS('/bitrix/templates/.default/bootstrap.min.css');
$APPLICATION->SetAdditionalCSS('/' . basename(__DIR__) . '/main.css');

$arUser = $arUserFields($USER->GetID());

if ($arRequest['holiday-compensation'] && $arRequest['holiday-compensation'] == 'add') {
    try {
        Loader::includeModule("workflow");
        Loader::includeModule("bizproc");

        if (!check_bitrix_sessid()) {
            throw new Exception('Проблема с сессией, обновите страницу');
        }

        $intKolichestvoDneyKompens = $arRequest['CH_KOLICHESTVO_DNEY_KOMPENSATSY'];

        if (empty($intKolichestvoDneyKompens)) {
            throw new Exception('Укажите количество дней для компенсации отпуска');
        }

        $strIDajax = $arRequest['bxajaxid'];
        $intMODULEID = 'bizproc';

        $strDeparnmentUser = mb_strtolower($arUser['UF_WORK_POSITION_ROD']);
        $strFIO = empty($arUser['FIO']) ? $arUser['LAST_NAME'] . ' ' . $arUser['NAME'] . ' ' . $arUser['MIDDLE_NAME'] : $arUser['FIO'];
        $strFIO = $morphFunct($strFIO, 'Р');

        $arPrpList = CIBlock::GetProperties(IBLOCK_ID_COMPENS_HOLIDAY, [], ['CODE' => 'CH_ZAYAVLENIE_FILE'])->Fetch();

        $strSHABLON = ShablonyTable::getScalar(
            [
                'filter' => ['=CODE' => IBLOCK_ID_COMPENS_HOLIDAY . "_" . $arPrpList['ID']],
                'limit' => 1,
                'select' => ['SHABLON']
            ]
        );

        $strContent = str_replace(
            [
                '#FIO#',
                '#DOLZHNOST_I_PODRAZDELENIE#',
                '#KOLICHESTVO_DNEY_KOMPENSATSY#'
            ],
            [
                $strFIO,
                $strDeparnmentUser,
                $intKolichestvoDneyKompens
            ],
            $strSHABLON
        );

        $arProps = [
            'CH_KOLICHESTVO_DNEY_KOMPENSATSY' => $intKolichestvoDneyKompens,
            'CH_ZAYAVITEL' => $arUser['ID'],
            'CH_DOLZHNOST_I_PODRAZDELENIE' => $strDeparnmentUser
        ];

        $objEl = new CIBlockElement();
        $arLoadProductArray = [
            'MODIFIED_BY' => $USER->GetId(),
            'IBLOCK_SECTION_ID' => false,
            'IBLOCK_ID' => IBLOCK_ID_COMPENS_HOLIDAY,
            'PROPERTY_VALUES' => $arProps,
            'NAME' => $APPLICATION->GetTitle(),
            'ACTIVE' => "Y",
            'PREVIEW_TEXT' => '',
        ];

        $boolDocumentid = $objEl->Add($arLoadProductArray);
        if (!$boolDocumentid) {
            throw new Exception($objEl->LAST_ERROR);
        }

        $msg = '';
        $docGenId = 0;
        if (!$GLOBALS['setElementPDFValue']($boolDocumentid, 'CH_ZAYAVLENIE_FILE', $strContent, "Заявка на компенсацию отпуска для " . $strFIO, $msg, $docGenId)) {
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
} elseif ($arRequest['holiday-compensation'] && $arRequest['holiday-compensation'] == 'signed') {
    try {
        $boolDocumentid = $arRequest['documentid'];

        $arErrorsTmp = [];

        $strWfId = CBPDocument::StartWorkflow(
            BP_TEMPLATE_ID,
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
    $arResult['CH_USER'] = $arUser['ID'];

    $intDEPARTID = array_shift($arUser['UF_DEPARTMENT']);
}

$arResult["__TEMPLATE_FOLDER"] = $this->__folder;
$this->__component->arResult = $arResult;
