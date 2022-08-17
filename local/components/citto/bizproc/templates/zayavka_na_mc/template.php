<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<form class="needs-validation js-zayavka-na-mc" novalidate="" style="" id="js-zayavka-na-mc" action="<?=htmlspecialchars($_SERVER['REQUEST_URI'])?>" method="POST" autocomplete="Off">
    <input type="hidden" name="zayavka-na-mc" value="add">
    <?=bitrix_sessid_post()?>
    <div class="alert d-none"></div>
    <div id="js--form-action-content" class="show">
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_MC_VEDOMSTVO">Ведомство</label>
            <div class="col-sm-10">
                <select <?=(!$arResult['DISABLED_OTDEL']) ?: 'disabled' ?> class="form-control" name="MC_VEDOMSTVO_SEL" required id="bp_MC_VEDOMSTVO">
                    <option value="0" disabled selected>Выбрать</option>
                    <? foreach ($arResult['OTDELLIST'] as $arOtdel) { ?>
                        <option <?=($arResult['DEFAULT_DEP_ID'] == $arOtdel['ID'])?' selected="selected"':''?> value="<?=$arOtdel['ID']?>"><?=$arOtdel['NAME']?></option>
                    <? } ?>
                </select>
                <input type="hidden" name="MC_VEDOMSTVO" value="<?=$arResult['DEFAULT_DEP_ID']?>" />
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_MC_SOTRUDNIK">Сотрудник</label>
            <div class="col-sm-10 py-2">
                <span><?=$arResult['MC_SOTRUDNIK_NAME']?></span>
                <input type="hidden" name="MC_SOTRUDNIK" value="<?=$arResult['ID_MC_SOTRUDNIK']?>" id="bp_MC_SOTRUDNIK" />
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_MC_RUKOVODITEL_SOGLASOVANIE_PROPUSK">Руководитель, с которым следует согласовать пропуск</label>
            <div class="col-sm-10">
                <select class="selectpicker form-control" data-live-search="true" data-live-search-placeholder="Найти" data-actions-box="true" name="MC_RUKOVODITEL_SOGLASOVANIE_PROPUSK" required id="bp_MC_RUKOVODITEL_SOGLASOVANIE_PROPUSK">
                    <option value="0" disabled selected>Выбрать</option>
                    <? foreach ($arResult['RUKOVODITEL'] as $arRucovoditel) { ?>
                        <option <?=($arResult['RUKOVODITEL_ID'] == $arRucovoditel['ID'])?' selected="selected"':''?> value="<?=$arRucovoditel['ID']?>"><?=$arRucovoditel['LAST_NAME']?> <?=$arRucovoditel['NAME']?></option>
                    <? } ?>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_MC_MATERIALNO_OTVETSTVENNIY">Выбор материально ответственного лица,  за вид материальной ценности подлежащей выносу УД АП ТО</label>
            <div class="col-sm-10">
                <select class="selectpicker form-control" data-live-search="true" data-live-search-placeholder="Найти" data-actions-box="true" name="MC_MATERIALNO_OTVETSTVENNIY" required id="bp_MC_MATERIALNO_OTVETSTVENNIY">
                    <option value="0" disabled selected>Выбрать</option>
                    <? foreach ($arResult['MATERIALNO_OTVETSTVENNIY'] as $arMatotvny) { ?>
                        <option value="<?=$arMatotvny['ID']?>"><?=$arMatotvny['LAST_NAME']?> <?=$arMatotvny['NAME']?></option>
                    <? } ?>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_MC_VYBOR_ZDANIYA">Выбор здания</label>
            <div class="col-sm-10">
                <select class="form-control" name="MC_VYBOR_ZDANIYA" required id="bp_MC_VYBOR_ZDANIYA">
                    <option value="0" disabled selected>Выбрать</option>
                    <? foreach ($arResult['ZDANIYA'] as $building) { ?>
                        <option value="<?=$building['ID']?>"><?=$building['NAME']?></option>
                    <? } ?>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_MC_KONTAKTNYY_TELEFON">Контактный телефон</label>
            <div class="col-sm-10">
                <input type="tel" class="form-control" name="MC_KONTAKTNYY_TELEFON" value="" id="bp_MC_KONTAKTNYY_TELEFON" required />
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_MC_DATA_VREMYA_VYNOSA">Дата и время выноса</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" placeholder="__.__.____ __:__" name="MC_DATA_VREMYA_VYNOSA" value="" id="bp_MC_DATA_VREMYA_VYNOSA" onclick="BX.calendar({node: this, field: this, bTime: true});" required />
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_MC_KOLICHESTVO_MC">Количество материальных ценностей</label>
            <div class="col-sm-10">
                <input type="text" disabled="disabled" class="form-control" name="MC_KOLICHESTVO_MC" value="1" id="bp_MC_KOLICHESTVO_MC" required />
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_MC_NAIMENOVANIE_MC">Информация о материальных ценностях</label>
            <div class="col-sm-10">
                <div class="col-12 mb-3" data-mc="1">
                <div class="row pb-3 alert alert-primary my-2">
                    <div class="col-xl-6">
                        <input type="text" class="form-control" placeholder="Название МЦ" name="MC_NAIMENOVANIE_MC[]" value="" id="bp_MC_NAIMENOVANIE_MC" />
                    </div>
                    <div class="col-xl-6">
                        <input type="text" class="form-control" placeholder="Инвентарный номер МЦ" name="MC_INVENTARNY_NOMER[]" value="" />
                    </div>
                </div>
                </div>
            </div>
            <div class="col-12 py-2"><button data-id="add-mc" type="button" title="Добавить МЦ" data-field="MC_NAIMENOVANIE_MC[]" data-count="MC_KOLICHESTVO_MC" class="btn btn-primary float-right">+</button></div>
        </div>

        <div class="row">
            <div class="col-12 col-sm-2">
                <div class="text-right mb-3">
                    <input type="hidden" name="MC_BUKHGALTER" value="<?=$arResult['BUHGALTER_ID']?>" />
                    <input type="hidden" name="MC_ZAMESTITELI_GLAVNOGO_BUKHGALTERA" value="<?=$arResult['BUHGALTER_ZAM_IDS']?>" />
                    <button class="btn btn-primary btn-block" type="submit">Далее &rarr;</button>
                </div>
            </div>
            <div class="col-12 col-sm-11">
            </div>
        </div>
    </div>
</form>