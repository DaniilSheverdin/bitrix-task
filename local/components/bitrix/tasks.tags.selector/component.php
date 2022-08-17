<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arResult["USER_TAGS"] = array();

if (!CModule::IncludeModule("tasks"))
{
	ShowError(GetMessage("TASKS_MODULE_NOT_FOUND"));
	return;
}

$orm = \Bitrix\Main\UserTable::getList([
    'select'    => ['ID', 'UF_DEPARTMENT'],
    'filter'    => ['ID' => $USER->getId()]
]);
$arUser = $orm->fetch();
if ($arUser['UF_DEPARTMENT'][0] == 438) {
	Bitrix\Main\Loader::includeModule('intranet');
	$res = CIntranetUtils::getDepartmentColleagues($USER->getId());
	$arResult["~USER_TAGS"] = $arResult["USER_TAGS"] = array();
	while ($row = $res->Fetch()) {
		$dbRes = CTaskTags::getTagsNamesByUserId($row['ID']);
		while ($tag = $dbRes->GetNext()) {
			$arResult["USER_TAGS"][] = $tag["NAME"];
			$arResult["~USER_TAGS"][] = $tag["~NAME"];
		}
	}
} else {
	$dbRes = CTaskTags::getTagsNamesByUserId($USER->getId());
	$arResult["~USER_TAGS"] = $arResult["USER_TAGS"] = array();
	while($tag = $dbRes->GetNext())
	{
		$arResult["USER_TAGS"][] = $tag["NAME"];
		$arResult["~USER_TAGS"][] = $tag["~NAME"];
	}
}

if (isset($arParams["VALUE"]) && $arParams["VALUE"])
{
	if (!is_array($arParams["VALUE"]))
	{
		$arResult["VALUE"] = explode(",", $arParams["VALUE"]);
		$arResult["~VALUE"] = explode(",", $arParams["~VALUE"]);
	}
	else
	{
		$arResult["VALUE"] = $arParams["VALUE"];
		$arResult["~VALUE"] = $arParams["~VALUE"];
	}
}
else
{
	$arResult["VALUE"] = $arResult["~VALUE"] = array();
}

if (sizeof($arResult["VALUE"]) > 0)
{
	$arResult["VALUE"] = array_map("trim", $arResult["VALUE"]);
	$arResult["~VALUE"] = array_map("trim", $arResult["~VALUE"]);
}

$arResult["NAME"] = htmlspecialcharsbx($arParams["NAME"]);
$arResult["~NAME"] = $arParams["NAME"];

if (isset($arParams["PATH_TO_TASKS"]) && !empty($arParams["PATH_TO_TASKS"]))
{
	$arResult['PATH_TO_TASKS'] = $arParams["PATH_TO_TASKS"];
}
else
{
	$arResult['PATH_TO_TASKS'] = '/company/personal/user/'.$USER->GetID().'/tasks/';
}

$this->IncludeComponentTemplate();
