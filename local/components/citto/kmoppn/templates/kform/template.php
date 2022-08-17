<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<div class="row">
    <div class="col-lg-8 col-md-10 col-12 my-3">
        <? if($arResult['NOT_PERMITTED']) { ?>
            <div class="alert alert-danger my-3">Данный раздел вам не досупен, обратитесь к администраторам портала.</div>
        <? } else { ?>
        <? if($arResult['SHOW_LINK_REPORT']) { ?>
        <p><a target="_blank" href="<?=$APPLICATION->GetCurDir()?>?download=xls">Скачать отчет по ОМСУ</a></p>
        <? } ?>
        <p>Заполните пожалуйста данные формы для структуры &laquo;<?=$arResult['DEPARTMENT_NAME']?>&raquo;:</p>
        <form action="" id="js--kmoppn" method="post" class="needs-validation form-horizontal" novalidate="novalidate">
            <? foreach($arResult['FIELDS'] as $arItem) { ?>
            <div class="form-group">
                <label for="<?=$arItem['CODE']?>"><?=$arItem['NAME']?></label>
                <input type="text" value="<?=$arItem['VALUE']?>" placeholder="<?=$arItem['NAME']?>" class="form-control" id="<?=$arItem['CODE']?>" name="<?=$arItem['CODE']?>" required="required" />
                <div class="valid-feedback">Правильно</div>
                <div class="invalid-feedback">Поле заполеннно не верно!</div>
            </div>
            <? } ?>
            <input type="hidden" name="__control_kmoppn" value="1">
            <div class="mt-5">
                <button type="submit" class="btn btn-primary">Отправить данные</button>
            </div>
        </form>
        <? } ?>
    </div>
</div>
