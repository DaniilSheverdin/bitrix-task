<<<<<<< HEAD
<?
define('NEED_AUTH', true);
define('XLS_TEMPLATE', $_SERVER['DOCUMENT_ROOT']."/upload/propusk_shablon.xlsx");

include $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

global $userFields;
$resp = (object)['status'=>"ERROR", 'status_message'=>"", 'data'=>(object)[]];
try{
    \Bitrix\Main\Loader::includeModule('iblock');
    \Bitrix\Main\Loader::includeModule('citto.filesigner');
    $REQUEST            = json_decode(file_get_contents('php://input'), true);
    $SOTRUDNIK          = $userFields($USER->GetId());
    $IBLOCK_ID          = $REQUEST['iblock_id']             ?? NULL;
    $FIO_KOMU           = $REQUEST['fio_komu']              ?? NULL;
    $FIO_PODAVSHEGO     = $SOTRUDNIK['FIO'];
    $DOLJNOST_PODAVSHEGO= $REQUEST['doljnost_podavshego']   ?? NULL;
    $K_KOMU             = $REQUEST['k_komu']                ?? NULL;
    $KABINET            = $REQUEST['kabinet']               ?? NULL;
    $VREMYA             = $REQUEST['vremya']                ?? NULL;

    if($REQUEST['sessid'] != bitrix_sessid()) throw new Exception('Ошибка. Обновите страницу');
    if(empty($IBLOCK_ID)) throw new Exception('IBLOCK_ID не найден');

    if(
        empty($FIO_KOMU)
        || empty($DOLJNOST_PODAVSHEGO)
        || empty($K_KOMU)
        || empty($KABINET)
        || empty($VREMYA)) throw new Exception("Заполните все поля");

    $VREMYA = DateTime::createFromFormat("d.m.Y H:i:s", $VREMYA);
    if(!$VREMYA) throw new Exception("Время указано неверно");

    $xlsfile = new \Citto\Filesigner\XLSXFile();
    $xlsfile->setName("Заявка на пропуск ".$SOTRUDNIK['FIO_INIC'].".xlsx");
    $xlsfile->setTemplate(XLS_TEMPLATE);
    $xlsfile->setActiveSheetIndex(0);
    $sheet = $xlsfile->getActiveSheet();
        $sheet->setCellValue('D4', $VREMYA->format('d'));
        $sheet->setCellValue('N4', $VREMYA->format('d'));
        $sheet->setCellValue('F4', $VREMYA->format('m'));
        $sheet->setCellValue('P4', $VREMYA->format('m'));
        $sheet->setCellValue('I4', $VREMYA->format('Y')."г.");
        $sheet->setCellValue('V4', $VREMYA->format('Y')."г.");
        
        $sheet->setCellValue('B6', $FIO_KOMU);
        $sheet->setCellValue('L6', $FIO_KOMU);
        
        $sheet->setCellValue('F9', "паспорт");
        
        $sheet->setCellValue('A14', $FIO_PODAVSHEGO);
        $sheet->setCellValue('K10', $FIO_PODAVSHEGO);
        $sheet->setCellValue('A15', $DOLJNOST_PODAVSHEGO);
        $sheet->setCellValue('K11', $DOLJNOST_PODAVSHEGO);
        
        $sheet->setCellValue('D18', $KABINET);
        $sheet->setCellValue('N15', $KABINET);

        $sheet->setCellValue('N13', $K_KOMU);
        $sheet->setCellValue('R17', $VREMYA->format('H'));
        $sheet->setCellValue('T17', $VREMYA->format('i'));
    $xlsfile->save();

    $resp->data->fields     = [
        'zayavlenie_fayl_id' => $xlsfile->getId(),
    ];
    
    $resp->status = "OK";
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
$resp = (object)['status'=>"ERROR", 'status_message'=>"", 'data'=>(object)[]];
try{
    \Bitrix\Main\Loader::includeModule('iblock');
    \Bitrix\Main\Loader::includeModule('citto.filesigner');
    $REQUEST            = json_decode(file_get_contents('php://input'), true);
    $SOTRUDNIK          = $userFields($USER->GetId());
    $IBLOCK_ID          = $REQUEST['iblock_id']             ?? NULL;
    $FIO_KOMU           = $REQUEST['fio_komu']              ?? NULL;
    $FIO_PODAVSHEGO     = $SOTRUDNIK['FIO'];
    $DOLJNOST_PODAVSHEGO= $REQUEST['doljnost_podavshego']   ?? NULL;
    $K_KOMU             = $REQUEST['k_komu']                ?? NULL;
    $KABINET            = $REQUEST['kabinet']               ?? NULL;
    $VREMYA             = $REQUEST['vremya']                ?? NULL;

    if($REQUEST['sessid'] != bitrix_sessid()) throw new Exception('Ошибка. Обновите страницу');
    if(empty($IBLOCK_ID)) throw new Exception('IBLOCK_ID не найден');

    if(
        empty($FIO_KOMU)
        || empty($DOLJNOST_PODAVSHEGO)
        || empty($K_KOMU)
        || empty($KABINET)
        || empty($VREMYA)) throw new Exception("Заполните все поля");

    $VREMYA = DateTime::createFromFormat("d.m.Y H:i:s", $VREMYA);
    if(!$VREMYA) throw new Exception("Время указано неверно");

    $xlsfile = new \Citto\Filesigner\XLSXFile();
    $xlsfile->setName("Заявка на пропуск ".$SOTRUDNIK['FIO_INIC'].".xlsx");
    $xlsfile->setTemplate(XLS_TEMPLATE);
    $xlsfile->setActiveSheetIndex(0);
    $sheet = $xlsfile->getActiveSheet();
        $sheet->setCellValue('D4', $VREMYA->format('d'));
        $sheet->setCellValue('N4', $VREMYA->format('d'));
        $sheet->setCellValue('F4', $VREMYA->format('m'));
        $sheet->setCellValue('P4', $VREMYA->format('m'));
        $sheet->setCellValue('I4', $VREMYA->format('Y')."г.");
        $sheet->setCellValue('V4', $VREMYA->format('Y')."г.");
        
        $sheet->setCellValue('B6', $FIO_KOMU);
        $sheet->setCellValue('L6', $FIO_KOMU);
        
        $sheet->setCellValue('F9', "паспорт");
        
        $sheet->setCellValue('A14', $FIO_PODAVSHEGO);
        $sheet->setCellValue('K10', $FIO_PODAVSHEGO);
        $sheet->setCellValue('A15', $DOLJNOST_PODAVSHEGO);
        $sheet->setCellValue('K11', $DOLJNOST_PODAVSHEGO);
        
        $sheet->setCellValue('D18', $KABINET);
        $sheet->setCellValue('N15', $KABINET);

        $sheet->setCellValue('N13', $K_KOMU);
        $sheet->setCellValue('R17', $VREMYA->format('H'));
        $sheet->setCellValue('T17', $VREMYA->format('i'));
    $xlsfile->save();

    $resp->data->fields     = [
        'zayavlenie_fayl_id' => $xlsfile->getId(),
    ];
    
    $resp->status = "OK";
}catch(Exception $exc){
    $resp->status_message = $exc->getMessage();
}
header('Content-Type: application/json');
echo json_encode($resp);
>>>>>>> e0a0eba79 (init)
die;