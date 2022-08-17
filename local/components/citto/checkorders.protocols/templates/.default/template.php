<?php

use Bitrix\Main\Page\Asset;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

Asset::getInstance()->addJs('/bitrix/templates/.default/bootstrap.min.js');
Asset::getInstance()->addCss('/bitrix/templates/bitrix24/css/sidebar.css');
Asset::getInstance()->addCss('/local/components/citto/checkorders/templates/.default/style.css');
