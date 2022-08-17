<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Citto\Integration\Source1C;

global $APPLICATION, $USER, $userFields;

$arUserFields = $userFields;
$arRequest = $arResult['REQUEST'];
Loader::includeModule("iblock");
CJSCore::Init(["date"]);

$APPLICATION->SetTitle("Запрос пояснений");

Asset::getInstance()->addJs('/bitrix/templates/.default/jquery.min.js');
Asset::getInstance()->addJs("/local/js/bootstrap-plugin/popper.min.js");
Asset::getInstance()->addJs('/bitrix/templates/.default/bootstrap.min.js');
Asset::getInstance()->addJs('/' . basename(__DIR__) . '/index.js');
Asset::getInstance()->addJs('/local/js/bootstrap-plugin/bootstrap-select.min.js');

$APPLICATION->SetAdditionalCSS('/bitrix/templates/.default/bootstrap.min.css');
$APPLICATION->SetAdditionalCSS('/' . basename(__DIR__) . '/main.css');
$APPLICATION->SetAdditionalCSS('/local/css/bootstrap-plugin/bootstrap-select.min.css');

$arUser = $arUserFields($USER->GetID());

$arResult['DATE'] = [];
$arYears = [];
$iPrevYear = ((int)date('Y')) - 1;
for ($iYear = $iPrevYear; $iYear >= ($iPrevYear - 10); $iYear--) {
    array_push($arYears, $iYear);
}

foreach ($arYears as $iYear) {
    $arResult['DATE'][] = $iYear;
}

if ($arRequest['action'] == 'select') {
    $iUserID = $arRequest['userid'];
    $iYear = $arRequest['year'];
    if ($iUserID && $iYear) {
        ini_set("soap.wsdl_cache_enabled", 0);
        $obConnect1C = Source1C::Connect1C(Source1C::SOURCE_1C_SPRAVKI, ['login' => Source1C::SOURCE_1C_PROD__USER, 'password' => Source1C::SOURCE_1C_PROD__PASS]);
        $arUserInfo = $GLOBALS['userFields']($iUserID);
        $sIDajax = $arRequest['bxajaxid'];
        $sName = $arUserInfo['NAME'];
        $sSurName = $arUserInfo['LAST_NAME'];
        $sMiddleName = $arUserInfo['MIDDLE_NAME'];
        $sSID = $arUserInfo['UF_SID'];

        if ($arUserInfo['PERSONAL_BIRTHDAY']) {
            $sBirthday = date('Y-m-d', strtotime($arUserInfo['PERSONAL_BIRTHDAY']));
        } else if ($sSID) {
            $arResC1 = Source1C::GetArray(
                Source1C::Connect1C(Source1C::SOURCE_1C_PROD, ['login' => Source1C::SOURCE_1C_PROD__USER, 'password' => Source1C::SOURCE_1C_PROD__PASS]),
                'PersonalData',
                ['EmployeeID' => $sSID]
            );
            $sBirthday = $arResC1['Data']['PersonalData']['DateOfBirth'];
        } else {
            $sBirthday = '';
        }

        $sSnils = '';
        $sINN = $arUserInfo['UF_INN'];

        $rRespone = Source1C::Get($obConnect1C, 'GetIncomeStatements', [
            'Individual' => [
                'Name' => $sName,
                "Surname" => $sSurName,
                'MiddleName' => $sMiddleName,
                'Birthday' => $sBirthday,
                'SNILS' => $sSnils,
                'INN' => $sINN,
            ],
            'ReportingPeriod' => $iYear
        ]);

        $obIncomeStatement = $rRespone->return->Data->IncomeStatements->IncomeStatementsList->IncomeStatement;
        $sObjectUID = (isset($obIncomeStatement->UID)) ? $obIncomeStatement->UID : $obIncomeStatement[0]->UID;

        $arResult['code'] = "OK";
        $arResult['ajaxid'] = $sIDajax;
        $arResult['message'] = $sObjectUID;
    }
} else if (isset($arRequest['zapros_poyasneniy'])) {
    try {
        Loader::includeModule("workflow");
        Loader::includeModule("bizproc");

        if (!check_bitrix_sessid()) {
            throw new Exception('Проблема с сессией, обновите страницу');
        }

        $sDatePod = date('d.m.Y', strtotime($arRequest['DATA_PODACHI']));
        $iServant = $arRequest['SLUZHASHCHIY'];
        $iOfficial = $arRequest['DOLZHNOSTNOE_LITSO'];
        $iDateFrom = $arRequest['DATE_FROM'];
        $sResult = $arRequest['RESULT'];
        $sStatus = $arRequest['STATUS'];
        $sUID  = $arRequest['UID'];

        if (empty($sDatePod)) {
            throw new Exception('Укажите дату подачи заявления');
        }
        if (empty($iServant)) {
            throw new Exception('Укажите государственного гражданского служащего');
        }
        if (empty($iOfficial)) {
            throw new Exception('Укажите должностное лицо');
        }
        if (empty($iDateFrom)) {
            throw new Exception('Укажите отчётный период');
        }
        if (empty($sResult)) {
            throw new Exception('Укажите результат анализа');
        }
        if (empty($sStatus)) {
            throw new Exception('Укажите статус');
        }

        $sIDajax = $arRequest['bxajaxid'];
        $iMODULEID = 'bizproc';

        $arProps = [
            'SLUZHASHCHIY' => $iServant,
            'DOLZHNOSTNOE_LITSO' => $iOfficial,
            'DATE_FROM' => $iDateFrom,
            'RESULT' => $sResult,
            'STATUS' => $sStatus,
            'DATA_PODACHI' => $sDatePod,
            'UID' => $sUID
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
    $arResult['OFFICIALS'] = [];
    $arResult['USERS'] = [];

    $obListUsersAll = (new CUser())->GetList($by = "LAST_NAME", $order = "ASC", ["ACTIVE" => 'Y', "!LAST_NAME" => null, "!NAME" => null], ['FIELDS' => ['SECOND_NAME', 'NAME', 'LAST_NAME', 'ID'], 'SELECT' => ['UF_WORK_POSITION', 'UF_WORK_POSITION_ROD']]);
    while ($arUser = $obListUsersAll->Fetch()) {
        if (!empty($arUser['LAST_NAME']) && !empty($arUser['SECOND_NAME'])) {
            $arUser['UF_WORK_POSITION'] = mb_strimwidth($arUser['UF_WORK_POSITION'], 0, 80, '...');
            $arResult['USERS'][] = $arUser;
        }

        if (in_array($arUser['ID'], [1117, 1039, 1608])) {
            $arResult['OFFICIALS'][] = $arUser;
        }
    }

    $arResult['STATUS'] = [];
    $arDeyat = CIBlockProperty::GetPropertyEnum('STATUS', [], ['IBLOCK_ID' => $arParams['ID_BIZPROC']]);
    while ($arDeyatItem = $arDeyat->GetNext()) {
        $arResult['STATUS'][$arDeyatItem['ID']] = [
            'ID' => $arDeyatItem['ID'],
            'NAME' => $arDeyatItem['VALUE']
        ];
    }
}

$arResult["__TEMPLATE_FOLDER"] = $this->__folder;
$this->__component->arResult = $arResult;
