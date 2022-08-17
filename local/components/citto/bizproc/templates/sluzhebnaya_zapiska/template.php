<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<form class="needs-validation js-zayavka-na-mc" novalidate="" style="" id="js-zayavka-na-mc" action="<?=htmlspecialchars($_SERVER['REQUEST_URI'])?>" method="POST" autocomplete="Off">
    <input type="hidden" name="sluzhebnaya_zapiska" value="add">
    <?=bitrix_sessid_post()?>
    <div class="alert d-none"></div>
    <div id="js--form-action-content" class="show">

        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_RUKOVODITEL_ORGANIZATSII_OIV">Руководитель организации/ОИВ</label>
            <div class="col-sm-10">
                <select class="selectpicker form-control" data-dropup-auto="false" data-size="5" title="Выбрать" data-live-search="true" data-live-search-placeholder="Найти" name="RUKOVODITEL_ORGANIZATSII_OIV" required id="bp_RUKOVODITEL_ORGANIZATSII_OIV">
                    <? foreach ($arResult['USERS'] as $arUser) { ?>
                        <option value="<?=$arUser['ID']?>"><?=$arUser['LAST_NAME']?> <?=$arUser['NAME']?> <?=$arUser['SECOND_NAME']?> (<?=$arUser['UF_WORK_POSITION']?>)</option>
                    <? } ?>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_NEPOSREDSTVENNYY_RUKOVODITEL">Непосредственный руководитель</label>
            <div class="col-sm-10">
                <select class="selectpicker form-control" data-dropup-auto="false" data-size="5" title="Выбрать" data-live-search="true"  data-live-search-placeholder="Найти" name="NEPOSREDSTVENNYY_RUKOVODITEL" id="bp_NEPOSREDSTVENNYY_RUKOVODITEL">
                    <? foreach ($arResult['USERS'] as $arUser) { ?>
                        <option value="<?=$arUser['ID']?>"><?=$arUser['LAST_NAME']?> <?=$arUser['NAME']?> <?=$arUser['SECOND_NAME']?> (<?=$arUser['UF_WORK_POSITION']?>)</option>
                    <? } ?>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-2"></div>
            <div id="list_users" class="col-sm-10">
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_FIO_SOTRUDNIKOV">ФИО сотрудников</label>
            <div class="col-sm-10">
                <select multiple="multiple" class="selectpicker form-control" data-dropup-auto="false" data-size="5" title="Выбрать" data-live-search="true" data-live-search-placeholder="Найти" name="FIO_SOTRUDNIKOV[]" required id="bp_FIO_SOTRUDNIKOV">
                    <? foreach ($arResult['USERS'] as $arUser) { ?>
                        <option value="<?=$arUser['ID']?>"><?=$arUser['LAST_NAME']?> <?=$arUser['NAME']?> <?=$arUser['SECOND_NAME']?> (<?= mb_substr($arUser['UF_WORK_POSITION'], 0, 60); ?>...)</option>
                    <? } ?>
                </select>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_DATA_KOMANDIROVANIYA_S">Дата командирования с</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" placeholder="__.__.____" name="DATA_KOMANDIROVANIYA_S" value="" id="bp_DATA_KOMANDIROVANIYA_S" onclick="BX.calendar({node: this, field: this, bTime: true});" required />
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_DATA_KOMANDIROVANIYA_PO">Дата командирования по</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" placeholder="__.__.____" name="DATA_KOMANDIROVANIYA_PO" value="" id="bp_DATA_KOMANDIROVANIYA_PO" onclick="BX.calendar({node: this, field: this, bTime: true});" required />
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp_TSEL_KOMANDIROVANIYA">С целью</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="TSEL_KOMANDIROVANIYA" value="" id="bp_TSEL_KOMANDIROVANIYA" placeholder="подготовки к мероприятию" required/>
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-2 col-form-label">Места командирования</label>
            <div class="col-sm-10 mesta">
                <input type="text" class="form-control" placeholder="Венёвский район" required>
                <a href="#" id="add_mesta">Добавить ещё</a>
            </div>
        </div>

        <div class="row">
            <div class="col-12 col-sm-2">
                <div class="text-right mb-3">
                    <input type="hidden" class="form-control" name="MESTO_KOMANDIROVANIYA" value="" id="bp_MESTO_KOMANDIROVANIYA" required/>
                    <button class="btn btn-primary btn-block" type="submit">Далее &rarr;</button>
                </div>
            </div>
            <div class="col-12 col-sm-11">
            </div>
        </div>
    </div>
</form>