<?php

namespace Sprint\Migration;


class KPI_20210729161121 extends Version
{

    protected $description = "";

    public function up() {
        $helper = new HelperManager();

    
            $iblockId = $helper->Iblock()->getIblockIdIfExists('kpi','kpi');
    
    
                $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Источник данных',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'ATT_DATA_SOURCE',
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
      'VALUE' => 'Ручной ввод',
      'DEF' => 'N',
      'SORT' => '10',
      'XML_ID' => 'ee7b026df1f43e2cf57fd2304cb1ef17',
    ),
    1 => 
    array (
      'VALUE' => 'АСЭД Дело - поручения',
      'DEF' => 'N',
      'SORT' => '20',
      'XML_ID' => '1bca09eb315d2d67470679fd4fd323c9',
    ),
    2 => 
    array (
      'VALUE' => 'Интегральный показатель',
      'DEF' => 'N',
      'SORT' => '30',
      'XML_ID' => '876d93f357fe1241ff53f8f1af79c257',
    ),
    3 => 
    array (
      'VALUE' => 'Поручения на КП',
      'DEF' => 'N',
      'SORT' => '40',
      'XML_ID' => 'd263995adbeaa8ec2daae04f1fb0382a',
    ),
  ),
));
        
    
    }

    public function down() {
        $helper = new HelperManager();

        //your code ...

    }

}
