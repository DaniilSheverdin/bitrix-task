<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

class CBPCITTOSocnetGroupActivity extends CBPActivity implements IBPEventActivity, IBPActivityExternalEventListener
{
    private $isInEventActivityMode = false;

    private static $arAllowedTasksFieldNames = array(
        'TITLE', 'CREATED_BY', 'ACCOMPLICES',
        'DESCRIPTION',
        'SUBJ_ID', 'OPENED', 'PROJECT', 'VISIBLE', 'SITE_ID'
    );

    public function __construct($name)
    {
        parent::__construct($name);
        $this->arProperties = array(
            "Title"                   => "",
            "Fields"                  => null,
            "GroupId"                  => null,
        );
    }


    public function Cancel()
    {
        if (!$this->isInEventActivityMode && $this->HoldToClose) {
            $this->Unsubscribe($this);
        }

        return CBPActivityExecutionStatus::Closed;
    }


    private function __GetUsers($arUsersDraft, $bFirst = false, $checkGroups = true)
    {
        $arUsers = array();

        $rootActivity = $this->GetRootActivity();
        $documentId = $rootActivity->GetDocumentId();

        $documentService = $this->workflow->GetService("DocumentService");

        $arUsersDraft = (is_array($arUsersDraft) ? $arUsersDraft : array($arUsersDraft));

        $l = mb_strlen("user_");
        foreach ($arUsersDraft as $user) {
            if (mb_substr($user, 0, $l) == "user_") {
                $user = intval(mb_substr($user, $l));
                if ($user > 0) {
                    $arUsers[] = $user;
                }
            } elseif (!$checkGroups) {
                $user = intval($user);
                if ($user > 0) {
                    $arUsers[] = $user;
                }
            } else {
                $arDSUsers = $documentService->GetUsersFromUserGroup($user, $documentId);
                foreach ($arDSUsers as $v) {
                    $user = intval($v);
                    if ($user > 0) {
                        $arUsers[] = $user;
                    }
                }
            }
        }

        if (!$bFirst) {
            return $arUsers;
        }

        if (count($arUsers) > 0) {
            return $arUsers[0];
        }

        return null;
    }


    public function Execute()
    {
        $arFields = $this->Fields;
        $arFields["CREATED_BY"] = $this->__GetUsers($this->Fields["CREATED_BY"], true, false);
        $arFields["ACCOMPLICES"] = $this->__GetUsers($this->Fields["ACCOMPLICES"]);

        if (isset($this->Fields['DESCRIPTION'])) {
            $arFields['DESCRIPTION'] = preg_replace(
                '/\[url=(.*)\](.*)\[\/url\]/i' . BX_UTF_PCRE_MODIFIER,
                '<a href="${1}">${2}</a>',
                $this->Fields['DESCRIPTION']
            );
        }

        $arUnsetFields = array();
        foreach ($arFields as $fieldName => $fieldValue) {
            if (mb_substr($fieldName, -5) === '_text') {
                $arFields[substr($fieldName, 0, -5)] = $fieldValue;
                $arUnsetFields[] = $fieldName;
            }
        }

        foreach ($arUnsetFields as $fieldName) {
            unset($arFields[$fieldName]);
        }

        // Check fields for "white" list
        $arFieldsChecked = array();
        foreach (array_keys($arFields) as $fieldName) {
            $arFieldsChecked[$fieldName] = $arFields[$fieldName];
        }

        $arGroupFields = array('NAME' => $arFieldsChecked["TITLE"],
            'DESCRIPTION' => $arFieldsChecked["DESCRIPTION"],
            'VISIBLE' => $arFieldsChecked["VISIBLE"],
            'OPENED' => $arFieldsChecked["OPENED"],
            'PROJECT' => $arFieldsChecked["PROJECT"],
            'CLOSED' => "N",
            'SUBJECT_ID' => $arFieldsChecked["SUBJ_ID"],
            'INITIATE_PERMS' => "K",
            'SPAM_PERMS' => "K",
            'SITE_ID' => $arFieldsChecked["SITE_ID"]
        );
        if (CModule::IncludeModule('socialnetwork')) {
            $result = CSocNetGroup::CreateGroup($arFieldsChecked["CREATED_BY"], $arGroupFields, false);
        }

        if (!$result) {
            if ($e = $GLOBALS["APPLICATION"]->GetException()) {
                $errorMessage .= $e->GetString();
                $this->WriteToTrackingService(GetMessage("BPSA_TRACK_ERROR"));
            }

            return CBPActivityExecutionStatus::Closed;
        }

        $this->GroupId = $result;

        foreach ($arFields["ACCOMPLICES"] as $UserToGroupID) {
            if ($UserToGroupID != $arFieldsChecked["CREATED_BY"]) {
                $arUserToGroupFields = array(
                    'USER_ID' => $UserToGroupID,
                    'GROUP_ID' => $result,
                    'ROLE' => SONET_ROLES_USER,
                    '=DATE_CREATE' => time(),
                    '=DATE_UPDATE' => time(),
                    'MESSAGE' => false,
                    'INITIATED_BY_TYPE' => "G",
                    'INITIATED_BY_USER_ID' => $arFieldsChecked["CREATED_BY"],
                    'SEND_MAIL' => 'N'
                );
                $USERTOGROUPID = CSocNetUserToGroup::Add($arUserToGroupFields);
            }
        }

        // По-умолчанию Диск не создаётся
        CSocNetFeatures::setFeature(
            SONET_ENTITY_GROUP,
            $result,
            'files',
            true
        );

        $this->WriteToTrackingService(str_replace("#VAL#", $result, GetMessage("BPSA_GROUP_OK")));

        $this->Subscribe($this);
        $this->isInEventActivityMode = false;

        $this->WriteToTrackingService(GetMessage("BPSA_TRACK_SUBSCR"));

        return CBPActivityExecutionStatus::Closed;
    }

    public function Subscribe(IBPActivityExternalEventListener $eventHandler)
    {
        /*
        if ($eventHandler == null)
            throw new Exception("eventHandler");

        $this->isInEventActivityMode = true;

        $schedulerService = $this->workflow->GetService("SchedulerService");
        //$schedulerService->SubscribeOnEvent($this->workflow->GetInstanceId(), $this->name, "tasks", "OnTaskUpdate", $this->TaskId);

        $this->workflow->AddEventHandler($this->name, $eventHandler);
        */
    }


    public function Unsubscribe(IBPActivityExternalEventListener $eventHandler)
    {
        /*
        if ($eventHandler == null)
            throw new Exception("eventHandler");

        $schedulerService = $this->workflow->GetService("SchedulerService");
        //$schedulerService->UnSubscribeOnEvent($this->workflow->GetInstanceId(), $this->name, "tasks", "OnTaskUpdate", $this->TaskId);

        $this->workflow->RemoveEventHandler($this->name, $eventHandler);
        */
    }


    public function OnExternalEvent($arEventParameters = array())
    {
        /*
        if ($this->TaskId != $arEventParameters[0])
            return;

        if ($this->executionStatus != CBPActivityExecutionStatus::Closed)
        {
            if ($arEventParameters[1]["STATUS"] == 5)
            {
                $this->ClosedBy = "user_".$arEventParameters[1]["CLOSED_BY"];
                $this->ClosedDate = $arEventParameters[1]["CLOSED_DATE"];

                $this->WriteToTrackingService(str_replace("#DATE#", $arEventParameters[1]["CLOSED_DATE"], GetMessage("BPSA_TRACK_CLOSED")));

                $this->Unsubscribe($this);
                $this->workflow->CloseActivity($this);
            }
        }
        */
    }


    public function HandleFault(Exception $exception)
    {
        /*
        if ($exception == null)
            throw new Exception("exception");

        $status = $this->Cancel();
        if ($status == CBPActivityExecutionStatus::Canceling)
            return CBPActivityExecutionStatus::Faulting;

        return $status;
        */
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

        if (!is_array($arWorkflowParameters)) {
            $arWorkflowParameters = array();
        }
        if (!is_array($arWorkflowVariables)) {
            $arWorkflowVariables = array();
        }

        $documentService = $runtime->GetService("DocumentService");

        if (!is_array($arCurrentValues)) {
            $arCurrentValues = array();

            $arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
            if (
                is_array($arCurrentActivity["Properties"])
                && array_key_exists("Fields", $arCurrentActivity["Properties"])
                && is_array($arCurrentActivity["Properties"]["Fields"])
            ) {
                foreach ($arCurrentActivity["Properties"]["Fields"] as $k => $v) {
                    $arCurrentValues[$k] = $v;

                    if (in_array($k, array("CREATED_BY", "RESPONSIBLE_ID", "ACCOMPLICES", "AUDITORS"))) {
                        if (!is_array($arCurrentValues[$k])) {
                            $arCurrentValues[$k] = array($arCurrentValues[$k]);
                        }

                        $ar = array();
                        foreach ($arCurrentValues[$k] as $v) {
                            if (intval($v)."!" == $v."!") {
                                $v = "user_".$v;
                            }
                            $ar[] = $v;
                        }

                        $arCurrentValues[$k] = CBPHelper::UsersArrayToString($ar, $arWorkflowTemplate, $documentType);
                    }
                }
            }
        } else {
            foreach (static::$arAllowedTasksFieldNames as $field) {
                if (
                    (!is_array($arCurrentValues[$field]) && (mb_strlen($arCurrentValues[$field]) <= 0)
                    || is_array($arCurrentValues[$field]) && (count($arCurrentValues[$field]) <= 0))
                    && $arCurrentValues[$field."_text"] != ''
                ) {
                    $arCurrentValues[$field] = $arCurrentValues[$field."_text"];
                }
            }
        }

        $arDocumentFields = self::__GetFields();

        return $runtime->ExecuteResourceFile(
            __FILE__,
            "properties_dialog.php",
            array(
                "arCurrentValues" => $arCurrentValues,
                "formName" => $formName,
                "documentType" => $documentType,
                "popupWindow" => &$popupWindow,
                "arDocumentFields" => $arDocumentFields,
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

        $arProperties = array("Fields" => array());

        if (CModule::IncludeModule("socialnetwork")) {
            $dn = CSocNetGroupSubject::GetList(array("NAME"=>"ASC"), array(), false, false);
                while ($gr = $dn->GetNext()) {
                    $arSubject[$gr["ID"]] = $gr["NAME"];
                }
        }

        $arDF = self::__GetFields();

        foreach (static::$arAllowedTasksFieldNames as $field) {
            $r = null;

            if (in_array($field, array("CREATED_BY", "RESPONSIBLE_ID", "ACCOMPLICES", "AUDITORS"))) {
                $value = $arCurrentValues[$field];
                if ($value != '') {
                    $arErrorsTmp = array();
                    $r = CBPHelper::UsersStringToArray($value, $documentType, $arErrorsTmp);
                    if (count($arErrorsTmp) > 0) {
                        $arErrors = array_merge($arErrors, $arErrorsTmp);
                    }
                }
            } elseif (array_key_exists($field, $arCurrentValues) || array_key_exists($field."_text", $arCurrentValues)) {
                $arValue = array();
                if (array_key_exists($field, $arCurrentValues)) {
                    $arValue = $arCurrentValues[$field];
                    if (!is_array($arValue) || is_array($arValue) && CBPHelper::IsAssociativeArray($arValue)) {
                        $arValue = array($arValue);
                    }
                }

                if (array_key_exists($field."_text", $arCurrentValues)) {
                    $arValue[] = $arCurrentValues[$field."_text"];
                }

                foreach ($arValue as $value) {
                    $value = trim($value);
                    if (!preg_match("#^\{=[a-z0-9_]+:[a-z0-9_]+\}$#i", $value) && (mb_substr($value, 0, 1) !== "=")) {
                        if ($field == "SUBJ_ID") {
                            if ($value == '') {
                                $value = null;
                            }
                            if ($value != null && !array_key_exists($value, $arSubject)) {
                                $value = null;
                                $arErrors[] = array(
                                    "code" => "ErrorValue",
                                    "message" => "Group is empty",
                                    "parameter" => $field,
                                );
                            }
                        } elseif (in_array($field, array("ALLOW_CHANGE_DEADLINE", "TASK_CONTROL", "ADD_IN_REPORT", 'ALLOW_TIME_TRACKING'))) {
                            if (strtoupper($value) == "Y" || $value === true || $value."!" == "1!") {
                                $value = "Y";
                            } elseif (strtoupper($value) == "N" || $value === false || $value."!" == "0!") {
                                $value = "N";
                            } else {
                                $value = null;
                            }
                        } else {
                            if (!is_array($value) && mb_strlen($value) <= 0) {
                                $value = null;
                            }
                        }
                    }

                    if ($value != null) {
                        $r[] = $value;
                    }
                }
            }

            $r_orig = $r;

            if (!in_array($field, array("ACCOMPLICES", "AUDITORS"))) {
                if (count($r) > 0) {
                    $r = $r[0];
                } else {
                    $r = null;
                }
            }

            if (in_array($field, array("TITLE", "CREATED_BY", "RESPONSIBLE_ID")) && ($r == null || is_array($r) && count($r) <= 0)) {
                $arErrors[] = array(
                    "code" => "emptyRequiredField",
                    "message" => str_replace("#FIELD#", $arDF[$field]["Name"], GetMessage("BPCDA_FIELD_REQUIED")),
                );
            }

            $arProperties["Fields"][$field] = $r;

            if (array_key_exists($field."_text", $arCurrentValues) && isset($r_orig[1])) {
                $arProperties["Fields"][$field . '_text'] = $r_orig[1];
            }
        }

        if (count($arErrors) > 0) {
            return false;
        }

        $arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
        $arCurrentActivity["Properties"] = $arProperties;

        return true;
    }


    private static function __GetFields()
    {
        if (CModule::IncludeModule("socialnetwork")) {
            $dn = CSocNetGroupSubject::GetList(array("NAME"=>"ASC"), array(), false, false);
                while ($gr = $dn->GetNext()) {
                    $arSubject[$gr["ID"]] = $gr["NAME"];
                }
        }

        $rsSites = CSite::GetList($by="sort", $order="desc");
        $arSites = array();
        while ($arSite = $rsSites->GetNext()) {
          $arSites[$arSite["ID"]] = $arSite["NAME"];
        }

        $arFields = array(
            "TITLE" => array(
                "Name" => GetMessage("BPTA1A_GROUPNAME"),
                "Type" => "S",
                "Filterable" => true,
                "Editable" => true,
                "Required" => true,
                "Multiple" => false,
                "BaseType" => "string"
            ),
            "CREATED_BY" => array(
                "Name" => GetMessage("BPTA1A_GROUPCREATEDBY"),
                "Type" => "S:UserID",
                "Filterable" => true,
                "Editable" => true,
                "Required" => true,
                "Multiple" => false,
                "BaseType" => "user"
            ),
            "ACCOMPLICES" => array(
                "Name" => GetMessage("BPTA1A_GROUPACCOMPLICES"),
                "Type" => "S:UserID",
                "Filterable" => true,
                "Editable" => true,
                "Required" => false,
                "Multiple" => true,
                "BaseType" => "user"
            ),
            "DESCRIPTION" => array(
                "Name" => GetMessage("BPTA1A_GROUPDETAILTEXT"),
                "Type" => "T",
                "Filterable" => true,
                "Editable" => true,
                "Required" => false,
                "Multiple" => false,
                "BaseType" => "text"
            ),
            'SITE_ID' =>array(
                "Name" => GetMessage("BPTA1A_SITES_ID"),
                "Type" => "L",
                "Options" => $arSites,
                "Filterable" => true,
                "Editable" => true,
                "Required" => false,
                "Multiple" => true,
                "BaseType" => "select"
            ),
            "SUBJ_ID" => array(
                "Name" => GetMessage("BPTA1A_SUBJ_ID"),
                "Type" => "L",
                "Options" => $arSubject,
                "Filterable" => true,
                "Editable" => true,
                "Required" => false,
                "Multiple" => false,
                "BaseType" => "select"
            ),
            "OPENED" => array(
                "Name" => GetMessage("BPTA1A_OPENED_GROUP"),
                "Type" => "B",
                "Filterable" => true,
                "Editable" => true,
                "Required" => false,
                "Multiple" => false,
                "BaseType" => "bool"
            ),
            "PROJECT" => array(
                "Name" => GetMessage("BPTA1A_PROJECT_GROUP"),
                "Type" => "B",
                "Filterable" => true,
                "Editable" => true,
                "Required" => false,
                "Multiple" => false,
                "BaseType" => "bool"
            ),
            "VISIBLE" => array(
                "Name" => GetMessage("BPTA1A_VISIBLE_GROUP"),
                "Type" => "B",
                "Filterable" => true,
                "Editable" => true,
                "Required" => false,
                "Multiple" => false,
                "BaseType" => "bool"
            ),
        );

        return $arFields;
    }
}
