<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

global $USER, $APPLICATION;

$APPLICATION->SetTitle('Отчёты при аттестации');

\Bitrix\Main\UI\Extension::load([
    'ui.buttons',
    'ui.alerts',
    'ui.tooltip',
    'ui.hint',
    'ui.buttons.icons',
    'ui.dialogs.messagebox'
]);

CJSCore::Init(['date', 'ui']);
include $_SERVER['DOCUMENT_ROOT'] . '/local/components/citto/profile.personal/templates/.default/reference-lens.php';
?>

<script type="text/javascript" src="/bitrix/templates/.default/bootstrap.min.js"></script>
<link rel="stylesheet" type="text/css" href="/bitrix/templates/.default/bootstrap.min.css">

<div id="workarea-content" class="ui-side-panel-wrap-workarea ui-page-slider-workarea-content-padding">
    <div>
        <? foreach ($arResult['REPORT_FILES'] as $arFileSrc): ?>
            <a href="<?= $arFileSrc['SRC'] ?>" download="download"><?= $arFileSrc['FILE_NAME'] ?></a><br>
        <? endforeach; ?>
    </div>
    <link rel="stylesheet" type="text/css" href="/bitrix/templates/.default/bootstrap.min.css">
</div>
