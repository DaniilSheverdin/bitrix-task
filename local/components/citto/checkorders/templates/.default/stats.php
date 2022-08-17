<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
        die();
}

CJSCore::Init(
    [
        'amcharts',
        'amcharts_serial',
        'amcharts_pie',
        'amcharts_xy',
        'amcharts_radar'
    ]
);
?>
<div class="box box-primary">
    <div class="box-body box-profile">
        <div id="view-switcher-container" class="calendar-view-switcher">
             <div class="view-switcher-list">
                <a href="?stats=main" <?=($_REQUEST['stats']=='main')?'class="active"':'' ?>>Графики</a>
                <a href="?stats=table" <?=($_REQUEST['stats']=='table')?'class="active"':'' ?>>Таблица</a>
                <? if ($GLOBALS['USER']->IsAdmin()) : ?>
                <a href="?stats=ispolnitel" <?=($_REQUEST['stats']=='ispolnitel')?'class="active"':'' ?>>Общая статистика</a>
                <? endif; ?>
            </div>
        </div>
    </div>
</div>
<?
switch ($_REQUEST['stats']) {
    case 'table':
        require 'stats_table.php';
        break;
    case 'ispolnitel':
        require 'stats_ispolnitel.php';
        break;
    default:
        require 'stats_main.php';
        break;
}
