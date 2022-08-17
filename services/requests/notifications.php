<?php
define('NOT_CHECK_FILE_PERMISSIONS', true);
define('PUBLIC_AJAX_MODE', true);
define('NO_KEEP_STATISTIC', 'Y');
define('STOP_STATISTICS', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';
require_once $_SERVER['DOCUMENT_ROOT'].'/local/vendor/autoload.php';

$APPLICATION->IncludeComponent(
    "serg:form.result.new",
    "notifications",
    [
        "CACHE_TYPE" => "A",
        "CACHE_TIME" => "3600",
        "SEF_MODE" => "N",
        "USE_EXTENDED_ERRORS" => "Y",
        "WEB_FORM_ID" => 3,
    ]
);