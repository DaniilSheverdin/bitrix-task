<?php

IncludeModuleLangFile(__FILE__);

$aMenu = array();


$aMenu[] = array(
    "parent_menu" => "global_menu_services",
    "sort" => 1000,
    "text" => GetMessage("CITTO_INTEGRATION_MENU"),
    "title" => "Настройки",
    "icon" => "default_menu_icon",
    "page_icon" => "default_menu_icon",
    "items_id" => "citto_integration_settings",

    "url" => "citto_integration_settings.php?lang=" . LANGUAGE_ID,
    "more_url" => array(),

    "items" => array(),
);


return (!empty($aMenu)) ? $aMenu : false;
