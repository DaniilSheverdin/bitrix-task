<?php

namespace Lnpa;

use Bitrix\Main\Loader, CUserTypeEntity, CUserFieldEnum, CIBlockElement, COption, CIBlockProperty, CIBlockPropertyEnum, CIntranetUtils, CMain, CCards;

Loader::includeModule('iblock');

class CFields
{

    private static $arFields = null;

    public static function getFields()
    {
        if (empty(self::$arFields)) {
            $arValuesEnums = self::getValuesEnums();
            $arFields = [];
            $obFields = CIBlockProperty::GetList(["SORT" => "ASC"], array("IBLOCK_ID" => \LNPA\CBeforeInit::getIBlockLnpa()));
            while ($arField = $obFields->GetNext()) {
                $sType = 'string';

                if ($arField['PROPERTY_TYPE'] == 'F') {
                    $sType = 'file';
                } else if ($arField['USER_TYPE'] == 'DateTime') {
                    $sType = 'datetime';
                } else if (in_array($arField['PROPERTY_TYPE'], ['L', 'G'])) {
                    $sType = 'enumeration';
                } else if ($arField['USER_TYPE'] == 'EAutocomplete') {
                    $sType = 'autocomplete';
                }

                $arFields['ID'][$arField['ID']] = [
                    'ID' => $arField['ID'],
                    'CODE' => $arField['CODE'],
                    'XML_ID' => $arField['XML_ID'],
                    'LABEL' => $arField['NAME'],
                    'TYPE' => $sType,
                    'LIST' => [],
                ];
                $arFields['CODE'][$arField['CODE']] = [
                    'ID' => $arField['ID'],
                    'CODE' => $arField['CODE'],
                    'XML_ID' => $arField['XML_ID'],
                    'LABEL' => $arField['NAME'],
                    'TYPE' => $sType,
                    'LIST' => [],
                ];
            }

            foreach ($arValuesEnums as $arValuesEnum) {
                $iFieldID = $arValuesEnum['PROPERTY_ID'];
                $iValueID = $arValuesEnum['ID'];
                if (isset($arFields['ID'][$iFieldID])) {
                    $arFields['ID'][$iFieldID]['LIST'][$iValueID] = $arValuesEnum;
                    $arFields['CODE'][$arValuesEnum['PROPERTY_CODE']]['LIST'][$iValueID] = $arValuesEnum;
                }
            }

            $arFields = self::getValuesField($arFields);

            self::$arFields = $arFields;
        }

        return self::$arFields;
    }

    public static function getValuesEnums($arEnumIDs = [])
    {
        $arEnums = [];
        $obEnums = CIBlockPropertyEnum::GetList(array("DEF" => "DESC", "SORT" => "ASC"), array("IBLOCK_ID" => \LNPA\CBeforeInit::getIBlockLnpa()));
        while ($arEnum = $obEnums->GetNext()) {
            $arEnums[$arEnum['ID']] = $arEnum;
        }

        return $arEnums;
    }

    public static function getEnumIDByField($sField, $sXml)
    {
        $iEnum = null;
        $arFields = self::getFields();
        foreach ($arFields['CODE'][$sField]['LIST'] as $arItem) {
            if ($arItem['XML_ID'] == $sXml) {
                $iEnum = (int)$arItem['ID'];
                break;
            }
        }

        return $iEnum;
    }

    private static function getValuesField($arFields = [])
    {
        global $USER;

        $arRequest = \LNPA\CMain::getRequest();
        $arCard = \LNPA\CCards::getDetailCard($arRequest['id']);
        $obUsers = \CUser::GetList($by = "", $order = "", ['ID' => $USER->GetID()], ['SELECT' => ['UF_DEPARTMENT']]);
        $iStructureID = $obUsers->getNext()['UF_DEPARTMENT'][0];

        if (empty($arRequest['UF_STRUCTURE']) && empty($arCard['IDS']['UF_STRUCTURE'])) {
            $arRequest['UF_STRUCTURE'] = [$iStructureID];
        }

        foreach ($arFields['CODE'] as $sKey => $arField) {
            $sValue = null;

            if (!empty($arRequest[$sKey])) {
                $sValue = $arRequest[$sKey];
            } else if (!empty($arCard['IDS'][$sKey])) {
                $sValue = ($arField['TYPE'] == 'autocomplete') ? "{$arCard[$sKey]} [{$arCard['IDS'][$sKey]}]" : $arCard['IDS'][$sKey];
            } else if (!empty($arCard[$sKey])) {
                $sValue = $arCard[$sKey];
            }

            $arFields['CODE'][$sKey]['VALUE'] = $sValue;
            $arFields['ID'][$arField['ID']]['VALUE'] = $sValue;
        }

        $arFields['TAGS'] = empty($arRequest['TAGS']) ? $arCard['TAGS'] : $arRequest['TAGS'];
        $arFields['FILE'] = $arCard['FILE'];

        return $arFields;
    }

    public static function getDataFields($sAction)
    {
        $arResult = [];
        $arRequest = \LNPA\CMain::getRequest();
        $arFields = self::getFields();

        if (in_array($sAction, ['DRAFT', 'MODERATION', 'PUBLISH'])) {
            $arProps = [];
            foreach ($arFields['CODE'] as $sKey => $arValue) {
                if (isset($arRequest[$sKey])) {
                    if ($arValue['TYPE'] == 'datetime') {
                        $arProps[$sKey] = date('d.m.Y H:i:s', strtotime($arRequest[$sKey]));
                    } else if ($arValue['TYPE'] == 'autocomplete') {
                        preg_match("/\[(.+?)\]/", $arValue['VALUE'], $sAutocomplete);
                        $arProps[$sKey] = $sAutocomplete[1];
                    } else {
                        $arProps[$sKey] = $arRequest[$sKey];
                    }
                }

                if ($sKey == 'UF_PUBLISH') {
                    foreach ($arFields['CODE'][$sKey]['LIST'] as $arItem) {
                        if ($arItem['VALUE'] == $arValue['VALUE']) {
                            $arProps[$sKey] = $arItem['ID'];
                            break;
                        }
                    }
                }
                if ($sKey == 'UF_VERSIONS') {
                    $arProps[$sKey] = $arValue['VALUE'];
                }
            }

            $sActionName = null;
            foreach ($arFields['CODE']['UF_STATUS']['LIST'] as $arItem) {
                if ($arItem['XML_ID'] == $sAction) {
                    $sActionName = $arItem['VALUE'];
                    $arProps['UF_STATUS'] = $arItem['ID'];
                    break;
                }
            }

            $arResult['DATA'] = [
                'IBLOCK_ID' => \LNPA\CBeforeInit::getIBlockLnpa(),
                'ACTIVE' => 'Y',
                'NAME' => empty($arProps['UF_NAME']) ? 'undefined' : $arProps['UF_NAME'],
                'PROPERTY_VALUES' => $arProps,
                'TAGS' => $arFields['TAGS']
            ];
        }

        $arResult = self::getValidateFileds($arResult, $sAction);
        $arResult['STATUS']['NAME'] = $sActionName;

        return $arResult;
    }

    private static function getValidateFileds($arResult = [], $sAction)
    {
        $arErrors = [];
        $arProperty = $arResult['DATA']['PROPERTY_VALUES'];
        $arRequest = \LNPA\CMain::getRequest();

        if (empty($arProperty['UF_FULLNAME'])) $arErrors[] = 'Введите полное название документа';
        if (empty($arProperty['UF_NAME'])) $arErrors[] = 'Введите краткое название документа';
        if (empty($arResult['DATA']['TAGS'])) $arErrors[] = 'Заполните теги';

        if ($arRequest['page'] == 'detail_edit') {
            $obFile = CIBlockElement::GetList(array(), ['ID' => $arRequest['id']], false, array(), ['ID', 'PROPERTY_UF_DOCUMENT']);
            if ($arFile = $obFile->GetNext()) {
                $arProperty['UF_DOCUMENT'] = $arFile['PROPERTY_UF_DOCUMENT_VALUE'];
            }
        }

        if ($sAction != 'DRAFT') {
            if (empty($arProperty['UF_STRUCTURE'])) $arErrors[] = 'Выберите управление';
            if (empty($arProperty['UF_NUMBER'])) $arErrors[] = 'Введите номер документа';
            if (empty($arProperty['UF_DATE']) || strtotime($arProperty['UF_DATE']) == 0) $arErrors[] = 'Введите дату';
            if (empty($arProperty['UF_TYPE_DOC'])) $arErrors[] = 'Выберите тип документа';
            if (empty($arProperty['UF_EXTINT'])) $arErrors[] = 'Внешний или внутренний документ?';
            if (empty($arProperty['UF_CONTRACTOR'])) $arErrors[] = 'Выберите контрагента';
            if (empty($arProperty['UF_MAIN'])) $arErrors[] = 'Основной документ или приложение?';
            if (empty($arProperty['UF_DOCUMENT'])) $arErrors[] = 'Прикрепите документ';
        }

        if (!empty($arErrors)) {
            $arResult['ERRORS'] = $arErrors;
        }

        return $arResult;
    }

    public function addEnumLnpa($arDataFields)
    {
        $iBlockLnpa = \Lnpa\CBeforeInit::getIBlockLnpa();
        $arFileds = \LNPA\CFields::getFields();

        foreach (['TYPE_DOC', 'CONTRACTOR'] as $sItem) {
            $sValue = $arDataFields['DATA']['PROPERTY_VALUES']['UF_CUSTOM_' . $sItem];
            if (!empty($sValue)) {
                $sPropID = 'UF_' . $sItem;

                $obEnum = CIBlockProperty::GetPropertyEnum($sPropID, [], ['IBLOCK_ID' => $iBlockLnpa, 'VALUE' => $sValue]);
                $arEnum = $obEnum->GetNext();

                if (!$arEnum) {
                    $ibpenum = new CIBlockPropertyEnum;
                    $iID = $ibpenum->Add(['PROPERTY_ID' => $arFileds['CODE'][$sPropID]['ID'], 'VALUE' => $sValue]);
                } else {
                    $iID = $arEnum["ID"];
                }

                if (!empty($iID)) {
                    $arDataFields['DATA']['PROPERTY_VALUES']['UF_' . $sItem] = $iID;
                }

                unset($arDataFields['DATA']['PROPERTY_VALUES']['UF_CUSTOM_' . $sItem]);
            }
        }

        return $arDataFields;
    }
}
