<?php

$treeFunc = function ($IBLOCK_ID, $SECTION_ID, $minusrepeat = 0) {
    CModule::includeModule("iblock");
    CModule::IncludeModule('intranet');

    $arrSecIds = CIntranetUtils::GetDeparmentsTree($SECTION_ID, true);
    $arrSecIds[] = $SECTION_ID;

    $objTree = CIBlockSection::GetTreeList(
        ["IBLOCK_ID" => $IBLOCK_ID, 'SECTION_ID' => $arrSecIds],
        ["ID", "NAME", "DEPTH_LEVEL"]
    );

    $arList = [];
    while ($depItem = $objTree->GetNext()) {
        $arList[$depItem['ID']] = $depItem;
        $arList[$depItem['ID']]['NAME'] = str_repeat('.', $depItem['DEPTH_LEVEL'] - $minusrepeat).$depItem['NAME'];
    }

    return $arList;
};
