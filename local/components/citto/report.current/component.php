<?

use Bitrix\Main\Loader;
use Sprint\Migration\Helpers\IblockHelper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

global $userFields, $USER;

Loader::includeModule('iblock');
Loader::includeModule('sprint.migration');

$arResult['REPORT_FILES'] = [];
$arSelect = array("ID", "IBLOCK_CODE", "PROPERTY_EMPLOYEE", "PROPERTY_REPORT_FILE", "PROPERTY_ADD_REPORT");
$arFilter = array("IBLOCK_CODE" => "certification_report", "ACTIVE_DATE" => "Y", "ACTIVE" => "Y", "PROPERTY_END" => "Y", "PROPERTY_EMPLOYEE" => $arResult['VARIABLES']['user_id']);
$obReport = CIBlockElement::GetList(array(), $arFilter, false, [], $arSelect);
$arTmpFiles = [];
while ($arReport = $obReport->GetNext()) {
    $arFiles = [$arReport['PROPERTY_REPORT_FILE_VALUE'], $arReport['PROPERTY_ADD_REPORT_VALUE']];
    foreach ($arFiles as $iFileID) {
        if ($iFileID && !in_array($iFileID, $arTmpFiles)) {
            $arTmpFiles[] = $iFileID;
            $arResult['REPORT_FILES'][] = CFile::GetFileArray($iFileID);
        }
    }
}
unset($arTmpFiles);

$this->IncludeComponentTemplate();
