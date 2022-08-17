<?php

namespace Citto\Iblock;

use Bitrix\Main\Loader;
use Bitrix\Main\EventManager;

Loader::registerAutoLoadClasses(
    null,
    [
        __NAMESPACE__ . '\\listElementWithDescription' => '/local/php_interface/classes/iblock/listElementWithDescription.php',
    ]
);

$eventManager = EventManager::getInstance();
$eventManager->addEventHandler('iblock', 'OnIBlockPropertyBuildList', [__NAMESPACE__ . '\\listElementWithDescription',  'GetIBlockPropertyDescription']);
