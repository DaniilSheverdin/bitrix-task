<?

use Bitrix\Main\Loader;
use Citto\Integration\Itilium\Sync;
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class CBPItiliumActivity
    extends CBPActivity
    implements IBPEventActivity, IBPActivityExternalEventListener, IBPEventDrivenActivity
{
    private $isInEventActivityMode = false;

    private static $arAllowedTasksFieldNames = [
        // 'TASK_STATUS',
        // 'INCIDENT_STATUS',
        'BUSINESS_SERVICE',
        'BUSINESS_SERVICE_COMPONENT',
        'TECH_SERVICE',
        'TECH_SERVICE_COMPONENT',
        'PARENT',
        'TARGETUSER',
        'DESCRIPTION',
        'SUBJECT',
        'DATE_START',
        'DATE_FINISH',
    ];

    private static $arRequiredTasksFieldNames = [
        'DESCRIPTION',
        'DATE_START',
        'DATE_FINISH',
    ];

    public function __construct($name)
    {
        parent::__construct($name);
        $this->arProperties = [
            'Title'             => '',
            'Fields'            => null,
            'HoldToClose'       => null,
            'CreateTask'        => null,

            'SyncId'            => null,
            'TaskGuid'          => null,
            'IncidentGuid'      => null,
            'Solution'          => null,
            'SolutionUID'       => null,
        ];

        $this->SetPropertiesTypes([
            'HoldToClose'   => ['Type' => 'string'],
            'CreateTask'    => ['Type' => 'string'],
            'SyncId'        => ['Type' => 'string'],
            'TaskGuid'      => ['Type' => 'string'],
            'IncidentGuid'  => ['Type' => 'string'],
            'Solution'      => ['Type' => 'string'],
            'SolutionUID' => ['Type' => 'string'],
        ]);
    }

    protected function ReInitialize()
    {
        parent::ReInitialize();

        $this->SyncId = null;
        $this->TaskGuid = null;
        $this->IncidentGuid = null;
        $this->Solution = null;
        $this->SolutionUID = null;
    }

    public function Cancel()
    {
        if (!$this->isInEventActivityMode && $this->HoldToClose == 'Y') {
            $this->Unsubscribe($this);
        }

        return CBPActivityExecutionStatus::Closed;
    }

    public function Execute()
    {
        if ($this->isInEventActivityMode) {
            return CBPActivityExecutionStatus::Closed;
        }

        if (!$this->createTask()) {
            return CBPActivityExecutionStatus::Closed;
        }

        if ($this->HoldToClose != 'Y' && $this->SyncId > 0) {
            $obSync = new Sync();
            $arSync = $obSync->getById($this->SyncId);
            if (!empty($arSync['UF_INCIDENT_GUID'])) {
                $this->TaskGuid = $arSync['UF_TASK_GUID']??false;
                $this->IncidentGuid = $arSync['UF_INCIDENT_GUID'];

                if (
                    ($this->Fields['CREATE_TASK'] == 'Y' && !is_null($this->TaskGuid)) ||
                    $this->Fields['CREATE_TASK'] != 'Y'
                ) {
                    return CBPActivityExecutionStatus::Closed;
                }
            } else {
                $this->WriteToTrackingService('Ожидание синхронизации с Itilium');
            }
        }

        $this->Subscribe($this);
        $this->isInEventActivityMode = false;

        return CBPActivityExecutionStatus::Executing;
    }

    private function createTask()
    {
        $rootActivity = $this->GetRootActivity();
        $documentId = $rootActivity->GetDocumentId();
        $fields = $this->Fields;
        $result = false;
        $task = null;
        $errors = [];
        try {
            Loader::includeModule('citto.integration');
            $obSync = new Sync();
            $activity = $this->workflow->GetActivityByName($this->name);
            $iblockId = str_replace('iblock_', '', $rootActivity->getDocumentType()[2]);
            $elementId = $rootActivity->getDocumentId()[2];

            $author = (int)CBPHelper::ExtractUsers($fields["TARGETUSER"], $documentId, true);

            $logger = new Logger('default');
            $logger->pushHandler(
                new RotatingFileHandler(
                    $_SERVER['DOCUMENT_ROOT'] . '/local/logs/debug/24032022.log',
                    90
                )
            );

            if ($author <= 0 || $author == NULL) {
                Bitrix\Main\Loader::includeModule('iblock');
                $arElement = \CIBlockElement::GetByID($elementId)->Fetch();
                $author = $arElement['CREATED_BY'];
            }

            $fields['AUTHOR'] = $author;

            $arSyncFields = [
                'UF_TYPE'           => 'BIZPROC',
                'UF_IBLOCK'         => $iblockId,
                'UF_ELEMENT'        => $elementId,
                'UF_WORKFLOW_ID'    => $this->GetWorkflowInstanceId(),
                'UF_ACTIVITY_NAME'  => $this->name,
                'UF_SOURCE'         => $fields,
            ];
            $result = $obSync->start($arSyncFields);

        } catch (Exception $e) {
            $errors[] = $e->getMessage();
        }

        $arSync = [];

        if (!$result) {
            if (count($errors) > 0) {
                $this->WriteToTrackingService(
                    GetMessage('BPSA_TRACK_ERROR') . implode(', ', $errors),
                    0,
                    CBPTrackingType::Error
                );
            }

            return false;
        } else {
            $obSync = new Sync();
            $arSync = $obSync->getById($result);
            if (!empty($arSync['UF_INCIDENT_GUID'])) {
                $this->TaskGuid = $arSync['UF_TASK_GUID']??false;
                $this->IncidentGuid = $arSync['UF_INCIDENT_GUID'];
                $this->WriteToTrackingService('Инцидент синхронизирован: ' . $arSync['UF_INCIDENT_GUID']);
                if ($this->TaskGuid) {
                    $this->WriteToTrackingService('Задача синхронизирована: ' . $this->TaskGuid);
                }
            }
        }

        $this->SyncId = $result;
        if ($this->HoldToClose != 'Y') {
            if (!empty($arSync['UF_INCIDENT_GUID'])) {
                if (
                    ($this->Fields['CREATE_TASK'] == 'Y' && !is_null($this->TaskGuid)) ||
                    $this->Fields['CREATE_TASK'] != 'Y'
                ) {
                    $this->WriteToTrackingService('Синхронизация успешно произведена');
                } elseif ($this->Fields['CREATE_TASK'] == 'Y' && is_null($this->TaskGuid)) {
                    $this->WriteToTrackingService('Ожидание синхронизации задачи');
                }
            }
        }

        return !empty($result);
    }

    public function Subscribe(IBPActivityExternalEventListener $eventHandler)
    {
        $this->isInEventActivityMode = true;

        if ($eventHandler instanceof CBPListenEventActivitySubscriber) {
            $result = $this->createTask();
            if (!$result) {
                return false;
            }
        }

        $syncId = $this->SyncId;
        if (empty($syncId)) {
            return false;
        }

        $schedulerService = $this->workflow->GetService('SchedulerService');
        $schedulerService->SubscribeOnEvent($this->workflow->GetInstanceId(), $this->name, 'citto.integration', 'OnItiliumTaskUpdate', $syncId);

        $this->workflow->AddEventHandler($this->name, $eventHandler);
        if ($this->HoldToClose == 'Y') {
            $this->WriteToTrackingService(GetMessage('BPSA_TRACK_SUBSCR'));
        }
    }

    public function Unsubscribe(IBPActivityExternalEventListener $eventHandler)
    {
        $syncId = $this->SyncId;

        $schedulerService = $this->workflow->GetService('SchedulerService');
        $schedulerService->UnSubscribeOnEvent($this->workflow->GetInstanceId(), $this->name, 'citto.integration', 'OnItiliumTaskUpdate', $syncId);

        //delete invalid subscriptions
        $schedulerService->UnSubscribeOnEvent($this->workflow->GetInstanceId(), $this->name, 'citto.integration', 'OnItiliumTaskUpdate', null);

        $this->workflow->RemoveEventHandler($this->name, $eventHandler);
    }

    public function OnExternalEvent($arEventParameters = [])
    {
        if ($this->onExternalEventHandler($arEventParameters)) {
            $this->Unsubscribe($this);
            $this->workflow->CloseActivity($this);
        }

        return;
    }

    public function OnExternalDrivenEvent($arEventParameters = [])
    {
        return $this->onExternalEventHandler($arEventParameters);
    }

    private function onExternalEventHandler($arEventParameters = [])
    {
        if ($this->SyncId != $arEventParameters[0]) {
            return;
        }

        $return = false;
        if ($this->executionStatus != CBPActivityExecutionStatus::Closed) {
            $obSync = new Sync();
            $arSync = $obSync->getById($this->SyncId);

            if ($this->HoldToClose != 'Y') {
                if (is_null($this->TaskGuid) && !empty($arSync['UF_TASK_GUID'])) {
                    $this->TaskGuid = $arSync['UF_TASK_GUID'];
                    $this->WriteToTrackingService('Задача синхронизирована: ' . $arSync['UF_TASK_GUID']);
                }

                if (is_null($this->IncidentGuid) && !empty($arSync['UF_INCIDENT_GUID'])) {
                    $this->IncidentGuid = $arSync['UF_INCIDENT_GUID'];
                    $this->WriteToTrackingService('Инцидент синхронизирован: ' . $arSync['UF_INCIDENT_GUID']);

                    if (
                        ($this->Fields['CREATE_TASK'] == 'Y' && !is_null($this->TaskGuid)) ||
                        $this->Fields['CREATE_TASK'] != 'Y'
                    ) {
                        $return = true;
                    }
                }
            }

            if (is_null($this->TaskGuid) && !empty($arSync['UF_TASK_GUID'])) {
                $this->TaskGuid = $arSync['UF_TASK_GUID'];
            }
            if (is_null($this->IncidentGuid) && !empty($arSync['UF_INCIDENT_GUID'])) {
                $this->IncidentGuid = $arSync['UF_INCIDENT_GUID'];
            }

            $arItiliumData = $arEventParameters[1] ?? [];

            if (
                !empty($arSync['UF_TASK_GUID']) &&
                !empty($arItiliumData) &&
                !empty($arItiliumData['Status']) &&
                $arItiliumData['UID'] == $arSync['UF_TASK_GUID']
            ) {
                if (!empty($arItiliumData['Solution'])) {
                    $this->Solution = $arItiliumData['Solution'];
                    $this->SolutionUID = $arItiliumData['QuickSolution']['UID'];
                    $this->WriteToTrackingService('Решение по задаче ' . $arSync['UF_TASK_GUID']. ': "' . $arItiliumData['Solution'] . ' (' . $arItiliumData['QuickSolution']['UID'] . ')"');
                }

                $this->WriteToTrackingService('Статус задачи ' . $arSync['UF_TASK_GUID']. ' изменён на "' . $arItiliumData['Status']['Name'] . '"');
                if (false != $arItiliumData['Status']['Final']) {
                    $return = true;
                    $this->WriteToTrackingService('Задача ' . $arSync['UF_TASK_GUID']. ' завершена');
                }
            }
        }

        if ($return) {
            $arUpdate = [
                'UF_DATE_SYNC'      => date('d.m.Y H:i:s'),
                'UF_DATE_UPDATE'    => date('d.m.Y H:i:s'),
                'UF_STOP_SYNC'      => 'Y',
            ];
            $obSync->entityDataClass::update($this->SyncId, $arUpdate);

            return true;
        }
    }

    public function HandleFault(Exception $exception)
    {
        $status = $this->Cancel();
        if ($status == CBPActivityExecutionStatus::Canceling) {
            return CBPActivityExecutionStatus::Faulting;
        }

        return $status;
    }

    public static function GetPropertiesDialog(
        $documentType,
        $activityName,
        $arWorkflowTemplate,
        $arWorkflowParameters,
        $arWorkflowVariables,
        $arCurrentValues = null,
        $formName = '',
        $popupWindow = null,
        $currentSiteId = null
    ) {
        if (!is_array($arWorkflowParameters)) {
            $arWorkflowParameters = [];
        }
        if (!is_array($arWorkflowVariables)) {
            $arWorkflowVariables = [];
        }

        $rawValues = [];

        if (!is_array($arCurrentValues)) {
            $arCurrentValues = [];

            $arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
            if (
                is_array($arCurrentActivity['Properties'])
                && array_key_exists('Fields', $arCurrentActivity['Properties'])
                && is_array($arCurrentActivity['Properties']['Fields'])
            ) {
                foreach ($arCurrentActivity['Properties']['Fields'] as $k => $v) {
                    $arCurrentValues[$k] = $v;

                    if (in_array($k, array("TARGETUSER")))
                    {
                        if (!is_array($arCurrentValues[$k]))
                            $arCurrentValues[$k] = array($arCurrentValues[$k]);

                        $ar = (array) $arCurrentValues[$k];
                        $rawValues[$k] = $ar;
                        $arCurrentValues[$k] = CBPHelper::UsersArrayToString($ar, $arWorkflowTemplate, $documentType);
                    }
                }
            }

            $arCurrentValues['HOLD_TO_CLOSE'] = ($arCurrentActivity['Properties']['HoldToClose'] == 'Y' ? 'Y' : 'N');
            $arCurrentValues['CREATE_TASK'] = ($arCurrentActivity['Properties']['CreateTask'] == 'Y' ? 'Y' : 'N');
        }

        $dialog = new \Bitrix\Bizproc\Activity\PropertiesDialog(__FILE__, [
            'documentType' => $documentType,
            'activityName' => $activityName,
            'workflowTemplate' => $arWorkflowTemplate,
            'workflowParameters' => $arWorkflowParameters,
            'workflowVariables' => $arWorkflowVariables,
            'currentValues' => $arCurrentValues,
            'formName' => $formName,
            'siteId' => $currentSiteId
        ]);

        $dialog->setRuntimeData([
            'formName' => $formName,
            'documentType' => $documentType,
            'popupWindow' => &$popupWindow,
            'arDocumentFields' => self::__GetFields(),
            'currentSiteId' => $currentSiteId,
            'rawValues' => $rawValues
        ]);

        return $dialog;
    }

    public static function GetPropertiesDialogValues(
        $documentType,
        $activityName,
        &$arWorkflowTemplate,
        &$arWorkflowParameters,
        &$arWorkflowVariables,
        $arCurrentValues,
        &$errors
    ) {
        $errors = [];
        $properties = ['Fields' => []];

        $arDF = self::__GetFields();

        $arRequired = self::$arRequiredTasksFieldNames;

        if (mb_strtoupper($arCurrentValues['CREATE_TASK']) == 'Y') {
            $arRequired[] = 'SUBJECT';
        }

        foreach (static::$arAllowedTasksFieldNames as $field) {
            $r = null;

            if (in_array($field, array("TARGETUSER")))
            {
                $value = $arCurrentValues[$field];
                if ($value <> '')
                {
                    $arErrorsTmp = array();
                    $r = CBPHelper::UsersStringToArray($value, $documentType, $arErrorsTmp);
                    if (count($arErrorsTmp) > 0)
                    {
                        $errors = array_merge($errors, $arErrorsTmp);
                    }
                }
            } elseif (array_key_exists($field, $arCurrentValues) || array_key_exists($field.'_text', $arCurrentValues)) {
                $arValue = array();
                if (array_key_exists($field, $arCurrentValues)) {
                    $arValue = $arCurrentValues[$field];
                    if (!is_array($arValue) || is_array($arValue) && CBPHelper::IsAssociativeArray($arValue)) {
                        $arValue = array($arValue);
                    }
                }
                if (array_key_exists($field.'_text', $arCurrentValues)) {
                    $arValue[] = $arCurrentValues[$field.'_text'];
                }

                foreach ($arValue as $value) {
                    if (!CBPDocument::IsExpression($value)) {
                        if (!is_array($value) && $value == '') {
                            $value = null;
                        }
                    }

                    if ($value != null) {
                        $r[] = $value;
                    }
                }
            }

            $r_orig = $r;

            if (!in_array($field, ['TASK_STATUS', 'INCIDENT_STATUS'])) {
                if ($r && count($r) > 0) {
                    $r = $r[0];
                } else {
                    $r = null;
                }
            }
            if (
                in_array($field, $arRequired) &&
                ($r == null || is_array($r) && count($r) <= 0)
            ) {
                $errors[] = array(
                    'code' => 'emptyRequiredField',
                    'message' => str_replace('#FIELD#', $arDF[$field]['Name'], GetMessage('BPCDA_FIELD_REQUIED')),
                );
            }

            $properties['Fields'][$field] = $r;

            if (array_key_exists($field.'_text', $arCurrentValues) && isset($r_orig[1])) {
                $properties['Fields'][$field . '_text'] = $r_orig[1];
            }
        }

        $properties['HoldToClose'] = ((mb_strtoupper($arCurrentValues['HOLD_TO_CLOSE']) == 'Y') ? 'Y' : 'N');
        $properties['CreateTask'] = ((mb_strtoupper($arCurrentValues['CREATE_TASK']) == 'Y') ? 'Y' : 'N');

        $properties['Fields']['HOLD_TO_CLOSE'] = $properties['HoldToClose'];
        $properties['Fields']['CREATE_TASK'] = $properties['CreateTask'];

        $currentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
        $currentActivity['Properties'] = $properties;

        return true;
    }

    private static function __GetFields()
    {
        $arFields = [
            'PARENT'        => [
                'Name'      => 'GUID Основного инцидента',
                'Type'      => 'S',
                'Editable'  => true,
                'Required'  => false,
                'Multiple'  => false,
                'BaseType'  => 'string'
            ],
            'TARGETUSER'        => [
                'Name'      => 'Автор инцидента',
                'Type'      => 'S:UserID',
                'Filterable' => true,
                'Editable'  => true,
                'Required'  => false,
                'Multiple'  => false,
                'BaseType'  => 'user'
            ],
            'SUBJECT'   => [
                'Name'      => 'Заголовок задачи (если создавать)',
                'Type'      => 'S',
                'Editable'  => true,
                'Required'  => true,
                'Multiple'  => false,
                'BaseType'  => 'string'
            ],
            'DESCRIPTION'   => [
                'Name'      => 'Описание задачи',
                'Type'      => 'S',
                'Editable'  => true,
                'Required'  => true,
                'Multiple'  => false,
                'BaseType'  => 'string'
            ],
            'DATE_START'    => array(
                'Name'      => 'Дата начала',
                'Type'      => 'S:DateTime',
                'Editable'  => true,
                'Required'  => true,
                'Multiple'  => false,
                'BaseType'  => 'datetime'
            ),
            'DATE_FINISH'   => array(
                'Name'      => 'Дата завершения',
                'Type'      => 'S:DateTime',
                'Editable'  => true,
                'Required'  => true,
                'Multiple'  => false,
                'BaseType'  => 'datetime'
            ),
        ];

        return $arFields;
    }
}
