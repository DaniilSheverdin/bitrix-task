<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

use Bitrix\Main\Loader;

if (!CModule::IncludeModule("iblock") || !Loader::includeModule("iblock"))
	return;

$arIBlockType = CIBlockParameters::GetIBlockTypes();

$arIBlock = array();
$rsIBlock = CIBlock::GetList(array("SORT" => "ASC"), array("TYPE" => $arCurrentValues["IBLOCK_TYPE"], "ACTIVE" => "Y"));
while ($arr = $rsIBlock->Fetch()) 
{
	$arIBlock[$arr["ID"]] = "[" . $arr["ID"] . "] " . $arr["NAME"];
}

$arSorts = array("ASC" => GetMessage("T_IBLOCK_DESC_ASC"), "DESC" => GetMessage("T_IBLOCK_DESC_DESC"));
$arSortFields = array(
	"ID" => GetMessage("T_IBLOCK_DESC_FID"),
	"NAME" => GetMessage("T_IBLOCK_DESC_FNAME"),
	"ACTIVE_FROM" => GetMessage("T_IBLOCK_DESC_FACT"),
	"SORT" => GetMessage("T_IBLOCK_DESC_FSORT"),
	"TIMESTAMP_X" => GetMessage("T_IBLOCK_DESC_FTSAMP")
);

$arProperty_LNS = array();
$rsProp = CIBlockProperty::GetList(array("sort" => "asc", "name" => "asc"), array("ACTIVE" => "Y", "IBLOCK_ID" => $arCurrentValues["IBLOCK_ID"]));
while ($arr = $rsProp->Fetch()) 
{
	$arProperty[$arr["CODE"]] = "[" . $arr["CODE"] . "] " . $arr["NAME"];
	if (in_array($arr["PROPERTY_TYPE"], array("L", "N", "S", "E"))) {
		$arProperty_LNS[$arr["CODE"]] = "[" . $arr["CODE"] . "] " . $arr["NAME"];
	}
}
$arProperty_LNSF = array(
	"NAME" => GetMessage("IBLOCK_ADD_NAME"),
	"IBLOCK_SECTION" => GetMessage("IBLOCK_ADD_IBLOCK_SECTION"),
	"PREVIEW_TEXT" => GetMessage("IBLOCK_ADD_PREVIEW_TEXT"),
	"DETAIL_TEXT" => GetMessage("IBLOCK_ADD_DETAIL_TEXT"),
	"DETAIL_PICTURE" => GetMessage("IBLOCK_ADD_DETAIL_PICTURE"),
);

$rsProp = CIBlockProperty::GetList(array("sort" => "asc", "name" => "asc"), array("ACTIVE" => "Y", "IBLOCK_ID" => $arCurrentValues["IBLOCK_ID"]));
while ($arr = $rsProp->Fetch()) 
{
	$arProperty[$arr["ID"]] = "[" . $arr["CODE"] . "] " . $arr["NAME"];
	if (in_array($arr["PROPERTY_TYPE"], array("L", "N", "S", "F"))) {
		$arProperty_LNSF[$arr["ID"]] = "[" . $arr["CODE"] . "] " . $arr["NAME"];
	}
}

$arVirtualProperties = $arProperty_LNSF;
if ($arCurrentValues["IBLOCK_ID"] > 0) 
{
	$arIBlock = CIBlock::GetArrayByID($arCurrentValues["IBLOCK_ID"]);

	$bWorkflowIncluded = ($arIBlock["WORKFLOW"] == "Y") && Loader::includeModule("workflow");
	$bBizproc = ($arIBlock["BIZPROC"] == "Y") && Loader::includeModule("bizproc");
}
else 
{
	$bWorkflowIncluded = Loader::includeModule("workflow");
	$bBizproc = false;
}
$arUGroupsEx = array();
$dbUGroups = CGroup::GetList();
while ($arUGroups = $dbUGroups->Fetch()) 
{
	$arUGroupsEx[$arUGroups["ID"]] = $arUGroups["NAME"];
}

$arGroups = array();
$rsGroups = CGroup::GetList("c_sort", "asc", array("ACTIVE" => "Y"));
while ($arGroup = $rsGroups->Fetch()) 
{
	$arGroups[$arGroup["ID"]] = $arGroup["NAME"];
}

if ($bWorkflowIncluded) 
{
	$rsWFStatus = CWorkflowStatus::GetList("c_sort", "asc", array("ACTIVE" => "Y"));
	$arWFStatus = array();
	while ($arWFS = $rsWFStatus->Fetch()) {
		$arWFStatus[$arWFS["ID"]] = $arWFS["TITLE"];
	}
}
else 
{
	$arActive = array("ANY" => GetMessage("IBLOCK_STATUS_ANY"), "INACTIVE" => GetMessage("IBLOCK_STATUS_INCATIVE"));
	$arActiveNew = array("N" => GetMessage("IBLOCK_ALLOW_N"), "NEW" => GetMessage("IBLOCK_ACTIVE_NEW_NEW"), "ANY" => GetMessage("IBLOCK_ACTIVE_NEW_ANY"));
}

$arAllowEdit = array("CREATED_BY" => GetMessage("IBLOCK_CREATED_BY"), "PROPERTY_ID" => GetMessage("IBLOCK_PROPERTY_ID"));

$arComponentParameters = array(
	"GROUPS" => array(
		"CATEGORY_SETTINGS" => array(
			"SORT" => 130,
			"NAME" => GetMessage("T_IBLOCK_DESC_CATEGORY_SETTINGS"),
		),
		"LIST_SETTINGS" => array(
			"NAME" => GetMessage("CN_P_LIST_SETTINGS"),
		),
		"DETAIL_SETTINGS" => array(
			"NAME" => GetMessage("CN_P_DETAIL_SETTINGS"),
		),
		"DETAIL_PAGER_SETTINGS" => array(
			"NAME" => GetMessage("CN_P_DETAIL_PAGER_SETTINGS"),
		),
		"PARAMS" => array(
			"NAME" => "Параметры",
			"SORT" => "200"
		),
		"ACCESS" => array(
			"NAME" => "Доступ",
			"SORT" => "400",
		),
		"FIELDS" => array(
			"NAME" => "Поля",
			"SORT" => "300",
		),
		"TITLES" => array(
			"NAME" => "Заголовки",
			"SORT" => "1000",
		),
	),
	"PARAMETERS" => array(
		"VARIABLE_ALIASES" => array(
			"SECTION_ID" => array("NAME" => GetMessage("BN_P_SECTION_ID_DESC")),
			"ELEMENT_ID" => array("NAME" => GetMessage("NEWS_ELEMENT_ID_DESC")),
		),
		"SEF_MODE" => array(
			"news" => array(
				"NAME" => GetMessage("T_IBLOCK_SEF_PAGE_NEWS"),
				"DEFAULT" => "",
				"VARIABLES" => array(),
			),
			"section" => array(
				"NAME" => GetMessage("T_IBLOCK_SEF_PAGE_NEWS_SECTION"),
				"DEFAULT" => "",
				"VARIABLES" => array("SECTION_ID"),
			),
			"detail" => array(
				"NAME" => GetMessage("T_IBLOCK_SEF_PAGE_NEWS_DETAIL"),
				"DEFAULT" => "#ELEMENT_ID#/",
				"VARIABLES" => array("ELEMENT_ID", "SECTION_ID"),
			),
		),
		"AJAX_MODE" => array(),
		"NAME_OF_SECTION" => array(
			"PARENT" => "BASE", "NAME" => "Заголовок поля формы добавления", "TYPE" => "STRING", "MULTIPLE" => "N", "DEFAULT" => "Имя категории", ),
		"IBLOCK_TYPE" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("BN_P_IBLOCK_TYPE"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlockType,
			"REFRESH" => "Y",
		),
		"IBLOCK_ID" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("BN_P_IBLOCK"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlock,
			"REFRESH" => "Y",
			"ADDITIONAL_VALUES" => "Y",
		),
		"NEWS_COUNT" => array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("T_IBLOCK_DESC_LIST_CONT"),
			"TYPE" => "STRING",
			"DEFAULT" => "20",
		),
		"SORT_BY1" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("T_IBLOCK_DESC_IBORD1"),
			"TYPE" => "LIST",
			"DEFAULT" => "ACTIVE_FROM",
			"VALUES" => $arSortFields,
			"ADDITIONAL_VALUES" => "Y",
		),
		"SORT_ORDER1" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("T_IBLOCK_DESC_IBBY1"),
			"TYPE" => "LIST",
			"DEFAULT" => "DESC",
			"VALUES" => $arSorts,
			"ADDITIONAL_VALUES" => "Y",
		),
		"SORT_BY2" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("T_IBLOCK_DESC_IBORD2"),
			"TYPE" => "LIST",
			"DEFAULT" => "SORT",
			"VALUES" => $arSortFields,
			"ADDITIONAL_VALUES" => "Y",
		),

		"SORT_ORDER2" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("T_IBLOCK_DESC_IBBY2"),
			"TYPE" => "LIST",
			"DEFAULT" => "ASC",
			"VALUES" => $arSorts,
			"ADDITIONAL_VALUES" => "Y",
		),
		"CHECK_DATES" => array(
			"PARENT" => "DATA_SOURCE",
			"NAME" => GetMessage("T_IBLOCK_DESC_CHECK_DATES"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"PREVIEW_TRUNCATE_LEN" => array(
			"PARENT" => "LIST_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_PREVIEW_TRUNCATE_LEN"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
		"LIST_ACTIVE_DATE_FORMAT" => CIBlockParameters::GetDateFormat(GetMessage("T_IBLOCK_DESC_ACTIVE_DATE_FORMAT"), "LIST_SETTINGS"),
		"LIST_FIELD_CODE" => CIBlockParameters::GetFieldCode(GetMessage("IBLOCK_FIELD"), "LIST_SETTINGS"),
		"LIST_PROPERTY_CODE" => array(
			"PARENT" => "LIST_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_PROPERTY"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => $arProperty_LNS,
			"ADDITIONAL_VALUES" => "Y",
		),
		"PROPERTY_CODES" => array(
			"PARENT" => "FIELDS",
			"NAME" => GetMessage("IBLOCK_PROPERTY"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => $arProperty_LNSF,
		),
		"PROPERTY_CODES_REQUIRED" => array(
			"PARENT" => "FIELDS",
			"NAME" => GetMessage("IBLOCK_PROPERTY_REQUIRED"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"ADDITIONAL_VALUES" => "N",
			"VALUES" => $arProperty_LNSF,
		),
		"HIDE_LINK_WHEN_NO_DETAIL" => array(
			"PARENT" => "LIST_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_HIDE_LINK_WHEN_NO_DETAIL"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"DISPLAY_NAME" => array(
			"PARENT" => "DETAIL_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_NEWS_NAME"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"DETAIL_ACTIVE_DATE_FORMAT" => CIBlockParameters::GetDateFormat(GetMessage("T_IBLOCK_DESC_ACTIVE_DATE_FORMAT"), "DETAIL_SETTINGS"),
		"DETAIL_FIELD_CODE" => CIBlockParameters::GetFieldCode(GetMessage("IBLOCK_FIELD"), "DETAIL_SETTINGS"),
		"DETAIL_PROPERTY_CODE" => array(
			"PARENT" => "DETAIL_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_PROPERTY"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"VALUES" => $arProperty_LNS,
			"ADDITIONAL_VALUES" => "Y",
		),
		"DETAIL_DISPLAY_TOP_PAGER" => array(
			"PARENT" => "DETAIL_PAGER_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_TOP_PAGER"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"DETAIL_DISPLAY_BOTTOM_PAGER" => array(
			"PARENT" => "DETAIL_PAGER_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_BOTTOM_PAGER"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"DETAIL_PAGER_TITLE" => array(
			"PARENT" => "DETAIL_PAGER_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_PAGER_TITLE"),
			"TYPE" => "STRING",
			"DEFAULT" => GetMessage("T_IBLOCK_DESC_PAGER_TITLE_PAGE"),
		),
		"DETAIL_PAGER_TEMPLATE" => array(
			"PARENT" => "DETAIL_PAGER_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_PAGER_TEMPLATE"),
			"TYPE" => "STRING",
			"DEFAULT" => "",
		),
		"DETAIL_PAGER_SHOW_ALL" => array(
			"PARENT" => "DETAIL_PAGER_SETTINGS",
			"NAME" => GetMessage("CP_BN_DETAIL_PAGER_SHOW_ALL"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"SET_LAST_MODIFIED" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("CP_BN_SET_LAST_MODIFIED"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"SET_TITLE" => array(),
		"INCLUDE_IBLOCK_INTO_CHAIN" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_INCLUDE_IBLOCK_INTO_CHAIN"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"ADD_SECTIONS_CHAIN" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_ADD_SECTIONS_CHAIN"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"ADD_ELEMENT_CHAIN" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_ADD_ELEMENT_CHAIN"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N"
		),
		"USE_PERMISSIONS" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_USE_PERMISSIONS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
			"REFRESH" => "Y",
		),
		"GROUP_PERMISSIONS" => array(
			"PARENT" => "ADDITIONAL_SETTINGS",
			"NAME" => GetMessage("T_IBLOCK_DESC_GROUP_PERMISSIONS"),
			"TYPE" => "LIST",
			"VALUES" => $arUGroupsEx,
			"DEFAULT" => array(1),
			"MULTIPLE" => "Y",
		),
		"CACHE_TIME" => array("DEFAULT" => 36000000),
		"CACHE_FILTER" => array(
			"PARENT" => "CACHE_SETTINGS",
			"NAME" => GetMessage("BN_P_CACHE_FILTER"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "N",
		),
		"CACHE_GROUPS" => array(
			"PARENT" => "CACHE_SETTINGS",
			"NAME" => GetMessage("CP_BN_CACHE_GROUPS"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),
		"GROUPS" => array(
			"PARENT" => "ACCESS",
			"NAME" => GetMessage("IBLOCK_GROUPS"),
			"TYPE" => "LIST",
			"MULTIPLE" => "Y",
			"ADDITIONAL_VALUES" => "N",
			"VALUES" => $arGroups,
		),
		"STATUS" => array(
			"PARENT" => "ACCESS",
			"NAME" => $bWorkflowIncluded ? GetMessage("IBLOCK_STATUS_STATUS") : GetMessage("IBLOCK_STATUS_ACTIVE"),
			"TYPE" => "LIST",
			"MULTIPLE" => $bWorkflowIncluded ? "Y" : "N",
			"VALUES" => $bWorkflowIncluded ? $arWFStatus : $arActive,
		),

		"STATUS_NEW" => array(
			"PARENT" => "ACCESS",
			"NAME" => $bWorkflowIncluded ? GetMessage("IBLOCK_STATUS_NEW") : ($bBizproc ? GetMessage("IBLOCK_BP_NEW") : GetMessage("IBLOCK_ACTIVE_NEW")),
			"TYPE" => "LIST",
			"MULTIPLE" => "N",
			"VALUES" => $bWorkflowIncluded ? $arWFStatus : $arActiveNew,
		),

		"ALLOW_EDIT" => array(
			"PARENT" => "ACCESS",
			"NAME" => GetMessage("IBLOCK_ALLOW_EDIT"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),

		"ALLOW_DELETE" => array(
			"PARENT" => "ACCESS",
			"NAME" => GetMessage("IBLOCK_ALLOW_DELETE"),
			"TYPE" => "CHECKBOX",
			"DEFAULT" => "Y",
		),

		"ELEMENT_ASSOC" => array(
			"PARENT" => "ACCESS",
			"NAME" => GetMessage("IBLOCK_ELEMENT_ASSOC"),
			"TYPE" => "LIST",
			"ADDITIONAL_VALUES" => "N",
			"VALUES" => $arAllowEdit,
			"REFRESH" => "Y",
			"DEFAULT" => "CREATED_BY",
		),
	),
);
if ($arCurrentValues["ELEMENT_ASSOC"] == "PROPERTY_ID") 
{
	$arComponentParameters["PARAMETERS"]["ELEMENT_ASSOC_PROPERTY"] = array(
		"PARENT" => "ACCESS",
		"NAME" => GetMessage("IBLOCK_ELEMENT_ASSOC_PROPERTY"),
		"TYPE" => "LIST",
		"MULTIPLE" => "N",
		"VALUES" => $arProperty,
		"ADDITIONAL_VALUES" => "Y",
	);
}
$arComponentParameters["PARAMETERS"]["MAX_USER_ENTRIES"] = array(
	"PARENT" => "ACCESS",
	"NAME" => GetMessage("IBLOCK_MAX_USER_ENTRIES"),
	"TYPE" => "TEXT",
	"DEFAULT" => "100000",
);
CIBlockParameters::AddPagerSettings(
	$arComponentParameters,
	GetMessage("T_IBLOCK_DESC_PAGER_NEWS"), //$pager_title
	true, //$bDescNumbering
	true, //$bShowAllParam
	true, //$bBaseLink
	$arCurrentValues["PAGER_BASE_LINK_ENABLE"] === "Y" //$bBaseLinkEnabled
);

CIBlockParameters::Add404Settings($arComponentParameters, $arCurrentValues);

if ($arCurrentValues["USE_FILTER"] == "Y") 
{
	$arComponentParameters["PARAMETERS"]["FILTER_NAME"] = array(
		"PARENT" => "FILTER_SETTINGS",
		"NAME" => GetMessage("T_IBLOCK_FILTER"),
		"TYPE" => "STRING",
		"DEFAULT" => "",
	);
	$arComponentParameters["PARAMETERS"]["FILTER_FIELD_CODE"] = CIBlockParameters::GetFieldCode(GetMessage("IBLOCK_FIELD"), "FILTER_SETTINGS");
	$arComponentParameters["PARAMETERS"]["FILTER_PROPERTY_CODE"] = array(
		"PARENT" => "FILTER_SETTINGS",
		"NAME" => GetMessage("T_IBLOCK_PROPERTY"),
		"TYPE" => "LIST",
		"MULTIPLE" => "Y",
		"VALUES" => $arProperty_LNS,
		"ADDITIONAL_VALUES" => "Y",
	);
}

if ($arCurrentValues["USE_PERMISSIONS"] != "Y")
	unset($arComponentParameters["PARAMETERS"]["GROUP_PERMISSIONS"]);


else 
{
	unset($arComponentParameters["PARAMETERS"]["SEF_MODE"]["rss"]);
	unset($arComponentParameters["PARAMETERS"]["SEF_MODE"]["rss_section"]);
}
$arComponentParameters["PARAMETERS"]["NAV_ON_PAGE"] = array(
	"PARENT" => "PARAMS",
	"NAME" => GetMessage("IBLOCK_NAV_ON_PAGE"),
	"TYPE" => "TEXT",
	"DEFAULT" => "10",
);
$arComponentParameters["PARAMETERS"]["USER_MESSAGE_ADD"] = array(
	"PARENT" => "PARAMS",
	"NAME" => GetMessage("IBLOCK_USER_MESSAGE_ADD"),
	"TYPE" => "TEXT",
);

$arComponentParameters["PARAMETERS"]["USER_MESSAGE_EDIT"] = array(
	"PARENT" => "PARAMS",
	"NAME" => GetMessage("IBLOCK_USER_MESSAGE_EDIT"),
	"TYPE" => "TEXT",
);

$arComponentParameters["PARAMETERS"]["DEFAULT_INPUT_SIZE"] = array(
	"PARENT" => "PARAMS",
	"NAME" => GetMessage("IBLOCK_DEFAULT_INPUT_SIZE"),
	"TYPE" => "TEXT",
	"DEFAULT" => 30,
);

$arComponentParameters["PARAMETERS"]["RESIZE_IMAGES"] = array(
	"PARENT" => "PARAMS",
	"NAME" => GetMessage("CP_BIEA_RESIZE_IMAGES"),
	"TYPE" => "CHECKBOX",
	"DEFAULT" => "Y",
);

$arComponentParameters["PARAMETERS"]["MAX_FILE_SIZE"] = array(
	"PARENT" => "ACCESS",
	"NAME" => GetMessage("IBLOCK_MAX_FILE_SIZE"),
	"TYPE" => "TEXT",
	"DEFAULT" => "0",
);

foreach ($arVirtualProperties as $key => $title) 
{
	$arComponentParameters["PARAMETERS"]["CUSTOM_TITLE_" . $key] = array(
		"PARENT" => "TITLES",
		"NAME" => $title,
		"TYPE" => "STRING",
	);
}
?>
