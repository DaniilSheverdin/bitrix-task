<<<<<<< HEAD
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/company/report.php");

$APPLICATION->SetTitle(GetMessage("COMPANY_TITLE"));
$APPLICATION->AddChainItem(GetMessage("COMPANY_TITLE"));
?>
<?$APPLICATION->IncludeComponent(
	"bitrix:tasks.report",
	".default",
	array(
		"USER_ID"     => $USER->GetID(),
		"ITEMS_COUNT" => 20,
		"PAGE_VAR"    => "page",
		"USER_VAR"    => "user_id",
		"VIEW_VAR"    => "view_id",
		"TASK_VAR"    => "task_id",
		"ACTION_VAR"  => "action",
		"PATH_TO_USER_TASKS"           => "/citto/company/personal/user/#user_id#/tasks/",
		"PATH_TO_USER_TASKS_TASK"      => "/citto/company/personal/user/#user_id#/tasks/task/#action#/#task_id#/",
		"PATH_TO_USER_TASKS_VIEW"      => "/citto/company/personal/user/#user_id#/tasks/view/#action#/#view_id#/",
		"PATH_TO_USER_TASKS_REPORT"    => "/citto/company/personal/user/#user_id#/tasks/report/",
		'PATH_TO_USER_TASKS_TEMPLATES' => '/citto/company/personal/user/#user_id#/tasks/templates/',
		"PATH_TO_GROUP_TASKS"          => "/citto/workgroups/group/#group_id#/tasks/",
		"PATH_TO_GROUP_TASKS_TASK"     => "/citto/workgroups/group/#group_id#/tasks/task/#action#/#task_id#/",
		"PATH_TO_GROUP_TASKS_VIEW"     => "/citto/workgroups/group/#group_id#/tasks/view/#action#/#view_id#/",
		"PATH_TO_GROUP_TASKS_REPORT"   => "/citto/workgroups/group/#group_id#/tasks/report/",
		"PATH_TO_CONPANY_DEPARTMENT"   => "/citto/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#",
		"BACK_TO_TASKS" => "N",
		"SET_NAVCHAIN"  => "N",
		"SET_TITLE"     => "N"
	)
);?>
=======
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/company/report.php");

$APPLICATION->SetTitle(GetMessage("COMPANY_TITLE"));
$APPLICATION->AddChainItem(GetMessage("COMPANY_TITLE"));
?>
<?$APPLICATION->IncludeComponent(
	"bitrix:tasks.report",
	".default",
	array(
		"USER_ID"     => $USER->GetID(),
		"ITEMS_COUNT" => 20,
		"PAGE_VAR"    => "page",
		"USER_VAR"    => "user_id",
		"VIEW_VAR"    => "view_id",
		"TASK_VAR"    => "task_id",
		"ACTION_VAR"  => "action",
		"PATH_TO_USER_TASKS"           => "/citto/company/personal/user/#user_id#/tasks/",
		"PATH_TO_USER_TASKS_TASK"      => "/citto/company/personal/user/#user_id#/tasks/task/#action#/#task_id#/",
		"PATH_TO_USER_TASKS_VIEW"      => "/citto/company/personal/user/#user_id#/tasks/view/#action#/#view_id#/",
		"PATH_TO_USER_TASKS_REPORT"    => "/citto/company/personal/user/#user_id#/tasks/report/",
		'PATH_TO_USER_TASKS_TEMPLATES' => '/citto/company/personal/user/#user_id#/tasks/templates/',
		"PATH_TO_GROUP_TASKS"          => "/citto/workgroups/group/#group_id#/tasks/",
		"PATH_TO_GROUP_TASKS_TASK"     => "/citto/workgroups/group/#group_id#/tasks/task/#action#/#task_id#/",
		"PATH_TO_GROUP_TASKS_VIEW"     => "/citto/workgroups/group/#group_id#/tasks/view/#action#/#view_id#/",
		"PATH_TO_GROUP_TASKS_REPORT"   => "/citto/workgroups/group/#group_id#/tasks/report/",
		"PATH_TO_CONPANY_DEPARTMENT"   => "/citto/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#",
		"BACK_TO_TASKS" => "N",
		"SET_NAVCHAIN"  => "N",
		"SET_TITLE"     => "N"
	)
);?>
>>>>>>> e0a0eba79 (init)
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>