<<<<<<< HEAD
<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
global $USER;
if($_GET['PAGE_NAME'] && $_GET['PAGE_NAME'] == "task_edit"){
    $ID = isset($_GET['ID'])?$_GET['ID']:$_GET['amp;ID'];
    $ID = intVal($ID);
    LocalRedirect("/company/personal/bizproc/".$ID."/?back_url=/company/personal/user/".$USER->GetId()."/");
}
if(isset($_POST['DateOfBirthHide']) && $USER->IsAuthorized() && $arParams['ID'] == $USER->GetID()){
    $cuser = new CUser;
    $cuser->Update($USER->GetID(), [
        'UF_DATEOFBIRTHHIDE'    => $_POST['DateOfBirthHide'] == 1?1:0,
        'PERSONAL_BIRTHDAY'     => $_POST['DateOfBirthHide'] == 0?$arResult['PERSONAL_DATA_DateOfBirth']:"",
        'UF_PERSONAL_BIRTHDAY'  => $arResult['PERSONAL_DATA_DateOfBirth']
    ]);
    
    $GLOBALS['APPLICATION']->RestartBuffer();
    die;
=======
<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
global $USER;
if($_GET['PAGE_NAME'] && $_GET['PAGE_NAME'] == "task_edit"){
    $ID = isset($_GET['ID'])?$_GET['ID']:$_GET['amp;ID'];
    $ID = intVal($ID);
    LocalRedirect("/company/personal/bizproc/".$ID."/?back_url=/company/personal/user/".$USER->GetId()."/");
}
if(isset($_POST['DateOfBirthHide']) && $USER->IsAuthorized() && $arParams['ID'] == $USER->GetID()){
    $cuser = new CUser;
    $cuser->Update($USER->GetID(), [
        'UF_DATEOFBIRTHHIDE'    => $_POST['DateOfBirthHide'] == 1?1:0,
        'PERSONAL_BIRTHDAY'     => $_POST['DateOfBirthHide'] == 0?$arResult['PERSONAL_DATA_DateOfBirth']:"",
        'UF_PERSONAL_BIRTHDAY'  => $arResult['PERSONAL_DATA_DateOfBirth']
    ]);
    
    $GLOBALS['APPLICATION']->RestartBuffer();
    die;
>>>>>>> e0a0eba79 (init)
}