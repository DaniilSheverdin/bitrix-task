<<<<<<< HEAD
<?
define('NEED_AUTH', true);
define('ED_VYPLATU', "7ddd7fca65ef48ea210c27131afefafd");

include $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

global $userFields;
$resp = (object)['status'=>"ERROR", 'status_message'=>"", 'data'=>(object)[]];
try{
    \Bitrix\Main\Loader::includeModule('iblock');
    \Bitrix\Main\Loader::includeModule('citto.filesigner');
    $REQUEST                = json_decode(file_get_contents('php://input'), true);
    $SOTRUDNIK              = $userFields($USER->GetId());
    $IBLOCK_ID              = $REQUEST['iblock_id']                 ?? NULL;
    $TIP_DOLZHNOSTI         = $REQUEST['tip_dolzhnosti']            ?? NULL;
    $PREDOSTAVIT            = $REQUEST['predostavit']               ?? NULL;
    $DATA_PRIKAZA           = $REQUEST['data_prikaza']              ?? NULL;
    $NOMER_PRIKAZA          = $REQUEST['nomer_prikaza']             ?? NULL;
    $DATA_NACHALA_OTPUSKA   = $REQUEST['data_nachala_otpuska']      ?? NULL;
    $RUK_OIV_ORG            = $REQUEST['ruk_oiv_org']               ?? NULL;
    $ZAYAVLENIE             = $REQUEST['zayavlenie']                ?? NULL;
    $predostavit            = NULL;


    if($REQUEST['sessid'] != bitrix_sessid()) throw new Exception('Ошибка. Обновите страницу');

    if(empty($IBLOCK_ID)) throw new Exception('IBLOCK_ID не найден');
    if(empty($TIP_DOLZHNOSTI)) throw new Exception('Заполните "Тип должности"');
    if(empty($PREDOSTAVIT)) throw new Exception('Заполните "Предоставить"');
    if(empty($RUK_OIV_ORG)) throw new Exception('Заполните "Руководитель ОИВ/Организации"');
    if(empty($ZAYAVLENIE)) throw new Exception('Заполните "Заявление"');
    
    $predostavit = CIBlockProperty::GetPropertyEnum('PREDOSTAVIT', [], ['IBLOCK_ID'=>$IBLOCK_ID, 'ID'=>(int)$PREDOSTAVIT])->fetch();
    if(empty($predostavit)) throw new Exception('Неверно "Предоставить"');
    if($predostavit['XML_ID'] == ED_VYPLATU){
        if(empty($DATA_PRIKAZA)) throw new Exception('Заполните "Дата приказа"');
        if(empty($NOMER_PRIKAZA)) throw new Exception('Заполните "Номер приказа"');
        if(empty($DATA_NACHALA_OTPUSKA)) throw new Exception('Заполните "Дата начала отпуска"');
    }


    $resp->status       = "OK";
    $resp->data->fields = [
        'nomer_prikaza' => $NOMER_PRIKAZA
    ];
}catch(Exception $exc){
    $resp->status_message = $exc->getMessage();
}
header('Content-Type: application/json');
echo json_encode($resp);
=======
<?
define('NEED_AUTH', true);
define('ED_VYPLATU', "7ddd7fca65ef48ea210c27131afefafd");

include $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

global $userFields;
$resp = (object)['status'=>"ERROR", 'status_message'=>"", 'data'=>(object)[]];
try{
    \Bitrix\Main\Loader::includeModule('iblock');
    \Bitrix\Main\Loader::includeModule('citto.filesigner');
    $REQUEST                = json_decode(file_get_contents('php://input'), true);
    $SOTRUDNIK              = $userFields($USER->GetId());
    $IBLOCK_ID              = $REQUEST['iblock_id']                 ?? NULL;
    $TIP_DOLZHNOSTI         = $REQUEST['tip_dolzhnosti']            ?? NULL;
    $PREDOSTAVIT            = $REQUEST['predostavit']               ?? NULL;
    $DATA_PRIKAZA           = $REQUEST['data_prikaza']              ?? NULL;
    $NOMER_PRIKAZA          = $REQUEST['nomer_prikaza']             ?? NULL;
    $DATA_NACHALA_OTPUSKA   = $REQUEST['data_nachala_otpuska']      ?? NULL;
    $RUK_OIV_ORG            = $REQUEST['ruk_oiv_org']               ?? NULL;
    $ZAYAVLENIE             = $REQUEST['zayavlenie']                ?? NULL;
    $predostavit            = NULL;


    if($REQUEST['sessid'] != bitrix_sessid()) throw new Exception('Ошибка. Обновите страницу');

    if(empty($IBLOCK_ID)) throw new Exception('IBLOCK_ID не найден');
    if(empty($TIP_DOLZHNOSTI)) throw new Exception('Заполните "Тип должности"');
    if(empty($PREDOSTAVIT)) throw new Exception('Заполните "Предоставить"');
    if(empty($RUK_OIV_ORG)) throw new Exception('Заполните "Руководитель ОИВ/Организации"');
    if(empty($ZAYAVLENIE)) throw new Exception('Заполните "Заявление"');
    
    $predostavit = CIBlockProperty::GetPropertyEnum('PREDOSTAVIT', [], ['IBLOCK_ID'=>$IBLOCK_ID, 'ID'=>(int)$PREDOSTAVIT])->fetch();
    if(empty($predostavit)) throw new Exception('Неверно "Предоставить"');
    if($predostavit['XML_ID'] == ED_VYPLATU){
        if(empty($DATA_PRIKAZA)) throw new Exception('Заполните "Дата приказа"');
        if(empty($NOMER_PRIKAZA)) throw new Exception('Заполните "Номер приказа"');
        if(empty($DATA_NACHALA_OTPUSKA)) throw new Exception('Заполните "Дата начала отпуска"');
    }


    $resp->status       = "OK";
    $resp->data->fields = [
        'nomer_prikaza' => $NOMER_PRIKAZA
    ];
}catch(Exception $exc){
    $resp->status_message = $exc->getMessage();
}
header('Content-Type: application/json');
echo json_encode($resp);
>>>>>>> e0a0eba79 (init)
die;