<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die(); ?>
<? $templateFolder = &$this->GetFolder();
Bitrix\Main\Page\Asset::getInstance()->addJs($templateFolder.'/lib/xls-export.es5.js');
Bitrix\Main\Page\Asset::getInstance()->addJs($templateFolder.'/lib/jquery.inputmask.bundle.js');
Bitrix\Main\Page\Asset::getInstance()->addJs($templateFolder.'/lib/jquery.inputmask.bundle.js');
Bitrix\Main\Page\Asset::getInstance()->addCss($templateFolder.'/lib/suggestions.min.css');
Bitrix\Main\Page\Asset::getInstance()->addJs($templateFolder.'/lib/jquery.suggestions.min.js');

?>

<?if ($arResult['CUR_PAGE'] !=  'index'):?>
<?$this->SetViewTarget('inside_pagetitle', 100);?>
<div class="pagetitle-container pagetitle-align-right-container">
    <a class="ui-btn ui-btn-light-border ui-btn-icon-back mr-2"
       href="<?echo $APPLICATION->GetCurPageParam("", array('PAGE', 'TYPE', 'quarantine', 'q'));?>">
        <?=GetMessage('BUTTON_MAIN_PAGE')?>
    </a>
</div>
<?$this->EndViewTarget();?>
<?endif;?>

<? include($arResult['INCLUDE_FILE']); ?>







