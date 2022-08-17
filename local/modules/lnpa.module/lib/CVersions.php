<?php

namespace Lnpa;

use Bitrix\Main\Loader, Bitrix\Highloadblock as HL, CUserTypeEntity, CUserFieldEnum, CIBlockElement, COption, CIBlockProperty, CIBlockPropertyEnum, CIntranetUtils, CMain, CFields;

Loader::includeModule('iblock');

class CVersions
{
    private static $sClassVersions = null;

    public static function getClass()
    {
        if (self::$sClassVersions == null) {
            $iHlBlockID = \LNPA\CBeforeInit::getHlBlockLnpaVersionsID();
            $obHlblock = HL\HighloadBlockTable::getById($iHlBlockID)->fetch();
            $obEntity = HL\HighloadBlockTable::compileEntity($obHlblock);
            self::$sClassVersions = $obEntity->getDataClass();
        }
        return self::$sClassVersions;
    }

    public static function add($iCardID)
    {
        $iElemID = null;

        $sClassVersions = self::getClass();
        $arCard = \LNPA\CCards::getDetailCard($iCardID);
        if ($arCard) {
            $iElemID = $sClassVersions::add([
                "UF_IBLOCK_ELEM" => $iCardID,
                "UF_FULLNAME" => $arCard["UF_FULLNAME"],
                "UF_NAME" => $arCard["UF_NAME"],
                "UF_STRUCTURE" => $arCard["UF_STRUCTURE"],
                "UF_TYPE_DOC" => $arCard["UF_TYPE_DOC"],
                "UF_NUMBER" => $arCard["UF_NUMBER"],
                "UF_DATE" => $arCard["UF_DATE"],
                "UF_EXTINT" => $arCard["UF_EXTINT"],
                "UF_CONTRACTOR" => $arCard["UF_CONTRACTOR"],
                "UF_PARENT_ELEM" => $arCard["UF_PARENT_ELEM"],
                "UF_TAGS" => $arCard["TAGS"],
                "UF_DOCUMENT" => \CFile::MakeFileArray($arCard["FILE"]["ID"]),
            ]);
        }

        return $iElemID;
    }

    public static function delete($arIDs)
    {
        $sClassVersions = self::getClass();

        if (!empty($arIDs)) {
            foreach ($arIDs as $iID) {
                $sClassVersions::Delete($iID);
            }
        }
    }

    public static function getVersionsByIDs($iIDs = [])
    {
        $sClassVersions = self::getClass();

        $arFilter = [
            "filter" => [
                'ID' => $iIDs
            ]
        ];

        $obData = $sClassVersions::getList($arFilter);
        $arVersions = [];
        while ($arItem = $obData->fetch()) {
            $arItem['FILE'] = \CFile::GetFileArray($arItem['UF_DOCUMENT']);
            $arVersions[$arItem['ID']] = $arItem;
        }

        return $arVersions;
    }
}
