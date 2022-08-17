<?

namespace Citto\Tests\Integration;

use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;

/**
 * СКУД
 */
class Scud
{
    /**
     * Нет новых записей в таблице
     * @run hourly
     */
    public static function testNoData()
    {
    	global $DB;
        $res = $DB->Query('SELECT * FROM tbl_scud WHERE UF_TOURNIQUET <> "" ORDER BY UF_ACTIVE_FROM DESC LIMIT 1');
        if ($res->SelectedRowsCount() <= 0) {
        	return assert(false, '<b>В таблице нет записей!</b>');
        }
        $row = $res->Fetch();
        if (time() - strtotime($row['UF_ACTIVE_FROM']) > 172800) {
            return assert(false, '<b>Последнее событие добавлено:</b> ' . $row['UF_DATE_CREATE']);
        }

        return assert(true);
    }
}
