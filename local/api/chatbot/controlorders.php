<?php

define("NEED_AUTH", false);
define("NOT_CHECK_PERMISSIONS", true);
define("NO_KEEP_STATISTIC", true);

require $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";
require $_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php";

$configFileName = '/config_controlorders.php';
if (file_exists(__DIR__ . $configFileName)) {
   include_once __DIR__ . $configFileName;
}

writeToLog($_REQUEST, 'Пришло сообшение');

if ($_REQUEST['event'] == 'ONIMBOTMESSAGEADD') {
    $userId = $_REQUEST['data']['PARAMS']['FROM_USER_ID'];
    $arReport = getAnswer($_REQUEST['data']['PARAMS']['MESSAGE'], $userId);

    $result = restCommand(
        'imbot.message.add',
        [
            "DIALOG_ID" => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
            "MESSAGE"   => $arReport['title'] . "\n" . $arReport['report'] . "\n",
        ],
        $_REQUEST["auth"]
    );
} else {
    if ($_REQUEST['event'] == 'ONIMBOTJOINCHAT') {
        $result = restCommand(
            'imbot.message.add',
            [
                'DIALOG_ID' => $_REQUEST['data']['PARAMS']['DIALOG_ID'],
                'MESSAGE'   => 'Привет! Я докладываю о поручениях',
            ],
            $_REQUEST["auth"]
        );
    } else {
        if ($_REQUEST['event'] == 'ONIMBOTDELETE') {
            saveParams([]);
        } else {
            if ($_REQUEST['event'] == 'ONAPPINSTALL') {
                $handlerBackUrl = 'https://' . $_SERVER['SERVER_NAME'] . $_SERVER['SCRIPT_NAME'];

                $result = restCommand(
                    'imbot.register',
                    [
                        'CODE'                  => 'ControlOrdersBot',
                        'TYPE'                  => 'B',
                        'EVENT_MESSAGE_ADD'     => $handlerBackUrl,
                        'EVENT_WELCOME_MESSAGE' => $handlerBackUrl,
                        'EVENT_BOT_DELETE'      => $handlerBackUrl,
                        'PROPERTIES'            => [
                            'NAME'              => 'Контроль поручений',
                            'LAST_NAME'         => '',
                            'COLOR'             => 'RED',
                            'EMAIL'             => 'noreply@tularegion.ru',
                            'PERSONAL_BIRTHDAY' => '2020-09-01',
                            'WORK_POSITION'     => 'Докладываю о поручениях',
                            'PERSONAL_WWW'      => '',
                            'PERSONAL_GENDER'   => 'M',
                        ],
                    ],
                    $_REQUEST
                );
                $appsConfig = [
                    'BOT_ID'      => $result['result'],
                    'LANGUAGE_ID' => $_REQUEST['data']['LANGUAGE_ID'],
                ];
                saveParams($appsConfig);
            }
        }
    }
}

function saveParams($params)
{
   $config = "<?php\n";
   $config .= "\$appsConfig = " . var_export($params, true) . ";\n";
   $configFileName = '/config_controlorders.php';
   file_put_contents(__DIR__ . $configFileName, $config);
   return true;
}

function restCommand($method, array $params = [], array $auth = [])
{
    writeToLog($params, 'Запрос ' . $method);
    $auth['domain'] = str_replace('corp.tularegion.local', 'corp.tularegion.ru', $auth['domain']);
    $queryUrl  = 'https://' . $auth['domain'] . '/rest/' . $method;
    $queryData = http_build_query(array_merge($params, ['auth' => $auth['access_token']]));
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_POST           => 1,
        CURLOPT_HEADER         => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL            => $queryUrl,
        CURLOPT_POSTFIELDS     => $queryData,
    ]);
    $result = curl_exec($curl);
    curl_close($curl);
    $result = json_decode($result, true);
    writeToLog($result, 'Результат REST');
    return $result;
}

function writeToLog($data, $title = '')
{
    $log = "\n------------------------\n";
    $log .= date("Y.m.d H:i:s") . "\n";
    $log .= (mb_strlen($title) > 0 ? $title : 'DEBUG') . "\n";
    $log .= print_r($data, true);
    $log .= "\n------------------------\n";
    file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/local/logs/imbot/' . date('Y-m-d') . '.log', $log, FILE_APPEND);
    return true;
}

function getAnswer($command = '', $user = 0)
{
    switch (mb_strtolower($command)) {
        case 'привет':
        default:
            $arResult = [
                'title' => 'Привет',
                'report'  => 'Я пока ничего не умею, кроме оповещений',
            ];
            break;
    }

    return $arResult;
}
