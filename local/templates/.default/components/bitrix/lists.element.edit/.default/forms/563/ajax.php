<<<<<<< HEAD
<?
define('NEED_AUTH', true);

include $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

global $userFields;
$resp = (object)['status'=>"ERROR", 'status_message'=>"", 'data'=>(object)[]];
try{
    \Bitrix\Main\Loader::includeModule('iblock');
    \Bitrix\Main\Loader::includeModule('citto.filesigner');
    $REQUEST            = json_decode(file_get_contents('php://input'), true);
    $SOTRUDNIK          = $userFields($USER->GetId());
    $IBLOCK_ID          = $REQUEST['iblock_id']         ?? NULL;
    $TIP_DOLZHNOSTI     = $REQUEST['tip_dolzhnosti']    ?? NULL;
    $PROSHU_PREDOSTAVIT = $REQUEST['proshu_predostavit']?? NULL;
    $filesSigned        = $REQUEST['filesSigned']       ?? NULL;
    $tipDolzhnosti      = NULL;
    $proshuPredostavit  = NULL;


    if($REQUEST['sessid'] != bitrix_sessid()) throw new Exception('Ошибка. Обновите страницу');
    if(empty($IBLOCK_ID)) throw new Exception('IBLOCK_ID не найден');
    
    if(empty($TIP_DOLZHNOSTI)) throw new Exception('Заполните "Тип должности"');
    $tipDolzhnosti = CIBlockProperty::GetPropertyEnum('TIP_DOLZHNOSTI', [], ['IBLOCK_ID'=>$IBLOCK_ID, 'ID'=>(int)$TIP_DOLZHNOSTI])->fetch();
    if(empty($tipDolzhnosti)) throw new Exception('Неверно "Тип должности"');
    
    if(empty($PROSHU_PREDOSTAVIT)) throw new Exception('Заполните "Прошу предоставить"');
    $proshuPredostavit = CIBlockProperty::GetPropertyEnum('PROSHU_PREDOSTAVIT', [], ['IBLOCK_ID'=>$IBLOCK_ID, 'ID'=>(int)$PROSHU_PREDOSTAVIT])->fetch();
    if(empty($proshuPredostavit)) throw new Exception('Неверно "Прошу предоставить"');

    if($proshuPredostavit['XML_ID'] == "edinovremennuyu_vyplatu"){
        if(empty(trim($REQUEST['data_prikaza']))) throw new Exception("Введите Дата приказа");
        if(empty(trim($REQUEST['nomer_prikaza']))) throw new Exception("Введите Номер приказа");
        if(empty(trim($REQUEST['data_nachala_otpuska']))) throw new Exception("Введите Дата начала отпуска");
    }
    if($filesSigned){
        $resp->status       = "OK";
        $resp->data->fields = [];
    }else{
        $SHABLON = \Citto\Filesigner\ShablonyTable::getScalar([
            'filter'=> ['=CODE' => $IBLOCK_ID."_".$tipDolzhnosti['XML_ID']."_".$proshuPredostavit['XML_ID']],
            'limit' => 1,
            'select'=> ['SHABLON']
        ]);
        if(empty($SHABLON)) throw new Exception("Не удалось найти шаблон заявления");
        $arProps = [
            '#FIO#'                 => $SOTRUDNIK['FIO_ROD'],
            '#DOLJNOST#'            => $GLOBALS['mb_lcfirst']($SOTRUDNIK['DOLJNOST_ROD']),
            '#ORGAN#'               => $SOTRUDNIK['DEPARTMENT'],
            '#DATE#'                => date('d.m.Y'),
            '#YEAR#'                => date('Y'),
        ];
        
        $doc_content = str_replace(
            array_keys($arProps),
            array_values($arProps),
            $SHABLON
        );
        
        $pdfile1 = new \Citto\Filesigner\PDFile();
        $pdfile1->setClearf(['#PODPIS1#', '#PODPIS2#']);
        $pdfile1->setName("Заявление (".$proshuPredostavit['VALUE'].").pdf");
        $pdfile1->insert($doc_content);
        $pdfile1->save();
    
        $resp->data->location = '/podpis-fayla/?'.http_build_query([
            'FILES' => [$pdfile1->getId()],
            'POS'   => "#PODPIS1#",
            'CLEARF'=> ['#PODPIS1#', '#PODPIS2#'],
            'sessid'=> bitrix_sessid()
        ]);
        $resp->data->fields     = [
            'zayavlenie_fayl_id' => $pdfile1->getId(),
        ];


        $SHABLON = \Citto\Filesigner\ShablonyTable::getScalar([
            'filter'=> ['=CODE' => $IBLOCK_ID."_REESTR_".$tipDolzhnosti['XML_ID']."_".$proshuPredostavit['XML_ID']],
            'limit' => 1,
            'select'=> ['SHABLON']
        ]);
        if(empty($SHABLON)) throw new Exception("Не удалось найти шаблон реестра");
        $arProps = [
            '#DATE#'                => date('d.m.Y'),
            '#YEAR#'                => date('Y'),
            '#TABLE#'               => '<table style="width:500px; border-collapse: collapse;">
                                            <tr>
                                                <th style="border:1px solid #000; text-align:center;">Фамилия И.О.</th>
                                                <th style="border:1px solid #000; text-align:center;">Должность</th>
                                            </tr>
                                            <tr>
                                                <td style="border:1px solid #000; text-align:center;">'.$SOTRUDNIK['FIO'].'</td>
                                                <td style="border:1px solid #000; text-align:center;">'.$SOTRUDNIK['DOLJNOST'].'</td>
                                            </tr>
                                        </table>'
        ];
        
        $doc_content = str_replace(
            array_keys($arProps),
            array_values($arProps),
            $SHABLON
        );
        
        $pdfile1 = new \Citto\Filesigner\PDFile();
        $pdfile1->setClearf(['#PODPIS1#', '#PODPIS2#']);
        $pdfile1->setName("Реестр заявок на ".$proshuPredostavit['VALUE']." Аппарата уполномоченных.pdf");
        $pdfile1->insert($doc_content);
        $pdfile1->save();
        
        $resp->data->fields['reestr_fayl_id'] = $pdfile1->getId();

        
        $resp->status = "REDIRECT";
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
    $REQUEST            = json_decode(file_get_contents('php://input'), true);
    $SOTRUDNIK          = $userFields($USER->GetId());
    $IBLOCK_ID          = $REQUEST['iblock_id']         ?? NULL;
    $TIP_DOLZHNOSTI     = $REQUEST['tip_dolzhnosti']    ?? NULL;
    $PROSHU_PREDOSTAVIT = $REQUEST['proshu_predostavit']?? NULL;
    $filesSigned        = $REQUEST['filesSigned']       ?? NULL;
    $tipDolzhnosti      = NULL;
    $proshuPredostavit  = NULL;


    if($REQUEST['sessid'] != bitrix_sessid()) throw new Exception('Ошибка. Обновите страницу');
    if(empty($IBLOCK_ID)) throw new Exception('IBLOCK_ID не найден');
    
    if(empty($TIP_DOLZHNOSTI)) throw new Exception('Заполните "Тип должности"');
    $tipDolzhnosti = CIBlockProperty::GetPropertyEnum('TIP_DOLZHNOSTI', [], ['IBLOCK_ID'=>$IBLOCK_ID, 'ID'=>(int)$TIP_DOLZHNOSTI])->fetch();
    if(empty($tipDolzhnosti)) throw new Exception('Неверно "Тип должности"');
    
    if(empty($PROSHU_PREDOSTAVIT)) throw new Exception('Заполните "Прошу предоставить"');
    $proshuPredostavit = CIBlockProperty::GetPropertyEnum('PROSHU_PREDOSTAVIT', [], ['IBLOCK_ID'=>$IBLOCK_ID, 'ID'=>(int)$PROSHU_PREDOSTAVIT])->fetch();
    if(empty($proshuPredostavit)) throw new Exception('Неверно "Прошу предоставить"');

    if($proshuPredostavit['XML_ID'] == "edinovremennuyu_vyplatu"){
        if(empty(trim($REQUEST['data_prikaza']))) throw new Exception("Введите Дата приказа");
        if(empty(trim($REQUEST['nomer_prikaza']))) throw new Exception("Введите Номер приказа");
        if(empty(trim($REQUEST['data_nachala_otpuska']))) throw new Exception("Введите Дата начала отпуска");
    }
    if($filesSigned){
        $resp->status       = "OK";
        $resp->data->fields = [];
    }else{
        $SHABLON = \Citto\Filesigner\ShablonyTable::getScalar([
            'filter'=> ['=CODE' => $IBLOCK_ID."_".$tipDolzhnosti['XML_ID']."_".$proshuPredostavit['XML_ID']],
            'limit' => 1,
            'select'=> ['SHABLON']
        ]);
        if(empty($SHABLON)) throw new Exception("Не удалось найти шаблон заявления");
        $arProps = [
            '#FIO#'                 => $SOTRUDNIK['FIO_ROD'],
            '#DOLJNOST#'            => $GLOBALS['mb_lcfirst']($SOTRUDNIK['DOLJNOST_ROD']),
            '#ORGAN#'               => $SOTRUDNIK['DEPARTMENT'],
            '#DATE#'                => date('d.m.Y'),
            '#YEAR#'                => date('Y'),
        ];
        
        $doc_content = str_replace(
            array_keys($arProps),
            array_values($arProps),
            $SHABLON
        );
        
        $pdfile1 = new \Citto\Filesigner\PDFile();
        $pdfile1->setClearf(['#PODPIS1#', '#PODPIS2#']);
        $pdfile1->setName("Заявление (".$proshuPredostavit['VALUE'].").pdf");
        $pdfile1->insert($doc_content);
        $pdfile1->save();
    
        $resp->data->location = '/podpis-fayla/?'.http_build_query([
            'FILES' => [$pdfile1->getId()],
            'POS'   => "#PODPIS1#",
            'CLEARF'=> ['#PODPIS1#', '#PODPIS2#'],
            'sessid'=> bitrix_sessid()
        ]);
        $resp->data->fields     = [
            'zayavlenie_fayl_id' => $pdfile1->getId(),
        ];


        $SHABLON = \Citto\Filesigner\ShablonyTable::getScalar([
            'filter'=> ['=CODE' => $IBLOCK_ID."_REESTR_".$tipDolzhnosti['XML_ID']."_".$proshuPredostavit['XML_ID']],
            'limit' => 1,
            'select'=> ['SHABLON']
        ]);
        if(empty($SHABLON)) throw new Exception("Не удалось найти шаблон реестра");
        $arProps = [
            '#DATE#'                => date('d.m.Y'),
            '#YEAR#'                => date('Y'),
            '#TABLE#'               => '<table style="width:500px; border-collapse: collapse;">
                                            <tr>
                                                <th style="border:1px solid #000; text-align:center;">Фамилия И.О.</th>
                                                <th style="border:1px solid #000; text-align:center;">Должность</th>
                                            </tr>
                                            <tr>
                                                <td style="border:1px solid #000; text-align:center;">'.$SOTRUDNIK['FIO'].'</td>
                                                <td style="border:1px solid #000; text-align:center;">'.$SOTRUDNIK['DOLJNOST'].'</td>
                                            </tr>
                                        </table>'
        ];
        
        $doc_content = str_replace(
            array_keys($arProps),
            array_values($arProps),
            $SHABLON
        );
        
        $pdfile1 = new \Citto\Filesigner\PDFile();
        $pdfile1->setClearf(['#PODPIS1#', '#PODPIS2#']);
        $pdfile1->setName("Реестр заявок на ".$proshuPredostavit['VALUE']." Аппарата уполномоченных.pdf");
        $pdfile1->insert($doc_content);
        $pdfile1->save();
        
        $resp->data->fields['reestr_fayl_id'] = $pdfile1->getId();

        
        $resp->status = "REDIRECT";
    }
}catch(Exception $exc){
    $resp->status_message = $exc->getMessage();
}
header('Content-Type: application/json');
echo json_encode($resp);
>>>>>>> e0a0eba79 (init)
die;