<?php

namespace Citto;

use Bitrix\Main\Loader;
use Bitrix\Main\EventManager;

include_once(__DIR__ . '/tasks/include.php');
include_once(__DIR__ . '/controlorders/include.php');
include_once(__DIR__ . '/mentoring/include.php');
include_once(__DIR__ . '/iblock/include.php');

Loader::registerAutoLoadClasses(
    null,
    [
        __NAMESPACE__ . '\\Mfc' => '/local/php_interface/classes/mfc.php',
        __NAMESPACE__ . '\\Pull' => '/local/php_interface/classes/pull.php',
        __NAMESPACE__ . '\\IM' => '/local/php_interface/classes/im.php',
        __NAMESPACE__ . '\\Blog' => '/local/php_interface/classes/blog.php',
        __NAMESPACE__ . '\\Bizproc' => '/local/php_interface/classes/bizproc.php',
        __NAMESPACE__ . '\\Tasks' => '/local/php_interface/classes/tasks.php',
        'CTasksReportHelperCustom' => '/local/php_interface/classes/report.php',
    ]
);

$eventManager = EventManager::getInstance();
$eventManager->addEventHandler('im', 'OnAfterContactListGetList', [__NAMESPACE__ . '\\Mfc',  'handleAfterContactListGetList']);
$eventManager->addEventHandler('socialnetwork', 'OnSocNetLogDestinationSearchUsers', [__NAMESPACE__ . '\\Mfc',  'handleOnSocNetLogDestinationSearchUsers']);
$eventManager->addEventHandler('main', 'OnBeforeUserUpdate', [__NAMESPACE__ . '\\Mfc',  'handleOnBeforeUserUpdate']);
$eventManager->addEventHandler('main', 'OnBeforeUserAdd', [__NAMESPACE__ . '\\Mfc',  'handleOnBeforeUserAdd']);
$eventManager->addEventHandler('tasks', 'OnBeforeTaskUpdate', [__NAMESPACE__ . '\\Mfc', 'handlerTasksDirectControlUpdate']);
$eventManager->addEventHandler('tasks', 'OnBeforeTaskAdd', [__NAMESPACE__ . '\\Mfc', 'handlerTasksDirectControlAdd']);
$eventManager->addEventHandler('socialnetwork', 'OnBeforeSocNetLogAdd', [__NAMESPACE__ . '\\Mfc',  'handleOnBeforeSocNetLogAdd']);

$eventManager->addEventHandler('pull', 'OnBeforeSendPush', [__NAMESPACE__ . '\\Pull',  'handleOnBeforeSendPush']);

$eventManager->addEventHandler('im', 'OnGetNotifySchema', [__NAMESPACE__ . '\\IM',  'handleOnGetNotifySchema']);
$eventManager->addEventHandler('im', 'OnBeforeMessageNotifyAdd', [__NAMESPACE__ . '\\IM',  'handleOnBeforeMessageNotifyAdd']);

$eventManager->addEventHandler('blog', 'OnPostAdd', [__NAMESPACE__ . '\\Blog',  'handleOnPostAdd']);
$eventManager->addEventHandler('blog', 'OnPostUpdate', [__NAMESPACE__ . '\\Blog',  'handleOnPostUpdate']);
$eventManager->addEventHandler('socialnetwork', 'OnAfterCBlogUserOptionsSet', [__NAMESPACE__ . '\\Blog',  'handleOnAfterCBlogUserOptionsSet']);

$eventManager->addEventHandler('tasks', 'OnBeforeTaskAdd', [__NAMESPACE__ . '\\Tasks',  'handleBeforeTaskAdd']);
$eventManager->addEventHandler('tasks', 'OnBeforeTaskUpdate', [__NAMESPACE__ . '\\Tasks',  'handleBeforeTaskUpdate']);
