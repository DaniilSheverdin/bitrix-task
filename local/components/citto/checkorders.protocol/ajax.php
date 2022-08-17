<?php

namespace Citto\ControlOrders\Protocol;

use Exception;
use CIBlockElement;
use CBitrixComponent;
use Citto\Integration\Delo;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Engine\Controller;
use Citto\ControlOrders\Protocol\Component as Protocols;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

Loader::includeModule('citto.integration');

/**
 * Class AjaxController
 *
 * @package Citto\ControlOrders\Protocol
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
            'addOrder' => $arParams,
            'getOrder' => $arParams,
            'updateOrder' => $arParams,
            'removeOrder' => $arParams,
            'document' => $arParams,
            'usersTree' => $arParams,
            'sort' => $arParams,
            'deloSync' => $arParams,
        ];
    }

    /**
     * Добавить поручение
     *
     * @param string $request
     *
     * @return array
     *
     * @throws LoaderException
     *
     * @todo Перенести на D7
     */
    public static function addOrderAction(string $request = ''): array
    {
        CBitrixComponent::includeComponentClass('citto:checkorders.protocol');
        $obProtocol = new Protocols();

        parse_str($request, $output);
        Loader::includeModule('iblock');
        $obIblockEl = new CIBlockElement();

        $arData = $obProtocol->getDetailData($output['PROP']['PROTOCOL_ID']);
        $arCurrentValue = $arData['~PROPERTY_ORDERS_VALUE'];
        if (!$arCurrentValue) {
            $arCurrentValue = [];
        }

        $arNewOrder = [
            'SORT'      => 500,
            'HASH'      => md5(serialize($output)),
            'NAME'      => $output['NAME'],
            'SROK'      => $output['SROK'],
            'TEXT'      => $output['DETAIL_TEXT'],
            'PROP'      => $output['PROP'],
            'ORDERS'    => [],
            'SUBORDERS' => [],
        ];

        //  Не учитывать на контроле
        if ($arNewOrder['PROP']['NOT_CONTROL'] !== 'Y') {
            $strSrok = '';
            if ($arNewOrder['SROK'] !== '') {
                $strSrok .= '<br/>Срок - ' . $arNewOrder['SROK'];
            }

            $arFields = [
                'PROP' => $arNewOrder['PROP'],
                'NAME' => $arData['NAME'],
                'DETAIL_TEXT' => $arNewOrder['NAME'] . ' ' . $arNewOrder['TEXT'] . $strSrok
            ];

            $arOrders = [];
            $ispolnitel = explode('_', $arNewOrder['PROP']['ISPOLNITEL']);
            if ($ispolnitel[0] == 'all') {
                $arExecutors = $obProtocol->getExecutors();
                foreach ($arExecutors as $executor) {
                    if ($executor['PROPERTY_TYPE_ENUM_ID'] === $ispolnitel[1]) {
                        $arFields['PROP']['ISPOLNITEL'] = $executor['ID'];
                        $arOrders[] = $obProtocol->setOrder(0, $arFields);
                    }
                }
            } else {
                $orderId = $obProtocol->setOrder(0, $arFields);
                $arOrders = [
                    $orderId
                ];
            }
            $arNewOrder['ORDERS'] = $arOrders;

            $output['PROP']['ACCOMPLICE'] = array_filter($output['PROP']['ACCOMPLICE']);

            if (!empty($output['PROP']['ACCOMPLICE'])) {
                foreach ($output['PROP']['ACCOMPLICE'] as $accId => $iAccomplice) {
                    $arFields['PROP']['ISPOLNITEL'] = $iAccomplice;
                    $arFields['PROP']['DATE_ISPOLN'] = $output['PROP']['DISABLE_DATE_ISPOLN'][ $accId ] == 'Y' ?
                                                            '31.12.2099' :
                                                            $output['PROP']['DATE_ISPOLN'];
                    $arNewOrder['SUBORDERS'][] = $obProtocol->setOrder(0, $arFields);
                }
            }
        }

        $arCurrentValue[] = [
            'VALUE' => [
                'TYPE' => 'TEXT',
                'TEXT' => Json::encode($arNewOrder)
            ]
        ];
        $obIblockEl->SetPropertyValues(
            $output['PROP']['PROTOCOL_ID'],
            $obProtocol->protocolsIblockId,
            $arCurrentValue,
            'ORDERS'
        );

        return [
            'ORDERS' => $obProtocol->getOrdersHtmlTable($output['PROP']['PROTOCOL_ID'], true),
            'REAL_ORDERS' => $obProtocol->getRealOrdersHtmlTable($output['PROP']['PROTOCOL_ID']),
        ];
    }

    /**
     * Получить информацию о поручении
     *
     * @param int $iProtocolId
     * @param string $hash
     *
     * @return string
     */
    public static function getOrderAction(int $iProtocolId, string $hash): string
    {
        CBitrixComponent::includeComponentClass('citto:checkorders.protocol');
        $obProtocol = new Protocols();
        $arOrders = $obProtocol->getOrders($iProtocolId);

        $arExecutors = $obProtocol->getExecutors();

        if (!isset($arOrders[ $hash ])) {
            return '';
        }

        $arOrder = $arOrders[ $hash ];

        global $APPLICATION;
        ob_start();
        ?>
        <input type="hidden" name="PROTOCOL_ID" value="<?=$iProtocolId;?>" />
        <input type="hidden" name="HASH" value="<?=$hash;?>" />
        <div class="row">
            <div class="col-12">
                <b>
                    <span class="required text-red">*</span>
                    Название поручения:
                </b><br/>
                <textarea
                    class="form-control"
                    name="NAME"
                    placeholder="Рекомендовать администрациям..."
                    required><?=$arOrder['NAME'];?></textarea>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-12">
                <b><span class="required text-red">*</span> Исполнитель:</b><br/>
                <select class="form-control" name="PROP[ISPOLNITEL]" required>
                    <option value="">(Не выбран)</option>
                    <?php
                    foreach ($arExecutors as $k => $v) {
                        $selected = ($k==$arOrder['PROP']['ISPOLNITEL']?'selected':'');
                        ?>
                        <option
                            value="<?=$k;?>"
                            <?=$selected;?>>
                            <?=$v['NAME'];?>
                        </option>
                        <?php
                    }
                    ?>
                </select>
            </div>
        </div>
        <div class="row mt-2 js-accomplice">
            <div class="col-12">
                <b>Соисполнитель:</b><br/>
                <?
                if (empty($arOrder['PROP']['ACCOMPLICE'])) {
                    $arOrder['PROP']['ACCOMPLICE'] = [0];
                }
                if (!is_array($arOrder['PROP']['ACCOMPLICE'])) {
                    $arOrder['PROP']['ACCOMPLICE'] = [$arOrder['PROP']['ACCOMPLICE']];
                }
                foreach ($arOrder['PROP']['ACCOMPLICE'] as $accId => $accomplice) : ?>
                    <div class="row js-accomplice-row <?=($accId != 0?'mt-2':'')?>" data-id="<?=$accId?>">
                        <div class="col-1">
                            <?if ($accId == 0) : ?>
                            <a
                                href="javascript:void(0);"
                                class="ui-btn ui-btn-primary ui-btn-icon-add js-accomplice-add"
                                title="Добавить соисполнителя"
                                ></a>
                            <?endif;?>
                            <a
                                href="javascript:void(0);"
                                class="ui-btn ui-btn-danger ui-btn-icon-remove js-accomplice-remove <?=($accId == 0)?'d-none':''?>"
                                title="Удалить соисполнителя"
                                ></a>
                        </div>
                        <div class="col-9">
                            <select
                                class="form-control"
                                name="PROP[ACCOMPLICE][<?=$accId?>]">
                                <option value="">(Не выбран)</option>
                                <?php
                                $arAccomplice = $arExecutors;
                                foreach (array_keys($arAccomplice) as $key) {
                                    if (0 === mb_strpos($key, 'all_')) {
                                        unset($arAccomplice[ $key ]);
                                    }
                                }
                                foreach ($arAccomplice as $k => $v) {
                                    $selected = ($k==$accomplice?'selected':'');
                                    ?>
                                    <option
                                        value="<?=$k;?>"
                                        <?=$selected;?>>
                                        <?=$v['NAME'];?>
                                    </option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-2 form-check d-flex align-items-center">
                            <input
                                type="hidden"
                                name="PROP[DISABLE_DATE_ISPOLN][<?=$accId?>]"
                                value="N" />
                            <input
                                id="DISABLE_DATE_ISPOLN-<?=$accId?>"
                                class="form-check-input mt-0"
                                type="checkbox"
                                name="PROP[DISABLE_DATE_ISPOLN][<?=$accId?>]"
                                <?=($arOrder['PROP']['DISABLE_DATE_ISPOLN'][ $accId ] == 'Y' ? 'checked' : '')?>
                                value="Y" />
                            <label class="form-check-label" for="DISABLE_DATE_ISPOLN-<?=$accId?>">
                                Без&nbsp;срока
                            </label>
                        </div>
                    </div>
                <? endforeach; ?>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-7">
                <b>
                    <span class="required text-red">*</span>
                    Срок исполнения:
                </b><br/>
                <input
                    class="form-control"
                    name="SROK"
                    value="<?=$arOrder['SROK'];?>"
                    placeholder="<?=FormatDate('f Y');?>"
                    required />
            </div>
            <div class="col-3">
                <b>
                    <span class="required text-red">*</span>
                    Дата исполнения:
                </b><br/>
                <input
                    class="form-control"
                    name="PROP[DATE_ISPOLN]"
                    value="<?=$arOrder['PROP']['DATE_ISPOLN'];?>"
                    placeholder="<?=date('d.m.Y');?>"
                    onclick="BX.calendar({node:this,field:this,bTime:false});"
                    required />
            </div>
            <div class="col-2 form-check">
                <br/>
                <input
                    type="hidden"
                    name="PROP[NOT_CONTROL]"
                    value="N" />
                <input
                    id="EDIT_NOT_CONTROL"
                    class="form-check-input"
                    type="checkbox"
                    name="PROP[NOT_CONTROL]"
                    value="Y"
                    <?=$arOrder['PROP']['NOT_CONTROL']=='Y'?'checked':'';?>/>
                <label class="form-check-label" for="EDIT_NOT_CONTROL">
                    Не&nbsp;учитывать на&nbsp;контроле
                </label>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-12">
                <b>
                    <span class="required text-red">*</span>
                    Содержание поручения:
                </b><br/>
                <?php
                $APPLICATION->IncludeComponent(
                    'bitrix:fileman.light_editor',
                    '',
                    [
                        'CONTENT' => $arOrder['TEXT'],
                        'INPUT_NAME' => 'DETAIL_TEXT',
                        'INPUT_ID' => '',
                        'WIDTH' => '100%',
                        'HEIGHT' => '200px',
                        'RESIZABLE' => 'Y',
                        'AUTO_RESIZE' => 'N',
                        'VIDEO_ALLOW_VIDEO' => 'N',
                        'USE_FILE_DIALOGS' => 'N',
                        'ID' => '',
                        'JS_OBJ_NAME' => 'DETAIL_TEXT',
                    ]
                );
                ?>
            </div>
        </div>
        <?php
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    /**
     * Изменить поручение
     *
     * @param string $request
     *
     * @return array
     *
     * @throws LoaderException
     */
    public static function updateOrderAction(string $request = ''): array
    {
        CBitrixComponent::includeComponentClass('citto:checkorders.protocol');
        $obProtocol = new Protocols();
        parse_str($request, $output);

        Loader::includeModule('iblock');
        $obIblockEl = new CIBlockElement();

        $arData = $obProtocol->getDetailData($output['PROTOCOL_ID']);

        $arOrders = [];
        CIBlockElement::GetPropertyValuesArray(
            $arOrders,
            $obProtocol->protocolsIblockId,
            ['ID' => $output['PROTOCOL_ID']],
            ['CODE' => 'ORDERS']
        );

        $arOrdersTmp = [];
        $arOrdersValue = $arOrders[ $output['PROTOCOL_ID'] ]['ORDERS']['~VALUE'];
        foreach ($arOrdersValue as $arOrder) {
            $text = Json::decode($arOrder['TEXT']);
            $arOrdersTmp[ $text['HASH'] ] = $text;
        }

        if (isset($arOrdersTmp[ $output['HASH'] ])) {
            unset($arOrdersTmp[ $output['HASH'] ]['SUBORDER']);
            $arOrdersTmp[ $output['HASH'] ]['NAME'] = $output['NAME'];
            $arOrdersTmp[ $output['HASH'] ]['SROK'] = $output['SROK'];
            $arOrdersTmp[ $output['HASH'] ]['TEXT'] = $output['DETAIL_TEXT'];
            $oldExecutor = $arOrdersTmp[ $output['HASH'] ]['PROP']['ISPOLNITEL'];
            $oldAccomplice = $arOrdersTmp[ $output['HASH'] ]['PROP']['ACCOMPLICE'];
            $oldDisableDateIspoln = $arOrdersTmp[ $output['HASH'] ]['PROP']['DISABLE_DATE_ISPOLN'];
            $arOrdersTmp[ $output['HASH'] ]['PROP']['ISPOLNITEL'] = $output['PROP']['ISPOLNITEL'];
            $arOrdersTmp[ $output['HASH'] ]['PROP']['ACCOMPLICE'] = $output['PROP']['ACCOMPLICE'];
            $arOrdersTmp[ $output['HASH'] ]['PROP']['DISABLE_DATE_ISPOLN'] = $output['PROP']['DISABLE_DATE_ISPOLN'];
            $arOrdersTmp[ $output['HASH'] ]['PROP']['DATE_ISPOLN'] = $output['PROP']['DATE_ISPOLN'];
            $arOrdersTmp[ $output['HASH'] ]['PROP']['NOT_CONTROL'] = $output['PROP']['NOT_CONTROL'];

            $arCurrentOrder = $arOrdersTmp[ $output['HASH'] ];

            $strSrok = '';
            if ($arCurrentOrder['SROK'] !== '') {
                $strSrok .= '<br/>Срок - ' . $arCurrentOrder['SROK'];
            }

            $arFields = [
                'PROP' => $arCurrentOrder['PROP'],
                'NAME' => $arData['NAME'],
                'DETAIL_TEXT' => $arCurrentOrder['NAME'] . ' ' . $arCurrentOrder['TEXT'] . $strSrok,
            ];

            // Перезаписать реальными данными из протокола
            $arFields['PROP']['PROTOCOL_ID'] = $arData['ID'];
            $arFields['PROP']['DATE_CREATE'] = $arData['DATE'];
            $arFields['PROP']['NUMBER'] = $arData['NUMBER'];

            $ispolnitel = explode('_', $arFields['PROP']['ISPOLNITEL']);
            $bMultiUsers = ($ispolnitel[0] == 'all');
            $arCurrentOrder['ORDERS'] = array_filter($arCurrentOrder['ORDERS']);

            $bCreateOrders = false;
            $bDeleteOrders = false;
            $bUpdateOrders = true;
            // Поручения не создавались и сняли галку "Не учитывать на контроле"
            if (empty($arCurrentOrder['ORDERS']) && $arCurrentOrder['PROP']['NOT_CONTROL'] != 'Y') {
                $bCreateOrders = true;
                $bDeleteOrders = false;
                $bUpdateOrders = false;
            }

            // Исполнитель изменился
            if ($oldExecutor !== $arFields['PROP']['ISPOLNITEL']) {
                $bCreateOrders = true;
                $bDeleteOrders = true;
                $bUpdateOrders = false;
            }

            // Соисполнитель изменился
            if (serialize($oldAccomplice) != serialize($arFields['PROP']['ACCOMPLICE'])) {
                $bCreateOrders = true;
                $bDeleteOrders = true;
                $bUpdateOrders = false;
            }
            if (serialize($oldDisableDateIspoln) != serialize($arFields['PROP']['DISABLE_DATE_ISPOLN'])) {
                $bCreateOrders = true;
                $bDeleteOrders = true;
                $bUpdateOrders = false;
            }

            // Поручения создавались и поставили галку "Не учитывать на контроле"
            if (!empty($arCurrentOrder['ORDERS']) && $arCurrentOrder['PROP']['NOT_CONTROL'] == 'Y') {
                $bCreateOrders = false;
                $bDeleteOrders = true;
                $bUpdateOrders = false;
            }

            if ($bDeleteOrders) {
                foreach ($arCurrentOrder['ORDERS'] as $realOrderId) {
                    CIBlockElement::Delete($realOrderId);
                }
                foreach ($arCurrentOrder['SUBORDERS'] as $realOrderId) {
                    CIBlockElement::Delete($realOrderId);
                }

                $arCurrentOrder['ORDERS'] = [];
                $arCurrentOrder['SUBORDERS'] = [];
                $arOrdersTmp[ $output['HASH'] ]['ORDERS'] = [];
                $arOrdersTmp[ $output['HASH'] ]['SUBORDERS'] = [];
            }

            if ($bCreateOrders) {
                $realOrders = [];
                if ($bMultiUsers) {
                    $arExecutors = $obProtocol->getExecutors();
                    foreach ($arExecutors as $executor) {
                        if ($executor['PROPERTY_TYPE_ENUM_ID'] === $ispolnitel[1]) {
                            $arFields['PROP']['ISPOLNITEL'] = $executor['ID'];
                            $realOrders[] = $obProtocol->setOrder(0, $arFields);
                        }
                    }
                } else {
                    $realOrders[] = $obProtocol->setOrder(0, $arFields);
                }

                $arOrdersTmp[ $output['HASH'] ]['ORDERS'] = $realOrders;

                $arOrdersTmp[ $output['HASH'] ]['PROP']['ACCOMPLICE'] = array_filter($arOrdersTmp[ $output['HASH'] ]['PROP']['ACCOMPLICE']);

                if (!empty($arOrdersTmp[ $output['HASH'] ]['PROP']['ACCOMPLICE'])) {
                    foreach ($arOrdersTmp[ $output['HASH'] ]['PROP']['ACCOMPLICE'] as $accId => $iAccomplice) {
                        $arFields['PROP']['ISPOLNITEL'] = $iAccomplice;
                        $arFields['PROP']['DATE_ISPOLN'] = $arOrdersTmp[ $output['HASH'] ]['PROP']['DISABLE_DATE_ISPOLN'][ $accId ] == 'Y' ?
                                                            '31.12.2099' :
                                                            $arOrdersTmp[ $output['HASH'] ]['PROP']['DATE_ISPOLN'];

                        $arOrdersTmp[ $output['HASH'] ]['SUBORDERS'][] = $obProtocol->setOrder(0, $arFields);
                    }
                }
            } elseif ($bUpdateOrders) {
                foreach ($arCurrentOrder['ORDERS'] as $realOrderId) {
                    if ($bMultiUsers) {
                        $arRealOrder = $obProtocol->getRealOrder($realOrderId);
                        $arFields['PROP']['ISPOLNITEL'] = $arRealOrder['EXECUTOR'];
                        $obProtocol->setOrder($realOrderId, $arFields);
                    } else {
                        $obProtocol->setOrder($realOrderId, $arFields);
                    }
                }

                foreach ($arCurrentOrder['SUBORDERS'] as $realOrderId) {
                    CIBlockElement::Delete($realOrderId);
                }
                $arOrdersTmp[ $output['HASH'] ]['SUBORDERS'] = [];

                $arOrdersTmp[ $output['HASH'] ]['PROP']['ACCOMPLICE'] = array_filter($arOrdersTmp[ $output['HASH'] ]['PROP']['ACCOMPLICE']);

                if (!empty($arOrdersTmp[ $output['HASH'] ]['PROP']['ACCOMPLICE'])) {
                    foreach ($arOrdersTmp[ $output['HASH'] ]['PROP']['ACCOMPLICE'] as $accId => $iAccomplice) {
                        $arFields['PROP']['ISPOLNITEL'] = $iAccomplice;
                        $arFields['PROP']['DATE_ISPOLN'] = $arOrdersTmp[ $output['HASH'] ]['PROP']['DISABLE_DATE_ISPOLN'][ $accId ] == 'Y' ?
                                                            '31.12.2099' :
                                                            $arOrdersTmp[ $output['HASH'] ]['PROP']['DATE_ISPOLN'];
                        $arOrdersTmp[ $output['HASH'] ]['SUBORDERS'][] = $obProtocol->setOrder(0, $arFields);
                    }
                }
            }
        }

        $arCurrentValue[] = [];
        foreach ($arOrdersTmp as $value) {
            $arCurrentValue[] = [
                'VALUE' => [
                    'TYPE' => 'TEXT',
                    'TEXT' => Json::encode($value)
                ]
            ];
        }
        $obIblockEl->SetPropertyValues(
            $output['PROTOCOL_ID'],
            $obProtocol->protocolsIblockId,
            $arCurrentValue,
            'ORDERS'
        );

        return [
            'ORDERS' => $obProtocol->getOrdersHtmlTable($output['PROTOCOL_ID'], true),
            'REAL_ORDERS' => $obProtocol->getRealOrdersHtmlTable($output['PROTOCOL_ID']),
        ];
    }

    /**
     * Удалить поручение из протокола
     *
     * @param int $iProtocolId
     * @param string $hash
     *
     * @return array
     */
    public static function removeOrderAction(int $iProtocolId, string $hash): array
    {
        CBitrixComponent::includeComponentClass('citto:checkorders.protocol');
        $obProtocol = new Protocols();

        Loader::includeModule('iblock');

        $obIblockEl = new CIBlockElement();

        $arOrders = [];
        CIBlockElement::GetPropertyValuesArray(
            $arOrders,
            $obProtocol->protocolsIblockId,
            ['ID' => $iProtocolId],
            ['CODE' => 'ORDERS']
        );

        $arOrdersTmp = [];
        foreach ($arOrders[ $iProtocolId ]['ORDERS']['~VALUE'] as $arOrder) {
            $text = Json::decode($arOrder['TEXT']);
            if ($text['HASH'] !== $hash) {
                $arOrdersTmp[ $text['HASH'] ] = $text;
            } elseif (!empty($text['ORDERS'])) {
                foreach ($text['ORDERS'] as $realOrderId) {
                    CIBlockElement::Delete($realOrderId);
                }
                foreach ($text['SUBORDERS'] as $realOrderId) {
                    CIBlockElement::Delete($realOrderId);
                }
            }
        }
        uasort($arOrdersTmp, $obProtocol->buildSorter('SORT'));

        $arCurrentValue[] = [];
        foreach ($arOrdersTmp as $value) {
            $arCurrentValue[] = [
                'VALUE' => [
                    'TYPE' => 'TEXT',
                    'TEXT' => Json::encode($value)
                ]
            ];
        }

        $obIblockEl->SetPropertyValues(
            $iProtocolId,
            $obProtocol->protocolsIblockId,
            $arCurrentValue,
            'ORDERS'
        );

        return [
            'ORDERS' => $obProtocol->getOrdersHtmlTable($iProtocolId, true),
            'REAL_ORDERS' => $obProtocol->getRealOrdersHtmlTable($iProtocolId),
        ];
    }

    /**
     * Генерация документа для протокола
     *
     * @param int $id
     *
     * @return string
     */
    public static function documentAction(int $id = 0): string
    {
        CBitrixComponent::includeComponentClass('citto:checkorders.protocol');
        return (new Protocols())->generateDocument($id);
    }

    /**
     * Получить полное дерево исполнителей
     *
     * @param array $selected
     *
     * @return array
     */
    public static function usersTreeAction(array $selected): array
    {
        return (new Delo\Users())->getJsonTree($selected);
    }

    /**
     * Сохранить сортировку поручений
     *
     * @param int $id
     * @param string $sorting
     *
     * @return array
     *
     * @throws LoaderException
     *
     * @todo Перенести на D7
     */
    public static function sortAction(int $id, string $sorting): array
    {
        CBitrixComponent::includeComponentClass('citto:checkorders.protocol');
        $obProtocol = new Protocols();
        parse_str($sorting, $output);

        Loader::includeModule('iblock');

        $obIblockEl = new CIBlockElement();

        $arOrders = [];
        CIBlockElement::GetPropertyValuesArray(
            $arOrders,
            $obProtocol->protocolsIblockId,
            ['ID' => $id],
            ['CODE' => 'ORDERS']
        );

        $arOrdersTmp = [];
        foreach ($arOrders[ $id ]['ORDERS']['~VALUE'] as $arOrder) {
            $text = Json::decode($arOrder['TEXT']);
            $arOrdersTmp[ $text['HASH'] ] = $text;
        }
        uasort($arOrdersTmp, $obProtocol->buildSorter('SORT'));

        foreach ($output['sort'] as $sort => $orderHash) {
            $arOrdersTmp[ $orderHash ]['SORT'] = ($sort == 0 ? 5 : $sort*10);
        }
        usort($arOrdersTmp, $obProtocol->buildSorter('SORT'));

        $arCurrentValue[] = [];
        foreach ($arOrdersTmp as $value) {
            $arCurrentValue[] = [
                'VALUE' => [
                    'TYPE' => 'TEXT',
                    'TEXT' => Json::encode($value)
                ]
            ];
        }

        $obIblockEl->SetPropertyValues(
            $id,
            $obProtocol->protocolsIblockId,
            $arCurrentValue,
            'ORDERS'
        );

        return [
            'ORDERS' => $obProtocol->getOrdersHtmlTable($id, true),
            'REAL_ORDERS' => $obProtocol->getRealOrdersHtmlTable($id),
        ];
    }

    /**
     * Синхронизация с АСЭД Дело
     *
     * @param int $iProtocolId
     *
     * @return array
     *
     * @throws Exception
     */
    public static function deloSyncAction(int $iProtocolId = 0): array
    {
        Loader::includeModule("iblock");
        Loader::includeModule("citto.integration");
        CBitrixComponent::includeComponentClass('citto:checkorders.protocol');
        $obDelo = new Delo();
        $obProtocol = new Protocols();

        $arResult = $obDelo->addProject($iProtocolId);

        if (!$arResult['error']) {
            $arBitrixStatuses = $obDelo->getBitrixStatusList();
            $arProtocolStatuses = $obProtocol->getStatusList();

            $arNewValues = [
                'DELO_ISN' => $arResult['result']['ISN'],
                'DELO_NUMBER' => $arResult['result']['NUMBER'],
                'DELO_DATE' => $arResult['result']['DATE'],
                'STATUS' => $arProtocolStatuses['EXPECT']['ID'],
                'DELO_STATUS' => $arBitrixStatuses[-1]['ID']
            ];

            CIBlockElement::SetPropertyValuesEx(
                $iProtocolId,
                $obProtocol->protocolsIblockId,
                $arNewValues
            );

            $obProtocol->log(
                $iProtocolId,
                [
                    'FIELD' => 'Отправлен в Дело',
                    'FIELD_CODE' => 'SYNC'
                ]
            );
            return $arResult['result'];
        }

        throw new Exception($arResult['error']);
    }
}
