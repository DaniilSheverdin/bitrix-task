<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

?>
<div class="info">
    <span>Заказ канцтоваров осуществляется в последнюю неделю каждого месяца. Выдача канцтоваров, заказанных ранее, осуществляется в первую неделю каждого месяца.</span>
</div>
<form class="needs-validation js-zakaz-kanctovarov" novalidate="" style="" id="js-zakaz-kanctovarov" action="<?=htmlspecialchars($_SERVER['REQUEST_URI'])?>" method="POST" autocomplete="Off">
    <input type="hidden" name="zakaz-kanctovarov" value="add">
    <?=bitrix_sessid_post()?>
    <div class="alert d-none"></div>
    <div id="js--form-action-content" class="show">
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_FIO">ФИО <span class="text-danger">*</span></label>
            <div class="col-sm-10 py-2">
                <input required="required" class="form-control" type="text" <?= (empty($arResult['FIO'])) ?: 'readonly'; ?>  name="FIO" value="<?=$arResult['FIO']?>" id="bp_FIO" />
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_PODRAZDELENIE"> Подразделение <span class="text-danger">*</span></label>
            <div class="col-sm-10 py-2">
                <input required="required" class="form-control" type="text" <?= (empty($arResult['PODRAZDELENIE'])) ?: 'readonly'; ?> name="PODRAZDELENIE" value="<?=$arResult['PODRAZDELENIE']?>" id="bp_PODRAZDELENIE" />
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_DOLZHNOST">Должность <span class="text-danger">*</span></label>
            <div class="col-sm-10">
                <input type="tel" class="form-control" <?= (empty($arResult['DOLZHNOST'])) ?: 'readonly'; ?> name="DOLZHNOST" value="<?=$arResult['DOLZHNOST']?>" id="bp_DOLZHNOST" required="required" />
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_DATA_VREMYA">Дата, время <span class="text-danger">*</span></label>
            <div class="col-sm-10">
                <input type="datetime-local" class="form-control" name="DATA_VREMYA" value="<?=$arResult['DATA_VREMYA']?>" id="bp_DATA_VREMYA" required="required" />
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="KOLICHESTVO_RUCHEK">Ручка (кол-во) <span class="text-danger">*</span></label>
            <div class="col-sm-10">
                <input type="number" class="form-control" name="KOLICHESTVO_RUCHEK" value="<?=$arResult['KOLICHESTVO_RUCHEK']?>" id="bp_KOLICHESTVO_RUCHEK" required="required" />
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_BUMAGA_KOL_VO">Бумага (кол-во) <span class="text-danger">*</span></label>
            <div class="col-sm-10">
                <input type="number" class="form-control" name="BUMAGA_KOL_VO" value="<?=$arResult['BUMAGA_KOL_VO']?>" id="bp_BUMAGA_KOL_VO" required="required" />
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_KARANDASH_KOL_VO">Карандаш (кол-во) <span class="text-danger">*</span></label>
            <div class="col-sm-10">
                <input type="number" class="form-control" name="KARANDASH_KOL_VO" value="<?=$arResult['KARANDASH_KOL_VO']?>" id="bp_KARANDASH_KOL_VO" required="required" />
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_RUKOVODITEL">Руководитель / зам. руководителя подразделения ОИВ</label>
            <div class="col-sm-10">
                <select class="selectpicker form-control" data-dropup-auto="false" data-size="5" title="Выбрать" data-live-search="true"  data-live-search-placeholder="Найти" name="RUKOVODITEL" id="bp_RUKOVODITEL">
                    <? foreach ($arResult['USERS'] as $arUser) { ?>
                        <option value="<?=$arUser['ID']?>"><?=$arUser['LAST_NAME']?> <?=$arUser['NAME']?> <?=$arUser['SECOND_NAME']?></option>
                    <? } ?>
                </select>
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