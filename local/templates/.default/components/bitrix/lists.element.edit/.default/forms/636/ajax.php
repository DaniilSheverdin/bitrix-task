<?
define('NEED_AUTH', true);

include $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

global $userFields;
$resp = (object)[
    'status'=>"ERROR",
    'status_message'=>"",
    'data'=>(object)[],
    'alert' => ""
];
try {
    \Bitrix\Main\Loader::includeModule('iblock');
    $REQUEST = json_decode(file_get_contents('php://input'), true);
    $SOTRUDNIK = $userFields($USER->GetId());
    $IBLOCK_ID = $REQUEST['iblock_id'] ?? null;

    if ($REQUEST['sessid'] != bitrix_sessid()) {
        throw new Exception('Ошибка. Обновите страницу');
    }
    if (empty($IBLOCK_ID)) {
        throw new Exception('IBLOCK_ID не найден');
    }

    $resp->data->fields = [];
    $resp->status       = "OK";
    // $resp->alert        = "OK";
} catch (Exception $exc) {
    $resp->status_message = $exc->getMessage();
}
header('Content-Type: application/json');
echo json_encode($resp);
die;