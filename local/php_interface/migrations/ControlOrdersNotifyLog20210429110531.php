<?php

namespace Sprint\Migration;


class ControlOrdersNotifyLog20210429110531 extends Version
{

    protected $description = "";

    public function up() {
        $helper = new HelperManager();

                $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'HLBLOCK_ControlOrdersNotifyLog',
  'FIELD_NAME' => 'UF_ORDER_ID',
  'USER_TYPE_ID' => 'string',
  'XML_ID' => '',
  'SORT' => '1',
  'MULTIPLE' => 'N',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'I',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'N',
  'IS_SEARCHABLE' => 'Y',
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
    'ru' => 'ID поручения',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => '',
    'ru' => 'ID поручения',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => '',
    'ru' => 'ID поручения',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => '',
    'ru' => 'ID поручения',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => '',
    'ru' => 'ID поручения',
  ),
));
            }

    public function down() {
        $helper = new HelperManager();

        //your code ...

    }

}
