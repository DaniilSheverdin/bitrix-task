<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
global $APPLICATION, $USER;
CMain::PrologActions();
define("BX_AUTH_FORM", true);
header('X-Bitrix-Ajax-Status: Authorize');
CHTTP::SetStatus("403 Forbidden");

$APPLICATION->SetTitle("Доступ ограничен");
$isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || isset($_REQUEST['IFRAME']);
$isAdmin = substr($APPLICATION->GetCurDir(), 0, strlen(BX_ROOT."/admin/")) == BX_ROOT."/admin/" || (defined("ADMIN_SECTION") && ADMIN_SECTION===true);
?>
<?if($isAjax || $isAdmin):?>
	<!DOCTYPE html>
	<html >
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<title><?=$APPLICATION->GetTitle()?></title>
	</head>
	<body>
	<style>
		body{font-family:sans-serif;}
		.conatiner{text-align:left;}
	</style>
<?else:?>
	<? include $_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/include/prolog_after.php"; ?>
<?endif;?>

<style>
	ul{list-style:none;padding:0;}
	li{margin-left:0;}
	.conatiner{padding:15px;;}
	a.gtlocal{
		display: inline-block;
		max-width: 100%;
		border: 1px solid #1dbaee;
		border-radius: 3px;
		padding: 10px;
		color: #fff;
		background: #36b5df;
		margin-bottom: 30px;
		text-decoration:none;
	}
</style>
<div class="conatiner">
	<h2>Внимание!</h2>
	<p>К данному адресу доступ ограничен вне сети ПТО.</p>
	<a class="gtlocal" href="https://corp.tularegion.local<?=$APPLICATION->GetCurDir()?>">Перейти на данную страницу внутри сети ПТО &rarr;</a>
	<p>Список доступных разделов сайта:</p>
	<ul style="list-style:none;">
		<li><a href="/company/personal/user/<?=$USER->GetID()?>/tasks/">Задачи и Проекты</a></li>
		<?if (!CSite::InGroup([120])) : ?>
			<li><a href="/stream/">Живая лента</a></li>
			<li><a href="/workgroups/">Группы</a></li>
		<?endif;?>
	</ul>
	<p>Если Вы зашли с рабочего места, получить доступ к ресурсу Вы можете пройдя авторизацию по ссылке: <a href="https://corp.tularegion.local">https://corp.tularegion.local</a></p>
</div>

<?if($isAjax || $isAdmin):?>
	</body>
	</html>
<?else:?>
	<? include $_SERVER['DOCUMENT_ROOT']."/bitrix/footer.php"; ?>
<?endif;?>
<?
die;