<?php

namespace Sprint\Migration;


class TaskProjectInitiativeUF_JUSTIFICATION20210923114600 extends Version
{

    protected $description = "";

    public function up() {
        $helper = new HelperManager();

            $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'TASKS_TASK',
  'FIELD_NAME' => 'UF_JUSTIFICATION',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => '',
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'N',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'SIZE' => 45,
    'ROWS' => 4,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 0,
    'DEFAULT_VALUE' => '',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => '',
    'ru' => 'Обоснование',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => '',
    'ru' => 'Обоснование',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => '',
    'ru' => 'Обоснование',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => '',
    'ru' => 'Обоснование',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => '',
    'ru' => 'Обоснование',
  ),
));
        }

    public function down() {
        $helper = new HelperManager();

        //your code ...

    }

}
