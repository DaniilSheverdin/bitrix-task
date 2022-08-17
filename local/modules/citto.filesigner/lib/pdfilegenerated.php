<?

namespace Citto\Filesigner;

use Bitrix\Main\Entity;

/**
 * Хранит текстовые шаблоны документов
 * используется при генерации документов
 */
class PdfilegeneratedTable extends Entity\DataManager
{
    public static function getTableName()
    {
        return 'pdfilegenerated';
    }

    public static function add($FILE_ID)
    {
        parent::add([
            'FILE_ID' => (int)$FILE_ID
        ]);
    }

    public static function exists($FILE_ID)
    {
        $row = static::getRow([
            'select' => ['ID'],
            'filter' => [
                '=FILE_ID' => (int)$FILE_ID
            ]
        ]);
        return (bool)$row;
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
            'FILE_ID' => array(
                'data_type' => 'integer',
                'title' => "FILE_ID",
            ),
        );
    }
}
