<?php

use Bitrix\Main\Loader;
use Sprint\Migration\Helpers\HlblockHelper;
use Bitrix\Highloadblock\HighloadBlockTable as HLTable;

require $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";
require $_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php";

Loader::includeModule('highloadblock');
Loader::includeModule('sprint.migration');
$helper = new HlblockHelper();
$hlId = $helper->getHlblockId('BrowserStats');
$hlblock = HLTable::getById($hlId)->fetch();
$entity = HLTable::compileEntity($hlblock);
$entityDataClass = $entity->getDataClass();

$entityDataClass::add([
    'UF_DATE'       => date('d.m.Y H:i:s'),
    'UF_USER'       => $GLOBALS['USER']->GetID(),
    'UF_USERAGENT'  => $_REQUEST['userAgent'],
]);