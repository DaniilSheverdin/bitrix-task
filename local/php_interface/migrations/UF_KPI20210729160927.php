<?php

namespace Sprint\Migration;


class UF_KPI20210729160927 extends Version
{

    protected $description = "";

    public function up() {
        $helper = new HelperManager();

            $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'IBLOCK_626_SECTION',
  'FIELD_NAME' => 'UF_KPI_SECTION_WEIGHT',
  'USER_TYPE_ID' => 'string',
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
    'SIZE' => 20,
    'ROWS' => 1,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 0,
    'DEFAULT_VALUE' => '',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => '',
    'ru' => 'Вес',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => '',
    'ru' => 'Вес',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => '',
    'ru' => 'Вес',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => '',
    'ru' => 'Вес',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => '',
    'ru' => 'Вес',
  ),
));
        }

    public function down() {
        $helper = new HelperManager();

        //your code ...

    }

}
