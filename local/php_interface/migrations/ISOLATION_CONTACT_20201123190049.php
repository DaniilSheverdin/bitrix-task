<?php

namespace Sprint\Migration;


class ISOLATION_CONTACT_20201123190049 extends Version
{

    protected $description = "Новое свойство -  Статус изоляции";

    public function up() {
        $helper = new HelperManager();

    
            $iblockId = $helper->Iblock()->getIblockIdIfExists('docs_migration','docs');
    
    
                $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Статус изоляции',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'ATT_ISOLATION_STATUS',
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
      'VALUE' => 'необходим больничный лист в связи с изоляцией',
      'DEF' => 'N',
      'SORT' => '10',
      'XML_ID' => '5396f0d0e109746fdbc459e793e2db2a',
    ),
    1 => 
    array (
      'VALUE' => 'отпуск',
      'DEF' => 'N',
      'SORT' => '20',
      'XML_ID' => '0bcf454213d6f57ec84ba5f0a3d44765',
    ),
    2 => 
    array (
      'VALUE' => 'удаленная работа',
      'DEF' => 'N',
      'SORT' => '30',
      'XML_ID' => 'b1d1fd859725ffe4c6b65ebe23062703',
    ),
    3 => 
    array (
      'VALUE' => 'дистанционное обучение',
      'DEF' => 'N',
      'SORT' => '40',
      'XML_ID' => '7a7dfa16966889cffd49b9e9b9c0e0bd',
    ),
    4 => 
    array (
      'VALUE' => 'др.',
      'DEF' => 'N',
      'SORT' => '50',
      'XML_ID' => '6bcb09587517af34ba6db9851da8d773',
    ),
  ),
));
        
    
    }

    public function down() {
        $helper = new HelperManager();

        //your code ...

    }

}
