<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arResult */

if (!$arResult['IS_AJAX']) {
    $this->addExternalJS("/local/templates/gubernator_calllist/js/jquery/jquery.inputmask.bundle.js");
    echo '<div class="form-call-gubernator">';
}
?>
<div class="row">
    <div class="col-lg-9 col-md-12 my-2 mx-auto">
        <? if ($arResult['access_edit']) { ?>
            <? if ($arResult['edit_result']) { ?>
                <div class="alert alert-<?= $arResult['class_result'] ?> alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?= $arResult['message_result'] ?>
                </div>
            <? } ?>
            <form action="<?= $APPLICATION->GetCurPage() ?>" method="post"
                  enctype="multipart/form-data" class="form-horizontal" id="form-call-gubernator">
                <div class="form-group d-md-flex py-2">
                    <div class="col-md-3 col-xl-2"><label for="date-call-text">Дата звонка:</label></div>
                    <div class="col-md-9 col-xl-10">
                        <input type="date" placeholder="Дата звонка" value="" name="gub[date_call]"
                               id="date-call-text" class="form-control" required="required"/>
                    </div>
                </div>
                <div class="form-group d-md-flex py-2">
                    <div class="col-md-3 col-xl-2"><label for="time-call-text">Время звонка:</label></div>
                    <div class="col-md-9 col-xl-10">
                        <input type="text" placeholder="Время звонка" value="" name="gub[time_call]"
                               pattern="[0-2][0-9]:[0-5][0-9]" required="required" id="time-call-text"
                               class="form-control"/>
                    </div>
                </div>
                <div class="form-group d-md-flex py-2">
                    <div class="col-md-3 col-xl-2"><label for="fio-call">ФИО:</label></div>
                    <div class="col-md-9 col-xl-10">
                        <input type="text" placeholder="ФИО" value="" name="gub[fio_call]" id="fio-call"
                               class="form-control" required="required"/>
                    </div>
                </div>
                <div class="form-group d-md-flex py-2">
                    <div class="col-md-3 col-xl-2"><label for="organization-call">Организация:</label></div>
                    <div class="col-md-9 col-xl-10">
                        <input type="text" placeholder="Организация" value="" name="gub[organization_call]"
                               id="organization-call" class="form-control"/>
                    </div>
                </div>
                <div class="form-group d-md-flex py-2">
                    <div class="col-md-3 col-xl-2"><label for="question-call">Вопрос:</label></div>
                    <div class="col-md-9 col-xl-10">
                        <textarea placeholder="Вопрос" name="gub[question_call]" id="question-call"
                                  class="form-control" required="required"></textarea>
                    </div>
                </div>
                <div class="form-group d-md-flex py-2">
                    <div class="col-md-3 col-xl-2"><label for="note-call">Примечание:</label></div>
                    <div class="col-md-9 col-xl-10">
                        <textarea placeholder="Примечание" name="gub[note_call]" id="note-call"
                                  class="form-control"></textarea>
                    </div>
                </div>
                <div class="form-group text-center my-2">
                    <input type="hidden" name="gub[confirm]" value="1"/>
                    <input type="hidden" name="gub[action]" value="edit"/>
                    <button type="submit" class="ui-btn ui-btn-success">Добавить</button>
                </div>
            </form>
        <? } else { ?>
            <div class="alert alert-danger">У вас недостаточно прав для работы с данным разделом!</div>
        <? } ?>
    </div>
</div>
<?
if ($arResult['access_edit']) {
    ?>
    <div class="my-3 row _list-calls position-relative">
        <div class="col-xl-12"><a href="javascript:" data-list-action="all" <?= ($arResult['filter_list'] == 'all') ? 'style="text-decoration: underline;"' : '' ?>>все</a> | <a href="javascript:" data-list-action="handle" <?= ($arResult['filter_list'] == 'handle') ? 'style="text-decoration: underline;"' : '' ?>>обработанные</a> | <a href="javascript:" data-list-action="nohandle" <?= ($arResult['filter_list'] == 'nohandle') ? 'style="text-decoration: underline;"' : '' ?>>не обработанные</a></div>
        <div class="col-xl-12">
            <? if ($arResult['CALL_LIST_EXIST']) { ?>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-3 col-lg-3 col-md-12">Дата и время
                            </div>
                            <div class="col-4 col-lg-4 col-md-12">ФИО</div>
                            <div class="col-4 col-lg-4 col-md-12">Вопрос</div>
                            <div class="text-center col-1 col-lg-1 col-md-12">
                                Действия
                            </div>
                        </div>
                    </li>
                    <? foreach ($arResult['CALL_LIST'] as $arCALL_ITEM) { ?>
                        <li class="list-group-item">
                            <div class="row">
                                <div class="col-3 col-lg-3 col-md-12">
                                    <span class="text-danger" style="cursor: pointer; font-size: 24px;" data-viewed-id="<?= $arCALL_ITEM['ID'] ?>">
                                        <input title="<?=( $arCALL_ITEM['UF_INWORK'] == '2' ? 'Обработан' : 'Отметить как обработанный')?>" type="checkbox" name="viewed_act[]" value="<?= $arCALL_ITEM['ID'] ?>" <?=( $arCALL_ITEM['UF_INWORK'] == '2' ? ' checked="checked"' : '')?> />
                                    </span>
                                    <?= $arCALL_ITEM['UF_DATECALL']->format("H:i d.m.Y") ?> г.
                                </div>
                                <div class="col-4 col-lg-4 col-md-12"><?= $arCALL_ITEM['UF_FIOCALL'] ?></div>
                                <div class="col-4 col-lg-4 col-md-12"><?= $arCALL_ITEM['UF_QUESTION'] ?></div>
                                <div class="text-center col-1 col-lg-1 col-md-12">
                                    <span class="text-danger" style="cursor: pointer; font-size: 24px;" data-del-id="<?= $arCALL_ITEM['ID'] ?>" title="Удалить">
                                        &times;
                                    </span>
                                </div>
                            </div>
                        </li>
                    <? } ?>
                </ul>
            <? } ?>
        </div>
        <div class="my-2 col-12">
            <?$APPLICATION->IncludeComponent(
                "bitrix:main.pagenavigation",
                '',
                array(
                    "NAV_OBJECT" => $arResult['objNAV'],
                    "SEF_MODE" => "N",
                    "SHOW_COUNT" => "N"
                ),
                false
            );?>
        </div>
    </div>
    <?php
}

if (!$arResult['IS_AJAX']) {
    echo '</div>';
}
