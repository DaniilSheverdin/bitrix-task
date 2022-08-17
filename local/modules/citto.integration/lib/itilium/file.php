<?php

namespace Citto\Integration\Itilium;

use Exception;
use Citto\Integration\Itilium;

/**
 * Class File
 * @package Citto\Integration\Itilium
 */
class File extends Itilium
{
    public function add()
    {
        if (is_null($this->instance)) {
            throw new Exception('Unknown error', -1);
        }
        /*
         * @todo AddFile
         *
         * Присоединение файла к объекту.
         * В случае успешного выполнения операции, возвращается уникальный идентификатор присоединенного файла.
         * Список обязательных входных данных:
         * [
         * "ObjectUID",
         * "File",
         * "File.Data",
         * "File.Name",
         * "File.Ext"
         * ]
         */
        throw new Exception(__METHOD__ . ' is not implemented', 500);
    }

    /**
     * Получение данных файла присоединенного к объекту.
     *
     * @param string $objectUid UID Объекта.
     * @param string $fileUid UID файла.
     *
     * @return array
     * @throws Exception
     */
    public function get(string $objectUid = '', string $fileUid = '')
    {
        if (is_null($this->instance)) {
            throw new Exception('Unknown error', -1);
        }

        if (empty($objectUid)) {
            throw new Exception('Empty ObjectUID', -1);
        }

        if (empty($fileUid)) {
            throw new Exception('Empty FileUID', -1);
        }

        try {
            $arRequest = [
                'ObjectUID' => $objectUid,
                'File'      => [
                    'UID'   => $fileUid,
                ],
            ];
            $result = $this->call('GetFile', $arRequest);

            return $result['File'] ?? [];
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 1);
        }
    }

    /**
     * Получение списка файлов, присоединенных к объекту.
     *
     * @param string $objectUid UID объекта.
     *
     * @return array
     * @throws Exception
     */
    public function getList(string $objectUid = '')
    {
        if (is_null($this->instance)) {
            throw new Exception('Unknown error', -1);
        }

        if (empty($objectUid)) {
            throw new Exception('Empty ObjectUID', -1);
        }

        try {
            $result = $this->call('GetFileList', ['ObjectUID' => $objectUid]);

            return $this->normalizeArray($result['FileList']['File'] ?? []);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 1);
        }
    }

    public function delete()
    {
        if (is_null($this->instance)) {
            throw new Exception('Unknown error', -1);
        }
        /*
         * @todo DeleteFile
         *
         * Удаление присоединенного к объекту файла.
         * У операции нет возвращаемых данных.
         * Список обязательных входных данных:
         * [
         * "ObjectUID",
         * "File",
         * "File.UID"
         * ]
         */
        throw new Exception(__METHOD__ . ' is not implemented', 500);
    }
}
