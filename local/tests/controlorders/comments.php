<?

namespace Citto\Tests\ControlOrders;

use CIBlockElement;
use Bitrix\Main\Loader;
use Citto\Controlorders\Settings;
use Citto\Controlorders\Executors;

/**
 * Контроль поручений - Комментарии
 */
class Comments
{
    /**
     * Неправильный руководитель в отчете исполнителя
     */
    public static function NOtestIncorrectCurrentUser()
    {
        $arFilter = [
            'PROPERTY_TYPE'     => 1131,
            'PROPERTY_FILE_ECP' => false
        ];
        $arOrders = self::getList($arFilter);
        $arExecutors = Executors::getList();
        $arHeads = [];
        $arFields = [
            'IMPLEMENTATION',
            'ZAMESTITELI',
            'ISPOLNITELI',
        ];
        foreach ($arExecutors as $row) {
            foreach ($arFields as $field) {
                $arHeads[ $row['PROPERTY_RUKOVODITEL_VALUE'] ][ $row['PROPERTY_RUKOVODITEL_VALUE'] ] = $row['PROPERTY_RUKOVODITEL_VALUE'];
                foreach ($row['PROPERTY_' . $field . '_VALUE'] as $uId) {
                    $arHeads[ $uId ][ $row['PROPERTY_RUKOVODITEL_VALUE'] ] = $row['PROPERTY_RUKOVODITEL_VALUE'];
                }
            }
        }

        $arResult = [];
        foreach ($arOrders as $row) {
            if ($row['PROPERTY_USER_VALUE'] == $row['PROPERTY_CURRENT_USER_VALUE']) {
                continue;
            }
            if (empty($row['PROPERTY_CURRENT_USER_VALUE'])) {
                continue;
            }
            if (array_key_exists($row['PROPERTY_CURRENT_USER_VALUE'], $arHeads)) {
                continue;
            }

            if (!array_key_exists($row['PROPERTY_CURRENT_USER_VALUE'], $arHeads[ $row['PROPERTY_USER_VALUE'] ])) {
                $text = 'Указан руководитель ' . $row['PROPERTY_CURRENT_USER_VALUE'] . '. В ИБ Исполнители руководитель = ' . implode(',', $arHeads[ $row['PROPERTY_USER_VALUE'] ]);
                $arResult[] = '<li>[url=/control-orders/?detail=' . $row['PROPERTY_PORUCH_VALUE'] . ']' . 'Отчет исполнителя №' . $row['ID'] . '[/url]: ' . $text . '</li>';
            }
        }

        if (!empty($arResult)) {
            return assert(false, 'Несоответствие руководителя сотрудника и указанного в отчете:<br/><ul>' . implode('', $arResult) . '</ul>');
        }

        return assert(true);
    }

    private static function getList(array $arFilter = [], array $arSelect = [])
    {
        Loader::includeModule('iblock');
        $arSelect = array_merge(
            [
                'ID',
                'NAME',
                'PROPERTY_ECP',
                'PROPERTY_TYPE',
                'PROPERTY_VISA',
                'PROPERTY_USER',
                'PROPERTY_PORUCH',
                'PROPERTY_COMMENT',
                'PROPERTY_FILE_ECP',
                'PROPERTY_VISA_TYPE',
                'PROPERTY_CURRENT_USER',
            ],
            $arSelect
        );
        $arFilter = array_merge(
            [
                'IBLOCK_ID'     => Settings::$iblockId['ORDERS_COMMENT'],
                'ACTIVE_DATE'   => 'Y',
                'ACTIVE'        => 'Y',
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

    /**
     * Получить неглавных контролеров для переданного пользователя
     *
     * @param int $uId ID контролера
     *
     * @return array
     */
    private static function getControlers(int $uId = 0)
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
}
