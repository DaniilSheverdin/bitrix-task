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
<form class="needs-validation js-vnutrenne_peremeshenie" novalidate="" style="" id="js-vnutrenne_peremeshenie" action="<?=htmlspecialchars($_SERVER['REQUEST_URI'])?>" method="POST" autocomplete="Off">
    <input type="hidden" name="vnutrenne_peremeshenie" value="add">
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
            <label class="col-sm-2 col-form-label" for="bp_PODRAZDELENIE">Подразделение <span class="text-danger">*</span></label>
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
            <label class="col-sm-2 col-form-label" for="bp_ADDRESS">Адрес <span class="text-danger">*</span></label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="ADDRESS" id="bp_ADDRESS" required="required" />
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_TARGET">Цель перемещения имущества <span class="text-danger">*</span></label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="TARGET" id="bp_TARGET" required="required" />
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
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_OTKUDA">Откуда перемещается имущество <span class="text-danger">*</span></label>
            <div class="col-sm-10">
                <select class="form-control" name="OTKUDA" required id="bp_OTKUDA">
                    <option value="0" disabled selected>Выбрать</option>
                    <? foreach ($arResult['OTKUDA'] as $building) { ?>
                        <option value="<?=$building['ID']?>"><?=$building['NAME']?></option>
                    <? } ?>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_KUDA">Куда перемещается имущество <span class="text-danger">*</span></label>
            <div class="col-sm-10">
                <select class="form-control" name="KUDA" required id="bp_KUDA">
                    <option value="0" disabled selected>Выбрать</option>
                    <? foreach ($arResult['KUDA'] as $building) { ?>
                        <option value="<?=$building['ID']?>"><?=$building['NAME']?></option>
                    <? } ?>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_PODROBNEE">Подробнее</span></label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="PODROBNEE" placeholder="из кабинета 669 в кабинет 210" id="bp_PODROBNEE" />
            </div>
        </div>
        <div class="mb-3">
            <span>Укажите имущество</span>
            <div class="row my-2 js-property">
                <div class="col-md-5"><input class="form-control js-objname" type="text" placeholder="Объект" required="required"></div>
                <div class="col-md-5"><input class="form-control js-objnumber" type="number" placeholder="Инв. номер" required="required"></div>
            </div>
            <input type="hidden" name="DVIZHIMOE_HTML">
            <a href="javascript: void(0);" id="js-property-add">Добавить ещё</a>
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