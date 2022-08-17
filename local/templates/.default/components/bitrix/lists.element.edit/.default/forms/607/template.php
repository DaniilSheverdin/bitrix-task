<?php

define('ED_VYPLATU', "12fa3dc93215b0600c5882223cb12fe8");
$strTemplate2Folder = $templateFolder . "/forms/" . $arResult['IBLOCK']['ID'];

$this->addExternalCss('/bitrix/templates/.default/bootstrap.min.css');
$this->addExternalCss($strTemplate2Folder . '/style.css');
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

$arFields = [
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
        'description' => "",
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
        'description' => "",
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
        'description' => "",
    ]
];

foreach ($arTabs[0]['fields'] as $arListfield) {
    $arField_props = $arResult['FIELDS'][preg_replace("!\[.*?\]!", "", $arListfield['id'])] ?? [];
    $arField_code = mb_strtolower($arField_props['CODE'] ?? $arListfield['id']);

    $arFields[$arField_code] = [
        'title' => $arListfield['name'],
        'name' => $arListfield['id'],
        'id' => $arField_code,
        'type' => $arField_props['PROPERTY_TYPE'] == "L" ? "list" : "text",
        'show' => in_array(
            $arField_code,
            [
                'fio',
                'dolzhnost',
                'organ',
                'tip_dolzhnosti',
                'proshu_predostavit',
                'data_prikaza',
                'nomer_prikaza',
                'data_nachala_otpuska',
                'year',
            ]
        ),
        'value' => !empty($arField_props['VALUES'])
            ? current($arField_props['VALUES'])['ID']
            : ($arField_props['DEFAULT_VALUE'] ?? ""),
        'values' => $arField_props['VALUES'] ?? null,
        'placeholder' => "",
        'custom' => $arListfield['type'] == "custom" ? $arListfield['value'] : "",
        'description' => "",
    ];
}
$arFields['name']['type'] = "hidden";
$arFields['name']['value'] = $arResult['IBLOCK']['NAME'];
$arFields['zayavlenie_fayl_id']['type'] = "hidden";
$arFields['fio']['type'] = "readonly";
$arFields['dolzhnost']['type'] = "readonly";
$arFields['tip_dolzhnosti']['type'] = "readonly";
$arFields['organ']['type'] = "readonly";
$arFields['data_prikaza']['type'] = "date";
$arFields['data_nachala_otpuska']['type'] = "date";

$arSOTRUDNIK = $GLOBALS['userFields']($USER->GetId());
$arFields['fio']['value'] = $arSOTRUDNIK['FIO'];
$arFields['dolzhnost']['value'] = $arSOTRUDNIK['WORK_POSITION'];
$arFields['organ']['value'] = $arSOTRUDNIK['DEPARTMENT'];
?>
<div id="lists_element_add_form"></div>
<script>
    $('body').ready(function () {
        function hideOrShowYear() {
            let sHelp = $('select[data-id="proshu_predostavit"] option:selected').text()
            let obYearField = $('select[data-id="year"]').parent()
            if (sHelp == 'материальную помощь') {
                obYearField.show()
            } else {
                obYearField.hide()
            }
        }

        hideOrShowYear()

        $('#lists_element_add_form').on('change', $('select[data-id="proshu_predostavit"]'), function(){
            hideOrShowYear()
        })
    })

    var bpFields = <?=json_encode($arFields)?>;
    bpFields.data_prikaza.show =
        bpFields.nomer_prikaza.show =
            bpFields.data_nachala_otpuska.show = function () {
                return this.getValueXmlId('proshu_predostavit') == "<?=ED_VYPLATU?>";
            };
    document.addEventListener('DOMContentLoaded', function (event) {
        ReactDOM.render(React.createElement(BPForm, {
            fields: bpFields,
            formName: "<?=$arResult["FORM_ID"]?>",
            formAction: "<?=htmlentities($APPLICATION->GetCurDir())?>?livefeed=y&list_id=<?=htmlentities($arResult['IBLOCK']['ID'])?>&element_id=0",
            formAjax: "<?=$strTemplate2Folder . '/ajax.php'?>",
        }), document.querySelector('#lists_element_add_form'));
    });
</script>