<?

use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Grid\Options as GridOptions;
use Bitrix\Main\UI\PageNavigation;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
$strNameRole = '';

foreach ($arResult['ROLES'] as $strCodeRole => $bValue) {
    if ($bValue) {
        switch ($strCodeRole) {
            case 'ADMIN':
                $strNameRole = 'Адмиинистратор';
                break;
            case 'OPERATOR':
                $strNameRole = 'Оператор';
                break;
            case 'KURATOR':
                $strNameRole = 'Куратор программы';
                break;
            case 'TEHNADZOR':
                $strNameRole = 'Сотрудник технического надзора';
                break;
            case 'FINANCE':
                $strNameRole = 'Специалист отдела финансирования';
                break;

        }
    }
}

?>

<div class="role"><?=$strNameRole?></div>

<?


if ($arResult['ROLES']['ADMIN'] || $arResult['ROLES']['OPERATOR']) {
    $APPLICATION->AddViewContent('inside_pagetitle', '
    <div class="float-right" style="margin-top:-50px">
        <a href="/edu/financing/?edit=0" class="ui-btn ui-btn-success ui-btn-icon-add">Создать</a>
    </div>
    ');
}


$list_id = 'edu-list-main';

$grid_options = new GridOptions($list_id);
$arUsedColumns = $grid_options->getUsedColumns();

$arColumns   = [];
$arColumns[] = ['id' => 'NUMBER', 'name' => 'Номер заявки', 'sort' => 'ID', 'default' => true];
$arColumns[] = ['id' => 'STATUS', 'name' => 'Статус', 'sort' => false, 'default' => true];
$arColumns[] = ['id' => 'PROGRAM', 'name' => 'Наименование программы', 'sort' => false, 'default' => true];
$arColumns[] = ['id' => 'EVENT', 'name' => 'Мероприятия программы', 'sort' => false, 'default' => true];
$arColumns[] = ['id' => 'MUNICIPALITY', 'name' => 'Муниципальное образование', 'sort' => false, 'default' => true];
$arColumns[] = ['id' => 'ORGAN', 'name' => 'Учреждение', 'sort' => false, 'default' => true];
$arColumns[] = ['id' => 'ADDRESS', 'name' => 'Адрес', 'sort' => false, 'default' => true];
$arColumns[] = ['id' => 'AMOUNT', 'name' => 'Запрашиваемая сумма', 'sort' => false, 'default' => true];


$arFilter = [];
$strRole = '';

foreach ($arResult['ROLES'] as $strRoleKey => $value) {
    if ($value) {
        $strRole = $strRoleKey;
    }
}

switch ($strRole) {
    case 'FINANCE':
        $arFilter['!PROPERTY_STATUS'] = [$arResult['ENUMS']['STATUS']['DRAFT']['ID'], $arResult['ENUMS']['STATUS']['NEW']['ID']];
        break;

    case 'KURATOR':
    case 'TEHNADZOR':
        $arFilter['!PROPERTY_STATUS'] = [$arResult['ENUMS']['STATUS']['DRAFT']['ID']];
        break;

}


$filterOption = new Bitrix\Main\UI\Filter\Options($list_id);
$filterData = $filterOption->getFilter([]);


foreach ($filterData as $k => $v) {
    if ($k == 'DATE_CREATE_from') {
        $arFilter['>=DATE_CREATE'] = $v;
    }
    if ($k == 'DATE_CREATE_to') {
        $arFilter['<=DATE_CREATE'] = $v;
    }
    if ($k == 'ATT_PROPERTY_STATUS') {
        foreach ($v as $status) {
            $arFilter['PROPERTY_STATUS'][] = $arResult['ENUMS']['STATUS'][$status]['ID'];
        }
    }
    if ($k == 'FIND') {
        $strYear = explode('-', $v)[0];
        $strNV = explode('-', $v)[1];
        $strN = explode('/', $strNV)[0];
        $strV = explode('/', $strNV)[1];

        $arFilter['PROPERTY_YEAR'] = $strYear;
        $arFilter['PROPERTY_NUMBER'] = intval($strN);
        $arFilter['PROPERTY_VERSION'] = intval($strV);
    }
}

$arList = $this->__component->getList($arFilter);
$arUIFilter = $this->__component->defaultFilter;


$arGrid['grid_options'] = new GridOptions($list_id);
$arGrid['sort'] = $arGrid['grid_options']->GetSorting(['sort' => ['DATE_CREATE' => 'DESC'], 'vars' => ['by' => 'by', 'order' => 'order']]);
$arGrid['nav_params'] = $arGrid['grid_options']->GetNavParams();


$arGrid['nav'] = new PageNavigation($list_id);
$arGrid['nav']->allowAllRecords(true)
    ->setPageSize($arGrid['nav_params']['nPageSize'])
    ->initFromUri();
if ($arGrid['nav']->allRecordsShown()) {
    $arGrid['nav_params'] = false;
} else {
    $arGrid['nav_params']['iNumPage'] = $arGrid['nav']->getCurrentPage();
}






?>








<div class="grid_filter">
    <?$APPLICATION->IncludeComponent('bitrix:main.ui.filter', '', [
        'FILTER_ID' => $list_id,
        'GRID_ID' => $list_id,
        'FILTER' => $arUIFilter,
        'ENABLE_LIVE_SEARCH' => true,
        'ENABLE_LABEL' => true
    ]);?>
</div>

<?


$APPLICATION->IncludeComponent(
    'bitrix:main.ui.grid',
    '',
    [
        'GRID_ID'                   => $list_id,
        'COLUMNS'                   => $arColumns,
        'ROWS'                      => $arList,
        'SHOW_ROW_CHECKBOXES'       => false,
        'NAV_OBJECT'                => $arGrid['nav'],
        'AJAX_MODE'                 => 'Y',
        'AJAX_ID'                   => CAjax::GetComponentID('bitrix:main.ui.grid', 'modified', ''),
        'PAGE_SIZES'                => [
            ['NAME' => '20', 'VALUE' => '20'],
            ['NAME' => '50', 'VALUE' => '50'],
            ['NAME' => '100', 'VALUE' => '100'],
            ['NAME' => '500', 'VALUE' => '500'],
            ['NAME' => 'Все', 'VALUE' => '99999'],
        ],
        'AJAX_OPTION_JUMP'          => 'Y',
        'SHOW_CHECK_ALL_CHECKBOXES' => false,
        'SHOW_ROW_ACTIONS_MENU'     => true,
        'ACTION_PANEL'              => $ACTION_PANEL,
        'SHOW_GRID_SETTINGS_MENU'   => true,
        'SHOW_NAVIGATION_PANEL'     => true,
        'SHOW_PAGINATION'           => true,
        'SHOW_SELECTED_COUNTER'     => true,
        'SHOW_TOTAL_COUNTER'        => true,
        'SHOW_PAGESIZE'             => true,
        'SHOW_ACTION_PANEL'         => true,
        'ALLOW_COLUMNS_SORT'        => true,
        'ALLOW_COLUMNS_RESIZE'      => true,
        'ALLOW_HORIZONTAL_SCROLL'   => true,
        'ALLOW_SORT'                => true,
        'ALLOW_PIN_HEADER'          => true,
        'AJAX_OPTION_HISTORY'       => 'Y',
        'TOTAL_ROWS_COUNT'          => $cntRows,
    ]
);



?>
<script type="text/javascript">
BX.SidePanel.Instance.bindAnchors({
   rules:
   [
        {
            condition: [
                /\/edu\/financing\/\?(edit|detail)=([a-zA-Z0-9_|]+)/i
            ],
        }
   ]
});
</script>
