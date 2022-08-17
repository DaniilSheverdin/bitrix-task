<?php

if (php_sapi_name() !== 'cli') {
    header('HTTP/1.0 404 Not Found');
    return;
}
define('RULE_EMAILTASKS', 1);

$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__)."/../..");
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];


define("IS_CRON", true);
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define('CHK_EVENT', false);
define('NO_MB_CHECK', true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
@set_time_limit(0);
@ignore_user_abort(true);

global $USER;
$USER->Authorize(1);
if (!$USER->IsAdmin()) {
    return;
}

\Bitrix\Main\Loader::includeModule('mail');
CMailbox::CheckMailAgent(4);

$messages = (\Bitrix\Main\Application::getConnection())
                ->query('SELECT ID FROM b_mail_message WHERE NEW_MESSAGE = "Y" AND DATE(DATE_INSERT) > DATE(NOW() - INTERVAL 2 DAY);')
                ->fetchAll();
if ($messages) {
    foreach ($messages as $message) {
        CMailFilter::FilterMessage($message['ID'], "M", RULE_EMAILTASKS);
    }
}

$USER->Logout();
