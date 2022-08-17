<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<?
use Bitrix\Main\Page\Asset;

$APPLICATION->SetTitle("Консультация врача");
Asset::getInstance()->addCss('/bitrix/templates/.default/bootstrap.min.css');
\Bitrix\Main\UI\Extension::load("ui.dialogs.messagebox");
\Bitrix\Main\UI\Extension::load("ui.forms");
?>

<div class="d-flex align-items-center justify-content-between pr-3">
    <? $APPLICATION->IncludeComponent(
        'bitrix:main.ui.filter',
        '',
        [
            'FILTER_ID' => $arResult['FILTER_ID'],
            'GRID_ID' => $arResult['GRID_ID'],
            'FILTER' => $arResult['FILTER'],
            'ENABLE_LIVE_SEARCH' => true,
            'ENABLE_LABEL' => true
        ]
    ); ?>

    <div>
        <a href="/local/components/citto/doctor_consultation/Инструкция.docx" download target="_blank" class="ui-btn ui-btn-secondary">Скачать инструкцию</a>
        <a href="/bizproc/processes/673/element/0/0/?list_section_id=" target="_blank" class="ui-btn ui-btn-success">Добавить запись</a>
    </div>
</div>

<?
if ($arResult['ROLE'] == 'ADMIN') {
    $arActionPanelItems = [
        [
            'ID'    => 'set-type',
            'TYPE' => 'DROPDOWN',
            'ITEMS' => [
                ['VALUE' => '', 'NAME' => '- Выбрать -'],
                ['VALUE' => 'IN_WORK', 'NAME' => 'Принята'],
                ['VALUE' => 'REJECTED', 'NAME' => 'Отклонена'],
                ['VALUE' => 'OK', 'NAME' => 'Консультация проведена'],
                ['VALUE' => 'WITHOUT_CONSULTATION', 'NAME' => 'Консультация не проведена'],
                ['VALUE' => 'export', 'NAME' => 'Экспорт'],
            ],
        ],
        [
            'ID' => "adduserfields",
            'TYPE' => \Bitrix\Main\Grid\Panel\Types::BUTTON,
            'CLASS' => "apply",
            'TEXT' => "Применить",
            'ONCHANGE' => [
                [
                    'ACTION' => Bitrix\Main\Grid\Panel\Actions::CALLBACK,
                    'DATA' => array(
                        ['JS' => "getExport()"]
                    )
                ]
            ]
        ],
    ];
} else {
    $arActionPanelItems = [];
}

$APPLICATION->IncludeComponent(
    "bitrix:main.ui.grid",
    "",
    array(
        "GRID_ID" => $arResult['GRID_ID'],
        "COLUMNS" => $arResult['COLUMNS'],
        "ROWS" => $arResult["RECORDS"]['ITEMS'],
        "NAV_STRING" => $arResult["NAV_STRING"],
        "TOTAL_ROWS_COUNT" => $arResult["RECORDS"]['COUNT'],
        "PAGE_SIZES" => $arResult["GRID_PAGE_SIZES"],
        "AJAX_MODE" => "N",
        "AJAX_ID" => CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
        "ENABLE_NEXT_PAGE" => $arResult["GRID_ENABLE_NEXT_PAGE"],
        'ACTION_PANEL' => [
            'GROUPS' => [
                'TYPE' => [
                    'ITEMS' => $arActionPanelItems
                ]
            ],
        ],
        "SHOW_ACTION_PANEL" => true,
        "SHOW_CHECK_ALL_CHECKBOXES" => true,
        "SHOW_ROW_CHECKBOXES" => true,
        "SHOW_ROW_ACTIONS_MENU" => true,
        "SHOW_GRID_SETTINGS_MENU" => true,
        "SHOW_NAVIGATION_PANEL" => true,
        "SHOW_PAGINATION" => true,
        "SHOW_SELECTED_COUNTER" => true,
        "SHOW_TOTAL_COUNTER" => true,
        "SHOW_PAGESIZE" => true,
        "ALLOW_COLUMNS_SORT" => true,
        "ALLOW_COLUMNS_RESIZE" => true,
        "ALLOW_HORIZONTAL_SCROLL" => true,
        "ALLOW_SORT" => true,
        "ALLOW_PIN_HEADER" => true,
        "AJAX_OPTION_JUMP" => "N",
        "AJAX_OPTION_HISTORY" => "N"
    ),
    $component,
    array("HIDE_ICONS" => "Y")
);
?>

<script>
    var SELECT_DATE_IDS = <?= json_encode($arResult["RECORDS"]['SELECT_DATE_IDS']) ?>
</script>
