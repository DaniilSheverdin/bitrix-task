<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;

global $APPLICATION, $USER, $userFields, $morphFunct;

$arUserFields = $userFields;
$arRequest = $arResult['REQUEST'];
Loader::includeModule("iblock");
Loader::includeModule('citto.filesigner');
CJSCore::Init(["date"]);

$APPLICATION->SetTitle("Перенос отпуска (ЦЗН)");

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

$arResult['PRICHINA'] = [];
$arResult['DATE_DEFAULT'] = ($_REQUEST['date_default']) ? date('Y-m-d', strtotime($_REQUEST['date_default'])) : date('Y-m-d');
$arResult['DAYS_DEFAULT'] = $_REQUEST['days_default'];
$arResult['UVEDOMLENIE'] = $_REQUEST['uved'] ?? '';

$arTerms = CIBlockProperty::GetPropertyEnum('PRICHINA', ['value' => "ASC"], ['IBLOCK_ID' => $arParams['ID_BIZPROC']]);
while ($arTerm = $arTerms->GetNext()) {
    $arResult['PRICHINA'][ $arTerm['ID'] ] = [
        'ID' => $arTerm['ID'],
        'NAME' => $arTerm['VALUE']
    ];
}

if ($arRequest['perenos_otpusk_czn'] && $arRequest['perenos_otpusk_czn'] == 'add') {
    try {
        Loader::includeModule("workflow");
        Loader::includeModule("bizproc");

        $strIDajax = $arRequest['bxajaxid'];
        $intMODULEID = 'bizproc';

        if (!check_bitrix_sessid()) {
            throw new Exception('Проблема с сессией, обновите страницу');
        }

        $sDate = $arRequest['OTPUSK__FROM'];
        $iCountDays = $arRequest['OTPUSK__DAYS'];
        $sPrichina = $arRequest['PRICHINA'];
        $sInayaPrichina = $arRequest['INAYA_PRICHINA'];
        $sMassiv = $arRequest['MASSIV_OTPUSKOV'];

        if (empty($sDate)) {
            throw new Exception('Укажите дату начала отпуска');
        }
        if (empty($iCountDays)) {
            throw new Exception('Укажите количество дней');
        }
        if (empty($sPrichina)) {
            throw new Exception('Укажите причину');
        }
        if ($arResult['PRICHINA'][$sPrichina]['NAME'] == 'Иная' && empty($sInayaPrichina)) {
            throw new Exception('Укажите иную причину');
        }
        if (empty($sMassiv)) {
            throw new Exception('Укажите дату и дни переноса');
        }

        $arLoadProductArray = [
            'MODIFIED_BY'       => $USER->GetId(),
            'CREATED_BY'        => $USER->GetId(),
            'IBLOCK_SECTION_ID' => false,
            'IBLOCK_ID'         => $arParams['ID_BIZPROC'],
            'PROPERTY_VALUES'   => [
                'OTPUSK__FROM'      => date('d.m.Y', strtotime($sDate)),
                'OTPUSK__DAYS'      => $iCountDays,
                'INAYA_PRICHINA'    => $sInayaPrichina,
                'PRICHINA'          => $sPrichina,
                'MASSIV_OTPUSKOV'   => $sMassiv,
                'UVEDOMLENIE'       => $arRequest['UVEDOMLENIE'],
                'UVEDOMLENIE_ID'    => $arRequest['UVEDOMLENIE'],
            ],
            'NAME'              => $APPLICATION->GetTitle(),
            'ACTIVE'            => "Y",
            'PREVIEW_TEXT'      => '',
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
