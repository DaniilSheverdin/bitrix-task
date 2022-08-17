<section id="lnpa-detail-view">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <? if ($arResult[$arResult['ACTION']]['RESULT']['STATUS']): ?>
                    <div class="alert alert-success" role="alert">
                        <?= $arResult[$arResult['ACTION']]['RESULT']['TEXT'] ?>
                    </div>
                <? else: ?>
                    <h3 class="title"><?= $arResult['DETAIL_CARD']['UF_FULLNAME'] ?></h3>
                    <div class="row">
                        <div class="col-md-12 buttons d-flex justify-content-between align-items-center">
                            <? if (!empty($arResult['DETAIL_CARD']['FILE'])): ?>
                                <a href="<?= $arResult['DETAIL_CARD']['FILE']['SRC'] ?>" class="download-doc text-success" download><i
                                            class="fa fa-download"></i><span>Скачать документ</span></a>
                            <? endif; ?>

                            <? if ($arResult['DETAIL_CARD']['FAVOURITE']): ?>
                                <a href="<?= $arResult['URI'] ?>&action=del_favourite" class="bookmark-doc text-info"><i class="fa fa-bookmark"></i><span>Убрать из закладок</span></a>
                            <? elseif ($arResult['DETAIL_CARD']['STATUS']['CODE'] == 'PUBLISH'): ?>
                                <a href="<?= $arResult['URI'] ?>&action=add_favourite" class="bookmark-doc text-info"><i class="fa fa-bookmark"></i><span>Добавить в закладки</span></a>
                            <? endif; ?>


                            <? if (($arResult['ROLE'] == 'ADMIN' || $arResult['ROLE'] == 'OPERATOR') && $arResult['DETAIL_CARD']['STATUS']['CODE'] == 'DRAFT'): ?>
                                <a href="?page=detail_view&action=del_doc&id=<?= $arResult['DETAIL_CARD']['ID'] ?>" class="del-doc float-right text-danger ml-3">
                                    <i class="fa fa-edit"></i><span>Удалить</span>
                                </a>
                            <? endif; ?>
                            <? if ($arResult['ROLE'] == 'ADMIN' || $arResult['ROLE'] == 'OPERATOR' && $arResult['DETAIL_CARD']['STATUS']['CODE'] == 'DRAFT'): ?>
                                <a href="?page=detail_edit&id=<?= $arResult['DETAIL_CARD']['ID'] ?>" class="edit-doc float-right text-primary">
                                    <i class="fa fa-edit"></i><span>Изменить</span>
                                </a>
                            <? endif; ?>

                            <div>
                                <? if ($arResult['IS_USER_SIGN'] && $arResult['FILE_SIGN_ID']): ?>
                                    <div>
                                        <a href="#" js-card-id="<?= $arResult['DETAIL_CARD']['ID'] ?>" js-file-sign-id="<?= $arResult['FILE_SIGN_ID'] ?>" js-card-id="<?= $arResult['DETAIL_CARD']['ID'] ?>" id="sign" js-session="<?= bitrix_sessid() ?>"
                                           class="btn-danger btn text-white  edit-doc float-right text-primary">
                                            <i class="fa fa-edit"></i><span>Подписать лист ознакомления</span>
                                        </a>
                                    </div>
                                <? endif; ?>
                                <? if ($arResult['ROLE'] == 'ADMIN' || $arResult['ROLE'] == 'CLERK'): ?>
                                    <div>
                                        <a href="?page=sign_generation&id=<?= $arResult['DETAIL_CARD']['ID'] ?>"
                                           class="btn-secondary btn text-white  edit-doc float-right text-primary mt-2">
                                            <i class="fa fa-edit"></i><span>Создать сбор подписей</span>
                                        </a>
                                    </div>
                                    <div>
                                        <a href="?page=statistics&id=<?= $arResult['DETAIL_CARD']['ID'] ?>"
                                           class="btn-secondary btn text-white  edit-doc float-right text-primary mt-2">
                                            <i class="fa fa-edit"></i><span>Статистика</span>
                                        </a>
                                    </div>
                                <? endif; ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row items">
                                <div class="col-md-12">
                                    <div class="row item">
                                        <div class="col-md-6">Полное название документа:</div>
                                        <div class="col-md-6"><?= $arResult['DETAIL_CARD']['UF_FULLNAME'] ?></div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="row item">
                                        <div class="col-md-6">Краткое название документа:</div>
                                        <div class="col-md-6"><?= $arResult['DETAIL_CARD']['UF_NAME'] ?></div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="row item">
                                        <div class="col-md-6">Автор документа:</div>
                                        <div class="col-md-6"><?= $arResult['DETAIL_CARD']['UF_STRUCTURE'] ?></div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="row item">
                                        <div class="col-md-6">Тип документа:</div>
                                        <div class="col-md-6"><?= $arResult['DETAIL_CARD']['UF_TYPE_DOC'] ?></div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="row item">
                                        <div class="col-md-6">Номер документа:</div>
                                        <div class="col-md-6"><?= $arResult['DETAIL_CARD']['UF_NUMBER'] ?></div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="row item">
                                        <div class="col-md-6">Дата публикации:</div>
                                        <div class="col-md-6"><?= $arResult['DETAIL_CARD']['UF_DATE'] ?></div>
                                    </div>
                                </div>
                                <div class="col-md-12 mt-3 hide">
                                    <div class="row item">
                                        <div class="col-md-6">Подписано Министерством Информатизации:</div>
                                        <div class="col-md-6"><?= $arResult['DETAIL_CARD']['UF_EXTINT'] ?></div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="row item">
                                        <div class="col-md-6">Контрагент:</div>
                                        <div class="col-md-6"><?= $arResult['DETAIL_CARD']['UF_CONTRACTOR'] ?></div>
                                    </div>
                                </div>
                                <!--                            <div class="col-md-12">-->
                                <!--                                <div class="row item">-->
                                <!--                                    <div class="col-md-6">Полное имя контрагента:</div>-->
                                <!--                                    <div class="col-md-6">--><? //= $arResult['DETAIL_CARD']['UF_FULL_CONTRACTOR'] ?><!--</div>-->
                                <!--                                </div>-->
                                <!--                            </div>-->
                                <!--                            <div class="col-md-12">-->
                                <!--                                <div class="row item">-->
                                <!--                                    <div class="col-md-6">Организационно-правовая форма:</div>-->
                                <!--                                    <div class="col-md-6">-->
                                <? //= $arResult['DETAIL_CARD']['UF_ORGANIZATIONAL_LEGAL'] ?><!--</div>-->
                                <!--                                </div>-->
                                <!--                            </div>-->
                                <div class="col-md-12 mt-3">
                                    <div class="row item">
                                        <div class="col-md-6">Статус:</div>
                                        <div class="col-md-6"><?= $arResult['DETAIL_CARD']['STATUS']['NAME'] ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row items">
                                <div class="col-md-12">
                                    <div class="row item">
                                        <div class="col-md-6">Родительский документ:</div>
                                        <div class="col-md-6"><a href="?page=detail_view&id=<?= $arResult['DETAIL_CARD']['IDS']['UF_PARENT_ELEM'] ?>"
                                                                 target="_blank"><?= $arResult['DETAIL_CARD']['UF_PARENT_ELEM'] ?></a></div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="row item">
                                        <div class="col-md-6">Другие версии документа:</div>
                                        <div class="col-md-6">
                                            <select onclick="location.href=document.querySelector('#js-versions').value;" id="js-versions">
                                                <option value="#">Не выбрано</option>
                                                <? foreach ($arResult['DETAIL_CARD']['VERSIONS'] as $iID => $arVersion): ?>
                                                    <option value="?page=version_view&id=<?= $iID ?>"><?= $arVersion['UF_NAME'] ?> (<?= $iID ?>)</option>
                                                <? endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="row item">
                                        <div class="col-md-6">Теги:</div>
                                        <div class="col-md-6">
                                            <? foreach ($arResult['DETAIL_CARD']['TAGS_ARR'] as $sTag): ?>
                                                <a href="#"><?= $sTag ?></a>
                                            <? endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <? endif; ?>
            </div>
        </div>
    </div>
</section>
