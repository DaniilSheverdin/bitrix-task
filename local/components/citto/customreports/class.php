<?php

namespace Citto\CustomReports;

use Exception;
use CBitrixComponent;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class Component extends CBitrixComponent
{
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
