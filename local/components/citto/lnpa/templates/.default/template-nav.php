<section id="lnpa-nav">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-md-9">
                <h1><a href="/citto/lpa">Каталог ЛПА</a></h1>
            </div>
            <div class="col-md-3">
                <div id="menu-list">
                    <? foreach ($arResult['NAV_MENU'] as $arItem): ?>
                        <a href="?<?= $arItem['URL'] ?>" class="menu-item <? if ($arItem['ACTIVE'] == 'Y') echo 'active'; ?>"><?= $arItem['NAME'] ?></a>
                    <? endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</section>
<? if (!empty($arResult['ERRORS'])): ?>
    <section id="lnpa-errors">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-12">
                    <div class="errors">
                        <? foreach ($arResult['ERRORS'] as $sError): ?>
                            <p><?= $sError ?></p>
                        <? endforeach; ?>
                    </div>
                </div>
            </div>
    </section>
<? endif; ?>
