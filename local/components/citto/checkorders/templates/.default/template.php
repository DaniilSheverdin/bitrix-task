<?

use Bitrix\Main\UI\Extension;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if ($GLOBALS['USER']->IsAuthorized()) {
    $this->__component->fixEvent('module', $GLOBALS['USER']->GetID());
}

Extension::load([
    'ui.dialogs.messagebox',
    'ui.buttons',
    'ui.buttons.icons',
    'ui.notification',
    'popup',
    'ui.vue',
    'ui.vue.vuex',
    'ui.bootstrap4'
]);

CJSCore::Init(['jquery3', 'popup', 'ui']);

$arBodyClass = explode(' ', $APPLICATION->GetPageProperty('BodyClass', ''));
$arBodyClass[] = 'pagetitle-toolbar-field-view';
$APPLICATION->SetPageProperty('BodyClass', implode(' ', $arBodyClass));

$sectionId = 'control_orders_menu';
$settings = CUserOptions::GetOption('UI', $sectionId);
if (!empty($settings['firstPageLink']) && empty($_GET)) {
    LocalRedirect($settings['firstPageLink']);
}

// $GLOBALS['APPLICATION']->AddHeadString('<script>const controlOrdersStore = BX.Vuex.store({state:{data:{
//     executors: ' . json_encode($arResult['ISPOLNITELS'], JSON_UNESCAPED_UNICODE) . ',
// }}});</script>');
$arResult['MENU_ITEMS'] = array_values($arResult['MENU_ITEMS']);

$this->SetViewTarget('above_pagetitle');
$APPLICATION->IncludeComponent(
    'bitrix:main.interface.buttons',
    '',
    [
        'ID'    => $sectionId,
        'ITEMS' => $arResult['MENU_ITEMS'],
    ],
    false
);
$this->EndViewTarget();

$arEnabledEnum = [
    1282, // ОИВ
    1283, // ОМСУ
];

if ($_REQUEST['edit']!='') {
    require('edit.php');
} elseif ($_REQUEST['detail']) {
    require('detail.php');
} elseif ($_REQUEST['stats']) {
    require('stats.php');
} elseif ($_REQUEST['enums']) {
    require('enums.php');
} elseif ($_REQUEST['page'] && in_array($_REQUEST['page'], ['map', 'changelog'])) {
    require($_REQUEST['page'] . '.php');
} else {
    require('list.php');
}
