<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle(GetMessage("DESKTOP_TITLE"));
?>
<?$APPLICATION->IncludeComponent(
	"bitrix:desktop",
	"",
	Array(
		"ID" => "dashboard_nh",
		"CAN_EDIT" => "Y",
		"COLUMNS" => "3",
		"COLUMN_WIDTH_0" => "260px",
		"COLUMN_WIDTH_1" => "",
		"COLUMN_WIDTH_2" => "260px",
		"G_ADV_TYPE" => "INFO",
		"GADGETS" => Array("ALL"),
		"G_VIDEO_IBLOCK_TYPE"=>"services",
		"G_VIDEO_IBLOCK_ID"=>"428",
		"G_VIDEO_PATH_TO_FILE"=>"1423",
		"G_VIDEO_DURATION"=>"1424",
		"G_VIDEO_SECTION_ID"=>"1542",
		"G_VIDEO_ELEMENT_ID"=>"5042",
		"G_VIDEO_WIDTH"=>"400",
		"G_VIDEO_HEIGHT"=>"300",
		"G_VIDEO_CACHE_TYPE"=>"A",
		"G_VIDEO_CACHE_TIME"=>"3600000",
		"G_VIDEO_LIST_URL"=>"/citto/about/media.php",
		"G_VOTE_CHANNEL_SID"=>"COMPANY",
		"G_VOTE_CACHE_TYPE"=>"A",
		"G_VOTE_CACHE_TIME"=>"3600",
		"G_VOTE_LIST_URL"=>"/citto/services/votes.php",
		"G_BIRTHDAY_STRUCTURE_PAGE"=>"structure.php",
		"G_BIRTHDAY_PM_URL"=>"/citto/company/personal/messages/chat/#USER_ID#/",
		"G_BIRTHDAY_SHOW_YEAR"=>"M",
		"G_BIRTHDAY_USER_PROPERTY" => Array("WORK_POSITION"),
		"G_BIRTHDAY_LIST_URL"=>"/citto/company/birthdays.php",
		"G_LIFE_IBLOCK_TYPE"=>"news",
		"G_LIFE_IBLOCK_ID"=>"423",
		"G_LIFE_DETAIL_URL"=>"/citto/about/life.php?ID=#ELEMENT_ID#",
		"G_LIFE_CACHE_TYPE"=>"A",
		"G_LIFE_CACHE_TIME"=>"3600000",
		"G_LIFE_LIST_URL"=>"/citto/about/life.php",
		"G_OFFICIAL_IBLOCK_TYPE"=>"news",
		"G_OFFICIAL_IBLOCK_ID"=>"424",
		"G_OFFICIAL_DETAIL_URL"=>"/citto/about/official.php?ID=#ELEMENT_ID#",
		"G_OFFICIAL_CACHE_TYPE"=>"A",
		"G_OFFICIAL_CACHE_TIME"=>"3600000",
		"G_OFFICIAL_LIST_URL"=>"/citto/about/",
		"G_SHARED_DOCS_IBLOCK_TYPE"=>"library",
		"G_SHARED_DOCS_IBLOCK_ID"=>"#SHARED_FILES_IBLOCK_ID#",
		"G_SHARED_DOCS_DETAIL_URL"=>"/citto/docs/shared/element/view/#ELEMENT_ID#/",
		"G_SHARED_DOCS_CACHE_TYPE"=>"A",
		"G_SHARED_DOCS_CACHE_TIME"=>"3600",
		"G_SHARED_DOCS_LIST_URL"=>"/citto/docs/",
		"G_COMPANY_CALENDAR_IBLOCK_TYPE"=>"events",
		"G_COMPANY_CALENDAR_IBLOCK_ID"=>"#CALENDAR_COMPANY_IBLOCK_ID#",
		"G_COMPANY_CALENDAR_DETAIL_URL"=>"/citto/about/calendar.php",
		"G_COMPANY_CALENDAR_CACHE_TIME"=>"3600",
		"G_PHOTOS_IBLOCK_TYPE"=>"photos",
		"G_PHOTOS_IBLOCK_ID"=>"433",
		"G_PHOTOS_DETAIL_URL"=>"/citto/about/gallery/#SECTION_ID#/#ELEMENT_ID#/",
		"G_PHOTOS_DETAIL_SLIDE_SHOW_URL"=>"/citto/about/gallery/#SECTION_ID#/#ELEMENT_ID#/slide_show/",
		"G_PHOTOS_CACHE_TYPE"=>"A",
		"G_PHOTOS_CACHE_TIME"=>"3600000",
		"G_PHOTOS_LIST_URL"=>"/citto/about/gallery/",
		"G_WORKGROUPS_GROUP_VAR"=>"group_id",
		"G_WORKGROUPS_PATH_TO_GROUP"=>"/citto/workgroups/group/#group_id#/",
		"G_WORKGROUPS_PATH_TO_GROUP_SEARCH"=>"/citto/workgroups/",
		"G_WORKGROUPS_CACHE_TIME"=>"180",
		"G_BLOG_PATH_TO_BLOG"=>"/citto/company/personal/user/#user_id#/blog/",
		"G_BLOG_PATH_TO_POST"=>"/citto/company/personal/user/#user_id#/blog/#post_id#/",
		"G_BLOG_PATH_TO_GROUP_BLOG_POST"=>"/citto/workgroups/group/#group_id#/blog/#post_id#/",
		"G_BLOG_PATH_TO_USER"=>"/citto/company/personal/user/#user_id#/",
		"G_BLOG_CACHE_TYPE"=>"A",
		"G_BLOG_CACHE_TIME"=>"180",
		"G_TASKS_IBLOCK_ID"=>"#TASKS_IBLOCK_ID#",
		"G_TASKS_PATH_TO_GROUP_TASKS"=>"/citto/workgroups/group/#group_id#/tasks/",
		"G_TASKS_PATH_TO_GROUP_TASKS_TASK"=>"/citto/workgroups/group/#group_id#/tasks/task/#action#/#task_id#/",
		"G_TASKS_PATH_TO_USER_TASKS"=>"/citto/company/personal/user/#user_id#/tasks/",
		"G_TASKS_PATH_TO_USER_TASKS_TASK"=>"/citto/company/personal/user/#user_id#/tasks/task/#action#/#task_id#/",
		"G_TASKS_IBLOCK_ID"=>"#TASKS_IBLOCK_ID#",
		"G_TASKS_PATH_TO_GROUP_TASKS"=>"/citto/workgroups/group/#group_id#/tasks/",
		"G_TASKS_PATH_TO_GROUP_TASKS_TASK"=>"/citto/workgroups/group/#group_id#/tasks/task/#action#/#task_id#/",
		"G_TASKS_PATH_TO_USER_TASKS"=>"/citto/company/personal/user/#user_id#/tasks/",
		"G_TASKS_PATH_TO_USER_TASKS_TASK"=>"/citto/company/personal/user/#user_id#/tasks/task/#action#/#task_id#/",
		"G_CALENDAR_IBLOCK_TYPE"=>"events",
		"G_CALENDAR_IBLOCK_ID"=>"#CALENDAR_USERS_IBLOCK_ID#",
		"G_CALENDAR_DETAIL_URL"=>"/citto/company/personal/user/#user_id#/calendar/",
		"G_CALENDAR_CACHE_TYPE"=>"N",
		"G_CALENDAR_CACHE_TIME"=>"3600000",
		"G_HONOUR_LIST_URL" => "/citto/company/leaders.php",
		"G_NEW_EMPLOYEES_LIST_URL" => "/citto/company/events.php",
		"DATE_TIME_FORMAT" => "d.m.Y H:i:s",
		"DATE_FORMAT" => "d.m.Y",
		"DATE_FORMAT_NO_YEAR" => "d.m",
		"G_LIFE_ACTIVE_DATE_FORMAT" => "d.m.Y",
		"G_OFFICIAL_ACTIVE_DATE_FORMAT" => "d.m.Y",
		"GU_BLOG_DATE_TIME_FORMAT" => "d.m.Y H:i:s",
		"GU_FORUM_DATE_TIME_FORMAT" => "d.m.Y H:i:s",
		"GU_WORKGROUPS_DATE_TIME_FORMAT" => "d.m.Y H:i:s"

	),
	false
);?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>