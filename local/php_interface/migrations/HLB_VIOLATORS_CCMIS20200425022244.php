<?php

namespace Sprint\Migration;


class HLB_VIOLATORS_CCMIS20200425022244 extends Version
{

    protected $description = "Дополнение для таблицы нарушителей";

    public function up() {
        $helper = new HelperManager();

        
        $helper->Hlblock()->saveHlblock(array (
  'NAME' => 'Ccmis',
  'TABLE_NAME' => 'ccmis',
  'LANG' => 
  array (
    'ru' => 
    array (
      'NAME' => 'Нарушители КЦ/МИС',
    ),
    'en' => 
    array (
      'NAME' => 'Нарушители КЦ/МИС',
    ),
  ),
));

                $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'HLBLOCK_Ccmis',
  'FIELD_NAME' => 'UF_FIO',
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
    'en' => 'ФИО',
    'ru' => 'ФИО',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'ФИО',
    'ru' => 'ФИО',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'ФИО',
    'ru' => 'ФИО',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => 'ФИО',
    'ru' => 'ФИО',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => 'ФИО',
    'ru' => 'ФИО',
  ),
));
                $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'HLBLOCK_Ccmis',
  'FIELD_NAME' => 'UF_DATE_BIRTHDAY',
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
    'en' => 'Дата рождения',
    'ru' => 'Дата рождения',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Дата рождения',
    'ru' => 'Дата рождения',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Дата рождения',
    'ru' => 'Дата рождения',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => 'Дата рождения',
    'ru' => 'Дата рождения',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => 'Дата рождения',
    'ru' => 'Дата рождения',
  ),
));
                $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'HLBLOCK_Ccmis',
  'FIELD_NAME' => 'UF_ADDRESS',
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
    'en' => 'Адрес',
    'ru' => 'Адрес',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Адрес',
    'ru' => 'Адрес',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Адрес',
    'ru' => 'Адрес',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => 'Адрес',
    'ru' => 'Адрес',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => 'Адрес',
    'ru' => 'Адрес',
  ),
));
                $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'HLBLOCK_Ccmis',
  'FIELD_NAME' => 'UF_PHONE',
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
    'en' => 'Телефон',
    'ru' => 'Телефон',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Телефон',
    'ru' => 'Телефон',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Телефон',
    'ru' => 'Телефон',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => 'Телефон',
    'ru' => 'Телефон',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => 'Телефон',
    'ru' => 'Телефон',
  ),
));
                $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'HLBLOCK_Ccmis',
  'FIELD_NAME' => 'UF_TYPE',
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
    'LIST_HEIGHT' => 5,
    'CAPTION_NO_VALUE' => '',
    'SHOW_NO_VALUE' => 'Y',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Тип',
    'ru' => 'Тип',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Тип',
    'ru' => 'Тип',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Тип',
    'ru' => 'Тип',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => 'Тип',
    'ru' => 'Тип',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => 'Тип',
    'ru' => 'Тип',
  ),
  'ENUM_VALUES' => 
  array (
    0 => 
    array (
      'VALUE' => 'КЦ',
      'DEF' => 'N',
      'SORT' => '10',
      'XML_ID' => 'СС',
    ),
    1 => 
    array (
      'VALUE' => 'МИС',
      'DEF' => 'N',
      'SORT' => '20',
      'XML_ID' => 'MIS',
    ),
  ),
));
        
            }

    public function down() {
        $helper = new HelperManager();

        //your code ...

    }

}
