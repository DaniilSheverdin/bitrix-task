<?php

namespace Sprint\Migration;


class AddControlVoteAnswer20200302135025 extends Version
{

    protected $description = "";

    public function up() {
        $helper = new HelperManager();

    
            $iblockId = $helper->Iblock()->getIblockIdIfExists('control_comment','control_poruch');
    
    
                $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Дата опроса',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'DATE_VOTE',
  'DEFAULT_VALUE' => NULL,
  'PROPERTY_TYPE' => 'S',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => '0',
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '2',
  'USER_TYPE' => 'Date',
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => '',
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Результат опроса',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'RESULT_VOTE',
  'DEFAULT_VALUE' => '',
  'PROPERTY_TYPE' => 'L',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => '0',
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '2',
  'USER_TYPE' => NULL,
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => '',
  'VALUES' => 
  array (
    0 => 
    array (
      'VALUE' => 'Затрудняюсь ответить',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'de4084e91746019f87a86e4b6778c805',
    ),
    1 => 
    array (
      'VALUE' => 'Не удовлетворен',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'fc6d0a69b0ccd6972212809d31003af7',
    ),
    2 => 
    array (
      'VALUE' => 'Нет связи с заявителем',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '99a94429ba931317254b69faa082899e',
    ),
    3 => 
    array (
      'VALUE' => 'Удовлетворен',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '73d51e315281b03f943b4c8a2acf3bec',
    ),
    4 => 
    array (
      'VALUE' => 'Удовлетворен частично',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '1dad6f8694236c217695ec0a42bd18b6',
    ),
  ),
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Файл для подписи',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'FILE_ECP',
  'DEFAULT_VALUE' => '',
  'PROPERTY_TYPE' => 'F',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => '0',
  'WITH_DESCRIPTION' => 'N',
  'SEARCHABLE' => 'N',
  'FILTRABLE' => 'N',
  'IS_REQUIRED' => 'N',
  'VERSION' => '2',
  'USER_TYPE' => NULL,
  'USER_TYPE_SETTINGS' => NULL,
  'HINT' => '',
));
        
    
    }

    public function down() {
        $helper = new HelperManager();

        //your code ...

    }

}
