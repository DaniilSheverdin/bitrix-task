<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<div class="info">
    <span>Заявка подается на получение заверенных копий законов Тульской области, правовых актов Губернатора Тульской области, правительства Тульской области, приказов
    заместителя Губернатора Тульской области – руководителя аппарата правительства Тульской области – начальника главного управления государственной службы и кадров
    аппарата правительства Тульской области за период с 2013 года по настоящее время. Для получения заверенных копий в установленном порядке необходимо иметь электронную
    подпись руководителя органа исполнительной власти (подразделения аппарата правительства), которой будет подписываться печатная форма запроса.Срок изготовления
    заверенных копий составляет от 1 до 5 рабочих дней со дня подачи заявки.</span>

</div>
<form class="needs-validation js-zakaz-copii-npa" novalidate="" style="" id="js-zakaz-copii-npa" action="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>" method="POST"
      autocomplete="Off">
    <input type="hidden" name="zakaz-copii-npa" value="add">
    <?= bitrix_sessid_post() ?>
    <div class="alert d-none"></div>
    <div id="js--form-action-content" class="show">
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="KOPIYA_PRAVOVOGO_AKTA">Копия правового акта (вид, дата, номер, заголовок правового акта, количество экземпляров)
                <span class="text-danger">*</span></label>
            <div class="col-sm-10 py-2">
                <textarea required="required" class="form-control" type="text" name="KOPIYA_PRAVOVOGO_AKTA" id="KOPIYA_PRAVOVOGO_AKTA"></textarea>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="TSEL_POLUCHENIYA">Цель получения заверенной копии <span class="text-danger">*</span></label>
            <div class="col-sm-10 py-2">
                <textarea required="required" class="form-control" type="text" name="TSEL_POLUCHENIYA" id="TSEL_POLUCHENIYA"></textarea>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="ISPOLNITEL">Исполнитель <span class="text-danger">*</span></label>
            <div class="col-sm-10">
                <select class="selectpicker form-control" data-dropup-auto="false" data-size="5" title="Выбрать" data-live-search="true"
                        data-live-search-placeholder="Найти" name="ISPOLNITEL" id="ISPOLNITEL">
                    <? foreach ($arResult['USERS'] as $arUser) { ?>
                        <option value="<?= $arUser['ID'] ?>"><?= $arUser['LAST_NAME'] ?> <?= $arUser['NAME'] ?> <?= $arUser['SECOND_NAME'] ?></option>
                    <? } ?>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="RUKOVODITEL_OIV_ORGANIZATSII">Руководитель ОИВ/Организации <span class="text-danger">*</span></label>
            <div class="col-sm-10">
                <select class="selectpicker form-control" data-dropup-auto="false" data-size="5" title="Выбрать" data-live-search="true"
                        data-live-search-placeholder="Найти" name="RUKOVODITEL_OIV_ORGANIZATSII" id="RUKOVODITEL_OIV_ORGANIZATSII">
                    <? foreach ($arResult['USERS'] as $arUser) { ?>
                        <option value="<?= $arUser['ID'] ?>"><?= $arUser['LAST_NAME'] ?> <?= $arUser['NAME'] ?> <?= $arUser['SECOND_NAME'] ?></option>
                    <? } ?>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="SROK_IZGOTOVLENIYA">Срок изготовления <span class="text-danger">*</span></label>
            <div class="col-sm-10">
                <select class="form-control" name="SROK_IZGOTOVLENIYA" required id="SROK_IZGOTOVLENIYA">
                    <option value="0" disabled selected>Выбрать</option>
                    <? foreach ($arResult['SROK_IZGOTOVLENIYA'] as $building) { ?>
                        <option value="<?= $building['ID'] ?>"><?= $building['NAME'] ?></option>
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
