<?php

define('PRICHINA_SEM', "63673b34acdd7de2e947bec93b4ed634");
$template2Folder = $templateFolder . "/forms/" . $arResult['IBLOCK']['ID'];

$this->addExternalCss('/bitrix/templates/.default/bootstrap.min.css');
$this->addExternalCss($template2Folder . '/style.css');
$this->addExternalJS('/bitrix/templates/.default/jquery.min.js');
$this->addExternalJS('/bitrix/templates/.default/bootstrap.min.js');
if (defined('DEV_SERVER')) {
    $this->addExternalJS('https://unpkg.com/react@16/umd/react.development.js');
    $this->addExternalJS('https://unpkg.com/react-dom@16/umd/react-dom.development.js');
} else {
    $this->addExternalJS($templateFolder . "/react.production.min.js");
    $this->addExternalJS($templateFolder . "/react-dom.production.min.js");
}
$this->addExternalJS($templateFolder . "/forms/common/form.js");

$fields = [
    'sessid' => [
        'title' => "",
        'name' => "sessid",
        'id' => "sessid",
        'type' => "hidden",
        'show' => true,
        'value' => bitrix_sessid(),
        'values' => null,
        'placeholder' => "",
        'custom' => "",
        'description' => ""
    ],
    'iblock_id' => [
        'title' => "",
        'name' => "IBLOCK_ID",
        'id' => "iblock_id",
        'type' => "hidden",
        'show' => true,
        'value' => $arResult['IBLOCK']['ID'],
        'values' => null,
        'placeholder' => "",
        'custom' => "",
        'description' => ""
    ],
    'save' => [
        'title' => "",
        'name' => "save",
        'id' => "save",
        'type' => "hidden",
        'show' => true,
        'value' => "Сохранить",
        'values' => null,
        'placeholder' => "",
        'custom' => "",
        'description' => ""
    ],
    'is_uvedomlenie' => [
        'title' => "",
        'name' => "is_uvedomlenie",
        'id' => "is_uvedomlenie",
        'type' => "hidden",
        'show' => true,
        'value' => 0,
        'values' => null,
        'placeholder' => "",
        'custom' => "",
        'description' => ""
    ]
];
if (!isset($_GET['otpusk__from'])) {
    $vacations = $GLOBALS['getUserVacations']($GLOBALS['USER']->GetId(), true);
    if ($vacations) {
        $vacations_values = [];
        foreach ($vacations as $vacationIndx => $vacation) {
            $vacationId = $vacation['FROM']->format('d_m_Y__' . $vacation['DAYS']);
            $vacations_values[$vacationId] = [
                'SORT' => $vacationIndx,
                'XML_ID' => $vacationId,
                'ID' => $vacationId,
                'VALUE' => "Дата начала: " . $vacation['FROM']->format('d.m.Y') . ". Кол. дней: " . $vacation['DAYS']
            ];
        }
        $vacations_values['inoe'] = [
            'SORT' => count($vacations_values),
            'XML_ID' => "inoe",
            'ID' => "inoe",
            'VALUE' => "Указать вручную"
        ];

        $fields['otpusk_calendar'] = [
            'title' => "Выберите отпуск который следует перенести",
            'name' => "otpusk_calendar",
            'id' => "otpusk_calendar",
            'type' => "list",
            'show' => true,
            'value' => current($vacations_values)['ID'],
            'values' => $vacations_values,
            'placeholder' => "",
            'custom' => "",
            'description' => ''
        ];
    }
}
foreach ($arTabs[0]['fields'] as $field) {
    $field_props = $arResult['FIELDS'][preg_replace("!\[.*?\]!", "", $field['id'])] ?? [];
    $field_code = mb_strtolower($field_props['CODE'] ?? $field['id']);

    $fields[$field_code] = [
        'title' => $field['name'],
        'name' => $field['id'],
        'id' => $field_code,
        'type' => $field_props['PROPERTY_TYPE'] == "L" ? "list" : "text",
        'show' => in_array($field_code, ['otpusk__from', 'otpusk__days']),
        'value' => !empty($field_props['VALUES'])
            ? current($field_props['VALUES'])['ID']
            : ($field_props['DEFAULT_VALUE'] ?? ""),
        'values' => $field_props['VALUES'] ?? null,
        'placeholder' => "",
        'custom' => $field['type'] == "custom" ? $field['value'] : "",
        'description' => ""
    ];
}

$fields['name']['type'] = "hidden";
$fields['name']['value'] = $arResult['IBLOCK']['NAME'];
$fields['otpusk__from']['type'] = "date";

$fields['prichina']['title'] = "Укажите причину";
$fields['prichina']['value'] = "по семейным обстоятельствам";
$fields['prichina']['type'] = 'readonly';

$fields['obstoyatelstva']['title'] = "Краткое описание обстоятельств для переноса отпуска";
$fields['obstoyatelstva']['type'] = 'text';


$fields['massiv_otpuskov']['type'] = "hidden";

$fields['osnovanie_perenosa_fayl']['type'] = "file";
$fields['osnovanie_perenosa_fayl']['show'] = true;
$fields['osnovanie_perenosa_fayl']['name'] .= "[n0][VALUE]";

if (isset($_GET['otpusk__from'])) {
    $REQ_OTPUSK__FROM = DateTime::createFromFormat('d.m.Y', $_GET['otpusk__from']);
    if ($REQ_OTPUSK__FROM && $REQ_OTPUSK__FROM->format('U') > strtotime("+1 day")) {
        $fields['otpusk__from']['value'] = $REQ_OTPUSK__FROM->format('d.m.Y');
        $fields['otpusk__from']['type'] = 'readonly';
    }
}
if (isset($_GET['otpusk__days'])) {
    $REQ_OTPUSK__DAYS = intVal($_GET['otpusk__days']);
    if ($REQ_OTPUSK__DAYS > 0) {
        $fields['otpusk__days']['value'] = $REQ_OTPUSK__DAYS;
        $fields['otpusk__days']['type'] = 'readonly';
    }
}
if (isset($_GET['uved'])) {
    $fields['uvedomlenie']['type'] = "hidden";
    $fields['uvedomlenie']['value'] = $_GET['uved'];
}
?>
<div id="lists_element_add_form"></div>
<script>
    $(document).ready(function () {
        var sNewString = '<div class = "row vacation my-2"><div class="col-md-5"><input class="date form-control" type="date" name="date_' + Date.now() + '"></div><div class="col-md-5"><input class="days form-control" type="number" placeholder="дни" name="days_' + Date.now() + '"></div><a class="delete_vacation col-md-2" href="javascript: void(0);" style="line-height: 35px;">удалить</a></div>';

        function addDivForVacations() {
            $('#jq_massiv').detach();
            $('input[data-id="obstoyatelstva"]').parent().after('<div id="jq_massiv" class="mb-3"><span>Дата и дни переноса отпуска</span>' + sNewString + '<a href="javascript: void(0);" id="add_vacation">Добавить ещё</a></div>');
        }

        addDivForVacations();

        $('body').on('change', '[data-id="otpusk_calendar"]', function () {
            addDivForVacations();
        });

        $('body').on('click', '.delete_vacation', function () {
            if ($('.vacation').length > 1) {
                $(this).parent().detach();
            }
        });

        $('body').on('click', '#add_vacation', function () {
            $(this).before(sNewString);
        });

        $('body').on('change', 'input', function (e) {
            var obVacations = $('#jq_massiv .vacation');
            var arVacations = {};
            obVacations.each(function (index, value) {
                let date = $(value).find('.date').val();
                let days = $(value).find('.days').val();
                if (date && days) {
                    arVacations[date] = days;
                }
            });
            var sJsonVacations = JSON.stringify(arVacations);
            $('[data-id="massiv_otpuskov"]').val(sJsonVacations);
        });
    });

    var bpFields = <?=json_encode($fields)?>;
    bpFields.prichina.show = function () {
        return true;
    };

    bpFields.obstoyatelstva.show = function () {
        return true;
    };

    bpFields.massiv_otpuskov.setval = function () {
        return true;
    }

    <?if(isset($fields['otpusk_calendar'])):?>
    bpFields.otpusk__days.show =
        bpFields.otpusk__from.show = function () {
            return this.getValueXmlId('otpusk_calendar') == "inoe";
        };
    <?endif;?>
    document.addEventListener('DOMContentLoaded', function (event) {
        ReactDOM.render(React.createElement(BPForm, {
            fields: bpFields,
            formName: "<?=$arResult["FORM_ID"]?>",
            formAction: "<?=htmlentities($APPLICATION->GetCurDir())?>?livefeed=y&list_id=<?=htmlentities($arResult['IBLOCK']['ID'])?>&element_id=0",
            formAjax: "<?=$template2Folder . '/ajax.php'?>",
            submitDataType: 'post',
        }), document.querySelector('#lists_element_add_form'));
    });
</script>