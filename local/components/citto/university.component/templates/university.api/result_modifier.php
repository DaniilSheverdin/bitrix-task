<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}

header('Content-Type: application/json');

$obCache_trade = new CPHPCache();
$CACHE_ID = 'CACHE_OPINIONS_MAIN';
if ($obCache_trade->InitCache($arParams['CACHE_TIME'], $CACHE_ID, '/')) {
    $arResult = $obCache_trade->GetVars();
} else {
    function debug($arr)
    {
        if (isset($_REQUEST['debug'])) {
            pre($arr);
        }
    }

    $obCache_trade->StartDataCache();
    CModule::IncludeModule('iblock');

    $sVersion = 'v'.$_REQUEST['version'];

    switch ($_REQUEST['action']) {
        case 'structure':
            include_once("include/$sVersion/structure.php");
            break;
    }

    $arResult["__TEMPLATE_FOLDER"] = $this->__folder;
    $obCache_trade->EndDataCache($arResult);
}

$this->__component->arResult = $arResult;
