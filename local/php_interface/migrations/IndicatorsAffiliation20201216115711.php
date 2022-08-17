<?php

namespace Sprint\Migration;


class IndicatorsAffiliation20201216115711 extends Version
{

    protected $description = "";

    public function up() {
        $helper = new HelperManager();

    
            $iblockId = $helper->Iblock()->getIblockIdIfExists('indicators_catalog','pokazateli');
    
    
                $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Принадлежность',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'AFFILIATION',
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
      'VALUE' => 'Бюджет',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '9054cb3a7d031797e1011bdd05cfae94',
    ),
    1 => 
    array (
      'VALUE' => 'Мебель, бытовая техника',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '54551910b2d005ce79e3e82b6efdb402',
    ),
    2 => 
    array (
      'VALUE' => 'Мероприятия',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '50b6fe398198fa00ec373a01d534f10e',
    ),
    3 => 
    array (
      'VALUE' => 'Оборудование',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '2608a7efb6477921f7a87db1f818a5c7',
    ),
    4 => 
    array (
      'VALUE' => 'Проекты',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '957acf07b81809ec4987b3ac96a9ecc5',
    ),
    5 => 
    array (
      'VALUE' => 'Сервисы',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '16e89684086cdaf13c3c208f4005af33',
    ),
  ),
));
            $helper->Iblock()->saveProperty($iblockId, array (
  'NAME' => 'Тематика (Стат данные)',
  'ACTIVE' => 'Y',
  'SORT' => '500',
  'CODE' => 'THEME_STAT',
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
      'VALUE' => 'Инфраструктура',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'dd76e05483e15b941e7147cae98eba93',
    ),
    1 => 
    array (
      'VALUE' => 'Проектное управление',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '3b189af55652393b4c626804b0dd1622',
    ),
    2 => 
    array (
      'VALUE' => 'Склад',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => '2b13a071d950c95a23088693bf099ac0',
    ),
    3 => 
    array (
      'VALUE' => 'Социальное развитие',
      'DEF' => 'N',
      'SORT' => '500',
      'XML_ID' => 'a47ac1a278e0d912f2de561ac097a261',
    ),
  ),
));
        
    
    }

    public function down() {
        $helper = new HelperManager();

        //your code ...

    }

}
