<?php

use CFile;
use CIBlockElement;
use CIntranetUtils;
use CBitrixComponent;
use \Bitrix\Main\Loader;
use Bitrix\Main\UserTable;
use \Bitrix\Main\Application;
use \Bitrix\Im\Integration\Intranet\Department as Department;


if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

Bitrix\Main\Loader::includeModule('bizproc');
Bitrix\Main\Loader::includeModule('workflow');

class Component extends CBitrixComponent
{
    private $sFilterID = 'filter_mentoring';
    private $sGridID = 'grid_mentoring';
    private $arColumns = [
        ['id' => 'ID', 'name' => 'ID', 'sort' => false, 'default' => false],
        ['id' => 'EMPLOYEE', 'name' => 'Сотрудник', 'sort' => false, 'default' => true],
        ['id' => 'MENTOR', 'name' => 'Наставник', 'sort' => false, 'default' => true],
        ['id' => 'OFFICIAL_MEMO', 'name' => 'Служебная записка', 'sort' => false, 'default' => true],
        ['id' => 'MENTORING_PLAN', 'name' => 'План по наставничеству', 'sort' => false, 'default' => true],
        ['id' => 'FEEDBACK_EMPLOYEE', 'name' => 'Отзыв наставляемого', 'sort' => false, 'default' => true],
        ['id' => 'MENTOR_REPORT', 'name' => 'Отчет по итогам наставничества', 'sort' => false, 'default' => true],
        ['id' => 'TASKS', 'name' => 'Файлы из задач', 'sort' => false, 'default' => true],
        ['id' => 'DATE_CREATE', 'name' => 'Дата запуска БП', 'sort' => false, 'default' => true],
        ['id' => 'PERIOD_MENTORING', 'name' => 'Период наставничества', 'sort' => false, 'default' => true],
        ['id' => 'STATE', 'name' => 'Статус', 'sort' => false, 'default' => true],
    ];
    private $arFilterData = null;
    private $arStates = [
        'Completed' => 'Завершён',
        'InProgress' => 'В процессе',
        'Terminated' => 'Прерван',
    ];
    private $arPeriodMentoring = [
        '3 месяца' => '3 месяца',
        '6 месяцев' => '6 месяцев',
        '1 год' => '1 год'
    ];

    private function getFilterData()
    {
        if (is_null($this->arFilterData)) {
            $this->arFilterData = [
                // 'MAIN' => [
                //     'id' => 'MAIN',
                //     'name' => 'Структура',
                //     'type' => 'dest_selector',
                //     'params' => [
                //         'contextCode' => 'U',
                //         'multiple' => 'Y',
                //         'departmentFlatEnable' => 'Y',
                //         'enableUsers' => 'N',
                //     ]
                // ],
                'EMPLOYEE' => [
                    'id' => 'EMPLOYEE',
                    'name' => 'Сотрудник',
                    'type' => 'dest_selector',
                    'params' => [
                        'contextCode' => 'U',
                        'multiple' => 'N',
                        'enableUsers' => 'Y',
                        'enableDepartments' => 'N'
                    ]
                ],
                'MENTOR' => [
                    'id' => 'MENTOR',
                    'name' => 'Наставник',
                    'type' => 'dest_selector',
                    'params' => [
                        'contextCode' => 'U',
                        'multiple' => 'N',
                        'enableUsers' => 'Y',
                        'enableDepartments' => 'N'
                    ]
                ],
                'NAME_DOC' => [
                    'id' => 'NAME_DOC',
                    'name' => 'Имя документа',
                    'type' => 'string',
                ],
                'DATE_CREATE' => [
                    'id' => 'DATE_CREATE',
                    'name' => 'Дата запуска БП',
                    'type' => 'date',
                ],
                'PERIOD_MENTORING' => [
                    'id' => 'PERIOD_MENTORING',
                    'name' => 'Период наставничества',
                    'type' => 'list',
                    'items' => $this->arPeriodMentoring
                ],
                'STATE' => [
                    'id' => 'STATE',
                    'name' => 'Статус',
                    'type' => 'list',
                    'items' => $this->arStates
                ]
            ];
        }

        return $this->arFilterData;
    }

    private function getFilter()
    {
        $arFilter = [];
        $obFilterOptions = new \Bitrix\Main\UI\Filter\Options($this->sFilterID);
        $arFilterFields = $obFilterOptions->getFilter($this->getFilterData());
        $arDepartments = [];

        foreach ($arFilterFields as $sKey => $sValue) {
            if ($sKey == 'MAIN') {
                // $arValue = $sValue;
                // foreach ($arValue as $sItem) {
                //     $sType = preg_replace('/[^a-zA-Z\s]/', '', $sItem);
                //     $iDepartment = preg_replace('/[^0-9]/', '', $sItem);
                //     if ($sType == 'DR') {
                //         $arDepartments = array_merge($arDepartments, CIntranetUtils::GetDeparmentsTree($iDepartment, true));
                //     } else if ($sType == 'D') {
                //         $arDepartments[] = $iDepartment;
                //     }
                // }
                // $arFilter['DEPARTMENT'] = $arDepartments;
            } elseif ($sKey == 'EMPLOYEE') {
                $arFilter['EMPLOYEE'] = preg_replace('/[^0-9]/', '', $sValue);
            } elseif ($sKey == 'MENTOR') {
                $arFilter['MENTOR'] = preg_replace('/[^0-9]/', '', $sValue);
            } elseif ($sKey == 'NAME_DOC') {
                $arFilter['NAME_DOC'] = $sValue;
            } elseif ($sKey == 'DATE_CREATE_datesel') {
                $sSymbol = "";
                $thisValue = null;

                $sFrom = $arFilterFields['DATE_CREATE_from'];
                $sTo = $arFilterFields['DATE_CREATE_to'];

                if ($sFrom && $sTo) {
                    $sSymbol = "><";
                    $thisValue = [$sFrom, $sTo];
                } elseif ($sFrom) {
                    $sSymbol = ">";
                    $thisValue = $sFrom;
                } elseif ($sTo) {
                    $sSymbol = "<";
                    $thisValue = $sTo;
                }

                if ($sSymbol && $sSymbol) {
                    $arFilter['DATE_CREATE'] = [
                        'SIGN' => $sSymbol,
                        'VALUE' => $thisValue
                    ];
                }
            } elseif ($sKey == 'PERIOD_MENTORING') {
                $arFilter['PERIOD_MENTORING'] = $sValue;
            } elseif ($sKey == 'STATE') {
                $arFilter['STATE'] = $sValue;
            }
        }

        return $arFilter;
    }

    public function getRecords()
    {
        $arFilter = $this->getFilter();

        $arSelect = [
            'ID',
            'DATE_CREATE',
            'PROPERTY_MENTOR',
            'PROPERTY_EMPLOYEE',
            'PROPERTY_PERIOD_MENTORING',
            'PROPERTY_OFFICIAL_MEMO',
            'PROPERTY_MENTORING_PLAN',
            'PROPERTY_FEEDBACK_EMPLOYEE',
            'PROPERTY_MENTOR_REPORT',
            'PROPERTY_TASK_ID_1',
            'PROPERTY_TASK_ID_2',
            'PROPERTY_TASK_ID_3',
        ];

        $arFilterBP = [
            "IBLOCK_CODE" => "mentoring",
            "ACTIVE" => "Y",
            [
                "LOGIC" => "OR",
                ">PROPERTY_OFFICIAL_MEMO" => 0,
                ">PROPERTY_FEEDBACK_EMPLOYEE" => 0,
                ">PROPERTY_MENTORING_PLAN" => 0,
                ">PROPERTY_MENTOR_REPORT" => 0,
                ">PROPERTY_TASK_ID_1" => 0,
                ">PROPERTY_TASK_ID_2" => 0,
                ">PROPERTY_TASK_ID_3" => 0
            ]
        ];

        if ($arFilter['MENTOR']) {
            $arFilterBP['PROPERTY_MENTOR'] = $arFilter['MENTOR'];
        }

        if ($arFilter['EMPLOYEE']) {
            $arFilterBP['PROPERTY_EMPLOYEE'] = $arFilter['EMPLOYEE'];
        }

        if ($arFilter['DATE_CREATE']) {
            $sSign = $arFilter['DATE_CREATE']['SIGN'];
            $thisValue = $arFilter['DATE_CREATE']['VALUE'];
            $arFilterBP[$sSign . 'DATE_CREATE'] = $thisValue;
        }

        if ($arFilter['PERIOD_MENTORING']) {
            $arFilterBP['PROPERTY_PERIOD_MENTORING_VALUE'] = $arFilter['PERIOD_MENTORING'];
        }

        $obEl = CIBlockElement::GetList([], $arFilterBP, false, [], $arSelect);
        $arTasks = [];
        $arFiles = [];

        $arElements = [];
        while ($arItem = $obEl->GetNext()) {
            if ($iFileMemoID = $arItem['PROPERTY_OFFICIAL_MEMO_VALUE']) {
                $arFiles[$iFileMemoID] = [
                    'ELEMENT_ID' => $arItem['ID'],
                    'TYPE' => 'OFFICIAL_MEMO',
                ];
            }

            if ($iFileFeedbackEmployee = $arItem['PROPERTY_FEEDBACK_EMPLOYEE_VALUE']) {
                $arFiles[$iFileFeedbackEmployee] = [
                    'ELEMENT_ID' => $arItem['ID'],
                    'TYPE' => 'FEEDBACK_EMPLOYEE',
                ];
            }

            if ($iFileMentoringPlanID = $arItem['PROPERTY_MENTORING_PLAN_VALUE']) {
                $arFiles[$iFileMentoringPlanID] = [
                    'ELEMENT_ID' => $arItem['ID'],
                    'TYPE' => 'MENTORING_PLAN',
                ];
            }

            if ($iFileMentorReportID = $arItem['PROPERTY_MENTOR_REPORT_VALUE']) {
                $arFiles[$iFileMentorReportID] = [
                    'ELEMENT_ID' => $arItem['ID'],
                    'TYPE' => 'MENTOR_REPORT',
                ];
            }

            if ($iTask_1 = $arItem['PROPERTY_TASK_ID_1_VALUE']) {
                $arTasks[$iTask_1] = [
                    'ELEMENT_ID' => $arItem['ID'],
                    'TYPE' => 'TASK',
                ];
            }

            if ($iTask_2 = $arItem['PROPERTY_TASK_ID_2_VALUE']) {
                $arTasks[$iTask_2] = [
                    'ELEMENT_ID' => $arItem['ID'],
                    'TYPE' => 'TASK',
                ];
            }

            if ($iTask_3 = $arItem['PROPERTY_TASK_ID_3_VALUE']) {
                $arTasks[$iTask_3] = [
                    'ELEMENT_ID' => $arItem['ID'],
                    'TYPE' => 'TASK',
                ];
            }

            if (
                $iFileMemoID ||
                $iFileFeedbackEmployee ||
                $iFileMentoringPlanID ||
                $iFileMentorReportID ||
                $iTask_1 ||
                $iTask_2 ||
                $iTask_3
            ) {
                $iMentor = $arItem['PROPERTY_MENTOR_VALUE'];
                $iEmployee = $arItem['PROPERTY_EMPLOYEE_VALUE'];

                $arElements[$arItem['ID']]['MENTOR'] = $iMentor;
                $arElements[$arItem['ID']]['EMPLOYEE'] = $iEmployee;
                $arElements[$arItem['ID']]['PERIOD_MENTORING'] = $arItem['PROPERTY_PERIOD_MENTORING_VALUE'];
                $arElements[$arItem['ID']]['DATE_CREATE'] = (new DateTime($arItem['DATE_CREATE']))->format('d.m.Y');
            }
        }

        $arFilterWorkflow = ['DOCUMENT_ID' => array_keys($arElements)];

        if ($arFilter['STATE']) {
            $arFilterWorkflow['STATE'] = $arFilter['STATE'];
        }

        $obRes = Bitrix\Bizproc\Workflow\Entity\WorkflowStateTable::getList([
            'select' => ['DOCUMENT_ID', 'STATE'],
            'filter' => $arFilterWorkflow,
        ]);

        $arTmpElements = [];

        while ($arRes = $obRes->fetch()) {
            $iDocID = $arRes['DOCUMENT_ID'];
            $arElements[$iDocID]['STATE'] = $this->arStates[$arRes['STATE']];
            $arTmpElements[$iDocID] = $arElements[$iDocID];
        }

        $arElements = $arTmpElements;
        unset($arTmpElements);

        return ['ELEMENTS' => $arElements, 'FILES' => $arFiles, 'TASKS' => $arTasks];
    }

    private function putFilesFromTasksToRecords($arTasks = [], $arElements = [])
    {
        $arFilter = $this->getFilter();

        $obConnectBD = Application::getConnection();

        $sTaskSQL = "
        SELECT FILE_ID, FILE.SUBDIR, FILE.FILE_NAME, FILE.ORIGINAL_NAME, ATT_OBJECT.ID as OBJECT_ID, OBJECT_ID, ENTITY_ID 
        FROM b_disk_attached_object ATT_OBJECT
        JOIN b_disk_object OBJECT on OBJECT_ID = OBJECT.ID
        JOIN b_file FILE on FILE.ID = FILE_ID
        WHERE ENTITY_ID in (" . implode(',', array_keys($arTasks)) . ")";

        if ($arFilter['NAME_DOC']) {
            $sTaskSQL .= " AND FILE.ORIGINAL_NAME LIKE '%{$arFilter['NAME_DOC']}%'";
        }

        $obFileTasks = $obConnectBD->query($sTaskSQL);

        while ($arItem = $obFileTasks->fetch()) {
            $iTaskID = $arItem['ENTITY_ID'];
            $iRecordID = $arTasks[$iTaskID]['ELEMENT_ID'];

            if ($arElements[$iRecordID]) {
                $arItem['SRC'] = "/upload/" . $arItem['SUBDIR'] . "/" . $arItem['FILE_NAME'];
                $arElements[$iRecordID]['TASKS'][] = $arItem;
            }
        }

        return $arElements;
    }


    private function putFilesToRecords($arFiles, $arElements)
    {
        $arFilter = $this->getFilter();
        $obFiles = CFile::GetList(["FILE_SIZE" => "desc"], ["@ID" => implode(",", array_keys($arFiles))]);
        while ($arFile = $obFiles->GetNext()) {
            $iFileID = $arFile['ID'];
            $iRecordID = $arFiles[$iFileID]['ELEMENT_ID'];

            if ($arElements[$iRecordID]) {
                if ($arFilter['NAME_DOC']) {
                    $sFileName = $arFile['ORIGINAL_NAME'];
                    if (mb_stripos($sFileName, $arFilter['NAME_DOC']) === false) {
                        continue;
                    }
                }

                $sType = $arFiles[$iFileID]['TYPE'];
                $arFile['SRC'] = "/upload/" . $arFile['SUBDIR'] . "/" . $arFile['FILE_NAME'];
                $arElements[$iRecordID][$sType] = $arFile;
            }
        }

        return $arElements;
    }

    private function getUsers()
    {
        $arUsers = [];
        $arFilterUsers = [];

        $obUsers = UserTable::getList([
            'select' => ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME'],
            'filter' => $arFilterUsers,
            'order' => ['LAST_NAME']
        ]);

        while ($arUser = $obUsers->fetch()) {
            $iUserID = $arUser['ID'];
            $sFIO = "{$arUser['LAST_NAME']} {$arUser['NAME']} {$arUser['SECOND_NAME']}";
            $arUsers[$iUserID] = [
                'FIO' => $sFIO,
                'DEPARTMENTS' => ''
            ];
        }

        return $arUsers;
    }

    public function getBPItems()
    {
        [
            'ELEMENTS' => $arElements,
            'TASKS' => $arTasks,
            'FILES' => $arFiles
        ] = $this->getRecords();

        /*Дёргаем файлы из задач и загоняем их в записи*/
        if ($arTasks) {
            $arElements = $this->putFilesFromTasksToRecords($arTasks, $arElements);
        }

        /*Дёргаем файлы из доп полей элемента и загоняем их в записи*/
        if ($arFiles) {
            $arElements = $this->putFilesToRecords($arFiles, $arElements);
        }

        $arItems = [
            'ITEMS' => [],
            'COUNT' => 0
        ];

        $arFieldsFile = [
            'OFFICIAL_MEMO',
            'FEEDBACK_EMPLOYEE',
            'MENTORING_PLAN',
            'MENTOR_REPORT'
        ];

        $arUsers = $this->getUsers();

        foreach ($arElements as $iTaskID => $arRecord) {
            $bIsFile = false;

            if ($arRecord['TASKS']) {
                $sTasks = "";
                foreach ($arRecord['TASKS'] as $arFile) {
                    $sTasks .= "<a class = 'download' data-id = '{$arFile['FILE_ID']}' href = '?download={$arFile['FILE_ID']}'>{$arFile['ORIGINAL_NAME']}</a><br>";
                }

                $arRecord['TASKS'] = $sTasks;
                $bIsFile = true;
            }

            foreach ($arFieldsFile as $sField) {
                if ($arRecord[$sField]) {
                    $arRecord[$sField] = "<a class = 'download' data-id = '{$arRecord[$sField]['ID']}' href = '?download={$arRecord[$sField]['ID']}'>{$arRecord[$sField]['ORIGINAL_NAME']}</a>";
                    $bIsFile = true;
                }
            }

            if ($bIsFile) {
                $arRecord['ID'] = $iTaskID;
                $arRecord['MENTOR'] = $arUsers[$arRecord['MENTOR']]['FIO'];
                $arRecord['EMPLOYEE'] = $arUsers[$arRecord['EMPLOYEE']]['FIO'];

                $arItems['ITEMS'][]['data'] = $arRecord;
                $arItems['COUNT']++;
            }
        }

        return $arItems;
    }

    public function executeComponent()
    {
        $this->_request = Application::getInstance()->getContext()->getRequest();
        $this->arResult['COLUMNS'] = $this->arColumns;
        $this->arResult['BP_ITEMS'] = $this->getBPItems();
        $this->arResult['FILTER'] = $this->getFilterData();
        $this->arResult['FILTER_ID'] = $this->sFilterID;
        $this->arResult['GRID_ID'] = $this->sGridID;
        $this->includeComponentTemplate();
    }
}
