<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$curUserId = $GLOBALS['USER']->GetID();

if ($arPerm['controler'] || $arPerm['kurator'] || $GLOBALS['USER']->IsAdmin()) {
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
                    <form action="?detail=<?=$_REQUEST['detail']?><?=($_REQUEST['view'] != 'summary') ? '&view=' . $_REQUEST['view'] : '' ?>&back_url=<?=$backUrl?>" method="POST">
                        <input type="hidden" name="action" value="add_kurator_comment" />
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
                        <button
                            name="subaction"
                            value="zamechanie"
                            class="ui-btn ui-btn-danger js-kurator-zamechanie"
                            type="submit"
                            >Замечание</button>
                    </form>
                    <div class="js-kurator-zamechanie-form d-none" data-new="true">
                        <textarea
                            name="RETURN_COMMENT"
                            placeholder="Комментарий"
                            class="form-control"
                            required></textarea>
                    </div>
                </div>
                <?
            }
            ?>
            <br />
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
