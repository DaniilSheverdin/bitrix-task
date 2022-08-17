<?php

namespace Citto\Integration\Itilium;

use Exception;
use Citto\Integration\Itilium;

/**
 * Class Message
 * @package Citto\Integration\Itilium
 */
class Message extends Itilium
{
    public function add()
    {
        if (is_null($this->instance)) {
            throw new Exception('Unknown error', -1);
        }
        /*
         * @todo AddCommunicationMessage
         *
         * Добавление сообщения в общение по объекту
         * При успешном выполнении операции, возвращает уникальный идентификатор добавленного сообщения.
         * Список обязательных входных данных:
         * [
         * "ObjectUID",
         * "Message",
         * "Message.Text"
         * ]
         */
        throw new Exception(__METHOD__ . ' is not implemented', 500);
    }

    /**
     * Получения общения по объекту.
     *
     * @param string $objectUid UID объекта.
     *
     * @return array
     * @throws Exception
     */
    public function get(string $objectUid = '')
    {
        if (is_null($this->instance)) {
            throw new Exception('Unknown error', -1);
        }

        if (empty($objectUid)) {
            throw new Exception('Empty ObjectUID', -1);
        }

        try {
            $result = $this->call('GetCommunication', ['ObjectUID' => $objectUid]);

            return $this->normalizeArray($result['Communication']['Messages']['Message'] ?? []);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 1);
        }
    }
}
