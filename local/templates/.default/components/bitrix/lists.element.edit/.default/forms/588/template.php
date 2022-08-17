<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) {
    die();
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
    'task_id',
    'subtask1_id',
    'subtask2_id',
    'file_concept',
    'file_tz',
    'file_ustav',
    'group_id',
];

// поля не для работников отдела 100
$arNonDepartment = [
    'terms',
    'freq',
    'exp_result',
    'risks',
];

// поля для работников отдела 100
$arDepartment = [
    'executive_leader',
    'project_tasks',
    'normative_base',
    'normative_base_file',
    'project_init_kpi',
    'realizing_justification',
    'description_as_is',
    'automation_description',
    'optimized_process_description_to_be',
    'optimized_process_description_to_be_file',
    'roles_description',
    'integrations_description',
    'reports_description',
    'reports_description_file',
    'system_service_access',
    'extra_files'
];

$res = CIntranetUtils::getDepartmentEmployees([100], true);
$employees = [];
while ($row = $res->Fetch()) {
    $employees[ $row['ID'] ] = $row['ID'];
}

unset($employees[ 6207 ]); // Кирилл Рожнов
unset($employees[ 107 ]); // Надежда Коняева
unset($employees[ 101 ]); // Екатерина Дедова
unset($employees[ 2748 ]); // Елена Боргер

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

    if ($arProps['TYPE'] == 'S:employee') {
        $sFieldType = 'user';
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

    $arFields[ $sFieldCode ] =[
        'title'         => $arField['name'],
        'name'          => $arField['id'],
        'id'            => $sFieldCode,
        'type'          => $sFieldType,
        'is_required'   => $arProps['IS_REQUIRED'],
        'sort'          => $arProps['SORT'],
        'show'          => !in_array($sFieldCode, $arNonEditable),
        'value'         => $value,
        'placeholder'   => $arField['name'],
        'custom'        => $arField['type'] == 'custom' ? $arField['value'] : '',
        'description'   => '',
        'values'        => $sFieldType == 'list' ? $arField['items'] : [],
        'non_department_field' => in_array($sFieldCode, $arNonDepartment),
        'department_field' => in_array($sFieldCode, $arDepartment)
    ];
}

CModule::includeModule('iblock');
CModule::IncludeModule('intranet');

// формирование массив ОИВ и полразделений аппарата ПТО
$arrSecIds = CIntranetUtils::GetDeparmentsTree(1727, false);
$arrSecStaffIds = CIntranetUtils::GetDeparmentsTree(1710, false);

$objTree = CIBlockSection::GetTreeList(
    [
        'IBLOCK_ID' => 5,
        'ID'        => $arrSecIds[1727],
        'ACTIVE'    => 'Y'
    ],
    ['ID', 'NAME']
);

$objStaffTree = CIBlockSection::GetTreeList(
    [
        'IBLOCK_ID' => 5,
        'ID'        => $arrSecStaffIds[1710],
        'ACTIVE'    => 'Y'
    ],
    ['ID', 'NAME']
);

$arList = [
    0 => [
        'XML_ID'    => '',
        'ID'        => '',
        'VALUE'     => 'Не выбрано',
    ]
];

$arStaffList = [];

while ($depItem = $objTree->GetNext()) {
    if (!in_array($depItem['ID'], $arrSecIds[1727])) {
        continue;
    }

    $arList[ $depItem['NAME'] ] = [
        'XML_ID'    => $depItem['NAME'],
        'ID'        => $depItem['NAME'],
        'VALUE'     => $depItem['NAME'],
    ];
}

while ($depStaffItem = $objStaffTree->GetNext()) {
    if (!in_array($depStaffItem['ID'], $arrSecStaffIds[1710])) {
        continue;
    }

    $arStaffList[ $depStaffItem['NAME'] ] = [
        'XML_ID'    => $depStaffItem['NAME'] . ' аппарата ПТО',
        'ID'        => $depStaffItem['NAME'] . ' аппарата ПТО',
        'VALUE'     => $depStaffItem['NAME'] . ' аппарата ПТО',
    ];
}

$arList = array_merge($arStaffList, $arList);

ksort($arList);

function createListValues($vals){

  $arListValues = [];

  foreach ($vals as $id => $field) {
    if (empty($id)) {
      $id = 0;
    }

    $arListValues[$id] = [
      'XML_ID' => $id,
      'ID' => $id,
      'VALUE' => $field,
    ];
  }

  return $arListValues;
}

global $userFields, $USER;

$arUserFields = $userFields($USER->GetID());

$arFields['fz']['type'] = 'list';
$arFields['fz']['values'] = $arList;

// настройка полей Задачи проекта и KPI проектной инициативы
$arFields['project_tasks']['type'] = 'table';
$arFields['project_tasks']['table'] = [
    'columns' => [
        [
            'id'    => 'task_description',
            'title' => 'Описание задачи'
        ],
        [
            'id'    => 'realizatoin_term',
            'title' => 'Срок реализации'
        ],
        [
            'id'    => 'justification',
            'title' => 'Обоснование'
        ],
    ]
];

$arFields['project_tasks']['separator'] = '|';

$arFields['project_init_kpi']['type'] = 'table';
$arFields['project_init_kpi']['table'] = [
    'columns' => [
        [
            'id'    => 'kpi_name',
            'title' => 'Название KPI'
        ],
        [
            'id'    => 'kpi_current_value',
            'title' => 'Текущее значение'
        ],
        [
            'id'    => 'kpi_target_value',
            'title' => 'Целевое значение'
        ],
    ]
];
$arFields['project_init_kpi']['separator'] = '|';

// настройка полей с файлами
$fileFields = [
  'normative_base_file',
  'description_as_is',
  'optimized_process_description_to_be_file',
  'reports_description_file',
];

foreach ($fileFields as $field) {
  $arFields[$field]['type'] = 'file';
  $arFields[$field]['name'] .= '[n0][VALUE]';
}

// настройка полей типа "список"
$listFields = [
  'system_service_access',
  'project_type',
  'project_request_type',
];

foreach ($listFields as $field) {
  $arFields[$field]['type'] = 'list';
  $arFields[$field]['values'] = createListValues($arFields[$field]['values']);
}

// обязательные поля
$getRequiredFields = function ($userId, $arEmployees) use ($arFields)
{
    $arEmployees[] = 107;

    $commonFields = array_keys(array_filter($arFields, function ($field) {
      return $field['is_required'] === 'Y';
    }));

    $nonDepFields = [
      'audience',
      'optimized_process_description_to_be',
    ];

    if (in_array($userId, $arEmployees)) {
        return $commonFields;
    }

    return $commonFields + $nonDepFields;
};

$arRequired = $getRequiredFields($arUserFields['ID'], $employees);

// плейсхолдеры и названия
$arFields['name']['title'] = 'Название проектной инициативы';
$arFields['name']['placeholder'] = 'Название проектной инициативы';

$arFields['executive_leader']['placeholder'] = 'Выбор из пользователей КП';

$arFields['user']['placeholder'] = 'Выбор из пользователей КП';

$arFields['audience']['placeholder'] = 'Необходимо описать пользователей создаваемого решения и их количество';

$arFields['project_tasks']['placeholder'] = 'Необходимо уточнить и детализировать перечень конкретных задач, которые должны быть решены в рамках проекта для достижения цели.';

$arFields['deadline']['placeholder'] = 'Крайний срок проекта и обоснование данного срока';

$arFields['planned_ext_experts']['placeholder'] = 'Необходимо заполнить, если выбран вид проекта "Подразумевающий взаимодействие с другими ведомствами" либо "Проект для жителей"';

$arFields['normative_base']['placeholder'] = 'Необходимо привести перечень НПА, регламентирующих деятельность участников процесса.
Если для финальной реализации необходимо вносить изменения в НПА, то также требуется перечень и описание изменений. 
Если правовой акт является локальным, то необходимо приложить его виде файла.';
$arFields['normative_base_file']['title'] = '';

$arFields['realizing_justification']['placeholder'] = 'Необходимо, основываясь на известной информации, провести оценку соотношения выгод и издержек проекта.';

$arFields['automation_description']['placeholder'] = 'В случае если процесс автоматизирован, необходимо описать с помощью какой системы, сервиса это реализовано,  указать имеющие недостатки автоматизации.';

$arFields['optimized_process_description_to_be']['placeholder'] = 'Необходимо привести описание оптимизированного процесса (каким он планируется после реализации настоящих функционально-технических требований) с использованием нотации BPMN версии 2.0 или (при наличии уже выбранного готового решения) указать название программного продукта, описание или ссылку на описание.';
$arFields['optimized_process_description_to_be_file']['title'] = '';

$arFields['roles_description']['placeholder'] = 'Необходимо описать основные роли пользователей (которые могут отличаться по наличию доступа к различным элементам системы/сервиса, по функционалу), задействованных в процессе, подлежащем оптимизации.';

$arFields['integrations_description']['placeholder'] = 'В случае если реализация функционально-технических требований предполагает интеграцию имеющейся или разрабатываемой системы/сервиса с другими системами, необходимо описать какие именно данные должны передаваться и получаться.';

$arFields['reports_description']['placeholder'] = 'Если по результатам реализации проекта должны формироваться определенные отчеты, необходимо описать примерную структуру и принцип формирования.';
$arFields['reports_description_file']['title'] = '';

$arFields['processed_data_list']['placeholder'] = 'Необходимо указать, будет ли в рамках проекта осуществляться обработка персональных данных. В случае её осуществления необходимо будет указать перечень данных и их владельцев (работники ОИВ, жители)';

$arFields['maximum_unavailability_time']['title'] = 'Какое максимальное время информационная система может
быть недоступна без ущерба для рабочего процесса?';
$arFields['maximum_unavailability_time']['placeholder'] = 'Есть ли время суток, в которое система не используется как пользователями, так и другими системами?';

$arFields['system_service_access']['title'] = 'Варианты доступа к системе/сервису в случае новой разработки';

$arFields['indicators']['placeholder'] = 'Необходимо указать, на достижение каких показателей, содержащихся в указах Президента, национальных целях, программах, проектов, государственных программах, направлена данная проектная инициатива';

// пометка обязательных полей символом *
$arFields = array_map(function ($field) use ($arRequired) {
    if (in_array($field['id'], $arRequired)) {
        $field['title'] .= '<b> *</b>';
    }

    return $field;
}, $arFields);

// добавление подсказки после названия поля 'Описание As-Is'
$arFields['description_as_is']['title'] .= '<br/> (Необходимо привести максимально полное описание текущего процесса с использованием нотации BPMN версии 2.0)';

// сортировка полей для разных пользователей
if (!in_array($arUserFields['ID'], $employees)) {
    $arFields['fz']['sort'] = 30;
    $arFields['user']['sort'] = 45;
    $arFields['project_tasks']['sort'] = 60;
    $arFields['target']['sort'] = 60;
    $arFields['deadline']['sort'] = 65;
    $arFields['risks_impact']['sort'] = 65;
    $arFields['projects_in_other_subjects']['sort'] = 70;
    $arFields['is_financing']['sort'] = 75;
    $arFields['implementation_cost']['sort'] = 80;
    $arFields['planned_ext_experts']['sort'] = 80;
    $arFields['normative_base']['sort'] = 85;
    $arFields['normative_base_file']['sort'] = 90;
    $arFields['audience']['sort'] = 95;

    uasort($arFields, function($a, $b) {
        return $a['sort'] <=> $b['sort'];
    });
}

// сортировка значений в $arFields['system_service_access']
$serviceAccessValues = &$arFields['system_service_access']['values'];

uasort($serviceAccessValues, function ($a, $b) {
  return $a['VALUE'] <=> $b['VALUE'];
});

$sort = 0;
$serviceAccessValues = array_map(function ($item) use (&$sort) {
  $sort += 10;
  $item['SORT'] = $sort;

  if ($item['VALUE'] === 'Другое') {
    $item['SORT'] = 999;
  }

  return $item;
}, $serviceAccessValues);

// показ разных полей для определенных пользователей
$arFields = in_array($arUserFields['ID'], $employees) ?
    array_filter($arFields, function ($item) {
        return !$item['department_field'];
    }) :
    array_filter($arFields, function ($item) {
        return !$item['non_department_field'];
    });
?>

<div id="lists_element_add_form"></div>

<?if (!in_array($arUserFields['ID'], $employees)) :?>
    <a
        href="/upload/project_initiative.7z"
        class="d-block mt-3 text-decoration-none"
        download="Пример заполнения формы.7z"
        >Пример заполнения формы</a>
<?endif;?>

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
