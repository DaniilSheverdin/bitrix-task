<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

// saving template name to cache array
$arResult["__TEMPLATE_FOLDER"] = $this->__folder;
// writing new $arResult to cache file
$this->__component->arResult = $arResult;

global $USER, $APPLICATION;
define("HLBID", 6);

use Bitrix\Highloadblock\HighloadBlockTable as HL;

$APPLICATION->SetAdditionalCss("/bitrix/css/main/bootstrap_v4/bootstrap.min.css");

$arResult['IS_AJAX'] = ($arParams['IS_AJAX'] == "Y");
$arResult['XLSX'] = ($_REQUEST["XLSX"] == 'Y');
$arExplodeHost = explode('.', getenv("HTTP_HOST"));

$arResult['ISLOCAL'] = mb_strpos(getenv("HTTP_HOST"), '.local');

if ($arResult['ISLOCAL']) {
    if (CModule::IncludeModule('highloadblock')) {
        $arResult['MOUNTHS'] = [
            '1' => 'Январь',
            '2' => 'Февраль',
            '3' => 'Март',
            '4' => 'Апрель',
            '5' => 'Май',
            '6' => 'Июнь',
            '7' => 'Июль',
            '8' => 'Август',
            '9' => 'Сентябрь',
            '10' => 'Октябрь',
            '11' => 'Ноябрь',
            '12' => 'Декабрь',
        ];

        $arResult['START_YEAR'] = isset($_REQUEST['START_DATE_submit']) ? (new DateTime($_REQUEST['START_DATE_submit']))->format("Y") : (new DateTime())->format("Y");
        $arResult['START_MOUNTH'] = isset($_REQUEST['START_DATE_submit']) ? (new DateTime($_REQUEST['START_DATE_submit']))->format("m") : (new DateTime())->format("m");
        $arResult['START_DAY'] = isset($_REQUEST['START_DATE_submit']) ? (new DateTime($_REQUEST['START_DATE_submit']))->format("d") : (new DateTime())->format("d");
        if (mb_strlen($arResult['START_MOUNTH']) == 1) {
            $arResult['START_MOUNTH'] = "0" . $arResult['START_MOUNTH'];
        }

        $arResult['DEFAULT_DATE'] = (new DateTime())->format("Y-m-d");
        $arResult['DEFAULT_DATE_SUBMIT'] = (new DateTime($arResult['DEFAULT_DATE']))->format("Y-m-d");

        $arResult['START_DATE'] = isset($_REQUEST['START_DATE_submit']) ? $_REQUEST['START_DATE_submit'] : $arResult['DEFAULT_DATE'];

        $arResult['START_DATE_INPUT'] = (new DateTime($arResult['START_DATE']))->format("Y-m-d");

        if ($_REQUEST['CURRENT_PERIOD'] == 'mounth') {
            $arResult['CURRENT_PERIOD'] = trim($_REQUEST['CURRENT_PERIOD']);
            $strModify = ' + 1 month';

            $arResult['BEFORE_DATE'] = (new DateTime($arResult['START_DATE']))->modify(" - 1 month")->format("Y-m-d");
            $arResult['BEFORE_DATE_SUBMIT'] = (new DateTime($arResult['BEFORE_DATE']))->format("Y-m-d");
            $arResult['AFTER_DATE'] = (new DateTime($arResult['START_DATE']))->modify(" + 1 month")->format("Y-m-d");
            $arResult['AFTER_DATE_SUBMIT'] = (new DateTime($arResult['AFTER_DATE']))->format("Y-m-d");
        } else {
            $arResult['CURRENT_PERIOD'] = 'day';
            $strModify = ' + 1 day';

            $arResult['BEFORE_DATE'] = (new DateTime($arResult['START_DATE']))->modify(" - 1 day")->format("Y-m-d");
            $arResult['BEFORE_DATE_SUBMIT'] = (new DateTime($arResult['BEFORE_DATE']))->format("Y-m-d");
            $arResult['AFTER_DATE'] = (new DateTime($arResult['START_DATE']))->modify(" + 1 day")->format("Y-m-d");
            $arResult['AFTER_DATE_SUBMIT'] = (new DateTime($arResult['AFTER_DATE']))->format("Y-m-d");
        }

        $objEntityData = HL::compileEntity(HL::getById(HLBID)->fetch())->getDataClass();

        if ($arResult['XLSX']) {
            $objResList = $objEntityData::getList(
                [
                    'select' => array("*"),
                    'order' => array(
                        'UF_DATECALL' => 'desc',
                        'UF_FIOCALL' => 'asc'
                    )
                ]
            );
        } else {
            $startDate = (new DateTime($arResult['START_DAY'] . '.' . $arResult['START_MOUNTH'] . '.' . $arResult['START_YEAR']))->format("d.m.Y") . " 00:00:00";

            $objDateEnd = new DateTime($startDate);
            $endDate = $objDateEnd->modify($strModify)->format("d.m.Y") . " 00:00:00";

            // $endDate = (new DateTime(cal_days_in_month(CAL_GREGORIAN, $arResult['START_MOUNTH'], $arResult['START_YEAR']) . '.' . $arResult['START_MOUNTH'] . '.' . $arResult['START_YEAR']))->format("d.m.Y") . " 23:59:59";

            $objResList = $objEntityData::getList(
                [
                    'select' => array("*"),
                    'order' => array(
                        'UF_DATECALL' => 'desc',
                        'UF_FIOCALL' => 'asc'
                    ),
                    'filter' => [
                        "LOGIC" => "AND",
                        [">=UF_DATECALL" => $startDate],
                        ["<=UF_DATECALL" => $endDate]
                    ]
                ]
            );
        }

        $arResult['ITEMS_CALL'] = $objResList->fetchAll();
        $arResult['ITEMS_CALL_GROUP'] = [];
        $arResult['COUNT_ITEM'] = count($arResult['ITEMS_CALL']);

        foreach ($arResult['ITEMS_CALL'] as $key => $item) {
            $arResult['ITEMS_CALL'][$key]['UF_DATECALL'] = $item['UF_DATECALL']->format("H:i d.m.Y");
            $arExplode = explode(' ', $arResult['ITEMS_CALL'][$key]['UF_DATECALL']);

            $arResult['ITEMS_CALL'][$key]['UF_DATECALL'] = trim($arExplode[1]);
            $arResult['ITEMS_CALL'][$key]['UF_TIMECALL'] = trim($arExplode[0]);

            $arResult['ITEMS_CALL_GROUP'][$item['UF_DATECALL']->format("d.m.Y")][] = $arResult['ITEMS_CALL'][$key];
        }

        if ($arResult['XLSX']) {
            include_once('xlsx/index.php');
        }
    }
} else {
    LocalRedirect('/');
}
