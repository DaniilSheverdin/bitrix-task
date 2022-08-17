<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}

/** @var array $arParams */
/** @var array $arResult */

$arParams["FILTER_NAME"] = trim($arParams["FILTER_NAME"]);
$arParams["LIST_URL"] = trim($arParams["LIST_URL"]);
if ($arParams["FILTER_NAME"] != '' && $arParams["LIST_URL"] != '') {
    foreach ($arResult["MONTH"] as $week => $arWeek) {
        foreach ($arWeek as $day => $arDay) {
            if (count($arDay["events"])>0) {
                $timeFROM = mktime(0, 0, 0, $arResult["currentMonth"], $arDay["day"], $arResult["currentYear"]);
                $timeTO   = mktime(0, 0, 0, $arResult["currentMonth"], $arDay["day"]+1, $arResult["currentYear"]);

                $strFROM = date($GLOBALS["DB"]->DateFormatToPHP(CLang::GetDateFormat("SHORT")), $timeFROM);
                $strTO   = date($GLOBALS["DB"]->DateFormatToPHP(CLang::GetDateFormat("SHORT")), $timeTO);

                $LIST_URL = $arParams["LIST_URL"];
                if (strpos($LIST_URL, "?") === false) {
                    $LIST_URL .= "?";
                }

                if (strpos($LIST_URL, "&") !== false) {
                    $LIST_URL .= "&";
                }

                $LIST_URL .= urlencode($arParams["FILTER_NAME"]."[>=".$arParams["DATE_FIELD"]."]")."=".urlencode($strFROM);
                $LIST_URL .= "&".urlencode($arParams["FILTER_NAME"]."[<".$arParams["DATE_FIELD"]."]")."=".urlencode($strTO);

                $arResult["MONTH"][$week][$day]["events"][0]["url"] = htmlspecialcharsbx($LIST_URL);
                $arResult["MONTH"][$week][$day]["events"][0]["title"] = "";
            }
        }
    }
}
