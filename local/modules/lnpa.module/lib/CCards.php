<?php

namespace Lnpa;

use Bitrix\Main\Loader, CUserTypeEntity, CUserFieldEnum, CIBlockElement, COption, CIBlockProperty, CIBlockPropertyEnum, CIntranetUtils, CMain, CFields, CVersions;

Loader::includeModule('iblock');

class CCards {
    public static $arCard = null;

    public static function getDetailCard($iID)
    {
        if (!empty($iID)) {
            global $USER;
            if (!isset(self::$arCard[$iID])) {
                $arResult = [];
                $arSelect = [
                    "ID",
                    "NAME",
                    "PROPERTY_UF_FULLNAME",
                    "PROPERTY_UF_STRUCTURE",
                    "PROPERTY_UF_TYPE_DOC",
                    "PROPERTY_UF_NUMBER",
                    "PROPERTY_UF_DATE",
                    "PROPERTY_UF_CONTRACTOR",
                    "PROPERTY_UF_STATUS_VALUE",
                    "PROPERTY_UF_EXTINT",
                    "PROPERTY_UF_DOCUMENT",
                    "PROPERTY_UF_PARENT_ELEM",
                    "PROPERTY_UF_MAIN",
                    "PROPERTY_UF_MAIN",
                    "PROPERTY_UF_CUSTOM_TYPE_DOC",
                    "PROPERTY_UF_CUSTOM_CONTRACTOR",
                    "PROPERTY_UF_FULL_CONTRACTOR",
                    "PROPERTY_UF_ORGANIZATIONAL_LEGAL",
                    "PROPERTY_UF_PUBLISH",
                    "PROPERTY_UF_VERSIONS",
                    "PROPERTY_UF_DATE_TRACKER",
                    "TAGS",
                    "PROPERTY_UF_DOCUMENT_SIGN"
                ];

                $obCard = CIBlockElement::GetList(array(), ['ID' => $iID], false, array(), $arSelect);
                $arFavouriteIDs = \LNPA\CFavourite::getFavouriteListID($USER->GetID());

                if ($arCard = $obCard->GetNext()) {
                    $arStructure = [];
                    if (!empty($arCard['PROPERTY_UF_STRUCTURE_VALUE'])) {
                        foreach ($arCard['PROPERTY_UF_STRUCTURE_VALUE'] as $iStructureID) {
                            $arStructure['NAMES'] .= \Lnpa\CMain::getStructure()[$iStructureID] . '<br>';
                            $arStructure['IDS'][] = $iStructureID;
                        }
                    }

                    $arResult = [
                        'ID' => $arCard['ID'],
                        'UF_FULLNAME' => $arCard['PROPERTY_UF_FULLNAME_VALUE'],
                        'UF_NAME' => $arCard['NAME'],
                        'UF_EXTINT' => $arCard['PROPERTY_UF_EXTINT_VALUE'],
                        'UF_STRUCTURE' => $arStructure['NAMES'],
                        'UF_TYPE_DOC' => $arCard['PROPERTY_UF_TYPE_DOC_VALUE'],
                        'UF_NUMBER' => $arCard['PROPERTY_UF_NUMBER_VALUE'],
                        'UF_DATE' => (new \DateTime($arCard['PROPERTY_UF_DATE_VALUE']))->format('d.m.Y'),
                        'UF_DATE_TRACKER' => (new \DateTime($arCard['PROPERTY_UF_DATE_TRACKER_VALUE']))->format('d.m.Y'),
                        'UF_CONTRACTOR' => $arCard['PROPERTY_UF_CONTRACTOR_VALUE'],
                        'UF_MAIN' => $arCard['PROPERTY_UF_MAIN_VALUE'],
                        'UF_CUSTOM_TYPE_DOC' => $arCard['PROPERTY_UF_CUSTOM_TYPE_DOC_VALUE'],
                        'UF_CUSTOM_CONTRACTOR' => $arCard['PROPERTY_UF_CUSTOM_CONTRACTOR_VALUE'],
                        'STATUS_ID' => $arCard['PROPERTY_UF_STATUS_VALUE_ENUM_ID'],
                        'TAGS' => $arCard['TAGS'],
                        'TAGS_ARR' => explode(', ', $arCard['TAGS']),
                        'FILE' => \CFile::GetFileArray($arCard['PROPERTY_UF_DOCUMENT_VALUE']),
                        'SIGN_FILE' => \CFile::GetFileArray($arCard['PROPERTY_UF_DOCUMENT_SIGN_VALUE']),
                        'UF_FULL_CONTRACTOR' => $arCard['PROPERTY_UF_FULL_CONTRACTOR_VALUE'],
                        'UF_ORGANIZATIONAL_LEGAL' => $arCard['PROPERTY_UF_ORGANIZATIONAL_LEGAL_VALUE'],
                        'UF_PUBLISH' => $arCard['PROPERTY_UF_PUBLISH_VALUE'],
                        'UF_VERSIONS' => $arCard['PROPERTY_UF_VERSIONS_VALUE'],
                        'IDS' => [
                            'UF_STRUCTURE' => $arStructure['IDS'],
                            'UF_TYPE_DOC' => $arCard['PROPERTY_UF_TYPE_DOC_ENUM_ID'],
                            'UF_EXTINT' => $arCard['PROPERTY_UF_EXTINT_ENUM_ID'],
                            'UF_CONTRACTOR' => $arCard['PROPERTY_UF_CONTRACTOR_ENUM_ID'],
                            'UF_MAIN' => $arCard['PROPERTY_UF_MAIN_ENUM_ID'],
                            'UF_ORGANIZATIONAL_LEGAL' => $arCard['PROPERTY_UF_ORGANIZATIONAL_LEGAL_ENUM_ID'],
                            'UF_DATE' => (new \DateTime($arCard['PROPERTY_UF_DATE_VALUE']))->format('Y-m-d'),
                            'UF_DATE_TRACKER' => (new \DateTime($arCard['PROPERTY_UF_DATE_TRACKER_VALUE']))->format('Y-m-d'),
                        ],
                        'FAVOURITE' => in_array($arCard['ID'], $arFavouriteIDs)
                    ];

                    if (!empty($iParentID = $arCard['PROPERTY_UF_PARENT_ELEM_VALUE'])) {
                        $arParent = CIBlockElement::GetList([], ['ID' => $iParentID])->GetNext();
                        if ($arParent) {
                            $arResult['UF_PARENT_ELEM'] = $arParent['NAME'];
                            $arResult['IDS']['UF_PARENT_ELEM'] = $arParent['ID'];
                        }
                    }
                }

                if (!empty($arResult)) {
                    $obEnum = CIBlockPropertyEnum::GetList([], ["IBLOCK_ID" => \Lnpa\CBeforeInit::getIBlockLnpa(), "ID" => $arResult['STATUS_ID'], "CODE" => "UF_STATUS"]);
                    $arEnum = $obEnum->GetNext();
                    $arResult['STATUS'] = [
                        'NAME' => $arEnum['VALUE'],
                        'CODE' => $arEnum['EXTERNAL_ID']
                    ];

                    if ($arVersionsIDs = unserialize($arResult['UF_VERSIONS'])) {
                        $arResult['VERSIONS'] = \LNPA\CVersions::getVersionsByIDs($arVersionsIDs);
                    }
                }

                self::$arCard[$iID] = $arResult;
            }

            return self::$arCard[$iID];
        }
        else return [];
    }

    public static function getCards($arStatuses = [], $iPageSize = 0, $iNumPage = 0)
    {
        global $USER;
        $arRequest = \LNPA\CMain::getRequest();
        $arFields = \LNPA\CFields::getFields();
        $arStatusesIDs = [];
        $arResult = [];

        foreach ($arFields['CODE']['UF_STATUS']['LIST'] as $arItem) {
            if (in_array($arItem['XML_ID'], $arStatuses)) {
                array_push($arStatusesIDs, $arItem['ID']);
            }

            if (empty($arRequest['page']) && $arItem['XML_ID'] == 'PUBLISH') {
                array_push($arStatusesIDs, $arItem['ID']);
            }
        }

        $arTags = array_map(function ($sTag) {
            return '%' . trim($sTag) . '%';
        }, explode(',', $arRequest['TAGS']));

        $arSelect = [
            "ID",
            "NAME",
            "PROPERTY_UF_STRUCTURE",
            "PROPERTY_UF_TYPE_DOC",
            "PROPERTY_UF_NUMBER",
            "PROPERTY_UF_DATE",
            "PROPERTY_UF_CONTRACTOR",
            "PROPERTY_UF_STATUS",
            "TAGS"
        ];
        $arFilter = [
            'IBLOCK_ID' => IntVal(\LNPA\CBeforeInit::getIBlockLnpa()),
            'ACTIVE' => 'Y',
            'TAGS' => $arTags,
            'PROPERTY_UF_STATUS' => $arStatusesIDs,
            '%NAME' => $arRequest['CONTAINS_AN']
        ];

        if (in_array($arRequest['page'], ['last_doc', 'moderation_view'])) {
            $arFilter['PROPERTY_UF_STRUCTURE'] = \LNPA\CMain::getRuleStructure($USER->GetID());
        }

        foreach ($arRequest as $sNameProp => $sValProp) {
            if (!empty($sValProp)) {
                if (mb_substr($sNameProp, 0, 3) == 'UF_') {
                    $arFilter['PROPERTY_' . $sNameProp] = $sValProp;
                } else if ($sNameProp == 'PUBLIC_YEAR') {
                    $arFilter['><PROPERTY_UF_DATE'] = ["{$sValProp}-01-01", "{$sValProp}-12-31 24:59:59"];
                }
            }
        }

        if (empty($arRequest['page'])) {
            $arPagination = ['nPageSize' => $iPageSize, 'iNumPage' => $iNumPage];
        }
        $obCards = CIBlockElement::GetList([], $arFilter, false, $arPagination, $arSelect);
        $arResult['NavPageCount'] = $obCards->NavPageCount;
        $arResult['NavPageNomer'] = $obCards->NavPageNomer;

        $arFavouriteIDs = \LNPA\CFavourite::getFavouriteListID($USER->GetID());

        while ($arCard = $obCards->GetNext()) {
            $sStructure = null;
            if (!empty($arCard['PROPERTY_UF_STRUCTURE_VALUE'])) {
                foreach ($arCard['PROPERTY_UF_STRUCTURE_VALUE'] as $iStructureID) {
                    $sStructure .= \Lnpa\CMain::getStructure()[$iStructureID] . '<br>';
                }
            }

            $sStatus = $arFields['CODE']['UF_STATUS']['LIST'][$arCard['PROPERTY_UF_STATUS_ENUM_ID']]['XML_ID'];

            $arResult[$sStatus]['ITEMS'][$arCard['ID']] = [
                'ID' => $arCard['ID'],
                'UF_NAME' => $arCard['NAME'],
                'UF_STRUCTURE' => $sStructure,
                'UF_TYPE_DOC' => $arCard['PROPERTY_UF_TYPE_DOC_VALUE'],
                'UF_NUMBER' => $arCard['PROPERTY_UF_NUMBER_VALUE'],
                'UF_DATE' => (new \DateTime($arCard['PROPERTY_UF_DATE_VALUE']))->format('d.m.Y'),
                'UF_CONTRACTOR' => $arCard['PROPERTY_UF_CONTRACTOR_VALUE'],
                'TAGS' => $arCard['TAGS'],
            ];

            if (in_array($arCard['ID'], $arFavouriteIDs)) {
                $arResult['FAVOURITE'][$arCard['ID']] = $arResult[$sStatus]['ITEMS'][$arCard['ID']];
            }
        }

        return $arResult;
    }

    public static function update($arDataFields = [], $sAction)
    {
        $arRequest = \LNPA\CMain::getRequest();
        $arCard = self::getDetailCard($arRequest['id']);

        if (!empty($arCard['ID'])) {
            $sRole = \LNPA\CMain::getRole();
            $sStatus = $arCard['STATUS']['CODE'];
            $bAccess = ($sStatus == 'DRAFT' || $sRole == 'ADMIN');

            if ($bAccess) {
                $obElement = new CIBlockElement;
                if ($sAction == 'PUBLISH') {
                    $arDataFields = \LNPA\CFields::addEnumLnpa($arDataFields);
                    if ($iEnumPublishID = \LNPA\CFields::getEnumIDByField('UF_PUBLISH', 'Y')) {
                        $arDataFields['DATA']['PROPERTY_VALUES']['UF_PUBLISH'] = $iEnumPublishID;
                        if (!empty($arRequest['SAVE_VERSION'])) {
                            $obElemHL = \LNPA\CVersions::add($arRequest['id']);
                            $iElemHL = $obElemHL->getID();

                            if ($arVersions = unserialize($arCard['UF_VERSIONS'])) {
                                array_push($arVersions, $iElemHL);
                            }
                            else {
                                $arVersions = [$iElemHL];
                            }

                            $arDataFields['DATA']['PROPERTY_VALUES']['UF_VERSIONS'] = serialize($arVersions);
                        }
                    }
                }

                $obElement->update($arCard['ID'], $arDataFields['DATA']);

                if ($sAction == 'MODERATION') {
                    header("Location: ".$_SERVER["DOCUMENT_URI"].'?page=last_doc');
                }
                else if ($sAction == 'PUBLISH') {
                    header("Location: ".$_SERVER["DOCUMENT_URI"].'?page=moderation_view');
                }
                else {
                    self::$arCard[$arCard['ID']]['STATUS']['NAME'] = $arDataFields['STATUS']['NAME'];
                    $arDataFields['RESULT'] = [
                        'STATUS' => true,
                        'TEXT' => "Карточка '{$arDataFields['DATA']['PROPERTY_VALUES']['UF_NAME']}' обновлена"
                    ];
                    header("Location: ".$_SERVER['REQUEST_URI']);
                }
            }
            else {
                $arDataFields['ERRORS'][] = 'Нет прав на обновление карточки';
            }
        }
        else {
            $arDataFields['ERRORS'][] = 'Не передан ID карточки';
        }

        return $arDataFields;
    }

    public static function add($arDataFields = [], $sAction)
    {
        $obElement = new CIBlockElement;

        if ($sAction == 'PUBLISH') {
            $arDataFields = \LNPA\CFields::addEnumLnpa($arDataFields);
            if ($iEnumPublishID = \LNPA\CFields::getEnumIDByField('UF_PUBLISH', 'Y')) {
                $arDataFields['DATA']['PROPERTY_VALUES']['UF_PUBLISH'] = $iEnumPublishID;
            }
        }

        $obElement->add($arDataFields['DATA']);

        if ($sAction == 'MODERATION') {
            header("Location: ".$_SERVER["DOCUMENT_URI"].'?page=last_doc');
        }
        else if ($sAction == 'PUBLISH') {
            header("Location: ".$_SERVER["DOCUMENT_URI"].'?page=moderation_view');
        }
        else {
            $arDataFields['RESULT'] = [
                'STATUS' => true,
                'TEXT' => "Карточка {$arDataFields['DATA']['PROPERTY_VALUES']['UF_NAME']} добавлена"
            ];
        }

        return $arDataFields;
    }

    public static function delete($iCardID, $sRole)
    {
        if (!empty($iCardID) && $sRole != 'USER') {
            $arCard = self::getDetailCard($iCardID);
            if ($arCard['STATUS']['CODE'] == 'DRAFT') {
                if ($arVersions = unserialize($arCard['UF_VERSIONS'])) {
                    \LNPA\CVersions::delete($arVersions);
                }
                CIBlockElement::Delete($iCardID);
                $arDataFields['RESULT'] = [
                    'STATUS' => true,
                    'TEXT' => "Карточка {$arCard['UF_NAME']} удалена"
                ];
            }
            else {
                $arDataFields['ERRORS'][] = 'Статус карточки должен быть "в черновике"';
            }
        }
        else {
            $arDataFields['ERRORS'][] = 'При удалении не был передан ID карточки или у Вас отсутствуют право удаления';
        }

        return $arDataFields;
    }
}
