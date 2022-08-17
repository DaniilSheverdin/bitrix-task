<<<<<<< HEAD
<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}

\Bitrix\Main\Loader::includeModule("iblock");
$root       = $this->GetRootActivity();
$docID      = $root->GetDocumentId();
$docService = $this->workflow->GetService("DocumentService");
$docFields  = $docService->GetDocument($docID);

global $declOfNum, $userFields;

$this_as =& $this;
$try_it =  function () use (&$root, $this_as, &$docFields, &$userFields, &$declOfNum) {
    $DATA_S = $this_as->GetVariable('DATA_S');
    $NA_KOL_VO_KALENDARNYKH_DNEY = $this_as->GetVariable('NA_KOL_VO_KALENDARNYKH_DNEY');
    $PRICHINA_V_SVYAZI_S = $this_as->GetVariable('PRICHINA_V_SVYAZI_S');
    $UF_HEAD = str_replace("user_", "", $this_as->GetVariable('UF_HEAD'));

    if (empty($DATA_S)) {
        return ("Не указана дата начала");
    }
    if (empty($NA_KOL_VO_KALENDARNYKH_DNEY)) {
        return ("Не указано количество дней");
    }
    if (empty($PRICHINA_V_SVYAZI_S)) {
        return ("Не указана причина");
    }
    if (empty($UF_HEAD)) {
        return ("Не указан руководитель ОИВ/Органа");
    }
    
    $arSotrudnik    = $userFields(str_replace("user_", "", $docFields['CREATED_BY']));
    $arRukovoditel  = $userFields($UF_HEAD);

    if (!is_array($arSotrudnik)) {
        return $arSotrudnik;
    }
    if (!is_array($arRukovoditel)) {
        return $arRukovoditel;
    }
    $DATE                       = date('d.m.Y');
    $DATA_S                     = new DateTime($DATA_S);
    $NA_KOL_VO_KALENDARNYKH_DNEY= (int)$NA_KOL_VO_KALENDARNYKH_DNEY;

    if ($NA_KOL_VO_KALENDARNYKH_DNEY < 1) {
        return ("Не указано количество дней");
    }

    $NA_KOL_VO_KALENDARNYKH_DNEY.= " ".$declOfNum($NA_KOL_VO_KALENDARNYKH_DNEY, ['календарный день', 'календарных дня', 'календарных дней']);

    if (is_array($docFields['PROPERTY_PROSHU_PREDOSTAVIT_OTPUSK_BEZ_SOKHRANENIYA'])) {
        $docFields['PROPERTY_PROSHU_PREDOSTAVIT_OTPUSK_BEZ_SOKHRANENIYA'] = current($docFields['PROPERTY_PROSHU_PREDOSTAVIT_OTPUSK_BEZ_SOKHRANENIYA']);
    }
    
    $zayavlenie_file = mb_stripos($docFields['PROPERTY_PROSHU_PREDOSTAVIT_OTPUSK_BEZ_SOKHRANENIYA'], "заработной платы") !== false?__DIR__.'/bez_sohraneniya.html':__DIR__.'/bez_soderjaniya.html';
    $doc_content = str_replace(
        [
            '#DATA#',
            '#DATA_S#',
            '#NA_KOL_VO_KALENDARNYKH_DNEY#',
            '#PRICHINA_V_SVYAZI_S#',
            '#LAST_NAME#',
            '#NAME#',
            '#SECOND_NAME#',
            '#FIO_ROD#',
            '#DOLJNOST_ROD#',
            '#PODPIS#',
            '#RUKOVODITEL_OIV__DOLJNOST#',
            '#RUKOVODITEL__FIO_INIC#',
        ],
        [
            $DATE,
            $DATA_S->format('d.m.Y'),
            $NA_KOL_VO_KALENDARNYKH_DNEY,
            $PRICHINA_V_SVYAZI_S,
            $arSotrudnik['LAST_NAME'],
            $arSotrudnik['FIRST_NAME'],
            $arSotrudnik['MIDDLE_NAME'],
            $arSotrudnik['FIO_ROD'],
            $GLOBALS['mb_lcfirst']($arSotrudnik['DOLJNOST_ROD']),
            "",
            $GLOBALS['mb_ucfirst']($arRukovoditel['UF_WORK_POSITION']),
            $arRukovoditel['FIO_INIC']
        ],
        file_get_contents($zayavlenie_file)
    );

    $doc_file = tempnam(sys_get_temp_dir(), 'bp_');
    file_put_contents($doc_file, $doc_content);


    $doc_file_pdf_data = shell_exec('/usr/bin/php72 -f '.realpath(__DIR__.'/../../libs/mpdf/converter.php').' '.$doc_file);
    if (empty($doc_file_pdf_data)) {
        return ("Ошибка создания pdf".$doc_file);
    }
    
    $doc_file_id = CFile::SaveFile([
        'name'			=> "Заявление об отпуске ".$arSotrudnik['FIO_INIC'].".pdf",
        'type'			=> "application/pdf",
        'content'		=> $doc_file_pdf_data,
        'MODULE_ID'		=> 'bizproc',
    ], "bizproc");
    if (empty($doc_file_id)) {
        return ("Ошибка сохранения pdf");
    }

    shell_exec('/usr/bin/php72 -f '.realpath(__DIR__.'/../../libs/mpdf/mpdf_arch_put.php').' '.$doc_file.' '.$doc_file_id);

    $root->setVariable('ZAYAVLENIE_FILE', $doc_file_id);
    return true;
};
$res = $try_it();

if ($res !== true) {
    $ERRORS = $root->getVariable('ERRORS')?:[];
    $ERRORS[] = $res;
    $root->setVariable('ERRORS', $ERRORS);
}
=======
<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}

\Bitrix\Main\Loader::includeModule("iblock");
$root       = $this->GetRootActivity();
$docID      = $root->GetDocumentId();
$docService = $this->workflow->GetService("DocumentService");
$docFields  = $docService->GetDocument($docID);

global $declOfNum, $userFields;

$this_as =& $this;
$try_it =  function () use (&$root, $this_as, &$docFields, &$userFields, &$declOfNum) {
    $DATA_S = $this_as->GetVariable('DATA_S');
    $NA_KOL_VO_KALENDARNYKH_DNEY = $this_as->GetVariable('NA_KOL_VO_KALENDARNYKH_DNEY');
    $PRICHINA_V_SVYAZI_S = $this_as->GetVariable('PRICHINA_V_SVYAZI_S');
    $UF_HEAD = str_replace("user_", "", $this_as->GetVariable('UF_HEAD'));

    if (empty($DATA_S)) {
        return ("Не указана дата начала");
    }
    if (empty($NA_KOL_VO_KALENDARNYKH_DNEY)) {
        return ("Не указано количество дней");
    }
    if (empty($PRICHINA_V_SVYAZI_S)) {
        return ("Не указана причина");
    }
    if (empty($UF_HEAD)) {
        return ("Не указан руководитель ОИВ/Органа");
    }
    
    $arSotrudnik    = $userFields(str_replace("user_", "", $docFields['CREATED_BY']));
    $arRukovoditel  = $userFields($UF_HEAD);

    if (!is_array($arSotrudnik)) {
        return $arSotrudnik;
    }
    if (!is_array($arRukovoditel)) {
        return $arRukovoditel;
    }
    $DATE                       = date('d.m.Y');
    $DATA_S                     = new DateTime($DATA_S);
    $NA_KOL_VO_KALENDARNYKH_DNEY= (int)$NA_KOL_VO_KALENDARNYKH_DNEY;

    if ($NA_KOL_VO_KALENDARNYKH_DNEY < 1) {
        return ("Не указано количество дней");
    }

    $NA_KOL_VO_KALENDARNYKH_DNEY.= " ".$declOfNum($NA_KOL_VO_KALENDARNYKH_DNEY, ['календарный день', 'календарных дня', 'календарных дней']);

    if (is_array($docFields['PROPERTY_PROSHU_PREDOSTAVIT_OTPUSK_BEZ_SOKHRANENIYA'])) {
        $docFields['PROPERTY_PROSHU_PREDOSTAVIT_OTPUSK_BEZ_SOKHRANENIYA'] = current($docFields['PROPERTY_PROSHU_PREDOSTAVIT_OTPUSK_BEZ_SOKHRANENIYA']);
    }
    
    $zayavlenie_file = mb_stripos($docFields['PROPERTY_PROSHU_PREDOSTAVIT_OTPUSK_BEZ_SOKHRANENIYA'], "заработной платы") !== false?__DIR__.'/bez_sohraneniya.html':__DIR__.'/bez_soderjaniya.html';
    $doc_content = str_replace(
        [
            '#DATA#',
            '#DATA_S#',
            '#NA_KOL_VO_KALENDARNYKH_DNEY#',
            '#PRICHINA_V_SVYAZI_S#',
            '#LAST_NAME#',
            '#NAME#',
            '#SECOND_NAME#',
            '#FIO_ROD#',
            '#DOLJNOST_ROD#',
            '#PODPIS#',
            '#RUKOVODITEL_OIV__DOLJNOST#',
            '#RUKOVODITEL__FIO_INIC#',
        ],
        [
            $DATE,
            $DATA_S->format('d.m.Y'),
            $NA_KOL_VO_KALENDARNYKH_DNEY,
            $PRICHINA_V_SVYAZI_S,
            $arSotrudnik['LAST_NAME'],
            $arSotrudnik['FIRST_NAME'],
            $arSotrudnik['MIDDLE_NAME'],
            $arSotrudnik['FIO_ROD'],
            $GLOBALS['mb_lcfirst']($arSotrudnik['DOLJNOST_ROD']),
            "",
            $GLOBALS['mb_ucfirst']($arRukovoditel['UF_WORK_POSITION']),
            $arRukovoditel['FIO_INIC']
        ],
        file_get_contents($zayavlenie_file)
    );

    $doc_file = tempnam(sys_get_temp_dir(), 'bp_');
    file_put_contents($doc_file, $doc_content);


    $doc_file_pdf_data = shell_exec('/usr/bin/php72 -f '.realpath(__DIR__.'/../../libs/mpdf/converter.php').' '.$doc_file);
    if (empty($doc_file_pdf_data)) {
        return ("Ошибка создания pdf".$doc_file);
    }
    
    $doc_file_id = CFile::SaveFile([
        'name'			=> "Заявление об отпуске ".$arSotrudnik['FIO_INIC'].".pdf",
        'type'			=> "application/pdf",
        'content'		=> $doc_file_pdf_data,
        'MODULE_ID'		=> 'bizproc',
    ], "bizproc");
    if (empty($doc_file_id)) {
        return ("Ошибка сохранения pdf");
    }

    shell_exec('/usr/bin/php72 -f '.realpath(__DIR__.'/../../libs/mpdf/mpdf_arch_put.php').' '.$doc_file.' '.$doc_file_id);

    $root->setVariable('ZAYAVLENIE_FILE', $doc_file_id);
    return true;
};
$res = $try_it();

if ($res !== true) {
    $ERRORS = $root->getVariable('ERRORS')?:[];
    $ERRORS[] = $res;
    $root->setVariable('ERRORS', $ERRORS);
}
>>>>>>> e0a0eba79 (init)
