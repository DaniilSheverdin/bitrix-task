<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * @global $APPLICATION
 */
?>
<form class="needs-validation js-mfc-oznakomlenie" novalidate="" style="" id="js-mfc-oznakomlenie" action="<?=$APPLICATION->GetCurPage()?>" method="POST" autocomplete="Off">
    <input type="hidden" name="mfc-oznakomlenie" value="add">
    <?=bitrix_sessid_post()?>
    <div class="alert d-none"></div>
    <div id="js--form-action-content" class="show">
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_NAME">Название <span class="text-danger">*</span>:</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="NAME" value="" id="bp_NAME" required="required" />
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_TYPE">Тип <span class="text-danger">*</span>:</label>
            <div class="col-sm-10">
                <select name="TYPE" class="form-control" id="bp_TYPE" required="required">
                    <? foreach ($arResult['ARTYPE'] as $arType) { ?>
                        <option value="<?=$arType['ID']?>"><?=$arType['VALUE']?></option>
                    <? } ?>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_USERS">Кто должен ознакомиться <span class="text-danger">*</span>:</label>
            <div class="col-sm-10">
                <select name="USERS" class="form-control" id="bp_USERS" required="required">
                    <? foreach ($arResult['ARUSERS'] as $arType) { ?>
                        <option value="<?=$arType['ID']?>" data-xml-id="<?=$arType['XML_ID']?>"><?=$arType['VALUE']?></option>
                    <? } ?>
                </select>
            </div>
        </div>
		<div class="form-group row" style="display: none;">
            <label class="col-sm-2 col-form-label" for="bp_USER_LIST">Список пользователей:</label>
            <div class="col-sm-10">
                <select multiple="multiple" data-live-search="true" data-live-search-placeholder="Найти" data-actions-box="true" class="selectpicker form-control" name="USER_LIST[]" id="bp_USER_LIST">
                    <? foreach($arResult['ALL_USERS'] as $arUser) { ?>
                        <option value="<?=$arUser['ID']?>"><?=$arUser['LAST_NAME']?> <?=$arUser['NAME']?> <?=$arUser['SECOND_NAME']?></option>
                    <? } ?>
                </select>
            </div>
        </div>
        <div class="form-group row" style="display: none;">
            <label class="col-sm-2 col-form-label" for="bp_GROUPS_LIST">Список групп пользователей:</label>
            <div class="col-sm-10">
                <select multiple="multiple" data-live-search="true" data-live-search-placeholder="Найти" data-actions-box="true" class="selectpicker form-control" name="GROUPS_LIST[]" id="bp_GROUPS_LIST">
                    <? foreach($arResult['ALL_GROUPS'] as $arGroup) { ?>
                        <option value="<?=$arGroup['ID']?>"><?=$arGroup['NAME']?></option>
                    <? } ?>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_MESSAGE">Сообщение:</label>
            <div class="col-sm-10">
                <textarea class="form-control" name="MESSAGE" id="bp_MESSAGE"></textarea>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_FILE">Файл:</label>
            <div class="col-sm-10 py-2">
                <input class="form-control" type="file" name="FILE" value="" id="bp_FILE" />
            </div>
        </div>
		<div class="row">
            <div class="col-12 col-sm-2">
                <div class="text-right mb-3">
                    <button class="btn btn-primary btn-block" type="submit">Далее &rarr;</button>
                </div>
            </div>
        </div>
    </div>
</form>