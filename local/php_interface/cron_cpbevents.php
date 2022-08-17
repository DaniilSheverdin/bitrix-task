<?php

/**
 * Отправка почтовых событий БП, фильтр по
 * EVENT_NAME IN('ZNVP_NEW', 'BP_TASK_ADDED', 'BP_APPROVE_TASK', 'BP_CHANGES', 'BIZPROC_HTML_MAIL_TEMPLATE', 'BIZPROC_MAIL_TEMPLATE')
 */

use Bitrix\Main\Mail\Internal\EventAttachmentTable;
use Bitrix\Main\Mail\Internal\EventTable;
use Bitrix\Main\Mail\Event;
use Bitrix\Main\Mail\EventManager;
use Bitrix\Main\Config as Config;
use Bitrix\Main\Type as Type;

$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__)."/../..");
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define('BX_NO_ACCELERATOR_RESET', true);
define('CHK_EVENT', true);
define('BX_WITH_ON_AFTER_EPILOG', true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

@set_time_limit(100);
@ignore_user_abort(true);

define("BX_CRONTAB_SUPPORT", true);
define("BX_CRONTAB", true);


class CPBEventManager extends EventManager
{
    public static function executeEvents()
    {
        $bulk = intval(Config\Option::get("main", "mail_event_bulk", 5));
        if ($bulk <= 0) {
            $bulk = 5;
        }

        $rsMails = null;

        $connection = \Bitrix\Main\Application::getConnection();
        if ($connection instanceof \Bitrix\Main\DB\MysqlCommonConnection) {
            $uniq = Config\Option::get("main", "server_uniq_id", "");
        }

        if (mb_strlen($uniq) <= 0) {
            $uniq = md5(uniqid(rand(), true));
            Config\Option::set("main", "server_uniq_id", $uniq);
        }

        $strSql= "SELECT 'x' FROM b_event WHERE SUCCESS_EXEC='N' LIMIT 1";
        $resultEventDb = $connection->query($strSql);
        if ($resultEventDb->fetch()) {
            $lockDb = $connection->query("SELECT GET_LOCK('".$uniq."_event', 0) as L");
            $arLock = $lockDb->fetch();
            if ($arLock["L"]=="0") {
                return "";
            }
        }

        $strSql = "
            SELECT ID, C_FIELDS, EVENT_NAME, MESSAGE_ID, LID,
                DATE_FORMAT(DATE_INSERT, '%d.%m.%Y %H:%i:%s') as DATE_INSERT,
                DUPLICATE, LANGUAGE_ID
            FROM b_event
            WHERE SUCCESS_EXEC='N' AND EVENT_NAME IN('ZNVP_NEW', 'BP_TASK_ADDED', 'BP_APPROVE_TASK', 'BP_CHANGES', 'BIZPROC_HTML_MAIL_TEMPLATE', 'BIZPROC_MAIL_TEMPLATE')
            ORDER BY ID
            LIMIT ".$bulk;

        $rsMails = $connection->query($strSql);
        if ($rsMails) {
            $arCallableModificator = array();
            $cnt = 0;
            foreach (EventTable::getFetchModificatorsForFieldsField() as $callableModificator) {
                if (is_callable($callableModificator)) {
                    $arCallableModificator[] = $callableModificator;
                }
            }
            while ($arMail = $rsMails->fetch()) {
                foreach ($arCallableModificator as $callableModificator) {
                    $arMail['C_FIELDS'] = call_user_func_array($callableModificator, array($arMail['C_FIELDS']));
                }

                $arFiles = array();
                $fileListDb = EventAttachmentTable::getList(array(
                    'select' => array('FILE_ID'),
                    'filter' => array('=EVENT_ID' => $arMail["ID"])
                ));
                while ($file = $fileListDb->fetch()) {
                    $arFiles[] = $file['FILE_ID'];
                }
                $arMail['FILE'] = $arFiles;

                if (!is_array($arMail['C_FIELDS'])) {
                    $arMail['C_FIELDS'] = array();
                }
                try {
                    $flag = Event::handleEvent($arMail);
                    EventTable::update($arMail["ID"], array('SUCCESS_EXEC' => $flag, 'DATE_EXEC' => new Type\DateTime));
                } catch (\Exception $e) {
                    EventTable::update($arMail["ID"], array('SUCCESS_EXEC' => "E", 'DATE_EXEC' => new Type\DateTime));

                    $application = \Bitrix\Main\Application::getInstance();
                    $exceptionHandler = $application->getExceptionHandler();
                    $exceptionHandler->writeToLog($e);

                    break;
                }

                $cnt++;
                if ($cnt >= $bulk) {
                    break;
                }
            }
        }

        if ($connection instanceof \Bitrix\Main\DB\MysqlCommonConnection) {
            $connection->query("SELECT RELEASE_LOCK('".$uniq."_event')");
        } elseif ($connection instanceof \Bitrix\Main\DB\MssqlConnection) {
            $connection->query("SET LOCK_TIMEOUT -1");
            $connection->commitTransaction();
        } elseif ($connection instanceof \Bitrix\Main\DB\OracleConnection) {
            $connection->commitTransaction();
        }

        return null;
    }
}

CPBEventManager::CheckEvents();
