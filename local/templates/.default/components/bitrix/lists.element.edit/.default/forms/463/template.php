<<<<<<< HEAD
<?

$template2Folder = $templateFolder."/forms/".$arResult['IBLOCK']['ID'];

$this->addExternalCss('/bitrix/templates/.default/bootstrap.min.css');
$this->addExternalJS('/bitrix/templates/.default/jquery.min.js');
$this->addExternalJS('/bitrix/templates/.default/bootstrap.min.js');
if(defined('DEV_SERVER')){
    $this->addExternalJS('https://unpkg.com/react@16/umd/react.development.js');
    $this->addExternalJS('https://unpkg.com/react-dom@16/umd/react-dom.development.js');
}else{
    $this->addExternalJS($templateFolder."/react.production.min.js");
    $this->addExternalJS($templateFolder."/react-dom.production.min.js");
}
$this->addExternalJS($templateFolder."/forms/common/form.js");

$fields = [
    'sessid' => [
        'title'         => "",
        'name'          => "sessid",
        'id'            => "sessid",
        'type'          => "hidden",
        'show'          => true,
        'value'         => bitrix_sessid(),
        'values'        => null,
        'placeholder'   => "",
        'custom'        => "",
        'description'   => "",
    ],
    'iblock_id' => [
        'title'         => "",
        'name'          => "IBLOCK_ID",
        'id'            => "iblock_id",
        'type'          => "hidden",
        'show'          => true,
        'value'         => $arResult['IBLOCK']['ID'],
        'values'        => null,
        'placeholder'   => "",
        'custom'        => "",
        'description'   => "",
    ],
    'save' => [
        'title'         => "",
        'name'          => "save",
        'id'            => "save",
        'type'          => "hidden",
        'show'          => true,
        'value'         => "Сохранить",
        'values'        => null,
        'placeholder'   => "",
        'custom'        => "",
        'description'   => "",
    ]
];



foreach($arTabs[0]['fields'] as $field){
    $field_props= $arResult['FIELDS'][preg_replace("!\[.*?\]!","",$field['id'])] ?? [];
    $field_code = mb_strtolower($field_props['CODE'] ?? $field['id']);

    $fields[$field_code] =[
        'title'         => $field['name'],
        'name'          => $field['id'],
        'id'            => $field_code,
        'type'          => $field_props['PROPERTY_TYPE'] == "L"?"list":"text",
        'show'          => !in_array($field_code, ['spravka_o_sredney_zp']),
        'value'         => !empty($field_props['VALUES'])
                                  ? current($field_props['VALUES'])['ID']
                                  : "",
        'values'        => $field_props['VALUES'] ?? NULL,
        'placeholder'   => "",
        'custom'        => $field['type'] == "custom"?$field['value']:"",
        'description'   => "",
    ];
}
$fields['name']['type']                                 = "hidden";
$fields['name']['value']                                = $arResult['IBLOCK']['NAME'];
$fields['zayavlenie_fayl_id']['type']                   = "hidden";
$fields['spravka_o_sredney_zp']['type']                 = "file";
$fields['spravka_o_sredney_zp']['name']                 = $fields['spravka_o_sredney_zp']['name']."[n0][VALUE]";

?>
<div id="lists_element_add_form"></div>
<script>
var bpFields = <?=json_encode($fields)?>;
bpFields.spravka_o_sredney_zp.show = function(){
    return this.getValueXmlId('spravka') == "67822e15e4d0b1fc251801406dbc3770";
};

document.addEventListener('DOMContentLoaded', function (event) {
  ReactDOM.render(React.createElement(BPForm, {
      fields:       bpFields,
      formName:     "<?=$arResult["FORM_ID"]?>",
      formAction:   "<?=htmlentities($APPLICATION->GetCurDir())?>?livefeed=y&list_id=<?=htmlentities($arResult['IBLOCK']['ID'])?>&element_id=0",
      formAjax:     "<?=$template2Folder.'/ajax.php'?>",
  }), document.querySelector('#lists_element_add_form'));
});
=======
<?

$template2Folder = $templateFolder."/forms/".$arResult['IBLOCK']['ID'];

$this->addExternalCss('/bitrix/templates/.default/bootstrap.min.css');
$this->addExternalJS('/bitrix/templates/.default/jquery.min.js');
$this->addExternalJS('/bitrix/templates/.default/bootstrap.min.js');
if(defined('DEV_SERVER')){
    $this->addExternalJS('https://unpkg.com/react@16/umd/react.development.js');
    $this->addExternalJS('https://unpkg.com/react-dom@16/umd/react-dom.development.js');
}else{
    $this->addExternalJS($templateFolder."/react.production.min.js");
    $this->addExternalJS($templateFolder."/react-dom.production.min.js");
}
$this->addExternalJS($templateFolder."/forms/common/form.js");

$fields = [
    'sessid' => [
        'title'         => "",
        'name'          => "sessid",
        'id'            => "sessid",
        'type'          => "hidden",
        'show'          => true,
        'value'         => bitrix_sessid(),
        'values'        => null,
        'placeholder'   => "",
        'custom'        => "",
        'description'   => "",
    ],
    'iblock_id' => [
        'title'         => "",
        'name'          => "IBLOCK_ID",
        'id'            => "iblock_id",
        'type'          => "hidden",
        'show'          => true,
        'value'         => $arResult['IBLOCK']['ID'],
        'values'        => null,
        'placeholder'   => "",
        'custom'        => "",
        'description'   => "",
    ],
    'save' => [
        'title'         => "",
        'name'          => "save",
        'id'            => "save",
        'type'          => "hidden",
        'show'          => true,
        'value'         => "Сохранить",
        'values'        => null,
        'placeholder'   => "",
        'custom'        => "",
        'description'   => "",
    ]
];



foreach($arTabs[0]['fields'] as $field){
    $field_props= $arResult['FIELDS'][preg_replace("!\[.*?\]!","",$field['id'])] ?? [];
    $field_code = mb_strtolower($field_props['CODE'] ?? $field['id']);

    $fields[$field_code] =[
        'title'         => $field['name'],
        'name'          => $field['id'],
        'id'            => $field_code,
        'type'          => $field_props['PROPERTY_TYPE'] == "L"?"list":"text",
        'show'          => !in_array($field_code, ['spravka_o_sredney_zp']),
        'value'         => !empty($field_props['VALUES'])
                                  ? current($field_props['VALUES'])['ID']
                                  : "",
        'values'        => $field_props['VALUES'] ?? NULL,
        'placeholder'   => "",
        'custom'        => $field['type'] == "custom"?$field['value']:"",
        'description'   => "",
    ];
}
$fields['name']['type']                                 = "hidden";
$fields['name']['value']                                = $arResult['IBLOCK']['NAME'];
$fields['zayavlenie_fayl_id']['type']                   = "hidden";
$fields['spravka_o_sredney_zp']['type']                 = "file";
$fields['spravka_o_sredney_zp']['name']                 = $fields['spravka_o_sredney_zp']['name']."[n0][VALUE]";

?>
<div id="lists_element_add_form"></div>
<script>
var bpFields = <?=json_encode($fields)?>;
bpFields.spravka_o_sredney_zp.show = function(){
    return this.getValueXmlId('spravka') == "67822e15e4d0b1fc251801406dbc3770";
};

document.addEventListener('DOMContentLoaded', function (event) {
  ReactDOM.render(React.createElement(BPForm, {
      fields:       bpFields,
      formName:     "<?=$arResult["FORM_ID"]?>",
      formAction:   "<?=htmlentities($APPLICATION->GetCurDir())?>?livefeed=y&list_id=<?=htmlentities($arResult['IBLOCK']['ID'])?>&element_id=0",
      formAjax:     "<?=$template2Folder.'/ajax.php'?>",
  }), document.querySelector('#lists_element_add_form'));
});
>>>>>>> e0a0eba79 (init)
</script>