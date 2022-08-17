<?php

namespace Lnpa;

use Bitrix\Main\Loader, CIBlockProperty, Bitrix\Highloadblock as HL, COption, CIBlockPropertyEnum, CUserTypeEntity, CUserFieldEnum, Bitrix\Main\SystemException;

Loader::includeModule("highloadblock");

class CBeforeInit
{
    private static $iBlockLnpa;
    private static $iHlBlockLnpaFavouriteID;
    private static $iHlBlockLnpaVersionsID;

    public static function init()
    {
        self::checkInstallFields();
        self::checkFavouritesBlock();
        self::checkVersionsBlock();
    }

    public static function getHlBlockLnpaFavouriteID()
    {
        if (!self::$iHlBlockLnpaFavouriteID) {
            self::$iHlBlockLnpaFavouriteID = HL\HighloadBlockTable::getList([
                'filter' => ['=NAME' => 'LnpaFavourite']
            ])->fetch()['ID'];

            if (!self::$iHlBlockLnpaFavouriteID) {
                self::$iHlBlockLnpaFavouriteID = (HL\HighloadBlockTable::add([
                    'NAME' => 'LnpaFavourite',
                    'TABLE_NAME' => 'tbl_lnpa_favourite',
                ]))->getId();
            }
        }

        return self::$iHlBlockLnpaFavouriteID;
    }

    public static function getHlBlockLnpaVersionsID()
    {
        if (!self::$iHlBlockLnpaVersionsID) {
            self::$iHlBlockLnpaVersionsID = HL\HighloadBlockTable::getList([
                'filter' => ['=NAME' => 'LnpaVersions']
            ])->fetch()['ID'];

            if (!self::$iHlBlockLnpaVersionsID) {
                self::$iHlBlockLnpaVersionsID = (HL\HighloadBlockTable::add([
                    'NAME' => 'LnpaVersions',
                    'TABLE_NAME' => 'tbl_lnpa_versions',
                ]))->getId();
            }
        }

        return self::$iHlBlockLnpaVersionsID;
    }

    public static function getIBlockLnpa()
    {
        if (!self::$iBlockLnpa) {
            self::$iBlockLnpa = COption::GetOptionInt('lnpa.module', 'IBLOCK_LNPA');
        }

        return self::$iBlockLnpa;
    }

    public static function getFields()
    {
        $arFields = [
            "string" => [
                "UF_FULLNAME" => "Полное название документа",
                "UF_NAME" => "Краткое название документа",
                "UF_NUMBER" => "Номер документа",
                "UF_CUSTOM_TYPE_DOC" => "Другой тип документа",
                "UF_CUSTOM_CONTRACTOR" => "Другой контрагент",
                "UF_FULL_CONTRACTOR" => "Полное название контрагента",
                "UF_VERSIONS" => "Версии",
                "UF_HTML_TEMPLATE_SIGN" => "Шаблон сбора подписей",
            ],
            "datetime" => [
                "UF_DATE" => "Дата документа",
                "UF_DATE_TRACKER" => "Дата следующей проверки актуальности"
            ],
            "file" => [
                "UF_DOCUMENT" => "Документ",
                "UF_DOCUMENT_SIGN" => "Документ сбора подписей"
            ],
            "enumeration" => [
                "UF_TYPE_DOC" => "Тип документа",
                "UF_EXTINT" => "Внешний/Внутренний",
                "UF_CONTRACTOR" => "Контрагент",
                "UF_MAIN" => "Основной/Приложение",
                "UF_STATUS" => "Статус",
                "UF_STRUCTURE" => "Автор документа",
                "UF_ORGANIZATIONAL_LEGAL" => "Организационно-правовая форма",
                "UF_PUBLISH" => "Был уже опубликован?"
            ],
            "autocomplete" => [
                "UF_PARENT_ELEM" => "Родительский документ"
            ],
        ];

        return $arFields;
    }

    private static function checkInstallFields()
    {
        $iBlockLnpa = self::getIBlockLnpa();
        $arCurrentFields = [];
        $arEmptyEnums = [];
        $obIblockProperty = new CIBlockProperty;

        $obProperties = CIBlockProperty::GetList([], array("IBLOCK_ID" => $iBlockLnpa));
        while ($arField = $obProperties->GetNext()) {
            $arCurrentFields[$arField["CODE"]] = $arField["ID"];
        }

        foreach (self::getFields() as $sFieldKey => $arField) {
            foreach ($arField as $sKey => $sValue) {
                if (!isset($arCurrentFields[$sKey])) {
                    $arFields = array(
                        "NAME" => $sValue,
                        "ACTIVE" => "Y",
                        "SORT" => "100",
                        "CODE" => $sKey,
                        "PROPERTY_TYPE" => "S",
                        "IBLOCK_ID" => $iBlockLnpa
                    );

                    if ($sFieldKey == "datetime") {
                        $arFields["USER_TYPE"] = "DateTime";
                    }

                    if ($sFieldKey == "file") {
                        $arFields["PROPERTY_TYPE"] = "F";
                    }

                    if ($sFieldKey == "enumeration") {
                        if ($sKey == 'UF_STRUCTURE') {
                            $arFields["PROPERTY_TYPE"] = "G";
                            $arFields["MULTIPLE"] = "Y";
                            $arFields["LINK_IBLOCK_ID"] = 5;
                        } else {
                            $arFields["PROPERTY_TYPE"] = "L";
                        }
                    }

                    if ($sFieldKey == "autocomplete") {
                        $arFields["PROPERTY_TYPE"] = "E";
                        $arFields["USER_TYPE"] = "EAutocomplete";
                        $arFields["LINK_IBLOCK_ID"] = self::getIBlockLnpa();
                    }

                    $arCurrentFields[$sKey] = $obIblockProperty->add($arFields);
                }

                if ($sFieldKey == "enumeration" && $sKey != 'UF_STRUCTURE') {
                    $obenumList = CIBlockProperty::GetPropertyEnum($sKey, array(), array("IBLOCK_ID" => $iBlockLnpa));

                    if (!$obenumList->fetch()) {
                        if ($sKey == 'UF_PUBLISH') {
                            $ibpenum = new CIBlockPropertyEnum;
                            $ibpenum->Add(array('PROPERTY_ID' => $arCurrentFields[$sKey], 'VALUE' => 'Y', 'XML_ID' => 'Y'));
                        } else {
                            array_push($arEmptyEnums, $sValue);
                        }
                    }
                }
            }
        }

        try {
            if (!empty($arEmptyEnums)) {
                $sReferences = implode(', ', $arEmptyEnums);
                throw new SystemException("Заполните следующие справочники: $sReferences.");
            }
        } catch (SystemException $exception) {
            echo $exception->getMessage();
            die;
        }
    }

    private static function checkUserFieldHL($sEntityID, $sFieldName)
    {
        return CUserTypeEntity::GetList([], ['ENTITY_ID' => $sEntityID, 'FIELD_NAME' => $sFieldName])->GetNext();
    }

    private static function checkFavouritesBlock()
    {
        $iHlBlockID = self::getHlBlockLnpaFavouriteID();

        $arFields = [
            "iblock_element" => [
                "UF_FAVOURITE" => "Закладки",
            ],
            "employee" => [
                "UF_USER" => "Пользователь",
            ]
        ];


        $ObUserType = new CUserTypeEntity;
        foreach ($arFields as $sFieldKey => $arField) {
            foreach ($arField as $sKey => $sValue) {
                if (!self::checkUserFieldHL("HLBLOCK_$iHlBlockID", $sKey)) {
                    $arFields = array(
                        "SORT" => "600",
                        "FIELD_NAME" => $sKey,
                        "USER_TYPE_ID" => $sFieldKey,
                        "ENTITY_ID" => "HLBLOCK_$iHlBlockID",
                        "EDIT_FORM_LABEL" => ['ru' => $sValue]
                    );

                    if ($sKey == 'UF_FAVOURITE') {
                        $arFields["PROPERTY_TYPE"] = "E";
                        $arFields["MULTIPLE"] = "Y";
                        $arFields['SETTINGS']["IBLOCK_ID"] = self::getIBlockLnpa();
                    }

                    $ObUserType->Add($arFields);
                }
            }
        }
    }

    private static function checkVersionsBlock()
    {
        $iHlBlockID = self::getHlBlockLnpaVersionsID();

        $arFields = [
            "string" => [
                "UF_FULLNAME" => "Полное название документа",
                "UF_NAME" => "Краткое название документа",
                "UF_STRUCTURE" => "Управление",
                "UF_TYPE_DOC" => "Тип документа",
                "UF_NUMBER" => "Номер документа",
                "UF_DATE" => "Дата документа",
                "UF_EXTINT" => "Внешний/Внутренний",
                "UF_CONTRACTOR" => "Контрагент",
                "UF_PARENT_ELEM" => "Родительский документ",
                "UF_TAGS" => "Теги",
            ],
            "double" => [
                "UF_IBLOCK_ELEM" => "Элемент инфоблока",
            ],
            "file" => [
                "UF_DOCUMENT" => "Документ"
            ]
        ];

        $ObUserType = new CUserTypeEntity;
        foreach ($arFields as $sFieldKey => $arField) {
            foreach ($arField as $sKey => $sValue) {
                if (!self::checkUserFieldHL("HLBLOCK_$iHlBlockID", $sKey)) {
                    $arFields = array(
                        "SORT" => "600",
                        "FIELD_NAME" => $sKey,
                        "USER_TYPE_ID" => $sFieldKey,
                        "ENTITY_ID" => "HLBLOCK_$iHlBlockID",
                        "EDIT_FORM_LABEL" => ['ru' => $sValue]
                    );
                    $ObUserType->Add($arFields);
                }
            }
        }
    }
}
