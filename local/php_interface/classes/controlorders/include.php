<?php

namespace Citto\ControlOrders;

use Bitrix\Main\Loader;
use Bitrix\Main\EventManager;

Loader::registerAutoLoadClasses(
    null,
    [
        __NAMESPACE__ . '\\Agent' => '/local/php_interface/classes/controlorders/agent.php',
        __NAMESPACE__ . '\\Notify' => '/local/php_interface/classes/controlorders/notify.php',
        __NAMESPACE__ . '\\Handlers' => '/local/php_interface/classes/controlorders/handlers.php',
        __NAMESPACE__ . '\\Settings' => '/local/php_interface/classes/controlorders/settings.php',

        __NAMESPACE__ . '\\Executors' => '/local/php_interface/classes/controlorders/executors.php',
        __NAMESPACE__ . '\\GroupExecutors' => '/local/php_interface/classes/controlorders/groupexecutors.php',

        __NAMESPACE__ . '\\Orders' => '/local/php_interface/classes/controlorders/orders.php',
    ]
);

$eventManager = EventManager::getInstance();
$eventManager->addEventHandler('iblock', 'OnBeforeIBlockElementAdd', [__NAMESPACE__ . '\\Handlers',  'handleOnBeforeIBlockElementAdd']);
$eventManager->addEventHandler('iblock', 'OnAfterIBlockElementAdd', [__NAMESPACE__ . '\\Handlers',  'handleOnAfterIBlockElementAdd']);
$eventManager->addEventHandler('iblock', 'OnIBlockElementSetPropertyValuesEx', [__NAMESPACE__ . '\\Handlers',  'handleOnIBlockElementSetPropertyValuesEx']);
