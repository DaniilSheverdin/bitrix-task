<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * @global $APPLICATION
 */
?>
<form class="needs-validation js-cit-prohozhdenie-stazhirovky" novalidate="" style="" id="js-cit-prohozhdenie-stazhirovky" action="<?=$APPLICATION->GetCurPage()?>" method="POST" autocomplete="Off">
    <input type="hidden" name="prohozhdenie-stazhirovky" value="add">
    <?=bitrix_sessid_post()?>
    <div class="alert d-none"></div>
    <div id="js--form-action-content" class="show">
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_ST_FIO_SOTRUDNIKA">ФИО сотрудника <span class="text-danger">*</span>:</label>
            <div class="col-sm-10">
                <select name="ST_FIO_SOTRUDNIKA" data-live-search="true" data-live-search-placeholder="Найти" data-actions-box="true" class="form-control selectpicker" id="bp_ST_FIO_SOTRUDNIKA" required="required">
                    <option value="0" disabled selected>Выбрать</option>
                    <? foreach ($arResult['ALL_USERS'] as $arUser) { ?>
                        <option value="<?=$arUser['ID']?>"><?=$arUser['LAST_NAME']?> <?=$arUser['NAME']?> <?=$arUser['SECOND_NAME']?></option>
                    <? } ?>
                </select>
            </div>
        </div>
		<div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_ST_DOLZHNOST">Должность <span class="text-danger">*</span>:</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="ST_DOLZHNOST" value="" id="bp_ST_DOLZHNOST" required="required" />
            </div>
        </div>
		<div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_ST_KURSY_NA_KORPORATIVNOM_UNIVERSITETE">Курсы на Корпоративном Университете <span class="text-danger">*</span>:</label>
            <div class="col-sm-10">
                <select multiple="multiple" class="selectpicker form-control" name="ST_KURSY_NA_KORPORATIVNOM_UNIVERSITETE[]" id="bp_ST_KURSY_NA_KORPORATIVNOM_UNIVERSITETE">
                    <? foreach($arResult['ST_KURSY_NA_KORPORATIVNOM_UNIVERSITETE'] as $arSitem) { ?>
                        <option value="<?=$arSitem['ID']?>" data-xml-id="<?=$arSitem['XML_ID']?>"><?=$arSitem['VALUE']?></option>
                    <? } ?>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_ST_ZADACHI_NA_STAZHIROVKU">Задачи на стажировку <span class="text-danger">*</span>:</label>
            <div class="col-sm-10">
                <div class="col-12 mb-3" data-mc="1">
                    <div class="row pb-3 alert alert-primary my-2">
                        <div class="col-xl-12">
                            <input type="text" class="form-control" name="ST_ZADACHI_NA_STAZHIROVKU[]" placeholder="Укажите подзадачу" value="" id="bp_ST_ZADACHI_NA_STAZHIROVKU" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 py-2"><button data-id="add-mc" type="button" title="Добавить задачу" class="btn btn-primary float-right">+</button></div>
        </div>
		<div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_ST_TREBOVANIYA_PO_ITOGU_STAZHIROVKI">Требования по итогу стажировки <span class="text-danger">*</span>:</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="ST_TREBOVANIYA_PO_ITOGU_STAZHIROVKI" value="" id="bp_ST_TREBOVANIYA_PO_ITOGU_STAZHIROVKI" required="required" />
            </div>
        </div>
		<div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_ST_SROK_OKONCHANIYA_STAZHIROVKI">Срок окончания стажировки <span class="text-danger">*</span>:</label>
            <div class="col-sm-10">
                <input type="date" class="form-control" name="ST_SROK_OKONCHANIYA_STAZHIROVKI" value="" id="bp_ST_SROK_OKONCHANIYA_STAZHIROVKI"  required="required" />
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