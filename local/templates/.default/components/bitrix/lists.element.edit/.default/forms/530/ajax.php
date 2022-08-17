<<<<<<< HEAD
<?
define('NEED_AUTH', true);
define('FAMILIA',       "90a26166e2c2ef42151b48a5a8aa5b0a");
define('IMYA',          "57018ea341b5ee6cca18c88da799105b");
define('OTCHECSTVO',    "469a85d467d11129ad6b86e4089e06df");
define('PASSPORT',      "9f75d0c97d65010bf8f0384958d9d53a");
define('PROPISKA',      "3e23056e20d5033b3aff940b2bd86460");
define('ADRES',         "f76a2986d241112e0ba863185e6db31b");
define('TELEFON',       "3e0338766544a353cdc17e6fbe5bfd94");
define('OBRAZOVANIE',   "8586b0cb84811ef9097e27ba3db7b677");

include $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

global $userFields;
$resp = (object)['status'=>"ERROR", 'status_message'=>"", 'data'=>(object)[]];
try{
    \Bitrix\Main\Loader::includeModule('iblock');
    \Bitrix\Main\Loader::includeModule('citto.filesigner');
    $REQUEST            = json_decode(file_get_contents('php://input'), true);
    $SOTRUDNIK          = $userFields($USER->GetId());
    $IBLOCK_ID          = $REQUEST['iblock_id']                                 ?? NULL;
    $V_KAKIE_DANNYE     = $REQUEST['v_kakie_dannye_sleduet_vnesti_izmeneniya']  ?? NULL;
    $NOVYE_DANNYE       = $REQUEST['novye_dannye']                              ?? NULL;
    $vKakieDannye       = NULL;

    if($REQUEST['sessid'] != bitrix_sessid()) throw new Exception('Ошибка. Обновите страницу');
    if(empty($IBLOCK_ID)) throw new Exception('IBLOCK_ID не найден');
    
    if(empty($V_KAKIE_DANNYE)) throw new Exception('Заполните "В какие данные следует внести изменения"');
    $vKakieDannye = CIBlockProperty::GetPropertyEnum('V_KAKIE_DANNYE_SLEDUET_VNESTI_IZMENENIYA', [], ['IBLOCK_ID'=>$IBLOCK_ID, 'ID'=>(int)$V_KAKIE_DANNYE])->fetch();
    if(empty($vKakieDannye)) throw new Exception('Неверно "В какие данные следует внести изменения"');
    
    $SHABLON = \Citto\Filesigner\ShablonyTable::getScalar([
        'filter'=> ['=CODE' => $IBLOCK_ID."_".$vKakieDannye['XML_ID']],
        'limit' => 1,
        'select'=> ['SHABLON']
    ]);
    switch($vKakieDannye['XML_ID']){
        case PASSPORT:
            $passport_seria = trim($REQUEST['passport_seria']);
            $passport_nomer = trim($REQUEST['passport_nomer']);
            $passport_kemv  = trim($REQUEST['passport_kemv']);
            $passport_datav = trim($REQUEST['passport_datav']);
            $passport_kodp  = trim($REQUEST['passport_kodp']);
            if(
                empty($passport_seria)
                || empty($passport_nomer)
                || empty($passport_kemv)
                || empty($passport_datav)
                || empty($passport_kodp)
            ) throw new Exception("Введите все данные паспорта");

            $NOVYE_DANNYE = implode("\n", [
                'Серия: '.$passport_seria,
                'Номер: '.$passport_nomer,
                'Кем выдан: '.$passport_kemv,
                'Дата выдачи: '.$passport_datav,
                'Код подразделения: '.$passport_kodp,
            ]);
            break;
            
        case PROPISKA:
            $propiska = trim($REQUEST['propiska']);
            if(
                empty($propiska)
            ) throw new Exception('Введите "Новый адрес по прописке"');

            $NOVYE_DANNYE = implode("\n", [
                'Новый адрес по прописке: '.$propiska,
            ]);
            break;
        case ADRES:
            $adres = trim($REQUEST['adres']);
            if(
                empty($adres)
            ) throw new Exception('Введите "Новый адрес места проживания"');

            $NOVYE_DANNYE = implode("\n", [
                'Новый адрес места проживания: '.$adres,
            ]);
            break;
        case TELEFON:
            $telefon = trim($REQUEST['telefon']);
            if(
                empty($telefon)
            ) throw new Exception('Введите "Новый номер телефона"');

            $NOVYE_DANNYE = implode("\n", [
                'Новый номер телефона: '.$telefon,
            ]);
            break;
        case OBRAZOVANIE:
            $obrasovanie_uroven         = trim($REQUEST['obrasovanie_uroven']);
            $obrasovanie_spesialnost    = trim($REQUEST['obrasovanie_spesialnost']);
            $obrasovanie_uchrejd        = trim($REQUEST['obrasovanie_uchrejd']);
            $obrasovanie_okonchanie     = trim($REQUEST['obrasovanie_okonchanie']);
            if(
                empty($obrasovanie_uroven)
                || empty($obrasovanie_spesialnost)
                || empty($obrasovanie_uchrejd)
                || empty($obrasovanie_okonchanie)
            ) throw new Exception('Введите все данные об образовании');

            $NOVYE_DANNYE = implode("\n", [
                'Уровень образования: '.$obrasovanie_uroven,
                'Специальность по диплому: '.$obrasovanie_spesialnost,
                'Образовательное учреждение: '.$obrasovanie_uchrejd,
                'Год окончания обучения: '.$obrasovanie_okonchanie,
            ]);
            break;
    }
    

    if($SHABLON){
        if($filesSigned){
            $resp->status       = "OK";
            $resp->data->fields = [
                'novye_dannye' => $NOVYE_DANNYE
            ];
        }else{
            $OLD_DATA = "";
            $NEW_DATA = "";
            switch($vKakieDannye['XML_ID']){
                case FAMILIA:
                    $OLD_DATA = trim($REQUEST['familia_old']);
                    $NEW_DATA = trim($REQUEST['familia_new']);
                    $NOVYE_DANNYE = implode("\n", [
                        'Фамилия (старая): '.$OLD_DATA,
                        'Фамилия (новая): '.$NEW_DATA,
                    ]);
                    break;
                case IMYA:
                    $OLD_DATA = trim($REQUEST['imya_old']);
                    $NEW_DATA = trim($REQUEST['imya_new']);
                    $NOVYE_DANNYE = implode("\n", [
                        'Имя (старое): '.$OLD_DATA,
                        'Имя (новое): '.$NEW_DATA,
                    ]);
                    break;
                case OTCHECSTVO:
                    $OLD_DATA = trim($REQUEST['otchestvo_old']);
                    $NEW_DATA = trim($REQUEST['otchestvo_new']);
                    $NOVYE_DANNYE = implode("\n", [
                        'Отчество (старое): '.$OLD_DATA,
                        'Отчество (новое): '.$NEW_DATA,
                    ]);
                    break;
            }
            if(empty($OLD_DATA)) throw new Exception("Укажите старые данные");
            if(empty($NEW_DATA)) throw new Exception("Укажите новые данные");
            
            $arProps = [
                '#SOTRUDNIK__FIO_ROD#'      => $SOTRUDNIK['FIO_ROD'],
                '#SOTRUDNIK__DOLJNOST_ROD#' => $GLOBALS['mb_lcfirst']($SOTRUDNIK['DOLJNOST_ROD']),
                '#DATE#'                    => date('d.m.Y'),
                '#OLD_DATA#'                => $OLD_DATA,
                '#NEW_DATA#'                => $NEW_DATA,
            ];
            
            
            $doc_content = str_replace(
                array_keys($arProps),
                array_values($arProps),
                $SHABLON
            );
            
            $pdfile1 = new \Citto\Filesigner\PDFile();
            $pdfile1->setClearf(['#PODPIS1#', '#PODPIS2#']);
            $pdfile1->setName("Заявление.pdf");
            $pdfile1->insert($doc_content);
            $pdfile1->save();
        
            $src = '/podpis-fayla/?'.http_build_query([
                'FILES' => [$pdfile1->getId()],
                'POS'   => "#PODPIS1#",
                'CLEARF'=> ['#PODPIS1#', '#PODPIS2#'],
                'sessid'=> bitrix_sessid()
            ]);
            $resp->data->location   = $src;
            $resp->data->fields     = [
                'zayavlenie_fayl_id'=> $pdfile1->getId(),
                'novye_dannye'      => $NOVYE_DANNYE
            ];
            $resp->status = "REDIRECT";
        }
    }else{
        $resp->status       = "OK";
        $resp->data->fields = [
            'zayavlenie_fayl_id'=>"0",
            'novye_dannye'      => $NOVYE_DANNYE
        ];
    }
}catch(Exception $exc){
    $resp->status_message = $exc->getMessage();
}
header('Content-Type: application/json');
echo json_encode($resp);
=======
<?
define('NEED_AUTH', true);
define('FAMILIA',       "90a26166e2c2ef42151b48a5a8aa5b0a");
define('IMYA',          "57018ea341b5ee6cca18c88da799105b");
define('OTCHECSTVO',    "469a85d467d11129ad6b86e4089e06df");
define('PASSPORT',      "9f75d0c97d65010bf8f0384958d9d53a");
define('PROPISKA',      "3e23056e20d5033b3aff940b2bd86460");
define('ADRES',         "f76a2986d241112e0ba863185e6db31b");
define('TELEFON',       "3e0338766544a353cdc17e6fbe5bfd94");
define('OBRAZOVANIE',   "8586b0cb84811ef9097e27ba3db7b677");

include $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

global $userFields;
$resp = (object)['status'=>"ERROR", 'status_message'=>"", 'data'=>(object)[]];
try{
    \Bitrix\Main\Loader::includeModule('iblock');
    \Bitrix\Main\Loader::includeModule('citto.filesigner');
    $REQUEST            = json_decode(file_get_contents('php://input'), true);
    $SOTRUDNIK          = $userFields($USER->GetId());
    $IBLOCK_ID          = $REQUEST['iblock_id']                                 ?? NULL;
    $V_KAKIE_DANNYE     = $REQUEST['v_kakie_dannye_sleduet_vnesti_izmeneniya']  ?? NULL;
    $NOVYE_DANNYE       = $REQUEST['novye_dannye']                              ?? NULL;
    $vKakieDannye       = NULL;

    if($REQUEST['sessid'] != bitrix_sessid()) throw new Exception('Ошибка. Обновите страницу');
    if(empty($IBLOCK_ID)) throw new Exception('IBLOCK_ID не найден');
    
    if(empty($V_KAKIE_DANNYE)) throw new Exception('Заполните "В какие данные следует внести изменения"');
    $vKakieDannye = CIBlockProperty::GetPropertyEnum('V_KAKIE_DANNYE_SLEDUET_VNESTI_IZMENENIYA', [], ['IBLOCK_ID'=>$IBLOCK_ID, 'ID'=>(int)$V_KAKIE_DANNYE])->fetch();
    if(empty($vKakieDannye)) throw new Exception('Неверно "В какие данные следует внести изменения"');
    
    $SHABLON = \Citto\Filesigner\ShablonyTable::getScalar([
        'filter'=> ['=CODE' => $IBLOCK_ID."_".$vKakieDannye['XML_ID']],
        'limit' => 1,
        'select'=> ['SHABLON']
    ]);
    switch($vKakieDannye['XML_ID']){
        case PASSPORT:
            $passport_seria = trim($REQUEST['passport_seria']);
            $passport_nomer = trim($REQUEST['passport_nomer']);
            $passport_kemv  = trim($REQUEST['passport_kemv']);
            $passport_datav = trim($REQUEST['passport_datav']);
            $passport_kodp  = trim($REQUEST['passport_kodp']);
            if(
                empty($passport_seria)
                || empty($passport_nomer)
                || empty($passport_kemv)
                || empty($passport_datav)
                || empty($passport_kodp)
            ) throw new Exception("Введите все данные паспорта");

            $NOVYE_DANNYE = implode("\n", [
                'Серия: '.$passport_seria,
                'Номер: '.$passport_nomer,
                'Кем выдан: '.$passport_kemv,
                'Дата выдачи: '.$passport_datav,
                'Код подразделения: '.$passport_kodp,
            ]);
            break;
            
        case PROPISKA:
            $propiska = trim($REQUEST['propiska']);
            if(
                empty($propiska)
            ) throw new Exception('Введите "Новый адрес по прописке"');

            $NOVYE_DANNYE = implode("\n", [
                'Новый адрес по прописке: '.$propiska,
            ]);
            break;
        case ADRES:
            $adres = trim($REQUEST['adres']);
            if(
                empty($adres)
            ) throw new Exception('Введите "Новый адрес места проживания"');

            $NOVYE_DANNYE = implode("\n", [
                'Новый адрес места проживания: '.$adres,
            ]);
            break;
        case TELEFON:
            $telefon = trim($REQUEST['telefon']);
            if(
                empty($telefon)
            ) throw new Exception('Введите "Новый номер телефона"');

            $NOVYE_DANNYE = implode("\n", [
                'Новый номер телефона: '.$telefon,
            ]);
            break;
        case OBRAZOVANIE:
            $obrasovanie_uroven         = trim($REQUEST['obrasovanie_uroven']);
            $obrasovanie_spesialnost    = trim($REQUEST['obrasovanie_spesialnost']);
            $obrasovanie_uchrejd        = trim($REQUEST['obrasovanie_uchrejd']);
            $obrasovanie_okonchanie     = trim($REQUEST['obrasovanie_okonchanie']);
            if(
                empty($obrasovanie_uroven)
                || empty($obrasovanie_spesialnost)
                || empty($obrasovanie_uchrejd)
                || empty($obrasovanie_okonchanie)
            ) throw new Exception('Введите все данные об образовании');

            $NOVYE_DANNYE = implode("\n", [
                'Уровень образования: '.$obrasovanie_uroven,
                'Специальность по диплому: '.$obrasovanie_spesialnost,
                'Образовательное учреждение: '.$obrasovanie_uchrejd,
                'Год окончания обучения: '.$obrasovanie_okonchanie,
            ]);
            break;
    }
    

    if($SHABLON){
        if($filesSigned){
            $resp->status       = "OK";
            $resp->data->fields = [
                'novye_dannye' => $NOVYE_DANNYE
            ];
        }else{
            $OLD_DATA = "";
            $NEW_DATA = "";
            switch($vKakieDannye['XML_ID']){
                case FAMILIA:
                    $OLD_DATA = trim($REQUEST['familia_old']);
                    $NEW_DATA = trim($REQUEST['familia_new']);
                    $NOVYE_DANNYE = implode("\n", [
                        'Фамилия (старая): '.$OLD_DATA,
                        'Фамилия (новая): '.$NEW_DATA,
                    ]);
                    break;
                case IMYA:
                    $OLD_DATA = trim($REQUEST['imya_old']);
                    $NEW_DATA = trim($REQUEST['imya_new']);
                    $NOVYE_DANNYE = implode("\n", [
                        'Имя (старое): '.$OLD_DATA,
                        'Имя (новое): '.$NEW_DATA,
                    ]);
                    break;
                case OTCHECSTVO:
                    $OLD_DATA = trim($REQUEST['otchestvo_old']);
                    $NEW_DATA = trim($REQUEST['otchestvo_new']);
                    $NOVYE_DANNYE = implode("\n", [
                        'Отчество (старое): '.$OLD_DATA,
                        'Отчество (новое): '.$NEW_DATA,
                    ]);
                    break;
            }
            if(empty($OLD_DATA)) throw new Exception("Укажите старые данные");
            if(empty($NEW_DATA)) throw new Exception("Укажите новые данные");
            
            $arProps = [
                '#SOTRUDNIK__FIO_ROD#'      => $SOTRUDNIK['FIO_ROD'],
                '#SOTRUDNIK__DOLJNOST_ROD#' => $GLOBALS['mb_lcfirst']($SOTRUDNIK['DOLJNOST_ROD']),
                '#DATE#'                    => date('d.m.Y'),
                '#OLD_DATA#'                => $OLD_DATA,
                '#NEW_DATA#'                => $NEW_DATA,
            ];
            
            
            $doc_content = str_replace(
                array_keys($arProps),
                array_values($arProps),
                $SHABLON
            );
            
            $pdfile1 = new \Citto\Filesigner\PDFile();
            $pdfile1->setClearf(['#PODPIS1#', '#PODPIS2#']);
            $pdfile1->setName("Заявление.pdf");
            $pdfile1->insert($doc_content);
            $pdfile1->save();
        
            $src = '/podpis-fayla/?'.http_build_query([
                'FILES' => [$pdfile1->getId()],
                'POS'   => "#PODPIS1#",
                'CLEARF'=> ['#PODPIS1#', '#PODPIS2#'],
                'sessid'=> bitrix_sessid()
            ]);
            $resp->data->location   = $src;
            $resp->data->fields     = [
                'zayavlenie_fayl_id'=> $pdfile1->getId(),
                'novye_dannye'      => $NOVYE_DANNYE
            ];
            $resp->status = "REDIRECT";
        }
    }else{
        $resp->status       = "OK";
        $resp->data->fields = [
            'zayavlenie_fayl_id'=>"0",
            'novye_dannye'      => $NOVYE_DANNYE
        ];
    }
}catch(Exception $exc){
    $resp->status_message = $exc->getMessage();
}
header('Content-Type: application/json');
echo json_encode($resp);
>>>>>>> e0a0eba79 (init)
die;