<?php

namespace Citto\Mentoring;

use Bitrix\Main\Loader;
use Bitrix\Main\EventManager;

Loader::registerAutoLoadClasses(
    null,
    [
        __NAMESPACE__ . '\\Users' => '/local/php_interface/classes/mentoring/users.php',
    ]
);
