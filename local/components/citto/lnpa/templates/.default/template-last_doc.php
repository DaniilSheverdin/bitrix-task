<section id="lnpa-last-doc">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <h5 class="title">Черновики:</h5>
                <? if (!empty($arResult['CARDS']['DRAFT'])): ?>
                    <? foreach ($arResult['CARDS']['DRAFT']['ITEMS'] as $arCard): ?>
                        <div class="row item mb-2">
                            <div class="col-md-12">
                                <span><?= $arCard['UF_STRUCTURE'] ?></span>
                                <a href="?page=detail_edit&id=<?= $arCard['ID'] ?>" class="text-info"><?= $arCard['UF_NAME'] ?></a>
                            </div>
                        </div>
                    <? endforeach; ?>
                <? endif; ?>
                <a href="?page=detail_add" class="btn btn-success mt-2">Добавить новый документ</a>
            </div>
            <div class="col-md-4">
                <h5 class="title">На проверке:</h5>
                <? if (!empty($arResult['CARDS']['MODERATION'])): ?>
                    <? foreach ($arResult['CARDS']['MODERATION']['ITEMS'] as $arCard): ?>
                        <div class="row item mb-2">
                            <div class="col-md-12">
                                <? if ($arResult['ROLE'] == 'ADMIN'): ?>
                                    <a href="?page=detail_edit&id=<?= $arCard['ID'] ?>" class="text-danger"><?= $arCard['UF_NAME'] ?></a>
                                <? else: ?>
                                    <a href="?page=detail_view&id=<?= $arCard['ID'] ?>" class="text-danger"><?= $arCard['UF_NAME'] ?></a>
                                <? endif; ?>
                                <br>
                                <span class="structure"><?= $arCard['UF_STRUCTURE'] ?></span>
                            </div>
                        </div>
                    <? endforeach; ?>
                <? endif; ?>
            </div>
            <div class="col-md-4">
                <h5 class="title">Последние опубликованные:</h5>
                <? if (!empty($arResult['CARDS']['PUBLISH'])): ?>
                    <? foreach ($arResult['CARDS']['PUBLISH']['ITEMS'] as $arCard): ?>
                        <div class="row item mb-2">
                            <div class="col-md-12">
                                <a href="?page=detail_view&id=<?= $arCard['ID'] ?>" class="text-success"><?= $arCard['UF_NAME'] ?></a>
                                <br>
                                <span class="structure"><?= $arCard['UF_STRUCTURE'] ?></span>
                            </div>
                        </div>
                    <? endforeach; ?>
                <? endif; ?>
            </div>
        </div>
    </div>
</section>
