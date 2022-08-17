<?

namespace Citto\Tests\Iblock;

use CIBlockSection;
use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;

/**
 * Подразделения
 */
class Departments
{
    /**
     * Неактивные пользователи в структуре
     * @responsible 54
     * @run hourly
     */
    public static function testInactiveusers()
    {
        Loader::includeModule('iblock');
        $arFilter = [
            'IBLOCK_ID' => 5,
            'ACTIVE'    => 'Y',
        ];
        $arNames = [
            'UF_HEAD'           => 'Руководитель',
            'UF_HEAD2'          => 'Зам руководителя',
            'UF_OTV_KADR'       => 'Ответственный по кадрам',
            'UF_BUHGALTER'      => 'Главный бухгалтер',
            'UF_BUHGALTER_ZAM'  => 'Заместители глав. бухгалтера',
        ];

        $arUsers = [];
        $orm = UserTable::getList([
            'select'    => ['ID', 'NAME', 'LAST_NAME'],
            'filter'    => ['ACTIVE' => 'N']
        ]);
        while ($arUser = $orm->fetch()) {
            $arUsers[ $arUser['ID'] ] = $arUser['LAST_NAME'] . ' ' . $arUser['NAME'];
        }

        $res = CIBlockSection::GetList(
            $arOrder = array('left_margin' => 'asc'),
            $arFilter,
            true,
            array(
                'ID',
                'NAME',
                'UF_HEAD', // Зам руководителя
                'UF_HEAD2', // Руководитель
                'UF_OTV_KADR', // Ответственный по кадрам
                'UF_BUHGALTER', // Главный бухгалтер
                'UF_BUHGALTER_ZAM', // Заместители глав. бухгалтера
            )
        );
        $arResult = [
            'USERS' => [],
            'DEPS'  => [],
        ];
        while ($row = $res->Fetch()) {
            $arResult['DEPS'][ $row['ID'] ] = $row['NAME'];
            foreach (array_keys($arNames) as $field) {
                if (is_array($row[ $field ])) {
                    foreach ($row[ $field ] as $value) {
                        if (array_key_exists($value, $arUsers)) {
                            $arResult['USERS'][ $row['ID'] ][ $field ] = $value;
                        }
                    }
                } else {
                    if (array_key_exists($row[ $field ], $arUsers)) {
                        $arResult['USERS'][ $row['ID'] ][ $field ] = $row[ $field ];
                    }
                }
            }
        }

        if (!empty($arResult['USERS'])) {
            $arMess = [];
            foreach ($arResult['USERS'] as $dep => $users) {
                foreach ($users as $field => $uId) {
                    $arMess[] = '<b>' . $arResult['DEPS'][ $dep ] . '</b>: (' . $arNames[ $field ] . ') неактивный пользователь [' . $uId . '] ' . $arUsers[ $uId ];
                }
            }

            return assert($send, implode('<br/>', $arMess));
        }

        return assert(true);
    }
}
