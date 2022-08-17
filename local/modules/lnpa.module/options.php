<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Page\Asset;

global $APPLICATION;

$sModuleID = 'lnpa.module';
$sUrlPathModule = explode($_SERVER["DOCUMENT_ROOT"], __DIR__)[1];

Asset::getInstance()->addJs('/bitrix/templates/.default/jquery.min.js');
Asset::getInstance()->addJs("/local/js/bootstrap-plugin/popper.min.js");
Asset::getInstance()->addJs('/bitrix/templates/.default/bootstrap.min.js');
Asset::getInstance()->addJs('/local/js/bootstrap-plugin/bootstrap-select.min.js');
Asset::getInstance()->addJs("$sUrlPathModule/assets/scripts/script.js");
$APPLICATION->SetAdditionalCSS('/bitrix/templates/.default/bootstrap.min.css');
$APPLICATION->SetAdditionalCSS('/local/css/bootstrap-plugin/bootstrap-select.min.css');
$APPLICATION->SetAdditionalCSS("$sUrlPathModule/assets/styles/style.css");

require_once('include.php');
require_once('CModuleOptions.php');
IncludeModuleLangFile('options.php');

$arTabs = array(
    array(
        'DIV' => 'edit1',
        'TAB' => 'Настройки',
        'ICON' => '',
        'TITLE' => 'Настройки'
    )
);
$showRightsTab = true;

$arGroups = array(
    'IBLOCK' => array('TITLE' => 'ID инфоблока ЛНПА:', 'TAB' => 0),
    'ROLES' => array('TITLE' => 'Роли:', 'TAB' => 0),
    'TYPE' => array('TITLE' => 'Тип документа (справочник):', 'TAB' => 0),
);

$obUsers = CUser::GetList(($by = "LAST_NAME"), ($order = "asc"), ['ACTIVE' => 'Y'], ['NAME', 'LAST_NAME', 'SECOND_NAME', 'ID']);
while ($arUser = $obUsers->GetNext()) {
    if (!empty($arUser['SECOND_NAME'])) {
        $sFIO = $arUser['LAST_NAME'] . ' ' . $arUser['NAME'] . ' ' . $arUser['SECOND_NAME'];
        $arUsSel[$arUser['ID']] = $sFIO;
    }
}

$arOptions = array(
    'IBLOCK_LNPA' => array(
        'GROUP' => 'IBLOCK',
        'TITLE' => 'ID инфоблока ЛНПА',
        'TYPE' => 'INT',
        'DEFAULT' => '0',
        'SORT' => '1',
        'REFRESH' => 'N',
    ),
    'SUPER_ADMINS' => array(
        'GROUP' => 'ROLES',
        'TITLE' => 'Доступна вся струтктура портала:',
        'TYPE' => 'MSELECT',
        'VALUES' => $arUsSel,
        'SORT' => '2'
    ),
    'ADMINS' => array(
        'GROUP' => 'ROLES',
        'TITLE' => 'Администраторы:',
        'TYPE' => 'MSELECT',
        'VALUES' => $arUsSel,
        'SORT' => '3'
    ),
    'OPERATORS' => array(
        'GROUP' => 'ROLES',
        'TITLE' => 'Операторы:',
        'TYPE' => 'MSELECT',
        'VALUES' => $arUsSel,
        'SORT' => '4'
    ),
    'CLERKS' => array(
        'GROUP' => 'ROLES',
        'TITLE' => 'Делопроизводители:',
        'TYPE' => 'MSELECT',
        'VALUES' => $arUsSel,
        'SORT' => '5'
    ),
);

$opt = new CModuleOptions($sModuleID, $arTabs, $arGroups, $arOptions, $showRightsTab);
$opt->ShowHTML();
