<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}
if (!CModule::IncludeModule("intranet")) :
    ShowError(GetMessage("W_INTRANET_IS_NOT_INSTALLED"));
    return 0;
endif;

$arParams["PAGE_VAR"] = trim($arParams["PAGE_VAR"]);
if (mb_strlen($arParams["PAGE_VAR"]) <= 0) {
    $arParams["PAGE_VAR"] = "page";
}

$arParams["MEETING_VAR"] = trim($arParams["MEETING_VAR"]);
if (mb_strlen($arParams["MEETING_VAR"]) <= 0) {
    $arParams["MEETING_VAR"] = "meeting_id";
}

$arParams["PATH_TO_MEETING_LIST"] = trim($arParams["PATH_TO_MEETING_LIST"]);
if (mb_strlen($arParams["PATH_TO_MEETING_LIST"]) <= 0) {
    $arParams["PATH_TO_MEETING_LIST"] = HtmlSpecialCharsbx($APPLICATION->GetCurPage());
}

$arParams["PATH_TO_MEETING"] = trim($arParams["PATH_TO_MEETING"]);
if (mb_strlen($arParams["PATH_TO_MEETING"]) <= 0) {
    $arParams["PATH_TO_MEETING"] = HtmlSpecialCharsbx($APPLICATION->GetCurPage() . "?" . $arParams["PAGE_VAR"] . "=meeting&" . $arParams["MEETING_VAR"] . "=#meeting_id#");
}

$arParams["PATH_TO_MODIFY_MEETING"] = trim($arParams["PATH_TO_MODIFY_MEETING"]);
if (mb_strlen($arParams["PATH_TO_MODIFY_MEETING"]) <= 0) {
    $arParams["PATH_TO_MODIFY_MEETING"] = HtmlSpecialCharsbx($APPLICATION->GetCurPage() . "?" . $arParams["PAGE_VAR"] . "=modify_meeting&" . $arParams["MEETING_VAR"] . "=#meeting_id#");
}

$arParams["PATH_TO_RESERVE_MEETING"] = trim($arParams["PATH_TO_RESERVE_MEETING"]);
if (mb_strlen($arParams["PATH_TO_RESERVE_MEETING"]) <= 0) {
    $arParams["PATH_TO_RESERVE_MEETING"] = HtmlSpecialCharsbx($APPLICATION->GetCurPage() . "?" . $arParams["PAGE_VAR"] . "=reserve_meeting&" . $arParams["MEETING_VAR"] . "=#meeting_id#&" . $arParams["ITEM_VAR"] . "=#item_id#");
}

$arParams["PATH_TO_SEARCH"] = trim($arParams["PATH_TO_SEARCH"]);
if (mb_strlen($arParams["PATH_TO_SEARCH"]) <= 0) {
    $arParams["PATH_TO_SEARCH"] = HtmlSpecialCharsbx($APPLICATION->GetCurPage() . "?" . $arParams["PAGE_VAR"] . "=search");
}

if (!is_array($arParams["USERGROUPS_MODIFY"])) {
    if (intval($arParams["USERGROUPS_MODIFY"]) > 0) {
        $arParams["USERGROUPS_MODIFY"] = [$arParams["USERGROUPS_MODIFY"]];
    } else {
        $arParams["USERGROUPS_MODIFY"] = [];
    }
}

if (!is_array($arParams["USERGROUPS_RESERVE"])) {
    if (intval($arParams["USERGROUPS_RESERVE"]) > 0) {
        $arParams["USERGROUPS_RESERVE"] = [$arParams["USERGROUPS_RESERVE"]];
    } else {
        $arParams["USERGROUPS_RESERVE"] = [];
    }
}

$meetingId = intval($arParams["MEETING_ID"]);
if ($meetingId <= 0) {
    $meetingId = intval($_REQUEST[$arParams["MEETING_VAR"]]);
}

$arResult["Page"] = trim($arParams["PAGE_ID"]);
if (mb_strlen($arResult["Page"]) <= 0) {
    $arResult["Page"] = trim($_REQUEST[$arParams["PAGE_VAR"]]);
}

$arResult["Urls"]["MeetingList"] = $arParams["PATH_TO_MEETING_LIST"];
$arResult["Urls"]["ModifyMeeting"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MODIFY_MEETING"], ["meeting_id" => $meetingId]);
$arResult["Urls"]["CreateMeeting"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MODIFY_MEETING"], ["meeting_id" => 0]);
$arResult["Urls"]["Meeting"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_MEETING"], ["meeting_id" => $meetingId]);
$arResult["Urls"]["Meeting"] .= (strpos($arResult["Urls"]["Meeting"], "?") === false ? "?" : "&")."week_start=".urlencode($_REQUEST["week_start"]);

$arResult["Urls"]["ReserveMeeting"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_RESERVE_MEETING"], ["meeting_id" => $meetingId, "item_id" => 0]);
$arResult["Urls"]["Search"] = $arParams["PATH_TO_SEARCH"];

$arResult["Perms"]["CanModify"] = (
    $GLOBALS["USER"]->IsAuthorized()
    && ($GLOBALS["USER"]->IsAdmin()
        || count(array_intersect($GLOBALS["USER"]->GetUserGroupArray(), $arParams["USERGROUPS_MODIFY"])) > 0)
);

$arResult["Perms"]["CanReserve"] = (
    $GLOBALS["USER"]->IsAuthorized()
    && ($GLOBALS["USER"]->IsAdmin()
        || count(array_intersect($GLOBALS["USER"]->GetUserGroupArray(), $arParams["USERGROUPS_RESERVE"])) > 0)
);

$this->IncludeComponentTemplate();
