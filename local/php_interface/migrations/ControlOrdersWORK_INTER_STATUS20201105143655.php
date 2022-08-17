<?php

namespace Sprint\Migration;


class ControlOrdersWORK_INTER_STATUS20201105143655 extends Version
{

    protected $description = "";

    public function up() {
        $helper = new HelperManager();

    
            $iblockId = $helper->Iblock()->getIblockIdIfExists('porucheniya','control_poruch');
    
    
                $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Промежуточный статус',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'WORK_INTER_STATUS',
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
      'VALUE' => 'На визировании',
      'DEF' => 'N',
      'SORT' => '100',
      'XML_ID' => 'TOVISA',
    ),
    1 => 
    array (
      'VALUE' => 'На подписи',
      'DEF' => 'N',
      'SORT' => '200',
      'XML_ID' => 'TOSIGN',
    ),
  ),
));
        
    
    }

    public function down() {
        $helper = new HelperManager();

        //your code ...

    }

}
