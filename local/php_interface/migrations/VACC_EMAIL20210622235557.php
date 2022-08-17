<?php

namespace Sprint\Migration;


class VACC_EMAIL20210622235557 extends Version
{

    protected $description = "";

    public function up() {
        $helper = new HelperManager();

        
                $helper->Event()->saveEventType('VACCINATION_CONFIRM', array (
  'LID' => 'ru',
  'EVENT_TYPE' => 'email',
  'NAME' => 'Запись на вакцинацию',
  'DESCRIPTION' => '',
  'SORT' => '150',
));
                $helper->Event()->saveEventType('VACCINATION_CONFIRM', array (
  'LID' => 'en',
  'EVENT_TYPE' => 'email',
  'NAME' => 'Запись на вакцинацию',
  'DESCRIPTION' => '',
  'SORT' => '150',
));
        
                $helper->Event()->saveEventMessage('VACCINATION_CONFIRM', array (
  'LID' => 
  array (
    0 => 'gi',
    1 => 'hy',
    2 => 'nh',
    3 => 's1',
  ),
  'ACTIVE' => 'Y',
  'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#',
  'EMAIL_TO' => '#EMAIL_TO#',
  'SUBJECT' => 'Запись на вакцинацию',
<<<<<<< HEAD
  'MESSAGE' => 'Вы записались на вакцинацию!

Вам необходимо скачать документы для вакцинации, приложенные к письму. Заполните их и принесите с собой на прием.

Необходимо прийти в медицинский кабинет по адресу: Тула, проспект Ленина, 2  к  #TIME#  #DATE#.
=======
  'MESSAGE' => 'Вы записались на вакцинацию!

Вам необходимо скачать документы для вакцинации, приложенные к письму. Заполните их и принесите с собой на прием.

Необходимо прийти в медицинский кабинет по адресу: Тула, проспект Ленина, 2  к  #TIME#  #DATE#.
>>>>>>> e0a0eba79 (init)
Не забудьте взять с собой оформленные документы для вакцинации!"',
  'BODY_TYPE' => 'html',
  'BCC' => '',
  'REPLY_TO' => '',
  'CC' => '',
  'IN_REPLY_TO' => '',
  'PRIORITY' => '',
  'FIELD1_NAME' => '',
  'FIELD1_VALUE' => '',
  'FIELD2_NAME' => '',
  'FIELD2_VALUE' => '',
  'SITE_TEMPLATE_ID' => '',
  'ADDITIONAL_FIELD' => 
  array (
  ),
  'LANGUAGE_ID' => '',
  'EVENT_TYPE' => '[ VACCINATION_CONFIRM ] Запись на вакцинацию',
));
        
            }

    public function down() {
        $helper = new HelperManager();

        //your code ...

    }

}
