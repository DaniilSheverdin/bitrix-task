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
<form class="needs-validation js-zayavka-na-kartridzhy-sudam" novalidate="" style="" id="js-zayavka-na-kartridzhy-sudam" action="<?=htmlspecialchars($_SERVER['REQUEST_URI'])?>" method="POST" autocomplete="Off">
    <input type="hidden" name="zayavka-na-kartridzhy-sudam" value="add">
    <?=bitrix_sessid_post()?>
    <div class="alert d-none"></div>
    <div id="js--form-action-content" class="show">
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_WJL_FIO">ФИО</label>
            <div class="col-sm-10 py-2">
                <span><?=$arResult['WJL_FIO']?></span>
                <input type="hidden" name="WJL_FIO" value="<?=$arResult['WJL_FIO']?>" id="bp_WJL_FIO" />
                <input type="hidden" name="WJL_POLZOVATEL" value="<?=$arResult['WJL_POLZOVATEL']?>" id="bp_WJL_POLZOVATEL" />
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_WJL_DOLZHNOST">Должность</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="WJL_DOLZHNOST" value="<?=$arResult['WJL_DOLZHNOST']?>" id="bp_WJL_DOLZHNOST" required="required" />
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_WJL_PODRAZDELENE">Подразделение</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="WJL_PODRAZDELENE" value="<?=$arResult['WJL_PODRAZDELENE']?>" id="bp_WJL_PODRAZDELENE" required="required" />
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_WJL_DATA_PODACHI_ZAYAVKI">Дата подачи заявки</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" placeholder="__.__.____ __:__" name="WJL_DATA_PODACHI_ZAYAVKI" value="<?=$arResult['WJL_DATA_PODACHI_ZAYAVKI']?>" id="bp_WJL_DATA_PODACHI_ZAYAVKI" onclick="BX.calendar({node: this, field: this, bTime: true});" required="required" />
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_WJL_SODERZHANIE">Содержание</label>
            <div class="col-sm-10">
                <div class="col-12 mb-3">
                    <div class="row">
                        <div class="col-xl-4 mb-3 text-center">
                            Марка и модель МФУ/Принтера
                        </div>
                        <div class="col-xl-4 mb-3 text-center">
                            Расходный материал
                        </div>
                        <div class="col-xl-4 mb-3 text-center">
                            Количество
                        </div>
                    </div>
                </div>
                <div class="col-12 mb-3" data-mc="1">
                    <div class="row pb-3 alert alert-primary my-2 position-relative">
                        <div class="col-xl-4">
                            <select class="form-control" name="WJL_SODERZHANIE_PRINTER[]" id="bp_WJL_SODERZHANIE">
                                <option value="0" selected="selected" disabled="disabled">Не выбрано</option>
                                <? foreach($arResult['PRINTERS'] as $arItem) { ?>
                                    <option value="<?=$arItem['ID']?>" data-mat-id="<?=implode(',', $arItem['k_material'])?>"><?=$arItem['UF_WJL_PRINTER_NAME']?></option>
                                <? } ?>
                            </select>
                        </div>
                        <div class="col-xl-4">
                            <select class="form-control" name="WJL_SODERZHANIE_MATERIAL[]">
                                <option value="0" selected="selected" disabled="disabled">Не выбрано</option>
                                <? foreach($arResult['PRINTER_MATERIALS'] as $arItem) { ?>
                                    <option value="<?=$arItem['ID']?>" data-printer-id="<?=implode(',', $arItem['printer'])?>"><?=$arItem['UF_WJL_MATERIAL_NAME']?></option>
                                <? } ?>
                            </select>
                        </div>
                        <div class="col-xl-4">
                            <input class="form-control" pattern="[0-9]+" type="number" value="1" name="WJL_SODERZHANIE_NUMBER[]" />
                        </div>
                        <div class="closed position-absolute text-danger" style="right: 5px; top: 15px; width: 20px; height: 20px; cursor: pointer; font-size: 20px;">&times;</div>
                    </div>
                </div>
            </div>
            <div class="col-12 py-2"><button id="add-mc" type="button" title="Добавить расходный материал" class="btn btn-primary float-right">+</button></div>
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