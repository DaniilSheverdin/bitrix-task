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
use Citto\Mentoring\Users as MentoringUsers;

Loader::includeModule("citto.integration");
Loader::includeModule('tasks');
Loader::includeModule('intranet');
Loader::includeModule('im');

global $APPLICATION, $USER;

$uid = isset($_GET['uid']) ? $_GET['uid'] : 0;

$bIsPositionForMentors = MentoringUsers::isPositionForMentors($uid);
$bOIV = (MentoringUsers::getUsersWithStrcuture([$uid])[$uid]['DEPARTMENT']['PODVED'] == 'N');

if ($bIsPositionForMentors && $bOIV) {
    $iMentoringID = CIBlock::GetList([], ["CODE"=>'mentoring'], true)->GetNext()['ID'];
    echo "<p>Пользователь подходит для участия в наставничестве</p>";
    echo "<p>Для запуска БП о наставничестве перейдите по ссылке <a href='/bizproc/processes/{$iMentoringID}/element/0/0/?user_id={$uid}'>наставничество</a></p>";
} else {
    echo "<p>Пользователь не подходит для участия в наставничестве</p>";
}
