<?php

define('NO_MB_CHECK', true);

require $_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_before.php";
require_once $DOCUMENT_ROOT . '/local/vendor/autoload.php';

global $USER, $DB;

try {
    if ($USER->IsAdmin()) {

        if (!empty($_REQUEST['BP_ID'])) {
            $res1 = $DB->Update(
                'b_bp_task',
                ['STATUS' => '1'],
                "WHERE `ID`='".intval($_REQUEST['BP_ID'])."'"
            );

            $res2 = $DB->Update(
                'b_bp_task_user',
                ['STATUS' => '3'],
                "WHERE `TASK_ID`='".intval($_REQUEST['BP_ID'])."'"
            );

            echo 'ОК! - '.$res2;

        } else {
            throw new Exception("Не указан идентификатор шага БП");
        }

    } else {
        throw new Exception("У вас нет прав");
    }
} catch (Exception $e) {
    echo $e->getMessage();
}


