<?php

use Citto\Controlorders\Orders;
use Citto\Controlorders\Settings;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$isMainPage = ($sView != 'otchet_ispolnitel');

$curUserId = $GLOBALS['USER']->GetID();

$bExternalExecutor = (new Orders())->isExternal($arElement['ID']);

$bMayAddComments = (
    (($arPerm['ispolnitel_implementation'] || $arPerm['ispolnitel']) && $arElement['PROPERTY_ACTION_ENUM_ID'] == 1136) &&
    (
        $arPerm['ispolnitel_main'] ||
        $arPerm['ispolnitel_submain'] ||
        $arPerm['ispolnitel_implementation'] ||
        $arDetail['ELEMENT']['PROPERTY_DELEGATE_USER_VALUE'] == $curUserId
    ) &&
    $arPerm['ispolnitel_data']['ID'] == $arElement['PROPERTY_ISPOLNITEL_VALUE']
);

if ($bExternalExecutor && $arPerm['controler']) {
    $bMayAddComments = true;
}

if (!$bMayAddComments && empty($arComments['OTCHET_ISPOLNITEL'])) {
    return;
}

$arFirstComment = $arComments['OTCHET_ISPOLNITEL'][0];

$bShowFormAdd = $isMainPage && empty($_REQUEST['edit_comment']);

if (!empty($arElement['PROPERTY_WORK_INTER_STATUS_VALUE'])) {
    $bShowFormAdd = false;
}

if (!empty($arFirstComment)) {
    if (
        $arFirstComment['PROPERTY_STATUS_ENUM_ID'] == $arParams['COMMENT_ENUM']['STATUS']['DRAFT']['ID'] &&
        $arFirstComment['PROPERTY_CURRENT_USER_VALUE'] == $arFirstComment['PROPERTY_USER_VALUE'] &&
        $arFirstComment['PROPERTY_USER_VALUE'] == $curUserId
    ) {
        $bShowFormAdd = false;
    }

    if (
        $arFirstComment['PROPERTY_CURRENT_USER_VALUE'] != $arFirstComment['PROPERTY_USER_VALUE'] &&
        $arFirstComment['PROPERTY_USER_VALUE'] == $curUserId
    ) {
        $bShowFormAdd = false;
    }

    if (
        $arFirstComment['PROPERTY_CURRENT_USER_VALUE'] == $curUserId &&
        empty($arFirstComment['PROPERTY_COMMENT_VALUE']) &&
        empty($arFirstComment['PROPERTY_CONTROLER_COMMENT_VALUE'])
    ) {
        $bShowFormAdd = false;
    }

    foreach ($arFirstComment['PROPERTY_VISA_VALUE'] as $visaKey => $visaRow) {
        [$userId, $status, $comment, $date] = explode(':', $visaRow, 4);
        if ($status == 'N') {
            $bShowFormAdd = true;
        }
    }

    if (
        !empty($arFirstComment['PROPERTY_CONTROLER_COMMENT_VALUE'])
    ) {
        $bShowFormAdd = true;
    }
}

?>
<div class="<?=$isMainPage ? 'post clearfix' : 'box box-primary col-10 col-xl-12'?>">
    <div class="box-header with-border">
        <h3 class="box-title"><b><?=(count($arDetail['POSITION_DATA']) > 0) ? 'Позиция:' : 'Отчет исполнителя:' ?></b></h3>
        <?if ($isMainPage && count($arComments['OTCHET_ISPOLNITEL']) > 1) :?>
            <a href="?detail=<?=$_REQUEST['detail']?>&view=otchet_ispolnitel&back_url=<?=$backUrl?>" class="float-right btn-box-tool">История</a>
        <?endif;?>
    </div>
    <div class="box-body box-profile">
        <?
        foreach ($arComments['OTCHET_ISPOLNITEL'] as $key => $value) {
            $bDraft = ($value['PROPERTY_STATUS_ENUM_ID'] == $arParams['COMMENT_ENUM']['STATUS']['DRAFT']['ID']);
            if (
                $bDraft &&
                $value['PROPERTY_USER_VALUE'] != $curUserId &&
                !isset($_REQUEST['show_drafts'])
            ) {
                ?>
                <div class="alert alert-danger" role="alert">
                    В поручении есть черновик отчета. <a href="<?=$APPLICATION->GetCurPageParam('show_drafts')?>">Показать</a>
                </div>
                <?
                break;
            }
        }
        $reportId = null;
        if ($bShowFormAdd && $bMayAddComments) {
            foreach ($arFirstComment['PROPERTY_VISA_VALUE'] as $visaKey => $visaRow) {
                [$userId, $status, $comment, $date] = explode(':', $visaRow, 4);
                if ($status == 'N') {
                    $reportId = $arFirstComment['ID'];
                }
            }
            // if (
            //     !empty($arFirstComment['PROPERTY_COMMENT_VALUE']) &&
            //     $arFirstComment['PROPERTY_COMMENT_VALUE'] != '-'
            // ) {
            //     $reportId = $arFirstComment['ID'];
            // }

            require(__DIR__ . '/detail_otchet_ispolnitel_edit.php');
        }
        $reportId = null;
        echo '<div>';
        require(__DIR__ . '/detail_otchet_ispolnitel_list.php');
        echo '</div>';
        ?>
    </div>
</div>
