<?php

namespace Sprint\Migration;


class ISOLATION_CONTACT_20201230130606 extends Version
{

    protected $description = "Добавление нового свойства notify_way_enum";

    public function up() {
        $helper = new HelperManager();

    
            $iblockId = $helper->Iblock()->getIblockIdIfExists('docs_migration','docs');
    
    
                $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Способ оповещения',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'ATT_NOTIFY_WAY_ENUM',
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
      'VALUE' => 'WhatsApp',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '1ddb5312dcec8b8b690fb810651da9ae',
    ),
    1 => 
    array (
      'VALUE' => 'почта',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '0c12a613a9082f25a0065d4c069ac269',
    ),
    2 => 
    array (
      'VALUE' => 'СМС',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '5e15e455edab04d9d59f768b00e71460',
    ),
    3 => 
    array (
      'VALUE' => 'телефонограмма',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'e20c60ef63fe44f55e3e5d63e0d359c7',
    ),
    4 => 
    array (
      'VALUE' => 'эл. почта',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '428dff7b14249e88f9eaeb2af3156330',
    ),
  ),
));
        
    
    }

    public function down() {
        $helper = new HelperManager();

        //your code ...

    }

}
