<?php

namespace Sprint\Migration;


class ControlPoruchDopStatusesNew20200211134400 extends Version
{

    protected $description = "";

    public function up() {
        $helper = new HelperManager();

    
            $iblockId = $helper->Iblock()->getIblockIdIfExists('porucheniya','control_poruch');
    
    
                $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Дополнительный статус',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'DOPSTATUS',
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
      'VALUE' => 'На позицию',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'to_position',
    ),
    1 => 
    array (
      'VALUE' => 'Передача на исполнение',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'change_ispoln',
    ),
    2 => 
    array (
      'VALUE' => 'Перенос срока',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'change_srok',
    ),
  ),
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Статус контролера',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'CONTROLER_STATUS',
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
      'VALUE' => 'Ждут подтверждения',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'on_accepting',
    ),
    1 => 
    array (
      'VALUE' => 'На позиции',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'on_position',
    ),
    2 => 
    array (
      'VALUE' => 'Не обработаны',
      'DEF' => 'Y',
      'SORT' => '500',
      'XML_ID' => 'on_beforing',
    ),
  ),
));
        
    
    }

    public function down() {
        $helper = new HelperManager();

        //your code ...

    }

}
