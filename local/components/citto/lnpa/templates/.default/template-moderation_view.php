<section id="lnpa-moderation-view">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-6">
                <h5 class="title">На проверке:</h5>
                <? if (!empty($arResult['CARDS']['MODERATION'])): ?>
                    <? foreach ($arResult['CARDS']['MODERATION']['ITEMS'] as $arCard): ?>
                        <div class="row item mb-2">
                            <div class="col-md-12">
                                <a href="?page=detail_edit&id=<?= $arCard['ID'] ?>" class="text-danger"><?= $arCard['UF_NAME'] ?></a>
                                <br>
                                <span class="structure"><?= $arCard['UF_STRUCTURE'] ?></span>
                            </div>
                        </div>
                    <? endforeach; ?>
                <? endif; ?>
            </div>
            <div class="col-md-6">
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