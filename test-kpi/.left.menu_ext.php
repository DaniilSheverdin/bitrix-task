<?php

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$aMenuLinks = Array(
    Array(
        'Правила расчета',
        SITE_DIR.'test-kpi/computed_rules',
        Array(),
        Array('menu_item_id' => 'menu_kpi_computed_rules'),
        ""
    ),
  Array(
    'Утвердить ФЕ',
    SITE_DIR.'test-kpi/staff_to_wp',
    Array(),
    Array('menu_item_id' => 'menu_kpi_computed_rules'),
    ""
  ),
  Array(
    'Данные отдела',
    SITE_DIR.'test-kpi/insert_data_dep',
    Array(),
    Array('menu_item_id' => 'menu_kpi_computed_rules'),
    ""
  ),
  Array(
    'Данные управления',
    SITE_DIR.'test-kpi/send_data_gov',
    Array(),
    Array('menu_item_id' => 'menu_kpi_computed_rules'),
    ""
  ),
  Array(
    'Уведомления',
    SITE_DIR.'test-kpi/notify/',
    Array(),
    Array('menu_item_id' => 'menu_kpi_notify'),
    ""
  )
);

