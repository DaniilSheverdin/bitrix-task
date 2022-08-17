<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php"); ?>
<?php
/**
 * @global $APPLICATION
 */
?>
<? $APPLICATION->SetTitle("Оповещение пользователю отправлено"); ?>
<? require_once $_SERVER['DOCUMENT_ROOT'] . '/local/vendor/autoload.php'; ?>

<p>Оповещение пользователю отправлено! Можно <a href="javascript: window.close();">закрыть это окно</a></p>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
