<?php

use Citto\Controlorders\Settings;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$isMainPage = ($sView != 'otchet_accomplience');

$curUserId = $GLOBALS['USER']->GetID();

$arAccomplices = $arElement['PROPERTY_ACCOMPLICES_VALUE'];

$bSubExecutor = false;
$arDelegate = [];
if (count($arElement['PROPERTY_SUBEXECUTOR_VALUE']) > 0) {
    foreach ($arElement['PROPERTY_SUBEXECUTOR_VALUE'] as $keySE => $valueSE) {
        $subRukovoditel = $arResult['ISPOLNITELS'][ $valueSE ]['PROPERTY_RUKOVODITEL_VALUE'];
        $arAccomplices[] = $subRukovoditel;
        if ($subRukovoditel == $curUserId) {
            $bSubExecutor = true;
        }
        if ($arElement['PROPERTY_SUBEXECUTOR_DESCRIPTION'][ $keySE ] > 0) {
            $subExecutor = $arElement['PROPERTY_SUBEXECUTOR_DESCRIPTION'][ $keySE ];
            $arAccomplices[] = $subExecutor;
            $arDelegate[] = $subExecutor;
            if ($subExecutor == $curUserId) {
                $bSubExecutor = true;
            }
        }
        foreach ($arResult['ISPOLNITELS'][ $valueSE ]['PROPERTY_ZAMESTITELI_VALUE'] as $uId) {
            $arAccomplices[] = $uId;
            if ($uId == $curUserId) {
                $bSubExecutor = true;
            }
        }
        foreach ($arResult['ISPOLNITELS'][ $valueSE ]['PROPERTY_IMPLEMENTATION_VALUE'] as $uId) {
            $arAccomplices[] = $uId;
            if ($uId == $curUserId) {
                $bSubExecutor = true;
            }
        }
    }
}

if (count($arElement['PROPERTY_SUBEXECUTOR_IDS']) > 0) {
    foreach ($arElement['PROPERTY_SUBEXECUTOR_IDS'] as $keySE => $valueSE) {
        $subRukovoditel = $arResult['ISPOLNITELS'][ $valueSE ]['PROPERTY_RUKOVODITEL_VALUE'];
        $arAccomplices[] = $subRukovoditel;
        if ($subRukovoditel == $curUserId) {
            $bSubExecutor = true;
        }
        if ($arElement['PROPERTY_SUBEXECUTOR_USERS'][ $keySE ] > 0) {
            $subExecutor = $arElement['PROPERTY_SUBEXECUTOR_USERS'][ $keySE ];
            $arAccomplices[] = $subExecutor;
            $arDelegate[] = $subExecutor;
            if ($subExecutor == $curUserId) {
                $bSubExecutor = true;
            }
        }
        foreach ($arResult['ISPOLNITELS'][ $valueSE ]['PROPERTY_ZAMESTITELI_VALUE'] as $uId) {
            $arAccomplices[] = $uId;
            if ($uId == $curUserId) {
                $bSubExecutor = true;
            }
        }
        foreach ($arResult['ISPOLNITELS'][ $valueSE ]['PROPERTY_IMPLEMENTATION_VALUE'] as $uId) {
            $arAccomplices[] = $uId;
            if ($uId == $curUserId) {
                $bSubExecutor = true;
            }
        }
    }
}

$arVisaUsers = [];
foreach ($arComments['OTCHET_ACCOMPLIENCE'] as $row) {
    foreach ($row['PROPERTY_VISA_VALUE'] as $visaRow) {
        [$userId, $status, $comment, $date] = explode(':', $visaRow, 4);
        $arAccomplices[] = $userId;
        $arVisaUsers[ $row['ID'] ][] = $userId;
    }
}

$arAccomplices = array_filter($arAccomplices);
$arDelegate = array_filter($arDelegate);

/**
 * В случае, если по поручению есть соисполнитель (два разных связанных
 * поручения/поручения на двух исполнителей) - давать возможность
 * оставить комментарий соисполнителя в связном поручении
 * @see https://corp.tularegion.local/company/personal/user/2440/tasks/task/view/55228/
 */
if (!empty($arElement['PROPERTY_PORUCH_VALUE'])) {
    $resAccomplice = CIBlockElement::GetList(
        false,
        [
            'IBLOCK_ID' => Settings::$iblockId['ORDERS'],
            'ACTIVE'    => 'Y',
            '!ID'       => $_REQUEST['detail'],
            'ID'        => $arElement['PROPERTY_PORUCH_VALUE'],
        ],
        false,
        false,
        [
            'ID', 'IBLOCK_ID', 'PROPERTY_DELEGATE_USER'
        ]
    );
    while ($rowAccomplice = $resAccomplice->GetNext()) {
        if (!empty($rowAccomplice['PROPERTY_DELEGATE_USER_VALUE'])) {
            $arAccomplices[] = $rowAccomplice['PROPERTY_DELEGATE_USER_VALUE'];
        }
    }
}

$bMayAddComments = (
    (
        $arPerm['ispolnitel'] ||
        $arResult['SVYAZI_EXECUTOR'] ||
        $arResult['VISA_ACCOMPLIENCE']
    ) &&
    (
        $arElement['PROPERTY_ACTION_ENUM_ID'] == 1136 ||
        ($bSubExecutor && $arElement['PROPERTY_ACTION_ENUM_ID'] == 1135)
    ) &&
    in_array($curUserId, $arAccomplices)
);

$bEditComment = ($bMayAddComments && isset($_REQUEST['edit_comment']) && $_REQUEST['edit_comment'] > 0);

if (!$bMayAddComments && empty($arComments['OTCHET_ACCOMPLIENCE'])) {
    return;
}

$arFirstComment = [];
foreach ($arComments['OTCHET_ACCOMPLIENCE'] as $row) {
    if ((int)$row['PROPERTY_USER_VALUE'] == $curUserId) {
        $arFirstComment = $row;
        break;
    } else {
        foreach ($row['PROPERTY_VISA_VALUE'] as $visaRow) {
            [$userId, $status, $comment, $date] = explode(':', $visaRow, 4);
            if ($isMainPage && $userId == $curUserId) {
                $arFirstComment = $row;
                break;
            }
        }
    }
}

$showComments = false;
if ($bMayAddComments && $isMainPage && !empty($arFirstComment)) {
    $showComments = true;
}

if (isset($_REQUEST['edit_comment']) && $_REQUEST['edit_comment'] == -1) {
    $showComments = false;
    $arFirstComment = [];
}

?>
<div class="<?=$isMainPage ? 'post clearfix' : 'box box-primary col-10 col-xl-12'?>">
    <div class="box-header with-border">
        <h3 class="box-title"><b>Отчет соисполнителя:</b></h3>
        <?if ($isMainPage && count($arComments['OTCHET_ACCOMPLIENCE']) > 0) :?>
            <a href="?detail=<?=$_REQUEST['detail']?>&view=otchet_accomplience&back_url=<?=$backUrl?>" class="float-right btn-box-tool">История</a>
        <?endif;?>
    </div>
    <div class="box-body box-profile">
        <?
        if (
            $isMainPage &&
            $bMayAddComments &&
            empty($arFirstComment) &&
            !empty($arComments['OTCHET_ACCOMPLIENCE'])
        ) {
            ?>
            <div class="alert alert-danger" role="alert">
                В поручении есть отчет<?=count($arComments['OTCHET_ACCOMPLIENCE']) > 1 ? 'ы' : ''?> соисполнителя. <a href="<?=$APPLICATION->GetCurPageParam('view=otchet_accomplience', ['view'])?>">Посмотреть</a>
            </div>
            <?
        }
        if (!$showComments && !$bEditComment && $bMayAddComments) {
            $showComments = false;
            ?>
            <div class="post clearfix">
                <?=$this->__component->getUserBlock()?>
                <form
                    action="?detail=<?=$_REQUEST['detail']?><?=(!$isMainPage) ? '&view=' . $_REQUEST['view'] : '' ?>&back_url=<?=$backUrl?>"
                    class="js-add-comment-accomplience"
                    method="POST">
                    <input type="hidden" name="action" value="add_comment_accomplience" />
                    <?$APPLICATION->IncludeComponent(
                        'bitrix:fileman.light_editor',
                        '',
                        array(
                            'CONTENT' => $arFirstComment['~DETAIL_TEXT'],
                            'INPUT_NAME' => 'DETAIL_TEXT',
                            'INPUT_ID' => '',
                            'WIDTH' => '100%',
                            'HEIGHT' => '200px',
                            'RESIZABLE' => 'Y',
                            'AUTO_RESIZE' => 'Y',
                            'VIDEO_ALLOW_VIDEO' => 'Y',
                            'VIDEO_MAX_WIDTH' => '640',
                            'VIDEO_MAX_HEIGHT' => '480',
                            'VIDEO_BUFFER' => '20',
                            'VIDEO_LOGO' => '',
                            'VIDEO_WMODE' => 'transparent',
                            'VIDEO_WINDOWLESS' => 'Y',
                            'VIDEO_SKIN' => '/bitrix/components/bitrix/player/mediaplayer/skins/bitrix.swf',
                            'USE_FILE_DIALOGS' => 'Y',
                            'ID' => '',
                            'JS_OBJ_NAME' => 'DETAIL_TEXT',
                        )
                    );?><br/>

                    <?$APPLICATION->IncludeComponent(
                        'bitrix:main.file.input',
                        'drag_n_drop',
                        array(
                            'INPUT_NAME' => 'FILES_ISPOLN',
                            'INPUT_VALUE' => $arFirstComment['~PROPERTY_DOCS_VALUE'],
                            'MULTIPLE' => 'Y',
                            'MODULE_ID' => 'checkorders',
                            'MAX_FILE_SIZE' => '',
                            'ALLOW_UPLOAD' => 'A',
                            'ALLOW_UPLOAD_EXT' => '',
                        ),
                        false
                    );?><br/>
                    <?
                    if (
                        $bSubExecutor &&
                        $arElement['PROPERTY_SUBEXECUTOR_DATE_VALUE'] != '' &&
                        $arElement['PROPERTY_SUBEXECUTOR_DATE_VALUE'] != $this->__component->disableSrokDate
                    ) {
                        $color = '#000';
                        if (strtotime($arElement['PROPERTY_SUBEXECUTOR_DATE_VALUE'] . ' 23:59:59') < time()) {
                            $color = 'red';
                        }
                        ?>
                        <b>Срок предоставления отчёта:</b>
                        <span style="color:<?=$color ?>"><?=$arElement['PROPERTY_SUBEXECUTOR_DATE_VALUE'] ?></span>
                        <input type="hidden" name="PROPERTY_SUBEXECUTOR_DATE_VALUE" value="<?=$arElement['PROPERTY_SUBEXECUTOR_DATE_VALUE']?>" />
                        <br/>
                        <?
                    }
                    ?>

                    <div class="row">
                        <div class="col-12">
                            <b>Согласующие:</b>
                            <?=$this->__component->showVisaTableEdit(
                                (array)$arFirstComment['PROPERTY_VISA_VALUE'],
                                (int)$arFirstComment['ID'],
                                (int)$arElement['ID'],
                                $arParams['COMMENT_ENUM']['VISA_TYPE']['same']['EXTERNAL_ID'] ?? '',
                                true,
                                'ACCOMPLIENCE'
                            )?>
                        </div>
                    </div>
                    <br/>
                    <button name="subaction" value="add_comment" class="ui-btn ui-btn-primary" type="submit">Добавить отчет</button>
                </form>
                <?
                ?>
            </div>
            <br/>
            <?
        }

        if (
            $bMayAddComments &&
            $isMainPage &&
            (
                in_array($arPerm['ispolnitel_data']['ID'], $arElement['PROPERTY_SUBEXECUTOR_VALUE']) ||
                in_array($arPerm['ispolnitel_data']['ID'], $arElement['PROPERTY_SUBEXECUTOR_IDS'])
            ) &&
            (
                ($arPerm['ispolnitel_employee'] && in_array($curUserId, $arDelegate)) ||
                $arPerm['ispolnitel_main'] ||
                $arPerm['ispolnitel_submain'] ||
                $arPerm['ispolnitel_implementation']
            )
        ) {
            echo '<br/>';
            require('detail_delegate_subexecutor.php');
        } elseif (
            $bMayAddComments &&
            $isMainPage &&
            in_array($curUserId, $arElement['PROPERTY_ACCOMPLICES_VALUE'])
        ) {
            echo '<br/>';
            require('detail_delegate_accomplience.php');
        }
        ?>

        <?if ($showComments || !$isMainPage) :?>
        <div>
            <?
            foreach ($arComments['OTCHET_ACCOMPLIENCE'] as $key => $value) {
                if ($isMainPage && $key > 0) {
                    break;
                }
                if ($bMayAddComments && $bEditComment && $_REQUEST['edit_comment'] == $value['ID']) {
                    ?>
                    <a name="accomplience-<?=$value['ID'] ?>"></a>
                    <div class="post clearfix">
                        <?=$this->__component->getUserBlock(
                            $value['PROPERTY_USER_VALUE'],
                            $value['DATE_CREATE']
                        )?>
                        <form
                            action="?detail=<?=$_REQUEST['detail']?><?=(!$isMainPage) ? '&view=' . $_REQUEST['view'] : '' ?>&back_url=<?=$backUrl?>"
                            class="js-add-comment-ispolnitel"
                            method="POST">
                            <input type="hidden" name="action" value="add_comment_accomplience" />
                            <input type="hidden" name="CURRENT_ID" value="<?=$value['ID'] ?>" />
                            <?$APPLICATION->IncludeComponent(
                                'bitrix:fileman.light_editor',
                                '',
                                array(
                                    'CONTENT' => $value['~DETAIL_TEXT'],
                                    'INPUT_NAME' => 'DETAIL_TEXT',
                                    'INPUT_ID' => '',
                                    'WIDTH' => '100%',
                                    'HEIGHT' => '200px',
                                    'RESIZABLE' => 'Y',
                                    'AUTO_RESIZE' => 'Y',
                                    'VIDEO_ALLOW_VIDEO' => 'Y',
                                    'VIDEO_MAX_WIDTH' => '640',
                                    'VIDEO_MAX_HEIGHT' => '480',
                                    'VIDEO_BUFFER' => '20',
                                    'VIDEO_LOGO' => '',
                                    'VIDEO_WMODE' => 'transparent',
                                    'VIDEO_WINDOWLESS' => 'Y',
                                    'VIDEO_SKIN' => '/bitrix/components/bitrix/player/mediaplayer/skins/bitrix.swf',
                                    'USE_FILE_DIALOGS' => 'Y',
                                    'ID' => '',
                                    'JS_OBJ_NAME' => 'DETAIL_TEXT',
                                )
                            );?><br/>

                            <?$APPLICATION->IncludeComponent(
                                'bitrix:main.file.input',
                                'drag_n_drop',
                                array(
                                    'INPUT_NAME' => 'FILES_ISPOLN',
                                    'INPUT_VALUE' => $value['~PROPERTY_DOCS_VALUE'],
                                    'MULTIPLE' => 'Y',
                                    'MODULE_ID' => 'checkorders',
                                    'MAX_FILE_SIZE' => '',
                                    'ALLOW_UPLOAD' => 'A',
                                    'ALLOW_UPLOAD_EXT' => '',
                                ),
                                false
                            );?><br/>
                            <?
                            if (
                                $bSubExecutor &&
                                $arElement['PROPERTY_SUBEXECUTOR_DATE_VALUE'] != '' &&
                                $arElement['PROPERTY_SUBEXECUTOR_DATE_VALUE'] != $this->__component->disableSrokDate
                            ) {
                                $color = '#000';
                                if (strtotime($arElement['PROPERTY_SUBEXECUTOR_DATE_VALUE'] . ' 23:59:59') < time()) {
                                    $color = 'red';
                                }
                                ?>
                                <b>Срок предоставления отчёта:</b>
                                <span style="color:<?=$color ?>"><?=$arElement['PROPERTY_SUBEXECUTOR_DATE_VALUE'] ?></span>
                                <input type="hidden" name="PROPERTY_SUBEXECUTOR_DATE_VALUE" value="<?=$arElement['PROPERTY_SUBEXECUTOR_DATE_VALUE']?>" />
                                <br/>
                                <?
                            }
                            ?>

                            <div class="row">
                                <div class="col-12">
                                    <b>Согласующие:</b>
                                    <?=$this->__component->showVisaTableEdit(
                                        (array)$value['PROPERTY_VISA_VALUE'],
                                        (int)$value['ID'],
                                        (int)$arElement['ID'],
                                        $arParams['COMMENT_ENUM']['VISA_TYPE']['same']['EXTERNAL_ID'] ?? '',
                                        true,
                                        'ACCOMPLIENCE'
                                    )?>
                                </div>
                            </div>
                            <br/>
                            <a href="javascript:void(0);" class="ui-btn ui-btn-primary js-subaction-add-comment" type="submit">Сохранить</a>
                            <button name="subaction" value="add_comment" class="ui-btn ui-btn-primary hide" type="submit">Сохранить</button>
                        </form>
                        <br/>
                    </div>
                    <?
                } else {
                    ?>
                    <a name="accomplience-<?=$value['ID'] ?>"></a>
                    <div class="post clearfix">
                        <?=$this->__component->getUserBlock(
                            $value['PROPERTY_USER_VALUE'],
                            $value['DATE_CREATE'],
                            [
                                'description' => ($value['PROPERTY_BROKEN_SROK_VALUE'] == 'Y' ? 'red' : '')
                            ]
                        )?>
                        <p><?=$value['~DETAIL_TEXT']?></p>
                        <?
                        if (count($value['PROPERTY_DOCS_VALUE']) > 0) {
                            ?>
                            <p>
                                <b>Документы:</b>
                                <?
                                foreach ($value['PROPERTY_DOCS_VALUE'] as $k2 => $aFile) {
                                    ?>
                                    <div>
                                        <?=$aFile['ORIGINAL_NAME']?> <a href="<?=$aFile['SRC']?>" target="_blank">Скачать</a>
                                    </div>
                                    <?
                                }
                                ?>
                            </p>
                            <?
                        }

                        if (!empty($value['PROPERTY_VISA_VALUE'])) {
                            ?>
                            <div class="row">
                                <div class="col-12">
                                    <b>Согласующие:</b>
                                    <?=$this->__component->showVisaTableEdit(
                                        (array)$value['PROPERTY_VISA_VALUE'],
                                        (int)$value['ID'],
                                        (int)$arElement['ID'],
                                        $arParams['COMMENT_ENUM']['VISA_TYPE']['same']['EXTERNAL_ID'] ?? '',
                                        false,
                                        'ACCOMPLIENCE'
                                    )?>
                                </div>
                            </div>
                            <?
                        }

                        if (
                            $bMayAddComments &&
                            (
                                $curUserId == $value['PROPERTY_USER_VALUE'] ||
                                $arPerm['ispolnitel_implementation'] ||
                                in_array($curUserId, $arVisaUsers[ $value['ID'] ])
                            )
                        ) {
                            ?>
                            <br/>
                            <a href="<?=$APPLICATION->GetCurPageParam('edit_comment=' . $value['ID']) ?>#accomplience-<?=$value['ID'] ?>">Редактировать</a>
                            <?
                        }
                        if ($isMainPage) {
                            ?>
                            <a class="ml-4" href="<?=$APPLICATION->GetCurPageParam('edit_comment=-1') ?>">Добавить отчет</a>
                            <?
                        }
                        ?>
                    </div>
                    <?
                }
            }
        ?>
        </div>
        <?endif;?>
    </div>
</div>
