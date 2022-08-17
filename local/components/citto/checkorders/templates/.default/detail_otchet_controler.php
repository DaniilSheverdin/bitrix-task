<?php

use Citto\ControlOrders\Orders;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$isMainPage = ($sView != 'otchet_controler');

$curUserId = $GLOBALS['USER']->GetID();

if (!$arPerm['controler'] && empty($arComments['OTCHET_CONTROLER'])) {
    return;
}

$bEditComment = false;
$bMayEditComments = false;
if ($arPerm['main_controler'] || $arPerm['controler']) {
    if (isset($_REQUEST['edit_comment']) && $_REQUEST['edit_comment'] > 0) {
        $bEditComment = true;
    }
    if (isset($_REQUEST['edit_thesis']) && $_REQUEST['edit_thesis'] > 0) {
        $bEditThesis = true;
    }
    $bMayEditComments = true;
}

$showComments = true;
?>

<div class="<?=$isMainPage ? 'post clearfix' : 'box box-primary col-10 col-xl-12'?>">
    <div class="box-header with-border">
        <h3 class="box-title"><b>Основная суть:</b></h3>
        <?if ($isMainPage && count($arComments['OTCHET_CONTROLER']) > 1) :?>
            <a href="?detail=<?=$_REQUEST['detail']?>&view=otchet_controler&back_url=<?=$backUrl?>" class="float-right btn-box-tool">История</a>
        <?endif;?>
    </div>

    <div class="box-body box-profile">
        <? if (
                (!$bEditComment && $arPerm['controler']) && !$arElement['THESIS']['THESIS'] ||
                ($bMayEditComments && $bEditThesis && $arElement['THESIS']['THESIS'])
        ): ?>

            <? if ((!$bEditComment && $arPerm['controler']) && !$arElement['THESIS']['THESIS']): ?>
            <p class="mb-2">
                <a class="btn btn-primary" data-toggle="collapse" href="#thesis" role="button" aria-expanded="false" aria-controls="thesis">
                    Добавить
                </a>
            </p>
            <? endif; ?>

            <div
                    id="thesis"
                    class="post clearfix <? if ((!$bEditComment && $arPerm['controler']) && !$arElement['THESIS']['THESIS']): ?>collapse<? endif; ?>"
            >
                <?=$this->__component->getUserBlock()?>
                <form action="?detail=<?=$_REQUEST['detail']?><?=($_REQUEST['view'] != 'summary') ? '&view=' . $_REQUEST['view'] : '' ?>&back_url=<?=$backUrl?>" method="POST">
                    <input type="hidden" name="action" value="thesis_from_controler" />
                    <?$APPLICATION->IncludeComponent(
                        'bitrix:fileman.light_editor',
                        '',
                        array(
                            'CONTENT' => ($bEditThesis) ? $arElement['THESIS']['THESIS'] : '',
                            'INPUT_NAME' => 'DETAIL_TEXT',
                            'INPUT_ID' => '',
                            'WIDTH' => '100%',
                            'HEIGHT' => '300px',
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
                    );?>
                    <button name="subaction" value="thesis" class="ui-btn " type="submit">Отправить</button>
                </form>
            </div>
        <? elseif (
            $arElement['THESIS']['USER_ID'] &&
            ($showComments || !$isMainPage)
        ) : ?>
            <div>
                <a name="controler-<?= $arElement['THESIS']['USER_ID'] ?>"></a>
                <div class="post clearfix">
                    <?= $this->__component->getUserBlock(
                        $arElement['THESIS']['USER_ID'],
                        $arElement['THESIS']['DATE_CREATE']
                    ) ?>
                    <p><?= $arElement['THESIS']['THESIS'] ?></p>

                    <? if ($bMayEditComments): ?>
                        <br/>
                        <a href="<?= $APPLICATION->GetCurPageParam('edit_thesis=' . $arElement['ID']) ?>#controler-<?= $arElement['THESIS']['USER_ID'] ?>">Редактировать</a>
                    <?endif;?>
                </div>

            </div>
        <?endif;?>
    </div>
</div>

<div class="<?=$isMainPage ? 'post clearfix' : 'box box-primary col-10 col-xl-12'?>">
    <div class="box-header with-border">
        <h3 class="box-title"><b>Отчет контролера:</b></h3>
        <?if ($isMainPage && count($arComments['OTCHET_CONTROLER']) > 1) :?>
            <a href="?detail=<?=$_REQUEST['detail']?>&view=otchet_controler&back_url=<?=$backUrl?>" class="float-right btn-box-tool">История</a>
        <?endif;?>
    </div>

    <div class="box-body box-profile">
        <?
        $showComments = true;
        if (!$bEditComment && $arPerm['controler'] && $arElement['PROPERTY_ACTION_ENUM_ID'] == 1137) {
            $showComments = false;
            $arFirstComment = $arComments['OTCHET_CONTROLER'][0];
            ?>

            <div class="post clearfix">
                <?=$this->__component->getUserBlock()?>
                <form action="?detail=<?=$_REQUEST['detail']?><?=(!$isMainPage) ? '&view=' . $_REQUEST['view'] : '' ?>&back_url=<?=$backUrl?>" method="POST">
                    <input type="hidden" name="action" value="add_comment_controler" />
                    <?$APPLICATION->IncludeComponent(
                        'bitrix:fileman.light_editor',
                        '',
                        array(
                            'CONTENT' => $arFirstComment['~DETAIL_TEXT'],
                            'INPUT_NAME' => 'DETAIL_TEXT',
                            'INPUT_ID' => '',
                            'WIDTH' => '100%',
                            'HEIGHT' => '300px',
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

                    <b>Решение контролера для куратора:</b>
                    <select class="form-control js-controler-resh" name="CONTROLER_RESH">
                        <option
                                value="1276"
                        >На снятие с контроля</option>
                        <option
                                value="1277"
                            <?=($arElement['PROPERTY_CONTROLER_RESH_ENUM_ID'] == 1277 || $arElement['PROPERTY_DOPSTATUS_ENUM_ID'] != '' || $arElement['PROPERTY_NEW_DATE_ISPOLN_VALUE'] != '')?'selected':'' ?>
                        >На допконтроль</option>
                        <option
                                value="1278"
                            <?=($arElement['PROPERTY_CONTROLER_RESH_ENUM_ID'] == 1278)?'selected':'' ?>
                        >Требуется уточнение</option>
                    </select>

                    <div
                            class="snytie"
                        <?=($arElement['PROPERTY_CONTROLER_RESH_ENUM_ID'] == 1277 || $arElement['PROPERTY_DOPSTATUS_ENUM_ID'] != '' || $arElement['PROPERTY_NEW_DATE_ISPOLN_VALUE'] != '')?'':'style="display:block;"'?>
                    >
                        <b>Дата исполнения: *</b><br>
                        <input
                                class="form-control"
                                required
                            <?=($arElement['PROPERTY_CONTROLER_RESH_ENUM_ID'] == 1277 || $arElement['PROPERTY_DOPSTATUS_ENUM_ID'] != '' || $arElement['PROPERTY_NEW_DATE_ISPOLN_VALUE'] != '')?'disabled':'' ?>
                                type="text"
                                name="DATE_REAL_ISPOLN"
                                value="<?=($arElement['PROPERTY_DATE_REAL_ISPOLN_VALUE'] != '')?$arElement['PROPERTY_DATE_REAL_ISPOLN_VALUE']:$arElement['PROPERTY_DATE_FACT_ISPOLN_VALUE'] ?>"
                                onclick="BX.calendar({node: this, field: this, bTime: false});">
                    </div>

                    <div
                            class="dop_control"
                        <?=($arElement['PROPERTY_CONTROLER_RESH_ENUM_ID'] == 1277 || $arElement['PROPERTY_DOPSTATUS_ENUM_ID'] != '' || $arElement['PROPERTY_NEW_DATE_ISPOLN_VALUE'] != '')?'style="display:block;"':''?>
                    >
                        <b>Дополнительно:</b>
                        <select class="form-control js-dop-fields-select" name="DOPSTATUS">
                            <option
                                    value="dopcontrol"
                            >Допконтроль (выполнено)</option>

                            <option
                                    value="change_srok_ispoln"
                            >Допконтроль (не выполнено)</option>

                            <?if ($arElement['PROPERTY_DATE_ISPOLN_VALUE'] != $this->__component->disableSrokDate) : ?>
                                <option
                                        value="change_srok"
                                    <?=($arElement['PROPERTY_DOPSTATUS_ENUM_ID']==$arResult['DOPSTATUS']['change_srok']['ID'])?'selected':'' ?>
                                >Смена срока (не выполнено)</option>
                            <?endif;?>

                            <option
                                    value="change_ispoln"
                                <?=($arElement['PROPERTY_DOPSTATUS_ENUM_ID']==$arResult['DOPSTATUS']['change_ispoln']['ID'])?'selected':'' ?>
                            >Передача на исполнение</option>
                            <option
                                    value="to_position"
                                <?=($arElement['PROPERTY_DOPSTATUS_ENUM_ID']==$arResult['DOPSTATUS']['to_position']['ID'])?'selected':'' ?>
                            >Передача на позицию</option>
                        </select>

                        <?if ($arElement['PROPERTY_DATE_ISPOLN_VALUE'] != $this->__component->disableSrokDate) : ?>
                            <div class="new_date">
                                <b>Новая дата: *</b><br>
                                <input
                                        class="form-control"
                                        required
                                        type="text"
                                    <?=($arElement['PROPERTY_CONTROLER_RESH_ENUM_ID'] == 1277 || $arElement['PROPERTY_DOPSTATUS_ENUM_ID'] != '' || $arElement['PROPERTY_NEW_DATE_ISPOLN_VALUE'] != '')?'':'disabled' ?>
                                        name="NEW_DATE_ISPOLN"
                                        value="<?=($arElement['PROPERTY_NEW_DATE_ISPOLN_VALUE'] != '')?$arElement['PROPERTY_NEW_DATE_ISPOLN_VALUE']:date('d.m.Y') ?>"
                                        onclick="BX.calendar({node: this, field: this, bTime: false});">
                            </div>
                            <div class="new_date js-date-ispoln">
                                <b>Новый срок для соисполнителя: *</b><span class="ml-2">
                                <input type="hidden" name="DISABLE_NEW_SUBEXECUTOR_DATE" value="N" />
                                <input class="form-check-input ml-0" type="checkbox" name="DISABLE_NEW_SUBEXECUTOR_DATE" value="Y" id="DisableNewSubexecutorDate" <?=(empty($arElement['PROPERTY_NEW_SUBEXECUTOR_DATE_VALUE']) || $arElement['PROPERTY_NEW_SUBEXECUTOR_DATE_VALUE']==$this->__component->disableSrokDate?'checked':'')?>>
                                <label class="form-check-label ml-3" for="DisableNewSubexecutorDate">
                                Не&nbsp;нужен
                                </label>
                            </span>
                                <br>
                                <input
                                        class="form-control <?=(empty($arElement['PROPERTY_NEW_SUBEXECUTOR_DATE_VALUE']) || $arElement['PROPERTY_NEW_SUBEXECUTOR_DATE_VALUE']==$this->__component->disableSrokDate?'d-none':'')?>"
                                        required
                                        type="text"
                                    <?=($arElement['PROPERTY_CONTROLER_RESH_ENUM_ID'] == 1277 || $arElement['PROPERTY_DOPSTATUS_ENUM_ID'] != '' || $arElement['PROPERTY_NEW_SUBEXECUTOR_DATE_VALUE'] != '')?'':'disabled' ?>
                                        name="NEW_SUBEXECUTOR_DATE"
                                        value="<?=($arElement['PROPERTY_NEW_SUBEXECUTOR_DATE_VALUE'] != '')?$arElement['PROPERTY_NEW_SUBEXECUTOR_DATE_VALUE']:date('d.m.Y') ?>"
                                        onclick="BX.calendar({node: this, field: this, bTime: false});">
                            </div>
                        <?endif;?>

                        <div
                                class="dop_fields change_ispoln change_srok_ispoln"
                            <?=($arElement['PROPERTY_DOPSTATUS_ENUM_ID']==$arResult['DOPSTATUS']['change_ispoln']['ID'])?'style="display:block;"':'' ?>
                        >
                            <b>Новый исполнитель: *</b><br>
                            <select
                                    class="form-control"
                                <?=($arElement['PROPERTY_DOPSTATUS_ENUM_ID']==$arResult['DOPSTATUS']['change_ispoln']['ID'])?'':'disabled' ?>
                                    name="NEWISPOLNITEL">
                                <?
                                foreach ($arResult['ISPOLNITELTYPES'] as $sKey => $sValue) {
                                    if ($sValue['CNT'] > 0) {
                                        ?>
                                        <optgroup label="<?=$sValue['VALUE']?>">
                                            <?
                                            foreach ($arResult['ISPOLNITELS'] as $k => $v) {
                                                if ($v['PROPERTY_TYPE_ENUM_ID'] != $sValue['ID']) {
                                                    continue;
                                                }
                                                ?>
                                                <option
                                                        value="<?=$v['ID']?>"
                                                    <?=($v['ID'] == $arElement['PROPERTY_NEWISPOLNITEL_VALUE']) ? 'selected' : '' ?>
                                                ><?=$v['NAME']?></option>
                                                <?
                                            }
                                            ?>
                                        </optgroup>
                                        <?
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div
                                class="dop_fields to_position"
                            <?=($arElement['PROPERTY_DOPSTATUS_ENUM_ID']==$arResult['DOPSTATUS']['to_position']['ID'])?'style="display:block;"':'' ?>
                        >
                            <b>Позиция на: *</b><br>
                            <select
                                    class="form-control"
                                <?=($arElement['PROPERTY_DOPSTATUS_ENUM_ID']==$arResult['DOPSTATUS']['to_position']['ID'])?'':'disabled' ?>
                                    name="POSITION_ISPOLN">
                                <?
                                foreach ($arResult['ISPOLNITELTYPES'] as $sKey => $sValue) {
                                    if ($sValue['CNT'] > 0) {
                                        ?>
                                        <optgroup label="<?=$sValue['VALUE']?>">
                                            <?
                                            foreach ($arResult['ISPOLNITELS'] as $k => $v) {
                                                if ($v['PROPERTY_TYPE_ENUM_ID'] != $sValue['ID']) {
                                                    continue;
                                                }
                                                ?>
                                                <option
                                                        value="<?=$v['ID']?>"
                                                    <?=($v['ID'] == $arElement['PROPERTY_POSITION_ISPOLN_VALUE']) ? 'selected' : '' ?>
                                                ><?=$v['NAME']?></option>
                                                <?
                                            }
                                            ?>
                                        </optgroup>
                                        <?
                                    }
                                }
                                ?>
                            </select>
                            <b>Требования для позиции: *</b><br/>
                            <textarea name="POSITION_ISPOLN_REQS" class="form-control"><?=$arElement['~PROPERTY_POSITION_ISPOLN_REQS_VALUE']['TEXT']?></textarea>
                            <br/>
                        </div>
                    </div>

                    <?$APPLICATION->IncludeComponent(
                        'bitrix:main.file.input',
                        'drag_n_drop',
                        array(
                            'INPUT_NAME' => 'FILES_COMMENT_CONTROLER',
                            'INPUT_VALUE' => $arFirstComment['~PROPERTY_DOCS_VALUE'],
                            'MULTIPLE' => 'Y',
                            'MODULE_ID' => 'checkorders',
                            'MAX_FILE_SIZE' => '',
                            'ALLOW_UPLOAD' => 'A',
                            'ALLOW_UPLOAD_EXT' => '',
                        ),
                        false
                    );?><br>

                    <?
                    if ($arPerm['main_controler']) {
                        if ($arElement['PROPERTY_CONTROLER_STATUS_ENUM_ID']==$arResult['CONTROLER_STATUS']['on_beforing']['ID']) {
                            ?>
                            <button name="subaction" value="accept" class="ui-btn ui-btn-primary" type="submit">Отправить на согласование</button>
                            <?
                        } else {
                            ?>
                            <button name="subaction" value="accept" class="ui-btn ui-btn-primary" type="submit">Принять и отправить на согласование</button>
                            <?
                        }
                        ?>

                        <button name="subaction" class="ui-btn js-return" type="submit">Отклонить</button>
                        <?
                    } else {
                        ?>
                        <button name="subaction" value="accept" class="ui-btn ui-btn-primary" type="submit">Добавить отчет</button>
                        <button name="subaction" class="ui-btn js-return" type="submit">Отклонить</button>
                        <?
                        $arPositionFrom = (new Orders())->getProperty($_REQUEST['detail'], 'POSITION_TO', true);
                        if (!empty($arPositionFrom)) {
                            ?>
                            <button name="subaction" value="close_position" class="ui-btn ui-btn-success" type="submit">Отправить в архив</button>
                            <?
                        }
                    }
                    ?>
                    <div class="js-return-form d-none">
                        <br/>
                        <textarea name="RETURN_COMMENT" cols="50" placeholder="Причина отклонения"></textarea><br/>
                        <button name="subaction" value="reject" class="ui-btn ui-btn-primary js-return-button">Отклонить</button>
                        <button class="ui-btn ui-btn-danger js-return-cancel">Отмена</button>
                    </div>
                </form>
                <br/>
                <br/>
                <?
                if ($arElement['PROPERTY_NEWISPOLNITEL_VALUE'] != '') {
                    ?>
                    <a class="label label-info label-badge">Новый исполнитель:</a> <?=$arResult['ISPOLNITELS'][ $arElement['PROPERTY_NEWISPOLNITEL_VALUE'] ]['NAME']?>
                    <br/>
                    <?
                }

                if ($arElement['PROPERTY_POSITION_ISPOLN_VALUE'] != '') {
                    ?>
                    <a class="label label-info label-badge">Передача на позицию:</a> <?=$arResult['ISPOLNITELS'][ $arElement['PROPERTY_POSITION_ISPOLN_VALUE'] ]['NAME']?>
                    <br/>
                    <?
                }

                if ($arElement['PROPERTY_DATE_ISPOLN_VALUE'] != $this->__component->disableSrokDate) {
                    if ($arElement['PROPERTY_NEW_DATE_ISPOLN_VALUE'] != '') {
                        ?>
                        <a class="label label-info label-badge">Изменение срока исполнения:</a> <?=$arElement['PROPERTY_NEW_DATE_ISPOLN_VALUE']?>
                        <br/>
                        <?
                    }
                }

                if ($arElement['PROPERTY_DATE_ISPOLN_VALUE'] != $this->__component->disableSrokDate) {
                    if (
                        $arElement['PROPERTY_NEW_SUBEXECUTOR_DATE_VALUE'] != '' &&
                        $arElement['PROPERTY_NEW_SUBEXECUTOR_DATE_VALUE'] != $this->__component->disableSrokDate
                    ) {
                        ?>
                        <a class="label label-info label-badge">Изменение срока соисполнителя:</a> <?=$arElement['PROPERTY_NEW_SUBEXECUTOR_DATE_VALUE']?>
                        <br/>
                        <?
                    }
                }
                ?>
            </div>
            <?
        }

        if (!$bEditComment && $arPerm['controler'] && $arElement['PROPERTY_ACTION_ENUM_ID'] == 1138) {
            ?>
            <p class="mb-2">
                <a class="btn btn-primary" data-toggle="collapse" href="#reject" role="button" aria-expanded="false" aria-controls="reject">
                    Отозвать
                </a>
            </p>

            <div id="reject" class="post clearfix collapse">
                <?=$this->__component->getUserBlock()?>
                <form action="?detail=<?=$_REQUEST['detail']?><?=($_REQUEST['view'] != 'summary') ? '&view=' . $_REQUEST['view'] : '' ?>&back_url=<?=$backUrl?>" method="POST">
                    <input type="hidden" name="action" value="reject_from_controler" />
                    <?$APPLICATION->IncludeComponent(
                        'bitrix:fileman.light_editor',
                        '',
                        array(
                            'CONTENT' => '',
                            'INPUT_NAME' => 'DETAIL_TEXT',
                            'INPUT_ID' => '',
                            'WIDTH' => '100%',
                            'HEIGHT' => '300px',
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
                    );?>
                    <button name="subaction" value="reject" class="ui-btn " type="submit">Отозвать</button>
                </form>
            </div>
            <?
        }
        ?>

        <?if ($showComments || !$isMainPage) :?>
            <div>
                <?
                foreach ($arComments['OTCHET_CONTROLER'] as $key => $value) {
                    if ($isMainPage && $key > 0) {
                        break;
                    }

                    if ($bMayEditComments && $bEditComment && $_REQUEST['edit_comment'] == $value['ID']) {
                        ?>
                        <a name="controler-<?=$value['ID'] ?>"></a>
                        <div class="post clearfix">
                            <?=$this->__component->getUserBlock(
                                $value['PROPERTY_USER_VALUE'],
                                $value['DATE_CREATE']
                            )?>
                            <form action="?detail=<?=$_REQUEST['detail']?><?=(!$isMainPage) ? '&view=' . $_REQUEST['view'] : '' ?>&back_url=<?=$backUrl?>" method="POST">
                                <input type="hidden" name="action" value="add_comment_controler" />
                                <input type="hidden" name="CURRENT_ID" value="<?=$value['ID'] ?>" />
                                <?$APPLICATION->IncludeComponent(
                                    'bitrix:fileman.light_editor',
                                    '',
                                    array(
                                        'CONTENT' => $value['~DETAIL_TEXT'],
                                        'INPUT_NAME' => 'DETAIL_TEXT',
                                        'INPUT_ID' => '',
                                        'WIDTH' => '100%',
                                        'HEIGHT' => '300px',
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
                                        'INPUT_NAME' => 'FILES_COMMENT_CONTROLER',
                                        'INPUT_VALUE' => $value['~PROPERTY_DOCS_VALUE'],
                                        'MULTIPLE' => 'Y',
                                        'MODULE_ID' => 'checkorders',
                                        'MAX_FILE_SIZE' => '',
                                        'ALLOW_UPLOAD' => 'A',
                                        'ALLOW_UPLOAD_EXT' => '',
                                    ),
                                    false
                                );?><br>
                                <button name="subaction" value="edit" class="ui-btn ui-btn-primary" type="submit">Редактировать</button>
                            </form>
                            <br/>
                            <br/>
                            <?
                            if ($arElement['PROPERTY_NEWISPOLNITEL_VALUE'] != '') {
                                ?>
                                <a class="label label-info label-badge">Новый исполнитель:</a> <?=$arResult['ISPOLNITELS'][ $arElement['PROPERTY_NEWISPOLNITEL_VALUE'] ]['NAME']?><br>
                                <?
                            }

                            if ($arElement['PROPERTY_POSITION_ISPOLN_VALUE'] != '') {
                                ?>
                                <a class="label label-info label-badge">Передача на позицию:</a> <?=$arResult['ISPOLNITELS'][ $arElement['PROPERTY_POSITION_ISPOLN_VALUE'] ]['NAME']?><br>
                                <?
                            }

                            if ($arElement['PROPERTY_DATE_ISPOLN_VALUE'] != $this->__component->disableSrokDate) {
                                if ($arElement['PROPERTY_NEW_DATE_ISPOLN_VALUE'] != '') {
                                    ?>
                                    <a class="label label-info label-badge">Изменение срока исполнения:</a> <?=$arElement['PROPERTY_NEW_DATE_ISPOLN_VALUE']?><br>
                                    <?
                                }
                            }

                            if ($arElement['PROPERTY_DATE_ISPOLN_VALUE'] != $this->__component->disableSrokDate) {
                                if (
                                    $arElement['PROPERTY_NEW_SUBEXECUTOR_DATE_VALUE'] != '' &&
                                    $arElement['PROPERTY_NEW_SUBEXECUTOR_DATE_VALUE'] != $this->__component->disableSrokDate
                                ) {
                                    ?>
                                    <a class="label label-info label-badge">Изменение срока соисполнителя:</a> <?=$arElement['PROPERTY_NEW_SUBEXECUTOR_DATE_VALUE']?><br>
                                    <?
                                }
                            }
                            ?>
                        </div>
                        <?
                    } else {
                        ?>
                        <a name="controler-<?=$value['ID'] ?>"></a>
                        <div class="post clearfix">
                            <?=$this->__component->getUserBlock(
                                $value['PROPERTY_USER_VALUE'],
                                $value['DATE_CREATE']
                            )?>
                            <p><?=$value['~DETAIL_TEXT']?></p>

                            <?
                            if ($key == 0) {
                                ?>
                                <br>
                                <?
                                if ($arElement['PROPERTY_NEWISPOLNITEL_VALUE'] != '') {
                                    ?>
                                    <a class="label label-info label-badge">Новый исполнитель:</a> <?=$arResult['ISPOLNITELS'][$arElement['PROPERTY_NEWISPOLNITEL_VALUE']]['NAME']?><br>
                                    <?
                                }

                                if ($arElement['PROPERTY_POSITION_ISPOLN_VALUE'] != '') {
                                    ?>
                                    <a class="label label-info label-badge">Передача на позицию:</a> <?=$arResult['ISPOLNITELS'][$arElement['PROPERTY_POSITION_ISPOLN_VALUE']]['NAME']?><br>
                                    <?
                                }

                                if ($arElement['PROPERTY_NEW_DATE_ISPOLN_VALUE'] != '') {
                                    ?>
                                    <a class="label label-info label-badge">Изменение срока исполнения:</a> <?=$arElement['PROPERTY_NEW_DATE_ISPOLN_VALUE']?><br>
                                    <?
                                }

                                if (
                                    $arElement['PROPERTY_NEW_SUBEXECUTOR_DATE_VALUE'] != '' &&
                                    $arElement['PROPERTY_NEW_SUBEXECUTOR_DATE_VALUE'] != $this->__component->disableSrokDate
                                ) {
                                    ?>
                                    <a class="label label-info label-badge">Изменение срока соисполнителя:</a> <?=$arElement['PROPERTY_NEW_SUBEXECUTOR_DATE_VALUE']?><br>
                                    <?
                                }
                            }

                            if (count($value['PROPERTY_DOCS_VALUE']) > 0) {
                                ?>
                                <p>
                                <b>Документы:</b>
                                <?
                                foreach ($value['PROPERTY_DOCS_VALUE'] as $aFile) {
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
                            if ($bMayEditComments) {
                                ?>
                                <br/>
                                <a href="<?=$APPLICATION->GetCurPageParam('edit_comment=' . $value['ID']) ?>#controler-<?=$value['ID'] ?>">Редактировать</a>
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
