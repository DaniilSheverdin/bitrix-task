<?php

$start = microtime(true);

use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;

header('Access-Control-Allow-Origin: *');

if (isset($_REQUEST['token']) && in_array($_REQUEST['token'], [md5(570)])) {
    define('NEED_AUTH', false);
    define('NOT_CHECK_PERMISSIONS', true);
    define('NO_KEEP_STATISTIC', true);
}
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

header('Content-Type: application/json');

if (!isset($_REQUEST['get']) && !isset($_REQUEST['set'])) {
    die(json_encode(['error' => 'noauth']));
}

$userId = (int)$GLOBALS['USER']->GetID();

if ($userId <= 0) {
    if (isset($_REQUEST['token']) && $_REQUEST['token'] == md5(570)) {
        $userId = 570;
    } else {
        die(json_encode(['error' => 'noauth']));
    }
}

$arResult = [];
$arDefaultSettings = [
    'view'      => [
    	'all' => 'user',
    ],
    'pinned'    => [],
    'show'      => [
        'programs'      => [],
        'departments'   => [],
        'themes'        => [],
    ],
];
$startProcessing = microtime(true);
if (isset($_REQUEST['get'])) {
    CBitrixComponent::includeComponentClass('citto:indicators');
    $obComponent = new \Citto\Indicators\MainComponent();
    CBitrixComponent::includeComponentClass('citto:indicators.edit');
    $obComponentEdit = new \Citto\Indicators\EditConponent();

    switch ($_REQUEST['get']) {
        case 'list':
            $arResult = $obComponent->apiGetList($_REQUEST);
            break;
        case 'settings':
            $arResult = CUserOptions::GetOption('citto:indicatios', 'dashboard', $arDefaultSettings, $userId);
            if (!isset($arResult['view']['all'])) {
            	$arResult['view']['all'] = 'user';
            }
            break;
        case 'program':
        case 'programs':
            $arResult = $obComponent->apiGetPrograms();
            break;
        case 'department':
        case 'departments':
            $arResult = $obComponentEdit->apiGetDepartments();
            break;
        case 'theme':
        case 'themes':
            $arResult = $obComponent->apiGetTheme();
            break;
        case 'theme_stat':
            $arResult = $obComponent->apiGetThemeStat();
            break;
        case 'affiliation':
            $arResult = $obComponent->apiGetAffiliation();
            break;
        case 'history':
            $arResult = $obComponent->apiGetHistory($_REQUEST['bi_id']);
            break;
        case 'edit_departments':
            $arResult = $obComponentEdit->apiGetDepartments($userId);
            break;
        case 'directory':
            $arResult = [
                'department'        => $obComponent->apiGetDepartments(),
                'program'           => $obComponent->apiGetPrograms(),
                'theme'             => $obComponent->apiGetTheme(),
                'theme_stat'        => $obComponent->apiGetThemeStat(),
                'affiliation'       => $obComponent->apiGetAffiliation(),
                'edit_departments'  => $obComponentEdit->apiGetDepartments($userId),
            ];
            break;
        default:
            die(json_encode(['error' => 'unknown method']));
            break;
    }
} elseif (isset($_REQUEST['set'])) {
    $arSettings = CUserOptions::GetOption('citto:indicatios', 'dashboard', $arDefaultSettings, $userId);
    switch ($_REQUEST['set']) {
        case 'show':
            if (!isset($_REQUEST['value']) || !is_array($_REQUEST['value'])) {
                die(json_encode(['error' => 'unknown params']));
            }
            foreach (array_keys($arDefaultSettings['show']) as $show) {
                if (!empty($_REQUEST['value'][ $show ])) {
                    if (false !== mb_strpos($_REQUEST['value'][ $show ], ',')) {
                        $_REQUEST['value'][ $show ] .= ',';
                    }

                    $_REQUEST['value'][ $show ] = explode(',', $_REQUEST['value'][ $show ]);
                } else {
                    $_REQUEST['value'][ $show ] = [];
                }
                $arSettings['show'][ $show ] = $_REQUEST['value'][ $show ];
                $arSettings['show'][ $show ] = array_filter($arSettings['show'][ $show ]);
                $arSettings['show'][ $show ] = array_map('intval', $arSettings['show'][ $show ]);
            }

            CUserOptions::SetOption('citto:indicatios', 'dashboard', $arSettings, false, $userId);
            $arResult = $arSettings;
            break;
        case 'view':
            if (!isset($_REQUEST['id']) || !isset($_REQUEST['value'])) {
                die(json_encode(['error' => 'unknown params']));
            }
            if ($_REQUEST['id'] != 'all') {
            	$_REQUEST['id'] = (int)$_REQUEST['id'];
            }
            $arSettings['view'][ $_REQUEST['id'] ] = $_REQUEST['value'];
            CUserOptions::SetOption('citto:indicatios', 'dashboard', $arSettings, false, $userId);
            $arResult = $arSettings;
            break;
        case 'pin':
        case 'unpin':
            if (!isset($_REQUEST['id'])) {
                die(json_encode(['error' => 'unknown params']));
            }
            if ($_REQUEST['set'] == 'pin') {
                $arSettings['pinned'][] = (int)$_REQUEST['id'];
            } else {
                $arSettings['pinned'] = array_diff($arSettings['pinned'], [$_REQUEST['id']]);
            }
            $arSettings['pinned'] = array_filter(array_unique($arSettings['pinned']));
            $arSettings['pinned'] = array_map('intval', $arSettings['pinned']);
            CUserOptions::SetOption('citto:indicatios', 'dashboard', $arSettings, false, $userId);
            $arResult = $arSettings;
            break;
        case 'indicators':
            CBitrixComponent::includeComponentClass('citto:indicators');
            $obComponent = new \Citto\Indicators\MainComponent();
            CBitrixComponent::includeComponentClass('citto:indicators.edit');
            $obComponentEdit = new \Citto\Indicators\EditConponent();
            require $_SERVER['DOCUMENT_ROOT'] . '/local/vendor/autoload.php';
            $logger = new Logger('default');
            $logger->pushHandler(
                new RotatingFileHandler(
                    $_SERVER['DOCUMENT_ROOT'] . '/local/logs/indicators/api_set_indicators.log',
                    90,
                    Logger::DEBUG
                )
            );

            $logger->debug('INDICATORS', $_REQUEST['INDICATORS']);
            $obComponentEdit->apiSetData($_REQUEST['department']??0, $_REQUEST['INDICATORS']);

            $arResult = $obComponent->apiGetList($_REQUEST);
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
        'start'         => $start,
        'finish'        => $finish,
        'duration'      => $finish-$start,
        'processing'    => $finish-$startProcessing,
        'date_start'    => date('d.m.Y H:i:s', $start),
        'date_finish'   => date('d.m.Y H:i:s', $finish),
    ],
];


die(json_encode($result));
