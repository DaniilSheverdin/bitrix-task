<?php

namespace Citto\ControlOrders;

use CUser;
use CIBlockElement;
use Monolog\Logger;
use Bitrix\Main\Loader;
use CIBlockPropertyEnum;
use Bitrix\Main\UserTable;
use Bitrix\Main\Config\Option;
use Monolog\Handler\RotatingFileHandler;

/**
 * Уведомления модуля Контроля поручений.
 */
class Agent
{
    /**
     * Логирование модуля
     * @param string $level   Уровень логирования
     * @param string $message Текст сообщения
     * @param array  $context Массив для лога
     * @return bool
     */
    public static function log($level = 'info', $message = '', $context = [])
    {
        if (empty($message)) {
            return true;
        }
        require $_SERVER['DOCUMENT_ROOT'] . '/local/vendor/autoload.php';
        $logger = new Logger('default');
        $logger->pushHandler(
            new RotatingFileHandler(
                $_SERVER['DOCUMENT_ROOT'] . '/local/logs/controlorders/agent.log',
                30
            )
        );
        $logger->log($level, $message, $context);
        return true;
    }

    /**
     * Агент для отправки всех уведомлений.
     *
     * @return string
     */
    public static function run()
    {
        self::agentNewOrders();
        self::agentSrokSoonExpire();
        self::agentSrokExpired();
        self::agentNewOrdersForControl();
        self::agentOrdersOnControl();
        self::agentOrdersOnSign();
        self::agentOrdersOnVisa();
        self::agentSrokExpireIn3Days();
        self::agentInactiveUsers();
        return __METHOD__ . '();';
    }

    /**
     * 1) В случае если по истечении 1 рабочего дня
     * после направления поручения главному исполнителю
     * (новое, доп. контроль или возврат с замечаниями)
     * данное поручение не принимается на исполнение.
     * И далее – каждый рабочий день в 10.00 и в 15.00,
     * пока главный исполнитель не примет поручение на исполнение.
     *
     * @return string
     */
    public static function agentNewOrders()
    {
        $messageName = 'NEW_ORDERS';
        $lastDate = self::getLastDate($messageName);
        if ($lastDate == '') {
            $lastDate = strtotime(date('d.m.Y 15:00:00', strtotime('YESTERDAY')));
        }

        $ts = time();
        if (self::isHoliday($ts)) {
            return __METHOD__ . '();';
        }
        if ((int)date('H') < 10) {
            return __METHOD__ . '();';
        }

        $bStart = false;
        if (date('H', $lastDate) == 10 && date('H') == 15) {
            $bStart = true;
        } elseif (date('H', $lastDate) == 15 && date('H') == 10) {
            $bStart = true;
        }

        if ($bStart) {
            self::log('info', __METHOD__);

            $find = self::addWorkDay($ts, -1);

            $arFilter = [
                'PROPERTY_ACTION'               => Settings::$arActions['NEW'],
                '<=PROPERTY_ACTION_DATE'        => date('Y-m-d H:i:s', $find),
            ];
            self::log('info', '$arFilter = ' . json_encode($arFilter));
            $arOrders = Notify::getOrders($arFilter);

            self::log('info', '$arOrders = ' . count($arOrders));
            $arSend = [];
            foreach ($arOrders as $arOrder) {
                $arSend[ $arOrder['PROPERTY_ISPOLNITEL_VALUE'] ][ $arOrder['ID'] ] = $arOrder['LINK_BB'];
            }

            $bSend = false;
            $arExecutors = Executors::getList();
            foreach ($arSend as $execId => $arLinks) {
                $arUsers = $arExecutors[ $execId ]['PROPERTY_IMPLEMENTATION_VALUE'];
                if (!empty($arUsers) && !empty($arLinks)) {
                    $message = str_replace(
                        '#LINKS#',
                        implode('[BR]', $arLinks),
                        Settings::$arEventMessages[ $messageName ]
                    );
                    $bSend = true;
                    Notify::send(
                        [],
                        $messageName,
                        $arUsers,
                        $message,
                        true,
                        [
                            'ORDERS' => array_keys($arLinks),
                        ]
                    );
                }
            }
            if ($bSend) {
                $hour = date('H') >= 15 ? 15 : 10;
                self::setLastDate($messageName, strtotime(date('d.m.Y ' . $hour . ':00:00')));
            }
        }

        return __METHOD__ . '();';
    }

    /**
     * 2) Уведомление о поручениях, сроки исполнения которых
     * истекают – в 10.00 рабочего дня, предшествующего
     * рабочему дню, на который приходится срок исполнения
     * поручения. Если срок исполнения поручения приходится
     * на выходной или праздничный день, то уведомление
     * направляется в 10.00 последнего рабочего дня,
     * предшествующего выходным или праздничным дням.
     * Если отчет по поручению предоставлен,
     * то уведомление не направляется.
     *
     * @return string
     */
    public static function agentSrokSoonExpire()
    {
        $messageName = 'SROK_SOON_EXPIRE';
        $lastDate = self::getLastDate($messageName);
        if ($lastDate == '') {
            $lastDate = strtotime(date('d.m.Y 10:00:00', strtotime('YESTERDAY')));
        }

        $ts = time();
        if (self::isHoliday($ts)) {
            return __METHOD__ . '();';
        }
        if ((int)date('H') < 10) {
            return __METHOD__ . '();';
        }
        if ((int)date('H') > 10) {
            return __METHOD__ . '();';
        }

        if (($ts - $lastDate) >= 86400) {
            self::log('info', __METHOD__);

            $find = self::addWorkDay($ts, 1);

            $arFilter = [
                [
                    'LOGIC'                     => 'AND',
                    '>=PROPERTY_DATE_ISPOLN'    => date('Y-m-d', strtotime('TOMORROW')),
                    '<=PROPERTY_DATE_ISPOLN'    => date('Y-m-d', $find),
                ],
                'PROPERTY_ACTION'               => Settings::$arActions['WORK'],
                // '!ID'                           => CIBlockElement::SubQuery(
                //     'PROPERTY_PORUCH',
                //     [
                //         'ACTIVE'        => 'Y',
                //         'IBLOCK_ID'     => Settings::$iblockId['ORDERS_COMMENT'],
                //         'PROPERTY_TYPE' => 1131,
                //     ]
                // ),
            ];
            self::log('info', '$arFilter = ' . json_encode($arFilter));
            $arOrders = Notify::getOrders($arFilter);
            self::log('info', '$arOrders = ' . count($arOrders));

            $arSend = [];
            $arExecutors = Executors::getList();
            foreach ($arOrders as $arOrder) {
                if (self::hasReport($arOrder['ID'])) {
                    continue;
                }
                if ($arExecutors[ $arOrder['PROPERTY_ISPOLNITEL_VALUE'] ]['PROPERTY_TYPE_CODE'] === 'external') {
                    $arUsers = (new Notify())->getAllControlers();
                } else {
                    $arUsers = $arExecutors[ $arOrder['PROPERTY_ISPOLNITEL_VALUE'] ]['PROPERTY_IMPLEMENTATION_VALUE'];
                    if ($arOrder['PROPERTY_DELEGATE_USER_VALUE'] != '') {
                        $arUsers[] = $arOrder['PROPERTY_DELEGATE_USER_VALUE'];
                    }
                }

                foreach ($arUsers as $uId) {
                    $arSend[ $uId ][ $arOrder['ID'] ] = $arOrder['LINK_BB'];
                }
            }

            $bSend = false;
            foreach ($arSend as $userId => $arLinks) {
                $message = str_replace(
                    '#LINKS#',
                    implode('[BR]', $arLinks),
                    Settings::$arEventMessages[ $messageName ]
                );
                $bSend = true;
                Notify::send(
                    [],
                    $messageName,
                    [$userId],
                    $message,
                    true,
                    [
                        'ORDERS' => array_keys($arLinks),
                    ]
                );
            }

            if ($bSend) {
                self::setLastDate($messageName, strtotime(date('d.m.Y 10:00:00')));
            }
        }

        return __METHOD__ . '();';
    }

    /**
     * 3) В случае если истекли сроки исполнения поручений.
     * И далее – каждый рабочий день в 10.00, пока по
     * поручениям не будут направлены отчеты об исполнении.
     *
     * @return string
     */
    public static function agentSrokExpired()
    {
        $messageName = 'SROK_EXPIRED';
        $lastDate = self::getLastDate($messageName);
        if ($lastDate == '') {
            $lastDate = strtotime(date('d.m.Y 10:00:00', strtotime('YESTERDAY')));
        }

        $ts = time();
        if (self::isHoliday($ts)) {
            return __METHOD__ . '();';
        }
        if ((int)date('H') < 10) {
            return __METHOD__ . '();';
        }

        if (($ts - $lastDate) >= 86400) {
            self::log('info', __METHOD__);

            $arFilter = [
                '<PROPERTY_DATE_ISPOLN'    => date('Y-m-d'),
                ['!PROPERTY_ACTION'         => Settings::$arActions['DRAFT']],
                ['!PROPERTY_ACTION'         => Settings::$arActions['ARCHIVE']],
                // '!ID'                       => CIBlockElement::SubQuery(
                //     'PROPERTY_PORUCH',
                //     [
                //         'ACTIVE'        => 'Y',
                //         'IBLOCK_ID'     => Settings::$iblockId['ORDERS_COMMENT'],
                //         'PROPERTY_TYPE' => 1131,
                //     ]
                // ),
            ];
            self::log('info', '$arFilter = ' . json_encode($arFilter));
            $arOrders = Notify::getOrders($arFilter);
            self::log('info', '$arOrders = ' . count($arOrders));

            $arSend = [];
            $arExecutors = Executors::getList();
            foreach ($arOrders as $arOrder) {
                if (self::hasReport($arOrder['ID'])) {
                    continue;
                }
                if ($arExecutors[ $arOrder['PROPERTY_ISPOLNITEL_VALUE'] ]['PROPERTY_TYPE_CODE'] === 'external') {
                    $arUsers = (new Notify())->getAllControlers();
                } else {
                    $arUsers = $arExecutors[ $arOrder['PROPERTY_ISPOLNITEL_VALUE'] ]['PROPERTY_IMPLEMENTATION_VALUE'];
                    if ($arOrder['PROPERTY_DELEGATE_USER_VALUE'] != '') {
                        $arUsers[] = $arOrder['PROPERTY_DELEGATE_USER_VALUE'];
                    }
                }

                foreach ($arUsers as $uId) {
                    $arSend[ $uId ][ $arOrder['ID'] ] = $arOrder['LINK_BB'];
                }
            }

            $bSend = false;
            foreach ($arSend as $userId => $arLinks) {
                $message = str_replace(
                    '#LINKS#',
                    implode('[BR]', $arLinks),
                    Settings::$arEventMessages[ $messageName ]
                );
                $bSend = true;
                Notify::send(
                    [],
                    $messageName,
                    [$userId],
                    $message,
                    true,
                    [
                        'ORDERS' => array_keys($arLinks),
                    ]
                );
            }

            if ($bSend) {
                self::setLastDate($messageName, strtotime(date('d.m.Y 10:00:00')));
            }
        }

        return __METHOD__ . '();';
    }

    /**
     * 6) В случае если по истечении 3 рабочих дней после
     * направления поручения главному исполнителю
     * (новое, доп. контроль или возврат с замечаниями)
     * главный исполнитель не принимает его на исполнение.
     * И далее – каждый рабочий день в 10.00, пока
     * главный исполнитель не примет поручение на исполнение.
     *
     * @return string
     */
    public static function agentNewOrdersForControl()
    {
        $messageName = 'NEW_ORDERS_CONTROL';
        $lastDate = self::getLastDate($messageName);
        if ($lastDate == '') {
            $lastDate = strtotime(date('d.m.Y 10:00:00', strtotime('YESTERDAY')));
        }

        $ts = time();
        if (self::isHoliday($ts)) {
            return __METHOD__ . '();';
        }
        if ((int)date('H') < 10) {
            return __METHOD__ . '();';
        }

        if (($ts - $lastDate) >= 86400) {
            self::log('info', __METHOD__);

            $find = self::addWorkDay($ts, -3);

            $arFilter = [
                'PROPERTY_ACTION'               => Settings::$arActions['NEW'],
                '<=PROPERTY_ACTION_DATE'        => date('Y-m-d H:i:s', $find),
            ];
            self::log('info', '$arFilter = ' . json_encode($arFilter));
            $arOrders = Notify::getOrders($arFilter);
            self::log('info', '$arOrders = ' . count($arOrders));

            $arSend = [];
            foreach ($arOrders as $arOrder) {
                $sendUsers = [
                    $arOrder['PROPERTY_CONTROLER_VALUE']
                ];
                $arUser = CUser::GetByID($arOrder['PROPERTY_CONTROLER_VALUE'])->Fetch();
                if ($arUser['UF_CONTROLER_HEAD'] != '') {
                    $sendUsers[] = $arUser['UF_CONTROLER_HEAD'];
                } else {
                    $sendUsers = array_merge(
                        $sendUsers,
                        Notify::getControlers($arOrder['PROPERTY_CONTROLER_VALUE'])
                    );
                }
                foreach ($sendUsers as $userId) {
                    $arSend[ $userId ][ $arOrder['PROPERTY_ISPOLNITEL_VALUE'] ][ $arOrder['ID'] ] = $arOrder['LINK_BB'];
                }
            }

            $bSend = false;
            $arExecutors = Executors::getList();
            foreach ($arSend as $userId => $arList) {
                $mess = '';
                $arAllOrders = [];
                foreach ($arList as $execId => $arLinks) {
                    $arAllOrders = array_merge($arAllOrders, array_keys($arLinks));
                    $mess .= '[B]' . $arExecutors[ $execId ]['NAME'] . '[/B][BR]';
                    $mess .= implode('[BR][BR]', $arLinks) . '[BR][BR][BR]';
                }

                if (!empty($mess)) {
                    $message = str_replace(
                        '#LINKS#',
                        $mess,
                        Settings::$arEventMessages[ $messageName ]
                    );
                    $bSend = true;
                    Notify::send(
                        [],
                        $messageName,
                        [$userId],
                        $message,
                        true,
                        [
                            'ORDERS' => $arAllOrders,
                        ]
                    );
                }
            }
            if ($bSend) {
                self::setLastDate($messageName, strtotime(date('d.m.Y 10:00:00')));
            }
        }
        return __METHOD__ . '();';
    }

    /**
     * 7) В случае если по истечении 3 рабочих дней после
     * направления главным исполнителем отчета об
     * исполнении поручения либо возврата поручения
     * куратором с замечаниями контролером не принято
     * по нему решение (не нажаты кнопки «Добавить отчет»
     * либо «Отклонить»). И далее – каждый рабочий день
     * в 10.00, пока не будет принято решение по отчету.
     *
     * @return string
     */
    public static function agentOrdersOnControl()
    {
        $messageName = 'ORDERS_ON_CONTROL';
        $lastDate = self::getLastDate($messageName);
        if ($lastDate == '') {
            $lastDate = strtotime(date('d.m.Y 10:00:00', strtotime('YESTERDAY')));
        }

        $ts = time();
        if (self::isHoliday($ts)) {
            return __METHOD__ . '();';
        }
        if ((int)date('H') < 10) {
            return __METHOD__ . '();';
        }
        if ((int)date('H') > 10) {
            return __METHOD__ . '();';
        }

        if ($ts - $lastDate >= 86400) {
            self::log('info', __METHOD__);

            $find = self::addWorkDay($ts, -3);

            $arFilter = [
                'PROPERTY_ACTION'               => Settings::$arActions['CONTROL'],
                '<=PROPERTY_ACTION_DATE'        => date('Y-m-d H:i:s', $find),
                '!PROPERTY_CONTROLER_STATUS'    => 1333, // На позиции
            ];
            self::log('info', '$arFilter = ' . json_encode($arFilter));
            $arOrders = Notify::getOrders($arFilter);
            self::log('info', '$arOrders = ' . count($arOrders));

            $arSend = [];
            $arExecutors = Executors::getList();
            foreach ($arOrders as $arOrder) {
                $arUsers = [
                    $arOrder['PROPERTY_CONTROLER_VALUE']
                ];
                $arUser = CUser::GetByID($arOrder['PROPERTY_CONTROLER_VALUE'])->Fetch();
                if ($arUser['UF_CONTROLER_HEAD'] != '') {
                    $arUsers[] = $arUser['UF_CONTROLER_HEAD'];
                } else {
                    $arUsers = array_merge(
                        $arUsers,
                        Notify::getControlers($arOrder['PROPERTY_CONTROLER_VALUE'])
                    );
                }

                foreach ($arUsers as $uId) {
                    $arSend[ $uId ][ $arOrder['ID'] ] = $arOrder['LINK_BB'];
                }
            }

            $bSend = false;
            foreach ($arSend as $userId => $arLinks) {
                $message = str_replace(
                    '#LINKS#',
                    implode('[BR][BR]', $arLinks),
                    Settings::$arEventMessages[ $messageName ]
                );
                $bSend = true;
                Notify::send(
                    [],
                    $messageName,
                    [$userId],
                    $message,
                    true,
                    [
                        'ORDERS' => array_keys($arLinks),
                    ]
                );
            }

            if ($bSend) {
                self::setLastDate($messageName, strtotime(date('d.m.Y 10:00:00')));
            }
        }

        return __METHOD__ . '();';
    }

    /**
     * 8) В 10:00 направлять следующее уведомление
     * (по всем поручениям, по которым на это время
     * у главного исполнителя висят отчеты
     * в папке «На подписи»)
     *
     * @return string
     */
    public static function agentOrdersOnSign()
    {
        $messageName = 'ORDERS_ON_SIGN';
        $lastDate = self::getLastDate($messageName);
        if ($lastDate == '') {
            $lastDate = strtotime(date('d.m.Y 10:00:00', strtotime('YESTERDAY')));
        }

        $ts = time();
        if (self::isHoliday($ts)) {
            return __METHOD__ . '();';
        }
        if ((int)date('H') < 10) {
            return __METHOD__ . '();';
        }
        if ((int)date('H') > 10) {
            return __METHOD__ . '();';
        }

        if ($ts - $lastDate >= 86400) {
            self::log('info', __METHOD__);

            $arFilter = [
                'PROPERTY_TYPE'             => 1131,
                '!PROPERTY_CURRENT_USER'    => false,
            ];
            $arComments = Notify::getComments($arFilter);

            $arResult = [];
            $arIds = [];
            foreach ($arComments as $arRow) {
                $arResult[ $arRow['PROPERTY_CURRENT_USER_VALUE'] ][] = $arRow['PROPERTY_PORUCH_VALUE'];
                $arIds[] = $arRow['PROPERTY_PORUCH_VALUE'];
            }

            $arSend = [];
            $arExecutors = Executors::getList();
            $arOrders = Notify::getOrders(['ID' => $arIds]);

            $arEnums = [];
            $res = CIBlockPropertyEnum::GetList(
                [
                    'DEF'  => 'DESC',
                    'SORT' => 'ASC'
                ],
                [
                    'IBLOCK_ID' => Settings::$iblockId['ORDERS']
                ]
            );
            while ($row = $res->Fetch()) {
                $arEnums[ $row['PROPERTY_CODE'] ][ $row['ID'] ] = $row;
                $arEnums[ $row['PROPERTY_CODE'] ][ $row['XML_ID'] ] = $row;
            }

            foreach ($arExecutors as $arExecutor) {
                if (empty($arExecutor['PROPERTY_IMPLEMENTATION_VALUE'])) {
                    continue;
                }
                $ruklId = $arExecutor['PROPERTY_RUKOVODITEL_VALUE'];
                if (array_key_exists($ruklId, $arResult)) {
                    foreach ($arExecutor['PROPERTY_IMPLEMENTATION_VALUE'] as $userId) {
                        foreach ($arResult[ $ruklId ] as $orderId) {
                            if (!array_key_exists($orderId, $arOrders)) {
                                continue;
                            }
                            if (
                                $arOrders[ $orderId ]['PROPERTY_WORK_INTER_STATUS_ENUM_ID'] != $arEnums['WORK_INTER_STATUS']['TOSIGN']['ID']
                            ) {
                                continue;
                            }
                            $arSend[ $userId ][ $orderId ] = $arOrders[ $orderId ]['LINK_BB'];
                        }
                    }
                }
            }

            $bSend = false;
            foreach ($arSend as $userId => $arLinks) {
                $message = str_replace(
                    '#LINKS#',
                    implode('[BR][BR]', $arLinks),
                    Settings::$arEventMessages[ $messageName ]
                );
                $bSend = true;
                Notify::send(
                    [],
                    $messageName,
                    [$userId],
                    $message,
                    true,
                    [
                        'ORDERS' => array_keys($arLinks),
                    ]
                );
            }

            if ($bSend) {
                self::setLastDate($messageName, strtotime(date('d.m.Y 10:00:00')));
            }
        }

        return __METHOD__ . '();';
    }

    /**
     * 9) В 10:00 направлять следующее уведомление
     * (по всем поручениям, по которым на это время
     * у главного исполнителя висят отчеты
     * в папке «На визировании»)
     *
     * @return string
     */
    public static function agentOrdersOnVisa()
    {
        $messageName = 'ORDERS_ON_VISA';
        $lastDate = self::getLastDate($messageName);
        if ($lastDate == '') {
            $lastDate = strtotime(date('d.m.Y 10:00:00', strtotime('YESTERDAY')));
        }

        $ts = time();
        if (self::isHoliday($ts)) {
            return __METHOD__ . '();';
        }
        if ((int)date('H') < 10) {
            return __METHOD__ . '();';
        }
        if ((int)date('H') > 10) {
            return __METHOD__ . '();';
        }

        if ($ts - $lastDate >= 86400) {
            self::log('info', __METHOD__);

            $arFilter = [
                'PROPERTY_TYPE'             => 1131,
                'DETAIL_TEXT'               => 'TOVISA',
                '!PROPERTY_CURRENT_USER'    => false,
            ];
            $arComments = Notify::getComments($arFilter);

            $arResult = [];
            $arIds = [];
            foreach ($arComments as $arRow) {
                $arResult[ $arRow['PROPERTY_CURRENT_USER_VALUE'] ][] = $arRow['PROPERTY_PORUCH_VALUE'];
                $arIds[] = $arRow['PROPERTY_PORUCH_VALUE'];
            }

            $arFilter = [
                'PROPERTY_TYPE'             => 1131,
                'PROPERTY_VISA'             => '%:E:%',
                '!PROPERTY_CURRENT_USER'    => false,
            ];
            $arComments = Notify::getComments($arFilter);

            $arResult = [];
            $arIds = [];
            foreach ($arComments as $arRow) {
                foreach ($arRow['PROPERTY_VISA_VALUE'] as $visaRow) {
                    [$userId, $status, $comment, $date] = explode(':', $visaRow, 4);
                    if ($status == 'E') {
                        $arResult[ $userId ][] = $arRow['PROPERTY_PORUCH_VALUE'];
                        $arIds[] = $arRow['PROPERTY_PORUCH_VALUE'];
                    }
                }
            }
            $arIds = array_unique($arIds);

            $arSend = [];
            $arExecutors = Executors::getList();
            $arOrders = Notify::getOrders(['ID' => $arIds]);

            foreach ($arExecutors as $arExecutor) {
                if (empty($arExecutor['PROPERTY_IMPLEMENTATION_VALUE'])) {
                    continue;
                }
                $ruklId = $arExecutor['PROPERTY_RUKOVODITEL_VALUE'];
                if (array_key_exists($ruklId, $arResult)) {
                    foreach ($arExecutor['PROPERTY_IMPLEMENTATION_VALUE'] as $userId) {
                        foreach ($arResult[ $ruklId ] as $orderId) {
                            if (!array_key_exists($orderId, $arOrders)) {
                                continue;
                            }
                            $arSend[ $userId ][ $orderId ] = $arOrders[ $orderId ]['LINK_BB'];
                        }
                    }
                }
            }

            $bSend = false;
            foreach ($arSend as $userId => $arLinks) {
                $message = str_replace(
                    '#LINKS#',
                    implode('[BR][BR]', $arLinks),
                    Settings::$arEventMessages[ $messageName ]
                );
                $bSend = true;
                Notify::send(
                    [],
                    $messageName,
                    [$userId],
                    $message,
                    true,
                    [
                        'ORDERS' => array_keys($arLinks),
                    ]
                );
            }

            if ($bSend) {
                self::setLastDate($messageName, strtotime(date('d.m.Y 10:00:00')));
            }
        }

        return __METHOD__ . '();';
    }

    /**
     * 10) Уведомление о поручениях, сроки
     * исполнения которых истекают через
     * 3 рабочих дня. Если отчет по
     * поручению предоставлен, то
     * уведомление не направляется.
     *
     * @return string
     */
    public static function agentSrokExpireIn3Days()
    {
        $messageName = 'SROK_EXPIRE_IN_3_DAYS';
        $lastDate = self::getLastDate($messageName);
        if ($lastDate == '') {
            $lastDate = strtotime(date('d.m.Y 10:00:00', strtotime('YESTERDAY')));
        }

        $ts = time();
        if (self::isHoliday($ts)) {
            return __METHOD__ . '();';
        }
        if ((int)date('H') < 10) {
            return __METHOD__ . '();';
        }
        if ((int)date('H') > 10) {
            return __METHOD__ . '();';
        }

        if (($ts - $lastDate) >= 86400) {
            self::log('info', __METHOD__);

            $find = self::addWorkDay($ts, 3);

            $arFilter = [
                'PROPERTY_DATE_ISPOLN'  => date('Y-m-d', $find),
                'PROPERTY_ACTION'       => Settings::$arActions['WORK'],
                // '!ID'                   => CIBlockElement::SubQuery(
                //     'PROPERTY_PORUCH',
                //     [
                //         'ACTIVE'        => 'Y',
                //         'IBLOCK_ID'     => Settings::$iblockId['ORDERS_COMMENT'],
                //         'PROPERTY_TYPE' => 1131,
                //     ]
                // ),
            ];
            self::log('info', '$arFilter = ' . json_encode($arFilter));
            $arOrders = Notify::getOrders($arFilter);
            self::log('info', '$arOrders = ' . count($arOrders));

            $arSend = [];
            $arExecutors = Executors::getList();
            foreach ($arOrders as $arOrder) {
                if (self::hasReport($arOrder['ID'])) {
                    continue;
                }
                if ($arExecutors[ $arOrder['PROPERTY_ISPOLNITEL_VALUE'] ]['PROPERTY_TYPE_CODE'] === 'external') {
                    $arUsers = (new Notify())->getAllControlers();
                } else {
                    $arUsers = $arExecutors[ $arOrder['PROPERTY_ISPOLNITEL_VALUE'] ]['PROPERTY_IMPLEMENTATION_VALUE'];
                    if ($arOrder['PROPERTY_DELEGATE_USER_VALUE'] != '') {
                        $arUsers[] = $arOrder['PROPERTY_DELEGATE_USER_VALUE'];
                    }
                }

                foreach ($arUsers as $uId) {
                    $arSend[ $uId ][ $arOrder['ID'] ] = $arOrder['LINK_BB'];
                }
            }

            $bSend = false;
            foreach ($arSend as $userId => $arLinks) {
                $message = str_replace(
                    '#LINKS#',
                    implode('[BR]', $arLinks),
                    Settings::$arEventMessages[ $messageName ]
                );
                $bSend = true;
                Notify::send(
                    [],
                    $messageName,
                    [$userId],
                    $message,
                    true,
                    [
                        'ORDERS' => array_keys($arLinks),
                    ]
                );
            }

            if ($bSend) {
                self::setLastDate($messageName, strtotime(date('d.m.Y 10:00:00')));
            }
        }

        return __METHOD__ . '();';
    }

    /**
     * 11) Уведомление о поручениях,
     * которые делегированы или в соисполнителях
     * указан неактивный пользователь
     *
     * @return string
     */
    public static function agentInactiveUsers()
    {
        $messageName = 'INACTIVE_USERS';
        $lastDate = self::getLastDate($messageName);
        if ($lastDate == '') {
            $lastDate = strtotime(date('d.m.Y 10:00:00', strtotime('YESTERDAY')));
        }

        $ts = time();
        if (self::isHoliday($ts)) {
            return __METHOD__ . '();';
        }
        if ((int)date('H') < 10) {
            return __METHOD__ . '();';
        }
        if ((int)date('H') > 10) {
            return __METHOD__ . '();';
        }

        if (($ts - $lastDate) >= 86400) {
            self::log('info', __METHOD__);

            $arFilter = [
                ['!PROPERTY_ACTION'     => Settings::$arActions['DRAFT']],
                ['!PROPERTY_ACTION'     => Settings::$arActions['ARCHIVE']],
            ];
            self::log('info', '$arFilter = ' . json_encode($arFilter));
            $arOrders = Notify::getOrders($arFilter);
            self::log('info', '$arOrders = ' . count($arOrders));

            $arUsers = [];
            $orm = UserTable::getList([
                'select'    => ['ID'],
                'filter'    => ['ACTIVE' => 'Y']
            ]);
            while ($arUser = $orm->fetch()) {
                $arUsers[ $arUser['ID'] ] = $arUser['ID'];
            }
            
            $arExecutors = Executors::getList();

            $arSend = [];
            $arFields = [
                'DELEGATE_USER',
                'ACCOMPLICES',
            ];
            $arResult = [];
            foreach ($arOrders as $row) {
                foreach ($arFields as $field) {
                    if (empty($row['PROPERTY_' . $field . '_VALUE'])) {
                        continue;
                    }
                    if (is_array($row['PROPERTY_' . $field . '_VALUE'])) {
                        foreach ($row['PROPERTY_' . $field . '_VALUE'] as $uId) {
                            if ($uId > 0 && !array_key_exists($uId, $arUsers)) {
                                $arResult[ $row['PROPERTY_ISPOLNITEL_VALUE'] ][ $uId ][ $field ][] = $row['LINK_BB'];
                            }
                        }
                    } else {
                        if (!array_key_exists($row['PROPERTY_' . $field . '_VALUE'], $arUsers)) {
                            $uId = $row['PROPERTY_' . $field . '_VALUE'];
                            $arExecutor = $arExecutors[ $row['PROPERTY_ISPOLNITEL_VALUE'] ];
                            $arCurList = array_merge(
                                [$arExecutor['PROPERTY_RUKOVODITEL_VALUE']],
                                $arExecutor['PROPERTY_ISPOLNITELI_VALUE'],
                                $arExecutor['PROPERTY_ZAMESTITELI_VALUE'],
                                $arExecutor['PROPERTY_IMPLEMENTATION_VALUE']
                            );

                            if (in_array($uId, $arCurList)) {
                                $arResult[ $arExecutor['ID'] ][ $uId ][ $field ][] = $row['LINK_BB'];
                            }
                        }
                    }
                }

                if (!empty($row['PROPERTY_SUBEXECUTOR_VALUE'])) {
                    foreach ($row['PROPERTY_SUBEXECUTOR_VALUE'] as $key => $execId) {
                        $uId = $row['PROPERTY_SUBEXECUTOR_DESCRIPTION'][ $key ];
                        if ($uId > 0 && !array_key_exists($uId, $arUsers)) {
                            $arResult[ $execId ][ $uId ]['SUBEXECUTOR'][] = $row['LINK_BB'];
                        }
                    }
                }
            }

            $bSend = false;
            if (!empty($arResult)) {
                foreach ($arResult as $execId => $users) {
                    if (!isset($arExecutors[ $execId ])) {
                        continue;
                    }
                    if (empty($arExecutors[ $execId ]['PROPERTY_IMPLEMENTATION_VALUE'])) {
                        continue;
                    }
                    $message = '';
                    foreach ($users as $uId => $fields) {
                        $arUser = CUser::GetByID($uId)->Fetch();
                        $message .= '[B]' . $arUser['LAST_NAME'] . ' ' . $arUser['NAME'] . '[/B]#BR#';
                        foreach ($fields as $field => $orders) {
                            if ($field == 'DELEGATE_USER') {
                                $message .= '[I]Исполнитель[/I]';
                            } else {
                                $message .= '[I]Соисполнитель[/I]';
                            }
                            $message .= '#BR#' . implode('#BR#', $orders) . '#BR#';
                        }
                    }
                    $bSend = true;
                    $message = str_replace(
                        '#LINKS#',
                        $message,
                        Settings::$arEventMessages[ $messageName ]
                    );
                    Notify::send(
                        [],
                        $messageName,
                        $arExecutors[ $execId ]['PROPERTY_IMPLEMENTATION_VALUE'],
                        $message,
                        false,
                        []
                    );
                }
            }

            if ($bSend) {
                self::setLastDate($messageName, strtotime(date('d.m.Y 10:00:00')));
            }
        }

        return __METHOD__ . '();';
    }

    /**
     * Установить дату последней отправки уведомления.
     *
     * @param string $messageName Код сообщения.
     * @param string $value       Значение.
     *
     * @return boolean
     */
    private static function setLastDate(
        string $messageName = '',
        string $value = ''
    ) {
        return Option::set('controlorders', 'LAST_DATE_' . $messageName, $value);
    }

    /**
     * Получить дату последней отправки уведомления.
     *
     * @param string $messageName Код сообщения.
     *
     * @return string
     */
    private static function getLastDate(string $messageName = '')
    {
        return Option::get('controlorders', 'LAST_DATE_' . $messageName);
    }

    /**
     * Получить список выходных.
     *
     * @return array
     */
    private static function getHolidays()
    {
        Loader::includeModule('bitrix.planner');
        $obCalc = new \HolidayList\CVacations();
        $arAllHolidays = $obCalc->getHolidays();
        return array_map(
            function ($timestamp) {
                return date('d.m', $timestamp);
            },
            array_merge(
                $arAllHolidays['holydays'],
                $arAllHolidays['weekends']
            )
        );
    }

    /**
     * Проверка даты на выходной.
     *
     * @param integer $date TS Даты.
     *
     * @return boolean
     */
    private static function isHoliday(int $date)
    {
        if (in_array(date('w', $date), [0, 6])) {
            return true;
        }
        $arHolidays = self::getHolidays();
        return (in_array(date('d.m', $date), $arHolidays, true));
    }

    /**
     * Добавить N рабочих дней к дате.
     *
     * @param integer $date TS Даты.
     * @param integer $days Количество дней.
     *
     * @return integer
     */
    private static function addWorkDay(int $date = 0, int $days = 0)
    {
        $delta = 86400;
        if ($days < 0) {
            $delta *= -1;
        }

        $days = abs($days);
        $iterations = 0;

        while ($days > 0 && $iterations < 1000) {
            ++$iterations;
            $date += $delta;

            if (self::isHoliday($date)) {
                continue;
            }
            --$days;
        }

        return $date;
    }

    /**
     * Есть ли отчёт исполнителя по поручению?
     *
     * @param int $orderId ID поручения
     *
     * @return boolean
     */
    public static function hasReport(int $orderId = 0)
    {
        if ($orderId <= 0) {
            return false;
        }
        $arOrder = (new Orders())->getById($orderId);
        if (!empty($arOrder['PROPERTY_WORK_INTER_STATUS_VALUE'])) {
            return true;
        }
        $arFilter = [
            'ACTIVE'            => 'Y',
            'IBLOCK_ID'         => Settings::$iblockId['ORDERS_COMMENT'],
            'PROPERTY_TYPE'     => 1131,
            'PROPERTY_PORUCH'   => $orderId,
        ];
        Loader::includeModule('iblock');
        $obRes = CIBlockElement::GetList(
            [
                'DATE_CREATE' => 'DESC'
            ],
            $arFilter,
            false,
            [
                'nTopCount' => 1
            ],
            [
                'ID',
                'NAME',
                'ACTIVE',
                'XML_ID',
                'IBLOCK_ID',
                'DATE_CREATE',
                'DETAIL_TEXT',
                'PREVIEW_TEXT',
                'PROPERTY_PORUCH', /* Поручение */
                'PROPERTY_USER', /* Пользователь */
                'PROPERTY_TYPE', /* Тип комментария */
                'PROPERTY_DOCS', /* Документы */
                'PROPERTY_ECP', /* Подпись ЭЦП */
                'PROPERTY_DATE_VOTE', /* Дата опроса */
                'PROPERTY_RESULT_VOTE', /* Результат опроса */
                'PROPERTY_FILE_ECP', /* Файл для подписи */
                'PROPERTY_VISA', /* Визирование */
                'PROPERTY_CURRENT_USER', /* Текущий пользователь */
                'PROPERTY_COMMENT', /* Комментарий главного исполнителя */
                'PROPERTY_BROKEN_SROK', /* Срок нарушен */
                'PROPERTY_VISA_TYPE', /* Тип визирования */
                'PROPERTY_DATE_FACT', /* Дата фактического исполнения */
                'PROPERTY_CONTROLER_COMMENT', /* Комментарий контролера */
                'PROPERTY_SIGNER', /* Кто подписывает */
                'PROPERTY_STATUS', /* Статус */
            ]
        );
        $arResult = [];
        while ($arRow = $obRes->GetNext()) {
            if (!empty($arRow['PROPERTY_COMMENT_VALUE'])) {
                return false;
            }
            if (!empty($arRow['PROPERTY_CONTROLER_COMMENT_VALUE'])) {
                return false;
            }
            if ($arRow['PROPERTY_STATUS_VALUE'] == 'Черновик') {
                return false;
            }
            if ((int)$arRow['PROPERTY_CURRENT_USER_VALUE'] == (int)$arOrder['PROPERTY_DELEGATE_USER_VALUE']) {
                return false;
            }
        }

        return $obRes->SelectedRowsCount() > 0;
    }
}
