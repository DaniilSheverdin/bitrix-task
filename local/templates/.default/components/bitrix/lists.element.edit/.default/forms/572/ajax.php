<<<<<<< HEAD
<?
define('NEED_AUTH', true);

include $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

global $userFields;
$resp = (object)['status'=>"ERROR", 'status_message'=>"", 'data'=>(object)[]];
try{
    \Bitrix\Main\Loader::includeModule('iblock');
    \Bitrix\Main\Loader::includeModule('citto.filesigner');
    \Bitrix\Main\Loader::includeModule('htc.twigintegrationmodule');
    \Bitrix\Main\Loader::includeModule('nkhost.phpexcel');

    //include $GLOBALS['PHPEXCELPATH'] . '/PHPExcel/IOFactory.php';  

    $REQUEST                            = $_REQUEST;
    $SOTRUDNIK                          = $userFields($USER->GetId());
    $IBLOCK_ID                          = $REQUEST['iblock_id']                         ?? null;
    // $RUKOVODITEL                        = $REQUEST['rukovoditel']                       ?? null;
    $VYBOR_ZDANIYA                      = $REQUEST['vybor_zdaniya']                     ?? null;
    $SPISOK_LITS                        = $REQUEST['spisok_lits']                       ?? null;
    $SPISOK_LITS_FAYL                   = $_FILES['spisok_lits_fayl']                   ?? null;
    $DATA_POSESHCHENIYA                 = $REQUEST['data_poseshcheniya']                ?? null;
    $KABINET                            = $REQUEST['kabinet']                           ?? null;
    $TSEL_POSESHCHENIYA                 = $REQUEST['tsel_poseshcheniya']                ?? null;
    $NEOBKHODIM_LI_DOSTUP_NA_PARKOVKU   = $REQUEST['neobkhodim_li_dostup_na_parkovku']  ?? null;
    $AVTO                               = $REQUEST['avto']                              ?? null;
    $SPISOK_LITS_MANUAL                 = !empty($REQUEST['spisok_lits_manual']);
    $vyborZdaniya                       = null;
    $spisokLits                         = [];
    $neobkhodimLiDostupNaParkovku       = null;

    if($REQUEST['sessid'] != bitrix_sessid()) throw new Exception('Ошибка. Обновите страницу');
    if(empty($IBLOCK_ID))           throw new Exception('IBLOCK_ID не найден');
    // if(empty($RUKOVODITEL))         throw new Exception('Укажите "Руководитель, с которым следует согласовать пропуск"');
    if(empty($VYBOR_ZDANIYA))       throw new Exception('Укажите "Выбор здания"');
    if(empty($DATA_POSESHCHENIYA))  throw new Exception('Укажите "Дата и время посещения"');
    if(empty($KABINET))             throw new Exception('Укажите "Кабинет"');
    if(empty($TSEL_POSESHCHENIYA))  throw new Exception('Укажите "Цель посещения"');

    $DATA_POSESHCHENIYA = array_filter($DATA_POSESHCHENIYA);
    if(empty($DATA_POSESHCHENIYA))  throw new Exception('Укажите "Дата и время посещения"');

    $vyborZdaniya = CIBlockProperty::GetPropertyEnum('VYBOR_ZDANIYA', [], ['IBLOCK_ID'=>$IBLOCK_ID, 'ID'=>(int)$VYBOR_ZDANIYA])->fetch();
    if(empty($vyborZdaniya)) throw new Exception('Неверно "Выбор здания"');
    
    $neobkhodimLiDostupNaParkovku = CIBlockProperty::GetPropertyEnum('NEOBKHODIM_LI_DOSTUP_NA_PARKOVKU', [], ['IBLOCK_ID'=>$IBLOCK_ID, 'ID'=>(int)$NEOBKHODIM_LI_DOSTUP_NA_PARKOVKU])->fetch();
    if(empty($neobkhodimLiDostupNaParkovku)) throw new Exception('Неверно "Необходим доступ на парковку"');
    $neobkhodimLiDostupNaParkovku = $neobkhodimLiDostupNaParkovku['XML_ID'] == 'Y';
    
    $SHABLON = \Citto\Filesigner\ShablonyTable::getScalar([
        'filter'=> ['=CODE' => $IBLOCK_ID],
        'limit' => 1,
        'select'=> ['SHABLON']
    ]);
    if(empty($SHABLON)) throw new Exception("Не удалось найти шаблон заявления");

    if($SPISOK_LITS_MANUAL){
        if(empty($SPISOK_LITS)) throw new Exception('Укажите "Список лиц"');
        $spisokLits = $SPISOK_LITS;
    }elseif($SPISOK_LITS_FAYL != null){
        if($SPISOK_LITS_FAYL['error'] != UPLOAD_ERR_OK) throw new Exception('Не удалось загрузить файл "Список лиц"');
           
        $reader = PHPExcel_IOFactory::createReader('Excel2007');
        if(!$reader->canRead($SPISOK_LITS_FAYL['tmp_name'])) throw new Exception('Загруженный файл имеет неверное расширение');

        $xls = PHPExcel_IOFactory::load($SPISOK_LITS_FAYL['tmp_name']);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();

        for($i = 2; $i <= $sheet->getHighestRow(); $i++) {
            $litso = [];
            $nColumn = PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn());
            for($j = 0; $j < $nColumn; $j++){
                $nColumnval = trim($sheet->getCellByColumnAndRow($j, $i)->getValue());
                if(empty($nColumnval)) continue;
                $litso[] = $nColumnval;
            }
            if(empty($litso)) continue;
            $spisokLits[] = implode(', ', $litso);
            unset($litso);
        }
    }
    $spisokLits = array_filter($spisokLits);
    if(empty($spisokLits)) throw new Exception('Пустой "Список лиц"');
     
    if($neobkhodimLiDostupNaParkovku){
        if(empty($AVTO)) throw new Exception('Укажите "Автомобили"');
        $AVTO = array_filter($AVTO);
        if(empty($AVTO)) throw new Exception('Укажите "Автомобили"');
    }
    $twig = new Twig_Environment(new Twig_Loader_String());
    $doc_content = $twig->render($SHABLON, [
        'sotrudnik_vedomstva'               => $SOTRUDNIK['FIO'],
        'sotrudnik_doljnost'                => $SOTRUDNIK['DOLJNOST'],
        'vybor_zdaniya'                     => $vyborZdaniya['VALUE'],
        'data_poseshcheniya'                => $DATA_POSESHCHENIYA,
        'kabinet'                           => $KABINET,
        'tsel_poseshcheniya'                => $TSEL_POSESHCHENIYA,
        'neobkhodim_li_dostup_na_parkovku'  => $neobkhodimLiDostupNaParkovku,
        'spisok_lits'                       => $spisokLits,
        'avto'                              => $AVTO,
    ]);

    $pdfile1 = new \Citto\Filesigner\PDFile();
    $pdfile1->setName("Заявление.pdf");
    $pdfile1->insert($doc_content);
    $pdfile1->save();
    $resp->data->fields = [
        'zayavlenie_fayl_id' => $pdfile1->getId(),
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

include $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

global $userFields;
$resp = (object)['status'=>"ERROR", 'status_message'=>"", 'data'=>(object)[]];
try{
    \Bitrix\Main\Loader::includeModule('iblock');
    \Bitrix\Main\Loader::includeModule('citto.filesigner');
    \Bitrix\Main\Loader::includeModule('htc.twigintegrationmodule');
    \Bitrix\Main\Loader::includeModule('nkhost.phpexcel');

    //include $GLOBALS['PHPEXCELPATH'] . '/PHPExcel/IOFactory.php';  

    $REQUEST                            = $_REQUEST;
    $SOTRUDNIK                          = $userFields($USER->GetId());
    $IBLOCK_ID                          = $REQUEST['iblock_id']                         ?? null;
    // $RUKOVODITEL                        = $REQUEST['rukovoditel']                       ?? null;
    $VYBOR_ZDANIYA                      = $REQUEST['vybor_zdaniya']                     ?? null;
    $SPISOK_LITS                        = $REQUEST['spisok_lits']                       ?? null;
    $SPISOK_LITS_FAYL                   = $_FILES['spisok_lits_fayl']                   ?? null;
    $DATA_POSESHCHENIYA                 = $REQUEST['data_poseshcheniya']                ?? null;
    $KABINET                            = $REQUEST['kabinet']                           ?? null;
    $TSEL_POSESHCHENIYA                 = $REQUEST['tsel_poseshcheniya']                ?? null;
    $NEOBKHODIM_LI_DOSTUP_NA_PARKOVKU   = $REQUEST['neobkhodim_li_dostup_na_parkovku']  ?? null;
    $AVTO                               = $REQUEST['avto']                              ?? null;
    $SPISOK_LITS_MANUAL                 = !empty($REQUEST['spisok_lits_manual']);
    $vyborZdaniya                       = null;
    $spisokLits                         = [];
    $neobkhodimLiDostupNaParkovku       = null;

    if($REQUEST['sessid'] != bitrix_sessid()) throw new Exception('Ошибка. Обновите страницу');
    if(empty($IBLOCK_ID))           throw new Exception('IBLOCK_ID не найден');
    // if(empty($RUKOVODITEL))         throw new Exception('Укажите "Руководитель, с которым следует согласовать пропуск"');
    if(empty($VYBOR_ZDANIYA))       throw new Exception('Укажите "Выбор здания"');
    if(empty($DATA_POSESHCHENIYA))  throw new Exception('Укажите "Дата и время посещения"');
    if(empty($KABINET))             throw new Exception('Укажите "Кабинет"');
    if(empty($TSEL_POSESHCHENIYA))  throw new Exception('Укажите "Цель посещения"');

    $DATA_POSESHCHENIYA = array_filter($DATA_POSESHCHENIYA);
    if(empty($DATA_POSESHCHENIYA))  throw new Exception('Укажите "Дата и время посещения"');

    $vyborZdaniya = CIBlockProperty::GetPropertyEnum('VYBOR_ZDANIYA', [], ['IBLOCK_ID'=>$IBLOCK_ID, 'ID'=>(int)$VYBOR_ZDANIYA])->fetch();
    if(empty($vyborZdaniya)) throw new Exception('Неверно "Выбор здания"');
    
    $neobkhodimLiDostupNaParkovku = CIBlockProperty::GetPropertyEnum('NEOBKHODIM_LI_DOSTUP_NA_PARKOVKU', [], ['IBLOCK_ID'=>$IBLOCK_ID, 'ID'=>(int)$NEOBKHODIM_LI_DOSTUP_NA_PARKOVKU])->fetch();
    if(empty($neobkhodimLiDostupNaParkovku)) throw new Exception('Неверно "Необходим доступ на парковку"');
    $neobkhodimLiDostupNaParkovku = $neobkhodimLiDostupNaParkovku['XML_ID'] == 'Y';
    
    $SHABLON = \Citto\Filesigner\ShablonyTable::getScalar([
        'filter'=> ['=CODE' => $IBLOCK_ID],
        'limit' => 1,
        'select'=> ['SHABLON']
    ]);
    if(empty($SHABLON)) throw new Exception("Не удалось найти шаблон заявления");

    if($SPISOK_LITS_MANUAL){
        if(empty($SPISOK_LITS)) throw new Exception('Укажите "Список лиц"');
        $spisokLits = $SPISOK_LITS;
    }elseif($SPISOK_LITS_FAYL != null){
        if($SPISOK_LITS_FAYL['error'] != UPLOAD_ERR_OK) throw new Exception('Не удалось загрузить файл "Список лиц"');
           
        $reader = PHPExcel_IOFactory::createReader('Excel2007');
        if(!$reader->canRead($SPISOK_LITS_FAYL['tmp_name'])) throw new Exception('Загруженный файл имеет неверное расширение');

        $xls = PHPExcel_IOFactory::load($SPISOK_LITS_FAYL['tmp_name']);
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();

        for($i = 2; $i <= $sheet->getHighestRow(); $i++) {
            $litso = [];
            $nColumn = PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn());
            for($j = 0; $j < $nColumn; $j++){
                $nColumnval = trim($sheet->getCellByColumnAndRow($j, $i)->getValue());
                if(empty($nColumnval)) continue;
                $litso[] = $nColumnval;
            }
            if(empty($litso)) continue;
            $spisokLits[] = implode(', ', $litso);
            unset($litso);
        }
    }
    $spisokLits = array_filter($spisokLits);
    if(empty($spisokLits)) throw new Exception('Пустой "Список лиц"');
     
    if($neobkhodimLiDostupNaParkovku){
        if(empty($AVTO)) throw new Exception('Укажите "Автомобили"');
        $AVTO = array_filter($AVTO);
        if(empty($AVTO)) throw new Exception('Укажите "Автомобили"');
    }
    $twig = new Twig_Environment(new Twig_Loader_String());
    $doc_content = $twig->render($SHABLON, [
        'sotrudnik_vedomstva'               => $SOTRUDNIK['FIO'],
        'sotrudnik_doljnost'                => $SOTRUDNIK['DOLJNOST'],
        'vybor_zdaniya'                     => $vyborZdaniya['VALUE'],
        'data_poseshcheniya'                => $DATA_POSESHCHENIYA,
        'kabinet'                           => $KABINET,
        'tsel_poseshcheniya'                => $TSEL_POSESHCHENIYA,
        'neobkhodim_li_dostup_na_parkovku'  => $neobkhodimLiDostupNaParkovku,
        'spisok_lits'                       => $spisokLits,
        'avto'                              => $AVTO,
    ]);

    $pdfile1 = new \Citto\Filesigner\PDFile();
    $pdfile1->setName("Заявление.pdf");
    $pdfile1->insert($doc_content);
    $pdfile1->save();
    $resp->data->fields = [
        'zayavlenie_fayl_id' => $pdfile1->getId(),
    ];
    
    $resp->status = "OK";
}catch(Exception $exc){
    $resp->status_message = $exc->getMessage();
}
header('Content-Type: application/json');
echo json_encode($resp);
>>>>>>> e0a0eba79 (init)
die;