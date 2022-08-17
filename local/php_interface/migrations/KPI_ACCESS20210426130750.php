<?php

namespace Sprint\Migration;


class KPI_ACCESS20210426130750 extends Version
{

    protected $description = "Поля для доступа к редактированию отделов";

    public function up() {
        $helper = new HelperManager();

            $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_KPI_ACCESS_TO_DEPARTMENT',
  'USER_TYPE_ID' => 'iblock_section',
  'XML_ID' => '',
  'SORT' => '100',
  'MULTIPLE' => 'Y',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'DISPLAY' => 'LIST',
    'LIST_HEIGHT' => 3,
    'IBLOCK_ID' => 5,
    'DEFAULT_VALUE' => '',
    'ACTIVE_FILTER' => 'N',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'KPI доступ к отделам',
    'ru' => 'KPI доступ к отделам',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'KPI доступ к отделам',
    'ru' => 'KPI доступ к отделам',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'KPI доступ к отделам',
    'ru' => 'KPI доступ к отделам',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => 'KPI доступ к отделам',
    'ru' => 'KPI доступ к отделам',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => 'KPI доступ к отделам',
    'ru' => 'KPI доступ к отделам',
  ),
));
            $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_KPI_ASSISTANT_TO_DEPARTMENT',
  'USER_TYPE_ID' => 'iblock_section',
  'XML_ID' => '',
  'SORT' => '100',
  'MULTIPLE' => 'Y',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => 
  array (
    'DISPLAY' => 'LIST',
    'LIST_HEIGHT' => 3,
    'IBLOCK_ID' => 5,
    'DEFAULT_VALUE' => '',
    'ACTIVE_FILTER' => 'N',
  ),
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'KPI помощник отделов',
    'ru' => 'KPI помощник отделов',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'KPI помощник отделов',
    'ru' => 'KPI помощник отделов',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'KPI помощник отделов',
    'ru' => 'KPI помощник отделов',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => 'KPI помощник отделов',
    'ru' => 'KPI помощник отделов',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => 'KPI помощник отделов',
    'ru' => 'KPI помощник отделов',
  ),
));
        }

    public function down() {
        $helper = new HelperManager();

        //your code ...

    }

}
