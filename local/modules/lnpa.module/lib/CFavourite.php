<?php

namespace Lnpa;

use Bitrix\Main\Loader, Bitrix\Highloadblock as HL, CUserTypeEntity, CUserFieldEnum, CIBlockElement, COption, CIBlockProperty, CIBlockPropertyEnum, CIntranetUtils, CMain, CFields;

Loader::includeModule('iblock');

class CFavourite
{
    private static $sClassFavourite = null;

    public static function getClassFavourite()
    {
        if (self::$sClassFavourite == null) {
            $iHlBlockID = \LNPA\CBeforeInit::getHlBlockLnpaFavouriteID();
            $obHlblock = HL\HighloadBlockTable::getById($iHlBlockID)->fetch();
            $obEntity = HL\HighloadBlockTable::compileEntity($obHlblock);
            self::$sClassFavourite = $obEntity->getDataClass();
        }
        return self::$sClassFavourite;
    }

    public static function add($iUserID, $iID)
    {
        if (!empty($iID) && !empty($iUserID)) {
            $sClassFavourite = self::getClassFavourite();

            $arFilter = [
                "filter" => [
                    'UF_USER' => $iUserID
                ]
            ];

            $obData = $sClassFavourite::getList($arFilter);

            if ($arItem = $obData->fetch()) {
                array_push($arItem["UF_FAVOURITE"], $iID);
                $sClassFavourite::update($arItem['ID'], array(
                    'UF_USER' => $iUserID,
                    'UF_FAVOURITE' => $arItem["UF_FAVOURITE"],
                ));
            } else {
                $sClassFavourite::add(array(
                    'UF_USER' => $iUserID,
                    'UF_FAVOURITE' => [$iID],
                ));
            }
        }
    }

    public static function delete($iUserID, $iID)
    {
        if (!empty($iID) && !empty($iUserID)) {
            $sClassFavourite = self::getClassFavourite();

            $arFilter = [
                "filter" => [
                    'UF_USER' => $iUserID
                ]
            ];
            $obData = $sClassFavourite::getList($arFilter);

            if ($arItem = $obData->fetch()) {
                $arNewFavourite = [];
                foreach ($arItem["UF_FAVOURITE"] as $iFavouriteID) {
                    if ($iFavouriteID != $iID) {
                        array_push($arNewFavourite, $iFavouriteID);
                    }
                }
                $sClassFavourite::update($arItem['ID'], array(
                    'UF_USER' => $iUserID,
                    'UF_FAVOURITE' => $arNewFavourite,
                ));
            }
        }
    }

    public static function getFavouriteListID($iUserID, $iID = 0)
    {
        $sClassFavourite = self::getClassFavourite();

        $arFilter = [
            "filter" => [
                'UF_USER' => $iUserID
            ]
        ];
        if (!empty($iID)) {
            $arFilter['filter']['UF_FAVOURITE'] = $iID;
        }
        $obData = $sClassFavourite::getList($arFilter);
        $arItem = $obData->fetch();

        return $arItem["UF_FAVOURITE"];
    }
}
