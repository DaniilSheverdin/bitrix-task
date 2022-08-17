<?php

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php';
$APPLICATION->SetTitle('Прием заявок на финансирование');
$APPLICATION->IncludeComponent(
    'citto:edu.financing',
    '',
    []
);
require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php';
