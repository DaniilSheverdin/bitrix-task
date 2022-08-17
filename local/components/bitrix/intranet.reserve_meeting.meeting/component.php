<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}

if (!CModule::IncludeModule("intranet")) {
    return ShowError(GetMessage("EC_INTRANET_MODULE_NOT_INSTALLED"));
}
if (!CModule::IncludeModule("iblock")) {
    return ShowError(GetMessage("EC_IBLOCK_MODULE_NOT_INSTALLED"));
}

$iblockId = intval($arParams["IBLOCK_ID"]);

$arParams["PAGE_VAR"] = trim($arParams["PAGE_VAR"]);
if (mb_strlen($arParams["PAGE_VAR"]) <= 0) {
    $arParams["PAGE_VAR"] = "page";
}

$arParams["MEETING_VAR"] = trim($arParams["MEETING_VAR"]);
if (mb_strlen($arParams["MEETING_VAR"]) <= 0) {
    $arParams["MEETING_VAR"] = "meeting_id";
}

$arParams["ITEM_VAR"] = trim($arParams["ITEM_VAR"]);
if (mb_strlen($arParams["ITEM_VAR"]) <= 0) {
    $arParams["ITEM_VAR"] = "item_id";
}

$meetingId = intval($arParams["MEETING_ID"]);
if ($meetingId <= 0) {
    $meetingId = intval($_REQUEST[$arParams["MEETING_VAR"]]);
}

$arParams["PATH_TO_MEETING"] = trim($arParams["PATH_TO_MEETING"]);
if (mb_strlen($arParams["PATH_TO_MEETING"]) <= 0) {
    $arParams["PATH_TO_MEETING"] = HtmlSpecialCharsbx($APPLICATION->GetCurPage() . "?" . $arParams["PAGE_VAR"] . "=meeting&" . $arParams["MEETING_VAR"] . "=#meeting_id#");
}

$arParams["PATH_TO_VIEW_ITEM"] = trim($arParams["PATH_TO_VIEW_ITEM"]);
if (mb_strlen($arParams["PATH_TO_VIEW_ITEM"]) <= 0) {
    $arParams["PATH_TO_VIEW_ITEM"] = HtmlSpecialCharsbx($APPLICATION->GetCurPage() . "?" . $arParams["PAGE_VAR"] . "=view_item&" . $arParams["MEETING_VAR"] . "=#meeting_id#&" . $arParams["ITEM_VAR"] . "=#item_id#");
}

$arParams["PATH_TO_MEETING_LIST"] = trim($arParams["PATH_TO_MEETING_LIST"]);
if (mb_strlen($arParams["PATH_TO_MEETING_LIST"]) <= 0) {
    $arParams["PATH_TO_MEETING_LIST"] = HtmlSpecialCharsbx($APPLICATION->GetCurPage());
}

$arParams["PATH_TO_RESERVE_MEETING"] = trim($arParams["PATH_TO_RESERVE_MEETING"]);
if (mb_strlen($arParams["PATH_TO_RESERVE_MEETING"]) <= 0) {
    $arParams["PATH_TO_RESERVE_MEETING"] = HtmlSpecialCharsbx($APPLICATION->GetCurPage() . "?" . $arParams["PAGE_VAR"] . "=reserve_meeting&" . $arParams["MEETING_VAR"] . "=#meeting_id#&" . $arParams["ITEM_VAR"] . "=#item_id#");
}

if (!is_array($arParams["USERGROUPS_CLEAR"])) {
    if (intval($arParams["USERGROUPS_CLEAR"]) > 0) {
        $arParams["USERGROUPS_CLEAR"] = [$arParams["USERGROUPS_CLEAR"]];
    } else {
        $arParams["USERGROUPS_CLEAR"] = [];
    }
}

if (!is_array($arParams["WEEK_HOLIDAYS"])) {
    if ($arParams["WEEK_HOLIDAYS"] != '' && $arParams["WEEK_HOLIDAYS"] >= 0 && $arParams["WEEK_HOLIDAYS"] < 7) {
        $arParams["WEEK_HOLIDAYS"] = [intval($arParams["WEEK_HOLIDAYS"])];
    } else {
        $arParams["WEEK_HOLIDAYS"] = [5, 6];
    }
}

$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "Y" ? "Y" : "N");
$arParams["SET_NAVCHAIN"] = ($arParams["SET_NAVCHAIN"] == "Y" ? "Y" : "N");

$arParams['NAME_TEMPLATE'] = $arParams['NAME_TEMPLATE'] ? $arParams['NAME_TEMPLATE'] : CSite::GetNameFormat(false);
$bUseLogin = $arParams['SHOW_LOGIN'] != "N";

$arParams["NAME_TEMPLATE_WO_NOBR"] = str_replace(
    ["#NOBR#", "#/NOBR#"],
    ["", ""],
    $arParams["NAME_TEMPLATE"]
);


$weekStart = trim($_REQUEST["week_start"]);
if ($weekStart != '' && mb_strlen($weekStart) != 8) {
    $weekStartTmp = MakeTimeStamp($weekStart, FORMAT_DATETIME);
    $weekStart = date("Y", $weekStartTmp).date("m", $weekStartTmp).date("d", $weekStartTmp);
}
if (mb_strlen($weekStart) != 8) {
    $weekStart = date("Ymd");
}

$weekYear = intval(mb_substr($weekStart, 0, 4));
$weekMonth = intval(mb_substr($weekStart, 4, 2));
$weekDay = intval(mb_substr($weekStart, 6, 2));

$weekTime = mktime(0, 0, 0, $weekMonth, $weekDay, $weekYear);
if ($weekTime === false || $weekTime == -1) {
    $weekTime = time();
}

$weekYear = intval(date("Y", $weekTime));
$weekMonth = intval(date("m", $weekTime));
$weekDay = intval(date("d", $weekTime));

$weekDoW = intval(date("w", $weekTime));
if ($weekDoW == 0) {
    $weekDoW = 7;
}

$weekDay = $weekDay - $weekDoW + 1;

$weekTimeStart = mktime(0, 0, 0, $weekMonth, $weekDay, $weekYear);
$weekTimeEnd = mktime(0, 0, 0, $weekMonth, $weekDay + 7, $weekYear);
$weekTimeEndPrint = mktime(0, 0, 0, $weekMonth, $weekDay + 6, $weekYear);

$arResult["FatalError"] = "";

if (!CIBlockRights::UserHasRightTo($iblockId, $iblockId, 'element_read')) {
    $arResult["FatalError"] .= GetMessage("INTS_NO_IBLOCK_PERMS") . ".";
}

if (mb_strlen($arResult["FatalError"]) <= 0) {
    $arResult["ALLOWED_FIELDS"] = [
        "ID" => [
            "NAME" => GetMessage("INAF_F_ID"),
            "ORDERABLE" => true,
            "FILTERABLE" => true,
            "TYPE" => "int",
            "IS_FIELD" => true,
        ],
        "NAME" => [
            "NAME" => GetMessage("INAF_F_NAME"),
            "ORDERABLE" => true,
            "FILTERABLE" => true,
            "TYPE" => "string",
            "IS_FIELD" => true,
            "MANDATORY" => 'Y'
        ],
        "UF_TYPE" => [
            "NAME" => "Тип зала по мероприятиям",
            "ORDERABLE" => false,
            "FILTERABLE" => false,
            "TYPE" => "enumeration",
            "SELECT_VALUES" => ["Не выбрано", 'Собрание', 'Торжественное собрание', 'Концерт', 'Круглый стол', 'Конференция', 'Другое']
        ],
        "UF_TYPE_OTHER" => [
            "NAME" => "Укажите тип зала",
            "ORDERABLE" => false,
            "FILTERABLE" => false,
            "TYPE" => "string",
            "IS_FIELD" => false,
        ],
        "UF_COUNTPLACE" => [
            "NAME" => "Количество мест в зале",
            "ORDERABLE" => false,
            "FILTERABLE" => false,
            "TYPE" => "double",
            "IS_FIELD" => false,
            "MANDATORY" => 'Y'
        ],
        "UF_STAGE" => [
            "NAME" => "Наличие сцены",
            "ORDERABLE" => false,
            "FILTERABLE" => false,
            "TYPE" => "boolean",
            "IS_FIELD" => false,
        ],
        "UF_STAGE_W" => [
            "NAME" => "Размер сцены. Ширина (в м)",
            "ORDERABLE" => false,
            "FILTERABLE" => false,
            "TYPE" => "double",
            "IS_FIELD" => false,
        ],
        "UF_STAGE_H" => [
            "NAME" => "Размер сцены. Длина (в м)",
            "ORDERABLE" => false,
            "FILTERABLE" => false,
            "TYPE" => "double",
            "IS_FIELD" => false,
        ],
        "UF_PRESIDIUM" => [
            "NAME" => "Наличие президиума",
            "ORDERABLE" => false,
            "FILTERABLE" => false,
            "TYPE" => "boolean",
            "IS_FIELD" => false,
        ],
        "UF_PRESIDIUM_W" => [
            "NAME" => "Размер президиума. Ширина (в м)",
            "ORDERABLE" => false,
            "FILTERABLE" => false,
            "TYPE" => "double",
            "IS_FIELD" => false,
        ],
        "UF_PRESIDIUM_H" => [
            "NAME" => "Размер президиума. Длина (в м)",
            "ORDERABLE" => false,
            "FILTERABLE" => false,
            "TYPE" => "double",
            "IS_FIELD" => false,
        ],
        "UF_AUDIO" => [
            "NAME" => "Наличие аудио аппаратуры",
            "ORDERABLE" => false,
            "FILTERABLE" => false,
            "TYPE" => "boolean",
            "IS_FIELD" => false,
        ],
        "UF_AUDIO_CHAR" => [
            "NAME" => "Характеристики аудио аппаратуры",
            "ORDERABLE" => false,
            "FILTERABLE" => false,
            "TYPE" => "string",
            "IS_FIELD" => false,
        ],
        "UF_DINNER" => [
            "NAME" => "Возможность организации обеда, фуршета",
            "ORDERABLE" => false,
            "FILTERABLE" => false,
            "TYPE" => "boolean",
            "IS_FIELD" => false,
        ],
        "UF_DINNER_INFO" => [
            "NAME" => "Дополнительная информация об организации обедов и фуршетов",
            "ORDERABLE" => false,
            "FILTERABLE" => false,
            "TYPE" => "string",
            "IS_FIELD" => false,
        ],
        "UF_PARKING" => [
            "NAME" => "Наличие парковки",
            "ORDERABLE" => false,
            "FILTERABLE" => false,
            "TYPE" => "boolean",
            "IS_FIELD" => false,
        ],
        "UF_PARKING_TYPE" => [
            "NAME" => "Тип парковки",
            "ORDERABLE" => false,
            "FILTERABLE" => false,
            "TYPE" => "enumeration",
            "SELECT_VALUES" => ['Платная', 'Бесплатная']
        ],
        "UF_PARKING_PLACE" => [
            "NAME" => "Количество мест на парковке",
            "ORDERABLE" => false,
            "FILTERABLE" => false,
            "TYPE" => "double",
            "IS_FIELD" => false,
        ],
        "UF_VENTILATION" => [
            "NAME" => "Вентиляция",
            "ORDERABLE" => false,
            "FILTERABLE" => false,
            "TYPE" => "boolean",
            "IS_FIELD" => false,
        ],
        "UF_CONDITION" => [
            "NAME" => "Кондиционирование",
            "ORDERABLE" => false,
            "FILTERABLE" => false,
            "TYPE" => "boolean",
            "IS_FIELD" => false,
        ],
        "UF_FACESTAGE" => [
            "NAME" => "Собственник зала",
            "ORDERABLE" => false,
            "FILTERABLE" => false,
            "TYPE" => "string",
            "IS_FIELD" => false,
        ],
        "UF_ARENDA" => [
            "NAME" => "Наличие арендной платы",
            "ORDERABLE" => false,
            "FILTERABLE" => false,
            "TYPE" => "boolean",
            "IS_FIELD" => false,
        ],
        "UF_ARENDA_PAY" => [
            "NAME" => "Стоимость аренды (р/час)",
            "ORDERABLE" => false,
            "FILTERABLE" => false,
            "TYPE" => "double",
            "IS_FIELD" => false,
        ],
        "UF_FACE" => [
            "NAME" => "Контактное лицо (телефон, e-mail)",
            "ORDERABLE" => false,
            "FILTERABLE" => false,
            "TYPE" => "string",
            "IS_FIELD" => false,
        ],
        "UF_ADDRESS" => [
            "NAME" => "Адрес помещения",
            "ORDERABLE" => false,
            "FILTERABLE" => false,
            "TYPE" => "string",
            "IS_FIELD" => false,
        ],
        "UF_FILE" => [
            "NAME" => "Фото",
            'MULTIPLE' => 'Y',
            "ORDERABLE" => false,
            "TYPE" => "file",
        ],
    ];

    $arUserFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("IBLOCK_".$iblockId."_SECTION", 0, LANGUAGE_ID);

    $arKeys = array_keys($arResult["ALLOWED_FIELDS"]);
    foreach ($arKeys as $key) {
        if (!$arResult["ALLOWED_FIELDS"][$key]["IS_FIELD"]) {
            if (!array_key_exists($key, $arUserFields)) {
                $arFields = [
                    "ENTITY_ID" => "IBLOCK_".$iblockId."_SECTION",
                    "FIELD_NAME" => $key,
                    "USER_TYPE_ID" => $arResult["ALLOWED_FIELDS"][$key]["TYPE"],
                    "XML_ID" => 'XML_UF_TYPE',
                    'MULTIPLE' => $arResult["ALLOWED_FIELDS"][$key]["MULTIPLE"],
                ];

                $obUserField = new CUserTypeEntity;
                $idField = $obUserField->Add($arFields);
                if (isset($arResult["ALLOWED_FIELDS"][$key]['SELECT_VALUES'])) {
                    $obEnum = new CUserFieldEnum();
                    $arAddEnum = [];
                    foreach ($arResult["ALLOWED_FIELDS"][$key]['SELECT_VALUES'] as $k => $it) {
                        $arAddEnum['n'.$k] = [
                            'VALUE' => $it
                        ];
                    }
                    $obEnum->SetEnumValues($idField, $arAddEnum);
                }
            }

            if (isset($arResult["ALLOWED_FIELDS"][$key]['SELECT_VALUES'])) {
                $obEnum = new \CUserFieldEnum;
                $rsEnum = $obEnum->GetList([], ["USER_FIELD_NAME" => $key]);
                while ($arEnum = $rsEnum->Fetch()) {
                    $arResult['SELECT_TYPE'][$arEnum['ID']] = $arEnum['VALUE'];
                }
            }
        }
    }
}

if (mb_strlen($arResult["FatalError"]) <= 0) {
    if ($arParams["SET_TITLE"] == "Y") {
        $APPLICATION->SetTitle(GetMessage("INTASK_C36_PAGE_TITLE"));
    }

    if ($arParams["SET_NAVCHAIN"] == "Y") {
        $APPLICATION->AddChainItem(GetMessage("INTASK_C36_PAGE_TITLE1"), CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MEETING_LIST"], []));
    }

    $arSelectFields = ["IBLOCK_ID"];
    foreach ($arResult["ALLOWED_FIELDS"] as $key => $value) {
        $arSelectFields[] = $key;
    }

    $dbMeeting = CIBlockSection::GetList(
        [],
        ["ID" => $meetingId, "ACTIVE" => "Y", "IBLOCK_ID" => $iblockId],
        false,
        $arSelectFields
    );
    $arMeeting = $dbMeeting->GetNext();

    if (!$arMeeting) {
        $arResult["FatalError"] = GetMessage("INAF_MEETING_NOT_FOUND");
    }
}

if (mb_strlen($arResult["FatalError"]) <= 0) {
    $clearId = intval($_REQUEST["clear_id"]);
    if ($clearId > 0 && check_bitrix_sessid()) {
        $dbElements = CIBlockElement::GetList(
            [],
            [
                "IBLOCK_ID" => $arMeeting["IBLOCK_ID"],
                "SECTION_ID" => $arMeeting["ID"],
                "ID" => $clearId,
            ],
            false,
            false,
            ["ID", "IBLOCK_ID", "CREATED_BY"]
        );
        if ($arElement = $dbElements->GetNext()) {
            if ($GLOBALS["USER"]->IsAuthorized()
                && ($GLOBALS["USER"]->IsAdmin()
                    || count(array_intersect($GLOBALS["USER"]->GetUserGroupArray(), $arParams["USERGROUPS_CLEAR"])) > 0
                    || $arElement["CREATED_BY"] == $GLOBALS["USER"]->GetID())) {
                CIBlockElement::Delete($arElement["ID"]);
            }
        }
    }
}

if (!function_exists("__RM_PrepateDate")) {
    function __RM_PrepateDate($time, $startTime, $endTime)
    {
        if ($time < $startTime) {
            $time = $startTime;
        }
        if ($time > $endTime) {
            $time = $endTime;
        }

        if ($time >= $endTime) {
            $timeDoW = 8;
        } else {
            $timeDoW = intval(date("w", $time));
            if ($timeDoW == 0) {
                $timeDoW = 7;
            }
        }

        $timeHour = intval(date("H", $time));
        $timeMinute = intval(date("i", $time));

        if ($timeMinute < 15) {
            $timeMinute = 0;
        } elseif ($timeMinute >= 15 && $timeMinute < 45) {
            $timeMinute = 30;
        } else {
            $timeHour++;
            $timeMinute = 0;
        }

        $time = mktime($timeHour, $timeMinute, 0, date("m", $time), date("d", $time), date("Y", $time));

        if ($time >= $endTime) {
            $timeDoW = 8;
        } else {
            $timeDoW = intval(date("w", $time));
            if ($timeDoW == 0) {
                $timeDoW = 7;
            }
        }

        $timeHour = intval(date("H", $time));
        $timeMinute = intval(date("i", $time));

        return ["Time" => $time, "DayOfWeek" => $timeDoW, "Hour" => $timeHour, "Minute" => $timeMinute];
    }

    function __RM_MkT($i)
    {
        $aMpM = IsAmPmMode();
        $h1 = intval($i / 2);
        if ($aMpM) {
            if ($h1 >= 12) {
                $mt1 = 'pm';
                if ($h1 > 12) {
                    $h1 -= 12;
                }
            } else {
                $mt1 = 'am';
            }
        } else {
            if ($h1 < 10) {
                $h1 = "0".$h1;
            }
        }

        
        $i1 = ($i % 2 != 0 ? "30" : "00");

        $h2 = intval(($i + 1) / 2);
        if ($aMpM) {
            if ($h2 >= 12) {
                $mt2 = 'pm';
                if ($h2 > 12) {
                    $h2 -= 12;
                }
            } else {
                $mt2 = 'am';
            }
        } else {
            if ($h2 < 10) {
                $h2 = "0".$h2;
            }
        }

        $i2 = (($i + 1) % 2 != 0 ? "30" : "00");

        return $h1.":".$i1.(!empty($mt1) ? ' '.$mt1: '')."-".$h2.":".$i2.(!empty($mt2) ? ' '.$mt2: '');
    }
}

if (mb_strlen($arResult["FatalError"]) <= 0) {
    $arResult["MEETING"] = $arMeeting;

    $arResult["CellClickUri"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_RESERVE_MEETING"], ["meeting_id" => $arMeeting["ID"], "item_id" => 0]);
    $arResult["CellClickUri"] .= HtmlSpecialCharsbx(strpos($arResult["CellClickUri"], "?") === false ? "?" : "&");

    if ($arParams["SET_TITLE"] == "Y") {
        $APPLICATION->SetTitle(GetMessage("INTASK_C36_PAGE_TITLE") . ": " . $arMeeting["NAME"]);
    }

    if ($arParams["SET_NAVCHAIN"] == "Y") {
        $APPLICATION->AddChainItem($arMeeting["NAME"]);
    }

    $arResult["WEEK_START"] = date($GLOBALS["DB"]->DateFormatToPHP(FORMAT_DATE), $weekTimeStart);
    $arResult["WEEK_END"] = date($GLOBALS["DB"]->DateFormatToPHP(FORMAT_DATE), $weekTimeEndPrint);
    $arResult["NEXT_WEEK"] = date($GLOBALS["DB"]->DateFormatToPHP(FORMAT_DATE), $weekTimeEnd);
    $arResult["PRIOR_WEEK"] = date($GLOBALS["DB"]->DateFormatToPHP(FORMAT_DATE), mktime(0, 0, 0, $weekMonth, $weekDay - 7, $weekYear));
    $arResult["WEEK_START_ARRAY"] = ["m" => $weekMonth, "d" => $weekDay, "Y" => $weekYear];

    $arResult["MEETING_URI"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MEETING"], ["meeting_id" => $arMeeting["ID"]]);
    $fl = (strpos($arResult["MEETING_URI"], "?") === false);
    $pwt = mktime(0, 0, 0, $weekMonth, $weekDay - 7, $weekYear);
    $arResult["PRIOR_WEEK_URI"] = $arResult["MEETING_URI"].($fl ? "?" : "&")."week_start=".date("Y", $pwt).date("m", $pwt).date("d", $pwt);
    $arResult["NEXT_WEEK_URI"] = $arResult["MEETING_URI"].($fl ? "?" : "&")."week_start=".date("Y", $weekTimeEnd).date("m", $weekTimeEnd).date("d", $weekTimeEnd);


    $dbElements = CIBlockElement::GetList(
        ["DATE_ACTIVE_FROM" => "ASC"],
        [
            "ACTIVE" => "Y",
            "IBLOCK_ID" => $arMeeting["IBLOCK_ID"],
            "SECTION_ID" => $arMeeting["ID"],
            "<DATE_ACTIVE_FROM" => date($GLOBALS["DB"]->DateFormatToPHP(FORMAT_DATE), $weekTimeEnd),
            ">=DATE_ACTIVE_TO" => date($GLOBALS["DB"]->DateFormatToPHP(FORMAT_DATE), $weekTimeStart),
            "PROPERTY_PERIOD_TYPE" => "NONE",
        ],
        false,
        false,
        []
    );

    $arResult["ITEMS"] = [];
    $arResult["ITEMS_MATRIX"] = [];
    $arResult["LIMITS"] = ["FROM" => 16, "TO" => 37];
    $arConflict = [];

    while ($arElement = $dbElements->GetNext()) {
        $arElement["CREATED_BY_NAME"] = "-";
        $dbUser = CUser::GetByID($arElement["CREATED_BY"]);
        if ($arUser = $dbUser->GetNext()) {
            $arElement["CREATED_BY_NAME"] = CUser::FormatName($arParams['NAME_TEMPLATE_WO_NOBR'], $arUser, $bUseLogin);
            $arElement["CREATED_BY_FIRST_NAME"] = $arUser["NAME"];
            $arElement["CREATED_BY_LAST_NAME"] = $arUser["LAST_NAME"];
            $arElement["CREATED_BY_SECOND_NAME"] = $arUser["SECOND_NAME"];
            $arElement["CREATED_BY_LOGIN"] = $arUser["LOGIN"];
        }

        if ($GLOBALS["USER"]->IsAuthorized()
            && ($GLOBALS["USER"]->IsAdmin()
                || count(array_intersect($GLOBALS["USER"]->GetUserGroupArray(), $arParams["USERGROUPS_CLEAR"])) > 0
                || $arElement["CREATED_BY"] == $GLOBALS["USER"]->GetID())) {
            $arElement["CLEAR_URI"] = $APPLICATION->GetCurPageParam("", ["clear_id"]);
            $arElement["CLEAR_URI"] .= (strpos($arElement["CLEAR_URI"], "?") === false ? "?" : "&")."clear_id=".$arElement["ID"]."&".bitrix_sessid_get();
        }

        $arElement["VIEW_ITEM_URI"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_VIEW_ITEM"], ["meeting_id" => $arMeeting["ID"], "item_id" => $arElement["ID"]]);
        $arElement["VIEW_ITEM_URI"] .= (strpos($arElement["VIEW_ITEM_URI"], "?") === false ? "?" : "&")."week_start=".urlencode($arResult["WEEK_START"]);

        if ($GLOBALS["USER"]->IsAuthorized()
            && ($GLOBALS["USER"]->IsAdmin() || $arElement["CREATED_BY"] == $GLOBALS["USER"]->GetID())) {
            $arElement["EDIT_ITEM_URI"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_RESERVE_MEETING"], ["meeting_id" => $arMeeting["ID"], "item_id" => $arElement["ID"]]);
            $arElement["EDIT_ITEM_URI"] .= (strpos($arElement["EDIT_ITEM_URI"], "?") === false ? "?" : "&")."week_start=".urlencode($arResult["WEEK_START"]);
        }

        $arResult["ITEMS"][$arElement["ID"]] = $arElement;

        $from = $arElement["DATE_ACTIVE_FROM"];
        $to = $arElement["DATE_ACTIVE_TO"];

        $fromTime = MakeTimeStamp($from, FORMAT_DATETIME);
        $toTime = MakeTimeStamp($to, FORMAT_DATETIME);

        if (IsAmPmMode()) {
            $arResult["ITEMS"][$arElement["ID"]]["DATE_ACTIVE_FROM_TIME"] = date("g:i a", $fromTime);
            $arResult["ITEMS"][$arElement["ID"]]["DATE_ACTIVE_TO_TIME"] = date("g:i a", $toTime);
        } else {
            $arResult["ITEMS"][$arElement["ID"]]["DATE_ACTIVE_FROM_TIME"] = date("H:i", $fromTime);
            $arResult["ITEMS"][$arElement["ID"]]["DATE_ACTIVE_TO_TIME"] = date("H:i", $toTime);
        }

        $from = __RM_PrepateDate($fromTime, $weekTimeStart, $weekTimeEnd);
        $to = __RM_PrepateDate($toTime, $weekTimeStart, $weekTimeEnd);

        if ($from["DayOfWeek"] == $to["DayOfWeek"]) {
            $i1 = $from["Hour"] * 2;
            if ($from["Minute"] == 30) {
                $i1++;
            }

            $i2 = $to["Hour"] * 2;
            if ($to["Minute"] == 30) {
                $i2++;
            }

            if ($i1 < $arResult["LIMITS"]["FROM"]) {
                $arResult["LIMITS"]["FROM"] = $i1;
            }
            if ($i2 > $arResult["LIMITS"]["TO"]) {
                $arResult["LIMITS"]["TO"] = $i2;
            }

            for ($i = $i1; $i < $i2; $i++) {
                if ($arResult["ITEMS_MATRIX"][$from["DayOfWeek"]][$i]) {
                    $cId = $arResult["ITEMS"][$arResult["ITEMS_MATRIX"][$from["DayOfWeek"]][$i]];
                    if (!in_array($arElement["ID"]."-".$cId["ID"], $arConflict)) {
                        $arResult["ErrorMessage"] .= str_replace(
                            ["#TIME#", "#RES1#", "#RES2#"],
                            [
                                date($GLOBALS["DB"]->DateFormatToPHP(FORMAT_DATE), $from["Time"])." ".__RM_MkT($i),
                                $cId["NAME"],
                                $arElement["NAME"],
                            ],
                            GetMessage("INTASK_C25_CONFLICT1").". "
                        );
                        $arConflict[] = $arElement["ID"]."-".$cId["ID"];
                    }
                } else {
                    $arResult["ITEMS_MATRIX"][$from["DayOfWeek"]][$i] = $arElement["ID"];
                }
            }
        } else {
            for ($i = $from["DayOfWeek"]; $i <= $to["DayOfWeek"]; $i++) {
                if ($i == $from["DayOfWeek"]) {
                    $j1 = $from["Hour"] * 2;
                    if ($from["Minute"] == 30) {
                        $j1++;
                    }
                    $j2 = 48;

                    if ($j1 < $arResult["LIMITS"]["FROM"]) {
                        $arResult["LIMITS"]["FROM"] = $j1;
                    }
                } elseif ($i == $to["DayOfWeek"]) {
                    $j1 = 0;
                    $j2 = $to["Hour"] * 2;
                    if ($to["Minute"] == 30) {
                        $j2++;
                    }

                    if ($j2 > $arResult["LIMITS"]["TO"]) {
                        $arResult["LIMITS"]["TO"] = $j2;
                    }
                } else {
                    $j1 = 0;
                    $j2 = 48;
                }
                $arResult["LIMITS"]["FROM"] = 0;
                $arResult["LIMITS"]["TO"] = 48;

                for ($j = $j1; $j < $j2; $j++) {
                    if ($arResult["ITEMS_MATRIX"][$i][$j]) {
                        $cId = $arResult["ITEMS"][$arResult["ITEMS_MATRIX"][$i][$j]];
                        if (!in_array($arElement["ID"]."-".$cId["ID"], $arConflict)) {
                            $arResult["ErrorMessage"] .= str_replace(
                                ["#TIME#", "#RES1#", "#RES2#"],
                                [
                                    date($GLOBALS["DB"]->DateFormatToPHP(FORMAT_DATE), mktime(0, 0, 0, $weekMonth, $weekDay + $i - 1, $weekYear))." ".__RM_MkT($j),
                                    $cId["NAME"],
                                    $arElement["NAME"],
                                ],
                                GetMessage("INTASK_C25_CONFLICT2").". "
                            );
                            $arConflict[] = $arElement["ID"]."-".$cId["ID"];
                        }
                    } else {
                        $arResult["ITEMS_MATRIX"][$i][$j] = $arElement["ID"];
                    }
                }
            }
        }
    }

    // Period
    $arMonthlyPeriods = [];
    if (date("n", $weekTimeStart) == date("n", $weekTimeEnd)) {
        $arMonthlyPeriods = [
            0 => [
                "year" => date("Y", $weekTimeStart),
                "month" => date("n", $weekTimeStart),
                "from" => date("j", $weekTimeStart),
                "to" => date("j", $weekTimeEnd) - 1,
            ],
        ];
    } else {
        $arMonthlyPeriods = [
            0 => [
                "year" => date("Y", $weekTimeStart),
                "month" => date("n", $weekTimeStart),
                "from" => date("j", $weekTimeStart),
                "to" => date("t", $weekTimeStart),
            ],
            1 => [
                "year" => date("Y", $weekTimeEnd),
                "month" => date("n", $weekTimeEnd),
                "from" => 1,
                "to" => date("j", $weekTimeEnd) - 1,
            ],
        ];
    }

    $dbElements = CIBlockElement::GetList(
        ["DATE_ACTIVE_FROM" => "ASC"],
        [
            "ACTIVE" => "Y",
            "IBLOCK_ID" => $arMeeting["IBLOCK_ID"],
            "SECTION_ID" => $arMeeting["ID"],
            "<DATE_ACTIVE_FROM" => date($GLOBALS["DB"]->DateFormatToPHP(FORMAT_DATE), $weekTimeEnd),
            ">=DATE_ACTIVE_TO" => date($GLOBALS["DB"]->DateFormatToPHP(FORMAT_DATE), $weekTimeStart),
            "!PROPERTY_PERIOD_TYPE" => "NONE",
        ],
        false,
        false,
        ["ID", "IBLOCK_ID", "NAME", "DATE_ACTIVE_FROM", "DATE_ACTIVE_TO", "CREATED_BY", "PROPERTY_PERIOD_TYPE", "PROPERTY_PERIOD_COUNT", "PROPERTY_EVENT_LENGTH", "PROPERTY_PERIOD_ADDITIONAL"]
    );

    while ($arElement = $dbElements->GetNext()) {
        $arDates = [];

        $from = $arElement["DATE_ACTIVE_FROM"];
        $to = $arElement["DATE_ACTIVE_TO"];

        $fromTime = MakeTimeStamp($from, FORMAT_DATETIME);
        $toTime = MakeTimeStamp($to, FORMAT_DATETIME);

        $fromTimeDateOnly = mktime(0, 0, 0, date("n", $fromTime), date("j", $fromTime), date("Y", $fromTime));
        $toTimeDateOnly = mktime(0, 0, 0, date("n", $toTime), date("j", $toTime), date("Y", $toTime));

        if ($arElement["PROPERTY_PERIOD_TYPE_VALUE"] == "DAILY") {
            $arElement["PROPERTY_PERIOD_COUNT_VALUE"] = intval($arElement["PROPERTY_PERIOD_COUNT_VALUE"]);
            if ($arElement["PROPERTY_PERIOD_COUNT_VALUE"] <= 0) {
                $arElement["PROPERTY_PERIOD_COUNT_VALUE"] = 1;
            }

            if ($weekTimeStart > $fromTime || $weekTimeEnd <= $fromTime) {
                $dayDiff = date_diff(
                    date_create_from_format('U', $weekTimeStart),
                    date_create_from_format('U', $fromTimeDateOnly)
                );
                $dayShift = $dayDiff->format('%a') % $arElement["PROPERTY_PERIOD_COUNT_VALUE"];
                if ($dayShift > 0) {
                    $dayShift = $arElement["PROPERTY_PERIOD_COUNT_VALUE"] - $dayShift;
                }

                $fromTimeTmp = mktime(
                    date("H", $fromTime),
                    date("i", $fromTime),
                    date("s", $fromTime),
                    date("n", $weekTimeStart),
                    date("j", $weekTimeStart) + $dayShift,
                    date("Y", $weekTimeStart)
                );
            } else {
                $fromTimeTmp = $fromTime;
            }

            while ($fromTimeTmp < $weekTimeEnd && $fromTimeTmp < $toTime) {
                $toTimeTmp = $fromTimeTmp + $arElement["PROPERTY_EVENT_LENGTH_VALUE"];
                $arDates[] = [
                    "DATE_ACTIVE_FROM" => $fromTimeTmp,
                    "DATE_ACTIVE_TO" => $toTimeTmp,
                ];

                $fromTimeTmp = strtotime(sprintf('+%u days', $arElement["PROPERTY_PERIOD_COUNT_VALUE"]), $fromTimeTmp);
            }
        } elseif ($arElement["PROPERTY_PERIOD_TYPE_VALUE"] == "WEEKLY") {
            $arElement["PROPERTY_PERIOD_COUNT_VALUE"] = intval($arElement["PROPERTY_PERIOD_COUNT_VALUE"]);
            if ($arElement["PROPERTY_PERIOD_COUNT_VALUE"] <= 0) {
                $arElement["PROPERTY_PERIOD_COUNT_VALUE"] = 1;
            }

            $arPeriodAdditional = [];
            if ($arElement["PROPERTY_PERIOD_ADDITIONAL_VALUE"] != '') {
                $arPeriodAdditionalTmp = explode(",", $arElement["PROPERTY_PERIOD_ADDITIONAL_VALUE"]);
                foreach ($arPeriodAdditionalTmp as $v) {
                    $v = intval($v);
                    if ($v >= 0) {
                        $arPeriodAdditional[] = $v;
                    }
                }
            }
            if (count($arPeriodAdditional) <= 0) {
                $w = date("w", $fromTime);
                $arPeriodAdditional[] = ($w == 0 ? 6 : $w - 1);
            }

            if ($arElement["PROPERTY_PERIOD_COUNT_VALUE"] > 1) {
                if ($weekTimeStart > $fromTime || $weekTimeEnd <= $fromTime) {
                    $wdw = intval(date("w", $fromTime));
                    if ($wdw == 0) {
                        $wdw = 7;
                    }

                    $wd = date("j", $fromTime) - $wdw + 1;
                    $wts = mktime(0, 0, 0, date("n", $fromTime), $wd, date("Y", $fromTime));

                    $dayDiff = date_diff(
                        date_create_from_format('U', $weekTimeStart),
                        date_create_from_format('U', $wts)
                    );
                    $weekShift = $dayDiff->format('%a') / 7;

                    if ($weekShift % $arElement["PROPERTY_PERIOD_COUNT_VALUE"] != 0) {
                        continue;
                    }
                }
            }

            foreach ($arPeriodAdditional as $w) {
                $fromTimeTmp = mktime(date("H", $fromTime), date("i", $fromTime), date("s", $fromTime), date("n", $weekTimeStart), date("j", $weekTimeStart) + $w, date("Y", $weekTimeStart));

                if ($fromTime > $fromTimeTmp || $toTime < $fromTimeTmp) {
                    continue;
                }

                $arDates[] = [
                    "DATE_ACTIVE_FROM" => $fromTimeTmp,
                    "DATE_ACTIVE_TO" => $fromTimeTmp + $arElement["PROPERTY_EVENT_LENGTH_VALUE"],
                ];
            }
        } elseif ($arElement["PROPERTY_PERIOD_TYPE_VALUE"] == "MONTHLY") {
            $arElement["PROPERTY_PERIOD_COUNT_VALUE"] = intval($arElement["PROPERTY_PERIOD_COUNT_VALUE"]);
            if ($arElement["PROPERTY_PERIOD_COUNT_VALUE"] <= 0) {
                $arElement["PROPERTY_PERIOD_COUNT_VALUE"] = 1;
            }

            $arPeriod = false;
            $dm = date("j", $fromTime);
            foreach ($arMonthlyPeriods as $arP) {
                if ($arP["from"] <= $dm && $arP["to"] >= $dm) {
                    $arPeriod = $arP;
                    break;
                }
            }

            if (!$arPeriod) {
                continue;
            }

            if ($arElement["PROPERTY_PERIOD_COUNT_VALUE"] > 1) {
                $nm = 0;
                if ($arPeriod["year"] == date("Y", $fromTime)) {
                    $nm += $arPeriod["month"] - date("n", $fromTime);
                } else {
                    $nm += 12 - date("n", $fromTime);
                    if ($arPeriod["year"] != date("Y", $fromTime) + 1) {
                        $nm += ($arPeriod["year"] - date("Y", $fromTime) - 1) * 12;
                    }
                    $nm += $arPeriod["month"];
                }

                if ($nm % $arElement["PROPERTY_PERIOD_COUNT_VALUE"] != 0) {
                    continue;
                }
            }

            $fromTimeTmp = mktime(date("H", $fromTime), date("i", $fromTime), date("s", $fromTime), $arPeriod["month"], date("j", $fromTime), $arPeriod["year"]);

            $arDates[] = [
                "DATE_ACTIVE_FROM" => $fromTimeTmp,
                "DATE_ACTIVE_TO" => $fromTimeTmp + $arElement["PROPERTY_EVENT_LENGTH_VALUE"],
            ];
        } elseif ($arElement["PROPERTY_PERIOD_TYPE_VALUE"] == "YEARLY") {
            $arElement["PROPERTY_PERIOD_COUNT_VALUE"] = intval($arElement["PROPERTY_PERIOD_COUNT_VALUE"]);
            if ($arElement["PROPERTY_PERIOD_COUNT_VALUE"] <= 0) {
                $arElement["PROPERTY_PERIOD_COUNT_VALUE"] = 1;
            }

            $arPeriod = false;
            $dm = date("j", $fromTime);
            $my = date("n", $fromTime);
            foreach ($arMonthlyPeriods as $arP) {
                if ($my == $arP["month"] && $arP["from"] <= $dm && $arP["to"] >= $dm) {
                    $arPeriod = $arP;
                    break;
                }
            }

            if (!$arPeriod) {
                continue;
            }

            if ($arElement["PROPERTY_PERIOD_COUNT_VALUE"] > 1) {
                if (($arPeriod["year"] - date("Y", $fromTime)) % $arElement["PROPERTY_PERIOD_COUNT_VALUE"] != 0) {
                    continue;
                }
            }

            $fromTimeTmp = mktime(date("H", $fromTime), date("i", $fromTime), date("s", $fromTime), date("n", $fromTime), date("j", $fromTime), $arPeriod["year"]);

            $arDates[] = [
                "DATE_ACTIVE_FROM" => $fromTimeTmp,
                "DATE_ACTIVE_TO" => $fromTimeTmp + $arElement["PROPERTY_EVENT_LENGTH_VALUE"],
            ];
        }

        $arElement["CREATED_BY_NAME"] = "-";
        $dbUser = CUser::GetByID($arElement["CREATED_BY"]);
        if ($arUser = $dbUser->GetNext()) {
            $arElement["CREATED_BY_NAME"] = CUser::FormatName($arParams['NAME_TEMPLATE_WO_NOBR'], $arUser, $bUseLogin);
            $arElement["CREATED_BY_FIRST_NAME"] = $arUser["NAME"];
            $arElement["CREATED_BY_LAST_NAME"] = $arUser["LAST_NAME"];
            $arElement["CREATED_BY_SECOND_NAME"] = $arUser["SECOND_NAME"];
            $arElement["CREATED_BY_LOGIN"] = $arUser["LOGIN"];
        }

        if ($GLOBALS["USER"]->IsAuthorized()
            && ($GLOBALS["USER"]->IsAdmin()
                || count(array_intersect($GLOBALS["USER"]->GetUserGroupArray(), $arParams["USERGROUPS_CLEAR"])) > 0
                || $arElement["CREATED_BY"] == $GLOBALS["USER"]->GetID())) {
            $arElement["CLEAR_URI"] = $APPLICATION->GetCurPageParam("", ["clear_id"]);
            $arElement["CLEAR_URI"] .= (strpos($arElement["CLEAR_URI"], "?") === false ? "?" : "&")."clear_id=".$arElement["ID"]."&".bitrix_sessid_get();
        }

        $arElement["VIEW_ITEM_URI"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_VIEW_ITEM"], ["meeting_id" => $arMeeting["ID"], "item_id" => $arElement["ID"]]);
        $arElement["VIEW_ITEM_URI"] .= (strpos($arElement["VIEW_ITEM_URI"], "?") === false ? "?" : "&")."week_start=".urlencode($arResult["WEEK_START"]);

        if ($GLOBALS["USER"]->IsAuthorized()
            && ($GLOBALS["USER"]->IsAdmin() || $arElement["CREATED_BY"] == $GLOBALS["USER"]->GetID())) {
            $arElement["EDIT_ITEM_URI"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_RESERVE_MEETING"], ["meeting_id" => $arMeeting["ID"], "item_id" => $arElement["ID"]]);
            $arElement["EDIT_ITEM_URI"] .= (strpos($arElement["EDIT_ITEM_URI"], "?") === false ? "?" : "&")."week_start=".urlencode($arResult["WEEK_START"]);
        }

        for ($counter = 0; $counter < count($arDates); $counter++) {
            //echo Date("d.m.Y H:i:s", $arDates[$counter]["DATE_ACTIVE_FROM"])." - ".Date("d.m.Y H:i:s", $arDates[$counter]["DATE_ACTIVE_TO"])."<br>";

            $arResult["ITEMS"][$arElement["ID"]."-".$counter] = $arElement;

            $arResult["ITEMS"][$arElement["ID"]."-".$counter]["DATE_ACTIVE_FROM_TIME"] = date("H:i", $arDates[$counter]["DATE_ACTIVE_FROM"]);
            $arResult["ITEMS"][$arElement["ID"]."-".$counter]["DATE_ACTIVE_TO_TIME"] = date("H:i", $arDates[$counter]["DATE_ACTIVE_TO"]);

            $from = __RM_PrepateDate($arDates[$counter]["DATE_ACTIVE_FROM"], $weekTimeStart, $weekTimeEnd);
            $to = __RM_PrepateDate($arDates[$counter]["DATE_ACTIVE_TO"], $weekTimeStart, $weekTimeEnd);

            if ($from["DayOfWeek"] == $to["DayOfWeek"]) {
                $i1 = $from["Hour"] * 2;
                if ($from["Minute"] == 30) {
                    $i1++;
                }

                $i2 = $to["Hour"] * 2;
                if ($to["Minute"] == 30) {
                    $i2++;
                }

                if ($i1 < $arResult["LIMITS"]["FROM"]) {
                    $arResult["LIMITS"]["FROM"] = $i1;
                }
                if ($i2 > $arResult["LIMITS"]["TO"]) {
                    $arResult["LIMITS"]["TO"] = $i2;
                }

                for ($i = $i1; $i < $i2; $i++) {
                    if ($arResult["ITEMS_MATRIX"][$from["DayOfWeek"]][$i]) {
                        $cId = $arResult["ITEMS"][$arResult["ITEMS_MATRIX"][$from["DayOfWeek"]][$i]];
                        if (!in_array($arElement["ID"]."-".$counter."-".$cId["ID"], $arConflict)) {
                            $arResult["ErrorMessage"] .= str_replace(
                                ["#TIME#", "#RES1#", "#RES2#"],
                                [
                                    date($GLOBALS["DB"]->DateFormatToPHP(FORMAT_DATE), $from["Time"])." ".__RM_MkT($i),
                                    $cId["NAME"],
                                    $arElement["NAME"],
                                ],
                                GetMessage("INTASK_C25_CONFLICT1").". "
                            );
                            $arConflict[] = $arElement["ID"]."-".$counter."-".$cId["ID"];
                        }
                    } else {
                        $arResult["ITEMS_MATRIX"][$from["DayOfWeek"]][$i] = $arElement["ID"]."-".$counter;
                    }
                }
            } else {
                for ($i = $from["DayOfWeek"]; $i <= $to["DayOfWeek"]; $i++) {
                    if ($i == $from["DayOfWeek"]) {
                        $j1 = $from["Hour"] * 2;
                        if ($from["Minute"] == 30) {
                            $j1++;
                        }
                        $j2 = 48;

                        if ($j1 < $arResult["LIMITS"]["FROM"]) {
                            $arResult["LIMITS"]["FROM"] = $j1;
                        }
                    } elseif ($i == $to["DayOfWeek"]) {
                        $j1 = 0;
                        $j2 = $to["Hour"] * 2;
                        if ($to["Minute"] == 30) {
                            $j2++;
                        }

                        if ($j2 > $arResult["LIMITS"]["TO"]) {
                            $arResult["LIMITS"]["TO"] = $j2;
                        }
                    } else {
                        $j1 = 0;
                        $j2 = 48;
                    }
                    $arResult["LIMITS"]["FROM"] = 0;
                    $arResult["LIMITS"]["TO"] = 48;

                    for ($j = $j1; $j < $j2; $j++) {
                        if ($arResult["ITEMS_MATRIX"][$i][$j]) {
                            $cId = $arResult["ITEMS"][$arResult["ITEMS_MATRIX"][$i][$j]];
                            if (!in_array($arElement["ID"]."-".$counter."-".$cId["ID"], $arConflict)) {
                                $arResult["ErrorMessage"] .= str_replace(
                                    ["#TIME#", "#RES1#", "#RES2#"],
                                    [
                                        date($GLOBALS["DB"]->DateFormatToPHP(FORMAT_DATE), mktime(0, 0, 0, $weekMonth, $weekDay + $i - 1, $weekYear))." ".__RM_MkT($j),
                                        $cId["NAME"],
                                        $arElement["NAME"],
                                    ],
                                    GetMessage("INTASK_C25_CONFLICT2").". "
                                );
                                $arConflict[] = $arElement["ID"]."-".$counter."-".$cId["ID"];
                            }
                        } else {
                            $arResult["ITEMS_MATRIX"][$i][$j] = $arElement["ID"]."-".$counter;
                        }
                    }
                }
            }
        }
    }
    // End Period

    $ar = [];
    foreach ($arParams["WEEK_HOLIDAYS"] as $v) {
        if (!array_key_exists($v + 1, $arResult["ITEMS_MATRIX"])) {
            $ar[] = $v;
        }
    }
    $arParams["WEEK_HOLIDAYS"] = $ar;
}

$this->IncludeComponentTemplate();
