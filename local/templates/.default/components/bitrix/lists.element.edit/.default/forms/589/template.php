<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) {
    die();
}

$arInitiative = [];
$arTask = [];
if (isset($_REQUEST['task_id'])) {
    CModule::IncludeModule('tasks');
    $arInitiative = \Citto\Tasks\ProjectInitiative::getBizProcByTaskId($_REQUEST['task_id']);
    $arTask = CTasks::GetByID($_REQUEST['task_id'])->Fetch();
}

if (empty($arInitiative) || empty($arTask)) {
    ShowError('Создание проекта доступно только из Проектной инициативы');
    exit;
}

$template2Folder = $templateFolder.'/forms/'.$arResult['IBLOCK']['ID'];

$this->addExternalCss('/bitrix/templates/.default/bootstrap.min.css');
$this->addExternalCss($template2Folder.'/style.css');
$this->addExternalCss($templateFolder.'/libs/select2/select2.min.css');
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
$this->addExternalJS($templateFolder.'/libs/select2/select2.min.js');

\Bitrix\Main\Loader::includeModule('iblock');
$arFields = [
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
    ],
];

$arNonEditable = [
    'parent',
    'link',
];

foreach ($arTabs[0]['fields'] as $arField) {
    $arProps = $arResult['FIELDS'][ preg_replace("!\[.*?\]!", '', $arField['id']) ] ?? [];

    $sFieldCode = mb_strtolower($arProps['CODE'] ?? $arField['id']);

    $sFieldType = 'text';
    if ($arProps['PROPERTY_TYPE'] == 'L') {
        $sFieldType = 'list';
    } elseif ($arProps['PROPERTY_TYPE'] == 'S' && $arProps['ROW_COUNT'] > 1) {
        $sFieldType = 'textarea';
    } elseif ($arProps['PROPERTY_TYPE'] == 'F') {
        $sFieldType = ($arProps['MULTIPLE']=='Y' ? 'filemultiple' : 'file');
    }

    $value = '';
    if (isset($arResult['FORM_DATA'][ $arField['id'] ]) && !is_array($arResult['FORM_DATA'][ $arField['id'] ])) {
        $value = $arResult['FORM_DATA'][ $arField['id'] ];
    } else {
        if (!empty($arProps['VALUES'])) {
            $value = current($arProps['VALUES'])['ID'];
        } else {
            $value = ($arProps['DEFAULT_VALUE'] ?? '');
        }
    }

    if ($sFieldCode == 'rukovoditel') {
        $arFields['rukovoditel'] = [
            'name'          => $arField['id'],
            'id'            => $arField['id'],
            'type'          => 'hidden',
            'value'         => $arTask['RESPONSIBLE_ID'],
        ];
        $sFieldCode = 'rukovoditel_name';
        $sFieldType = 'readonly';
        $value = implode(' ', [$arTask['RESPONSIBLE_NAME'], $arTask['RESPONSIBLE_LAST_NAME']]);
        $arField['id'] = 'rukovoditel_name';
    }

    $arFields[ $sFieldCode ] = [
        'title'         => $arField['name'],
        'name'          => $arField['id'],
        'id'            => $sFieldCode,
        'type'          => $sFieldType,
        'show'          => !in_array($sFieldCode, $arNonEditable),
        'value'         => $value,
        'values'        => $arProps['VALUES'] ?? null,
        'placeholder'   => $arField['name'],
        'custom'        => $arField['type'] == 'custom' ? $arField['value'] : '',
        'description'   => '',
    ];
}

$arFields['name']['value'] = $arInitiative['NAME'];
$arFields['parent']['value'] = $arInitiative['ID'];
$arFields['fz']['value'] = $arInitiative['PROPERTY_FZ_VALUE'];
$arFields['link']['value'] = 'https://corp.tularegion.local/workgroups/group/' . \Citto\Tasks\ProjectInitiative::$groupId . '/tasks/task/view/' . $_REQUEST['task_id'] . '/';

?>
<div id="lists_element_add_form"></div>
<script>
var bpFields = <?=json_encode($arFields)?>;
document.addEventListener('DOMContentLoaded', function (event) {
    ReactDOM.render(
        React.createElement(BPForm, {
            fields:       bpFields,
            formName:     '<?=$arResult['FORM_ID']?>',
            formAction:   '<?=htmlentities($APPLICATION->GetCurDir())?>?livefeed=y&list_id=<?=htmlentities($arResult['IBLOCK']['ID'])?>&element_id=0',
            formAjax:     '<?=$template2Folder.'/ajax.php'?>',
        }),
        document.querySelector('#lists_element_add_form')
    );
});
</script>