<?php

define('BP_IBLOCK', 595);
define('BP_TEMPLATE_ID', 1009);
define('BP_PROPUSK_VODY', isset($_GET['BP_PROPUSK_VODY']));

$included_window = defined("B_PROLOG_INCLUDED");
if (!$included_window) {
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
}
global $APPLICATION,$USER,$userFields,$morph_client,$declOfNum,$mb_lcfirst;
\Bitrix\Main\Loader::includeModule("iblock");
\Bitrix\Main\Loader::includeModule("lists");
CJSCore::Init(["date"]);
$APPLICATION->SetTitle("Заявка на ".(BP_PROPUSK_VODY?"пропуск ТМЦ (вода, канцтовары и т.п.)":"массовый пропуск"));

$APPLICATION->SetAdditionalCSS('/bitrix/templates/.default/bootstrap.min.css');
$APPLICATION->AddHeadScript('/bitrix/templates/.default/jquery.min.js');
$APPLICATION->AddHeadScript('/bitrix/templates/.default/bootstrap.min.js');
$APPLICATION->AddHeadScript('/bitrix/templates/.default/jquery.mask.js');
$APPLICATION->AddHeadScript('/czn/propusk-massovy/index.js');
$APPLICATION->SetAdditionalCSS('/czn/propusk-massovy/main.css');

$DOSTUP_NA_PARKOVKU = [];
$ZDANIYA = [];
$IBLOCK_FIELDS = [];
foreach ((new CList(BP_IBLOCK))->GetFields() as $field) {
    $IBLOCK_FIELDS[$field['CODE']?:$field['FIELD_ID']] = $field;
}
$arUser = $userFields($USER->GetID());

$db_enum_list = CIBlockProperty::GetPropertyEnum('VYBOR_ZDANIYA', ['value'=>"ASC"], ['IBLOCK_ID'=>BP_IBLOCK]);
while ($ar_enum_list = $db_enum_list->GetNext()) {
    $ar_enum_list__title = explode(":", $ar_enum_list['VALUE'], 2);
    $ZDANIYA[$ar_enum_list['ID']] = [
        'ID'        => $ar_enum_list['ID'],
        'TITLE'     => $ar_enum_list__title[0],
        'PARKING'   => in_array("P", $ar_enum_list__title),
    ];
}

$db_enum_list = CIBlockProperty::GetPropertyEnum('NEOBKHODIM_LI_DOSTUP_NA_PARKOVKU', ['value'=>"ASC"], ['IBLOCK_ID'=>BP_IBLOCK]);
while ($ar_enum_list = $db_enum_list->GetNext()) {
    $DOSTUP_NA_PARKOVKU[$ar_enum_list['ID']] = $ar_enum_list['VALUE'];
}

if (isset($_REQUEST['propusk-massovy'])) {
    $APPLICATION->RestartBuffer();
    $resp = (object)['code'=>"ERROR",'message'=>"Ошибка"];
    try {
         \Bitrix\Main\Loader::includeModule("workflow");
         \Bitrix\Main\Loader::includeModule("bizproc");
        if (!check_bitrix_sessid()) {
            throw new Exception('Проблема с сессией, обновите страницу');
        }
        $VEDOMSTVO_PODAYUSHCHEE_ZAYAVKU            = $_REQUEST['VEDOMSTVO_PODAYUSHCHEE_ZAYAVKU']           ?? null;
        $SOTRUDNIK_VEDOMSTVA                       = $_REQUEST['SOTRUDNIK_VEDOMSTVA']                      ?? null;
        $ORGANIZATSIYA_KOTOROY_NEOBKHODIM_PROPUSK  = $_REQUEST['ORGANIZATSIYA_KOTOROY_NEOBKHODIM_PROPUSK'] ?? null;
        $VYBOR_ZDANIYA                             = $_REQUEST['VYBOR_ZDANIYA']                            ?? null;
        $SPISOK_LITS                               = $_FILES['SPISOK_LITS']                                ?? null;
        $SPISOK_LITS_STR                           = $_REQUEST['SPISOK_LITS_STR']                          ?? [];
        $DATA_POSESHCHENIYA                        = $_REQUEST['DATA_POSESHCHENIYA']                       ?? [];
        $KOLICHESTVO                               = $_REQUEST['KOLICHESTVO']                              ?? null;
        $KABINET                                   = $_REQUEST['KABINET']                                  ?? null;
        $TSEL_POSESHCHENIYA                        = $_REQUEST['TSEL_POSESHCHENIYA']                       ?? null;
        $NEOBKHODIM_LI_DOSTUP_NA_PARKOVKU          = $_REQUEST['NEOBKHODIM_LI_DOSTUP_NA_PARKOVKU']         ?? null;
        $COMMENT                                   = $_REQUEST['COMMENT']                                  ?? null;
        $AVTO                                      = $_REQUEST['AVTO']                                     ?? [];
        $KONTAKTNYY_TELEFON                        = $_REQUEST['KONTAKTNYY_TELEFON']                       ?? null;
        $RUKOVODITEL                               = null;
         
        if (isset($_REQUEST[$IBLOCK_FIELDS['RUKOVODITEL']['FIELD_ID']]['n0']['VALUE'])) {
            $RUKOVODITEL = $_REQUEST[$IBLOCK_FIELDS['RUKOVODITEL']['FIELD_ID']]['n0']['VALUE'];
        }
         

        $DATA_POSESHCHENIYA = array_filter($DATA_POSESHCHENIYA);
        foreach ($DATA_POSESHCHENIYA as &$DATA_POSESHCHENIYA_ITEM) {
            $temp_dt = DateTime::createFromFormat("d.m.Y H:i:s", $DATA_POSESHCHENIYA_ITEM);
            if (!$temp_dt) {
                throw new Exception("Дата посещения указана неверно");
            }
            if ($temp_dt < new DateTime()) {
                throw new Exception("Дата посещения не может быть ранее текущей");
            }
            $DATA_POSESHCHENIYA_ITEM = ConvertTimeStamp($temp_dt->format('U'), "FULL");
        }
        unset($temp_dt, $DATA_POSESHCHENIYA_ITEM);
         
        $AVTO = array_filter($AVTO, function ($avto_item) { return array_filter($avto_item); });
        $AVTO_LIST = array_map(function ($avto_item) { return implode(", ", array_filter($avto_item))."."; }, $AVTO);
        $VEDOMSTVO_PODAYUSHCHEE_ZAYAVKU = $arUser['DEPARTMENT'];
        $SOTRUDNIK_VEDOMSTVA = $arUser['FIO'];

         
        if ($SPISOK_LITS) {
             if ($SPISOK_LITS['error'] != UPLOAD_ERR_NO_FILE) {
                if ($SPISOK_LITS['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception("Не удалось загрузить файл 'Список лиц'".$SPISOK_LITS['error']);
                }
                $SPISOK_LITS['MODULE_ID'] = "iblock";
             } else {
                $SPISOK_LITS = null;
             }
        }

        if ($SPISOK_LITS_STR) {
            $SPISOK_LITS_STR = array_filter($SPISOK_LITS_STR);
        }

        if (empty($SPISOK_LITS) && empty($SPISOK_LITS_STR)) {
            throw new Exception("Укажите ".(BP_PROPUSK_VODY?"экспедитора":"список лиц"));
        }

        $arProps = [
            'VEDOMSTVO_PODAYUSHCHEE_ZAYAVKU'          => $VEDOMSTVO_PODAYUSHCHEE_ZAYAVKU,
            'SOTRUDNIK_VEDOMSTVA'                     => $SOTRUDNIK_VEDOMSTVA,
            'ORGANIZATSIYA_KOTOROY_NEOBKHODIM_PROPUSK'=> trim($ORGANIZATSIYA_KOTOROY_NEOBKHODIM_PROPUSK),
            'VYBOR_ZDANIYA'                           => $VYBOR_ZDANIYA,
            'SPISOK_LITS'                             => $SPISOK_LITS,
            'SPISOK_LITS_STR'                         => $SPISOK_LITS_STR,
            'KOLICHESTVO'                             => trim($KOLICHESTVO),
            'DATA_POSESHCHENIYA'                      => $DATA_POSESHCHENIYA,
            'KABINET'                                 => trim($KABINET),
            'TSEL_POSESHCHENIYA'                      => trim($TSEL_POSESHCHENIYA),
            'NEOBKHODIM_LI_DOSTUP_NA_PARKOVKU'        => $NEOBKHODIM_LI_DOSTUP_NA_PARKOVKU,
            'COMMENT'                                 => $COMMENT,
            'AVTO'                                    => $AVTO_LIST,
            'RUKOVODITEL'                             => $RUKOVODITEL,
            'TIP'                                    => BP_PROPUSK_VODY?"пропуск ТМЦ (вода, канцтовары и т.п.)":"массовый",
            'KONTAKTNYY_TELEFON'                      => trim(strip_tags($KONTAKTNYY_TELEFON)),
        ];

        /**
         * file
         */
        $zayavka_file_props = $arProps;

        $zayavka_file_props['SOTRUDNIK_DOLJNOST'] = $arUser['DOLJNOST'];

        $zayavka_file_props['VYBOR_ZDANIYA']       = str_replace(":P", "", $ZDANIYA[$zayavka_file_props['VYBOR_ZDANIYA']]['TITLE']);
        $zayavka_file_props['ZAYAVKA_TYPE']        = BP_PROPUSK_VODY?"пропуск ТМЦ (вода, канцтовары и т.п.)":"массовый пропуск";
        $zayavka_file_props['DATA_POSESHCHENIYA']  = implode("<br/>", $zayavka_file_props['DATA_POSESHCHENIYA']);

        if ($zayavka_file_props['KOLICHESTVO']) {
            $zayavka_file_props['KOLICHESTVO'] = '<p><strong>Количество</strong><br/>'.$zayavka_file_props['KOLICHESTVO'].'</p>';
        }
        
        if ($zayavka_file_props['KABINET']) {
            $zayavka_file_props['KABINET'] = '<p><strong>Кабинет</strong><br/>'.$zayavka_file_props['KABINET'].'</p>';
        }
        
        if ($zayavka_file_props['AVTO']) {
            $zayavka_file_props['AVTO'] = '<p><strong>Автомобили</strong><br/>'.implode("<br/>", $zayavka_file_props['AVTO']).'</p>';
        } else {
            $zayavka_file_props['AVTO'] = "";
        }
        
        if ($zayavka_file_props['NEOBKHODIM_LI_DOSTUP_NA_PARKOVKU']) {
            $zayavka_file_props['NEOBKHODIM_LI_DOSTUP_NA_PARKOVKU'] =
                    '<p><strong>Необходим доступ на парковку</strong><br/>'.$DOSTUP_NA_PARKOVKU[$zayavka_file_props['NEOBKHODIM_LI_DOSTUP_NA_PARKOVKU']].'</p>';
        }

        if ($zayavka_file_props['KONTAKTNYY_TELEFON']) {
            $zayavka_file_props['KONTAKTNYY_TELEFON'] = '<p><strong>Контактный телефон</strong><br/>'.$zayavka_file_props['KONTAKTNYY_TELEFON'].'</p>';
        }
        
        if ($zayavka_file_props['SPISOK_LITS']) {
            if (CModule::IncludeModule("nkhost.phpexcel")) {
                global $PHPEXCELPATH;
                require_once ($PHPEXCELPATH . '/PHPExcel/IOFactory.php');

                $reader = PHPExcel_IOFactory::createReader('Excel2007');
                if (!$reader->canRead($zayavka_file_props['SPISOK_LITS']['tmp_name'])) {
                    throw new Exception("Загруженный файл имеет неверное расширение");
                }

                $xls = PHPExcel_IOFactory::load($zayavka_file_props['SPISOK_LITS']['tmp_name']);
                $xls->setActiveSheetIndex(0);
                $sheet = $xls->getActiveSheet();

                for ($i = 2; $i <= $sheet->getHighestRow(); $i++) {
                    $litso = [];
                    $nColumn = PHPExcel_Cell::columnIndexFromString($sheet->getHighestColumn());
                    for ($j = 0; $j < $nColumn; $j++) {
                        $nColumnval = trim($sheet->getCellByColumnAndRow($j, $i)->getValue());
                        if (empty($nColumnval)) {
                            continue;
                        }
                        $litso[] = $nColumnval;
                    }
                    if (empty($litso)) {
                        continue;
                    }
                    $zayavka_file_props['SPISOK_LITS_STR'][] = implode(", ", $litso);
                }
            } else {
                $zayavka_file_props['SPISOK_LITS_STR'][] = "Во вложении";
            }
            $zayavka_file_props['SPISOK_LITS'] = "";
        }

        if ($zayavka_file_props['SPISOK_LITS_STR']) {
            $SPISOK_LITS_PRINT = '<ol style="padding:0 0 0 20px;margin:0;">';
            foreach ($zayavka_file_props['SPISOK_LITS_STR'] as $lits_item) {
                $ar_lits_item = array_filter(explode(",", $lits_item));
                if (empty($ar_lits_item)) {
                    continue;
                }
                $SPISOK_LITS_PRINT .= '<li><strong>'.current($ar_lits_item).'</strong> '.implode(", ", array_slice($ar_lits_item, 1)).'</li>';
            }
            $SPISOK_LITS_PRINT .= '</ol>';
           $zayavka_file_props['SPISOK_LITS_STR'] = $SPISOK_LITS_PRINT;
        } else {
           $zayavka_file_props['SPISOK_LITS_STR'] = "";
        }

        $doc_content = str_replace(
            array_map(function ($item) { return "#".$item."#"; }, array_keys($zayavka_file_props)),
            $zayavka_file_props,
            file_get_contents(__DIR__.'/zayavka.html')
        );

        $el = new CIBlockElement;
        $arLoadProductArray = [
              'MODIFIED_BY'        => $USER->GetID(),
              'IBLOCK_SECTION_ID'  => false,
              'IBLOCK_ID'          => BP_IBLOCK,
              'PROPERTY_VALUES'    => $arProps,
              'NAME'               => $APPLICATION->GetTitle(),
              'ACTIVE'             => "Y",
              'PREVIEW_TEXT'       => "",
        ];

        $documentId = $el->Add($arLoadProductArray);
        if (!$documentId) {
            throw new Exception($el->LAST_ERROR);
        }
         
        if (!$GLOBALS['setElementPDFValue']($documentId, 'FAYL_ZAYAVKI', $doc_content, "Заявка на пропуск для ".$SOTRUDNIK_VEDOMSTVA)) {
            CIBlockElement::Delete($documentId);
            throw new Exception("Не удалось создать файл");
        } else {
            $arErrorsTmp = array();
            $wfId = CBPDocument::StartWorkflow(
                BP_TEMPLATE_ID,
                ["lists", "BizprocDocument", $documentId],
                ['TargetUser' => "user_".$USER->GetId()],
                $arErrorsTmp
            );

            if (count($arErrorsTmp) > 0) {
                throw new Exception(array_reduce($arErrorsTmp, function ($carry, $item) { return $carry.".".$item['message']; }, ""));
            } else {
                $resp->code = "OK";
                $resp->message = "Заявка отправлена";
            }
        }
    } catch (Exception $exc) {
        $resp->code = "ERROR";
        $resp->message = $exc->getMessage();
    }

    header('Content-Type: application/json');
    echo json_encode($resp);
    die;
}
?>
<form class="needs-validation propusk-massovy" novalidate="" style="" id="propusk-massovy" action="<?=htmlspecialchars($_SERVER['REQUEST_URI'])?>" method="POST" autocomplete="Off">
    <input type="hidden" name="propusk-massovy" value="add">
    <?=bitrix_sessid_post()?>
    <div class="alert" style="display:none"></div>
    <div class="form-group row">
        <label class="col-sm-2 col-form-label ">Ведомство, подающее заявку</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" name="VEDOMSTVO_PODAYUSHCHEE_ZAYAVKU" value="<?=htmlspecialchars($arUser['DEPARTMENT'])?>" disabled>
        </div>
    </div>
    <div class="form-group row">
        <label class="col-sm-2 col-form-label ">Сотрудник ведомства</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" name="SOTRUDNIK_VEDOMSTVA" value="<?=htmlspecialchars($arUser['FIO']);?>" disabled>
        </div>
    </div>
    <div class="form-group row">
        <label class="col-sm-2 col-form-label ">Руководитель, с которым следует согласовать пропуск</label>
        <div class="col-sm-10">
            <div class="propusk-massovy__userselector">
            <?
                $IBLOCK_FIELDS['RUKOVODITEL']["LIST_SECTIONS_URL"]  = NULL;
                $IBLOCK_FIELDS['RUKOVODITEL']["SOCNET_GROUP_ID"]    = 0;
                $IBLOCK_FIELDS['RUKOVODITEL']["LIST_ELEMENT_URL"]   = "/bizproc/processes/#list_id#/element/#section_id#/#element_id#/";
                $IBLOCK_FIELDS['RUKOVODITEL']["LIST_FILE_URL"]      = "/bizproc/processes/#list_id#/file/#section_id#/#element_id#/#field_id#/#file_id#/";
                $IBLOCK_FIELDS['RUKOVODITEL']["SECTION_ID"]         = 0;
                $IBLOCK_FIELDS['RUKOVODITEL']["ELEMENT_ID"]         = 0;
                $IBLOCK_FIELDS['RUKOVODITEL']["VALUE"]              = ['n0'=>['VALUE'=>"",'DESCRIPTION'=>""]];
                $IBLOCK_FIELDS['RUKOVODITEL']["COPY_ID"]            = NULL;
                echo \Bitrix\Lists\Field::prepareFieldDataForEditForm($IBLOCK_FIELDS['RUKOVODITEL'])['value'];
            ?>
            </div>
        </div>
    </div>
    <div class="form-group row" style="display:none">
        <label class="col-sm-2 col-form-label">Организация, которой необходим пропуск</label>
        <div class="col-sm-10">
            <input type="text" class="form-control" name="ORGANIZATSIYA_KOTOROY_NEOBKHODIM_PROPUSK">
        </div>
    </div>
    <div class="form-group row">
        <label class="col-sm-2 col-form-label ">Выбор здания</label>
        <div class="col-sm-10">
            <select class="form-control" name="VYBOR_ZDANIYA" required onchange="$('.propusk-massovy__avto,.propusk-massovy__avto-need').toggleClass('available',(this.options[this.selectedIndex].getAttribute('data-parking') == '1')); $('.zayavka-comment').prop('hidden', this.value != '1093')">
                <option value="0" disabled selected>Выбрать</option>
                <?foreach($ZDANIYA as $zdaniya_item):?>
                    <option value="<?=$zdaniya_item['ID']?>" data-parking="<?=($zdaniya_item['PARKING']?"1":"0")?>"><?=$zdaniya_item['TITLE']?></option>
                <?endforeach;?>
            </select>
        </div>
    </div>
    <?if (BP_PROPUSK_VODY):?>
        <div class="form-group row zayavka-comment" hidden>
            <label class="col-sm-2 col-form-label ">Комментарий</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="COMMENT">
                <small class="text-uppercase">если необходим доступ на парковку во внутреннем дворе – сообщить в комментарии</small>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label ">Экспедитор</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="SPISOK_LITS_STR[]" required>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label ">Контактный телефон</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="KONTAKTNYY_TELEFON">
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label ">Количество</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="KOLICHESTVO" required>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label ">Дата и время посещения</label>
            <div class="col-sm-10">
                <div class="input-group mb-1">
                    <input type="text" class="form-control" name="DATA_POSESHCHENIYA[]" onclick="BX.calendar({node: this, field: this, bTime: true, bHideTime:false});">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" onclick="var _inpg = $(this).closest('.input-group'); if (_inpg.siblings('.input-group').length) {_inpg.remove();}">&times;</button>
                    </div>
                </div>
                <button type="button" class="btn btn-info" onclick="$(this).prev().clone().insertBefore(this).find('input').val(null)">Добавить</button>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label ">Цель посещения</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="TSEL_POSESHCHENIYA" required>
            </div>
        </div>
        <div class="card mb-4 propusk-massovy__avto need">
            <div class="card-body">
                <div class="propusk-massovy__avto__item" data-index="0">
                    <div class="card">
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label ">Марка ТС</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="AVTO[0][]">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label ">Номер ТС</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="AVTO[0][]">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label ">Водитель ФИО</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="AVTO[0][]">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?else:?>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label ">Список лиц</label>
            <div class="col-sm-10">
                <div class="row">
                    <div class="col">
                        <div class="mb-2">
                            <input type="file" name="SPISOK_LITS"><br/>
                            <small>Просьба приложить файл с заполненными данными по столбцам: ФИО – Организация – Должность. <a target="_blank" download="Пример.xlsx" href="/upload/propusk-massovy-primer.xlsx">Скачать пример</a></small>                    
                        </div>
                        <div class="mb-2">
                            <button type="button" class="btn btn-primary btn-sm" onclick="$('input[name=SPISOK_LITS]').val(null);$(this).closest('.row').hide().next().show();">Указать вручную</button>    
                        </div>
                        
                    </div>
                </div>
                <div style="display:none;">
                    <div class="mb-2">
                        <table class="table table-stripped">
                            <thead>
                                <tr>
                                    <th>ФИО</th>
                                    <th>Организация</th>
                                    <th>Должность</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" oninput="propusk_massovy__spl_change.call(this)">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" oninput="propusk_massovy__spl_change.call(this)">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" oninput="propusk_massovy__spl_change.call(this)">
                                        <input type="text" class="spisok_lits_str" name="SPISOK_LITS_STR[]" style="display:none">
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-block btn-danger" onclick="propusk_massovy__spl_del.call(this)">Удалить</button>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="4"><button type="button" class="btn btn-sm btn-primary" onclick="propusk_massovy__spl_new.call(this)">Добавить</button></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label ">Дата и время посещения</label>
            <div class="col-sm-10">
                <div class="input-group mb-1">
                    <input type="text" class="form-control" name="DATA_POSESHCHENIYA[]" onclick="BX.calendar({node: this, field: this, bTime: true, bHideTime:false});">
                    <div class="input-group-append">
                        <button class="btn btn-outline-secondary" type="button" onclick="var _inpg = $(this).closest('.input-group'); if (_inpg.siblings('.input-group').length) {_inpg.remove();}">&times;</button>
                    </div>
                </div>
                <button type="button" class="btn btn-info" onclick="$(this).prev().clone().insertBefore(this).find('input').val(null)">Добавить</button>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label ">Кабинет</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="KABINET" required>
            </div>
        </div>
        <div class="form-group row">
            <label class="col-sm-2 col-form-label ">Цель посещения</label>
            <div class="col-sm-10">
                <input type="text" class="form-control" name="TSEL_POSESHCHENIYA" required>
            </div>
        </div>
        <div class="form-group row propusk-massovy__avto-need">
            <label class="col-sm-2 col-form-label ">Необходим доступ на парковку</label>
            <div class="col-sm-10">
                <select class="form-control" name="NEOBKHODIM_LI_DOSTUP_NA_PARKOVKU" onchange="$('.propusk-massovy__avto').toggleClass('need',(this.options[this.selectedIndex].textContent == 'Да'));">
                    <option value="0" disabled selected>Выбрать</option>
                    <?foreach($DOSTUP_NA_PARKOVKU as $id=>$title):?>
                        <option value="<?=$id?>" ><?=$title?></option>
                    <?endforeach;?>
                </select>
            </div>
        </div>
        
        <div class="card mb-4 propusk-massovy__avto">
            <div class="card-body">
                <div class="propusk-massovy__avto__item mb-3" data-index="0">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="propusk-massovy__avto__indx mb-3">Автомобиль №</h5>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label ">Марка ТС</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="AVTO[0][]">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label ">Номер ТС</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="AVTO[0][]">
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label ">Водитель ФИО</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" name="AVTO[0][]">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mb-2">
                    <button class="btn btn-dark" type="button" onclick="propusk_massovy__avto_new()">Добавить еще</button>
                </div>
            </div>
        </div>
    <?endif;?>

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
if (!$included_window) {
    require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
}?>