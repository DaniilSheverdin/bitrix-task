<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<form class="needs-validation js-zapros_poyasneniy" novalidate="" style="" id="js-zapros_poyasneniy" action="<?=htmlspecialchars($_SERVER['REQUEST_URI'])?>" method="POST" autocomplete="Off">
    <input type="hidden" name="zapros_poyasneniy" value="add">
    <?=bitrix_sessid_post()?>
    <div class="alert d-none"></div>
    <div id="js--form-action-content" class="show">
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_STATUS">Статус <span class="text-danger">*</span></label>
            <div class="col-sm-10">
                <select class="selectpicker form-control" data-dropup-auto="false" data-size="5" title="Выбрать" data-live-search="true"  data-live-search-placeholder="Найти" name="STATUS" id="bp_STATUS">
                    <? foreach ($arResult['STATUS'] as $sStatus) { ?>
                        <option value="<?=$sStatus['ID']?>"><?=$sStatus['NAME']?></option>
                    <? } ?>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_DATE_FROM">Отчётный период <span class="text-danger">*</span></label>
            <div class="col-sm-10">
                <select class="form-control" data-dropup-auto="false" data-size="5" title="Выбрать" data-live-search="true"  data-live-search-placeholder="Найти" name="DATE_FROM" id="bp_DATE_FROM">
                    <? foreach ($arResult['DATE'] as $iYear) { ?>
                        <option value="<?=$iYear?>"><?=$iYear?></option>
                    <? } ?>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_DATA_PODACHI">Отчётная дата <span class="text-danger">*</span></label>
            <div class="col-sm-10">
                <input type="date" class="form-control" name="DATA_PODACHI" id="bp_DATA_PODACHI" required="required" />
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_SLUZHASHCHIY">Государственный гражданский служащий</label>
            <div class="col-sm-10">
                <select class="selectpicker form-control" data-dropup-auto="false" data-size="5" title="Выбрать" data-live-search="true"  data-live-search-placeholder="Найти" name="SLUZHASHCHIY" id="bp_SLUZHASHCHIY">
                    <? foreach ($arResult['USERS'] as $arUser) { ?>
                        <option value="<?=$arUser['ID']?>"><?=$arUser['LAST_NAME']?> <?=$arUser['NAME']?> <?=$arUser['SECOND_NAME']?>  (<?=$arUser['UF_WORK_POSITION']?>)</option>
                    <? } ?>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_DOLZHNOSTNOE_LITSO">Руководитель, которому будет направлен документ на подпись</label>
            <div class="col-sm-10">
                <select class="selectpicker form-control" data-dropup-auto="false" data-size="5" title="Выбрать" data-live-search="true"  data-live-search-placeholder="Найти" name="DOLZHNOSTNOE_LITSO" id="bp_DOLZHNOSTNOE_LITSO">
                    <? foreach ($arResult['OFFICIALS'] as $arUser) { ?>
                        <option value="<?=$arUser['ID']?>"><?=$arUser['LAST_NAME']?> <?=$arUser['NAME']?> <?=$arUser['SECOND_NAME']?></option>
                    <? } ?>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_RESULT">Результат анализа <span class="text-danger">*</span></label>
            <div class="col-sm-10">
                <textarea class="form-control" name="RESULT" id="bp_RESULT" required="required"></textarea>
            </div>
        </div>
        <input type="hidden" name = "UID">
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
