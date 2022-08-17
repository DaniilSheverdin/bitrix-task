<?
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

use Bitrix\Main,
    Bitrix\Main\Localization\Loc,
    Bitrix\Main\Loader;

define("STOP_STATISTICS", true);
define("BX_SECURITY_SHOW_MESSAGE", true);
define('ADMIN_SECTION', true);
define('SITE_ID', 'ru');

CModule::IncludeModule('lnpa.module');
CUtil::JSPostUnescape();

global $APPLICATION;

$sBanSym = trim($_REQUEST['BAN_SYM']);
$arBanSym = str_split($sBanSym, 1);
$sRepSym = trim($_REQUEST['REP_SYM']);
$arRepSym = array_fill(0, sizeof($arBanSym), $sRepSym);

$APPLICATION->RestartBuffer();

$iBlockID = Lnpa\CBeforeInit::getIBlockLnpa();
$obEnum = CIBlockPropertyEnum::GetList([], ["IBLOCK_ID" => $iBlockID, "XML_ID" => "DRAFT", "CODE" => "UF_STATUS"]);
$iDraftEnumID = $obEnum->GetNext()['ID'];

$arFilter = [
    '%NAME' => trim($_REQUEST['search']),
    'IBLOCK_ID' => $iBlockID,
    '!PROPERTY_UF_STATUS' => $iDraftEnumID
];
$obRes = CIBlockElement::GetList(array(), $arFilter, false, array("nTopCount" => 20), array("ID", "NAME"));

$arResult = [];
while ($arRes = $obRes->Fetch()) {
    $arResult[] = array(
        'ID' => $arRes['ID'],
        'NAME' => str_replace($arBanSym, $arRepSym, $arRes['NAME']),
    );
}

header('Content-Type: application/json');
echo json_encode($arResult);
die();
