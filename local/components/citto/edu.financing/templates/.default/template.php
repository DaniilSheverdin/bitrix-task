<?php

use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI\Extension;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

Asset::getInstance()->addJs('/local/js/select2.min.js');
//Asset::getInstance()->addJs('/bitrix/templates/.default/bootstrap.min.js');
Asset::getInstance()->addJs($this->__component->__template->__folder . '/components/FilesTable.js');

Asset::getInstance()->addCss('/local/css/select2.css');
Asset::getInstance()->addCss('/bitrix/templates/bitrix24/css/sidebar.css');
Asset::getInstance()->addCss('/bitrix/templates/.default/bootstrap.min.css');
Asset::getInstance()->addCss('/local/js/adminlte/css/AdminLTE.min.css');
Asset::getInstance()->addCss('/local/js/adminlte/css/skins/_all-skins.min.css');

Extension::load([
    'date',
    'popup',
    'sidepanel',
    'ui.buttons',
    'ui.buttons.icons',
    'ui.dialogs.messagebox',
    'ui.forms',
    'ui.tooltip',
    'ui.vue',
    'ui.vue.vuex',
]);

CJSCore::Init([
    'jquery3',
    'popup',
    'ui',
]);

if (isset($_REQUEST['detail'])) {
    require_once('detail.php');
} elseif (isset($_REQUEST['edit'])) {
    require_once('edit.php');
} else {
    require_once('list.php');
}
