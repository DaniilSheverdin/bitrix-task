<?php

namespace Sprint\Migration;


class ControlOrdersResolution20210415113949 extends Version
{

    protected $description = "";

    public function up() {
        $helper = new HelperManager();

                $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'HLBLOCK_ControlOrdersResolution',
  'FIELD_NAME' => 'UF_SROK',
  'USER_TYPE_ID' => 'date',
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
    'DEFAULT_VALUE' => 
    array (
      'TYPE' => 'NONE',
      'VALUE' => '',
    ),
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => '',
    'ru' => 'Срок',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => '',
    'ru' => 'Срок',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => '',
    'ru' => 'Срок',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => '',
    'ru' => 'Срок',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => '',
    'ru' => 'Срок',
  ),
));
        
            }

    public function down() {
        $helper = new HelperManager();

        //your code ...

    }

}
