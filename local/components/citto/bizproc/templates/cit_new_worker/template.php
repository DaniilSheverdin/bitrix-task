<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<form class="needs-validation js-cit_new_worker" novalidate="" style="" id="cit_new_worker" action="<?=htmlspecialchars($_SERVER['REQUEST_URI'])?>" method="POST" autocomplete="Off">
    <input type="hidden" name="cit_new_worker" value="add">
    <?=bitrix_sessid_post()?>
    <div class="alert d-none"></div>
    <div id="js--form-action-content" class="show">
        <div class="form-group row">
            <label class="col-sm-3 col-form-label" for="bp_FIO_NOVOGO_SOTRUDNIKA">Ф.И.О. <span class="text-danger">*</span></label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="FIO_NOVOGO_SOTRUDNIKA" value="" id="bp_FIO_NOVOGO_SOTRUDNIKA" required />
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-3 col-form-label" for="bp_DOLZHNOST">Должность <span class="text-danger">*</span></label>
            <div class="col-sm-9">
                <input type="text" class="form-control" name="DOLZHNOST" value="" id="bp_DOLZHNOST" required />
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-3 col-form-label">Отдел <span class="text-danger">*</span></label>
            <div class="col-sm-9">
                <select class="form-control js-work-otdel" name="WORK_OTDEL" required>
                    <option value="0" disabled selected>Выбрать</option>
                    <? foreach ($arResult['OTDELLIST'] as $arOtdel) : ?>
                        <option value="<?=$arOtdel['ID']?>"><?=$arOtdel['NAME']?></option>
                    <? endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-group row" style="display: none;">
            <label class="col-sm-3 col-form-label">Руководитель <span class="text-danger">*</span></label>
            <div class="col-sm-9">
                <select class="selectpicker form-control js-rukovoditel-otdela" data-live-search="true" data-live-search-placeholder="Найти" data-actions-box="true" name="RUKOVODITEL_OTDELA" required="required">
                    <option value="0" disabled selected>Не выбран</option>
                    <? foreach ($arResult['RUKOVODITEL'] as $arRucovoditel) { ?>
                        <option value="<?=$arRucovoditel['ID']?>"><?=$arRucovoditel['LAST_NAME']?> <?=$arRucovoditel['NAME']?> <?=$arRucovoditel['SECOND_NAME']?></option>
                    <? } ?>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-3 col-form-label" for="bp_RELEASE_DATE">Дата выхода нового сотрудника <span class="text-danger">*</span></label>
            <div class="col-sm-9">
                <input type="date" class="form-control" name="RELEASE_DATE" value="" id="bp_RELEASE_DATE" required />
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-sm-3">
                <div class="text-right mb-3">
                    <button class="btn btn-success btn-block" type="submit">Далее &rarr;</button>
                </div>
            </div>
            <div class="col-12 col-sm-9">
            </div>
        </div>
    </div>
</form>