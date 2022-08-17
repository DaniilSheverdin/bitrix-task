<?

namespace Citto\Tests\Integration;

use CUser;
use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;
use Citto\Integration\Delo\Users;
use Citto\Integration\Delo\BpSign;

Loader::includeModule('citto.integration');

/**
 * Дело
 */
class Delo
{
    /**
     * В HL DeloUsers у записи несколько пользователей
     * @run hourly
     */
    public static function testMultiUserEstimate()
    {
        $arDeloUsers = (new Users())->getList(false);

        $arUsers = [];
        foreach ($arDeloUsers as $row) {
            if (empty($row['UF_USER_ESTIMATE'])) {
                continue;
            }
            if (false !== mb_strpos($row['UF_USER_ESTIMATE'], ',')) {
                $arUsers[] = $row;
            }
        }

        if (!empty($arUsers)) {
            $arMess = [];
            foreach ($arUsers as $row) {
                $arMess[] = $row['ID'] . ' ' . $row['UF_NAME'] . ' несколько записей: ' . $row['UF_USER_ESTIMATE'];
            }

            return assert(false, implode("<br/>", $arMess));
        }

        return assert(true);
    }

    /**
     * Дубли пользователей в HL DeloUsers
     * @run hourly
     */
    public static function NOtestDoubleUserEstimate()
    {
        $arDeloUsers = (new Users())->getList(false);

        $arUsers = [];
        foreach ($arDeloUsers as $row) {
            if (empty($row['UF_USER_ESTIMATE'])) {
                continue;
            }
            $arEstimate = explode(',', $row['UF_USER_ESTIMATE']);
            foreach ($arEstimate as $userId) {
                $arUsers[ $userId ][] = $row['ID'];
            }
        }

        foreach ($arUsers as $userId => $rows) {
            if (count($rows) <= 1) {
                unset($arUsers[ $userId ]);
            }
        }

        if (!empty($arUsers)) {
            $arMess = [];
            foreach ($arUsers as $userId => $rows) {
                $arMess[] = 'Для пользователя ' . $userId . ' в таблице несколько записей: ' . implode(', ', $rows);
            }

            return assert(false, implode("<br/>", $arMess));
        }

        return assert(true);
    }

    /**
     * Последняя синхронизация пользователей в HL DeloUsers
     * @run hourly
     */
    public static function testNoUpdate()
    {
        $arDeloUsers = (new Users())->getList();

        $count = 0;
        foreach ($arDeloUsers as $row) {
            if (empty($row['UF_DATE_UPDATE'])) {
                continue;
            }
            if ((time() - $row['UF_DATE_UPDATE']->getTimestamp()) > 172800) {
                $count++;
            }
        }

        if ($count > 0) {
            return assert(false, $count . ' пользователей не синхронизировались более 2 дней!');
        }

        return assert(true);
    }

    /**
     * Наличие привязки пользователя в HL DeloUsers
     * @run every(2 HOURS)
     */
    public static function NOtestNoInHL()
    {
        $arDeloUsers = (new Users())->getList(false);

        $arUsers = [];
        $arEmptyFields = [
            'EXTERNAL_AUTH_ID',
            'XML_ID',
            'LAST_LOGIN',
            'WORK_POSITION',
            'UF_LAST_1C_UPD',
            'UF_DEPARTMENT',
            'UF_INN',
        ];
        $orm = UserTable::getList([
            'select'    => array_merge(['ID', 'LID', 'NAME', 'LAST_NAME'], $arEmptyFields),
            'filter'    => ['ACTIVE' => 'Y']
        ]);
        while ($arUser = $orm->fetch()) {
            $skip = false;
            if ($arUser['LID'] != 's1') {
                $skip = true;
            }
            foreach ($arEmptyFields as $key) {
                if (empty($arUser[ $key ])) {
                    $skip = true;
                }
            }
            if (!$skip) {
                $arGroups = CUser::GetUserGroup($arUser['ID']);
                // Загс
                if (in_array(67, $arGroups)) {
                    $skip = true;
                }
                // Гостехнадзор
                if (in_array(79, $arGroups)) {
                    $skip = true;
                }
            }

            if (!$skip) {
                $arUsers[ $arUser['ID'] ] = '[' . $arUser['ID'] . '] ' . $arUser['NAME'] . ' ' . $arUser['LAST_NAME'];
            }
        }

        $arResult = [];
        foreach ($arDeloUsers as $row) {
            if (empty($row['UF_USER_ESTIMATE'])) {
                continue;
            }
            $arEstimate = explode(',', $row['UF_USER_ESTIMATE']);
            foreach ($arEstimate as $userId) {
                unset($arUsers[ $userId ]);
            }
        }

        if (!empty($arUsers)) {
            return assert(false, "Нет привязки " . count($arUsers) . " пользователей:<br/>" . implode("<br/>", $arUsers));
        }

        return assert(true);
    }

    /**
     * Пустой ISN в синхронизации БП
     * @run hourly
     */
    public static function NOtestBpSignEmptyISN()
    {
        $obSign = new BpSign();
        $arFilter = [
            'UF_ISN'        => false,
            '!UF_ERRORS'    => false,
        ];
        $rsData = $obSign->entityDataClass::getList(
            [
                'filter' => $arFilter
            ]
        );
        $arResult = [];
        while ($arData = $rsData->Fetch()) {
            $arResult[] = '<li>' . $arData['ID'] . ' от ' . $arData['UF_DATE_ADD']->format('d.m.Y') . ' (' . $arData['UF_WORKFLOW_ID'] . '): ' . ($arData['UF_ERRORS'] ?? '') . '</li>';
        }
        if (!empty($arResult)) {
            return assert(false, 'В HL "Подпись БП в АСЭД" есть записи с пустым ISN:<br/><ul>' . implode('', $arResult) . '</ul>');
        }

        return assert(true);
    }
}
