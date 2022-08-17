<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<form enctype="multipart/form-data" class="needs-validation js-perenos_otpusk_czn" novalidate="" style="" id="js-perenos_otpusk_czn" action="<?=htmlspecialchars($_SERVER['REQUEST_URI'])?>" method="POST" autocomplete="Off">
    <input type="hidden" name="perenos_otpusk_czn" value="add">
    <input type="hidden" name="UVEDOMLENIE" value="<?=$arResult['UVEDOMLENIE']?>">
    <?=bitrix_sessid_post()?>
    <div class="alert d-none"></div>
    <div id="js--form-action-content" class="show">
        <div class="form-group row">
            <label class="col-sm-4 col-form-label" for="bp_OTPUSK__FROM">Дата начала отпуска который следует перенести <span class="text-danger">*</span></label>
            <div class="col-sm-8 py-2">
                <input required="required" class="form-control" type="date" name="OTPUSK__FROM" id="OTPUSK__FROM" value="<?=$arResult['DATE_DEFAULT']?>" />
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-4 col-form-label" for="bp_OTPUSK__DAYS">Длительность отпуска который следует перенести <span class="text-danger">*</span></label>
            <div class="col-sm-8 py-2">
                <input required="required" class="form-control" type="number" name="OTPUSK__DAYS" id="OTPUSK__DAYS" value="<?=$arResult['DAYS_DEFAULT']?>" />
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-4 col-form-label" for="PRICHINA">Укажите причину <span class="text-danger">*</span></label>
            <div class="col-sm-8 py-2">
                <select class="form-control" name="PRICHINA" required id="PRICHINA">
                    <option value="0" disabled selected>Выбрать</option>
                    <? foreach ($arResult['PRICHINA'] as $building) { ?>
                        <option value="<?= $building['ID'] ?>"><?= $building['NAME'] ?></option>
                    <? } ?>
                </select>
            </div>
        </div>
        <div class="form-group row" id="node_inaya_prichina">
            <label class="col-sm-4 col-form-label" for="bp_INAYA_PRICHINA">Иная причина <span class="text-danger">*</span></label>
            <div class="col-sm-8 py-2">
                <input class="form-control" type="text" name="INAYA_PRICHINA" id="INAYA_PRICHINA" />
            </div>
        </div>

        <div class="mb-3">
            <span>Дата и дни переноса</span>
            <div class="row my-2 js-property">
                <div class="col-md-5"><input class="form-control js-objname" type="date" placeholder="Дата" required="required"></div>
                <div class="col-md-5"><input class="form-control js-objnumber" type="number" placeholder="Дни" required="required"></div>
            </div>
            <input type="hidden" name="MASSIV_OTPUSKOV">
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
