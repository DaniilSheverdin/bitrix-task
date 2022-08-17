<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Iblock\SectionTable;

CModule::includeModule("iblock");
CModule::IncludeModule('intranet');

global $APPLICATION, $USER, $userFields;

$arRequest = $arResult['REQUEST'];
$arUserFields = $userFields;

Loader::includeModule("iblock");
require_once __DIR__ . '/constants/index.php';
CJSCore::Init(["date"]);

$APPLICATION->SetTitle("Согласование отчета по листу адаптации");

Asset::getInstance()->addJs('/bitrix/templates/.default/jquery.min.js');
Asset::getInstance()->addJs("/local/js/bootstrap-plugin/popper.min.js");
Asset::getInstance()->addJs('/bitrix/templates/.default/bootstrap.min.js');
Asset::getInstance()->addJs('/' . basename(__DIR__) . '/index.js');
Asset::getInstance()->addJs('/local/js/bootstrap-plugin/bootstrap-select.min.js');

$APPLICATION->SetAdditionalCSS('/bitrix/templates/.default/bootstrap.min.css');
$APPLICATION->SetAdditionalCSS('/' . basename(__DIR__) . '/main.css');
$APPLICATION->SetAdditionalCSS('/local/css/bootstrap-plugin/bootstrap-select.min.css');

$arUser = $userFields($USER->GetID());

if (isset($arRequest['soglasovanie-otcheta-la'])) {
    try {
        Loader::includeModule("workflow");
        Loader::includeModule("bizproc");
        Loader::includeModule("intranet");

        if (!check_bitrix_sessid()) {
            throw new Exception('Проблема с сессией, обновите страницу');
        }

        $intSotrudnic = $arRequest['SOLA_FIO_NS'];
        $intRuc = $arRequest['SLA_RUKOVODITEL_SOTRUDNIKA'];
        $strIDajax = $arRequest['bxajaxid'];

        $arListDeps = GetParentDepartmentstucture($intSotrudnic);

        if (isset($arListDeps[2])) {
            $intDepOIV = $arListDeps[2];
        } elseif (count($arListDeps) < 3) {
            $intDepOIV = array_pop($arListDeps);
        }

        if (!empty($intDepOIV)) {
            $intHead = CIntranetUtils::GetDepartmentManagerID($intDepOIV);
        }

        if (empty($intHead)) {
            $intHead = null;
        }

        $strMODULEID = 'bizproc';

        if (empty($intSotrudnic)) {
            throw new Exception('Укажите ФИО сотрудника');
        }

        if (empty($intRuc)) {
            throw new Exception('Укажите руководителя сотрудника');
        }

        if (empty($_FILES['SOLA_OTCHET_LA'])) {
            throw new Exception('Укажите отчет по листу адаптации');
        }

        if ($_FILES['SOLA_OTCHET_LA']['type'] != 'application/pdf') {
            throw new Exception('Отчет по листу адаптации нового сотрудника должен быть в формате pdf');
        }

        $arProps = [
            'SOLA_FIO_NS' => $intSotrudnic,
            'SLA_RUKOVODITEL_SOTRUDNIKA' => $intRuc,
            'SOLA_RUKOVODITEL_OIV' => $intHead
        ];

        $arFileArray = array_merge(
            $_FILES['SOLA_OTCHET_LA'],
            [
                'MODULE_ID' => $strMODULEID
            ]
        );

        $intfileSaveId = CFile::SaveFile(
            $arFileArray,
            'bp/' . IBLOCK_ID_SOLA
        );
        $arProps['SOLA_OTCHET_LA'] = $intfileSaveId;

        $objEl = new CIBlockElement();
        $arLoadProductArray = [
            'MODIFIED_BY' => $USER->GetId(),
            'IBLOCK_SECTION_ID' => false,
            'IBLOCK_ID' => IBLOCK_ID_SOLA,
            'PROPERTY_VALUES' => $arProps,
            'NAME' => $APPLICATION->GetTitle(),
            'ACTIVE' => "Y",
            'PREVIEW_TEXT' => '',
        ];

        $boolDocumentid = $objEl->Add($arLoadProductArray);
        if (!$boolDocumentid) {
            throw new Exception($objEl->LAST_ERROR);
        }

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
        $arResult['message'] = "Бизнесс-процесс \"{$APPLICATION->GetTitle()}\" запущен.";
        $arResult['ajaxid'] = $strIDajax;
    } catch (Exception $exc) {
        $arResult['message'] = $exc->getMessage();
    }
} else {
    $objlistUsersAll = (new CUser())->GetList($by = "LAST_NAME", $order = "ASC", ["ACTIVE" => 'Y', "!LAST_NAME" => null, "!NAME" => null]);

    while ($arRuc = $objlistUsersAll->Fetch()) {
        if (!empty($arRuc['LAST_NAME'])) {
            $arResult['SOLA_FIO_LIST'][] = $arRuc;
        }
    }
}

$arResult["__TEMPLATE_FOLDER"] = $this->__folder;
$this->__component->arResult = $arResult;
