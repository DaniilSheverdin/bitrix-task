<?php

namespace Sprint\Migration;


class IndicatorsShortName20210428191630 extends Version
{

    protected $description = "";

    public function up() {
        $helper = new HelperManager();

    
            $iblockId = $helper->Iblock()->getIblockIdIfExists('indicators_catalog','pokazateli');
    
    
                $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Короткое название',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'SHORT_NAME',
  'DEFAULT_VALUE' => '',
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
