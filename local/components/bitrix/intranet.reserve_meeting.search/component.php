<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

function vardump($vardump)
{
    echo "<pre>";
    var_dump($vardump);
    echo "</pre>";
}

if (!CModule::IncludeModule("intranet")) {
    return ShowError(GetMessage("EC_INTRANET_MODULE_NOT_INSTALLED"));
}
if (!CModule::IncludeModule("iblock")) {
    return ShowError(GetMessage("EC_IBLOCK_MODULE_NOT_INSTALLED"));
}

$iblockId = trim($arParams["IBLOCK_ID"]);

$arParams["PAGE_VAR"] = trim($arParams["PAGE_VAR"]);
if (mb_strlen($arParams["PAGE_VAR"]) <= 0) {
    $arParams["PAGE_VAR"] = "page";
}

$arParams["MEETING_VAR"] = trim($arParams["MEETING_VAR"]);
if (mb_strlen($arParams["MEETING_VAR"]) <= 0) {
    $arParams["MEETING_VAR"] = "meeting_id";
}

$arParams["PATH_TO_MEETING"] = trim($arParams["PATH_TO_MEETING"]);
if (mb_strlen($arParams["PATH_TO_MEETING"]) <= 0) {
    $arParams["PATH_TO_MEETING"] = HtmlSpecialCharsbx($APPLICATION->GetCurPage() . "?" . $arParams["PAGE_VAR"] . "=meeting&" . $arParams["MEETING_VAR"] . "=#meeting_id#");
}

$arParams["PATH_TO_RESERVE_MEETING"] = trim($arParams["PATH_TO_RESERVE_MEETING"]);
if (mb_strlen($arParams["PATH_TO_RESERVE_MEETING"]) <= 0) {
    $arParams["PATH_TO_RESERVE_MEETING"] = HtmlSpecialCharsbx($APPLICATION->GetCurPage() . "?" . $arParams["PAGE_VAR"] . "=reserve_meeting&" . $arParams["MEETING_VAR"] . "=#meeting_id#&" . $arParams["ITEM_VAR"] . "=#item_id#");
}

$arParams["ITEMS_COUNT"] = intval($arParams["ITEMS_COUNT"]);
if ($arParams["ITEMS_COUNT"] <= 0) {
    $arParams["ITEMS_COUNT"] = 20;
}

$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "Y" ? "Y" : "N");
$arParams["SET_NAVCHAIN"] = ($arParams["SET_NAVCHAIN"] == "Y" ? "Y" : "N");

$arResult["FatalError"] = "";

if (!CIBlockRights::UserHasRightTo($iblockId, $iblockId, 'element_read')) {
    $arResult["FatalError"] .= GetMessage("INTS_NO_IBLOCK_PERMS") . ".";
}

include_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/components/bitrix/intranet.reserve_meeting/init.php");

$ar = __IRM_InitReservation($iblockId);
$arResult["ALLOWED_FIELDS"] = $ar["ALLOWED_FIELDS"];
$arResult["CUSTOM_FIELDS"] = [
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
];
$arResult["ALLOWED_FIELDS"] = $arResult["ALLOWED_FIELDS"] + $arResult["CUSTOM_FIELDS"];

$arResult["ALLOWED_ITEM_PROPERTIES"] = $ar["ALLOWED_ITEM_PROPERTIES"];

if ($arParams["SET_TITLE"] == "Y") {
    $APPLICATION->SetTitle(GetMessage("INTASK_C36_PAGE_TITLE"));
}

if ($arParams["SET_NAVCHAIN"] == "Y") {
    $APPLICATION->AddChainItem(GetMessage("INTASK_C36_PAGE_TITLE"));
}

if (mb_strlen($arResult["FatalError"]) <= 0) {
    $arFilter = ["IBLOCK_ID" => $iblockId, "ACTIVE" => "Y"];
    foreach ($_REQUEST as $key => $value) {
        if ($value != 'NULL') {
            if (mb_strtoupper(mb_substr($key, 0, 4)) != "FLT_") {
                continue;
            }
            if (!is_array($value) && mb_strlen($value) <= 0 || is_array($value) && count($value) <= 0) {
                continue;
            }
            $key = mb_strtoupper(mb_substr($key, 4));
            if ($arResult['CUSTOM_FIELDS'][$key]['TYPE'] == 'double') {
                $value = explode(" - ", $value);
                if (empty($value[0]) || empty($value[1])) {
                    $_REQUEST['flt_'.strtolower($key)] = null;
                    continue;
                }
                $arFilter[] = [
                    "LOGIC" => "OR",
                    ["><".$key => [$value[0],  $value[1]]],
                    [$key => null]
                ];
            } else {
                $arFilter[$key] = $value;
            }
        }
    }

    $arSelectFields = ["IBLOCK_ID"];
    foreach ($arResult["ALLOWED_FIELDS"] as $key => $value) {
        $arSelectFields[] = $key;
    }

    $arResult["MEETINGS_LIST"] = [];
    $arMeetingId = [];

    $dbMeetingsList = CIBlockSection::GetList(
        [],
        $arFilter,
        false,
        $arSelectFields
    );
    while ($arMeeting = $dbMeetingsList->GetNext()) {
        $arResult["MEETINGS_LIST"][$arMeeting["ID"]] = $arMeeting;
        $arMeetingId[] = $arMeeting["ID"];
    }
}

if (mb_strlen($arResult["FatalError"]) <= 0) {
    $arResult["ITEMS"] = [];
    foreach ($arResult["MEETINGS_LIST"] as $key => $value) {
        $arResult["ITEMS"][] = [
            "MEETING_ID" => $key,
            "URI" => '?page=reserve_meeting&meeting_id='.$key
        ];
    }

    $arFilter = ["IBLOCK_ID" => $iblockId, "ACTIVE" => "Y"];
    $arSelectFields = ["IBLOCK_ID", "ID", "NAME", "UF_*"];
    $arResult["MEETINGS_ALL"] = [];
    $dbMeetingsList = CIBlockSection::GetList(
        [],
        $arFilter,
        false,
        $arSelectFields
    );
    while ($arMeeting = $dbMeetingsList->GetNext()) {
        $arResult["MEETINGS_ALL"][$arMeeting["ID"]]['NAME'] = $arMeeting["NAME"];
    }
}

foreach ($arResult["CUSTOM_FIELDS"] as $k => $v) {
    if (isset($arResult["CUSTOM_FIELDS"][$k]['SELECT_VALUES'])) {
        $obEnum = new \CUserFieldEnum;
        $rsEnum = $obEnum->GetList([], ["USER_FIELD_NAME" => $k]);
        while ($arEnum = $rsEnum->Fetch()) {
            $arResult['SELECT_TYPE'][$k][] = ['ID' => $arEnum['ID'], 'VALUE' => $arEnum['VALUE']];
        }
    }
}

$this->IncludeComponentTemplate();
