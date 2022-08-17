<?php

use Bitrix\Main\Loader;
use Sprint\Migration\Helpers\IblockHelper;
use Sprint\Migration\Helpers\HlblockHelper;

Loader::includeModule('sprint.migration');
$helper = new IblockHelper();

$objhlHelper = new HlblockHelper();

define('BP_TEMPLATE_ID', 946);

define('BP_HL_KARTRIDZY', $objhlHelper->getHlblockId('WJLRashodnyMaterial'));
define('BP_HL_PRINTERY', $objhlHelper->getHlblockId('WJLPrinters'));
define('BP_HL_PK_LINK', $objhlHelper->getHlblockId('WJLLINKS'));
