<?

namespace Citto\Tests\Iblock;

use Bitrix\Main\UserTable;

/**
 * Доступы инфоблоков
 */
class Permisions
{
    /**
     * Неактивные пользователи имеют доступ
     * @responsible 54
     * @dataProvider providerTestPermUserToIblock
     * @run hourly
     */
    public static function testPermUserToIblock($entity, $name)
    {
        global $DB;

        $mess = '';

        $perms = $permsIb = [];
        $res = $DB->Query('SELECT * FROM `b_iblock_right` WHERE GROUP_CODE LIKE "%U%" AND ENTITY_TYPE LIKE "' . $entity . '"');
        while ($row = $res->Fetch()) {
            $u = str_replace(['IU', 'U'], '', $row['GROUP_CODE']);
            $perms[ $u ][] = $row['IBLOCK_ID'];
            $permsIb[ $row['IBLOCK_ID'] ][ $u ] = $u;
        }

        $arUsers = [];
        $orm = UserTable::getList([
            'select'    => ['ID', 'NAME', 'LAST_NAME'],
            'filter'    => ['ACTIVE' => 'N']
        ]);
        while ($arUser = $orm->fetch()) {
            $arUsers[ $arUser['ID'] ] = $arUser;
        }

        foreach ($perms as $k => $v) {
            if (!array_key_exists($k, $arUsers)) {
                unset($perms[ $k ]);
                foreach ($v as $ib) {
                    unset($permsIb[ $ib ][ $k ]);
                }
            }
        }

        if (!empty($permsIb)) {
            $send = true;
            asort($permsIb);
            foreach ($permsIb as $ib => $users) {
                if (empty($users)) {
                    continue;
                }
                $uText = [];
                foreach ($users as $uId) {
                    $uText[] = '[' . $uId . '] ' . $arUsers[ $uId ]['NAME'] . ' ' . $arUsers[ $uId ]['LAST_NAME'];
                }
                $ibData = $DB->Query('SELECT * FROM b_iblock WHERE ID = ' . $ib)->Fetch();
                if ($entity == 'element' && in_array($ibData['IBLOCK_TYPE_ID'], ['bitrix_processes', 'bizproc_iblockx'])) {
                    continue;
                }
                $mess .= 'К ' . $name . ' ' . $ibData['NAME'] . ' (' . $ib . ') имеет доступ неактивные пользователи: ' . implode(', ', $uText) . ".<br/>";
                $send = false;
            }

            return assert($send, $mess);
        }

        return assert(true);
    }

    /**
     * Провайдер данных для теста testPermUserToIblock
     * @return array
     */
    public function providerTestPermUserToIblock()
    {
        return [
            ['iblock', 'Инфоблокам'],
            ['element', 'Элементам инфоблоков'],
        ];
    }

    /**
     * Неактивные отделы имеют доступ
     * @responsible 54
     * @run hourly
     */
    public static function testPermDepToIblock()
    {
        global $DB;

        $mess = '';

        $perms = [];
        $res = $DB->Query('SELECT * FROM `b_iblock_right` WHERE GROUP_CODE LIKE "D%"');
        while ($row = $res->Fetch()) {
            $row['GROUP_CODE'] = str_replace(['DR', 'D'], '', $row['GROUP_CODE']);
            $perms[ $row['GROUP_CODE'] ][] = $row['IBLOCK_ID'];
        }

        $send = [];
        $allDeps = [];
        $res2 = $DB->Query('SELECT * FROM `b_iblock_section` WHERE IBLOCK_ID = 5 AND ACTIVE = "N"');
        while ($row2 = $res2->Fetch()) {
            if (isset($perms[ $row2['ID'] ])) {
                $allDeps[ $row2['ID'] ] = $row2['NAME'];
                foreach ($perms[ $row2['ID'] ] as $ib) {
                    $send[ $ib ][] = $row2['ID'];
                }
            }
        }

        if (!empty($send)) {
            foreach ($send as $ib => $deps) {
                if (empty($deps)) {
                    continue;
                }
                $uText = [];
                foreach ($deps as $dep) {
                    $uText[ $dep ] = $allDeps[ $dep ] . ' (' . $dep . ')';
                }
                $ibData = $DB->Query('SELECT * FROM b_iblock WHERE ID = ' . $ib)->Fetch();
                $mess .= 'К инфоблоку ' . $ibData['NAME'] . ' (' . $ib . ') имеет доступ неактивные отделы: ' . implode(', ', $uText) . ".<br/>";
            }

            return assert(false, $mess);
        }

        return assert(true);
    }
}
