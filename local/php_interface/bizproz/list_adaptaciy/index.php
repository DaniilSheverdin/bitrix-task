<?php

// php_sapi_name() === 'cli'

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
use Citto\Mentoring\Users as MentoringUsers;

Loader::includeModule("citto.integration");
Loader::includeModule('tasks');
Loader::includeModule('intranet');
Loader::includeModule('im');

global $APPLICATION, $USER;

$sended = false;

$objlogger = new Logger('list_adaptaciy');
$objlogger->pushHandler(
    new StreamHandler(
        $DOCUMENT_ROOT . '/local/logs/list_adaptaciy/info_' . date("Y-m-d") . '.log',
        Logger::INFO
    )
);

$arSotrudniks = [];

try {
    $uid = isset($_GET['uid']) ? $_GET['uid'] : 0;

    if (isset($_SERVER['HTTP_HOST']) && ($uid == 0 || !$USER->IsAuthorized())) {
        exit(0);
    }

    $bIsPositionForMentors = MentoringUsers::isPositionForMentors($uid);
    $bOIV = (MentoringUsers::getUsersWithStrcuture([$uid])[$uid]['DEPARTMENT']['PODVED'] == 'N');

    if (!isset($_SERVER['HTTP_HOST'])) {
        $USER->Authorize(1);
        $arSotrudniks = UserTable::getList(
            [
                'select' => [
                    'UF_DEPARTMENT',
                    'UF_FIRST_LOGIN',
                    'ID',
                    'XML_ID',
                    'LOGIN',
                    'EMAIL'
                ],
                'filter' => [
                    [
                        'LOGIC' => "OR",
                        'UF_FIRST_LOGIN' => null,
                        'UF_FIRST_LOGIN' => 0
                    ],
                    'ACTIVE' => 'Y',
                    [
                        'LOGIC' => "AND",
                        '!XML_ID' => null,
                        '!XML_ID' => ''
                    ]
                ],
                'limit' => 10000
            ]
        )->fetchAll();
        $type = 'cli';
    } else {
        $type = 'fpm-fcgi';
        $arSotrudniks = UserTable::getList(
            [
                'select' => [
                    'UF_DEPARTMENT',
                    'UF_FIRST_LOGIN',
                    'ID',
                    'XML_ID',
                    'LOGIN',
                    'EMAIL'
                ],
                'filter' => [
                    'ID' => $uid,
                    'ACTIVE' => 'Y',
                ],
                'limit' => 10000
            ]
        )->fetchAll();
    }

    if (empty($arSotrudniks)) {
        throw new Exception("Новых сотрудников не найдено");
        return;
    }

    foreach ($arSotrudniks as $arSotrudnik) {
        $sended = false;
        if (empty($arSotrudnik['XML_ID']) && $type != 'fpm-fcgi') {
            continue;
        }

        $listDepartSections = GetParentDepartmentstucture($arSotrudnik['ID']);

        if (!(isset($listDepartSections)
            && ($listDepartSections[1] == '1710'
                || ($listDepartSections[1] == '1727'
                    && count($listDepartSections) >= 2
                )
            ) // только ОИВ и аппарт ПТО
        )) {
            continue; // если не входит в ОИВ и аппарт ПТО
        }

        $arSotrudnikLast = $arSotrudnik;

        $objSect = CIBlockSection::GetList(
            [],
            ['IBLOCK_ID' => IBLOCK_ID_STRUCTURE, 'ACTIVE' => 'Y', 'ID' => $arSotrudnikLast['UF_DEPARTMENT'][0]],
            false,
            ['ID', 'UF_NOTIFICATION_NEW_PERSONAL']
        );
        $arDepart = $objSect->Fetch();

        if ($type == 'fpm-fcgi') {
            if (!empty($arSotrudnikLast['EMAIL'])) {
                Event::send(
                    [
                        'EVENT_NAME' => 'BP_LIST_ADAPTATION_START',
                        'LID' => 's1',
                        'C_FIELDS' => [
                            'EMAIL_TO' => $arSotrudnikLast['EMAIL']
                        ]
                    ]
                );
            }

            CIMMessenger::Add(
                [
                    "MESSAGE_TYPE" => "S",
                    "TO_USER_ID" => $arSotrudnikLast['ID'],
                    "FROM_USER_ID" => 1,
                    "MESSAGE" => file_get_contents("./message-corp.html"),
                    "AUTHOR_ID" => 1,
                    "EMAIL_TEMPLATE" => "some",
                    "NOTIFY_TYPE" => IM_NOTIFY_CONFIRM,
                    "NOTIFY_BUTTONS" => [
                        [
                            'TITLE' => 'Перейти',
                            'VALUE' => 'Y',
                            'TYPE' => 'accept',
                            'URL' => '/bizproc/processes/600/element/0/0/?list_section_id='
                        ]
                    ]
                ]
            );
            $objlogger->info(
                "Для пользователя " . $arSotrudnikLast['EMAIL'] . ": Сотруднику отправлено оповещение в ручную пользователем ".$USER->GetLogin()
            );
            $sended = true;
        } elseif((!empty($arDepart) && intval($arDepart['UF_NOTIFICATION_NEW_PERSONAL']) > 0)) {
            $objlogger->info(
                $arSotrudnikLast['EMAIL'] .' - '. $type . ": Условная отправка письма пользователю:\r\n ".print_r($arSotrudnikLast, true)
            );
            $sended = true;
        } else {
            $objlogger->info(
                $arSotrudnikLast['EMAIL'] .' - '. $type . ": Не определен отдел или отдел сотрудника исключени из информирования:\r\n ".print_r($arSotrudnikLast, true)
            );
        }

        if ($sended || ($type == 'fpm-fcgi')) {
            $objUser = new \CUser();
            $objUser->Update(
                $arSotrudnikLast['ID'],
                [
                    'UF_FIRST_LOGIN' => '1'
                ]
            );
        }
    }

    $objlogger->info(
        "\r\n\r\n-----------------------------\r\n\r\n"
    );
} catch (Exception $exc) {
    $objlogger->info($exc->getMessage());
    echo $exc->getMessage();
}

if ($type == 'cli') {
    echo 'END';
    $USER->Logout();
} else {
    if ($bIsPositionForMentors && $bOIV) {
        $iMentoringID = CIBlock::GetList([], ["CODE"=>'mentoring'], true)->GetNext()['ID'];
        echo "<p>Пользователь подходит для участия в наставничестве</p>";
        echo "<p>Для запуска БП о наставничестве перейдите по ссылке <a href='/bizproc/processes/{$iMentoringID}/element/0/0/?user_id={$uid}'>наставничество</a></p>";
    } else {
        echo "<p>Пользователь не подходит для участия в наставничестве</p>";
    }

    echo '<p>Оповещение отправлено для пользователя ' . $uid . '. <a href="/company/personal/user/'.$uid.'/">Вернуться обратно</a></p>';
}
