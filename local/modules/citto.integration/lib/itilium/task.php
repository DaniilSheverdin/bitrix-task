<?php

namespace Citto\Integration\Itilium;

use Citto\Integration\Itilium;
use Exception;

/**
 * Class Task
 * @package Citto\Integration\Itilium
 */
class Task extends Itilium
{
    /**
     * @var array Перечень обязательных полей для создания инцидента.
     */
    public $arRequiredFields = [
        'TECH_SERVICE',
        'TECH_SERVICE_COMPONENT',
        'SUBJECT',
        'DESCRIPTION',
        'DATE_START',
        'DATE_FINISH',
    ];
    /**
     * Добавление новой задачи (наряда) в систему.
     *
     * @param array $arData
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
            'Subject'           => $arData['SUBJECT'],
            'Description'       => $arData['DESCRIPTION'],
            'DateStart'         => date('Y-m-d H:i:s', strtotime($arData['DATE_START'])),
            'DateEnd'           => date('Y-m-d H:i:s', strtotime($arData['DATE_FINISH'])),
        ];

        if (isset($arData['PARENT_GUID']) && !empty($arData['PARENT_GUID'])) {
            $arFields['MainDocument'] = [
                'Incident' => [
                    'UID' => $arData['PARENT_GUID'],
                ],
            ];
        }

        $arFields['DateStart'] = str_replace(' ', 'T', $arFields['DateStart']);
        $arFields['DateEnd'] = str_replace(' ', 'T', $arFields['DateEnd']);

        $this->logger->debug('Поля для создания задачи', $arFields);

        try {
            $result = $this->call('CreateTask', ['Task' => $arFields]);

            $this->logger->debug('Результат', [$result]);

            return $result['UID'] ?? '';
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 1);
        }
    }

    /**
     * Получение списка задач (нарядов).
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

        $result = (new parent())->getList('GetTasks', $arFilter);

        return $this->normalizeArray($result['Tasks']['Task'] ?? []);
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
     * Получение текущего статуса задачи (наряда).
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
            $result = $this->call('GetTaskStatus', ['ObjectUID' => $objectUid]);

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
            $result = $this->call('GetTaskStatuses');

            return $result['Statuses']['Status'] ?? [];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 1);
        }
    }

    /**
     * Получение списка кодов закрытия задач (нарядов).
     *
     * @return array
     * @throws Exception
     */
    public function getClosureCodes()
    {
        if (is_null($this->instance)) {
            throw new Exception('Unknown error', -1);
        }

        try {
            $result = $this->call('GetTaskClosureCodes');

            return $result['TaskClosureCodes']['TaskClosureCode'] ?? [];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 1);
        }
    }

    /**
     * Получение истории изменения статусов задачи (наряда).
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
            $result = $this->call('GetTaskStatusHistory', ['ObjectUID' => $objectUid]);

            return $this->normalizeArray($result['StatusHistory']['StatusHistoryItem'] ?? []);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 1);
        }
    }

    public function setStatus(array $arParams)
    {
        if (is_null($this->instance)) {
            throw new Exception('Unknown error', -1);
        }

        if (empty($arParams['ObjectUID'])) {
            throw new Exception('Empty ObjectUID', -1);
        }

        if (empty($arParams['Status'])) {
            throw new Exception('Status', -1);
        }

        try {
            $result = $this->call('SetTaskStatus', ['ObjectUID' => $arParams['ObjectUID'], 'Status' => $arParams['Status']]);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 1);
        }
    }

    public function update()
    {
        if (is_null($this->instance)) {
            throw new Exception('Unknown error', -1);
        }
        /*
         * @todo UpdateTask
         *
         * Обновление данных существующей задачи (наряда).
         * В случае успешного выполнения операции, возвращается уникальный идентификатор задачи (наряда).
         * Список обязательных входных данных:
         * [
         * "ObjectUID",
         * "Task"
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
         * @todo DeleteTask
         *
         * Удаление существующей в системе задачи (наряда).
         * У операции нет возвращаемых данных.
         * Список обязательных входных данных:
         * [
         * "ObjectUID"
         * ]
         */
        throw new Exception(__METHOD__ . ' is not implemented', 500);
    }
}
