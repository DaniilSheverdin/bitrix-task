<?php
$obEl = new CIBlockElement;
$iBlockID = CIBlock::GetList([], ["CODE" => 'risks'], true)->Fetch()['ID'];
$arResult['IBLOCK_ID'] = $iBlockID;

$arResult['LIST_ID'] = 'project_risks';
$arResult['FILTER'] = [];

$arFields = array(
    'MODIFIED_BY' => $GLOBALS['USER']->GetID(),
    'IBLOCK_ID' => $iBlockID,
    'PROPERTY_VALUES' => [],
    'NAME' => $_POST['RISK_NAME'],
    'ACTIVE' => 'Y',
);

$arEnums = [];
$obPropertyEnums = CIBlockPropertyEnum::GetList([], ["IBLOCK_ID" => $iBlockID]);
while ($arEnum = $obPropertyEnums->GetNext()) {
    $arEnums[$arEnum['PROPERTY_CODE']][$arEnum['XML_ID']] = $arEnum['ID'];
}

$arResult['ENUMS'] = $arEnums;

if ($_POST['ADD_RISK']) {
    if ($iGroupID = $arParams['GROUP_ID']) {
        $arFields = array(
            'MODIFIED_BY' => $GLOBALS['USER']->GetID(),
            'IBLOCK_ID' => $iBlockID,
            'NAME' => $_POST['RISK_NAME'],
            'ACTIVE' => 'Y',
        );

        $arFields['PROPERTY_VALUES'] = [
            'RISK_GROUP' => $iGroupID,
            'IS_RISK' => $arEnums['IS_RISK'][$_POST['IS_RISK']],
            'RISK_TYPE' => $arEnums['RISK_TYPE'][$_POST['RISK_TYPE']],
            'RISK_ACTION' => $_POST['RISK_ACTION'],
        ];

        if (
            $arFields['PROPERTY_VALUES']['IS_RISK'] &&
            $arFields['PROPERTY_VALUES']['RISK_TYPE']
        ) {
            $obEl->Add($arFields);
            LocalRedirect($_SERVER['DOCUMENT_URI']);
        }
    }
}

if ($_REQUEST['ACTION'] == 'DELETE' && $iElementID = $_REQUEST['ID']) {
    CIBlockElement::Delete($iElementID);
    LocalRedirect($_SERVER['DOCUMENT_URI']);
}
