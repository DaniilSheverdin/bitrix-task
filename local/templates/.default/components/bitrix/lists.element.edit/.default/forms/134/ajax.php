<?
define('NEED_AUTH', true);
define('HAS_VALID_SIGN_DA',         "8bbc1edaa99be3d636e05dcf0e64382d");
define('BP_POLUCHAET_LICHNO_DA',    "7536693b12021eef1a42d4295224d985");
define('ZAYAVITEL_RUKOVODITEL',     "2ff4e9b1f177af7ef92a64b39e504647");

include $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

global $userFields;
$resp = (object)['status'=>"ERROR", 'status_message'=>"", 'data'=>(object)[]];
try{
    \Bitrix\Main\Loader::includeModule('iblock');
    \Bitrix\Main\Loader::includeModule('citto.filesigner');
    $REQUEST                                = $_REQUEST;
    $SOTRUDNIK                              = $userFields($USER->GetId());
    $IBLOCK_ID                              = $REQUEST['iblock_id']                             ?? NULL;
    $HAS_VALID_SIGN                         = $REQUEST['has_valid_sign']                        ?? NULL;
    $POLUCHENIE_LICHNO                      = $REQUEST['poluchenie_lichno']                     ?? NULL;
    $ZAYAVITEL_YAVLYAETSYA_RUKOVODITELEM    = $REQUEST['zayavitel_yavlyaetsya_rukovoditelem']   ?? NULL;
    $RUKOVODITEL                            = $REQUEST['rukovoditel']                           ?? NULL;
    $ZAYAVKA                                = $_FILES['zayavka_file']                           ?? NULL;
    $DOVERENNOST                            = $_FILES['doverennost_file']                       ?? NULL;
    $DOVERENNOST_UPOLNOMOCHENNOGO           = $_FILES['doverennost_upolnomochennogo_file']      ?? NULL;
    $files                                  = [];

    if($REQUEST['sessid'] != bitrix_sessid()) throw new Exception('Ошибка. Обновите страницу');
    if(empty($IBLOCK_ID)) throw new Exception('IBLOCK_ID не найден');
    
    if(empty($HAS_VALID_SIGN)) throw new Exception('Заполните "Есть действующая ЭП"');
    $HAS_VALID_SIGN = (bool)CIBlockProperty::GetPropertyEnum('HAS_VALID_SIGN', [], ['IBLOCK_ID'=>$IBLOCK_ID, 'ID'=>(int)$HAS_VALID_SIGN, 'EXTERNAL_ID'=>HAS_VALID_SIGN_DA])->fetch();

    if(empty($POLUCHENIE_LICHNO)) throw new Exception('Заполните "Заявитель является руководителем организации/ОИВ"');
    $POLUCHENIE_LICHNO = (bool)CIBlockProperty::GetPropertyEnum('POLUCHENIE_LICHNO', [], ['IBLOCK_ID'=>$IBLOCK_ID, 'ID'=>(int)$POLUCHENIE_LICHNO, 'EXTERNAL_ID'=>BP_POLUCHAET_LICHNO_DA])->fetch();
    
    if(empty($ZAYAVITEL_YAVLYAETSYA_RUKOVODITELEM)) throw new Exception('Заполните "Получение лично"');
    $ZAYAVITEL_YAVLYAETSYA_RUKOVODITELEM = (bool)CIBlockProperty::GetPropertyEnum('ZAYAVITEL_YAVLYAETSYA_RUKOVODITELEM', [],
                                                                    ['IBLOCK_ID'=>$IBLOCK_ID, 'ID'=>(int)$ZAYAVITEL_YAVLYAETSYA_RUKOVODITELEM, 'EXTERNAL_ID'=>ZAYAVITEL_RUKOVODITEL])->fetch();
    if(!$ZAYAVITEL_YAVLYAETSYA_RUKOVODITELEM && empty($RUKOVODITEL)) throw new Exception('Заполните "Укажите руководителя Вашей организации/ОИВ"');
    if($HAS_VALID_SIGN){
        if($filesSigned){
            $resp->status       = "OK";
            $resp->data->fields = [
                'iblock_id' => $IBLOCK_ID,
            ];
        }else{
            if(!$ZAYAVKA) throw new Exception('Заполните "Заявка на изготовление сертификата"');
            if($ZAYAVKA['error'] != UPLOAD_ERR_OK) throw new Exception('Не удалось загрузить "Заявка на изготовление сертификата"');
            
            $files['zayavka']                       = $ZAYAVKA;
            $files['doverennost']                   = "";
            $files['doverennost_upolnomochennogo']  = "";
            if($POLUCHENIE_LICHNO){
                if(!$ZAYAVITEL_YAVLYAETSYA_RUKOVODITELEM){
                    if(!$DOVERENNOST) throw new Exception('Заполните "Доверенность на осуществление действий от имени юр. Лица (или приказ)"');
                    if($DOVERENNOST['error'] != UPLOAD_ERR_OK) throw new Exception('Не удалось загрузить "Доверенность на осуществление действий от имени юр. Лица (или приказ)"');
                    $files['doverennost'] = $DOVERENNOST;
                }
            }else{
                if(!$DOVERENNOST_UPOLNOMOCHENNOGO) throw new Exception('Заполните "Доверенность уполномоченного пользователя"');
                if($DOVERENNOST_UPOLNOMOCHENNOGO['error'] != UPLOAD_ERR_OK) throw new Exception('Не удалось загрузить "Доверенность уполномоченного пользователя"');
                $files['doverennost_upolnomochennogo'] = $DOVERENNOST_UPOLNOMOCHENNOGO;
            }
            
            foreach($files as &$file){
                if(empty($file)) continue;
                $file['MODULE_ID']    = "pdf_file";
                $file['external_id']  = uniqid("tosign_".$IBLOCK_ID."_".$SOTRUDNIK['ID']."_");
                $file['ID']           = \CFile::SaveFile($file, "pdf_file", true);

                if(empty($file['ID'])) throw new Exception("Не удалось сохранить файл: ".htmlentities($file['name']));
            }
            unset($file);

            $resp->data->fields = array_map(function($file){ return $file['ID'] ?? ""; }, $files);
            $src                = '/podpis-fayla/?'.http_build_query([
                                        'FILES' => array_values(array_filter($resp->data->fields)),
                                        'POS'   => "#PODPIS1#",
                                        'CLEARF'=> ['#PODPIS1#', '#PODPIS2#'],
                                        'sessid'=> bitrix_sessid()
                                    ]);
            $resp->data->location   = $src;
            $resp->status = "REDIRECT";
        }
    }else{
        $resp->status       = "OK";
        $resp->data->fields = [
            'iblock_id' => $IBLOCK_ID
        ];
    }
    
}catch(Exception $exc){
    $resp->status_message = $exc->getMessage();
}
header('Content-Type: application/json');
echo json_encode($resp);
die;