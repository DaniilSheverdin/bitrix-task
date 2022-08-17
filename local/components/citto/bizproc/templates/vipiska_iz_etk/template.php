<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>

<? if ($arResult['WORKBOOK_ELECTRONIC'] == 1): ?>
<form enctype="multipart/form-data" class="needs-validation js-vipiska_iz_etk" novalidate="" style="" id="js-vipiska_iz_etk" action="<?=htmlspecialchars($_SERVER['REQUEST_URI'])?>" method="POST" autocomplete="Off">
    <input type="hidden" name="vipiska_iz_etk" value="add">
    <?=bitrix_sessid_post()?>
    <div class="alert d-none"></div>
    <div id="js--form-action-content" class="show">
        <div class="form-group row">
            <label class="col-sm-4 col-form-label" for="bp_TSEL_KOMANDIROVANIYA">Причина <span class="text-danger">*</span></label>
            <div class="col-sm-8 py-2">
                <input type="text" class="form-control" name="PRICHINA" value="" id="bp_PRICHINA" required/>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-4 col-form-label" for="bp_MAIL">Электронная почта <span class="text-danger">*</span></label>
            <div class="col-sm-8 py-2">
                <input type="text" class="form-control" name="MAIL" value="<?=$arResult['DEFAULT_MAIL']?>" id="bp_MAIL" required/>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-4 col-form-label" for="bp_FORMA">Форма выписки</label>
            <div class="col-sm-8 py-2">
                <select class="selectpicker form-control" data-dropup-auto="false" data-size="5" title="Выбрать" data-live-search="true"  data-live-search-placeholder="Найти" name="FORMA" id="bp_FORMA">
                    <? foreach ($arResult['FORMA'] as $sDeyat) { ?>
                        <option value="<?=$sDeyat['ID']?>"><?=$sDeyat['NAME']?></option>
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
<? else: ?>
    <div class="alert alert-danger" role="alert">
        Вы не можете запустить данный бизнес-процесс, так как ЭТК по Вам не ведётся.
    </div>
<? endif; ?>
