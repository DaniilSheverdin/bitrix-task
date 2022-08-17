<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
CJSCore::Init(['popup', 'date']);
$component_epilog_forms = __DIR__ . "/forms/" .
    $arParams['IBLOCK_ID'] . "/component_epilog.php";

if (file_exists($component_epilog_forms)) {
    include $component_epilog_forms;
}