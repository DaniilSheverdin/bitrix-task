<section id="lnpa-detail-view">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                    <h3 class="title"><?= $arResult['DETAIL_CARD']['UF_FULLNAME'] ?></h3>
                    <div class="row">
                        <div class="col-md-12 buttons">
                            <? if (!empty($arResult['DETAIL_CARD']['FILE'])): ?>
                                <a href="<?= $arResult['DETAIL_CARD']['FILE']['SRC'] ?>" class="download-doc text-success" download><i
                                            class="fa fa-download"></i><span>Скачать документ</span></a>
                            <? endif; ?>
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
                                <div class="col-md-12 mt-3">
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
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row items">
                                <div class="col-md-12">
                                    <div class="row item">
                                        <div class="col-md-6">Родительский документ:</div>
                                        <div class="col-md-6"><a href="#"><?= $arResult['DETAIL_CARD']['UF_PARENT_ELEM'] ?></a></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
        </div>
    </div>
</section>