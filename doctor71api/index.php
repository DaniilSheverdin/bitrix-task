<?
define('NEED_AUTH', false);
define('NOT_CHECK_PERMISSIONS', true);
define('NO_KEEP_STATISTIC', true);
require $_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php";
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/vendor/autoload.php';

/**
 * @global $APPLICATION
 */
?><?

if(isset($_REQUEST['token']) && $_REQUEST['token'] == 'gLjr32RWh32grghe3h23Pjw') {
	$APPLICATION->IncludeComponent(
		"citto:doctortask",
		"main",
        $_REQUEST
	);
}
?>