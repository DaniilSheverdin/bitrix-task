<?php
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
$APPLICATION->SetTitle('Управление протокола');
require $_SERVER['DOCUMENT_ROOT'] . '/control-orders/.settings.php';
$APPLICATION->IncludeComponent(
    'citto:checkorders.protocol',
    '',
    [
        'IBLOCK_ID_ISPOLNITEL'      => IBLOCK_ID_ISPOLNITEL,
        'IBLOCK_ID_ORDERS'          => IBLOCK_ID_ORDERS,
        'IBLOCK_ID_ORDERS_COMMENT'  => IBLOCK_ID_ORDERS_COMMENT,
        'IBLOCK_ID_ORDERS_OBJECT'   => IBLOCK_ID_ORDERS_OBJECT,
        'IBLOCK_ID_ORDERS_THEME'    => IBLOCK_ID_ORDERS_THEME,
        'IBLOCK_ID_PROTOCOLS'       => IBLOCK_ID_PROTOCOLS,
    ]
);

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
