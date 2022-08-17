<?php

namespace Citto\Integration\Delo;

use CFile;
use Exception;
use CRestServer;
use CIBlockElement;
use Monolog\Logger;
use CBitrixComponent;
use Bitrix\Main\Loader;
use CIBlockPropertyEnum;
use Bitrix\Main\Web\Json;
use Bitrix\Main\LoaderException;
use Citto\Integration\Delo as DeloObj;
use Monolog\Handler\RotatingFileHandler;
use Citto\ControlOrders\Protocol\Component as Protocols;

require $_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php";

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
                'checkorders.protocol.setStatus'    => [
                    'callback'  => [self::class, 'setStatus'],
                    'options'   => []
                ],
                'checkorders.protocol.setData'      => [
                    'callback'  => [self::class, 'setData'],
                    'options'   => []
                ],
            ]
        ];
    }

    /**
     * Изменение статуса протокола по ответу от Дело
     *
     * @param array $params
     * @param int $start
     * @param CRestServer $server
     *
     * @return array
     *
     * @throws LoaderException
     */
    public static function setStatus(
        array $params = null,
        $start,
        CRestServer $server
    ) {
        if ($params['ISN'] <= 0) {
            return [
                'result'    => false,
                'error'     => 'Empty isn'
            ];
        }

        if ($params['STATUS'] <= 0) {
            return [
                'result'    => false,
                'error'     => 'Empty status'
            ];
        }

        $logger = new Logger('setStatus');
        $logger->pushHandler(
            new RotatingFileHandler(
                $_SERVER['DOCUMENT_ROOT'] . '/local/logs/checkorders.protocol/setStatus.log',
                self::$maxFiles
            )
        );

        Loader::includeModule('iblock');
        Loader::includeModule('citto.integration');
        CBitrixComponent::includeComponentClass('citto:checkorders.protocol');
        $obIblockEl = new CIBlockElement();
        $obProtocol = new Protocols();
        $obDeloSync = new Sync($logger);
        $obDelo     = new DeloObj();
        $obBpSign   = new BpSign($logger);

        $arBitrixStatuses = $obDelo->getBitrixStatusList();
        $arProtocolStatuses = $obProtocol->getStatusList();

        if (isset($arBitrixStatuses[ $params['STATUS'] ])) {
            $protocolId = $obDeloSync->findProtocolIdByISN($params['ISN']);
            $arData = [];
            if ($protocolId > 0) {
                $arData = $obProtocol->getDetailData($protocolId);
                if (!empty($arData)) {
                    $logger->debug('Входные параметры', $params);
                    $logger->info('Найден протокол с ISN = ' . $params['ISN']);
                    $curStatus = 0;
                    $curStatusEnum = (int)$arData['PROPERTY_DELO_STATUS_ENUM_ID'];
                    foreach ($arBitrixStatuses as $status) {
                        if ($status['ID'] == $curStatusEnum) {
                            $curStatus = (int)$status['XML_ID'];
                        }
                    }

                    if ($curStatus == $params['STATUS']) {
                        $logger->info('Статус проекта уже изменён');

                        return [
                            'result' => true
                        ];
                    }

                    CIBlockElement::SetPropertyValuesEx(
                        $arData['ID'],
                        $obProtocol->protocolsIblockId,
                        [
                            'DELO_STATUS' => $arBitrixStatuses[ $params['STATUS'] ]['ID']
                        ]
                    );

                    $logger->info(
                        'Статус проекта успешно обновлён',
                        [
                            'FROM' => $arData['PROPERTY_DELO_STATUS_VALUE'],
                            'TO' => $arBitrixStatuses[ $params['STATUS'] ]['VALUE']
                        ]
                    );

                    $obProtocol->log(
                        $arData['ID'],
                        [
                            'FIELD' => 'Статус проекта в АСЭД Дело',
                            'FIELD_CODE' => 'DELO_STATUS',
                            'OLD' => $arData['PROPERTY_DELO_STATUS_VALUE'],
                            'NEW' => $arBitrixStatuses[ $params['STATUS'] ]['VALUE']
                        ]
                    );

                    if ($params['STATUS'] == $obDelo::DELO_STATUS_NOTSIGNED) {
                        // Если пришёл статус НЕподписано
                        CIBlockElement::SetPropertyValuesEx(
                            $arData['ID'],
                            $obProtocol->protocolsIblockId,
                            [
                                'STATUS' => $arProtocolStatuses['NOTSIGN']['ID']
                            ]
                        );

                        $logger->info('Статус проекта Не подписано, изменяем статус протокола на Не подписано');

                        $obProtocol->log(
                            $arData['ID'],
                            [
                                'FIELD' => 'Статус протокола',
                                'FIELD_CODE' => 'STATUS',
                                'OLD' => $arData['PROPERTY_STATUS_VALUE'],
                                'NEW' => $arProtocolStatuses['NOTSIGN']['VALUE']
                            ]
                        );
                    } elseif ($params['STATUS'] == $obDelo::DELO_STATUS_SIGNED) {
                        // Если пришёл статус подписано
                        CIBlockElement::SetPropertyValuesEx(
                            $arData['ID'],
                            $obProtocol->protocolsIblockId,
                            [
                                'STATUS' => $arProtocolStatuses['SIGN']['ID']
                            ]
                        );

                        $logger->info('Статус проекта Подписано, изменяем статус протокола на Подписано');

                        $obProtocol->log(
                            $arData['ID'],
                            [
                                'FIELD'         => 'Статус протокола',
                                'FIELD_CODE'    => 'STATUS',
                                'OLD'           => $arData['PROPERTY_STATUS_VALUE'],
                                'NEW'           => $arProtocolStatuses['SIGN']['VALUE']
                            ]
                        );

                        $arFile = [];
                        foreach ($arData['FILES'] as $arFileRow) {
                            if ($arFileRow['ISN'] == $params['ISN']) {
                                $arFile = [
                                    'name'      => 'Перечень поручений.' . pathinfo($arFileRow['src'], PATHINFO_EXTENSION),
                                    'MODULE_ID' => 'iblock',
                                    'tmp_name'  => $_SERVER['DOCUMENT_ROOT'] . $arFileRow['src']
                                ];
                            }
                        }

                        $cntAll = 0;
                        $cntUpd = 0;
                        foreach ($arData['~PROPERTY_ORDERS_VALUE'] as $order) {
                            $arOrder = Json::decode($order['TEXT'], true);
                            //  Не учитывать на контроле
                            if ($arOrder['PROP']['NOT_CONTROL'] == 'Y') {
                                continue;
                            }
                            if (empty($arOrder['ORDERS']) && empty($arOrder['SUBORDERS'])) {
                                continue;
                            }

                            $arSetFields = [
                                'ACTION'    => 1135, // Новое
                                'POST'      => 1112, // Куратор = Александр Бибиков
                                'CONTROLER' => 1151, // Контролер = Дмитрий Сафронов
                            ];
                            foreach ($arOrder['ORDERS'] as $iRealId) {
                                $cntAll++;
                                $arRealOrder = $obProtocol->getRealOrder($iRealId);
                                // Черновик
                                if ($arRealOrder['ACTION_ENUM'] == 1134) {
                                    if (!empty($arFile)) {
                                        $arSetFields['DOCS'] = CFile::SaveFile($arFile, 'iblock');
                                    }
                                    foreach ($arSetFields as $key => $value) {
                                        $obIblockEl->SetPropertyValues(
                                            $iRealId,
                                            $obProtocol->ordersIblockId,
                                            $value,
                                            $key
                                        );
                                    }
                                    unset($arSetFields['DOCS']);
                                    $cntUpd++;
                                }
                            }

                            foreach ($arOrder['SUBORDERS'] as $iRealId) {
                                $cntAll++;
                                $arRealOrder = $obProtocol->getRealOrder($iRealId);
                                // Черновик
                                if ($arRealOrder['ACTION_ENUM'] == 1134) {
                                    if (!empty($arFile)) {
                                        $arSetFields['DOCS'] = CFile::SaveFile($arFile, 'iblock');
                                    }
                                    foreach ($arSetFields as $key => $value) {
                                        $obIblockEl->SetPropertyValues(
                                            $iRealId,
                                            $obProtocol->ordersIblockId,
                                            $value,
                                            $key
                                        );
                                    }
                                    unset($arSetFields['DOCS']);
                                    $cntUpd++;
                                }
                            }
                        }

                        $logger->info('Найдено ' . $cntAll . ' поручений');
                        $logger->info('Обновлено ' . $cntUpd . ' поручений');
                    }

                    return [
                        'result' => true
                    ];
                }
            } else {
                $bpSignId = $obBpSign->getByISN($params['ISN']);
                if ($bpSignId > 0) {
                    $logger->info('Найдена запись активити подписи с ISN = ' . $params['ISN']);
                    $obBpSign->update(
                        $bpSignId,
                        [
                            'UF_DELO_STATUS' => $params['STATUS']
                        ]
                    );
                    return [
                        'result' => true
                    ];
                }
            }

            $logger->notice('Не найден протокол с переданным ISN');
            return [
                'result'    => false,
                'error'     => 'Unknown protocol'
            ];
        } else {
            $logger->notice('Неизвестный статус проекта');
            return [
                'result'    => false,
                'error'     => 'Unknown status'
            ];
        }

        $logger->warning('Неизвестная ошибка');
        return [
            'result'    => false,
            'error'     => 'Unknown error'
        ];
    }

    /**
     * Фиксация изменения проекта в Дело
     *
     * @param array $params
     * @param int $start
     * @param CRestServer $server
     *
     * @return array
     *
     * @todo Пока это заглушка с логом
     */
    public static function setData(
        array $params = null,
        $start,
        CRestServer $server
    ) {
        $logger = new Logger('setData');
        $logger->pushHandler(
            new RotatingFileHandler(
                $_SERVER['DOCUMENT_ROOT'] . '/local/logs/checkorders.protocol/setData.log',
                self::$maxFiles
            )
        );

        $sync       = new Sync($logger);
        $strParams  = $params['input'];
        $strParams  = str_replace('\"', '"', $strParams);
        try {
            $arParams = Json::decode($strParams);
        } catch (Exception | ArgumentException $e) {
            $logger->error($e->getMessage());
            return [
                'result'    => false,
                'error'     => 'Failed parse JSON'
            ];
        }

        $logger->debug('Входные параметры', $arParams);
        $return = $sync->fixChanges($arParams);
        $logger->info('Результат синхронизации', $return);

        return $return;
    }
}
