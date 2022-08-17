<?php

use Citto\Mentoring\Users as MentoringUsers;

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
    CBitrixComponent::includeComponentClass('citto:scud');

    switch ($_REQUEST['get']) {
        case 'user_analytics':
            $obDate = new DateTime();
            $iMonth = $obDate->modify('-1 month')->format('m');
            $iYear = $obDate->modify('-1 month')->format('Y');
            $iCountDays = cal_days_in_month(CAL_GREGORIAN, $iMonth, $iYear);
            $iStartDate = strtotime("01.$iMonth.$iYear 00:00:00");
            $iEndDate = strtotime("$iCountDays.$iMonth.$iYear 23:59:59");
            if ($_GET['debug']) {
                var_dump($iStartDate);
                var_dump($iEndDate);
            }
            $arUsersIDs = [];
            foreach (MentoringUsers::getUsersWithStrcuture() as $iUserID => $arUser) {
                if ($arUser['DEPARTMENT']['PODVED'] == 'N') {
                    $arUsersIDs[$iUserID] = $arUser['DEPARTMENT']['NAME'];
                }
            }

            $arUsers = SCUD::getUsers(array_keys($arUsersIDs));

            $arAbsence = SCUD::getAbsence(
                $iStartDate,
                $iEndDate,
                $arUsers,
                $arViolations = [],
                $arEvent = ['ALL'],
                $sPage = 'events',
                $iRecordFrom = null,
                $iRecordTo = null,
                $sExport = null,
                $sApiMode = true
            );
            if ($_GET['debug']) {
                echo "<pre>";
                print_r(count($arAbsence));
                echo "</pre>";
            }
            $arResult = SCUD::exportAnalytics(
                $arAbsence,
                $sPage = 'events',
                $sFile = 'none',
                $bOutput = true,
                $iStartDate,
                $iEndDate,
                $sTypeAnalytics = 'users',
                $sAjaxMode = true
            );

            foreach ($arResult as $iID => $arUser) {
                if (floatval($arUser['coefficient']) > 0) {
                    $iUserID = $arUser['user_id'];
                    $arResult[$iID]['department'] = $arUsersIDs[$iUserID];
                } else {
                    unset($arResult[$iID]);
                }
            }
            $arResultsNew=[];
            foreach ($arResult as $iID => $arUser) {
                $arResultsNew[]=$arUser;
            }
            $arResult=$arResultsNew;
            if ($_GET['debug']) {
                var_dump($arResult);
                die;
            }
            break;
        default:
            die(json_encode(['error' => 'unknown method']));
            break;
    }
}
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
echo json_encode(utf8ize($result));
//echo json_last_error();
die();
