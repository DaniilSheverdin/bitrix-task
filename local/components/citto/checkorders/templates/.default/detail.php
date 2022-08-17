<?php

use Bitrix\Intranet\Integration\Templates\Bitrix24\ThemePicker;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if (isset($_REQUEST['IFRAME']) && $_REQUEST['IFRAME'] == 'Y') {
    global $APPLICATION;
    $APPLICATION->RestartBuffer();
    CJSCore::Init("sidepanel");

    ThemePicker::getInstance()->showHeadAssets();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <?
        $APPLICATION->ShowHead();
        ?>
    </head>
    <body class="template-bitrix24 p-3 bitrix24-<?=ThemePicker::getInstance()->getCurrentBaseThemeId()?>-theme">
    <?
}

if ($arResult['ACCES'] != 'Y') {
    ShowError('Доступ запрещен!');
    return;
}

$arDetail = $arResult['DETAIL_DATA'];
$arElement = $arDetail['ELEMENT'];
$arComments = $arDetail['COMMENTS'];
$arPerm = $arResult['PERMISSIONS'];

if (empty($arElement)) {
    ShowError('Поручение не найдено');
    return;
}

$backUrl = rawurlencode('/control-orders/');
if (isset($_REQUEST['back_url']) && !empty($_REQUEST['back_url'])) {
    $backUrl = $_REQUEST['back_url'];
}

$this->SetViewTarget('inside_pagetitle', 100);
?>
<div class="pagetitle-container pagetitle-align-right-container">
<?php
if (
    $arPerm['controler'] ||
    $arPerm['protocol'] ||
    $GLOBALS['USER']->IsAdmin()
) {
    ?>
    <a class="ui-btn ui-btn-primary ui-btn-icon-add" href="/control-orders/?edit=0&template=<?=$_REQUEST['detail']?>&back_url=<?=rawurlencode($APPLICATION->GetCurPageParam())?>" title="Добавить поручение этого же протокола"></a>
    <?
}

if (
    ($arPerm['kurator'] && in_array($arElement['PROPERTY_POST_VALUE'], [1112, $USER->GetID()])) ||
    ($arPerm['controler'] && $arElement['PROPERTY_CONTROLER_VALUE'] == $USER->GetID()) ||
    ($arPerm['controler'] && $arElement['PROPERTY_CONTROLER_VALUE'] == $arPerm['controler_head']) ||
    $GLOBALS['USER']->IsAdmin()
) {
    ?><a class="ui-btn ui-btn-light-border ui-btn-icon-download js-save-to-pdf-single mr-2" data-id="<?=$arElement["ID"]?>" title="Выгрузить отчет"></a>
    <a class="ui-btn ui-btn-primary ui-btn-icon-edit mr-2" href="/control-orders/?edit=<?=$_REQUEST['detail']?>&back_url=<?=rawurlencode($APPLICATION->GetCurPageParam())?>">Изменить</a>
    <?
    if (
        $arPerm['controler'] ||
        $arPerm['main_controler'] ||
        $GLOBALS['USER']->IsAdmin()
    ) {
        ?>
        <a class="ui-btn ui-btn-danger ui-btn-icon-remove mr-2 js-delete-order" href="javascript:void(0);" data-href="/control-orders/?detail=<?=$_REQUEST['detail']?>&action=delete&from=list&back_url=<?=$backUrl?>">Удалить</a>
        <?
    }
}
?>
<a class="ui-btn ui-btn-light-border ui-btn-icon-back" href="<?=str_replace('|', '&', rawurldecode($backUrl)) ?>">Возврат к списку</a>
</div>
<?php
$this->EndViewTarget();

$arViews = [
    'summary' => 'Общая',
];

if (!empty($arResult['DETAIL_DATA']['HISTORY'])) {
    $arViews['history'] = 'История';
}

if (!empty($arComments['OTCHET_ISPOLNITEL'])) {
    if (count($arDetail['POSITION_DATA']) > 0) {
        $arViews['otchet_ispolnitel'] = 'Позиция';
    } else {
        $arViews['otchet_ispolnitel'] = 'Отчет исполнителя';
    }
}

if (!empty($arComments['OTCHET_ACCOMPLIENCE'])) {
    $arViews['otchet_accomplience'] = 'Отчет соисполнителя';
}

if (!empty($arComments['OTCHET_CONTROLER'])) {
    $arViews['otchet_controler'] = 'Отчет контролера';
}

if ($arPerm['controler'] || $arPerm['kurator'] || $GLOBALS['USER']->IsAdmin()) {
    $arViews['zametki_controler'] = 'Заметки контролера';
    $arViews['zametki_kurator']   = 'Заметки куратора';
}

$arViews['svyazi'] = 'Связи';

$sView = 'summary';
if ($_REQUEST['view']) {
    $sView = $_REQUEST['view'];
}

?>
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title"><b>
            <?
            if (count($arDetail['POSITION_DATA']) > 0) {
                echo '[Позиция]';
            }
            ?>
            <?=$arElement['NAME']?> № <?=$arElement['PROPERTY_NUMBER_VALUE']?> от <?=$arElement['PROPERTY_DATE_CREATE_VALUE']?>
        </b></h3>
    </div>

    <div class="box-body box-profile">
        <div id="view-switcher-container" class="detail-page calendar-view-switcher">
            <div class="view-switcher-list">
                <?foreach ($arViews as $key => $value) {?>
                    <a href="/control-orders/?detail=<?=$_REQUEST['detail']?><?=($key != 'summary') ? '&view=' . $key : '' ?>&back_url=<?=$backUrl?>" <?=($sView == $key) ? 'class="active"' : '' ?>>
                        <?=$value?>
                    </a>
                <?}?>
            </div>
        </div>
    </div>
</div>
<?

switch ($sView) {
    case 'svyazi':
        switch ($_REQUEST['subaction']) {
            case 'add':
                require('detail_svyazi_add.php');
                break;
            default:
                require('detail_svyazi.php');
                break;
        }
        break;
    case 'history':
        require('detail_history.php');
        break;
    case 'otchet_ispolnitel':
        require('detail_otchet_ispolnitel_list.php');
        break;
    case 'otchet_accomplience':
        require('detail_otchet_accomplience.php');
        break;
    case 'otchet_controler':
        require('detail_otchet_controler.php');
        break;
    case 'zametki_controler':
        require('detail_zametki_controler.php');
        break;
    case 'zametki_kurator':
        require('detail_zametki_kurator_new.php');
        break;
    default:
        require('detail_main.php');
        break;
}

if (isset($_REQUEST['IFRAME']) && $_REQUEST['IFRAME'] == 'Y') {
    ?>
    </body>
    </html>
    <?
    require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_after.php');
    exit;
}
