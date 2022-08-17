<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;

global $APPLICATION, $USER, $userFields;

$arUserFields = $userFields;
$arRequest = $arResult['REQUEST'];
Loader::includeModule("iblock");
CJSCore::Init(["date"]);

$APPLICATION->SetTitle("Внутреннее перемещение");

Asset::getInstance()->addJs('/bitrix/templates/.default/jquery.min.js');
Asset::getInstance()->addJs("/local/js/bootstrap-plugin/popper.min.js");
Asset::getInstance()->addJs('/bitrix/templates/.default/bootstrap.min.js');
Asset::getInstance()->addJs('/' . basename(__DIR__) . '/index.js');
Asset::getInstance()->addJs('/local/js/bootstrap-plugin/bootstrap-select.min.js');

$APPLICATION->SetAdditionalCSS('/bitrix/templates/.default/bootstrap.min.css');
$APPLICATION->SetAdditionalCSS('/' . basename(__DIR__) . '/main.css');
$APPLICATION->SetAdditionalCSS('/local/css/bootstrap-plugin/bootstrap-select.min.css');

$arUser = $arUserFields($USER->GetID());

if (isset($arRequest['vnutrenne_peremeshenie'])) {
    try {
        Loader::includeModule("workflow");
        Loader::includeModule("bizproc");

        if (!check_bitrix_sessid()) {
            throw new Exception('Проблема с сессией, обновите страницу');
        }

        $sFIO = $arRequest['FIO'];
        $sDepartment = $arRequest['PODRAZDELENIE'];
        $sPosition = $arRequest['DOLZHNOST'];
        $sDateTime = date('d.m.Y H:i', strtotime($arRequest['DATA_VREMYA']));
        $sBoss = $arRequest['RUKOVODITEL'];
        $sProperty = $arRequest['DVIZHIMOE_HTML'];
        $sFromObjects = $arRequest['OTKUDA'];
        $sToObjects = $arRequest['KUDA'];
        $sAddress = $arRequest['ADDRESS'];
        $sTarget = $arRequest['TARGET'];
        $sMore = $arRequest['PODROBNEE'];

        if (empty($sFIO)) {
            throw new Exception('Укажите ФИО');
        }
        if (empty($sDepartment)) {
            throw new Exception('Укажите Подразделение');
        }
        if (empty($sPosition)) {
            throw new Exception('Укажите Должность');
        }
        if (empty($sDateTime)) {
            throw new Exception('Укажите дату, время');
        }
        if (empty($sBoss)) {
            throw new Exception('Укажите руководителя');
        }
        if (empty($sProperty)) {
            throw new Exception('Укажите имущество');
        }
        if (empty($sAddress)) {
            throw new Exception('Укажите адрес');
        }
        if (empty($sTarget)) {
            throw new Exception('Укажите цель перемещения имущества');
        }
        if (empty($sFromObjects)) {
            throw new Exception('Укажите, откуда перемещается имущество');
        }
        if (empty($sToObjects)) {
            throw new Exception('Укажите, куда перемещается имущество');
        }

        $sIDajax = $arRequest['bxajaxid'];

        $arProps = [
            'FIO' => $sFIO,
            'PODRAZDELENIE' => $sDepartment,
            'DOLZHNOST' => $sPosition,
            'DATA_VREMYA' => $sDateTime,
            'RUKOVODITEL' => $sBoss,
            'DVIZHIMOE_HTML' => $sProperty,
            'OTKUDA' => $sFromObjects,
            'KUDA' => $sToObjects,
            'ADDRESS' => $sAddress,
            'TARGET' => $sTarget,
            'PODROBNEE' => $sMore

        ];

        $obEl = new CIBlockElement();
        $arLoadProductArray = [
            'MODIFIED_BY' => $USER->GetId(),
            'IBLOCK_SECTION_ID' => false,
            'IBLOCK_ID' => $arParams['ID_BIZPROC'],
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
    $arResult['ID_USER'] = $arUser['ID'];
    $arResult['FIO'] = empty($arUser['FIO']) ? $arUser['LAST_NAME'] . ' ' . $arUser['NAME'] . ' ' . $arUser['MIDDLE_NAME'] : $arUser['FIO'];
    $arResult['PODRAZDELENIE'] = empty($arUser['PODRAZDELENIE']) ? implode(', ', $arUser['DEPARTMENTS']) : implode(', ', $arUser['PODRAZDELENIE']);
    $arResult['DOLZHNOST'] = empty($arUser['WORK_POSITION_CLEAR']) ? $arUser['DOLJNOST_CLEAR'] : $arUser['WORK_POSITION_CLEAR'];

    $oblistUsersAll = (new CUser())->GetList($by = "LAST_NAME", $order = "ASC", ["ACTIVE" => 'Y', "!LAST_NAME" => null, "!NAME" => null]);

    while ($arRuc = $oblistUsersAll->Fetch()) {
        if (!empty($arRuc['LAST_NAME'])) {
            $arResult['RUKOVODITEL'][] = $arRuc;
        }
    }

    $arResult['DATA_VREMYA'] = date('Y-m-d\TH:i', time());
    $arListUsersAll = (new CUser())->GetList($by = "LAST_NAME", $order = "ASC", ["ACTIVE" => 'Y', "!LAST_NAME" => null, "!NAME" => null], ['FIELDS' => ['SECOND_NAME', 'NAME', 'LAST_NAME', 'ID'], 'SELECT' => ['UF_WORK_POSITION', 'UF_WORK_POSITION_ROD']]);
    while ($arUser = $arListUsersAll->Fetch()) {
        if (!empty($arUser['LAST_NAME']) && !empty($arUser['SECOND_NAME'])) {
            $arResult['USERS'][] = $arUser;
        }
    }

    $arResult['OTKUDA'] = [];
    $arrZdaniya = CIBlockProperty::GetPropertyEnum('OTKUDA', ['value' => "ASC"], ['IBLOCK_ID' => $arParams['ID_BIZPROC']]);
    while ($arZdaniyeItem = $arrZdaniya->GetNext()) {
        $arResult['OTKUDA'][$arZdaniyeItem['ID']] = [
            'ID' => $arZdaniyeItem['ID'],
            'NAME' => $arZdaniyeItem['VALUE']
        ];
    }

    $arResult['KUDA'] = [];
    $arrZdaniya = CIBlockProperty::GetPropertyEnum('KUDA', ['value' => "ASC"], ['IBLOCK_ID' => $arParams['ID_BIZPROC']]);
    while ($arZdaniyeItem = $arrZdaniya->GetNext()) {
        $arResult['KUDA'][$arZdaniyeItem['ID']] = [
            'ID' => $arZdaniyeItem['ID'],
            'NAME' => $arZdaniyeItem['VALUE']
        ];
    }
}

$arResult["__TEMPLATE_FOLDER"] = $this->__folder;
$this->__component->arResult = $arResult;
