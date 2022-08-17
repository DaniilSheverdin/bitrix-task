<<<<<<< HEAD
<?
define('NEED_AUTH', true);
define('XLS_TEMPLATE', $_SERVER['DOCUMENT_ROOT']."/upload/propusk_shablon.xlsx");

include $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php';

global $userFields;
$resp = (object)['status'=>"ERROR", 'status_message'=>"", 'data'=>(object)[], 'alert' => ""];
try{
    \Bitrix\Main\Loader::includeModule('iblock');
    $REQUEST            = $_REQUEST;
    $SOTRUDNIK          = $userFields($USER->GetId());
    $IBLOCK_ID          = $REQUEST['iblock_id']             ?? NULL;
    $IBLOCK_SECTION_ID  = $REQUEST['iblock_section_id']     ?? NULL;
    $TEMA               = trim($REQUEST['tema'] ?? "");
    $DETAIL_TEXT        = trim($REQUEST['detail_text'] ?? "");

    if($REQUEST['sessid'] != bitrix_sessid()) throw new Exception('Ошибка. Обновите страницу');
    if(empty($IBLOCK_ID)) throw new Exception('IBLOCK_ID не найден');
    if(empty($IBLOCK_SECTION_ID)) throw new Exception('Выберите тему');
    if(empty($DETAIL_TEXT)) throw new Exception('Введите "Подробное описание"');

    if(!empty($_FILES)){
        foreach($_FILES as $file){
            if($file['size'] > 15728640) throw new Exception('Размер файла: "'.htmlentities($file['name']).'" превышает допустимый(15 мегабайт)');
        }
    }
    $arTema = [];
    $res = CIBlockSection::GetNavChain(false, $IBLOCK_SECTION_ID, ['ID', 'CODE', 'NAME']);
    while($ob = $res->fetch()){
        $arTema[$ob['CODE']] = $ob['NAME'];
    }
    if(empty($arTema)) throw new Exception("Тема не найдена");
    if(key($arTema) == "ccdrugoe"){
        if(empty($TEMA)) throw new Exception("Введите тему вручную");
    }else{
        $TEMA = implode(" / ", $arTema);
    }

    
    $resp->data->fields = [
        'tema' => $TEMA,
    ];
    $resp->status = "OK";
    $resp->alert    = nl2br("<div style='font-size:16px'>Ваше обращение отправлено на почту Технической поддержки omnitracker@tularegion.ru.
                            После обработки обращения на Вашу почту поступит сообщение с номером зарегистрированного обращения.
                            Дальнейшее отслеживание статусов будет происходить по почте или Вы можете уточнить статус обращения, позвонив на линию технической поддержки по номеру 24-83-83</div>");
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
$resp = (object)['status'=>"ERROR", 'status_message'=>"", 'data'=>(object)[], 'alert' => ""];
try{
    \Bitrix\Main\Loader::includeModule('iblock');
    $REQUEST            = $_REQUEST;
    $SOTRUDNIK          = $userFields($USER->GetId());
    $IBLOCK_ID          = $REQUEST['iblock_id']             ?? NULL;
    $IBLOCK_SECTION_ID  = $REQUEST['iblock_section_id']     ?? NULL;
    $TEMA               = trim($REQUEST['tema'] ?? "");
    $DETAIL_TEXT        = trim($REQUEST['detail_text'] ?? "");

    if($REQUEST['sessid'] != bitrix_sessid()) throw new Exception('Ошибка. Обновите страницу');
    if(empty($IBLOCK_ID)) throw new Exception('IBLOCK_ID не найден');
    if(empty($IBLOCK_SECTION_ID)) throw new Exception('Выберите тему');
    if(empty($DETAIL_TEXT)) throw new Exception('Введите "Подробное описание"');

    if(!empty($_FILES)){
        foreach($_FILES as $file){
            if($file['size'] > 15728640) throw new Exception('Размер файла: "'.htmlentities($file['name']).'" превышает допустимый(15 мегабайт)');
        }
    }
    $arTema = [];
    $res = CIBlockSection::GetNavChain(false, $IBLOCK_SECTION_ID, ['ID', 'CODE', 'NAME']);
    while($ob = $res->fetch()){
        $arTema[$ob['CODE']] = $ob['NAME'];
    }
    if(empty($arTema)) throw new Exception("Тема не найдена");
    if(key($arTema) == "ccdrugoe"){
        if(empty($TEMA)) throw new Exception("Введите тему вручную");
    }else{
        $TEMA = implode(" / ", $arTema);
    }

    
    $resp->data->fields = [
        'tema' => $TEMA,
    ];
    $resp->status = "OK";
    $resp->alert    = nl2br("<div style='font-size:16px'>Ваше обращение отправлено на почту Технической поддержки omnitracker@tularegion.ru.
                            После обработки обращения на Вашу почту поступит сообщение с номером зарегистрированного обращения.
                            Дальнейшее отслеживание статусов будет происходить по почте или Вы можете уточнить статус обращения, позвонив на линию технической поддержки по номеру 24-83-83</div>");
}catch(Exception $exc){
    $resp->status_message = $exc->getMessage();
}
header('Content-Type: application/json');
echo json_encode($resp);
>>>>>>> e0a0eba79 (init)
die;