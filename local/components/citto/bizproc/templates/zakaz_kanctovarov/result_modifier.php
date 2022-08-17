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
require_once __DIR__ . '/constants/index.php';
CJSCore::Init(["date"]);

$APPLICATION->SetTitle("Заказ канцтоваров");

Asset::getInstance()->addJs('/bitrix/templates/.default/jquery.min.js');
Asset::getInstance()->addJs("/local/js/bootstrap-plugin/popper.min.js");
Asset::getInstance()->addJs('/bitrix/templates/.default/bootstrap.min.js');
Asset::getInstance()->addJs('/' . basename(__DIR__) . '/index.js');
Asset::getInstance()->addJs('/local/js/bootstrap-plugin/bootstrap-select.min.js');

$APPLICATION->SetAdditionalCSS('/bitrix/templates/.default/bootstrap.min.css');
$APPLICATION->SetAdditionalCSS('/' . basename(__DIR__) . '/main.css');
$APPLICATION->SetAdditionalCSS('/local/css/bootstrap-plugin/bootstrap-select.min.css');

$arUser = $arUserFields($USER->GetID());

if (isset($arRequest['zakaz-kanctovarov'])) {
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
        $iPen = (int) $arRequest['KOLICHESTVO_RUCHEK'];
        $iPaper = (int) $arRequest['BUMAGA_KOL_VO'];
        $iPencil = (int) $arRequest['KARANDASH_KOL_VO'];

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
        if (!is_numeric($iPen)) {
            throw new Exception('Укажите количество ручек');
        }
        if (!is_numeric($iPaper)) {
            throw new Exception('Укажите количество бумаги');
        }
        if (!is_numeric($iPencil)) {
            throw new Exception('Укажите количество карандашей');
        }
        if (empty($sBoss)) {
            throw new Exception('Укажите руководителя');
        }

        $sIDajax = $arRequest['bxajaxid'];

        $iKolichestvo = 0;
        $iMODULEID = 'bizproc';

        $arProps = [
            'FIO' => $sFIO,
            'PODRAZDELENIE' => $sDepartment,
            'DOLZHNOST' => $sPosition,
            'DATA_VREMYA' => $sDateTime,
            'KOLICHESTVO_RUCHEK' => $iPen,
            'BUMAGA_KOL_VO' => $iPaper,
            'KARANDASH_KOL_VO' => $iPencil,
            'RUKOVODITEL' => $sBoss
        ];

        $obEl = new CIBlockElement();
        $arLoadProductArray = [
            'MODIFIED_BY' => $USER->GetId(),
            'IBLOCK_SECTION_ID' => false,
            'IBLOCK_ID' => IBLOCK_ID_ZKCH,
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
            BP_TEMPLATE_ID,
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
    $arResult['KOLICHESTVO_RUCHEK'] = 0;
    $arResult['BUMAGA_KOL_VO'] = 0;
    $arResult['KARANDASH_KOL_VO'] = 0;

    $arListUsersAll = (new CUser())->GetList($by = "LAST_NAME", $order = "ASC", ["ACTIVE" => 'Y', "!LAST_NAME" => null, "!NAME" => null], ['FIELDS' => ['SECOND_NAME', 'NAME', 'LAST_NAME', 'ID'], 'SELECT' => ['UF_WORK_POSITION', 'UF_WORK_POSITION_ROD']]);
    while ($arUser = $arListUsersAll->Fetch()) {
        if (!empty($arUser['LAST_NAME']) && !empty($arUser['SECOND_NAME'])) {
            $arResult['USERS'][] = $arUser;
        }
    }
}

$arResult["__TEMPLATE_FOLDER"] = $this->__folder;
$this->__component->arResult = $arResult;
