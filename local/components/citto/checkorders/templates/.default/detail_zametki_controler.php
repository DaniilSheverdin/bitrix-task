<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$isMainPage = ($sView != 'zametki_controler');

$curUserId = $GLOBALS['USER']->GetID();

if (!$arPerm['controler'] && !$arPerm['kurator']) {
    if (!$GLOBALS['USER']->IsAdmin()) {
        return;
    }
}

$bEditComment = false;
$bMayEditComments = false;
if ($arPerm['main_controler'] || $arPerm['controler']) {
    if (isset($_REQUEST['edit_comment']) && $_REQUEST['edit_comment'] > 0) {
        $bEditComment = true;
    }
    $bMayEditComments = true;
}

?>
<div class="box box-primary col-10 col-xl-12">
    <div class="box-header with-border">
        <h3 class="box-title"><b>Заметки контролера:</b></h3>
    </div>

    <div class="box-body box-profile">
        <?
        if (!$bEditComment && $arPerm['controler']) {
            ?>
            <div class="post clearfix">
                <?=$this->__component->getUserBlock()?>
                <form action="?detail=<?=$_REQUEST['detail']?><?=(!$isMainPage) ? '&view=' . $_REQUEST['view'] : '' ?>&back_url=<?=$backUrl?>" method="POST">
                    <input type="hidden" name="action" value="add_zametka" />
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
                    );?><br/>

                    <?$APPLICATION->IncludeComponent(
                        'bitrix:main.file.input',
                        'drag_n_drop',
                        array(
                            'INPUT_NAME' => 'FILES_ZAMETKA_CONTROLER',
                            'INPUT_VALUE' => '',
                            'MULTIPLE' => 'Y',
                            'MODULE_ID' => 'checkorders',
                            'MAX_FILE_SIZE' => '',
                            'ALLOW_UPLOAD' => 'A',
                            'ALLOW_UPLOAD_EXT' => '',
                        ),
                        false
                    );?><br>

                    <a href="#" class="js-show-vote-data">Прикрепить опрос</a>
                    <div class="vote-data js-vote-data row border-secondary">
                        <div class="col-md-4">
                            <b>Дата опроса: * </b>
                            <input class="form-control" required type="text" disabled="" name="DATE_VOTE" value="<?=date('d.m.Y') ?>" onclick="BX.calendar({node: this, field: this, bTime: false});">
                        </div>
                        <div class="col-md-4">
                            <b>Результат опроса: * </b>
                            <select class="form-control" disabled="" name="RESULT_VOTE">
                                <?
                                foreach ($arResult['RESULT_VOTE'] as $sKey => $arResultVote) {
                                    ?>
                                    <option value="<?=$arResultVote['ID']?>"><?=$arResultVote['VALUE']?></option>
                                    <?
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <br>
                    <button class="ui-btn ui-btn-primary" type="submit">Добавить заметку</button>
                </form>
            </div>
            <?
        }
        ?>

        <div>
            <?
            foreach ($arComments['COMMENTS_CONTROLER'] as $key => $value) {
                if ($bMayEditComments && $bEditComment && $_REQUEST['edit_comment'] == $value['ID']) {
                    ?>
                    <a name="zametka-controler-<?=$value['ID'] ?>"></a>
                    <div class="post clearfix">
                        <?=$this->__component->getUserBlock(
                            $value['PROPERTY_USER_VALUE'],
                            $value['DATE_CREATE']
                        )?>
                        <form action="?detail=<?=$_REQUEST['detail']?><?=(!$isMainPage) ? '&view=' . $_REQUEST['view'] : '' ?>&back_url=<?=$backUrl?>" method="POST">
                            <input type="hidden" name="action" value="add_zametka" />
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
                                    'INPUT_NAME' => 'FILES_ZAMETKA_CONTROLER',
                                    'INPUT_VALUE' => $value['~PROPERTY_DOCS_VALUE'],
                                    'MULTIPLE' => 'Y',
                                    'MODULE_ID' => 'checkorders',
                                    'MAX_FILE_SIZE' => '',
                                    'ALLOW_UPLOAD' => 'A',
                                    'ALLOW_UPLOAD_EXT' => '',
                                ),
                                false
                            );?><br>

                            <a href="#" class="js-show-vote-data">Прикрепить опрос</a>
                            <div
                                class="vote-data js-vote-data row border-secondary"
                                <?=($value['PROPERTY_DATE_VOTE_VALUE']!=''?'style="display:block"':'')?>
                                >
                                <div class="col-md-4">
                                    <b>Дата опроса: *</b>
                                    <input
                                        class="form-control"
                                        required
                                        type="text"
                                        <?=($value['PROPERTY_DATE_VOTE_VALUE']==''?'disabled':'')?>
                                        name="DATE_VOTE"
                                        value="<?=$value['PROPERTY_DATE_VOTE_VALUE']??date('d.m.Y')?>"
                                        onclick="BX.calendar({node: this, field: this, bTime: false});"
                                        />
                                </div>
                                <div class="col-md-4">
                                    <b>Результат опроса: *</b>
                                    <select
                                        class="form-control"
                                        <?=($value['PROPERTY_DATE_VOTE_VALUE']==''?'disabled':'')?>
                                        name="RESULT_VOTE">
                                        <?
                                        foreach ($arResult['RESULT_VOTE'] as $sKey => $arResultVote) {
                                            ?>
                                            <option
                                                value="<?=$arResultVote['ID']?>"
                                                <?=($arResultVote['ID']==$value['PROPERTY_RESULT_VOTE_ENUM_ID']?'selected':'')?>
                                                ><?=$arResultVote['VALUE']?></option>
                                            <?
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <br>
                            <button name="subaction" value="edit" class="ui-btn ui-btn-primary" type="submit">Редактировать</button>
                        </form>
                    </div>
                    <?
                } else {
                    ?>
                    <a name="zametka-controler-<?=$value['ID'] ?>"></a>
                    <div class="post clearfix">
                        <?=$this->__component->getUserBlock(
                            $value['PROPERTY_USER_VALUE'],
                            $value['DATE_CREATE']
                        )?>
                        <p><?=$value['~DETAIL_TEXT']?></p>

                        <?
                        if ($value['PROPERTY_DATE_VOTE_VALUE'] != '') {
                            ?>
                            <b>Опрос:</b> <?=$value['PROPERTY_DATE_VOTE_VALUE']?><br/>
                            <b>Результат:</b> <?=$value['PROPERTY_RESULT_VOTE_VALUE']?><br/>
                            <?
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
                            <a href="<?=$APPLICATION->GetCurPageParam('edit_comment=' . $value['ID'] . '&view=zametki_controler', ['edit_comment', 'view']) ?>#zametka-controler-<?=$value['ID'] ?>">Редактировать</a>
                            <?
                        }
                        ?>
                    </div>
                    <?
                }
            }
            ?>
        </div>
    </div>
</div>
