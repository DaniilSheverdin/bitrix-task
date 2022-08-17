<?php /** @noinspection PhpIncludeInspection */
/**
 * @var array $arResult
 * @var array $arParams
 */

use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$templatePath = $this->__folder;

CJSCore::Init(array("jquery","date"));

Bitrix\Main\Page\Asset::getInstance()->addJs($templatePath.'/js/xls-export.es5.js');
Bitrix\Main\Page\Asset::getInstance()->addJs($templatePath.'/js/jquery.inputmask.min.js');
Bitrix\Main\Page\Asset::getInstance()->addJs($templatePath.'/js/functions.js');

?>

<div class="container">
  <?include($arResult['INCLUDE_FILE']);?>
</div>



