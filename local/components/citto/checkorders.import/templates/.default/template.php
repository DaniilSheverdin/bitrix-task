<?php

use Bitrix\Main\Page\Asset;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

// Asset::getInstance()->addJs('/bitrix/templates/.default/bootstrap.min.js');
Asset::getInstance()->addCss('/bitrix/templates/bitrix24/css/sidebar.css');
Asset::getInstance()->addCss('/local/components/citto/checkorders/templates/.default/style.css');
Asset::getInstance()->addCss('/local/js/adminlte/css/AdminLTE.min.css');
Asset::getInstance()->addCss('/local/js/adminlte/css/skins/_all-skins.min.css');

$this->SetViewTarget('inside_pagetitle', 100);
?>
<div class="pagetitle-container pagetitle-align-right-container">
    <a class="ui-btn ui-btn-light-border mr-3" href="/control-orders/">
        Возврат к списку
    </a>
</div>
<?php
$this->EndViewTarget();
if ($arResult['STEP'] == 1 && !empty($arResult['RESULT'])) {
	$arResult['STEP'] = '1_5';
}
$tplFile = __DIR__ . '/step' . $arResult['STEP'] . '.php';
if (file_exists($tplFile)) {
    include $tplFile;
} else {
    LocalRedirect('/control-orders/import/');
}