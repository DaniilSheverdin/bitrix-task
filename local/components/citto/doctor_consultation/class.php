<?php

namespace Citto\DoctorConsultation;

use \Bitrix\Main\Loader;
use \Bitrix\Main\Application;
use CBitrixComponent;
use CIBlockElement;
use Bitrix\Main\UserTable;
use CIntranetUtils;
use CIMMessenger;
use CModule;
use CGroup;


if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

CModule::IncludeModule('im');

class Component extends CBitrixComponent
{
    private $_request;
    private $sFilterID = 'filter_instruction';
    private $sGridID = 'grid_instruction';
    private $arColumns = [
        ['id' => 'ID', 'name' => 'ID', 'sort' => false, 'default' => false],
        ['id' => 'FIO', 'name' => 'ФИО', 'sort' => false, 'default' => true],
        ['id' => 'SNILS', 'name' => 'СНИЛС', 'sort' => false, 'default' => true],
        ['id' => 'PHONE', 'name' => 'Телефон', 'sort' => false, 'default' => true],
        ['id' => 'INFORMATION', 'name' => 'Сведения', 'sort' => false, 'default' => true],
        ['id' => 'DATE_CREATE', 'name' => 'Дата записи', 'sort' => false, 'default' => true],
        ['id' => 'DATE_MODIFY_STATUS', 'name' => 'Дата изменения статуса', 'sort' => false, 'default' => true],
        ['id' => 'STATUS', 'name' => 'Статус', 'sort' => false, 'default' => true],
        ['id' => 'REASON', 'name' => 'Причина', 'sort' => false, 'default' => true],
    ];
    private $arFilterData = null;
    private $arRole = null;

    private function getFilterData()
    {
        if (is_null($this->arFilterData)) {
            $this->arFilterData = [
                'DATE_CREATE' => [
                    'id' => 'DATE_CREATE',
                    'name' => 'Дата записи',
                    'type' => 'date'
                ],
                'STATUS' => [
                    'id' => 'STATUS',
                    'name' => 'Статус',
                    'type' => 'list',
                    'items' => [
                        'Новая' => 'Новая',
                        'Принята' => 'Принята',
                        'Отклонена' => 'Отклонена',
                        'Консультация проведена' => 'Консультация проведена',
                        'Консультация не проведена' => 'Консультация не проведена'
                    ]
                ],
                'EMPLOYEE' => [
                    'id' => 'EMPLOYEE',
                    'name' => 'Сотрудник',
                    'type' => 'string',
                ],
            ];

            $arRole = $this->getRole($GLOBALS["USER"]->GetID());

            if ($arRole['ROLE'] != 'ADMIN') {
                $this->arFilterData['MAIN']['params']['siteDepartmentId'] = $arRole['DEPARTMENT'];
            }
        }

        return $this->arFilterData;
    }

    private function getFilter()
    {
        $arFilter = [];

        $obFilterOptions = new \Bitrix\Main\UI\Filter\Options($this->sFilterID);
        $arFilterFields = $obFilterOptions->getFilter($this->getFilterData());
        $arRole = $this->getRole($GLOBALS["USER"]->GetID());

        if ($arRole['ROLE'] == 'USER') {
            $arFilter['USERS']['ID'] = $GLOBALS["USER"]->GetID();
        }

        foreach ($arFilterFields as $sKey => $sValue) {
            if ($sKey == 'DATE_CREATE_datesel') {
                $sSymbol = "";
                $thisValue = null;

                $sFrom = (new \DateTime($arFilterFields['DATE_CREATE_from']))->format('d.m.Y H:i:s');
                $sTo = (new \DateTime($arFilterFields['DATE_CREATE_to']))->format('d.m.Y H:i:s');

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
            } elseif ($sKey == 'STATUS') {
                $arFilter['STATUS'] = $sValue;
            } elseif ($sKey == 'EMPLOYEE') {
                $arFilter['EMPLOYEE'] = "%{$sValue}%";
            }
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
        $arRole = $this->getRole($GLOBALS["USER"]->GetID());

        $arRecords = [
            'ITEMS' => [],
            'COUNT' => 0,
            'SELECT_DATE_IDS' => []
        ];

        $arFilterMain = $this->getFilter();

        $arSelect = [
            "ID",
            "NAME",
            "CREATED_BY",
            "DATE_CREATE",
            "DATE_ACTIVE_FROM",
            "DATE_ACTIVE_TO",
            "PROPERTY_FIO",
            "PROPERTY_BIRTHDAY",
            "PROPERTY_SNILS",
            "PROPERTY_PHONE",
            "PROPERTY_STATUS",
            "PROPERTY_REASON",
            "PROPERTY_DATE_MODIFY_STATUS",
            "PROPERTY_TEMPERATURE",
            "PROPERTY_ARI",
            "PROPERTY_FIRST_ACTION",
            "PROPERTY_NEED_LIST",
            "PROPERTY_FIO_STRING",
        ];

        $arFilter = ["IBLOCK_CODE" => "doctor_consultation", "ID" => $arElementsID, "ACTIVE_DATE" => "Y", "ACTIVE" => "Y"];

        if ($arRole['ROLE'] != 'ADMIN') {
            $arFilter['PROPERTY_FIO'] = $GLOBALS["USER"]->GetID();
        }

        if ($arFilterMain['EMPLOYEE']) {
            $arFilter['PROPERTY_FIO_STRING'] = $arFilterMain['EMPLOYEE'];
        }

        if ($arFilterMain['STATUS']) {
            if ($arFilterMain['STATUS'] == 'Новая') {
                $arFilter['!PROPERTY_STATUS_VALUE'] = [
                    'Принята',
                    'Отклонена',
                    'Консультация проведена',
                    'Консультация не проведена'
                ];
            } else {
                $arFilter['PROPERTY_STATUS_VALUE'] = $arFilterMain['STATUS'];
            }
        }

        if ($arFilterMain['DATE_CREATE']) {
            $sSign = $arFilterMain['DATE_CREATE']['SIGN'];
            $thisValue = $arFilterMain['DATE_CREATE']['VALUE'];

            $arFilter[] = [
                'LOGIC' => 'OR',
                "{$sSign}DATE_CREATE" => $thisValue,
            ];
        }

        $arUsers = [];
        $obRecords = CIBlockElement::GetList([], $arFilter, false, [], $arSelect);

        while ($arRecord = $obRecords->fetch()) {
            $iRecordID = $arRecord['ID'];
            $iUserID = $arRecord['PROPERTY_FIO_VALUE'];
            $arUsers[$iUserID][] = $iRecordID;
            $iInformation = "";

            if ($sTemperature = $arRecord['PROPERTY_TEMPERATURE_VALUE']) {
                $iInformation .= "Температура: $sTemperature<br>";
            }

            if ($sAri = $arRecord['PROPERTY_ARI_VALUE']) {
                $iInformation .= "Признаки ОРВИ: $sAri<br>";
            }

            if ($sFirstAction = $arRecord['PROPERTY_FIRST_ACTION_VALUE']) {
                $iInformation .= "Статус: $sFirstAction<br>";

                if ($sFirstAction == 'Впервые' && $sNeedList = $arRecord['PROPERTY_NEED_LIST_VALUE']) {
                    $iInformation .= "ЛН: $sNeedList<br>";
                }
            }

            $sStatus = ($arRecord['PROPERTY_STATUS_VALUE']) ? $arRecord['PROPERTY_STATUS_VALUE'] : 'Новая';

            /* Если прошло более 2 часов с момента подачи заявки */
            if ($sStatus == 'Новая' || $sStatus == 'Принята') {
                if (time() > strtotime($arRecord['DATE_CREATE']) + 3600 * 2) {
                    $arRecords['SELECT_DATE_IDS'][]= $iRecordID;
                }
            }

            $arRecords['ITEMS'][$iRecordID]['data'] = [
                'ID' => $iRecordID,
                'CREATED_BY' =>  $arRecord['CREATED_BY'],
                'DATE_CREATE' => $arRecord['DATE_CREATE'],
                'FIO' => $arRecord['PROPERTY_FIO_STRING_VALUE'],
                'BIRTHDAY' => $arRecord['PROPERTY_BIRTHDAY_VALUE'],
                'SNILS' => $arRecord['PROPERTY_SNILS_VALUE'],
                'PHONE' => $arRecord['PROPERTY_PHONE_VALUE'],
                'STATUS' => $sStatus,
                'DATE_MODIFY_STATUS' => $arRecord['PROPERTY_DATE_MODIFY_STATUS_VALUE'],
                'REASON' => $arRecord['PROPERTY_REASON_VALUE'],
                'INFORMATION' => $iInformation
            ];

            $arRecords['COUNT']++;
        }

        return $arRecords;
    }

    public function getDoctors()
    {
        $obGroups = CGroup::GetList ($by = "c_sort", $order = "asc", Array ("STRING_ID" => 'DOCTORS'));
        $arGroups = $obGroups->Fetch();
        $arDoctors = CGroup::GetGroupUser($arGroups['ID']);

        return $arDoctors;
    }

    public function getRole($iUserID = 0)
    {
        $arDoctors = $this->getDoctors();

        if (is_null($this->arRole)) {
            $iDepartment = CIntranetUtils::GetUserDepartments($iUserID)[0];

            $this->arRole = [
                'ROLE' => 'USER',
                'DEPARTMENT' => $iDepartment,
            ];

            if ($GLOBALS["USER"]->IsAdmin() || in_array($iUserID, $arDoctors)) {
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

    public function alertUsers($arUsers = [])
    {
        if ($arUsers) {
            $sTheme = 'Консультация врача';

            $arUsersMail = [];
            $obUsers = UserTable::getList([
                'select' => ['ID', 'EMAIL'],
            ]);

            while ($arItem = $obUsers->fetch()) {
                $arUsersMail[$arItem['ID']] = [
                    'EMAIL' => $arItem['EMAIL']
                ];
            }

            foreach ($arUsers as $arUser) {
                $sMessage = "Дата записи: {$arUser['DATE_CREATE']} Статус заявки: {$arUser['STATUS']}";

                if ($sEmailUser = $arUsersMail[$arUser['CREATED_BY']]) {
                    \CEvent::Send("ALERT_LPA", 's1', ['TEXT' => $sMessage, 'THEME' => $sTheme, 'EMAIL_TO' => $sEmailUser]);
                }

                CIMMessenger::Add(array(
                    'TITLE' => $sTheme,
                    'MESSAGE' => $sMessage,
                    'TO_USER_ID' => $arUser['CREATED_BY'],
                    'FROM_USER_ID' => 2661,
                    'MESSAGE_TYPE' => 'S',
                    'NOTIFY_MODULE' => 'intranet',
                    'NOTIFY_TYPE' => 2,
                ));
            }
        }
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
