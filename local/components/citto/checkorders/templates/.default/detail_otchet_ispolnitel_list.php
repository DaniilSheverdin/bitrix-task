<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$isMainPage = ($sView != 'otchet_ispolnitel');

$curUserId = $GLOBALS['USER']->GetID();

if (!$isMainPage) {
    ?>
    <div class="box box-primary col-10 col-xl-12">
        <div class="box-header with-border">
            <h3 class="box-title"><b><?=(count($arDetail['POSITION_DATA']) > 0) ? 'Позиция:' : 'Отчет исполнителя:' ?></b></h3>
        </div>
        <div class="box-body box-profile">
    <?
}

$bShowNewVisa = false;
// if (in_array(7770, $arPerm['ispolnitel_ids'])) {
//     $bShowNewVisa = true;
// } elseif (in_array(251527, $arPerm['ispolnitel_ids'])) {
//     $bShowNewVisa = true;
// } elseif (in_array(250530, $arPerm['ispolnitel_ids'])) {
//     $bShowNewVisa = true;
// }

$break = false;
$arPermisions = [];
$arMayEditReports = [];
foreach ($arPerm['ispolnitel_ids'] as $executorId) {
    $arExecutor = $arResult['ISPOLNITELS'][ $executorId ];
    $arPermisions = array_merge(
        $arPermisions,
        [$arExecutor['PROPERTY_RUKOVODITEL_VALUE']],
        $arExecutor['PROPERTY_ZAMESTITELI_VALUE'],
        $arExecutor['PROPERTY_ISPOLNITELI_VALUE'],
        $arExecutor['PROPERTY_IMPLEMENTATION_VALUE']
    );

    if ($executorId == $arElement['PROPERTY_ISPOLNITEL_VALUE']) {
        $arMayEditReports = array_merge(
            $arMayEditReports,
            [$arExecutor['PROPERTY_RUKOVODITEL_VALUE']],
            $arExecutor['PROPERTY_ZAMESTITELI_VALUE']
        );
    }
}

foreach ($arComments['OTCHET_ISPOLNITEL'] as $key => $value) {
    if ($isMainPage && $break) {
        break;
    }
    $bStatusSign = ($value['PROPERTY_STATUS_ENUM_ID'] == $arParams['COMMENT_ENUM']['STATUS']['SIGN']['ID']);
    $bStatusDraft = ($value['PROPERTY_STATUS_ENUM_ID'] == $arParams['COMMENT_ENUM']['STATUS']['DRAFT']['ID']);
    $bStatusToVisa = ($value['PROPERTY_STATUS_ENUM_ID'] == $arParams['COMMENT_ENUM']['STATUS']['TOVISA']['ID']);
    $bStatusToSign = ($value['PROPERTY_STATUS_ENUM_ID'] == $arParams['COMMENT_ENUM']['STATUS']['TOSIGN']['ID']);

    if (
        !$bExternalExecutor &&
        $bStatusDraft &&
        !in_array($curUserId, $arPermisions)
    ) {
        continue;
    }

    if (
        !$isMainPage &&
        $bStatusDraft &&
        !$break &&
        $value['PROPERTY_USER_VALUE'] != $curUserId
    ) {
        ?>
        <div class="alert alert-danger" role="alert">
            В поручении есть черновик отчета. <a href="<?=$APPLICATION->GetCurPageParam('show_drafts')?>">Показать</a>
        </div>
        <?
        continue;
    }
    if (
        $bStatusDraft &&
        !isset($_REQUEST['show_drafts']) &&
        $value['PROPERTY_USER_VALUE'] != $curUserId
    ) {
        continue;
    }
    $break = true;
    if (isset($_REQUEST['edit_comment']) && $_REQUEST['edit_comment'] == $value['ID']) {
        require(__DIR__ . '/detail_otchet_ispolnitel_edit.php');
    } else {
        ?>
        <a name="ispolnitel-<?=$value['ID'] ?>"></a>
        <div class="post clearfix <?=$bStatusDraft?'bg-gray-light p-2 js-draft':''?>">
            <?=$bStatusDraft?'<b style="color:red">[Черновик]</b><br/><br/>':''?>
            <?=$this->__component->getUserBlock(
                $value['PROPERTY_USER_VALUE'],
                $value['DATE_CREATE']
            )?>
            <p><?=$value['~DETAIL_TEXT']?></p>
            <?

            if (!empty($value['PROPERTY_DATE_FACT_VALUE'])) {
                ?>
                <p class="my-2"><b>Дата фактического исполнения поручения:</b> <?=$value['PROPERTY_DATE_FACT_VALUE']?></p>
                <?
            }

            if ($bShowNewVisa) {
                echo $this->__component->showVisaAndSignTable(
                    (int)$value['ID'],
                    (int)$arElement['ID'],
                    false
                );
            } else {
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
            }

            if (
                ($arPerm['ispolnitel'] || $arPerm['controler']) &&
                !empty($value['PROPERTY_COMMENT_VALUE']) &&
                $value['PROPERTY_COMMENT_VALUE'] != '-'
            ) {
                ?>
                <div class="alert bg-yellow" role="alert">
                    <b>Комментарий руководителя:</b><br/>
                    <?=$value['PROPERTY_COMMENT_VALUE']?>
                </div>
                <?
            }

            if (
                !empty($value['PROPERTY_CONTROLER_COMMENT_VALUE']) &&
                $value['PROPERTY_CONTROLER_COMMENT_VALUE'] != '-'
            ) {
                ?>
                <div class="alert alert-danger" role="alert">
                    <b>Комментарий контролера:</b><br/>
                    <?=$value['PROPERTY_CONTROLER_COMMENT_VALUE']?>
                </div>
                <?
            }

            if (count($value['PROPERTY_DOCS_VALUE']) > 0) {
                ?>
                <p>
                    <b>Документы:</b>
                    <?
                    foreach ($value['PROPERTY_DOCS_VALUE'] as $arFile) {
                        ?>
                        <div>
                            <?=$arFile['ORIGINAL_NAME']?> <a href="<?=$arFile['SRC']?>" target="_blank">Скачать</a>
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
                        'FILES' => [$value['PROPERTY_FILE_ECP_VALUE']['ID']],
                        'SIGNED_BY_YOU_MSG' => $signByYou,
                    ],
                    false
                );
                if (!$bShowNewVisa && !empty($arElement['PROPERTY_WORK_INTER_STATUS_ENUM_ID'])) {
                    echo $this->__component->showSigner($arElement['ID'], $value['ID'], false);
                }
            } elseif (!$bShowNewVisa && !$bExternalExecutor) {
                echo $this->__component->showSigner($arElement['ID'], $value['ID'], false);
            }

            if (
                (
                    $bStatusDraft ||
                    $bStatusToVisa ||
                    $bStatusToSign ||
                    (empty($value['PROPERTY_VISA_VALUE']) && $bStatusToSign)
                ) && (
                    $value['PROPERTY_USER_VALUE'] == $curUserId ||
                    in_array($curUserId, $arMayEditReports) ||
                    $arPerm['ispolnitel_implementation']
                )
            ) {
                ?>
                <br/>
                <?
                if (
                    empty($value['PROPERTY_CONTROLER_COMMENT_VALUE']) &&
                    (
                        !$bStatusSign ||
                        in_array($curUserId, $arMayEditReports)
                    )
                ) {
                    ?>
                    <a class="ui-btn ui-btn-success" href="<?=$APPLICATION->GetCurPageParam('edit_comment=' . $value['ID']) ?>#ispolnitel-<?=$value['ID'] ?>">Редактировать</a>
                    <?
                }

                if ($arPerm['controler'] && $bExternalExecutor) {
                    ?>
                    <button
                        class="ui-btn ui-btn-primary js-send-to-control"
                        data-report-id="<?=(int)$value['ID']?>"
                        data-order-id="<?=(int)$arElement['ID']?>"
                        data-external="true"
                        >Отправить на контроль</button>
                    <?
                } elseif ($bStatusDraft) {
                    ?>
                    <button
                        class="ui-btn ui-btn-primary js-undraft"
                        data-report-id="<?=$value['ID']?>"
                        data-order-id="<?=$arElement['ID']?>"
                        ><?=empty($value['PROPERTY_VISA_VALUE']) ? 'Отправить на подпись' : 'Отправить на визирование и подпись'?></button>
                    <?
                }
            }

            $arDelegator = [];
            if (!empty($arElement['PROPERTY_DELEGATION_VALUE']) && (int)$arElement['PROPERTY_DELEGATION_VALUE'][0] > 0) {
                $arDelegator = $arResult['ISPOLNITELS'][ $arElement['PROPERTY_DELEGATION_VALUE'][0] ];
            }

            if (
                (
                    $arPerm['ispolnitel_data']['ID'] == $arElement['PROPERTY_ISPOLNITEL_VALUE'] &&
                    ($arPerm['ispolnitel_main'] || $arPerm['ispolnitel_submain']) &&
                    (
                        $value['PROPERTY_CURRENT_USER_VALUE'] == $curUserId ||
                        $arPerm['ispolnitel_data']['PROPERTY_RUKOVODITEL_VALUE'] == $value['PROPERTY_CURRENT_USER_VALUE'] ||
                        $arPerm['ispolnitel_data']['PROPERTY_RUKOVODITEL_VALUE'] == $curUserId
                    ) &&
                    !empty($arElement['PROPERTY_WORK_INTER_STATUS_ENUM_ID'])
                ) || (
                    $value['PROPERTY_CURRENT_USER_VALUE'] == $arDelegator['PROPERTY_RUKOVODITEL_VALUE'] &&
                    $value['PROPERTY_CURRENT_USER_VALUE'] == $curUserId &&
                    !empty($arElement['PROPERTY_WORK_INTER_STATUS_ENUM_ID'])
                ) || (
                    $arDelegator['ID'] == 250902 && // Гремякова
                    $curUserId == 3751 && // Марков
                    $value['PROPERTY_CURRENT_USER_VALUE'] == $curUserId &&
                    !empty($arElement['PROPERTY_WORK_INTER_STATUS_ENUM_ID'])
                ) || (
                    $arDelegator['ID'] == 250892 && // Федорищев
                    $curUserId == 398 && // Шерин
                    $value['PROPERTY_CURRENT_USER_VALUE'] == $curUserId &&
                    !empty($arElement['PROPERTY_WORK_INTER_STATUS_ENUM_ID'])
                )
            ) {
                $GLOBALS['APPLICATION']->AddHeadScript($this->GetFolder() . '/docssign.js');

                $arVisaIds = [];
                if (count($value['PROPERTY_VISA_VALUE']) > 0) {
                    foreach ($value['PROPERTY_VISA_VALUE'] as $vKey => $visaRow) {
                        [$userId, $status, $comment, $date] = explode(':', $visaRow, 4);
                        if (
                            0 === mb_strpos($userId, 'SIGN') &&
                            str_replace('SIGN', '', $userId) == $value['PROPERTY_CURRENT_USER_VALUE'] &&
                            empty($arVisaIds)
                        ) {
                            break;
                        }
                        if (in_array($status, ['S', 'E'])) {
                            $arVisaIds[] = $userId;
                        }
                    }
                }

                if (
                    empty($arVisaIds) &&
                    empty($value['PROPERTY_CONTROLER_COMMENT_VALUE'])
//                    empty($value['PROPERTY_COMMENT_VALUE'])
                ) {
                    ?>
                    <div class="mr-3 d-inline-block">
                        <button
                            class="ui-btn ui-btn-success js-send-to-control"
                            data-report-id="<?=(int)$value['ID']?>"
                            data-order-id="<?=(int)$arElement['ID']?>"
                            >Подписать</button>
                    </div>
                    <div class="d-inline-block mr-3">
                        <button class="ui-btn ui-btn-primary js-return">Вернуть на доработку</button>
                    </div>
                    <div class="js-return-form d-none" data-new="true">
                        <textarea
                            name="RETURN_COMMENT"
                            placeholder="Причина возврата"
                            class="form-control"
                            required></textarea>
                        <input type="hidden" name="ORDER_ID" value="<?=$arElement['ID']?>" />
                        <input type="hidden" name="RETURN_ID" value="<?=$value['ID']?>" />
                    </div>
                    <?
                }
            }
            ?>
        </div>
        <?
    }
}

if (!$isMainPage) {
    ?>
        </div>
    </div>
    <?
}
