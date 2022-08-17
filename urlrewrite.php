<?php
$arUrlRewrite = array(
    array(
        'CONDITION' => '#^/isolation/api/v([0-9]+)/([a-zA-Z0-9\\.\\-_]+)?.*#',
        'RULE' => 'version=$1&action=$2',
        'ID' => '',
        'PATH' => '/isolation/api/index.php',
        'SORT' => 100,
    ),
    array(
        'CONDITION' => '#^/university/api/v([0-9]+)/([a-zA-Z0-9\\.\\-_]+)?.*#',
        'RULE' => 'version=$1&action=$2',
        'ID' => '',
        'PATH' => '/university/api/index.php',
        'SORT' => 100,
    ),
    array(
        'CONDITION' => '#^/apps/htmls.docdesigner/sbp/#',
        'RULE' => '',
        'ID' => 'bitrix:bitrix:bizproc.wizards',
        'PATH' => '/apps/htmls.docdesigner/sbp/index.php',
        'SORT' => 100,
    ),
    array(
        'CONDITION' => '#^/apps/htmls.docdesigner/bp/#',
        'RULE' => '',
        'ID' => 'bitrix:crm.config.bp',
        'PATH' => '/apps/htmls.docdesigner/bp/index.php',
        'SORT' => 100,
    ),
    array(
        'CONDITION' => '#^' . C_SITE_DIR . 'kpi/([a-zA-Z0-9\\.\\-_]+)?.*#',
        'RULE' => 'page=$1',
        'ID' => '',
        'PATH' => '/kpi/index.php',
        'SORT' => 100,
    ),
    array(
        'CONDITION' => '#^/citto/indicators_new/#',
        'RULE' => '',
        'ID' => '',
        'PATH' => '/citto/indicators_new/index.php',
        'SORT' => 100,
    ),
    array(
        'CONDITION' => '#^' . C_SITE_DIR . 'test-kpi/([a-zA-Z0-9\\.\\-_]+)?.*#',
        'RULE' => 'page=$1',
        'ID' => '',
        'PATH' => '/test-kpi/index.php',
        'SORT' => 100,
    ),
    array(
        'CONDITION' => '#^/company/personal/#',
        'RULE' => '',
        'ID' => 'bitrix:socialnetwork_user',
        'PATH' => '/company/personal.php',
        'SORT' => 100,
    ),
    array(
        'CONDITION' => '#^/mfc/company/personal/#',
        'RULE' => '',
        'ID' => 'bitrix:socialnetwork_user',
        'PATH' => '/mfc/company/personal.php',
        'SORT' => 100,
    ),
    array(
        'CONDITION' => '#^/citto/company/personal/#',
        'RULE' => '',
        'ID' => 'bitrix:socialnetwork_user',
        'PATH' => '/citto/company/personal.php',
        'SORT' => 100,
    ),
    array(
        'CONDITION' => '#^/gusc/company/personal/#',
        'RULE' => '',
        'ID' => 'bitrix:socialnetwork_user',
        'PATH' => '/gusc/company/personal.php',
        'SORT' => 100,
    ),
);

include __DIR__ . "/urlrewrite_extra.php";

