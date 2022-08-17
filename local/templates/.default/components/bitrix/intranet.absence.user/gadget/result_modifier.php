<?php
$arTypes = [];
$absenceList = CIBlockProperty::GetPropertyEnum("ABSENCE_TYPE", [], Array("IBLOCK_CODE" => 'absence'));
while ($type = $absenceList->GetNext()) {
    $arTypes['ID'][$type['ID']] = ['XML_ID' => $type['XML_ID'], 'VALUE' => $type['VALUE']];
    $arTypes['XML_ID'][$type['XML_ID']] = ['ID' => $type['ID'], 'VALUE' => $type['VALUE']];
}

$arResult['ENTRIS_TYPES'] = $arTypes;
