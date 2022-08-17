<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$MESS['TASKS_STATUS_1'] = 'Новая';
$MESS['TASKS_STATUS_2'] = 'Ждет выполнения';
$MESS['TASKS_STATUS_3'] = 'Выполняется';
$MESS['TASKS_STATUS_4'] = 'Ждет контроля';
$MESS['TASKS_STATUS_5'] = 'Завершена';
$MESS['TASKS_STATUS_6'] = 'Отложена';
$MESS['TASKS_STATUS_7'] = 'Отклонена';

?>
<div class="box-header with-border">
    <h3 class="box-title">Связанные задачи</h3>
    <?php
    if ($arResult['PERMISSIONS']['controler'] || $arResult['PERMISSIONS']['kurator'] || $arResult['PERMISSIONS']['ispolnitel']) {
        ?>
        <a class="ui-btn-success ui-btn ui-btn-icon-add float-right js-popup-task-add" href="#">Добавить существующую задачу</a>
        <a class="ui-btn-success ui-btn ui-btn-icon-add float-right mr-2" href="/company/personal/user/<?=$GLOBALS['USER']->GetID() ?>/tasks/task/edit/0/">Добавить новую задачу</a>
        <div class="popup-task-add">
            <?$APPLICATION->IncludeComponent(
                'bitrix:tasks.task.selector',
                '.default',
                array(
                    'MULTIPLE' => 'N',
                    'NAME' => 'PARENT_TASK',
                    'VALUE' => $arData['PARENT_ID'],
                    'POPUP' => 'N',
                    'ON_SELECT' => 'onAddTaskSelect',
                    'PATH_TO_TASKS_TASK' => $arParams['PATH_TO_TASKS_TASK'],
                    'SITE_ID' => SITE_ID,
                    'SELECT' => array('ID', 'TITLE', 'STATUS'),
                ),
                null,
                array('HIDE_ICONS' => 'Y')
            );?>
            <form class="js-task-add-form" method="POST" action="/control-orders/?detail=<?=$_REQUEST['detail'] ?>&view=svyazi&sub=<?=$_REQUEST['sub'] ?>&back_url=<?=$backUrl?>">
                <input type="hidden" name="subaction" value='add_task'>
                <input type="hidden" name="add" value="">
            </form>
        </div>
        <?php
    }
    ?>
</div>

<div class="box-body box-profile">
<?php
$arColumns   = [
    ['id' => 'ID', 'name' => 'ID', 'sort' => 'ID', 'default' => false],
    ['id' => 'NAME', 'name' => 'Название задачи', 'sort' => 'NAME', 'default' => false],
    ['id' => 'TEXT', 'name' => 'Описание задачи', 'sort' => 'TEXT', 'default' => true],
    ['id' => 'ISPOLNITEL', 'name' => 'Исполнитель', 'sort' => 'ISPOLNITEL', 'default' => true],
    ['id' => 'DATE_SROK', 'name' => 'Срок исполнения', 'sort' => 'DEADLINE', 'default' => true],
    ['id' => 'STATUS', 'name' => 'Статус', 'sort' => 'STATUS', 'default' => true],
];
$arList = [];
global $USER;
foreach ($arResult['DETAIL_DATA']['TASKS'] as $sKey => $arTask) {
    $arrData = [
        'data'    => [
            'ID' => $arTask['ID'],
            'NAME' => $arTask['TITLE'],
            'TEXT' => $arTask['~DESCRIPTION'],
            'ISPOLNITEL' => $arTask['~RESPONSIBLE_LAST_NAME'] . ' ' . $arTask['~RESPONSIBLE_NAME'],
            'DATE_SROK' => $arTask['DEADLINE'],
            'STATUS' => $MESS['TASKS_STATUS_' . $arTask['STATUS'] ]
        ],
        'class'   => '',
        'actions' => [
            [
                'text'    => 'Просмотр',
                'default' => true,
                'onclick' => 'document.location.href="/company/personal/user/'.$USER->GetID().'/tasks/task/view/' . $arTask['ID'] . '/"'
            ],
        ],
    ];
    $arList[] = $arrData;
}
?>
<?$APPLICATION->IncludeComponent(
    'bitrix:main.ui.grid',
    'modified',
    [
        'GRID_ID'                   => 'list_tasks',
        'COLUMNS'                   => $arColumns,
        'ROWS'                      => $arList,
        'SHOW_ROW_CHECKBOXES'       => false,
        'NAV_OBJECT'                => $nav,
        'AJAX_MODE'                 => 'N',
        'AJAX_ID'                   => CAjax::GetComponentID('bitrix:main.ui.grid', 'modified', ''),
        'PAGE_SIZES'                => [
            ['NAME' => '20', 'VALUE' => '20'],
            ['NAME' => '50', 'VALUE' => '50'],
            ['NAME' => '100', 'VALUE' => '100'],
        ],
        'AJAX_OPTION_JUMP'          => 'N',
        'SHOW_CHECK_ALL_CHECKBOXES' => false,
        'SHOW_ROW_ACTIONS_MENU'     => true,
        'ACTION_PANEL'              => $ACTION_PANEL,
        'SHOW_GRID_SETTINGS_MENU'   => true,
        'SHOW_NAVIGATION_PANEL'     => true,
        'SHOW_PAGINATION'           => true,
        'SHOW_SELECTED_COUNTER'     => false,
        'SHOW_TOTAL_COUNTER'        => true,
        'SHOW_PAGESIZE'             => true,
        'SHOW_ACTION_PANEL'         => false,
        'ALLOW_COLUMNS_SORT'        => true,
        'ALLOW_COLUMNS_RESIZE'      => true,
        'ALLOW_HORIZONTAL_SCROLL'   => true,
        'ALLOW_SORT'                => true,
        'ALLOW_PIN_HEADER'          => true,
        'AJAX_OPTION_HISTORY'       => 'N',
    ]
);?>
</div>
<script type="text/javascript">
BX.addCustomEvent("tasksTaskEvent", BX.delegate(function(type, data) {
    if (type === 'ADD') {
        $('.js-task-add-form input[name=add]').val(data.task.ID);
        $('.js-task-add-form').submit();
    }
}, this));
</script>