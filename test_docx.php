<?
require $_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php";
CModule::IncludeModule('citto.integration');
CJSCore::Init(array("jquery",'popup','ui'));
global $APPLICATION;
$APPLICATION->SetAdditionalCSS('/bitrix/templates/.default/bootstrap.min.css');
$APPLICATION->SetAdditionalCSS('/bitrix/templates/bitrix24/css/sidebar.css');
$APPLICATION->SetAdditionalCss('/local/js/adminlte/css/AdminLTE.min.css');
$APPLICATION->SetAdditionalCss('/local/js/adminlte/css/skins/_all-skins.min.css')
?>
<form method="post">
	Дата прилета: <input class="form-control" type="text" name="DATE_ARRIVALS" value="<?=$_REQUEST['DATE_ARRIVALS']?>"><br>
	ФИО: <input class="form-control" type="text" name="FIO" value="<?=$_REQUEST['FIO']?>"><br>
	Адрес: <input class="form-control" type="text" name="ADDRESS_TEXT" value="<?=$_REQUEST['ADDRESS_TEXT']?>"><br>
	Год рождения: <input class="form-control" type="text" name="YEAR_BIRTHDAY" value="<?=$_REQUEST['YEAR_BIRTHDAY']?>"><br>
	ФИО представителя: <input class="form-control" type="text" name="FIO_PREDSTAVITEL" value="<?=$_REQUEST['FIO_PREDSTAVITEL']?>"><br>
	Адрес представителя: <input class="form-control" type="text" name="ADDRESS_TEXT_PREDSTAVITEL" value="<?=$_REQUEST['ADDRESS_TEXT_PREDSTAVITEL']?>"><br>
	Год рождения представителя: <input class="form-control" type="text" name="YEAR_BIRTHDAY_PREDSTAVITEL" value="<?=$_REQUEST['YEAR_BIRTHDAY_PREDSTAVITEL']?>"><br>
	Дата начала: <input class="form-control" type="text" name="DATE_START" value="<?=$_REQUEST['DATE_START']?>"><br>
	Дата конца: <input class="form-control" type="text" name="DATE_END" value="<?=$_REQUEST['DATE_END']?>"><br>
	
	
	

	<button class="ui-btn" type="submit" name="action" value="isolation">Взрослый</button>
	<button class="ui-btn" type="submit" name="action" value="isolation_child">Детский</button>
</form>
<?
if($_REQUEST['action']!=''){
	echo "<pre>";print_r($_REQUEST);echo "</pre>";
	$sFileName=time();
	\Citto\Integration\Docx::generateDocument($sFileName,$_REQUEST,$_REQUEST['action']);
	echo "<br>";
	echo "<a href='/upload/docx_generated/".$sFileName.".docx'>Скачать</a>";
}
