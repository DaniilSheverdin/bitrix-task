<?

namespace Citto\Tests\ControlOrders;

use CIBlockElement;
use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;
use Citto\Controlorders\Settings;

/**
 * Контроль поручений - Поручения
 */
class Orders
{
    /**
     * Пустые поля в поручениях
     * @run hourly
     */
    public static function testEmptyFields()
    {
        $arFields = [
            'POST',
            // 'ISPOLNITEL',
            'CONTROLER',
            'DATE_CREATE',
            'DATE_ISPOLN',
            'ACTION',
            'STATUS',
        ];
        $arSelect = [
            'ID', 'NAME',
        ];
        foreach ($arFields as $field) {
            $arSelect[] = 'PROPERTY_' . $field;
        }
        $arFilter = [
            '!PROPERTY_ACTION' => Settings::$arActions['DRAFT']
        ];
        $arOrders = self::getOrders($arFilter, $arSelect);
        foreach ($arOrders as $row) {
            foreach ($arFields as $field) {
                if (empty($row['PROPERTY_' . $field . '_VALUE'])) {
                    $arResult[ $row['ID'] ][] = $field;
                }
            }
        }

        $arMess = [];
        foreach ($arResult as $id => $fields) {
            $arMess[] = 'В поручении <b>' . $id . '</b> не заполнены обязательные поля: ' . implode(', ', $fields);
        }

        if (!empty($arMess)) {
            return assert(false, implode('<br/>', $arMess));
        }

        return assert(true);
    }

    /**
     * Не указан ISN из Дело
     */
    public static function NOtestIncorrectIsn()
    {
        $arOrders = self::getOrders();
        $arResult = [];
        foreach ($arOrders as $row) {
            if (count($arResult) >= 10) {
                break;
            }
            if ($row['XML_ID'] == $row['ID']) {
                $arResult[] = '<li>[url=/control-orders/?detail=' . $row['ID'] . ']' . $row['NAME'] . '[/url]</li>';
            }
        }

        if (!empty($arResult)) {
            return assert(false, 'В следующих поручениях не указан ISN: <br/>Первые 10<ul>' . implode('', $arResult) . '</ul>');
        }

        return assert(true);
    }

    /**
     * Ждет решения без решения контролера
     * @run hourly
     */
    public static function testIncorrectReady()
    {
        $arFilter = [
            'PROPERTY_ACTION'       => Settings::$arActions['READY'],
            'PROPERTY_POSITION_TO'  => false,
        ];
        $arSelect = [
            'PROPERTY_CONTROLER_RESH'
        ];
        $arOrders = self::getOrders($arFilter, $arSelect);
        $arResult = [];
        foreach ($arOrders as $row) {
            if (empty($row['PROPERTY_CONTROLER_RESH_VALUE'])) {
                $arResult[] = '<li>[url=/control-orders/?detail=' . $row['ID'] . ']' . $row['NAME'] . '[/url]</li>';
            }
        }

        if (!empty($arResult)) {
            return assert(false, '<b>Статус:</b> Ждет решения<br/><b>Решение контролера:</b> пусто<br/><ul>' . implode('', $arResult) . '</ul>');
        }

        return assert(true);
    }

    /**
     * Неактивные пользователи в поручениях
     * Вынесено в уведомление для ответственных исполнителей.
     */
    public static function NOtestInactiveUsers()
    {
        $arFields = [
            'POST',
            'CONTROLER',
            'DELEGATE_USER',
            'ACCOMPLICES',
        ];
        $arSelect = [
            'ID', 'NAME',
        ];
        foreach ($arFields as $field) {
            $arSelect[] = 'PROPERTY_' . $field;
        }
        $arUsers = [];
        $orm = UserTable::getList([
            'select'    => ['ID'],
            'filter'    => ['ACTIVE' => 'Y']
        ]);
        while ($arUser = $orm->fetch()) {
            $arUsers[ $arUser['ID'] ] = $arUser['ID'];
        }
        $arFilter = [
            '!PROPERTY_ACTION' => [
                Settings::$arActions['DRAFT'],
                Settings::$arActions['ARCHIVE'],
            ]
        ];
        $arOrders = self::getOrders($arFilter, $arSelect);
        foreach ($arOrders as $row) {
            foreach ($arFields as $field) {
                if (empty($row['PROPERTY_' . $field . '_VALUE'])) {
                    continue;
                }
                if (is_array($row['PROPERTY_' . $field . '_VALUE'])) {
                    foreach ($row['PROPERTY_' . $field . '_VALUE'] as $uId) {
                        if ($uId > 0 && !array_key_exists($uId, $arUsers)) {
                            $arResult[ $row['ID'] ][ $field ][] = $uId;
                        }
                    }
                } else {
                    if (!array_key_exists($row['PROPERTY_' . $field . '_VALUE'], $arUsers)) {
                        $arResult[ $row['ID'] ][ $field ][] = $row['PROPERTY_' . $field . '_VALUE'];
                    }
                }
            }
        }

        $arMess = [];
        foreach ($arResult as $id => $fields) {
            $arValue = [];
            foreach ($fields as $name => $value) {
                $arValue[] = $name . ' (' . implode(', ', $value) . ')';
            }
            $arMess[] = 'В поручении <b>' . $id . '</b> есть неактивные пользователи: ' . implode(', ', $arValue);
        }

        if (!empty($arMess)) {
            return assert(false, implode('<br/>', $arMess));
        }

        return assert(true);
    }

    /**
     * Удаленные пользователи в поручениях
     */
    public static function testDeletedUsers()
    {
        $arFields = [
            'POST',
            'CONTROLER',
            'DELEGATE_USER',
            'ACCOMPLICES',
        ];
        $arSelect = [
            'ID', 'NAME',
        ];
        foreach ($arFields as $field) {
            $arSelect[] = 'PROPERTY_' . $field;
        }
        $arUsers = [];
        $orm = UserTable::getList([
            'select'    => ['ID'],
        ]);
        while ($arUser = $orm->fetch()) {
            $arUsers[ $arUser['ID'] ] = $arUser['ID'];
        }
        $arFilter = [
            '!PROPERTY_ACTION' => [
                Settings::$arActions['DRAFT'],
                Settings::$arActions['ARCHIVE'],
            ]
        ];
        $arOrders = self::getOrders($arFilter, $arSelect);
        foreach ($arOrders as $row) {
            foreach ($arFields as $field) {
                if (empty($row['PROPERTY_' . $field . '_VALUE'])) {
                    continue;
                }
                if (is_array($row['PROPERTY_' . $field . '_VALUE'])) {
                    foreach ($row['PROPERTY_' . $field . '_VALUE'] as $uId) {
                        if ($uId > 0 && !array_key_exists($uId, $arUsers)) {
                            $arResult[ $row['ID'] ][ $field ][] = $uId;
                        }
                    }
                } else {
                    if (!array_key_exists($row['PROPERTY_' . $field . '_VALUE'], $arUsers)) {
                        $arResult[ $row['ID'] ][ $field ][] = $row['PROPERTY_' . $field . '_VALUE'];
                    }
                }
            }
        }

        $arMess = [];
        foreach ($arResult as $id => $fields) {
            $arValue = [];
            foreach ($fields as $name => $value) {
                $arValue[] = $name . ' (' . implode(', ', $value) . ')';
            }
            $arMess[] = 'В поручении <b>' . $id . '</b> есть удаленные пользователи: ' . implode(', ', $arValue);
        }

        if (!empty($arMess)) {
            return assert(false, implode('<br/>', $arMess));
        }

        return assert(true);
    }

    private static function getOrders(array $arFilter = [], array $arSelect = [])
    {
        Loader::includeModule('iblock');
        $arSelect = array_merge(
            [
                'ID', 'XML_ID', 'NAME'
            ],
            $arSelect
        );
        $arFilter = array_merge(
            [
                'IBLOCK_ID'             => Settings::$iblockId['ORDERS'],
                'ACTIVE_DATE'           => 'Y',
                'ACTIVE'                => 'Y',
                '!PROPERTY_ISPOLNITEL'  => 7770
            ],
            $arFilter
        );
        $arResult = [];
        $res = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);
        while ($row = $res->GetNext()) {
            $arResult[] = $row;
        }

        return $arResult;
    }
}
