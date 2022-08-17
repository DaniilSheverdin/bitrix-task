<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Шаг 1 - введите данные из Дело</h3>
    </div>
    <?php
    if (!empty($arResult['ERROR'])) {
        ?>
    <div class="alert alert-danger" role="alert">
        <?=$arResult['ERROR'];?>
    </div>
        <?php
    }
    ?>
    <form method="POST" action="/control-orders/import/">
        <input type="hidden" name="step" value="2" />
        <div class="box-body">
            <div class="row">
                <div class="col-3">
                    <b>Номер:</b><br />
                    <input
                        class="form-control js-input js-input-number"
                        name="NUMBER"
                        required
                        value="<?=$arResult['NUMBER']?>" />
                </div>
                <div class="col-3">
                    <b>Дата поручения:</b><br/>
                    <input
                        class="form-control js-input js-input-date"
                        name="DATE"
                        required
                        value="<?=$arResult['DATE']?>"
                        onclick="BX.calendar({node: this, field: this, bTime: false});" />
                </div>
                <?
                $disabled = true;
                if (!empty($arResult['ERROR'])) {
                    $disabled = false;
                }
                ?>
                <div class="col-3">
                    <br/>
                    <button
                        class="ui-btn ui-btn-icon-cloud <?=$disabled?'ui-btn-disabled':'ui-btn-primary'?> js-submit"
                        <?=$disabled?'disabled':''?>></button>
                </div>
            </div>
        </div>
    </form>
</div>
