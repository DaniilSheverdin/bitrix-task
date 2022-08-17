<?php

namespace SCUD;

use Monolog\Logger;
use Bitrix\Main\Loader;
use Monolog\Handler\RotatingFileHandler;
use SoapClient, Bitrix\Main\Config\Option;
use Sprint\Migration\Helpers\HlblockHelper;
use Bitrix\Highloadblock\HighloadBlockTable as HLTable;

Loader::includeModule('highloadblock');

class CUsersParsec
{
    private $arOptions;

    private static $ObParsec = null;
    private static $sSessionID = null;
    private static $arAllUsers = null;
    private static $obConnection = null;

    private $sField_type_event = '57CA38E4-ED6F-4D12-ADCB-2FAA16F950D7';
    private $sField_fio = '68ef9fd3-a72d-4520-9c63-5c37b0ae8539';
    private $sField_persID = '7C6D82A0-C8C8-495B-9728-357807193D23';
    private $sField_datatime = '2c5ee108-28e3-4dcc-8c95-7f3222d8e67f';
    private $sField_tourniquet = '4c5807cb-2c06-4725-9243-747e40c41d6c';
    private $iEvent_entry = 590144;
    private $iEvent_exit = 590145;

    public function __construct()
    {
        $this->arOptions = unserialize(Option::get('citto.integration', 'values'));

        if (self::$obConnection == null) {
            self::$obConnection = new \Bitrix\Main\DB\MssqlConnection(array(
                'host' => $this->arOptions['host_parsec'],
                'database' => $this->arOptions['namedb_parsec'],
                'login' => $this->arOptions['userdb_parsec'],
                'password' => $this->arOptions['passwordb_parsec']
            ));
        }
    }

    public function getObParsec()
    {
        if (self::$ObParsec == null) {
            try {
                self::$ObParsec = new SoapClient($this->arOptions['wsdl_path_parsec'], array('soap_version' => SOAP_1_2));
            } catch (\Exception $e) {
                $logger = new Logger('default');
                $logger->pushHandler(
                    new RotatingFileHandler(
                        $_SERVER['DOCUMENT_ROOT'] . '/local/logs/scud/soap_unavailable.log',
                        90
                    ));

                $logger->error('SOAP недоступен: ', [$e->faultcode, $e->faultstring]);

                throw $e;
            }
        }

        return self::$ObParsec;
    }

    public function getUsersHistory($iStartDate = 0, $iEndDate = 0, $arSIDs = [])
    {
        $iStartDate = $iStartDate - 3600 * 3;
        $iEndDate = $iEndDate - 3600 * 3;

        $arAllUsers = self::getAllUsers($arSIDs);

        $sEventHistorySessionID = $this->getHistorySessionID($arAllUsers['PERS_IDS'], $iStartDate, $iEndDate);

        $iCountUsersHistory = $this->getObParsec()->GetEventHistoryResultCount([
            'sessionID' => $this->getSessionID(),
            'eventHistorySessionID' => $sEventHistorySessionID
        ])->GetEventHistoryResultCountResult;

        $arUsersHistory = $this->getObParsec()->GetEventHistoryResult([
            'sessionID' => $this->getSessionID(),
            'eventHistorySessionID' => $sEventHistorySessionID,
            'fields' => [
                $this->sField_type_event,
                $this->sField_fio,
                $this->sField_persID,
                $this->sField_datatime,
                $this->sField_tourniquet,
            ],
            'offset' => 0,
            'count' => $iCountUsersHistory
        ])->GetEventHistoryResultResult->EventObject;

        $this->getObParsec()->CloseEventHistorySession([
            'sessionID' => $this->getSessionID(),
            'eventHistorySessionID' => $sEventHistorySessionID,
        ]);
        $this->getObParsec()->CloseSession(['sessionID' => $this->getSessionID()]);

        $arUsers = $this->handlerEvents($arUsersHistory, $arAllUsers);
        $arUsers['DOUBLE_USERS'] = $arAllUsers['DOUBLE_USERS'];

        return $arUsers;
    }

    public function getAllUsers($arSIDs = [])
    {
        if (self::$arAllUsers == null) {
            $sSIDs = implode("', '", $arSIDs);
            $sWhere = (empty($arSIDs)) ? '' : "and EV.EXTRA_FIELD_VALUE in ('$sSIDs')";

            $sSqlUsers = "select P.PERS_ID, EV.EXTRA_FIELD_VALUE as SID, S.SCH_ID, ORG.SCH_ID as ORG_SCH_ID, P.LAST_NAME, P.FIRST_NAME, P.MIDDLE_NAME from PERSON P
                            join EXTRA_FIELD_VALUE EV on P.PERS_ID = EV.PERS_ID
                            join EXTRA_FIELD_TEMPLATE ET on EV.FIELD_TEMPLATE_ID = ET.FIELD_TEMPLATE_ID
                            join ORGANIZATION ORG on ORG.ORG_ID = P.ORG_ID
                            left join SCHEDULE S on P.SCH_ID = S.SCH_ID
                            where ET.FIELD_TEMPLATE_NAME = 'LDAP' $sWhere";

            $obQueryUsers = self::$obConnection->query($sSqlUsers);
            $arAllUsers = [];
            $arWorkTime = $this->getWorkTime();

            $arDoubleUsers = [];
            $arTmpNotSidUsers = [];
            $arPersIDs = [];
            while ($arUser = $obQueryUsers->fetch()) {
                $sFio = "{$arUser['LAST_NAME']} {$arUser['FIRST_NAME']} {$arUser['MIDDLE_NAME']}";
                $sFioKey = md5(str_replace(" ", "", mb_strtoupper($sFio)));

                if (!$arUser['SID']) {
                    if (in_array($sFioKey, $arTmpNotSidUsers)) {
                        array_push($arDoubleUsers, $sFioKey);
                        unset($arAllUsers[$sFioKey]);
                    } else {
                        array_push($arTmpNotSidUsers, $sFioKey);
                    }
                }

                $arUserWorkTime = [];
                if (!empty($arUser['SCH_ID'])) {
                    $arUserWorkTime = $arWorkTime[$arUser['SCH_ID']];
                } elseif (!empty($arUser['ORG_SCH_ID'])) {
                    $arUserWorkTime = $arWorkTime[$arUser['ORG_SCH_ID']];
                } else {
                    $arUserWorkTime[0] = $arWorkTime[0];
                }

                if ($arUser['SID']) {
                    $arAllUsers['SID'][$arUser['PERS_ID']] = [
                        'SID' => trim($arUser['SID']),
                        'FIO' => $sFio,
                        'WORKTIME' => $arUserWorkTime,
                    ];
                } else {
                    $arAllUsers['FIO'][$sFioKey] = [
                        'FIO' => $sFio,
                        'WORKTIME' => $arUserWorkTime,
                    ];
                }
                array_push($arPersIDs, $arUser['PERS_ID']);
            }

            $arAllUsers['DOUBLE_USERS'] = $arDoubleUsers;
            $arAllUsers['PERS_IDS'] = $arPersIDs;

            unset($arDoubleUsers, $arTmpNotSidUsers, $arPersIDs);
            self::$arAllUsers = $arAllUsers;
        }

        return self::$arAllUsers;
    }

    private function getSessionID()
    {
        if (self::$sSessionID == null) {
            self::$sSessionID = self::$sSessionID = $this->getObParsec()->OpenSession([
                'domain' => $this->arOptions['domain_parsec'],
                'userName' => $this->arOptions['user_parsec'],
                'password' => $this->arOptions['password_parsec'],
            ])->OpenSessionResult->Value->SessionID;
        }

        return self::$sSessionID;
    }

    private function getHistorySessionID($arPersIDs = [], $iStartDate = 0, $iEndDate = 0)
    {
        $sEventHistorySessionID = $this->getObParsec()->OpenEventHistorySession([
            'sessionID' => $this->getSessionID(),
            'parameters' => [
                'Users' => $arPersIDs,
                'TransactionTypes' => [$this->iEvent_entry, $this->iEvent_exit],
                'StartDate' => $iStartDate,
                'EndDate' => $iEndDate,
                'MaxResultSize' => 50000,
                'UseLocalTime' => true
            ]
        ])->OpenEventHistorySessionResult->Value;

        return $sEventHistorySessionID;
    }

    private function handlerEvents($arUsersHistory = [], $arAllUsers = [])
    {
        $arEventUsers = [];
        foreach ($arUsersHistory as $obUser) {
            $arUser = $obUser->Values->anyType;
            $sTypeEvent = ($arUser[0] == $this->iEvent_entry) ? 'entry' : 'exit';
            $sFio = $arUser[1];
            $sPersID = mb_strtoupper($arUser[2]->enc_value);
            $iTime = strtotime($arUser[3]);
            $sDevice = explode(' (Контроллер', $arUser[4])[0];
            $iDate = strtotime(date('Y-m-d', $iTime));

            $sKeyField = ($arAllUsers['SID'][$sPersID]) ? 'SID' : 'FIO';

            krsort($arAllUsers[$sKeyField][$sPersID]['WORKTIME']);
            foreach ($arAllUsers[$sKeyField][$sPersID]['WORKTIME'] as $iTimestamp => $arWork) {
                if ($iDate > $iTimestamp) {
                    $arWorktime = $arWork;
                    break;
                }
            }

            $iNumDay = date('w', $iTime);
            $arEvents = [
                'typeEvent' => $sTypeEvent,
                'fio' => $sFio,
                'datetime' => $iTime,
                'humandate' => date('d.m.Y H:i:s', $iTime),
                'device' => $sDevice,
                'worktime' => ($arWorktime[$iNumDay]) ? $arWorktime[$iNumDay] : 'NOT_WORKTIME',
            ];

            if ($sKeyField == 'SID') {
                $arEventUsers['SID'][$arAllUsers['SID'][$sPersID]['SID']][$iDate][] = $arEvents;
            } else {
                $fioKey = md5(str_replace(" ", "", mb_strtoupper($sFio)));
                $arEventUsers['FIO'][$fioKey][$iDate][] = $arEvents;
            }
        }

        return $arEventUsers;
    }

    private function getWorkTime()
    {
        $sSqlWorkTime = "select TIMEINT_ID, TI.TIMEINTTYPE_ID, S.SCH_ID, DAY_NO, INT_START, INT_END, CYCLESTART_DATE from SCHEDULE S
                    join SCHEDULE_CYCLES SC on S.SCH_ID = SC.SCH_ID
                    join CYCLE_DAYS CD on CD.CYCLE_ID = SC.CYCLE_ID
                    join TIME_INTERVAL TI on TI.DAY_ID = CD.DAY_ID
                where TI.TIMEINTTYPE_ID = 3";
        $obQueryWorkTime = self::$obConnection->query($sSqlWorkTime);
        $arWorkTime = [];

        // default
        $arWorkTime[0] = [
            1 => ['START' => 540, 'END' => 1080],
            2 => ['START' => 540, 'END' => 1080],
            3 => ['START' => 540, 'END' => 1080],
            4 => ['START' => 540, 'END' => 1080],
            5 => ['START' => 540, 'END' => 1020],
        ];

        while ($item = $obQueryWorkTime->fetch()) {
            $iScheduleDate = $item['CYCLESTART_DATE']->getTimeStamp();
            $iDayNumber = $item['DAY_NO'];
            $arWorkTime[$item['SCH_ID']][$iScheduleDate][$iDayNumber] = [
                'START' => $item['INT_START'],
                'END' => $item['INT_END']
            ];
        }

        return $arWorkTime;
    }

    /**
     * Возвращает пользвателей,у которых истекает пропуск в указанный промежуток
     * @param $sDateFrom
     * @param $sDateTo
     * @return array
     * @throws \Bitrix\Main\DB\SqlQueryException
     */
    public function getExpiresPersonal(string $sDateFrom, string $sDateTo): array
    {
        $sDateFrom = date('Y-m-d', strtotime($sDateFrom));
        $sDateTo = date('Y-m-d', strtotime($sDateTo));

        $sSQL = "SELECT ID.PERS_ID, CONCAT(P.LAST_NAME, ' ', P.FIRST_NAME, ' ', P.MIDDLE_NAME) as FIO, DEV.DEV_NAME, ID.ACCGROUP_ID, O.ORG_NAME, ID.VALID_TO, O.ORG_ID
                from IDENTIFIER ID
                         JOIN PERSON P ON P.PERS_ID = ID.PERS_ID
                         JOIN ORGANIZATION O ON O.ORG_ID = P.ORG_ID
                         JOIN ACCGROUP_UNFOLDED AU ON ID.ACCGROUP_ID = AU.ACCGROUP_ID
                         JOIN DEVICE DEV ON DEV.DEV_ID = AU.DEV_ID
                where ID.VALID_TO BETWEEN '$sDateFrom' AND '$sDateTo'
                  AND O.ORG_ID != 'D2BC7E25-8949-4230-A3BA-0E8E0D4A8148' /* Кроме тех, кто состоит в группе уволенных */
                order by FIO ASC";

        $arPersonal = [];
        $obPersonal = self::$obConnection->query($sSQL);

        while ($arItem = $obPersonal->fetch()) {
            $arPersonal[$arItem['PERS_ID']]['FIO'] = $arItem['FIO'];
            $arPersonal[$arItem['PERS_ID']]['ORG_ID'] = $arItem['ORG_ID'];
            $arPersonal[$arItem['PERS_ID']]['ORG_NAME'] = $arItem['ORG_NAME'];
            $arPersonal[$arItem['PERS_ID']]['VALID_TO'] = $arItem['VALID_TO'];
            $arPersonal[$arItem['PERS_ID']]['DEVICES'][] = $arItem['DEV_NAME'];
        }

        return $arPersonal;
    }

    /**
     * Возвращает организации из парсека и записывает их на КП в HL
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\DB\SqlQueryException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getOrganizations(): array
    {
        $helper = new HlblockHelper();
        $signHLId = $helper->getHlblockId('ScudOrg');
        $hlblock = HLTable::getById($signHLId)->fetch();
        $entity = HLTable::compileEntity($hlblock);
        $entityDataClass = $entity->getDataClass();

        $arOrganizations = [];
        $obOrganizations = $entityDataClass::getList();
        while ($arItem = $obOrganizations->fetch()) {
            $arOrganizations[$arItem['UF_ORG_ID']] = $arItem;
        }

        $sSql = "SELECT * FROM ORGANIZATION";
        if ($arOrganizations) {
            $sSql .= " WHERE ORG_ID NOT IN ('" . implode("', '", array_keys($arOrganizations)) . "')";
        }

        $obOrganization = self::$obConnection->query($sSql);

        while ($arItem = $obOrganization->fetch()) {
            $arFields = [
                'UF_ORG_ID'    => $arItem['ORG_ID'],
                'UF_ORG_NAME'  => $arItem['ORG_NAME'],
                'UF_PARENT_ID' => $arItem['PARENT_ID'],
                'UF_LEVEL'     => $arItem['LEVEL'],
            ];

            $obResult = $entityDataClass::add($arFields);
            $arFields['ID'] = $obResult->getId();
            $arOrganizations[$arItem['ORG_ID']] = $arFields;
        }

        return $arOrganizations;
    }
}
