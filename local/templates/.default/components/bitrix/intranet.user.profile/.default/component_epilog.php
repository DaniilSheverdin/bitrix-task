<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
global $USER;
if($_GET['PAGE_NAME'] && $_GET['PAGE_NAME'] == "task_edit"){
    $ID = isset($_GET['ID'])?$_GET['ID']:$_GET['amp;ID'];
    $ID = intVal($ID);
    LocalRedirect("/company/personal/bizproc/".$ID."/?back_url=/company/personal/user/".$USER->GetId()."/");
}

if (isset($_POST['auth_user_id'])) {
	$iAuthId = (int)$_POST['auth_user_id'];
	$bAuth = false;
	if ($iAuthId == $USER->GetID()) {
		$bAuth = true;
	}
	if (array_key_exists($iAuthId, $arResult['USER_AUTH_SELECT'])) {
		$bAuth = true;
	}

	if ($bAuth) {
		$GLOBALS['USER']->Authorize($iAuthId);
		LocalRedirect('/company/personal/user/' . $iAuthId . '/');
		exit;
	}
}