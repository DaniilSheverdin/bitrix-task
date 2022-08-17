<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;

global $APPLICATION, $USER, $userFields;

$arRequest = $arResult['REQUEST'];
Loader::includeModule("iblock");
Loader::includeModule("intranet");
require_once __DIR__ . '/constants/index.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/podrazdeleniya_tree.php';
CJSCore::Init(["date"]);

$APPLICATION->SetTitle("Выход нового сотрудника ЦИТ");
Asset::getInstance()->addCss('/bitrix/templates/.default/bootstrap.min.css');
Asset::getInstance()->addJs('/bitrix/templates/.default/jquery.min.js');
Asset::getInstance()->addJs("/local/js/bootstrap-plugin/popper.min.js");
Asset::getInstance()->addJs('/bitrix/templates/.default/bootstrap.min.js');
Asset::getInstance()->addJs('/bitrix/templates/.default/jquery.mask.js');
Asset::getInstance()->addJs('/' . basename(__DIR__) . '/index.js');
Asset::getInstance()->addJs('/local/js/bootstrap-plugin/bootstrap-select.min.js');

Asset::getInstance()->addCss('/' . basename(__DIR__) . '/main.css');
Asset::getInstance()->addCss('/local/css/bootstrap-plugin/bootstrap-select.min.css');

$arUser = $userFields($USER->GetID());

$objPropsWorkerAdd = CIBlockPropertyEnum::GetList(
    [
        "SORT" => "ASC",
        "VALUE" => "ASC"
    ],
    [
        'IBLOCK_ID' => IBLOCK_ID_NEW_EMPLOYEE,
        'CODE' => 'ADD_WORKER'
    ]
);

while ($arVal = $objPropsWorkerAdd->Fetch()) {
    $arResult['ADD_WORKER'][] = $arVal;
}

if (isset($arRequest['cit_new_worker'])) {
    try {
        Loader::includeModule("workflow");
        Loader::includeModule("bizproc");

        if (!check_bitrix_sessid()) {
            throw new Exception('Проблема с сессией, обновите страницу');
        }

        $strFioNovogoSotrudnika = $arRequest['FIO_NOVOGO_SOTRUDNIKA'];
        $strDolzhnost = $arRequest['DOLZHNOST'];
        $intWorkotdel = $arRequest['WORK_OTDEL'];
        $intRuc = $arRequest['RUKOVODITEL_OTDELA'];
        $intAddworker = ($arRequest['ADD_WORKER']) ?? 0;

        $arSectionsData = CIBlockSection::GetList(
            ['SORT' => 'ASC'],
            [
                'ID' => $intWorkotdel,
                'IBLOCK_ID' => IBLOCK_ID_STRUCTURE
            ],
            false,
            ['UF_HEAD2']
        );

        if ($intAddworker) {
            $intAddworker = $arResult['ADD_WORKER'][0]['ID'];
        } else {
            $intAddworker = $arResult['ADD_WORKER'][1]['ID'];
        }

        $arRequest['ZAM_RUKOVODITELYA'] = $arSectionsData->Fetch()['UF_HEAD2'];

        $intZam = $arRequest['ZAM_RUKOVODITELYA'];
        $strReleseDate = (new DateTime($arRequest['RELEASE_DATE']))->format("d.m.Y");
        $strIDajax = $arRequest['bxajaxid'];

        $SPISOK['MODULE_ID'] = 'bizproc';

        $arProps = [
            'FIO_NOVOGO_SOTRUDNIKA' => $strFioNovogoSotrudnika,
            'DOLZHNOST' => $strDolzhnost,
            'WORK_OTDEL' => $intWorkotdel,
            'RELEASE_DATE' => $strReleseDate,
            'RUKOVODITEL_OTDELA' => $intRuc,
            'ZAM_RUKOVODITELYA' => $intZam,
            'ADD_WORKER' => $intAddworker
        ];

        $el = new CIBlockElement();
        $arLoadProductArray = [
            'MODIFIED_BY' => $USER->GetId(),
            'IBLOCK_SECTION_ID' => false,
            'IBLOCK_ID' => $arParams['ID_BIZPROC'],
            'PROPERTY_VALUES' => $arProps,
            'NAME' => $APPLICATION->GetTitle(),
            'ACTIVE' => "Y",
            'PREVIEW_TEXT' => '',
        ];

        $boolDocumentid = $el->Add($arLoadProductArray);
        if (!$boolDocumentid) {
            throw new Exception($el->LAST_ERROR);
        }

        $arErrorsTmp = [];
        $wfId = CBPDocument::StartWorkflow(
            BP_TEMPLATE_ID,
            ["lists", "BizprocDocument", $boolDocumentid],
            ['TargetUser' => "user_" . $arUser['ID']],
            $arErrorsTmp
        );

        if (count($arErrorsTmp) > 0) {
            throw new Exception(
                array_reduce(
                    $arErrorsTmp,
                    function ($carry, $item) {
                        return $carry . "." . $item['message'];
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
} elseif (isset($arRequest['OTDEL_ID'])) {
    $intOtdelID = CIntranetUtils::GetDepartmentManagerID($arRequest['OTDEL_ID']);

    $NACHALNIC_OTDELA = ($intOtdelID) ? $intOtdelID : false;

    $strIDajax = $arRequest['bxajaxid'];

    $arResult['code'] = "OK";
    $arResult['message'] = '';
    $arResult['glava_id'] = $NACHALNIC_OTDELA ? $NACHALNIC_OTDELA : '';
    $arResult['ajaxid'] = $strIDajax;
} else {
    $arResult['OTDELLIST'] = $treeFunc(IBLOCK_ID_STRUCTURE, SECTION_ID_CITTO_STRUCTURE, 4);

    $objlistUsersAll = (new CUser())->GetList($by = "LAST_NAME", $order = "ASC", ["ACTIVE" => 'Y', "!LAST_NAME" => null, "!NAME" => null]);

    while ($arRuc = $objlistUsersAll->Fetch()) {
        if (!empty($arRuc['LAST_NAME'])) {
            $arResult['RUKOVODITEL'][] = $arRuc;
        }
    }
}

$arResult["__TEMPLATE_FOLDER"] = $this->__folder;
$this->__component->arResult = $arResult;
