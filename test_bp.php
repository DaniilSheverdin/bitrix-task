<?
require $_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php";
CModule::IncludeModule('citto.integration');
CJSCore::Init(array("jquery",'popup','ui'));
global $APPLICATION;
$APPLICATION->SetAdditionalCSS('/bitrix/templates/.default/bootstrap.min.css');
$APPLICATION->SetAdditionalCSS('/bitrix/templates/bitrix24/css/sidebar.css');
$APPLICATION->SetAdditionalCss('/local/js/adminlte/css/AdminLTE.min.css');
$APPLICATION->SetAdditionalCss('/local/js/adminlte/css/skins/_all-skins.min.css');
?>
<form method="post" enctype="multipart/form-data">
	ID Бизнес-процесса: <input class="form-control" type="text" name="TASK_ID" value="<?=$_REQUEST['TASK_ID']?>"><br>
	ID Пользователя: <input class="form-control" type="text" name="ID_USER" value="<?=$_REQUEST['ID_USER']?>"><br>
	Название задачи: <input class="form-control" type="text" name="TASK_NAME" value="<?=$_REQUEST['TASK_NAME']?>"><br>
	Электронная почта: <input class="form-control" type="text" name="EMAIL_TO" value="<?=$_REQUEST['EMAIL_TO']?>"><br>
	Файл: <input class="form-control" type="file" name="FILE" value=""><br>
	<button class="ui-btn" type="submit" name="action" value="active">Получить</button>
</form>
<?
if($_REQUEST['action']!=''){
	$uploaddir=$_SERVER['DOCUMENT_ROOT'].'/upload/';
	$uploadfile = $uploaddir . basename($_FILES['FILE']['name']);
	if (move_uploaded_file($_FILES['FILE']['tmp_name'], $uploadfile)) {
		$arFields['TASK_NAME']=$_REQUEST['TASK_NAME'];
		$arFields['EMAIL_TO']=$_REQUEST['EMAIL_TO'];
		$arFields['CONTROLS']="<a href='https://corp.tularegion.ru/bp_approve_task.php?token=".$GLOBALS['bp_enccc'](implode('_',array($_REQUEST['TASK_ID'],$_REQUEST['ID_USER'])))."&approve'>Согласовать</a> | ";
		$arFields['CONTROLS'].="<a href='https://corp.tularegion.ru/bp_approve_task.php?token=".$GLOBALS['bp_enccc'](implode('_',array($_REQUEST['TASK_ID'],$_REQUEST['ID_USER'])))."&disapprove'>Отклонить</a>";

        $arFields['CONTROLS'] .= "<hr /><p>Если Вы находитесь внутри сети ПТО (на рабочем месте):<br />";
        $arFields['CONTROLS'] .= "<a href='https://corp.tularegion.local/bp_approve_task.php?token=".$GLOBALS['bp_enccc'](implode('_',array($_REQUEST['TASK_ID'],$_REQUEST['ID_USER'])))."&approve'>Согласовать</a> | ";
        $arFields['CONTROLS'] .= "<a href='https://corp.tularegion.local/bp_approve_task.php?token=".$GLOBALS['bp_enccc'](implode('_',array($_REQUEST['TASK_ID'],$_REQUEST['ID_USER'])))."&disapprove'>Отклонить</a></p>";


        CEvent::Send('BP_APPROVE_TASK',array('s1'),$arFields,'N','',array($uploadfile));
	}
	
}