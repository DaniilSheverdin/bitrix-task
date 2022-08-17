<?php

use Bitrix\Main\Loader;

include __DIR__ . "/bizproc.functions.php";
include_once('include/iblocks.php');
include_once('include/hlblocks.php');
include_once('include/users_groups.php');
include_once('include/agents.php');
include_once(__DIR__ . '/classes/include.php');

$eventManager = \Bitrix\Main\EventManager::getInstance();
$eventManager->addEventHandler("main", "OnAfterUserAuthorize", ['PageEventHandlers', "checkUserPdExpired"]);
$eventManager->addEventHandler("main", "OnAfterUserLoginHandler", ['PageEventHandlers', "checkUserPdExpired"]);
$eventManager->addEventHandler("main", "OnAfterUserLoginByHash", ['PageEventHandlers', "checkUserPdExpired"]);
$eventManager->addEventHandler("main", "OnPageStart", ['PageEventHandlers', "redirectLocal"]);
$eventManager->addEventHandler("socialnetwork", "OnParseSocNetComponentPath", ['PageEventHandlers', "profileLinks"]);
$eventManager->addEventHandler("main", "OnBeforeUserUpdate", ['PageEventHandlers', "updateBirthDate"]);
$eventManager->addEventHandler("bizproc", "OnTaskAdd", ['PageEventHandlers', "onTaskAdd"]);
$eventManager->addEventHandler("bizproc", "OnTaskMarkCompleted", ['PageEventHandlers', "onTaskMarkCompleted"]);
$eventManager->addEventHandler("main", "OnBeforeEventAdd", ['PageEventHandlers', "onTaskEmail"]);

$eventManager->addEventHandler('iblock', 'OnAfterIBlockElementAdd', ['PageEventHandlers', 'pagesAccessRulesCreate']);
$eventManager->addEventHandler('iblock', 'OnAfterIBlockElementUpdate', ['PageEventHandlers', 'pagesAccessRulesCreate']);
$eventManager->addEventHandler('iblock', 'OnAfterIBlockElementDelete', ['PageEventHandlers', 'pagesAccessRulesCreate']);
$eventManager->addEventHandler('main', 'OnBeforeProlog', ['PageEventHandlers', 'pagesAccessRulesCheck']);
$eventManager->addEventHandler("main", 'OnBeforeProlog', ['PageEventHandlers', 'defineIblockIds']);
$eventManager->addEventHandler("main", 'OnBeforeProlog', ['PageEventHandlers', 'tokenAuth']);
$eventManager->addEventHandler("main", 'OnBeforeProlog', ['PageEventHandlers', 'redirects']);
include __DIR__ . "/email_task.php";
AddEventHandler("forum", "onAfterMessageAdd", "tasks_UF_LAST_COMMENT");
AddEventHandler("forum", "onAfterMessageUpdate", "tasks_UF_LAST_COMMENT");
AddEventHandler("forum", "onAfterMessageDelete", "tasks_UF_LAST_COMMENT");

Loader::includeModule('citto.integration');
\Citto\Integration\Module::onPageStart();

function pre($m)
{
    echo "<pre>";
    print_r($m);
    echo "</pre>";
}

function runSystemTests($debug = false)
{
    require_once($_SERVER['DOCUMENT_ROOT'] . '/local/tests/include.php');
    testSystem($debug);

    return __METHOD__ . '();';
}

$userIsInLocal = function () {
    if (false !== mb_strpos($_SERVER['SERVER_NAME'], 'dev.tularegion.org')) {
        return true;
    }
    $arMasks = [
        '172\.21',
        '10\.21',
        '10\.200',
        '10\.10',
        '10\.5\d{1,2}', // МФЦ
        '10\.6\d{1,2}', // МФЦ
    ];
    return (
        $_SERVER['HTTP_HOST'] == "corp.tularegion.local" &&
        preg_match(
            '/^((' . implode(')|(', $arMasks) . '))\.\d{1,3}\.\d{1,3}$/',
            \Bitrix\Main\Service\GeoIp\Manager::getRealIp()
        )
    );
};

class PageEventHandlers
{
    public static function profileLinks(&$arDefaultUrlTemplates404, &$arCustomPagesPath, $arParams)
    {
        $arDefaultUrlTemplates404['user_personal'] = 'user/#user_id#/personal/';
        $arParams['SEF_URL_TEMPLATES']['user_personal'] = 'user/#user_id#/personal/';
        $arDefaultUrlTemplates404['user_career'] = 'user/#user_id#/career/';
        $arParams['SEF_URL_TEMPLATES']['user_career'] = 'user/#user_id#/career/';
        $arDefaultUrlTemplates404['user_my_bizproc'] = 'user/#user_id#/my_bizproc/';
        $arParams['SEF_URL_TEMPLATES']['user_my_bizproc'] = 'user/#user_id#/my_bizproc/';
        $arDefaultUrlTemplates404['user_my_kpi'] = 'user/#user_id#/my_kpi/';
        $arParams['SEF_URL_TEMPLATES']['user_my_kpi'] = 'user/#user_id#/my_kpi/';
        $arDefaultUrlTemplates404['user_zayavi'] = 'user/#user_id#/zayavi/';
        $arParams['SEF_URL_TEMPLATES']['user_zayavi'] = 'user/#user_id#/zayavi/';
        $arDefaultUrlTemplates404['user_reports'] = 'user/#user_id#/reports/';
        $arParams['SEF_URL_TEMPLATES']['user_reports'] = 'user/#user_id#/reports/';
    }

    public static function updateBirthDate(&$arFields)
    {
        if (isset($arFields['PERSONAL_BIRTHDAY'])) {
            $arFields['UF_PERSONAL_BIRTHDAY'] = $arFields['PERSONAL_BIRTHDAY'];
            // Если Скрывать ДР
            if ($arFields['UF_DATEOFBIRTHHIDE']) {
                $arFields['PERSONAL_BIRTHDAY'] = '';
            }
        }
    }

    public static function tokenAuth()
    {
        if (!isset($_GET['auth_token'])) {
            return;
        }
        global $APPLICATION, $USER, $auth_token_check;
        $user_id = null;
        $redirect_to = $APPLICATION->GetCurPageParam("", ['auth_token']);
        if ($auth_token_check($_GET['auth_token'], $user_id) === true && $user_id) {
            $USER->Authorize($user_id);
            LocalRedirect($redirect_to);
        } else {
            echo '<html><head><meta http-equiv="refresh" content="2;https://corp.tularegion.local' . $redirect_to . '"></head><body>Ссылка устарела или уже была использована</body></html>';
            die;
        }
    }

    public static function defineIblockIds()
    {
        global $USER;
        if (!($USER instanceof CUser) || !$USER->IsAuthorized()) {
            return;
        }

        $define_iblocks = [];
        $user_groups = $USER->GetUserGroupArray();
        if (!$user_groups) {
            return;
        }

        $defineIblockIds_cache = \Bitrix\Main\Data\Cache::createInstance();
        if ($defineIblockIds_cache->initCache(60 * 60, "defineIblockIds_" . serialize($user_groups), '/init/')) {
            $define_iblocks = $defineIblockIds_cache->getVars()['DEFINE_IBLOCKS'];
        } elseif ($defineIblockIds_cache->startDataCache()) {
            $connection = \Bitrix\Main\Application::getConnection();
            $sqlHelper = $connection->getSqlHelper();
            $group_string_id = $connection->queryScalar('SELECT TRIM(REPLACE(STRING_ID, "EMPLOYEES_", "")) FROM b_group WHERE ID IN(' . $sqlHelper->forSql(implode(",", $user_groups)) . ') AND STRING_ID LIKE "EMPLOYEES_%"');
            if (!$group_string_id) {
                return;
            }

            $iblock_res = $connection->query('SELECT ID, CODE, IBLOCK_TYPE_ID FROM b_iblock WHERE CODE LIKE "%_' . $sqlHelper->forSql($group_string_id) . '"');
            while ($iblock_fields = $iblock_res->fetch()) {
                $define_iblocks[$iblock_fields['ID']] = "IBLOCK_ID_" . preg_replace(
                    "/[^a-zA-Z0-9_]+/i",
                    "",
                    mb_strtoupper(trim($iblock_fields['IBLOCK_TYPE_ID']) . "_" . mb_substr($iblock_fields['CODE'], 0, mb_strlen($iblock_fields['CODE']) - mb_strlen($group_string_id) - 1))
                );
            }

            $defineIblockIds_cache->endDataCache([
                'DEFINE_IBLOCKS' => $define_iblocks
            ]);
        }
        foreach ($define_iblocks as $define_iblock_ID => $define_iblock_CODE) {
            define($define_iblock_CODE, $define_iblock_ID);
        }
    }

    public static function onTaskEmail(&$event, &$lid, &$arFields, &$message_id, &$files)
    {
        if (empty($lid)) {
            $lid = "s1";
        }

        /*
         * Чтобы не приходили письма на блог без доступа.
         * @see https://corp.tularegion.local/company/personal/user/2440/tasks/task/view/52042/
         */
        if (in_array($event, ['BLOG_POST_BROADCAST', 'BLOG_SONET_NEW_POST'])) {
            /*
             * Чтобы важные сообщения помечались высоким приоритетом.
             * @see https://corp.tularegion.local/company/personal/user/2440/tasks/task/view/60671/
             */
            $arFields['PRIORITY'] = '3 (Normal)';
            preg_match('/\/company\/personal\/user\/(\d{1,})\/blog\/(\d{1,})\//si', $arFields['MESSAGE_PATH'], $matches);
            $postId = (int)$matches[2];
            if ($postId > 0) {
                $arPostFields = $GLOBALS['USER_FIELD_MANAGER']->GetUserFields('BLOG_POST', $postId, LANGUAGE_ID);
                if ($arPostFields['UF_BLOG_POST_IMPRTNT']['VALUE'] == 1) {
                    $arFields['PRIORITY'] = '1 (Highest)';
                }
            }

            Loader::includeModule('mail');
            $arEmailUser = \Bitrix\Main\UserTable::getList([
                'select' => [
                    'ID', 'LID'
                ],
                'filter' => [
                    '=EMAIL' => CMailUtil::ExtractMailAddress($arFields['EMAIL_TO'])
                ]
            ])->Fetch();

            require $_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php";
            $logger = new \Monolog\Logger('default');
            $logger->pushHandler(
                new \Monolog\Handler\RotatingFileHandler(
                    $_SERVER['DOCUMENT_ROOT'] . '/local/logs/mail/onTaskEmail.log',
                    7
                )
            );
            $logger->info('$event', [$event]);
            $logger->info('$lid', [$lid]);
            $logger->info('$arFields', [$arFields]);
            $logger->info('$message_id', [$message_id]);
            $logger->info('$files', [$files]);
            $logger->info('$arEmailUser', [$arEmailUser]);
            $logger->info('$postId', [$postId]);

            if ($arEmailUser['LID'] != $lid) {
                return false;
            }
        }

        if (!in_array($event, ['IM_NEW_NOTIFY_GROUP', 'IM_NEW_NOTIFY'])) {
            return;
        }
        if (!is_array($arFields)) {
            return;
        }
        foreach ($arFields as &$fieldVal) {
            if (!is_string($fieldVal)) {
                continue;
            }
            if (mb_strpos($fieldVal, "corp.tularegion.ru") === false) {
                continue;
            }
            $fieldVal = str_replace("corp.tularegion.ru", "corp.tularegion.local", $fieldVal);
        }
        unset($fieldVal);
        return true;
    }

    public static function onTaskMarkCompleted($ID, $userId, $status)
    {
        $GLOBALS['bp_task_updated']($ID, "Закрыта", [$userId]);
    }

    public static function onTaskAdd($ID, $arFields)
    {
        $GLOBALS['bp_task_updated']($ID, "Поставлена", $arFields['USERS']);
    }

    public static function redirectLocal()
    {
        if ($_SERVER['HTTP_HOST'] == "corp.tularegion.ru" && !empty($_SERVER['HTTP_REFERER']) && mb_strpos($_SERVER['HTTP_REFERER'], "https://corp.tularegion.local") === 0) {
            echo '<html><head><meta http-equiv="refresh" content="0;https://corp.tularegion.local' . htmlentities($_SERVER['REQUEST_URI']) . '"></head><body></body></html>';
            die;
        }
    }

    public static function checkUserPdExpired($arUser)
    {
        global $USER;
        if (!$USER->IsAuthorized()) {
            return;
        }

        $by = "id";
        $order = "asc";
        $UF_CHEKC_PD_DATE = CUser::GetList(
            $by,
            $order,
            ['ID' => $USER->GetID()],
            [
                'SELECT' => [
                    'UF_CHEKC_PD_DATE'
                ],
                'FIELDS' => ['ID'],
                'NAV_PARAMS' => ['nTopCount' => 1]
            ]
        )->fetch()['UF_CHEKC_PD_DATE'] ?? null;

        if (empty($UF_CHEKC_PD_DATE) || strtotime($UF_CHEKC_PD_DATE) < strtotime('-90 days')) {
            Loader::includeModule('im');

            $cUser = new CUser();
            $cUser->Update(
                $USER->GetID(),
                [
                  'UF_CHEKC_PD_DATE' => date('d.m.Y')
                ]
            );

            CIMMessenger::Add([
                "MESSAGE_TYPE" => "S",
                "TO_USER_ID" => $USER->GetID(),
                "FROM_USER_ID" => 1,
                "MESSAGE" => "Просьба проверить актуальность персональных данных в личном кабинете и при необходимости внести изменения",
                "AUTHOR_ID" => 1,
                "EMAIL_TEMPLATE" => "some",
                "NOTIFY_TYPE" => IM_NOTIFY_CONFIRM,
                "NOTIFY_MODULE" => "main",
                "NOTIFY_TITLE" => "Просьба проверить актуальность персональных данных в личном кабинете и при необходимости внести изменения",
                "NOTIFY_BUTTONS" => [
                    [
                        'TITLE' => 'Проверить',
                        'VALUE' => 'Y',
                        'TYPE' => 'accept',
                        'URL' => '/company/personal/user/' . $USER->GetID() . '/'
                    ]
                ]
            ]);
        }
    }

    private const PAGES_ACCESS_RULES_IBLOCK = 133;
    private const PAGES_ACCESS_RULES_FILE = '/pages_access_rules.php';

    public static function pagesAccessRulesCreate($arFields)
    {
        if (empty($arFields['IBLOCK_ID']) || $arFields['IBLOCK_ID'] != self::PAGES_ACCESS_RULES_IBLOCK) {
            return;
        }

        Loader::includeModule('iblock');
        $res = CIBlockElement::GetList(['SORT' => 'DESC'], ['IBLOCK_ID' => self::PAGES_ACCESS_RULES_IBLOCK, 'ACTIVE_DATE' => 'Y', 'ACTIVE' => 'Y'], false, false, ['PROPERTY_REGEXP']);
        while ($ob = $res->Fetch()) {
            $rules[] = $ob['PROPERTY_REGEXP_VALUE'];
        }

        file_put_contents($_SERVER['DOCUMENT_ROOT'] . self::PAGES_ACCESS_RULES_FILE, '<?php return ' . var_export($rules, true) . ';');
    }

    public static function pagesAccessRulesCheck()
    {
        global $USER;
		$USER->Authorize(1);
        if (defined('NO_MB_CHECK')) {
            return;
        }
        if (!($USER instanceof CUser) || !$USER->IsAuthorized()) {
            return;
        }

        $url_available = function () {
            if (
                CSite::InDir('/mfc/stream/') ||
                CSite::InDir('/mfc/docs/') ||
                CSite::InDir('/mfc/workgroups/')
            ) {
                return false;
            }
            $rules = include $_SERVER['DOCUMENT_ROOT'] . self::PAGES_ACCESS_RULES_FILE;
            $CUR_DIR = preg_replace('!^' . preg_quote(SITE_DIR) . '!', '/', $_SERVER['REQUEST_URI']);
            if (empty($rules)) {
                return true;
            }
            foreach ($rules as $rule) {
                if (!preg_match('/' . $rule . '/', $CUR_DIR)) {
                    continue;
                }
                return true;
            }
            return false;
        };

        try {
            if (
                !$GLOBALS['userIsInLocal']()
                && !CSite::InGroup([36])
                && !$url_available()
            ) {
                file_put_contents(
                    $_SERVER['DOCUMENT_ROOT'] . '/../newcorp_arch/checkrights.log',
                    implode(
                        ' | ',
                        [
                            date('d.m.Y H:i:s'),
                            $_SERVER['HTTP_HOST'],
                            \Bitrix\Main\Service\GeoIp\Manager::getRealIp(),
                            '[' . $USER->GetID() . '] ' . $USER->GetFullName(),
                            $_SERVER['REQUEST_URI']
                        ]
                    ) . PHP_EOL,
                    FILE_APPEND
                );
                include $_SERVER['DOCUMENT_ROOT'] . '/only_local.php';
            }
        } catch (\Exception $exc) {
            CHTTP::SetStatus('500 Internal Server Error');
            die(500);
        }
    }

    public static function redirects()
    {
        global $APPLICATION, $USER;
        $GLOBALS['USER_IS_PODVED'] = false;

        if (
            $_SERVER['REQUEST_METHOD'] !== 'GET'
            || isset($_REQUEST['noredir'])
            || !($USER instanceof CUser)
            || CSite::InDir('/podpis-fayla/')
            || CSite::InDir('/bitrix/')
            || CSite::InDir('/mobile/')
            || CSite::InDir('/online/')
            || CSite::InDir('/desktop_app/')
            || CSite::InDir('/rpa/')
            || CSite::InDir('/crm/')
            || CSite::InDir('/pub/')
            || CSite::InDir('/upload/')
            || CSite::InDir('/rest/')
            || CSite::InDir('/mobileapp/')
            || CSite::InDir('/control-orders/')
            || CSite::InDir('/local/api/')
            || CSite::InDir('/services/kmoppn/')
        ) {
            return;
        }

        $arRedirectUsers = [
            2548, // Грудинин
        ];
        $curUserId = $USER->GetID();
        if (CSite::InGroup([1, 113]) && !in_array($curUserId, $arRedirectUsers)) {
            return;
        }

        $arSites = [
            [
                'site' => 'citto',
                'groups' => [85]
            ],
            [
                'site' => 'mfc',
                'groups' => [120]
            ],
            [
                'site' => 'edu',
                'groups' => [122]
            ],
            [
                'site' => 'gusc',
                'groups' => [138]
            ],
            [
                'site' => 'czn',
                'groups' => [152]
            ],
        ];

        foreach ($arSites as $arSite) {
            if (SITE_DIR != "/{$arSite['site']}/" && CSite::InGroup($arSite['groups'])) {
                CHTTP::SetStatus('302 Found');
                header("Location: /{$arSite['site']}" . $_SERVER['REQUEST_URI']);
                die;
            }
        }

        if (CSite::InGroup([135])) {
            if (
                false === mb_strpos($_SERVER['REQUEST_URI'], '/company/personal/') &&
                false === mb_strpos($_SERVER['REQUEST_URI'], '/bizproc/')
            ) {
                LocalRedirect('/company/personal/bizproc/');
            }
        }

        if (CSite::InGroup([96])) {
            /*
             * Пользователей ОМСУ редиректнуть в Контроль поручений.
             */
            $obCache = new \CPHPCache();
            if ($obCache->InitCache(86400, __METHOD__ . '_control_orders_deps_27_04_14_39', '/citto/controlorders/')) {
                $arCacheData = $obCache->GetVars();
                $arControlDeps = $arCacheData['DEPS'];
                $arSkipUsers = $arCacheData['USERS'];
            } elseif ($obCache->StartDataCache()) {
                Loader::includeModule('iblock');
                Loader::includeModule('intranet');
                $arControlDeps = \CIntranetUtils::GetDeparmentsTree(2331, true);
                $arControlDeps[] = 2331;

                $arSkipUsers = [];

                /*
                // ГУЗ ТО "ТОМИАЦ"
                $arSkipDeps = \CIntranetUtils::GetDeparmentsTree(2182, true);
                $arSkipDeps[] = 2182;

                // ГУЗ ТО "Территориальный центр медицины катастроф, скорой и неотложной медицинской помощи"
                $arSkipDeps[] = 2856;

                $rsParentSection = \CIBlockSection::GetByID(458);
                if ($arParentSection = $rsParentSection->GetNext()) {
                    $arFilter = array(
                        'IBLOCK_ID'     => $arParentSection['IBLOCK_ID'],
                        '>LEFT_MARGIN'  => $arParentSection['LEFT_MARGIN'],
                        '<RIGHT_MARGIN' => $arParentSection['RIGHT_MARGIN'],
                        '>DEPTH_LEVEL'  => $arParentSection['DEPTH_LEVEL']
                    );
                    $rsSect = \CIBlockSection::GetList(array('left_margin' => 'asc'), $arFilter);
                    while ($arSect = $rsSect->GetNext()) {
                    	if (in_array($arSect['ID'], $arSkipDeps)) {
                    		continue;
                    	}
                        $arSectFields = $GLOBALS['USER_FIELD_MANAGER']->GetUserFields('IBLOCK_5_SECTION', $arSect['ID'], LANGUAGE_ID);
                        $arSkipUsers[] = $arSectFields['UF_HEAD']['VALUE'];
                        if ($arSectFields['UF_PODVED']['VALUE']) {
                            $arControlDeps[] = $arSect['ID'];
                        }
                    }
                }
                $arSkipUsers = array_filter($arSkipUsers);
                */
                $obCache->EndDataCache([
                    'DEPS'  => $arControlDeps,
                    'USERS' => $arSkipUsers,
                ]);
            }

            if (!in_array($curUserId, $arSkipUsers)) {
                $orm = \Bitrix\Main\UserTable::getList([
                    'select'    => ['ID', 'UF_DEPARTMENT'],
                    'filter'    => ['ID' => $curUserId]
                ]);
                $arUser = $orm->fetch();
                if (in_array($arUser['UF_DEPARTMENT'][0], $arControlDeps)) {
                    $bRedirect = true;
                    $GLOBALS['USER_IS_PODVED'] = true;
                    if (preg_match('/\/company\/personal\/user\/(\d{1,})\/(.*)\/(.*)/si', $APPLICATION->GetCurPage(), $matches)) {
                        if (in_array($matches[2], ['tasks'])) {
                            LocalRedirect('/control-orders/');
                        }
                        if (in_array($matches[2], ['disk', 'disk/path'])) {
                            $bRedirect = false;
                        }
                    } elseif (
                        CSite::InDir('/company/personal/user/') ||
                        CSite::InDir('/docs/shared/path/Туластат') ||
                        mb_strpos($_SERVER['REQUEST_URI'], 'disk/downloadFile')
                    ) {
                        $bRedirect = false;
                    }

                    if ($bRedirect) {
                        LocalRedirect('/control-orders/');
                    }
                }
            }
        }

        if (CSite::InDir('/citto/workgroups/group/')) {
            $orm = \Bitrix\Main\UserTable::getList([
                'select'    => ['ID', 'LID'],
                'filter'    => ['ID' => $curUserId]
            ]);
            $arUser = $orm->fetch();
            if ($arUser['LID'] == 's1') {
                LocalRedirect(str_replace('/citto/', '/', $APPLICATION->GetCurPageParam()));
            }
        }
    }

    public static function handleOnBuildSocNetLogSql(&$arFields, &$arOrder, &$arFilter, &$arGroupBy, &$arSelectFields, &$arSqls)
    {
        if (CSIte::InDir('/mobile/') && CSite::InGroup([85]) && isset($arFilter['SITE_ID'])) {
            $arFilter['SITE_ID'] = ['nh'];
            $arSqls['WHERE'] = str_replace(" = 's1'", " = 'nh' ", $arSqls['WHERE']);
        }
    }
}

function tasks_UF_LAST_COMMENT($comment_id)
{
    Loader::includeModule('forum');
    Loader::includeModule('tasks');

    $arMessage = CForumMessage::GetList(
        [],
        ['ID' => $comment_id],
        false,
        0,
        ['SELECT' => ['UF_*']]
    )->Fetch();
    if (empty($arMessage)) {
        return;
    }
    if (mb_substr($arMessage['XML_ID'], 0, 5) != "TASK_") {
        return;
    }
    if ($arMessage['AUTHOR_ID'] == 1) {
        return;
    }
    $TASK_ID = intVal(trim(str_replace("TASK_", "", $arMessage['XML_ID'])));
    if (empty($TASK_ID)) {
        return;
    }

    if ($arMessage['PARAM1'] == 'TK' && $arMessage['PARAM2'] == $TASK_ID) {
        return;
    }

    $parser = new forumTextParser();
    $parser->imageWidth = 150;
    $parser->imageHtmlWidth = 150;
    $parser->userPath = SITE_DIR . "company/personal/user/#user_id#/";
    $parser->userNameTemplate = "";
    $parser->arUserfields = array_filter(
        $arMessage,
        function ($field_code) {
            return mb_substr($field_code, 0, 3) == "UF_";
        },
        ARRAY_FILTER_USE_KEY
    );

    if (empty($arMessage['SERVICE_TYPE'])) {
        $sText = $parser->convert($arMessage['POST_MESSAGE']);
        $sText = str_replace(['&lt;', '&gt;'], ['<', '>'], $sText);
        $sText = str_replace(['<br>', '<br/>', '<br />'], ' ', $sText);
        $sText = strip_tags($sText);
        $GLOBALS['USER_FIELD_MANAGER']->Update(
            'TASKS_TASK',
            $TASK_ID,
            [
                'UF_LAST_COMMENT' => $arMessage['AUTHOR_NAME'] . ': ' . $sText
            ]
        );
    }
}

function custom_mail($to, $subject, $message, $additional_headers = '', $additional_parameters = '')
{
    $tos = array_filter(explode(',', $to));
    $csbj = $subject;
    if (stripos($csbj, "=?UTF-8?B?") !== false) {
        $csbj = trim(base64_decode(str_replace("=?UTF-8?B?", "", $csbj)));
    }
    $subject = trim(preg_replace("/^(BP\:|NB\:)?(BP\:|NB\:)?/", "", $csbj));
    $message = str_replace($csbj, $subject, $message);
    if (!preg_match("/^(NB\:)|(BP\:NB\:)|(NB\:BP\:)/", $csbj)) {
        $exceptTo = [];
        $by = "ID";
        $order = "desc";
        $rsUsers = CUser::GetList(
            $by,
            $order,
            [
                'GROUPS_ID' => [102]
            ],
            [
                'FIELDS' => ["LOGIN", "EMAIL"]
            ]
        );
        while ($arUser = $rsUsers->Fetch()) {
            $exceptTo[] = mb_strtolower(trim($arUser['EMAIL'])) ?: mb_strtolower(trim($arUser['LOGIN'])) . '@tularegion.ru';
        }
        $tos = array_filter($tos, function ($email) use ($exceptTo) {
            return !in_array(mb_strtolower(trim($email)), $exceptTo);
        });
        if (empty($tos)) {
            return true;
        }
    }

    // require $_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php";
    // $logger = new \Monolog\Logger('default');
    // $logger->pushHandler(
    //     new \Monolog\Handler\RotatingFileHandler(
    //         $_SERVER['DOCUMENT_ROOT'] . '/local/logs/mail/custom_mail.log',
    //         7
    //     )
    // );
    // $logger->info('$tos', [$tos]);
    // $logger->info('$subject', [$subject]);
    // $logger->info('$message', [$message]);
    // $logger->info('$additional_headers', [$additional_headers]);
    // $logger->info('$additional_parameters', [$additional_parameters]);

    return mail(implode(",", $tos), $subject, $message, $additional_headers, $additional_parameters);
}

/**
 * @param $arIndicatorInput
 * @param $arReestrData
 * @param string $strControl
 * @param string $departmentsList
 * @throws \Bitrix\Main\LoaderException
 */
function generateBICSVOutput(
    $arIndicatorInput,
    $arReestrData,
    $strControl = '',
    $departmentsList = ''
) {
    if (Loader::includeModule("iblock")) {
        $arHeader = [
            'Полное наименование показателя',
            'Краткое наименование показателя',
            'Основание установления целевого показателя',
            'Целевое значение',
            'Текущее значение',
            '% исполнения',
            'Значение за предыдущий период',
            'Примечание',
            'Отдел',
            'Управление',
            'Дата последнего измененния'
        ];

        $arCSV = [];
        $arCSV[0] = $arHeader;

        $ci = 1;

        foreach ($arIndicatorInput['SHORT_NAME'] as $indicator) {
            $arCSV[$ci] = [
                $arIndicatorInput['FULL_NAME'][$ci - 1]['TEXT'],
                $arIndicatorInput['SHORT_NAME'][$ci - 1]['TEXT'],
                $arIndicatorInput['BASE_SET'][$ci - 1]['TEXT'],
                (isset($arReestrData[$ci - 1]['target_value'])) ? $arReestrData[$ci - 1]['target_value'] : $arIndicatorInput['TARGET_VALUE'][$ci - 1],
                (isset($arReestrData[$ci - 1]['state_value'])) ? $arReestrData[$ci - 1]['state_value'] : '',
                (isset($arReestrData[$ci - 1]['percent_exec'])) ? $arReestrData[$ci - 1]['percent_exec'] . '%' : '0%',
                (isset($arReestrData[$ci - 1]['state_value_old'])) ? $arReestrData[$ci - 1]['state_value_old'] : '',
                (isset($arReestrData[$ci - 1]['comment'])) ? $arReestrData[$ci - 1]['comment'] : '',
                (isset($arReestrData[$ci - 1]['department'])) ? $arReestrData[$ci - 1]['department'] : $departmentsList[$arIndicatorInput['OTDEL'][$ci - 1]],
                (isset($arReestrData[$ci - 1]['control'])) ? $arReestrData[$ci - 1]['control'] : $strControl,
                (isset($arReestrData[$ci - 1]['date_last_change'])) ? $arReestrData[$ci - 1]['date_last_change']->toString() . " г." : (new \DateTime())->format("d.m.Y") . " г.",
            ];
            $ci++;
        }

        $now = gmdate("D, d M Y H:i:s");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        header("Last-Modified: {$now} GMT");

        // force download
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");

        // disposition / encoding on response body
        header("Content-Disposition: attachment;filename=bi.csv");
        header("Content-Transfer-Encoding: binary");

        $fp = fopen("php://output", 'w');

        fputs($fp, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

        foreach ($arCSV as $fields) {
            fputcsv($fp, $fields, ';');
        }

        fclose($fp);
    }

    echo ob_get_clean();
}

if (!function_exists('File_Get_Contents_curl')) {
    /**
     * @param $url
     * @param array $header
     * @return bool|string
     */
    function File_Get_Contents_curl($url, $header = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        if (count($header) > 0) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }
}

if (!function_exists('GetParentDepartmentstucture')) {
    /**
     * Получение структуры вышестоящих учреждений
     * @param null $user_id
     */
    function GetParentDepartmentstucture($user_id = null)
    {
        Loader::includeModule("bizproc");
        Loader::includeModule("intranet");

        $arDeptopStruct = CIntranetUtils::GetUserDepartments($user_id);

        $arDeptopStructUser = function ($id) use (&$arDeptopStructUser) {
            $department = null;
            $arDepartmentData = [];

            if (!empty($id)) {
                $department = \Bitrix\Iblock\SectionTable::getRow(
                    [
                        'filter' => ['ID' => $id],
                        'select' => ['IBLOCK_SECTION_ID', 'NAME']
                    ]
                );

                if ($department) {
                    $arDepartment = [$department['IBLOCK_SECTION_ID']];

                    if ($department['IBLOCK_SECTION_ID']) {
                        $arDepartmentData = array_merge($arDepartment, $arDeptopStructUser($department['IBLOCK_SECTION_ID']));
                    }
                }
            }

            return $arDepartmentData;
        };

        if (isset($arDeptopStruct[0])) {
            $arListDeps = $arDeptopStructUser($arDeptopStruct[0]);
        } else {
            $arListDeps = [];
        }

        $arListDeps = array_reverse($arListDeps);

        return $arListDeps;
    }

}

if (!function_exists('getOmsuDep')) {
    /**
     * Входит ли авторизованный пользователь в подразделения ОМСУ внутри Контроля Поручений
     * @return bool
     */
    function getOmsuDep()
    {
        global $USER;
        $arUser = CUser::GetById($USER->GetID())->Fetch();

        $OMSU_IDS = CIntranetUtils::getSubDepartments(2331);

        return (!empty($arUser['UF_DEPARTMENT']) && count($OMSU_IDS) > 0 && array_intersect($arUser['UF_DEPARTMENT'], $OMSU_IDS));
    }
}
