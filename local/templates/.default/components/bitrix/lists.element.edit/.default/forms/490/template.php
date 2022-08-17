<<<<<<< HEAD
<?
\Bitrix\Main\Loader::includeModule('intranet');
$arDepartmentsJudge = CIntranetUtils::GetIBlockSectionChildren(2229);
$el = new CIBlockElement;
$arUsersJudge = [];
$obUsers = CUser::GetList($by = 'LAST_NAME', $order = 'ASC', ['ID' => $USER->GetId()], ['SELECT' => ['UF_DEPARTMENT']]);
while ($arUser = $obUsers->getNext()) {
    foreach ($arUser['UF_DEPARTMENT'] as $iDepartID) {
        if (in_array($iDepartID, $arDepartmentsJudge)) {
            array_push($arUsersJudge, $arUser['ID']);
        }
    }
}
$bIsJudge = (in_array($USER->GetId(), $arUsersJudge));
$bIsJudge = ($bIsJudge) ? 1 : 0;

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
                                    'fio',
                                    'dolzhnost',
                                    'proshu_predostavit_otpusk_bez_sokhraneniya',
                                    'data_s',
                                    'na_kol_vo_kalendarnykh_dney',
                                    'prichina_v_svyazi_s',
                                    'prichina_podrobnee',
                                    'rukovoditel_oiv',
                                    'neposredstvennye_rukovoditeli',
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
$fields['zayavlenie_fayl_id']['type']       = "hidden";
$fields['fio']['type']                      = "readonly";
$fields['dolzhnost']['type']                = "readonly";
$fields['data_s']['type']                   = "date";
$fields['prichina_podrobnee']['placeholder']= "Опишите причину подробнее";

$fields['rukovoditel_oiv']['type']                  = "user";
$fields['neposredstvennye_rukovoditeli']['type']    = "user";


$SOTRUDNIK                          = $GLOBALS['userFields']($USER->GetId());
$fields['fio']['value']             = $SOTRUDNIK['FIO'];
$fields['dolzhnost']['value']       = $SOTRUDNIK['WORK_POSITION'];
?>
<div id="lists_element_add_form"></div>
<script>
var bIsJudge = <?=$bIsJudge?>;
var bpFields = <?=json_encode($fields)?>;
bpFields.prichina_podrobnee.show = function(){
    return this.getValueXmlId('prichina_v_svyazi_s') == "1a55e3f43ed23ecd462580e368544507" || this.getValueXmlId('prichina_v_svyazi_s') == "5eae82e832f195ce056b0229e83141f6";
}
document.addEventListener('DOMContentLoaded', function (event) {
  ReactDOM.render(React.createElement(BPForm, {
      fields:       bpFields,
      formName:     "<?=$arResult["FORM_ID"]?>",
      formAction:   "<?=htmlentities($APPLICATION->GetCurDir())?>?livefeed=y&list_id=<?=htmlentities($arResult['IBLOCK']['ID'])?>&element_id=0",
      formAjax:     "<?=$template2Folder.'/ajax.php'?>",
  }), document.querySelector('#lists_element_add_form'));
});
$('body').ready(function(){
    if (bIsJudge) {
        $('[data-id="proshu_predostavit_otpusk_bez_sokhraneniya"] option[value="1079"]').detach();
    }
});
=======
<?
\Bitrix\Main\Loader::includeModule('intranet');
$arDepartmentsJudge = CIntranetUtils::GetIBlockSectionChildren(2229);
$el = new CIBlockElement;
$arUsersJudge = [];
$obUsers = CUser::GetList($by = 'LAST_NAME', $order = 'ASC', ['ID' => $USER->GetId()], ['SELECT' => ['UF_DEPARTMENT']]);
while ($arUser = $obUsers->getNext()) {
    foreach ($arUser['UF_DEPARTMENT'] as $iDepartID) {
        if (in_array($iDepartID, $arDepartmentsJudge)) {
            array_push($arUsersJudge, $arUser['ID']);
        }
    }
}
$bIsJudge = (in_array($USER->GetId(), $arUsersJudge));
$bIsJudge = ($bIsJudge) ? 1 : 0;

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
                                    'fio',
                                    'dolzhnost',
                                    'proshu_predostavit_otpusk_bez_sokhraneniya',
                                    'data_s',
                                    'na_kol_vo_kalendarnykh_dney',
                                    'prichina_v_svyazi_s',
                                    'prichina_podrobnee',
                                    'rukovoditel_oiv',
                                    'neposredstvennye_rukovoditeli',
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
$fields['zayavlenie_fayl_id']['type']       = "hidden";
$fields['fio']['type']                      = "readonly";
$fields['dolzhnost']['type']                = "readonly";
$fields['data_s']['type']                   = "date";
$fields['prichina_podrobnee']['placeholder']= "Опишите причину подробнее";

$fields['rukovoditel_oiv']['type']                  = "user";
$fields['neposredstvennye_rukovoditeli']['type']    = "user";


$SOTRUDNIK                          = $GLOBALS['userFields']($USER->GetId());
$fields['fio']['value']             = $SOTRUDNIK['FIO'];
$fields['dolzhnost']['value']       = $SOTRUDNIK['WORK_POSITION'];
?>
<div id="lists_element_add_form"></div>
<script>
var bIsJudge = <?=$bIsJudge?>;
var bpFields = <?=json_encode($fields)?>;
bpFields.prichina_podrobnee.show = function(){
    return this.getValueXmlId('prichina_v_svyazi_s') == "1a55e3f43ed23ecd462580e368544507" || this.getValueXmlId('prichina_v_svyazi_s') == "5eae82e832f195ce056b0229e83141f6";
}
document.addEventListener('DOMContentLoaded', function (event) {
  ReactDOM.render(React.createElement(BPForm, {
      fields:       bpFields,
      formName:     "<?=$arResult["FORM_ID"]?>",
      formAction:   "<?=htmlentities($APPLICATION->GetCurDir())?>?livefeed=y&list_id=<?=htmlentities($arResult['IBLOCK']['ID'])?>&element_id=0",
      formAjax:     "<?=$template2Folder.'/ajax.php'?>",
  }), document.querySelector('#lists_element_add_form'));
});
$('body').ready(function(){
    if (bIsJudge) {
        $('[data-id="proshu_predostavit_otpusk_bez_sokhraneniya"] option[value="1079"]').detach();
    }
});
>>>>>>> e0a0eba79 (init)
</script>