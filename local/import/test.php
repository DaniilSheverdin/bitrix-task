<?

setlocale(LC_NUMERIC, 'C');
$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__) . "/../..");
$DOCUMENT_ROOT            = $_SERVER["DOCUMENT_ROOT"];

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define('CHK_EVENT', true);
define('MODULE_NAME', 'citto.integration');
//Require
require $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";
require $_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php";

//Uses
use Bitrix\Main\Config\Option;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Citto\Integration;
use Citto\Integration\Delo;
use Bitrix\Main\{Page\Asset, Web\Json, UI};
CModule::IncludeModule(MODULE_NAME);
$arModulesOptions = unserialize(Option::get(MODULE_NAME, "values"));

if (CModule::IncludeModule("socialnetwork")) {
    $results = $DB->Query("SELECT * FROM `b_sonet_user2group` WHERE `GROUP_ID`='636'");
    //выполняем произвольный запрос
    
    $name_array=array();
    //создаем пустой массив, но можно эту строчку исключить
    $n=0;
    while ($row = $results->Fetch()) {
        if ($row['INITIATED_BY_USER_ID']!=$row['USER_ID']) {
            $n++;
            CSocNetUserToGroup::Add(
                array(
                    "USER_ID" => $row['USER_ID'],
                    "GROUP_ID" => 636,
                    "ROLE" => SONET_ROLES_USER,
                    "=DATE_CREATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
                    "=DATE_UPDATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
                    "INITIATED_BY_TYPE" => SONET_INITIATED_BY_USER,
                    "INITIATED_BY_USER_ID" => $row['USER_ID'],
                    "MESSAGE" => false,
                )
            );
        }
    }
}
