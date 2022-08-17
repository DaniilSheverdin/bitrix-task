<<<<<<< HEAD
<?
define('NEED_AUTH', true);
define('XLS_TEMPLATE', $_SERVER['DOCUMENT_ROOT']."/upload/propusk_shablon.xlsx");

include $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

global $userFields;
$resp = (object)['status'=>"ERROR", 'status_message'=>"", 'data'=>(object)[], 'alert' => ""];
try{
    \Bitrix\Main\Loader::includeModule('iblock');
    $REQUEST            = json_decode(file_get_contents('php://input'), true);
    $SOTRUDNIK          = $userFields($USER->GetId());
    $IBLOCK_ID          = $REQUEST['iblock_id']             ?? NULL;
    $DATA               = $REQUEST['data']                  ?? NULL;

    if($REQUEST['sessid'] != bitrix_sessid()) throw new Exception('Ошибка. Обновите страницу');
    if(empty($IBLOCK_ID)) throw new Exception('IBLOCK_ID не найден');
    if(empty($DATA)) throw new Exception('Заполните "Дата"');

    $data = DateTime::createFromFormat('d.m.Y', $DATA);
    if(!$data) throw new Exception('Неверно "Дата"');


    $resp->data->fields = [];
    $resp->status       = "OK";
    $resp->alert        = "ОТПРАВКА НА ПОЧТУ СЕРВИСА";
}catch(Exception $exc){
    $resp->status_message = $exc->getMessage();
}
header('Content-Type: application/json');
echo json_encode($resp);
=======
<?
define('NEED_AUTH', true);
define('XLS_TEMPLATE', $_SERVER['DOCUMENT_ROOT']."/upload/propusk_shablon.xlsx");

include $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

global $userFields;
$resp = (object)['status'=>"ERROR", 'status_message'=>"", 'data'=>(object)[], 'alert' => ""];
try{
    \Bitrix\Main\Loader::includeModule('iblock');
    $REQUEST            = json_decode(file_get_contents('php://input'), true);
    $SOTRUDNIK          = $userFields($USER->GetId());
    $IBLOCK_ID          = $REQUEST['iblock_id']             ?? NULL;
    $DATA               = $REQUEST['data']                  ?? NULL;

    if($REQUEST['sessid'] != bitrix_sessid()) throw new Exception('Ошибка. Обновите страницу');
    if(empty($IBLOCK_ID)) throw new Exception('IBLOCK_ID не найден');
    if(empty($DATA)) throw new Exception('Заполните "Дата"');

    $data = DateTime::createFromFormat('d.m.Y', $DATA);
    if(!$data) throw new Exception('Неверно "Дата"');


    $resp->data->fields = [];
    $resp->status       = "OK";
    $resp->alert        = "ОТПРАВКА НА ПОЧТУ СЕРВИСА";
}catch(Exception $exc){
    $resp->status_message = $exc->getMessage();
}
header('Content-Type: application/json');
echo json_encode($resp);
>>>>>>> e0a0eba79 (init)
die;