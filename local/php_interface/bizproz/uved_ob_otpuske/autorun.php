<?php

(php_sapi_name() === 'cli') ?: die();

define('NEED_AUTH', false);
define('NOT_CHECK_PERMISSIONS', true);
define('NO_KEEP_STATISTIC', true);
define('LANG', "s1");
define('SITE_ID', "s1");
define('JUDGE_DEPARTMENT', 2229);

$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . "/../../../../");
require $_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php";
require_once $DOCUMENT_ROOT . '/local/vendor/autoload.php';

use \Bitrix\Main\Loader;
use \Bitrix\Main\UserTable;
use \Bitrix\Main\Mail\Event;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

if (defined('BP_OTPUSK_NO_AUTORUN')) {
    return null;
}

Loader::includeModule("citto.integration");
Loader::includeModule('tasks');
Loader::includeModule('intranet');

global $APPLICATION, $USER;

$objlogger = new Logger('uved_ob_otpuske');
$objlogger->pushHandler(
    new StreamHandler(
        $DOCUMENT_ROOT . '/local/logs/uved_ob_otpuske/uved_info_'.date("Y-m-d").'.log',
        Logger::INFO
    )
);

$arUsersJudge = [];
$arDepartmentsJudge = CIntranetUtils::GetIBlockSectionChildren(JUDGE_DEPARTMENT);
$obUsers = CUser::GetList($by = 'LAST_NAME', $order = 'ASC', ['ID'], ['SELECT' => ['UF_DEPARTMENT']]);
while ($arUser = $obUsers->getNext()) {
    foreach ($arUser['UF_DEPARTMENT'] as $iDepartID) {
        if (in_array($iDepartID, $arDepartmentsJudge)) {
            array_push($arUsersJudge, $arUser['ID']);
        }
    }
}

$arSotrudniks = [];
$DATE = new DateTime();
$DATE_BORDER = new DateTime("+31 days");
$dateBx = function ($date = null) {
    if (is_null($date)) {
        $date = new DateTime('Now');
    } elseif (!is_object($date)) {
        $date = new DateTime($date);
    }
    return $date->format($GLOBALS['DB']->DateFormatToPHP(CSite::GetDateFormat("SHORT", "s1")));
};
$tolog = function ($exc) {
    if (mb_stripos($exc->getMessage(), "Fetching http headers") !== false) {
        return;
    }

    file_put_contents(
        $_SERVER['DOCUMENT_ROOT'] . "/../newcorp_arch/uved_ob_otpuske.log",
        $exc->getMessage() . PHP_EOL . print_r($exc->getTrace(), true) . PHP_EOL,
        FILE_APPEND
    );
};
try {
    $USER->Authorize(1);

    $arSotrudniks = UserTable::getList(
        [
            'select' => [
                'UF_OTP_UVED',
                'UF_OTP_UVED_LC',
                'ID',
                'XML_ID',
                'LOGIN',
                'EMAIL'
            ],
            'filter' => [
                [
                    'LOGIC' => "OR",
                    'UF_OTP_UVED_LC' => false,
                    '<UF_OTP_UVED_LC' => $dateBx()
                ],
                '!XML_ID' => false,
                'ACTIVE' => 'Y',
            ],
            'limit' => 10
        ]
    )->fetchAll();

    if (empty($arSotrudniks)) {
        return;
    }

    foreach ($arSotrudniks as $arSotrudnik) {
        $arSotrudnik['UF_OTP_UVED'] = array_map($dateBx, $arSotrudnik['UF_OTP_UVED']) ?: [];
        $arSotrudnikLast = $arSotrudnik;
        $bIsJudge = (in_array($arSotrudnik['ID'], $arUsersJudge));
        $sTaskCreatedLogin = ($bIsJudge) ? 'Ivanova.Irina' : 'Evgeniya.Balashova';
        $arLgData = CUser::GetByLogin($sTaskCreatedLogin)->Fetch();
        $cUser = new CUser();

        if (!$arLgData) {
            $objlogger->info(
                "Error: Постановщик задачи не найден"
            );

            $cUser->Update(
                $arSotrudnik['ID'],
                [
                    'UF_OTP_UVED_LC' => $dateBx()
                ]
            );
        } else {
            $arVacations = $getUserVacationsByXmlId($arSotrudnik['XML_ID']);
            foreach ($arVacations as $vacation) {
                $objlogger->info(
                    "Сотрудник: ".$arSotrudnik['LAST_NAME'].' '.$arSotrudnik['NAME'].' ['.$arSotrudnik['ID'].'], XML_ID: '.
                    $arSotrudnik['XML_ID'].', отпуск с '.$vacation['FROM']->format('Y-m-d'). ' на '.$vacation['DAYS']. ' дней'
                );

                $OTPUSK__FROM = $vacation['FROM'];
                $OTPUSK__DAYS = $vacation['DAYS'];

                if ($OTPUSK__FROM < $DATE) {
                    $objlogger->info(
                        $OTPUSK__FROM->format('Y-m-d')." - дата уже прошла"
                    );
                    continue;
                }
                if ($OTPUSK__FROM > $DATE_BORDER) {
                    $objlogger->info(
                        $OTPUSK__FROM->format('Y-m-d')." - дата еще не наступила"
                    );
                    continue;
                }
                if (in_array($dateBx($OTPUSK__FROM), $arSotrudnik['UF_OTP_UVED'])) {
                    $objlogger->info(
                        $OTPUSK__FROM->format('Y-m-d')." - работник ранее был уведомлен"
                    );
                    continue;
                }

                try {
                    $objlogger->info(
                        $OTPUSK__FROM->format('Y-m-d')." - процесс уведомления работника запущен"
                    );

                    $objMailed = Event::send(
                        [
                            'EVENT_NAME' => 'BP_UVED_OB_OTPUSKE',
                            'LID' => 's1',
                            'C_FIELDS' => [
                                'QUERY' => http_build_query(
                                    [
                                        'uvedomlenie' => '1',
                                        'otpusk__days' => $OTPUSK__DAYS,
                                        'otpusk__from' => $OTPUSK__FROM->format('d.m.Y'),
                                        'auth_token' => $GLOBALS['auth_token_get']($arSotrudnik['ID'])
                                    ]
                                ),
                                'OTPUSK__DAYS' => $OTPUSK__DAYS,
                                'OTPUSK__FROM' => $OTPUSK__FROM->format('d.m.Y'),
                                'EMAIL' => $arSotrudnik['EMAIL'],
                            ]
                        ]
                    );
                    $objlogger->info(
                        "Почтовое уведомление статус отправки:".json_encode((array)$objMailed)
                    );

                    $arSotrudnik['UF_OTP_UVED'][] = $dateBx($OTPUSK__FROM);

                    $objEventMsg = CEventMessage::GetList(
                        $msgBy = "ID",
                        $msgDesc = "DESC",
                        [
                            'TYPE' => 'BP_UVED_OB_OTPUSKE'
                        ]
                    );

                    if ($objEventMsg->SelectedRowsCount() > 0) {
                        $arEventData = $objEventMsg->Fetch();

                        $strMessage = $arEventData['MESSAGE'];
                        $strSubject = $arEventData['SUBJECT'];

                        $arSotrudnik = CUser::GetByID($arSotrudnik['ID'])->Fetch();

                        $strMessage = str_replace(
                            [
                                '#OTPUSK__FROM#',
                                '#OTPUSK__DAYS#',
                                '#EMAIL#',
                                '#QUERY#'
                            ],
                            [
                                $OTPUSK__FROM->format('d.m.Y'),
                                $OTPUSK__DAYS,
                                $arSotrudnik['EMAIL'],
                                http_build_query(
                                    [
                                        'uvedomlenie' => '1',
                                        'otpusk__days' => $OTPUSK__DAYS,
                                        'otpusk__from' => $OTPUSK__FROM->format('d.m.Y'),
                                        'auth_token' => $GLOBALS['auth_token_get']($arSotrudnik['ID'])
                                    ]
                                )
                            ],
                            $strMessage
                        );

                        $arMatches = [];
                        preg_match_all("#<a.+href=\"(.+)\".*>(.+)<\/a>#Us", $strMessage, $arMatches, PREG_PATTERN_ORDER);
                        if (count($arMatches) > 0) {
                            foreach ($arMatches[0] as $intI => $arMatch) {
                                $arMatches[2][$intI] = str_replace(["\r\n", "\n"], ['', ''], $arMatches[2][$intI]);
                                $strMessage = str_replace(
                                    $arMatch,
                                    '[url='.$arMatches[1][$intI].']'.$arMatches[2][$intI].'[/url]',
                                    $strMessage
                                );
                            }
                        }

                        if ($arLgData) {
                            $resAdd = CTaskItem::Add(
                                [
                                    'TITLE' => $strSubject,
                                    'DESCRIPTION' => HTMLToTxt($strMessage),
                                    'DEADLINE' => null,
                                    'START_DATE_PLAN' => $DATE->format('d.m.Y'),
                                    'TASK_CONTROL' => 'N',
                                    'RESPONSIBLE_ID' => $arSotrudnik['ID'],
                                    'CREATED_BY' => isset($arLgData) ? $arLgData['ID'] : 1,
                                    'SITE_ID' => SITE_ID
                                ],
                                $arLgData['ID']
                            );
                            $objlogger->info(
                                "Постановка задачи на КП статус:".json_encode((array)$resAdd)
                            );
                        } else {
                            $objlogger->info(
                                "Error: Постановщик задачи не найден"
                            );
                        }
                    } else {
                        $objlogger->info(
                            "Error: Шаблон сообщения не найден"
                        );
                    }

                    $arSotrudnikLast['UF_OTP_UVED'][] = $dateBx($OTPUSK__FROM);
                } catch (Exception $exc) {
                    $tolog($exc);
                }
            }

            $cUser->Update(
                $arSotrudnik['ID'],
                [
                    'UF_OTP_UVED' => $arSotrudnikLast['UF_OTP_UVED'],
                    'UF_OTP_UVED_LC' => $dateBx()
                ]
            );
        }

        $objlogger->info(
            "\r\n\r\n-----------------------------\r\n\r\n"
        );
    }
} catch (Exception $exc) {
    $tolog($exc);
}

$USER->Logout();
