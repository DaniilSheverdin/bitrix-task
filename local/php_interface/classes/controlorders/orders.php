<?php

namespace Citto\ControlOrders;

use CPHPCache;
use CIBlockElement;
use CBitrixComponent;
use Bitrix\Main\Loader;
use CIBlockPropertyEnum;
use Citto\ControlOrders\Main\Component as MainComponent;

class Orders
{
    /** @var array */
    public $arFields = [
        'ID',
        'NAME',
        'TAGS',
        'ACTIVE',
        'XML_ID',
        'IBLOCK_ID',
        'DATE_CREATE',
        'DETAIL_TEXT',
        'PROPERTY_POST', /* Куратор. */
        'PROPERTY_ISPOLNITEL', /* Исполнитель. */
        'PROPERTY_CONTROLER', /* Контролер. */
        'PROPERTY_NUMBER', /* Номер обращения. */
        'PROPERTY_OBJECT', /* Обьект. */
        'PROPERTY_DATE_CREATE', /* Дата поручения. */
        'PROPERTY_DATE_ISPOLN', /* Срок исполнения. */
        'PROPERTY_DATE_FACT', /* Дата снятия с контроля - проверить нужно ли поле. */
        'PROPERTY_ACTION', /* Состояние. */
        'PROPERTY_STATUS', /* Статус. */
        'PROPERTY_TYPE', /* Тип поручения. */
        'PROPERTY_DOCUMENT', /* Документ основания - проверить нужно ли поле. */
        'PROPERTY_THEME', /* Тема поручения. */
        'PROPERTY_HISTORY_SROK', /* История изменения срока исполнения. */
        'PROPERTY_DSP', /* Для служебного пользования - проверить нужно ли поле. */
        'PROPERTY_OST_VIEW', /* Отметка о выезде - проверить нужно ли поле. */
        'PROPERTY_DOCS', /* Документы. */
        'PROPERTY_TASKS', /* Задачи. */
        'PROPERTY_PORUCH', /* Связанные поручения. */
        'PROPERTY_VIEWS', /* Отметки о просмотре. */
        'PROPERTY_POST_RESH', /* Решение Куратора. */
        'PROPERTY_CAT_THEME', /* Категория классификатора. */
        'PROPERTY_CONTROLER_RESH', /* Решение контролера. */
        'PROPERTY_CATEGORY', /* Категория поручения. */
        'PROPERTY_DATE_FACT_SNYAT', /* Дата фактического снятия с контроля. */
        'PROPERTY_DATE_FACT_ISPOLN', /* Дата отчета. */
        'PROPERTY_DOPSTATUS', /* Дополнительный статус. */
        'PROPERTY_NEWISPOLNITEL', /* Новый исполнитель. */
        'PROPERTY_OLD_PORUCH', /* Старое поручение. */
        'PROPERTY_NEW_PORUCH', /* Новое поручение. */
        'PROPERTY_DATE_ISPOLN_HIST', /* Сроки исполнения. */
        'PROPERTY_NEW_DATE_ISPOLN', /* Новый срок исполнения. */
        'PROPERTY_POSITION_TO', /* Поручения для позиции. */
        'PROPERTY_POSITION_FROM', /* Позиция по поручению. */
        'PROPERTY_CONTROLER_STATUS', /* Статус контролера. */
        'PROPERTY_DELEGATE_USER', /* Делегирование пользователю. */
        'PROPERTY_PROTOCOL_ID', /* Протокол. */
        'PROPERTY_DELEGATE_HISTORY', /* История делегирования. */
        'PROPERTY_NOT_STATS', /* Не учитывать в нарушениях. */
        'PROPERTY_CONTROL_REJECT', /* Возвраты от контролёра. */
        'PROPERTY_ACCOMPLICES', /* Соисполнители. */
        'PROPERTY_ACTION_DATE', /* Дата изменения состояния. */
        'PROPERTY_SUBEXECUTOR', /* Соисполнитель - привязка к ИБ Исполнителям. */
        'PROPERTY_SUBEXECUTOR_DATE', /* Срок для соисполнителя. */
        'PROPERTY_DELEGATION', /* Делегирование. */
        'PROPERTY_POSITION_ISPOLN', /* Передача на позицию. */
        'PROPERTY_DATE_REAL_ISPOLN', /* Дата исполнения. */
        'PROPERTY_DATE_ISPOLN_BAD', /* Сроки исполнения (не выполнено). */
        'PROPERTY_WORK_INTER_STATUS', /* Промежуточный статус. */
        'PROPERTY_REQUIRED_VISA', /* Нужна виза. */
        'PROPERTY_NEW_SUBEXECUTOR_DATE', /* Новый срок соисполнителя. */
        'PROPERTY_FIRST_EXECUTOR', /* Первый исполнитель. */
        'PROPERTY_POSITION_ISPOLN_REQS', /* Требования для позиции. */
        'PROPERTY_THESIS', /* Основная суть. */
    ];

    /**
     * Получить информацию о поручении.
     *
     * @param int $id ID поручения.
     *
     * @return array
     */
    public function getById(
        int $id = 0,
        $bFixView = true,
        array $arSelect = []
    ) {
        if (empty($arSelect)) {
            $arSelect = $this->arFields;
        }
        Loader::includeModule('iblock');
        $res = CIBlockElement::GetList(
            [
                'DATE_CREATE' => 'DESC'
            ],
            [
                'IBLOCK_ID' => Settings::$iblockId['ORDERS'],
                'ID'        => $id,
                'ACTIVE'    => 'Y',
            ],
            false,
            [
                'nPageSize' => 1
            ],
            $arSelect
        );

        if ($arFields = $res->GetNext()) {
            if (in_array('PROPERTY_SUBEXECUTOR', $arSelect)) {
                $arFields['PROPERTY_SUBEXECUTOR_IDS'] = [];
                $arFields['PROPERTY_SUBEXECUTOR_USERS'] = [];
                foreach ($arFields['PROPERTY_SUBEXECUTOR_VALUE'] as $key => $value) {
                    if (false !== mb_strpos($value, ':')) {
                        $value = explode(':', $value);
                        $ispId = $value[0];
                        $userId = $value[1];
                    } else {
                        $ispId = $value;
                        $userId = $arFields['PROPERTY_SUBEXECUTOR_DESCRIPTION'][ $key ] ?? 0;
                    }
                    $arFields['PROPERTY_SUBEXECUTOR_VALUE'][ $key ] = $ispId . ':' . $userId;
                    $arFields['PROPERTY_SUBEXECUTOR_IDS'][ $key ] = $ispId;
                    $arFields['PROPERTY_SUBEXECUTOR_USERS'][ $key ] = $userId;
                }
            }
            $curUserId = $GLOBALS['USER']->GetID();
            if ($bFixView && substr_count($arFields['PROPERTY_VIEWS_VALUE'], ',' . $curUserId . ',') == 0) {
                $arFields['PROPERTY_VIEWS_VALUE'] .= ',' . $curUserId . ',';
                CIBlockElement::SetPropertyValuesEx(
                    $arFields['ID'],
                    false,
                    [
                        'VIEWS' => $arFields['PROPERTY_VIEWS_VALUE'],
                    ]
                );
            }

            $arFields['THESIS'] = unserialize($arFields['~PROPERTY_THESIS_VALUE']);

            return $arFields;
        }

        return [];
    }

    /**
     * Получить значение свойства поручения.
     * @param int $id        ID поручения.
     * @param string $code   Код свойства.
     * @param bool $bIsArray Получить массив значений?
     *
     * @return array
     */
    public function getProperty(
        int $id = 0,
        string $code = '',
        bool $bIsArray = false
    ): array {
        $arResult = [];
        $res = CIBlockElement::GetProperty(
            Settings::$iblockId['ORDERS'],
            $id,
            'sort',
            'asc',
            [
                'CODE' => $code
            ]
        );
        if ($bIsArray) {
            while ($row = $res->GetNext()) {
                if (!empty($row['~VALUE'])) {
                    $arResult[] = $row['~VALUE'];
                }
            }
        } else {
            $arResult = $res->GetNext();
        }

        return $arResult;
    }

    /**
     * Получить список enum поручений.
     *
     * @return array
     */
    public function getEnums(): array
    {
        $arEnums = [];
        $obCache = new CPHPCache();
        if ($obCache->InitCache(86400, __METHOD__, '/citto/controlorders/')) {
            $arEnums = $obCache->GetVars();
        } elseif ($obCache->StartDataCache()) {
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
            $obCache->EndDataCache($arEnums);
        }

        return $arEnums;
    }

    /**
     * Является ли исполнителем поручения внешняя организация.
     *
     * @param int $id ID поручения
     *
     * @return boolean
     */
    public function isExternal(int $id = 0): bool
    {
        if ($id <= 0) {
            return false;
        }

        $executor = (int)$this->getProperty($id, 'ISPOLNITEL')['VALUE'];
        if ($executor > 0) {
            $arExecutors = Executors::getList();
            return $arExecutors[ $executor ]['PROPERTY_TYPE_CODE'] == 'external';
        }

        return false;
    }

    /**
     * Получить цвет поручения для списка.
     *
     * @param int   $id      ID поручения.
     * @param array $arOrder Массив с данными поручения (чтобы не генерить запросы).
     *
     * @return string
     */
    public function getColor(
        int $id = 0,
        array $arOrder = []
    ) {
        $return = 'alert-white';
        if ($id > 0 && empty($arOrder)) {
            $arSelect = [
                'PROPERTY_TYPE',
                'PROPERTY_ACTION',
                'PROPERTY_POST_RESH',
                'PROPERTY_DATE_ISPOLN',
                'PROPERTY_DATE_ISPOLN_HIST',
            ];
            $arOrder = $this->getById($id, false, $arSelect);
        }

        if ($arOrder['PROPERTY_ACTION_ENUM_ID'] == 1140) {
            /*
             * Состояние == Завершено.
             */
            $return = 'alert-success';
        } else {
            if ($arOrder['PROPERTY_POST_RESH_VALUE'] == 1203) {
                /*
                 * Решение Куратора == Дополнительный контроль.
                 */
                $return = 'alert-info';
            }

            if (!empty($arOrder['PROPERTY_DATE_ISPOLN_HIST_VALUE'])) {
                /*
                 * История изменения срока исполнения != пусто.
                 */
                $return = 'alert-info';
            }

            if (in_array('no_ispoln', $arOrder['PROPERTY_TYPE_VALUE'])) {
                $return = 'alert-danger';
            } elseif (strtotime(date('Y-m-d 00:00:00')) > strtotime($arOrder['PROPERTY_DATE_ISPOLN_VALUE'])) {
                if ($arOrder['PROPERTY_ACTION_ENUM_ID'] == 1136) {
                    $return = 'bg-orange';
                }
            }
        }

        return $return;
    }

    /**
     * Получить срок исполнения для конечного пользователя.
     *
     * @param int $orderId ID поручения.
     * @param int $userId  ID пользователя.
     *
     * @return string
     */
    public function getSrok(
        int $orderId = 0,
        int $userId = 0
    ) {
        if ($orderId <= 0) {
            return false;
        }
        if ($userId <= 0) {
            $userId = $GLOBALS['USER']->GetID();
        }
        $arSelect = [
            'DATE_CREATE',
            'PROPERTY_ACTION',
            'PROPERTY_DELEGATION',
            'PROPERTY_ISPOLNITEL',
            'PROPERTY_DATE_ISPOLN',
            'PROPERTY_DELEGATE_USER',
            'PROPERTY_DELEGATE_HISTORY',
        ];
        $arOrder = $this->getById($orderId, false, $arSelect);

        $arDelegationList = [];
        foreach ($arOrder['~PROPERTY_DELEGATE_HISTORY_VALUE'] as $delegHistory) {
            $arDelData = json_decode($delegHistory, true);
            if (isset($arDelData['DELEGATE'])) {
                $arDelegationList[] = [
                    'USER'      => $arDelData['DELEGATE'],
                    'COMMENT'   => $arDelData['COMMENT'],
                    'SROK'      => $arDelData['SROK']??false,
                ];
            } elseif (isset($arDelData['SUBEXECUTOR_USER'])) {
                $arDelegationList[] = [
                    'USER'      => $arDelData['SUBEXECUTOR_USER'],
                    'COMMENT'   => $arDelData['COMMENT'],
                    'SROK'      => $arDelData['SROK']??false,
                ];
            } elseif (isset($arDelData['SUBEXECUTOR'])) {
                if (false !== mb_strpos($arDelData['SUBEXECUTOR'], ':')) {
                    $val = explode(':', $arDelData['SUBEXECUTOR']);
                    $arDelegationList[] = [
                        'DEP'       => $val[0],
                        'COMMENT'   => $arDelData['COMMENT'],
                        'SROK'      => $arDelData['SROK']??false,
                    ];
                    $arDelegationList[] = [
                        'USER'      => $val[1],
                        'COMMENT'   => $arDelData['COMMENT'],
                        'SROK'      => $arDelData['SROK']??false,
                    ];
                } else {
                    $arDelegationList[] = [
                        'DEP'       => $arDelData['SUBEXECUTOR'],
                        'COMMENT'   => $arDelData['COMMENT'],
                        'SROK'      => $arDelData['SROK']??false,
                    ];
                }
            }
        }

        usort(
            $arDelegationList,
            static function ($a, $b) {
                return strnatcmp($a['TS'], $b['TS']);
            }
        );

        CBitrixComponent::includeComponentClass('citto:checkorders');
        $obComponent = new MainComponent();
        $arPermissions = $obComponent->getPermisions($orderId);

        $tsUser = '';
        $tsDep = '';
        foreach ($arDelegationList as $row) {
            if (isset($row['USER'])) {
                if ($row['USER'] == $userId) {
                    $tsUser = $row['SROK'] ? strtotime($row['SROK']) : false;
                }
            } elseif (isset($row['DEP'])) {
                if (in_array($row['DEP'], $arPermissions['ispolnitel_ids'])) {
                    $tsDep = $row['SROK'] ? strtotime($row['SROK']) : false;
                }
            }
        }

        $return = false;
        if (!empty($tsUser)) {
            $return = $tsUser;
        }

        if (!$return && !empty($tsDep)) {
            $return = $tsDep;
        }

        if (!$return) {
            $return = strtotime($arOrder['PROPERTY_DATE_ISPOLN_VALUE']);
        }

        return date('d.m.Y', $return);
    }
}
