<<<<<<< HEAD
<?

$template2Folder = $templateFolder."/forms/".$arResult['IBLOCK']['ID'];

$this->addExternalCss('/bitrix/templates/.default/bootstrap.min.css');
$this->addExternalCss($template2Folder.'/style.css');
$this->addExternalCss($templateFolder.'/libs/select2/select2.min.css');
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
$this->addExternalJS($templateFolder."/libs/select2/select2.min.js");

\Bitrix\Main\Loader::includeModule('iblock');
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
    ],
    'iblock_section_id' => [
        'title'         => "Тема",
        'name'          => "IBLOCK_SECTION_ID",
        'id'            => "iblock_section_id",
        'type'          => "treelist",
        'show'          => true,
        'value'         => "",
        'values'        => count($arResult['LIST_SECTIONS']) < 2
                                ? []
                                : array_reduce(
                                    array_map(
                                        function($item){
                                            $item['XML_ID'] = $item['CODE'] . ($item['CODE'] != "ccdrugoe" ? "_".$item['ID']: ""); return $item;
                                        },
                                        \Bitrix\Iblock\SectionTable::getList([
                                            'select' => [
                                                'ID'    => "ID",
                                                'CODE'  => "CODE",
                                                'SORT'  => "SORT",
                                                'VALUE' => "NAME",
                                                'PARENT'=> "IBLOCK_SECTION_ID",
                                            ],
                                            'order' => [
                                                'SORT' => "ASC"
                                            ],
                                            'filter' => [
                                                'ID' => array_filter(array_keys($arResult['LIST_SECTIONS']))
                                            ],
                                        ])->fetchAll()
                                    ),
                                    function($carry, $item){
                                        return $carry + [$item['ID'] => $item];
                                    },
                                    []
                                ),
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
                                    'detail_text',
                                    'iblock_section_id',
                                    'prilozheniya',
                            ]),
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
$fields['tema']['show']                     = false;
$fields['tema']['title']                    = "";
$fields['tema']['placeholder']              = "Введите тему вручную";
$fields['prilozheniya']['type']             = "filemultiple";
$fields['detail_text']['title']             = "Подробное описание";
$fields['detail_text']['type']              = "textarea";
?>
<div id="lists_element_add_form"></div>
<script>
var bpFields = <?=json_encode($fields)?>;
bpFields.tema.show = function(){
    return this.getValueXmlId('iblock_section_id') == "ccdrugoe";
};
document.addEventListener('DOMContentLoaded', function (event) {
  ReactDOM.render(React.createElement(BPForm, {
      fields:       bpFields,
      formName:     "<?=$arResult["FORM_ID"]?>",
      formAction:   "<?=htmlentities($APPLICATION->GetCurDir())?>?livefeed=y&list_id=<?=htmlentities($arResult['IBLOCK']['ID'])?>&element_id=0",
      formAjax:     "<?=$template2Folder.'/ajax.php'?>",
      submitText:   "Отправить обращение в техническую поддержку",
      submitDataType:   "multipart/form-data"
  }), document.querySelector('#lists_element_add_form'));
});
=======
<?

$template2Folder = $templateFolder."/forms/".$arResult['IBLOCK']['ID'];

$this->addExternalCss('/bitrix/templates/.default/bootstrap.min.css');
$this->addExternalCss($template2Folder.'/style.css');
$this->addExternalCss($templateFolder.'/libs/select2/select2.min.css');
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
$this->addExternalJS($templateFolder."/libs/select2/select2.min.js");

\Bitrix\Main\Loader::includeModule('iblock');
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
    ],
    'iblock_section_id' => [
        'title'         => "Тема",
        'name'          => "IBLOCK_SECTION_ID",
        'id'            => "iblock_section_id",
        'type'          => "treelist",
        'show'          => true,
        'value'         => "",
        'values'        => count($arResult['LIST_SECTIONS']) < 2
                                ? []
                                : array_reduce(
                                    array_map(
                                        function($item){
                                            $item['XML_ID'] = $item['CODE'] . ($item['CODE'] != "ccdrugoe" ? "_".$item['ID']: ""); return $item;
                                        },
                                        \Bitrix\Iblock\SectionTable::getList([
                                            'select' => [
                                                'ID'    => "ID",
                                                'CODE'  => "CODE",
                                                'SORT'  => "SORT",
                                                'VALUE' => "NAME",
                                                'PARENT'=> "IBLOCK_SECTION_ID",
                                            ],
                                            'order' => [
                                                'SORT' => "ASC"
                                            ],
                                            'filter' => [
                                                'ID' => array_filter(array_keys($arResult['LIST_SECTIONS']))
                                            ],
                                        ])->fetchAll()
                                    ),
                                    function($carry, $item){
                                        return $carry + [$item['ID'] => $item];
                                    },
                                    []
                                ),
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
                                    'detail_text',
                                    'iblock_section_id',
                                    'prilozheniya',
                            ]),
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
$fields['tema']['show']                     = false;
$fields['tema']['title']                    = "";
$fields['tema']['placeholder']              = "Введите тему вручную";
$fields['prilozheniya']['type']             = "filemultiple";
$fields['detail_text']['title']             = "Подробное описание";
$fields['detail_text']['type']              = "textarea";
?>
<div id="lists_element_add_form"></div>
<script>
var bpFields = <?=json_encode($fields)?>;
bpFields.tema.show = function(){
    return this.getValueXmlId('iblock_section_id') == "ccdrugoe";
};
document.addEventListener('DOMContentLoaded', function (event) {
  ReactDOM.render(React.createElement(BPForm, {
      fields:       bpFields,
      formName:     "<?=$arResult["FORM_ID"]?>",
      formAction:   "<?=htmlentities($APPLICATION->GetCurDir())?>?livefeed=y&list_id=<?=htmlentities($arResult['IBLOCK']['ID'])?>&element_id=0",
      formAjax:     "<?=$template2Folder.'/ajax.php'?>",
      submitText:   "Отправить обращение в техническую поддержку",
      submitDataType:   "multipart/form-data"
  }), document.querySelector('#lists_element_add_form'));
});
>>>>>>> e0a0eba79 (init)
</script>