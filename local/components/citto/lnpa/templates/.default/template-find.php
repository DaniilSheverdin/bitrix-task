<form method="post" action="<?= POST_FORM_ACTION_URI ?>">
    <?= bitrix_sessid_post(); ?>
    <section id="lnpa-find">
        <div class="form container-fluid">
            <div class="row form-inline">
                <div class="col-md-3">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <label for="tags" class="col-md-4">Название содержит</label>
                        </div>
                        <div class="col-md-6">
                            <input type="text" name="CONTAINS_AN" id="contains_an" class="form-control col-md-8" placeholder=""
                                   value="<?= $arResult['REQUEST']['CONTAINS_AN'] ?>">
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <label for="department"><?= $arResult['FIELDS']['CODE']['UF_STRUCTURE']['LABEL'] ?></label>
                        </div>
                        <div class="col-md-6">
                            <select multiple name="<?= $arResult['FIELDS']['CODE']['UF_STRUCTURE']['CODE'] ?>[]" id="department" data-dropup-auto="false" data-size="5"
                                    data-live-search="true" data-live-search-placeholder="Найти" class="form-control selectpicker">
                                <? foreach ($arResult['STRUCTURE'] as $id => $name): ?>
                                    <option
                                            value="<?= $id ?>"
                                        <? if (in_array($id, $arResult['REQUEST']['UF_STRUCTURE'])): ?>
                                            selected
                                        <? endif; ?>
                                    ><?= $name ?></option>
                                <? endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <label for="type_document"><?= $arResult['FIELDS']['CODE']['UF_TYPE_DOC']['LABEL'] ?></label>
                        </div>
                        <div class="col-md-6">
                            <select name="<?= $arResult['FIELDS']['CODE']['UF_TYPE_DOC']['CODE'] ?>" id="type_document" class="form-control">
                                <option value="">Ничего не выбрано</option>
                                <? foreach ($arResult['FIELDS']['CODE']['UF_TYPE_DOC']['LIST'] as $arEnum): ?>
                                    <option
                                            value="<?= $arEnum['ID'] ?>"
                                        <? if ($arEnum['ID'] == $arResult['REQUEST']['UF_TYPE_DOC']): ?>
                                            selected
                                        <? endif; ?>
                                    ><?= $arEnum['VALUE'] ?></option>
                                <? endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <label for="doc_number"><?= $arResult['FIELDS']['CODE']['UF_NUMBER']['LABEL'] ?></label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" name="<?= $arResult['FIELDS']['CODE']['UF_NUMBER']['CODE'] ?>" id="doc_number" class="form-control"
                                   placeholder="Номер документа" value="<?= $arResult['REQUEST']['UF_NUMBER'] ?>">
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <label for="public_year" class="col-md-4">Год публикации</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" name="PUBLIC_YEAR" id="public_year" class="form-control col-md-8" placeholder="Введите год"
                                   value="<?= $arResult['REQUEST']['PUBLIC_YEAR'] ?>">
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <label for="contractor"><?= $arResult['FIELDS']['CODE']['UF_CONTRACTOR']['LABEL'] ?></label>
                        </div>
                        <div class="col-md-8">
                            <select multiple name="<?= $arResult['FIELDS']['CODE']['UF_CONTRACTOR']['CODE'] ?>[]" id="contractor" data-dropup-auto="false" data-size="5"
                                    data-live-search="true" data-live-search-placeholder="Найти" class="form-control selectpicker">
                                <option value="">Ничего не выбрано</option>
                                <? foreach ($arResult['FIELDS']['CODE']['UF_CONTRACTOR']['LIST'] as $arEnum): ?>
                                    <option
                                            value="<?= $arEnum['ID'] ?>"
                                        <? if (in_array($arEnum['ID'], $arResult['REQUEST']['UF_CONTRACTOR'])): ?>
                                            selected
                                        <? endif; ?>
                                    ><?= $arEnum['VALUE'] ?></option>
                                <? endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <label for="tags" class="col-md-4">Теги</label>
                        </div>
                        <div class="col-md-8">
                            <input type="text" name="TAGS" id="tags" class="form-control col-md-8" placeholder="Введите теги, разделяя запятыми"
                                   value="<?= $arResult['REQUEST']['TAGS'] ?>">
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="row align-items-center">
                        <div class="col-md-12">
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="doc_external">Подписано МинЦифрой</label>
                                </div>
                                <div class="col-md-8">
                                    <select name="<?= $arResult['FIELDS']['CODE']['UF_EXTINT']['CODE'] ?>" id="doc_external" class="form-control">
                                        <option value="">Ничего не выбрано</option>
                                        <? foreach ($arResult['FIELDS']['CODE']['UF_EXTINT']['LIST'] as $arEnum): ?>
                                            <option
                                                    value="<?= $arEnum['ID'] ?>"
                                                <? if ($arEnum['ID'] == $arResult['REQUEST']['UF_EXTINT']): ?>
                                                    selected
                                                <? endif; ?>
                                            ><?= $arEnum['VALUE'] ?></option>
                                        <? endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <input type="submit" name="FIND[]" id="find" class="form-control btn-primary" value="Поиск">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section id="lnpa-table">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <? if (!empty($arResult['CARDS']['PUBLISH'])): ?>
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>Краткое название документа</th>
                                <th>Автор документа</th>
                                <th>Тип документа</th>
                                <th>Номер документа</th>
                                <th>Дата публикации</th>
                                <th>Контрагент</th>
                                <th>Теги</th>
                            </tr>
                            </thead>
                            <tbody>
                            <? foreach ($arResult['CARDS']['PUBLISH']['ITEMS'] as $arCard): ?>
                                <tr data-card="<?= $arCard['ID'] ?>">
                                    <td><a href="?page=detail_view&id=<?= $arCard['ID'] ?>"><?= $arCard['UF_NAME'] ?></a></td>
                                    <td><?= $arCard['UF_STRUCTURE'] ?></td>
                                    <td><?= $arCard['UF_TYPE_DOC'] ?></td>
                                    <td><?= $arCard['UF_NUMBER'] ?></td>
                                    <td><?= $arCard['UF_DATE'] ?></td>
                                    <td><?= $arCard['UF_CONTRACTOR'] ?></td>
                                    <td><?= $arCard['TAGS'] ?></td>
                                </tr>
                            <? endforeach; ?>
                            </tbody>
                        </table>
                        <div id="pagination" class="text-center">Страницы:
                            <? for ($iPage = 1; $iPage <= $arResult['CARDS']['NavPageCount']; $iPage++): ?>
                                <? if (
                                        $arResult['REQUEST']['FIND'][0] == $iPage ||
                                        $iPage == 1 && (is_string($arResult['REQUEST']['FIND'][0]) || !$arResult['REQUEST']['FIND'])
                                ): ?>
                                    <button type="submit" name="FIND[]" value="<?= $iPage ?>" class="current"><?= $iPage ?></button>
                                <? else: ?>
                                    <button type="submit" name="FIND[]" value="<?= $iPage ?>"><?= $iPage ?></button>
                                <? endif; ?>
                            <? endfor; ?>
                        </div>
                    <? else: ?>
                        <b class="text-danger">Результатов не найдено</b>
                    <? endif; ?>

                    <h3 class="title">Мои закладки</h3>
                    <? if (!empty($arResult['CARDS']['FAVOURITE'])): ?>
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th>Краткое название документа</th>
                                <th>Автор документа</th>
                                <th>Тип документа</th>
                                <th>Номер документа</th>
                                <th>Дата публикации</th>
                                <th>Контрагент</th>
                                <th>Теги</th>
                            </tr>
                            </thead>
                            <tbody>
                            <? foreach ($arResult['CARDS']['FAVOURITE'] as $arCard): ?>
                                <tr data-card="<?= $arCard['ID'] ?>">
                                    <td><a href="?page=detail_view&id=<?= $arCard['ID'] ?>"><?= $arCard['UF_NAME'] ?></a></td>
                                    <td><?= $arCard['UF_STRUCTURE'] ?></td>
                                    <td><?= $arCard['UF_TYPE_DOC'] ?></td>
                                    <td><?= $arCard['UF_NUMBER'] ?></td>
                                    <td><?= $arCard['UF_DATE'] ?></td>
                                    <td><?= $arCard['UF_CONTRACTOR'] ?></td>
                                    <td><?= $arCard['TAGS'] ?></td>
                                </tr>
                            <? endforeach; ?>
                            </tbody>
                        </table>
                    <? else: ?>
                        <p class="text-info text-center">Чтобы добавить документ в закладки, перейдите на его карточку и нажмите кнопку «Добавить в закладки»</p>
                    <? endif; ?>
                </div>
            </div>
        </div>
    </section>
</form>