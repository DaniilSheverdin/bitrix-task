<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$aMenuLinks = Array(
    Array(
        'Правила расчета',
        SITE_DIR.'kpi/computed_rules',
        Array(),
        Array('menu_item_id' => 'menu_kpi_computed_rules'),
        ""
    ),
    Array(
        'Утвердить ФЕ',
        SITE_DIR.'kpi/staff_to_wp',
        Array(),
        Array('menu_item_id' => 'menu_kpi_computed_rules'),
        ""
    ),
    Array(
        'Данные отдела',
        SITE_DIR.'kpi/insert_data_dep',
        Array(),
        Array('menu_item_id' => 'menu_kpi_computed_rules'),
        ""
    ),
    Array(
        'Данные управления',
        SITE_DIR.'kpi/send_data_gov',
        Array(),
        Array('menu_item_id' => 'menu_kpi_computed_rules'),
        ""
    ),
    Array(
        'Показатели',
        SITE_DIR.'kpi/show_kpi/',
        Array(),
        Array('menu_item_id' => 'menu_kpi_show_kpi'),
        ""
    ),
    Array(
        'Настройки и интеграции',
        SITE_DIR.'kpi/access/',
        Array(),
        Array('menu_item_id' => 'menu_kpi_notify'),
        ""
    ),

);

