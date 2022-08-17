<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
$APPLICATION->SetTitle('Контроль поручений');
require $_SERVER['DOCUMENT_ROOT'] . '/control-orders/.settings.php';
?>
<?$APPLICATION->IncludeComponent(
    'citto:checkorders',
    '',
    [
        'IBLOCK_ID_ISPOLNITEL'      => IBLOCK_ID_ISPOLNITEL,
        'IBLOCK_ID_ORDERS'          => IBLOCK_ID_ORDERS,
        'IBLOCK_ID_ORDERS_COMMENT'  => IBLOCK_ID_ORDERS_COMMENT,
        'IBLOCK_ID_ORDERS_OBJECT'   => IBLOCK_ID_ORDERS_OBJECT,
        'IBLOCK_ID_ORDERS_THEME'    => IBLOCK_ID_ORDERS_THEME,
    ]
);?>
<?
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
