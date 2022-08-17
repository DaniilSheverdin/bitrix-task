<?php

use Citto\Controlorders\Orders;
use Citto\Controlorders\Notify;
use Citto\Controlorders\Settings;
use Citto\Controlorders\Executors;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$isMainPage = ($sView != 'otchet_ispolnitel');

$curUserId = $GLOBALS['USER']->GetID();

if (is_null($reportId)) {
    $reportId = $_REQUEST['edit_comment'] ?? 0;
}

$bShowNewVisa = false;
// if (in_array(7770, $arPerm['ispolnitel_ids'])) {
//     $bShowNewVisa = true;
// } elseif (in_array(251527, $arPerm['ispolnitel_ids'])) {
//     $bShowNewVisa = true;
// } elseif (in_array(250530, $arPerm['ispolnitel_ids'])) {
//     $bShowNewVisa = true;
// }

$arCommentData = [];
$bStatusDraft = true;
$bStatusToVisa = false;
$bStatusToSign = false;
if ($reportId > 0) {
    $res = CIBlockElement::GetList(
        [
            'DATE_CREATE' => 'DESC'
        ],
        [
            'ACTIVE'            => 'Y',
            'ID'                => $reportId,
            'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
            'PROPERTY_PORUCH'   => (int)$_REQUEST['detail']
        ],
        false,
        false,
        $this->__component->arReportFields
    );
    if ($arReport = $res->GetNext()) {
        $arCommentData = $arReport;
        $bStatusDraft = ($arReport['PROPERTY_STATUS_ENUM_ID'] == $arParams['COMMENT_ENUM']['STATUS']['DRAFT']['ID']);
        $bStatusToVisa = ($arReport['PROPERTY_STATUS_ENUM_ID'] == $arParams['COMMENT_ENUM']['STATUS']['TOVISA']['ID']);
        $bStatusToSign = ($arReport['PROPERTY_STATUS_ENUM_ID'] == $arParams['COMMENT_ENUM']['STATUS']['TOSIGN']['ID']);
    }
}
?>
<a name="ispolnitel-<?=$reportId?>"></a>
<div class="post clearfix">
    <?=$this->__component->getUserBlock(
        $arCommentData['PROPERTY_USER_VALUE'] ?? 0,
        $arCommentData['DATE_CREATE'] ?? ''
    )?>
    <form
        action="?detail=<?=$_REQUEST['detail']?><?=(!$isMainPage) ? '&view=' . $_REQUEST['view'] : '' ?>&back_url=<?=$backUrl?>"
        class="js-add-comment-ispolnitel"
        method="POST">
        <input type="hidden" name="action" value="add_comment_ispolnitel_new" />
        <input type="hidden" name="send_report_to_sign" value="0" />
        <? if ($bExternalExecutor) : ?>
            <input type="hidden" name="send_report_to_control" value="0" />
        <? endif; ?>
        <input type="hidden" name="CURRENT_DATE_ISPOLN" value="<?=$arDetail['ELEMENT']['PROPERTY_DATE_ISPOLN_VALUE']?>" />
        <input type="hidden" name="CURRENT_ID" value="<?=$arCommentData['ID']??0?>" />
        <div class="row mb-2">
            <div class="col-5">
                <b>Дата фактического исполнения поручения</b><br/>
                <input
                    class="form-control"
                    type="text"
                    name="DATE_FACT"
                    required="required"
                    value="<?=$arCommentData['PROPERTY_DATE_FACT_VALUE']?>"
                    onclick="BX.calendar({node: this, field: this, bTime: false});"
                    />
            </div>
        </div>
        <?$APPLICATION->IncludeComponent(
            'bitrix:fileman.light_editor',
            '',
            array(
                'CONTENT' => $arCommentData['~DETAIL_TEXT'],
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
        <br/>
        <?$APPLICATION->IncludeComponent(
            'bitrix:main.file.input',
            'drag_n_drop',
            array(
                'INPUT_NAME' => 'FILES_ISPOLN',
                'INPUT_VALUE' => $arCommentData['~PROPERTY_DOCS_VALUE'],
                'MULTIPLE' => 'Y',
                'MODULE_ID' => 'checkorders',
                'MAX_FILE_SIZE' => '',
                'ALLOW_UPLOAD' => 'A',
                'ALLOW_UPLOAD_EXT' => '',
            ),
            false
        );?><br/>
        <?
        $visaTypeId = $arCommentData['PROPERTY_VISA_TYPE_ENUM_ID'];
        $visaTypeCode = $arParams['COMMENT_ENUM']['VISA_TYPE'][ $visaTypeId ]['EXTERNAL_ID'] ?? '';
        $visaTypeStr = $arCommentData['PROPERTY_VISA_TYPE_VALUE'];
        foreach ($arParams['COMMENT_ENUM']['VISA_TYPE'] as $visaTypeRow) {
            unset($arParams['COMMENT_ENUM']['VISA_TYPE'][ $visaTypeRow['ID'] ]);
        }

        $bEditVisa = true;
        if ($arCommentData['PROPERTY_ECP_VALUE'] != '') {
            $bEditVisa = false;
        }
        if (
            !empty($arCommentData['PROPERTY_CONTROLER_COMMENT_VALUE']) ||
            !empty($arCommentData['PROPERTY_COMMENT_VALUE'])
        ) {
            $bEditVisa = true;
        }

        if (!$bExternalExecutor) {
            if ($bShowNewVisa) {
                echo $this->__component->showVisaAndSignTable(
                    (int)$value['ID'],
                    (int)$arElement['ID'],
                    true
                );
            } else {
                ?>
                <div class="row">
                    <?if ($bEditVisa || !empty($arCommentData['PROPERTY_VISA_VALUE'])) : ?>
                        <div class="col-9">
                            <b>Визирующие:</b>
                            <?=$this->__component->showVisaTableEdit(
                                (array)$arCommentData['PROPERTY_VISA_VALUE'],
                                (int)$arCommentData['ID'],
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
                echo $this->__component->showSigner($arElement['ID'], $arCommentData['ID']??0);
            }
        }
        ?>
        <?if ($_REQUEST['edit_comment']): ?>
            <a href="javascript:void(0);" class="ui-btn ui-btn-primary js-subaction-add-comment" type="submit">Сохранить</a>
            <button name="subaction" value="add_comment" class="ui-btn ui-btn-primary hide" type="submit">
                <?=$reportId > 0 ? 'Сохранить' . ($bStatusDraft ? ' черновик' : '') : 'Добавить черновик'?>
            </button>
        <? else: ?>
            <button name="subaction" value="add_comment" class="ui-btn ui-btn-primary" type="submit">
                <?=$reportId > 0 ? 'Сохранить' . ($bStatusDraft ? ' черновик' : '') : 'Добавить черновик'?>
            </button>
        <? endif; ?>
        <?
        if ($arPerm['controler'] && $bExternalExecutor) {
            ?>
            <button
                class="ui-btn ui-btn-primary js-send-to-control"
                data-report-id="<?=(int)$arCommentData['ID']?>"
                data-order-id="<?=(int)$arElement['ID']?>"
                data-external="true"
                >Отправить на контроль</button>
            <?
        } elseif ($bStatusDraft) {
            ?>
            <button
                class="ui-btn ui-btn-primary js-send-nodraft"
                data-sign="Отправить на подпись"
                data-visa="Отправить на визирование и подпись"
                ><?=empty($arCommentData['PROPERTY_VISA_VALUE']) ? 'Отправить на подпись' : 'Отправить на визирование и подпись'?></button>
            <?
        }
        ?>
    </form>
</div>
<br/>
