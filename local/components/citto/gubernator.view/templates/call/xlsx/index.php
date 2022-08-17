<?

//include_once $_SERVER['DOCUMENT_ROOT'].'/local/php_interface/libs/phpexcel/PHPExcel.php';
global $APPLICATION;

if (CModule::IncludeModule("nkhost.phpexcel")) {
    $APPLICATION->RestartBuffer();

    global $PHPEXCELPATH;
    require_once($PHPEXCELPATH . '/PHPExcel/IOFactory.php');

    $APPLICATION->EndBufferContentMan();
    $objPXL = new PHPExcel();

    $objPXL->setActiveSheetIndex(0);
    $aSh = $objPXL->getActiveSheet();

    $aSh->setTitle("Звонки Губернатору ТО");

    $style_wrap = array(
        //рамки
        'borders' => array(
            'outline' => array(
                'style' => PHPExcel_Style_Border::BORDER_THICK,
                'color' => array(
                    'rgb' => '000000'
                )
            )
        ),
        'fill' => array(
            'type' => PHPExcel_STYLE_FILL::FILL_SOLID,
            'color' => array(
                'rgb' => 'CFCFCF'
            )
        )
    );

    $style_head = array(
        //Шрифт
        'font' => array(
            'bold' => true,
            'size' => 14
        )
    );

    $style_subhead = array(
        //Шрифт
        'font' => array(
            'bold' => true,
            'size' => 16
        )
    );

    $aSh->getColumnDimension('A')->setWidth(30);
    $aSh->getColumnDimension('B')->setWidth(40);
    $aSh->getColumnDimension('C')->setWidth(40);
    $aSh->getColumnDimension('D')->setWidth(70);
    $aSh->getColumnDimension('E')->setWidth(50);

    $aSh->getStyle('A1')->applyFromArray($style_wrap);
    $aSh->getStyle('B1')->applyFromArray($style_wrap);
    $aSh->getStyle('C1')->applyFromArray($style_wrap);
    $aSh->getStyle('D1')->applyFromArray($style_wrap);
    $aSh->getStyle('E1')->applyFromArray($style_wrap);
    $aSh->getStyle('A1:E1')->applyFromArray($style_head);

    $aSh->setCellValue('A1', 'Дата и время звонка');
    $aSh->setCellValue('B1', 'ФИО');
    $aSh->setCellValue('C1', 'Организация');
    $aSh->setCellValue('D1', 'Вопрос');
    $aSh->setCellValue('E1', 'Примечание');

    $i = 2;

    foreach ($arResult['ITEMS_CALL_GROUP'] as $sDate => $arList) {
        $i++;
        $aSh->mergeCells('A' . $i . ':E' . $i);
        $aSh->getStyle('A' . $i . ':E' . $i)->applyFromArray($style_subhead);

        $aSh->setCellValue('A' . $i, $sDate . ' г.');

        $i++;
        foreach ($arList as $arCall) {
            $aSh->setCellValue('A' . $i, $arCall['UF_TIMECALL']);
            $aSh->setCellValue('B' . $i, $arCall['UF_FIOCALL']);
            $aSh->setCellValue('C' . $i, $arCall['UF_ORGCALL']);
            $aSh->setCellValue('D' . $i, $arCall['UF_QUESTION']);
            $aSh->setCellValue('E' . $i, $arCall['UF_NOTECALL']);
            $i++;
        }
    }

    header("Content-Type:application/vnd.ms-excel");
    header("Content-Disposition:attachment;filename=zvonki_gubernatoru.xls");

    $objW = PHPExcel_IOFactory::createWriter($objPXL, 'Excel5');
    $objW->save('php://output');
}
