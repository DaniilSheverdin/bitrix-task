<?

setlocale(LC_NUMERIC, 'C');
set_time_limit(0);
$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__) . "/../../../..");
$DOCUMENT_ROOT            = $_SERVER["DOCUMENT_ROOT"];
define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define('CHK_EVENT', true);
require $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";
use Bitrix\Main\Entity;
use Bitrix\Highloadblock as HL;
$arModulesOptions['ac_password']='password';
if ($_REQUEST['p'] != $arModulesOptions['ac_password']) {
    if ($argv[0] != '') {
        echo('SBS: Started Covid Import');
        $started_name = 'SBS:';
    } else {
        echo('WBS: Not password accepted');
        die();
    }
} else {
    $started_name = 'WBS:';
    echo('WBS: Started Covid Import');
}

class TooManyRequests extends Exception
{
}

class Dadata
{
    private $base_url = "https://suggestions.dadata.ru/suggestions/api/4_1/rs";
    private $token;
    private $handle;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function init()
    {
        $this->handle = curl_init();
        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($this->handle, CURLOPT_HTTPHEADER, array(
            "Content-Type: application/json",
            "Accept: application/json",
            "Authorization: Token " . $this->token
        ));
        curl_setopt($this->handle, CURLOPT_POST, 1);
    }

    /**
     * See https://dadata.ru/api/outward/ for details.
     */
    public function findById($type, $fields)
    {
        $url = $this->base_url . "/findById/$type";
        return $this->executeRequest($url, $fields);
    }

    /**
     * See https://dadata.ru/api/geolocate/ for details.
     */
    public function geolocate($lat, $lon, $count = 10, $radius_meters = 100)
    {
        $url = $this->base_url . "/geolocate/address";
        $fields = array(
            "lat" => $lat,
            "lon" => $lon,
            "count" => $count,
            "radius_meters" => $radius_meters
        );
        return $this->executeRequest($url, $fields);
    }

    /**
     * See https://dadata.ru/api/iplocate/ for details.
     */
    public function iplocate($ip)
    {
        $url = $this->base_url . "/iplocate/address?ip=" . $ip;
        return $this->executeRequest($url, $fields = null);
    }

    /**
     * See https://dadata.ru/api/suggest/ for details.
     */
    public function suggest($type, $fields)
    {
        $url = $this->base_url . "/suggest/$type";
        return $this->executeRequest($url, $fields);
    }

    public function close()
    {
        curl_close($this->handle);
    }

    private function executeRequest($url, $fields)
    {
        curl_setopt($this->handle, CURLOPT_URL, $url);
        if ($fields != null) {
            curl_setopt($this->handle, CURLOPT_POST, 1);
            curl_setopt($this->handle, CURLOPT_POSTFIELDS, json_encode($fields));
        } else {
            curl_setopt($this->handle, CURLOPT_POST, 0);
        }
        $result = $this->exec();
        $result = json_decode($result, true);
        return $result;
    }

    private function exec()
    {
        $result = curl_exec($this->handle);
        $info = curl_getinfo($this->handle);
        if ($info['http_code'] == 429) {
            throw new TooManyRequests();
        } elseif ($info['http_code'] != 200) {
            throw new Exception('Request failed with http code ' . $info['http_code'] . ': ' . $result);
        }
        return $result;
    }
}

$dadata = new Dadata("202ef02ba212fda90bb83c1957d4f84c1d14aea8");
$dadata->init();
$csvFile = new CCSVData('R', true);
$sIshFileName=$argv[1];
$csvFile->LoadFile($_SERVER['DOCUMENT_ROOT'].'/upload/adres/'.$sIshFileName.'.csv');
$csvFile->SetDelimiter(',');
$arData=[];
$n=0;
$fp = fopen($_SERVER['DOCUMENT_ROOT'].'/upload/adres/'.$sIshFileName.'_result.csv', 'w');
$fp2 = fopen($_SERVER['DOCUMENT_ROOT'].'/upload/adres/'.$sIshFileName.'_result1251.csv', 'w');
while ($arRes = $csvFile->Fetch()) {
    mb_detect_order("UTF-8,WINDOWS-1251");
    $sDetectEncoding = mb_detect_encoding($arRes[0]);
    $arRes = $GLOBALS["APPLICATION"]->ConvertCharsetArray($arRes, $sDetectEncoding, SITE_CHARSET);
    $fields = array("query"=>'Тульская область,'.$arRes['5'], "count"=>1);
    $aResult = $dadata->suggest("address", $fields);
    $area=$aResult['suggestions'][0]['data']['area_with_type'];
    if ($area!='') {
        $arRes[6]=$area;
    } else {
        $arRes[6]=$aResult['suggestions'][0]['data']['city_with_type'];
    }
    fputcsv($fp, $arRes, ';', '"', "\0");
    foreach ($arRes as $sKey => $sValue) {
        $arRes[$sKey]=iconv('UTF-8', 'CP1251', $sValue);
    }
    fputcsv($fp2, $arRes, ';', '"', "\0");
    $arData[]=$arRes;
    $n++;
}
file_put_contents($_SERVER['DOCUMENT_ROOT'].'/upload/adres/'.$sIshFileName.'.json', json_encode($arData));
file_put_contents($_SERVER['DOCUMENT_ROOT'].'/upload/adres/'.$sIshFileName.'_ok.csv', date('d.m.Y H:i:s'));
fclose($fp);
$dadata->close();
