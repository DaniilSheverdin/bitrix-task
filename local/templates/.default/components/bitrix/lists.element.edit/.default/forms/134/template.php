<?
define('HAS_VALID_SIGN_DA',         "8bbc1edaa99be3d636e05dcf0e64382d");
define('BP_POLUCHAET_LICHNO_DA',    "7536693b12021eef1a42d4295224d985");
define('ZAYAVITEL_RUKOVODITEL',     "2ff4e9b1f177af7ef92a64b39e504647");
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
        'show'          => !in_array($field_code, ['']),
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
$fields['rukovoditel']['type']                  = "user";

$fields['zayavka']['type']                      = "hidden";
$fields['zayavka_file']                         =  array_merge($fields['zayavka'], [
                                                    'name'          => "zayavka_file",
                                                    'id'            => "zayavka_file",
                                                    'description'   => '<a href="https://corp.tularegion.ru/~rlFX8">скачать шаблон заявки</a>',
                                                    'type'          => "file",
                                                ]);

$fields['doverennost']['type']                  = "hidden";
$fields['doverennost_file']                     = array_merge($fields['doverennost'], [
                                                    'name'          => "doverennost_file",
                                                    'id'            => "doverennost_file",
                                                    'description'   => '<a href="https://corp.tularegion.ru/~A6X6m">скачать шаблон доверенности</a>',
                                                    'type'          => "file",
                                                ]);

$fields['doverennost_upolnomochennogo']['type'] = "hidden";
$fields['doverennost_upolnomochennogo_file']    = array_merge($fields['doverennost_upolnomochennogo'], [
                                                    'name'          => "doverennost_upolnomochennogo_file",
                                                    'id'            => "doverennost_upolnomochennogo_file",
                                                    'description'   => '<a href="https://corp.tularegion.ru/~8JGZg">скачать шаблон доверенности</a>',
                                                    'type'          => "file",
                                                ]);

$fields['descr_noep'] = [
    'title'         => "",
    'name'          => "descr_noep",
    'id'            => "descr_noep",
    'type'          => "textblock",
    'show'          => true,
    'value'         => '',
    'values'        => null,
    'placeholder'   => "",
    'custom'        => "",
    'description'   => '
        <div>Для получения Электронной подписи Вам необходимо явиться в УЦ и иметь при себе:</div>
        <div><strong>Заполненный и подписанный пакет документов:</strong></div>
        <div>- Заявление - <a href="https://corp.tularegion.ru/~rlFX8"><strong>скачать шаблон</strong></a></div>
        <div>- Доверенность на осуществление действий от имени юр. Лица (или приказ)  - <a href="https://corp.tularegion.ru/~A6X6m"><strong>скачать шаблон</strong></a></div>
    '
];
$fields['descr_noep_pol_lichno'] = [
    'title'         => "",
    'name'          => "descr_noep_pol_lichno",
    'id'            => "descr_noep_pol_lichno",
    'type'          => "textblock",
    'show'          => true,
    'value'         => '',
    'values'        => null,
    'placeholder'   => "",
    'custom'        => "",
    'description'   => '
        <div>- Паспорт оригинал</div>
        <div>- СНИЛС оригинал</div>
    '
];
$fields['descr_noep_ne_pol_lichno'] = [
    'title'         => "",
    'name'          => "descr_noep_ne_pol_lichno",
    'id'            => "descr_noep_ne_pol_lichno",
    'type'          => "textblock",
    'show'          => true,
    'value'         => '',
    'values'        => null,
    'placeholder'   => "",
    'custom'        => "",
    'description'   => '
        <div>- Доверенность уполномоченного пользователя - <a href="https://corp.tularegion.ru/~8JGZg"><strong>скачать шаблон</strong></a></div>
        <div>- Паспорт копия</div>
        <div>- СНИЛС копия</div>
    '
];

$fields['descr_ep'] = [
    'title'         => "",
    'name'          => "descr_ep",
    'id'            => "descr_ep",
    'type'          => "textblock",
    'show'          => true,
    'value'         => '',
    'values'        => null,
    'placeholder'   => "",
    'custom'        => "",
    'description'   => '
        <div>Для получения Электронной подписи Вам необходимо явиться в УЦ и иметь при себе:</div>
        <div>- Носитель</div>
    '
];
$fields['descr_ep_pol_lichno'] = [
    'title'         => "",
    'name'          => "descr_ep_pol_lichno",
    'id'            => "descr_ep_pol_lichno",
    'type'          => "textblock",
    'show'          => true,
    'value'         => '',
    'values'        => null,
    'placeholder'   => "",
    'custom'        => "",
    'description'   => '
        <div>- Паспорт оригинал</div>
    '
];
$fields['descr_ep_ne_pol_lichno'] = [
    'title'         => "",
    'name'          => "descr_ep_ne_pol_lichno",
    'id'            => "descr_ep_ne_pol_lichno",
    'type'          => "textblock",
    'show'          => true,
    'value'         => '',
    'values'        => null,
    'placeholder'   => "",
    'custom'        => "",
    'description'   => '
        <div>- Паспорт копия</div>
    '
];

$SOTRUDNIK = $GLOBALS['userFields']($USER->GetId());
?>
<div id="lists_element_add_form"></div>
<script>
var bpFields = <?=json_encode($fields)?>;
bpFields.rukovoditel.show = function(){
    return this.getValueXmlId('zayavitel_yavlyaetsya_rukovoditelem') != "<?=ZAYAVITEL_RUKOVODITEL?>";
};
bpFields.zayavka_file.show = function(){
    return this.getValueXmlId('has_valid_sign') == "<?=HAS_VALID_SIGN_DA?>";
};
bpFields.doverennost_file.show = function(){
    return this.getValueXmlId('has_valid_sign') == "<?=HAS_VALID_SIGN_DA?>" && this.getValueXmlId('poluchenie_lichno') == "<?=BP_POLUCHAET_LICHNO_DA?>"
            && this.getValueXmlId('zayavitel_yavlyaetsya_rukovoditelem') != "<?=ZAYAVITEL_RUKOVODITEL?>";
};
bpFields.doverennost_upolnomochennogo_file.show = function(){
    return this.getValueXmlId('has_valid_sign') == "<?=HAS_VALID_SIGN_DA?>" && this.getValueXmlId('poluchenie_lichno') != "<?=BP_POLUCHAET_LICHNO_DA?>";
};
bpFields.descr_noep.show = function(){
    return this.getValueXmlId('has_valid_sign') != "<?=HAS_VALID_SIGN_DA?>";
};
bpFields.descr_noep_pol_lichno.show = function(){
    return this.getValueXmlId('has_valid_sign') != "<?=HAS_VALID_SIGN_DA?>" && this.getValueXmlId('poluchenie_lichno') == "<?=BP_POLUCHAET_LICHNO_DA?>";
};
bpFields.descr_noep_ne_pol_lichno.show = function(){
    return this.getValueXmlId('has_valid_sign') != "<?=HAS_VALID_SIGN_DA?>" && this.getValueXmlId('poluchenie_lichno') != "<?=BP_POLUCHAET_LICHNO_DA?>";
};
bpFields.descr_ep.show = function(){
    return this.getValueXmlId('has_valid_sign') == "<?=HAS_VALID_SIGN_DA?>";
};
bpFields.descr_ep_pol_lichno.show = function(){
    return this.getValueXmlId('has_valid_sign') == "<?=HAS_VALID_SIGN_DA?>"  && this.getValueXmlId('poluchenie_lichno') == "<?=BP_POLUCHAET_LICHNO_DA?>";
};
bpFields.descr_ep_ne_pol_lichno.show = function(){
    return this.getValueXmlId('has_valid_sign') == "<?=HAS_VALID_SIGN_DA?>"  && this.getValueXmlId('poluchenie_lichno') != "<?=BP_POLUCHAET_LICHNO_DA?>";
};
document.addEventListener('DOMContentLoaded', function (event) {
  ReactDOM.render(React.createElement(BPForm, {
      fields:           bpFields,
      formName:         "<?=$arResult["FORM_ID"]?>",
      formAction:       "<?=htmlentities($APPLICATION->GetCurDir())?>?livefeed=y&list_id=<?=htmlentities($arResult['IBLOCK']['ID'])?>&element_id=0",
      formAjax:         "<?=$template2Folder.'/ajax.php'?>",
      submitText:       "Сформировать задачу на получение электронной подписи в отдел УЦ",
      submitDataType:   "multipart/form-data"
  }), document.querySelector('#lists_element_add_form'));
});
</script>