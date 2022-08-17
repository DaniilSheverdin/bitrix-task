<<<<<<< HEAD
<?
define('ED_VYPLATU', '7ddd7fca65ef48ea210c27131afefafd');
$template2Folder = $templateFolder.'/forms/'.$arResult['IBLOCK']['ID'];

/*
 * Мировых судей редиректнуть в свой процесс
 */
/*
\Bitrix\Main\Loader::includeModule('intranet');
$arMirSudDeps = \CIntranetUtils::GetDeparmentsTree(2229, true);
$arMirSudDeps[] = 2229;
$orm = \Bitrix\Main\UserTable::getList([
    'select'    => ['ID', 'UF_DEPARTMENT'],
    'filter'    => ['ID' => $GLOBALS['USER']->GetID()]
]);
$arUser = $orm->fetch();
if (in_array($arUser['UF_DEPARTMENT'][0], $arMirSudDeps)) {
    LocalRedirect('/bizproc/processes/?livefeed=y&list_id=607&element_id=0');
}
*/

$this->addExternalCss('/bitrix/templates/.default/bootstrap.min.css');
$this->addExternalCss($template2Folder.'/style.css');
$this->addExternalJS('/bitrix/templates/.default/jquery.min.js');
$this->addExternalJS('/bitrix/templates/.default/bootstrap.min.js');
if (defined('DEV_SERVER')) {
    $this->addExternalJS('https://unpkg.com/react@16/umd/react.development.js');
    $this->addExternalJS('https://unpkg.com/react-dom@16/umd/react-dom.development.js');
} else {
    $this->addExternalJS($templateFolder.'/react.production.min.js');
    $this->addExternalJS($templateFolder.'/react-dom.production.min.js');
}
$this->addExternalJS($templateFolder.'/forms/common/form.js');

$fields = [
    'sessid' => [
        'title'         => '',
        'name'          => 'sessid',
        'id'            => 'sessid',
        'type'          => 'hidden',
        'show'          => true,
        'value'         => bitrix_sessid(),
        'values'        => null,
        'placeholder'   => '',
        'custom'        => '',
        'description'   => '',
    ],
    'iblock_id' => [
        'title'         => '',
        'name'          => 'IBLOCK_ID',
        'id'            => 'iblock_id',
        'type'          => 'hidden',
        'show'          => true,
        'value'         => $arResult['IBLOCK']['ID'],
        'values'        => null,
        'placeholder'   => '',
        'custom'        => '',
        'description'   => '',
    ],
    'save' => [
        'title'         => '',
        'name'          => 'save',
        'id'            => 'save',
        'type'          => 'hidden',
        'show'          => true,
        'value'         => 'Сохранить',
        'values'        => null,
        'placeholder'   => '',
        'custom'        => '',
        'description'   => '',
    ]
];

foreach ($arTabs[0]['fields'] as $field) {
    $field_props = $arResult['FIELDS'][ preg_replace("!\[.*?\]!", '', $field['id']) ] ?? [];
    $field_code  = mb_strtolower($field_props['CODE'] ?? $field['id']);
    if ($field_code == 'year') {
        foreach ($field_props['VALUES'] as $key => $val) {
            if ((int)$val['VALUE'] == 2020) {
                unset($field_props['VALUES'][ $key ]);
            }
        }
    }
    $fields[ $field_code ] = [
        'title'         => $field['name'],
        'name'          => $field['id'],
        'id'            => $field_code,
        'type'          => $field_props['PROPERTY_TYPE'] == 'L'?'list':'text',
        'show'          => in_array($field_code, [
                                'fio',
                                'dolzhnost',
                                'organ',
                                'tip_dolzhnosti',
                                'proshu_predostavit',
                                'data_prikaza',
                                'nomer_prikaza',
                                'data_nachala_otpuska',
                                'ruk_oiv_org',
                                'year',
                            ]),
        'value'         => !empty($field_props['VALUES'])
                                  ? current($field_props['VALUES'])['ID']
                                  : ($field_props['DEFAULT_VALUE'] ?? ''),
        'values'        => $field_props['VALUES'] ?? null,
        'placeholder'   => '',
        'custom'        => $field['type'] == 'custom'?$field['value']:'',
        'description'   => '',
    ];
}
$fields['name']['type']                 = 'hidden';
$fields['name']['value']                = $arResult['IBLOCK']['NAME'];
$fields['zayavlenie_fayl_id']['type']   = 'hidden';
$fields['fio']['type']                  = 'readonly';
$fields['dolzhnost']['type']            = 'readonly';
$fields['organ']['type']                = 'readonly';
$fields['data_prikaza']['type']         = 'date';
$fields['data_nachala_otpuska']['type'] = 'date';
$fields['ruk_oiv_org']['type']          = 'user';


$SOTRUDNIK                      = $GLOBALS['userFields']($USER->GetId());
$fields['fio']['value']         = $SOTRUDNIK['FIO'];
$fields['dolzhnost']['value']   = $SOTRUDNIK['WORK_POSITION'];
$fields['organ']['value']       = $SOTRUDNIK['DEPARTMENT'];
?>
<div id="lists_element_add_form"></div>
<script>
var bpFields = <?=json_encode($fields)?>;
bpFields.data_prikaza.show = 
bpFields.nomer_prikaza.show = 
bpFields.data_nachala_otpuska.show = function(){
    return this.getValueXmlId('proshu_predostavit') == '<?=ED_VYPLATU?>';
};
document.addEventListener('DOMContentLoaded', function (event) {
  ReactDOM.render(React.createElement(BPForm, {
      fields:       bpFields,
      formName:     '<?=$arResult['FORM_ID']?>',
      formAction:   '<?=htmlentities($APPLICATION->GetCurDir())?>?livefeed=y&list_id=<?=htmlentities($arResult['IBLOCK']['ID'])?>&element_id=0',
      formAjax:     '<?=$template2Folder.'/ajax.php'?>',
  }), document.querySelector('#lists_element_add_form'));
});
=======
<?
define('ED_VYPLATU', '7ddd7fca65ef48ea210c27131afefafd');
$template2Folder = $templateFolder.'/forms/'.$arResult['IBLOCK']['ID'];

/*
 * Мировых судей редиректнуть в свой процесс
 */
/*
\Bitrix\Main\Loader::includeModule('intranet');
$arMirSudDeps = \CIntranetUtils::GetDeparmentsTree(2229, true);
$arMirSudDeps[] = 2229;
$orm = \Bitrix\Main\UserTable::getList([
    'select'    => ['ID', 'UF_DEPARTMENT'],
    'filter'    => ['ID' => $GLOBALS['USER']->GetID()]
]);
$arUser = $orm->fetch();
if (in_array($arUser['UF_DEPARTMENT'][0], $arMirSudDeps)) {
    LocalRedirect('/bizproc/processes/?livefeed=y&list_id=607&element_id=0');
}
*/

$this->addExternalCss('/bitrix/templates/.default/bootstrap.min.css');
$this->addExternalCss($template2Folder.'/style.css');
$this->addExternalJS('/bitrix/templates/.default/jquery.min.js');
$this->addExternalJS('/bitrix/templates/.default/bootstrap.min.js');
if (defined('DEV_SERVER')) {
    $this->addExternalJS('https://unpkg.com/react@16/umd/react.development.js');
    $this->addExternalJS('https://unpkg.com/react-dom@16/umd/react-dom.development.js');
} else {
    $this->addExternalJS($templateFolder.'/react.production.min.js');
    $this->addExternalJS($templateFolder.'/react-dom.production.min.js');
}
$this->addExternalJS($templateFolder.'/forms/common/form.js');

$fields = [
    'sessid' => [
        'title'         => '',
        'name'          => 'sessid',
        'id'            => 'sessid',
        'type'          => 'hidden',
        'show'          => true,
        'value'         => bitrix_sessid(),
        'values'        => null,
        'placeholder'   => '',
        'custom'        => '',
        'description'   => '',
    ],
    'iblock_id' => [
        'title'         => '',
        'name'          => 'IBLOCK_ID',
        'id'            => 'iblock_id',
        'type'          => 'hidden',
        'show'          => true,
        'value'         => $arResult['IBLOCK']['ID'],
        'values'        => null,
        'placeholder'   => '',
        'custom'        => '',
        'description'   => '',
    ],
    'save' => [
        'title'         => '',
        'name'          => 'save',
        'id'            => 'save',
        'type'          => 'hidden',
        'show'          => true,
        'value'         => 'Сохранить',
        'values'        => null,
        'placeholder'   => '',
        'custom'        => '',
        'description'   => '',
    ]
];

foreach ($arTabs[0]['fields'] as $field) {
    $field_props = $arResult['FIELDS'][ preg_replace("!\[.*?\]!", '', $field['id']) ] ?? [];
    $field_code  = mb_strtolower($field_props['CODE'] ?? $field['id']);
    if ($field_code == 'year') {
        foreach ($field_props['VALUES'] as $key => $val) {
            if ((int)$val['VALUE'] == 2020) {
                unset($field_props['VALUES'][ $key ]);
            }
        }
    }
    $fields[ $field_code ] = [
        'title'         => $field['name'],
        'name'          => $field['id'],
        'id'            => $field_code,
        'type'          => $field_props['PROPERTY_TYPE'] == 'L'?'list':'text',
        'show'          => in_array($field_code, [
                                'fio',
                                'dolzhnost',
                                'organ',
                                'tip_dolzhnosti',
                                'proshu_predostavit',
                                'data_prikaza',
                                'nomer_prikaza',
                                'data_nachala_otpuska',
                                'ruk_oiv_org',
                                'year',
                            ]),
        'value'         => !empty($field_props['VALUES'])
                                  ? current($field_props['VALUES'])['ID']
                                  : ($field_props['DEFAULT_VALUE'] ?? ''),
        'values'        => $field_props['VALUES'] ?? null,
        'placeholder'   => '',
        'custom'        => $field['type'] == 'custom'?$field['value']:'',
        'description'   => '',
    ];
}
$fields['name']['type']                 = 'hidden';
$fields['name']['value']                = $arResult['IBLOCK']['NAME'];
$fields['zayavlenie_fayl_id']['type']   = 'hidden';
$fields['fio']['type']                  = 'readonly';
$fields['dolzhnost']['type']            = 'readonly';
$fields['organ']['type']                = 'readonly';
$fields['data_prikaza']['type']         = 'date';
$fields['data_nachala_otpuska']['type'] = 'date';
$fields['ruk_oiv_org']['type']          = 'user';


$SOTRUDNIK                      = $GLOBALS['userFields']($USER->GetId());
$fields['fio']['value']         = $SOTRUDNIK['FIO'];
$fields['dolzhnost']['value']   = $SOTRUDNIK['WORK_POSITION'];
$fields['organ']['value']       = $SOTRUDNIK['DEPARTMENT'];
?>
<div id="lists_element_add_form"></div>
<script>
var bpFields = <?=json_encode($fields)?>;
bpFields.data_prikaza.show = 
bpFields.nomer_prikaza.show = 
bpFields.data_nachala_otpuska.show = function(){
    return this.getValueXmlId('proshu_predostavit') == '<?=ED_VYPLATU?>';
};
document.addEventListener('DOMContentLoaded', function (event) {
  ReactDOM.render(React.createElement(BPForm, {
      fields:       bpFields,
      formName:     '<?=$arResult['FORM_ID']?>',
      formAction:   '<?=htmlentities($APPLICATION->GetCurDir())?>?livefeed=y&list_id=<?=htmlentities($arResult['IBLOCK']['ID'])?>&element_id=0',
      formAjax:     '<?=$template2Folder.'/ajax.php'?>',
  }), document.querySelector('#lists_element_add_form'));
});
>>>>>>> e0a0eba79 (init)
</script>