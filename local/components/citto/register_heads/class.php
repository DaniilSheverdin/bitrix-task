<?php

namespace Citto\RegisterHeads;

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
        ['id' => 'OIV', 'name' => 'ОИВ', 'sort' => 'FIO', 'default' => true],
        ['id' => 'SUBORDINATE', 'name' => 'Подведомственное учреждение', 'sort' => false, 'default' => true],
        ['id' => 'FIO_HEAD', 'name' => 'ФИО руководителя', 'sort' => false, 'default' => true],
        ['id' => 'DATE_ASSIGN', 'name' => 'Дата назначения', 'sort' => false, 'default' => true],
        ['id' => 'YEARS_ASSIGN', 'name' => 'Срок назначения (лет)', 'sort' => false, 'default' => true],
        ['id' => 'DATE_END_CONTRACT', 'name' => 'Дата окончания контракта', 'sort' => false, 'default' => true],
        ['id' => 'CREATED_BY', 'name' => 'ФИО отв. лица от ОИВ', 'sort' => false, 'default' => true],
        ['id' => 'DATE_CREATE', 'name' => 'Дата внесения', 'sort' => false, 'default' => true],
        ['id' => 'CONTEST', 'name' => 'Назначение по конкурсу на вакансию', 'sort' => false, 'default' => true],
        ['id' => 'RESERVE', 'name' => 'Назначение из резерва управленческих кадров', 'sort' => false, 'default' => true],
    ];
    private $arFilterData = null;

    private function getFilterData()
    {
        if (is_null($this->arFilterData)) {
            $this->arFilterData = [
                'OIV'               => [
                    'id'   => 'OIV',
                    'name' => 'ОИВ',
                    'type' => 'string',
                ],
                'SUBORDINATE'       => [
                    'id'   => 'SUBORDINATE',
                    'name' => 'Подведомственное учреждение',
                    'type' => 'string',
                ],
                'YEARS_ASSIGN'      => [
                    'id'   => 'YEARS_ASSIGN',
                    'name' => 'Срок назначения (лет)',
                    'type' => 'number',
                ],
                'FIO_HEAD'          => [
                    'id'   => 'FIO_HEAD',
                    'name' => 'ФИО руководителя',
                    'type' => 'string',
                ],
                'DATE_ASSIGN'       => [
                    'id'   => 'DATE_ASSIGN',
                    'name' => 'Дата назначения',
                    'type' => 'date'
                ],
                'DATE_END_CONTRACT' => [
                    'id'   => 'DATE_END_CONTRACT',
                    'name' => 'Дата окончания контракта',
                    'type' => 'date'
                ],
                'CREATED_BY'        => [
                    'id'     => 'CREATED_BY',
                    'name'   => 'ФИО отв. лица от ОИВ',
                    'type'   => 'dest_selector',
                    'params' => [
                        'contextCode'       => 'U',
                        'multiple'          => 'N',
                        'enableUsers'       => 'Y',
                        'enableDepartments' => 'N'
                    ]
                ],
                'CONTEST'       => [
                    'id'   => 'CONTEST',
                    'name' => 'Назначение по конкурсу на вакансию',
                    'type' => 'checkbox',
                ],
                'RESERVE'       => [
                    'id'   => 'RESERVE',
                    'name' => 'Назначение из резерва управленческих кадров',
                    'type' => 'checkbox',
                ],
            ];
        }

        return $this->arFilterData;
    }

    private function getFilter()
    {
        $arFilter = [];

        $obFilterOptions = new \Bitrix\Main\UI\Filter\Options($this->sFilterID);
        $arFilterFields = $obFilterOptions->getFilter($this->getFilterData());
        $sRole = $this->getRole($GLOBALS["USER"]->GetID());

        if ($sRole == 'USER') {
            $arFilter['CREATED_BY']['ID'] = $GLOBALS["USER"]->GetID();
        }

        foreach ($arFilterFields as $sKey => $sValue) {
            if (
                $sKey == 'DATE_ASSIGN_datesel' ||
                $sKey == 'DATE_END_CONTRACT_datesel'
            ) {
                $sFieldName = str_replace('_datesel', '', $sKey);

                $sSymbol = "";
                $thisValue = null;

                $sFrom = (new \DateTime($arFilterFields["{$sFieldName}_from"]))->sub(new \DateInterval('P6MT1S'))->format('Y-m-d H:i:s');
                $sTo = (new \DateTime($arFilterFields["{$sFieldName}_to"]))->sub(new \DateInterval('P6M'))->format('Y-m-d H:i:s');

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
                    $arFilter[$sFieldName] = [
                        'SIGN'  => $sSymbol,
                        'VALUE' => $thisValue
                    ];
                }
            } elseif (
                $sKey == 'OIV' ||
                $sKey == 'SUBORDINATE' ||
                $sKey == 'FIO_HEAD'
            ) {
                $arFilter[$sKey] = $sValue;
            } elseif (
                $sKey == 'CREATED_BY'
            ) {
                $arFilter[$sKey]['ID'] = preg_replace('/[^0-9]/', '', $sValue);
            } elseif (
                $sKey == 'YEARS_ASSIGN_from' ||
                $sKey == 'YEARS_ASSIGN_to'
            ) {
                $arFilter['YEARS_ASSIGN'] = [
                    $arFilterFields['YEARS_ASSIGN_from'],
                    $arFilterFields['YEARS_ASSIGN_to']
                ];
            } elseif (
                $sKey == 'CONTEST' ||
                $sKey == 'RESERVE'
            ) {
                $arFilter[$sKey] = ($sValue == 'Y') ? 'Y' : false;
            }
        }

        return $arFilter;
    }

    public function getRecords($arElementsID = [], $arMainFilter = [])
    {
        $arGridFilter = (empty($arMainFilter)) ? $this->getFilter() : $arMainFilter;

        $arRecords = [
            'ITEMS' => [],
            'COUNT' => 0
        ];

        $arUserOrder = [
            'LAST_NAME' => 'asc'
        ];

        if ($_REQUEST['by'] == 'FIO') {
            $arUserOrder['LAST_NAME'] = $_REQUEST['order'];
        }

        $obUsers = UserTable::getList([
            'select' => ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'UF_DEPARTMENT', 'UF_WORK_POSITION'],
            'filter' => [],
            'order'  => $arUserOrder
        ]);

        $arUsers = [];

        while ($arUser = $obUsers->fetch()) {
            $iUserID = $arUser['ID'];
            $sFIO = "{$arUser['LAST_NAME']} {$arUser['NAME']} {$arUser['SECOND_NAME']}";

            $arUsers[$iUserID] = [
                'FIO' => $sFIO,
            ];
        }

        $arSelect = [
            "ID",
            "NAME",
            "DATE_CREATE",
            "CREATED_BY",
            "PROPERTY_OIV",
            "PROPERTY_SUBORDINATE",
            "PROPERTY_FIO_HEAD",
            "PROPERTY_DATE_ASSIGN",
            "PROPERTY_YEARS_ASSIGN",
            "PROPERTY_DATE_END_CONTRACT",
            "PROPERTY_ALERT_1_MONTH",
            "PROPERTY_ALERT_3_MONTH",
            "PROPERTY_CONTEST",
            "PROPERTY_RESERVE",
        ];

        $arFilter = [];

        if ($arMainFilter) {
            $arFilter = $arMainFilter;
        }

        $arFilter['IBLOCK_CODE'] = "register_heads";
        $arFilter['ID'] = $arElementsID;
        $arFilter['ACTIVE_DATE'] = "Y";
        $arFilter['ACTIVE'] = "Y";

        if ($arGridFilter) {
            $arFilter['PROPERTY_OIV'] = "%{$arGridFilter['OIV']}%";
            $arFilter['PROPERTY_SUBORDINATE'] = "%{$arGridFilter['SUBORDINATE']}%";
            $arFilter['PROPERTY_FIO_HEAD'] = "%{$arGridFilter['FIO_HEAD']}%";
            $arFilter['CREATED_BY'] = $arGridFilter['CREATED_BY']['ID'];

            $arFilter[$arGridFilter['DATE_ASSIGN']['SIGN'] . 'PROPERTY_DATE_ASSIGN'] = $arGridFilter['DATE_ASSIGN']['VALUE'];
            $arFilter[$arGridFilter['DATE_END_CONTRACT']['SIGN'] . 'PROPERTY_DATE_END_CONTRACT'] = $arGridFilter['DATE_END_CONTRACT']['VALUE'];

            $arFilter['><PROPERTY_YEARS_ASSIGN'] = $arGridFilter['YEARS_ASSIGN'];

            $arFilter[$arGridFilter['ALERT_1_MONTH']['SIGN'] . 'PROPERTY_ALERT_1_MONTH'] = $arGridFilter['ALERT_1_MONTH']['VALUE'];
            $arFilter[$arGridFilter['ALERT_3_MONTH']['SIGN'] . 'PROPERTY_ALERT_3_MONTH'] = $arGridFilter['ALERT_3_MONTH']['VALUE'];

            if (isset($arGridFilter['CONTEST'])) {
                $arFilter['PROPERTY_CONTEST'] = $arGridFilter['CONTEST'];
            }

            if (isset($arGridFilter['RESERVE'])) {
                $arFilter['PROPERTY_RESERVE'] = $arGridFilter['RESERVE'];
            }
        }

        $obOrders = CIBlockElement::GetList(array(), $arFilter, false, [], $arSelect);
        while ($arItem = $obOrders->GetNext()) {
            $arRecords['ITEMS'][$arItem['ID']]['data'] = [
                'ID'                => $arItem['ID'],
                'OIV'               => $arItem['~PROPERTY_OIV_VALUE'],
                'SUBORDINATE'       => $arItem['~PROPERTY_SUBORDINATE_VALUE'],
                'FIO_HEAD'          => $arItem['~PROPERTY_FIO_HEAD_VALUE'],
                'DATE_ASSIGN'       => $arItem['PROPERTY_DATE_ASSIGN_VALUE'],
                'YEARS_ASSIGN'      => $arItem['PROPERTY_YEARS_ASSIGN_VALUE'],
                'DATE_END_CONTRACT' => $arItem['PROPERTY_DATE_END_CONTRACT_VALUE'],
                'CREATED_BY_ID'     => $arItem['CREATED_BY'],
                'CREATED_BY'        => $arUsers[$arItem['CREATED_BY']]['FIO'],
                'DATE_CREATE'       => $arItem['DATE_CREATE'],
                'ALERT_1_MONTH'     => $arItem['PROPERTY_ALERT_1_MONTH_VALUE'],
                'ALERT_3_MONTH'     => $arItem['PROPERTY_ALERT_3_MONTH_VALUE'],
                'CONTEST'           => ($arItem['PROPERTY_CONTEST_VALUE']) ? '+' : '-',
                'RESERVE'           => ($arItem['PROPERTY_RESERVE_VALUE']) ? '+' : '-',
            ];
        }

        return $arRecords;
    }

    public function getManagers()
    {
        $obGroups = CGroup::GetList($by = "c_sort", $order = "asc", ["STRING_ID" => 'REGISTER_HEADS']);
        $arGroups = $obGroups->Fetch();
        $arManagers = CGroup::GetGroupUser($arGroups['ID']);

        return $arManagers;
    }

    public function getRole($iUserID = 0)
    {
        if ($iUserID == 0) {
            $iUserID = $GLOBALS["USER"]->GetID();
        }

        $arManagers = $this->getManagers();

        if ($GLOBALS["USER"]->IsAdmin()) {
            $sRole = 'ADMIN';
        } else if (in_array($iUserID, $arManagers)) {
            $sRole = 'MANAGER';
        } else {
            $sRole = 'USER';
        }

        return $sRole;
    }

    public function alertUsersContract()
    {
        CModule::IncludeModule('im');

        $arUsers = [];
        $obUsers = UserTable::getList([
            'select' => ['ID', 'EMAIL', 'LAST_NAME', 'NAME', 'SECOND_NAME', 'UF_WORK_POSITION', 'UF_DEPARTMENT'],
        ]);

        while ($arItem = $obUsers->fetch()) {
            $arUsers[$arItem['ID']] = [
                'FIO'      => "{$arItem['LAST_NAME']} {$arItem['NAME']} {$arItem['SECOND_NAME']}",
                'EMAIL'    => $arItem['EMAIL'],
                'POSITION' => $arItem['UF_WORK_POSITION'],
            ];
        }

        $sDate1Month = (new \DateTime())->modify('+1 month')->format('Y-m-d');
        $sDate3Month = (new \DateTime())->modify('+3 month')->format('Y-m-d');

        $arRecords = $this->getRecords(
            [],
            [
                [
                    "LOGIC" => "OR",
                    [
                        '><PROPERTY_DATE_END_CONTRACT' => [$sDate1Month, $sDate3Month],
                        '!ALERT_3_MONTH'              => 'Y',
                    ],
                    [
                        '<PROPERTY_DATE_END_CONTRACT' => $sDate1Month,
                        '!ALERT_1_MONTH'              => 'Y',
                    ]
                ]
            ]
        );

        $arAlerts = [
            '1_MONTH' => [],
            '3_MONTH' => [],
        ];

        foreach ($arRecords['ITEMS'] as $arRecord) {
            $arItem = $arRecord['data'];

            if (
                $arItem['ALERT_3_MONTH'] != 'Y' &&
                $arItem['ALERT_1_MONTH'] != 'Y' &&
                strtotime($arItem['DATE_END_CONTRACT']) < strtotime($sDate3Month) &&
                strtotime($arItem['DATE_END_CONTRACT']) > strtotime($sDate1Month)
            ) {
                $arAlerts['3_MONTH'][] = $arItem;
            }

            if (
                $arItem['ALERT_1_MONTH'] != 'Y' &&
                strtotime($arItem['DATE_END_CONTRACT']) < strtotime($sDate1Month)
            ) {
                $arAlerts['1_MONTH'][] = $arItem;
            }
        }

        foreach ($arAlerts as $sTypeAlert => $arAlert) {
            foreach ($arAlert as $arInfo) {
                if ($sTypeAlert == '1_MONTH') {
                    $sMessage = "Через месяц истекает срок контракта руководителя - {$arInfo['FIO_HEAD']}. Рекомендуем запустить необходимые процедуры по подготовке к проведению с кандидатом комиссионного собеседования.<br>";
                    $sMessage .= "В Отдел по работе с персоналом необходимо направить пакет документов:<br>";
                    $sMessage .= "- справка – объективка на кандидата (по форме),<br>";
                    $sMessage .= "- ключевые показатели эффективности руководителя,<br>";
                    $sMessage .= "- должностная инструкция / выписка из документа, где прописан функционал руководителя,<br>";
                    $sMessage .= "- фото учреждения (самостоятельно подготовленные в виде брошюры).";

                    CIBlockElement::SetPropertyValuesEx($arInfo['ID'], false, ['ALERT_1_MONTH' => 'Y']);
                    $arRecipients = [
                        593,  // Петрова Екатерина
                        591,  // Леонтьева Татьяна
                        5810, // Гришаева Светлана
                        1502, // Кузнецова Юлия
                    ];
                } else {
                    $sMessage = "Через 3 месяца истекает срок контракта руководителя - {$arInfo['FIO_HEAD']}.<br>";
                    $sMessage .= "Рекомендуем запустить необходимые процедуры назначения на должность руководителя государственного унитарного предприятия, государственного учреждения Тульской области:<br>";
                    $sMessage .= "• <a target='_blank' href='http://corp.tularegion.local/register_heads/?action=contest&id={$arInfo['ID']}'>проведение конкурса на замещение вакантной должности руководителя государственного унитарного предприятия, государственного учреждения</a><br>";
                    $sMessage .= "• <a target='_blank' href='http://corp.tularegion.local/register_heads/?action=reserve&id={$arInfo['ID']}'>включение в резерв управленческих кадров Тульской области</a>";

                    CIBlockElement::SetPropertyValuesEx($arInfo['ID'], false, ['ALERT_3_MONTH' => 'Y']);
                    $arRecipients = [
                        593,  // Петрова Екатерина
                        591,  // Леонтьева Татьяна
                        5810, // Гришаева Светлана
                        1126, // Комарова Татьяна
                    ];
                }

                if (!in_array($arInfo['CREATED_BY_ID'], $arRecipients)) {
                    $arRecipients[] = $arInfo['CREATED_BY_ID'];
                }

                foreach ($arRecipients as $iUserID) {
                    $sTheme = 'Реестр руководителей';

                    \CIMMessenger::Add(array(
                        'TITLE'         => $sTheme,
                        'MESSAGE'       => str_replace('<br>', PHP_EOL, $sMessage),
                        'TO_USER_ID'    => $iUserID,
                        'FROM_USER_ID'  => 2661,
                        'MESSAGE_TYPE'  => 'S',
                        'NOTIFY_MODULE' => 'intranet',
                        'NOTIFY_TYPE'   => 2,
                    ));

                    if ($sEmailUser = $arUsers[$iUserID]['EMAIL']) {
                        \CEvent::Send("ALERT_REESTR_HEADS", 's1', ['TEXT' => $sMessage, 'THEME' => $sTheme, 'EMAIL_TO' => $sEmailUser]);
                    }
                }
            }
        }
    }

    private function executeRequests($obRequest)
    {
        $arGetRequest = $obRequest->getQueryList()->toArray();

        if ($this->getRole() != 'USER') {
            if ($arGetRequest['action'] == 'contest' && !empty($arGetRequest['id'])) {
                CIBlockElement::SetPropertyValuesEx($arGetRequest['id'], false, ['CONTEST' => 'Y']);
            }

            if ($arGetRequest['action'] == 'reserve' && !empty($arGetRequest['id'])) {
                $arFilter = [
                    'IBLOCK_CODE' => "register_heads",
                    'ID' => $arGetRequest['id'],
                    'PROPERTY_RESERVE' => false
                ];

                $obElements = CIBlockElement::GetList(array(), $arFilter, false, [], ['ID', 'CREATED_BY', 'PROPERTY_RESERVE']);

                if ($arItem = $obElements->fetch()) {
                    $sEmailUser = \CUser::GetByID($arItem['CREATED_BY'])->fetch()['EMAIL'];
                    if ($sEmailUser) {
                        $sTheme = "Перечень документов, представляемых кандидатами в Комиссию по формированию и подготовке резерва управленческих кадров Тульской области";
                        $sMessage = "<b>$sTheme<b>";
                        $sMessage .= "<br>1. Заявление кандидата об участии в отборе для включения в резерв управленческих кадров;";
                        $sMessage .= "<br>2. Анкета кандидата;";
                        $sMessage .= "<br>3. Лист кандидата;";
                        $sMessage .= "<br>4. Копия паспорта;";
                        $sMessage .= "<br>5. Копии документов об образовании;";
                        $sMessage .= "<br>6. Заверенная копия трудовой книжки (нотариально или по месту работы);";
                        $sMessage .= "<br>7. Тезисы (эссе) по развитию отрасли, сферы деятельности (не более 5 печатных листов формата А4);";
                        $sMessage .= "<br>8. Рекомендации за подписью руководителя органа исполнительной власти Тульской области и / или организации с указанием уровня должности, на которую рекомендуется кандидат.";
                        $sMessage .= "<br>Кандидат вправе дополнительно представить иные документы, подтверждающие его профессиональный уровень.";

                        \CEvent::Send("ALERT_REESTR_HEADS", 's1', ['TEXT' => $sMessage, 'THEME' => $sTheme, 'EMAIL_TO' => $sEmailUser]);
                    }

                    CIBlockElement::SetPropertyValuesEx($arGetRequest['id'], false, ['RESERVE' => 'Y']);
                }
            }
        }
    }

    protected function getActionPanelItems()
    {
        $arItems = [
            ['VALUE' => '', 'NAME' => '- Выбрать -'],
            ['VALUE' => 'export', 'NAME' => 'Экспорт'],
        ];

        if ($this->getRole() != 'USER') {
            $arItems[] = ['VALUE' => 'delete', 'NAME' => 'Удалить'];
        }

        return $arItems;
    }

    public function executeComponent()
    {
        $this->_request = Application::getInstance()->getContext()->getRequest();
        $this->executeRequests($this->_request);

        $this->arResult['COLUMNS'] = $this->arColumns;
        $this->arResult['RECORDS'] = $this->getRecords();
        $this->arResult['ROLE'] = $this->getRole();
        $this->arResult['FILTER'] = $this->getFilterData();
        $this->arResult['FILTER_ID'] = $this->sFilterID;
        $this->arResult['GRID_ID'] = $this->sGridID;
        $this->arResult['ACTION_PANEL_ITEMS'] = $this->getActionPanelItems();
        $this->arResult['IBLOCK_ID'] = \CIBlock::GetList([], ['CODE' => 'register_heads'])->fetch()['ID'];
        $this->includeComponentTemplate();
    }
}
