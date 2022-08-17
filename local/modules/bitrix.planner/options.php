<?

$module_id = 'bitrix.planner';

require_once($_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $module_id . '/include.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $module_id . '/CModuleOptions.php');
IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'] . '/local/modules/' . $module_id . '/options.php');

$showRightsTab = true;
$arUsSel['REFERENCE_ID'] = $arUsSel['REFERENCE'] = [];
$arTypeBlock['REFERENCE_ID'] = $arTypeBlock['REFERENCE'] = [];
$arIBlock['REFERENCE_ID'] = $arIBlock['REFERENCE'] = [];
$ID_OIVS['REFERENCE_ID'] = $ID_OIVS['REFERENCE'] = [];

$iblockTypes = CIBlockParameters::GetIBlockTypes();
foreach ($iblockTypes as $key => $value) {
    array_push($arTypeBlock['REFERENCE_ID'], $key);
    array_push($arTypeBlock['REFERENCE'], $value);
}
$keytype = array_keys($iblockTypes)[0];

$iblocks = COption::GetOptionString($module_id, "TYPE_IBLOCK");
if ($iblocks) {
    $keytype = $iblocks;
}
$s = CIBlock::GetList(array("SORT"=>"ASC"), array('TYPE'=>$keytype));
while ($item = $s->GetNext()) {
    array_push($arIBlock['REFERENCE_ID'], $item['ID']);
    array_push($arIBlock['REFERENCE'], $item['NAME']);
}

$ID_OIV = COption::GetOptionInt('bitrix.planner', "ID_OIV");
$res_section = CIBlockSection::GetByID($ID_OIV);
if ($ar_res = $res_section->GetNext()) {
    $parent_sec = [
        'IBLOCK_ID' => COption::GetOptionInt('intranet', 'iblock_structure'),
        'LEFT_MARGIN' => $ar_res['LEFT_MARGIN'],
        'RIGHT_MARGIN' => $ar_res['RIGHT_MARGIN']
    ];
}
$ID_OIV = CIBlockSection::GetList(array("NAME" => "ASC"), $parent_sec, false, ['ID', 'NAME'], false);
while ($item = $ID_OIV->getNext()) {
    array_push($ID_OIVS['REFERENCE_ID'], $item['ID']);
    array_push($ID_OIVS['REFERENCE'], $item['NAME']);
}

$arUsersIDs = [];
$rsUsers = CUser::GetList(
    ($by = "LAST_NAME"),
    ($order = "asc"),
    array(
        'ACTIVE' => 'Y',
    ),
    array('NAME', 'LAST_NAME', 'SECOND_NAME', 'ID')
);
while ($us = $rsUsers->GetNext()) {
    if (!empty($us['SECOND_NAME'])) {
        $fio = $us['LAST_NAME'] . ' ' . $us['NAME'] . ' ' . $us['SECOND_NAME'];
        array_push($arUsSel['REFERENCE_ID'], $us['ID']);
        array_push($arUsSel['REFERENCE'], $fio);
        $arUsersIDs[$us['ID']] = $fio;
    }
}

$arTabs = array(
    array(
        'DIV' => 'edit1',
        'TAB' => 'Настройки',
        'ICON' => '',
        'TITLE' => 'Настройки'
    )
);

$arGroups = array(
    'MAIN' => array('TITLE' => 'Основные настройки', 'TAB' => 0),
    'DELEGATION' => array('TITLE' => 'Делегирование полномочий руководителя отдела', 'TAB' => 0),
    'CROSS' => array('TITLE' => 'Взаимозаменяемость в правительстве', 'TAB' => 0),
    'USER_CROSS' => array('TITLE' => 'Взаимозаменяемость в правительстве (пользователи)', 'TAB' => 0, 'DIV' => 'user_cross'),
);

$arOptions = array(
    'ID_OIV' => array(
        'GROUP' => 'MAIN',
        'TITLE' => 'ID секции инфоблока подразделений',
        'TYPE' => 'INT',
        'DEFAULT' => '0',
        'SORT' => '1',
        'REFRESH' => 'N',
    ),

    'TYPE_IBLOCK' => array(
        'GROUP' => 'MAIN',
        'TITLE' => 'Тип инфоблока',
        'TYPE' => 'SELECT',
        'VALUES' => $arTypeBlock,
        'SORT' => '3'
    ),

    'VACATION_RECORDS' => array(
        'GROUP' => 'MAIN',
        'TITLE' => 'Инфоблок записей об отпусках',
        'TYPE' => 'SELECT',
        'VALUES' => $arIBlock,
        'SORT' => '4'
    ),

    'STAFF_DEPARTMENT' => array(
        'GROUP' => 'MAIN',
        'TITLE' => 'Представители отдела кадров',
        'TYPE' => 'MSELECT',
        'VALUES' => $arUsSel,
        'SORT' => '5'
    ),

    'DEPARTMENTS' => array(
        'GROUP' => 'DELEGATION',
        'TITLE' => 'Пользователи, которым делегируют полномочия',
        'TYPE' => 'SELECT',
        'VALUES' => $arUsSel,
        'SORT' => '6'
    ),

    'DELEGATION_USERS' => array(
        'GROUP' => 'DELEGATION',
        'TITLE' => 'Пользователи, делегирующие полномочия',
        'TYPE' => 'MSELECT',
        'VALUES' => $arUsSel,
        'SORT' => '7',
        'ID' => 'myInput2'
    ),

    'DELEGATION_ARR' => array(
        'GROUP' => 'DELEGATION',
        'TITLE' => 'Массив пользователей',
        'TYPE' => 'TEXT',
        'DEFAULT' => '',
        'SORT' => '8',
    ),
    'CROSS' => array(
        'GROUP' => 'CROSS',
        'TITLE' => 'Выберите представителей',
        'TYPE' => 'MSELECT',
        'VALUES' => $arUsSel,
        'SORT' => '9'
    ),
);

$arCross = unserialize(Bitrix\Main\Config\Option::get('bitrix.planner', "CROSS"));
foreach ($arCross as $iUserID) {
    $arOptions['USER_CROSS_' . $iUserID] = [
        'GROUP' => 'USER_CROSS',
        'TITLE' => $arUsersIDs[$iUserID],
        'TYPE' => 'MSELECT',
        'VALUES' => $arUsSel,
        'SORT' => '10'
    ];
}
/*
Конструктор класса CModuleOptions
$module_id - ID модуля
$arTabs - массив вкладок с параметрами
$arGroups - массив групп параметров
$arOptions - собственно сам массив, содержащий параметры
$showRightsTab - определяет надо ли показывать вкладку с настройками прав доступа к модулю ( true / false )
*/

$opt = new CModuleOptions($module_id, $arTabs, $arGroups, $arOptions, $showRightsTab);
$opt->ShowHTML();
