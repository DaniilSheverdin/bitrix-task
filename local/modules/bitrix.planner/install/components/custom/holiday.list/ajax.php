<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (CModule::IncludeModule("nkhost.phpexcel")){
    global $PHPEXCELPATH;
    require_once ($PHPEXCELPATH . '/PHPExcel/IOFactory.php');
}

use Bitrix\Main\Engine\Controller, Bitrix\Main\IO, Bitrix\Main\Application;

class CustomAjaxController extends Controller
{
    /**
     * @return array
     */
    public function configureActions()
    {
        return [
            'excel' => [
                'prefilters' => []
            ],
            'getusers' => [
                'prefilters' => []
            ],
            'setheads' => [
                'prefilters' => []
            ],
            'getviolations' => [
                'prefilters' => []
            ],
            'signature' => [
                'prefilters' => []
            ]
        ];
    }

    public static function zipSigXls($sign, $file, $path)
    {
        $time = time();
        $path_tmp = Application::getDocumentRoot() . "/upload/tmp/{$time}";
        $dir_tmp = new IO\Directory($path_tmp);
        $dir_tmp->create();

        $fileXLS = new IO\File("{$path_tmp}/{$time}.xls");
        $fileSIG = new IO\File("{$path_tmp}/{$time}.sig");

        $fileXLS->putContents($file);
        $fileSIG->putContents($sign);

        // Массив со списком путей, до архивируемых файлов
        $arFiles = Array("{$path_tmp}/{$time}.xls", "{$path_tmp}/{$time}.sig");
        foreach($arFiles as $iFileID) {
            $arPackFiles[] = $iFileID;
        }

        // Архивирование в zip
        $packarc = CBXArchive::GetArchive("{$path_tmp}/{$time}.zip");
        $packarc->SetOptions(Array( //Убираем путь до upload
            "REMOVE_PATH" => $path_tmp,
        ));
        $pRes = $packarc->Pack($arPackFiles);

        $dir = new IO\Directory($path);
        if(!$dir->isExists()) $dir->create();
        $fileZIP = new IO\File("{$path_tmp}/{$time}.zip");
        if($fileZIP->rename("{$path}/{$time}.zip"))
            $dir_tmp->delete();

        return explode(Application::getDocumentRoot(), "{$path}/{$time}.zip")[1];
    }

    public static function signatureAction($sign = 'sign', $file = 'file', $year = 'year')
    {
        $sModule = 'holiday.list';
        return self::zipSigXls($sign, base64_decode($file), Application::getDocumentRoot() . "/upload/{$sModule}/{$year}");
    }

    public static function getviolationsAction($arViolations = []) {
        $objPHPExcel = new PHPExcel();
        ob_start();
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Нарушения');

        $sheet->setCellValue("A1", "ФИО");
        $sheet->setCellValue("B1", "Нарушения");
        $sheet->getRowDimension(1)->setRowHeight(20);
        $sheet->getStyle("A1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->getStyle("B1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $index = 2;

        foreach ($arViolations as $k => $v) {
            $violations = explode(PHP_EOL, $v['violations']);
            $violations = implode('', $violations);

            $sheet->setCellValue("A{$index}", "{$v['fio']}");
            $sheet->setCellValue("B{$index}", "{$violations}");

            $sheet->getRowDimension($index)->setRowHeight(100);

            $sheet->getStyle("A{$index}")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $sheet->getStyle("B{$index}")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

            $index++;
        }

        $sheet->getColumnDimensionByColumn("A")->setAutoSize(true);
        $sheet->getColumnDimension("B")->setWidth(50);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        $xlsData = ob_get_contents();
        ob_end_clean();
        $response = 'data:application/vnd.ms-excel;base64,' . base64_encode($xlsData);
        return $response;
    }

    public static function setheadsAction($heads = 'heads', $userId = 'userId', $oldHeads = 'oldHeads')
    {

        global $USER;
        $USER->Update($userId, ['UF_THIS_HEADS' => $heads]);

        if($heads != '' || $oldHeads !='') {
            $arAction = [];
            $arMerge = array_unique(array_merge(explode('|', $heads),explode('|', $oldHeads)));

            foreach ($arMerge as $item) {
                if(empty($item)) continue;

                if(in_array($item, explode('|', $oldHeads)) && !in_array($item, explode('|', $heads))) $arAction[$item] = 'delete';
                else if(in_array($item, explode('|', $oldHeads)) && in_array($item, explode('|', $heads))) return;
                else $arAction[$item] = 'add';
            }



            $arMerge = implode('|', $arMerge);
            $arHeads = $USER->GetList($by = '', $order = '', ['ID' => $arMerge], ['SELECT' => ['UF_SUBORDINATE']]);

            while($a = $arHeads->GetNext()) {
                if(isset($a['~UF_SUBORDINATE'])) {
                    $us = json_decode($a['~UF_SUBORDINATE']);
                    if($arAction[$a['ID']] == 'add' && !in_array($userId, $us)) array_push($us, $userId);
                    else if($arAction[$a['ID']] == 'delete' && in_array($userId, $us)) unset($us[array_search($userId, $us)]);
                    else return;
                }
                else $us = array_values([$userId]);

                if(!isset($us)) $us = '';
                else $us = json_encode(array_values($us));

                $USER->Update($a['ID'], ['UF_SUBORDINATE' => $us]);
            }
        }
        return $us;
    }

    public static function getusersAction($word = 'word')
    {
        $arUsers = [];
        $users = CUser::GetList($by = 'LAST_NAME', $order = "asc");
        while ($u = $users->getNext()) {
            if (!$u['SECOND_NAME']) continue;
            $fio = "{$u['LAST_NAME']} {$u['NAME']} {$u['SECOND_NAME']}";

            if (stripos(mb_strtoupper($fio), mb_strtoupper($word)) === false) continue;

            $arUsers[$u['ID']] = ['fio' => $fio];
        }
        return $arUsers;
    }

    public static function excelAction($data = 'data', $users = 'users', $year = 'year', $file = 'file', $sign = 'sign', $introduction = 'introduction', $sCertInfo = 'sCertInfo')
    {
        usort($data, function ($x, $y) {
            return ($x['PROPERTY_USER_VALUE'] > $y['PROPERTY_USER_VALUE']);
        });

        $objPHPExcel = new PHPExcel();
        ob_start();
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Лист');

        $sheet->mergeCells("A1:G1");
        $sheet->setCellValue("A1", "");

        $sheet->mergeCells("H1:K1");
        $sheet->setCellValue("H1", "УТВЕРЖДАЮ");

        $sheet->mergeCells("A2:G2");
        $sheet->setCellValue("A2", "");

        $sheet->mergeCells("H2:K2");
        $sheet->setCellValue("H2", "Заместитель Губернатора Тульской области – руководитель аппарата правительства Тульской области - начальник главного управления государственной службы и кадров аппарата правительства Тульской области");

        $sheet->mergeCells("A3:G3");
        $sheet->setCellValue("A3", "");

        $sheet->mergeCells("H3:K3");
        $sheet->setCellValue("H3", "____________________ Г.И. Якушкина");

        $sheet->mergeCells("A4:G4");
        $sheet->setCellValue("A4", "");

        $sheet->mergeCells("H4:K4");
        $sheet->setCellValue("H4", "\"____\" _____________________ 20__ г.");

        $sheet->mergeCells("B6:D6");
        $sheet->setCellValue("B6", "График отпусков");
        $sheet->mergeCells("E6:H6");
        $sheet->setCellValue("I6", "на $year год");
        $sheet->mergeCells("I6:K6");

        $sheet->mergeCells("E7:H7");
        $sheet->setCellValue("E7", "(наименование подразделения аппарата, органа исполнительной власти)");

        $sheet->mergeCells("A9:B11");
        $sheet->setCellValue("A9", "Фамилия, имя, отчество (полностью)");

        $sheet->mergeCells("C9:E11");
        $sheet->setCellValue("C9", "Должность по штатному расписанию");

        $sheet->mergeCells("F9:J9");
        $sheet->setCellValue("F9", "ОТПУСК");

        $sheet->mergeCells("F10:F11");
        $sheet->setCellValue("F10", "ОТПУСК");

        $sheet->mergeCells("G10:H10");
        $sheet->setCellValue("G10", "дата");

        $sheet->setCellValue("G11", "запланированная");

        $sheet->setCellValue("H11", "фактическая");

        $sheet->mergeCells("I10:J10");
        $sheet->setCellValue("I10", "перенесение отпуска");

        $sheet->setCellValue("I11", "основание (документ)");

        $sheet->setCellValue("J11", "дата предполагаемого отпуска");

        $sheet->mergeCells("K9:K11");
        $sheet->setCellValue("K9", "Примечание");

        $sheet->mergeCells("A12:B12");
        $sheet->setCellValue("A12", "1");

        $sheet->mergeCells("C12:E12");
        $sheet->setCellValue("C12", "2");

        $rows = ['F', 'G', 'H', 'I', 'J', 'K'];
        $cycle = 3;
        foreach ($rows as $i) {
            $sheet->setCellValue($i . "12", $cycle);
            $cycle++;
        }

        $iRow = 13;
        foreach ($data as $v) {
            if ($v['ACTIVE'] != 'N') {
                $cuser = $users[$v["PROPERTY_USER_VALUE"]];
                $position = $cuser['WORK_POSITION'];
                $fio = $cuser['LAST_NAME'] . ' ' . $cuser['NAME'] . ' ' . $cuser['SECOND_NAME'];
                $from = $v["ACTIVE_FROM"];
                $to = $v["ACTIVE_TO"];
                $count = $v["PERIOD"] / 86400;

                $sheet->mergeCells("A$iRow:B$iRow");
                $sheet->setCellValue("A$iRow", $fio);

                $sheet->mergeCells("C$iRow:E$iRow");
                $sheet->setCellValue("C$iRow", $position);

                $sheet->setCellValue("F$iRow", $count);
                $sheet->setCellValue("G$iRow", $from);

                $iRow++;
            }

        }
        $endVacations = $iRow - 1;

        $iRow = $iRow + 2;

        $sheet->mergeCells("A$iRow:F$iRow");
        $sheet->setCellValue("A$iRow", "Наименование должности руководителя подразделения аппарата правительства (органа исполнительной власти)  Тульской области");
        $sheet->mergeCells("G$iRow:H$iRow");

        $sheet->mergeCells("J$iRow:K$iRow");
        $sheet->setCellValue("J$iRow", 'Инициалы, фамилия');

        $iRow++;
        $sheet->mergeCells("G$iRow:I$iRow");
        $sheet->setCellValue("G$iRow", '(подпись)');

        $iRow = $iRow + 2;
        $sheet->mergeCells("A$iRow:E$iRow");
        $sheet->setCellValue("A$iRow", 'СОГЛАСОВАНО');

        $iRow++;

        $sheet->mergeCells("A$iRow:E$iRow");
        $sheet->setCellValue("A$iRow", 'Первый заместитель (заместитель) Губернатора Тульской области (заместитель председателя правительства Тульской области)');

        $sheet->mergeCells("J$iRow:K$iRow");
        $sheet->setCellValue("J$iRow", 'Инициалы, фамилия');

        $iRow++;

        $sheet->mergeCells("G$iRow:I$iRow");
        $sheet->setCellValue("G$iRow", '(подпись)');

        if($introduction == 'false') {
            $iRow = $iRow + 5;

            $border = array(
                'borders'=>array(
                    'outline' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THICK,
                        'color' => array('rgb' => '000000')
                    ),
                )
            );
            $endBord = $iRow+5;
            $sheet->getStyle("D$iRow:H$endBord")->applyFromArray($border);

            $sheet->mergeCells("D$iRow:H$iRow");
            $sheet->setCellValue("D$iRow", 'ПОДПИСАНО ЭЛЕКТРОННОЙ ПОДПИСЬЮ');
            $iRow++;
            $sheet->mergeCells("D$iRow:H$iRow");
            $sheet->setCellValue("D$iRow", 'СВЕДЕНИЯ О СЕРТИФИКАТЕ ЭП');
            $iRow++;
            foreach(explode(';;', $sCertInfo) as $info) {
                $sheet->mergeCells("D$iRow:H$iRow");
                $sheet->setCellValue("D$iRow", $info);
                $iRow++;
            }
        }

        // Стили
        $sheet->getStyle("E7")->applyFromArray(['borders'=>['top' => ['style' => PHPExcel_Style_Border::BORDER_THIN,'color' => ['rgb' => '000000']]]]);
        $sheet->getStyle("A9:K$endVacations")->applyFromArray(['borders'=>['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN,'color' => ['rgb' => '000000']]]]);
        $sheet->getColumnDimension("H")->setWidth(15);
        $sheet->getStyle("H2")->getAlignment()->setWrapText(true);

        // Выравние и задаем шрифт
        $rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'];
        for ($i = 1; $i <= $iRow; $i++) {
            $style = array(
                'font' => array(
                    'name' => 'Times New Roman',
                    'size' => ($i < 7) ? 14 : 10,
                )
            );
            foreach ($rows as $item) {
                $alignV = PHPExcel_Style_Alignment::VERTICAL_CENTER;
                $alignH = PHPExcel_Style_Alignment::HORIZONTAL_CENTER;

                if (in_array($item . $i, ['E1', 'E7', 'G17', 'G21', 'K9', 'C9', 'A9'])) $alignV = PHPExcel_Style_Alignment::VERTICAL_TOP;
                if (in_array($item . $i, ['J16', 'J20'])) $alignV = PHPExcel_Style_Alignment::VERTICAL_BOTTOM;

                $sheet->getStyle($item . $i)->applyFromArray($style);
                $sheet->getStyle($item . $i)->getAlignment()->setHorizontal($alignH);
                $sheet->getStyle($item . $i)->getAlignment()->setWrapText(true);
                $sheet->getStyle($item . $i)->getAlignment()->setVertical($alignV);
                $sheet->getRowDimension($i)->setRowHeight(20);
            }
        }

        foreach ($rows as $item) {
            $sheet->getColumnDimension($item)->setWidth(12);
        }

        $sheet->getRowDimension("2")->setRowHeight(85);
        $sheet->getRowDimension("11")->setRowHeight(35);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        $xlsData = ob_get_contents();
        ob_end_clean();

        if($introduction == 'false') $response = base64_encode($xlsData);
        else $response = 'data:application/vnd.ms-excel;base64,' . base64_encode($xlsData);

        return $response;
    }
}
