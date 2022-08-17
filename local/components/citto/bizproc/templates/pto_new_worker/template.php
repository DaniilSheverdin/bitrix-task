<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<form class="needs-validation js-pto_new_worker" novalidate="" style="" id="pto_new_worker" action="<?=htmlspecialchars($_SERVER['REQUEST_URI'])?>" method="POST" autocomplete="Off">
    <input type="hidden" name="pto_new_worker" value="add">
    <?=bitrix_sessid_post()?>
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
                            <?if ($arField['CODE'] == 'OIV_LEAD'): ?>
                            <? if ($arField['IS_REQUIRED'] == 'Y'): ?>
                                required="required"
                            <? endif; ?>
                                class="selectpicker form-control js-oiv_lead"
                                data-dropup-auto="false" data-size="5" title="Выбрать"
                                data-live-search="true" data-live-search-placeholder="Найти"
                                name="<?= $arField['CODE'] ?>" id="bp_<?= $arField['CODE'] ?>"
                        >
                            <? foreach ($arResult['USERS'] as $arUser) { ?>
                                <option
                                        value="<?= $arUser['ID'] ?>"
                                    <? if (!$arField['CODE'] == 'OIV'): ?>
                                        selected
                                    <? endif; ?>
                                ><?= $arUser['USER_INFO'] ?></option>
                            <? } ?>
                            <? else: ?>
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
                                        <? if ($arField['CODE'] == '7' && $arResult['7'] == $arUser['ID']): ?>
                                            selected
                                        <? endif; ?>
                                    ><?= $arUser['USER_INFO'] ?></option>
                                <? } ?>
                            <? endif; ?>
                        </select>
                    <? elseif ($arField['TYPE'] == 'STRING'): ?>
                        <? if ($arField['CODE'] == 'OIV'): ?>
                            <select
                                <? if ($arField['IS_REQUIRED'] == Y): ?>
                                    required
                                <? endif; ?>
                                    class="selectpicker form-control js-oiv"
                                    data-dropup-auto="false" data-size="5" title="Выбрать"
                                    data-live-search="true" data-live-search-placeholder="Найти"
                                    name="<?= $arField['CODE'] ?>" id="bp_<?= $arField['CODE'] ?>"
                            <? foreach ($arResult['OTDELLIST'] as $otdel) {?>
                                <option value="<?=$otdel['ID'] ?>"><?=$otdel['NAME']?></option>
                            <?}?>
                            </select>
                        <?else: ?>
                            <input
                                <? if ($arField['IS_REQUIRED'] == 'Y'): ?>
                                    required="required"
                                <? endif; ?>
                                    class="form-control" type="text" name="<?= $arField['CODE'] ?>" id="bp_<?= $arField['CODE'] ?>"
                                <? if ($arField['CODE'] == 'EMAIL_SC'): ?>
                                    value="<?=$arResult['EMAIL'] ?>"
                                <? endif; ?>
                            />
                        <? endif; ?>
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
                            <? if ($arField['MULTIPLE'] == 'Y'): ?>
                                multiple
                                name="<?= $arField['CODE'] ?>[]" id="bp_<?= $arField['CODE'] ?>"
                            <? else: ?>
                                name="<?= $arField['CODE'] ?>" id="bp_<?= $arField['CODE'] ?>"
                            <? endif;?>
                        >
                            <? if ($arField['CODE'] == 'OIV'): ?>
                                <? foreach ($arResult['OTDELLIST'] as $otdel) {?>
                                    <option value="<?=$otdel['ID'] ?>"><?=$otdel['NAME']?></option>
                                <?}?>
                            <? else: ?>
                                <? foreach ($arField['ENUMS'] as $arEnum) { ?>
                                    <option value="<?= $arEnum['ID'] ?>"><?= $arEnum['VALUE'] ?></option>
                                <? } ?>
                            <?endif;?>
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
                    <? elseif ($arField['TYPE'] == 'HTML'): ?>
                        <textarea
                            <? if ($arField['IS_REQUIRED'] == 'Y'): ?>
                                required="required"
                            <?endif;?>
                                class="form-control"

                                height="200"
                                name="<?= $arField['CODE'] ?>"
                                id="bp_<?= $arField['CODE'] ?>"
                        >MS Office, Яндекс.Браузер, Adobe Reader, 7-Zip, Учетная запись для работы в сети правительства Тульской области (доменная учетная запись), Учетная запись служебной электронной почты, Учетная запись Lync, Учетная запись в АСЭД Дело, Сетевой диск К (права на чтение), Сетевой диск органа исполнительной власти (права на редактирование/изменение содержимого), Справочно-правовая система «КонсультантПлюс»
                    </textarea>
                    <? endif; ?>
                </div>
            </div>
        <? endforeach; ?>
        <div class="row">
            <div class="col-12 col-sm-3">
                <div class="text-right mb-3">
                    <button class="btn btn-success btn-block" type="submit">Далее &rarr;</button>
                </div>
            </div>
            <div class="col-12 col-sm-9">
            </div>
        </div>
    </div>
</form>