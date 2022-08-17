<?php

namespace Serg\Bi\Rest;

\Bitrix\Main\Localization\Loc::loadMessages(__FILE__);
use \Bitrix\Main\Loader;
use CIBlockElement;

class Service
{
    public static function getDescription(): array
    {
        return array(
            'serg.bi' => array(
                'bi.indicators.list' => array(
                    'callback' => array(RestApi::class,'getList'),
                    'options' => array(),
                ),

            )
        );

    }
}

class RestApi {

    public static function getList() {

        Loader::includeModule('iblock');

        $arIndicators = array();
        $count = 0;
        $arSelect = Array("ID", "IBLOCK_ID", "NAME", "DATE_ACTIVE_FROM", "PROPERTY_*");
        $arFilter = Array("IBLOCK_ID" => 524, "ACTIVE_DATE" => "Y", "ACTIVE" => "Y");
        $res = CIBlockElement::GetList(Array(), $arFilter, false, false, $arSelect);

        while ($ob = $res->GetNextElement()) {

            $arFields = $ob->GetFields();
            $arIndicators[$count]['NAME'] = $arFields['NAME'];
            $arProps = $ob->GetProperties();
            $arIndicators[$count]['SHORT_NAME'] = $arProps['ATT_SHORT_NAME']['VALUE'];
            $arIndicators[$count]['BASE_SET'] = $arProps['ATT_BASE_SET']['VALUE'];
            $arIndicators[$count]['TARGET_VALUE'] = $arProps['ATT_TARGET_VALUE']['VALUE'];
            $arIndicators[$count]['STATE_VALUE'] = $arProps['ATT_STATE_VALUE']['VALUE'];
            $arIndicators[$count]['PERCENT_EXEC'] = $arProps['ATT_PERCENT_EXEC']['VALUE'];
            $arIndicators[$count]['COMMENT'] = $arProps['ATT_COMMENT']['VALUE'];
            $arIndicators[$count]['DEPARTMENT'] = $arProps['ATT_DEPARTMENT']['VALUE'];
            $arIndicators[$count]['DATE'] = $arProps['ATT_DATE']['VALUE'];
            $arIndicators[$count]['FLAG'] = $arProps['ATT_FLAG']['VALUE'];

            $count++;
        }
        return $arIndicators;


    }
}