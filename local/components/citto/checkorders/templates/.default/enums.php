<?

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
global $APPLICATION;

$APPLICATION->SetTitle('Редактирование списков');

$bAccess = ($GLOBALS['USER']->IsAdmin() || $arResult['PERMISSIONS']['controler'] || $arResult['PERMISSIONS']['kurator']);

if (!$bAccess) {
    LocalRedirect('/control-orders/');
}

$APPLICATION->SetAdditionalCSS('/local/js/jstree/themes/default/style.min.css');
$APPLICATION->AddHeadScript('/local/js/jstree/jstree.min.js');

$page = $_REQUEST['enums'];
if (!file_exists(__DIR__ . '/enums_' . $page . '.php')) {
	$page = 'view';
}
?>
<div class="box box-primary h-100">
    <div class="box-body box-profile">
        <?
        if ($page == 'view') {
            ?>
            <ul>
                <li><a href="/control-orders/?enums=classificator">Тематики</a></li>
                <li><a href="/control-orders/?enums=groups">Группы исполнителей</a></li>
                <li><a href="/control-orders/?enums=external">Внешние организации</a></li>
                <li><a href="/control-orders/?enums=object">Объекты поручений</a></li>
            </ul>
            <?
        } else {
            require('enums_' . $page . '.php');
        }
        ?>
    </div>
</div>
