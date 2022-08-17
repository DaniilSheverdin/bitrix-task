<?php

namespace Citto\Iblock;

use CIBlockElement;

class listElementWithDescription
{
    function GetIBlockPropertyDescription()
    {
        return [
            'PROPERTY_TYPE'         => 'E', // основываемся на привязке к элементам
            'USER_TYPE'             => 'listElementWithDescription',
            'DESCRIPTION'           => 'Привязка к элементам с описанием',
            'GetPropertyFieldHtml'  => [__CLASS__, 'GetPropertyFieldHtml'],
            'ConvertToDB'           => [__CLASS__, 'ConvertToDB'],
            'ConvertFromDB'         => [__CLASS__, 'ConvertFromDB'],
        ];
    }
     
    function GetPropertyFieldHtml($arProperty, $value, $strHTMLControlName)
    {
        $value['DESCRIPTION'] = unserialize($value['DESCRIPTION']);

        $arItem = [
            'ID'        => 0,
            'IBLOCK_ID' => 0,
            'NAME'      => ''
        ];

        if (intval($value['VALUE']) > 0) {
            $arFilter = [
                'ID'        => intval($value['VALUE']),
                'IBLOCK_ID' => $arProperty['LINK_IBLOCK_ID'],
            ];
 
            $arItem = CIBlockElement::GetList([], $arFilter, false, false, ['ID', 'IBLOCK_ID', 'NAME'])->Fetch();
        }

        return '<input name="'.$strHTMLControlName["VALUE"].'" id="'.$strHTMLControlName["VALUE"].'" value="'.htmlspecialcharsEx($value["VALUE"]).'" size="5" type="text"><input type="button" value="..." onClick="jsUtils.OpenWindow(\'/bitrix/admin/iblock_element_search.php?lang='.LANG.'&amp;IBLOCK_ID='.$arProperty["LINK_IBLOCK_ID"].'&amp;n='.$strHTMLControlName["VALUE"].'&amp;k='.$key.'&amp;iblockfix=y\', 900, 700);">&nbsp;<span id="sp_'.md5($strHTMLControlName["VALUE"]).'_'.$key.'">'.$arItem["NAME"].'</span>&nbsp;&nbsp;&nbsp;Описание: <input type="text" name="'.$strHTMLControlName["DESCRIPTION"].'" value="'.htmlspecialcharsEx($value["DESCRIPTION"]).'" />';
    }
     
    function GetAdminListViewHTML($arProperty, $value, $strHTMLControlName)
    {
        return;
    }
     
    function ConvertToDB($arProperty, $value)
    {
        $return = false;
        if (is_array($value) && array_key_exists('VALUE', $value)) {
            $return = [
                'VALUE' => serialize($value['VALUE'])
            ];
        }

        if (is_array($value) && array_key_exists('DESCRIPTION', $value) && !empty($value['DESCRIPTION'])) {
            $return['DESCRIPTION'] = serialize($value['DESCRIPTION']);
        }

        return $return;
    }
     
    function ConvertFromDB($arProperty, $value)
    {
        $return = false;
         
        if (!is_array($value['VALUE'])) {
            $return = [
                'VALUE' => unserialize($value['VALUE'])
            ];
        }

        return $return;
    }
}
