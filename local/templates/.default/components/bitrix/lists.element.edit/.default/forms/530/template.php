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
        'show'          => !in_array($field_code, ['novye_dannye']),
        'value'         => !empty($field_props['VALUES'])
                                  ? current($field_props['VALUES'])['ID']
                                  : ($field_props['DEFAULT_VALUE'] ?? ""),
        'values'        => $field_props['VALUES'] ?? NULL,
        'placeholder'   => "",
        'custom'        => $field['type'] == "custom"?$field['value']:"",
        'description'   => "",
    ];
}
$fields['name']['type']                     = "hidden";
$fields['name']['value']                    = $arResult['IBLOCK']['NAME'];
$fields['zayavlenie_fayl_id']['type']       = "hidden";
$fields['novye_dannye']['type']             = "textarea";



$SOTRUDNIK = $GLOBALS['userFields']($USER->GetId());
$newField = function($id, $title, $value="", $type="text") use(&$fields){
    $fields[$id] = [
        'title'         => $title,
        'name'          => $id,
        'id'            => $id,
        'type'          => $type,
        'show'          => true,
        'value'         => $value,
        'values'        => null,
        'placeholder'   => "",
        'custom'        => "",
        'description'   => "",
    ];
};
$newField('familia_old',    'Фамилия (старая)', $SOTRUDNIK['LAST_NAME']);
$newField('familia_new',    'Фамилия (новая)');
$newField('imya_old',       'Имя (старое)', $SOTRUDNIK['NAME']);
$newField('imya_new',       'Имя (новое)');
$newField('otchestvo_old',  'Отчество (старое)', $SOTRUDNIK['SECOND_NAME']);
$newField('otchestvo_new',  'Отчество (новое)');

$newField('passport_seria', 'Серия');
$newField('passport_nomer', 'Номер');
$newField('passport_kemv',  'Кем выдан');
$newField('passport_datav', 'Дата выдачи', '', 'date');
$newField('passport_kodp',  'Код подразделения');

$newField('propiska',       'Новый адрес по прописке');

$newField('adres',          'Новый адрес места проживания');

$newField('telefon',        'Новый номер телефона');

$newField('obrasovanie_uroven',         'Уровень образования');
$newField('obrasovanie_spesialnost',    'Специальность по диплому');
$newField('obrasovanie_uchrejd',        'Образовательное учреждение');
$newField('obrasovanie_okonchanie',     'Год окончания обучения');
?>
<div id="lists_element_add_form"></div>
<script>
var bpFields = <?=json_encode($fields)?>;
bpFields.familia_old.show =
bpFields.familia_new.show =
    function(){
        return this.getValueXmlId('v_kakie_dannye_sleduet_vnesti_izmeneniya') == "90a26166e2c2ef42151b48a5a8aa5b0a";
    };

bpFields.imya_old.show =
bpFields.imya_new.show =
    function(){
        return this.getValueXmlId('v_kakie_dannye_sleduet_vnesti_izmeneniya') == "57018ea341b5ee6cca18c88da799105b";
    };

bpFields.otchestvo_old.show =
bpFields.otchestvo_new.show =
    function(){
        return this.getValueXmlId('v_kakie_dannye_sleduet_vnesti_izmeneniya') == "469a85d467d11129ad6b86e4089e06df";
    };

bpFields.passport_seria.show =
bpFields.passport_nomer.show =
bpFields.passport_kemv.show =
bpFields.passport_datav.show =
bpFields.passport_kodp.show =
        function(){
            return this.getValueXmlId('v_kakie_dannye_sleduet_vnesti_izmeneniya') == "9f75d0c97d65010bf8f0384958d9d53a";
        };
bpFields.propiska.show =
        function(){
            return this.getValueXmlId('v_kakie_dannye_sleduet_vnesti_izmeneniya') == "3e23056e20d5033b3aff940b2bd86460";
        }
bpFields.adres.show =
        function(){
            return this.getValueXmlId('v_kakie_dannye_sleduet_vnesti_izmeneniya') == "f76a2986d241112e0ba863185e6db31b";
        }
bpFields.telefon.show =
        function(){
            return this.getValueXmlId('v_kakie_dannye_sleduet_vnesti_izmeneniya') == "3e0338766544a353cdc17e6fbe5bfd94";
        }
        
bpFields.obrasovanie_uroven.show =
bpFields.obrasovanie_spesialnost.show =
bpFields.obrasovanie_uchrejd.show =
bpFields.obrasovanie_okonchanie.show =
        function(){
            return this.getValueXmlId('v_kakie_dannye_sleduet_vnesti_izmeneniya') == "8586b0cb84811ef9097e27ba3db7b677";
        }
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
        'show'          => !in_array($field_code, ['novye_dannye']),
        'value'         => !empty($field_props['VALUES'])
                                  ? current($field_props['VALUES'])['ID']
                                  : ($field_props['DEFAULT_VALUE'] ?? ""),
        'values'        => $field_props['VALUES'] ?? NULL,
        'placeholder'   => "",
        'custom'        => $field['type'] == "custom"?$field['value']:"",
        'description'   => "",
    ];
}
$fields['name']['type']                     = "hidden";
$fields['name']['value']                    = $arResult['IBLOCK']['NAME'];
$fields['zayavlenie_fayl_id']['type']       = "hidden";
$fields['novye_dannye']['type']             = "textarea";



$SOTRUDNIK = $GLOBALS['userFields']($USER->GetId());
$newField = function($id, $title, $value="", $type="text") use(&$fields){
    $fields[$id] = [
        'title'         => $title,
        'name'          => $id,
        'id'            => $id,
        'type'          => $type,
        'show'          => true,
        'value'         => $value,
        'values'        => null,
        'placeholder'   => "",
        'custom'        => "",
        'description'   => "",
    ];
};
$newField('familia_old',    'Фамилия (старая)', $SOTRUDNIK['LAST_NAME']);
$newField('familia_new',    'Фамилия (новая)');
$newField('imya_old',       'Имя (старое)', $SOTRUDNIK['NAME']);
$newField('imya_new',       'Имя (новое)');
$newField('otchestvo_old',  'Отчество (старое)', $SOTRUDNIK['SECOND_NAME']);
$newField('otchestvo_new',  'Отчество (новое)');

$newField('passport_seria', 'Серия');
$newField('passport_nomer', 'Номер');
$newField('passport_kemv',  'Кем выдан');
$newField('passport_datav', 'Дата выдачи', '', 'date');
$newField('passport_kodp',  'Код подразделения');

$newField('propiska',       'Новый адрес по прописке');

$newField('adres',          'Новый адрес места проживания');

$newField('telefon',        'Новый номер телефона');

$newField('obrasovanie_uroven',         'Уровень образования');
$newField('obrasovanie_spesialnost',    'Специальность по диплому');
$newField('obrasovanie_uchrejd',        'Образовательное учреждение');
$newField('obrasovanie_okonchanie',     'Год окончания обучения');
?>
<div id="lists_element_add_form"></div>
<script>
var bpFields = <?=json_encode($fields)?>;
bpFields.familia_old.show =
bpFields.familia_new.show =
    function(){
        return this.getValueXmlId('v_kakie_dannye_sleduet_vnesti_izmeneniya') == "90a26166e2c2ef42151b48a5a8aa5b0a";
    };

bpFields.imya_old.show =
bpFields.imya_new.show =
    function(){
        return this.getValueXmlId('v_kakie_dannye_sleduet_vnesti_izmeneniya') == "57018ea341b5ee6cca18c88da799105b";
    };

bpFields.otchestvo_old.show =
bpFields.otchestvo_new.show =
    function(){
        return this.getValueXmlId('v_kakie_dannye_sleduet_vnesti_izmeneniya') == "469a85d467d11129ad6b86e4089e06df";
    };

bpFields.passport_seria.show =
bpFields.passport_nomer.show =
bpFields.passport_kemv.show =
bpFields.passport_datav.show =
bpFields.passport_kodp.show =
        function(){
            return this.getValueXmlId('v_kakie_dannye_sleduet_vnesti_izmeneniya') == "9f75d0c97d65010bf8f0384958d9d53a";
        };
bpFields.propiska.show =
        function(){
            return this.getValueXmlId('v_kakie_dannye_sleduet_vnesti_izmeneniya') == "3e23056e20d5033b3aff940b2bd86460";
        }
bpFields.adres.show =
        function(){
            return this.getValueXmlId('v_kakie_dannye_sleduet_vnesti_izmeneniya') == "f76a2986d241112e0ba863185e6db31b";
        }
bpFields.telefon.show =
        function(){
            return this.getValueXmlId('v_kakie_dannye_sleduet_vnesti_izmeneniya') == "3e0338766544a353cdc17e6fbe5bfd94";
        }
        
bpFields.obrasovanie_uroven.show =
bpFields.obrasovanie_spesialnost.show =
bpFields.obrasovanie_uchrejd.show =
bpFields.obrasovanie_okonchanie.show =
        function(){
            return this.getValueXmlId('v_kakie_dannye_sleduet_vnesti_izmeneniya') == "8586b0cb84811ef9097e27ba3db7b677";
        }
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