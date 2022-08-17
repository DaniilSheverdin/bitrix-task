<?

namespace Citto\Filesigner;

use Bitrix\Main\Entity;

/**
 * Хранит текстовые шаблоны документов
 * используется при генерации документов
 */
class ShablonyTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'shablony_dokumentov';
    }

    public static function getScalar($params)
    {
        $row = static::getRow($params);
        return current($row?:[]);
    }

    public static function getMap()
    {
        return array(
            'ID' => array(
                'data_type' => 'integer',
                'primary' => true,
                'autocomplete' => true,
                'title' => "ID",
            ),
            'CODE' => array(
                'data_type' => 'string',
                'title' => "CODE",
            ),
            'SHABLON' => array(
                'data_type' => 'string',
                'title' => "SHABLON",
            ),
        );
    }
}
