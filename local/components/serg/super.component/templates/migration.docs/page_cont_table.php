<?php

use Bitrix\Main\Grid\Panel\DefaultValue;
use Bitrix\Main\Grid\Panel\Snippet\Button;
use Bitrix\Main\Grid\Panel\Snippet\Onchange;
use Bitrix\Main\Grid\Panel\Actions;

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

\Bitrix\Main\UI\Extension::load("ui.forms");



if ($arResult['ACCESS']) : ?>

    <div class="page_title">
        <span><?=GetMessage('BUTTON_CONT_TABLE')?></span>

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
		<div id="js-button-contact"></div>
	</div>

</div>
<div class="action_row">
    <div id="add_element_buttons"></div>
</div>

<form action="" method="post" class="add_element_form">
    <div class="add_element_form_row">
    <? foreach ($arResult['columns'] as $column) : ?>
        <? if ($column['id'] != 'ID') : ?>
        <div>
            <div class="name"><label for="<?=$column['id']?>"><?=$column['name']?></label></div>
            <div class="value">
                <? if ($column['type'] == 'list') : ?>
                    <div class="ui-ctl ui-ctl-after-icon ui-ctl-dropdown">
                        <div class="ui-ctl-after ui-ctl-icon-angle"></div>
                        <select class="ui-ctl-element" name="<?=$column['id']?>" id="<?=$column['id']?>">
                            <? foreach ($column['items'] as $key => $item) : ?>
                            <option value="<?=$key?>"><?=$item?></option>
                            <?endforeach;?>
                        </select>
                    </div>
                <?elseif ($column['type'] == 'date') : ?>
                    <div class="ui-ctl ui-ctl-after-icon ui-ctl-date">
                        <div class="ui-ctl-after ui-ctl-icon-calendar"></div>
                        <input class="ui-ctl-element" id="<?=$column['id']?>" name="<?=$column['id']?>" type="text" onclick="BX.calendar({node: this, field: this, bTime: false});">
                    </div>
                <?elseif ($column['type'] == 'checkbox') : ?>
                        <input id="<?=$column['id']?>" value="yes" name="<?=$column['id']?>" type="checkbox">
                <?else : ?>
                <div class="ui-ctl ui-ctl-textbox">
                    <input class="ui-ctl-element" id="<?=$column['id']?>" name="<?=$column['id']?>" type="<?=$column['type'] ?? "text"?>">
                </div>
                <?endif;?>
            </div>
        </div>
        <?endif;?>
    <?endforeach;?>
    </div>
</form>
<?
    function getSaveEditButton()
    {

        $onchange = new Onchange();
        $onchange->addAction(array("ACTION" => Actions::CALLBACK, "DATA" => array(array("JS" => "validSelectedSave(self.parent)"))));

        $saveButton = new Button();

        $saveButton->setClass(DefaultValue::SAVE_BUTTON_CLASS);
        $saveButton->setText("Сохранить");
        $saveButton->setId(DefaultValue::SAVE_BUTTON_ID);
        $saveButton->setOnchange($onchange);

        return $saveButton->toArray();
    }



    function getCancelEditButton()
    {

        $onchange = new Onchange();
        $onchange->addAction(array("ACTION" => Actions::SHOW_ALL, "DATA" => array()));
        $onchange->addAction(array("ACTION" => Actions::CALLBACK, "DATA" => array(array("JS" => "Grid.editSelectedCancel()"))));
        $onchange->addAction(array("ACTION" => Actions::REMOVE, "DATA" => array(array("ID" => DefaultValue::SAVE_BUTTON_ID), array("ID" => DefaultValue::CANCEL_BUTTON_ID))));

        $cancelButton = new Button();
        $cancelButton->setClass(DefaultValue::CANCEL_BUTTON_CLASS);
        $cancelButton->setText("Отменить");
        $cancelButton->setId(DefaultValue::CANCEL_BUTTON_ID);
        $cancelButton->setOnchange($onchange);

        return $cancelButton->toArray();
    }

    $onchange2 = new Onchange();
    $onchange2->addAction(
        [
            "ACTION" => Actions::CREATE,
            "DATA" => array(getSaveEditButton(), getCancelEditButton())
        ]
    );
    $onchange2->addAction(
        [
            "ACTION" => Actions::CALLBACK,
            "DATA" => array(array("JS" => "Grid.editSelected()"))
        ]
    );
    $onchange2->addAction(
        [
            "ACTION" => Actions::HIDE_ALL_EXPECT,
            "DATA" => array(
                array("ID" => DefaultValue::SAVE_BUTTON_ID),
                array("ID" => DefaultValue::CANCEL_BUTTON_ID))
        ]
    );
    $onchange2->addAction(
        [
            "ACTION" => Actions::CALLBACK,
            "DATA" => array(array("JS" => "makeMaskForEditField('ATT_PHONE', '+7 (999) 999 99 99')"))
        ]
    );
		$onchange2->addAction(
				[
						"ACTION" => Actions::CALLBACK,
						"DATA" => array(array("JS" => "getAddressForEditField('ATT_ADDRESS')"))
				]
		);

?>
<?  $APPLICATION->IncludeComponent('bitrix:main.ui.grid', '', [
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
        'SHOW_CHECK_ALL_CHECKBOXES' => true,
        'SHOW_ROW_ACTIONS_MENU'     => true,
        'SHOW_GRID_SETTINGS_MENU'   => true,
        'SHOW_NAVIGATION_PANEL'     => true,
        'SHOW_PAGINATION'           => true,
        'SHOW_SELECTED_COUNTER'     => true,
        'SHOW_TOTAL_COUNTER'        => true,
        'SHOW_PAGESIZE'             => true,
				'TOTAL_ROWS_COUNT'          => $arResult['nav']->getRecordCount(),
        'SHOW_ACTION_PANEL'         => true,
        'ACTION_PANEL'              => [
            'GROUPS' => [
                'TYPE' => [
                    'ITEMS' => [

                        [
                            'ID'       => 'edit',
                            'TYPE'     => 'BUTTON',
                            'TEXT'        => 'Редактировать',
                            'CLASS'        => 'icon edit',
                            'ONCHANGE' => $onchange2->toArray()
                        ],

                    ],
                ]
            ],
        ],
        'ALLOW_COLUMNS_SORT'        => true,
        'ALLOW_COLUMNS_RESIZE'      => true,
        'ALLOW_HORIZONTAL_SCROLL'   => true,
        'ALLOW_SORT'                => true,
        'ALLOW_PIN_HEADER'          => true,
        'AJAX_OPTION_HISTORY'       => 'N'
    ]);?>

	<form method="post" action="/isolation/?PAGE=ADD&TYPE=CONT" enctype="multipart/form-data">
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

		BX.addCustomEvent('BX.Main.Filter:apply', BX.delegate(function (grid_id) {

			filter = {};
			var searchString = BX.Main.filterManager.data[grid_id].search.lastSearchString;
			console.log('searchString', searchString);

			if (searchString) {
				filter['NAME'] = searchString;
				// filter['PROPERTY_ATT_LEGAL_REPRES'] = searchString;
			}

			BX.Main.filterManager.data[grid_id].params.PRESETS[0].FIELDS.map(function (el) {

				console.log('ele', el)

				if (el.NAME === "ATT_DATE_ADD_DATA" && el.VALUES._from && el.VALUES._to) {

					filter['>=PROPERTY_ATT_DATE_ADD_DATA'] = el.VALUES._from;
					filter['<=PROPERTY_ATT_DATE_ADD_DATA'] = el.VALUES._to
				}

				if (el.NAME === "ATT_AREA" && el.VALUE.length) {

					filter['PROPERTY_ATT_AREA'] = []
					el.VALUE.forEach(el => { filter['PROPERTY_ATT_AREA'].push(el.VALUE) })

				}
				if (el.NAME === "ATT_CITY" && el.VALUE.length) {

					filter['PROPERTY_ATT_CITY'] = []
					el.VALUE.forEach(el => { filter['PROPERTY_ATT_CITY'].push(el.VALUE) })

				}
				if (el.NAME === "ATT_GUZ_NAV" && el.VALUE.length) {

					filter['PROPERTY_ATT_GUZ_NAV'] = []
					el.VALUE.forEach(el => { filter['PROPERTY_ATT_GUZ_NAV'].push(el.VALUE) })

				}
				if (el.NAME === "ATT_DATE_PERESECHENIYA") {

					var date = new Date()
					var dateQuarante = date.setDate(date.getDate() + 14);

				if (el.VALUE.VALUE === 'Y') {
					filter['>=PROPERTY_ATT_DATE_PERESECHENIYA'] = 'Y'
				}
				if (el.VALUE.VALUE === 'N') {
					filter['<PROPERTY_ATT_DATE_PERESECHENIYA'] = 'Y'
				}




				}

			});
			console.log('filter', filter);

		}));
	</script>

<?else :
    echo GetMessage('ACCESS_ERROR');
endif;
