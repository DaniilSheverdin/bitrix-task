<?php /** @noinspection PhpIncludeInspection */
/**
 * @var array $arResult
 * @var array $arParams
 */

use Bitrix\Main\Localization\Loc;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$templatePath = $this->__folder;
Bitrix\Main\Page\Asset::getInstance()->addCss($templatePath.'/css/loader.css');
Bitrix\Main\Page\Asset::getInstance()->addCss($templatePath.'/css/selectize.css');
Bitrix\Main\Page\Asset::getInstance()->addCss($templatePath.'/css/jquery.fancybox.min.css');

Bitrix\Main\Page\Asset::getInstance()->addJs($templatePath.'/js/functions.js');
Bitrix\Main\Page\Asset::getInstance()->addJs($templatePath.'/js/attrchange.js');
Bitrix\Main\Page\Asset::getInstance()->addJs($templatePath.'/js/selectize.min.js');
Bitrix\Main\Page\Asset::getInstance()->addJs($templatePath.'/js/jquery.fancybox.min.js');

?>

<div class="container">
  <div class="row">
    <div class="col-12">
      <div class="kpi_header">
        <? if ($_REQUEST['page'] == 'index' || !isset($_REQUEST['page'])): ?>
<!--        <div class="helper">-->
<!--          <div class="icon"><img src="--><?//=$templatePath?><!--/icons/user-friends-solid.svg" alt=""></div>-->
<!--          <div class="label">--><?//=$arResult['USER_HELPER']['NAME'] ? $arResult['USER_HELPER']['NAME'] : Loc::getMessage('SET_HELPER')?><!--</div>-->
<!--        </div>-->
        <?else:?>
          <div class="back">
            <div class="icon"><img src="<?=$templatePath?>/icons/arrow-left-solid.svg" alt=""></div>
            <div class="label"><a href="<?=SITE_DIR?>test-kpi"><?=Loc::getMessage('REDIRECT_MAIN')?></a></div>
          </div>
        <?endif;?>
      </div>
    </div>
  </div>

<?include($arResult['INCLUDE_FILE']);?>
</div>



