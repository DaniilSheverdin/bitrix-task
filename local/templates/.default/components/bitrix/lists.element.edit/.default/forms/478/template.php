<<<<<<< HEAD
<?
$template2Folder = $templateFolder."/forms/".$arResult['IBLOCK']['ID'];

$this->addExternalCss('/bitrix/templates/.default/bootstrap.min.css');
$this->addExternalCss($template2Folder.'/style.css');
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
        'description'   => ""
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
        'description'   => ""
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
        'description'   => ""
    ],
    'is_uvedomlenie' => [
        'title'         => "",
        'name'          => "is_uvedomlenie",
        'id'            => "is_uvedomlenie",
        'type'          => "hidden",
        'show'          => true,
        'value'         => 0,
        'values'        => null,
        'placeholder'   => "",
        'custom'        => "",
        'description'   => ""
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
        'show'          => in_array($field_code, ['otpusk__from', 'otpusk__days', 'prichina_v_svyazi_s', 'rukovoditel_oiv', 'neposredstvennyy_rukovoditel']),
        'value'         => !empty($field_props['VALUES'])
                                  ? current($field_props['VALUES'])['ID']
                                  : ($field_props['DEFAULT_VALUE'] ?? ""),
        'values'        => $field_props['VALUES'] ?? NULL,
        'placeholder'   => "",
        'custom'        => $field['type'] == "custom"?$field['value']:"",
        'description'   => ""
    ];
}
$fields['name']['type']                         = "hidden";
$fields['name']['value']                        = $arResult['IBLOCK']['NAME'];
$fields['zayavlenie_fayl_id']['type']           = "hidden";
$fields['rukovoditel_oiv']['type']              = "user";
$fields['neposredstvennyy_rukovoditel']['type'] = "user";
$fields['otpusk__from']['type']                 = "date";

$fields['prichina']['title']                    = "Укажите подробнее причину";
$fields['prichina']['placeholder']              = "по причине ....";
$SOTRUDNIK = $GLOBALS['userFields']($USER->GetId());

if(isset($_GET['otpusk__from'])){
    $REQ_OTPUSK__FROM = DateTime::createFromFormat('d.m.Y', $_GET['otpusk__from']);
    if($REQ_OTPUSK__FROM && $REQ_OTPUSK__FROM->format('U') > strtotime("+1 day")){
        $fields['otpusk__from']['value'] = $REQ_OTPUSK__FROM->format('d.m.Y');
        $fields['otpusk__from']['type']  = 'readonly';
    }
}
if(isset($_GET['otpusk__days'])){
    $REQ_OTPUSK__DAYS = intVal($_GET['otpusk__days']);
    if($REQ_OTPUSK__DAYS > 0){
        $fields['otpusk__days']['value'] = $REQ_OTPUSK__DAYS;
        $fields['otpusk__days']['type']  = 'readonly';
    }
}
if(isset($_GET['uvedomlenie'])){
    $fields['is_uvedomlenie']['value']      = 1;
    $fields['prichina_v_svyazi_s']['show']  = false;
        
    $fields['uved_descr'] = [
        'title'         => "",
        'name'          => "uved_descr",
        'id'            => "uved_descr",
        'type'          => "textblock",
        'show'          => true,
        'value'         => '',
        'values'        => null,
        'placeholder'   => "",
        'custom'        => "",
        'description'   => '
            <div>Заполните указанные поля</div>
        '
    ];
}
?>
<div id="lists_element_add_form"></div>
<script>
var bpFields = <?=json_encode($fields)?>;
bpFields.prichina.show = function(){
    return this.getValueXmlId('prichina_v_svyazi_s') == "e82962cfdd7c090e480813453653118c" || this.getValueXmlId('prichina_v_svyazi_s') == "97a8321184f8054a8dbe54562de88911"
};
document.addEventListener('DOMContentLoaded', function (event) {
  ReactDOM.render(React.createElement(BPForm, {
      fields:           bpFields,
      formName:         "<?=$arResult["FORM_ID"]?>",
      formAction:       "<?=htmlentities($APPLICATION->GetCurDir())?>?livefeed=y&list_id=<?=htmlentities($arResult['IBLOCK']['ID'])?>&element_id=0",
      formAjax:         "<?=$template2Folder.'/ajax.php'?>",
  }), document.querySelector('#lists_element_add_form'));
});
=======
<?
$template2Folder = $templateFolder."/forms/".$arResult['IBLOCK']['ID'];

$this->addExternalCss('/bitrix/templates/.default/bootstrap.min.css');
$this->addExternalCss($template2Folder.'/style.css');
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
        'description'   => ""
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
        'description'   => ""
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
        'description'   => ""
    ],
    'is_uvedomlenie' => [
        'title'         => "",
        'name'          => "is_uvedomlenie",
        'id'            => "is_uvedomlenie",
        'type'          => "hidden",
        'show'          => true,
        'value'         => 0,
        'values'        => null,
        'placeholder'   => "",
        'custom'        => "",
        'description'   => ""
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
        'show'          => in_array($field_code, ['otpusk__from', 'otpusk__days', 'prichina_v_svyazi_s', 'rukovoditel_oiv', 'neposredstvennyy_rukovoditel']),
        'value'         => !empty($field_props['VALUES'])
                                  ? current($field_props['VALUES'])['ID']
                                  : ($field_props['DEFAULT_VALUE'] ?? ""),
        'values'        => $field_props['VALUES'] ?? NULL,
        'placeholder'   => "",
        'custom'        => $field['type'] == "custom"?$field['value']:"",
        'description'   => ""
    ];
}
$fields['name']['type']                         = "hidden";
$fields['name']['value']                        = $arResult['IBLOCK']['NAME'];
$fields['zayavlenie_fayl_id']['type']           = "hidden";
$fields['rukovoditel_oiv']['type']              = "user";
$fields['neposredstvennyy_rukovoditel']['type'] = "user";
$fields['otpusk__from']['type']                 = "date";

$fields['prichina']['title']                    = "Укажите подробнее причину";
$fields['prichina']['placeholder']              = "по причине ....";
$SOTRUDNIK = $GLOBALS['userFields']($USER->GetId());

if(isset($_GET['otpusk__from'])){
    $REQ_OTPUSK__FROM = DateTime::createFromFormat('d.m.Y', $_GET['otpusk__from']);
    if($REQ_OTPUSK__FROM && $REQ_OTPUSK__FROM->format('U') > strtotime("+1 day")){
        $fields['otpusk__from']['value'] = $REQ_OTPUSK__FROM->format('d.m.Y');
        $fields['otpusk__from']['type']  = 'readonly';
    }
}
if(isset($_GET['otpusk__days'])){
    $REQ_OTPUSK__DAYS = intVal($_GET['otpusk__days']);
    if($REQ_OTPUSK__DAYS > 0){
        $fields['otpusk__days']['value'] = $REQ_OTPUSK__DAYS;
        $fields['otpusk__days']['type']  = 'readonly';
    }
}
if(isset($_GET['uvedomlenie'])){
    $fields['is_uvedomlenie']['value']      = 1;
    $fields['prichina_v_svyazi_s']['show']  = false;
        
    $fields['uved_descr'] = [
        'title'         => "",
        'name'          => "uved_descr",
        'id'            => "uved_descr",
        'type'          => "textblock",
        'show'          => true,
        'value'         => '',
        'values'        => null,
        'placeholder'   => "",
        'custom'        => "",
        'description'   => '
            <div>Заполните указанные поля</div>
        '
    ];
}
?>
<div id="lists_element_add_form"></div>
<script>
var bpFields = <?=json_encode($fields)?>;
bpFields.prichina.show = function(){
    return this.getValueXmlId('prichina_v_svyazi_s') == "e82962cfdd7c090e480813453653118c" || this.getValueXmlId('prichina_v_svyazi_s') == "97a8321184f8054a8dbe54562de88911"
};
document.addEventListener('DOMContentLoaded', function (event) {
  ReactDOM.render(React.createElement(BPForm, {
      fields:           bpFields,
      formName:         "<?=$arResult["FORM_ID"]?>",
      formAction:       "<?=htmlentities($APPLICATION->GetCurDir())?>?livefeed=y&list_id=<?=htmlentities($arResult['IBLOCK']['ID'])?>&element_id=0",
      formAjax:         "<?=$template2Folder.'/ajax.php'?>",
  }), document.querySelector('#lists_element_add_form'));
});
>>>>>>> e0a0eba79 (init)
</script>