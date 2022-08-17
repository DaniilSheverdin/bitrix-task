<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;

global $APPLICATION, $USER, $userFields;

$arUserFields = $userFields;
$arRequest = $arResult['REQUEST'];
Loader::includeModule("iblock");
CJSCore::Init(["date"]);

$APPLICATION->SetTitle("Заказ копий НПА");

Asset::getInstance()->addJs('/bitrix/templates/.default/jquery.min.js');
Asset::getInstance()->addJs("/local/js/bootstrap-plugin/popper.min.js");
Asset::getInstance()->addJs('/bitrix/templates/.default/bootstrap.min.js');
Asset::getInstance()->addJs('/' . basename(__DIR__) . '/index.js');
Asset::getInstance()->addJs('/local/js/bootstrap-plugin/bootstrap-select.min.js');

$APPLICATION->SetAdditionalCSS('/bitrix/templates/.default/bootstrap.min.css');
$APPLICATION->SetAdditionalCSS('/' . basename(__DIR__) . '/main.css');
$APPLICATION->SetAdditionalCSS('/local/css/bootstrap-plugin/bootstrap-select.min.css');

$arUser = $arUserFields($USER->GetID());

if (isset($arRequest['zakaz-copii-npa'])) {
    try {
        Loader::includeModule("workflow");
        Loader::includeModule("bizproc");

        if (!check_bitrix_sessid()) {
            throw new Exception('Проблема с сессией, обновите страницу');
        }

        $sCopyText = $arRequest['KOPIYA_PRAVOVOGO_AKTA'];
        $sTarget = $arRequest['TSEL_POLUCHENIYA'];
        $sEjecutor = $arRequest['ISPOLNITEL'];
        $sBoss = $arRequest['RUKOVODITEL_OIV_ORGANIZATSII'];
        $sTerm = $arRequest['SROK_IZGOTOVLENIYA'];

        if (empty($sCopyText)) {
            throw new Exception('Укажите "Копия правового акта (вид, дата, номер, заголовок правового акта, количество экземпляров)"');
        }
        if (empty($sTarget)) {
            throw new Exception('Укажите "Цель получения заверенной копии"');
        }
        if (empty($sEjecutor)) {
            throw new Exception('Укажите "Исполнитель"');
        }
        if (empty($sBoss)) {
            throw new Exception('Укажите "Руководитель ОИВ/Организации"');
        }
        if (empty($sTerm)) {
            throw new Exception('Укажите "Срок изготовления"');
        }

        $sIDajax = $arRequest['bxajaxid'];

        $iKolichestvo = 0;
        $iMODULEID = 'bizproc';

        $arProps = [
            'KOPIYA_PRAVOVOGO_AKTA' => $sCopyText,
            'TSEL_POLUCHENIYA' => $sTarget,
            'ISPOLNITEL' => $sEjecutor,
            'RUKOVODITEL_OIV_ORGANIZATSII' => $sBoss,
            'SROK_IZGOTOVLENIYA' => $sTerm,
        ];

        $obEl = new CIBlockElement();
        $arLoadProductArray = [
            'MODIFIED_BY' => $USER->GetId(),
            'IBLOCK_SECTION_ID' => false,
            'IBLOCK_ID' =>  $arParams['ID_BIZPROC'],
            'PROPERTY_VALUES' => $arProps,
            'NAME' => $APPLICATION->GetTitle(),
            'ACTIVE' => "Y",
            'PREVIEW_TEXT' => '',
        ];

        $bDocumentid = $obEl->Add($arLoadProductArray);
        if (!$bDocumentid) {
            throw new Exception($obEl->LAST_ERROR);
        }

        $arErrorsTmp = [];
        $sWfId = CBPDocument::StartWorkflow(
            $arParams['ID_TEMPLEATE'],
            ["lists", "BizprocDocument", $bDocumentid],
            ['TargetUser' => "user_" . $USER->GetID()],
            $arErrorsTmp
        );

        if (count($arErrorsTmp) > 0) {
            throw new Exception(
                array_reduce(
                    $arErrorsTmp,
                    function ($sCarry, $arItem) {
                        return $sCarry . "." . $arItem['message'];
                    },
                    ''
                )
            );
        }

        $arResult['code'] = "OK";
        $arResult['message'] = "Бизнесс-процесс \"{$APPLICATION->GetTitle()}\" запущен.";
        $arResult['ajaxid'] = $sIDajax;
    } catch (Exception $exc) {
        $arResult['message'] = $exc->getMessage();
    }
} else {
    $obListUsersAll = (new CUser())->GetList($by = "LAST_NAME", $order = "ASC", ["ACTIVE" => 'Y', "!LAST_NAME" => null, "!NAME" => null]);
    $arResult['USERS'] = [];
    while ($arUser = $obListUsersAll->Fetch()) {
        if (!empty($arUser['LAST_NAME']) && !empty($arUser['NAME'])) {
            $arResult['USERS'][] = $arUser;
        }
    }

    $arResult['SROK_IZGOTOVLENIYA'] = [];
    $arTerms = CIBlockProperty::GetPropertyEnum('SROK_IZGOTOVLENIYA', ['value' => "ASC"], ['IBLOCK_ID' => $arParams['ID_BIZPROC']]);
    while ($arTerm = $arTerms->GetNext()) {
        $arResult['SROK_IZGOTOVLENIYA'][$arTerm['ID']] = [
            'ID' => $arTerm['ID'],
            'NAME' => $arTerm['VALUE']
        ];
    }
}

$arResult["__TEMPLATE_FOLDER"] = $this->__folder;
$this->__component->arResult = $arResult;
