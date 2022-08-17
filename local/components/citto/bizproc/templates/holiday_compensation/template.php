<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<form class="needs-validation js-holiday-compensation" novalidate="" style="" id="js-holiday-compensation" action="<?=htmlspecialchars($_SERVER['REQUEST_URI'])?>" method="POST" autocomplete="Off">
    <input type="hidden" name="holiday-compensation" value="add">
    <?=bitrix_sessid_post()?>
    <div class="alert d-none"></div>
    <div id="js--form-action-content" class="show">
        <div class="form-group row">
            <label class="col-sm-4 col-form-label" for="bp_CH_KOLICHESTVO_DNEY_KOMPENSATSY">Количество дней для компенсации отпуска <span class="text-danger">*</span></label>
            <div class="col-sm-8 py-2">
                <input required="required" class="form-control" type="text" name="CH_KOLICHESTVO_DNEY_KOMPENSATSY" value="<?=$arResult['CH_KOLICHESTVO_DNEY_KOMPENSATSY']?>" id="bp_CH_KOLICHESTVO_DNEY_KOMPENSATSY" />
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