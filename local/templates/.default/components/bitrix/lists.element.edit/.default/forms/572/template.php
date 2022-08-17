<<<<<<< HEAD
<?
//todo - очистка полей при выборе по условиям
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
        'show'          => in_array($field_code, [
                                    'vedomstvo_podayushchee_zayavku',
                                    'sotrudnik_vedomstva',
                                    'rukovoditel',
                                    'vybor_zdaniya',
                                    'spisok_lits',
                                    'spisok_lits_fayl',
                                    'data_poseshcheniya',
                                    'kabinet',
                                    'tsel_poseshcheniya',
                                    'neobkhodim_li_dostup_na_parkovku',
                                    'avto',
                                    'zayavlenie_fayl_id',
                                    'kontaktnyy_telefon',
                            ]),
        'value'         => !empty($field_props['VALUES'])
                                  ? current($field_props['VALUES'])['ID']
                                  : ($field_props['DEFAULT_VALUE'] ?? ""),
        'values'        => $field_props['VALUES'] ?? NULL,
        'placeholder'   => "",
        'custom'        => $field['type'] == "custom"?$field['value']:"",
        'description'   => "",
    ];
    if($field_code == 'spisok_lits_fayl'){
            
        $fields['spisok_lits_manual'] = [
            'title'         => "",
            'name'          => "spisok_lits_manual",
            'id'            => "spisok_lits_manual",
            'type'          => "bool",
            'show'          => true,
            'value'         => '0',
            'values'        => [
                '0' => 'Указать вручную',
                '1' => 'Приложить файл',
            ],
            'placeholder'   => "",
            'custom'        => "",
            'description'   => "",
        ];
    }
}
$fields['name']['type']                     = "hidden";
$fields['name']['value']                    = $arResult['IBLOCK']['NAME'];
$fields['zayavlenie_fayl_id']['type']       = "hidden";

$SOTRUDNIK  = $GLOBALS['userFields']($USER->GetId());
$fields['sotrudnik_vedomstva']['value']             = $SOTRUDNIK['FIO'];
$fields['sotrudnik_vedomstva']['type']              = 'readonly';
$fields['vedomstvo_podayushchee_zayavku']['value']  = $SOTRUDNIK['DEPARTMENT'];
$fields['vedomstvo_podayushchee_zayavku']['type']   = 'readonly';

$fields['data_poseshcheniya']['type']               = 'datetimemultiple';
$fields['data_poseshcheniya']['value']              = '';

$fields['spisok_lits']['type']                      = 'table';
$fields['spisok_lits']['table']                     = [
    'columns' => [
        [
            'id'    => 'fio',
            'title' => 'ФИО'
        ],
        [
            'id'    => 'organizacia',
            'title' => 'Организация'
        ],
        [
            'id'    => 'doljnost',
            'title' => 'Должность'
        ],
    ]
];
$fields['avto']['type']                      = 'table';
$fields['avto']['table']                     = [
    'columns' => [
        [
            'id'    => 'ts_marka',
            'title' => 'Марка ТС'
        ],
        [
            'id'    => 'ts_nomer',
            'title' => 'Номер ТС'
        ],
        [
            'id'    => 'ts_voditel',
            'title' => 'Водитель ФИО'
        ],
    ]
];
$fields['spisok_lits_fayl']['type']                 = 'file'; 
$fields['spisok_lits_fayl']['name']                 = $fields['spisok_lits_fayl']['name']."[n0][VALUE]";
$fields['spisok_lits_fayl']['description']          = 
    'Просьба приложить файл с заполненными данными по столбцам: ФИО – Организация – Должность. <a target="_blank" download="Пример.xlsx" href="/upload/propusk-massovy-primer.xlsx">Скачать пример</a>';


if(isset($fields['rukovoditel'])){
    $userDepartmentManagers = $GLOBALS['GetUserDepartmentManager']($USER->GetId(), false, true);
    foreach(array_values($userDepartmentManagers) as $indx => $userDepartmentManager){
        $fields['rukovoditel_'.$indx] = [
                'type'  => 'hidden',
                'id'    => 'rukovoditel_'.$indx,
                'value' => $userDepartmentManager['ID'],
                'name'  => $fields['rukovoditel']['name'].'[n'.$indx.'][VALUE]',
                'custom'=> null
            ] + $fields['rukovoditel'];
    }
    unset($fields['rukovoditel']);
}
?>
<div id="lists_element_add_form"></div>
<script>
var bpFields = <?=json_encode($fields)?>;
bpFields.neobkhodim_li_dostup_na_parkovku.show = function(){
    return ~this.getValueXmlId('vybor_zdaniya').indexOf('parking_');
}
bpFields.spisok_lits.show = function(){
    return +this.getValue('spisok_lits_manual');
}
bpFields.spisok_lits_fayl.show = function(){
    return !+this.getValue('spisok_lits_manual');
}
bpFields.avto.show = function(){
    return this.getValueXmlId('neobkhodim_li_dostup_na_parkovku') == 'Y';
}
// bpFields.data_poseshcheniya.callback = function(date){
//     window.cbppw_vremya = window.cbppw || BX.PopupWindowManager.create("cbppw_vremya", null, {
//         content: "",
//         darkMode: true,
//         autoHide: true
//     });
//     if(date < new Date()){
//         window.cbppw_vremya.setContent("Время должно быть позднее текущего");
//         window.cbppw_vremya.show();
//         return false;
//     }
//     return true;
// };
document.addEventListener('DOMContentLoaded', function (event) {
  ReactDOM.render(React.createElement(BPForm, {
      fields:       bpFields,
      formName:     "<?=$arResult["FORM_ID"]?>",
      formAction:   "<?=htmlentities($APPLICATION->GetCurDir())?>?livefeed=y&list_id=<?=htmlentities($arResult['IBLOCK']['ID'])?>&element_id=0",
      formAjax:     "<?=$template2Folder.'/ajax.php'?>",
      submitDataType: 'multipart/form-data'
  }), document.querySelector('#lists_element_add_form'));
});
=======
<?
//todo - очистка полей при выборе по условиям
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
        'show'          => in_array($field_code, [
                                    'vedomstvo_podayushchee_zayavku',
                                    'sotrudnik_vedomstva',
                                    'rukovoditel',
                                    'vybor_zdaniya',
                                    'spisok_lits',
                                    'spisok_lits_fayl',
                                    'data_poseshcheniya',
                                    'kabinet',
                                    'tsel_poseshcheniya',
                                    'neobkhodim_li_dostup_na_parkovku',
                                    'avto',
                                    'zayavlenie_fayl_id',
                                    'kontaktnyy_telefon',
                            ]),
        'value'         => !empty($field_props['VALUES'])
                                  ? current($field_props['VALUES'])['ID']
                                  : ($field_props['DEFAULT_VALUE'] ?? ""),
        'values'        => $field_props['VALUES'] ?? NULL,
        'placeholder'   => "",
        'custom'        => $field['type'] == "custom"?$field['value']:"",
        'description'   => "",
    ];
    if($field_code == 'spisok_lits_fayl'){
            
        $fields['spisok_lits_manual'] = [
            'title'         => "",
            'name'          => "spisok_lits_manual",
            'id'            => "spisok_lits_manual",
            'type'          => "bool",
            'show'          => true,
            'value'         => '0',
            'values'        => [
                '0' => 'Указать вручную',
                '1' => 'Приложить файл',
            ],
            'placeholder'   => "",
            'custom'        => "",
            'description'   => "",
        ];
    }
}
$fields['name']['type']                     = "hidden";
$fields['name']['value']                    = $arResult['IBLOCK']['NAME'];
$fields['zayavlenie_fayl_id']['type']       = "hidden";

$SOTRUDNIK  = $GLOBALS['userFields']($USER->GetId());
$fields['sotrudnik_vedomstva']['value']             = $SOTRUDNIK['FIO'];
$fields['sotrudnik_vedomstva']['type']              = 'readonly';
$fields['vedomstvo_podayushchee_zayavku']['value']  = $SOTRUDNIK['DEPARTMENT'];
$fields['vedomstvo_podayushchee_zayavku']['type']   = 'readonly';

$fields['data_poseshcheniya']['type']               = 'datetimemultiple';
$fields['data_poseshcheniya']['value']              = '';

$fields['spisok_lits']['type']                      = 'table';
$fields['spisok_lits']['table']                     = [
    'columns' => [
        [
            'id'    => 'fio',
            'title' => 'ФИО'
        ],
        [
            'id'    => 'organizacia',
            'title' => 'Организация'
        ],
        [
            'id'    => 'doljnost',
            'title' => 'Должность'
        ],
    ]
];
$fields['avto']['type']                      = 'table';
$fields['avto']['table']                     = [
    'columns' => [
        [
            'id'    => 'ts_marka',
            'title' => 'Марка ТС'
        ],
        [
            'id'    => 'ts_nomer',
            'title' => 'Номер ТС'
        ],
        [
            'id'    => 'ts_voditel',
            'title' => 'Водитель ФИО'
        ],
    ]
];
$fields['spisok_lits_fayl']['type']                 = 'file'; 
$fields['spisok_lits_fayl']['name']                 = $fields['spisok_lits_fayl']['name']."[n0][VALUE]";
$fields['spisok_lits_fayl']['description']          = 
    'Просьба приложить файл с заполненными данными по столбцам: ФИО – Организация – Должность. <a target="_blank" download="Пример.xlsx" href="/upload/propusk-massovy-primer.xlsx">Скачать пример</a>';


if(isset($fields['rukovoditel'])){
    $userDepartmentManagers = $GLOBALS['GetUserDepartmentManager']($USER->GetId(), false, true);
    foreach(array_values($userDepartmentManagers) as $indx => $userDepartmentManager){
        $fields['rukovoditel_'.$indx] = [
                'type'  => 'hidden',
                'id'    => 'rukovoditel_'.$indx,
                'value' => $userDepartmentManager['ID'],
                'name'  => $fields['rukovoditel']['name'].'[n'.$indx.'][VALUE]',
                'custom'=> null
            ] + $fields['rukovoditel'];
    }
    unset($fields['rukovoditel']);
}
?>
<div id="lists_element_add_form"></div>
<script>
var bpFields = <?=json_encode($fields)?>;
bpFields.neobkhodim_li_dostup_na_parkovku.show = function(){
    return ~this.getValueXmlId('vybor_zdaniya').indexOf('parking_');
}
bpFields.spisok_lits.show = function(){
    return +this.getValue('spisok_lits_manual');
}
bpFields.spisok_lits_fayl.show = function(){
    return !+this.getValue('spisok_lits_manual');
}
bpFields.avto.show = function(){
    return this.getValueXmlId('neobkhodim_li_dostup_na_parkovku') == 'Y';
}
// bpFields.data_poseshcheniya.callback = function(date){
//     window.cbppw_vremya = window.cbppw || BX.PopupWindowManager.create("cbppw_vremya", null, {
//         content: "",
//         darkMode: true,
//         autoHide: true
//     });
//     if(date < new Date()){
//         window.cbppw_vremya.setContent("Время должно быть позднее текущего");
//         window.cbppw_vremya.show();
//         return false;
//     }
//     return true;
// };
document.addEventListener('DOMContentLoaded', function (event) {
  ReactDOM.render(React.createElement(BPForm, {
      fields:       bpFields,
      formName:     "<?=$arResult["FORM_ID"]?>",
      formAction:   "<?=htmlentities($APPLICATION->GetCurDir())?>?livefeed=y&list_id=<?=htmlentities($arResult['IBLOCK']['ID'])?>&element_id=0",
      formAjax:     "<?=$template2Folder.'/ajax.php'?>",
      submitDataType: 'multipart/form-data'
  }), document.querySelector('#lists_element_add_form'));
});
>>>>>>> e0a0eba79 (init)
</script>