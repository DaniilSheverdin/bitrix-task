<?php

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

function generateBICSV()
{
    if (CModule::IncludeModule("iblock")) {
        $arHeader = [
            'Название',
            'Краткое наименование показателя',
            'Основание установления целевого показателя',
            'Целевое значение',
            'Текущее значение',
            '% исполнения',
            'Примечание',
            'Отдел',
            'Дата',
            'Флаг',
        ];

        $arIndicators = array();
        $arCSV = array();
        $arCSV[0] = $arHeader;
        $count = 0;
        $arSelect = array("ID", "IBLOCK_ID", "NAME", "DATE_ACTIVE_FROM", "PROPERTY_*");
        $arFilter = array("IBLOCK_ID" => 524, "ACTIVE_DATE" => "Y", "ACTIVE" => "Y", '>=DATE_ACTIVE_FROM' => date($DB->DateFormatToPHP(CLang::GetDateFormat("SHORT")), strtotime('-7 day')));
        $res = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);

        while ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            $arIndicators[$count]['NAME'] = $arFields['NAME'];
            $arProps = $ob->GetProperties();
            $arIndicators[$count]['SHORT_NAME'] = $arProps['ATT_SHORT_NAME']['VALUE'];
            $arIndicators[$count]['BASE_SET'] = $arProps['ATT_BASE_SET']['VALUE'];
            $arIndicators[$count]['TARGET_VALUE'] = $arProps['ATT_TARGET_VALUE']['VALUE'];
            $arIndicators[$count]['STATE_VALUE'] = $arProps['ATT_STATE_VALUE']['VALUE'];
            $arIndicators[$count]['PERCENT_EXEC'] = $arProps['ATT_PERCENT_EXEC']['VALUE'];
            $arIndicators[$count]['COMMENT'] = $arProps['ATT_COMMENT']['VALUE']['TEXT'];
            $arIndicators[$count]['DEPARTMENT'] = $arProps['ATT_DEPARTMENT']['VALUE'];
            $arIndicators[$count]['DATE'] = $arProps['ATT_DATE']['VALUE'];
            $arIndicators[$count]['FLAG'] = $arProps['ATT_FLAG']['VALUE'];

            $count++;
        }

        $cnt = 1;
        foreach ($arIndicators as $value) {
            $arCSV[$cnt] = array(
                $value['NAME'],
                $value['SHORT_NAME'],
                $value['BASE_SET'],
                $value['TARGET_VALUE'],
                $value['STATE_VALUE'],
                $value['PERCENT_EXEC'],
                $value['COMMENT'],
                $value['DEPARTMENT'],
                $value['DATE'],
                $value['FLAG'],
            );
            $cnt++;
        }

        $fp = fopen('../bi.csv', 'w');
        fputs($fp, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

        foreach ($arCSV as $fields) {
            fputcsv($fp, $fields, ';');
        }

        fclose($fp);
    }
}
