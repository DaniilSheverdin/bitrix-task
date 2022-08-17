<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * @global $APPLICATION
 */

use Bitrix\Main\Page\Asset;

Asset::getInstance()->addCss('/bitrix/templates/.default/bootstrap.min.css');

Asset::getInstance()->addJs('/bitrix/templates/.default/jquery.min.js');
Asset::getInstance()->addJs("/local/js/bootstrap-plugin/popper.min.js");
Asset::getInstance()->addJs('/bitrix/templates/.default/bootstrap.min.js');

// saving template name to cache array
$arResult["__TEMPLATE_FOLDER"] = $this->__folder;
// writing new $arResult to cache file
$this->__component->arResult = $arResult;
?>