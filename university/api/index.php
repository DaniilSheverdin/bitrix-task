<?php
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->IncludeComponent(
    'citto:university.component',
    'university.api',
    Array(
        "IBLOCK_ID" => 5,
        "EXCLUDED_SECTIONS" => [2237, 2228, 2137, 2970]
    ),
    false
);
