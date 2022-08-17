<?php

$this->addExternalCss($templateFolder."/select2.css");
$this->addExternalJS($templateFolder.'/select2.min.js');
?>

<div class="omni js-omni">
    <div class="omni_table">
        <div class="omni_table_head">
            <div class="department"><?=GetMessage('DEPARTMENT')?></div>
            <div class="name"><?=GetMessage('USER')?></div>
            <div class="create"><?=GetMessage('CREATED')?></div>
            <div class="complete"><?=GetMessage('COMPLETED')?></div>
            <div class="wrong"><?=GetMessage('DEFECTION')?></div>
            <div class="percent_exec"><?=GetMessage('PERCENT')?></div>
        </div>
        <div id="js-omni_table_body" class="omni_table_body">
            <? foreach ($arResult['USERS_DATA'] as $arValues) : ?>
                <div class="row">
                    <div class="department"><p><?=$arValues['DEPARTMENT']?></p></div>
                    <div class="name"><p><?=$arValues['USER']['FULL_NAME']?></p></div>
                    <div class="create"><p><?=$arValues['CREATED']?></p></div>
                    <div class="complete"><p><?=$arValues['COMPLETED']?></p></div>
                    <div class="wrong"><p><?=$arValues['DEFECTION']?></p></div>
                    <div class="percent_exec"><p><?=$arValues['PERCENT']?></p></div>
                </div>
            <?endforeach;?>
        </div>
    </div>

    <? if ($USER->IsAdmin() || CSite::InGroup([GROUP_ID_OMNI])) : ?>
        <div id="js-add_user" class="omni_add-user">
            <form id="js-omni_form" class="omni_form">

                <div class="input-group">
                    <label for="js-names_select"><?=GetMessage('USER')?></label>
                    <select name="user_id" id="js-names_select">
                        <option value=""></option>
                        <? foreach ($arResult['USERS'] as $intID => $arValues) : ?>
                            <option value="<?=$intID?>"><?=$arValues['FULL_NAME']?></option>
                        <?endforeach;?>
                    </select>
                </div>
                <div class="input-group">
                    <label for="js-departments_select"><?=GetMessage('DEPARTMENT')?></label>
                    <select name="department_id" id="js-departments_select"></select>
                </div>

                <div class="input-group">
                    <label for="js-create"><?=GetMessage('CREATED')?></label>
                    <input id="js-create" type="number" name="create">
                </div>
                <div class="input-group">
                    <label for="js-complete"><?=GetMessage('COMPLETED')?></label>
                    <input id="js-complete" type="number" name="complete">
                </div>
                <div class="input-group">
                    <label for="wrong"><?=GetMessage('DEFECTION')?></label>
                    <input id="wrong" type="number" name="wrong">
                </div>
                <div class="input-group">
                    <label for="js-percent_exec"><?=GetMessage('PERCENT')?></label>
                    <input readonly id="js-percent_exec" type="number" name="percent_exec">
                </div>

                <div class="input-group">
                    <div id="js-message"></div>
                </div>
                <div class="input-group">
                    <button id="js-save" type="submit" class="omni_button"><?=GetMessage('BUTTON_SAVE')?></button>
                </div>
            </form>
        </div>
        <div id="js-add" class="omni_button"><?=GetMessage('BUTTON_ADD')?></div>
    <?endif;?>
</div>
