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
    $VYBOR_PERIODA      = $REQUEST['vybor_perioda']         ?? NULL;

    if($REQUEST['sessid'] != bitrix_sessid()) throw new Exception('Ошибка. Обновите страницу');
    if(empty($IBLOCK_ID)) throw new Exception('IBLOCK_ID не найден');
    if(empty($DATA)) throw new Exception('Заполните "Дата"');
    if(empty($VYBOR_PERIODA)) throw new Exception('Заполните "Время передачи документов курьеру"');

    $vyborPerioda = (CIBlockPropertyEnum::GetList([], ['IBLOCK_ID' => $IBLOCK_ID, "CODE" => "VYBOR_PERIODA", 'ID' => $VYBOR_PERIODA]))->fetch();
    if(empty($vyborPerioda)) throw new Exception('Неверно "Время передачи документов курьеру"');
    
    $vyborPeriodaStart = explode(".", trim(current(explode("-", $vyborPerioda['VALUE'], 2))), 3);
    if(empty($vyborPeriodaStart) || count($vyborPeriodaStart) != 2) throw new Exception("Ошибка. Обратитесь в службу поддержки");
    
    $data = DateTime::createFromFormat('d.m.Y', $DATA);
    if(!$data) throw new Exception('Неверно "Дата"');
    $data->setTime(...$vyborPeriodaStart);

    if($data < new DateTime("+1 hours")) throw new Exception("Заказ доставки формируется на текущий день, не позже, чем за 1 час до начала временного периода");
    
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
    $VYBOR_PERIODA      = $REQUEST['vybor_perioda']         ?? NULL;

    if($REQUEST['sessid'] != bitrix_sessid()) throw new Exception('Ошибка. Обновите страницу');
    if(empty($IBLOCK_ID)) throw new Exception('IBLOCK_ID не найден');
    if(empty($DATA)) throw new Exception('Заполните "Дата"');
    if(empty($VYBOR_PERIODA)) throw new Exception('Заполните "Время передачи документов курьеру"');

    $vyborPerioda = (CIBlockPropertyEnum::GetList([], ['IBLOCK_ID' => $IBLOCK_ID, "CODE" => "VYBOR_PERIODA", 'ID' => $VYBOR_PERIODA]))->fetch();
    if(empty($vyborPerioda)) throw new Exception('Неверно "Время передачи документов курьеру"');
    
    $vyborPeriodaStart = explode(".", trim(current(explode("-", $vyborPerioda['VALUE'], 2))), 3);
    if(empty($vyborPeriodaStart) || count($vyborPeriodaStart) != 2) throw new Exception("Ошибка. Обратитесь в службу поддержки");
    
    $data = DateTime::createFromFormat('d.m.Y', $DATA);
    if(!$data) throw new Exception('Неверно "Дата"');
    $data->setTime(...$vyborPeriodaStart);

    if($data < new DateTime("+1 hours")) throw new Exception("Заказ доставки формируется на текущий день, не позже, чем за 1 час до начала временного периода");
    
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