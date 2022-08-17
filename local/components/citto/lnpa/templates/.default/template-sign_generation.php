<section id="lnpa-sign-genaration">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h4>Собрать подписи по "<?= $arResult['ITEM']['UF_FULLNAME'] ?>"</h4>

                <? if ($arResult['IS_COLLECTION']): ?>
                    <div class="mt-4"></div>
                    <div class="alert alert-warning" role="alert">Внимание! Сбор подписей уже создан. При создании нового сбора предыдущие результаты не сохранятся!</div>
                <? endif; ?>

                <form action="<?= POST_FORM_ACTION_URI ?>" method="post" class="mt-4">
                    <?= bitrix_sessid_post(); ?>
                    <b>Пользователи и отделы</b>
                    <?
                    $APPLICATION->IncludeComponent(
                        'bitrix:main.user.selector',
                        ' ',
                        [
                            "ID" => "DEPARTMENTS_USERS",
                            "API_VERSION" => 3,
                            "LIST" => [],
                            "INPUT_NAME" => "departments_users[]",
                            "USE_SYMBOLIC_ID" => true,
                            "BUTTON_SELECT_CAPTION" => 'добавить',
                            "SELECTOR_OPTIONS" =>
                                [
                                    'departmentSelectDisable' => 'N',
                                    'enableUsers' => 'Y',
                                    'departmentFlatEnable' => 'Y'
                                ]
                        ]
                    );
                    ?>
                    <div class="mt-4"></div>
                    <b>Заголовок листа ознакомления</b>
                    <input type="text" name="title" class="form-control" placeholder="Заголовок листа ознакомления" required="">

                    <div class="mt-4"></div>
                    <b>Оповещение об ознакомлении</b>
                    <br>
                    <input type="checkbox" id="alert_check" name="alert_check" class="form-check-input">
                    <label class="form-check-label" for="alert_check">Напомнить мне об этом сборе</label>
                    <input type="date" name="alert_date" class="form-control" placeholder="" required="" disabled>

                    <div class="mt-4 d float-right">
                        <button type="submit" class="btn btn-primary">Создать сбор подписей</button>
                        <a href="/lpa" class="btn btn-secondary">Отменить</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
