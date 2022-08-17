<section id="lnpa-detail-edit">
    <div id="lnpa-detail-actions" class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <? if ($arResult[$arResult['ACTION']]['RESULT']['STATUS']): ?>
                <div class="alert alert-success" role="alert">
                    <?= $arResult[$arResult['ACTION']]['RESULT']['TEXT']  ?>
                </div>
                <? elseif (!empty($arResult['DETAIL_CARD'])): ?>
                    <h3 class="title">Редактирование документа (<?= $arResult['DETAIL_CARD']['STATUS']['NAME'] ?>)</h3>
                    <form class="row form-inline" method="post" action="<?= POST_FORM_ACTION_URI ?>">
                        <?= bitrix_sessid_post(); ?>
                        <? foreach ($arResult['FIELDS']['CODE'] as $arField): ?>
                            <? if (in_array($arField['CODE'], ['UF_STATUS', 'UF_PUBLISH' , 'UF_VERSIONS'])) continue; ?>

                            <div class="item align-items-center" id="<?= $arField['CODE'] ?>">
                                <div class="col-md-2">
                                    <label><?= $arField['LABEL'] ?></label>
                                </div>
                                <div class="col-md-10">
                                    <? if ($arField['TYPE'] == 'string'): ?>
                                        <input type="text"
                                               class="form-control"
                                               name="<?= $arField['CODE'] ?>"
                                            <? if ($arField['CODE'] == 'UF_CUSTOM_TYPE_DOC'): ?>
                                                placeholder="Если нет подходящего значения, введите сами в поле 'Другой тип документа'"
                                            <? elseif ($arField['CODE'] == 'UF_CUSTOM_CONTRACTOR'): ?>
                                                placeholder="Если нет подходящего значения, введите сами в поле 'Другой контрагент'"
                                            <? else: ?>
                                                placeholder="ввести"
                                            <? endif; ?>
                                               value="<?= $arField['VALUE'] ?>"
                                        >
                                    <? elseif ($arField['TYPE'] == 'enumeration'): ?>
                                        <? if ($arField['CODE'] == 'UF_STRUCTURE'): ?>
                                            <select multiple name="<?= $arField['CODE'] ?>[]" id="department" data-dropup-auto="false" data-size="5"
                                                    data-live-search="true"
                                                    data-live-search-placeholder="Найти" class="form-control selectpicker">
                                                <? foreach ($arResult['STRUCTURE'] as $iID => $sName): ?>
                                                    <option <? if (in_array($iID, $arField['VALUE'])) echo 'selected' ?> value="<?= $iID ?>"><?= $sName ?></option>
                                                <? endforeach; ?>
                                            </select>
                                        <? else: ?>
                                            <select name="<?= $arField['CODE'] ?>" data-dropup-auto="false" data-size="5"
                                                    data-live-search="true" data-live-search-placeholder="Найти" class="form-control selectpicker">
                                                <? foreach ($arField['LIST'] as $arEnum): ?>
                                                    <option <? if ($arEnum['ID'] == $arField['VALUE']) echo 'selected' ?>
                                                            value="<?= $arEnum['ID'] ?>"><?= $arEnum['VALUE'] ?></option>
                                                <? endforeach; ?>
                                            </select>
                                        <? endif; ?>
                                    <? elseif ($arField['TYPE'] == 'datetime'): ?>
                                        <input type="date" class="form-control" name="<?= $arField['CODE'] ?>" value="<?= $arField['VALUE'] ?>">
                                    <? elseif ($arField['TYPE'] == 'file'): ?>
                                        <div id="file-upload">
                                            <a href="<?= $arResult['DETAIL_CARD']['FILE']['SRC'] ?>" class="btn-success"
                                               download><?= $arResult['DETAIL_CARD']['FILE']['ORIGINAL_NAME'] ?></a>
                                            <? $APPLICATION->IncludeComponent("bitrix:main.file.input", "drag_n_drop",
                                                array(
                                                    "INPUT_NAME" => $arField['CODE'],
                                                    "MULTIPLE" => "N",
                                                    "MODULE_ID" => "main",
                                                    "MAX_FILE_SIZE" => "",
                                                ),
                                                false
                                            ); ?>
                                        </div>
                                    <? elseif ($arField['TYPE'] == 'autocomplete'): ?>
                                        <input id="js-ajax-parent" type="text" class="form-control" name="<?= $arField['CODE'] ?>" placeholder="ввести"
                                               value="<?= $arField['VALUE'] ?>">
                                        <div class="js-result-parent"></div>
                                    <? endif; ?>
                                </div>
                            </div>
                        <? endforeach; ?>

                        <div class="item align-items-center">
                            <div class="col-md-2">
                                <label for="tags" class="">Теги</label>
                            </div>
                            <div class="col-md-10">
                                <input type="text" name="TAGS" id="tags" class="form-control" placeholder="Введите теги, разделяя запятыми"
                                       value="<?= $arResult['FIELDS']['TAGS'] ?>">
                            </div>
                        </div>

                        <? if ($arResult['DETAIL_CARD']['UF_PUBLISH'] == 'Y'): ?>
                        <div class="item align-items-center">
                            <div class="col-md-2">
                                <label for="save_version" class="">Сохранить как новую версию документа</label>
                            </div>
                            <div class="col-md-10">
                                <input type="checkbox" name="SAVE_VERSION" id="save_version">
                            </div>
                        </div>
                        <? endif; ?>

                        <div class="row item">
                            <div class="col-md-4">
                                <input class="btn btn-info" name="DRAFT" type="submit" value="В черновик">
                            </div>

                            <div class="col-md-4">
                                <input class="btn btn-info" name="MODERATION" type="submit" value="На модерацию">
                            </div>
                            <? if ($arResult['ROLE'] == 'ADMIN'): ?>
                                <div class="col-md-4">
                                    <input class="btn btn-success" name="PUBLISH" type="submit" value="Опубликовать">
                                </div>
                            <? endif; ?>
                        </div>
                    </form>
                <? endif; ?>
            </div>
        </div>
    </div>
</section>