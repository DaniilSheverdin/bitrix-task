<?

use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;
use Bitrix\Tasks\Integration\SocialNetwork;

define('STOP_STATISTICS', true);
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);
define('PUBLIC_AJAX_MODE', true);

if (isset($_POST['SITE_ID']) && (string) $_POST['SITE_ID'] != '') {
    $siteId = mb_substr(trim((string) $_POST['SITE_ID']), 0, 2);
    if (preg_match('#^[a-zA-Z0-9]{2}$#', $siteId)) {
        define('SITE_ID', $siteId);
    }
}

require_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/main/include/prolog_before.php');


/**
 * Скопировано из Bitrix\Tasks\Dispatcher\PublicAction\Integration\SocialNetwork::getDestinationData()
 * Кастомизировано:
 * Если юзер из МФЦ - показать только структуру МФЦ
 * Если юзер из МинОбр - показать только структуру МинОбр
 */
if ($_REQUEST['ACTION']['0']['PARAMETERS']['code'] == 'get_destination_data') {
    Loader::includeModule('tasks');
    $arDestination = SocialNetwork::getLogDestination('TASKS', array(
        'AVATAR_WIDTH' => 100,
        'AVATAR_HEIGHT' => 100,
        'USE_PROJECTS' => 'Y'
    ));

    global $USER;
    $arCurrentUser = UserTable::getList([
        'select'    => ['ID', 'LID', 'UF_DEPARTMENT'],
        'filter'    => ['ID' => $USER->GetID()]
    ])->Fetch();

    // МФЦ
    if ($arCurrentUser['LID'] == 'gi') {
        $arDestination['DEPARTMENT_RELATION']['DR58'] = $arDestination['DEPARTMENT_RELATION']['DR53']['items']['DR1727']['items']['DR204']['items']['DR58'];
        unset($arDestination['DEPARTMENT_RELATION']['DR53']);
    } else {
        // МинОбр
        $iMinObr = 463;
        Loader::includeModule('intranet');
        $arDeps = CIntranetUtils::GetDeparmentsTree($iMinObr, true);
        $arDeps[] = $iMinObr;
        if (in_array($arCurrentUser['UF_DEPARTMENT'][0], $arDeps)) {
            $arDestination['DEPARTMENT_RELATION']['DR463'] = $arDestination['DEPARTMENT_RELATION']['DR53']['items']['DR1727']['items']['DR463'];
            unset($arDestination['DEPARTMENT_RELATION']['DR53']);
        }
    }

    $arReturn = [
        'SUCCESS' => true,
        'ERROR' => [],
        'DATA' => [
            'get_destination_data' => [
                'OPERATION' => 'integration.socialnetwork.getdestinationdata',
                'ARGUMENTS' => [
                    'context' => 'TASKS',
                ],
                'RESULT' => $arDestination,
                'SUCCESS' => true,
                'ERRORS' => [],
            ]
        ],
        'ASSET' => []
    ];

    header('Content-type: application/json');
    die(json_encode($arReturn));
} else {
    require_once($_SERVER["DOCUMENT_ROOT"].'/bitrix/components/bitrix/tasks.base/class.php');

    TasksBaseComponent::executeComponentAjax();
    TasksBaseComponent::doFinalActions();
}