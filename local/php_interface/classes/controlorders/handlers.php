<?php

namespace Citto\ControlOrders;

use CIBlockElement;
use Monolog\Logger;
use CBitrixComponent;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Citto\ControlOrders\Main\Component as MainComponent;

/**
 * Уведомления модуля Контроля поручений.
 */
class Handlers
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
                $_SERVER['DOCUMENT_ROOT'] . '/local/logs/controlorders/handlers.log',
                30
            )
        );
        $logger->log($level, $message, $context);
        return true;
    }

    /**
     * Обработчик перед добавлением Элемента.
     *
     * @param array $arFields Поля элемента.
     *
     * @return boolean
     */
    public static function handleOnBeforeIBlockElementAdd(array &$arFields)
    {
        if ($arFields['IBLOCK_ID'] == Settings::$iblockId['ORDERS']) {
            if (!isset($arFields['PROPERTY_VALUES']['ACTION_DATE'])) {
                $arFields['PROPERTY_VALUES']['ACTION_DATE'] = date('d.m.Y H:i:s');
            }
        }
        return true;
    }

    /**
     * Обработчик после добавлением Элемента.
     *
     * @param array $arFields Поля элемента.
     *
     * @return boolean
     */
    public static function handleOnAfterIBlockElementAdd(array &$arFields)
    {
        if ($arFields['IBLOCK_ID'] == Settings::$iblockId['ORDERS']) {
            if ($arFields['PROPERTY_VALUES']['ACTION'] == Settings::$arActions['NEW']) {
                Notify::send([$arFields['ID']], 'NEW');
                Notify::send([$arFields['ID']], 'NEW_IMPLEMENTATION');

                if (!empty($arFields['PROPERTY_VALUES']['SUBEXECUTOR'])) {
                    $arExecutors = Executors::getList();
                    $arSendUsers = [];
                    foreach ($arFields['PROPERTY_VALUES']['SUBEXECUTOR'] as $subExec) {
                        if (false !== mb_strpos($subExec['VALUE'], ':')) {
                            $subExec['VALUE'] = explode(':', $subExec['VALUE'])[0];
                        }
                        $arSendUsers = array_merge(
                            $arSendUsers,
                            $arExecutors[ $subExec['VALUE'] ]['PROPERTY_IMPLEMENTATION_VALUE']
                        );
                    }
                    $arSendUsers = array_unique(array_filter($arSendUsers));
                    if (!empty($arSendUsers)) {
                        Notify::send([$arFields['ID']], 'ACCOMPLICES', $arSendUsers);
                    }
                }
            }

            BXClearCache(true, '/citto/controlorders/MenuCounters/');
        }
        return true;
    }

    /**
     * Обработчик перед изменением свойства элемента.
     * @param integer $ELEMENT_ID      ID элемента.
     * @param integer $IBLOCK_ID       ID инфоблока.
     * @param array   $PROPERTY_VALUES Массив измененных свойств.
     * @param array   $propertyList    Массив свойств.
     * @param array   $arDBProps       Массив данных в базе.
     *
     * @return boolean
     */
    public static function handleOnIBlockElementSetPropertyValuesEx(
        int $ELEMENT_ID,
        int $IBLOCK_ID,
        array $PROPERTY_VALUES,
        array $propertyList,
        array $arDBProps
    ) {
        if ($IBLOCK_ID == Settings::$iblockId['ORDERS']) {
            $arPropIds = [];
            foreach ($propertyList as $prop) {
                $arPropIds[ $prop['CODE'] ] = $prop['ID'];
            }

            /*
             * Если изменилось состояние поручения.
             */
            if (array_key_exists('ACTION', $PROPERTY_VALUES)) {
                $propId = $arPropIds['ACTION'];
                if ($propId > 0) {
                    $arCurrentAction = current($arDBProps[ $propId ]);
                    if (
                        $arCurrentAction['VALUE'] != Settings::$arActions['NEW'] &&
                        $PROPERTY_VALUES['ACTION'] == Settings::$arActions['NEW']
                    ) {
                        Notify::send([$ELEMENT_ID], 'NEW');
                        Notify::send([$ELEMENT_ID], 'NEW_IMPLEMENTATION');
                        CIBlockElement::SetPropertyValuesEx($ELEMENT_ID, false, ['WORK_INTER_STATUS' => false]);
                    }

                    /*
                     * Зафиксировать дату изменения состояния
                     */
                    if ($arCurrentAction['VALUE'] != $PROPERTY_VALUES['ACTION']) {
                        CIBlockElement::SetPropertyValuesEx($ELEMENT_ID, false, ['ACTION_DATE' => date('d.m.Y H:i:s')]);
                    }
                }
            }

            /*
             * Если изменились соисполнители.
             */
            if (array_key_exists('ACCOMPLICES', $PROPERTY_VALUES)) {
                $propId = $arPropIds['ACCOMPLICES'];
                if ($propId > 0) {
                    $arOldData = [];
                    foreach ($arDBProps[ $propId ] as $row) {
                        $arOldData[] = $row['VALUE'];
                    }
                    $arNewData = $PROPERTY_VALUES['ACCOMPLICES'];

                    $arAddedUsers = array_diff($arNewData, $arOldData);
                    if (!empty($arAddedUsers)) {
                        Notify::send([$ELEMENT_ID], 'ACCOMPLICES', $arAddedUsers);
                        self::addDelegateHistory(
                            $ELEMENT_ID,
                            'ACCOMPLICES',
                            $arAddedUsers,
                            $PROPERTY_VALUES['DELEGATE_COMMENT'],
                            $PROPERTY_VALUES['DELEGATE_SROK']??false
                        );
                    }
                }
            }

            /*
             * Если изменились соисполнители-министерства.
             * Отправить ответственным исполнителям о новом и при делегировании конечному пользователю.
             */
            if (array_key_exists('SUBEXECUTOR', $PROPERTY_VALUES)) {
                $propId = $arPropIds['SUBEXECUTOR'];
                if ($propId > 0) {
                    $arOldData = [];
                    $arOldIds = [];
                    $arOldUsers = [];
                    $arNewData = [];
                    $arNewIds = [];
                    $arNewUsers = [];
                    foreach ($arDBProps[ $propId ] as $row) {
                        $arOldData[] = [
                            'VALUE'         => $row['VALUE'],
                            'DESCRIPTION'   => $row['DESCRIPTION'],
                        ];
                        if (false !== mb_strpos($row['VALUE'], ':')) {
                            $row['VALUE'] = explode(':', $row['VALUE'])[0];
                        }
                        $arOldIds[] = $row['VALUE'];
                        if ((int)$row['DESCRIPTION'] > 0) {
                            $arOldUsers[] = $row['DESCRIPTION'];
                        }
                    }

                    foreach ($PROPERTY_VALUES['SUBEXECUTOR'] as $row) {
                        $arNewData[] = [
                            'VALUE'         => $row['VALUE'],
                            'DESCRIPTION'   => $row['DESCRIPTION'],
                        ];
                        if (false !== mb_strpos($row['VALUE'], ':')) {
                            $row['VALUE'] = explode(':', $row['VALUE'])[0];
                        }
                        $arNewIds[] = $row['VALUE'];
                        if ((int)$row['DESCRIPTION'] > 0) {
                            $arNewUsers[] = $row['DESCRIPTION'];
                        }
                    }

                    $arSendUsers = array_filter(array_diff($arNewUsers, $arOldUsers));
                    if (!empty($arSendUsers)) {
                        self::addDelegateHistory(
                            $ELEMENT_ID,
                            'SUBEXECUTOR_USER',
                            $arSendUsers,
                            $PROPERTY_VALUES['DELEGATE_COMMENT'],
                            $PROPERTY_VALUES['DELEGATE_SROK']??false
                        );
                    }
                    $arAddedIds = array_filter(array_diff($arNewIds, $arOldIds));
                    if (!empty($arAddedIds)) {
                        self::addDelegateHistory(
                            $ELEMENT_ID,
                            'SUBEXECUTOR',
                            $arAddedIds,
                            $PROPERTY_VALUES['DELEGATE_COMMENT'],
                            $PROPERTY_VALUES['DELEGATE_SROK']??false
                        );
                        $arExecutors = Executors::getList();
                        foreach ($arAddedIds as $ispId) {
                            $arSendUsers = array_merge(
                                $arSendUsers,
                                $arExecutors[ $ispId ]['PROPERTY_IMPLEMENTATION_VALUE']
                            );
                        }
                    }

                    if (!empty($arSendUsers)) {
                        Notify::send([$ELEMENT_ID], 'ACCOMPLICES', $arSendUsers);
                    }
                }
            }

            /*
             * Если изменился конечный исполнитель.
             */
            if (array_key_exists('DELEGATE_USER', $PROPERTY_VALUES)) {
                $propId = $arPropIds['DELEGATE_USER'];
                if ($propId > 0) {
                    $oldData = current($arDBProps[ $propId ])['VALUE'];
                    $newData = $PROPERTY_VALUES['DELEGATE_USER'];
                    if (
                        $newData != $oldData &&
                        $newData != $GLOBALS['USER']->GetID()
                    ) {
                        Notify::send([$ELEMENT_ID], 'NEW', [$newData]);
                    }
                    if (
                        $newData != $oldData ||
                        !empty($PROPERTY_VALUES['DELEGATE_COMMENT']) ||
                        !empty($PROPERTY_VALUES['DELEGATE_SROK'])
                    ) {
                        self::addDelegateHistory(
                            $ELEMENT_ID,
                            'DELEGATE',
                            [$newData],
                            $PROPERTY_VALUES['DELEGATE_COMMENT'],
                            $PROPERTY_VALUES['DELEGATE_SROK']??false
                        );
                    }
                }
            }

            CBitrixComponent::includeComponentClass('citto:checkorders');
            $obComponent = new MainComponent();
            $arSkip = [
                'VIEWS',
                // 'NUMBER',
                // 'NOT_STATS',
                // 'ACTION_DATE',
                'HISTORY_SROK',
                // 'DELEGATE_HISTORY',
            ];
            foreach ($PROPERTY_VALUES as $key => $newData) {
                if (!in_array($key, $arSkip)) {
                    $propId = $arPropIds[ $key ];
                    if ($propId > 0) {
                        $oldData = $arDBProps[ $propId ];
                        if (count($oldData) == 1) {
                            $oldData = current($oldData)['VALUE'];
                        } else {
                            $oldDataRaw = $oldData;
                            $oldData = [];
                            foreach ($oldDataRaw as $row) {
                                $oldData[] = $row['VALUE'];
                            }
                        }
                        if (is_array($oldData) && !is_array($newData)) {
                            $newData = [$newData];
                        }
                        if (is_array($newData) && !is_array($oldData)) {
                            $oldData = [$oldData];
                        }
                        if (0 === mb_strpos($key, 'DATE_')) {
                            if (is_array($newData)) {
                                $newData = array_map('strtotime', $newData);
                                $oldData = array_map('strtotime', $oldData);
                            } else {
                                $newData = strtotime($newData);
                                $oldData = strtotime($oldData);
                            }
                        }
                        if (is_array($newData)) {
                            foreach ($newData as $nDataKey => $nDataValue) {
                                if (is_array($nDataValue) && empty($nDataValue['VALUE'])) {
                                    unset($newData[ $nDataKey ]);
                                }
                            }
                            $newData = array_filter($newData);
                        }
                        sort($newData);
                        sort($oldData);
                        if (md5(serialize($newData)) != md5(serialize($oldData))) {
                            $obComponent->log(
                                $ELEMENT_ID,
                                'Изменено свойство',
                                [
                                    'PROP'  => $key,
                                    'OLD'   => $oldData,
                                    'NEW'   => $newData,
                                ]
                            );
                        }
                    }
                }
            }

            BXClearCache(true, '/citto/controlorders/MenuCounters/');
        }

        return true;
    }

    /**
     * Добавить в историю изменений новую запись
     * @param type   $id      ID поручения.
     * @param type   $type    Тип делегирования.
     * @param array  $users   Массив новых пользователей.
     * @param string $comment Комментарий при делегировании.
     * @return bool
     */
    protected static function addDelegateHistory(
        $id,
        $type,
        $users = [],
        $comment = '',
        $srok = ''
    ) {
        $arHistory = [];
        $res = CIBlockElement::GetProperty(
            Settings::$iblockId['ORDERS'],
            $id,
            'sort',
            'asc',
            ['CODE' => 'DELEGATE_HISTORY']
        );
        while ($row = $res->GetNext()) {
            $arHistory[] = $row['~VALUE'];
        }

        foreach ($users as $user) {
            $arHistory[] = json_encode([
                'TIME'          => time(),
                'CURRENT_USER'  => (int)$GLOBALS['USER']->GetID(),
                $type           => $user,
                'COMMENT'       => trim($comment),
                'SROK'          => $srok ?? false
            ], JSON_UNESCAPED_UNICODE);
        }

        CIBlockElement::SetPropertyValuesEx(
            $id,
            false,
            [
                'DELEGATE_HISTORY' => $arHistory
            ]
        );
        return true;
    }
}
