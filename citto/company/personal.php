<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/company/personal.php");
$APPLICATION->SetTitle(GetMessage("COMPANY_TITLE"));
?>

<?$APPLICATION->IncludeComponent("bitrix:socialnetwork_user", ".default", Array(
	"ITEM_DETAIL_COUNT"	=>	"32",
	"ITEM_MAIN_COUNT"	=>	"6",
	"DATE_TIME_FORMAT"	=> "d.m.Y H:i:s",
	"NAME_TEMPLATE" => "",
	"PATH_TO_GROUP" => "/citto/workgroups/group/#group_id#/",
	"PATH_TO_GROUP_SUBSCRIBE" => "/citto/workgroups/group/#group_id#/subscribe/",
	"PATH_TO_GROUP_SEARCH" => "/citto/workgroups/group/search/",
	"PATH_TO_SEARCH_EXTERNAL" => "/citto/company/index.php",
	"PATH_TO_CONPANY_DEPARTMENT" => "/citto/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#",
	"PATH_TO_GROUP_TASKS" => "/citto/workgroups/group/#group_id#/tasks/",
	"PATH_TO_GROUP_TASKS_TASK" => "/citto/workgroups/group/#group_id#/tasks/task/#action#/#task_id#/",
	"PATH_TO_GROUP_TASKS_VIEW" => "/citto/workgroups/group/#group_id#/tasks/view/#action#/#view_id#/",
	"PATH_TO_GROUP_POST" => "/citto/workgroups/group/#group_id#/blog/#post_id#/",
	"PATH_TO_GROUP_PHOTO" => "/citto/workgroups/group/#group_id#/photo/",
	"PATH_TO_GROUP_PHOTO_SECTION" => "/citto/workgroups/group/#group_id#/photo/album/#section_id#/",
	"PATH_TO_GROUP_PHOTO_ELEMENT" => "/citto/workgroups/group/#group_id#/photo/#section_id#/#element_id#/",
	"SEF_MODE" => "Y",
	"SEF_FOLDER" => "/citto/company/personal/",
	"AJAX_MODE" => "N",
	"AJAX_OPTION_SHADOW" => "Y",
	"AJAX_OPTION_JUMP" => "N",
	"AJAX_OPTION_STYLE" => "Y",
	"AJAX_OPTION_HISTORY" => "Y",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "3600",
	"CACHE_TIME_LONG" => "604800",
	"PATH_TO_SMILE" => "/bitrix/images/socialnetwork/smile/",
	"PATH_TO_BLOG_SMILE" => "/bitrix/images/blog/smile/",
	"PATH_TO_FORUM_SMILE" => "/bitrix/images/forum/smile/",
	"PATH_TO_FORUM_ICON" => "/bitrix/images/forum/icon/",
	"SET_TITLE" => "Y",
	"SET_NAV_CHAIN" => "Y",
	"HIDE_OWNER_IN_TITLE" => "Y",
	"SHOW_RATING" => "",
	"RATING_TYPE" => "",
	"USER_FIELDS_MAIN" => array(
		0 => "LAST_LOGIN",
		1 => "PERSONAL_PROFESSION",
		2 => "WORK_POSITION",
	),
	"USER_PROPERTY_MAIN" => array(
		0 => "UF_DEPARTMENT",
	),
	"USER_FIELDS_CONTACT" => array(
		0 => "EMAIL",
		1 => "PERSONAL_WWW",
		2 => "PERSONAL_ICQ",
		3 => "PERSONAL_PHONE",
		4 => "PERSONAL_FAX",
		5 => "PERSONAL_MOBILE",
		6 => "WORK_WWW",
		7 => "WORK_PHONE",
		8 => "WORK_FAX",
	),
	"USER_PROPERTY_CONTACT" => array(
		0 => "UF_PHONE_INNER",	
		1 => "UF_SKYPE",
		2 => "UF_TWITTER",
		3 => "UF_FACEBOOK",
		4 => "UF_LINKEDIN",
		5 => "UF_XING",
	),
	"USER_FIELDS_PERSONAL" => array(
		0 => "PERSONAL_BIRTHDAY",
		1 => "PERSONAL_GENDER",
	),
	"USER_PROPERTY_PERSONAL" => array(
		0 => "UF_SKILLS",
		1 => "UF_INTERESTS",
		2 => "UF_WEB_SITES",
	),
	"AJAX_LONG_TIMEOUT" => "60",
	"EDITABLE_FIELDS" => array(
		0 => "LOGIN",
		1 => "NAME",
		2 => "SECOND_NAME",
		3 => "LAST_NAME",
		4 => "EMAIL",
		5 => "PASSWORD",
		6 => "PERSONAL_BIRTHDAY",
		7 => "PERSONAL_WWW",
		8 => "PERSONAL_ICQ",
		9 => "PERSONAL_GENDER",
		10 => "PERSONAL_PHOTO",
		11 => "PERSONAL_PHONE",
		12 => "PERSONAL_FAX",
		13 => "PERSONAL_MOBILE",
		14 => "PERSONAL_COUNTRY",
		15 => "PERSONAL_STATE",
		16 => "PERSONAL_CITY",
		17 => "PERSONAL_ZIP",
		18 => "PERSONAL_STREET",
		19 => "PERSONAL_MAILBOX",
		20 => "WORK_PHONE",
		21 => "FORUM_SHOW_NAME",
		22 => "FORUM_DESCRIPTION",
		23 => "FORUM_INTERESTS",
		24 => "FORUM_SIGNATURE",
		25 => "FORUM_AVATAR",
		26 => "FORUM_HIDE_FROM_ONLINE",
		27 => "FORUM_SUBSC_GET_MY_MESSAGE",
		28 => "BLOG_ALIAS",
		29 => "BLOG_DESCRIPTION",
		30 => "BLOG_INTERESTS",
		31 => "BLOG_AVATAR",
		32 => "UF_PHONE_INNER",
		33 => "UF_SKYPE",
		34 => "TIME_ZONE",
		35 => "UF_TWITTER",
		36 => "UF_FACEBOOK",
		37 => "UF_LINKEDIN",
		38 => "UF_XING",
		39 => "UF_SKILLS",
		40 => "UF_INTERESTS",
		41 => "UF_WEB_SITES",
	),
	"SHOW_YEAR" => "M",
	"USER_FIELDS_SEARCH_SIMPLE" => array(
		0 => "PERSONAL_GENDER",
		1 => "PERSONAL_CITY",
	),
	"USER_PROPERTIES_SEARCH_SIMPLE" => array(
	),
	"USER_FIELDS_SEARCH_ADV" => array(
		0 => "PERSONAL_GENDER",
		1 => "PERSONAL_COUNTRY",
		2 => "PERSONAL_CITY",
	),
	"USER_PROPERTIES_SEARCH_ADV" => array(
	),
	"SONET_USER_FIELDS_LIST" => array(
		0 => "PERSONAL_BIRTHDAY",
		1 => "PERSONAL_GENDER",
		2 => "PERSONAL_CITY",
	),
	"SONET_USER_PROPERTY_LIST" => array(
	),
	"SONET_USER_FIELDS_SEARCHABLE" => array(
	),
	"SONET_USER_PROPERTY_SEARCHABLE" => array(
	),
	"BLOG_GROUP_ID" => "63",
	"BLOG_COMMENT_AJAX_POST" => "Y",
	"FORUM_ID" => "327",
	"CALENDAR_IBLOCK_TYPE"	=>	"events",
	"CALENDAR_USER_IBLOCK_ID"	=>	"0",
	"CALENDAR_WEEK_HOLIDAYS"	=>	array(
		0	=>	"5",
		1	=>	"6",
	),
	"CALENDAR_YEAR_HOLIDAYS"	=>	(LANGUAGE_ID == "en") ? "1.01, 25.12" : ((LANGUAGE_ID == "de") ? "1.01, 25.12" : "1.01, 2.01, 7.01, 23.02, 8.03, 1.05, 9.05, 12.06, 4.11"),
	"CALENDAR_WORK_TIME_START" => "9",
	"CALENDAR_WORK_TIME_END" => "19",
	"CALENDAR_ALLOW_SUPERPOSE" => "Y",
	"CALENDAR_SUPERPOSE_CAL_IDS" => array(
		0 => "#CALENDAR_COMPANY_IBLOCK_ID#",
	),
	"CALENDAR_SUPERPOSE_CUR_USER_CALS" => "Y",
	"CALENDAR_SUPERPOSE_USERS_CALS" => "Y",
	"CALENDAR_SUPERPOSE_GROUPS_CALS" => "Y",
	"CALENDAR_SUPERPOSE_GROUPS_IBLOCK_ID" => "#CALENDAR_GROUPS_IBLOCK_ID#",
	"CALENDAR_ALLOW_RES_MEETING" => "Y",
	"CALENDAR_RES_MEETING_IBLOCK_ID" => "432",
	"CALENDAR_PATH_TO_RES_MEETING" => "/citto/services/?page=meeting&meeting_id=#id#",
	"CALENDAR_RES_MEETING_USERGROUPS" => array("1"),
	"CALENDAR_ALLOW_VIDEO_MEETING" => "Y",
	"CALENDAR_VIDEO_MEETING_IBLOCK_ID" => "#CALENDAR_RES_VIDEO_IBLOCK_ID#",
	"CALENDAR_PATH_TO_VIDEO_MEETING_DETAIL" => "/citto/services/video/detail.php?ID=#ID#",
	"CALENDAR_PATH_TO_VIDEO_MEETING" => "/citto/services/video/",
	"CALENDAR_VIDEO_MEETING_USERGROUPS" => array("1"),
	"TASK_FORUM_ID" => "332",
	"FILES_USER_IBLOCK_TYPE"	=>	"library",
	"FILES_USER_IBLOCK_ID"	=>	"0",
	"FILES_USE_AUTH"	=>	"Y",
	"NAME_FILE_PROPERTY"	=>	"FILE",
	"FILES_UPLOAD_MAX_FILESIZE"	=>	"1024",
	"FILES_UPLOAD_MAX_FILE"	=>	"4",
	"FILES_USE_COMMENTS" => "Y",
	"FILES_FORUM_ID" => "331",
	"PHOTO_USER_IBLOCK_TYPE"	=>	"photos",
	"PHOTO_USER_IBLOCK_ID"	=>	"16",
	"PHOTO_UPLOAD_MAX_FILESIZE"	=>	"64",
	"PHOTO_UPLOAD_MAX_FILE"	=>	"4",
	"PHOTO_ORIGINAL_SIZE" => "1280",
	"PHOTO_UPLOADER_TYPE" => "form",
	"PHOTO_USE_RATING"	=>	"Y",
	"PHOTO_DISPLAY_AS_RATING" => "vote_avg",
	"PHOTO_USE_COMMENTS" => "Y",
	"PHOTO_FORUM_ID" => "2",
	"PHOTO_USE_CAPTCHA" => "N",
	"PHOTO_GALLERY_AVATAR_SIZE" => "50",
	"PHOTO_ALBUM_PHOTO_THUMBS_SIZE" => "150",
	"PHOTO_ALBUM_PHOTO_SIZE" => "150",
	"PHOTO_THUMBS_SIZE" => "250",
	"PHOTO_PREVIEW_SIZE" => "700",
	"PHOTO_JPEG_QUALITY1" => "95",
	"PHOTO_JPEG_QUALITY2" => "95",
	"PHOTO_JPEG_QUALITY" => "90",
	"SEF_URL_TEMPLATES"	=>	array(
		"index"	=>	"index.php",
		"user"	=>	"user/#user_id#/",
		"user_friends"	=>	"user/#user_id#/friends/",
		"user_friends_add"	=>	"user/#user_id#/friends/add/",
		"user_friends_delete"	=>	"user/#user_id#/friends/delete/",
		"user_groups"	=>	"user/#user_id#/groups/",
		"user_groups_add"	=>	"user/#user_id#/groups/add/",
		"group_create"	=>	"user/#user_id#/groups/create/",
		"user_profile_edit"	=>	"user/#user_id#/edit/",
		"user_settings_edit"	=>	"user/#user_id#/settings/",
		"user_features"	=>	"user/#user_id#/features/",
		"group_request_group_search"	=>	"group/#user_id#/group_search/",
		"group_request_user"	=>	"group/#group_id#/user/#user_id#/request/",
		"search"	=>	"search.php",
		"message_form"	=>	"messages/form/#user_id#/",
		"message_form_mess"	=>	"messages/form/#user_id#/#message_id#/",
		"user_ban"	=>	"messages/ban/",
		"messages_chat"	=>	"messages/chat/#user_id#/",
		"messages_input"	=>	"messages/input/",
		"messages_input_user"	=>	"messages/input/#user_id#/",
		"messages_output"	=>	"messages/output/",
		"messages_output_user"	=>	"messages/output/#user_id#/",
		"messages_users"	=>	"messages/",
		"messages_users_messages"	=>	"messages/#user_id#/",
		"user_photo"	=>	"user/#user_id#/photo/",
		"user_calendar"	=>	"user/#user_id#/calendar/",
		"user_files"	=>	"user/#user_id#/files/lib/#path#",
		"user_blog"	=>	"user/#user_id#/blog/",
		"user_blog_post_edit"	=>	"user/#user_id#/blog/edit/#post_id#/",
		"user_blog_rss"	=>	"user/#user_id#/blog/rss/#type#/",
		"user_blog_draft"	=>	"user/#user_id#/blog/draft/",
		"user_blog_post"	=>	"user/#user_id#/blog/#post_id#/",
		"user_forum"	=>	"user/#user_id#/forum/",
		"user_forum_topic_edit"	=>	"user/#user_id#/forum/edit/#topic_id#/",
		"user_forum_topic"	=>	"user/#user_id#/forum/#topic_id#/",
		"user_tasks" => "user/#user_id#/tasks/",
		"user_tasks_task" => "user/#user_id#/tasks/task/#action#/#task_id#/",
		"user_tasks_view" => "user/#user_id#/tasks/view/#action#/#view_id#/",
	),
	"LOG_NEW_TEMPLATE" => "Y"
	)
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>