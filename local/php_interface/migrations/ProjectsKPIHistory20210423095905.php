<?php

namespace Sprint\Migration;

class ProjectsKPIHistory20210423095905 extends Version
{

    protected $description = "";

    public function up() {
        $helper = new HelperManager();

                $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'HLBLOCK_ProjectsKPI',
  'FIELD_NAME' => 'UF_HISTORY',
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
    'ROWS' => 3,
    'REGEXP' => '',
    'MIN_LENGTH' => 0,
    'MAX_LENGTH' => 0,
    'DEFAULT_VALUE' => '',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => '',
    'ru' => 'История изменений',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => '',
    'ru' => 'История изменений',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => '',
    'ru' => 'История изменений',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => '',
    'ru' => 'История изменений',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => '',
    'ru' => 'История изменений',
  ),
));
        
            }

    public function down() {
        $helper = new HelperManager();

        //your code ...

    }

}
