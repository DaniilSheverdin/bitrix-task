<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
$APPLICATION->SetTitle('Данные о местоположении');
require $_SERVER['DOCUMENT_ROOT'] . '/control-orders/.settings.php';
?>
<?$APPLICATION->IncludeComponent(
    'citto:geoposition',
    '',
    [
    	'CACHE_TYPE'	=>'N',
        'IBLOCK_ID'      => IBLOCK_ID_GEOPOSITION_DATA,
    ]
);?>
<?
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
