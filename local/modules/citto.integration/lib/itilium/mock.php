<?php

namespace Citto\Integration\Itilium;

use Bitrix\Main\LoaderException;
use ReflectionClass;
use Bitrix\Main\Loader;
use Bitrix\Tasks\Util;

/**
 * Class Mock
 * @package Citto\Integration\Itilium
 */
class Mock
{
    public function __construct(
        $wsdl,
        $options = []
    ) {
    }

    public function __soapCall(
        $name,
        $args,
        $options = null,
        $inputHeaders = null,
        &$outputHeaders = null
    ) {
        $arResult = [];

        $reflection = new ReflectionClass($this);
        if ($reflection->hasMethod($name)) {
            $arResult = call_user_func_array([$this, $name], [$args]);
        }

        return $arResult;
    }

    /**
     * @param array $arRequest
     * @return array
     */
    public function Request(array $arRequest = [])
    {
        $arResult = [];

        $reflection = new ReflectionClass($this);
        if ($reflection->hasMethod($arRequest['Operation'])) {
            $arResult = call_user_func_array([$this, $arRequest['Operation']], [$arRequest['Data']]);
        }

        return [
            'return' => [
                'Result'    => true,
                'Data'      => $arResult,
            ],
        ];
    }

    /**
     * @param array $arRequest
     * @return array
     */
    public function GetIncidentStatuses($arRequest = [])
    {
        $arResult = [];
        $file = __DIR__ . '/mock/GetIncidentStatuses.json';
        if (file_exists($file)) {
            $arResult = json_decode(file_get_contents($file), true);
        }

        return [
            'Statuses' => [
                'Status' => $arResult,
            ],
        ];
    }

    /**
     * @param array $arRequest
     * @return array
     */
    public function GetTaskStatuses($arRequest = [])
    {
        $arResult = [];
        $file = __DIR__ . '/mock/GetTaskStatuses.json';
        if (file_exists($file)) {
            $arResult = json_decode(file_get_contents($file), true);
        }

        return [
            'Statuses' => [
                'Status' => $arResult,
            ],
        ];
    }

    /**
     * @param array $arRequest
     * @return array
     */
    public function GetBusinessServices($arRequest = [])
    {
        $arResult = [];
        $file = __DIR__ . '/mock/GetBusinessServices.json';
        if (file_exists($file)) {
            $arResult = json_decode(file_get_contents($file), true);
        }

        return [
            'Services' => [
                'Service' => $arResult,
            ],
        ];
    }

    /**
     * @param array $arRequest
     * @return array
     */
    public function GetTechnicalServices($arRequest = [])
    {
        $arResult = [];
        $file = __DIR__ . '/mock/GetTechnicalServices.json';
        if (file_exists($file)) {
            $arResult = json_decode(file_get_contents($file), true);
        }

        return [
            'Services' => [
                'Service' => $arResult,
            ],
        ];
    }

    /**
     * @param array $arRequest
     * @return array
     */
    public function GetPriorities($arRequest = [])
    {
        $arResult = [];
        $file = __DIR__ . '/mock/GetPriorities.json';
        if (file_exists($file)) {
            $arResult = json_decode(file_get_contents($file), true);
        }

        return [
            'Priorities' => [
                'Priority' => $arResult,
            ],
        ];
    }

    /**
     * @param array $arRequest
     * @return array
     * @throws LoaderException
     */
    public function CreateIncident($arRequest = [])
    {
        Loader::includeModule('tasks');
        return [
            'UID' => Util::generateUUID(false),
        ];
    }

    /**
     * @param array $arRequest
     * @return array
     * @throws LoaderException
     */
    public function CreateTask($arRequest = [])
    {
        Loader::includeModule('tasks');
        return [
            'UID' => Util::generateUUID(false),
        ];
    }

    /**
     * @param array $arRequest
     * @return array
     * @throws LoaderException
     */
    public function GetIncidents($arRequest = [])
    {
        return $arRequest;
    }

    /**
     * @param array $arRequest
     * @return array
     * @throws LoaderException
     */
    public function GetInitiators($arRequest = [])
    {
        $arResult = [];
        $file = __DIR__ . '/mock/GetInitiators.json';
        if (file_exists($file)) {
            $arResult = json_decode(file_get_contents($file), true);
        }

        return [
            'Initiators' => [
                'Initiator' => $arResult,
            ],
        ];
    }
}
