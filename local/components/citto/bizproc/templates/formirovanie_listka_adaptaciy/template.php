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
<form class="needs-validation js-formirovanie-listka-adaptaciy" novalidate="" style="" id="js-formirovanie-listka-adaptaciy" action="<?=htmlspecialchars($_SERVER['REQUEST_URI'])?>" method="POST" autocomplete="Off">
    <input type="hidden" name="formirovanie-listka-adaptaciy" value="add">
    <?=bitrix_sessid_post()?>
    <div class="alert d-none"></div>
    <div id="js--form-action-content" class="show">
        <div class="form-group row">
            <label class="col-sm-4 col-form-label" for="bp_FLA_FIO">ФИО <span class="text-danger">*</span></label>
            <div class="col-sm-8 py-2">
                <input required="required" class="form-control" type="text" name="FLA_FIO" value="<?=$arResult['FLA_FIO']?>" id="bp_FLA_FIO" />
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-4 col-form-label" for="bp_FLA_PODRAZDELENIE">Подразделение <span class="text-danger">*</span></label>
            <div class="col-sm-8 py-2">
                <input required="required" class="form-control" type="text" name="FLA_PODRAZDELENIE" value="<?=$arResult['FLA_PODRAZDELENIE']?>" id="bp_FLA_PODRAZDELENIE" />
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-4 col-form-label" for="bp_FLA_RUKOVODITEL">Выберите непосредственного руководителя (начните набирать фамилию в строке) <span class="text-danger">*</span></label>
            <div class="col-sm-8">
                <select class="selectpicker form-control" data-live-search="true" data-live-search-placeholder="Найти" data-actions-box="true" name="FLA_RUKOVODITEL" required="required" id="bp_FLA_RUKOVODITEL">
                    <option value="0" disabled selected>Не выбран</option>
                    <? foreach ($arResult['RUKOVODITEL'] as $arRucovoditel) { ?>
                        <option value="<?=$arRucovoditel['ID']?>"><?=$arRucovoditel['LAST_NAME']?> <?=$arRucovoditel['NAME']?> <?=$arRucovoditel['SECOND_NAME']?></option>
                    <? } ?>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-4 col-form-label" for="bp_FLA_DOLZHNOST">Должность <span class="text-danger">*</span></label>
            <div class="col-sm-8">
                <input type="tel" class="form-control" name="FLA_DOLZHNOST" value="<?=$arResult['FLA_DOLZHNOST']?>" id="bp_FLA_DOLZHNOST" required="required" />
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-4 col-form-label" for="bp_FLA_GOGS">Являетесь ли вы государственным служащим? <span class="text-danger">*</span></label>
            <div class="col-sm-8">
                <select class="form-control" name="FLA_GOGS" required="required" id="bp_FLA_GOGS">
                    <option value="0" disabled selected="selected">Не выбран</option>
                    <? foreach($arResult['FLA_GOGS'] as $arSitem) { ?>
                        <option value="<?=$arSitem['ID']?>" data-xml-id="<?=$arSitem['XML_ID']?>"><?=$arSitem['VALUE']?></option>
                    <? } ?>
                </select>
            </div>
        </div>
        <div class="form-group row px-3">
            <div class="alert alert-info w-100">
                <p>Основные курсы, которые необходимо пройти по листу адаптации:</p>
                <ul>
                    <? foreach($arResult['FLA_OSNOVNYE_KURSY'] as $arCourse) { ?>
                        <li><?=$arCourse['VALUE']?></li>
                    <? } ?>
                </ul>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-4 col-form-label" for="bp_FLA_KURSY_ADAPTACIY">Выберите дополнительные онлайн курсы, которые необходимо включить в Лист адаптации</label>
            <div class="col-sm-8">
                <select multiple="multiple" class="selectpicker form-control" name="FLA_KURSY_ADAPTACIY[]" id="bp_FLA_KURSY_ADAPTACIY">
                    <? foreach($arResult['FLA_KURSY_ADAPTACIY'] as $arSitem) { ?>
                        <option value="<?=$arSitem['ID']?>" data-xml-id="<?=$arSitem['XML_ID']?>"><?=$arSitem['VALUE']?></option>
                    <? } ?>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-4 col-form-label" for="bp_FLA_KURSY_ADAPTACIY">Выберите обязанности, которые будут присутствовать в деятельности (обращаем внимание, что в списке указаны обязанности, по которым предусмотрено обучение на КУ. Обучение профильным обязанностям осуществляется на рабочем месте с Наставником) <span class="text-danger">*</span></label>
            <div class="col-sm-8">
                <select multiple="multiple" class="selectpicker form-control" name="FLA_OBYAZ_NS[]" id="bp_FLA_OBYAZ_NS">
                    <? foreach($arResult['FLA_OBYAZ_NS'] as $arSitem) { ?>
                        <option value="<?=$arSitem['ID']?>" data-xml-id="<?=$arSitem['XML_ID']?>"><?=$arSitem['VALUE']?></option>
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