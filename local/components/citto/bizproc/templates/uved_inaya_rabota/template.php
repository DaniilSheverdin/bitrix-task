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
<form enctype="multipart/form-data" class="needs-validation js-uved_inaya_rabota" novalidate="" style="" id="js-uved_inaya_rabota" action="<?=htmlspecialchars($_SERVER['REQUEST_URI'])?>" method="POST" autocomplete="Off">
    <input type="hidden" name="uved_inaya_rabota" value="add">
    <?=bitrix_sessid_post()?>
    <div class="alert d-none"></div>
    <div id="js--form-action-content" class="show">
        <div class="form-group row">
            <label class="col-sm-4 col-form-label" for="bp_DATE_FROM">Дата начала <span class="text-danger">*</span></label>
            <div class="col-sm-8 py-2">
                <input required="required" class="form-control" type="date" name="DATE_FROM" value="<?=$arResult['DATE_FROM']?>" id="DATE_FROM" />
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-4 col-form-label" for="bp_DATE_TO">Дата окончания</label>
            <div class="col-sm-8 py-2">
                <input class="form-control" type="date" name="DATE_TO" value="<?=$arResult['DATE_TO']?>" id="DATE_TO" />
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-4 col-form-label" for="bp_DATE">Дата подачи</label>
            <div class="col-sm-8 py-2">
                <input class="form-control" type="date" name="DATE" value="<?=$arResult['DATE']?>" id="DATE" />
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-4 col-form-label" for="bp_VID_DEYATELNOSTI">Вид деятельности</label>
            <div class="col-sm-8 py-2">
                <select class="selectpicker form-control" data-dropup-auto="false" data-size="5" title="Выбрать" data-live-search="true"  data-live-search-placeholder="Найти" name="VID_DEYATELNOSTI" id="bp_VID_DEYATELNOSTI">
                    <? foreach ($arResult['VID_DEYATELNOSTI'] as $sDeyat) { ?>
                        <option value="<?=$sDeyat['ID']?>"><?=$sDeyat['NAME']?></option>
                    <? } ?>
                </select>
            </div>
        </div>

        <div class="form-group row hide" id="js-other-vid">
            <label class="col-sm-4 col-form-label" for="bp_DRUGOY_VID_DEYATELNOSTI">Другой вид деятельности (Твор. падеж) <span class="text-danger">*</span></label>
            <div class="col-sm-8 py-2">
                <input class="form-control" type="text" name="DRUGOY_VID_DEYATELNOSTI" value="<?=$arResult['DRUGOY_VID_DEYATELNOSTI']?>" id="DRUGOY_VID_DEYATELNOSTI" />
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-4 col-form-label" for="bp_OSNOVANIE_VOZNIKNOVENIYA_PRAVOVYKH_OTNOSHENIY">Основание возникновения правовых отношений</label>
            <div class="col-sm-8 py-2">
                <select class="selectpicker form-control" data-dropup-auto="false" data-size="5" title="Выбрать" data-live-search="true"  data-live-search-placeholder="Найти" name="OSNOVANIE_VOZNIKNOVENIYA_PRAVOVYKH_OTNOSHENIY" id="bp_OSNOVANIE_VOZNIKNOVENIYA_PRAVOVYKH_OTNOSHENIY">
                    <? foreach ($arResult['OSNOVANIE'] as $sOsn) { ?>
                        <option value="<?=$sOsn['ID']?>"><?=$sOsn['NAME']?></option>
                    <? } ?>
                </select>
            </div>
        </div>

        <div class="form-group row hide" id="js-other-osnovanie">
            <label class="col-sm-4 col-form-label" for="bp_DRUGOE_OSNOVANIE_VOZNIKNOVENIYA_PRAVOVYKH_OTNOSHEN">Другое основание возникновения правовых отношений (Именит. падеж) <span class="text-danger">*</span></label>
            <div class="col-sm-8 py-2">
                <input class="form-control" type="text" name="DRUGOE_OSNOVANIE_VOZNIKNOVENIYA_PRAVOVYKH_OTNOSHEN" value="<?=$arResult['DRUGOE_OSNOVANIE_VOZNIKNOVENIYA_PRAVOVYKH_OTNOSHEN']?>" id="MESTONAKHOZHDENIE_YURIDICHESKOGO_LITSA" />
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-4 col-form-label" for="bp_NAIMENOVANIE_YURIDICHESKOGO_LITSA_V_KOTOROM_PLANIR">Наименование юридического лица<span class="text-danger">*</span></label>
            <div class="col-sm-8 py-2">
                <input required="required" class="form-control" type="text" name="NAIMENOVANIE_YURIDICHESKOGO_LITSA_V_KOTOROM_PLANIR" value="<?=$arResult['NAIMENOVANIE_YURIDICHESKOGO_LITSA_V_KOTOROM_PLANIR']?>" id="NAIMENOVANIE_YURIDICHESKOGO_LITSA_V_KOTOROM_PLANIR" />
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-4 col-form-label" for="bp_MESTONAKHOZHDENIE_YURIDICHESKOGO_LITSA">Юридический адрес организации<span class="text-danger">*</span></label>
            <div class="col-sm-8 py-2">
                <input required="required" class="form-control" type="text" name="MESTONAKHOZHDENIE_YURIDICHESKOGO_LITSA" value="<?=$arResult['MESTONAKHOZHDENIE_YURIDICHESKOGO_LITSA']?>" id="MESTONAKHOZHDENIE_YURIDICHESKOGO_LITSA" />
            </div>
        </div>

        <div class="form-group row">
            <label class="col-sm-4 col-form-label" for="bp_RUKOVODITEL_OIV">Согласование с руководителем ОИВ, подразделения аппарата правительства Тульской области</label>
            <div class="col-sm-8 py-2">
                <select class="selectpicker form-control" data-show-subtext="true" data-size="5" title="Выбрать" data-live-search="true"  data-live-search-placeholder="Найти" name="RUKOVODITEL_OIV" id="bp_RUKOVODITEL_OIV">
                    <? foreach ($arResult['USERS'] as $arUser) { ?>
                        <option value="<?=$arUser['ID']?>"><?=$arUser['LAST_NAME']?> <?=$arUser['NAME']?> <?=$arUser['SECOND_NAME']?></option>
                    <? } ?>
                </select>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-4 col-form-label" for="bp_KOPII_DOKUMENTOV">Копии документов <span class="text-danger">*</span></label>
            <div class="col-sm-8 py-2">
<!--                <input required="required" type="file" multiple name="KOPII_DOKUMENTOV[]">-->
                <?$APPLICATION->IncludeComponent("bitrix:main.file.input", "drag_n_drop",
                    array(
                        "INPUT_NAME"=>"KOPII_DOKUMENTOV",
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
            <div class="col-12 col-sm-11">
            </div>
        </div>
    </div>
</form>
