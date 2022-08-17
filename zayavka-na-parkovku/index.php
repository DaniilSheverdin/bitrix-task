<?
define('BP_IBLOCK', 507);
define('BP_TEMPLATE_ID', 398);

$included_window = defined("B_PROLOG_INCLUDED");
if(!$included_window){
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
}
global $APPLICATION,$USER,$userFields,$morph_client,$declOfNum,$mb_lcfirst;
\Bitrix\Main\Loader::includeModule("iblock");
CJSCore::Init(["date"]);
$APPLICATION->SetTitle("Заявка на парковку");

$APPLICATION->SetAdditionalCSS('/bitrix/templates/.default/bootstrap.min.css');
$APPLICATION->AddHeadScript('/bitrix/templates/.default/jquery.min.js');
$APPLICATION->AddHeadScript('/bitrix/templates/.default/bootstrap.min.js');
$APPLICATION->AddHeadScript('/bitrix/templates/.default/jquery.mask.js');
$APPLICATION->AddHeadScript('/zayavka-na-parkovku/index.js');
$APPLICATION->SetAdditionalCSS('/zayavka-na-parkovku/main.css');


$normalizeFiles = function($files){
	$_files       = [ ];
	$_files_count = count( $files[ 'name' ] );
	$_files_keys  = array_keys( $files );

	for ( $i = 0; $i < $_files_count; $i++ )
		foreach ( $_files_keys as $key )
			$_files[ $i ][ $key ] = $files[ $key ][ $i ];

	return $_files;
};

$arUser = $userFields($USER->GetID());

$PARKOVKI = [];
$db_enum_list = CIBlockProperty::GetPropertyEnum('PARKOVKA_V_ZDANII_PRAVITELSTVA', ['value'=>"ASC"], ['IBLOCK_ID'=>BP_IBLOCK]);
while($ar_enum_list = $db_enum_list->GetNext()){
    $PARKOVKI[$ar_enum_list['ID']] = [
        'ID'        => $ar_enum_list['ID'],
        'TITLE'     => $ar_enum_list['VALUE'],
    ];
}

if(isset($_REQUEST['zayavka-na-parkovku'])){
    $APPLICATION->RestartBuffer();
    $resp = (object)['code'=>"ERROR",'message'=>"Ошибка"];
    try{
         \Bitrix\Main\Loader::includeModule("workflow");
         \Bitrix\Main\Loader::includeModule("bizproc");
        if(!check_bitrix_sessid()) throw new Exception('Проблема с сессией, обновите страницу');
         $F_I_O                                             = $arUser['FIO'];
         $DOLZHNOST                                         = $arUser['WORK_POSITION_CLEAR'];
         $PODRAZDELENIE                                     = implode(" ",$arUser['PODRAZDELENIE']);
         $PARKOVKA_V_ZDANII_PRAVITELSTVA                    = $_REQUEST['PARKOVKA_V_ZDANII_PRAVITELSTVA']                       ?? NULL;
         $AVTOMOBILI                                        = "";
         $SHTATNAYA_CHISLENNOST_VSEGO                       = $_REQUEST['SHTATNAYA_CHISLENNOST_VSEGO']                          ?? NULL;
         $SHTATNAYA_CHISLENNOST_V_ZDANII_PRAVITELSTVA_TO    = $_REQUEST['SHTATNAYA_CHISLENNOST_V_ZDANII_PRAVITELSTVA_TO']       ?? NULL;
         $SHTATNAYA_CHISLENNOST_VNE_ZDANIYA_PRAVITELSTVA_TO = $_REQUEST['SHTATNAYA_CHISLENNOST_VNE_ZDANIYA_PRAVITELSTVA_TO']    ?? NULL;
         $SPISOK                                            = $_FILES['SPISOK'] ?? NULL;
         $FAYL_ZAYAVKI                                      = NULL;
         $AVTO                                              = $_REQUEST['AVTO']                                                 ?? [];
         $AVTO_STS                                          = $_FILES['AVTO_STS'] ?? [];

        if($PARKOVKA_V_ZDANII_PRAVITELSTVA == 1128){
            if(empty($AVTO) || empty($AVTO_STS)) throw new Exception("Автомобили не указаны");

            $AVTO_STS = $normalizeFiles($AVTO_STS);
            foreach($AVTO as $avto_indx=>$avto_item){
                if(empty($avto_item['MARKA'])) throw new Exception("Укажите марку авто ".($avto_indx+1));
                if(empty($avto_item['NOMER'])) throw new Exception("Укажите номер авто ".($avto_indx+1));
                if(empty($avto_item['OBOSNOVANIE'])) throw new Exception("Укажите обоснование авто ".($avto_indx+1));
                if(empty($AVTO_STS[$avto_indx])) throw new Exception("Загрузите СТС авто ".($avto_indx+1));
                if($AVTO_STS[$avto_indx]['error'] !== UPLOAD_ERR_OK) throw new Exception("Не удалось загрузить СТС авто ".($avto_indx+1));
                if(
                    !in_array(mime_content_type($AVTO_STS[$avto_indx]['tmp_name']), ['image/jpeg','image/jpg','image/png'])
                    || !in_array(pathinfo($AVTO_STS[$avto_indx]['name'], PATHINFO_EXTENSION), ['jpeg','jpg','png'])
                    ) throw new Exception("Не удалось загрузить СТС авто ".($avto_indx+1).". Неверное расширение файла.");
            
            }
            $AVTOMOBILI = '<div>';
            foreach($AVTO as $avto_indx=>$avto_item){
                $AVTO_STS[$avto_indx]['MODULE_ID'] = "bizproc";

                $AVTO_STS[$avto_indx]['FILE_ID'] = CFile::SaveFile($AVTO_STS[$avto_indx], "bizproc");
                if(empty($AVTO_STS[$avto_indx]['FILE_ID'])) throw new Exception("Ошибка сохранения СТС авто ".($avto_indx+1));

                $sts_file = $_SERVER['DOCUMENT_ROOT'].CFile::GetPath($AVTO_STS[$avto_indx]['FILE_ID']);
                $AVTOMOBILI .= '<div><div style="border:1px solid #222; padding:10px 15px;">';
                    $AVTOMOBILI .= '<div style="margin-bottom:10px">Марка:'.$avto_item['MARKA'].'</div>';
                    $AVTOMOBILI .= '<div style="margin-bottom:10px">Номер:'.$avto_item['NOMER'].'</div>';
                    $AVTOMOBILI .= '<div style="margin-bottom:25px">Обоснование:'.$avto_item['OBOSNOVANIE'].'</div>';
                    $AVTOMOBILI .= '<div><img style="max-width:600px" src="data:'.mime_content_type($sts_file).';base64,'.base64_encode(file_get_contents($sts_file)).'" alt="СТС"></div>';
                $AVTOMOBILI .= '</div></div>';
            }
            $AVTOMOBILI .= "</div>";

            $SPISOK = NULL;
        }else{
            if(empty($SPISOK) || $SPISOK['error'] !== UPLOAD_ERR_OK) throw new Exception("Не удалось загрузить файл");
            $SPISOK['MODULE_ID'] = "bizproc";
            $SPISOK['FILE_ID'] = CFile::SaveFile($SPISOK, "bizproc");
            
            if(
                !in_array(mime_content_type($SPISOK['tmp_name']), ['application/excel','application/vnd.ms-excel','application/x-excel','application/x-msexcel','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                || !in_array(pathinfo($SPISOK['name'], PATHINFO_EXTENSION), ['xls','xlsx'])
                ) throw new Exception("Не удалось загрузить файл. Неверное расширение файла.");

            if(empty($SPISOK['FILE_ID'])) throw new Exception("Ошибка сохранения СТС авто ".($avto_indx+1));
        }

        $arProps = [
            'F_I_O' => $F_I_O,
            'DOLZHNOST' => $DOLZHNOST,
            'PODRAZDELENIE' => $PODRAZDELENIE,
            'PARKOVKA_V_ZDANII_PRAVITELSTVA' => $PARKOVKA_V_ZDANII_PRAVITELSTVA,
            'AVTOMOBILI' => $AVTOMOBILI,
            'SHTATNAYA_CHISLENNOST_VSEGO' => $SHTATNAYA_CHISLENNOST_VSEGO,
            'SHTATNAYA_CHISLENNOST_V_ZDANII_PRAVITELSTVA_TO' => $SHTATNAYA_CHISLENNOST_V_ZDANII_PRAVITELSTVA_TO,
            'SHTATNAYA_CHISLENNOST_VNE_ZDANIYA_PRAVITELSTVA_TO' => $SHTATNAYA_CHISLENNOST_VNE_ZDANIYA_PRAVITELSTVA_TO,
            'SPISOK' => $SPISOK?$SPISOK['FILE_ID']:NULL,
        ];

        /**
         * file
         */
        $zayavka_file_props = $arProps;

        $zayavka_file_props['PARKOVKA_V_ZDANII_PRAVITELSTVA'] = $PARKOVKI[$PARKOVKA_V_ZDANII_PRAVITELSTVA]['TITLE'];
        $zayavka_file_props['MESSAGE'] = "";
        if($PARKOVKA_V_ZDANII_PRAVITELSTVA == 1128){
            $zayavka_file_props['MESSAGE'] .= '<p><strong>Автомобили</strong><br/>'.$AVTOMOBILI.'</p>';
        }else{
            $zayavka_file_props['MESSAGE'] .= '<p><strong>Штатная численность всего</strong><br/>'.$SHTATNAYA_CHISLENNOST_VSEGO.'</p>';
            $zayavka_file_props['MESSAGE'] .= '<p><strong>Штатная численность в здании правительства ТО</strong><br/>'.$SHTATNAYA_CHISLENNOST_V_ZDANII_PRAVITELSTVA_TO.'</p>';
            $zayavka_file_props['MESSAGE'] .= '<p><strong>Штатная численность вне здания правительства ТО</strong><br/>'.$SHTATNAYA_CHISLENNOST_VNE_ZDANIYA_PRAVITELSTVA_TO.'</p>';
        }

        $doc_content = str_replace(
                    array_map(function($item){ return "#".$item."#"; }, array_keys($zayavka_file_props))
                    ,$zayavka_file_props
                    ,file_get_contents(__DIR__.'/zayavka.html'));
        ////

         $el = new CIBlockElement;
         $arLoadProductArray = [
              'MODIFIED_BY'        => $USER->GetId(), 
              'IBLOCK_SECTION_ID'  => false, 
              'IBLOCK_ID'          => BP_IBLOCK,
              'PROPERTY_VALUES'    => $arProps,
              'NAME'               => $GLOBALS['APPLICATION']->GetTitle(),
              'ACTIVE'             => "Y", 
              'PREVIEW_TEXT'       => "",
         ];

         $documentId = $el->Add($arLoadProductArray);
         if(!$documentId) throw new Exception($el->LAST_ERROR);
         
         if(!$GLOBALS['setElementPDFValue']($documentId, 'FAYL_ZAYAVKI', $doc_content, "Заявка на парковку для ".$F_I_O)){
            CIBlockElement::Delete($documentId);
            throw new Exception("Не удалось создать файл");
         }
    
         $arErrorsTmp = array();
         $wfId = CBPDocument::StartWorkflow(
              BP_TEMPLATE_ID,
              ["lists", "BizprocDocument", $documentId],
              ['TargetUser' => "user_".$USER->GetId()],
              $arErrorsTmp
         );
         if(count($arErrorsTmp) > 0)  throw new Exception(array_reduce($arErrorsTmp, function($carry,$item){ return $carry.".".$item['message']; },""));

         $resp->code = "OK";
         $resp->message = "Заявка отправлена";
    }catch(Exception $exc){
         $resp->message = $exc->getMessage();
    }
    header('Content-Type: application/json');
    echo json_encode($resp);
    die;
}
?>
<form class="needs-validation zayavka-na-parkovku" novalidate="" style="" id="zayavka-na-parkovku" action="<?=htmlspecialchars($_SERVER['REQUEST_URI'])?>" method="POST" autocomplete="Off">
    <input type="hidden" name="zayavka-na-parkovku" value="add">
    <?=bitrix_sessid_post()?>
    <div class="alert" style="display:none"></div>
    <div class="form-group row">
        <label class="col-sm-2 col-form-label ">Ф .И. О.</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" name="F_I_O" value="<?=htmlspecialchars($arUser['FIO']);?>" disabled>
        </div>
    </div>
    <div class="form-group row">
        <label class="col-sm-2 col-form-label ">Должность</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" name="DOLZHNOST" value="<?=htmlspecialchars($arUser['WORK_POSITION_CLEAR']);?>" disabled>
        </div>
    </div>
    <div class="form-group row">
        <label class="col-sm-2 col-form-label ">Подразделение</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" name="PODRAZDELENIE" value="<?=htmlspecialchars(implode(" ",$arUser['PODRAZDELENIE']));?>" disabled>
        </div>
    </div>
    <div class="form-group row">
        <label class="col-sm-2 col-form-label ">Парковка в здании Правительства</label>
        <div class="col-sm-10">
            <select class="form-control" name="PARKOVKA_V_ZDANII_PRAVITELSTVA" required onchange="$('.zayavka-na-parkovku__zdanie').hide().filter('[data-id='+this.value+']').show()">
                <option value="0" disabled selected>Выбрать</option>
                <?foreach($PARKOVKI as $parkovka):?>
                    <option value="<?=$parkovka['ID']?>"><?=$parkovka['TITLE']?></option>
                <?endforeach;?>
            </select>
        </div>
    </div>
        
    <div class="zayavka-na-parkovku__zdanie" data-id="1128">
        <div class="card mb-4 zayavka-na-parkovku__avto">
            <div class="card-body">
                <div class="zayavka-na-parkovku__avto__item mb-3" data-index="0">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="zayavka-na-parkovku__avto__indx mb-3">Автомобиль № <button type="button" class="close" onclick="$(this).closest('.zayavka-na-parkovku__avto__item').remove()">&times;</button></h5>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label ">Марка ТС</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="AVTO[0][MARKA]">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label ">Номер ТС</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="AVTO[0][NOMER]">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label ">Обоснование</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="AVTO[0][OBOSNOVANIE]">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label ">Копия свидетельства о регистрации ТС</label>
                                <div class="col-sm-10">
                                    <div class="mb-2"><input type="file" name="AVTO_STS[0]"></div>
                                    <div><small>jpeg,png</small></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-2">
                    <button class="btn btn-secondary" type="button" onclick="zayavka_na_parkovku__avto_new()">Добавить еще</button>
                </div>
            </div>
        </div>
    </div>
    <div class="zayavka-na-parkovku__zdanie" data-id="1129">
        <div class="card mb-4">
            <div class="card-body">
                <div class="alert alert-info mb-4 text-center">Для подачи заявки на право парковки автомобилей на стоянке № 3, правительства Тульской области сотрудникам ведомства, рассчитайте допустимое количество мест</div>
                <h5 class="text-center mb-3">Штатная численность</h5>
                <div class="row">
                    <div class="col-md-7">
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label ">Всего<br/><br/></label>
                            <div class="col-sm-12">
                                <input type="text" class="form-control" name="SHTATNAYA_CHISLENNOST_VSEGO">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label ">Располагаются в здании правительства Тульской области<br/><br/></label>
                            <div class="col-sm-12">
                                <input type="text" class="form-control" name="SHTATNAYA_CHISLENNOST_V_ZDANII_PRAVITELSTVA_TO">
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label ">Располагаются вне здания  правительства Тульской области, но требуется периодическое  посещение его на личном транспорте для выполнения сл.поручений</label>
                            <div class="col-sm-12">
                                <input type="text" class="form-control" name="SHTATNAYA_CHISLENNOST_VNE_ZDANIYA_PRAVITELSTVA_TO">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label ">Максимальное Количество электронных индивидуальных пропусков для доступа на стоянку № 3</label>
                            <div class="col-sm-12">
                                <input type="text" class="form-control" name="MAKS1" readonly>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label ">Максимальное Количество электронных обезличенных  пропусков (групп) для доступа на стоянку № 3</label>
                            <div class="col-sm-12">
                                <input type="text" class="form-control" name="MAKS2" readonly>
                            </div>
                        </div>
                        
                    </div>
                    
                    <div class="col-md-12">
                        <div class="form-group row">
                            <label class="col-sm-12 col-form-label ">Приложите файл, заполненный по примеру с учетом максимально допустимых пропусков на ведомство для доступа на стоянку № 3</label>
                            <div class="col-sm-12">
                                <p><small><a href="/zayavka-na-parkovku/primer.xlsx" download="Пример для заполнения.xlsx">Скачать пример</a></small></p>
                                <input type="file" name="SPISOK">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 col-sm-1">
            <div class="text-right mb-3">
                <button class="btn btn-success btn-block" type="submit">Далее &rarr;</button>
            </div>
        </div>
        <div class="col-12  col-sm-11">
        </div>
    </div>
</form>

<?
if(!$included_window){
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
}?>