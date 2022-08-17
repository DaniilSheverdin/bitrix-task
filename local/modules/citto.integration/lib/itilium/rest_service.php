<?php

namespace Citto\Integration\Itilium;

use Exception;
use CRestServer;
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;

require $_SERVER['DOCUMENT_ROOT'] . '/local/vendor/autoload.php';

/**
 * Class RestService
 * @package Citto\Integration\Itilium
 */
class RestService
{
    /**
     * Сколько дней хранить логи
     *
     * @var int
     */
    private static $maxFiles = 30;

    /**
     * Описание REST-сервиса
     *
     * @return array
     */
    public function getDescription(): array
    {
        return [
            'citto.integration' => [
                'itilium.setTask' => [
                    'callback'  => [self::class, 'setTask'],
                    'options'   => []
                ],
            ]
        ];
    }

    /**
     * @param array|null $params
     * @param $start
     * @param CRestServer $server
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\LoaderException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public static function setTask(
        array $params = null,
        $start,
        CRestServer $server
    ) {
        $logger = new Logger('setTask');
        $logger->pushHandler(
            new RotatingFileHandler(
                $_SERVER['DOCUMENT_ROOT'] . '/local/logs/itilium/setTask.log',
                self::$maxFiles
            )
        );

        $obSync = new Sync($logger);
        try {
            $taskId = $obSync->addBitrixTask($params);
            return [
                'status'    => 'success',
                'data'      => [
                    'ID' => $taskId,
                ],
            ];
        } catch (Exception $e) {
            return [
                'status'    => 'error',
                'error'     => $e->getMessage(),
            ];
        }
    }
}
