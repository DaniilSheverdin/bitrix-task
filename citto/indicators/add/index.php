<?php
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');
$APPLICATION->SetTitle('Показатели');
?>
<?$APPLICATION->IncludeComponent(
    'citto:indicators.edit',
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
