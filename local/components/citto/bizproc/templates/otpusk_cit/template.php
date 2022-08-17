<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<form enctype="multipart/form-data" class="needs-validation js-otpusk_cit" novalidate="" style="" id="js-otpusk_cit" action="<?=htmlspecialchars($_SERVER['REQUEST_URI'])?>" method="POST" autocomplete="Off">
    <input type="hidden" name="otpusk_cit" value="add">
    <?=bitrix_sessid_post()?>
    <div class="alert d-none"></div>
    <div id="js--form-action-content" class="show">
        <div class="form-group row">
            <label class="col-sm-4 col-form-label" for="bp_POLZOVATEL">Пользователь <span class="text-danger">*</span></label>
            <div class="col-sm-8 py-2">
                <select class="selectpicker form-control" data-dropup-auto="false" data-size="5" title="Выбрать" data-live-search="true"  data-live-search-placeholder="Найти" name="POLZOVATEL" id="bp_POLZOVATEL">
                    <? foreach ($arResult['USERS'] as $arUser) { ?>
                        <option value="<?=$arUser['ID']?>"><?=$arUser['USER_INFO']?></option>
                    <? } ?>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-4 col-form-label" for="bp_DATA_NACHALA">Дата начала <span class="text-danger">*</span></label>
            <div class="col-sm-8 py-2">
                <input required="required" class="form-control" type="date" name="DATA_NACHALA" id="DATA_NACHALA" />
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-4 col-form-label" for="bp_KOLICHESTVO_DNEY">Количество дней <span class="text-danger">*</span></label>
            <div class="col-sm-8 py-2">
                <input required="required" class="form-control" type="number" name="KOLICHESTVO_DNEY" id="KOLICHESTVO_DNEY" />
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
