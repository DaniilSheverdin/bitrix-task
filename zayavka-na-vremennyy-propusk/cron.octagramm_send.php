<<<<<<< HEAD
<?php
if(php_sapi_name() !== 'cli') die;
$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__)."/..");
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];
define("CLI_MODE", true);
define("CUSTOM_SITE_ID","s1");
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS",true); 
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
@set_time_limit(0);
@ignore_user_abort(true);

require_once("zayavka.class.php");

$date = new \DateTime();
if($date->format('H') > WORK_TIME_FROM && $date->format('H') < WORK_TIME_TO + 1){
    $zayavki = Zayavka::get($date, NULL, NULL, [Zayavka::$STATUS_LOADED, Zayavka::$STATUS_NO_SLOTS]);
    foreach($zayavki as $zayavka){
        $zayavka->STATUS = OctagramZayavka::add($zayavka)?Zayavka::$STATUS_WAITING:Zayavka::$STATUS_NO_SLOTS;
        $zayavka->save();
        
        if($zayavka->STATUS == Zayavka::$STATUS_WAITING){
            \Bitrix\Main\Mail\Event::send([
                'EVENT_NAME'=> "OCTAGRAM_NEW_ONE",
                'LID'       => "s1",
                'C_FIELDS'  => [
                    'DATA_VYDACHI'          => $zayavka->DATA_VYDACHI,
                    'FIO_PODAVSHEGO'        => $zayavka->FIO_PODAVSHEGO,
                    'FIO_KOMU'              => $zayavka->FIO_KOMU,
                    'VID_DOCUMENTA'         => $zayavka->VID_DOCUMENTA,
                    'NOMER_DOCUMENTA'       => $zayavka->NOMER_DOCUMENTA,
                    'DOLJNOST_PODAVSHEGO'   => $zayavka->DOLJNOST_PODAVSHEGO,
                    'K_KOMU'                => $zayavka->K_KOMU,
                    'KABINET'               => $zayavka->KABINET,
                ],
            ]); 
        }
    }
=======
<?php
if(php_sapi_name() !== 'cli') die;
$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__)."/..");
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];
define("CLI_MODE", true);
define("CUSTOM_SITE_ID","s1");
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS",true); 
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
@set_time_limit(0);
@ignore_user_abort(true);

require_once("zayavka.class.php");

$date = new \DateTime();
if($date->format('H') > WORK_TIME_FROM && $date->format('H') < WORK_TIME_TO + 1){
    $zayavki = Zayavka::get($date, NULL, NULL, [Zayavka::$STATUS_LOADED, Zayavka::$STATUS_NO_SLOTS]);
    foreach($zayavki as $zayavka){
        $zayavka->STATUS = OctagramZayavka::add($zayavka)?Zayavka::$STATUS_WAITING:Zayavka::$STATUS_NO_SLOTS;
        $zayavka->save();
        
        if($zayavka->STATUS == Zayavka::$STATUS_WAITING){
            \Bitrix\Main\Mail\Event::send([
                'EVENT_NAME'=> "OCTAGRAM_NEW_ONE",
                'LID'       => "s1",
                'C_FIELDS'  => [
                    'DATA_VYDACHI'          => $zayavka->DATA_VYDACHI,
                    'FIO_PODAVSHEGO'        => $zayavka->FIO_PODAVSHEGO,
                    'FIO_KOMU'              => $zayavka->FIO_KOMU,
                    'VID_DOCUMENTA'         => $zayavka->VID_DOCUMENTA,
                    'NOMER_DOCUMENTA'       => $zayavka->NOMER_DOCUMENTA,
                    'DOLJNOST_PODAVSHEGO'   => $zayavka->DOLJNOST_PODAVSHEGO,
                    'K_KOMU'                => $zayavka->K_KOMU,
                    'KABINET'               => $zayavka->KABINET,
                ],
            ]); 
        }
    }
>>>>>>> e0a0eba79 (init)
}