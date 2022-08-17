<?php
//define('NO_MB_CHECK',true);
//define('NOT_CHECK_PERMISSIONS', true);
var_dump(extension_loaded('ldap')); exit;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Верификация цифровой подписи");

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

//$APPLICATION->SetTitle("Верификация цифровой подписи");
$sign = 'test';
$url = "http://172.21.254.50/uverify.php";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, ['sign' => $sign]);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
$returned = curl_exec($ch);

print_r($returned);
?>

<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
?>