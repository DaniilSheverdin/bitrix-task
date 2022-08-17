<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/services/idea/index.php");
$APPLICATION->SetTitle(GetMessage("SERVICES_TITLE"));
?><?$APPLICATION->IncludeComponent(
	"bitrix:idea",
	"",
	Array(
		"ALLOW_POST_CODE" => "Y",
		"BLOG_PROPERTY" => array(),
		"BLOG_PROPERTY_LIST" => array(),
		"BLOG_URL" => "idea_nh",
		"CACHE_TIME" => "3600",
		"CACHE_TIME_LONG" => "604800",
		"CACHE_TYPE" => "A",
		"COMMENTS_COUNT" => "10",
		"COMMENT_ALLOW_VIDEO" => "N",
		"COMMENT_EDITOR_CODE_DEFAULT" => "N",
		"COMMENT_EDITOR_DEFAULT_HEIGHT" => "200",
		"COMMENT_EDITOR_RESIZABLE" => "Y",
		"COMPOSITE_FRAME_MODE" => "A",
		"COMPOSITE_FRAME_TYPE" => "AUTO",
		"DATE_TIME_FORMAT" => "d.m.Y H:i:s",
		"DISABLE_EMAIL" => "N",
		"DISABLE_RSS" => "N",
		"DISABLE_SONET_LOG" => "N",
		"EDITOR_CODE_DEFAULT" => "N",
		"EDITOR_DEFAULT_HEIGHT" => "300",
		"EDITOR_RESIZABLE" => "Y",
		"IBLOCK_CATEGORIES" => "1",
		"IBLOCK_CATOGORIES" => "18",
		"IMAGE_MAX_HEIGHT" => "770",
		"IMAGE_MAX_WIDTH" => "770",
		"MESSAGE_COUNT" => "10",
		"NAME_TEMPLATE" => "",
		"NAV_TEMPLATE" => "",
		"NO_URL_IN_COMMENTS" => "",
		"PATH_TO_SMILE" => "/bitrix/images/blog/smile/",
		"POST_BIND_STATUS_DEFAULT" => "26",
		"POST_BIND_USER" => array("1"),
		"POST_PROPERTY" => array(),
		"POST_PROPERTY_LIST" => array(),
		"RATING_TEMPLATE" => "standart",
		"SEF_FOLDER" => "/citto/services/idea/",
		"SEF_MODE" => "Y",
		"SEF_URL_TEMPLATES" => Array("category_1"=>"category/#category_1#/","category_1_status"=>"category/#category_1#/status/#status_code#/","category_2"=>"category/#category_1#/#category_2#/","category_2_status"=>"category/#category_1#/#category_2#/status/#status_code#/","index"=>"","post"=>"#post_id#/","post_edit"=>"edit/#post_id#/","post_rss"=>"#blog#/rss/#type#/#post_id#/","rss"=>"#blog#/rss/#type#","rss_all"=>"rss/#type#/#group_id#/","rss_category"=>"rss/#type#/category/#category#/","rss_category_status"=>"rss/#type#/category/#category#/status/#status_code#/","rss_status"=>"rss/#type#/status/#status_code#/","rss_user_ideas"=>"rss/#type#/user/#user_id#/idea/","rss_user_ideas_status"=>"rss/#type#/user/#user_id#/idea/status/#status_code#/","status_0"=>"status/#status_code#/","user"=>"user/#user_id#/","user_ideas"=>"user_idea/#user_id#/","user_ideas_status"=>"user_idea/#user_id#/status/#status_code#/","user_subscribe"=>"user/#user_id#/subscribe/"),
		"SET_NAV_CHAIN" => "Y",
		"SET_TITLE" => "Y",
		"SHOW_LOGIN" => "Y",
		"SHOW_RATING" => "Y",
		"SHOW_SPAM" => "N",
		"SMILES_COUNT" => "2",
		"TAGS_COUNT" => "0",
		"USE_ASC_PAGING" => "N",
		"USE_GOOGLE_CODE" => "Y",
		"USE_SHARE" => "N"
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>