<?php

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;

if (!isset($_REQUEST['message'])) {
    exit;
}
$arSkip = [
    "Uncaught TypeError: Cannot read property 'catch' of undefined",
    "Uncaught SyntaxError: Block-scoped declarations (let, const, function, class)",
    "BX.IM is not a constructor", // Если в яндексе остановить загрузку до загрузки футера
];
foreach ($arSkip as $mess) {
    if (false !== mb_strpos($_REQUEST['message'], $mess)) {
        exit;
    }
}

require $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";
require $_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php";

$logger = new Logger('default');
$logger->pushHandler(
    new RotatingFileHandler(
        $_SERVER['DOCUMENT_ROOT'] . '/local/logs/errors/js.log',
        90,
        Logger::ERROR
    )
);

$logger->error(str_repeat('==', 10) . ' ' . '[' . $GLOBALS['USER']->GetID() . '] ' . $GLOBALS['USER']->GetFullName() . ' ' . str_repeat('==', 10));
$logger->error($_REQUEST['message'], $_REQUEST['params']);
