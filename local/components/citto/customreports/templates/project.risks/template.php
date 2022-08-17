<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI\Extension;
use Citto\Tasks\ProjectInitiative;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) {
    die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$css = [
    '/bitrix/templates/.default/bootstrap.min.css',
    '/local/js/jstree/themes/default/style.min.css',
    '/bitrix/css/main/grid/webform-button.css',
    '/local/js/adminlte/css/AdminLTE.min.css',
    '/local/js/adminlte/css/skins/_all-skins.min.css',
];
array_walk(
    $css,
    static function ($path) {
        Asset::getInstance()
            ->addCss($path);
    }
);

Asset::getInstance()->addJs("/local/js/bootstrap-plugin/popper.min.js");
Asset::getInstance()->addJs('/bitrix/templates/.default/bootstrap.min.js');

Extension::load(['ui.forms', 'ui.notification']);

include __DIR__ . '/main.php';
