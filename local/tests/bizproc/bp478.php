<?

namespace Citto\Tests\Bizproc;

use Bitrix\Main\UserTable;

/**
 * БП Заявление на отпуск
 */
class Bp478
{
    /**
     * Проверка наличия файла в заявлении
     * @run hourly
     */
    public static function NOtestEmptyFile()
    {
        global $DB;

        $mess = '';

        $perms = $permsIb = [];
        $res = $DB->Query('SELECT * FROM b_bp_task WHERE activity_name = "A22314_20157_78074_99224" AND STATUS = 0 AND DESCRIPTION NOT LIKE "%[url%" AND ID > 70000 ORDER BY ID DESC');
        if ($res->SelectedRowsCount() > 0) {
            return assert($send, $res->SelectedRowsCount() . ' процессов без файла');
        }

        return assert(true);
    }
}
