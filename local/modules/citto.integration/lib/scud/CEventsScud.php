<?php

namespace SCUD;

use CUsersCorp;
use CUsersParsec;
use CUserFieldEnum;
use CUserTypeEntity;
use SimpleXMLElement;
use Bitrix\Main\Loader;
use Bitrix\Highloadblock as HL;

Loader::includeModule("highloadblock");

class CEventsScud
{
    private $arTypesAbsence;
    private $iHlBlockScudID;
    private $arJournalIDs;
    private static $arEventTourn = null;
    private static $arHolidays = [];

    public function __construct()
    {
        $this->iHlBlockScudID = HL\HighloadBlockTable::getList([
            'filter' => ['=NAME' => 'SCUD']
        ])->fetch()['ID'];
        if (!$this->iHlBlockScudID) {
            $this->iHlBlockScudID = (HL\HighloadBlockTable::add(array(
                'NAME' => 'SCUD',
                'TABLE_NAME' => 'tbl_scud',
            )))->getId();
        }

        $this->checkInstallFields();
        $this->arTypesAbsence = $this->getTypesAbsence();
    }

    public function runEvents($StartDate, $EndDate, $arSIDs = [])
    {
        $OParsec = new \Scud\CUsersParsec();
        $OCorp = new \Scud\CUsersCorp();

        $arUsersCorp = $OCorp->getAllUsers($arSIDs);
        $arUsersParsec = $OParsec->getUsersHistory($StartDate, $EndDate, $arSIDs);
        $arCorpEvents = $this->getEvents($StartDate, $EndDate, $arSIDs, $arUsersCorp);
        $this->arJournalIDs = $this->getEventsJournal($arSIDs, $arUsersCorp);

        $obHlblock = HL\HighloadBlockTable::getById($this->iHlBlockScudID)->fetch();
        $obEntity = HL\HighloadBlockTable::compileEntity($obHlblock);
        $sClassScud = $obEntity->getDataClass();

        foreach ($arCorpEvents as $iID) {
            $sClassScud::Delete($iID);
        }

        foreach ($arUsersCorp['SID'] as $sUser => $iID) {
            if (isset($arUsersParsec['SID'][$sUser])) {
                $arUserInfo = $arUsersParsec['SID'][$sUser];
                $bException = in_array($iID, $arUsersCorp['EXCEPTION']);
                $this->handlerEvents($iID, $arUserInfo, $bException);
            }
        }

        foreach ($arUsersCorp['FIO'] as $sUser => $iID) {
            if (isset($arUsersParsec['FIO'][$sUser]) && !in_array($sUser, $arUsersParsec['DOUBLE_USERS'])) {
                $arUserInfo = $arUsersParsec['FIO'][$sUser];
                $bException = in_array($iID, $arUsersCorp['EXCEPTION']);
                $this->handlerEvents($iID, $arUserInfo, $bException);
            }
        }
    }

    private function getEvents($iStartDate, $iEndDate, $arSIDs = [], $arUsersCorp = [])
    {
        $obHlblock = HL\HighloadBlockTable::getById($this->iHlBlockScudID)->fetch();
        $obEntity = HL\HighloadBlockTable::compileEntity($obHlblock);
        $sClassScud = $obEntity->getDataClass();

        $arFilter = [
            "filter" => [
                ">=UF_ACTIVE_FROM" => date('d.m.Y H:i:s', $iStartDate),
                "<=UF_ACTIVE_TO" => date('d.m.Y H:i:s', $iEndDate)
            ]
        ];

        $arFilterUserIDs = [];
        foreach ($arSIDs as $sSID) {
            if (isset($arUsersCorp['SID'][$sSID])) {
                array_push($arFilterUserIDs, $arUsersCorp['SID'][$sSID]);
            }
        }

        if (!empty($arFilterUserIDs)) {
            $arFilter["filter"]["UF_USER"] = $arFilterUserIDs;
        }

        $obData = $sClassScud::getList($arFilter);

        $arSCUDIDs = [];

        while ($arData = $obData->Fetch()) {
            $iEventID = $arData['UF_TYPE_EVENT'];
            if (in_array($iEventID, array_values($this->arTypesAbsence))) {
                array_push($arSCUDIDs, $arData['ID']);
            }
        }

        return $arSCUDIDs;
    }

    private function getEventsJournal($arSIDs = [], $arUsersCorp = [])
    {
        $obHlblock = HL\HighloadBlockTable::getById($this->iHlBlockScudID)->fetch();
        $obEntity = HL\HighloadBlockTable::compileEntity($obHlblock);
        $sClassScud = $obEntity->getDataClass();

        $obTypesAbsence = CUserFieldEnum::GetList(array(), array(
            "ENTITY_ID" => "HLBLOCK_$this->iHlBlockScudID",
            'USER_FIELD_NAME' => 'UF_TYPE_EVENT',
            'XML_ID' => 'OTHER'
        ));
        $arTypeJournal = $obTypesAbsence->getNext();

        $arFilter = [
            "filter" => [
                "UF_TYPE_EVENT" => $arTypeJournal['ID']
            ]
        ];

        $arFilterUserIDs = [];
        foreach ($arSIDs as $sSID) {
            if (isset($arUsersCorp['SID'][$sSID])) {
                array_push($arFilterUserIDs, $arUsersCorp['SID'][$sSID]);
            }
        }

        if (!empty($arFilterUserIDs)) {
            $arFilter["filter"]["UF_USER"] = $arFilterUserIDs;
        }

        $obData = $sClassScud::getList($arFilter);

        $arJournalIDs = [];

        while ($arData = $obData->Fetch()) {
            if (isset($arData['UF_ACTIVE_FROM']) && isset($arData['UF_ACTIVE_TO'])) {
                $iDateFrom = $arData['UF_ACTIVE_FROM']->format('U');
                $iDateTo = $arData['UF_ACTIVE_TO']->format('U');
                $arJournalIDs[$arData['UF_USER']][] = [
                    'from' => $iDateFrom,
                    'to' => $iDateTo
                ];
            }
        }

        return $arJournalIDs;
    }

    private function addEvent($iUserID = 0, $sReason = '', $iTypeEventID = 0, $sTourniquet = '', $iFrom = 0, $iTo = 0, $iEventTourn = 0)
    {
        $obHlblock = HL\HighloadBlockTable::getById($this->iHlBlockScudID)->fetch();
        $obEntity = HL\HighloadBlockTable::compileEntity($obHlblock);
        $sClassScud = $obEntity->getDataClass();
        $arData = [
            'UF_USER' => $iUserID,
            'UF_REASON_ABSENCE' => $sReason,
            'UF_ACTIVE_FROM' => date('d.m.Y H:i:s', $iFrom),
            'UF_ACTIVE_TO' => date('d.m.Y H:i:s', $iTo),
            'UF_EVENT_TOURN' => $iEventTourn,
            'UF_TOURNIQUET' => $sTourniquet,
            'UF_TYPE_EVENT' => $iTypeEventID,
            'UF_DATE_CREATE' => date('d.m.Y H:i:s', time()),
        ];

        $sClassScud::add($arData);
    }

    private function handlerEvents($iUserID = 0, $arUserInfo = [], $bException = false)
    {
        $arEventTourn = $this->getEventTourn();

        foreach ($arUserInfo as $iCurDate => $arUser) {
            $sCurYear = date('Y', $iCurDate);
            $iLastKey = count($arUser) - 1;
            $arEntryExitKeys = [
                'ENTRY' => null,
                'EXIT' => null
            ];

            foreach ($arUser as $iKey => $arField) {
                $sDevice = mb_strtoupper($arField['device']);

                if (mb_substr_count($sDevice, 'ПАРКОВКА') == 0) {
                    if ($arField['typeEvent'] == 'entry' && is_null($arEntryExitKeys['ENTRY'])) {
                        $arEntryExitKeys['ENTRY'] = $iKey;
                    }
                    if ($arField['typeEvent'] == 'exit') {
                        $arEntryExitKeys['EXIT'] = $iKey;
                    }
                }
            }
            foreach ($arUser as $iKey => $arField) {
                $sType = mb_strtoupper($arField['typeEvent']);
                $iEventTourn = $arEventTourn[$sType]['ID'];
                $sReason = $sType;

                if (in_array($iCurDate, $this->getHolidays($sCurYear)['weekends'])) {
                    $sReason = 'NOT';
                    $sType = mb_strtoupper($arField['typeEvent']);
                } else {
                    if ($arField['worktime'] != 'NOT_WORKTIME' || !empty($arField['worktime'])) {
                        ['type' => $sType, 'reason' => $arReason] = $this->verification($iUserID, $arField, $iCurDate, $iKey, $iLastKey, $arUser, $arEntryExitKeys);
                        $sReason = implode('; ', array_values($arReason));
                    }

                    $iDayWeek = date("w", $iCurDate);
                    if ($bException || in_array($iDayWeek, [0, 6])) {
                        $sReason = 'NOT';
                        $sType = mb_strtoupper($arField['typeEvent']);
                    }
                }

                $this->addEvent($iUserID, $sReason, $this->arTypesAbsence[$sType], $arField['device'], $arField['datetime'], $arField['datetime'], $iEventTourn);
            }
        }
    }

    public function getEventTourn()
    {
        if (self::$arEventTourn == null) {
            $obEventTourn = CUserFieldEnum::GetList([], ['ENTITY_ID' => "HLBLOCK_$this->iHlBlockScudID", 'USER_FIELD_NAME' => 'UF_EVENT_TOURN']);
            while ($arTourn = $obEventTourn->getNext()) {
                self::$arEventTourn[$arTourn['XML_ID']] = ['ID' => $arTourn['ID']];
            }
        }

        return self::$arEventTourn;
    }

    private function verification($iUserID = 0, $arField = [], $iCurDate = 0, $iKey = 0, $iLastKey = 0, $arUser = [], $arEntryExitKeys = [])
    {
        $iStartWorkTime = strtotime("+{$arField['worktime']['START']} minute", $iCurDate);
        $iEndWorkTime = strtotime("+{$arField['worktime']['END']} minute", $iCurDate);
        $iEventTime = $arField['datetime'];

        $arReason = [];

        $sType = mb_strtoupper($arField['typeEvent']);

        if ($iKey == $arEntryExitKeys['ENTRY']) {
            $iDifference = $iStartWorkTime - $iEventTime;
            if ($iDifference < -3600 * 4) {
                $arReason['LATE_4'] = 'Опоздание более, чем на 4 часа';
                $sType = 'VIOLATION';
            } elseif ($iDifference < 0) {
                $arReason['LATE'] = 'Опоздание';
                $sType = 'VIOLATION';
            }

            if (!empty($arReason) && isset($this->arJournalIDs[$iUserID])) {
                foreach ($this->arJournalIDs[$iUserID] as $iKey_2 => $arDate) {
                    $iDateTo = $arDate['to'] + 3600;
                    $iDateFrom = $arDate['from'] - 3600;
                    if ($iStartWorkTime >= $iDateFrom && $iStartWorkTime <= $iDateTo) {
                        if ($iEventTime >= $iDateFrom && $iEventTime <= $iDateTo) {
                            $arReason = [];
                            $sType = mb_strtoupper($arField['typeEvent']);
                            break;
                        }
                    }
                }
            }
        } elseif ($iKey == $arEntryExitKeys['EXIT']) {
            $iDifference = $iEndWorkTime - $iEventTime;
            if ($iDifference < -3600) {
                $arReason['AFTER_EXIT'] = 'Выход после окончания рабочего дня';
                $sType = 'VIOLATION_POSITIVE';
            } elseif (($iDifference > 0)) {
                $arReason['BEFORE_EXIT'] = 'Уход раньше окончания рабочего дня';
                $sType = 'VIOLATION';
                if (isset($this->arJournalIDs[$iUserID])) {
                    foreach ($this->arJournalIDs[$iUserID] as $iKey_2 => $arDate) {
                        if ($iEndWorkTime >= $arDate['from'] && ($iEndWorkTime - $arDate['to']) <= 3600) {
                            $arReason = [];
                            $sType = mb_strtoupper($arField['typeEvent']);
                            break;
                        }
                    }
                }
            }
        }

        if (isset($this->arJournalIDs[$iUserID])) {
            if ($arField['typeEvent'] == 'exit' && isset($arUser[$iKey + 1])) {
                if ($arUser[$iKey + 1]['typeEvent'] == 'exit') {
                    foreach ($this->arJournalIDs[$iUserID] as $iKey_2 => $arDate) {
                        if ($iCurDate == strtotime(date('Y-m-d', $arDate['to'])) && $iEventTime < $arDate['to'] && ($arUser[$iKey + 1]['datetime'] - $arDate['to']) > 7200) {
                            $arReason['DIFF_JOURNAL'] = 'Расхождение с журналом УРВ более 2 часов';
                            unset($this->arJournalIDs[$iUserID][$iKey_2]);
                            $sType = 'VIOLATION';
                            break;
                        }
                    }
                }
            } elseif ($arField['typeEvent'] == 'exit' && !isset($arUser[$iKey + 1])) {
                foreach ($iCurDate == strtotime(date('Y-m-d', $arDate['to'])) && $this->arJournalIDs[$iUserID] as $iKey_2 => $arDate) {
                    if ($iEventTime < $arDate['to'] && ($iEndWorkTime - $arDate['to']) > 7200) {
                        $arReason['DIFF_JOURNAL'] = 'Расхождение с журналом УРВ более 2 часов';
                        unset($this->arJournalIDs[$iUserID][$iKey_2]);
                        $sType = 'VIOLATION';
                        break;
                    }
                }
            } elseif ($arField['typeEvent'] == 'entry' && $iKey == 0 && $iEventTime > $iStartWorkTime) {
                foreach ($iCurDate == strtotime(date('Y-m-d', $arDate['to'])) && $this->arJournalIDs[$iUserID] as $iKey_2 => $arDate) {
                    if ($iEventTime > $arDate['to'] && ($iEventTime - $arDate['to']) > 7200) {
                        $arReason['DIFF_JOURNAL'] = 'Расхождение с журналом УРВ более 2 часов';
                        unset($this->arJournalIDs[$iUserID][$iKey_2]);
                        $sType = 'VIOLATION';
                        break;
                    }
                }
            }
        }

        if ($iKey == 0 && $arField['typeEvent'] != 'entry') {
            $arReason['NOT_ENTRY'] = 'Отсутствует информация о входе';
        }

        if ($iKey == $iLastKey && $arField['typeEvent'] != 'exit') {
            $arReason['NOT_EXIT'] = 'Отсутствует информация о выходе';
        }

        if (empty($arReason)) {
            $arReason = ['NOT'];
        }

        return ['type' => $sType, 'reason' => $arReason];
    }

    private function getTypesAbsence()
    {
        $arTypesAbsence = [];
        $obTypesAbsence = CUserFieldEnum::GetList(array(), array(
            "ENTITY_ID" => "HLBLOCK_$this->iHlBlockScudID",
            'USER_FIELD_NAME' => 'UF_TYPE_EVENT'
        ));
        while ($arType = $obTypesAbsence->getNext()) {
            if ($arType['XML_ID'] != 'OTHER') {
                $arTypesAbsence[$arType['XML_ID']] = $arType['ID'];
            }
        }

        return $arTypesAbsence;
    }

    private function checkUserField($sEntityID, $sFieldName)
    {
        return CUserTypeEntity::GetList([], ['ENTITY_ID' => $sEntityID, 'FIELD_NAME' => $sFieldName])->GetNext();
    }

    private function checkInstallFields()
    {
        $ObUserType = new CUserTypeEntity;
        $obEnum = new CUserFieldEnum();

        if (!$this->checkUserField("HLBLOCK_$this->iHlBlockScudID", 'UF_TOURNIQUET')) {
            $arFields = array(
                "SORT" => "600",
                "FIELD_NAME" => "UF_TOURNIQUET",
                "USER_TYPE_ID" => "string",
                "ENTITY_ID" => "HLBLOCK_$this->iHlBlockScudID",
                "EDIT_FORM_LABEL" => ['ru' => 'Турникет']
            );
            $ObUserType->Add($arFields);
        }

        if (!$this->checkUserField("HLBLOCK_$this->iHlBlockScudID", 'UF_EVENT_TOURN')) {
            $arFields = array(
                "SORT" => "600",
                "FIELD_NAME" => "UF_EVENT_TOURN",
                "USER_TYPE_ID" => "enumeration",
                "ENTITY_ID" => "HLBLOCK_$this->iHlBlockScudID",
                "EDIT_FORM_LABEL" => ['ru' => 'Проход через турникет']
            );
            $iEventTourn = $ObUserType->Add($arFields);

            $arEnumVal = [
                'ENTRY' => 'Вход',
                'EXIT' => 'Выход'
            ];

            $arAddEnum = [];
            $iStep = 0;
            foreach ($arEnumVal as $sEvent => $sValue) {
                $arAddEnum['n' . $iStep] = array(
                    'VALUE' => $sValue,
                    'XML_ID' => $sEvent,
                );
                $iStep++;
            }
            $obEnum->SetEnumValues($iEventTourn, $arAddEnum);
        }

        if (!$this->checkUserField("HLBLOCK_$this->iHlBlockScudID", 'UF_HEAD_CONFIRM')) {
            $arFields = array(
                "SORT" => "600",
                "FIELD_NAME" => "UF_HEAD_CONFIRM",
                "USER_TYPE_ID" => "string",
                "ENTITY_ID" => "HLBLOCK_$this->iHlBlockScudID",
                "EDIT_FORM_LABEL" => ['ru' => 'Фамилия руководителя, давшего разрешение на убытие (СКУД)']
            );
            $ObUserType->Add($arFields);
        }

        if (!$this->checkUserField("HLBLOCK_$this->iHlBlockScudID", 'UF_ACTIVE_FROM')) {
            $arFields = array(
                "SORT" => "600",
                "FIELD_NAME" => "UF_ACTIVE_FROM",
                "USER_TYPE_ID" => "datetime",
                "ENTITY_ID" => "HLBLOCK_$this->iHlBlockScudID",
                "EDIT_FORM_LABEL" => ['ru' => 'Начало']
            );
            $ObUserType->Add($arFields);
        }

        if (!$this->checkUserField("HLBLOCK_$this->iHlBlockScudID", 'UF_ACTIVE_TO')) {
            $arFields = array(
                "SORT" => "600",
                "FIELD_NAME" => "UF_ACTIVE_TO",
                "USER_TYPE_ID" => "datetime",
                "ENTITY_ID" => "HLBLOCK_$this->iHlBlockScudID",
                "EDIT_FORM_LABEL" => ['ru' => 'Окончание']
            );
            $ObUserType->Add($arFields);
        }

        if (!$this->checkUserField("HLBLOCK_$this->iHlBlockScudID", 'UF_REASON_ABSENCE')) {
            $arFields = array(
                "SORT" => "600",
                "FIELD_NAME" => "UF_REASON_ABSENCE",
                "USER_TYPE_ID" => "string",
                "ENTITY_ID" => "HLBLOCK_$this->iHlBlockScudID",
                "EDIT_FORM_LABEL" => ['ru' => 'Причина отсутствия']
            );
            $ObUserType->Add($arFields);
        }

        if (!$this->checkUserField("HLBLOCK_$this->iHlBlockScudID", 'UF_USER')) {
            $arFields = array(
                "SORT" => "600",
                "FIELD_NAME" => "UF_USER",
                "USER_TYPE_ID" => "integer",
                "ENTITY_ID" => "HLBLOCK_$this->iHlBlockScudID",
                "EDIT_FORM_LABEL" => ['ru' => 'Пользователь']
            );
            $ObUserType->Add($arFields);
        }

        if (!$this->checkUserField("HLBLOCK_$this->iHlBlockScudID", 'UF_TYPE_EVENT')) {
            $arFields = array(
                "SORT" => "600",
                "FIELD_NAME" => "UF_TYPE_EVENT",
                "USER_TYPE_ID" => "enumeration",
                "ENTITY_ID" => "HLBLOCK_$this->iHlBlockScudID",
                "EDIT_FORM_LABEL" => ['ru' => 'Тип события']
            );
            $iEvent = $ObUserType->Add($arFields);

            $arEnumVal = [
                'VIOLATION' => 'Нарушение',
                'VIOLATION_POSITIVE' => 'Положительное нарушение',
                'ENTRY' => 'Вход',
                'EXIT' => 'Выход',
                'OTHER' => 'Другое',
            ];

            $arAddEnum = [];
            $iStep = 0;
            foreach ($arEnumVal as $sEvent => $sValue) {
                $arAddEnum['n' . $iStep] = array(
                    'VALUE' => $sValue,
                    'XML_ID' => $sEvent,
                );
                $iStep++;
            }
            $obEnum->SetEnumValues($iEvent, $arAddEnum);
        }

        if (!$this->checkUserField("HLBLOCK_$this->iHlBlockScudID", 'UF_DATE_CREATE')) {
            $arFields = array(
                "SORT" => "600",
                "FIELD_NAME" => "UF_DATE_CREATE",
                "USER_TYPE_ID" => "datetime",
                "ENTITY_ID" => "HLBLOCK_$this->iHlBlockScudID",
                "EDIT_FORM_LABEL" => ['ru' => 'Время создания записи']
            );
            $ObUserType->Add($arFields);
        }
    }

    private function getHolidays($iYear)
    {
        if (!isset(self::$arHolidays[$iYear])) {
            $sPath = 'http://xmlcalendar.ru/data/ru/' . $iYear . '/calendar.xml';
            $rCh = curl_init();
            curl_setopt($rCh, CURLOPT_URL, $sPath);
            curl_setopt($rCh, CURLOPT_FAILONERROR, 1);
            curl_setopt($rCh, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($rCh, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($rCh, CURLOPT_TIMEOUT, 30);
            $sValue = curl_exec($rCh);
            curl_close($rCh);

            if ($sValue) {
                $obXml = new SimpleXMLElement($sValue);
                $arHolidays = [];
                foreach ($obXml->days->day as $arItem) {
                    if (isset($arItem['h'])) {
                        $arTmp = explode('.', $arItem['d']);
                        $sTmp = $arTmp[1] . '.' . $arTmp[0] . '.' . $iYear;
                        $arHolidays['holidays'][] = strtotime($sTmp);
                    }
                    if ($arItem['t'] == 1 && !isset($arItem['h'])) {
                        $arTmp = explode('.', $arItem['d']);
                        $sTmp = $arTmp[1] . '.' . $arTmp[0] . '.' . $iYear;
                        $arHolidays['weekends'][] = strtotime($sTmp);
                    }
                    if ($arItem['t'] == 2 && !isset($arItem['h'])) {
                        $arTmp = explode('.', $arItem['d']);
                        $sTmp = $arTmp[1] . '.' . $arTmp[0] . '.' . $iYear;
                        $arHolidays['shortdays'][] = strtotime($sTmp);
                    }
                }
                self::$arHolidays[$iYear] = $arHolidays;
            } else {
                self::$arHolidays[$iYear] = 'error';
            }
        }

        return self::$arHolidays[$iYear];
    }
}
