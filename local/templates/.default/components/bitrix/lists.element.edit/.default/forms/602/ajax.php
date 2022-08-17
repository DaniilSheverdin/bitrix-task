<?
include $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';
global $userFields;
$resp = (object)['status'=>"ERROR", 'status_message'=>"", 'data'=>(object)[]];
try{
    \Bitrix\Main\Loader::includeModule('iblock');
    \Bitrix\Main\Loader::includeModule('citto.filesigner');
    $REQUEST            = json_decode(file_get_contents('php://input'), true);
    $SOTRUDNIK          = $userFields($USER->GetId());
    $IBLOCK_ID          = $REQUEST['iblock_id']         ?? NULL;
    $NAIMENOVANIE_IMUSHCHESTVA = $REQUEST['naimenovanie_imushchestva']?? NULL;
	$INVENTARNYY_NOMER = $REQUEST['inventarnyy_nomer']?? NULL;
	$MESTO_OTKUDA_SDAETSYA = $REQUEST['mesto_otkuda_sdaetsya']?? NULL;
	$ADRES = $REQUEST['adres']?? NULL;

    if($REQUEST['sessid'] != bitrix_sessid()) throw new Exception('Ошибка. Обновите страницу');
    if(empty($IBLOCK_ID)) throw new Exception('IBLOCK_ID не найден');
    
    if(empty($NAIMENOVANIE_IMUSHCHESTVA)) throw new Exception('Заполните "Наименование имущества"');
    //$tipDolzhnosti = CIBlockProperty::GetPropertyEnum('TIP_DOLZHNOSTI', [], ['IBLOCK_ID'=>$IBLOCK_ID, 'ID'=>(int)$TIP_DOLZHNOSTI])->fetch();
    if(empty($INVENTARNYY_NOMER)) throw new Exception('Заполните "Инвентарный номер"');
    
    if(empty($MESTO_OTKUDA_SDAETSYA)) throw new Exception('Заполните "Место откуда сдается"');   

	if(empty($ADRES)) throw new Exception('Заполните "Адрес"');

    $resp->status       = "OK";
    $resp->data->fields = [
        'zayavlenie_fayl_id'=>"0"
    ];

}catch(Exception $exc){
    print_r("catch");
    $resp->status_message = $exc->getMessage();
}

header('Content-Type: application/json');
echo json_encode($resp);
die;