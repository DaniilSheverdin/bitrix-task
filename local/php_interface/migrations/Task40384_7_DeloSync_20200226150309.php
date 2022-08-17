<?php

namespace Sprint\Migration;


class Task40384_7_DeloSync_20200226150309 extends Version
{

    protected $description = "";

    public function up() {
        $helper = new HelperManager();

    
            $iblockId = $helper->Iblock()->getIblockIdIfExists('control-protocols','control_poruch');
    
    
                $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Дело - ISN',
  'ACTIVE' => 'Y',
  'SORT' => '1000',
  'CODE' => 'DELO_ISN',
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
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Дело - статус',
  'ACTIVE' => 'Y',
  'SORT' => '1100',
  'CODE' => 'DELO_STATUS',
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
      'VALUE' => 'Не синхронизирован',
      'DEF' => 'Y',
      'SORT' => '1',
      'XML_ID' => '-1',
    ),
    1 => 
    array (
      'VALUE' => 'Создан',
      'DEF' => 'N',
      'SORT' => '100',
      'XML_ID' => '1',
    ),
    2 => 
    array (
      'VALUE' => 'На визировании',
      'DEF' => 'N',
      'SORT' => '200',
      'XML_ID' => '2',
    ),
    3 => 
    array (
      'VALUE' => 'Завизирован',
      'DEF' => 'N',
      'SORT' => '300',
      'XML_ID' => '3',
    ),
    4 => 
    array (
      'VALUE' => 'На подписи',
      'DEF' => 'N',
      'SORT' => '400',
      'XML_ID' => '4',
    ),
    5 => 
    array (
      'VALUE' => 'Подписан',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '5',
    ),
    6 => 
    array (
      'VALUE' => 'Не подписан',
      'DEF' => 'N',
      'SORT' => '600',
      'XML_ID' => '6',
    ),
    7 => 
    array (
      'VALUE' => 'На регистрации без удаления РК',
      'DEF' => 'N',
      'SORT' => '700',
      'XML_ID' => '7',
    ),
    8 => 
    array (
      'VALUE' => 'На регистрации с удалением РК',
      'DEF' => 'N',
      'SORT' => '800',
      'XML_ID' => '8',
    ),
    9 => 
    array (
      'VALUE' => 'Зарегистрирован',
      'DEF' => 'N',
      'SORT' => '900',
      'XML_ID' => '9',
    ),
  ),
));
        
    
    }

    public function down() {
        $helper = new HelperManager();

        //your code ...

    }

}
