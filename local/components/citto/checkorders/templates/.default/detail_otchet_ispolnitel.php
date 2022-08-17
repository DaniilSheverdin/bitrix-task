<?php

use Citto\Controlorders\Orders;
use Citto\Controlorders\Settings;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$isMainPage = ($sView != 'otchet_ispolnitel');

$curUserId = $GLOBALS['USER']->GetID();

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

$bExternalExecutor = (new Orders())->isExternal($arElement['ID']);

if (
    $arPerm['controler'] &&
    $arElement['PROPERTY_ACTION_ENUM_ID'] == 1136 &&
    $bExternalExecutor
) {
    $bMayAddComments = true;
}

/*
 * Подписывает по-умолчанию руководитель
 */
$iSignUser = $arResult['ISPOLNITELS'][ $arElement['PROPERTY_ISPOLNITEL_VALUE'] ]['PROPERTY_RUKOVODITEL_VALUE'];
$arDelegator = [
    'PROPERTY_RUKOVODITEL_VALUE'    => $iSignUser,
    'PROPERTY_TYPE_CODE'            => '',
];

/*
 * Если поручение делегировано сверху, то проверим тип того кто делегировал
*/
if (!empty($arElement['PROPERTY_DELEGATION_VALUE']) && (int)$arElement['PROPERTY_DELEGATION_VALUE'][0] > 0) {
    $arDelegator = $arResult['ISPOLNITELS'][ $arElement['PROPERTY_DELEGATION_VALUE'][0] ];

    /*
     * Если зампред - то подписывает сам
     */
    if (
        $arDelegator['PROPERTY_TYPE_CODE'] == 'zampred' &&
        $curUserId != $iSignUser
    ) {
        $iSignUser = $arDelegator['PROPERTY_RUKOVODITEL_VALUE'];
    }
}

if (
    (
        $curUserId == $iSignUser ||
        $curUserId == $arDelegator['PROPERTY_RUKOVODITEL_VALUE']
    ) &&
    $arElement['PROPERTY_ACTION_ENUM_ID'] == 1136
) {
    $bMayAddComments = true;
}

if (!$bMayAddComments && empty($arComments['OTCHET_ISPOLNITEL'])) {
    return;
}

$arFirstComment = $arComments['OTCHET_ISPOLNITEL'][0];

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
        $iEditId = 0;
        $bShowComments = true;
        if ($bMayAddComments) {
            $bShowComments = false;

            $bEditComment = false;
            $bEditEnabled = true;
            if (
                $arFirstComment['PROPERTY_CURRENT_USER_VALUE'] == $iSignUser &&
                (
                	$arFirstComment['PROPERTY_USER_VALUE'] == $curUserId ||
                	$arPerm['ispolnitel_implementation']
                )
            ) {
                $bEditComment = true;

                foreach ($arFirstComment['PROPERTY_VISA_VALUE'] as $visaRow) {
                    [$userId, $status, $comment, $date] = explode(':', $visaRow, 4);
                    /*
                     * Если хоть один не согласен - только добавить новый отчет.
                     */
                    if ($status == 'N') {
                        $bEditComment = false;
                        $bEditEnabled = true;
                        $bShowComments = false;
                    }
                }
            }

            if (!empty($arFirstComment['PROPERTY_COMMENT_VALUE'])) {
                $bEditComment = false;
            }

            if ($arPerm['ispolnitel_submain']) {
                $bEditComment = false;
            }

            if ($arFirstComment['PROPERTY_ECP_VALUE'] != '' && !empty($arElement['PROPERTY_WORK_INTER_STATUS_VALUE'])) {
                $bEditComment = false;
                $bEditEnabled = false;
                $bShowComments = true;
            }

            if ($arFirstComment['PROPERTY_CURRENT_USER_VALUE'] == $curUserId) {
                $bEditEnabled = true;
                $bShowComments = false;
            }

            if ($bEditComment && $_REQUEST['edit_comment'] != $arFirstComment['ID']) {
                $bEditComment = false;
                $bEditEnabled = false;
                $bShowComments = true;
                $iEditId = $arFirstComment['ID'];
            }

            if ($bExternalExecutor) {
                $bEditEnabled = true;
                $bShowComments = false;
            }

            $bOnZamestitel = (
                $arFirstComment['PROPERTY_ECP_VALUE'] != '' &&
                in_array($arDelegator['PROPERTY_TYPE_CODE'], ['zampred', 'gubernator']) &&
                $arFirstComment['PROPERTY_CURRENT_USER_VALUE'] == $arDelegator['PROPERTY_RUKOVODITEL_VALUE'] &&
                empty($arFirstComment['PROPERTY_COMMENT_VALUE']) &&
                empty($arFirstComment['PROPERTY_CONTROLER_COMMENT_VALUE'])
            );

            /*
             * Если поручение вернули, новый отчет у исполнителя
             * должен быть с пустыми полями
             */
            if (
                !empty($arFirstComment) &&
                !$bOnZamestitel &&
                // !$arPerm['ispolnitel_main'] &&
                // !$arPerm['ispolnitel_submain'] &&
                !$bEditComment &&
                $bEditEnabled &&
                (
                    // !empty($arFirstComment['PROPERTY_COMMENT_VALUE']) ||
                    !empty($arFirstComment['PROPERTY_CONTROLER_COMMENT_VALUE'])
                )
            ) {
                $arFirstComment['DETAIL_TEXT'] = '';
                $arFirstComment['~DETAIL_TEXT'] = '';
                $arFirstComment['PROPERTY_DATE_FACT_VALUE'] = '';
                $arFirstComment['PROPERTY_DOCS_VALUE'] = '';
                $arFirstComment['~PROPERTY_DOCS_VALUE'] = '';
                $arFirstComment['PROPERTY_VISA_VALUE'] = '';
            }

            if (
                !empty($arFirstComment['PROPERTY_COMMENT_VALUE']) ||
                !empty($arFirstComment['PROPERTY_CONTROLER_COMMENT_VALUE'])
            ) {
                $bEditComment = false;
                $bEditEnabled = true;
                $bShowComments = false;
            }

            if (
                $arElement['PROPERTY_WORK_INTER_STATUS_ENUM_ID'] == $arParams['ENUM']['WORK_INTER_STATUS']['TOSIGN']['ID'] &&
                $arFirstComment['PROPERTY_CURRENT_USER_VALUE'] == $curUserId
            ) {
                $bEditComment = false;
                $bEditEnabled = true;
                $bShowComments = false;
            }

            if ($bEditEnabled) {
                $bEditVisa = true;
                ?>
                <a name="ispolnitel-<?=$bEditComment?$arFirstComment['ID']:0?>"></a>
                <div class="post clearfix">
                    <?=$bEditComment && !empty($arFirstComment) ? $this->__component->getUserBlock($arFirstComment['PROPERTY_USER_VALUE'], $arFirstComment['DATE_CREATE']) : $this->__component->getUserBlock()?>
                    <form
                        action="?detail=<?=$_REQUEST['detail']?><?=(!$isMainPage) ? '&view=' . $_REQUEST['view'] : '' ?>&back_url=<?=$backUrl?>"
                        class="js-add-comment-ispolnitel"
                        data-main="<?=(($arPerm['ispolnitel_main'] || $arPerm['ispolnitel_submain']) ? 'true' : 'false')?>"
                        method="POST">
                        <input type="hidden" name="action" value="add_comment_ispolnitel" />
                        <input type="hidden" name="CURRENT_DATE_ISPOLN" value="<?=$arDetail['ELEMENT']['PROPERTY_DATE_ISPOLN_VALUE']?>" />
                        <input type="hidden" name="CURRENT_ID" value="<?=$bEditComment?$arFirstComment['ID']:0?>" />
                        <div class="row mb-2">
                            <div class="col-5">
                                <b>Дата фактического исполнения поручения</b><br/>
                                <input
                                    class="form-control"
                                    type="text"
                                    name="DATE_FACT"
                                    required="required"
                                    value="<?=$arFirstComment['PROPERTY_DATE_FACT_VALUE']?>"
                                    <?
                                    if ($bOnZamestitel) {
                                        ?>
                                        readonly
                                        <?
                                    } else {
                                        ?>
                                        onclick="BX.calendar({node: this, field: this, bTime: false});"
                                        <?
                                    }
                                    ?>
                                    />
                            </div>
                        </div>
                        <?
                        if ($bOnZamestitel) {
                            ?>
                            <textarea name="DETAIL_TEXT" readonly="readonly" class="d-none"><?=$arFirstComment['~DETAIL_TEXT']?></textarea>
                            <div><?=$arFirstComment['~DETAIL_TEXT']?></div>
                            <?
                        } else {
                            ?>
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
                                    'VIDEO_ALLOW_VIDEO' => 'N',
                                    'USE_FILE_DIALOGS' => 'N',
                                    'ID' => '',
                                    'JS_OBJ_NAME' => 'DETAIL_TEXT',
                                )
                            );?>
                            <?
                        }
                        ?>
                        <br/>

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
                            !empty($arFirstComment['PROPERTY_COMMENT_VALUE']) &&
                            $arFirstComment['PROPERTY_COMMENT_VALUE'] != '-'
                        ) {
                            ?>
                            <div class="alert bg-yellow" role="alert">
                                <b>Комментарий руководителя:</b><br/><?=$arFirstComment['PROPERTY_COMMENT_VALUE']?>
                            </div>
                            <?
                        }

                        if (
                            !empty($arFirstComment['PROPERTY_CONTROLER_COMMENT_VALUE']) &&
                            $arFirstComment['PROPERTY_CONTROLER_COMMENT_VALUE'] != '-'
                        ) {
                            ?>
                            <div class="alert alert-danger" role="alert">
                                <b>Комментарий контролера:</b><br/><?=$arFirstComment['PROPERTY_CONTROLER_COMMENT_VALUE']?>
                            </div>
                            <?
                        }

                        $visaTypeId = $arFirstComment['PROPERTY_VISA_TYPE_ENUM_ID'];
                        $visaTypeCode = $arParams['COMMENT_ENUM']['VISA_TYPE'][ $visaTypeId ]['EXTERNAL_ID'] ?? '';
                        $visaTypeStr = $arFirstComment['PROPERTY_VISA_TYPE_VALUE'];
                        foreach ($arParams['COMMENT_ENUM']['VISA_TYPE'] as $visaTypeRow) {
                            unset($arParams['COMMENT_ENUM']['VISA_TYPE'][ $visaTypeRow['ID'] ]);
                        }

                        if ($arFirstComment['PROPERTY_ECP_VALUE'] != '') {
                            $bEditVisa = false;
                        }
                        if (
                            !empty($arFirstComment['PROPERTY_CONTROLER_COMMENT_VALUE']) ||
                            !empty($arFirstComment['PROPERTY_COMMENT_VALUE'])
                        ) {
                            $bEditVisa = true;
                        }

                        if ($bExternalExecutor) {
                            $bEditVisa = false;
                        }
                        ?>
                        <div class="row">
                            <?if ($bEditVisa || !empty($arFirstComment['PROPERTY_VISA_VALUE'])) : ?>
                                <div class="col-9">
                                    <b>Визирующие:</b>
                                    <?=$this->__component->showVisaTableEdit(
                                        (array)$arFirstComment['PROPERTY_VISA_VALUE'],
                                        (int)$arFirstComment['ID'],
                                        (int)$arElement['ID'],
                                        $visaTypeCode,
                                        $bEditVisa
                                    )?>
                                </div>
                                <?if ($bEditVisa) : ?>
                                <div class="col-3">
                                    <b>Тип визирования:</b><br/>
                                    <select class="form-control" name="VISA_TYPE">
                                        <?foreach ($arParams['COMMENT_ENUM']['VISA_TYPE'] as $visaTypeRow) : ?>
                                        <option value="<?=$visaTypeRow['ID'] ?>" <?=($visaTypeRow['ID']==$visaTypeId?'selected':'')?>><?=$visaTypeRow['VALUE'] ?></option>
                                        <?endforeach;?>
                                    </select>
                                </div>
                                <?else : ?>
                                <input type="hidden" name="VISA_TYPE" value="<?=$visaTypeId ?>" />
                                <?endif;?>
                            <?endif;?>
                        </div>
                        <br/>
                        <?

                        if ($arPerm['ispolnitel_main'] || $arPerm['ispolnitel_submain'] || $bEditComment) {
                            if (
                                empty($arFirstComment['PROPERTY_CURRENT_USER_VALUE']) ||
                                $arFirstComment['PROPERTY_CURRENT_USER_VALUE'] == $iSignUser ||
                                $curUserId == $arDelegator['PROPERTY_RUKOVODITEL_VALUE']
                            ) {
                                $GLOBALS['APPLICATION']->AddHeadScript($this->GetFolder() . '/docssign.js');

                                global $userFields;
                                $arVisa = [];
                                $arVisaIds = [];
                                $arCurVisa = [];
                                if (count($arFirstComment['PROPERTY_VISA_VALUE']) > 0) {
                                    foreach ($arFirstComment['PROPERTY_VISA_VALUE'] as $vKey => $visaRow) {
                                        [$userId, $status, $comment, $date] = explode(':', $visaRow, 4);
                                        if ($status == 'Y') {
                                            $userData = $userFields($userId);
                                            $arVisa[] = $userData['FIO_INIC_REV'];
                                        }
                                        if (in_array($status, ['S', 'E'])) {
                                            $arVisaIds[] = $userId;
                                        }
                                        $arCurVisa[ $userId ] = $status;
                                    }
                                }
                                if (count($arElement['PROPERTY_REQUIRED_VISA_VALUE']) > 0) {
                                    foreach ($arElement['PROPERTY_REQUIRED_VISA_VALUE'] as $exId) {
                                        if (0 === mb_strpos($exId, 'I')) {
                                            $uId = (int)$arResult['ISPOLNITELS'][ mb_substr($exId, 1) ]['PROPERTY_RUKOVODITEL_VALUE'];
                                        } else {
                                            $uId = (int)$exId;
                                        }
                                        if (!isset($arCurVisa[ $uId ])) {
                                            $arVisaIds[] = $uId;
                                        }
                                    }
                                }

                                $signUser = $arResult['ISPOLNITELS'][ $arElement['PROPERTY_ISPOLNITEL_VALUE'] ]['PROPERTY_RUKOVODITEL_VALUE'];
                                if ($arFirstComment['PROPERTY_CURRENT_USER_VALUE'] != $signUser) {
                                    $signUser = 0;
                                }

                                if ($arFirstComment['PROPERTY_ECP_VALUE'] != '') {
                                    if (
                                        $bOnZamestitel &&
                                        $arFirstComment['PROPERTY_ECP_VALUE'] != $arDelegator['PROPERTY_RUKOVODITEL_VALUE']
                                    ) {
                                        ?><?
                                    } else {
                                        $signText = 'Подписано';
                                        if ($arFirstComment['PROPERTY_ECP_VALUE'] != 'Y' && $arFirstComment['PROPERTY_ECP_VALUE'] > 0) {
                                            $signUser = $arFirstComment['PROPERTY_ECP_VALUE'];
                                        }

                                        if (
                                            $arDelegator['PROPERTY_TYPE_CODE'] == 'gubernator' &&
                                            $arDelegator['PROPERTY_RUKOVODITEL_VALUE'] == $signUser
                                        ) {
                                            $signText = 'Согласовано';
                                        }

                                        if ($signUser > 0) {
                                            ?>
                                            <p class="mt-2"><b><?=$signText ?>:</b> <?=$this->__component->getUserFullName($signUser)?></p>
                                            <?
                                        }
                                    }
                                }

                                if (
                                    $signUser > 0 &&
                                    $arElement['PROPERTY_WORK_INTER_STATUS_ENUM_ID'] == $arParams['ENUM']['WORK_INTER_STATUS']['TOSIGN']['ID']
                                ) {
                                    $subSignText = 'Главных исполнителей';
                                    if ($arFirstComment['PROPERTY_SIGNER_VALUE'] > 0) {
                                        $subSignText = $this->__component->getUserFullName($arFirstComment['PROPERTY_SIGNER_VALUE']);
                                    }
                                    if (
                                        $arFirstComment['PROPERTY_CURRENT_USER_VALUE'] == $curUserId ||
                                        $arFirstComment['PROPERTY_SIGNER_VALUE'] == $curUserId
                                    ) {
                                        $subSignText = 'Вас';
                                    }
                                    ?>
                                    <span class="label label-success my-2">На подписи у <?=$subSignText?></span><br/><br/>
                                    <?
                                } elseif (
                                    $arDelegator['PROPERTY_TYPE_CODE'] == 'zampred' &&
                                    $arDelegator['PROPERTY_RUKOVODITEL_VALUE'] == $arFirstComment['PROPERTY_CURRENT_USER_VALUE'] &&
                                    $arElement['PROPERTY_WORK_INTER_STATUS_ENUM_ID'] == $arParams['ENUM']['WORK_INTER_STATUS']['TOSIGN']['ID']
                                ) {
                                    $subSignText = '';
                                    if ($arFirstComment['PROPERTY_CURRENT_USER_VALUE'] == $curUserId) {
                                        $subSignText = ' у Вас';
                                    }
                                    ?>
                                    <span class="label label-success my-2">На подписи<?=$subSignText?></span><br/><br/>
                                    <?
                                } elseif (
                                    $arDelegator['PROPERTY_TYPE_CODE'] == 'gubernator' &&
                                    $arDelegator['PROPERTY_RUKOVODITEL_VALUE'] == $arFirstComment['PROPERTY_CURRENT_USER_VALUE'] &&
                                    $arElement['PROPERTY_WORK_INTER_STATUS_ENUM_ID'] == $arParams['ENUM']['WORK_INTER_STATUS']['TOVISA']['ID']
                                ) {
                                    ?>
                                    <span class="label label-success my-2">На визировании: <?=$this->__component->getUserFullName($arFirstComment['PROPERTY_CURRENT_USER_VALUE'])?></span><br/><br/>
                                    <?
                                }

                                if ($arFirstComment['PROPERTY_ECP_VALUE'] != '') {
                                    $signByYou = '';
                                    if (
                                        $arDelegator['PROPERTY_TYPE_CODE'] == 'gubernator' &&
                                        $arDelegator['PROPERTY_RUKOVODITEL_VALUE'] == $curUserId
                                    ) {
                                        $signByYou = 'Согласовано Вами';
                                    }

                                    if (
                                        $arDelegator['PROPERTY_TYPE_CODE'] == 'zampred' &&
                                        $arFirstComment['PROPERTY_ECP_VALUE'] != $arDelegator['PROPERTY_RUKOVODITEL_VALUE']
                                    ) {
                                        ?><?
                                    } else {
                                        $APPLICATION->IncludeComponent(
                                            'citto:filesigner',
                                            'controlorders',
                                            [
                                                'FILES'             => [$arFirstComment['PROPERTY_FILE_ECP_VALUE']['ID']],
                                                'SIGNED_BY_YOU_MSG' => $signByYou,
                                            ],
                                            false
                                        );
                                        echo '<br/><br/>';
                                    }
                                }

                                if ($bEditComment) {
                                    echo $this->__component->showSigner($arElement['ID'], $arFirstComment['ID']??0);
                                    ?>
                                    <button name="subaction" value="add_comment" class="ui-btn ui-btn-primary" type="submit">Сохранить</button>
                                    <?
                                } elseif (!in_array($curUserId, $arVisaIds)) {
                                    $bShowReturnBtn = true;
                                    if ($arFirstComment['PROPERTY_ECP_VALUE'] != '') {
                                        $signType = 'exist';
                                        $buttonText = 'Подписать';
                                        if ($arDelegator['PROPERTY_TYPE_CODE'] == 'gubernator') {
                                            $buttonText = 'Согласовать';
                                        }
                                        if ($arDelegator['PROPERTY_TYPE_CODE'] == 'zampred') {
                                            $signType = 'simple';
                                        }
                                        ?>
                                        <div class="docsign-form mr-3 d-inline-block">
                                            <button
                                                class="ui-btn ui-btn-success js-sign-file-<?=$signType?>"
                                                data-id="<?=$_REQUEST['detail']?>"
                                                data-actionhead="ispolnitel_main"
                                                id="docsign__sign-files"
                                                data-file="<?=$arFirstComment['PROPERTY_FILE_ECP_VALUE']['ID']?>"
                                                data-visa="<?=implode(', ', $arVisa) ?>"
                                                ><?=$buttonText ?></button>
                                            <input
                                                type="hidden"
                                                class="js-signed-data-id"
                                                name="sign_data_id"
                                                value="<?=$arFirstComment['PROPERTY_FILE_ECP_VALUE']['ID']?>"
                                                />
                                            <input type="hidden" name="subaction" value="send_to_control" />
                                        </div>
                                        <?
                                    } elseif (
                                        empty($arVisaIds) &&
                                        empty($arFirstComment['PROPERTY_COMMENT_VALUE']) &&
                                        empty($arFirstComment['PROPERTY_CONTROLER_COMMENT_VALUE'])
                                    ) {
                                        ?>
                                        <div class="docsign-form mr-3 d-inline-block">
                                            <button
                                                class="ui-btn ui-btn-success js-sign-file-simple"
                                                data-id="<?=$_REQUEST['detail']?>"
                                                data-actionhead="ispolnitel_main"
                                                id="docsign__sign-files"
                                                data-visa="<?=implode(', ', $arVisa) ?>"
                                                >Подписать</button>
                                            <input
                                                type="hidden"
                                                class="js-signed-data-id"
                                                name="sign_data_id"
                                                value=""
                                                />
                                            <input type="hidden" name="subaction" value="send_to_control" />
                                        </div>
                                        <?
                                    }

                                    $bShowAddForm = false;

                                    if (
                                        !empty($arFirstComment['PROPERTY_COMMENT_VALUE']) ||
                                        !empty($arFirstComment['PROPERTY_CONTROLER_COMMENT_VALUE'])
                                    ) {
                                        $bShowReturnBtn = false;
                                        $bShowAddForm = true;
                                    }

                                    if (
                                        (empty($arFirstComment) && $arPerm['ispolnitel_main']) ||
                                        ($bShowAddForm && $arPerm['ispolnitel_main']) ||
                                        $arPerm['ispolnitel_submain']
                                    ) {
                                        ?>
                                        <button name="subaction" value="add_comment" class="ui-btn ui-btn-primary" type="submit">Добавить отчет</button>
                                        <?
                                    }

                                    if (empty($arFirstComment)) {
                                        $bShowReturnBtn = false;
                                    }

                                    if ($bShowReturnBtn) {
                                        ?>
                                        <div class="d-inline-block mr-3">
                                            <button class="ui-btn ui-btn-primary js-return">Вернуть на доработку</button>
                                        </div>
                                        <div class="js-return-form d-none">
                                            <br/><br/>
                                            <textarea name="RETURN_COMMENT" cols="50" placeholder="Причина возврата"></textarea><br/>
                                            <input type="hidden" name="RETURN_ID" value="<?=$arFirstComment['ID']?>" />
                                            <button name="subaction" value="return_work" class="ui-btn ui-btn-primary js-return-button">Вернуть на доработку</button>
                                            <button class="ui-btn ui-btn-danger js-return-cancel">Отмена</button>
                                        </div>
                                        <?
                                    }
                                }
                            } else {
                                echo $this->__component->showSigner($arElement['ID'], $arFirstComment['ID']??0);
                                ?>
                                <button name="subaction" value="add_comment" class="ui-btn ui-btn-primary" type="submit"><?=$bEditComment?'Сохранить':'Добавить отчет'?></button>
                                <?
                            }
                        } else {
                            echo $this->__component->showSigner($arElement['ID'], $arFirstComment['ID']??0);
                            ?>
                            <button name="subaction" value="add_comment" class="ui-btn ui-btn-primary" type="submit"><?=$bEditComment?'Сохранить':'Добавить отчет'?></button>
                            <?
                            if ($arPerm['controler'] && $bExternalExecutor) {
                                ?>
                                <button name="subaction" value="send_to_control" class="ui-btn ui-btn-success" type="submit">Отправить на контроль</button>
                                <?
                            }
                        }
                        ?>
                    </form>
                </div>
                <br/>
                <?
            }
        }

        if ($bShowComments || !$isMainPage) :?>
        <div>
            <?foreach ($arComments['OTCHET_ISPOLNITEL'] as $key => $value) {
                if ($bEditEnabled && $isMainPage && $key > 0) {
                    break;
                }
                if ($isMainPage && !$bEditEnabled && $key > 0) {
                    break;
                }
                ?>
                <a name="ispolnitel-<?=$value['ID'] ?>"></a>
                <div class="post clearfix">
                    <?=$this->__component->getUserBlock(
                        $value['PROPERTY_USER_VALUE'],
                        $value['DATE_CREATE']
                    )?>
                    <p><?=$value['~DETAIL_TEXT']?></p>

                    <?
                    if (count($value['PROPERTY_VISA_VALUE']) > 0) {
                        $visaTypeId = $value['PROPERTY_VISA_TYPE_ENUM_ID'];
                        $visaTypeCode = $arParams['COMMENT_ENUM']['VISA_TYPE'][ $visaTypeId ]['EXTERNAL_ID'] ?? '';
                        $visaTypeStr = $value['PROPERTY_VISA_TYPE_VALUE'];
                        ?>
                        <p class="mb-0"><b title="<?=$visaTypeStr?>">Визирующие:</b></p>
                        <?
                        echo $this->__component->showVisaTableEdit(
                            (array)$value['PROPERTY_VISA_VALUE'],
                            (int)$value['ID'],
                            (int)$arElement['ID'],
                            $visaTypeCode,
                            false
                        );
                    }

                    $signUser = $arResult['ISPOLNITELS'][ $arElement['PROPERTY_ISPOLNITEL_VALUE'] ]['PROPERTY_RUKOVODITEL_VALUE'];
                    if ($value['PROPERTY_CURRENT_USER_VALUE'] != $signUser) {
                        $signUser = 0;
                    }

                    if ($value['PROPERTY_ECP_VALUE'] != '') {
                        if (
                            $arDelegator['PROPERTY_TYPE_CODE'] == 'zampred' &&
                            $value['PROPERTY_ECP_VALUE'] != $arDelegator['PROPERTY_RUKOVODITEL_VALUE']
                        ) {
                            ?><?
                        } else {
                            $signText = 'Подписано';
                            if ($value['PROPERTY_ECP_VALUE'] != 'Y' && $value['PROPERTY_ECP_VALUE'] > 0) {
                                $signUser = $value['PROPERTY_ECP_VALUE'];
                            }

                            if (
                                $arDelegator['PROPERTY_TYPE_CODE'] == 'gubernator' &&
                                $arDelegator['PROPERTY_RUKOVODITEL_VALUE'] == $signUser
                            ) {
                                $signText = 'Согласовано';
                            }

                            if ($signUser > 0) {
                                ?>
                                <p class="my-2"><b><?=$signText ?>:</b> <?=$this->__component->getUserFullName($signUser)?></p>
                                <?
                            }
                        }
                    }

                    if ($value['ID'] == $arComments['OTCHET_ISPOLNITEL'][0]['ID']) {
                        if (
                            $signUser > 0 &&
                            $arElement['PROPERTY_WORK_INTER_STATUS_ENUM_ID'] == $arParams['ENUM']['WORK_INTER_STATUS']['TOSIGN']['ID']
                        ) {
                            $subSignText = 'Главных исполнителей';
                            if ($value['PROPERTY_SIGNER_VALUE'] > 0) {
                                $subSignText = $this->__component->getUserFullName($value['PROPERTY_SIGNER_VALUE']);
                            }
                            if (
                                $value['PROPERTY_CURRENT_USER_VALUE'] == $curUserId ||
                                $value['PROPERTY_SIGNER_VALUE'] == $curUserId
                            ) {
                                $subSignText = 'Вас';
                            }
                            ?>
                            <span class="label label-success my-2">На подписи у <?=$subSignText?></span><br/><br/>
                            <?
                        } elseif (
                            $arDelegator['PROPERTY_TYPE_CODE'] == 'zampred' &&
                            $arDelegator['PROPERTY_RUKOVODITEL_VALUE'] == $value['PROPERTY_CURRENT_USER_VALUE'] &&
                            $arElement['PROPERTY_WORK_INTER_STATUS_ENUM_ID'] == $arParams['ENUM']['WORK_INTER_STATUS']['TOSIGN']['ID']
                        ) {
                            $subSignText = '';
                            if ($value['PROPERTY_CURRENT_USER_VALUE'] == $curUserId) {
                                $subSignText = ' у Вас';
                            }
                            ?>
                            <span class="label label-success my-2">На подписи<?=$subSignText?></span><br/><br/>
                            <?
                        } elseif (
                            $arDelegator['PROPERTY_TYPE_CODE'] == 'gubernator' &&
                            $arDelegator['PROPERTY_RUKOVODITEL_VALUE'] == $value['PROPERTY_CURRENT_USER_VALUE'] &&
                            $arElement['PROPERTY_WORK_INTER_STATUS_ENUM_ID'] == $arParams['ENUM']['WORK_INTER_STATUS']['TOVISA']['ID']
                        ) {
                            ?>
                            <span class="label label-success my-2">На визировании: <?=$this->__component->getUserFullName($value['PROPERTY_CURRENT_USER_VALUE'])?></span><br/><br/>
                            <?
                        }
                    }

                    if (!empty($value['PROPERTY_DATE_FACT_VALUE'])) {
                        ?>
                        <p class="my-2"><b>Дата фактического исполнения поручения:</b> <?=$value['PROPERTY_DATE_FACT_VALUE']?></p>
                        <?
                    }

                    if ($arPerm['ispolnitel'] && !empty($value['PROPERTY_COMMENT_VALUE']) && $value['PROPERTY_COMMENT_VALUE'] != '-') {
                        ?>
                        <div class="alert bg-yellow" role="alert">
                            <b>Комментарий руководителя:</b><br/><?=$value['PROPERTY_COMMENT_VALUE']?>
                        </div>
                        <?
                    }

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
                        $signByYou = '';
                        if (
                            $arDelegator['PROPERTY_TYPE_CODE'] == 'gubernator' &&
                            $arDelegator['PROPERTY_RUKOVODITEL_VALUE'] == $curUserId
                        ) {
                            $signByYou = 'Согласовано Вами';
                        }

                        if (
                            $arDelegator['PROPERTY_TYPE_CODE'] == 'zampred' &&
                            $value['PROPERTY_ECP_VALUE'] != $arDelegator['PROPERTY_RUKOVODITEL_VALUE']
                        ) {
                            ?><?
                        } else {
                            $APPLICATION->IncludeComponent(
                                'citto:filesigner',
                                'controlorders',
                                [
                                    'FILES' => [$value['PROPERTY_FILE_ECP_VALUE']['ID']],
                                    'SIGNED_BY_YOU_MSG' => $signByYou,
                                ],
                                false
                            );
                        }
                    } elseif ($arElement['PROPERTY_WORK_INTER_STATUS_ENUM_ID'] == $arParams['ENUM']['WORK_INTER_STATUS']['TOVISA']['ID']) {
                        echo $this->__component->showSigner($arElement['ID'], $value['ID'], false);
                    }

                    if (!empty($value['PROPERTY_CONTROLER_COMMENT_VALUE']) && $value['PROPERTY_CONTROLER_COMMENT_VALUE'] != '-') {
                        ?>
                        <div class="alert alert-danger" role="alert">
                            <b>Комментарий контролера:</b><br/><?=$value['PROPERTY_CONTROLER_COMMENT_VALUE']?>
                        </div>
                        <?
                    }

                    if ($iEditId == $value['ID']) {
                        ?>
                        <br/>
                        <a href="<?=$APPLICATION->GetCurPageParam('edit_comment=' . $value['ID']) ?>#ispolnitel-<?=$value['ID'] ?>">Редактировать</a>
                        <?
                    }
                    ?>
                </div>
                <?
            }
        ?>
        </div>
        <?endif;?>
    </div>
</div>
