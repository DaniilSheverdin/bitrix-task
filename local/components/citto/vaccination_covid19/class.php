<?php

namespace Citto\Vaccinationcovid19;

use \Bitrix\Main\Loader;
use \Bitrix\Main\Application;
use CBitrixComponent;
use CIBlockElement;
use CFile;
use Bitrix\Main\UserTable;
use CIntranetUtils;
use Citto\Mentoring\Users as MentoringUsers;
use Citto\Vaccinationcovid19\Component as MainComponent;
use CModule;
use \Bitrix\Im\Integration\Intranet\Department as Department;
use CGroup;


if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class Component extends CBitrixComponent
{
    private $_request;
    private $sFilterID = 'filter_instruction';
    private $sGridID = 'grid_instruction';
    private $arColumns = [
        ['id' => 'ID', 'name' => 'ID', 'sort' => 'ID', 'default' => false],
        ['id' => 'FIO', 'name' => 'ФИО', 'sort' => 'FIO', 'default' => true],
        ['id' => 'DEPARTMENT', 'name' => 'Отдел', 'sort' => false, 'default' => true],
        ['id' => 'STATUS', 'name' => 'Предоставлены сведения', 'sort' => false, 'default' => true],
        ['id' => 'CRT_NUMBER', 'name' => 'Номер сертификата', 'sort' => false, 'default' => true],
        ['id' => 'TYPE_VACCINATION', 'name' => 'Вид прививки', 'sort' => false, 'default' => true],
        ['id' => 'DATE_VACCINATION', 'name' => 'Дата последней вакцинации', 'sort' => 'DATE_VACCINATION', 'default' => true],
        ['id' => 'INFO_DISEASE', 'name' => 'Информация о перенесенном заболеваним', 'sort' => false, 'default' => true],
        ['id' => 'DATE_RECOVERY', 'name' => 'Дата выздоровления', 'sort' => 'DATE_RECOVERY', 'default' => true],
        ['id' => 'MEDOTVOD', 'name' => 'Медотвод', 'sort' => false, 'default' => true],
        ['id' => 'DATE_END_MEDOTVOD', 'name' => 'Срок окончания медотвода', 'sort' => 'DATE_END_MEDOTVOD', 'default' => true],
        ['id' => 'CRT_FILE', 'name' => 'Сертификат о вакцинации', 'sort' => false, 'default' => true],
        ['id' => 'MEDOTVOD_FILE', 'name' => 'Медотвод (документ)', 'sort' => false, 'default' => true],
        ['id' => 'DATE_REVACCINATION', 'name' => 'Дата ревакцинации', 'sort' => false, 'default' => true]
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
                'PERIOD' => [
                    'id' => 'PERIOD',
                    'name' => 'Период ревакцинации',
                    'type' => 'date'
                ],
                'STATUS' => [
                    'id' => 'STATUS',
                    'name' => 'Предоставлены сведения',
                    'type' => 'list',
                    'items' => ['Y' => 'Да', 'N' => 'Нет']
                ],
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
            } elseif ($sKey == 'PERIOD_datesel') {
                $sSymbol = "";
                $thisValue = null;

                $sFrom = (new \DateTime($arFilterFields['PERIOD_from']))->sub(new \DateInterval('P6MT1S'))->format('Y-m-d H:i:s');
                $sTo = (new \DateTime($arFilterFields['PERIOD_to']))->sub(new \DateInterval('P6M'))->format('Y-m-d H:i:s');

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
                    $arFilter['ITEMS']['PERIOD'] = [
                        'SIGN' => $sSymbol,
                        'VALUE' => $thisValue
                    ];
                }
            } elseif ($sKey == 'STATUS') {
                $arFilter['ITEMS']['STATUS'] = $sValue;
            } elseif ($sKey == 'EMPLOYEE') {
                $arFilter['USERS']['ID'] = preg_replace('/[^0-9]/', '', $sValue);
            }
        }

        if (empty($arDepartments) && !$arFilterFields['MAIN'] && !$arFilter['USERS']['ID']) {
            $arFilter['USERS']['UF_DEPARTMENT'][] = $arRole['DEPARTMENT'];
        }

        $arFilter[] = [
            'LOGIC' => 'AND',
            '!LAST_NAME' => '',
            '!NAME' => '',
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
            'filter' => $arFilterMain['USERS'],
            'order' => $arUserOrder
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
                'FIO' => $sFIO,
                'DEPARTMENT' => $sDepartment,
            ];

            $arRecords['ITEMS'][$iUserID]['data'] = [
                'ID' => $iUserID,
                'USER_ID' => $iUserID,
                'FIO' => $sFIO,
                'DEPARTMENT' => $sDepartment,
                'STATUS' => 'Нет'
            ];
        }

        $arSelect = [
            "ID",
            "NAME",
            "DATE_CREATE",
            "CREATED_BY",
            "PROPERTY_CRT_NUMBER",
            "PROPERTY_TYPE_VACCINATION",
            "PROPERTY_DATE_VACCINATION",
            "PROPERTY_INFO_DISEASE",
            "PROPERTY_DATE_RECOVERY",
            "PROPERTY_MEDOTVOD",
            "PROPERTY_DATE_END_MEDOTVOD",
            "PROPERTY_CRT_FILE",
            "PROPERTY_MEDOTVOD_FILE",
        ];

        /* Если есть пользователи и не выбран фильтр "Не предоставили сведения" */
        if ($arUsers && $arFilterMain['ITEMS']['STATUS'] != 'N') {
            $arFilter = ["IBLOCK_CODE" => "vaccination_covid19", "ACTIVE_DATE" => "Y", "ACTIVE" => "Y", "CREATED_BY" => array_keys($arUsers)];

            if ($arFilterMain['ITEMS']['PERIOD']) {
                $sSign = $arFilterMain['ITEMS']['PERIOD']['SIGN'];
                $thisValue = $arFilterMain['ITEMS']['PERIOD']['VALUE'];

                $arFilter[] = [
                    'LOGIC' => 'OR',
                    "{$sSign}PROPERTY_DATE_RECOVERY"  => $thisValue,
                    "{$sSign}PROPERTY_DATE_VACCINATION"  => $thisValue,
                    "{$sSign}PROPERTY_DATE_END_MEDOTVOD"  => $thisValue
                ];
            }

            $arUsersWithInfo = [];

            $arOrder = [];

            if ($_REQUEST['by']) {
                $arOrder = [
                    'PROPERTY_' . $_REQUEST['by'] =>  $_REQUEST['order']
                ];
            }

            $obRecords = CIBlockElement::GetList($arOrder, $arFilter, false, [], $arSelect);

            while ($arRecord = $obRecords->fetch()) {
                $iUserID = $arRecord['CREATED_BY'];
                $iCustomElementID = "{$iUserID}_{$arRecord['ID']}";

                $sDate = $arRecord['DATE_CREATE'];
                $sFileCrt = CFile::GetPath($arRecord['PROPERTY_CRT_FILE_VALUE']);
                $sFileMedotvod = CFile::GetPath($arRecord['PROPERTY_MEDOTVOD_FILE_VALUE']);

                $sDateRevaccination = $this->getDateRevaccination(
                    [
                        strtotime($arRecord['PROPERTY_DATE_VACCINATION_VALUE']),
                        strtotime($arRecord['PROPERTY_DATE_RECOVERY_VALUE']),
                    ]
                );

                if (strtotime($sDateRevaccination) < strtotime($arRecord['PROPERTY_DATE_END_MEDOTVOD_VALUE'])) {
                    $sDateRevaccination = $arRecord['PROPERTY_DATE_END_MEDOTVOD_VALUE'];
                }

                $arRecords['ITEMS'][$iCustomElementID]['data'] = [
                    'ID' => $iCustomElementID,
                    'USER_ID' => $iUserID,
                    'STATUS' => 'Да',
                    'DATE' => $sDate,
                    'FIO' => $arUsers[$iUserID]['FIO'],
                    'DEPARTMENT' => $arUsers[$iUserID]['DEPARTMENT'],
                    'POSITION' => $arUsers[$iUserID]['POSITION'],
                    'CRT_NUMBER' => $arRecord['PROPERTY_CRT_NUMBER_VALUE'],
                    'TYPE_VACCINATION' => $arRecord['PROPERTY_TYPE_VACCINATION_VALUE'],
                    'DATE_VACCINATION' => $arRecord['PROPERTY_DATE_VACCINATION_VALUE'],
                    'INFO_DISEASE' => $arRecord['PROPERTY_INFO_DISEASE_VALUE'],
                    'DATE_RECOVERY' => $arRecord['PROPERTY_DATE_RECOVERY_VALUE'],
                    'MEDOTVOD' => $arRecord['PROPERTY_MEDOTVOD_VALUE'],
                    'DATE_END_MEDOTVOD' => $arRecord['PROPERTY_DATE_END_MEDOTVOD_VALUE'],
                    'CRT_FILE' => ($sFileCrt) ? "<a href ='$sFileCrt' download>Скачать</a>" : "",
                    'MEDOTVOD_FILE' => ($sFileMedotvod) ? "<a href ='$sFileMedotvod' download>$sFileMedotvod</a>" : "",
                    'CRT_FILE_URL' => $_SERVER['HTTP_ORIGIN'] . $sFileCrt,
                    'MEDOTVOD_FILE_URL' => $_SERVER['HTTP_ORIGIN'] . $sFileMedotvod,
                    'DATE_REVACCINATION' => $sDateRevaccination
                ];

                if (!$arRecords['ITEMS'][$iUserID]) {
                    $arRecords['COUNT']++;
                } else {
                    unset($arRecords['ITEMS'][$iUserID]);
                    array_push($arUsersWithInfo, $iUserID);
                }
            }

            /*
            Если есть фильтры:
            1) По тем, кто предоставил сведения
            2) По записям в ИБ 'vaccination_covid19'
            Не показывать тех, кто не предоставил сведения о вакцинации
            */
            if ($arFilterMain['ITEMS']['STATUS'] == 'Y' || $arFilterMain['ITEMS']) {
                foreach ($arRecords['ITEMS'] as $arItem) {
                    $iUserID = $arItem['data']['USER_ID'];

                    if (!in_array($iUserID, $arUsersWithInfo)) {
                        unset($arRecords['ITEMS'][$iUserID]);
                        $arRecords['COUNT']--;
                    }
                }
            }

            /* Передаются ли ID при экпорте */
            if ($arElementsID) {
                $arNewRecords = [
                    'ITEMS' => [],
                    'COUNT' => $arRecords['COUNT']
                ];

                foreach ($arElementsID as $iID) {
                    $arNewRecords['ITEMS'][$iID] = $arRecords['ITEMS'][$iID];
                }

                $arRecords = $arNewRecords;
                unset($arNewRecords);
            }
        }

        return $arRecords;
    }

    public function getDateRevaccination($arDates = [])
    {
        rsort($arDates);

        if ($arDates[0] > 0) {
            $obVaccination = new \DateTime(date('d.m.Y H:i:s', $arDates[0]));
            $sDateRevaccination = $obVaccination->add(new \DateInterval('P6M'))->format('d.m.Y');
        } else {
            $sDateRevaccination = '';
        }

        return $sDateRevaccination;
    }

    public function getManagers()
    {
        $obGroups = CGroup::GetList ($by = "c_sort", $order = "asc", Array ("STRING_ID" => 'VACCINATION_COVID19'));
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
                'ROLE' => 'USER',
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

    public function alertUsersRevaccination()
    {
        CModule::IncludeModule('im');

        $arDepartments = CIntranetUtils::GetStructure();

        $arUsers = [];
        $obUsers = UserTable::getList([
            'select' => ['ID', 'EMAIL', 'LAST_NAME', 'NAME', 'SECOND_NAME', 'UF_WORK_POSITION', 'UF_DEPARTMENT'],
        ]);

        while ($arItem = $obUsers->fetch()) {
            $iDepartment = current($arItem['UF_DEPARTMENT']);

            $arUsers[$arItem['ID']] = [
                'FIO' => "{$arItem['LAST_NAME']} {$arItem['NAME']} {$arItem['SECOND_NAME']}",
                'EMAIL' => $arItem['EMAIL'],
                'POSITION' => $arItem['UF_WORK_POSITION'],
                'DEPARTMENT' => $arDepartments['DATA'][$iDepartment]['NAME'],
            ];
        }

        $arSelect = [
            "ID",
            "CREATED_BY",
            "PROPERTY_DATE_VACCINATION",
            "PROPERTY_DATE_RECOVERY",
            "PROPERTY_DATE_END_MEDOTVOD"
        ];

        $arFilter = ["IBLOCK_CODE" => "vaccination_covid19", "ACTIVE_DATE" => "Y", "ACTIVE" => "Y"];
        $obRecords = CIBlockElement::GetList([], $arFilter, false, [], $arSelect);

        $sMessage = '';

        while ($arRecord = $obRecords->fetch()) {
            $sDateRevaccination = $this->getDateRevaccination(
                [
                    strtotime($arRecord['PROPERTY_DATE_VACCINATION_VALUE']),
                    strtotime($arRecord['PROPERTY_DATE_RECOVERY_VALUE']),
                ]
            );

            if (strtotime($sDateRevaccination) < strtotime($arRecord['PROPERTY_DATE_END_MEDOTVOD_VALUE'])) {
                $sDateRevaccination = $arRecord['PROPERTY_DATE_END_MEDOTVOD_VALUE'];
            }

            if ($sDateRevaccination) {
                $obDate = new \DateTime($sDateRevaccination);
                $obInterval = (new \DateTime())->diff($obDate);
                $iIntervalDays = (int) $obInterval->format('%R%a');

                if ($iIntervalDays <= 3 && $iIntervalDays >= 0) {
                    $sFIO = $arUsers[$arRecord['CREATED_BY']]['FIO'];
                    $sPosition = $arUsers[$arRecord['CREATED_BY']]['POSITION'];
                    $sDepartment = $arUsers[$arRecord['CREATED_BY']]['DEPARTMENT'];
                    $sCurrentMeassge = "\nОИВ/ПАП: $sDepartment Должность: $sPosition  Пользователь: $sFIO;  Дата ревакцинации: $sDateRevaccination";
                    $sMessage .= $sCurrentMeassge;

                    \CIMMessenger::Add(array(
                        'TITLE' => 'Ревакцинация пользователей',
                        'MESSAGE' => "\nПользователь: $sFIO; Дата ревакцинации: $sDateRevaccination",
                        'TO_USER_ID' => $arRecord['CREATED_BY'],
                        'FROM_USER_ID' => 2661,
                        'MESSAGE_TYPE' => 'S',
                        'NOTIFY_MODULE' => 'intranet',
                        'NOTIFY_TYPE' => 2,
                    ));

                    if ($sEmailUser = $arUsers[$arRecord['CREATED_BY']]['EMAIL']) {
                        \CEvent::Send("ALERT_LPA", 's1', ['TEXT' => "$sCurrentMeassge", 'THEME' => 'Ревакцинация пользователей', 'EMAIL_TO' => $sEmailUser]);
                    }
                }
            }
        }

        if ($sMessage) {
            foreach ($this->getManagers() as $iUserID) {
                $sTheme = 'Ревакцинация пользователей';

                \CIMMessenger::Add(array(
                    'TITLE' => $sTheme,
                    'MESSAGE' => $sMessage,
                    'TO_USER_ID' => $iUserID,
                    'FROM_USER_ID' => 2661,
                    'MESSAGE_TYPE' => 'S',
                    'NOTIFY_MODULE' => 'intranet',
                    'NOTIFY_TYPE' => 2,
                ));

                if ($sEmailUser = $arUsers[$iUserID]['EMAIL']) {
                    \CEvent::Send("ALERT_LPA", 's1', ['TEXT' => $sMessage, 'THEME' => $sTheme, 'EMAIL_TO' => $sEmailUser]);
                }
            }
        }
    }

    public function getRecordsForApi() {
        $arAllUsers = MentoringUsers::getUsersWithStrcuture();

        $arSelect = [
            "ID",
            "NAME",
            "DATE_CREATE",
            "CREATED_BY",
            "PROPERTY_CRT_NUMBER",
            "PROPERTY_TYPE_VACCINATION",
            "PROPERTY_DATE_VACCINATION",
            "PROPERTY_INFO_DISEASE",
            "PROPERTY_DATE_RECOVERY",
            "PROPERTY_MEDOTVOD",
            "PROPERTY_DATE_END_MEDOTVOD",
        ];

        $arFilter = ["IBLOCK_CODE" => "vaccination_covid19", "ACTIVE_DATE" => "Y", "ACTIVE" => "Y"];
        $obRecords = CIBlockElement::GetList([], $arFilter, false, [], $arSelect);

        while ($arRecord = $obRecords->fetch()) {
            $iUserID = $arRecord['CREATED_BY'];

            $sDateRevaccination = $this->getDateRevaccination(
                [
                    strtotime($arRecord['PROPERTY_DATE_VACCINATION_VALUE']),
                    strtotime($arRecord['PROPERTY_DATE_RECOVERY_VALUE']),
                ]
            );

            if (strtotime($sDateRevaccination) < strtotime($arRecord['PROPERTY_DATE_END_MEDOTVOD_VALUE'])) {
                $sDateRevaccination = $arRecord['PROPERTY_DATE_END_MEDOTVOD_VALUE'];
            }

            $arAllUsers[$iUserID]['VACCINATION'] = [
                'CRT_NUMBER' => $arRecord['PROPERTY_CRT_NUMBER_VALUE'],
                'TYPE_VACCINATION' => $arRecord['PROPERTY_TYPE_VACCINATION_VALUE'],
                'DATE_VACCINATION' => $arRecord['PROPERTY_DATE_VACCINATION_VALUE'],
                'INFO_DISEASE' => $arRecord['PROPERTY_INFO_DISEASE_VALUE'],
                'DATE_RECOVERY' => $arRecord['PROPERTY_DATE_RECOVERY_VALUE'],
                'MEDOTVOD' => $arRecord['PROPERTY_MEDOTVOD_VALUE'],
                'DATE_END_MEDOTVOD' => $arRecord['PROPERTY_DATE_END_MEDOTVOD_VALUE'],
                'DATE_REVACCINATION' => $sDateRevaccination
            ];
        }

        $arDepartments = [];

        foreach ($arAllUsers as $iUserID => $arUser) {
            if ($arUser['DEPARTMENT']['PODVED'] == 'Y') {
                unset($arAllUsers[$iUserID]);
            } else {
                unset($arUser['LOGIN']);
                unset($arUser['SID']);
                unset($arUser['EMAIL']);
                unset($arUser['GOVERMENT']);
                unset($arUser['ACTIVE']);

                if ($arUser['DEPARTMENT']['NAME']) {
                    $arDepartments[] = $arUser;
                }
            }
        }

        return $arDepartments;
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
