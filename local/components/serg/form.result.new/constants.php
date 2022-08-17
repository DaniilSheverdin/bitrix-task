<?php

define("TEST_MODE", false); // для тестирования в локальном режиме true
define("TEST_MAIL", false); // для тестирования отправки оповещений true

$arEmailsDep = [
    '2129' => 'Kulagina.Tatiana@tularegion.ru',
    '2130' => 'Ivan.Popov@tularegion.ru',
    '2131' => 'Vitaliy.Prokudin@tularegion.ru',
    '2132' => 'Igor.Zenin@tularegion.ru',
    '2133' => 'Larisa.Klimenova@tularegion.ru'
];

define("DUPLICATE_BI_EMAIL_NOTIFICATION", 'natalya.nikitina@tularegion.ru');
define("DUPLICATE_BI_EMAIL_NOTIFICATION2", 'Taisiya.Dobrieva@tularegion.ru');
define("TIME_EXCESS_LIMIT", 16 * 24 * 60 * 60);

define("IS_LOCAL", getenv('HTTP_HOST') == 'corp.tularegion');
