<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

$templatePath = $this->__folder;

\Bitrix\Main\UI\Extension::load("ui.tooltip");
Bitrix\Main\Page\Asset::getInstance()->addJs($templatePath.'/js/jquery.inputmask.min.js');
\Bitrix\Main\UI\Extension::load("ui.alerts");
?>
<div class="ui-alert ui-alert-primary">
    <div class="ui-alert-message">
        <p><strong>Внимание!</strong></p>
        <p>Если Вы заболели ОРВИ, вам необходимо остаться дома. Для получения помощи и листа нетрудоспособности Вы можете воспользоваться записью на телемедицинскую консультацию на Корпоративном портале. Медицинский работник свяжется с Вами, при необходимости будет направлена бригада для получения медикаментов и взятия экспресс-теста на Ковид.</p>
        <p>Подача заявки на телемедицинскую консультацию возможна с 8:00 до 12:00 (пн-пт)</p>
        <p>Если у Вас температура тела выше 38 С, есть одышка, выраженная головная боль и другие симптомы интоксикации, то вам необходимо вызвать бригаду скорой медицинской помощи по телефонам 112 или 103.</p>
    </div>
</div>
<? if ($arResult['ACCESS'] == 'Y'): ?>
    <form enctype="multipart/form-data" class="needs-validation js-vaccination_covid19" id="js-vaccination_covid19" action="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>"
      method="POST" autocomplete="Off">
    <input type="hidden" name="vaccination_covid19" value="add">
    <?= bitrix_sessid_post() ?>
    <div class="alert d-none"></div>
    <div id="js--form-action-content" class="show">
        <? foreach ($arResult['FIELDS'] as $arField): ?>
            <div class="form-group row"
                <? if ($arField['SHOW'] == 'N'): ?>
                    style="display: none"
                <? endif; ?>
            >
                <label class="col-sm-4 col-form-label" for="bp_<?= $arField['CODE'] ?>"><?= $arField['NAME'] ?>
                    <? if ($arField['IS_REQUIRED'] == 'Y'): ?>
                        <span class="text-danger">*</span>
                    <? endif; ?>
                </label>
                <div class="col-sm-8 py-2">
                    <? if ($arField['TYPE'] == 'EMPLOYEE'): ?>
                        <select
                            <? if ($arField['IS_REQUIRED'] == 'Y'): ?>
                                required="required"
                            <? endif; ?>
                                class="selectpicker form-control"
                                data-dropup-auto="false" data-size="5" title="Выбрать"
                                data-live-search="true" data-live-search-placeholder="Найти"
                                name="<?= $arField['CODE'] ?>" id="bp_<?= $arField['CODE'] ?>"
                        >
                            <? foreach ($arResult['USERS'] as $arUser) { ?>
                                <option
                                        value="<?= $arUser['ID'] ?>"
                                    <? if ($arField['CODE'] == 'EMPLOYEE' && $_GET['user_id'] == $arUser['ID']): ?>
                                        selected
                                    <? endif; ?>
                                ><?= $arUser['USER_INFO'] ?></option>
                            <? } ?>
                        </select>
                    <? elseif ($arField['TYPE'] == 'STRING'): ?>
                        <input
                            <? if ($arField['IS_REQUIRED'] == 'Y'): ?>
                                required="required"
                            <? endif; ?>
                                class="form-control" type="text" name="<?= $arField['CODE'] ?>" id="bp_<?= $arField['CODE'] ?>"
                        />
                    <? elseif ($arField['TYPE'] == 'NUMBER'): ?>
                        <input
                            <? if ($arField['IS_REQUIRED'] == 'Y'): ?>
                                required="required"
                            <? endif; ?>
                                class="form-control" type="number" name="<?= $arField['CODE'] ?>" id="bp_<?= $arField['CODE'] ?>"
                        />
                    <? elseif ($arField['TYPE'] == 'LIST'): ?>
                        <select class="selectpicker form-control"
                            <? if ($arField['IS_REQUIRED'] == 'Y'): ?>
                                required="required"
                            <? endif; ?>
                                data-dropup-auto="false" data-size="5" title="Выбрать"
                                data-live-search="true" data-live-search-placeholder="Найти"
                                name="<?= $arField['CODE'] ?>" id="bp_<?= $arField['CODE'] ?>"
                        >
                            <? foreach ($arField['ENUMS'] as $arEnum) { ?>
                                <option value="<?= $arEnum['ID'] ?>"><?= $arEnum['VALUE'] ?></option>
                            <? } ?>
                        </select>
                    <? elseif ($arField['TYPE'] == 'FILE'): ?>
                        <input
                            <? if ($arField['IS_REQUIRED'] == 'Y'): ?>
                                required="required"
                            <? endif; ?>
                                class="form-control"
                                type="file"
                            <? if ($arField['MULTIPLE'] == 'Y'): ?>
                                name="<?= $arField['CODE'] ?>[]"
                                multiple
                            <? else: ?>
                                name="<?= $arField['CODE'] ?>"
                            <? endif; ?>
                                value="" id="bp_<?= $arField['CODE'] ?>"
                        />
                    <? elseif ($arField['TYPE'] == 'DATE'): ?>
                        <input
                            <? if ($arField['IS_REQUIRED'] == 'Y'): ?>
                                required="required"
                            <? endif; ?>
                                class="form-control"
                                type="date"
                                name="<?= $arField['CODE'] ?>"
                                value="" id="bp_<?= $arField['CODE'] ?>"
                        />
                    <? endif; ?>
                </div>
            </div>
        <? endforeach; ?>
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
<? endif; ?>
