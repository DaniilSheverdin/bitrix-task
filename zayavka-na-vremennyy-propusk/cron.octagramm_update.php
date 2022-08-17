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
$zayavki = Zayavka::get($date);
foreach($zayavki as $zayavka){
    if(!$zayavka->inOctagramm()) continue;

    $status = OctagramZayavka::get($zayavka);
    
    if($status['LEAVE']){
        OctagramZayavka::clear($zayavka);
        $zayavka->STATUS = Zayavka::$STATUS_LEAVE;
        $zayavka->save();
    }elseif($status['ARRIVE']){
        $zayavka->STATUS = Zayavka::$STATUS_ARRIVE;
        $zayavka->save();
    }elseif($status['GIVEN']){
        $zayavka->STATUS = Zayavka::$STATUS_GIVEN;
        $zayavka->save();
    }else{
        if($zayavka->VREMYA->format('U') < $date->format('U') + LATE_MAX){
            if(OctagramZayavka::cancel($zayavka)){
                $zayavka->STATUS = Zayavka::$STATUS_NOT_USED;
                $zayavka->save();
            }
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
$zayavki = Zayavka::get($date);
foreach($zayavki as $zayavka){
    if(!$zayavka->inOctagramm()) continue;

    $status = OctagramZayavka::get($zayavka);
    
    if($status['LEAVE']){
        OctagramZayavka::clear($zayavka);
        $zayavka->STATUS = Zayavka::$STATUS_LEAVE;
        $zayavka->save();
    }elseif($status['ARRIVE']){
        $zayavka->STATUS = Zayavka::$STATUS_ARRIVE;
        $zayavka->save();
    }elseif($status['GIVEN']){
        $zayavka->STATUS = Zayavka::$STATUS_GIVEN;
        $zayavka->save();
    }else{
        if($zayavka->VREMYA->format('U') < $date->format('U') + LATE_MAX){
            if(OctagramZayavka::cancel($zayavka)){
                $zayavka->STATUS = Zayavka::$STATUS_NOT_USED;
                $zayavka->save();
            }
        }
    }
>>>>>>> e0a0eba79 (init)
}