<?php

use Bitrix\Main\UI;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Page\Asset;
use Sprint\Migration\Helpers\IblockHelper;

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php';
require_once __DIR__ . '/.settings.php';
$APPLICATION->SetTitle('Переход на Электронные трудовые книжки (ЭТК)');
$APPLICATION->IncludeComponent(
    'citto:etk',
    '',
    [
        'DEPARTMENTS'   => $arNeed
    ]
);
require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php';
