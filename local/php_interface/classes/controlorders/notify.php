<?php

namespace Citto\ControlOrders;

use CUser;
use CEvent;
use CIMNotify;
use Exception;
use CIMMessenger;
use CIBlockElement;
use Monolog\Logger;
use forumTextParser;
use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;
use Monolog\Handler\RotatingFileHandler;
use Sprint\Migration\Helpers\HlblockHelper;
use Bitrix\Highloadblock\HighloadBlockTable as HLTable;

/**
 * Уведомления модуля Контроля поручений.
 */
class Notify
{
    public static $botId = 4200;

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
                $_SERVER['DOCUMENT_ROOT'] . '/local/logs/controlorders/notify.log',
                30
            )
        );
        $logger->log($level, $message, $context);
        return true;
    }

    /**
     * Логирование отправки в HL.
     *
     * @param array $arFields Массив для вставки
     *
     * @return integer
     */
    private static function logTable($arFields = [])
    {
        $helper = new HlblockHelper();
        $hlId = $helper->getHlblockId('ControlOrdersNotifyLog');
        $hlblock           = HLTable::getById($hlId)->fetch();
        $entity            = HLTable::compileEntity($hlblock);
        $entityDataClass   = $entity->getDataClass();

        return $entityDataClass::add($arFields);
    }

    /**
     * Отправка уведомлений.
     *
     * @param array   $arIds        ID поручений.
     * @param string  $strMessageId Код сообщения.
     * @param array   $arUserIds    ID пользователей.
     * @param string  $strMessage   Текст сообщения.
     * @param boolean $bSentIm      Отправлять ли в месенжер.
     *
     * @return boolean
     */
    public static function send(
        array $arIds = [],
        string $strMessageId = '',
        array $arUserIds = [],
        string $strMessage = '',
        bool $bSentIm = true,
        array $arAdditionals = []
    ) {
        if (false !== mb_strpos($_SERVER['SERVER_NAME'], 'dev.tularegion')) {
            return false;
        }
        if (false !== mb_strpos($_SERVER['SERVER_NAME'], 'localhost')) {
            return false;
        }
        self::log('info', __METHOD__);
        self::log('info', '$arIds = ' . json_encode($arIds));
        self::log('info', '$strMessageId = ' . json_encode($strMessageId));
        self::log('info', '$arUserIds = ' . json_encode($arUserIds));
        self::log('info', '$bSentIm = ' . json_encode($bSentIm));
        self::log('info', '$arAdditionals = ' . json_encode($arAdditionals));
        if (empty($strMessageId) && empty($strMessage)) {
            return false;
        }

        global $APPLICATION;

        $arSend = [];
        $arIds = array_filter($arIds);
        if (!empty($arIds)) {
            $arOrders = self::getOrders(['ID' => $arIds]);
            $arExecutors = Executors::getList();
            foreach ($arOrders as $row) {
                if (empty($arUserIds)) {
                    if ($strMessageId == 'NEW') {
                        if ($row['PROPERTY_DELEGATE_USER_VALUE'] != '') {
                            $arUserIds = [
                                $row['PROPERTY_DELEGATE_USER_VALUE']
                            ];
                        }

                        // Якушкина Г.И.
                        if ($row['PROPERTY_ISPOLNITEL_VALUE'] == 250900) {
                            $arUserIds[] = $arExecutors[ $row['PROPERTY_ISPOLNITEL_VALUE'] ]['PROPERTY_RUKOVODITEL_VALUE'];
                        }
                    } elseif ($strMessageId == 'NEW_IMPLEMENTATION') {
                        $arUserIds = $arExecutors[ $row['PROPERTY_ISPOLNITEL_VALUE'] ]['PROPERTY_IMPLEMENTATION_VALUE'];
                    } elseif ($strMessageId == 'ACCOMPLICES_REPORT') {
                        $arUserIds = (new Notify())->getAllControlers();
                    }
                }

                if (!empty($arUserIds)) {
                    foreach ($arUserIds as $uId) {
                        $arSend[ $uId ][ $row['ID'] ] = $row['LINK_BB'];
                    }
                }
            }
        } else {
            foreach ($arUserIds as $uId) {
                $arSend[ $uId ][] = $strMessage;
            }
        }

        $arOrders = null;
        Loader::includeModule('im');
        Loader::includeModule('forum');
        Loader::includeModule('socialnetwork');
        $arMessageFields = [
            'DATE_CREATE'       => $GLOBALS['DB']->CurrentTimeFunction(),
            'MESSAGE_TYPE'      => SONET_MESSAGE_SYSTEM,
            'FROM_USER_ID'      => 1,
            'MESSAGE'           => '',
            'MESSAGE_OUT'       => '#SKIP#',
            'EMAIL_TEMPLATE'    => 'some',
            'NOTIFY_MODULE'     => 'controlorders',
            'NOTIFY_EVENT'      => (!empty($strMessageId) ? $strMessageId : 'default'),
            'NOTIFY_TAG'        => 'controlorders|' . $strMessageId,
        ];
        $obParser = new ForumTextParser();

        foreach ($arSend as $userId => $arOrders) {
            if (empty($strMessage)) {
                $strMessage = Settings::$arEventMessages[ $strMessageId ];
            }
            if (!empty($arIds)) {
                $strMessage = str_replace(
                    [
                        '#LINKS#'
                    ],
                    [
                        implode('[BR][BR]', $arOrders)
                    ],
                    $strMessage
                );
            }

            $strMessage = str_replace(
                ['<br/>', '<br>', '<br />', '#BR#'],
                '[BR]',
                $strMessage
            );

            $arUser = [];
            if (false === mb_strpos($userId, 'chat')) {
                $arUser = CUser::GetByID($userId)->Fetch();
                if ($arUser['ACTIVE'] != 'Y') {
                    self::log('info', 'Пользователь ' . $userId . ' неактивен. Не отправляем сообщение');
                    continue;
                }
            }

            if (
                in_array(
                    $userId,
                    [
                        581, // Галина Якушкина
                    ]
                )
            ) {
                $bSentIm = false;
                if (in_array(377491, $arIds)) {
                	self::log('info', 'Не отправляем Якушкиной уведомление');
                	return true;
                }
            }

            if ($bSentIm) {
                try {
                    $APPLICATION->ResetException();
                    $arMessageFields['TO_USER_ID'] = $userId;
                    $arMessageFields['MESSAGE']    = $strMessage;
                    $arMessageFields['NOTIFY_TAG'] = 'controlorders|' . $strMessageId . '|' . $userId;
                    $arMessageFieldsNew = $arMessageFields;
                    $arMessageFieldsNew['FROM_USER_ID'] = self::$botId;
                    $arMessageFieldsNew['MESSAGE_TYPE'] = 'P';
                    if (0 === mb_strpos($userId, 'chat')) {
                        $arMessageFieldsNew['DIALOG_ID'] = $userId;
                        unset($arMessageFieldsNew['TO_USER_ID']);
                        unset($arMessageFieldsNew['MESSAGE_TYPE']);
                    }

                    $arMessageFieldsNew['MESSAGE'] = str_replace('#BUTTONS#', '', $arMessageFieldsNew['MESSAGE']);
                    $arMessageFieldsNew['MESSAGE'] = str_replace('#BR##BR##BR##BR#', '#BR##BR#', $arMessageFieldsNew['MESSAGE']);
                    $return = CIMMessenger::Add($arMessageFieldsNew);

                    self::log('info', '$return = ' . json_encode($return));
                    self::log('info', 'Отправлено сообщение ' . $arMessageFields['NOTIFY_TAG']);

                    if ($ex = $APPLICATION->GetException()) {
                        self::log('info', 'ERROR SEND IM (APPLICATION): ' . $ex->GetString());
                    } else {
                        $arSendOrders = array_keys($arOrders);
                        if (isset($arAdditionals['ORDERS']) && !empty($arAdditionals['ORDERS'])) {
                            $arSendOrders = $arAdditionals['ORDERS'];
                        }
                        foreach ($arSendOrders as $orderId) {
                            self::logTable([
                                'UF_ORDER'      => $orderId,
                                'UF_ORDER_ID'   => $orderId,
                                'UF_TYPE'       => $strMessageId,
                                'UF_USER'       => $userId,
                                'UF_DATE'       => date('d.m.Y H:i:s'),
                                'UF_TEXT'       => $strMessage,
                            ]);
                        }
                    }
                } catch (Exception $exc) {
                    self::log('info', 'ERROR SEND IM (Exception): ' . $exc->getMessage());
                }
            }

            $strMessageTitle = Settings::$arEventMessageTitles[ $strMessageId ];
            if (empty($strMessageTitle)) {
                $strMessageTitle = 'Уведомление';
            }

            if (!empty($strMessageTitle) && !empty($arUser)) {
                try {
                    $APPLICATION->ResetException();
                    $prefix = '';
                    if ($userId == 581) {
                        // NB: чтобы не срабатывал запрет рассылки для 102 группы
                        $prefix = 'NB: ';
                    }
                    $arMailFields = [
                        'SENDER'    => 'info@corp.cit71.ru',
                        'REPLY_TO'  => 'info@corp.cit71.ru',
                        'RECEIVER'  => $arUser['EMAIL'],
                        'TITLE'     => $prefix . '[Контроль поручений] ' . $strMessageTitle,
                    ];

                    $strMessage = $obParser->convert($strMessage);
                    $strMessage = str_replace('[BR]', '<br/>', $strMessage);
                    $strMessage = str_replace(
                        ['[B]', '[/B]', '[I]', '[/I]'],
                        ['<b>', '</b>', '<i>', '</i>'],
                        $strMessage
                    );

                    if (isset($arAdditionals['BUTTONS'])) {
                        $strMessage = str_replace('#BUTTONS#', $arAdditionals['BUTTONS'], $strMessage);
                    }

                    $arMailFields['MESSAGE'] = '<p style="font-family:\'PT Astra Serif\'">' . $strMessage . '</p>';
                    $obEvent = new CEvent();
                    $return = $obEvent->Send('BIZPROC_HTML_MAIL_TEMPLATE', 's1', $arMailFields, 'N');

                    self::log('info', '$strMessage = ' . $strMessage);
                    self::log('info', '$return = ' . json_encode($return));
                    self::log('info', 'Отправлено письмо ' . $strMessageId . ' на адрес ' . $arUser['EMAIL']);

                    if ($ex = $APPLICATION->GetException()) {
                        self::log('info', 'ERROR SEND MAIL (APPLICATION): ' . $ex->GetString());
                    }
                } catch (Exception $exc) {
                    self::log('info', 'ERROR SEND MAIL (Exception): ' . $exc->getMessage());
                }
            }
        }
        return true;
    }

    /**
     * Получить поручения по фильтру.
     *
     * @param array $arFilter Фильтр.
     *
     * @return array
     */
    public static function getOrders(array $arFilter = [])
    {
        $arFilter = array_merge(
            [
                'IBLOCK_ID' => Settings::$iblockId['ORDERS'],
                'ACTIVE'    => 'Y',
                ['!PROPERTY_ISPOLNITEL' => 7770],
            ],
            $arFilter
        );
        Loader::includeModule('iblock');
        $obRes = CIBlockElement::GetList(
            [],
            $arFilter,
            false,
            false,
            [
                'ID',
                'NAME',
                'CODE',
                'IBLOCK_ID',
                'DETAIL_TEXT',
                'PROPERTY_ISPOLNITEL',
                'PROPERTY_CONTROLER',
                'PROPERTY_DATE_ISPOLN',
                'PROPERTY_ACTION',
                'PROPERTY_ACTION_DATE',
                'PROPERTY_DELEGATE_USER',
                'PROPERTY_ACCOMPLICES',
                'PROPERTY_SUBEXECUTOR',
                'PROPERTY_WORK_INTER_STATUS',
            ]
        );
        $arResult = [];
        while ($arRow = $obRes->GetNext()) {
            $arRow['~DETAIL_TEXT'] = trim(str_replace('&quot;', '"', $arRow['~DETAIL_TEXT']));
            $arRow['~DETAIL_TEXT'] = str_replace(["\r\n", "\r", "\n"], '[BR]', strip_tags($arRow['~DETAIL_TEXT']));
            if (empty($arRow['~DETAIL_TEXT'])) {
                $arRow['~DETAIL_TEXT'] = 'Поручение';
            }
            $arRow['LINK'] = '<a href="https://corp.tularegion.local/control-orders/?detail=' . $arRow['ID'] . '" target="_blank">' . $arRow['~DETAIL_TEXT'] . '</a>';
            $arRow['LINK_BB'] = '[URL=https://corp.tularegion.local/control-orders/?detail=' . $arRow['ID'] . ']' . $arRow['~DETAIL_TEXT'] . '[/URL]';
            $arResult[ $arRow['ID'] ] = $arRow;
        }

        return $arResult;
    }

    /**
     * Получить комментарии по фильтру.
     *
     * @param array $arFilter Фильтр.
     *
     * @return array
     */
    public static function getComments(array $arFilter = [])
    {
        $arFilter = array_merge(
            [
                'IBLOCK_ID' => Settings::$iblockId['ORDERS_COMMENT'],
                'ACTIVE'    => 'Y',
            ],
            $arFilter
        );

        Loader::includeModule('iblock');
        $obRes = CIBlockElement::GetList(
            [],
            $arFilter,
            false,
            false,
            [
                'ID',
                'NAME',
                'IBLOCK_ID',
                'DATE_CREATE',
                'PREVIEW_TEXT',
                'DETAIL_TEXT',
                'PROPERTY_PORUCH',
                'PROPERTY_USER',
                'PROPERTY_TYPE',
                'PROPERTY_DOCS',
                'PROPERTY_ECP',
                'PROPERTY_DATE_VOTE',
                'PROPERTY_RESULT_VOTE',
                'PROPERTY_FILE_ECP',
                'PROPERTY_VISA',
                'PROPERTY_CURRENT_USER',
                'PROPERTY_COMMENT',
                'PROPERTY_BROKEN_SROK',
                'PROPERTY_VISA_TYPE',
                'PROPERTY_DATE_FACT',
                'PROPERTY_CONTROLER_COMMENT',
            ]
        );
        $arResult = [];
        while ($arRow = $obRes->GetNext()) {
            $arResult[ $arRow['ID'] ] = $arRow;
        }

        return $arResult;
    }

    /**
     * Получить неглавных контролеров для переданного пользователя.
     *
     * @param int $uId ID контролера
     *
     * @return array
     */
    public static function getControlers(int $uId = 0)
    {
        if ($uId <= 0) {
            return [];
        }
        $arReturn = [];
        $res = UserTable::getList([
            'select'    => ['ID'],
            'filter'    => ['UF_CONTROLER_HEAD' => $uId]
        ]);
        while ($row = $res->fetch()) {
            $arReturn[] = $row['ID'];
        }

        return $arReturn;
    }

    /**
     * Получить всех контролеров.
     *
     * @return array
     */
    public static function getAllControlers()
    {
        $arReturn = [];
        $res = UserTable::getList([
            'select'    => ['ID', 'UF_CONTROLER_HEAD'],
            'filter'    => ['!UF_CONTROLER_HEAD' => false]
        ]);
        while ($row = $res->fetch()) {
            $arReturn[] = $row['ID'];
            $arReturn[] = $row['UF_CONTROLER_HEAD'];
        }

        $arReturn = array_unique($arReturn);

        return $arReturn;
    }
}
