<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arResult */

if (!$arResult['IS_AJAX']) {
    ?>
<div class="container">
    <h1 class="mt-2 mb-3 text-center">Звонки Губернатору ТО</h1>
    <div class="form-call-gubernator">
        <form action="<?= $APPLICATION->GetCurPage() ?>" id="selector-call-date" method="post">
            <div class="row mb-3 mt-2">
                <div class="col-lg-1 col-md-1 col-sm-12">
                    <a href="<?= $APPLICATION->GetCurPage() ?>?XLSX=Y" target="_blank" title="Скачать список звонков"><span class="fa fa-print p-2 my-2"></span></a>
                </div>
                <div class="col-lg-6 col-md-5 col-sm-12 py-2">
                    <div class="row" style="line-height: 1.5em;">
                        <div class="col-xl-4 col-md-6 col-sm-12"><label for="calendar_init">Дата начала:&nbsp;<span class="fa fa-calendar-alt m-2"></span></label></div>
                        <div class="col-xl-6 col-md-6 col-sm-12"><input type="text" name="START_DATE" id="calendar_init" data-value="<?=$arResult['START_DATE_INPUT']?>" value="" class="form-control datepicker" /></div>
                    </div>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-12 py-2">
                    <select class="form-control browser-default custom-select" name="CURRENT_PERIOD">
                        <option value="day" <?=($arResult['CURRENT_PERIOD'] == 'day')?' selected="selected"':''?>>День</option>
                        <option value="mounth" <?=($arResult['CURRENT_PERIOD'] == 'mounth')?' selected="selected"':''?>>Месяц</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-12 py-2">
                    <a class="default-link" style="line-height: 2.2em;" href="javascript:void(0);" data-date-default="<?= $arResult['DEFAULT_DATE'] ?>" data-date-default-submit="<?= $arResult['DEFAULT_DATE_SUBMIT'] ?>">
                        Сегодня
                    </a>
                </div>
                <input type="hidden" name="IS_AJAX" value="ajax" />
            </div>
        </form>
        <div class="row">
            <div class="col-xl-12 my-2 mx-auto --tl-inner-list position-relative">
    <?
}
?>
                <div class="row my-2">
                    <div class="col-lg-6 text-left"><a class="default-link" href="javascript:void(0)" data-date-default="<?=$arResult['BEFORE_DATE']?>" data-date-default-submit="<?=$arResult['BEFORE_DATE_SUBMIT']?>">&larr; Предыдущий <?=($arResult['CURRENT_PERIOD'] == 'mounth') ? 'месяц' : 'день'?></a></div>
                    <div class="col-lg-6 text-right"><a class="default-link" href="javascript:void(0)" data-date-default="<?=$arResult['AFTER_DATE']?>" data-date-default-submit="<?=$arResult['AFTER_DATE_SUBMIT']?>">Следующий <?=($arResult['CURRENT_PERIOD'] == 'mounth') ? 'месяц' : 'день'?> &rarr;</a></div>
                </div>
                <? if (count($arResult['ITEMS_CALL_GROUP']) > 0) { ?>
                    <section class="timeline">
                        <div class="row">
                            <? foreach ($arResult['ITEMS_CALL_GROUP'] as $sDate => $arGROUP_CALL) { ?>
                                <div class="col-12 col-sm-6 timeline-wrapper">
                                    <div class="timeline-item">
                                        <div class="timeline-item-date-main text-center px-1"><?= $sDate ?> г.</div>
                                        <? foreach ($arGROUP_CALL as $arCALL) { ?>
                                            <div class="px-1 py-3 border-bottom border-white">
                                                <span class="timeline-item-date"><b>Дата и время звонка:</b> <?= $arCALL['UF_DATECALL'] ?>
                                                    г. в <?= $arCALL['UF_TIMECALL'] ?></span>
                                                <span class="timeline-item-header">ФИО: <?= $arCALL['UF_FIOCALL'] ?></span>
                                                <span class="timeline-item-org"><b>Организация:</b> <?= $arCALL['UF_ORGCALL'] ?></span>
                                                <span class="timeline-item-description"><b>Вопрос:</b> <?= $arCALL['UF_QUESTION'] ?></span>
                                                <span class="timeline-item-description"><b>Примечание:</b> <?= $arCALL['UF_NOTECALL'] ?></span>
                                            </div>
                                        <? } ?>
                                    </div>
                                </div>
                            <? } ?>
                        </div>
                    </section>
                <? } else { ?>
                    <div class="alert alert-info">Звонков не найдено за указанный период</div>
                <? } ?>
                <div class="my-3">Всего за период: <?= $arResult['COUNT_ITEM'] ?></div>
<?
if (!$arResult['IS_AJAX']) {
    ?>
            </div>
        </div>
    </div>
</div>
    <?
}
