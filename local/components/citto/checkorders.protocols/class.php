<?php

namespace Citto\ControlOrders\Protocols;

use CBitrixComponent;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

require $_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php";

class Component extends CBitrixComponent
{
    /**
     * Подготовка параметров
     *
     * @param array $arParams Параметры компонента.
     *
     * @return array Обновлённые параметры.
     */
    public function onPrepareComponentParams(array $arParams): array
    {
        $arParams['CACHE_TIME'] = $arParams['CACHE_TIME'] ?? 3600;

        if (isset($arParams['IBLOCK_ID_ISPOLNITEL'])) {
            $this->ispolnitelIblockId = $arParams['IBLOCK_ID_ISPOLNITEL'];
        }

        if (isset($arParams['IBLOCK_ID_ORDERS'])) {
            $this->ordersIblockId = $arParams['IBLOCK_ID_ORDERS'];
        }

        if (isset($arParams['IBLOCK_ID_PROTOCOLS'])) {
            $this->protocolsIblockId = $arParams['IBLOCK_ID_PROTOCOLS'];
        }

        return $arParams;
    }

    /**
     * Сортировка любого массива.
     *
     * @param string $key Поле для сортировки.
     *
     * @return Closure
     */
    public function buildSorter(string $key): callable
    {
        return static function ($a, $b) use ($key) {
            return strnatcmp($a[ $key ], $b[ $key ]);
        };
    }

    /**
     * Запуск компонента
     *
     * @return void
     */
    public function executeComponent()
    {
        try {
        	$this->includeComponentTemplate();
        } catch (Exception $e) {
            ShowError($e->getMessage());
        }
    }
}
