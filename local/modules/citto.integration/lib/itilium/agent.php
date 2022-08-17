<?php

namespace Citto\Integration\Itilium;

use CBPRuntime;
use Monolog\Logger;
use Bitrix\Main\Event;
use Monolog\Handler\RotatingFileHandler;

/**
 * Class Agent
 * @package Citto\Integration\Itilium
 */
class Agent
{
    private static $errorCounter;

    public static function syncCreate()
    {
        $logger = new Logger('default');
        $logger->pushHandler(
            new RotatingFileHandler(
                $_SERVER['DOCUMENT_ROOT'] . '/local/logs/itilium/agent/syncCreate.log',
                90
            )
        );

        $obSync = new Sync($logger);

        $res = $obSync->entityDataClass::getList([
            'filter' => [
                [
                    'LOGIC' => 'OR',
                    'UF_TASK_GUID' => false,
                    'UF_INCIDENT_GUID' => false,
                ],
                [
                    'LOGIC' => 'OR',
                    'UF_STOP_SYNC' => false,
                    '!UF_STOP_SYNC' => 'Y',
                ],
            ],
            'order' => [
                'UF_DATE_SYNC' => 'asc',
            ],
            'limit' => 50,
        ]);
        while ($row = $res->fetch()) {
            if ($row['UF_RETRY_COUNT'] > 10) {
                continue;
            }
            $arCreateFields = json_decode($row['UF_SOURCE'], true);
            $tsStart = is_object($arCreateFields['DATE_START']) ?
                $arCreateFields['DATE_START']->getTimeStamp() :
                strtotime($arCreateFields['DATE_START']);
            $tsFinish = is_object($arCreateFields['DATE_FINISH']) ?
                $arCreateFields['DATE_FINISH']->getTimeStamp() :
                strtotime($arCreateFields['DATE_FINISH']);
            $arCreateFields['DATE_START'] = date('Y-m-d H:i:s', $tsStart);
            $arCreateFields['DATE_FINISH'] = date('Y-m-d H:i:s', $tsFinish);
            if (!empty($arCreateFields['PARENT'])) {
                $arCreateFields['PARENT_GUID'] = $arCreateFields['PARENT'];
            }
            $incidentGuid = null;
            $taskGuid = null;
            $arUpdate = [
                'UF_DATE_SYNC' => date('d.m.Y H:i:s'),
                'UF_DATE_UPDATE' => date('d.m.Y H:i:s'),
            ];
            if (empty($row['UF_INCIDENT_GUID'])) {
                $obSyncIncident = new Incident($logger);
                $incidentGuid = $obSyncIncident->add($arCreateFields);
                $arUpdate['UF_RETRY_COUNT'] = (int)$row['UF_RETRY_COUNT'] + 1;
                if (!empty($incidentGuid)) {
                    $arUpdate['UF_INCIDENT_GUID'] = $incidentGuid;
                }
            } elseif (empty($row['UF_TASK_GUID']) && $arCreateFields['CREATE_TASK']) {
                $arCreateFields['PARENT_GUID'] = $row['UF_INCIDENT_GUID'];
                $obSyncTask = new Task($logger);
                $taskGuid = $obSyncTask->add($arCreateFields);
                $arUpdate['UF_RETRY_COUNT'] = (int)$row['UF_RETRY_COUNT'] + 1;
                if (!empty($taskGuid)) {
                    $arUpdate['UF_TASK_GUID'] = $taskGuid;
                }
            }

            $obSync->entityDataClass::update($row['ID'], $arUpdate);
            if (count($arUpdate) > 3) {
                $event = new Event('citto.integration', 'OnItiliumTaskUpdate', [$row['ID']]);
                $event->send();
                foreach ($event->getResults() as $eventResult) {
                    $logger->debug('OnItiliumTaskUpdate', [$eventResult]);
                }
            }
        }

        return __METHOD__ . '();';
    }

    public static function syncStatus()
    {
        $logger1 = new Logger('default');
        $logger1->pushHandler(
            new RotatingFileHandler(
                $_SERVER['DOCUMENT_ROOT'] . '/local/logs/itilium/agent/syncStatusRestart.log',
                90
            )
        );
        try {
            $logger = new Logger('default');
            $logger->pushHandler(
                new RotatingFileHandler(
                    $_SERVER['DOCUMENT_ROOT'] . '/local/logs/itilium/agent/syncStatus.log',
                    90
                )
            );

            $obSync = [
                'SYNC' => new Sync($logger),
                'TASK' => new Task($logger),
                'INCIDENT' => new Incident($logger),
            ];

            try {
                $res = $obSync['SYNC']->entityDataClass::getList([
                    'filter' => [
                        [
                            'LOGIC' => 'OR',
                            'UF_STOP_SYNC' => false,
                            '!UF_STOP_SYNC' => 'Y',
                        ],
                        [
                            'LOGIC' => 'OR',
                            '!UF_TASK_GUID' => false,
                            '!UF_INCIDENT_GUID' => false,
                        ],

                    ],
                    'order' => [
                        'ID' => 'desc',
                    ],
                    'limit' => 500,
                ]);
                $arFetch = [
                    'TASK' => [],
                    'INCIDENT' => [],
                ];

                while ($row = $res->fetch()) {
                    $row['UF_TASK_STATUS'] = $row['UF_TASK_STATUS'] ? json_decode($row['UF_TASK_STATUS'], true) : ['UID' => 'empty'];
                    $row['UF_INCIDENT_STATUS'] = $row['UF_INCIDENT_STATUS'] ? json_decode($row['UF_INCIDENT_STATUS'], true) : ['UID' => 'empty'];

                    if (!empty($row['UF_INCIDENT_GUID'])) {
                        $arFetch['INCIDENT'][$row['UF_INCIDENT_GUID']] = $row;
                    }
                    if (!empty($row['UF_TASK_GUID'])) {
                        $arFetch['TASK'][$row['UF_TASK_GUID']] = $row;
                    }
                }

                foreach ($arFetch as $type => $fetch) {
                    $arChunks = array_chunk(array_keys($fetch), 10);
                    foreach ($arChunks as $chunk) {
                        $arTasks = $obSync[$type]->getList(['UID' => $chunk]);
                        foreach ($arTasks as $task) {
                            if (empty($task['Status'])) {
                                $task['Status'] = [
                                    'UID' => '',
                                    'Name' => '',
                                    'Final' => false
                                ];
                            }
                            if ($arFetch[$type][$task['UID']]['UF_' . $type . '_STATUS']['UID'] != $task['Status']['UID']) {
                                $arUpdate = [
                                    'UF_DATE_SYNC' => date('d.m.Y H:i:s'),
                                    'UF_DATE_UPDATE' => date('d.m.Y H:i:s'),
                                ];
                                $arUpdate['UF_' . $type . '_STATUS'] = json_encode($task['Status'], JSON_UNESCAPED_UNICODE);
                                $arUpdate['US_LAST_STATUS'] = json_decode(($task['Status']['Name']));
                                $obSync['SYNC']->entityDataClass::update($arFetch[$type][$task['UID']]['ID'], $arUpdate);

                                // CBPRuntime::SendExternalEvent(
                                //     $arFetch[ $type ][ $task['UID'] ]['UF_WORKFLOW_ID'],
                                //     $arFetch[ $type ][ $task['UID'] ]['UF_ACTIVITY_NAME'],
                                //     [
                                //         $arFetch[ $type ][ $task['UID'] ]['ID'],
                                //         $task
                                //     ]
                                // );

                                $event = new Event(
                                    'citto.integration',
                                    'OnItiliumTaskUpdate',
                                    [
                                        $arFetch[$type][$task['UID']]['ID'],
                                        $task
                                    ]
                                );
                                $event->send();
                                foreach ($event->getResults() as $eventResult) {
                                    $logger->debug('OnItiliumTaskUpdate', [$eventResult]);
                                }
                            }
                        }
                    }
                }
            } catch (Exception $e) {
                $logger->error('Ошибка: ' . $e->getMessage(), []);
            }

            return __METHOD__ . '();';
        } catch (Exception $e) {

            $ERR = ['TEXT' => $e];

            if (static::$errorCounter == 3) {
                $logger1->error('Ошибка при перезапуске: ' . $e->getMessage(), []);
                \CEvent::Send('nh', 'AGENT_ERROR_LOG', $ERR);
                return __METHOD__ . '();';
            } else {
                static::$errorCounter++;
                sleep(30);
                self::syncStatus();
            }
        }

    }
}
