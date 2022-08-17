<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<form enctype="multipart/form-data" class="needs-validation js-remote_work" novalidate="" style="" id="js-remote_work" action="<?=htmlspecialchars($_SERVER['REQUEST_URI'])?>" method="POST" autocomplete="Off">
    <input type="hidden" name="uved_remote_work" value="add">
    <?=bitrix_sessid_post()?>
    <div class="alert d-none"></div>
    <div id="js--form-action-content" class="show">
        <div class="form-group row">
            <label class="col-sm-4 col-form-label" for="bp_SOTRUDNIK">Пользователь <span class="text-danger">*</span></label>
            <div class="col-sm-8 py-2">
                <select class="selectpicker form-control" data-dropup-auto="false" data-size="5" title="Выбрать" data-live-search="true"  data-live-search-placeholder="Найти" name="SOTRUDNIK" id="bp_SOTRUDNIK" required>
                    <? foreach ($arResult['USERS'] as $arUser) { ?>
                        <option value="<?=$arUser['ID']?>" <?=($arUser['ID']==$GLOBALS['USER']->GetID()?'selected':'')?>><?=$arUser['USER_INFO']?></option>
                    <? } ?>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-4 col-form-label" for="bp_HEAD_OIV">Руководитель ОИВ <span class="text-danger">*</span></label>
            <div class="col-sm-8 py-2">
                <select class="selectpicker form-control" data-dropup-auto="false" data-size="5" title="Выбрать" data-live-search="true"  data-live-search-placeholder="Найти" name="HEAD_OIV" id="bp_HEAD_OIV" required>
                    <? foreach ($arResult['USERS'] as $arUser) { ?>
                        <option
                                <? if ($arResult['EMPLOYEE']['HEAD'] == $arUser['ID']): ?>
                                    selected
                                <? endif; ?>
                                value="<?=$arUser['ID']?>"><?=$arUser['USER_INFO']?>
                        </option>
                    <? } ?>
                </select>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-4 col-form-label" for="bp_CATEGORY">Категория <span class="text-danger">*</span></label>
            <div class="col-sm-8 py-2">
                <select class="selectpicker form-control" data-dropup-auto="false" data-size="5" title="Выбрать" data-live-search="true"  data-live-search-placeholder="Найти" name="CATEGORY" id="bp_CATEGORY" required>
                    <? foreach ($arResult['CATEGORY'] as $arCategory) { ?>
                        <option value="<?=$arCategory['ID']?>"><?=$arCategory['VALUE']?></option>
                    <? } ?>
                </select>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-4 col-form-label" for="bp_DATA_NACHALA">Дата начала удаленной работы<br> (не ранее, чем за 2 рабочих дня от текущей даты) <span class="text-danger">*</span></label>
            <div class="col-sm-8 py-2">
                <input required="required" class="form-control" type="date" name="DATA_NACHALA" id="DATA_NACHALA" />
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-4 col-form-label" for="DATA_OKONCHANIYA">Дата окончания удаленной работы <span class="text-danger">*</span></label>
            <div class="col-sm-8 py-2">
                <input required="required" class="form-control" type="date" name="DATA_OKONCHANIYA" id="DATA_OKONCHANIYA" />
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-sm-2">
                <div class="text-right mb-3">
                    <button class="btn btn-primary btn-block" type="submit">Далее &rarr;</button>
                </div>
            </div>
            <div class="col-12 col-sm-11">
            </div>
        </div>
    </div>
</form>
