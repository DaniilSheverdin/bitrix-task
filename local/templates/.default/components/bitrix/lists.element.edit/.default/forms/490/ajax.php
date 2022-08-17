<<<<<<< HEAD
<?
define('NEED_AUTH', true);

include $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

global $userFields, $USER;
$resp = (object)['status'=>"ERROR", 'status_message'=>"", 'data'=>(object)[]];
try{
    \Bitrix\Main\Loader::includeModule('iblock');
    \Bitrix\Main\Loader::includeModule('citto.filesigner');
    \Bitrix\Main\Loader::includeModule('intranet');
    $REQUEST            = json_decode(file_get_contents('php://input'), true);
    $SOTRUDNIK          = $userFields($USER->GetId());
    $IBLOCK_ID          = $REQUEST['iblock_id']                                     ?? NULL;
    $filesSigned        = $REQUEST['filesSigned']                                   ?? NULL;
    $PROSHU_PREDOSTAVIT = $REQUEST['proshu_predostavit_otpusk_bez_sokhraneniya']    ?? NULL;
    $DATA_S             = $REQUEST['data_s']                                        ?? NULL;
    $NA_KOL_VO_DNEY     = $REQUEST['na_kol_vo_kalendarnykh_dney']                   ?? NULL;
    $PRICHINA_V_SVYAZI_S= $REQUEST['prichina_v_svyazi_s']                           ?? NULL;
    $PRICHINA_PODROBNEE = $REQUEST['prichina_podrobnee']                            ?? NULL;
    $RUKOVODITEL_OIV    = $REQUEST['rukovoditel_oiv']                                ?? NULL;

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

    if(empty($IBLOCK_ID))           throw new Exception('IBLOCK_ID не найден');
    if(empty($PROSHU_PREDOSTAVIT))  throw new Exception('Заполните "Прошу предоставить отпуск без сохранения"');
    if(empty($DATA_S))              throw new Exception('Заполните "Дата с"');
    if(empty($NA_KOL_VO_DNEY))      throw new Exception('Заполните "На (кол-во календарных дней)"');
    if(empty($PRICHINA_V_SVYAZI_S)) throw new Exception('Заполните "Причина (в связи с)"');
    if(empty($RUKOVODITEL_OIV))     throw new Exception('Заполните "Руководитель организации/ОИВ"');

 
    if((int)$NA_KOL_VO_DNEY < 1) throw new Exception('Заполните правильно "На (кол-во календарных дней)"');
    $NA_KOL_VO_DNEY = (int)$NA_KOL_VO_DNEY." ".$GLOBALS['declOfNum']((int)$NA_KOL_VO_DNEY, ['календарный день', 'календарных дня', 'календарных дней']);


    $PRICHINA_V_SVYAZI_S = CIBlockProperty::GetPropertyEnum('PRICHINA_V_SVYAZI_S', [], ['IBLOCK_ID' => $IBLOCK_ID, 'ID' => (int)$PRICHINA_V_SVYAZI_S])->fetch();
    if(empty($PRICHINA_V_SVYAZI_S)) throw new Exception('Неверно "Причина (в связи с)"');
    if(in_array($PRICHINA_V_SVYAZI_S['XML_ID'], ["1a55e3f43ed23ecd462580e368544507", "5eae82e832f195ce056b0229e83141f6"])){
        if(empty($PRICHINA_PODROBNEE)) throw new Exception('Заполните "Причина подробнее"');
    }else{
        $PRICHINA_PODROBNEE = $PRICHINA_V_SVYAZI_S['VALUE'];
    }

    $RUKOVODITEL_OIV = $userFields($RUKOVODITEL_OIV);
    if(empty($RUKOVODITEL_OIV)) throw new Exception('Неверно "Руководитель организации/ОИВ"');

    $SHABLON = \Citto\Filesigner\ShablonyTable::getScalar([
        'filter'=> ['=CODE' => $IBLOCK_ID."_".$PROSHU_PREDOSTAVIT],
        'limit' => 1,
        'select'=> ['SHABLON']
    ]);

    if(!$SHABLON) throw new Exception("Шаблон заявления не найден");
    
    if($filesSigned){
        $resp->status       = "OK";
        $resp->data->fields = [];
    }else{
        $arProps = [
            '#DATA#'                        => date('d.m.Y'),
            '#DATA_S#'                      => $DATA_S,
            '#NA_KOL_VO_KALENDARNYKH_DNEY#' => $NA_KOL_VO_DNEY,
            '#PRICHINA_V_SVYAZI_S#'         => $PRICHINA_PODROBNEE,
            '#LAST_NAME#'                   => $SOTRUDNIK['LAST_NAME'],
            '#NAME#'                        => $SOTRUDNIK['FIRST_NAME'],
            '#SECOND_NAME#'                 => $SOTRUDNIK['MIDDLE_NAME'],
            '#FIO_ROD#'                     => $SOTRUDNIK['FIO_ROD'],
            '#DOLJNOST_ROD#'                => $GLOBALS['mb_lcfirst']($SOTRUDNIK['DOLJNOST_ROD']),
            '#PODPIS#'                      => "",
            '#RUKOVODITEL_OIV__DOLJNOST#'   => $GLOBALS['mb_ucfirst']($RUKOVODITEL_OIV['UF_WORK_POSITION']),
            '#RUKOVODITEL__FIO_INIC#'       => $RUKOVODITEL_OIV['FIO_INIC'],
        ];

        if ($bIsJudge) {
            $arProps['#RUKOVODITEL_OIV#'] = '';
            $arProps['#HEADER_DOLJNOST#'] = 'Председателю комитета<br>по делам записи актов гражданского состояния<br>и обеспечению деятельности мировых судей<br>в Тульской области';
            $arProps['#HEADER_FIO#'] = 'Абросимовой Т.А.';
        } else {
            $arProps['#RUKOVODITEL_OIV#'] = '
            <table style="overflow: hidden;margin-bottom: 30px;width:500px;font-size: 14px">
                <tr>
                    <td style="width:250px;line-height: normal;text-align: center;float: left;">
                        <b>'.$GLOBALS['mb_ucfirst']($RUKOVODITEL_OIV['UF_WORK_POSITION']).'</b>
                    </td>
                    <td style="text-align: right;line-height: normal;vertical-align: bottom;">
                        <b>'.$RUKOVODITEL_OIV['FIO_INIC'].'</b>
                    </td>
                </tr>
            </table>';
            $arProps['#HEADER_DOLJNOST#'] = 'Заместителю Губернатора<br>Тульской области – руководителю<br>аппарата Правительства Тульской<br>области – начальнику главного<br>управления государственной<br>службы и кадров аппарата<br>Правительства Тульской области';
            $arProps['#HEADER_FIO#'] = 'Якушкиной Г.И.';
        }

        $doc_content = str_replace(
            array_keys($arProps),
            array_values($arProps),
            $SHABLON
        );
        
        $pdfile1 = new \Citto\Filesigner\PDFile();
        $pdfile1->setClearf(['#PODPIS1#', '#PODPIS2#']);
        $pdfile1->setName("Заявление на отпуск без оплаты ".$SOTRUDNIK['FIO_INIC']);
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
            'prichina_podrobnee' => $PRICHINA_PODROBNEE
        ];
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

global $userFields, $USER;
$resp = (object)['status'=>"ERROR", 'status_message'=>"", 'data'=>(object)[]];
try{
    \Bitrix\Main\Loader::includeModule('iblock');
    \Bitrix\Main\Loader::includeModule('citto.filesigner');
    \Bitrix\Main\Loader::includeModule('intranet');
    $REQUEST            = json_decode(file_get_contents('php://input'), true);
    $SOTRUDNIK          = $userFields($USER->GetId());
    $IBLOCK_ID          = $REQUEST['iblock_id']                                     ?? NULL;
    $filesSigned        = $REQUEST['filesSigned']                                   ?? NULL;
    $PROSHU_PREDOSTAVIT = $REQUEST['proshu_predostavit_otpusk_bez_sokhraneniya']    ?? NULL;
    $DATA_S             = $REQUEST['data_s']                                        ?? NULL;
    $NA_KOL_VO_DNEY     = $REQUEST['na_kol_vo_kalendarnykh_dney']                   ?? NULL;
    $PRICHINA_V_SVYAZI_S= $REQUEST['prichina_v_svyazi_s']                           ?? NULL;
    $PRICHINA_PODROBNEE = $REQUEST['prichina_podrobnee']                            ?? NULL;
    $RUKOVODITEL_OIV    = $REQUEST['rukovoditel_oiv']                                ?? NULL;

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

    if(empty($IBLOCK_ID))           throw new Exception('IBLOCK_ID не найден');
    if(empty($PROSHU_PREDOSTAVIT))  throw new Exception('Заполните "Прошу предоставить отпуск без сохранения"');
    if(empty($DATA_S))              throw new Exception('Заполните "Дата с"');
    if(empty($NA_KOL_VO_DNEY))      throw new Exception('Заполните "На (кол-во календарных дней)"');
    if(empty($PRICHINA_V_SVYAZI_S)) throw new Exception('Заполните "Причина (в связи с)"');
    if(empty($RUKOVODITEL_OIV))     throw new Exception('Заполните "Руководитель организации/ОИВ"');

 
    if((int)$NA_KOL_VO_DNEY < 1) throw new Exception('Заполните правильно "На (кол-во календарных дней)"');
    $NA_KOL_VO_DNEY = (int)$NA_KOL_VO_DNEY." ".$GLOBALS['declOfNum']((int)$NA_KOL_VO_DNEY, ['календарный день', 'календарных дня', 'календарных дней']);


    $PRICHINA_V_SVYAZI_S = CIBlockProperty::GetPropertyEnum('PRICHINA_V_SVYAZI_S', [], ['IBLOCK_ID' => $IBLOCK_ID, 'ID' => (int)$PRICHINA_V_SVYAZI_S])->fetch();
    if(empty($PRICHINA_V_SVYAZI_S)) throw new Exception('Неверно "Причина (в связи с)"');
    if(in_array($PRICHINA_V_SVYAZI_S['XML_ID'], ["1a55e3f43ed23ecd462580e368544507", "5eae82e832f195ce056b0229e83141f6"])){
        if(empty($PRICHINA_PODROBNEE)) throw new Exception('Заполните "Причина подробнее"');
    }else{
        $PRICHINA_PODROBNEE = $PRICHINA_V_SVYAZI_S['VALUE'];
    }

    $RUKOVODITEL_OIV = $userFields($RUKOVODITEL_OIV);
    if(empty($RUKOVODITEL_OIV)) throw new Exception('Неверно "Руководитель организации/ОИВ"');

    $SHABLON = \Citto\Filesigner\ShablonyTable::getScalar([
        'filter'=> ['=CODE' => $IBLOCK_ID."_".$PROSHU_PREDOSTAVIT],
        'limit' => 1,
        'select'=> ['SHABLON']
    ]);

    if(!$SHABLON) throw new Exception("Шаблон заявления не найден");
    
    if($filesSigned){
        $resp->status       = "OK";
        $resp->data->fields = [];
    }else{
        $arProps = [
            '#DATA#'                        => date('d.m.Y'),
            '#DATA_S#'                      => $DATA_S,
            '#NA_KOL_VO_KALENDARNYKH_DNEY#' => $NA_KOL_VO_DNEY,
            '#PRICHINA_V_SVYAZI_S#'         => $PRICHINA_PODROBNEE,
            '#LAST_NAME#'                   => $SOTRUDNIK['LAST_NAME'],
            '#NAME#'                        => $SOTRUDNIK['FIRST_NAME'],
            '#SECOND_NAME#'                 => $SOTRUDNIK['MIDDLE_NAME'],
            '#FIO_ROD#'                     => $SOTRUDNIK['FIO_ROD'],
            '#DOLJNOST_ROD#'                => $GLOBALS['mb_lcfirst']($SOTRUDNIK['DOLJNOST_ROD']),
            '#PODPIS#'                      => "",
            '#RUKOVODITEL_OIV__DOLJNOST#'   => $GLOBALS['mb_ucfirst']($RUKOVODITEL_OIV['UF_WORK_POSITION']),
            '#RUKOVODITEL__FIO_INIC#'       => $RUKOVODITEL_OIV['FIO_INIC'],
        ];

        if ($bIsJudge) {
            $arProps['#RUKOVODITEL_OIV#'] = '';
            $arProps['#HEADER_DOLJNOST#'] = 'Председателю комитета<br>по делам записи актов гражданского состояния<br>и обеспечению деятельности мировых судей<br>в Тульской области';
            $arProps['#HEADER_FIO#'] = 'Абросимовой Т.А.';
        } else {
            $arProps['#RUKOVODITEL_OIV#'] = '
            <table style="overflow: hidden;margin-bottom: 30px;width:500px;font-size: 14px">
                <tr>
                    <td style="width:250px;line-height: normal;text-align: center;float: left;">
                        <b>'.$GLOBALS['mb_ucfirst']($RUKOVODITEL_OIV['UF_WORK_POSITION']).'</b>
                    </td>
                    <td style="text-align: right;line-height: normal;vertical-align: bottom;">
                        <b>'.$RUKOVODITEL_OIV['FIO_INIC'].'</b>
                    </td>
                </tr>
            </table>';
            $arProps['#HEADER_DOLJNOST#'] = 'Заместителю Губернатора<br>Тульской области – руководителю<br>аппарата Правительства Тульской<br>области – начальнику главного<br>управления государственной<br>службы и кадров аппарата<br>Правительства Тульской области';
            $arProps['#HEADER_FIO#'] = 'Якушкиной Г.И.';
        }

        $doc_content = str_replace(
            array_keys($arProps),
            array_values($arProps),
            $SHABLON
        );
        
        $pdfile1 = new \Citto\Filesigner\PDFile();
        $pdfile1->setClearf(['#PODPIS1#', '#PODPIS2#']);
        $pdfile1->setName("Заявление на отпуск без оплаты ".$SOTRUDNIK['FIO_INIC']);
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
            'prichina_podrobnee' => $PRICHINA_PODROBNEE
        ];
        $resp->status = "REDIRECT";
    }
}catch(Exception $exc){
    $resp->status_message = $exc->getMessage();
}
header('Content-Type: application/json');
echo json_encode($resp);
>>>>>>> e0a0eba79 (init)
die;