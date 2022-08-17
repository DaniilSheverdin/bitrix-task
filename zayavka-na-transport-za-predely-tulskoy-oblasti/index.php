<<<<<<< HEAD
<?php
define('NEED_AUTH', true);
define('BP_IBLOCK', 484);
define('BP_TEMPLATE_ID', 365);
$included_window = defined("B_PROLOG_INCLUDED");
if(!$included_window){
     require $_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php";
}
global $APPLICATION,$USER,$userFields,$morph_client,$declOfNum,$mb_lcfirst;
CJSCore::Init(["date"]);
\Bitrix\Main\Loader::includeModule("iblock");

if(isset($_REQUEST['zayavka-na-transport'])){
     $resp = (object)['code'=>"ERROR",'message'=>"Ошибка"];
     try{
          \Bitrix\Main\Loader::includeModule("workflow");
          \Bitrix\Main\Loader::includeModule("bizproc");
          if(!check_bitrix_sessid()) throw new Exception('Проблема с сессией, обновите страницу');

          $el = new CIBlockElement;
          $PROP = [
               'FIO'               => $_REQUEST['FIO']                ? trim($_REQUEST['FIO'])            : NULL,
               'DOLZHNOST'         => $_REQUEST['DOLZHNOST']          ? trim($_REQUEST['DOLZHNOST'])      : NULL,
               'PODRAZDELENIE'     => $_REQUEST['PODRAZDELENIE']      ? trim($_REQUEST['PODRAZDELENIE'])  : NULL,
               'DATA'              => $_REQUEST['DATA']               ? trim($_REQUEST['DATA'])           : NULL,
               'VREMYA_PODACHI'    => $_REQUEST['VREMYA_PODACHI']     ? trim($_REQUEST['VREMYA_PODACHI']) : NULL,
               'MARSHRUT'          => $_REQUEST['MARSHRUT']           ? trim($_REQUEST['MARSHRUT'])       : NULL,
               'ADRES_PODACHI'     => $_REQUEST['ADRES_PODACHI']      ? trim($_REQUEST['ADRES_PODACHI'])  : NULL,
               'TSEL_POEZDKI'      => $_REQUEST['TSEL_POEZDKI']       ? trim($_REQUEST['TSEL_POEZDKI'])   : NULL,
               'ZAPUSTIL_PROTSESS' => $USER->GetId(),
               
               'UROVEN_ZAKAZYVAEMOGO_A_M'    =>
                                                  $_REQUEST['UROVEN_ZAKAZYVAEMOGO_A_M']
                                                  ? trim($_REQUEST['UROVEN_ZAKAZYVAEMOGO_A_M'])
                                                  : "Не указано",
               'DOPOLNITELNYE_POZHELANIYA_PO_ZAPOLNENIYU_AVTOMOBIL'
                                             =>
                                                  $_REQUEST['DOPOLNITELNYE_POZHELANIYA_PO_ZAPOLNENIYU_AVTOMOBIL']
                                                  ? trim($_REQUEST['DOPOLNITELNYE_POZHELANIYA_PO_ZAPOLNENIYU_AVTOMOBIL'])
                                                  : "Нет",
               'POZHELANIYA_PO_MARSHRUTU_DVIZHENIYA'
                                             =>
                                                  $_REQUEST['POZHELANIYA_PO_MARSHRUTU_DVIZHENIYA']
                                                  ? trim($_REQUEST['POZHELANIYA_PO_MARSHRUTU_DVIZHENIYA'])
                                                  : "Нет",
               'VREMYA_ISPOLZOVANIYA_AVTOTRANSPORTA'
                                             =>
                                                  $_REQUEST['VREMYA_ISPOLZOVANIYA_AVTOTRANSPORTA']
                                                  ? trim($_REQUEST['VREMYA_ISPOLZOVANIYA_AVTOTRANSPORTA'])
                                                  : "Не указано",
               'KOLICHESTVO_PASSAZHIROV'     =>
                                                  $_REQUEST['KOLICHESTVO_PASSAZHIROV']
                                                  ? trim($_REQUEST['KOLICHESTVO_PASSAZHIROV'])
                                                  : "Не указано",
               'DOSTAVKA_ILI_VYVOZ_GRUZOV'   =>
                                                  $_REQUEST['DOSTAVKA_ILI_VYVOZ_GRUZOV']
                                                  ? trim($_REQUEST['DOSTAVKA_ILI_VYVOZ_GRUZOV'])
                                                  : NULL,
               'ZAYAVKA_NA_PODVED'   =>
                                                  $_REQUEST['ZAYAVKA_NA_PODVED']
                                                  ? trim($_REQUEST['ZAYAVKA_NA_PODVED'])
                                                  : NULL,
               'TELEFON_DLYA_SVYAZI'         =>
                                                  $_REQUEST['TELEFON_DLYA_SVYAZI']
                                                  ? trim($_REQUEST['TELEFON_DLYA_SVYAZI'])
                                                  : "Не указан",
          ];
     
          $arLoadProductArray = [
               'MODIFIED_BY'        => $USER->GetId(), 
               'IBLOCK_SECTION_ID'  => false, 
               'IBLOCK_ID'          => BP_IBLOCK,
               'PROPERTY_VALUES'    => &$PROP,
               'NAME'               => $GLOBALS['APPLICATION']->GetTitle(),
               'ACTIVE'             => "Y", 
               'PREVIEW_TEXT'       => "",
          ];

          if(empty($PROP['FIO'])) throw new Exception("Введите ФИО");
          if(empty($PROP['DOLZHNOST'])) throw new Exception("Введите должность");
          if(empty($PROP['PODRAZDELENIE'])) throw new Exception("Введите подразделение");
          if(empty($PROP['DATA'])) throw new Exception("Введите дату");
          if(empty($PROP['VREMYA_PODACHI'])) throw new Exception("Введите время подачи");
          if(empty($PROP['MARSHRUT'])) throw new Exception("Введите маршрут");
          if(empty($PROP['ADRES_PODACHI'])) throw new Exception("Введите адрес подачи");
          if(empty($PROP['TSEL_POEZDKI'])) throw new Exception("Введите цель поездки");

          $DATA = new DateTime($PROP['DATA']);
          $DATA->setTime(...explode(":",$PROP['VREMYA_PODACHI']));
          if($DATA < (new DateTime())) throw new Exception("Дата указана неверно");
          $PROP['DATA'] = ConvertTimeStamp($DATA->format('U'),"SHORT");
          

          $doc_fields    = array_merge(['DATE'=>date('d.m.Y')], $PROP);
          $doc_content   = str_replace(
                                   array_map(function($item){ return "#".$item."#"; }, array_keys($doc_fields))
                                   ,$doc_fields
                                   ,file_get_contents(__DIR__.'/zayavka.html'));

          $documentId = $el->Add($arLoadProductArray);
          if(!$documentId) throw new Exception($el->LAST_ERROR);

          if(!$GLOBALS['setElementPDFValue']($documentId, 'FAYL_ZAYAVKI', $doc_content, "Заявка на транспорт за пределы ТО для ".$PROP['FIO'])){
               CIBlockElement::Delete($documentId);
               throw new Exception("Не удалось создать файл");
          }
     
          $arErrorsTmp = array();
          $wfId = CBPDocument::StartWorkflow(
               BP_TEMPLATE_ID,
               ["lists", "BizprocDocument", $documentId],
               ['TargetUser' => "user_".$arLoadProductArray['MODIFIED_BY']],
               $arErrorsTmp
          );
          if(count($arErrorsTmp) > 0)  throw new Exception(array_reduce($arErrorsTmp, function($carry,$item){ return $carry.".".$item['message']; },""));

          $resp->code = "OK";
          $resp->message = "Заявка отправлена на согласование";
     }catch(Exception $exc){
          $resp->message = $exc->getMessage();
     }
     $APPLICATION->RestartBuffer();
     header('Content-Type: application/json');
     echo json_encode($resp);
     die;
}

if(!empty($_REQUEST['zayavka-na-transport-user-seacrh'])){
     $arUsers = [];
     $arF = CUser::GetList(($by = "NAME"), ($order = "desc"), ['NAME'=>$_REQUEST['zayavka-na-transport-user-seacrh']], ['NAV_PARAMS'=>['nTopCount'=>10],'FIELDS'=>["NAME", "LAST_NAME", "SECOND_NAME", "ID"]]);
     while($res = $arF->GetNext()){
          $arUsers[] = $res;
     }
     $APPLICATION->RestartBuffer();
     header('Content-Type: application/json');
     echo json_encode($arUsers);
     die;
}


$DEPARTMENT    = [];
$WORK_POSITION = NULL;

$arUser = $GLOBALS['userFields'](empty($_REQUEST['zayavka-na-transport-user-get'])?$USER->GetID():$_REQUEST['zayavka-na-transport-user-get']);


if(!empty($_REQUEST['zayavka-na-transport-user-get'])){
     $APPLICATION->RestartBuffer();
     header('Content-Type: application/json');
     echo json_encode([
          'PODRAZDELENIE'=> implode(", ",$arUser['PODRAZDELENIE']),
          'DOLZHNOST'    => $arUser['WORK_POSITION_CLEAR']
     ]);
     die;
}

$APPLICATION->SetAdditionalCSS('/bitrix/templates/.default/bootstrap.min.css');
$APPLICATION->AddHeadScript('/bitrix/templates/.default/jquery.min.js');
$APPLICATION->AddHeadScript('/bitrix/templates/.default/bootstrap.min.js');
$APPLICATION->AddHeadScript('/bitrix/templates/.default/jquery.mask.js');
$APPLICATION->AddHeadScript('/zayavka-na-transport-za-predely-tulskoy-oblasti/index.js');
$APPLICATION->SetAdditionalCSS('/zayavka-na-transport-za-predely-tulskoy-oblasti/main.css');
$APPLICATION->SetTitle("Заявка на транспорт за пределы Тульской области");

?>
<div class="alert alert-info d-none"></div>
<div class="mb-3"><button type="button" class="btn btn-dark btn-sm" onclick="$('#zayavka-na-transport input').each(function(){ var val = this.getAttribute('data-val'); if(val) this.value = val; })">Оформить на себя</button></div>
<form class="needs-validation zayavka-na-transport" novalidate="" style="" id="zayavka-na-transport" action="<?=htmlspecialchars($_SERVER['REQUEST_URI'])?>" method="POST" autocomplete="Off">
     <input type="hidden" name="zayavka-na-transport" value="add">
     <?=bitrix_sessid_post()?>
     <div class="alert" style="display:none"></div>
     <div class="form-group row">
          <label class="col-sm-2 col-form-label ">ФИО ответственного за заявку</label>
          <div class="col-sm-10">
               <input type="text" class="form-control" name="FIO" required data-val="<?=$USER->getFullName()?>">
               <div class="invalid-feedback">Необходимо заполнить</div>
          </div>
          <div class="col-2"></div>
          <div class="col-10">
               <div class="card"><div class="zayavka-na-transport-user-seacrh card-body" style="display:none"></div></div>
          </div>
     </div>
     <div class="form-group row">
        <label class="col-sm-2 col-form-label ">Должность</label>
        <div class="col-sm-10">
             <input type="text" class="form-control" name="DOLZHNOST" data-val="<?=$arUser['WORK_POSITION_CLEAR']?>" required>
             <div class="invalid-feedback">Необходимо заполнить</div>
        </div>
     </div>
     <div class="form-group row">
        <label class="col-sm-2 col-form-label ">Подразделение</label>
        <div class="col-sm-10">
             <input type="text" class="form-control" name="PODRAZDELENIE" data-val="<?=implode(" ",$arUser['PODRAZDELENIE'])?>" required>
             <div class="invalid-feedback">Необходимо заполнить</div>
        </div>
     </div>
     <div class="form-group row">
          <label class="col-sm-2 col-form-label ">Дата</label>
          <div class="col-sm-2">
               <input type="text" class="form-control" name="DATA" value="" required onclick="BX.calendar({node: this, field: this, bTime: false});">
               <div class="invalid-feedback">Необходимо заполнить</div>
          </div>
          <label class="col-sm-1 col-form-label ">Время подачи</label>
          <div class="col-sm-2">
               <input type="text" class="form-control" name="VREMYA_PODACHI" data-val="" required placeholder="00:00">
               <div class="invalid-feedback">Необходимо заполнить</div>
          </div>
     </div>
     <div class="form-group row">
        <label class="col-sm-2 col-form-label ">Для поездки по маршруту</label>
        <div class="col-sm-10">
             <input type="text" class="form-control" name="MARSHRUT" value="" required placeholder="пример, Тула - Москва или Тула - Москва, аэропорт Шереметьево">
             <div class="invalid-feedback">Необходимо заполнить</div>
        </div>
     </div>
     <div class="form-group row">
        <label class="col-sm-2 col-form-label ">Адрес подачи автотранспорта</label>
        <div class="col-sm-10">
             <input type="text" class="form-control" name="ADRES_PODACHI" value="" required>
             <div class="invalid-feedback">Необходимо заполнить</div>
        </div>
     </div>
     <div class="form-group row">
        <label class="col-sm-2 col-form-label ">Цель поездки</label>
        <div class="col-sm-10">
             <textarea class="form-control" name="TSEL_POEZDKI" required></textarea>
             <div class="invalid-feedback">Необходимо заполнить</div>
        </div>
     </div>
     
     <div class="form-group row">
        <label class="col-sm-2 col-form-label ">Уровень заказываемого а/м (VIP, средний, эконом)</label>
        <div class="col-sm-10">
             <input type="text" class="form-control" name="UROVEN_ZAKAZYVAEMOGO_A_M" value="" placeholder="пример, легковой автомобиль">
             <div class="invalid-feedback">Необходимо заполнить</div>
        </div>
     </div>
     
     <div class="form-group row">
        <label class="col-sm-2 col-form-label ">Дополнительные пожелания по заполнению автомобиля</label>
        <div class="col-sm-10">
             <textarea class="form-control" name="DOPOLNITELNYE_POZHELANIYA_PO_ZAPOLNENIYU_AVTOMOBIL"></textarea>
             <div class="invalid-feedback">Необходимо заполнить</div>
        </div>
     </div>
     
     <div class="form-group row">
        <label class="col-sm-2 col-form-label ">Пожелания по маршруту движения</label>
        <div class="col-sm-10">
             <textarea class="form-control" name="POZHELANIYA_PO_MARSHRUTU_DVIZHENIYA"></textarea>
             <div class="invalid-feedback">Необходимо заполнить</div>
        </div>
     </div>
     
     <div class="form-group row">
        <label class="col-sm-2 col-form-label ">Время использования автотранспорта</label>
        <div class="col-sm-10">
             <input type="text" class="form-control" name="VREMYA_ISPOLZOVANIYA_AVTOTRANSPORTA" value="">
             <div class="invalid-feedback">Необходимо заполнить</div>
        </div>
     </div>

     <div class="form-group row">
        <label class="col-sm-2 col-form-label ">Количество пассажиров</label>
        <div class="col-sm-10">
             <input type="text" class="form-control" name="KOLICHESTVO_PASSAZHIROV" value="">
             <div class="invalid-feedback">Необходимо заполнить</div>
        </div>
     </div>

     <div class="form-group row">
        <label class="col-sm-2 col-form-label ">Доставка или вывоз грузов</label>
        <div class="col-sm-10">
               <select class="form-control" name="DOSTAVKA_ILI_VYVOZ_GRUZOV">
                    <option value="Нет" selected>Нет</option>
                    <option value="Да">Да</option>
               </select>
               <div class="invalid-feedback">Необходимо заполнить</div>
        </div>
     </div>
     <div class="form-group row">
        <label class="col-sm-2 col-form-label ">Заявка оформляется на подведомственное учреждение?</label>
        <div class="col-sm-10">
               <select class="form-control" name="ZAYAVKA_NA_PODVED" required>
                    <option value="Нет" selected>Нет</option>
                    <option value="Да">Да</option>
               </select>
               <div class="invalid-feedback">Необходимо заполнить</div>
        </div>
     </div>

     <div class="form-group row">
        <label class="col-sm-2 col-form-label ">Телефон для связи</label>
        <div class="col-sm-10">
             <input type="text" class="form-control" name="TELEFON_DLYA_SVYAZI" value="">
             <div class="invalid-feedback">Необходимо заполнить</div>
        </div>
     </div>

     <div class="text-right">
          <button class="btn btn-primary" type="submit">Отправить на согласование &rarr;</button>
     </div>
</form>

<?
if(!$included_window){
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
=======
<?php
define('NEED_AUTH', true);
define('BP_IBLOCK', 484);
define('BP_TEMPLATE_ID', 365);
$included_window = defined("B_PROLOG_INCLUDED");
if(!$included_window){
     require $_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php";
}
global $APPLICATION,$USER,$userFields,$morph_client,$declOfNum,$mb_lcfirst;
CJSCore::Init(["date"]);
\Bitrix\Main\Loader::includeModule("iblock");

if(isset($_REQUEST['zayavka-na-transport'])){
     $resp = (object)['code'=>"ERROR",'message'=>"Ошибка"];
     try{
          \Bitrix\Main\Loader::includeModule("workflow");
          \Bitrix\Main\Loader::includeModule("bizproc");
          if(!check_bitrix_sessid()) throw new Exception('Проблема с сессией, обновите страницу');

          $el = new CIBlockElement;
          $PROP = [
               'FIO'               => $_REQUEST['FIO']                ? trim($_REQUEST['FIO'])            : NULL,
               'DOLZHNOST'         => $_REQUEST['DOLZHNOST']          ? trim($_REQUEST['DOLZHNOST'])      : NULL,
               'PODRAZDELENIE'     => $_REQUEST['PODRAZDELENIE']      ? trim($_REQUEST['PODRAZDELENIE'])  : NULL,
               'DATA'              => $_REQUEST['DATA']               ? trim($_REQUEST['DATA'])           : NULL,
               'VREMYA_PODACHI'    => $_REQUEST['VREMYA_PODACHI']     ? trim($_REQUEST['VREMYA_PODACHI']) : NULL,
               'MARSHRUT'          => $_REQUEST['MARSHRUT']           ? trim($_REQUEST['MARSHRUT'])       : NULL,
               'ADRES_PODACHI'     => $_REQUEST['ADRES_PODACHI']      ? trim($_REQUEST['ADRES_PODACHI'])  : NULL,
               'TSEL_POEZDKI'      => $_REQUEST['TSEL_POEZDKI']       ? trim($_REQUEST['TSEL_POEZDKI'])   : NULL,
               'ZAPUSTIL_PROTSESS' => $USER->GetId(),
               
               'UROVEN_ZAKAZYVAEMOGO_A_M'    =>
                                                  $_REQUEST['UROVEN_ZAKAZYVAEMOGO_A_M']
                                                  ? trim($_REQUEST['UROVEN_ZAKAZYVAEMOGO_A_M'])
                                                  : "Не указано",
               'DOPOLNITELNYE_POZHELANIYA_PO_ZAPOLNENIYU_AVTOMOBIL'
                                             =>
                                                  $_REQUEST['DOPOLNITELNYE_POZHELANIYA_PO_ZAPOLNENIYU_AVTOMOBIL']
                                                  ? trim($_REQUEST['DOPOLNITELNYE_POZHELANIYA_PO_ZAPOLNENIYU_AVTOMOBIL'])
                                                  : "Нет",
               'POZHELANIYA_PO_MARSHRUTU_DVIZHENIYA'
                                             =>
                                                  $_REQUEST['POZHELANIYA_PO_MARSHRUTU_DVIZHENIYA']
                                                  ? trim($_REQUEST['POZHELANIYA_PO_MARSHRUTU_DVIZHENIYA'])
                                                  : "Нет",
               'VREMYA_ISPOLZOVANIYA_AVTOTRANSPORTA'
                                             =>
                                                  $_REQUEST['VREMYA_ISPOLZOVANIYA_AVTOTRANSPORTA']
                                                  ? trim($_REQUEST['VREMYA_ISPOLZOVANIYA_AVTOTRANSPORTA'])
                                                  : "Не указано",
               'KOLICHESTVO_PASSAZHIROV'     =>
                                                  $_REQUEST['KOLICHESTVO_PASSAZHIROV']
                                                  ? trim($_REQUEST['KOLICHESTVO_PASSAZHIROV'])
                                                  : "Не указано",
               'DOSTAVKA_ILI_VYVOZ_GRUZOV'   =>
                                                  $_REQUEST['DOSTAVKA_ILI_VYVOZ_GRUZOV']
                                                  ? trim($_REQUEST['DOSTAVKA_ILI_VYVOZ_GRUZOV'])
                                                  : NULL,
               'ZAYAVKA_NA_PODVED'   =>
                                                  $_REQUEST['ZAYAVKA_NA_PODVED']
                                                  ? trim($_REQUEST['ZAYAVKA_NA_PODVED'])
                                                  : NULL,
               'TELEFON_DLYA_SVYAZI'         =>
                                                  $_REQUEST['TELEFON_DLYA_SVYAZI']
                                                  ? trim($_REQUEST['TELEFON_DLYA_SVYAZI'])
                                                  : "Не указан",
          ];
     
          $arLoadProductArray = [
               'MODIFIED_BY'        => $USER->GetId(), 
               'IBLOCK_SECTION_ID'  => false, 
               'IBLOCK_ID'          => BP_IBLOCK,
               'PROPERTY_VALUES'    => &$PROP,
               'NAME'               => $GLOBALS['APPLICATION']->GetTitle(),
               'ACTIVE'             => "Y", 
               'PREVIEW_TEXT'       => "",
          ];

          if(empty($PROP['FIO'])) throw new Exception("Введите ФИО");
          if(empty($PROP['DOLZHNOST'])) throw new Exception("Введите должность");
          if(empty($PROP['PODRAZDELENIE'])) throw new Exception("Введите подразделение");
          if(empty($PROP['DATA'])) throw new Exception("Введите дату");
          if(empty($PROP['VREMYA_PODACHI'])) throw new Exception("Введите время подачи");
          if(empty($PROP['MARSHRUT'])) throw new Exception("Введите маршрут");
          if(empty($PROP['ADRES_PODACHI'])) throw new Exception("Введите адрес подачи");
          if(empty($PROP['TSEL_POEZDKI'])) throw new Exception("Введите цель поездки");

          $DATA = new DateTime($PROP['DATA']);
          $DATA->setTime(...explode(":",$PROP['VREMYA_PODACHI']));
          if($DATA < (new DateTime())) throw new Exception("Дата указана неверно");
          $PROP['DATA'] = ConvertTimeStamp($DATA->format('U'),"SHORT");
          

          $doc_fields    = array_merge(['DATE'=>date('d.m.Y')], $PROP);
          $doc_content   = str_replace(
                                   array_map(function($item){ return "#".$item."#"; }, array_keys($doc_fields))
                                   ,$doc_fields
                                   ,file_get_contents(__DIR__.'/zayavka.html'));

          $documentId = $el->Add($arLoadProductArray);
          if(!$documentId) throw new Exception($el->LAST_ERROR);

          if(!$GLOBALS['setElementPDFValue']($documentId, 'FAYL_ZAYAVKI', $doc_content, "Заявка на транспорт за пределы ТО для ".$PROP['FIO'])){
               CIBlockElement::Delete($documentId);
               throw new Exception("Не удалось создать файл");
          }
     
          $arErrorsTmp = array();
          $wfId = CBPDocument::StartWorkflow(
               BP_TEMPLATE_ID,
               ["lists", "BizprocDocument", $documentId],
               ['TargetUser' => "user_".$arLoadProductArray['MODIFIED_BY']],
               $arErrorsTmp
          );
          if(count($arErrorsTmp) > 0)  throw new Exception(array_reduce($arErrorsTmp, function($carry,$item){ return $carry.".".$item['message']; },""));

          $resp->code = "OK";
          $resp->message = "Заявка отправлена на согласование";
     }catch(Exception $exc){
          $resp->message = $exc->getMessage();
     }
     $APPLICATION->RestartBuffer();
     header('Content-Type: application/json');
     echo json_encode($resp);
     die;
}

if(!empty($_REQUEST['zayavka-na-transport-user-seacrh'])){
     $arUsers = [];
     $arF = CUser::GetList(($by = "NAME"), ($order = "desc"), ['NAME'=>$_REQUEST['zayavka-na-transport-user-seacrh']], ['NAV_PARAMS'=>['nTopCount'=>10],'FIELDS'=>["NAME", "LAST_NAME", "SECOND_NAME", "ID"]]);
     while($res = $arF->GetNext()){
          $arUsers[] = $res;
     }
     $APPLICATION->RestartBuffer();
     header('Content-Type: application/json');
     echo json_encode($arUsers);
     die;
}


$DEPARTMENT    = [];
$WORK_POSITION = NULL;

$arUser = $GLOBALS['userFields'](empty($_REQUEST['zayavka-na-transport-user-get'])?$USER->GetID():$_REQUEST['zayavka-na-transport-user-get']);


if(!empty($_REQUEST['zayavka-na-transport-user-get'])){
     $APPLICATION->RestartBuffer();
     header('Content-Type: application/json');
     echo json_encode([
          'PODRAZDELENIE'=> implode(", ",$arUser['PODRAZDELENIE']),
          'DOLZHNOST'    => $arUser['WORK_POSITION_CLEAR']
     ]);
     die;
}

$APPLICATION->SetAdditionalCSS('/bitrix/templates/.default/bootstrap.min.css');
$APPLICATION->AddHeadScript('/bitrix/templates/.default/jquery.min.js');
$APPLICATION->AddHeadScript('/bitrix/templates/.default/bootstrap.min.js');
$APPLICATION->AddHeadScript('/bitrix/templates/.default/jquery.mask.js');
$APPLICATION->AddHeadScript('/zayavka-na-transport-za-predely-tulskoy-oblasti/index.js');
$APPLICATION->SetAdditionalCSS('/zayavka-na-transport-za-predely-tulskoy-oblasti/main.css');
$APPLICATION->SetTitle("Заявка на транспорт за пределы Тульской области");

?>
<div class="alert alert-info d-none"></div>
<div class="mb-3"><button type="button" class="btn btn-dark btn-sm" onclick="$('#zayavka-na-transport input').each(function(){ var val = this.getAttribute('data-val'); if(val) this.value = val; })">Оформить на себя</button></div>
<form class="needs-validation zayavka-na-transport" novalidate="" style="" id="zayavka-na-transport" action="<?=htmlspecialchars($_SERVER['REQUEST_URI'])?>" method="POST" autocomplete="Off">
     <input type="hidden" name="zayavka-na-transport" value="add">
     <?=bitrix_sessid_post()?>
     <div class="alert" style="display:none"></div>
     <div class="form-group row">
          <label class="col-sm-2 col-form-label ">ФИО ответственного за заявку</label>
          <div class="col-sm-10">
               <input type="text" class="form-control" name="FIO" required data-val="<?=$USER->getFullName()?>">
               <div class="invalid-feedback">Необходимо заполнить</div>
          </div>
          <div class="col-2"></div>
          <div class="col-10">
               <div class="card"><div class="zayavka-na-transport-user-seacrh card-body" style="display:none"></div></div>
          </div>
     </div>
     <div class="form-group row">
        <label class="col-sm-2 col-form-label ">Должность</label>
        <div class="col-sm-10">
             <input type="text" class="form-control" name="DOLZHNOST" data-val="<?=$arUser['WORK_POSITION_CLEAR']?>" required>
             <div class="invalid-feedback">Необходимо заполнить</div>
        </div>
     </div>
     <div class="form-group row">
        <label class="col-sm-2 col-form-label ">Подразделение</label>
        <div class="col-sm-10">
             <input type="text" class="form-control" name="PODRAZDELENIE" data-val="<?=implode(" ",$arUser['PODRAZDELENIE'])?>" required>
             <div class="invalid-feedback">Необходимо заполнить</div>
        </div>
     </div>
     <div class="form-group row">
          <label class="col-sm-2 col-form-label ">Дата</label>
          <div class="col-sm-2">
               <input type="text" class="form-control" name="DATA" value="" required onclick="BX.calendar({node: this, field: this, bTime: false});">
               <div class="invalid-feedback">Необходимо заполнить</div>
          </div>
          <label class="col-sm-1 col-form-label ">Время подачи</label>
          <div class="col-sm-2">
               <input type="text" class="form-control" name="VREMYA_PODACHI" data-val="" required placeholder="00:00">
               <div class="invalid-feedback">Необходимо заполнить</div>
          </div>
     </div>
     <div class="form-group row">
        <label class="col-sm-2 col-form-label ">Для поездки по маршруту</label>
        <div class="col-sm-10">
             <input type="text" class="form-control" name="MARSHRUT" value="" required placeholder="пример, Тула - Москва или Тула - Москва, аэропорт Шереметьево">
             <div class="invalid-feedback">Необходимо заполнить</div>
        </div>
     </div>
     <div class="form-group row">
        <label class="col-sm-2 col-form-label ">Адрес подачи автотранспорта</label>
        <div class="col-sm-10">
             <input type="text" class="form-control" name="ADRES_PODACHI" value="" required>
             <div class="invalid-feedback">Необходимо заполнить</div>
        </div>
     </div>
     <div class="form-group row">
        <label class="col-sm-2 col-form-label ">Цель поездки</label>
        <div class="col-sm-10">
             <textarea class="form-control" name="TSEL_POEZDKI" required></textarea>
             <div class="invalid-feedback">Необходимо заполнить</div>
        </div>
     </div>
     
     <div class="form-group row">
        <label class="col-sm-2 col-form-label ">Уровень заказываемого а/м (VIP, средний, эконом)</label>
        <div class="col-sm-10">
             <input type="text" class="form-control" name="UROVEN_ZAKAZYVAEMOGO_A_M" value="" placeholder="пример, легковой автомобиль">
             <div class="invalid-feedback">Необходимо заполнить</div>
        </div>
     </div>
     
     <div class="form-group row">
        <label class="col-sm-2 col-form-label ">Дополнительные пожелания по заполнению автомобиля</label>
        <div class="col-sm-10">
             <textarea class="form-control" name="DOPOLNITELNYE_POZHELANIYA_PO_ZAPOLNENIYU_AVTOMOBIL"></textarea>
             <div class="invalid-feedback">Необходимо заполнить</div>
        </div>
     </div>
     
     <div class="form-group row">
        <label class="col-sm-2 col-form-label ">Пожелания по маршруту движения</label>
        <div class="col-sm-10">
             <textarea class="form-control" name="POZHELANIYA_PO_MARSHRUTU_DVIZHENIYA"></textarea>
             <div class="invalid-feedback">Необходимо заполнить</div>
        </div>
     </div>
     
     <div class="form-group row">
        <label class="col-sm-2 col-form-label ">Время использования автотранспорта</label>
        <div class="col-sm-10">
             <input type="text" class="form-control" name="VREMYA_ISPOLZOVANIYA_AVTOTRANSPORTA" value="">
             <div class="invalid-feedback">Необходимо заполнить</div>
        </div>
     </div>

     <div class="form-group row">
        <label class="col-sm-2 col-form-label ">Количество пассажиров</label>
        <div class="col-sm-10">
             <input type="text" class="form-control" name="KOLICHESTVO_PASSAZHIROV" value="">
             <div class="invalid-feedback">Необходимо заполнить</div>
        </div>
     </div>

     <div class="form-group row">
        <label class="col-sm-2 col-form-label ">Доставка или вывоз грузов</label>
        <div class="col-sm-10">
               <select class="form-control" name="DOSTAVKA_ILI_VYVOZ_GRUZOV">
                    <option value="Нет" selected>Нет</option>
                    <option value="Да">Да</option>
               </select>
               <div class="invalid-feedback">Необходимо заполнить</div>
        </div>
     </div>
     <div class="form-group row">
        <label class="col-sm-2 col-form-label ">Заявка оформляется на подведомственное учреждение?</label>
        <div class="col-sm-10">
               <select class="form-control" name="ZAYAVKA_NA_PODVED" required>
                    <option value="Нет" selected>Нет</option>
                    <option value="Да">Да</option>
               </select>
               <div class="invalid-feedback">Необходимо заполнить</div>
        </div>
     </div>

     <div class="form-group row">
        <label class="col-sm-2 col-form-label ">Телефон для связи</label>
        <div class="col-sm-10">
             <input type="text" class="form-control" name="TELEFON_DLYA_SVYAZI" value="">
             <div class="invalid-feedback">Необходимо заполнить</div>
        </div>
     </div>

     <div class="text-right">
          <button class="btn btn-primary" type="submit">Отправить на согласование &rarr;</button>
     </div>
</form>

<?
if(!$included_window){
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
>>>>>>> e0a0eba79 (init)
}?>