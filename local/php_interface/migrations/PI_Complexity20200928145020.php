<?php

namespace Sprint\Migration;


class PI_Complexity20200928145020 extends Version
{

    protected $description = "";

    public function up() {
        $helper = new HelperManager();

            $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'SONET_GROUP',
  'FIELD_NAME' => 'UF_COMPLEXITY',
  'USER_TYPE_ID' => 'enumeration',
  'XML_ID' => '',
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'DISPLAY' => 'LIST',
    'LIST_HEIGHT' => 1,
    'CAPTION_NO_VALUE' => '',
    'SHOW_NO_VALUE' => 'Y',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => '',
    'ru' => 'Сложность проекта',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => '',
    'ru' => 'Сложность проекта',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => '',
    'ru' => 'Сложность проекта',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => '',
    'ru' => '',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => '',
    'ru' => '',
  ),
  'ENUM_VALUES' => 
  array (
    0 => 
    array (
      'VALUE' => 'A (Сложный)',
      'DEF' => 'N',
      'SORT' => '100',
      'XML_ID' => 'A',
    ),
    1 => 
    array (
      'VALUE' => 'B (Средний)',
      'DEF' => 'N',
      'SORT' => '200',
      'XML_ID' => 'B',
    ),
    2 => 
    array (
      'VALUE' => 'C (Легкий)',
      'DEF' => 'N',
      'SORT' => '300',
      'XML_ID' => 'C',
    ),
    3 => 
    array (
      'VALUE' => 'D (Фоновый)',
      'DEF' => 'N',
      'SORT' => '400',
      'XML_ID' => 'D',
    ),
  ),
));
        }

    public function down() {
        $helper = new HelperManager();

        //your code ...

    }

}
