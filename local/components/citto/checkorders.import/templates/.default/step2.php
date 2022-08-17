<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Шаг 2 - Данные протокола</h3>
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
    <div class="box-body">
        <div class="row">
            <div class="col-6">
                <b>Название протокола:</b> <?=$arResult['PROTOCOL']['ANNOTAT'];?><br/>
                <b>Номер протокола:</b> <?=$arResult['PROTOCOL']['FREE_NUM'];?><br/>
                <b>Дата протокола:</b> <?=$arResult['PROTOCOL']['DOC_DATE'];?><br/>
                <?
                if (!empty($arResult['PROTOCOL']['files'])) {
                    ?>
                    <b>Файлы:</b>
                    <ul>
                    <?
                    foreach ($arResult['PROTOCOL']['files'] as $file) {
                        ?>
                        <li><a
                            href="<?=$file['LOCAL'];?>"
                            download="<?=$file['NAME'];?>">
                            <?=$file['NAME'];?>
                        </a></li>
                        <?
                    }
                    ?>
                    </ul>
                    <?
                }
                ?>
            </div>
        </div>
        <?$APPLICATION->IncludeComponent(
            'bitrix:main.ui.grid',
            'modified',
            [
                'GRID_ID'                   => $arResult['LIST_ID'],
                'COLUMNS'                   => $arResult['COLUMNS'],
                'ROWS'                      => $arResult['ROWS'],
                'SHOW_ROW_CHECKBOXES'       => true,
                'NAV_OBJECT'                => $arResult['NAV'],
                'AJAX_MODE'                 => 'N',
                'SHOW_CHECK_ALL_CHECKBOXES' => true,
                'SHOW_ROW_ACTIONS_MENU'     => true,
                'ACTION_PANEL'              => $arResult['ACTION_PANEL'],
                'SHOW_GRID_SETTINGS_MENU'   => true,
                'SHOW_NAVIGATION_PANEL'     => true,
                'SHOW_PAGINATION'           => true,
                'SHOW_SELECTED_COUNTER'     => true,
                'SHOW_TOTAL_COUNTER'        => true,
                'SHOW_PAGESIZE'             => false,
                'SHOW_ACTION_PANEL'         => true,
                'ALLOW_COLUMNS_SORT'        => true,
                'ALLOW_COLUMNS_RESIZE'      => true,
                'ALLOW_HORIZONTAL_SCROLL'   => true,
                'ALLOW_SORT'                => true,
                'ALLOW_PIN_HEADER'          => true,
                'ALLOW_INLINE_EDIT'         => true,
                'ALLOW_INLINE_EDIT_ALL'     => true,
                'ALLOW_EDIT'                => true,
                'ALLOW_EDIT_ALL'            => true,
                'SHOW_GROUP_EDIT_BUTTON'    => true,
                'EDITABLE'                  => true,
                'AJAX_OPTION_HISTORY'       => 'N',
            ]
        );?>
    </div>
</div>
