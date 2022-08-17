<?php

namespace Citto\Integration\Itilium;

use Citto\Integration\Itilium;
use Exception;

/**
 * Class User
 * @package Citto\Integration\Itilium
 */
class User extends Itilium
{
    /**
     * Получение списка пользователей.
     */
    public function getList(array $arFilter = [])
    {
        if (is_null($this->instance)) {
            throw new Exception('Unknown error', -1);
        }

        $result = (new parent())->getList('GetUsers', $arFilter);
        return $result;
        // return $this->normalizeArray($result['Initiators']['Initiator'] ?? []);
    }

    /**
     * Получение списка потребителей услуг.
     */
    public function getInitiators(array $arFilter = [])
    {
        if (is_null($this->instance)) {
            throw new Exception('Unknown error', -1);
        }
        $arAllowedFilter = [
            'LOGIC',
            'UID',
            'Name',
            'ContactInformation',
        ];

        foreach (array_keys($arFilter) as $filName) {
            foreach ($arAllowedFilter as $allow) {
                if (false !== mb_strpos($allow, $filName)) {
                    throw new Exception('Filter ' . $filName . ' is not allowed', -1);
                }
            }
        }

        $result = (new parent())->getList('GetInitiators', $arFilter);
        
        return $this->normalizeArray($result['Initiators']['Initiator'] ?? []);
    }

    /**
     * Получение списка сотрудников.
     */
    public function getEmployees(array $arFilter = [])
    {
        if (is_null($this->instance)) {
            throw new Exception('Unknown error', -1);
        }
        $arAllowedFilter = [
            'LOGIC',
            'UID',
            'Name',
            'ContactInformation',
        ];

        foreach (array_keys($arFilter) as $filName) {
            foreach ($arAllowedFilter as $allow) {
                if (false !== mb_strpos($allow, $filName)) {
                    throw new Exception('Filter ' . $filName . ' is not allowed', -1);
                }
            }
        }

        $result = (new parent())->getList('GetEmployees', $arFilter);
        
        return $this->normalizeArray($result['Employees']['Employee'] ?? []);
    }

    /**
     * Получение списка физических лиц.
     */
    public function getIndividuals(array $arFilter = [])
    {
        if (is_null($this->instance)) {
            throw new Exception('Unknown error', -1);
        }
        $arAllowedFilter = [
            'LOGIC',
            'UID',
            'Name',
            'ContactInformation',
        ];

        foreach (array_keys($arFilter) as $filName) {
            foreach ($arAllowedFilter as $allow) {
                if (false !== mb_strpos($allow, $filName)) {
                    throw new Exception('Filter ' . $filName . ' is not allowed', -1);
                }
            }
        }

        $result = (new parent())->getList('GetIndividuals', $arFilter);
        
        return $this->normalizeArray($result['Individuals']['Individual'] ?? []);
    }

    public function getInitiatorByBitrixId(int $userId = 0)
    {
        if (is_null($this->instance)) {
            throw new Exception('Unknown error', -1);
        }

        if ($userId < 0) {
            throw new Exception('Empty userId', -1);
        }

        $arUser = $GLOBALS['userFields']($userId);

        $result = $this->getInitiators([
            '%ContactInformation' => $arUser['EMAIL'],
        ]);

        return $result[0];
    }
}
