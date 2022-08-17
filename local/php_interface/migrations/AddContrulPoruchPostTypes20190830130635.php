<?php

namespace Sprint\Migration;


class AddContrulPoruchPostTypes20190830130635 extends Version
{

    protected $description = "";

    public function up() {
        $helper = new HelperManager();

    
            $iblockId = $helper->Iblock()->getIblockIdIfExists('porucheniya','control_poruch');
    
    
                $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Решение Куратора',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'POST_RESH',
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
      'VALUE' => 'Дополнительный контроль',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'b80b15ba1b987218e4fa3c3dbf76c6d9',
    ),
    1 => 
    array (
      'VALUE' => 'Снять с контроля',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'bcba9a9ed52d593c9e95b5c96182ad9e',
    ),
  ),
));
        
    
    }

    public function down() {
        $helper = new HelperManager();

        //your code ...

    }

}