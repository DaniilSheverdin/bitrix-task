<?php

namespace Sprint\Migration;


class ControlOrdersVisaType20200922105046 extends Version
{

    protected $description = "";

    public function up() {
        $helper = new HelperManager();

    
            $iblockId = $helper->Iblock()->getIblockIdIfExists('control_comment','control_poruch');
    
    
                $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Тип визирования',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'VISA_TYPE',
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
      'VALUE' => 'Одновременно',
      'DEF' => 'Y',
      'SORT' => '500',
      'XML_ID' => 'same',
    ),
    1 => 
    array (
      'VALUE' => 'По порядку',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'after',
    ),
  ),
));
        
    
    }

    public function down() {
        $helper = new HelperManager();

        //your code ...

    }

}
