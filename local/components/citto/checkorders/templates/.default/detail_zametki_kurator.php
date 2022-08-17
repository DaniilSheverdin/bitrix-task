<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$curUserId = $GLOBALS['USER']->GetID();

if ($arPerm['controler'] || $arPerm['kurator']) {
    ?>
    <div class="box box-primary col-10 col-xl-12">
        <div class="box-header with-border">
            <h3 class="box-title">Заметки куратора</h3>
        </div>

        <div class="box-body box-profile">
            <?
            if ($arPerm['kurator'] && $arElement['PROPERTY_ACTION_ENUM_ID'] == 1138) {
                ?>
                <div class="post clearfix">
                    <?=$this->__component->getUserBlock()?>
                    <form action="?detail=<?=$_REQUEST['detail']?><?=($_REQUEST['view'] != 'summary') ? '&view=' . $_REQUEST['view'] : '' ?>&back_url=<?=$backUrl?>" method="POST">
                        <input type="hidden" name="action" value="add_kurator_comment" />
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
                        <?$APPLICATION->IncludeComponent(
                            'bitrix:main.file.input',
                            'drag_n_drop',
                            array(
                                'INPUT_NAME' => 'FILES_KURATOR',
                                'INPUT_VALUE' => $arComments['OTCHET_KURATOR'][0]['~PROPERTY_DOCS_VALUE'],
                                'MULTIPLE' => 'Y',
                                'MODULE_ID' => 'checkorders',
                                'MAX_FILE_SIZE' => '',
                                'ALLOW_UPLOAD' => 'A',
                                'ALLOW_UPLOAD_EXT' => '',
                            ),
                            false
                        );?>
                        <br/>
                        <button name="subaction" value="comment" class="ui-btn" type="submit">Оставить комментарий</button>
                        <br/><br/>

                        <?
                        $arControllerResh = $arParams['ENUM']['CONTROLER_RESH'][ $arElement['PROPERTY_CONTROLER_RESH_ENUM_ID'] ];
                        ?>
                        <?if ($arControllerResh['XML_ID'] != 'dop') : ?>
                            <div class="docsign-form p-0 d-inline">
                                <button class="ui-btn ui-btn-success js-sign-file-simple" data-id="<?=$_REQUEST['detail']?>" data-actionhead="kurator" id="docsign__sign-files">Снять с контроля</button>
                                <input type="hidden" class="js-signed-data-id" name="sign_data_id" />
                                <input type="hidden" class="js-signed-data" name="sign_data" />
                                <input type="hidden" name="subaction" value="accept" />
                            </div>
                        <?endif;?>
                        <?if ($arControllerResh['XML_ID'] != 'snyatie') : ?>
                            <div class="docsign-form p-0 d-inline">
                                <button class="ui-btn ui-btn-success js-sign-file-simple" data-id="<?=$_REQUEST['detail']?>" data-actionhead="kurator" id="docsign__sign-files">Отправить на доп контроль</button>
                                <input type="hidden" class="js-signed-data-id" name="sign_data_id" />
                                <input type="hidden" class="js-signed-data" name="sign_data" />
                                <input type="hidden" name="subaction" value="reject" />
                            </div>
                        <?endif;?>
                        <button name="subaction" value="zamechanie" class="ui-btn" type="submit">Замечание</button>
                    </form>
                </div>
                <?
            }
            ?>
            <br>
            <?
            foreach ($arComments['OTCHET_KURATOR'] as $key => $value) {
                ?>
                <div class="post clearfix">
                    <?=$this->__component->getUserBlock(
                        $value['PROPERTY_USER_VALUE'],
                        $value['DATE_CREATE']
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

                    if ($value['PROPERTY_ECP_VALUE'] != '') {
                        $APPLICATION->IncludeComponent(
                            'citto:filesigner',
                            'controlorders',
                            [
                                'FILES' => [$value['PROPERTY_FILE_ECP_VALUE']['ID']]
                            ],
                            false
                        );
                    }
                    ?>
                </div>
                <?
            }
            ?>
        </div>
    </div>
    <?
}
