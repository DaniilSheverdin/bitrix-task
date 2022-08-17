<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<div class="box box-primary">
    <div class="box-body box-profile">
        <div id="view-switcher-container" class="calendar-view-switcher">
            <div class="view-switcher-list">
                <a href="?detail=<?=$_REQUEST['detail']?>&view=svyazi&back_url=<?=$backUrl?>" <?=($_REQUEST['sub']=='')?'class="active"':'' ?> >Все</a>
                <a href="?detail=<?=$_REQUEST['detail']?>&view=svyazi&sub=poruch&back_url=<?=$backUrl?>" <?=($_REQUEST['sub']=='poruch')?'class="active"':'' ?>>Поручения</a>
                <a href="?detail=<?=$_REQUEST['detail']?>&view=svyazi&sub=tasks&back_url=<?=$backUrl?>" <?=($_REQUEST['sub']=='tasks')?'class="active"':'' ?>>Задачи</a>
            </div>
        </div>
    </div>
</div>

<div class="box box-primary col-10 col-xl-12">
<?
switch ($_REQUEST['sub']) {
    case 'tasks':
        require 'detail_svyazi_tasks.php';
        break;
    case 'poruch':
        require 'detail_svyazi_poruch.php';
        break;
    default:
        require 'detail_svyazi_poruch.php';
        require 'detail_svyazi_tasks.php';
        break;
}
?>
</div>