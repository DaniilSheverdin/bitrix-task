<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";

CModule::IncludeModule("form");

try {
    if (CModule::IncludeModule("nkhost.phpexcel")) {
        global $PHPEXCELPATH;
        require_once($PHPEXCELPATH . '/PHPExcel/IOFactory.php');

        $intWFID = $_REQUEST['WEB_FORM_ID'];
        $arInfoForm = CForm::GetByID($intWFID)->Fetch();

        if (empty($arInfoForm)) {
           throw new Exception('Форма не найдена');
        }

        $aPerm = CForm::GetPermission($intWFID);
        if ($aPerm < 20) {
            throw new Exception('У вас нет прав на скачивание отчета');
        }

        $obExcel = new PHPExcel();
        $obExcel->setActiveSheetIndex(0);
        $obExcel->getProperties()->setCreator("Sivers");
        $aSheet = $obExcel->getActiveSheet();
        $aSheet->setTitle('Выгрузка по форме №' . $intWFID);
        $obExcel->setActiveSheetIndex(0);

        $arBackgroundHead = [
            'fill' => [
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => '999999']
            ]
        ];

        $obFormResult = CFormResult::GetList($intWFID, $lBy, $lFrder, [], $lFilter);

        if ($obFormResult->AffectedRowsCount() > 0) {
            $arFormList = [];

            $obNameFields = CFormField::GetList($intWFID, 'ALL', $lBy = 's_sort', $lFrder = 'asc', [], $lFilter);
            $arListFilter = [];
            $arAllResult = [];

            $arAllResult[0]['STATUS'] = 'Статус';
            $arAllResult[0]['DATE_CREATE'] = 'Дата создания';

            while ($arFiledStruct = $obNameFields->Fetch()) {
                $arListFilter[] = $arFiledStruct['SID'];
                $arAllResult[0][$arFiledStruct['SID']] = $arFiledStruct['TITLE'];
            }

            $i = 1;
            while ($arItem = $obFormResult->Fetch()) {
                $arAnsw = $arAnsw2 = [];
                $arAllResult[$i]['STATUS'] = $arItem['STATUS_TITLE'];
                $arAllResult[$i]['DATE_CREATE'] = $arItem['DATE_CREATE'];

                foreach ($arListFilter as $item) {
                    $arAllResult[$i][$item] = '';
                }

                CFormResult::GetDataByID(
                    $arItem['ID'],
                    [],
                    $arAnsw,
                    $arAnsw2
                );
                foreach ($arAnsw2 as $k => $item) {
                    $arTRes = array_shift($item);
                    $arAllResult[$i][$k] = $arTRes['USER_TEXT'];
                }
                $intCols = count($arAllResult[$i]);
                $i++;
            }
        }

        $arLetters = array_slice(range('A', 'Z'), 0, $intCols);

        foreach ($arLetters as $strLetter) {
            $obExcel->getActiveSheet()->getColumnDimension($strLetter)->setWidth(50);
            $obExcel->getActiveSheet()->getStyle($strLetter.'1')->applyFromArray($arBackgroundHead);
        }

        foreach ($arAllResult as $k => $arListed) {
            $i = 0;
            foreach ($arListed as $j => $strData) {
                $obExcel->getActiveSheet()->setCellValue($arLetters[$i] . strval($k + 1), $strData);
                $i++;
            }
        }

        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $arInfoForm['NAME'] . '.xls"');
        header('Cache-Control: max-age=0');
        $obXlsWriter = PHPExcel_IOFactory::createWriter($obExcel, 'Excel5');
        $obXlsWriter->save('php://output');
        exit;
    }
} catch (\Exception $e) {
    echo $e->getMessage();
    exit;
}