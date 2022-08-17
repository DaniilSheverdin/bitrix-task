<?php

namespace Sprint\Migration;


class AUTH_USERS_LIST20200214121927 extends Version
{

    protected $description = "";

    public function up() {
        $helper = new HelperManager();

            $helper->UserTypeEntity()->saveUserTypeEntity(array (
  'ENTITY_ID' => 'USER',
  'FIELD_NAME' => 'UF_AUTH_OTHER_USER',
  'USER_TYPE_ID' => 'employee',
  'XML_ID' => '',
  'SORT' => '100',
  'MULTIPLE' => 'Y',
  'MANDATORY' => 'N',
  'SHOW_FILTER' => 'N',
  'SHOW_IN_LIST' => 'Y',
  'EDIT_IN_LIST' => 'Y',
  'IS_SEARCHABLE' => 'N',
  'SETTINGS' => NULL,
  'EDIT_FORM_LABEL' => 
  array (
    'en' => 'auth_other_user',
    'ru' => 'Список пользователей для авторизации',
  ),
  'LIST_COLUMN_LABEL' => 
  array (
    'en' => 'auth_other_user',
    'ru' => 'Список пользователей для авторизации',
  ),
  'LIST_FILTER_LABEL' => 
  array (
    'en' => 'auth_other_user',
    'ru' => 'Список пользователей для авторизации',
  ),
  'ERROR_MESSAGE' => 
  array (
    'en' => 'auth_other_user',
    'ru' => 'Список пользователей для авторизации',
  ),
  'HELP_MESSAGE' => 
  array (
    'en' => 'auth_other_user',
    'ru' => 'Список пользователей для авторизации',
  ),
));
        }

    public function down() {
        $helper = new HelperManager();

        //your code ...

    }

}
