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

$meetingId = intval($arParams["MEETING_ID"]);
if ($meetingId <= 0) {
    $meetingId = intval($_REQUEST[$arParams["MEETING_VAR"]]);
}

$arParams["PATH_TO_MEETING"] = trim($arParams["PATH_TO_MEETING"]);
if (mb_strlen($arParams["PATH_TO_MEETING"]) <= 0) {
    $arParams["PATH_TO_MEETING"] = HtmlSpecialCharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=meeting&".$arParams["MEETING_VAR"]."=#meeting_id#");
}

$arParams["PATH_TO_MEETING_LIST"] = trim($arParams["PATH_TO_MEETING_LIST"]);
if (mb_strlen($arParams["PATH_TO_MEETING_LIST"]) <= 0) {
    $arParams["PATH_TO_MEETING_LIST"] = HtmlSpecialCharsbx($APPLICATION->GetCurPage());
}

$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "Y" ? "Y" : "N");
$arParams["SET_NAVCHAIN"] = ($arParams["SET_NAVCHAIN"] == "Y" ? "Y" : "N");

if (!is_array($arParams["USERGROUPS_MODIFY"])) {
    if (intval($arParams["USERGROUPS_MODIFY"]) > 0) {
        $arParams["USERGROUPS_MODIFY"] = [$arParams["USERGROUPS_MODIFY"]];
    } else {
        $arParams["USERGROUPS_MODIFY"] = [];
    }
}

$arResult["FatalError"] = "";

if (!CIBlockRights::UserHasRightTo($iblockId, $iblockId, 'element_read')) {
    $arResult["FatalError"] .= GetMessage("INTS_NO_IBLOCK_PERMS").".";
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

            $obUserField = new CUserTypeEntity();
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
            $obEnum = new \CUserFieldEnum();
            $rsEnum = $obEnum->GetList([], ["USER_FIELD_NAME" => $key]);
            while ($arEnum = $rsEnum->Fetch()) {
                if ($arUserFields[$key]['ID'] == $arEnum['USER_FIELD_ID']) {
                    $arResult['SELECT_TYPE'][$key][] = ['ID' => $arEnum['ID'], 'VALUE' => $arEnum['VALUE']];
                }
            }
        }
    }
}


if ($arParams["SET_TITLE"] == "Y") {
    $APPLICATION->SetTitle(GetMessage("INTASK_C36_PAGE_TITLE"));
}

if ($arParams["SET_NAVCHAIN"] == "Y") {
    $APPLICATION->AddChainItem(GetMessage("INTASK_C36_PAGE_TITLE1"), CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MEETING_LIST"], []));
}

if (!$GLOBALS["USER"]->IsAuthorized()) {
    $arResult["FatalError"] = GetMessage("INTASK_C36_SHOULD_AUTH").". ";
}

if (mb_strlen($arResult["FatalError"]) <= 0) {
    if (!$GLOBALS["USER"]->IsAdmin() && count(array_intersect($GLOBALS["USER"]->GetUserGroupArray(), $arParams["USERGROUPS_MODIFY"])) <= 0) {
        $arResult["FatalError"] = GetMessage("INTASK_C36_NO_PERMS2CREATE").". ";
    }
}

if (mb_strlen($arResult["FatalError"]) <= 0) {
    $arMeeting = false;

    if ($meetingId > 0) {
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

        $arCurFiles = [];
        foreach ($arMeeting['UF_FILE'] as $k) {
            $arGetFile = CFile::GetFileArray($k);
            $arCurFiles[$arGetFile['ID']] = ['ORIGINAL_NAME' => $arGetFile['ORIGINAL_NAME']];
        }
        $arResult['arCurFiles'] = $arCurFiles;

        if (!$arMeeting) {
            $arResult["FatalError"] = GetMessage("INAF_MEETING_NOT_FOUND")." ";
        }
    }
}

if (isset($_POST['deleteFiles'])) {
    foreach (json_decode($_POST['deleteFiles'], true) as $del) {
        CFile::Delete($del);
    }
}

if (mb_strlen($arResult["FatalError"]) <= 0) {
    $bVarsFromForm = false;
    if ($_SERVER["REQUEST_METHOD"] == "POST" && mb_strlen($_POST["save"]) > 0 && check_bitrix_sessid()) {
        $errorMessage = "";
        $arFields = [];
        foreach ($arResult["ALLOWED_FIELDS"] as $key => $item) {
            if ($key != 'ID') {
                $sName = mb_strtolower($key).'V';
                if ($key == 'UF_FILE') {
                    $$sName = $_POST['uf_file'];
                    $arFiles = [];
                    $arDelete = [];
                    foreach ($$sName as $it) {
                        array_push($arFiles, CFile::MakeFileArray($it));
                    }

                    foreach ($arResult['arCurFiles'] as $k2 => $it2) {
                        array_push($arFiles, CFile::MakeFileArray($k2));
                        array_push($arDelete, $k2);
                    }
                    $$sName = $arFiles;
                } else {
                    $$sName = $_REQUEST[strtolower($key)];
                }

                $arFields[$key] = $$sName;

                if ($item['MANDATORY'] == 'Y' && mb_strlen($$sName) <= 0) {
                    $errorMessage .= "Поле '".$item['NAME']. "' не заполнено <br>";
                }
            }
        }

        if (mb_strlen($errorMessage) <= 0) {
            $sanitizer = new \CBXSanitizer();
            $sanitizer->setLevel(\CBXSanitizer::SECURE_LEVEL_LOW);
            $sanitizer->applyHtmlSpecChars(true);
            $sanitizer->deleteSanitizedTags(false);

            $arFields["ACTIVE"] = 'Y';
            $arFields["IBLOCK_ID"] = $iblockId;
            $arFields["IBLOCK_SECTION_ID"] = 0;

            $iblockSectionObject = new CIBlockSection();

            if ($arMeeting) {
                $res = $iblockSectionObject->Update($meetingId, $arFields);
            } else {
                $idTmp = $iblockSectionObject->Add($arFields);
                $res = ($idTmp > 0);
            }

            foreach ($arDelete as $del) {
                CFile::Delete($del);
            }

            if (!$res) {
                $errorMessage .= $iblockSectionObject->LAST_ERROR." ";
            } else {
                CIBlockSection::ReSort($iblockId);
            }
        }

        if (mb_strlen($errorMessage) <= 0) {
            LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MEETING_LIST"], []));
        } else {
            $arResult["ErrorMessage"] .= $errorMessage;
            $bVarsFromForm = true;

            foreach ($arResult["ALLOWED_FIELDS"] as $key => $item) {
                if ($key != 'ID') {
                    $sName = mb_strtolower($key).'V';
                    if ($key == 'UF_FILE') {
                        $$sName = $_POST['uf_file'];
                        $arFiles = [];
                        foreach ($$sName as $it) {
                            array_push($arFiles, CFile::MakeFileArray($it));
                        }
                        $$sName = $arFiles;
                    } else {
                        $$sName = $_REQUEST[strtolower($key)];
                    }
                    $arResult["Item"][$key] = HtmlSpecialCharsbx($$sName);
                }
            }
        }
    }
}

if (mb_strlen($arResult["FatalError"]) <= 0) {
    $arResult["MEETING"] = $arMeeting;

    if ($arParams["SET_TITLE"] == "Y") {
        $APPLICATION->SetTitle($arMeeting ? GetMessage("INTASK_C36_PAGE_TITLE2").": ".$arMeeting["NAME"] : GetMessage("INTASK_C36_PAGE_TITLE"));
    }

    if ($arParams["SET_NAVCHAIN"] == "Y") {
        $APPLICATION->AddChainItem($arMeeting ? $arMeeting["NAME"] : GetMessage("INTASK_C36_PAGE_TITLE"));
    }

    if (!$bVarsFromForm) {
        foreach ($arResult["ALLOWED_FIELDS"] as $key => $item) {
            if ($key != 'ID') {
                $arResult["Item"][$key] = $arMeeting ? $arMeeting[$key] : "";
            }
        }
    }
}
$this->IncludeComponentTemplate();
