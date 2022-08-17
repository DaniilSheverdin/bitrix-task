<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UserTable, CIntranetUtils;

global $APPLICATION, $USER, $userFields; 

$arRequest = $arResult['REQUEST'];
Loader::includeModule("iblock");
CJSCore::Init(["date"]);

$APPLICATION->SetTitle("Прохождение стажировки новым сотрудником");

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

define('BP_TEMPLATE_ID', $arParams['ID_TEMPLEATE']);

if (isset($arRequest['prohozhdenie-stazhirovky'])) {
    try {
        Loader::includeModule("workflow");
        Loader::includeModule("bizproc");

        $intSotrudnic = $USER->GetID();

        if (!check_bitrix_sessid()) {
            throw new Exception('Проблема с сессией, обновите страницу');
        }

        $intST_FIO_SOTRUDNIKA = $arRequest['ST_FIO_SOTRUDNIKA'];
        $strST_DOLZHNOST = $arRequest['ST_DOLZHNOST'];
        $arST_KURSY_NA_KORPORATIVNOM_UNIVERSITETE = $arRequest['ST_KURSY_NA_KORPORATIVNOM_UNIVERSITETE'];
        $strST_ZADACHI_NA_STAZHIROVKU = json_encode($arRequest['ST_ZADACHI_NA_STAZHIROVKU']);
        $strST_TREBOVANIYA_PO_ITOGU_STAZHIROVKI = $arRequest['ST_TREBOVANIYA_PO_ITOGU_STAZHIROVKI'];
        $strST_SROK_OKONCHANIYA_STAZHIROVKI = (new DateTime($arRequest['ST_SROK_OKONCHANIYA_STAZHIROVKI']))->format("d.m.Y");
        $strIDajax = $arRequest['bxajaxid'];

        if (empty($intST_FIO_SOTRUDNIKA)) {
            throw new Exception('Укажите ФИО сотрудника');
        }
        if (empty($strST_DOLZHNOST)) {
            throw new Exception('Укажите Должность');
        }
        if (count($arST_KURSY_NA_KORPORATIVNOM_UNIVERSITETE) <= 0) {
            throw new Exception('Укажите Курсы на Корпоративном Университете');
        }
        if (empty($strST_ZADACHI_NA_STAZHIROVKU)) {
            throw new Exception('Укажите Задачи на стажировку');
        }
        if (empty($strST_TREBOVANIYA_PO_ITOGU_STAZHIROVKI)) {
            throw new Exception('Укажите Требования по итогу стажировки');
        }
        if (empty($strST_SROK_OKONCHANIYA_STAZHIROVKI)) {
            throw new Exception('Укажите Срок окончания стажировки');
        }

        $SPISOK['MODULE_ID'] = 'bizproc';

        $arProps = [
            'ST_FIO_SOTRUDNIKA' => $intST_FIO_SOTRUDNIKA,
            'ST_DOLZHNOST' => $strST_DOLZHNOST,
            'ST_KURSY_NA_KORPORATIVNOM_UNIVERSITETE' => $arST_KURSY_NA_KORPORATIVNOM_UNIVERSITETE,
            'ST_ZADACHI_NA_STAZHIROVKU' => $strST_ZADACHI_NA_STAZHIROVKU,
            'ST_TREBOVANIYA_PO_ITOGU_STAZHIROVKI' => $strST_TREBOVANIYA_PO_ITOGU_STAZHIROVKI,
            'ST_SROK_OKONCHANIYA_STAZHIROVKI' => $strST_SROK_OKONCHANIYA_STAZHIROVKI
        ];

        $obIBElem = new CIBlockElement();
        $arLoadProductArray = [
            'MODIFIED_BY' => $USER->GetId(),
            'IBLOCK_SECTION_ID' => false,
            'IBLOCK_ID' => $arParams['ID_BIZPROC'],
            'PROPERTY_VALUES' => $arProps,
            'NAME' => $APPLICATION->GetTitle(),
            'ACTIVE' => 'Y',
            'PREVIEW_TEXT' => '',
        ];

        $intDocumentid = $obIBElem->Add($arLoadProductArray);
        if (!$intDocumentid) {
            throw new Exception($obIBElem->LAST_ERROR);
        }

        $arErrorsTmp = [];

        $wfId = CBPDocument::StartWorkflow(
            BP_TEMPLATE_ID,
            ["lists", "BizprocDocument", $intDocumentid],
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

    $obAllUsers = CUser::GetList($by = 'LAST_NAME', $ord = 'ASC', ['ACTIVE' => 'Y', '!=LAST_NAME' => null, '!=NAME' => null], ['SELECT' => ['UF_DEPARTMENT']]);
    while($arUser = $obAllUsers->GetNext()) {
        if (!empty($arUser['LAST_NAME']) && isset($arUser['UF_DEPARTMENT'][0])) {
            $arParentDeps = GetParentDepartmentstucture($arUser['ID']);
            if(in_array(SECTION_ID_CITTO_STRUCTURE, $arParentDeps) || $arUser['UF_DEPARTMENT'][0] == SECTION_ID_CITTO_STRUCTURE) {
                $arResult['ALL_USERS'][] = $arUser;
            }
        }
    }

    $arPropsList = CIBlockPropertyEnum::GetList(
        [
            "SORT" => "ASC",
            "VALUE" => "ASC"
        ],
        [
            'IBLOCK_ID' => $arParams['ID_BIZPROC'],
            'CODE' => 'ST_KURSY_NA_KORPORATIVNOM_UNIVERSITETE'
        ]
    );

    while ($arVal = $arPropsList->Fetch()) {
        $arResult['ST_KURSY_NA_KORPORATIVNOM_UNIVERSITETE'][] = $arVal;
    }

}

$arResult["__TEMPLATE_FOLDER"] = $this->__folder;
$this->__component->arResult = $arResult;
