<?

namespace Citto\Tests\Integration;

use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;

/**
 * Почта
 */
class Mail
{
    /**
     * Ошибки получения почты
     * @run hourly
     */
    public static function testErrorFetchingMails()
    {
    	global $DB;
        $res = $DB->Query('SELECT * FROM b_mail_log WHERE STATUS_GOOD = "N" AND MESSAGE <> "<" ORDER BY ID DESC');
        $cnt = $res->SelectedRowsCount();

        if ($cnt > 0) {
            $row = $res->Fetch();

            $mess = '<b>Зафиксировано ошибок получения почты:</b> ' . $cnt . '<br/>';
            $mess .= '<b>Последнее сообщение:</b> ' . $row['MESSAGE'] . '<br/>';
            $mess .= '<b>Дата:</b> ' . $row['DATE_INSERT'] . '<br/>';

            return assert(false, $mess);
        }

        return assert(true);
    }
}
