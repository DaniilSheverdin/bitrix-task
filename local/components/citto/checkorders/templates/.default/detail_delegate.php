<?

use Bitrix\Main\Loader;
use Citto\ControlOrders\Orders;
use Citto\ControlOrders\Settings;
use Bitrix\Highloadblock\HighloadBlockTable as HLTable;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$curUserId = $GLOBALS['USER']->GetID();

$isMainPage = ($sView != 'otchet_ispolnitel');
$buttonText = $isMainPage ? 'Принять на исполнение' : 'Передать на исполнение';
$bHideEdit = false;

if (!empty($arElement['PROPERTY_DELEGATE_USER_VALUE'])) {
    $buttonText = 'Делегировать';
    $bHideEdit = true;
}

if ($arElement['PROPERTY_ACTION_ENUM_ID'] == Settings::$arActions['WORK']) {
    $buttonText = 'Делегировать';
    $bHideEdit = true;
}

if (!isset($_REQUEST['detail'])) {
    return;
}

$bDelegateOther = (
    !empty($arPerm['ispolnitel_data']['PROPERTY_ORDER_VALUE']) &&
    $arElement['PROPERTY_ACTION_ENUM_ID'] == 1135
);

if (!$bDelegateOther && empty($arPerm['ispolnitel_delegated'])) {
    return;
}

$arDelegate = [];
if ($bDelegateOther) {
    if ($arElement['PROPERTY_ACTION_ENUM_ID'] == Settings::$arActions['NEW']) {
        $buttonText = 'Принять на исполнение';
        $bHideEdit = false;
    }

    /*
     * @todo mlyamin 28.01.21 Вынести вместе с detail_delegate_subexecutor
     */
    $arDelegate = [];
    $arFinded = [];
    foreach ($arPerm['ispolnitel_data']['PROPERTY_ISPOLNITELI_VALUE'] as $userId) {
        $arDelegate[] = [
            'ID'        => 'user_' . $userId,
            'NAME'      => $this->__component->getUserFullName($userId),
            'TYPE'      => 'Сотрудники',
            'TYPE_CODE' => 'USER',
        ];
    }
    foreach ($arPerm['ispolnitel_data']['PROPERTY_ORDER_VALUE'] as $delegateType) {
        if ((int)$delegateType > 0 && isset($arResult['ISPOLNITELS'][ $delegateType ])) {
            $ispolnitel = $arResult['ISPOLNITELS'][ $delegateType ];
            if (in_array($ispolnitel['ID'], $arFinded)) {
                continue;
            }
            $arDelegate[] = [
                'ID'        => $ispolnitel['ID'],
                'NAME'      => $ispolnitel['NAME'],
                'TYPE'      => $ispolnitel['PROPERTY_TYPE_VALUE'],
                'TYPE_CODE' => $ispolnitel['PROPERTY_TYPE_CODE'],
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
                if ($ispolnitel['PROPERTY_TYPE_CODE'] == $delegateType) {
                    if (
                        $delegateType == 'podved' &&
                        !in_array($ispolnitel['PROPERTY_PARENT_ID_VALUE'], $arPerm['ispolnitel_ids'])
                    ) {
                        continue;
                    }
                    $arDelegate[] = [
                        'ID'        => $ispolnitel['ID'],
                        'NAME'      => $ispolnitel['NAME'],
                        'TYPE'      => $ispolnitel['PROPERTY_TYPE_VALUE'],
                        'TYPE_CODE' => $ispolnitel['PROPERTY_TYPE_CODE'],
                    ];
                    $arFinded[] = $ispolnitel['ID'];
                }
            }
        }
    }
}

$bShowNewAccepting = false;
if ($arElement['PROPERTY_ACTION_ENUM_ID'] == Settings::$arActions['NEW']) {
    if (in_array(7770, $arPerm['ispolnitel_ids'])) {
        $bShowNewAccepting = true;
    } elseif (in_array(251527, $arPerm['ispolnitel_ids'])) {
        $bShowNewAccepting = true;
    } elseif (in_array(250530, $arPerm['ispolnitel_ids'])) {
        $bShowNewAccepting = true;
    }
}
$bShowNewAccepting = true;

$arResolutions = [];
if ($bShowNewAccepting) {
    Loader::includeModule('highloadblock');
    $hlblock = HLTable::getById($this->__component->hlblock['Resolution'])->fetch();
    $entity = HLTable::compileEntity($hlblock);
    $entityDataClass = $entity->getDataClass();

    $rsData   = $entityDataClass::getList([
        'filter' => [
            'UF_ORDER'      => $arElement['ID'],
            '!UF_APPROVE'   => [
                $this->__component->arResolutionStatus['Y']['ID'],
                $this->__component->arResolutionStatus['D']['ID'],
            ],
        ],
        'order'  => [
            'UF_DATE' => 'DESC',
        ],
    ]);
    while ($arRes = $rsData->fetch()) {
        $arResolutions[] = $arRes;
    }
}

$maxDelegateDate = (new Orders())->getSrok($arElement['ID'], (int)$arElement['PROPERTY_DELEGATE_USER_VALUE']);
$arFormClasses = [
    'js-delegate-form',
];

$arUserList = [];
$bMyResolution = false;
?>
<div class="card mb-2 h-auto">
    <div class="card-body">
        <?
        foreach ($arResolutions as $arRes) {
            if ($arRes['UF_AUTHOR'] == $curUserId) {
                $bMyResolution = true;
            }
            ?>
            <div class="alert alert-secondary" role="alert">
                <b>Проект резолюции от <?=$arRes['UF_DATE']->format('d.m.Y H:i:s')?>:</b><br/>
                <b>Отправил проект:</b> <?=$this->__component->getUserFullName($arRes['UF_AUTHOR'])?><br/>
                <b>Исполнитель:</b>
                    <?
                    if (false === mb_strpos($arRes['UF_ISPOLNITEL'], 'DEP')) {
                        $userName = $this->__component->getUserFullName($arRes['UF_ISPOLNITEL']);
                    } else {
                        $userName = $arResult['ISPOLNITELS'][ str_replace('DEP', '', $arRes['UF_ISPOLNITEL']) ]['NAME'];
                    }
                    echo $userName;
                    $arUserList[ $arRes['UF_ISPOLNITEL'] ] = $userName;
                    ?>
                    <br/>
                <? if (!empty($arRes['UF_SROK'])) : ?>
                <b>Срок для исполнителя:</b> <?=$arRes['UF_SROK']?><br/>
                <?endif;?>
                <?
                $arSubExec = json_decode($arRes['UF_SUBEXECUTOR'], true);
                foreach ($arSubExec as $key => $value) {
                    $arSubExec[ $value ] = $value;
                    unset($arSubExec[ $key ]);
                }
                $arSubExec = array_map(function ($id) use ($arResult) {
                    if (false === mb_strpos($id, 'DEP')) {
                        return $this->__component->getUserFullName($id);
                    } else {
                        return $arResult['ISPOLNITELS'][ str_replace('DEP', '', $id) ]['NAME'];
                    }
                }, $arSubExec);
                $arUserList = $arUserList + $arSubExec;
                ?>
                <? if (!empty($arSubExec)) : ?>
                    <b>Соисполнители:</b> <?=implode(', ', $arSubExec);?><br/>
                <? endif;?>
                <? if (!empty($arRes['UF_COMMENT'])) : ?>
                    <b>Комментарий:</b> <?=$arRes['UF_COMMENT']?><br/>
                <? endif; ?>

                <?
                if (!empty($arRes['UF_REJECT_COMMENT'])) {
                    ?>
                    <br/>
                    <div class="alert bg-yellow" role="alert">
                        <b>Проект отклонён:</b><br/>
                        <?=$arRes['UF_REJECT_COMMENT']?>
                    </div>
                    <?
                }

                ?>
                <br/>
                <div class="js-new-ispolnitel-form">
                    <?

                    if (
                        ($arPerm['ispolnitel_main'] || $arPerm['ispolnitel_submain']) &&
                        $arRes['UF_APPROVE'] == $this->__component->arResolutionStatus['E']['ID']
                    ) {
                        ?>
                        <input
                            class="ui-btn ui-btn-xs ui-btn-success js-accept-new-ispolnitel"
                            type="button"
                            data-id="<?=$arRes['ID']?>"
                            data-value="Y"
                            value="Согласовать" />
                        <input
                            class="ui-btn ui-btn-xs ui-btn-danger js-accept-new-ispolnitel"
                            type="button"
                            data-id="<?=$arRes['ID']?>"
                            data-value="N"
                            value="Отклонить" />
                        <?
                    }

                    if (
                        $arPerm['ispolnitel_implementation'] &&
                        $arRes['UF_AUTHOR'] == $curUserId
                    ) {
                        ?>
                        <input
                            class="ui-btn ui-btn-xs ui-btn-danger js-accept-new-ispolnitel"
                            type="button"
                            data-id="<?=$arRes['ID']?>"
                            data-value="D"
                            value="Отозвать" />
                        <?
                    }

                    if (
                        ($arPerm['ispolnitel_implementation'] && $arRes['UF_AUTHOR'] == $curUserId) ||
                        $arPerm['ispolnitel_main'] ||
                        $arPerm['ispolnitel_submain']
                    ) {
                        ?>
                        <input
                            class="ui-btn ui-btn-xs ui-btn-primary js-edit-new-ispolnitel"
                            type="button"
                            data-id="<?=$arRes['ID']?>"
                            data-ispolnitel="<?=$arRes['UF_ISPOLNITEL']?>"
                            data-srok="<?=$arRes['UF_SROK']?>"
                            data-subexecutor="<?=rawurlencode($arRes['UF_SUBEXECUTOR'])?>"
                            data-comment="<?=$arRes['UF_COMMENT']?>"
                            value="Редактировать" />
                        <?
                    }
                    ?>
                </div>
                <div class="js-new-ispolnitel-form-cancel d-none">
                    <?
                    if (
                        ($arPerm['ispolnitel_implementation'] && $arRes['UF_AUTHOR'] == $curUserId) ||
                        $arPerm['ispolnitel_main'] ||
                        $arPerm['ispolnitel_submain']
                    ) {
                        ?>
                        <input
                            class="ui-btn ui-btn-xs ui-btn-danger js-edit-new-ispolnitel-cancel"
                            type="button"
                            value="Отменить редактирование" />
                        <?
                    }
                    ?>
                </div>
            </div>
            <?
        }

        if (!empty($arResolutions)) {
            if (
                (
                    $arPerm['ispolnitel_implementation'] &&
                    $bShowNewAccepting &&
                    $bMyResolution
                ) || (
                    !$arPerm['ispolnitel_implementation']
                )
            ) {
                $arFormClasses[] = 'd-none';
                $arFormClasses[] = 'mt-4';
            }
        }
        ?>
        <script type="text/javascript">
            var userList = <?=json_encode($arUserList);?>;
        </script>
        <form method="POST" action="?detail=<?=$_REQUEST['detail']?>&back_url=<?=$backUrl?>" class="<?=implode(' ', $arFormClasses)?>">
            <?
            if (!$bHideEdit && $bDelegateOther && !empty($arDelegate)) {
                $dataField = 'DELEGATE_PORUCH';
                ?>
                <div class="row">
                    <div class="col-3">
                        <b>Исполнитель:</b>
                    </div>
                    <div class="col-9">
                        <select
                            class="form-control js-delegate-poruch select2-users"
                            name="DELEGATE_PORUCH"
                            required="required">
                            <option value="">(Не выбран)</option>
                            <?
                            foreach ($arDelegate as $value) {
                                if ($value['TYPE_CODE'] == 'podved') {
                                    continue;
                                }
                                ?>
                                <option value="<?=$value['ID']?>">[<?=$value['TYPE']?>] <?=$value['NAME']?></option>
                                <?
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <?
                if ($arElement['PROPERTY_DATE_ISPOLN_VALUE'] != $this->__component->disableSrokDate) : ?>
                <div class="row mt-2">
                    <div class="col-3">
                        <b>Срок для исполнителя:</b>
                    </div>
                    <div class="col-3">
                        <span class="<?=$bHideEdit?'js-hide-edit':'d-none'?>"><?=$maxDelegateDate?></span>
                        <input
                            class="form-control <?=$bHideEdit?'js-hide-edit d-none':''?>"
                            type="text"
                            name="DELEGATE_SROK"
                            required="required"
                            value="<?=$maxDelegateDate?>"
                            onclick="BX.calendar({node: this, field: this, bTime: false});"
                            />
                    </div>
                </div>
                <? endif; ?>

                <div class="row mt-2">
                    <div class="col-3">
                        <b>Соисполнители:</b> <a class="ui-btn ui-btn-xs ui-btn-primary ui-btn-icon-add js-add-subexecutor" data-field="DELEGATE_SUBEXECUTOR[]"></a>
                    </div>
                    <div class="col-9">
                        <div class="subexecutor mb-2 row">
                            <div class="col-12">
                                <select class="form-control js-delegate-poruch select2-users" name="DELEGATE_SUBEXECUTOR[]">
                                    <option value="">(Не выбран)</option>
                                    <?foreach ($arDelegate as $value) : ?>
                                    <option value="<?=$value['ID']?>">[<?=$value['TYPE']?>] <?=$value['NAME']?></option>
                                    <?endforeach;?>
                                </select>
                            </div>
                            <div class="col-1 d-none pl-1 m-auto">
                                <a href="#" class="js-delete-subexecutor ui-btn ui-btn-xs ui-btn-icon-remove"></a>
                            </div>
                        </div>
                    </div>
                </div>
                <?
            } else {
                $dataField = 'DELEGATE_USER';
                ?>
                <div class="row">
                    <div class="col-3">
                        <b>Исполнитель:</b>
                    </div>
                    <div class="col-9 order-tags js-user-selector">
                        <?if ($arElement['PROPERTY_DELEGATE_USER_VALUE'] > 0) :?>
                        <div class="order-tag">
                            <span bx-tooltip-user-id="<?=$arElement['PROPERTY_DELEGATE_USER_VALUE']?>"><?=$this->__component->getUserFullName($arElement['PROPERTY_DELEGATE_USER_VALUE'])?></span>
                            <input name="DELEGATE_USER" type="hidden" value="<?=(int)$arElement['PROPERTY_DELEGATE_USER_VALUE']?>" />
                        </div>
                        <?endif;?>
                        <div class="d-none order-tag">
                            <span bx-tooltip-user-id=""></span>
                            <input class="dummy" name="DELEGATE_USER" type="hidden" disabled />
                        </div>
                        <a
                            href="javascript:void(0);"
                            class="ui-btn ui-btn-xs ui-btn-primary ui-btn-icon-business js-delegate-add js-delegate-user <?=$bHideEdit?' js-hide-edit d-none':''?>"
                            data-single="true"
                            data-user="<?=$arElement['PROPERTY_DELEGATE_USER_VALUE']?>"
                            title="Изменить исполнителя"></a>
                    </div>
                </div>

                <?
                if ($arElement['PROPERTY_DATE_ISPOLN_VALUE'] != $this->__component->disableSrokDate) : ?>
                <div class="row mt-2">
                    <div class="col-3">
                        <b>Срок для исполнителя:</b>
                    </div>
                    <div class="col-3">
                        <span class="<?=$bHideEdit?'js-hide-edit':'d-none'?>"><?=$maxDelegateDate?></span>
                        <input
                            class="form-control <?=$bHideEdit?'js-hide-edit d-none':''?>"
                            type="text"
                            name="DELEGATE_SROK"
                            required="required"
                            value="<?=$maxDelegateDate?>"
                            onclick="BX.calendar({node: this, field: this, bTime: false});"
                            />
                    </div>
                </div>
                <? endif; ?>

                <div class="row mt-2">
                    <div class="col-3">
                        <b>Соисполнители:</b>
                    </div>
                    <div class="col-9 order-tags js-user-selector">
                        <?foreach ($arElement['PROPERTY_ACCOMPLICES_VALUE'] as $accomplice) :?>
                        <div class="order-tag">
                            <span bx-tooltip-user-id="<?=$accomplice?>"><?=$this->__component->getUserFullName($accomplice) ?></span>
                            <i class="js-order-tag-remove <?=$bHideEdit?' js-hide-edit d-none':''?>">&times;</i>
                            <input name="ACCOMPLICES[]" type="hidden" value="<?=$accomplice?>" />
                        </div>
                        <?endforeach;?>
                        <div class="d-none order-tag">
                            <span bx-tooltip-user-id=""></span>
                            <i class="js-order-tag-remove">&times;</i>
                            <input class="dummy" name="ACCOMPLICES[]" type="hidden" disabled />
                        </div>
                        <a
                            href="javascript:void(0);"
                            class="ui-btn ui-btn-xs ui-btn-primary ui-btn-icon-add js-delegate-add js-delegate-subexecutor <?=$bHideEdit?' js-hide-edit d-none':''?>"
                            data-single="false"
                            title="Добавить соисполнителя"></a>
                    </div>
                </div>
                <?
            }
            ?>

            <div class="row mt-2 <?=$bHideEdit?' js-hide-edit d-none':''?>">
                <div class="col-3">
                    <b>Комментарий:</b>
                </div>
                <div class="col-9">
                    <textarea class="form-control" name="DELEGATE_COMMENT" placeholder="Комментарий" cols="70" rows="1"></textarea>
                </div>
            </div>
            <?
            if ($bHideEdit) {
                ?>
                <button class="ui-btn ui-btn-success js-hide-edit">Редактировать</button>
                <?
            }
            ?>
            <button
                class="ui-btn ui-btn-success js-accept-ispolnitel <?=$bHideEdit?' js-hide-edit d-none':''?> mt-2"
                type="submit"
                name="action"
                value="accept_ispolnitel"
                data-field="<?=$dataField?>"
                data-text="<?=$buttonText?>"
                data-user="<?=(int)$arElement['PROPERTY_DELEGATE_USER_VALUE']?>"
                data-srok="<?=$maxDelegateDate?>"
                >
                <?=$buttonText?>
            </button>
            <?
            if (
                $arPerm['ispolnitel_implementation'] &&
                $bShowNewAccepting
            ) {
                ?>
                <button
                    class="ui-btn ui-btn-primary js-accept-ispolnitel <?=$bHideEdit?' js-hide-edit d-none':''?> mt-2"
                    type="submit"
                    name="action"
                    value="accepting_ispolnitel"
                    data-field="<?=$dataField?>"
                    data-text="Отправить проект на согласование"
                    data-user="<?=(int)$arElement['PROPERTY_DELEGATE_USER_VALUE']?>"
                    data-srok="<?=$maxDelegateDate?>"
                    data-edit="0"
                    >
                    Отправить проект на согласование
                </button>
                <?
            }
            ?>
        </form>
        <div class="d-none" id="SELECT_USER">
            <select class="form-control" name="SELECT_USER">
                <?
                foreach ($arPerm['ispolnitel_delegated'] as $sKey => $sIspolnitelName) {
                    ?>
                    <option value="<?=$sKey?>"><?=$sIspolnitelName?></option>
                    <?
                }
                ?>
            </select>
        </div>
    </div>
</div>
