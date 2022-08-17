<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Шаг 1 - введите данные из Дело</h3>
    </div>
    <div class="alert alert-info" role="alert">
         По указанным реквизитам найдено несколько документов. Выберите требуемый документ.
    </div>
    <div class="box-body">
        <div class="row">
            <div class="col-3">
                <b>Номер:</b><br />
                <input
                    class="form-control"
                    name="NUMBER"
                    readonly
                    value="<?=$arResult['NUMBER']?>" />
            </div>
            <div class="col-3">
                <b>Дата поручения:</b><br/>
                <input
                    class="form-control"
                    name="DATE"
                    readonly
                    value="<?=$arResult['DATE']?>" />
            </div>
        </div>
    </div>
    <?php
    foreach ($arResult['RESULT'] as $row) {
        ?>
    <form method="POST" action="/control-orders/import/">
        <input type="hidden" name="step" value="2" />
        <input type="hidden" name="ISN" value="<?=$row['ISN_DOC'];?>" />
        <input type="hidden" name="NUMBER" value="<?=$row['FREE_NUM'];?>" />
        <input type="hidden" name="DATE" value="<?=$row['DOC_DATE'];?>" />
        <div class="box-body">
            <div class="row">
                <div class="col-7">
                    <?=$row['ANNOTAT'];?>
                </div>
                <div class="col-2">
                    <button class="ui-btn ui-btn-icon-cloud ui-btn-primary js-submit">
                        Выбрать
                    </button><br/>&nbsp;
                </div>
            </div>
        </div>
    </form>
        <?php
    }
    ?>
</div>
