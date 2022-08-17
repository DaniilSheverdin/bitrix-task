<?php
include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$APPLICATION->IncludeComponent(
    'serg:super.component',
    'migration.docs.api',
    Array(),
    false
);


