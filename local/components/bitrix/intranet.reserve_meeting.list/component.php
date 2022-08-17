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

$iblockId = trim($arParams["IBLOCK_ID"]);

$arParams["PAGE_VAR"] = trim($arParams["PAGE_VAR"]);
if (mb_strlen($arParams["PAGE_VAR"]) <= 0) {
    $arParams["PAGE_VAR"] = "page";
}

$arParams["MEETING_VAR"] = trim($arParams["MEETING_VAR"]);
if (mb_strlen($arParams["MEETING_VAR"]) <= 0) {
    $arParams["MEETING_VAR"] = "meeting_id";
}

$arParams["PATH_TO_MEETING_LIST"] = trim($arParams["PATH_TO_MEETING_LIST"]);
if (mb_strlen($arParams["PATH_TO_MEETING_LIST"]) <= 0) {
    $arParams["PATH_TO_MEETING_LIST"] = HtmlSpecialCharsbx($APPLICATION->GetCurPage());
}

$arParams["PATH_TO_MEETING"] = trim($arParams["PATH_TO_MEETING"]);
if (mb_strlen($arParams["PATH_TO_MEETING"]) <= 0) {
    $arParams["PATH_TO_MEETING"] = HtmlSpecialCharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=meeting&".$arParams["MEETING_VAR"]."=#meeting_id#");
}

$arParams["PATH_TO_RESERVE_MEETING"] = trim($arParams["PATH_TO_RESERVE_MEETING"]);
if (mb_strlen($arParams["PATH_TO_RESERVE_MEETING"]) <= 0) {
    $arParams["PATH_TO_RESERVE_MEETING"] = HtmlSpecialCharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=reserve_meeting&".$arParams["MEETING_VAR"]."=#meeting_id#&".$arParams["ITEM_VAR"]."=#item_id#");
}

$arParams["PATH_TO_MODIFY_MEETING"] = trim($arParams["PATH_TO_MODIFY_MEETING"]);
if (mb_strlen($arParams["PATH_TO_MODIFY_MEETING"]) <= 0) {
    $arParams["PATH_TO_MODIFY_MEETING"] = HtmlSpecialCharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=modify_meeting&".$arParams["MEETING_VAR"]."=#meeting_id#");
}

$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "Y" ? "Y" : "N");
$arParams["SET_NAVCHAIN"] = ($arParams["SET_NAVCHAIN"] == "Y" ? "Y" : "N");

if (!is_array($arParams["USERGROUPS_MODIFY"])) {
    if ((int)$arParams["USERGROUPS_MODIFY"] > 0) {
        $arParams["USERGROUPS_MODIFY"] = [$arParams["USERGROUPS_MODIFY"]];
    } else {
        $arParams["USERGROUPS_MODIFY"] = [];
    }
}

if (!is_array($arParams["USERGROUPS_RESERVE"])) {
    if ((int)$arParams["USERGROUPS_RESERVE"] > 0) {
        $arParams["USERGROUPS_RESERVE"] = [$arParams["USERGROUPS_RESERVE"]];
    } else {
        $arParams["USERGROUPS_RESERVE"] = [];
    }
}

$arResult["FatalError"] = "";

if (!CIBlockRights::UserHasRightTo($iblockId, $iblockId, 'element_read')) {
    $arResult["FatalError"] .= GetMessage("INTS_NO_IBLOCK_PERMS").".";
}

if (mb_strlen($arResult["FatalError"]) <= 0) {
    $deleteMeetingId = (int)$_REQUEST["delete_meeting_id"];

    if ($deleteMeetingId > 0 &&
        check_bitrix_sessid() &&
        $GLOBALS["USER"]->IsAuthorized() &&
        ($GLOBALS["USER"]->IsAdmin() || count(array_intersect($GLOBALS["USER"]->GetUserGroupArray(), $arParams["USERGROUPS_MODIFY"])) > 0)) {
        $dbMeetingsList = CIBlockSection::GetList(
            [],
            ["IBLOCK_ID" => $iblockId, "ID" => $deleteMeetingId]
        );
        if ($arMeeting = $dbMeetingsList->Fetch()) {
            CIBlockSection::Delete($arMeeting["ID"]);
        }
    }
}

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

if ($arParams["SET_TITLE"] == "Y") {
    $APPLICATION->SetTitle(GetMessage("INTASK_C36_PAGE_TITLE"));
}

if ($arParams["SET_NAVCHAIN"] == "Y") {
    $APPLICATION->AddChainItem(GetMessage("INTASK_C36_PAGE_TITLE"));
}

if (mb_strlen($arResult["FatalError"]) <= 0) {
    for ($i = 0; $i < 3; $i++) {
        $orderBy = (array_key_exists("order_by_".$i, $_REQUEST) ? $_REQUEST["order_by_".$i] : $arParams["ORDER_BY_".$i]);
        $orderDir = (array_key_exists("order_dir_".$i, $_REQUEST) ? $_REQUEST["order_dir_".$i] : $arParams["ORDER_DIR_".$i]);

        $orderBy = mb_strtoupper(trim($orderBy));
        if (array_key_exists($orderBy, $arResult["ALLOWED_FIELDS"]) && $arResult["ALLOWED_FIELDS"][$orderBy]["ORDERABLE"]) {
            $arParams["ORDER_BY_".$i] = $orderBy;
            $arParams["ORDER_DIR_".$i] = mb_strtoupper(trim($orderDir));
            if (!in_array($arParams["ORDER_DIR_".$i], ["ASC", "DESC"])) {
                $arParams["ORDER_DIR_".$i] = "ASC";
            }
        } else {
            $arParams["ORDER_BY_".$i] = "";
            $arParams["ORDER_DIR_".$i] = "";
        }
    }

    foreach ($arParams as $key => $value) {
        if (mb_strtoupper(mb_substr($key, 0, 4)) != "FLT_") {
            continue;
        }
        if (!is_array($value) && mb_strlen($value) <= 0 || is_array($value) && count($value) <= 0) {
            continue;
        }

        $key = mb_strtoupper(mb_substr($key, 4));

        $op = "";
        $opTmp = mb_substr($key, 0, 1);
        if (in_array($opTmp, ["!", "<", ">"])) {
            $op = $opTmp;
            $key = mb_substr($key, 1);
        }

        if (array_key_exists($key, $arResult["ALLOWED_FIELDS"]) && $arResult["ALLOWED_FIELDS"][$key]["FILTERABLE"]) {
            if ($arResult["ALLOWED_FIELDS"][$key]["TYPE"] == "datetime") {
                if ($value == "current") {
                    $arParams["FILTER"][$op.$key] = date($GLOBALS["DB"]->DateFormatToPHP(FORMAT_DATE));
                } else {
                    $arParams["FILTER"][$op.$key] = $value;
                }
            } elseif ($arResult["ALLOWED_FIELDS"][$key]["TYPE"] == "user") {
                if ($value == "current") {
                    $arParams["FILTER"][$op.$key] = $GLOBALS["USER"]->GetID();
                } else {
                    $arParams["FILTER"][$op.$key] = $value;
                }
            } else {
                $arParams["FILTER"][$op.$key] = $value;
            }
        }
    }

    foreach ($_REQUEST as $key => $value) {
        if (mb_strtoupper(mb_substr($key, 0, 4)) != "FLT_") {
            continue;
        }
        if (!is_array($value) && mb_strlen($value) <= 0 || is_array($value) && count($value) <= 0) {
            continue;
        }

        $key = mb_strtoupper(mb_substr($key, 4));

        $op = "";
        $opTmp = mb_substr($key, 0, 1);
        if (in_array($opTmp, ["!", "<", ">"])) {
            $op = $opTmp;
            $key = mb_substr($key, 1);
        }

        if (array_key_exists($key, $arResult["ALLOWED_FIELDS"]) && $arResult["ALLOWED_FIELDS"][$key]["FILTERABLE"]) {
            if ($arResult["ALLOWED_FIELDS"][$key]["TYPE"] == "datetime") {
                if ($value == "current") {
                    $arParams["FILTER"][$op . $key] = date($GLOBALS["DB"]->DateFormatToPHP(FORMAT_DATE));
                } else {
                    $arParams["FILTER"][$op . $key] = $value;
                }
            } elseif ($arResult["ALLOWED_FIELDS"][$key]["TYPE"] == "user") {
                if ($value == "current") {
                    $arParams["FILTER"][$op . $key] = $GLOBALS["USER"]->GetID();
                } else {
                    $arParams["FILTER"][$op . $key] = $value;
                }
            } else {
                $arParams["FILTER"][$op.$key] = $value;
            }
        }
    }
}

if (mb_strlen($arResult["FatalError"]) <= 0) {
    $arOrderBy = [];
    for ($i = 0; $i < 3; $i++) {
        if (mb_strlen($arParams["ORDER_BY_".$i]) <= 0) {
            continue;
        }
        
        $orderBy = $arParams["ORDER_BY_".$i];

        if (array_key_exists($orderBy, $arResult["ALLOWED_FIELDS"]) && $arResult["ALLOWED_FIELDS"][$orderBy]["ORDERABLE"]) {
            $arParams["ORDER_DIR_".$i] = (strtoupper($arParams["ORDER_DIR_".$i]) == "ASC" ? "ASC" : "DESC");
            $arOrderBy[$orderBy] = $arParams["ORDER_DIR_".$i];
        }
    }

    if (count($arOrderBy) <= 0) {
        $arOrderBy["NAME"] = "ASC";
        $arOrderBy["ID"] = "DESC";
    }

    $arFilter = ["IBLOCK_ID" => $iblockId, "ACTIVE" => "Y"];

    if (is_array($arParams["FILTER"])) {
        foreach ($arParams["FILTER"] as $key => $value) {
            $op = "";
            $opTmp = mb_substr($key, 0, 1);
            if (in_array($opTmp, ["!", "<", ">"])) {
                $op = $opTmp;
                $key = mb_substr($key, 1);
            }

            if (array_key_exists($key, $arResult["ALLOWED_FIELDS"]) && $arResult["ALLOWED_FIELDS"][$key]["FILTERABLE"]) {
                $arFilter[$op . $key] = $value;
            }
        }
    }
}

if (mb_strlen($arResult["FatalError"]) <= 0) {
    $arNavStartParams = ["nPageSize" => $arParams["ITEMS_COUNT"], "bShowAll" => false, "bDescPageNumbering" => false];
    $arNavigation = CDBResult::GetNavParams($arNavStartParams);

    $arSelectFields = ["IBLOCK_ID"];
    foreach ($arResult["ALLOWED_FIELDS"] as $key => $value) {
        $arSelectFields[] = $key;
    }

    $arResult["MEETINGS_LIST"] = [];

    $dbMeetingsList = CIBlockSection::GetList(
        $arOrderBy,
        $arFilter,
        false,
        $arSelectFields
    );
    while ($arMeeting = $dbMeetingsList->GetNext()) {
        $arMeeting["URI"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MEETING"], ["meeting_id" => $arMeeting["ID"]]);

        $arMeeting["ACTIONS"] = [];
        $arMeeting["ACTIONS"][] = [
            "ICON" => "",
            "TITLE" => GetMessage("INTASK_C23_GRAPH"),
            "CONTENT" => "<b>".GetMessage("INTASK_C23_GRAPH_DESCR")."</b>",
            "ONCLICK" => "setTimeout(HideThisMenuS".$arMeeting["ID"].", 900); jsUtils.Redirect([], '".CUtil::JSEscape($arMeeting["URI"])."');",
        ];

        if ($GLOBALS["USER"]->IsAuthorized()
            && ($GLOBALS["USER"]->IsAdmin()
                || count(array_intersect($GLOBALS["USER"]->GetUserGroupArray(), $arParams["USERGROUPS_RESERVE"])) > 0)) {
            $arMeeting["ACTIONS"][] = [
                "ICON" => "",
                "TITLE" => GetMessage("INTASK_C23_RESERV"),
                "CONTENT" => GetMessage("INTASK_C23_RESERV_DESCR"),
                "ONCLICK" => "setTimeout(HideThisMenuS".$arMeeting["ID"].", 900); jsUtils.Redirect([], '".CUtil::JSEscape(CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_RESERVE_MEETING"], ["meeting_id" => $arMeeting["ID"], "item_id" => 0]))."');",
            ];
        }

        if ($GLOBALS["USER"]->IsAuthorized()
            && ($GLOBALS["USER"]->IsAdmin()
                || count(array_intersect($GLOBALS["USER"]->GetUserGroupArray(), $arParams["USERGROUPS_MODIFY"])) > 0)) {
            $arMeeting["ACTIONS"][] = [
                "ICON" => "",
                "TITLE" => GetMessage("INTASK_C23_EDIT"),
                "CONTENT" => GetMessage("INTASK_C23_EDIT_DESCR"),
                "ONCLICK" => "setTimeout(HideThisMenuS".$arMeeting["ID"].", 900); jsUtils.Redirect([], '".CUtil::JSEscape(CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MODIFY_MEETING"], ["meeting_id" => $arMeeting["ID"]]))."');",
            ];

            $p = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MEETING_LIST"], []);
            $p .= (strpos($p, "?") === false ? "?" : "&")."delete_meeting_id=".$arMeeting["ID"]."&".bitrix_sessid_get();

            $arMeeting["ACTIONS"][] = [
                "ICON" => "",
                "TITLE" => GetMessage("INTASK_C23_DELETE"),
                "CONTENT" => GetMessage("INTASK_C23_DELETE_DESCR"),
                "ONCLICK" => "if(confirm('".CUtil::JSEscape(GetMessage("INTASK_C23_DELETE_CONF"))."')){jsUtils.Redirect([], '".CUtil::JSEscape($p)."')};",
            ];
        }

        $arCurFiles = [];
        foreach ($arMeeting['UF_FILE'] as $k) {
            $arGetFile = CFile::GetFileArray($k);
            $arCurFiles[$arGetFile['ID']] = ['ORIGINAL_NAME' => $arGetFile['ORIGINAL_NAME']];
        }
        $arMeeting['arCurFiles'] = $arCurFiles;

        $arResult["MEETINGS_LIST"][] = $arMeeting;
    }
}

$this->IncludeComponentTemplate();
