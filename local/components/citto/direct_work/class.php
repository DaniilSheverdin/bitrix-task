<?php

namespace Citto\DirectWork;

use \Bitrix\Main\Loader;
use \Bitrix\Main\Application;
use CBitrixComponent;
use CIBlockElement;
use CFile;
use Bitrix\Main\UserTable;
use CIntranetUtils;
use CModule;
use \Bitrix\Im\Integration\Intranet\Department as Department;
use CGroup;


if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class DirectWork extends CBitrixComponent
{
    private $_request;
    private $sFilterID = 'filter_instruction';
    private $sGridID = 'grid_instruction';
    private $arColumns = [
        ['id' => 'ID', 'name' => 'ID', 'sort' => 'ID', 'default' => false],
        ['id' => 'FIO', 'name' => 'ФИО', 'sort' => 'FIO', 'default' => true],
        ['id' => 'DEPARTMENT', 'name' => 'Отдел', 'sort' => false, 'default' => true],
        ['id' => 'LEADER', 'name' => 'Руководитель отдела', 'sort' => false, 'default' => true],
        ['id' => 'COUNT_DAY', 'name' => 'Количество часов в день', 'sort' => false, 'default' => true],
        ['id' => 'COUNT_WEEK', 'name' => 'Количество часов в неделю', 'sort' => false, 'default' => true]
    ];
    private $arFilterData = null;
    private $arRole = null;

    private function getFilterData()
    {
        if (is_null($this->arFilterData)) {
            $this->arFilterData = [
                'MAIN'       => [
                    'id'     => 'MAIN',
                    'name'   => 'Структура',
                    'type'   => 'dest_selector',
                    'params' => [
                        'contextCode'          => 'U',
                        'multiple'             => 'Y',
                        'departmentFlatEnable' => 'Y',
                        'enableUsers'          => 'N',
                    ]
                ],
                'DEPARTMENT' => [
                    'id'   => 'DEPARTMENT',
                    'name' => 'Отдел',
                    'type' => 'dest_selector'
                ],
                'COUNT_DAY'  => [
                    'id'   => 'COUNT_DAY',
                    'name' => 'Количество рабочих часов в день',
                    'type' => 'string'
                ],
                'COUNT_WEEK' => [
                    'id'   => 'COUNT_WEEK',
                    'name' => 'Количество рабочих часов в неделю',
                    'type' => 'string'
                ],
                'EMPLOYEE'   => [
                    'id'     => 'EMPLOYEE',
                    'name'   => 'Сотрудник',
                    'type'   => 'dest_selector',
                    'params' => [
                        'contextCode'       => 'U',
                        'multiple'          => 'N',
                        'enableUsers'       => 'Y',
                        'enableDepartments' => 'N'
                    ]
                ],
            ];

            $arRole = $this->getRole($GLOBALS["USER"]->GetID());

            if ($arRole['ROLE'] != 'ADMIN') {
                $this->arFilterData['MAIN']['params']['siteDepartmentId'] = $arRole['DEPARTMENT'];
            }
        }

        return $this->arFilterData;
    }

    private function getFilter($arUsers = [])
    {
        $arFilter = [
            'USERS' => [],
            'ITEMS' => []
        ];

        $obFilterOptions = new \Bitrix\Main\UI\Filter\Options($this->sFilterID);
        $arFilterFields = $obFilterOptions->getFilter($this->getFilterData());
        $arDepartments = [];
        $arRole = $this->getRole($GLOBALS["USER"]->GetID());

        if ($arRole['ROLE'] == 'USER') {
            $arFilter['USERS']['ID'] = $GLOBALS["USER"]->GetID();
        }

        foreach ($arFilterFields as $sKey => $sValue) {
            if ($sKey == 'MAIN') {
                $arValue = $sValue;
                foreach ($arValue as $sItem) {
                    $sType = preg_replace('/[^a-zA-Z\s]/', '', $sItem);
                    $iDepartment = preg_replace('/[^0-9]/', '', $sItem);
                    if ($sType == 'DR') {
                        $arDepartments = array_merge($arDepartments, CIntranetUtils::GetDeparmentsTree($iDepartment, true));
                    } else if ($sType == 'D') {
                        $arDepartments[] = $iDepartment;
                    }
                }
                $arFilter['USERS']['UF_DEPARTMENT'] = $arDepartments;
            } elseif ($sKey == 'DEPARTMENT') {
                $arFilter['ITEMS']['DEPARTMENT'] = $sValue;
            } elseif ($sKey == 'EMPLOYEE') {
                $arFilter['USERS']['ID'] = preg_replace('/[^0-9]/', '', $sValue);
            } elseif ($sKey == 'COUNT_DAY') {
                $arFilter['ITEMS']['COUNT_DAY'] == $sValue;
            } elseif ($sKey == 'COUNT_WEEK') {
                $arFilter['ITEMS']['COUNT_WEEK'] == $sValue;
            }
        }

        if (empty($arDepartments) && !$arFilterFields['MAIN'] && !$arFilter['USERS']['ID']) {
            $arFilter['USERS']['UF_DEPARTMENT'][] = $arRole['DEPARTMENT'];
            $arFilter['USERS']['UF_DISTANCE_WORK'] = '1';
        }

        $arFilter[] = [
            'LOGIC'        => 'AND',
            '!LAST_NAME'   => '',
            '!NAME'        => '',
            '!SECOND_NAME' => ''
        ];
        $arFilter['USERS']['=ACTIVE'] = 'Y';

        return $arFilter;
    }

    public function getRecords($arElementsID = [])
    {
        $arRecords = [
            'ITEMS' => [],
            'COUNT' => 0
        ];

        $arFilterMain = $this->getFilter();

        $arUserOrder = [
            'LAST_NAME' => 'asc'
        ];

        if ($_REQUEST['by'] == 'FIO') {
            $arUserOrder['LAST_NAME'] = $_REQUEST['order'];
        }

        $obUsers = UserTable::getList([
            'select' => ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'UF_DEPARTMENT', 'UF_WORK_POSITION'],
            'order'  => $arUserOrder
        ]);

        $arDepartments = CIntranetUtils::GetDepartmentsData($arFilterMain['USERS']['UF_DEPARTMENT']);

        $arUsers = [];

        while ($arUser = $obUsers->fetch()) {
            $arRecords['COUNT']++;
            $iUserID = $arUser['ID'];
            $sFIO = "{$arUser['LAST_NAME']} {$arUser['NAME']} {$arUser['SECOND_NAME']}";
            $arDepartmentUser = [];
            foreach ($arUser['UF_DEPARTMENT'] as $iDepartment) {
                if ($arDepartments[$iDepartment]) {
                    array_push($arDepartmentUser, $arDepartments[$iDepartment]);
                }
            }

            $sDepartment = implode(', ', $arDepartmentUser);

            $arUsers[$iUserID] = [
                'FIO'        => $sFIO,
                'DEPARTMENT' => $sDepartment,
            ];
        }

        $arSelect = [
            "ID",
            "NAME",
            "DATE_CREATE",
            "CREATED_BY",
            "PROPERTY_REESTR_FIO",
            "PROPERTY_REESTR_FIO_HEAD",
            "PROPERTY_COUNT_DAY",
            "PROPERTY_COUNT_WEEK",
        ];

        $arFilter = ["IBLOCK_CODE" => "sc_reestr", "ACTIVE_DATE" => "Y", "ACTIVE" => "Y"];

        /*
        if ($this->getRole($GLOBALS["USER"]->GetID())['ROLE'] != 'ADMIN') {
            $arFilter["CREATED_BY"] = $GLOBALS["USER"]->GetID();
        }
        */

        $arOrder = [];

        if ($_REQUEST['by']) {
            $arOrder = [
                'PROPERTY_' . $_REQUEST['by'] => $_REQUEST['order']
            ];
        }

        $obRecords = CIBlockElement::GetList($arOrder, $arFilter, false, [], $arSelect);

        while ($arRecord = $obRecords->GetNext()) {
            $iUserID = $arRecord['PROPERTY_REESTR_FIO_VALUE'];
            $arLeader = \CUser::GetByID($arRecord['PROPERTY_REESTR_FIO_HEAD_VALUE'])->Fetch();
            $sLeader = "{$arLeader['LAST_NAME']} {$arLeader['NAME']} {$arLeader['SECOND_NAME']}";

            $sDate = $arRecord['DATE_CREATE'];

            $arRecords['ITEMS'][$arRecord['ID']]['data'] = [
                'ID'         => $iCustomElementID,
                'USER_ID'    => $iUserID,
                'DATE'       => $sDate,
                'FIO'        => $arUsers[$iUserID]['FIO'],
                'DEPARTMENT' => $arUsers[$iUserID]['DEPARTMENT'],
                'LEADER'     => $sLeader,
                'COUNT_DAY'  => $arRecord['PROPERTY_COUNT_DAY_VALUE'],
                'COUNT_WEEK' => $arRecord['PROPERTY_COUNT_WEEK_VALUE']
            ];


            $arRecords['COUNT']++;
        }

        return $arRecords;
    }


    public function getManagers()
    {
        $obGroups = CGroup::GetList($by = "c_sort", $order = "asc", array("STRING_ID" => 'U'));
        $arGroups = $obGroups->Fetch();
        $arManagers = CGroup::GetGroupUser($arGroups['ID']);

        return $arManagers;
    }

    public function getRole($iUserID = 0)
    {
        $arManagers = $this->getManagers();

        if (is_null($this->arRole)) {
            $iDepartment = CIntranetUtils::GetUserDepartments($iUserID)[0];

            $this->arRole = [
                'ROLE'       => 'USER',
                'DEPARTMENT' => $iDepartment,
            ];

            if ($GLOBALS["USER"]->IsAdmin() || in_array($iUserID, $arManagers)) {
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
        $this->arResult['ROLE'] = $this->getRole($GLOBALS["USER"]->GetID())['ROLE'];
        $this->arResult['FILTER'] = $this->getFilterData();
        $this->arResult['FILTER_ID'] = $this->sFilterID;
        $this->arResult['GRID_ID'] = $this->sGridID;
        $this->includeComponentTemplate();
    }
}
