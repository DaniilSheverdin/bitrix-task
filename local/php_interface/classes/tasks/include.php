<?php

namespace Citto\Tasks;

use Bitrix\Main\Loader;
use Bitrix\Main\EventManager;

$curDir = str_replace('\\', '/', __DIR__); //windows
$curDir = str_replace($_SERVER['DOCUMENT_ROOT'], '', $curDir);

Loader::registerAutoLoadClasses(
    null,
    [
        __NAMESPACE__ . '\\ProjectInitiative'		=> '/local/php_interface/classes/tasks/project_initiative.php',
        __NAMESPACE__ . '\\ProjectInitiative\\Kpi'	=> '/local/php_interface/classes/tasks/project_initiative/kpi.php',
        __NAMESPACE__ . '\\BPTaskControl'			=> '/local/php_interface/classes/tasks/bp_task_control.php',
        __NAMESPACE__ . '\\DevSprints'				=> '/local/php_interface/classes/tasks/dev_sprints.php',
    ]
);

$eventManager = EventManager::getInstance();
$eventManager->addEventHandler('tasks', 'OnTaskUpdate', [__NAMESPACE__ . '\\ProjectInitiative',  'handleTaskUpdate']);
$eventManager->addEventHandler('tasks', 'OnBeforeTaskUpdate', [__NAMESPACE__ . '\\ProjectInitiative',  'handleBeforeTaskUpdate']);
$eventManager->addEventHandler('tasks', 'OnTaskExpired', [__NAMESPACE__ . '\\ProjectInitiative',  'handleTaskExpire']);
$eventManager->addEventHandler('tasks', 'OnTaskExpiredSoon', [__NAMESPACE__ . '\\ProjectInitiative',  'handleTaskExpire']);
$eventManager->addEventHandler('socialnetwork', 'OnFillSocNetFeaturesList', [__NAMESPACE__ . '\\ProjectInitiative',  'handleOnFillSocNetFeaturesList']);
$eventManager->addEventHandler('socialnetwork', 'OnFillSocNetMenu', [__NAMESPACE__ . '\\ProjectInitiative',  'handleOnFillSocNetMenu']);
$eventManager->addEventHandler('socialnetwork', 'OnParseSocNetComponentPath', [__NAMESPACE__ . '\\ProjectInitiative', 'handleOnParseSocNetComponentPath']);

$eventManager->addEventHandler('tasks', 'OnTaskUpdate', [__NAMESPACE__ . '\\BPTaskControl', 'handlerTasksComplite']);

/* Итилиум */
$eventManager->addEventHandler('tasks', 'OnTaskUpdate', [__NAMESPACE__ . '\\BPTaskControl', 'handlerTasksItiliumStatus']);