<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}

$obCache_trade = new CPHPCache();
$CACHE_ID = 'CACHE_ID';
if ($obCache_trade->InitCache($arParams['CACHE_TIME'], $CACHE_ID, '/')) {
    $arResult = $obCache_trade->GetVars();
} else {
    $obCache_trade->StartDataCache();
    
    // component text here
    $arResult = array();
    
    // saving template name to cache array
    $arResult["__TEMPLATE_FOLDER"] = $this->__folder;
    
    // writing new $arResult to cache file
    $obCache_trade->EndDataCache($arResult);
}

$this->__component->arResult = $arResult;
