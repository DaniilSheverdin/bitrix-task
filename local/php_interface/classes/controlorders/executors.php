<?php

namespace Citto\ControlOrders;

use CUser;
use CIBlockElement;
use CIBlockPropertyEnum;
use Bitrix\Main\Loader;

class Executors
{
    public static function getTypesList(bool $bCalcCount = true)
    {
        $arResult = [];
        Loader::includeModule('iblock');
        $res = CIBlockPropertyEnum::GetList(
            [
                'SORT' => 'ASC'
            ],
            [
                'IBLOCK_ID' => Settings::$iblockId['ISPOLNITEL'],
                'CODE'      => 'TYPE'
            ]
        );
        if ($bCalcCount) {
            $arExecutors = self::getList();
        }
        while ($row = $res->GetNext()) {
            if ($bCalcCount) {
                $row['CNT'] = 0;
                foreach ($arExecutors as $executor) {
                    if ($executor['PROPERTY_TYPE_CODE'] == $row['XML_ID']) {
                        $row['CNT']++;
                    }
                }
            }
            $arResult[ $row['ID'] ] = $row;
        }

        return $arResult;
    }

    public static function getList()
    {
        $arTypes = self::getTypesList(false);
        $arResult = [];
        Loader::includeModule('iblock');

        $arSelect = [
            'ID',
            'NAME',
            'PROPERTY_IMPLEMENTATION',
            'PROPERTY_RUKOVODITEL',
            'PROPERTY_ZAMESTITELI',
            'PROPERTY_ISPOLNITELI',
            'PROPERTY_TYPE',
            'PROPERTY_ORDER',
            'PROPERTY_PARENT_ID',
        ];
        $arFilter = [
            'IBLOCK_ID'     => Settings::$iblockId['ISPOLNITEL'],
            'ACTIVE_DATE'   => 'Y',
            'ACTIVE'        => 'Y'
        ];
        $res = CIBlockElement::GetList(
            ['NAME' => 'ASC', 'TYPE' => 'ASC'],
            $arFilter,
            false,
            false,
            $arSelect
        );
        while ($row = $res->Fetch()) {
            $row['PROPERTY_TYPE_CODE'] = '';
            if (!empty($row['PROPERTY_TYPE_ENUM_ID'])) {
                $row['PROPERTY_TYPE_CODE'] = $arTypes[ $row['PROPERTY_TYPE_ENUM_ID'] ]['XML_ID'];
            }

            $arResult[ $row['ID'] ] = $row;
        }

        uasort(
            $arResult,
            function ($a, $b) {
                return strnatcmp($a['NAME'], $b['NAME']);
            }
        );

        return $arResult;
    }
}
