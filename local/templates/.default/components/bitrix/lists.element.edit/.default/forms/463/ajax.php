<<<<<<< HEAD
<?
define('NEED_AUTH', true);
define('NA_VIZU',       "67822e15e4d0b1fc251801406dbc3770");
define('S_MESTA_RABOTY',"3b0dd611a5b03c415baa613dcbb06480");
define('S_OSTAJE',      "051db25c2242b2603a4650408b81f94a");

include $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

global $userFields;
$resp = (object)['status'=>"ERROR", 'status_message'=>"", 'data'=>(object)[]];
try{
    \Bitrix\Main\Loader::includeModule('iblock');
    \Bitrix\Main\Loader::includeModule('citto.filesigner');
    \Bitrix\Main\Loader::includeModule('intranet');
    $REQUEST = json_decode(file_get_contents('php://input'), true);

    $SOTRUDNIK              = $userFields($USER->GetId());
    $IBLOCK_ID              = $REQUEST['iblock_id'] ?? NULL;
    $SPRAVKA                = $REQUEST['spravka']   ?? NULL;
    $SPRAVKA_O_SREDNEY_ZP   = $REQUEST['spravka_o_sredney_zp']   ?? NULL;
    $TSEL                   = $REQUEST['tsel']   ?? NULL;
    $SHABLON                = NULL;
    $spravkaVar             = NULL;

    $arDepartmentsJudge = CIntranetUtils::GetIBlockSectionChildren(2229);
    $el = new CIBlockElement;
    $arUsersJudge = [];
    $obUsers = CUser::GetList($by = 'LAST_NAME', $order = 'ASC', ['ID' => $USER->GetId()], ['SELECT' => ['UF_DEPARTMENT']]);
    while ($arUser = $obUsers->getNext()) {
        foreach ($arUser['UF_DEPARTMENT'] as $iDepartID) {
            if (in_array($iDepartID, $arDepartmentsJudge)) {
                array_push($arUsersJudge, $arUser['ID']);
            }
        }
    }
    $bIsJudge = (in_array($USER->GetId(), $arUsersJudge));

    if($REQUEST['sessid'] != bitrix_sessid()) throw new Exception('Ошибка. Обновите страницу');
    if(empty($IBLOCK_ID)) throw new Exception('IBLOCK_ID не найден');

    if(empty($SPRAVKA)) throw new Exception('Заполните "Справка"');
    $spravkaVar = CIBlockProperty::GetPropertyEnum('SPRAVKA', [], ['IBLOCK_ID'=>$IBLOCK_ID, 'ID'=>$SPRAVKA])->fetch();
    if(empty($spravkaVar)) throw new Exception('Неверно "Справка"');

    if($spravkaVar['XML_ID'] == NA_VIZU){
        if(empty($SPRAVKA_O_SREDNEY_ZP)) throw new Exception('Приложите "Справка о средней з/плате за 3-месяца из ЦБ"');
    }
    if(empty($TSEL)) throw new Exception('Заполните "Цель (указать в родительном падеже)"');
    
    $SHABLON = \Citto\Filesigner\ShablonyTable::getScalar([
        'filter'=> ['=CODE' => $IBLOCK_ID."_".$spravkaVar['XML_ID']],
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
            $arFields = [
                '#SOTRUDNIK__FIO_ROD#' => $SOTRUDNIK['FIO_ROD'],
                '#SOTRUDNIK__DOLJNOST_ROD#' => $SOTRUDNIK['DOLJNOST_ROD'],
                '#DATE#' => date('d.m.Y'),
                '#TSEL#' => $TSEL
            ];

            if ($bIsJudge) {
                $arFields['#HEADER_DOLJNOST#'] = 'Председателю комитета<br>по делам записи актов гражданского состояния<br>и обеспечению деятельности мировых судей<br>в Тульской области';
                $arFields['#HEADER_FIO#'] = 'Абросимовой Т.А.';
            } else {
                $arFields['#HEADER_DOLJNOST#'] = 'Заместителю Губернатора<br>Тульской области – руководителю<br>аппарата правительства Тульской<br>области – начальнику главного<br>управления государственной<br>службы и кадров аппарата<br>правительства Тульской области';
                $arFields['#HEADER_FIO#'] = 'Якушкиной Г.И.';
            }

            $doc_content = str_replace(
                array_keys($arFields),
                array_values($arFields),
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
define('NA_VIZU',       "67822e15e4d0b1fc251801406dbc3770");
define('S_MESTA_RABOTY',"3b0dd611a5b03c415baa613dcbb06480");
define('S_OSTAJE',      "051db25c2242b2603a4650408b81f94a");

include $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

global $userFields;
$resp = (object)['status'=>"ERROR", 'status_message'=>"", 'data'=>(object)[]];
try{
    \Bitrix\Main\Loader::includeModule('iblock');
    \Bitrix\Main\Loader::includeModule('citto.filesigner');
    \Bitrix\Main\Loader::includeModule('intranet');
    $REQUEST = json_decode(file_get_contents('php://input'), true);

    $SOTRUDNIK              = $userFields($USER->GetId());
    $IBLOCK_ID              = $REQUEST['iblock_id'] ?? NULL;
    $SPRAVKA                = $REQUEST['spravka']   ?? NULL;
    $SPRAVKA_O_SREDNEY_ZP   = $REQUEST['spravka_o_sredney_zp']   ?? NULL;
    $TSEL                   = $REQUEST['tsel']   ?? NULL;
    $SHABLON                = NULL;
    $spravkaVar             = NULL;

    $arDepartmentsJudge = CIntranetUtils::GetIBlockSectionChildren(2229);
    $el = new CIBlockElement;
    $arUsersJudge = [];
    $obUsers = CUser::GetList($by = 'LAST_NAME', $order = 'ASC', ['ID' => $USER->GetId()], ['SELECT' => ['UF_DEPARTMENT']]);
    while ($arUser = $obUsers->getNext()) {
        foreach ($arUser['UF_DEPARTMENT'] as $iDepartID) {
            if (in_array($iDepartID, $arDepartmentsJudge)) {
                array_push($arUsersJudge, $arUser['ID']);
            }
        }
    }
    $bIsJudge = (in_array($USER->GetId(), $arUsersJudge));

    if($REQUEST['sessid'] != bitrix_sessid()) throw new Exception('Ошибка. Обновите страницу');
    if(empty($IBLOCK_ID)) throw new Exception('IBLOCK_ID не найден');

    if(empty($SPRAVKA)) throw new Exception('Заполните "Справка"');
    $spravkaVar = CIBlockProperty::GetPropertyEnum('SPRAVKA', [], ['IBLOCK_ID'=>$IBLOCK_ID, 'ID'=>$SPRAVKA])->fetch();
    if(empty($spravkaVar)) throw new Exception('Неверно "Справка"');

    if($spravkaVar['XML_ID'] == NA_VIZU){
        if(empty($SPRAVKA_O_SREDNEY_ZP)) throw new Exception('Приложите "Справка о средней з/плате за 3-месяца из ЦБ"');
    }
    if(empty($TSEL)) throw new Exception('Заполните "Цель (указать в родительном падеже)"');
    
    $SHABLON = \Citto\Filesigner\ShablonyTable::getScalar([
        'filter'=> ['=CODE' => $IBLOCK_ID."_".$spravkaVar['XML_ID']],
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
            $arFields = [
                '#SOTRUDNIK__FIO_ROD#' => $SOTRUDNIK['FIO_ROD'],
                '#SOTRUDNIK__DOLJNOST_ROD#' => $SOTRUDNIK['DOLJNOST_ROD'],
                '#DATE#' => date('d.m.Y'),
                '#TSEL#' => $TSEL
            ];

            if ($bIsJudge) {
                $arFields['#HEADER_DOLJNOST#'] = 'Председателю комитета<br>по делам записи актов гражданского состояния<br>и обеспечению деятельности мировых судей<br>в Тульской области';
                $arFields['#HEADER_FIO#'] = 'Абросимовой Т.А.';
            } else {
                $arFields['#HEADER_DOLJNOST#'] = 'Заместителю Губернатора<br>Тульской области – руководителю<br>аппарата правительства Тульской<br>области – начальнику главного<br>управления государственной<br>службы и кадров аппарата<br>правительства Тульской области';
                $arFields['#HEADER_FIO#'] = 'Якушкиной Г.И.';
            }

            $doc_content = str_replace(
                array_keys($arFields),
                array_values($arFields),
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