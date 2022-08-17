<?php

namespace SCUD;

use CUser;
use CUserTypeEntity;

class CUsersCorp
{
    public function __construct()
    {
        $this->checkUserFieldException();
    }

    public function getAllUsers($arSIDs = [])
    {
        $arFilter = ['ACTIVE' => 'Y'];
        if (!empty($arSIDs)) {
            $arFilter['UF_SID'] = $arSIDs;
        }

        $obUsers = CUser::GetList(
            $by = "",
            $order = "",
            $arFilter,
            [
                'FIELDS' => ['ID', 'LAST_NAME', 'NAME', 'SECOND_NAME'],
                'SELECT' => ['UF_SID', 'UF_SCUD_EXCEPTION']
            ]
        );
        $arUsers = ['SID' => [], 'FIO' => [], 'DOUBLE_USERS' => [], 'EXCEPTION' => []];
        $arDoubleUsers = [];
        while ($arUser = $obUsers->getNext()) {
            $sFio = "{$arUser['LAST_NAME']} {$arUser['NAME']} {$arUser['SECOND_NAME']}";
            $sFio = md5(str_replace(" ", "", mb_strtoupper($sFio)));

            if (!$arUser['UF_SID']) {
                if (empty($arUser['LAST_NAME']) || empty($arUser['NAME']) || empty($arUser['SECOND_NAME'])) {
                    continue;
                }

                if (!isset($arDoubleUsers[$sFio])) {
                    $arUsers['FIO'][$sFio] = $arUser['ID'];
                } else {
                    unset($arUsers['FIO'][$sFio]);
                    array_push($arDoubleUsers, $arUser['ID']);
                }
            } else {
                $arUsers['SID'][$arUser['UF_SID']] = $arUser['ID'];
            }

            if ($arUser['UF_SCUD_EXCEPTION']) {
                array_push($arUsers['EXCEPTION'], $arUser['ID']);
            }
        }
        $arUsers['DOUBLE_USERS'] = $arDoubleUsers;

        return $arUsers;
    }

    public function checkUserFieldException()
    {
        $UF_SCUD_EXCEPTION = CUserTypeEntity::GetList(array($by => $order), array('ENTITY_ID' => 'USER', 'FIELD_NAME' => 'UF_SCUD_EXCEPTION'));
        if (!$UF_SCUD_EXCEPTION->arResult) {
            $obUserField = new CUserTypeEntity();

            $arFields = array(
                "ENTITY_ID" => 'USER',
                "FIELD_NAME" => "UF_SCUD_EXCEPTION",
                "USER_TYPE_ID" => "boolean",
                "EDIT_FORM_LABEL" => array("ru" => "Не фиксировать нарушения (СКУД)", "en" => "UF_SCUD_EXCEPTION")
            );
            $obUserField->Add($arFields);
        }
    }
}
