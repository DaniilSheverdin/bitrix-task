<?php

use Citto\Controlorders\GroupExecutors;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$arGroupExecutors = (new GroupExecutors())->getList();

?>
<form action="?edit=0&action=add&back_url=<?=rawurlencode($APPLICATION->GetCurPageParam())?>" method="post">
    <input type="hidden" name="back_url" value="<?=$backUrl?>" />
    <div class="row">
        <div class="col-10 col-xl-12 text-right mb-2">
            <a class="ui-btn ui-btn-disabled" href="<?=$APPLICATION->GetCurPageParam('', ['multi'])?>">На одного исполнителя</a>
            <a class="ui-btn ui-btn-success" href="<?=$APPLICATION->GetCurPageParam('multi=yes', ['multi'])?>">На несколько исполнителей</a>
        </div>
        <div class="col-10 col-xl-9">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Основная информация</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-6 col-md-9">
                            <b><span class="required">*</span>Наименование поручения:</b><br>
                            <input class="form-control" required="" type="text" name="NAME" value="<?=$_REQUEST['NAME']?>" size="30">
                        </div>
                        <div class="col-6 col-md-3">
                            <b>Номер:</b><br>
                            <input class="form-control" type="text"  name="PROP[NUMBER]" value="<?=$_REQUEST['PROP']['NUMBER']?>" size="30">
                        </div>
                        <div class="col-12">
                            <b><span class="required">*</span>Содержание поручения:</b><br>
                            <div class="card">
                                <?$APPLICATION->IncludeComponent(
                                    'bitrix:fileman.light_editor',
                                    '',
                                    array(
                                        'CONTENT' => $_REQUEST['DETAIL_TEXT'],
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
                                    'INPUT_VALUE' => $_REQUEST['DOCS'],
                                ),
                                false
                            );?>
                        </div>
                        <div class="col-12 col-md-6">
                            <b>Тип поручения</b><br>
                            <?
                            foreach ($arResult['TYPES_DATA'] as $key => $value) {
                                ?>
                                <div class="form-check">
                                    <input
                                        class="form-check-input"
                                        <?=(isset($_REQUEST['PROP']) && in_array($key, $_REQUEST['PROP']['TYPE'])) ? 'checked' : '' ?>
                                        type="checkbox"
                                        name="PROP[TYPE][]"
                                        value="<?=$key?>"
                                        id="TypeCheck<?=$key?>">
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
                            <select
                                class="form-control js-classifcator-cat-select my-2"
                                name="PROP[CAT_THEME]"
                                required="required"
                                >
                                <option value="">(Не выбрано)</option>
                                <?
                                foreach ($arResult['CLASSIFICATOR'] as $k => $v) {
                                    ?>
                                    <option
                                        <?=($v['ID'] == $_REQUEST['PROP']['CAT_THEME']) ? 'selected' : '' ?>
                                        value="<?=$v['ID']?>"
                                        ><?=$v['NAME']?></option>
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
                                    <?=($v['ID'] == $_REQUEST['PROP']['CAT_THEME']) ? '' : 'disabled=""' ?>
                                    id="classificator_id_<?=$v['ID']?>"
                                    required="required"
                                    >
                                    <option value="">(Не выбрано)</option>
                                    <?
                                foreach ($v['THEMES'] as $k2 => $v2) {
                                    ?>
                                        <option
                                            <?=($v2['ID'] == $_REQUEST['PROP']['THEME']) ? 'selected' : '' ?>
                                            value="<?=$v2['ID']?>"
                                            ><?=$v2['NAME']?></option>
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
            <button class="ui-btn ui-btn-primary" type="submit">Добавить поручение</button>
        </div>

        <div class="col-10 col-xl-3">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Состояние</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-12">
                            <b><span class="required">*</span>Состояние:</b><br>
                            <select class="form-control" required name="PROP[ACTION]">
                                <?if (!$arResult['PERMISSIONS']['protocol']) : ?>
                                <option value="1135">Новое</option>
                                <?endif;?>
                                <option value="1134">Черновик</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <b>Дата поручения:</b><br>
                            <input
                                class="form-control"
                                type="text"
                                name="PROP[DATE_CREATE]"
                                value="<?=($_REQUEST['PROP']['DATE_CREATE'] != '') ? $_REQUEST['PROP']['DATE_CREATE'] : date('d.m.Y')?>"
                                onclick="BX.calendar({node: this, field: this, bTime: false});">
                        </div>
                        <div class="col-12 js-date-ispoln" data-required="true">
                            <b><span class="required">*</span>Срок исполнения:&nbsp;&nbsp;&nbsp;</b>&nbsp;&nbsp;&nbsp;<span>
                                <input type="hidden" name="DISABLE_DATE_ISPOLN" value="N" />
                                <input class="form-check-input" type="checkbox" name="DISABLE_DATE_ISPOLN" value="Y" id="DisableDateIspoln">
                                <label class="form-check-label" for="DisableDateIspoln">
                                Без&nbsp;срока
                                </label>
                            </span><br>
                            <input
                                class="form-control"
                                type="text"
                                name="PROP[DATE_ISPOLN]"
                                value="<?=$_REQUEST['PROP']['DATE_ISPOLN']?>"
                                required
                                onclick="BX.calendar({node: this, field: this, bTime: false});"
                                />
                        </div>

                        <div class="col-12">
                            <b><span class="required">*</span>Категория поручения</b><br>
                            <?
                            if ($_REQUEST['PROP']['CATEGORY'] != '') {
                                $sCategorySelected = $_REQUEST['PROP']['CATEGORY'];
                            }
                            foreach ($arResult['CATEGORIES'] as $key => $value) {
                                ?>
                                <div class="form-check">
                                    <input
                                        required="required"
                                        class="form-check-input"
                                        <?=(($sCategorySelected != '' && $key == $sCategorySelected) || ($sCategorySelected == '' && $value['DEF'] == 'Y')) ? 'checked' : '' ?>
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
                            <select  class="form-control" required name="PROP[ISPOLNITEL]">
                                <option value="">(Не выбран)</option>
                                <optgroup label="Группа исполнителей">
                                    <?
                                    foreach ($arGroupExecutors as $sValue) {
                                        ?>
                                        <option
                                            <?=(('group_' . $sValue['ID']) == $_REQUEST['PROP']['ISPOLNITEL']) ? 'selected' : '' ?>
                                            value="<?='group_' . $sValue['ID']?>"
                                            ><?=$sValue['UF_NAME']?></option>
                                        <?
                                    }
                                    ?>
                                </optgroup>
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
                                                <?=($v['ID'] == $_REQUEST['PROP']['ISPOLNITEL']) ? 'selected' : '' ?>
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
                        $arSubExecutors = $_REQUEST['PROP']['SUBEXECUTOR'];
                        if (empty($arSubExecutors)) {
                            $arSubExecutors = [0];
                        }
                        if (!is_array($arSubExecutors)) {
                            $arSubExecutors = [$arSubExecutors];
                        }
                        $i = 0;

                        foreach ($arSubExecutors as $keySE => $valueSE) {
                            ?>
                            <div class="subexecutor row col-12 mt-2 pr-0" data-id="<?=$i?>">
                                <div class="<?=($keySE==0?'col-12':'col-11')?> pr-0">
                                    <select class="form-control" name="PROP[SUBEXECUTOR][<?=$i?>]">
                                        <option value="">(Не выбран)</option>
                                        <optgroup label="Группа исполнителей">
                                            <?
                                            foreach ($arGroupExecutors as $sValue) {
                                                ?>
                                                <option
                                                    <?=(('group_' . $sValue['ID']) == (string)$valueSE) ? 'selected' : '' ?>
                                                    value="<?='group_' . $sValue['ID']?>"
                                                    ><?=$sValue['UF_NAME']?></option>
                                                <?
                                            }
                                            ?>
                                        </optgroup>
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
                                    $bIsChecked = $_REQUEST['PROP']['REQUIRED_VISA'][ $keySE ] == 'Y';
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

                        $disableSubExecSrok = ($_REQUEST['PROP']['SUBEXECUTOR_DATE']==$this->__component->disableSrokDate ||
                                                $arResult['EDIT_DATA']['PROPERTY_SUBEXECUTOR_DATE_VALUE']==$this->__component->disableSrokDate);
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
                                class="form-control <?=$disableSubExecSrok?'d-none':'' ?>"
                                type="text"
                                name="PROP[SUBEXECUTOR_DATE]"
                                value="<?=$disableSubExecSrok?'':(($_REQUEST['PROP']['SUBEXECUTOR_DATE'] != '') ? $_REQUEST['PROP']['SUBEXECUTOR_DATE'] : $arResult['EDIT_DATA']['PROPERTY_SUBEXECUTOR_DATE_VALUE']) ?>"
                                onclick="BX.calendar({node: this, field: this, bTime: false});">
                        </div>

                        <div class="col-12 mt-2">
                            <b><span class="required">*</span>Контролер:</b><br>
                            <?$GLOBALS['APPLICATION']->IncludeComponent(
                                'bitrix:intranet.user.selector',
                                '',
                                array(
                                    'INPUT_NAME'            => 'CONTROLER',
                                    'INPUT_NAME_SUSPICIOUS' => 'CONTROLER_SUP',
                                    'INPUT_NAME_STRING'     => 'CONTROLER_STRING',
                                    'TEXTAREA_MIN_HEIGHT'   => 30,
                                    'TEXTAREA_MAX_HEIGHT'   => 60,
                                    'INPUT_VALUE'           => ($_REQUEST['CONTROLER'] != '') ? $_REQUEST['CONTROLER'] : 1151,
                                    'EXTERNAL'              => 'A',
                                    'MULTIPLE'              => 'N',
                                    'SOCNET_GROUP_ID'       => ($arParams['TASK_TYPE'] == 'group' ? $arParams['OWNER_ID'] : ''),
                                )
                            );?>
                        </div>
                        <div class="col-12 mt-2">
                            <b><span class="required">*</span>Куратор:</b><br>
                            <?$GLOBALS['APPLICATION']->IncludeComponent(
                                'bitrix:intranet.user.selector',
                                '',
                                array(
                                    'INPUT_NAME'            => 'POST',
                                    'INPUT_NAME_SUSPICIOUS' => 'POST_SUP',
                                    'INPUT_NAME_STRING'     => 'POST_STRING',
                                    'INPUT_VALUE'           => ($_REQUEST['POST'] != '') ? $_REQUEST['POST'] : 1112,
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