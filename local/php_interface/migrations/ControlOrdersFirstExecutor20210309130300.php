<?php

namespace Sprint\Migration;


class ControlOrdersFirstExecutor20210309130300 extends Version
{

    protected $description = "";

    public function up() {
        $helper = new HelperManager();

    
            $iblockId = $helper->Iblock()->getIblockIdIfExists('porucheniya','control_poruch');
    
    
                $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Первый исполнитель',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'FIRST_EXECUTOR',
  'DEFAULT_VALUE' => '',
  'PROPERTY_TYPE' => 'E',
  'ROW_COUNT' => '1',
  'COL_COUNT' => '30',
  'LIST_TYPE' => 'L',
  'MULTIPLE' => 'N',
  'XML_ID' => NULL,
  'FILE_TYPE' => '',
  'MULTIPLE_CNT' => '5',
  'LINK_IBLOCK_ID' => 'control_poruch:organisations',
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
