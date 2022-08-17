<?php

namespace Sprint\Migration;


class HLB_VIOLATORS20200423010925 extends Version
{

    protected $description = "данные для изоляции по нарушителям";

    public function up() {
        $helper = new HelperManager();

        
        $helper->Hlblock()->saveHlblock(array (
  'NAME' => 'Violators',
  'TABLE_NAME' => 'violators',
  'LANG' => 
  array (
    'ru' => 
    array (
      'NAME' => 'Нарушители',
    ),
    'en' => 
    array (
      'NAME' => 'Violators',
    ),
  ),
));

                $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'HLBLOCK_Violators',
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
  'ENTITY_ID' => 'HLBLOCK_Violators',
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
    'en' => 'Адрес из системы',
    'ru' => 'Адрес из системы',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Адрес из системы',
    'ru' => 'Адрес из системы',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Адрес из системы',
    'ru' => 'Адрес из системы',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => 'Адрес из системы',
    'ru' => 'Адрес из системы',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => 'Адрес из системы',
    'ru' => 'Адрес из системы',
  ),
));
                $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'HLBLOCK_Violators',
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
    'en' => 'Номер телефона',
    'ru' => 'Номер телефона',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Номер телефона',
    'ru' => 'Номер телефона',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Номер телефона',
    'ru' => 'Номер телефона',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => 'Номер телефона',
    'ru' => 'Номер телефона',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => 'Номер телефона',
    'ru' => 'Номер телефона',
  ),
));
                $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'HLBLOCK_Violators',
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
  'ENTITY_ID' => 'HLBLOCK_Violators',
  'FIELD_NAME' => 'UF_DATE_VIOLATION',
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
    'en' => 'Дата нарушения',
    'ru' => 'Дата нарушения',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Дата нарушения',
    'ru' => 'Дата нарушения',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Дата нарушения',
    'ru' => 'Дата нарушения',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => 'Дата нарушения',
    'ru' => 'Дата нарушения',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => 'Дата нарушения',
    'ru' => 'Дата нарушения',
  ),
));
                $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'HLBLOCK_Violators',
  'FIELD_NAME' => 'UF_ADDRESS_VIOLATION',
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
    'en' => 'Адрес нарушения',
    'ru' => 'Адрес нарушения',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Адрес нарушения',
    'ru' => 'Адрес нарушения',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Адрес нарушения',
    'ru' => 'Адрес нарушения',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => 'Адрес нарушения',
    'ru' => 'Адрес нарушения',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => 'Адрес нарушения',
    'ru' => 'Адрес нарушения',
  ),
));
                $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'HLBLOCK_Violators',
  'FIELD_NAME' => 'UF_COORDINATES',
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
    'en' => 'Координаты нарушения',
    'ru' => 'Координаты нарушения',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Координаты нарушения',
    'ru' => 'Координаты нарушения',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Координаты нарушения',
    'ru' => 'Координаты нарушения',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => 'Координаты нарушения',
    'ru' => 'Координаты нарушения',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => 'Координаты нарушения',
    'ru' => 'Координаты нарушения',
  ),
));
                $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'HLBLOCK_Violators',
  'FIELD_NAME' => 'UF_DATA_TYPE',
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
    'en' => 'Тип найденных данных',
    'ru' => 'Тип найденных данных',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Тип найденных данных',
    'ru' => 'Тип найденных данных',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Тип найденных данных',
    'ru' => 'Тип найденных данных',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => 'Тип найденных данных',
    'ru' => 'Тип найденных данных',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => 'Тип найденных данных',
    'ru' => 'Тип найденных данных',
  ),
  'ENUM_VALUES' => 
  array (
    0 => 
    array (
      'VALUE' => 'РПН',
      'DEF' => 'N',
      'SORT' => '10',
      'XML_ID' => 'RPN',
    ),
    1 => 
    array (
      'VALUE' => 'Внешние источники',
      'DEF' => 'N',
      'SORT' => '20',
      'XML_ID' => 'EXT',
    ),
  ),
));
                $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'HLBLOCK_Violators',
  'FIELD_NAME' => 'UF_COORDINATES_FIRST',
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
    'en' => 'Координаты первой ночи',
    'ru' => 'Координаты первой ночи',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Координаты первой ночи',
    'ru' => 'Координаты первой ночи',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Координаты первой ночи',
    'ru' => 'Координаты первой ночи',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => 'Координаты первой ночи',
    'ru' => 'Координаты первой ночи',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => 'Координаты первой ночи',
    'ru' => 'Координаты первой ночи',
  ),
));
        
            }

    public function down() {
        $helper = new HelperManager();

        //your code ...

    }

}
