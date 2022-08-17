<<<<<<< HEAD
<?
define('NEED_AUTH', true);
define('KOPIYA_DOKUMENTA_TK', "e8bb8d61d5f545d3d06015184b8ee2eb");
define('DLYA_PREDOSTAVLENIYA_IN', "5b77cfa6840f4766482be6329a63f002");
include $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

global $userFields;
$resp = (object)['status'=>"ERROR", 'status_message'=>"", 'data'=>(object)[]];
try{
    \Bitrix\Main\Loader::includeModule('iblock');
    \Bitrix\Main\Loader::includeModule('citto.filesigner');
    \Bitrix\Main\Loader::includeModule('intranet');
    $REQUEST = json_decode(file_get_contents('php://input'), true);

    $IBLOCK_ID                          = $REQUEST['iblock_id']                            ?? NULL;
    $KOPIYA_DOKUMENTA                   = $REQUEST['kopiya_dokumenta']                     ?? NULL;
    $DLYA_PREDOSTAVLENIYA               = $REQUEST['dlya_predostavleniya']                 ?? NULL;
    $filesSigned                        = $REQUEST['filesSigned']                          ?? NULL;
    $DLYA_PREDOSTAVLENIYA_STROKA        = trim($REQUEST['dlya_predostavleniya_stroka']     ?? "");
    $NAZVANIE_DOKUMENTA                 = trim($REQUEST['nazvanie_dokumenta']              ?? "");
    $SOTRUDNIK                          = $userFields($USER->GetId());
    $dlyaPredostavleniya                = NULL;
    $kopiyaDokumenta                    = NULL;
    $SHABLON                            = NULL;

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

    if(empty($KOPIYA_DOKUMENTA)) throw new Exception('Заполните "Копия докемента"');
    $kopiyaDokumenta = CIBlockProperty::GetPropertyEnum('KOPIYA_DOKUMENTA', [], ['IBLOCK_ID'=>$IBLOCK_ID, 'ID'=>$KOPIYA_DOKUMENTA])->fetch();
    if(empty($kopiyaDokumenta)) throw new Exception('Неверно "Копия докемента"');

    if($kopiyaDokumenta['XML_ID'] == KOPIYA_DOKUMENTA_TK){
        

        if($filesSigned){
            $resp->status       = "OK";
            $resp->data->fields = [
                'dlya_predostavleniya_stroka' => $DLYA_PREDOSTAVLENIYA_STROKA,
            ];
        }else{
            if(empty($DLYA_PREDOSTAVLENIYA)) throw new Exception('Заполните "Для предоставления"');
            $dlyaPredostavleniya = CIBlockProperty::GetPropertyEnum('DLYA_PREDOSTAVLENIYA', [], ['IBLOCK_ID'=>$IBLOCK_ID, 'ID'=>$DLYA_PREDOSTAVLENIYA])->fetch();
            if(empty($dlyaPredostavleniya)) throw new Exception('Неверно "Для предоставления"');
            
            if($dlyaPredostavleniya['XML_ID'] == DLYA_PREDOSTAVLENIYA_IN){
                if(empty($DLYA_PREDOSTAVLENIYA_STROKA)) throw new Exception('Заполните "Для предоставления"');
                if(mb_strpos($DLYA_PREDOSTAVLENIYA_STROKA, "для предоставления") !== 0){
                    $DLYA_PREDOSTAVLENIYA_STROKA = "для предоставления ".$DLYA_PREDOSTAVLENIYA_STROKA;
                }
            }else{
                $DLYA_PREDOSTAVLENIYA_STROKA = $dlyaPredostavleniya['VALUE'];
            }

            $SHABLON = \Citto\Filesigner\ShablonyTable::getScalar([
                'filter'=> ['=CODE' => $IBLOCK_ID],
                'limit' => 1,
                'select'=> ['SHABLON']
            ]);

            $arFields = [
                '#SOTRUDNIK__FIO_ROD#' => $SOTRUDNIK['FIO_ROD'],
                '#SOTRUDNIK__DOLJNOST_ROD#' => $SOTRUDNIK['DOLJNOST_ROD'],
                '#DATE#' => date('d.m.Y'),
                '#DLYA_PREDOST_STR#' => htmlentities($DLYA_PREDOSTAVLENIYA_STROKA)
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
                'dlya_predostavleniya_stroka' => $DLYA_PREDOSTAVLENIYA_STROKA,
                'zayavlenie_fayl_id'          => $pdfile1->getId(),
            ];
            $resp->status = "REDIRECT";
        }
    }else{
        if(empty($DLYA_PREDOSTAVLENIYA_STROKA)) throw new Exception('Заполните "Для предоставления"');
        if(empty($NAZVANIE_DOKUMENTA)) throw new Exception('Заполните "Название документа"');
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
define('KOPIYA_DOKUMENTA_TK', "e8bb8d61d5f545d3d06015184b8ee2eb");
define('DLYA_PREDOSTAVLENIYA_IN', "5b77cfa6840f4766482be6329a63f002");
include $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

global $userFields;
$resp = (object)['status'=>"ERROR", 'status_message'=>"", 'data'=>(object)[]];
try{
    \Bitrix\Main\Loader::includeModule('iblock');
    \Bitrix\Main\Loader::includeModule('citto.filesigner');
    \Bitrix\Main\Loader::includeModule('intranet');
    $REQUEST = json_decode(file_get_contents('php://input'), true);

    $IBLOCK_ID                          = $REQUEST['iblock_id']                            ?? NULL;
    $KOPIYA_DOKUMENTA                   = $REQUEST['kopiya_dokumenta']                     ?? NULL;
    $DLYA_PREDOSTAVLENIYA               = $REQUEST['dlya_predostavleniya']                 ?? NULL;
    $filesSigned                        = $REQUEST['filesSigned']                          ?? NULL;
    $DLYA_PREDOSTAVLENIYA_STROKA        = trim($REQUEST['dlya_predostavleniya_stroka']     ?? "");
    $NAZVANIE_DOKUMENTA                 = trim($REQUEST['nazvanie_dokumenta']              ?? "");
    $SOTRUDNIK                          = $userFields($USER->GetId());
    $dlyaPredostavleniya                = NULL;
    $kopiyaDokumenta                    = NULL;
    $SHABLON                            = NULL;

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

    if(empty($KOPIYA_DOKUMENTA)) throw new Exception('Заполните "Копия докемента"');
    $kopiyaDokumenta = CIBlockProperty::GetPropertyEnum('KOPIYA_DOKUMENTA', [], ['IBLOCK_ID'=>$IBLOCK_ID, 'ID'=>$KOPIYA_DOKUMENTA])->fetch();
    if(empty($kopiyaDokumenta)) throw new Exception('Неверно "Копия докемента"');

    if($kopiyaDokumenta['XML_ID'] == KOPIYA_DOKUMENTA_TK){
        

        if($filesSigned){
            $resp->status       = "OK";
            $resp->data->fields = [
                'dlya_predostavleniya_stroka' => $DLYA_PREDOSTAVLENIYA_STROKA,
            ];
        }else{
            if(empty($DLYA_PREDOSTAVLENIYA)) throw new Exception('Заполните "Для предоставления"');
            $dlyaPredostavleniya = CIBlockProperty::GetPropertyEnum('DLYA_PREDOSTAVLENIYA', [], ['IBLOCK_ID'=>$IBLOCK_ID, 'ID'=>$DLYA_PREDOSTAVLENIYA])->fetch();
            if(empty($dlyaPredostavleniya)) throw new Exception('Неверно "Для предоставления"');
            
            if($dlyaPredostavleniya['XML_ID'] == DLYA_PREDOSTAVLENIYA_IN){
                if(empty($DLYA_PREDOSTAVLENIYA_STROKA)) throw new Exception('Заполните "Для предоставления"');
                if(mb_strpos($DLYA_PREDOSTAVLENIYA_STROKA, "для предоставления") !== 0){
                    $DLYA_PREDOSTAVLENIYA_STROKA = "для предоставления ".$DLYA_PREDOSTAVLENIYA_STROKA;
                }
            }else{
                $DLYA_PREDOSTAVLENIYA_STROKA = $dlyaPredostavleniya['VALUE'];
            }

            $SHABLON = \Citto\Filesigner\ShablonyTable::getScalar([
                'filter'=> ['=CODE' => $IBLOCK_ID],
                'limit' => 1,
                'select'=> ['SHABLON']
            ]);

            $arFields = [
                '#SOTRUDNIK__FIO_ROD#' => $SOTRUDNIK['FIO_ROD'],
                '#SOTRUDNIK__DOLJNOST_ROD#' => $SOTRUDNIK['DOLJNOST_ROD'],
                '#DATE#' => date('d.m.Y'),
                '#DLYA_PREDOST_STR#' => htmlentities($DLYA_PREDOSTAVLENIYA_STROKA)
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
                'dlya_predostavleniya_stroka' => $DLYA_PREDOSTAVLENIYA_STROKA,
                'zayavlenie_fayl_id'          => $pdfile1->getId(),
            ];
            $resp->status = "REDIRECT";
        }
    }else{
        if(empty($DLYA_PREDOSTAVLENIYA_STROKA)) throw new Exception('Заполните "Для предоставления"');
        if(empty($NAZVANIE_DOKUMENTA)) throw new Exception('Заполните "Название документа"');
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