<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UserTable, CIntranetUtils;
use JetBrains\PhpStorm\ExpectedValues;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

global $APPLICATION, $USER, $userFields, $morphFunct;

$arUserFields = $userFields;
$arRequest = $arResult['REQUEST'];
Loader::includeModule("iblock");
Loader::includeModule('citto.filesigner');
CJSCore::Init(["date"]);

$APPLICATION->SetTitle("Заявка на разработку");

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

if ($arRequest['develop_application'] && $arRequest['develop_application'] == 'add') {
    try {
        Loader::includeModule("workflow");
        Loader::includeModule("bizproc");

        $srtIDajax = $arRequest['bxajaxid'];
        $intMODULEID = 'bizproc';

        if (!check_bitrix_sessid()) {
            throw new Exception('Проблема с сессией, обновите страницу');
        }

        $sName = $arRequest['NAZVANIE_RAZRABOTKI'];
        $sFile = $arRequest['FUNKTSIONALNO_TEKHNICHESKIE_TREBOVANIYA'];
        $sFuncZak = $arRequest['FUNKTSIONALNYY_ZAKAZCHIK'];
        $sOtvFunc = $arRequest['OTVETSTVENNYY_OT_FUNKTSIONALNOGO_ZAKAZCHIKA'];
        $sVidRab = $arRequest['VID_RABOT'];
        $sProject = $arRequest['PROEKT'];
        $sDesc = $arRequest['OPISANIE_RAZRABOTKI'];
        $sDate = $arRequest['SROK_REALIZATSII'];
        $sObosn = $arRequest['OBOSNOVANIE_SROKA'];
        $sDopDocs = $arRequest['DOPOLNITELNYE_DOKUMENTY'];

        if (empty($sFile)) {
            throw new Exception('Прикрепите функционально-технические требования');
        }
        if (empty($sFuncZak)) {
            throw new Exception('Выберите функционального заказчика');
        }
        if (empty($sOtvFunc)) {
            throw new Exception('ВЫберите ответственного от функционального заказчик');
        }
        if (empty($sVidRab)) {
            throw new Exception('Выберите вид работ');
        }
        if (empty($sProject)) {
            throw new Exception('Выберите проект');
        }
        if (empty($sDesc)) {
            throw new Exception('Укажите описание разработки');
        }
        if (empty($sDate)) {
            throw new Exception('Укажите срок реализации');
        }
        if (empty($sObosn)) {
            throw new Exception('Укажите обоснование срока');
        }

        $arLoadProductArray =
            [
                'CREATED_BY' => $USER->GetId(),
                'MODIFIED_BY' => $USER->GetId(),
                'IBLOCK_SECTION_ID' => false,
                'IBLOCK_ID' => $arParams['ID_BIZPROC'],
                'PROPERTY_VALUES' => [
                    'NAZVANIE_RAZRABOTKI' => $sName,
                    'FUNKTSIONALNO_TEKHNICHESKIE_TREBOVANIYA' => $sFile,
                    'FUNKTSIONALNYY_ZAKAZCHIK' => $sFuncZak,
                    'OTVETSTVENNYY_OT_FUNKTSIONALNOGO_ZAKAZCHIKA' => $sOtvFunc,
                    'VID_RABOT' => $sVidRab,
                    'PROEKT' => $sProject,
                    'OPISANIE_RAZRABOTKI' => $sDesc,
                    'SROK_REALIZATSII' => date('d.m.Y', strtotime($sDate)),
                    'OBOSNOVANIE_SROKA' => $sObosn,
                    'DOPOLNITELNYE_DOKUMENTY' => $sDopDocs
                ],
                'NAME' => $APPLICATION->GetTitle(),
                'ACTIVE' => "Y",
                'PREVIEW_TEXT' => '',
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
        $arResult['message'] = "<p>Бизнес-процесс запущен! Ожидайте ответ по почте в течении 30 дней</p>";
    } catch (Exception $exc) {
        $arResult['message'] = $exc->getMessage();
    }
}

$objVRabot = CIBlockPropertyEnum::GetList(
    [
        "SORT" => "ASC",
        "VALUE" => "ASC"
    ],
    [
        "IBLOCK_ID" => $arParams['ID_BIZPROC'],
        "CODE" => "VID_RABOT"
    ]
    );

$objProekt = CIBlockPropertyEnum::GetList(
    [
        "SORT" => "ASC",
        "VALUE" => "ASC"
    ],
    [
        "IBLOCK_ID" => $arParams['ID_BIZPROC'],
        "CODE" => "PROEKT"
    ]
    );

while ($arVal = $objVRabot->Fetch()){
    $arResult['VID_RABOT'][$arVal['ID']] = $arVal;
}

while ($arVal = $objProekt->Fetch()){
    $arResult['PROEKT'][$arVal['ID']] = $arVal;
}

$arOtdelList = CIBlockSection::GetList(false, ['IBLOCK_ID'=>5,'ACTIVE'=>'Y']);
while ($otdel=$arOtdelList->Fetch()){
    $arResult['OTDELLIST'][] = $otdel;
}


$arListUsersAll = (new CUser())->GetList($by = "LAST_NAME", $order = "ASC", ["ACTIVE" => 'Y', "!LAST_NAME" => null, "!NAME" => null], ['FIELDS' => ['SECOND_NAME', 'NAME', 'LAST_NAME', 'ID'], 'SELECT' => ['UF_WORK_POSITION', 'UF_WORK_POSITION_ROD']]);
    while ($arUser = $arListUsersAll->Fetch()) {
        if (!empty($arUser['LAST_NAME']) && !empty($arUser['SECOND_NAME'])) {
            $arResult['USERS'][] = $arUser;
        }
    }


$arResult["__TEMPLATE_FOLDER"] = $this->__folder;
$this->__component->arResult = $arResult;
