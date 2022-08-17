<?php
/**
 * @var $arResult
 */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<form enctype="multipart/form-data" class="needs-validation js-citto-uvolnenie" novalidate="" style="" id="js-citto-uvolnenie" action="<?=htmlspecialchars($APPLICATION->GetCurPage())?>" method="POST" autocomplete="Off">
    <input type="hidden" name="uved_citto_uvolnenie" value="add">
    <?=bitrix_sessid_post()?>
    <div class="alert d-none"></div>
    <div id="js--form-action-content" class="show">
        <div class="form-group row">
            <label class="col-sm-4 col-form-label" for="bp_DM_SOTRUDNIK">Сотрудник <span class="text-danger">*</span></label>
            <div class="col-sm-8 py-2">
                <select class="selectpicker form-control" data-dropup-auto="false" data-size="5" title="Выбрать" data-live-search="true" data-live-search-placeholder="Найти" name="DM_SOTRUDNIK" id="bp_DM_SOTRUDNIK">
                    <? foreach ($arResult['USERS'] as $arUser) { ?>
                        <option value="<?=$arUser['ID']?>"><?=$arUser['LAST_NAME']?> <?=$arUser['NAME']?> <?=$arUser['SECOND_NAME']?></option>
                    <? } ?>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-4 col-form-label" for="bp_PROSHU_RASTORGNUT_TRUDOVOY_DOGOVOR">Прошу расторгнуть трудовой договор в связи с <span class="text-danger">*</span></label>
            <div class="col-sm-8 py-2">
                <select class="selectpicker form-control" data-size="5" title="Выбрать" name="PROSHU_RASTORGNUT_TRUDOVOY_DOGOVOR" id="bp_PROSHU_RASTORGNUT_TRUDOVOY_DOGOVOR">
                    <? foreach ($arResult['PROSHU_RASTORGNUT_TRUDOVOY_DOGOVOR'] as $arItem) { ?>
                        <option data-xml-id="<?=$arItem['XML_ID']?>" value="<?=$arItem['ID']?>"><?=$arItem['VALUE']?></option>
                    <? } ?>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-4 col-form-label" for="bp_DM_DATA_UVOLNENIYA">Дата увольнения <span class="text-danger">*</span></label>
            <div class="col-sm-8 py-2">
                <input required="required" class="form-control" type="date" name="DM_DATA_UVOLNENIYA" id="bp_DM_DATA_UVOLNENIYA" />
            </div>
        </div>
        <div class="form-group row" id="doc-scan">
            <label class="col-sm-4 col-form-label" for="bp_DM_REQUEST_SCAN">Отсканированное бумажное заявление</label>
            <div class="col-sm-8 py-2">
                <input class="form-control" type="file" name="DM_REQUEST_SCAN" value="" id="bp_DM_REQUEST_SCAN" />
                <div class="alert alert-danger w-100 mt-2">Внимание! Загрузка отсканированного заявления обязательна при увольнении по инициативе работника.</div>
                <div class="alert alert-info w-100 mt-2">Можно загрузить файл в формате pdf, doc, docx или архив с отсканированным заявлением упакованным в rar, zip.</div>
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
