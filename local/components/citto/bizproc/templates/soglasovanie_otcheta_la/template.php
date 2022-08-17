<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<form class="needs-validation js-soglasovanie-otcheta-la" novalidate="" style="" id="js-soglasovanie-otcheta-la" action="<?=htmlspecialchars($_SERVER['REQUEST_URI'])?>" method="POST" autocomplete="Off">
    <input type="hidden" name="soglasovanie-otcheta-la" value="add">
    <?=bitrix_sessid_post()?>
    <div class="alert d-none"></div>
    <div id="js--form-action-content" class="show">
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_SOLA_FIO_NS">ФИО нового сотрудника <span class="text-danger">*</span></label>
            <div class="col-sm-10 py-2">
                <select class="selectpicker form-control" data-live-search="true" data-live-search-placeholder="Найти" data-actions-box="true" name="SOLA_FIO_NS" required="required" id="bp_SOLA_FIO_NS">
                    <option value="0" disabled="disabled" selected="selected">Выбрать</option>
                    <? foreach ($arResult['SOLA_FIO_LIST'] as $arSotrItem) { ?>
                        <option value="<?=$arSotrItem['ID']?>"><?=$arSotrItem['LAST_NAME']?> <?=$arSotrItem['NAME']?> <?=$arSotrItem['SECOND_NAME']?></option>
                    <? } ?>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_SLA_RUKOVODITEL_SOTRUDNIKA">Руководитель нового сотрудника <span class="text-danger">*</span></label>
            <div class="col-sm-10 py-2">
                <select class="selectpicker form-control" data-live-search="true" data-live-search-placeholder="Найти" data-actions-box="true" name="SLA_RUKOVODITEL_SOTRUDNIKA" required="required" id="bp_SLA_RUKOVODITEL_SOTRUDNIKA">
                    <option value="0" disabled="disabled" selected="selected">Выбрать</option>
                    <? foreach ($arResult['SOLA_FIO_LIST'] as $arSotrItem) { ?>
                        <option value="<?=$arSotrItem['ID']?>"><?=$arSotrItem['LAST_NAME']?> <?=$arSotrItem['NAME']?> <?=$arSotrItem['SECOND_NAME']?></option>
                    <? } ?>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_SOLA_OTCHET_LA">Отчет по листу адаптации <span class="text-danger">*</span></label>
            <div class="col-sm-10 py-2">
                <input required="required" class="form-control" type="file" name="SOLA_OTCHET_LA" value="" id="bp_SOLA_OTCHET_LA" />
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-sm-2">
                <div class="text-right mb-3">
                    <button class="btn btn-primary btn-block" type="submit">Согласовать</button>
                </div>
            </div>
            <div class="col-12 col-sm-11">
            </div>
        </div>
    </div>
</form>