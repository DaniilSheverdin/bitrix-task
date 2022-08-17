<?php

namespace Citto\ControlOrders\Import;

use Bitrix\Main\Loader;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Controller;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

Loader::includeModule('citto.integration');

/**
 * Class AjaxController
 *
 * @package Citto\ControlOrders\Import
 */
class AjaxController extends Controller
{
    /**
     * Конфигурация действий
     *
     * @return array
     */
    public function configureActions(): array
    {
        $arParams = [
            'prefilters' => [
                new ActionFilter\Authentication(),
                new ActionFilter\HttpMethod(
                    [ActionFilter\HttpMethod::METHOD_POST]
                ),
                new ActionFilter\Csrf(),
            ],
            'postfilters' => []
        ];

        return [
            'step1' => $arParams,
        ];
    }

    public static function step1Action()
    {
    }
}
