<?php

(php_sapi_name() === 'cli') ?: die();

define('NEED_AUTH', false);
define('NOT_CHECK_PERMISSIONS', true);
define('NO_KEEP_STATISTIC', true);
define('LANG', "s1");
define('SITE_ID', "s1");

$DOCUMENT_ROOT = $_SERVER['DOCUMENT_ROOT'] = realpath(__DIR__ . "/../../../../");
require $_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php";
require_once $DOCUMENT_ROOT . '/local/vendor/autoload.php';

use \Bitrix\Main\Loader;
use \Bitrix\Main\UserTable;
use \Bitrix\Main\Mail\Event;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Bitrix\Highloadblock;

Loader::includeModule('highloadblock');
Loader::IncludeModule("im");

$intHID = (defined("HLBLOCK_ID_COURSES_LA")) ? HLBLOCK_ID_COURSES_LA : 18;

$objHlblock = Highloadblock\HighloadBlockTable::getById($intHID)->fetch();

$objEntity = Highloadblock\HighloadBlockTable::compileEntity($objHlblock);
$class_Entity_data = $objEntity->getDataClass();

$objCurses = $class_Entity_data::getList(
    [
        'select' => ['*'],
        'filter' => [
            'UF_COMPLITE' => 0
        ]
    ]
);

$arList = $objCurses->fetchAll();

foreach ($arList as $arValue) {
    $objUser = CUser::GetByID($arValue['UF_SOTRUDNIC']);
    $arUser = $objUser->Fetch();

    if (isset($arUser['LOGIN'])) {
        $strUserLogin = mb_strtolower($arUser['LOGIN']);
        $strToken = File_Get_Contents_curl('https://university.tularegion.ru/login/token.php?username=web_api&password=Cfeirbyhjvfy1*&service=api');
        $arToken = json_decode($strToken, true);
        if (isset($arToken['token'])) {
            $strJSONInfoCourse = File_Get_Contents_curl('https://university.tularegion.ru/webservice/rest/server.php?&moodlewsrestformat=json&wstoken=' . $arToken['token'] . '&wsfunction=local_lastcourse&course_id=' . $arValue['UF_CURSE'] . '&user=' . $strUserLogin);
            $arStatus = json_decode($strJSONInfoCourse, true);
            $boolComplite = $arStatus['result'];

            if ($arValue['UF_COMPLITE'] == 0 && $boolComplite) {
                // todo: ID Токсубаева: 2120, Cавельева: 1927, Чернопятова: 1813
                foreach([1927, 1813] as $intUser) {
                    $boolRes = CIMMessenger::Add(
                        [
                            "MESSAGE_TYPE" => "S",
                            "TO_USER_ID" => $intUser,
                            "FROM_USER_ID" => 1,
                            "MESSAGE" => "Сотрудник " . $arUser['LAST_NAME'] . " " . $arUser['NAME'] . " " . $arUser['SECOND_NAME'] . " завершил курс «Охрана труда» на Корпоративном Университете.",
                            "AUTHOR_ID" => 1,
                            "EMAIL_TEMPLATE" => "some",
                            "NOTIFY_TYPE" => IM_NOTIFY_FROM
                        ]
                    );
                }

                $class_Entity_data::update(
                    $arValue['ID'],
                    [
                        'UF_COMPLITE' => 1
                    ]
                );
            }
        }
    }
}