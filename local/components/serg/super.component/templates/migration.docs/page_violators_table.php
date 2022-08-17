<?php


/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

if ($arResult['ACCESS']):?>

<?php
    \Bitrix\Main\UI\Extension::load("ui.forms");
    ?>

    <div class="page_title">
        <span><?=GetMessage('BUTTON_VIOLATORS_TABLE')?></span>

    </div>
    <div class="action_row">
        <div class="grid_filter">
            <?$APPLICATION->IncludeComponent('bitrix:main.ui.filter', '', [
                'FILTER_ID' => $arResult['grid_id'],
                'GRID_ID' => $arResult['grid_id'],
                'FILTER' => $arResult['UI_FILTER'],
                'ENABLE_LIVE_SEARCH' => true,
                'ENABLE_LABEL' => true
            ]);?>
        </div>
        <div id="preloader">
            <div id="js-button"></div>
        </div>
    </div>


    <? $APPLICATION->IncludeComponent('bitrix:main.ui.grid', '', [
        'GRID_ID' => $arResult['grid_id'],
        'COLUMNS' => $arResult['columns'],
        'ROWS' => $arResult['list'],
        'SHOW_ROW_CHECKBOXES' => true,
        'NAV_OBJECT' => $arResult['nav'],
        'AJAX_MODE' => 'Y',
        'AJAX_ID' => \CAjax::getComponentID('serg:main.ui.grid', '', ''),
        'PAGE_SIZES' =>  [
            ['NAME' => '5', 'VALUE' => '5'],
            ['NAME' => '10', 'VALUE' => '10'],
            ['NAME' => '20', 'VALUE' => '20'],
            ['NAME' => '50', 'VALUE' => '50']
        ],
        'AJAX_OPTION_JUMP'          => 'N',
        'SHOW_CHECK_ALL_CHECKBOXES' => false,
        'SHOW_ROW_ACTIONS_MENU'     => false,
        'SHOW_GRID_SETTINGS_MENU'   => true,
        'SHOW_NAVIGATION_PANEL'     => true,
        'SHOW_PAGINATION'           => true,
        'SHOW_SELECTED_COUNTER'     => true,
        'SHOW_TOTAL_COUNTER'        => true,
        'SHOW_PAGESIZE'             => true,
        'SHOW_ACTION_PANEL'         => false,
        'ALLOW_COLUMNS_SORT'        => true,
        'ALLOW_COLUMNS_RESIZE'      => true,
        'ALLOW_HORIZONTAL_SCROLL'   => true,
        'ALLOW_SORT'                => true,
        'ALLOW_PIN_HEADER'          => true,
        'AJAX_OPTION_HISTORY'       => 'N'
    ]);?>

    <form method="post" action="/isolation/?PAGE=ADD&TYPE=VIOLATORS" enctype="multipart/form-data">
        <h3><?=GetMessage('TITLE_ADD_PEOPLES')?></h3>
        <label class="ui-ctl ui-ctl-file-btn">
            <input name="file" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel, .xls" type="file" class="ui-ctl-element">
            <div class="ui-ctl-label-text">Выберите файл</div>
        </label>
        <button type="submit" class="ui-btn ui-btn-icon-back"><?=GetMessage('BUTTON_UPLOAD_XLS')?></button>
        <div class="message"><span class="message_success"><?=GetMessage('FILES_TYPE')?></span></div>
    </form>


    <script>
        var filter = <?php echo json_encode($arResult['FILTER_DATA']); ?>;
        console.log('filter', filter);

        BX.addCustomEvent('BX.Main.Filter:apply', BX.delegate(function (grid_id) {

            filter = {};
            var searchString = BX.Main.filterManager.data[grid_id].search.lastSearchString;
            console.log('searchString', searchString);

            if (searchString) filter['UF_FIO'] = '%'+searchString+'%';

            BX.Main.filterManager.data[grid_id].params.PRESETS[0].FIELDS.map(function (el) {

                if (el.NAME === "UF_DATE_VIOLATION" && el.VALUES._from && el.VALUES._to) {

                    filter['>=UF_DATE_VIOLATION'] = el.VALUES._from;
                    filter['<=UF_DATE_VIOLATION'] = el.VALUES._to
                }

            });
            console.log('filter', filter);

        }));
    </script>


<?else:

    echo GetMessage('ACCESS_ERROR');

endif;?>





