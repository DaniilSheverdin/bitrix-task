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

>>>>>>> e0a0eba79 (init)
OctagramZayavka::clearAll();