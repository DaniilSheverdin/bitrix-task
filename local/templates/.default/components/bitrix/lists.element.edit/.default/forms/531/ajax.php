<<<<<<< HEAD
<?
define('NEED_AUTH', true);

include $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

global $userFields;
$resp = (object)['status'=>"ERROR", 'status_message'=>"", 'data'=>(object)[]];
try{
    \Bitrix\Main\Loader::includeModule('iblock');
    \Bitrix\Main\Loader::includeModule('citto.filesigner');
    $REQUEST    = json_decode(file_get_contents('php://input'), true);
    $SOTRUDNIK  = $userFields($USER->GetId());
    $IBLOCK_ID  = $REQUEST['iblock_id'] ?? NULL;


    if($REQUEST['sessid'] != bitrix_sessid()) throw new Exception('Ошибка. Обновите страницу');
    if(empty($IBLOCK_ID)) throw new Exception('IBLOCK_ID не найден');
    
    $SHABLON = \Citto\Filesigner\ShablonyTable::getScalar([
        'filter'=> ['=CODE' => $IBLOCK_ID],
        'limit' => 1,
        'select'=> ['SHABLON']
    ]);

    if($SHABLON){
        if($filesSigned){
            $resp->status       = "OK";
            $resp->data->fields = [
                'tsel' => $TSEL,
            ];
        }else{
            $arProps = [
                '#SOTRUDNIK__FIO_ROD#'      => $SOTRUDNIK['FIO_ROD'],
                '#SOTRUDNIK__DOLJNOST_ROD#' => $SOTRUDNIK['DOLJNOST_ROD'],
                '#DATE#'                    => date('d.m.Y'),
            ];
           
            $props = \Bitrix\Iblock\PropertyTable::getList([
                'filter' => ['IBLOCK_ID' => $IBLOCK_ID],
                'select' => ['NAME', 'CODE', 'IS_REQUIRED']
            ])->fetchAll();
            foreach($props as $prop){
                $propCode   = $prop['CODE'];
                $propName   = $prop['NAME'];
                $propVal    = trim(strip_tags(strVal($REQUEST[mb_strtolower($propCode)] ?? "")));
                
                if(isset($arProps["#PROPERTY_$propCode#"])) continue;
                if($prop['IS_REQUIRED'] == "Y" && empty($propVal)) throw new Exception("Укажите: $propName");
                $arProps["#PROPERTY_$propCode#"] = $propVal;
            }

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
                'zayavlenie_fayl_id' => $pdfile1->getId(),
            ];
            $resp->status = "REDIRECT";
        }
    }else{
        $resp->status       = "OK";
        $resp->data->fields = [
            'zayavlenie_fayl_id'=>"0"
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

include $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

global $userFields;
$resp = (object)['status'=>"ERROR", 'status_message'=>"", 'data'=>(object)[]];
try{
    \Bitrix\Main\Loader::includeModule('iblock');
    \Bitrix\Main\Loader::includeModule('citto.filesigner');
    $REQUEST    = json_decode(file_get_contents('php://input'), true);
    $SOTRUDNIK  = $userFields($USER->GetId());
    $IBLOCK_ID  = $REQUEST['iblock_id'] ?? NULL;


    if($REQUEST['sessid'] != bitrix_sessid()) throw new Exception('Ошибка. Обновите страницу');
    if(empty($IBLOCK_ID)) throw new Exception('IBLOCK_ID не найден');
    
    $SHABLON = \Citto\Filesigner\ShablonyTable::getScalar([
        'filter'=> ['=CODE' => $IBLOCK_ID],
        'limit' => 1,
        'select'=> ['SHABLON']
    ]);

    if($SHABLON){
        if($filesSigned){
            $resp->status       = "OK";
            $resp->data->fields = [
                'tsel' => $TSEL,
            ];
        }else{
            $arProps = [
                '#SOTRUDNIK__FIO_ROD#'      => $SOTRUDNIK['FIO_ROD'],
                '#SOTRUDNIK__DOLJNOST_ROD#' => $SOTRUDNIK['DOLJNOST_ROD'],
                '#DATE#'                    => date('d.m.Y'),
            ];
           
            $props = \Bitrix\Iblock\PropertyTable::getList([
                'filter' => ['IBLOCK_ID' => $IBLOCK_ID],
                'select' => ['NAME', 'CODE', 'IS_REQUIRED']
            ])->fetchAll();
            foreach($props as $prop){
                $propCode   = $prop['CODE'];
                $propName   = $prop['NAME'];
                $propVal    = trim(strip_tags(strVal($REQUEST[mb_strtolower($propCode)] ?? "")));
                
                if(isset($arProps["#PROPERTY_$propCode#"])) continue;
                if($prop['IS_REQUIRED'] == "Y" && empty($propVal)) throw new Exception("Укажите: $propName");
                $arProps["#PROPERTY_$propCode#"] = $propVal;
            }

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
                'zayavlenie_fayl_id' => $pdfile1->getId(),
            ];
            $resp->status = "REDIRECT";
        }
    }else{
        $resp->status       = "OK";
        $resp->data->fields = [
            'zayavlenie_fayl_id'=>"0"
        ];
    }
}catch(Exception $exc){
    $resp->status_message = $exc->getMessage();
}
header('Content-Type: application/json');
echo json_encode($resp);
>>>>>>> e0a0eba79 (init)
die;