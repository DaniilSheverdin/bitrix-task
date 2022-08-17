<?
define('NEED_AUTH', true);

include $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

global $userFields, $USER;
$resp = (object)[
    'status'            => 'ERROR',
    'status_message'    => '',
    'data'              => (object)[],
    'alert'             => '',
];

$department = 100;
$group_leader_id = 107;

try {
    \Bitrix\Main\Loader::includeModule('iblock');
    $REQUEST = json_decode(file_get_contents('php://input'), true);
    $SOTRUDNIK = $userFields($USER->GetID());
    $IBLOCK_ID = $REQUEST['iblock_id'] ?? null;
    $FZ = $REQUEST['fz'] ?? null;
    $EXECUTIVE_LEADER = $REQUEST['executive_leader'] ?? null;
    $USER = $REQUEST['user'] ?? null;
    $TARGET = $REQUEST['target'] ?? null;
    $PROJECT_TYPE = $REQUEST['project_type'] ?? null;
    $PROJECT_REQUEST_TYPE = $REQUEST['project_request_type'] ?? null;
    $DEADLINE = $REQUEST['deadline'] ?? null;
    $RISKS_IMPACT = $REQUEST['risks_impact'] ?? null;
    $PROJECTS_IN_OTHER_SUBJECTS = $REQUEST['projects_in_other_subjects'] ?? null;
    $IS_FINANCING = $REQUEST['is_financing'] ?? null;
    $PROCESSED_DATA_LIST = $REQUEST['processed_data_list'] ?? null;
    $MAXIMUM_UNAVAILABILITY_TIME = $REQUEST['maximum_unavailability_time'] ?? null;
    $PROJECT_TASKS = $REQUEST['project_tasks'] ?? null;
    $NORMATIVE_BASE = $REQUEST['normative_base'] ?? null;
    $AUDIENCE = $REQUEST['audience'] ?? null;
    $PROJECT_INIT_KPI = $REQUEST['project_init_kpi'] ?? null;
    $DESCRIPTION_AS_IS = $REQUEST['description_as_is'] ?? null;
    $DESCRIPTION_TO_BE = $REQUEST['optimized_process_description_to_be'] ?? null;
    $DESCRIPTION_TO_BE_FILE = $REQUEST['optimized_process_description_to_be_file'] ?? null;
    $INDICATORS = $REQUEST['indicators'] ?? null;

    if ($REQUEST['sessid'] != bitrix_sessid()) {
        throw new Exception('Ошибка. Обновите страницу');
    }

    if (empty($IBLOCK_ID)) {
        throw new Exception('IBLOCK_ID не найден');
    }

    if ($SOTRUDNIK['ID'] != $group_leader_id) {
        if (empty($TARGET)) {
            throw new Exception('Заполните поле \'Цель\'');
        }
    }

    if (empty($INDICATORS)) {
        throw new Exception('Заполните поле \'Показатели\'');
    }

    if (empty($PROJECT_TYPE)) {
        throw new Exception('Заполните поле \'Вид проекта\'');
    }

    if (empty($PROJECT_REQUEST_TYPE)) {
        throw new Exception('Заполните поле \'Тип проектной заявки\'');
    }

    if (empty($DEADLINE)) {
        throw new Exception('Заполните поле \'Крайний срок\'');
    }

    if (empty($RISKS_IMPACT)) {
        throw new Exception('Заполните поле \'Риски и последствия\'');
    }

    if (empty($PROJECTS_IN_OTHER_SUBJECTS)) {
        throw new Exception('Заполните поле \'Наличие проектов в других субъектах Российской Федерации\'');
    }

    if (empty($IS_FINANCING)) {
        throw new Exception('Заполните поле \'Наличие финансирования и источники финансирования\'');
    }

    if (empty($PROCESSED_DATA_LIST)) {
        throw new Exception('Заполните поле \'Перечень обрабатываемых данных\'');
    }

    if (empty($MAXIMUM_UNAVAILABILITY_TIME)) {
        throw new Exception('Заполните поле \'Какое максимальное время информационная система может быть недоступна без ущерба для рабочего процесса?\'');
    }

    if (
        !in_array($department, $SOTRUDNIK['UF_DEPARTMENT']) ||
        $SOTRUDNIK['ID'] == 2748 // Елена Боргер
    ) {
        if (empty($FZ)) {
            throw new Exception('Заполните поле \'Функциональный заказчик\'');
        }

        if (empty($EXECUTIVE_LEADER)) {
            throw new Exception('Заполните поле \'Руководитель ОИВ, ответственный за цифровую трансформацию\'');
        }

        if (empty($USER)) {
            throw new Exception('Заполните поле \'Контакты ответственных сотрудников со стороны ФЗ\'');
        }

        if (empty($PROJECT_TASKS)) {
            throw new Exception('Заполните поле \'Задачи проекта\'');
        }

        if (empty($NORMATIVE_BASE)) {
            throw new Exception('Заполните поле \'Нормативная база\'');
        }

        if (empty($AUDIENCE)) {
            throw new Exception('Заполните поле \'Целевая аудитория\'');
        }

        if (empty($PROJECT_INIT_KPI)) {
            throw new Exception('Заполните поле \'KPI проектной инициативы\'');
        }

        if (empty($DESCRIPTION_AS_IS)) {
            throw new Exception('Заполните поле \'Описание текущего процесса (As-Is)\'');
        }

        if (empty($DESCRIPTION_TO_BE) && empty($DESCRIPTION_TO_BE_FILE)) {
            throw new Exception('Заполните поля \'Описание оптимизированного процесса (To-Be)\' и \'Описание оптимизированного процесса (To-Be) - файл\', либо одно из них');
        }
    }

    $resp->data->fields = [];
    $resp->status       = 'OK';
} catch (Exception $exc) {
    $resp->status_message = $exc->getMessage();
}
header('Content-Type: application/json');
die(json_encode($resp));
