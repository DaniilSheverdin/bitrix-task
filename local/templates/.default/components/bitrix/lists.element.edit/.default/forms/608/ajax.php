<?php

define('NEED_AUTH', true);
define('PRICHINA_SEM', "63673b34acdd7de2e947bec93b4ed634");
define('ZAP_SAM_DA', "1637");
define('ZAP_SAM_NET', "1638");
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/vendor/autoload.php';

global $userFields, $getUserOtvpoKadram;
$resp = (object)['status' => "ERROR", 'status_message' => "", 'data' => (object)[]];

/*
 if($CL_DATE_FROM->format("U") < strtotime("+1 day")){
    $root->setVariable('ERRORS', "Дата введена неверно. Дата должна быть позднее текущей на 2 дня");
    return;
}
if($CL_DAYS_COUNT <= 0){
    $root->setVariable('ERRORS', "Количество дней введено неверно");
    return;
};
 */

try {
    \Bitrix\Main\Loader::includeModule('iblock');
    \Bitrix\Main\Loader::includeModule('citto.filesigner');
    $REQUEST = $_REQUEST;
    //$REQUEST = json_decode(file_get_contents('php://input'), true);

    $IBLOCK_ID = $REQUEST['iblock_id'] ?? null;
    $OTPUSK__FROM = $REQUEST['otpusk__from'] ?? null;
    $OTPUSK__DAYS = $REQUEST['otpusk__days'] ?? null;
    $PRICHINA = $REQUEST['prichina'] ?? null;
    $OSNOVANIE_PERENOSA_FAYL = $_FILES['osnovanie_perenosa_fayl'] ?? NULL;
    $filesSigned = $REQUEST['filesSigned'] ?? null;
    $is_uvedomlenie = $REQUEST['is_uvedomlenie'] ?? null;
    $otpusk_calendar = $REQUEST['otpusk_calendar'] ?? null;
    $SOTRUDNIK = $userFields($USER->GetId());
    $SHABLON = null;
    $strOtpList = json_decode($REQUEST['massiv_otpuskov'], true);
    $sObstoyatelstva = $REQUEST['obstoyatelstva'] ?? null;
    $iFileUved = $REQUEST['uvedomlenie'];

    $intDaysSum = array_sum($strOtpList);
    $datesChange = array_keys($strOtpList);

    $datesExclude = array_filter(
        $datesChange,
        function ($day) {
            return floor((strtotime($day) - time()) / (24 * 60 * 60)) < 2;
        }
    );

    if (!empty($otpusk_calendar) && $otpusk_calendar != "inoe") {
        [$OTPUSK__FROM, $OTPUSK__DAYS] = explode("__", $otpusk_calendar);
        $OTPUSK__FROM = str_replace("_", ".", $OTPUSK__FROM);
    }
    $IBLOCK_ID = (int)$IBLOCK_ID;
    $OTPUSK__FROM = DateTime::createFromFormat('d.m.Y', $OTPUSK__FROM);
    $OTPUSK__DAYS = (int)$OTPUSK__DAYS;

    if (empty($sObstoyatelstva)) {
        throw new Exception("Дайте краткое описание обстоятельств для переноса отпуска");
    }
    if ($REQUEST['sessid'] != bitrix_sessid())
        throw new Exception('Ошибка. Обновите страницу');
    if (!$IBLOCK_ID)
        throw new Exception('IBLOCK_ID не найден');
    if ($OTPUSK__DAYS < 1)
        throw new Exception('Заполните "Длительность(количество дней)"');
    if (!$OTPUSK__FROM) {
        throw new Exception('Укажите "Дата начала"');
    }
    if (empty($strOtpList)) {
        throw new Exception("Укажите дату и период переноса отпуска");
    }
    if (count($datesExclude) > 0) {
        throw new Exception("Дата введена неверно. Дата должна быть позднее текущей на 2 дня");
    }
    if ($intDaysSum != $OTPUSK__DAYS) {
        throw new Exception("Количество дней введено неверно");
    }
    if (empty($OSNOVANIE_PERENOSA_FAYL)) {
        throw new Exception("Прикрепите основание переноса");
    }

    $resp->status = "OK";
    $resp->data->fields = [
        'prichina' => $PRICHINA,
        'zapushcheno_samostoyatelno' => $is_uvedomlenie ? ZAP_SAM_NET : ZAP_SAM_DA,
        'otvetstvenny_oiv' => $getUserOtvpoKadram($SOTRUDNIK['ID']) ?: "",
        'otpusk__from' => $OTPUSK__FROM->format('d.m.Y'),
        'otpusk__days' => $OTPUSK__DAYS,
        'uvedomlenie' => $iFileUved,
        'uvedomlenie_id' => $iFileUved
    ];
} catch (Exception $exc) {
    $resp->status_message = $exc->getMessage();
}
header('Content-Type: application/json');
echo json_encode($resp);
die;