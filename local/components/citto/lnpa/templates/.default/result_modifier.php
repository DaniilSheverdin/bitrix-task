<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
CModule::IncludeModule('lnpa.module');

use Bitrix\Main\Page\Asset;

//CJSCore::Init(array("jquery"));
Asset::getInstance()->addJs('/bitrix/templates/.default/jquery.min.js');
Asset::getInstance()->addJs("/local/js/bootstrap-plugin/popper.min.js");
Asset::getInstance()->addJs('/bitrix/templates/.default/bootstrap.min.js');
Asset::getInstance()->addJs('/local/js/bootstrap-plugin/bootstrap-select.min.js');

Asset::getInstance()->addCss("/bitrix/js/ui/bootstrap4/css/bootstrap.css");
Asset::getInstance()->addCss("/bitrix/css/main/font-awesome.css");
Asset::getInstance()->addCss("/local/css/bootstrap-plugin/bootstrap-select.min.css");

\Bitrix\Main\UI\Extension::load('ui.entity-selector');

$arResult['TEMPLATE_PATH'] = $_SERVER['DOCUMENT_ROOT'] . $this->GetFolder();
$arResult['PAGE'] = (empty($_GET['page'])) ? 'find' : $_GET['page'];

Lnpa\CBeforeInit::init();
$arRequest = Lnpa\CMain::getRequest();
$arResult['REQUEST'] = $arRequest;
$arResult['ROLE'] = Lnpa\CMain::getRole();

if ($sAction = Lnpa\CMain::getAction()) {
    $arResult[$sAction] = Lnpa\CMain::actionCard($sAction);
}

if ($arResult['PAGE'] == 'detail_view' || $arResult['PAGE'] == 'detail_edit') {
    $arResult['DETAIL_CARD'] = Lnpa\CCards::getDetailCard($arRequest['id']);
    $arUserSign = Lnpa\CSign::getUserSignElement($arRequest['id']);
    $arResult['IS_USER_SIGN'] = $arUserSign['IS_USER_SIGN'];
    $arResult['FILE_SIGN_ID'] = $arUserSign['FILE_SIGN_ID'];
} else if ($arResult['PAGE'] == 'version_view') {
    $arResult['DETAIL_CARD'] = Lnpa\CVersions::getVersionsByIDs($arRequest['id'])[$arRequest['id']];
} else if ($arResult['PAGE'] == 'sign_generation' || $arResult['PAGE'] == 'statistics') {
    if ($arRequest['id'] && in_array($arResult['ROLE'], ['ADMIN', 'CLERK'])) {
        $arResult['ITEM'] = Lnpa\CCards::getDetailCard($arRequest['id']);
        $arResult['IS_COLLECTION'] = Lnpa\CSign::isCollectionSignCard($arRequest['id']);
        $arResult['ELEMENT_SIGN'] = Lnpa\CSign::getUserSignElement($arRequest['id']);

        if ($arRequest['repeat']) {
            Lnpa\CSign::alertUsersByElement($arResult['ITEM']);
        }

        if ($arRequest['departments_users']) {
            $arSignUsers = Lnpa\CSign::getSignUsers($arRequest['departments_users']);
            if ($arSignUsers) {
                Lnpa\CSign::createCollectionSign($arSignUsers, $arRequest, $arResult['ITEM']);
            }
        }
    } else {
        die;
    }
}

$iBlockLnpa = Lnpa\CBeforeInit::getIBlockLnpa();
$arResult['FIELDS'] = Lnpa\CFields::getFields();
$arResult['STRUCTURE'] = Lnpa\CMain::getStructure($arResult['PAGE']);
$arResult['ERRORS'] = $arResult[$sAction]['ERRORS'];
$arResult['ACTION'] = $sAction;
$arResult['IBLOCK'] = $iBlockLnpa;

$iPageNav = ($arRequest['nav'] > 1) ? $arRequest['nav'] : 1;
$arResult['CARDS'] = ($sAction == 'FIND') ? $arResult[$sAction] : Lnpa\CCards::getCards([], 10, $iPageNav);

$arResult['URI'] = \LNPA\CMain::getURI();
$arResult['NAV_MENU'] = \LNPA\CMain::getNavManu();

// Если карточку добавили или обновили, очищаем значения в полях
if ($arResult[$sAction]['RESULT']['STATUS']) {
    foreach ($arResult['FIELDS']['CODE'] as &$arField) {
        unset($arField['VALUE']);
    }
    unset($arResult['FIELDS']['TAGS']);
    unset($arResult['FIELDS']['FILE']);
}

if ($arResult['PAGE'] == "find") {
    $arCurrentStructureIDs = Lnpa\CMain::getCurrentStructure();
    if ($arCurrentStructureIDs) {
        $arTmpStructure = [];
        foreach ($arCurrentStructureIDs as $iID) {
            $arTmpStructure[$iID] = $arResult['STRUCTURE'][$iID];
        }
        $arResult['STRUCTURE'] = $arTmpStructure;
        unset($arTmpStructure);
    }
}

$arResult["__TEMPLATE_FOLDER"] = $this->__folder;
$this->__component->arResult = $arResult;
