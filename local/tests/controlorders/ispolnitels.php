<?

namespace Citto\Tests\ControlOrders;

use CIBlockElement;
use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;
use Citto\Controlorders\Settings;
use Citto\Controlorders\Executors;

/**
 * Контроль поручений - Исполнители
 */
class Ispolnitels
{
    /**
     * Пользователь привязан к нескольким исполнителям
     * Тест неактуален, можно находиться в нескольких исполнителях
     * Внутри поручения доступы проверяются индивидуально
     */
    public static function NOtestMultiUser()
    {
        $arExecutors = Executors::getList();
        foreach ($arExecutors as $arFields) {
            $arResult[ $arFields['PROPERTY_RUKOVODITEL_VALUE'] ][ $arFields['ID'] ][] = $arFields['ID'] . ' (Руководитель)';
            foreach ($arFields['PROPERTY_ZAMESTITELI_VALUE'] as $uId) {
                $arResult[ $uId ][ $arFields['ID'] ][] = $arFields['ID'] . ' (Заместитель)';
            }
            foreach ($arFields['PROPERTY_ISPOLNITELI_VALUE'] as $uId) {
                $arResult[ $uId ][ $arFields['ID'] ][] = $arFields['ID'] . ' (Исполнитель)';
            }
            foreach ($arFields['PROPERTY_IMPLEMENTATION_VALUE'] as $uId) {
                unset($arResult[ $uId ][ $arFields['ID'] ]);
                $arResult[ $uId ]['IMPLEMENTATION'][ $arFields['ID'] ] = $arFields['ID'] . ' (Внедренец)';
            }
        }

        $arMess = [];
        foreach ($arResult as $uId => $arIds) {
            if (count($arIds) <= 1) {
                unset($arResult[ $uId ]);
            } else {
                $arTexts = [];
                foreach ($arIds as $rows) {
                    $arTexts = array_merge($arTexts, $rows);
                }
                $arMess[] = 'Пользователь <b>' . $uId . '</b> привязан к исполнителям: ' . implode(', ', $arTexts);
            }
        }

        if (!empty($arMess)) {
            return assert(false, implode('<br/>', $arMess));
        }

        return assert(true);
    }

    /**
     * Неактивный пользователь
     * @run hourly
     * @auditor 1927
     */
    public static function testInactiveUser()
    {
        $arExecutors = Executors::getList();
        foreach ($arExecutors as $arFields) {
            $arResult[ (int)$arFields['PROPERTY_RUKOVODITEL_VALUE'] ][] = $arFields['ID'] . ' (Руководитель)';
            foreach ($arFields['PROPERTY_ZAMESTITELI_VALUE'] as $uId) {
                $arResult[ (int)$uId ][] = $arFields['ID'] . ' (Заместитель)';
            }
            foreach ($arFields['PROPERTY_ISPOLNITELI_VALUE'] as $uId) {
                $arResult[ (int)$uId ][] = $arFields['ID'] . ' (Исполнитель)';
            }
            foreach ($arFields['PROPERTY_IMPLEMENTATION_VALUE'] as $uId) {
                $arResult[ (int)$uId ][] = $arFields['ID'] . ' (Ответственный исполнитель)';
            }
        }

        ksort($arResult);
        unset($arResult[0]);

        $arUsers = [];
        $arInactiveUsers = [];
        $orm = UserTable::getList([
            'select'    => ['ID', 'LAST_LOGIN', 'ACTIVE'],
        ]);
        while ($arUser = $orm->fetch()) {
            if ($arUser['ACTIVE'] == 'Y') {
                $arUsers[ $arUser['ID'] ] = $arUser['LAST_LOGIN'];
            } else {
                $arInactiveUsers[ $arUser['ID'] ] = $arUser['LAST_LOGIN'];
            }
        }
        $arMess = [];
        foreach ($arResult as $uId => $arRows) {
            if (array_key_exists($uId, $arUsers)) {
                continue;
            } else {
                $arMess[] = 'Неактивный пользователь <b>' . $uId . ' (Последний вход ' . $arInactiveUsers[ $uId ] . ')</b> привязан к исполнителям: ' . implode(', ', $arRows);
            }
        }

        if (!empty($arMess)) {
            return assert(false, implode('<br/>', $arMess));
        }

        return assert(true);
    }
}
