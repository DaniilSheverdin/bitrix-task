<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Localization\Loc;

CJSCore::Init("sidepanel");
CJSCore::Init("CJSTask");

global $APPLICATION;
Loc::loadMessages(__FILE__);

if (\Bitrix\Tasks\Util\DisposableAction::needConvertTemplateFiles())
{
	$APPLICATION->IncludeComponent(
		"bitrix:tasks.util.process",
		'',
		array(),
		false,
		array("HIDE_ICONS" => "Y")
	);
}

$this->addExternalJS("/local/components/serg/tasks.task.list/templates/.default/d3.v5.js");
$this->addExternalJS("/local/components/serg/tasks.task.list/templates/.default/donat3d.js");

$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
$APPLICATION->SetPageProperty("BodyClass", ($bodyClass ? $bodyClass." " : "")."page-one-column");
?>

<?php $APPLICATION->IncludeComponent(
	'bitrix:tasks.interface.header',
	'',
	array(
		'FILTER_ID' => $arParams["FILTER_ID"],
		'GRID_ID'   => $arParams["GRID_ID"],

		'FILTER'    => $arResult['FILTER'],
		'PRESETS'   => $arResult['PRESETS'],

		'SHOW_QUICK_FORM'  => 'Y',
		'GET_LIST_PARAMS'  => $arResult['GET_LIST_PARAMS'],
		'COMPANY_WORKTIME' => $arResult['COMPANY_WORKTIME'],
		'NAME_TEMPLATE'    => $arParams['NAME_TEMPLATE'],

		'USER_ID'  => $arParams['USER_ID'],
		'GROUP_ID' => $arParams['GROUP_ID'],

		'MARK_ACTIVE_ROLE'    => $arParams['MARK_ACTIVE_ROLE'],
		'MARK_SECTION_ALL'    => $arParams['MARK_SECTION_ALL'],
		'MARK_SPECIAL_PRESET' => $arParams['MARK_SPECIAL_PRESET'],

		'PATH_TO_USER_TASKS'                   => $arParams['PATH_TO_USER_TASKS'],
		'PATH_TO_USER_TASKS_TASK'              => $arParams['PATH_TO_USER_TASKS_TASK'],
		'PATH_TO_USER_TASKS_VIEW'              => $arParams['PATH_TO_USER_TASKS_VIEW'],
		'PATH_TO_USER_TASKS_REPORT'            => $arParams['PATH_TO_USER_TASKS_REPORT'],
		'PATH_TO_USER_TASKS_TEMPLATES'         => $arParams['PATH_TO_USER_TASKS_TEMPLATES'],
		'PATH_TO_USER_TASKS_PROJECTS_OVERVIEW' => $arParams['PATH_TO_USER_TASKS_PROJECTS_OVERVIEW'],

		'PATH_TO_GROUP'              => $arParams['PATH_TO_GROUP'],
		'PATH_TO_GROUP_TASKS'        => $arParams['PATH_TO_GROUP_TASKS'],
		'PATH_TO_GROUP_TASKS_TASK'   => $arParams['PATH_TO_GROUP_TASKS_TASK'],
		'PATH_TO_GROUP_TASKS_VIEW'   => $arParams['PATH_TO_GROUP_TASKS_VIEW'],
		'PATH_TO_GROUP_TASKS_REPORT' => $arParams['PATH_TO_GROUP_TASKS_REPORT'],

		'PATH_TO_USER_PROFILE'       => $arParams['PATH_TO_USER_PROFILE'],
		'PATH_TO_MESSAGES_CHAT'      => $arParams['PATH_TO_MESSAGES_CHAT'],
		'PATH_TO_VIDEO_CALL'         => $arParams['PATH_TO_VIDEO_CALL'],
		'PATH_TO_CONPANY_DEPARTMENT' => $arParams['PATH_TO_CONPANY_DEPARTMENT'],

		'USE_EXPORT'             => 'Y',
		// export on role pages and all
		'USE_AJAX_ROLE_FILTER'  => 'Y',
		'USE_GROUP_BY_SUBTASKS'  => 'Y',
		'USE_GROUP_BY_GROUPS'    => $arParams['NEED_GROUP_BY_GROUPS'] === 'Y' ? 'Y' : 'N',
		'GROUP_BY_PROJECT'       => $arResult['GROUP_BY_PROJECT'],
		'SHOW_USER_SORT'         => 'Y',
		'SORT_FIELD'             => $arParams['SORT_FIELD'],
		'SORT_FIELD_DIR'         => $arParams['SORT_FIELD_DIR'],
		'SHOW_SECTION_TEMPLATES' => $arParams['GROUP_ID'] > 0 ? 'N' : 'Y',
		'DEFAULT_ROLEID'		 =>	$arParams['DEFAULT_ROLEID']
	),
	$component,
	array('HIDE_ICONS' => true)
); ?>

<?php
if (is_array($arResult['ERROR']['FATAL']) && !empty($arResult['ERROR']['FATAL'])):
	foreach ($arResult['ERROR']['FATAL'] as $error):
		echo ShowError($error['MESSAGE']);
	endforeach;

	return;
endif
?>

<? if (is_array($arResult['ERROR']['WARNING'])): ?>
	<? foreach ($arResult['ERROR']['WARNING'] as $error): ?>
		<?=ShowError($error['MESSAGE'])?>
	<? endforeach ?>
<? endif ?>

<?
foreach ($arResult['ROWS']['ITEMS'] as $item) {
    if (!in_array($item['columns']['RESPONSIBLE_NAME'] ,$arResult['USERS'])) {
        $arResult['USERS'][] = $item['columns']['RESPONSIBLE_NAME'];
    }



}
?>

<?
if ($_REQUEST['mode_users'] == 'Y') {
    echo "<div class='tasks-view-switcher-list-item switch-button'><a href='?F_STATE=".$_REQUEST['F_STATE']."'>Сгруппировать по группам</a></div>";
} else {
    echo "<div class='tasks-view-switcher-list-item switch-button'><a href='?F_STATE=".$_REQUEST['F_STATE']."&mode_users=Y'>Сгруппировать по пользователям</a></div>";
}
?>
<div class="container-users">
    <div class="users__list">
        <div class="users__list_item">
            <?foreach ($arResult['USERS'] as $user):?>
                <? $group = '';?>
                <div class="users__list_title"><?=$user?><span class="triangle_grey"></span></div>
                <div class="project__list_container">
                    <? foreach ($arResult['ROWS']['ITEMS'] as $item): ?>
                    <? if ($item['columns']['RESPONSIBLE_NAME'] == $user): ?>
                         <? if ($group != $item['columns']['GROUP_NAME']):  ?>
                    <div class="projects__list_title" data-group="<?=$item['parent_group_id']?>"><?=$item['columns']['GROUP_NAME']?><span class="triangle_grey"></span></div>
                    <?endif;?>

                    <div class="tasks__list_container" data-group="<?=$item['parent_group_id']?>">

                        <div class="tasks__list_title"><?=$item['columns']['TITLE']?><?=$item['columns']['DEADLINE']?></div>

                    </div>

                            <? $group = $item['columns']['GROUP_NAME'] ?>
                    <?endif;?>
                    <? endforeach; ?>
                </div>
            <?endforeach;?>
        </div>
    </div>

    <div class="users__graph">
        <div id="graph"></div>
        <div id="legend"></div>
    </div>

</div>

<script>
    var namesData = <?echo json_encode($arResult['NAMES'])?>;
</script>
