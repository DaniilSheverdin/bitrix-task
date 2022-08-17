<?

namespace Citto\Tests\Integration;

use Exception;
use Bitrix\Main\Loader;
use Citto\Integration\Itilium as ItiliumObj;

Loader::includeModule('citto.integration');

/**
 * Itilium
 */
class Itilium
{
    /**
     * Версия API отличается от реализованного взаимодействия
     * @run daily
     */
    public static function testApiVersion()
    {
        try {
            $obClient = new ItiliumObj();
            $current = $obClient->APIVersion;
            $actual  = $obClient->getAPIVersion();

            if ($current != $actual) {
                return assert(false, 'Версия API = ' . $actual . '<br/>Версия используемого API = ' . $current);
            }
        } catch (Exception $e) {
            return assert(true);
        }

        return assert(true);
    }
}
