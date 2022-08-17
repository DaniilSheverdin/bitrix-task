<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}

class CBPDocsignActivity extends CBPActivity implements IBPEventActivity, IBPActivityExternalEventListener
{

    private $taskId = 0;
    private $taskUsers = array();
    private $subscriptionId = 0;
    private $isInEventActivityMode = false;
    private $taskStatus = false;

    public function __construct($name)
    {
        parent::__construct($name);
        $this->arProperties = array(
            "Title" => "",
            "Users" => null,
            "Name" => null,
            "Props" => null,
        );
        $this->SetPropertiesTypes(array(
            'TaskId' => ['Type' => 'int'],
        ));
    }

    protected function ReInitialize()
    {
        parent::ReInitialize();
        $this->TaskId = 0;
        $this->Comments = '';
        $this->InfoUser = null;
        $this->IsTimeout = 0;
        $this->Changes = array();
    }

    private static function getActivity($WORKFLOW_ID, $ACTIVITY_NAME)
    {
        if (!$WORKFLOW_ID) {
            throw new Exception("БП не указан");
        }
        $runtime = CBPRuntime::GetRuntime();
        $workflow = $runtime->GetWorkflow($WORKFLOW_ID, true);
        $activity = $workflow->GetActivityByName($ACTIVITY_NAME);
        return $activity;
    }

    private static function getFileSign($file_id)
    {
        $res = $GLOBALS['DB']->Query('SELECT ID FROM b_file WHERE EXTERNAL_ID="'.$GLOBALS['DB']->ForSql(("SIGN_".$GLOBALS['USER']->GetID()."_".$file_id)).'"')->Fetch();
        return isset($res['ID'])?$res['ID']:null;
    }

    private static function getWorkflowFiles($WORKFLOW_ID, $ACTIVITY_NAME)
    {
        \Bitrix\Main\Loader::includeModule('workflow');

        $files = [];
        $activity = self::getActivity($WORKFLOW_ID, $ACTIVITY_NAME);

        // $props = $activity->Props;
        // if (!is_array($props)){
            // $props = array_filter(explode(",",$props));
        // }

        $props = array_filter(explode(",", str_replace(['{=Variable:','}'], ['',''], $activity->arProperties['Props'])));

        foreach ($props as $prop) {
            $var = $activity->getVariable($prop);
            if (empty($var)) {
                continue;
            }

            if (!is_array($var)) {
                $var = [$var];
            }
            $var = array_values($var);

            foreach ($var as $var_indx => $var_item) {
                $file = CFile::GetFileArray(trim($var_item));

                $source_path = "";
                if ($file['DESCRIPTION'] && mb_substr($file['DESCRIPTION'], 0, 7) == "source-") {
                    $source_path = CFile::GetPath(mb_substr($file['DESCRIPTION'], 7));
                }
                $files[$prop][$var_indx] = [
                    'url'           => $file['SRC'],
                    'name'          => $file['ORIGINAL_NAME'],
                    'id'            => (int)$file['ID'],
                    'signed'        => (bool)self::getFileSign($file['ID']),
                    'path'          => $_SERVER['DOCUMENT_ROOT'].$file['SRC'],
                    'source_path'   => $_SERVER['DOCUMENT_ROOT'].$source_path,
                    'extension'     => pathinfo($_SERVER['DOCUMENT_ROOT'].$file['SRC'], PATHINFO_EXTENSION)
                ];
            }
        }
        return $files;
    }

    public static function ShowTaskForm($arTask, $userId, $userName = "")
    {
        $form = "";
        CJSCore::Init(['jquery']);
        $GLOBALS['APPLICATION']->SetAdditionalCss('/local/activities/custom/docsignactivity/css/docsignactivity.css');
        $GLOBALS['APPLICATION']->AddHeadScript('/local/activities/custom/docsignactivity/js/es6-promise.min.js');
        $GLOBALS['APPLICATION']->AddHeadScript('/local/activities/custom/docsignactivity/js/ie_eventlistner_polyfill.js');
        $GLOBALS['APPLICATION']->AddHeadScript('/local/activities/custom/docsignactivity/js/cadesplugin_api.js');
        $GLOBALS['APPLICATION']->AddHeadScript('/local/activities/custom/docsignactivity/js/plugin.js');
        $GLOBALS['APPLICATION']->AddHeadScript('/local/activities/custom/docsignactivity/js/docsignactivity.js');

        $files = self::getWorkflowFiles($arTask['WORKFLOW_ID'], $arTask['ACTIVITY_NAME']);

        include __DIR__."/ShowTaskForm.php";


        return [$form, '<input type="submit" name="docsign-submit" value="Продолжить"/>'];
    }

    public static function getTaskControls($arTask)
    {
        return array(
            'BUTTONS' => array(
                array(
                    'TYPE'  => 'submit',
                    'TARGET_USER_STATUS' => CBPTaskUserStatus::Ok,
                    'NAME'  => 'docsign-submit',
                    'VALUE' => 'Y',
                    'TEXT'  => "Продолжить"
                )
            )
        );
    }

    public static function PostTaskForm($arTask, $userId, $arRequest, &$arErrors, $userName = "", $realUserId = null)
    {
        $arErrors = array();
        try {
            $files = self::getWorkflowFiles($arTask['WORKFLOW_ID'], $arTask['ACTIVITY_NAME']);

            foreach ($files as $prop_files) {
                foreach ($prop_files as $file) {
                    if (!$file['signed']) {
                        throw new CBPArgumentNullException("Не подписан файл:".$file['name']);
                    }
                }
            }

            $userId = intval($userId);
            if ($userId <= 0) {
                throw new CBPArgumentNullException("userId");
            }

            $arEventParameters = array(
                "USER_ID" => $userId,
                "REAL_USER_ID" => $realUserId,
                "USER_NAME" => $userName,
                "COMMENT" => isset($arRequest["task_comment"]) ? trim($arRequest["task_comment"]) : '',
            );

            if (isset($arRequest['INLINE_USER_STATUS']) && $arRequest['INLINE_USER_STATUS'] != CBPTaskUserStatus::Ok) {
                throw new CBPNotSupportedException(("BPAA_ACT_NO_ACTION"));
            }

            CBPRuntime::SendExternalEvent($arTask["WORKFLOW_ID"], $arTask["ACTIVITY_NAME"], $arEventParameters);
            return true;
        } catch (Exception $e) {
            $arErrors[] = array(
                "code" => $e->getCode(),
                "message" => $e->getMessage(),
                "file" => $e->getFile()." [".$e->getLine()."]",
            );
        }

        return false;
    }

    public static function ValidateProperties($arTestProperties = array(), CBPWorkflowTemplateUser $user = null)
    {
        $arErrors = array();

        if (!array_key_exists("Users", $arTestProperties)) {
            $bUsersFieldEmpty = true;
        } else {
            if (!is_array($arTestProperties["Users"])) {
                $arTestProperties["Users"] = array($arTestProperties["Users"]);
            }

            $bUsersFieldEmpty = true;
            foreach ($arTestProperties["Users"] as $userId) {
                if (!is_array($userId) && (trim($userId) != '') || is_array($userId) && (count($userId) > 0)) {
                    $bUsersFieldEmpty = false;
                    break;
                }
            }
        }

        if ($bUsersFieldEmpty) {
            $arErrors[] = array("code" => "NotExist", "parameter" => "Users", "message" => "Need Users");
        }

        if (!array_key_exists("Name", $arTestProperties) || mb_strlen($arTestProperties["Name"]) <= 0) {
            $arErrors[] = array("code" => "NotExist", "parameter" => "Name", "message" => "Need Name");
        }

        return array_merge($arErrors, parent::ValidateProperties($arTestProperties, $user));
    }

    public static function GetPropertiesDialog(
        $documentType,
        $activityName,
        $arWorkflowTemplate,
        $arWorkflowParameters,
        $arWorkflowVariables,
        $arCurrentValues = null,
        $formName = ""
    ) {
        $runtime = CBPRuntime::GetRuntime();

        $arMap = array(
            "Users" => "docsign_users",
            "Props" => "docsign_props",
            "Name" => "docsign_name",
        );

        if (!is_array($arWorkflowParameters)) {
            $arWorkflowParameters = array();
        }
        if (!is_array($arWorkflowVariables)) {
            $arWorkflowVariables = array();
        }

        if (!is_array($arCurrentValues)) {
            $arCurrentValues = [];
            $arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
            if (is_array($arCurrentActivity["Properties"])) {
                foreach ($arMap as $k => $v) {
                    if (array_key_exists($k, $arCurrentActivity["Properties"])) {
                        if ($k == "Users") {
                            $arCurrentValues[$arMap[$k]] = CBPHelper::UsersArrayToString($arCurrentActivity["Properties"][$k], $arWorkflowTemplate, $documentType);
                        } else {
                            $arCurrentValues[$arMap[$k]] = $arCurrentActivity["Properties"][$k];
                        }
                    } else {
                        if (!is_array($arCurrentValues) || !array_key_exists($arMap[$k], $arCurrentValues)) {
                            $arCurrentValues[$arMap[$k]] = "";
                        }
                    }
                }
            } else {
                foreach ($arMap as $k => $v) {
                    $arCurrentValues[$arMap[$k]] = "";
                }
            }
        }

        $documentService = $runtime->GetService("DocumentService");
        $arDocumentFields = $documentService->GetDocumentFields($documentType);

        return $runtime->ExecuteResourceFile(
            __FILE__,
            "properties_dialog.php",
            array(
                "arCurrentValues" => $arCurrentValues,
                "arDocumentFields" => $arDocumentFields,
                "formName" => $formName,
            )
        );
    }

    public static function GetPropertiesDialogValues(
        $documentType,
        $activityName,
        &$arWorkflowTemplate,
        &$arWorkflowParameters,
        &$arWorkflowVariables,
        $arCurrentValues,
        &$arErrors
    ) {
        $arErrors = array();

        $runtime = CBPRuntime::GetRuntime();

        $arMap = array(
            "docsign_users" => "Users",
            "docsign_props" => "Props",
            "docsign_name" => "Name",
        );

        $arProperties = array();
        foreach ($arMap as $key => $value) {
            if ($key == "docsign_users") {
                continue;
            }

            if ($arCurrentValues[$key."_X"] != '') {
                $arProperties[$value] = $arCurrentValues[$key."_X"];
            } else {
                $arProperties[$value] = $arCurrentValues[$key];
            }
        }

        $arProperties["Users"] = CBPHelper::UsersStringToArray($arCurrentValues["docsign_users"], $documentType, $arErrors);
        if (count($arErrors) > 0) {
            return false;
        }

        $arErrors = self::ValidateProperties($arProperties, new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser));
        if (count($arErrors) > 0) {
            return false;
        }

        $arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
        $arCurrentActivity["Properties"] = $arProperties;

        return true;
    }

    public function Execute()
    {
        if ($this->isInEventActivityMode) {
            return CBPActivityExecutionStatus::Closed;
        }

        $this->Subscribe($this);

        $this->isInEventActivityMode = false;
        return CBPActivityExecutionStatus::Executing;
    }

    public function Subscribe(IBPActivityExternalEventListener $eventHandler)
    {
        if ($eventHandler == null) {
            throw new Exception("eventHandler");
        }

        $this->isInEventActivityMode = true;

        $arUsersTmp = $this->Users;
        if (!is_array($arUsersTmp)) {
            $arUsersTmp = array($arUsersTmp);
        }

        $rootActivity = $this->GetRootActivity();
        $documentId = $rootActivity->GetDocumentId();

        $arUsers = CBPHelper::ExtractUsers($arUsersTmp, $documentId, false);

        $arParameters = $this->Parameters;
        if (!is_array($arParameters)) {
            $arParameters = array($arParameters);
        }

        $runtime = CBPRuntime::GetRuntime();
        $documentService = $runtime->GetService("DocumentService");

        $arParameters["DOCUMENT_ID"]        = $documentId;
        $arParameters["DOCUMENT_URL"]       = $documentService->GetDocumentAdminPage($documentId);
        $arParameters["DOCUMENT_TYPE"]      = $this->GetDocumentType();
        $arParameters["FIELD_TYPES"]        = $documentService->GetDocumentFieldTypes($arParameters["DOCUMENT_TYPE"]);
        $arParameters["REQUEST"]            = array();
        $arParameters["TaskButtonMessage"]  = "BPRIA_ACT_BUTTON1";
        $arParameters["CommentLabelMessage"]= "BPRIA_ACT_COMMENT";
        $arParameters["ShowComment"]        = "N";
        $arParameters["CommentRequired"]    = $this->IsPropertyExists("CommentRequired") ? $this->CommentRequired : "N";
        $arParameters["AccessControl"]      = $this->IsPropertyExists("AccessControl") && $this->AccessControl == 'Y' ? 'Y' : 'N';

        $overdueDate = $this->OverdueDate;
        $overdueDate = ConvertTimeStamp(time() + 60*60*24*30*12*10, "FULL");

        /** @var CBPTaskService $taskService */
        $taskService = $this->workflow->GetService("TaskService");
        $this->taskId = $taskService->CreateTask(
            array(
                "USERS"             => $arUsers,
                "WORKFLOW_ID"       => $this->GetWorkflowInstanceId(),
                "ACTIVITY"          => "DocsignActivity",
                "ACTIVITY_NAME"     => $this->name,
                "OVERDUE_DATE"      => $overdueDate,
                "NAME"              => $this->Name,
                "DESCRIPTION"       => $this->Description,
                "PARAMETERS"        => $arParameters,
                'DELEGATION_TYPE'   => (int)$this->DelegationType,
                'DOCUMENT_NAME'     => $documentService->GetDocumentName($documentId)
            )
        );
        $this->TaskId = $this->taskId;
        $this->taskUsers = $arUsers;

        $this->workflow->AddEventHandler($this->name, $eventHandler);
    }

    public function Unsubscribe(IBPActivityExternalEventListener $eventHandler)
    {
        if ($eventHandler == null) {
            throw new Exception("eventHandler");
        }

        $taskService = $this->workflow->GetService("TaskService");
        if ($this->taskStatus === false) {
            $taskService->DeleteTask($this->taskId);
        } else {
            $taskService->Update($this->taskId, array(
                'STATUS' => $this->taskStatus
            ));
        }


        $this->workflow->RemoveEventHandler($this->name, $eventHandler);

        $this->taskId = 0;
        $this->taskUsers = array();
        $this->taskStatus = false;
        $this->subscriptionId = 0;
    }

    public function HandleFault(Exception $exception)
    {
        if ($exception == null) {
            throw new Exception("exception");
        }

        $status = $this->Cancel();
        if ($status == CBPActivityExecutionStatus::Canceling) {
            return CBPActivityExecutionStatus::Faulting;
        }

        return $status;
    }

    public function Cancel()
    {
        if (!$this->isInEventActivityMode && $this->taskId > 0) {
            $this->Unsubscribe($this);
        }

        return CBPActivityExecutionStatus::Closed;
    }

    public function OnExternalEvent($eventParameters = array())
    {
        if ($this->executionStatus == CBPActivityExecutionStatus::Closed) {
            return;
        }

        if (!array_key_exists("USER_ID", $eventParameters) || intval($eventParameters["USER_ID"]) <= 0) {
            return;
        }

        if (empty($eventParameters["REAL_USER_ID"])) {
            $eventParameters["REAL_USER_ID"] = $eventParameters["USER_ID"];
        }

        $rootActivity = $this->GetRootActivity();
        $arUsers = $this->taskUsers;
        if (empty($arUsers)) {
            $arUsers = CBPHelper::ExtractUsers($this->Users, $this->GetDocumentId(), false);
        }

        $eventParameters["USER_ID"] = intval($eventParameters["USER_ID"]);
        $eventParameters["REAL_USER_ID"] = intval($eventParameters["REAL_USER_ID"]);
        if (!in_array($eventParameters["USER_ID"], $arUsers)) {
            return;
        }

        $this->Comments = $eventParameters["COMMENT"];

        if ($this->IsPropertyExists("InfoUser")) {
            $this->InfoUser = "user_".$eventParameters["REAL_USER_ID"];
        }

        $taskService = $this->workflow->GetService("TaskService");
        $taskService->MarkCompleted($this->taskId, $eventParameters["REAL_USER_ID"], CBPTaskUserStatus::Ok);

        $arUserData = $GLOBALS['userFields']($eventParameters["REAL_USER_ID"]);
        $this->WriteToTrackingService($arUserData['FIO'] . ' подписал документ(ы)', $eventParameters["REAL_USER_ID"]);

        $SIGNED_FILES = $rootActivity->GetVariable('SIGNED_FILES')?:[];

        $props = array_filter(explode(",", str_replace(['{=Variable:','}'], ['',''], $this->arProperties['Props'])));

        foreach ($props as $prop) {
            $var = $this->getVariable($prop);
            if (empty($var)) {
                continue;
            }

            if (!is_array($var)) {
                $var = [$var];
            }
            $var = array_values($var);

            foreach ($var as $var_item) {
                $file_sign = self::getFileSign($var_item);
                if ($file_sign) {
                    $SIGNED_FILES[] = $file_sign;
                }
            }
        }
        $rootActivity->SetVariable('SIGNED_FILES', $SIGNED_FILES);

        $this->taskStatus = CBPTaskStatus::CompleteOk;
        $this->Unsubscribe($this);

        $this->workflow->CloseActivity($this);
    }

    protected function OnEvent(CBPActivity $sender)
    {
        $sender->RemoveStatusChangeHandler(self::ClosedEvent, $this);
        $this->workflow->CloseActivity($this);
    }
}
