<?php

CModule::IncludeModule('bitrix.planner');

function treeFunc($IBLOCK_ID, $SECTION_ID = 0, $intDeep = 0, $arOTDELList = [])
{
    if ($intDeep >= 20) {
        return $arOTDELList;
    }
    $intDeep += 1;
    $objDbOtdels = CIBlockSection::GetList(
        ["SORT"=>"ASC"],
        ['IBLOCK_ID'=> $IBLOCK_ID, 'GLOBAL_ACTIVE'=>'Y', 'SECTION_ID' => $SECTION_ID],
        false,
        ['UF_HEAD', 'UF_HEAD2', 'UF_OTV_KADR', 'UF_BUHGALTER', 'UF_BUHGALTER_ZAM']
    );
    while ($arOtdel = $objDbOtdels->GetNext()) {
        $arOTDELList[$arOtdel['ID']]['NAME'] = $arOtdel['NAME']; //str_repeat('.', $intDeep).$arOtdel['NAME'];
        $arOTDELList[$arOtdel['ID']]['ID'] = $arOtdel['ID'];
        $arOTDELList[$arOtdel['ID']]['UF_HEAD'] = $arOtdel['UF_HEAD'];
        $arOTDELList[$arOtdel['ID']]['UF_HEAD2'] = $arOtdel['UF_HEAD2'];
        $arOTDELList[$arOtdel['ID']]['UF_OTV_KADR'] = $arOtdel['UF_OTV_KADR'];
        $arOTDELList[$arOtdel['ID']]['UF_BUHGALTER'] = $arOtdel['UF_BUHGALTER'];
        $arOTDELList[$arOtdel['ID']]['UF_BUHGALTER_ZAM'] = $arOtdel['UF_BUHGALTER_ZAM'];
    }

    return $arOTDELList;
}

function isUserCit($iCitID)
{
    global $USER;
    $obUsers = HolidayList\CUsers::getInstance();
    $arCanSeeUsers = $obUsers->canSeeUsers($iCitID);
    return (in_array($USER->getID(), $arCanSeeUsers));
}
