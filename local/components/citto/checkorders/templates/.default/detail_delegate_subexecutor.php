<?

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

if (!isset($_REQUEST['detail'])) {
    return;
}

if (empty($arPerm['ispolnitel_delegated'])) {
    return;
}

$arCurrentSubExecutor = 0;
$arCurrentSubExecutorDelegate = 0;
foreach ($arElement['PROPERTY_SUBEXECUTOR_VALUE'] as $keySE => $valueSE) {
    if ($arPerm['ispolnitel_data']['ID'] == $valueSE) {
        $arCurrentSubExecutor = $valueSE;
        $arCurrentSubExecutorDelegate = $arElement['PROPERTY_SUBEXECUTOR_DESCRIPTION'][ $keySE ];
    }
}
foreach ($arElement['PROPERTY_SUBEXECUTOR_IDS'] as $keySE => $valueSE) {
    if ($arPerm['ispolnitel_data']['ID'] == $valueSE) {
        $arCurrentSubExecutor = $valueSE;
        $arCurrentSubExecutorDelegate = $arElement['PROPERTY_SUBEXECUTOR_USERS'][ $keySE ];
    }
}

if ($arCurrentSubExecutor <= 0) {
    return;
}

$curUserId = $GLOBALS['USER']->GetID();
$arMyDelegates = [];
foreach ($arElement['~PROPERTY_DELEGATE_HISTORY_VALUE'] as $delegateRow) {
    $arDelegateRow = json_decode($delegateRow, true);
    if (($arDelegateRow['CURRENT_USER'] == $curUserId) && isset($arDelegateRow['ACCOMPLICES'])) {
        if (is_array($arDelegateRow['ACCOMPLICES'])) {
            $arMyDelegates = array_merge(
                $arMyDelegates,
                $arDelegateRow['ACCOMPLICES']
            );
        } else {
            $arMyDelegates[] = $arDelegateRow['ACCOMPLICES'];
        }
    } elseif (($arDelegateRow['CURRENT_USER'] == $curUserId) && isset($arDelegateRow['SUBEXECUTOR'])) {
        if (is_array($arDelegateRow['SUBEXECUTOR'])) {
            foreach ($arDelegateRow['SUBEXECUTOR'] as $val) {
                $arMyDelegates[] = 'DEP' . $val;
            }
        } else {
            $arMyDelegates[] = 'DEP' . $arDelegateRow['SUBEXECUTOR'];
        }
    }
}

/*
 * @todo mlyamin 28.01.21 Вынести вместе с detail_delegate
 */
$arDelegate = [];
$arFinded = [];
foreach ($arPerm['ispolnitel_data']['PROPERTY_ISPOLNITELI_VALUE'] as $userId) {
    $arDelegate[] = [
        'ID'    => $userId,
        'NAME'  => $this->__component->getUserFullName($userId),
        'TYPE'  => 'Сотрудники',
    ];
}

$arNewDelegate = [];
foreach ($arPerm['ispolnitel_ids'] as $exId) {
    $arUsersDelegate = array_merge(
        $arResult['ISPOLNITELS'][ $exId ]['PROPERTY_ZAMESTITELI_VALUE'],
        $arResult['ISPOLNITELS'][ $exId ]['PROPERTY_IMPLEMENTATION_VALUE'],
        $arResult['ISPOLNITELS'][ $exId ]['PROPERTY_ISPOLNITELI_VALUE']
    );
    $arUsersDelegate = array_unique($arUsersDelegate);
    foreach ($arUsersDelegate as $userId) {
        $arNewDelegate[ $exId ][] = [
            'ID'    => $userId,
            'NAME'  => $this->__component->getUserFullName($userId),
            'TYPE'  => 'Сотрудники',
        ];
    }
    foreach ($arResult['ISPOLNITELS'][ $exId ]['PROPERTY_ORDER_VALUE'] as $delegateType) {
        if ((int)$delegateType > 0 && isset($arResult['ISPOLNITELS'][ $delegateType ])) {
            $ispolnitel = $arResult['ISPOLNITELS'][ $delegateType ];
            if (in_array($ispolnitel['ID'], $arFinded)) {
                continue;
            }
            $arDelegate[] = [
                'ID'    => 'DEP' . $ispolnitel['ID'],
                'NAME'  => $ispolnitel['NAME'],
                'TYPE'  => $ispolnitel['PROPERTY_TYPE_VALUE'],
            ];
            $arFinded[] = $arResult['ISPOLNITELS'][ $delegateType ]['ID'];
        } elseif ((int)$delegateType < 0 && isset($arResult['ISPOLNITELS'][ ($delegateType*-1) ])) {
            $arFinded[] = ($delegateType*-1);
        } else {
            foreach ($arResult['ISPOLNITELS'] as $ispolnitel) {
                if (in_array($ispolnitel['ID'], $arFinded)) {
                    continue;
                }
                if ($ispolnitel['ID'] == $arPerm['ispolnitel_data']['ID']) {
                    continue;
                }
                // if (in_array($ispolnitel['ID'], $arPerm['ispolnitel_ids'])) {
                //     continue;
                // }
                if ($ispolnitel['PROPERTY_TYPE_CODE'] == $delegateType) {
                    if (
                        $delegateType == 'podved' &&
                        !in_array($ispolnitel['PROPERTY_PARENT_ID_VALUE'], $arPerm['ispolnitel_ids'])
                    ) {
                        continue;
                    }
                    $arDelegate[] = [
                        'ID'    => 'DEP' . $ispolnitel['ID'],
                        'NAME'  => $ispolnitel['NAME'],
                        'TYPE'  => $ispolnitel['PROPERTY_TYPE_VALUE'],
                    ];
                    $arFinded[] = $ispolnitel['ID'];
                }
            }
        }
    }
}

?>
<div class="card mb-2" style="height:auto">
    <div class="card-body">
        <form method="POST" action="?detail=<?=$_REQUEST['detail']?>&back_url=<?=$backUrl?>">
            <div class="row">
                <?
                $cntSubEx = 0;
                foreach ($arElement['PROPERTY_SUBEXECUTOR_IDS'] as $key => $exId) {
                    if (in_array($exId, $arPerm['ispolnitel_ids'])) {
                        $cntSubEx++;
                    }
                }
                foreach ($arElement['PROPERTY_SUBEXECUTOR_IDS'] as $key => $exId) {
                    if (!in_array($exId, $arPerm['ispolnitel_ids'])) {
                        continue;
                    }
                    ?>
                    <div class="col-3">
                        <b>Делегировано:</b><br/>
                        <?
                        if ($cntSubEx > 1) {
                            echo $arResult['ISPOLNITELS'][ $exId ]['NAME'];
                        }
                        ?>
                    </div>
                    <div class="col-9 order-tags js-user-selector">
                        <?if ($arElement['PROPERTY_SUBEXECUTOR_USERS'][ $key ] > 0) :?>
                        <div class="order-tag">
                            <span bx-tooltip-user-id="<?=$arElement['PROPERTY_SUBEXECUTOR_USERS'][ $key ]?>"><?=$this->__component->getUserFullName($arElement['PROPERTY_SUBEXECUTOR_USERS'][ $key ])?></span>
                            <input name="SUBEXECUTOR_USER[<?=$exId?>]" type="hidden" value="<?=$arElement['PROPERTY_SUBEXECUTOR_USERS'][ $key ]?>" />
                        </div>
                        <?endif;?>
                        <div class="d-none order-tag">
                            <span bx-tooltip-user-id=""></span>
                            <input class="dummy" name="SUBEXECUTOR_USER[<?=$exId?>]" type="hidden" disabled />
                        </div>
                        <a
                            href="javascript:void(0);"
                            class="ui-btn ui-btn-xs ui-btn-primary ui-btn-icon-business js-delegate-add"
                            data-single="true"
                            data-user="<?=$arElement['PROPERTY_SUBEXECUTOR_USERS'][ $key ]?>"
                            data-container="#SELECT_USER_<?=$exId?>"
                            title="Изменить исполнителя"></a>
                    </div>
                    <?
                }
                ?>
                <?/*
                <div class="col-3">
                    <b>Делегировано:</b>
                </div>
                <div class="col-9">
                    <?if ($arCurrentSubExecutorDelegate > 0) :?>
                    <div class="order-tag">
                        <span bx-tooltip-user-id="<?=$arCurrentSubExecutorDelegate?>"><?=$this->__component->getUserFullName($arCurrentSubExecutorDelegate)?></span>
                        <input name="SUBEXECUTOR_USER[<?=$arCurrentSubExecutor?>]" type="hidden" value="<?=$arCurrentSubExecutorDelegate?>" />
                    </div>
                    <?endif;?>
                    <div class="d-none order-tag">
                        <span bx-tooltip-user-id=""></span>
                        <input class="dummy" name="SUBEXECUTOR_USER[<?=$arCurrentSubExecutor?>]" type="hidden" disabled />
                    </div>
                    <a
                        href="javascript:void(0);"
                        class="ui-btn ui-btn-xs ui-btn-primary ui-btn-icon-business js-delegate-add"
                        data-single="true"
                        data-user="<?=$arCurrentSubExecutorDelegate?>"
                        data-container="#SELECT_USER"
                        title="Изменить исполнителя"></a>
                </div>
                */?>
            </div>
            <div class="row">
                <div class="col-3">
                    <b>Соисполнители:</b>
                </div>
                <div class="col-9 order-tags js-user-selector">
                    <?
                    foreach ($arElement['PROPERTY_ACCOMPLICES_VALUE'] as $accomplice) {
                        $bDelete = false;
                        if ($accomplice == $curUserId) {
                            $bDelete = false;
                        } elseif (in_array($accomplice, $arMyDelegates)) {
                            $bDelete = true;
                        }
                        ?>
                        <div class="order-tag">
                            <span bx-tooltip-user-id="<?=$accomplice?>"><?=$this->__component->getUserFullName($accomplice) ?></span>
                            <?if ($bDelete) : ?>
                            <i class="js-delegate-remove">&times;</i>
                            <input name="DELEGATE_ACCOMPLICES[]" type="hidden" class="DELEGATE_ACCOMPLICES" value="<?=$accomplice?>" />
                            <?endif;?>
                        </div>
                        <?
                    }

                    foreach ($arElement['PROPERTY_SUBEXECUTOR_IDS'] as $accomplice) {
                        $bDelete = false;
                        if (in_array('DEP' . $accomplice, $arMyDelegates)) {
                            $bDelete = true;
                        }
                        ?>
                        <div class="order-tag">
                            <span><?=$arResult['ISPOLNITELS'][ $accomplice ]['NAME']?></span>
                            <?if ($bDelete) : ?>
                            <i class="js-delegate-remove">&times;</i>
                            <input name="DELEGATE_ACCOMPLICES[]" type="hidden" class="DELEGATE_ACCOMPLICES" value="DEP<?=$accomplice?>" />
                            <?endif;?>
                        </div>
                        <?
                    }
                    ?>
                    <div class="d-none order-tag">
                        <span bx-tooltip-user-id=""></span>
                        <i class="js-delegate-remove">&times;</i>
                        <input name="DELEGATE_ACCOMPLICES[]" type="hidden" class="dummy DELEGATE_ACCOMPLICES" disabled />
                    </div>
                    <a
                        href="javascript:void(0);"
                        class="ui-btn ui-btn-xs ui-btn-primary ui-btn-icon-add js-delegate-add"
                        data-single="false"
                        title="Добавить соисполнителя"></a>
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-3">
                    <b>Комментарий:</b>
                </div>
                <div class="col-9">
                    <textarea name="DELEGATE_COMMENT" placeholder="Комментарий" cols="70" rows="1"></textarea>
                </div>
            </div>
            <button class="ui-btn js-accept-ispolnitel" type="submit" name="action" value="accept_subexecutor" data-field="SUBEXECUTOR_USER[<?=$arCurrentSubExecutor?>]">Делегировать</button>
        </form>
        <div class="d-none" id="SELECT_USER">
            <select class="form-control" name="SELECT_USER">
                <?foreach ($arDelegate as $value) : ?>
                <option value="<?=$value['ID']?>">[<?=$value['TYPE']?>] <?=$value['NAME']?></option>
                <?endforeach;?>
            </select>
        </div>
        <?
        foreach ($arNewDelegate as $exId => $exUsers) {
            ?>
            <div class="d-none" id="SELECT_USER_<?=$exId?>">
                <select class="form-control" name="SELECT_USER_<?=$exId?>">
                    <?foreach ($exUsers as $value) : ?>
                    <option value="<?=$value['ID']?>"><?=$value['NAME']?></option>
                    <?endforeach;?>
                </select>
            </div>
            <?
        }
        ?>
        <div class="d-none" id="SELECT_SIMPLE_USER">
            <select class="form-control" name="SELECT_SIMPLE_USER">
                <?foreach ($arDelegate as $value) : ?>
                    <?
                    if (0 === mb_strpos($value['ID'], 'DEP')) {
                        continue;
                    }
                    ?>
                    <option value="<?=$value['ID']?>"><?=$value['NAME']?></option>
                <?endforeach;?>
            </select>
        </div>
    </div>
</div>
