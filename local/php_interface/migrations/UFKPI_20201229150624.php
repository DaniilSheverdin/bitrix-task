<?php

namespace Sprint\Migration;


class UFKPI_20201229150624 extends Version
{

    protected $description = "Доп поля для KPI";

    public function up() {
        $helper = new HelperManager();

            $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_KPI_WORK_POSITION',
  'USER_TYPE_ID' => 'iblock_element',
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
    'IBLOCK_ID' => 598,
    'DEFAULT_VALUE' => '',
    'ACTIVE_FILTER' => 'N',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Привязка к должности KPI',
    'ru' => 'Привязка к должности KPI',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Привязка к должности KPI',
    'ru' => 'Привязка к должности KPI',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Привязка к должности KPI',
    'ru' => 'Привязка к должности KPI',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => 'Привязка к должности KPI',
    'ru' => 'Привязка к должности KPI',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => 'Привязка к должности KPI',
    'ru' => 'Привязка к должности KPI',
  ),
));
            $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'IBLOCK_599_SECTION',
  'FIELD_NAME' => 'UF_KPI_PROGRESS',
  'USER_TYPE_ID' => 'double',
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
    'PRECISION' => 4,
    'SIZE' => 20,
    'MIN_VALUE' => 0.0,
    'MAX_VALUE' => 0.0,
    'DEFAULT_VALUE' => '',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'Коэффициент KPI развития',
    'ru' => 'Коэффициент KPI развития',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Коэффициент KPI развития',
    'ru' => 'Коэффициент KPI развития',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Коэффициент KPI развития',
    'ru' => 'Коэффициент KPI развития',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => 'Коэффициент KPI развития',
    'ru' => 'Коэффициент KPI развития',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => 'Коэффициент KPI развития',
    'ru' => 'Коэффициент KPI развития',
  ),
));
            $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'IBLOCK_599_SECTION',
  'FIELD_NAME' => 'UF_KPI_FACTS_CRITICAL',
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
    'en' => 'Сохраненные значения критического KPI ',
    'ru' => 'Сохраненные значения критического KPI ',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Сохраненные значения критического KPI ',
    'ru' => 'Сохраненные значения критического KPI ',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Сохраненные значения критического KPI ',
    'ru' => 'Сохраненные значения критического KPI ',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => 'Сохраненные значения критического KPI ',
    'ru' => 'Сохраненные значения критического KPI ',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => 'Сохраненные значения критического KPI ',
    'ru' => 'Сохраненные значения критического KPI ',
  ),
));
            $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'IBLOCK_599_SECTION',
  'FIELD_NAME' => 'UF_FORMULA_EXT_CRITICAL',
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
    'en' => 'Формула критического KPI',
    'ru' => 'Формула критического KPI',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Формула критического KPI',
    'ru' => 'Формула критического KPI',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Формула критического KPI',
    'ru' => 'Формула критического KPI',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => 'Формула критического KPI',
    'ru' => 'Формула критического KPI',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => 'Формула критического KPI',
    'ru' => 'Формула критического KPI',
  ),
));
            $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'IBLOCK_599_SECTION',
  'FIELD_NAME' => 'UF_FORMULA_EXT_PROGRESS',
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
    'en' => 'Формула KPI развития',
    'ru' => 'Формула KPI развития',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'Формула KPI развития',
    'ru' => 'Формула KPI развития',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'Формула KPI развития',
    'ru' => 'Формула KPI развития',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => 'Формула KPI развития',
    'ru' => 'Формула KPI развития',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => 'Формула KPI развития',
    'ru' => 'Формула KPI развития',
  ),
));
        }

    public function down() {
        $helper = new HelperManager();

        //your code ...

    }

}
