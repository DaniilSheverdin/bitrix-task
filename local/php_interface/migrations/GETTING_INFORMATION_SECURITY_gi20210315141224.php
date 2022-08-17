<?php

namespace Sprint\Migration;


class GETTING_INFORMATION_SECURITY_gi20210315141224 extends Version
{

    protected $description = "";

    public function up() {
        $helper = new HelperManager();

        $formHelper = $helper->Form();
        $formId = $formHelper->saveForm(array (
  'NAME' => 'Заявка в службу информационной безопасности',
  'SID' => 'GETTING_INFORMATION_SECURITY_gi',
  'BUTTON' => 'Сохранить',
  'C_SORT' => '900',
  'FIRST_SITE_ID' => NULL,
  'IMAGE_ID' => NULL,
  'USE_CAPTCHA' => 'N',
  'DESCRIPTION' => '',
  'DESCRIPTION_TYPE' => 'text',
  'FORM_TEMPLATE' => '',
  'USE_DEFAULT_TEMPLATE' => 'Y',
  'SHOW_TEMPLATE' => NULL,
  'MAIL_EVENT_TYPE' => 'FORM_FILLING_GETTING_INFORMATION_SECURITY_gi',
  'SHOW_RESULT_TEMPLATE' => NULL,
  'PRINT_RESULT_TEMPLATE' => NULL,
  'EDIT_RESULT_TEMPLATE' => NULL,
  'FILTER_RESULT_TEMPLATE' => '',
  'TABLE_RESULT_TEMPLATE' => '',
  'USE_RESTRICTIONS' => 'N',
  'RESTRICT_USER' => '0',
  'RESTRICT_TIME' => '0',
  'RESTRICT_STATUS' => '',
  'STAT_EVENT1' => 'form',
  'STAT_EVENT2' => 'getting_information_security_gi',
  'STAT_EVENT3' => '',
  'LID' => NULL,
  'C_FIELDS' => '1',
  'QUESTIONS' => '4',
  'STATUSES' => '4',
  'arSITE' => 
  array (
    0 => 'gi',
  ),
  'arMENU' => 
  array (
    'ru' => 'Заявка в службу информационной безопасности',
    'en' => 'Заявка в службу информационной безопасности',
  ),
  'arGROUP' => 
  array (
    'everyone' => '1',
    'EMPLOYEES_gi' => '15',
    'PORTAL_ADMINISTRATION_gi' => '30',
  ),
  'arMAIL_TEMPLATE' => 
  array (
  ),
));

        $formHelper->saveStatuses($formId, array (
  0 => 
  array (
    'CSS' => 'statusgray',
    'C_SORT' => '100',
    'ACTIVE' => 'Y',
    'TITLE' => 'Новое',
    'DESCRIPTION' => '',
    'DEFAULT_VALUE' => 'Y',
    'HANDLER_OUT' => '',
    'HANDLER_IN' => '',
  ),
  1 => 
  array (
    'CSS' => 'statusblue',
    'C_SORT' => '200',
    'ACTIVE' => 'Y',
    'TITLE' => 'Принято к рассмотрению',
    'DESCRIPTION' => '',
    'DEFAULT_VALUE' => 'N',
    'HANDLER_OUT' => '',
    'HANDLER_IN' => '',
  ),
  2 => 
  array (
    'CSS' => 'statusgreen',
    'C_SORT' => '300',
    'ACTIVE' => 'Y',
    'TITLE' => 'Выполнено',
    'DESCRIPTION' => '',
    'DEFAULT_VALUE' => 'N',
    'HANDLER_OUT' => '',
    'HANDLER_IN' => '',
  ),
  3 => 
  array (
    'CSS' => 'statusred',
    'C_SORT' => '400',
    'ACTIVE' => 'Y',
    'TITLE' => 'Отказано',
    'DESCRIPTION' => '',
    'DEFAULT_VALUE' => 'N',
    'HANDLER_OUT' => '',
    'HANDLER_IN' => '',
  ),
));

        $formHelper->saveFields($formId, array (
  0 => 
  array (
    'ACTIVE' => 'Y',
    'TITLE' => '№ отделения',
    'TITLE_TYPE' => 'text',
    'SID' => 'NUMBER',
    'C_SORT' => '50',
    'ADDITIONAL' => 'N',
    'REQUIRED' => 'Y',
    'IN_FILTER' => 'N',
    'IN_RESULTS_TABLE' => 'Y',
    'IN_EXCEL_TABLE' => 'Y',
    'FIELD_TYPE' => '',
    'IMAGE_ID' => NULL,
    'COMMENTS' => '',
    'FILTER_TITLE' => '',
    'RESULTS_TABLE_TITLE' => '',
    'ANSWERS' => 
    array (
      0 => 
      array (
        'MESSAGE' => ' ',
        'VALUE' => '',
        'FIELD_TYPE' => 'text',
        'FIELD_WIDTH' => '0',
        'FIELD_HEIGHT' => '0',
        'FIELD_PARAM' => '',
        'C_SORT' => '100',
        'ACTIVE' => 'Y',
      ),
    ),
    'VALIDATORS' => 
    array (
    ),
  ),
  1 => 
  array (
    'ACTIVE' => 'Y',
    'TITLE' => 'Тема обращения',
    'TITLE_TYPE' => 'text',
    'SID' => 'REQUEST_TYPE',
    'C_SORT' => '150',
    'ADDITIONAL' => 'N',
    'REQUIRED' => 'Y',
    'IN_FILTER' => 'N',
    'IN_RESULTS_TABLE' => 'Y',
    'IN_EXCEL_TABLE' => 'Y',
    'FIELD_TYPE' => '',
    'IMAGE_ID' => NULL,
    'COMMENTS' => '',
    'FILTER_TITLE' => '',
    'RESULTS_TABLE_TITLE' => '',
    'ANSWERS' => 
    array (
      0 => 
      array (
        'MESSAGE' => 'Учетные записи: изменение прав доступа',
        'VALUE' => 'account',
        'FIELD_TYPE' => 'dropdown',
        'FIELD_WIDTH' => '0',
        'FIELD_HEIGHT' => '0',
        'FIELD_PARAM' => '',
        'C_SORT' => '100',
        'ACTIVE' => 'Y',
      ),
      1 => 
      array (
        'MESSAGE' => 'POS-терминал перенос в другое окно или здание (требуется вложение - служебка)',
        'VALUE' => 'transfer_ window',
        'FIELD_TYPE' => 'dropdown',
        'FIELD_WIDTH' => '0',
        'FIELD_HEIGHT' => '0',
        'FIELD_PARAM' => '',
        'C_SORT' => '200',
        'ACTIVE' => 'Y',
      ),
      2 => 
      array (
        'MESSAGE' => 'POS-терминал настройка терминала на рабочем месте (с вложением заполненной формы)',
        'VALUE' => 'terminal_workplace',
        'FIELD_TYPE' => 'dropdown',
        'FIELD_WIDTH' => '0',
        'FIELD_HEIGHT' => '0',
        'FIELD_PARAM' => '',
        'C_SORT' => '300',
        'ACTIVE' => 'Y',
      ),
      3 => 
      array (
        'MESSAGE' => 'POS-терминал - сбросить сессию (с вложением заполненной формы)',
        'VALUE' => 'reset_session',
        'FIELD_TYPE' => 'dropdown',
        'FIELD_WIDTH' => '0',
        'FIELD_HEIGHT' => '0',
        'FIELD_PARAM' => '',
        'C_SORT' => '400',
        'ACTIVE' => 'Y',
      ),
      4 => 
      array (
        'MESSAGE' => 'Проблемы с видеонаблюдением',
        'VALUE' => 'video_surveillance',
        'FIELD_TYPE' => 'dropdown',
        'FIELD_WIDTH' => '0',
        'FIELD_HEIGHT' => '0',
        'FIELD_PARAM' => '',
        'C_SORT' => '500',
        'ACTIVE' => 'Y',
      ),
      5 => 
      array (
        'MESSAGE' => 'Компьютер гос. услуг настройка',
        'VALUE' => 'computer_setting',
        'FIELD_TYPE' => 'dropdown',
        'FIELD_WIDTH' => '0',
        'FIELD_HEIGHT' => '0',
        'FIELD_PARAM' => '',
        'C_SORT' => '600',
        'ACTIVE' => 'Y',
      ),
      6 => 
      array (
        'MESSAGE' => 'Технические проблемы с КБК',
        'VALUE' => 'problems_KBK',
        'FIELD_TYPE' => 'dropdown',
        'FIELD_WIDTH' => '0',
        'FIELD_HEIGHT' => '0',
        'FIELD_PARAM' => '',
        'C_SORT' => '700',
        'ACTIVE' => 'Y',
      ),
      7 => 
      array (
        'MESSAGE' => 'Регистрация съёмных носителей',
        'VALUE' => 'media_registration',
        'FIELD_TYPE' => 'dropdown',
        'FIELD_WIDTH' => '0',
        'FIELD_HEIGHT' => '0',
        'FIELD_PARAM' => '',
        'C_SORT' => '800',
        'ACTIVE' => 'Y',
      ),
      8 => 
      array (
        'MESSAGE' => 'Настройка электронных пропусков',
        'VALUE' => 'electronic_passes',
        'FIELD_TYPE' => 'dropdown',
        'FIELD_WIDTH' => '0',
        'FIELD_HEIGHT' => '0',
        'FIELD_PARAM' => '',
        'C_SORT' => '900',
        'ACTIVE' => 'Y',
      ),
      9 => 
      array (
        'MESSAGE' => 'Настройка Антивируса (Kaspersky)',
        'VALUE' => 'configuring_antivirus',
        'FIELD_TYPE' => 'dropdown',
        'FIELD_WIDTH' => '0',
        'FIELD_HEIGHT' => '0',
        'FIELD_PARAM' => '',
        'C_SORT' => '1000',
        'ACTIVE' => 'Y',
      ),
      10 => 
      array (
        'MESSAGE' => 'Проблемы со сканером штрихкода',
        'VALUE' => 'barcode_scanner',
        'FIELD_TYPE' => 'dropdown',
        'FIELD_WIDTH' => '0',
        'FIELD_HEIGHT' => '0',
        'FIELD_PARAM' => '',
        'C_SORT' => '1100',
        'ACTIVE' => 'Y',
      ),
      11 => 
      array (
        'MESSAGE' => 'Смена пароля по требованию сотрудника',
        'VALUE' => 'password_ request',
        'FIELD_TYPE' => 'dropdown',
        'FIELD_WIDTH' => '0',
        'FIELD_HEIGHT' => '0',
        'FIELD_PARAM' => '',
        'C_SORT' => '1200',
        'ACTIVE' => 'Y',
      ),
    ),
    'VALIDATORS' => 
    array (
    ),
  ),
  2 => 
  array (
    'ACTIVE' => 'Y',
    'TITLE' => 'Описание проблемы',
    'TITLE_TYPE' => 'text',
    'SID' => 'TROUBLE_DESCRIPTION',
    'C_SORT' => '250',
    'ADDITIONAL' => 'N',
    'REQUIRED' => 'N',
    'IN_FILTER' => 'N',
    'IN_RESULTS_TABLE' => 'Y',
    'IN_EXCEL_TABLE' => 'Y',
    'FIELD_TYPE' => '',
    'IMAGE_ID' => NULL,
    'COMMENTS' => '',
    'FILTER_TITLE' => '',
    'RESULTS_TABLE_TITLE' => '',
    'ANSWERS' => 
    array (
      0 => 
      array (
        'MESSAGE' => ' ',
        'VALUE' => '',
        'FIELD_TYPE' => 'textarea',
        'FIELD_WIDTH' => '0',
        'FIELD_HEIGHT' => '0',
        'FIELD_PARAM' => '',
        'C_SORT' => '100',
        'ACTIVE' => 'Y',
      ),
    ),
    'VALIDATORS' => 
    array (
    ),
  ),
  3 => 
  array (
    'ACTIVE' => 'Y',
    'TITLE' => 'Файл',
    'TITLE_TYPE' => 'text',
    'SID' => 'FILE',
    'C_SORT' => '350',
    'ADDITIONAL' => 'N',
    'REQUIRED' => 'N',
    'IN_FILTER' => 'N',
    'IN_RESULTS_TABLE' => 'Y',
    'IN_EXCEL_TABLE' => 'Y',
    'FIELD_TYPE' => '',
    'IMAGE_ID' => NULL,
    'COMMENTS' => '',
    'FILTER_TITLE' => '',
    'RESULTS_TABLE_TITLE' => '',
    'ANSWERS' => 
    array (
      0 => 
      array (
        'MESSAGE' => ' ',
        'VALUE' => '',
        'FIELD_TYPE' => 'file',
        'FIELD_WIDTH' => '0',
        'FIELD_HEIGHT' => '0',
        'FIELD_PARAM' => '',
        'C_SORT' => '100',
        'ACTIVE' => 'Y',
      ),
    ),
    'VALIDATORS' => 
    array (
    ),
  ),
  4 => 
  array (
    'ACTIVE' => 'Y',
    'TITLE' => 'Ответ',
    'TITLE_TYPE' => 'text',
    'SID' => 'ADMIN_NOTE',
    'C_SORT' => '450',
    'ADDITIONAL' => 'Y',
    'REQUIRED' => 'N',
    'IN_FILTER' => 'N',
    'IN_RESULTS_TABLE' => 'Y',
    'IN_EXCEL_TABLE' => 'Y',
    'FIELD_TYPE' => 'text',
    'IMAGE_ID' => NULL,
    'COMMENTS' => '',
    'FILTER_TITLE' => '',
    'RESULTS_TABLE_TITLE' => '',
    'ANSWERS' => 
    array (
    ),
    'VALIDATORS' => 
    array (
    ),
  ),
));

    }

    public function down() {
        $helper = new HelperManager();

    }

}

