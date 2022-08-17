<?php

use Bitrix\Main\UI;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Page\Asset;
use Sprint\Migration\Helpers\IblockHelper;

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php';
require_once __DIR__ . '/.settings.php';
$APPLICATION->SetTitle('Ознакомление с экспериментом');
$APPLICATION->IncludeComponent(
    'citto:experiment',
    '',
    [
        'DEPARTMENTS'   => $arNeed
    ]
);
require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php';
