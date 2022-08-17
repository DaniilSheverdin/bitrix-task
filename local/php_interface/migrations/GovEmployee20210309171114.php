<?php

namespace Sprint\Migration;


class GovEmployee20210309171114 extends Version
{

    protected $description = "";

    public function up() {
        $helper = new HelperManager();

            $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_GOV_EMPLOYEE',
  'USER_TYPE_ID' => 'boolean',
  'XML_ID' => '',
  'SORT' => '100',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'N',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'DEFAULT_VALUE' => 0,
    'DISPLAY' => 'CHECKBOX',
    'LABEL' => 
    array (
      0 => '',
      1 => '',
    ),
    'LABEL_CHECKBOX' => '',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'ГосСлужащий',
    'ru' => 'ГосСлужащий',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'ГосСлужащий',
    'ru' => 'ГосСлужащий',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'ГосСлужащий',
    'ru' => 'ГосСлужащий',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => 'ГосСлужащий',
    'ru' => 'ГосСлужащий',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => 'ГосСлужащий',
    'ru' => 'ГосСлужащий',
  ),
));
        }

    public function down() {
        $helper = new HelperManager();

        //your code ...

    }

}
