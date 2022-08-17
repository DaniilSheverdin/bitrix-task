<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

if ($arResult["FatalError"] != '') {
    ?>
    <span class='errortext'><?= $arResult["FatalError"] ?></span><br/><br/>
    <?php
} else {
    if ($arResult["ErrorMessage"] != '') {
        ?>
        <span class='errortext'><?= $arResult["ErrorMessage"] ?></span><br/><br/>
        <?php
    }
    ?>
    <form method="post" action="<?= POST_FORM_ACTION_URI ?>" enctype="multipart/form-data" name="meeting_add">
        <table cellpadding="0" cellspacing="0" border="0" width="100%" class="intask-main data-table">
            <tbody>
            <?php foreach ($arResult["Item"] as $key => $item) : ?>
                <tr>
                    <td><?php if ($arResult['ALLOWED_FIELDS'][$key]['MANDATORY'] == 'Y') :
                        ?><span class='red_star'>*</span><?php
                        endif; ?><?= $arResult['ALLOWED_FIELDS'][$key]["NAME"] ?>:</td>
                    <?php if ($arResult['ALLOWED_FIELDS'][$key]['TYPE'] == 'double') : ?>
                        <td><input type="number" id="<?= mb_strtolower($key) ?>" name="<?= mb_strtolower($key) ?>" value="<?= $item ?>" size="50"></td>
                    <?php elseif ($arResult['ALLOWED_FIELDS'][$key]['TYPE'] == 'string') : ?>
                        <td><input type="text" id="<?= mb_strtolower($key) ?>" name="<?= mb_strtolower($key) ?>" value="<?= $item ?>" size="50"></td>
                    <?php elseif ($arResult['ALLOWED_FIELDS'][$key]['TYPE'] == 'enumeration') : ?>
                        <td>
                            <select id = "<?= mb_strtolower($key) ?>" name="<?= mb_strtolower($key) ?>">
                                <?php foreach ($arResult['SELECT_TYPE'][$key] as $arEnum) : ?>
                                    <?php if ($arResult["Item"][$key] == $arEnum['ID']) : ?>
                                        <option value="<?= $arEnum['ID'] ?>" selected><?= $arEnum['VALUE'] ?></option>
                                    <?php else : ?>
                                        <option value="<?= $arEnum['ID'] ?>"><?= $arEnum['VALUE'] ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </td>
                    <?php elseif ($arResult['ALLOWED_FIELDS'][$key]['TYPE'] == 'boolean') : ?>
                        <td>
                            <select id="<?= mb_strtolower($key) ?>" name="<?= mb_strtolower($key) ?>">
                                <option value="1" <?=($item)?'selected':''?> >Да</option>
                                <option value="0" <?=(!$item)?'selected':''?> >Нет</option>
                            </select>
                        </td>
                    <?php elseif ($arResult['ALLOWED_FIELDS'][$key]['TYPE'] == 'file') : ?>
                    <td>
                        <?php $APPLICATION->IncludeComponent(
                            "bitrix:main.file.input",
                            "drag_n_drop",
                            [
                                "INPUT_NAME"=>strtolower($key),
                                "MULTIPLE"=>"Y",
                                "MODULE_ID"=>"iblock",
                                "MAX_FILE_SIZE"=>"",
                                "ALLOW_UPLOAD"=>"A",
                                "ALLOW_UPLOAD_EXT"=>"",
                            ],
                            false
                        ); ?>
                    </td>
                    <?php endif; ?>
                </tr>
            <?php endforeach; ?>
                <tr>
                    <td>Файлы:</td>
                    <td>
                        <?php foreach ($arResult['arCurFiles'] as $k => $item) : ?>
                            <p><?=$item['ORIGINAL_NAME']?> <a class="delete" id="<?=$k?>" href="#">удалить</a></p>
                        <?php endforeach; ?>
                        <input type="hidden" name="deleteFiles" value>
                    </td>
                </tr>
            </tbody>
        </table>

        <br>
        <input type="submit" name="save" value="<?= GetMessage("INTASK_C87_SAVE") ?>">
        <?= bitrix_sessid_post() ?>
    </form>
    <?php
}
