<?php

use Citto\Vaccinationcovid19\Component as Vaccinationcovid19;

$start = microtime(true);

header('Access-Control-Allow-Origin: *');

if (isset($_REQUEST['token']) && in_array($_REQUEST['token'], [md5(570)])) {
    define('NEED_AUTH', false);
    define('NOT_CHECK_PERMISSIONS', true);
    define('NO_KEEP_STATISTIC', true);
}
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

header('Content-Type: application/json');

if (!isset($_REQUEST['get'])) {
    die(json_encode(['error' => 'noauth']));
}

$arResult = [];
$startProcessing = microtime(true);
if (isset($_REQUEST['get'])) {
    CBitrixComponent::includeComponentClass('citto:vaccination_covid19');

    switch ($_REQUEST['get']) {
        case 'stat_oiv':
            $obVaccinationcovid19 = new Vaccinationcovid19();
            $arResult['DATA'] = $obVaccinationcovid19->getRecordsForApi();
            break;
        default:
            die(json_encode(['error' => 'unknown method']));
            break;
    }
}

$finish = microtime(true);
$result = [
    'result' => $arResult,
    'time' => [
        'start' => $start,
        'finish' => $finish,
        'duration' => $finish - $start,
        'processing' => $finish - $startProcessing,
        'date_start' => date('d.m.Y H:i:s', $start),
        'date_finish' => date('d.m.Y H:i:s', $finish),
    ],
];

function utf8ize($d)
{
    if (is_array($d)) {
        foreach ($d as $k => $v) {
            $d[$k] = utf8ize($v);
        }
    } else {
        if (is_string($d)) {
            $enc = mb_detect_encoding($d);
            $value = iconv($enc, 'UTF-8//IGNORE', $d);
            //echo $enc."-".$value."<br>";
            return $value;
        }
    }
    return $d;
}

echo json_encode(utf8ize($result));
//echo json_last_error();
die();
