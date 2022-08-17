<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;

global $APPLICATION, $USER, $userFields;

$arRequest = $arResult['REQUEST'];
Loader::includeModule("iblock");
include_once __DIR__ . '/functions/index.php';
include_once __DIR__ . '/constants/index.php';
CJSCore::Init(["date"]);

$APPLICATION->SetTitle("Служебная записка");

Asset::getInstance()->addJs('/bitrix/templates/.default/jquery.min.js');
Asset::getInstance()->addJs("/local/js/bootstrap-plugin/popper.min.js");
Asset::getInstance()->addJs('/bitrix/templates/.default/bootstrap.min.js');
Asset::getInstance()->addJs('/bitrix/templates/.default/jquery.mask.js');
Asset::getInstance()->addJs('/' . basename(__DIR__) . '/index.js');
Asset::getInstance()->addJs('/local/js/bootstrap-plugin/bootstrap-select.min.js');

$APPLICATION->SetAdditionalCSS('/bitrix/templates/.default/bootstrap.min.css');
$APPLICATION->SetAdditionalCSS('/' . basename(__DIR__) . '/main.css');
$APPLICATION->SetAdditionalCSS('/local/css/bootstrap-plugin/bootstrap-select.min.css');

$arUser = $userFields($USER->GetID());

$arUsersIDs = [];
$arListUsersAll = (new CUser())->GetList($by = "LAST_NAME", $order = "ASC", ["ACTIVE" => 'Y', "!LAST_NAME" => null, "!NAME" => null], ['FIELDS' => ['SECOND_NAME', 'NAME', 'LAST_NAME', 'ID'], 'SELECT' => ['UF_WORK_POSITION', 'UF_WORK_POSITION_ROD']]);
while ($arUser = $arListUsersAll->Fetch()) {
    if (!empty($arUser['LAST_NAME']) && !empty($arUser['SECOND_NAME'])) {
        $arResult['USERS'][] = $arUser;
        $arUsersIDs[$arUser['ID']] = $arUser;
    }
}

if (isset($arRequest['sluzhebnaya_zapiska'])) {
    try {
        Loader::includeModule("workflow");
        Loader::includeModule("bizproc");

        if (!check_bitrix_sessid()) {
            throw new Exception('Проблема с сессией, обновите страницу');
        }
        $intSotrudnic = $USER->GetID();

        $iRucovoditel = $arRequest['NEPOSREDSTVENNYY_RUKOVODITEL'];
        $iRucovoditelOiv = $arRequest['RUKOVODITEL_ORGANIZATSII_OIV'];
        $arFioSotrudnikov = $arRequest['FIO_SOTRUDNIKOV'];
        $sTselKomandirovaniya = $arRequest['TSEL_KOMANDIROVANIYA'];
        $sDateFrom = $arRequest['DATA_KOMANDIROVANIYA_S'];
        $sDateTo = $arRequest['DATA_KOMANDIROVANIYA_PO'];
        $arMestaCommand = json_decode($arRequest['MESTO_KOMANDIROVANIYA']);
        $strIDajax = $arRequest['bxajaxid'];
        $SPISOK['MODULE_ID'] = 'bizproc';

        // Руководитель ОИВ
        $arRucovoditelDataOiv = $arUsersIDs[$iRucovoditelOiv];
        if (!$arRucovoditelDataOiv) {
            throw new Exception("Не найден руководитель ОИВ");
        }

        // Непосредственный руководитель
        if (!empty($iRucovoditel)) {
            $arRucovoditelData = $arUsersIDs[$iRucovoditel];
            if (!$arRucovoditelData) {
                throw new Exception("Не найден руководитель");
            }
        }

        // ФИО сотрудников
        $arInfoSotr = [];
        foreach ($arFioSotrudnikov as $iKey => $iUserID) {
            $arUser = $userFields($iUserID);
            $sFio = $arUser['FIO_VIN'];
            $sWorkPosition = $arUser['DOLJNOST_VIN'];
            if (!empty($sWorkPosition)) {
                $sWorkPosition = " - " . lcfirst_cyr($sWorkPosition);
            }
            $arInfoSotr[]  = $sFio . $sWorkPosition;
        }

        if (empty($arInfoSotr)) {
            throw new Exception("Не найден сотрудник");
        }

        // Цель командирования
        if (empty($sTselKomandirovaniya)) {
            throw new Exception("Не указана цель командирования");
        }

        // Дата командирования
        if (empty($sDateFrom) || empty($sDateTo)) {
            throw new Exception("Не указана дата командирования");
        } elseif (strtotime($sDateTo) - strtotime($sDateFrom) < 0) {
            throw new Exception("Дата окончания не может быть меньше даты начала");
        } else {
            $sDateFrom = (new DateTime($arRequest['DATA_KOMANDIROVANIYA_S']))->format("d.m.Y");
            $sDateTo = (new DateTime($arRequest['DATA_KOMANDIROVANIYA_PO']))->format("d.m.Y");
        }

        $sTextPeriod = (strtotime($sDateFrom) == strtotime($sDateTo)) ? "$sDateFrom" : "в период с $sDateFrom по $sDateTo";

        // Места командирования
        if (empty($arMestaCommand)) {
            throw new Exception("Не указаны места командирования");
        }

        $sFirstLetter = mb_substr($arRucovoditelDataOiv['NAME'], 0, 1, 'UTF-8');
        $sSecondLetter = mb_substr($arRucovoditelDataOiv['SECOND_NAME'], 0, 1, 'UTF-8');

        $arProps = [
            'DATE_PERIOD' => $sTextPeriod,
            'RUKOVODITEL_FIO' => "{$sFirstLetter}. $sSecondLetter. {$arRucovoditelDataOiv['LAST_NAME']}",
            'RUKOVODITEL_DOLZHNOST' => $arRucovoditelDataOiv['UF_WORK_POSITION'],
            'USERS_FIO' => implode(', ', $arInfoSotr),
            'MESTA_KOMANDIROVANIYA'=> implode(', ', $arMestaCommand),
            'TSEL_KOMANDIROVANIYA' => $sTselKomandirovaniya,
            'RUKOVODITEL_ORGANIZATSII_OIV' => $iRucovoditelOiv,
            'FIO_SOTRUDNIKOV' => $arFioSotrudnikov,
            'MESTO_KOMANDIROVANIYA' => implode(', ', $arMestaCommand),
            'DATA_KOMANDIROVANIYA_S' => $sDateFrom,
            'DATA_KOMANDIROVANIYA_PO' => $sDateTo,
            'NEPOSREDSTVENNYY_RUKOVODITEL' => $iRucovoditel
        ];

        $strContent = str_replace(
            array_map(
                function ($item) {
                    return "#" . $item . "#";
                },
                array_keys($arProps)
            ),
            $arProps,
            file_get_contents(__DIR__ . '/pdftpl/zayavka.html')
        );

        $el = new CIBlockElement();
        $arLoadProductArray = [
            'MODIFIED_BY' => $USER->GetId(),
            'IBLOCK_SECTION_ID' => false,
            'IBLOCK_ID' => IBLOCK_ID_SZ,
            'PROPERTY_VALUES' => $arProps,
            'NAME' => $APPLICATION->GetTitle(),
            'ACTIVE' => "Y",
            'PREVIEW_TEXT' => '',
        ];

        $boolDocumentid = $el->Add($arLoadProductArray);
        if (!$boolDocumentid) {
            throw new Exception($el->LAST_ERROR);
        }

        if (!$GLOBALS['setElementPDFValue']($boolDocumentid, 'SLUZHEBNAYA_ZAPISKA', $strContent, "Служебная записка {$arUsersIDs[$intSotrudnic]['LAST_NAME']}")) {
            CIBlockElement::Delete($boolDocumentid);
            throw new Exception("Не удалось создать файл");
        }

        $arErrorsTmp = [];

        $wfId = CBPDocument::StartWorkflow(
            BP_TEMPLATE_ID,
            ["lists", "BizprocDocument", $boolDocumentid],
            ['TargetUser' => "user_" . $intSotrudnic],
            $arErrorsTmp
        );
        if (count($arErrorsTmp) > 0) {
            throw new Exception(array_reduce($arErrorsTmp, function ($carry, $item) {
                return $carry . "." . $item['message'];
            }, ''));
        }

        $arResult['code'] = "OK";
        $arResult['message'] = "Бизнесс-процесс \"{$APPLICATION->GetTitle()}\" запущен.";
        $arResult['ajaxid'] = $strIDajax;
    } catch (Exception $exc) {
        $arResult['message'] = $exc->getMessage();
    }
} else {
    $arResult['ID_MC_SOTRUDNIK'] = $arUser['ID'];
    $arResult['MC_SOTRUDNIK_NAME'] = $arUser['LAST_NAME'] . ' ' . $arUser['NAME'];
}

$arResult["__TEMPLATE_FOLDER"] = $this->__folder;
$this->__component->arResult = $arResult;
