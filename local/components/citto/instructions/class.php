<?php

namespace Citto\Instructions;

use \Bitrix\Main\Loader;
use \Bitrix\Main\Application;
use CBitrixComponent;
use CIBlockElement;
use CFile;
use Bitrix\Main\UserTable;
use CIntranetUtils;
use \Bitrix\Im\Integration\Intranet\Department as Department;


if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class Component extends CBitrixComponent
{
    private $_request;
    private $sFilterID = 'filter_instruction';
    private $sGridID = 'grid_instruction';
    private $arColumns = [
        ['id' => 'ID', 'name' => 'ID', 'sort' => false, 'default' => false],
        ['id' => 'FIO', 'name' => 'ФИО', 'sort' => false, 'default' => true],
        ['id' => 'DEPARTMENT', 'name' => 'Отдел', 'sort' => false, 'default' => true],
        ['id' => 'POSITION', 'name' => 'Должность', 'sort' => false, 'default' => true],
        ['id' => 'DATE', 'name' => 'Дата', 'sort' => false, 'default' => true],
        ['id' => 'DOC', 'name' => 'Д/И Doc', 'sort' => false, 'default' => true],
        ['id' => 'PDF', 'name' => 'Д/И Pdf', 'sort' => false, 'default' => true]
    ];
    private $arFilterData = null;
    private $arRole = null;

    private function getFilterData()
    {
        if (is_null($this->arFilterData)) {
            $this->arFilterData = [
                'MAIN' => [
                    'id' => 'MAIN',
                    'name' => 'Структура',
                    'type' => 'dest_selector',
                    'params' => [
                        'contextCode' => 'U',
                        'multiple' => 'Y',
                        'departmentFlatEnable' => 'Y',
                        'enableUsers' => 'N',
                    ]
                ],
                [
                    'id' => 'IS_INSTRUCTION',
                    'name' => 'Наличие Д/И',
                    'type' => 'list',
                    'items' => ['Y' => 'Да', 'N' => 'Нет']
                ],
            ];

            $arRole = $this->getRole($GLOBALS["USER"]->GetID());

            if ($arRole['ROLE'] != 'ADMIN') {
                $this->arFilterData['MAIN']['params']['siteDepartmentId'] = $arRole['DEPARTMENT'];
            }
        }

        return $this->arFilterData;
    }

    private function getFilter($arUsersInstruction = [])
    {
        $arFilter = [];
        $obFilterOptions = new \Bitrix\Main\UI\Filter\Options($this->sFilterID);
        $arFilterFields = $obFilterOptions->getFilter($this->getFilterData());
        $arDepartments = [];
        $arRole = $this->getRole($GLOBALS["USER"]->GetID());

        if ($arRole['ROLE'] == 'USER') {
            $arFilter['ID'] = $GLOBALS["USER"]->GetID();
        }

        foreach ($arFilterFields as $sKey => $sValue) {
            if ($sKey == 'MAIN') {
                $arValue = $sValue;
                foreach ($arValue as $sItem) {
                    $sType = preg_replace('/[^a-zA-Z\s]/', '', $sItem);
                    $iDepartment = preg_replace('/[^0-9]/', '', $sItem);
                    if ($sType == 'DR') {
                        $arDepartments[] = $iDepartment + CIntranetUtils::GetDeparmentsTree($iDepartment, true);
                    } else if ($sType == 'D') {
                        $arDepartments[] = $iDepartment;
                    }
                }
                $arFilter['UF_DEPARTMENT'] = $arDepartments;
            } else if ($sKey == 'IS_INSTRUCTION') {
                $sIsInstruction = ($sValue == 'N') ? '!' : '';
                if ($arRole['ROLE'] == 'USER') {
                    $arFilter[$sIsInstruction . 'ID'] = [$GLOBALS["USER"]->GetID()];
                } else {
                    $arFilter[$sIsInstruction . 'ID'] = $arUsersInstruction;
                }
            }
        }

        if (empty($arDepartments)) {
            $arFilter['UF_DEPARTMENT'][] = $arRole['DEPARTMENT'];
        }

        $arFilter[] = [
            'LOGIC' => 'AND',
            '!LAST_NAME' => '',
            '!NAME' => '',
            '!SECOND_NAME' => ''
        ];
        $arFilter['=ACTIVE'] = 'Y';

        return $arFilter;
    }

    public function getRecords($arElementsID = [])
    {
        $arRecords = [
            'ITEMS' => [],
            'COUNT' => 0
        ];

        $arSelect = [
            "ID",
            "NAME",
            "DATE_CREATE",
            "CREATED_BY",
            "FILE",
            "FILE_PDF",
            "EMPLOYEES",
            "END_PROCESS"
        ];
        $obRecords = \Bitrix\Iblock\Elements\ElementInstructionsTable::getList([
            'select' => $arSelect,
            'filter' => ['=ACTIVE' => 'Y'],
        ]);

        $arUsersInstruction = [];

        while ($arRecord = $obRecords->fetch()) {
            $iUserID = $arRecord['IBLOCK_ELEMENTS_ELEMENT_INSTRUCTIONS_EMPLOYEES_VALUE'];

            if (!isset($arUsersInstruction[$iUserID])) {
                $arUsersInstruction[$iUserID] = [
                    'DATA' => []
                ];
            }

            $sDate = $arRecord['DATE_CREATE'];
            $sFileSrcDoc = CFile::GetPath($arRecord['IBLOCK_ELEMENTS_ELEMENT_INSTRUCTIONS_FILE_IBLOCK_GENERIC_VALUE']);
            $sFileSrcPdf = CFile::GetPath($arRecord['IBLOCK_ELEMENTS_ELEMENT_INSTRUCTIONS_FILE_PDF_IBLOCK_GENERIC_VALUE']);

            $arUsersInstruction[$iUserID]['DATA'][$arRecord['ID']] = [
                'DATE' => $sDate,
                'DOC_URL' => $_SERVER['HTTP_ORIGIN'] . $sFileSrcDoc,
                'DOC' => "<a href ='$sFileSrcDoc' download>Скачать</a>",
                'PDF_URL' => $_SERVER['HTTP_ORIGIN'] . $sFileSrcPdf,
                'PDF' => "<a href ='$sFileSrcPdf' download>Скачать</a>",
            ];
        }

        $arFilter = $this->getFilter(array_keys($arUsersInstruction));

        $obUsers = UserTable::getList([
            'select' => ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'UF_DEPARTMENT', 'UF_WORK_POSITION'],
            'filter' => $arFilter,
            'order' => ['LAST_NAME']
        ]);

        $arDepartments = CIntranetUtils::GetDepartmentsData($arFilter['UF_DEPARTMENT']);

        while ($arUser = $obUsers->fetch()) {
            $arRecords['COUNT']++;
            $arRecordItems = [];
            $iUserID = $arUser['ID'];
            $sFIO = "{$arUser['LAST_NAME']} {$arUser['NAME']} {$arUser['SECOND_NAME']}";
            $sPosition = $arUser['UF_WORK_POSITION'];
            $arDepartmentUser = [];
            foreach ($arUser['UF_DEPARTMENT'] as $iDepartment) {
                if ($arDepartments[$iDepartment]) {
                    array_push($arDepartmentUser, $arDepartments[$iDepartment]);
                }
            }

            $sDepartment = implode(', ', $arDepartmentUser);

            if ($arUsersInstruction[$iUserID]) {
                foreach ($arUsersInstruction[$iUserID] as $arData) {
                    foreach ($arData as $iRecordID => $arField) {
                        $arInfo = [
                            'data' => [
                                'ID' => "{$iUserID}_{$iRecordID}",
                                'FIO' => $sFIO,
                                'DEPARTMENT' => $sDepartment,
                                'POSITION' => $sPosition,
                                'DATE' => $arField['DATE'],
                                'DOC' => $arField['DOC'],
                                'PDF' => ($arField['PDF_URL']) ? $arField['PDF'] : '-',
                                'DOC_URL' => $arField['DOC_URL'],
                                'PDF_URL' => $arField['PDF_URL']
                            ]
                        ];

                        $arRecordItems[] = $arInfo;
                    }
                }
            } else {
                $arRecordItems[] = [
                    'data' => [
                        'ID' => $iUserID,
                        'FIO' => $sFIO,
                        'DEPARTMENT' => $sDepartment,
                        'POSITION' => $sPosition,
                        'DATE' => '-',
                        'DOC' => '-',
                        'PDF' => '-'
                    ]
                ];
            }


            foreach ($arRecordItems as $arRecordItem) {
                if (!empty($arElementsID)) {
                    if (in_array($arRecordItem['data']['ID'], $arElementsID)) {
                        $arRecords['ITEMS'][] = $arRecordItem;
                    }
                } else {
                    $arRecords['ITEMS'][] = $arRecordItem;
                }
            }
        }

        return $arRecords;
    }

    public function getRole($iUserID = 0)
    {
        if (is_null($this->arRole)) {
            $iDepartment = CIntranetUtils::GetUserDepartments($iUserID)[0];

            $this->arRole = [
                'ROLE' => 'USER',
                'DEPARTMENT' => $iDepartment,
            ];

            if ($GLOBALS["USER"]->IsAdmin()) {
                $this->arRole['ROLE'] = 'ADMIN';
            } else {
                $iHead = current(CIntranetUtils::GetDepartmentManager([$iDepartment]))['ID'];
                if ($iHead == $iUserID) {
                    $this->arRole['ROLE'] = 'HEAD';
                }
            }
        }

        return $this->arRole;
    }

    public function executeComponent()
    {
        $this->_request = Application::getInstance()->getContext()->getRequest();

        $this->arResult['COLUMNS'] = $this->arColumns;
        $this->arResult['RECORDS'] = $this->getRecords();
        $this->arResult['COUNT'] = count($this->getRecords());
        $this->arResult['FILTER'] = $this->getFilterData();
        $this->arResult['FILTER_ID'] = $this->sFilterID;
        $this->arResult['GRID_ID'] = $this->sGridID;

        $this->includeComponentTemplate();
    }
}
