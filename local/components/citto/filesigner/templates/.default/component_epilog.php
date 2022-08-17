<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
$GLOBALS['APPLICATION']->AddHeadScript($templateFolder.'/js/es6-promise.min.js');
$GLOBALS['APPLICATION']->AddHeadScript($templateFolder.'/js/ie_eventlistner_polyfill.js');
$GLOBALS['APPLICATION']->AddHeadScript($templateFolder.'/js/cadesplugin_api.js');
$GLOBALS['APPLICATION']->AddHeadScript($templateFolder.'/js/plugin.js');
$GLOBALS['APPLICATION']->AddHeadScript($templateFolder.'/js/docsignactivity.js', true);
