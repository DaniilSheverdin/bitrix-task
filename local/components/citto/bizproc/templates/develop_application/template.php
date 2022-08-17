<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
/**
 * @global $APPLICATION
 */
?>
<form class="needs-validation js-develop_application" novalidate="" style="" id="js-develop_application" action="<?=$APPLICATION->GetCurPageParam()?>" method="POST" autocomplete="Off">
    <input type="hidden" name="develop_application" value="add">
    <?=bitrix_sessid_post()?>
    <div class="alert d-none"></div>
    <div id="js--form-action-content" class="show">
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="NAZVANIE_RAZRABOTKI">Название разработки<span class="text-danger">*</span>:</label>
            <div class="col-sm-10 py-2">
                <input class="form_control" type="text" name="NAZVANIE_RAZRABOTKI" id="NAZVANIE_RAZRABOTKI" required="required"/>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="FUNKTSIONALNYY_ZAKAZCHIK">Функциональный заказчик<span class="text-danger">*</span>:</label>
            <div class="col-sm-10 py-2">
                <select class="selectpicker form-control" data-dropup-auto="false" data-size="5" title="Выбрать" data-live-search="true"  data-live-search-placeholder="Найти" name="FUNKTSIONALNYY_ZAKAZCHIK" id="FUNKTSIONALNYY_ZAKAZCHIK">
                <? foreach ($arResult['OTDELLIST'] as $arOtdel) { ?>
                        <option value="<?=$arOtdel['NAME']?>"><?=$arOtdel['NAME']?></option>
                    <? } ?>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="OTVETSTVENNYY_OT_FUNKTSIONALNOGO_ZAKAZCHIKA">Ответственный от функционального заказчика<span class="text-danger">*</span>:</label>
            <div class="col-sm-10 py-2">
            <select class="selectpicker form-control" data-dropup-auto="false" data-size="5" title="Выбрать" data-live-search="true"  data-live-search-placeholder="Найти" name="OTVETSTVENNYY_OT_FUNKTSIONALNOGO_ZAKAZCHIKA" id="OTVETSTVENNYY_OT_FUNKTSIONALNOGO_ZAKAZCHIKA">
            <? foreach ($arResult['USERS'] as $arUser) { ?>
                        <option value="<?=$arUser['ID']?>"><?=$arUser['LAST_NAME']?> <?=$arUser['NAME']?> <?=$arUser['SECOND_NAME']?></option>
                    <? } ?>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="VID_RABOT">Вид работ<span class="text-danger">*</span>:</label>
            <div class="col-sm-10 py-2">
                <select class="selectpicker form-control" data-dropup-auto="false" data-size="5" title="Выбрать" data-live-search="true"  data-live-search-placeholder="Найти" name="VID_RABOT" id="VID_RABOT">
                    <? foreach ($arResult['VID_RABOT'] as $arVidRab) { ?>
                        <option value="<?=$arVidRab['ID']?>"><?=$arVidRab['VALUE']?></option>
                    <? } ?>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="PROEKT">Проект<span class="text-danger">*</span>:</label>
            <div class="col-sm-10 py-2">
                <select class="selectpicker form-control" data-dropup-auto="false" data-size="5" title="Выбрать" data-live-search="true"  data-live-search-placeholder="Найти" name="PROEKT" id="PROEKT">
                    <? foreach ($arResult['PROEKT'] as $arProekt) { ?>
                        <option value="<?=$arProekt['ID']?>"><?=$arProekt['VALUE']?></option>
                    <? } ?>
                </select>
            </div>  
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="OPISANIE_RAZRABOTKI">Описание разработки<span class="text-danger">*</span>:</label>
            <div class="col-sm-10 py-2">
            <input required="required" class="form-control" type="text" name="OPISANIE_RAZRABOTKI" value="" id="OPISANIE_RAZRABOTKI" />
            </div>  
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="SROK_REALIZATSII">Срок реализации<span class="text-danger">*</span>:</label>
            <div class="col-sm-10 py-2">
            <input required="required" class="form-control" type="date" name="SROK_REALIZATSII" id="SROK_REALIZATSII" />
            </div>  
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="OBOSNOVANIE_SROKA">Обоснование срока<span class="text-danger">*</span>:</label>
            <div class="col-sm-10 py-2">
            <input required="required" class="form-control" type="text" name="OBOSNOVANIE_SROKA" value="" id="OBOSNOVANIE_SROKA" />
            </div>  
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="bp=develop_application">Шаблон Функционально-технических требований</label>
            <div class="col-sm-10 py-2">
                <a href="/local/components/citto/bizproc/templates/develop_application/shablon.docx" download>Скачать</a>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="FUNKTSIONALNO_TEKHNICHESKIE_TREBOVANIYA">Функционально-технические требования<span class="text-danger">*</span>:</label>
            <div class="col-sm-10 py-2">
            <?$APPLICATION->IncludeComponent("bitrix:main.file.input", "drag_n_drop",
                      array(
                        "INPUT_NAME"=>"FUNKTSIONALNO_TEKHNICHESKIE_TREBOVANIYA",
                        "MULTIPLE"=>"Y",
                        "MODULE_ID"=>"main",
                        "MAX_FILE_SIZE"=>"",
                        "ALLOW_UPLOAD"=>"A",
                        "ALLOW_UPLOAD_EXT"=>""
                    ),
                    false
                );?>
            </div>  
        </div>
        
        <div class="form-group row">
            <label class="col-sm-2 col-form-label" for="DOPOLNITELNYE_DOKUMENTY">Дополнительный документы</label>
            <div class="col-sm-10 py-2">
            <?$APPLICATION->IncludeComponent("bitrix:main.file.input", "drag_n_drop",
                      array(
                        "INPUT_NAME"=>"DOPOLNITELNYE_DOKUMENTY",
                        "MULTIPLE"=>"Y",
                        "MODULE_ID"=>"main",
                        "MAX_FILE_SIZE"=>"",
                        "ALLOW_UPLOAD"=>"A",
                        "ALLOW_UPLOAD_EXT"=>""
                    ),
                    false
                );?>
            </div>
        </div>
		<div class="row">
            <div class="col-12 col-sm-2">
                <div class="text-right mb-3">
                    <button class="btn btn-primary btn-block" type="submit">Далее &rarr;</button>
                </div>
            </div>
        </div>
    </div>
</form>
