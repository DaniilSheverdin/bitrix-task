<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * @global $APPLICATION
 * @var $arResult
 */
?>
<form class="needs-validation js-mfc-soglasovanie-materialov" novalidate="" style="" id="js-mfc-soglasovanie-materialov" action="<?=$APPLICATION->GetCurPage()?>" method="POST" autocomplete="Off">
    <input type="hidden" name="mfc_soglasovanie_materialov" value="add">
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
            <label class="col-sm-2 col-form-label" for="bp_TIP_DOKUMENTA">Тип документа <span class="text-danger">*</span>:</label>
            <div class="col-sm-10">
                <select name="TIP_DOKUMENTA" class="form-control" id="bp_TIP_DOKUMENTA" required="required">
                    <? foreach ($arResult['AR_TIP_DOKUMENTA'] as $arTip) { ?>
                        <option value="<?=$arTip['ID']?>"><?=$arTip['VALUE']?></option>
                    <? } ?>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_STEP1">Кто должен согласовать Этап 1 <span class="text-danger">*</span>:</label>
            <div class="col-sm-10">
                <select name="STEP1" data-live-search="true" data-live-search-placeholder="Найти" data-actions-box="true" class="form-control selectpicker" id="bp_STEP1" required="required">
                    <option value="0" disabled selected>Выбрать</option>
                    <? foreach ($arResult['ALL_USERS'] as $arUser) { ?>
                        <option value="<?=$arUser['ID']?>"><?=$arUser['LAST_NAME']?> <?=$arUser['NAME']?> <?=$arUser['SECOND_NAME']?></option>
                    <? } ?>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_NEED_TOP">Необходимо согласовывать с руководством:</label>
            <div class="col-sm-10">
                <select name="NEED_TOP" class="form-control" id="bp_NEED_TOP">
                    <? foreach ($arResult['AR_NEED_TOP'] as $arNeed) { ?>
                        <option value="<?=$arNeed['ID']?>" data-xml-id="<?=$arNeed['XML_ID']?>"><?=$arNeed['VALUE']?></option>
                    <? } ?>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_STEP2">Кто должен согласовать Этап 2:</label>
            <div class="col-sm-10">
                <select name="STEP2" class="form-control" id="bp_STEP2" >
                    <option value="0" disabled="disabled" selected="selected">-</option>
                    <? foreach ($arResult['AR_STEP2'] as $arStep) { ?>
                        <option value="<?=$arStep['ID']?>"><?=$arStep['VALUE']?></option>
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