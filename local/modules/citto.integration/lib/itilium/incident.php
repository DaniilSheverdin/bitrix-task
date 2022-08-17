<?php

namespace Citto\Integration\Itilium;

use Citto\Integration\Itilium;
use Exception;

/**
 * Class Incident
 * @package Citto\Integration\Itilium
 */
class Incident extends Itilium
{
    /**
     * @var array Перечень обязательных полей для создания инцидента.
     */
    public $arRequiredFields = [
        'BUSINESS_SERVICE',
        'BUSINESS_SERVICE_COMPONENT',
        'DESCRIPTION',
        'DATE_START',
        'DATE_FINISH',
    ];

    /**
     * Добавление нового инцидента (обращения) в систему.
     * В случае успешного выполнения операции, возвращается уникальный идентификатор созданной задачи (наряда).
     *
     * @param array $arData Массив данных об инциденте.
     *
     * @return string
     * @throws Exception
     */
    public function add(array $arData = [])
    {
        if (is_null($this->instance)) {
            throw new Exception('Unknown error', -1);
        }

        foreach ($this->arRequiredFields as $field) {
            if (!isset($arData[ $field ])) {
                throw new Exception('Required field ' . $field . ' is not set');
            }
            if (empty($arData[ $field ])) {
                throw new Exception('Required field ' . $field . ' is empty');
            }
        }

        $arFields = [
            'Service'           => [
                'UID' => $arData['BUSINESS_SERVICE'],
            ],
            'ServiceComponent'  => [
                'UID' => $arData['BUSINESS_SERVICE_COMPONENT'],
            ],
            'Description'       => $arData['DESCRIPTION'],
            'DateStart'         => date('Y-m-d H:i:s', strtotime($arData['DATE_START'])),
            'DateEnd'           => date('Y-m-d H:i:s', strtotime($arData['DATE_FINISH'])),
            'IncidentInitiator' => (new User())->getInitiatorByBitrixId($arData['AUTHOR']),
        ];

        if (isset($arData['PARENT_GUID']) && !empty($arData['PARENT_GUID'])) {
            $arFields['Incident'] = [
                'UID' => $arData['PARENT_GUID'],
            ];
        }

        $arFields['DateStart'] = str_replace(' ', 'T', $arFields['DateStart']);
        $arFields['DateEnd'] = str_replace(' ', 'T', $arFields['DateEnd']);

        $this->logger->debug('Поля для создания инцидента', $arFields);

        try {
            $result = $this->call('CreateIncident', ['Incident' => $arFields]);

            $this->logger->debug('Результат', [$result]);

            return $result['UID'] ?? '';
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 1);
        }
    }

    /**
     * Получение списка инцидентов (обращений).
     *
     * @param array $arFilter Фильтр.
     *
     * @return array
     */
    public function getList(array $arFilter = [])
    {
        if (is_null($this->instance)) {
            throw new Exception('Unknown error', -1);
        }

        $result = (new parent())->getList('GetIncidents', $arFilter);

        return $this->normalizeArray($result['Incidents']['Incident'] ?? []);
    }

    public function getByUID(string $objectUid = '')
    {
        if (is_null($this->instance)) {
            throw new Exception('Unknown error', -1);
        }

        if (empty($objectUid)) {
            throw new Exception('Empty objectUid', -1);
        }

        return $this->getList(['UID' => $objectUid]);
    }

    /**
     * Получение текущего статуса инцидента (обращения).
     *
     * @param string $objectUid UID объекта.
     *
     * @return array
     * @throws Exception
     */
    public function getStatus(string $objectUid = '')
    {
        if (is_null($this->instance)) {
            throw new Exception('Unknown error', -1);
        }

        if (empty($objectUid)) {
            throw new Exception('Empty ObjectUID', -1);
        }

        try {
            $result = $this->call('GetIncidentStatus', ['ObjectUID' => $objectUid]);

            return $result['Status'] ?? [];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 1);
        }
    }

    /**
     * Получить список доступных статусов.
     *
     * @return array
     * @throws Exception
     */
    public function getStatuses()
    {
        if (is_null($this->instance)) {
            throw new Exception('Unknown error', -1);
        }

        try {
            $result = $this->call('GetIncidentStatuses');

            return $result['Statuses']['Status'] ?? [];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 1);
        }
    }

    /**
     * Получение истории изменения статусов инцидента (обращения).
     *
     * @param string $objectUid UID объекта.
     *
     * @return array
     * @throws Exception
     */
    public function getStatusHistory(string $objectUid = '')
    {
        if (is_null($this->instance)) {
            throw new Exception('Unknown error', -1);
        }

        if (empty($objectUid)) {
            throw new Exception('Empty ObjectUID', -1);
        }

        try {
            $result = $this->call('GetIncidentStatusHistory', ['ObjectUID' => $objectUid]);

            return $this->normalizeArray($result['StatusHistory']['StatusHistoryItem'] ?? []);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 1);
        }
    }

    public function setStatus()
    {
        if (is_null($this->instance)) {
            throw new Exception('Unknown error', -1);
        }

        /*
         * @todo SetIncidentStatus
         *
         * Изменение статуса инцидента (обращения).
         * У операции нет возвращаемых данных.
         * Список обязательных входных данных:
         * [
         * "ObjectUID",
         * "Status",
         * "Status.UID"
         * ]
         */
        throw new Exception(__METHOD__ . ' is not implemented', 500);
    }

    public function update()
    {
        if (is_null($this->instance)) {
            throw new Exception('Unknown error', -1);
        }

        /*
         * @todo UpdateIncident
         *
         * Обновление данных существующего инцидента (обращения).
         * В случае успешного выполнения операции, возвращается уникальный идентификатор инцидента (обращения).
         * Список обязательных входных данных:
         * [
         * "ObjectUID",
         * "Incident"
         * ]
         */
        throw new Exception(__METHOD__ . ' is not implemented', 500);
    }

    public function delete()
    {
        if (is_null($this->instance)) {
            throw new Exception('Unknown error', -1);
        }

        /*
         * DeleteIncident
         *
         * Удаление существующего в системе инцидента (обращения).
         * У операции нет возвращаемых данных.
         * Список обязательных входных данных:
         * [
         * "ObjectUID"
         * ]
         */
        throw new Exception(__METHOD__ . ' is not implemented', 500);
    }
}
