<<<<<<< HEAD
<?

define('NEED_AUTH', true);
define('ED_VYPLATU', '7ddd7fca65ef48ea210c27131afefafd');

include $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

global $userFields;
$resp = (object)['status'=>'ERROR', 'status_message'=>'', 'data'=>(object)[]];

try {
    \Bitrix\Main\Loader::includeModule('iblock');
    \Bitrix\Main\Loader::includeModule('citto.filesigner');
    $REQUEST            = json_decode(file_get_contents('php://input'), true);
    $SOTRUDNIK          = $userFields($USER->GetId());
    $IBLOCK_ID          = $REQUEST['iblock_id']         ?? null;
    $TIP_DOLZHNOSTI     = $REQUEST['tip_dolzhnosti']    ?? null;
    $PROSHU_PREDOSTAVIT = $REQUEST['proshu_predostavit']?? null;
    $tipDolzhnosti      = null;
    $proshuPredostavit  = null;


    if ($REQUEST['sessid'] != bitrix_sessid()) {
        throw new Exception('Ошибка. Обновите страницу');
    }
    if (empty($IBLOCK_ID)) {
        throw new Exception('IBLOCK_ID не найден');
    }
    
    if (empty($TIP_DOLZHNOSTI)) {
        throw new Exception('Заполните "Тип должности"');
    }
    $tipDolzhnosti = CIBlockProperty::GetPropertyEnum('TIP_DOLZHNOSTI', [], ['IBLOCK_ID'=>$IBLOCK_ID, 'ID'=>(int)$TIP_DOLZHNOSTI])->fetch();
    if (empty($tipDolzhnosti)) {
        throw new Exception('Неверно "Тип должности"');
    }
    
    if (empty($PROSHU_PREDOSTAVIT)) {
        throw new Exception('Заполните "Прошу предоставить"');
    }
    $proshuPredostavit = CIBlockProperty::GetPropertyEnum('PROSHU_PREDOSTAVIT', [], ['IBLOCK_ID'=>$IBLOCK_ID, 'ID'=>(int)$PROSHU_PREDOSTAVIT])->fetch();
    if (empty($proshuPredostavit)) {
        throw new Exception('Неверно "Прошу предоставить"');
    }

    if ($proshuPredostavit['XML_ID'] == ED_VYPLATU) {
        if (empty(trim($REQUEST['data_prikaza']))) {
            throw new Exception('Введите Дата приказа');
        }
        if (empty(trim($REQUEST['nomer_prikaza']))) {
            throw new Exception('Введите Номер приказа');
        }
        if (empty(trim($REQUEST['data_nachala_otpuska']))) {
            throw new Exception('Введите Дата начала отпуска');
        }
    }

    if (empty(trim($REQUEST['ruk_oiv_org']))) {
        throw new Exception('Укажите "Руководитель ОИВ/Организации"');
    }

    $SHABLON = \Citto\Filesigner\ShablonyTable::getScalar([
        'filter'=> ['=CODE' => $IBLOCK_ID.'_'.$tipDolzhnosti['ID'].'_'.$proshuPredostavit['ID']],
        'limit' => 1,
        'select'=> ['SHABLON']
    ]);

    if ($SHABLON) {
        if ($filesSigned) {
            $resp->status       = 'OK';
            $resp->data->fields = [
                'tsel' => $TSEL,
            ];
        } else {
            $arProps = [
                '#FIO#'                 => $SOTRUDNIK['FIO_ROD'],
                '#DOLJNOST#'            => $GLOBALS['mb_lcfirst']($SOTRUDNIK['DOLJNOST_ROD']),
                '#ORGAN#'               => $SOTRUDNIK['DEPARTMENT'],
                '#DATE#'                => date('d.m.Y'),
                '#TIP_DOLZHNOSTI#'      => $tipDolzhnosti['VALUE'],
                '#PROSHU_PREDOSTAVIT#'  => $proshuPredostavit['VALUE'],
            ];
            
            $doc_content = str_replace(
                array_keys($arProps),
                array_values($arProps),
                $SHABLON
            );
            
            $pdfile1 = new \Citto\Filesigner\PDFile();
            $pdfile1->setClearf(['#PODPIS1#', '#PODPIS2#']);
            $pdfile1->setName('Заявление.pdf');
            $pdfile1->insert($doc_content);
            $pdfile1->save();
        
            $src = '/podpis-fayla/?'.http_build_query([
                'FILES' => [$pdfile1->getId()],
                'POS'   => '#PODPIS1#',
                'CLEARF'=> ['#PODPIS1#', '#PODPIS2#'],
                'sessid'=> bitrix_sessid()
            ]);
            $resp->data->location   = $src;
            $resp->data->fields     = [
                'zayavlenie_fayl_id' => $pdfile1->getId(),
            ];
            $resp->status = 'REDIRECT';
        }
    } else {
        $resp->status       = 'OK';
        $resp->data->fields = [
            'zayavlenie_fayl_id'=>'0'
        ];
    }
} catch (Exception $exc) {
    $resp->status_message = $exc->getMessage();
}
header('Content-Type: application/json');
echo json_encode($resp);
die;
=======
<?

define('NEED_AUTH', true);
define('ED_VYPLATU', '7ddd7fca65ef48ea210c27131afefafd');

include $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

global $userFields;
$resp = (object)['status'=>'ERROR', 'status_message'=>'', 'data'=>(object)[]];

try {
    \Bitrix\Main\Loader::includeModule('iblock');
    \Bitrix\Main\Loader::includeModule('citto.filesigner');
    $REQUEST            = json_decode(file_get_contents('php://input'), true);
    $SOTRUDNIK          = $userFields($USER->GetId());
    $IBLOCK_ID          = $REQUEST['iblock_id']         ?? null;
    $TIP_DOLZHNOSTI     = $REQUEST['tip_dolzhnosti']    ?? null;
    $PROSHU_PREDOSTAVIT = $REQUEST['proshu_predostavit']?? null;
    $tipDolzhnosti      = null;
    $proshuPredostavit  = null;


    if ($REQUEST['sessid'] != bitrix_sessid()) {
        throw new Exception('Ошибка. Обновите страницу');
    }
    if (empty($IBLOCK_ID)) {
        throw new Exception('IBLOCK_ID не найден');
    }
    
    if (empty($TIP_DOLZHNOSTI)) {
        throw new Exception('Заполните "Тип должности"');
    }
    $tipDolzhnosti = CIBlockProperty::GetPropertyEnum('TIP_DOLZHNOSTI', [], ['IBLOCK_ID'=>$IBLOCK_ID, 'ID'=>(int)$TIP_DOLZHNOSTI])->fetch();
    if (empty($tipDolzhnosti)) {
        throw new Exception('Неверно "Тип должности"');
    }
    
    if (empty($PROSHU_PREDOSTAVIT)) {
        throw new Exception('Заполните "Прошу предоставить"');
    }
    $proshuPredostavit = CIBlockProperty::GetPropertyEnum('PROSHU_PREDOSTAVIT', [], ['IBLOCK_ID'=>$IBLOCK_ID, 'ID'=>(int)$PROSHU_PREDOSTAVIT])->fetch();
    if (empty($proshuPredostavit)) {
        throw new Exception('Неверно "Прошу предоставить"');
    }

    if ($proshuPredostavit['XML_ID'] == ED_VYPLATU) {
        if (empty(trim($REQUEST['data_prikaza']))) {
            throw new Exception('Введите Дата приказа');
        }
        if (empty(trim($REQUEST['nomer_prikaza']))) {
            throw new Exception('Введите Номер приказа');
        }
        if (empty(trim($REQUEST['data_nachala_otpuska']))) {
            throw new Exception('Введите Дата начала отпуска');
        }
    }

    if (empty(trim($REQUEST['ruk_oiv_org']))) {
        throw new Exception('Укажите "Руководитель ОИВ/Организации"');
    }

    $SHABLON = \Citto\Filesigner\ShablonyTable::getScalar([
        'filter'=> ['=CODE' => $IBLOCK_ID.'_'.$tipDolzhnosti['ID'].'_'.$proshuPredostavit['ID']],
        'limit' => 1,
        'select'=> ['SHABLON']
    ]);

    if ($SHABLON) {
        if ($filesSigned) {
            $resp->status       = 'OK';
            $resp->data->fields = [
                'tsel' => $TSEL,
            ];
        } else {
            $arProps = [
                '#FIO#'                 => $SOTRUDNIK['FIO_ROD'],
                '#DOLJNOST#'            => $GLOBALS['mb_lcfirst']($SOTRUDNIK['DOLJNOST_ROD']),
                '#ORGAN#'               => $SOTRUDNIK['DEPARTMENT'],
                '#DATE#'                => date('d.m.Y'),
                '#TIP_DOLZHNOSTI#'      => $tipDolzhnosti['VALUE'],
                '#PROSHU_PREDOSTAVIT#'  => $proshuPredostavit['VALUE'],
            ];
            
            $doc_content = str_replace(
                array_keys($arProps),
                array_values($arProps),
                $SHABLON
            );
            
            $pdfile1 = new \Citto\Filesigner\PDFile();
            $pdfile1->setClearf(['#PODPIS1#', '#PODPIS2#']);
            $pdfile1->setName('Заявление.pdf');
            $pdfile1->insert($doc_content);
            $pdfile1->save();
        
            $src = '/podpis-fayla/?'.http_build_query([
                'FILES' => [$pdfile1->getId()],
                'POS'   => '#PODPIS1#',
                'CLEARF'=> ['#PODPIS1#', '#PODPIS2#'],
                'sessid'=> bitrix_sessid()
            ]);
            $resp->data->location   = $src;
            $resp->data->fields     = [
                'zayavlenie_fayl_id' => $pdfile1->getId(),
            ];
            $resp->status = 'REDIRECT';
        }
    } else {
        $resp->status       = 'OK';
        $resp->data->fields = [
            'zayavlenie_fayl_id'=>'0'
        ];
    }
} catch (Exception $exc) {
    $resp->status_message = $exc->getMessage();
}
header('Content-Type: application/json');
echo json_encode($resp);
die;
>>>>>>> e0a0eba79 (init)
