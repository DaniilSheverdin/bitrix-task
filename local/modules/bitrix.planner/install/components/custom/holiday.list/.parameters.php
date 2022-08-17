<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var array $arCurrentValues */

if(!CModule::IncludeModule("iblock"))
	return;

$arTypesEx = CIBlockParameters::GetIBlockTypes(Array("-"=>" "));

$arIBlocks=Array();
$db_iblock = CIBlock::GetList(Array("SORT"=>"ASC"), Array("SITE_ID"=>$_REQUEST["site"], "TYPE" => ($arCurrentValues["IBLOCK_TYPE"]!="-"?$arCurrentValues["IBLOCK_TYPE"]:"")));
while($arRes = $db_iblock->Fetch())
	$arIBlocks[$arRes["ID"]] = $arRes["NAME"];

$arGroup = array(0 => '['.GetMessage("BITRIX_PLANNER_NET"));
$rs = CGroup::GetList($by = 'name', $order = 'asc', $arFilter = array('ACTIVE' => 'Y'));
while($arRes = $rs->Fetch())
	$arGroup[$arRes['ID']] = $arRes['NAME'];


$arComponentParameters = array(
	"GROUPS" => array(
	),
	"PARAMETERS" => array(
		"IBLOCK_TYPE" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("BITRIX_PLANNER_TIP_INFOBLOKA"),
			"TYPE" => "LIST",
			"VALUES" => $arTypesEx,
			"DEFAULT" => "news",
			"REFRESH" => "Y",
		),
		"IBLOCK_ID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("BITRIX_PLANNER_INFOBLOK_ZAPISEY_OB"),
			"TYPE" => "LIST",
			"VALUES" => $arIBlocks,
			"DEFAULT" => '={$_REQUEST["ID"]}',
			"ADDITIONAL_VALUES" => "Y",
			"REFRESH" => "Y",
		),
		"COUNT_DAYS" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("BITRIX_PLANNER_UCITYVATQ_OSTATKI_OT"),
			"TYPE" => "CHECKBOX",
			"VALUE" => 'Y',
		),
		"MANAGER_ADD_DAYS" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("BITRIX_PLANNER_MANAGER"),
			"TYPE" => "CHECKBOX",
			"VALUE" => 'Y',
		),
		"SHOW_TIME" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("BITRIX_PLANNER_POKAZYVATQ_POLE_VVOD"),
			"TYPE" => "CHECKBOX",
			"VALUE" => 'N',
		),
		"SHOW_ALL" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("BITRIX_PLANNER_SHOW_ALL"),
			"TYPE" => "CHECKBOX",
			"VALUE" => 'N',
		),
		"HR_GROUP_ID" => Array(
			"PARENT" => "BASE",
			"NAME" => GetMessage("BITRIX_PLANNER_GRUPPA_S_POLNYM_DOST"),
			"TYPE" => "LIST",
			"VALUES" => $arGroup,
			"DEFAULT" => '',
		),
	)
);
?>
