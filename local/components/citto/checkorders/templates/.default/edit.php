<?php

use Bitrix\Main\UI\Extension;
use Citto\Controlorders\Orders;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$GLOBALS['APPLICATION']->SetAdditionalCss('/local/css/jquery.ui.1.12.1.css');
$GLOBALS['APPLICATION']->AddHeadScript('/local/js/jquery.ui.1.12.1.js');
Extension::load(['ui.buttons.icons', 'ui.dialogs.messagebox']);
$backUrl = '/control-orders/';
if (isset($_REQUEST['back_url']) && !empty($_REQUEST['back_url'])) {
    $backUrl = $_REQUEST['back_url'];
}

$bAccess = (
    $arResult['PERMISSIONS']['controler'] ||
    $arResult['PERMISSIONS']['kurator'] ||
    $arResult['PERMISSIONS']['protocol'] ||
    $GLOBALS['USER']->IsAdmin()
);

if (!$bAccess) {
    LocalRedirect($backUrl);
}

$arResult['AVAILABLE_TAGS'] = $this->__component->getAvailableTags();
?>
<?$this->SetViewTarget('inside_pagetitle', 100);?>
<div class="pagetitle-container pagetitle-align-right-container">
<?
    if (false !== mb_strpos($backUrl, 'detail=')) {
        $action_after = 'detail';
        ?>
        <a class="ui-btn ui-btn-light-border ui-btn-icon-back" href="<?=$backUrl ?>">Возврат к поручению</a>
        <?
    } else {
        $action_after = 'list';
        ?>
        <a class="ui-btn ui-btn-light-border ui-btn-icon-back" href="<?=str_replace('|', '&', rawurldecode($backUrl)) ?>">Возврат к списку</a>
        <?
    }
?>
</div>
<?$this->EndViewTarget();

if ((int)$_REQUEST['edit'] > 0) {
    if (empty($arResult['EDIT_DATA'])) {
        ShowError('Поручение не найдено');
        return;
    }

    if ($arResult['ACCES'] == 'Y') {
        ?>
        <form action="?edit=<?=$_REQUEST['edit']?>&action=update&back_url=<?=rawurlencode($APPLICATION->GetCurPageParam())?>" method="post">
            <input type="hidden" name="back_url" value="<?=$backUrl?>" />
            <input type="hidden" name="action_after" value="<?=$action_after?>" />
            <div class="row">
                <div class="col-10 col-xl-9">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Основная информация</h3>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-6 col-md-9">
                                    <b><span class="required">*</span>Наименование поручения:</b><br>
                                    <input class="form-control" required="" type="text" name="NAME" value="<?=($_REQUEST['NAME'] != '') ? $_REQUEST['NAME'] : $arResult['EDIT_DATA']['NAME'] ?>" size="30">
                                </div>
                                <div class="col-6 col-md-3">
                                    <b>Номер:</b><br>
                                    <input class="form-control" type="text" name="PROP[NUMBER]" value="<?=($_REQUEST['PROP']['NUMBER'] != '') ? $_REQUEST['PROP']['NUMBER'] : $arResult['EDIT_DATA']['PROPERTY_NUMBER_VALUE'] ?>" size="30">
                                </div>
                                <div class="col-12">
                                    <b><span class="required">*</span>Содержание поручения:</b><br>
                                    <div class="card">
                                        <?$APPLICATION->IncludeComponent(
                                            'bitrix:fileman.light_editor',
                                            '',
                                            array(
                                                'CONTENT' => ($_REQUEST['DETAIL_TEXT'] != '') ? $_REQUEST['DETAIL_TEXT'] : $arResult['EDIT_DATA']['~DETAIL_TEXT'],
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
                                    </div>
                                </div>
                                <div class="col-12">
                                    <?$APPLICATION->IncludeComponent(
                                        'bitrix:main.file.input',
                                        'drag_n_drop',
                                        array(
                                            'INPUT_NAME' => 'DOCS',
                                            'MULTIPLE' => 'Y',
                                            'MODULE_ID' => 'iblock',
                                            'MAX_FILE_SIZE' => '',
                                            'ALLOW_UPLOAD' => 'A',
                                            'ALLOW_UPLOAD_EXT' => '',
                                            'INPUT_VALUE' => ($_REQUEST['DOCS'] != '') ? $_REQUEST['DOCS'] : $arResult['EDIT_DATA']['PROPERTY_DOCS_VALUE'],
                                        ),
                                        false
                                    );?>
                                </div>

                                <div class="col-12 col-md-6">
                                    <b>Тип поручения</b><br>
                                    <input type="hidden" name="PROP[TYPE][]" value="" />
                                    <?
                                    if (is_array($_REQUEST['PROP']['TYPE'])) {
                                        $arTypesCheck = $_REQUEST['PROP']['TYPE'];
                                    } else {
                                        $arTypesCheck = $arResult['EDIT_DATA']['PROPERTY_TYPE_VALUE'];
                                    }
                                    foreach ($arResult['TYPES_DATA'] as $key => $value) {
                                        ?>
                                        <div class="form-check">
                                            <input
                                                class="form-check-input"
                                                <?=(in_array($key, $arTypesCheck)) ? 'checked' : '' ?>
                                                type="checkbox"
                                                name="PROP[TYPE][]"
                                                value="<?=$key?>"
                                                id="TypeCheck<?=$key?>"
                                                />
                                            <label class="form-check-label label-<?=$key?>" for="TypeCheck<?=$key?>">
                                                <?=$value['UF_NAME']?>
                                            </label>
                                        </div>
                                        <?
                                    }
                                    ?>
                                </div>

                                <div class="col-12 col-md-6">
                                    <b><span class="required">*</span>Тема поручения:</b>
                                    <?
                                    if ($_REQUEST['PROP']['CAT_THEME'] != '') {
                                        $CAT_THEME = $_REQUEST['PROP']['CAT_THEME'];
                                    } else {
                                        $CAT_THEME = $arResult['EDIT_DATA']['PROPERTY_CAT_THEME_VALUE'];
                                    }

                                    if ($_REQUEST['PROP']['THEME'] != '') {
                                        $THEME = $_REQUEST['PROP']['THEME'];
                                    } else {
                                        $THEME = $arResult['EDIT_DATA']['PROPERTY_THEME_VALUE'];
                                    }
                                    ?>
                                    <select
                                        class="form-control js-classifcator-cat-select my-2"
                                        name="PROP[CAT_THEME]"
                                        required="required"
                                        >
                                        <option value="">(Не выбрано)</option>
                                        <?
                                        foreach ($arResult['CLASSIFICATOR'] as $k => $v) {
                                            ?>
                                            <option <?=($v['ID'] == $CAT_THEME) ? 'selected' : '' ?> value="<?=$v['ID']?>"><?=$v['NAME']?></option>
                                            <?
                                        }
                                        ?>
                                    </select>
                                    <?
                                    foreach ($arResult['CLASSIFICATOR'] as $k => $v) {
                                        ?>
                                        <select
                                            name="PROP[THEME]"
                                            class="form-control themes-selects my-2"
                                            <?=($v['ID'] == $CAT_THEME) ? '' : 'disabled=""' ?>
                                            id="classificator_id_<?=$v['ID']?>"
                                            required="required"
                                            >
                                            <option value="">(Не выбрано)</option>
                                            <?
                                        foreach ($v['THEMES'] as $k2 => $v2) {
                                            ?>
                                                <option <?=($v2['ID'] == $THEME) ? 'selected' : '' ?> value="<?=$v2['ID']?>">
                                                    <?=$v2['NAME']?>
                                                </option>
                                            <?
                                        }
                                        ?>
                                        </select>
                                        <?
                                    }

                                    require 'edit_objects.php';
                                    ?>
                                    <div class="my-2">
                                        <b>Теги:</b>
                                    </div>
                                    <div class="row">
                                        <div class="col-12">
                                            <?
                                            $arTags = explode(',', $arResult['EDIT_DATA']['TAGS']);
                                            $arTags = array_unique($arTags);
                                            $arTags = array_filter($arTags);
                                            $arTags = array_map('trim', $arTags);
                                            ?>
                                            <select name="TAGS[]" class="select2" multiple>
                                                <?foreach ($arResult['AVAILABLE_TAGS'] as $tag) :?>
                                                <option value="<?=$tag ?>" <?=in_array($tag, $arTags) ? 'selected': ''?>><?=$tag ?></option>
                                                <?endforeach;?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button class="ui-btn ui-btn-primary" name="action" value="update" type="submit">Сохранить поручение</button>
                </div>

                <div class="col-10 col-xl-3">
                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Состояние</h3>
                        </div>
                        <div class="box-body">
                            <ul class="list-group list-group-unbordered">
                                <li class="list-group-item">
                                    <b>Состояние</b> <a class="pull-right"><?=$arResult['EDIT_DATA']['PROPERTY_ACTION_VALUE']?></a>
                                    <input type="hidden" name="PROP[ACTION]" value="<?=$arResult['EDIT_DATA']['PROPERTY_ACTION_ENUM_ID']?>" />
                                </li>
                                <li class="list-group-item">
                                    <b>Статус</b> <a class="pull-right"><?=$arResult['EDIT_DATA']['PROPERTY_STATUS_VALUE']?></a>
                                    <input type="hidden" name="PROP[STATUS]" value="<?=$arResult['EDIT_DATA']['PROPERTY_STATUS_ENUM_ID']?>" />
                                </li>
                                <?if ($arResult['DETAIL_DATA']['ELEMENT']['PROPERTY_DATE_FACT_ISPOLN_VALUE'] != '') {?>
                                <li class="list-group-item">
                                    <b>Дата исполнения</b> <a class="pull-right"><?=$arResult['DETAIL_DATA']['ELEMENT']['PROPERTY_DATE_FACT_ISPOLN_VALUE']?></a>
                                </li>
                                <?}?>
                                <?if ($arResult['DETAIL_DATA']['ELEMENT']['PROPERTY_DATE_FACT_SNYAT_VALUE'] != '') {?>
                                <li class="list-group-item">
                                    <b>Дата снятия с контроля</b> <a class="pull-right"><?=$arResult['DETAIL_DATA']['ELEMENT']['PROPERTY_DATE_FACT_SNYAT_VALUE']?></a>
                                </li>
                                <?}?>
                            </ul>

                            <div class="row">
                                <div class="col-12 mt-2">
                                    <b>Дата поручения:</b><br>
                                    <input
                                        class="form-control"
                                        type="text"
                                        name="PROP[DATE_CREATE]"
                                        value="<?=($_REQUEST['PROP']['DATE_CREATE'] != '') ? $_REQUEST['PROP']['DATE_CREATE'] : $arResult['EDIT_DATA']['PROPERTY_DATE_CREATE_VALUE']?>"
                                        onclick="BX.calendar({node: this, field: this, bTime: false});"
                                        />
                                </div>
                                <?
                                $disableSrok = $_REQUEST['PROP']['DATE_ISPOLN']==$this->__component->disableSrokDate ||
                                                $arResult['EDIT_DATA']['PROPERTY_DATE_ISPOLN_VALUE']==$this->__component->disableSrokDate;
                                ?>
                                <div class="col-12 mt-2 js-date-ispoln" data-required="true">
                                    <b><span class="required">*</span>Срок исполнения:&nbsp;&nbsp;&nbsp;</b>&nbsp;&nbsp;&nbsp;<span>
                                        <input type="hidden" name="DISABLE_DATE_ISPOLN" value="N" />
                                        <input class="form-check-input" type="checkbox" name="DISABLE_DATE_ISPOLN" value="Y" id="DisableDateIspoln" <?=($disableSrok?'checked':'')?>>
                                        <label class="form-check-label" for="DisableDateIspoln">
                                        Без&nbsp;срока
                                        </label>
                                    </span>
                                    <br>
                                    <input
                                        type="text"
                                    	class="form-control <?=$disableSrok?'d-none':'' ?>"
                                    	name="PROP[DATE_ISPOLN]"
                                    	value="<?=$disableSrok?'':(($_REQUEST['PROP']['DATE_ISPOLN'] != '') ? $_REQUEST['PROP']['DATE_ISPOLN'] : $arResult['EDIT_DATA']['PROPERTY_DATE_ISPOLN_VALUE']) ?>"
                                    	<?=$disableSrok?'':'required'?>
                                    	onclick="BX.calendar({node: this, field: this, bTime: false});"
                                    	/>
                                </div>
                                <div class="col-12 mt-2">
                                    <b>Категория поручения</b><br>
                                    <?
                                    foreach ($arResult['CATEGORIES'] as $key => $value) {
                                        ?>
                                        <div class="form-check">
                                            <input
                                                required="required"
                                                class="form-check-input"
                                                <?=($key == $arResult['EDIT_DATA']['PROPERTY_CATEGORY_ENUM_ID']) ? 'checked' : '' ?>
                                                type="radio"
                                                name="PROP[CATEGORY]"
                                                value="<?=$key?>"
                                                id="TypeCheckCat<?=$key?>">
                                            <label class="form-check-label" for="TypeCheckCat<?=$key?>">
                                                <?=$value['VALUE']?>
                                            </label>
                                        </div>
                                        <?
                                    }
                                    ?>
                                </div>
                                <div class="col-12 mt-2">
                                    <b>Не учитывать в нарушениях</b><br>
                                    <input type="hidden" name="PROP[NOT_STATS]" value="N" checked />
                                    <div class="form-check">
                                        <input
                                            class="form-check-input"
                                            <?=($arResult['NOT_STATS']['Y']['ID'] == $arResult['EDIT_DATA']['PROPERTY_NOT_STATS_ENUM_ID']) ? 'checked' : '' ?>
                                            type="checkbox"
                                            name="PROP[NOT_STATS]"
                                            value="<?=$arResult['NOT_STATS']['Y']['ID']?>"
                                            id="NotStats<?=$arResult['NOT_STATS']['Y']['ID']?>">
                                        <label class="form-check-label" for="NotStats<?=$arResult['NOT_STATS']['Y']['ID']?>">
                                            <?=$arResult['NOT_STATS']['Y']['VALUE']?>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="box box-primary">
                        <div class="box-header with-border">
                            <h3 class="box-title">Ответственные</h3>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-12">
                                    <b><span class="required">*</span>Исполнитель:</b><br>
                                    <?
                                    if ($_REQUEST['PROP']['ISPOLNITEL'] != '') {
                                        $ispolnitel = $_REQUEST['PROP']['ISPOLNITEL'];
                                    } else {
                                        $ispolnitel = $arResult['EDIT_DATA']['PROPERTY_ISPOLNITEL_VALUE'];
                                    }
                                    $title = '';
                                    foreach ($arResult['ISPOLNITELS'] as $k => $v) {
                                        if ($v['ID'] == $ispolnitel) {
                                            $title = $v['NAME'];
                                        }
                                    }
                                    ?>
                                    <select class="form-control" required name="PROP[ISPOLNITEL]" title="<?=$title?>">
                                        <option value="">(Не выбран)</option>
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
                                                        <?=($v['ID'] == $ispolnitel) ? 'selected' : '' ?>
                                                        value="<?=$v['ID']?>"
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

                                <div class="col-12 mt-2">
                                    <b>Соисполнители:</b> <a class="ui-btn ui-btn-xs ui-btn-primary ui-btn-icon-add js-add-subexecutor"></a>
                                </div>
                                <?
                                $arSubExecutors = $arResult['EDIT_DATA']['PROPERTY_SUBEXECUTOR_IDS'];
                                if (empty($arSubExecutors)) {
                                    $arSubExecutors = [0];
                                }
                                if (!is_array($arSubExecutors)) {
                                    $arSubExecutors = [$arSubExecutors];
                                }
                                $i = 0;
                                foreach ($arSubExecutors as $keySE => $valueSE) {
                                    $title = '';
                                    foreach ($arResult['ISPOLNITELS'] as $k => $v) {
                                        if ($v['ID'] == $valueSE) {
                                            $title = $v['NAME'];
                                        }
                                    }
                                    ?>
                                    <div class="subexecutor row col-12 mt-2 pr-0" data-id="<?=$i?>">
                                        <div class="<?=($keySE==0?'col-12':'col-11')?> pr-0">
                                            <select class="form-control" name="PROP[SUBEXECUTOR][<?=$i?>]" title="<?=$title?>">
                                                <option value="">(Не выбран)</option>
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
                                                            <?=($v['ID'] == $valueSE) ? 'selected' : '' ?>
                                                            value="<?=$v['ID']?>"
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
                                            <?
                                            $bIsChecked = in_array('I'.$valueSE, $arResult['EDIT_DATA']['PROPERTY_REQUIRED_VISA_VALUE']);
                                            ?>
                                            Обязательная виза
                                            <label>
                                                <input
                                                    type="radio"
                                                    name="PROP[REQUIRED_VISA][<?=$i?>]"
                                                    value="Y"
                                                    <?=$bIsChecked?'checked':''?>
                                                    />
                                                Да
                                            </label>
                                            <label>
                                                <input
                                                    type="radio"
                                                    name="PROP[REQUIRED_VISA][<?=$i?>]"
                                                    value="N"
                                                    <?=!$bIsChecked?'checked':''?>
                                                    />
                                                Нет
                                            </label>
                                        </div>
                                        <div class="<?=($keySE==0?'d-none':'')?> col-1 pl-1 mx-auto mt-1">
                                            <a href="#" class="js-delete-subexecutor ui-btn ui-btn-xs ui-btn-icon-remove"></a>
                                        </div>
                                    </div>
                                    <?
                                    $i++;
                                }

                                $disableSubExecSrok = $_REQUEST['PROP']['SUBEXECUTOR_DATE']==$this->__component->disableSrokDate ||
                                                $arResult['EDIT_DATA']['PROPERTY_SUBEXECUTOR_DATE_VALUE']==$this->__component->disableSrokDate;
                                ?>
                                <div class="col-12 mt-2 js-date-ispoln" data-required="false">
                                    <b>Срок соисполнителя:&nbsp;&nbsp;&nbsp;</b>&nbsp;&nbsp;&nbsp;<span>
                                        <input type="hidden" name="DISABLE_SUBEXECUTOR_DATE" value="N" />
                                        <input class="form-check-input" type="checkbox" name="DISABLE_SUBEXECUTOR_DATE" value="Y" id="DisableSubExecSrok" <?=($disableSubExecSrok?'checked':'')?>>
                                        <label class="form-check-label" for="DisableSubExecSrok">
                                        Без&nbsp;срока
                                        </label>
                                    </span>
                                    <br>
                                    <input
                                        type="text"
                                        class="form-control <?=$disableSubExecSrok?'d-none':'' ?>"
                                        name="PROP[SUBEXECUTOR_DATE]"
                                        value="<?=$disableSubExecSrok?'':(($_REQUEST['PROP']['SUBEXECUTOR_DATE'] != '') ? $_REQUEST['PROP']['SUBEXECUTOR_DATE'] : $arResult['EDIT_DATA']['PROPERTY_SUBEXECUTOR_DATE_VALUE']) ?>"
                                        onclick="BX.calendar({node: this, field: this, bTime: false});"
                                        />
                                </div>

                                <div class="col-12 mt-2">
                                    <b><span class="required">*</span>Контролер:</b><br/>
                                    <?/*
                                    <?
                                    $arControlers = [];
                                    $res = \Bitrix\Main\UserGroupTable::getList([
                                        'select'    => ['USER_ID'],
                                        'filter'    => ['GROUP_ID' => 97]
                                    ]);
                                    while ($row = $res->fetch()) {
                                        $arControlers[] = $row['USER_ID'];
                                    }
                                    ?>
                                    <select class="form-control" name="PROP[CONTROLER]" required>
                                        <?
                                        foreach ($arControlers as $cId) {
                                            ?>
                                            <option
                                                <?=($cId == (($_REQUEST['CONTROLER'] != '') ? $_REQUEST['CONTROLER'] : $arResult['EDIT_DATA']['PROPERTY_CONTROLER_VALUE'])) ? 'selected' : '';?>
                                                value="<?=$cId?>">
                                                <?=$this->__component->getUserFullName($cId)?>
                                            </option>
                                            <?
                                        }
                                        ?>
                                    </select>
                                    */?>
                                    <?$GLOBALS['APPLICATION']->IncludeComponent(
                                        'bitrix:intranet.user.selector',
                                        '',
                                        array(
                                            'INPUT_NAME'            => 'CONTROLER',
                                            'INPUT_NAME_SUSPICIOUS' => 'CONTROLER_SUP',
                                            'INPUT_NAME_STRING'     => 'CONTROLER_STRING',
                                            'TEXTAREA_MIN_HEIGHT'   => 30,
                                            'TEXTAREA_MAX_HEIGHT'   => 60,
                                            'INPUT_VALUE'           => ($_REQUEST['CONTROLER'] != '') ? $_REQUEST['CONTROLER'] : $arResult['EDIT_DATA']['PROPERTY_CONTROLER_VALUE'],
                                            'EXTERNAL'              => 'A',
                                            'MULTIPLE'              => 'N',
                                            'SOCNET_GROUP_ID'       => ($arParams['TASK_TYPE'] == 'group' ? $arParams['OWNER_ID'] : ''),
                                        )
                                    );?>
                                </div>

                                <div class="col-12 mt-2">
                                    <b><span class="required">*</span>Куратор:</b><br>
                                    <?/*
                                    <?
                                    $arKurators = [];
                                    $res = \Bitrix\Main\UserGroupTable::getList([
                                        'select'    => ['USER_ID'],
                                        'filter'    => ['GROUP_ID' => 98]
                                    ]);
                                    while ($row = $res->fetch()) {
                                        $arKurators[] = $row['USER_ID'];
                                    }
                                    ?>
                                    <select class="form-control" name="PROP[POST]" required>
                                        <?
                                        foreach ($arKurators as $cId) {
                                            ?>
                                            <option
                                                <?=($cId == (($_REQUEST['POST'] != '') ? $_REQUEST['POST'] : $arResult['EDIT_DATA']['PROPERTY_POST_VALUE'])) ? 'selected' : '';?>
                                                value="<?=$cId?>">
                                                <?=$this->__component->getUserFullName($cId)?>
                                            </option>
                                            <?
                                        }
                                        ?>
                                    </select>
                                    */?>
                                    <?$GLOBALS['APPLICATION']->IncludeComponent(
                                        'bitrix:intranet.user.selector',
                                        '',
                                        array(
                                            'INPUT_NAME'            => 'POST',
                                            'INPUT_NAME_SUSPICIOUS' => 'POST_SUP',
                                            'INPUT_NAME_STRING'     => 'POST_STRING',
                                            'INPUT_VALUE'           => ($_REQUEST['POST'] != '') ? $_REQUEST['POST'] : $arResult['EDIT_DATA']['PROPERTY_POST_VALUE'],
                                            'TEXTAREA_MIN_HEIGHT'   => 30,
                                            'TEXTAREA_MAX_HEIGHT'   => 60,
                                            'EXTERNAL'              => 'A',
                                            'MULTIPLE'              => 'N',
                                            'SOCNET_GROUP_ID'       => ($arParams['TASK_TYPE'] == 'group' ? $arParams['OWNER_ID'] : ''),
                                        )
                                    );?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <?
    } else {
        ?>
        <div class="color-red">Не достаточно прав для редактирования поручения</div>
        <?
    }
} elseif ($_REQUEST['action'] == 'add_position') {
    require('edit_add_position.php');
} else {
    if (
        empty($_POST) &&
        (
            $arResult['PERMISSIONS']['controler'] ||
            $arResult['PERMISSIONS']['protocol'] ||
            $GLOBALS['USER']->IsAdmin()
        ) &&
        $_REQUEST['template'] > 0
    ) {
        $arOrder = (new Orders())->getByID($_REQUEST['template']);
        if (empty($arOrder)) {
            ShowError('Поручение не найдено');
        } else {
            $_REQUEST['NAME'] = $arOrder['NAME'];
            $_REQUEST['PROP']['NUMBER'] = $arOrder['PROPERTY_NUMBER_VALUE'];
            $_REQUEST['PROP']['DATE_CREATE'] = $arOrder['PROPERTY_DATE_CREATE_VALUE'];
            $_REQUEST['PROP']['CATEGORY'] = $arOrder['PROPERTY_CATEGORY_ENUM_ID'];
            $_REQUEST['PROP']['TYPE'] = [];
            if (!empty($arOrder['PROPERTY_DOCS_VALUE'])) {
                foreach ($arOrder['PROPERTY_DOCS_VALUE'] as $fId) {
                    $_REQUEST['DOCS'][] = CFile::CopyFile($fId);
                }
            }
        }
    }

    if ($_REQUEST['multi']=='yes') {
        require('edit_add_multi.php');
    } else {
        require('edit_add_simple.php');
    }
}
