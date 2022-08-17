<section id="lnpa-sign-genaration">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <h4>Статистика ознакомления с "<?= $arResult['ITEM']['UF_FULLNAME'] ?>"</h4>

                <p>Ознакомились <?= $arResult['ELEMENT_SIGN']['COUNT_SIGNED'] ?> сотрудника из <?= $arResult['ELEMENT_SIGN']['COUNT'] ?></p>

                <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th>ФИО сотрудника</th>
                        <th>Отдел/Группа</th>
                        <th>Статус</th>
                    </tr>
                    </thead>
                    <tbody>
                    <? foreach($arResult['ELEMENT_SIGN']['USERS'] as $arUser): ?>
                        <tr>
                            <td><?= $arUser['FIO'] ?></td>
                            <td><?= $arResult['ELEMENT_SIGN']['DATA_DEPARTMENTS'][$arUser['DEPARTMENT_ID']] ?></td>
                            <td>
                                <? if ($arUser['STATUS'] == 'SIGNED'): ?>
                                    <span class="text-success">Ознакомлен</span>
                                <? else: ?>
                                    <span class="text-danger">Не ознакомлен</span>
                                <? endif; ?>
                            </td>
                        </tr>
                    <? endforeach; ?>
                    </tbody>
                </table>

                <form action="<?= POST_FORM_ACTION_URI ?>" method="post" class="mt-4">
                    <?= bitrix_sessid_post(); ?>
                    <div class="mt-4 d float-right">
                        <button type="submit" class="btn btn-primary" name="repeat" value="<?= $arResult['ITEM']['ID'] ?>">Оповестить повторно</button>
                        <? if ($sFileSignSrc = $arResult['ITEM']['SIGN_FILE']['SRC']): ?>
                            <a href="<?= $sFileSignSrc ?>" class="btn btn-secondary" download>Экспорт</a>
                        <? endif; ?>
                        <a href="/lpa" class="btn btn-secondary">Отменить</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
