<?php

use CPHPCache;
use CIntranetUtils;
use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

if (Loader::includeModule("nkhost.phpexcel")) {
    global $PHPEXCELPATH;
    require_once($PHPEXCELPATH . '/PHPExcel/IOFactory.php');
}
Loader::includeModule("bitrix.planner");
Loader::includeModule("citto.filesigner");

use Bitrix\Main\Engine\Controller, Bitrix\Main\IO, Bitrix\Main\Application, Citto\Filesigner\Signer as Signer, Bitrix\Main\Config\Option;

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
            ],
            'getzamgub' => [
                'prefilters' => []
            ],
            'getministers' => [
                'prefilters' => []
            ],
        ];
    }

    public static function zipSigXls($file, $path)
    {
        $time = time();
        $path_tmp = Application::getDocumentRoot() . "/upload/tmp/{$time}";
        $dir_tmp = new IO\Directory($path_tmp);
        $dir_tmp->create();

        $fileXLS = new IO\File("{$path_tmp}/{$time}.xls");
        $fileXLS->putContents($file);

        $dir = new IO\Directory($path);
        if (!$dir->isExists()) {
            $dir->create();
        }

        $arFileInfo = CFile::MakeFileArray("{$path_tmp}/{$time}.xls", $path);
        $iFileID = CFile::SaveFile($arFileInfo, $path);

        if ($iFileID) {
            $dir_tmp->delete();
        }

        return $iFileID;
    }

    public static function signatureAction($file = 'file', $year = 'year')
    {
        $sModule = 'holiday.list';
        $arzipSigXls = self::zipSigXls(base64_decode($file), Application::getDocumentRoot() . "/upload/{$sModule}/{$year}");

        return ['ID' => $arzipSigXls, 'sessid' => bitrix_sessid_get()];
    }

    public static function callbackCell($array = [], $cfunction = '')
    {
        foreach ($array as $k) {
            $cfunction($k);
        }
    }

    public static function getviolationsAction(
        $arViolations = [],
        $users = [],
        $vacations = []
    ) {
        $objPHPExcel = new PHPExcel();
        ob_start();
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->SetPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

        $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
        $sheet->setTitle('Нарушения');

        $sheet->setCellValue("A1", "ФИО");
        $sheet->setCellValue("B1", "Нарушения");
        $sheet->setCellValue("C1", "Рабочие периоды");
        $sheet->setCellValue("D1", "Запланированные отпуска");
        $sheet->getRowDimension(1)->setRowHeight(20);
        $sheet->getStyle("A1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $sheet->getStyle("B1")->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $index = 2;

        foreach ($arViolations as $k => $v) {
            $workingPeriods = $users[$v['id']]['WORKPERIODS']['HUMAN'];
            $workingPeriods = implode(PHP_EOL, $workingPeriods);

            $violations = explode(PHP_EOL, $v['violations']);
            $violations = implode('', $violations);

            $vacations = implode(PHP_EOL, $v['vacations']);

            $sheet->setCellValue("A{$index}", "{$v['fio']}");
            $sheet->setCellValue("B{$index}", "{$violations}");
            $sheet->setCellValue("C{$index}", "{$workingPeriods}");
            $sheet->setCellValue("D{$index}", "{$vacations}");

            $sheet->getRowDimension($index)->setRowHeight(100);

            self::callbackCell(["A{$index}", "B{$index}", "C{$index}", "D{$index}"], function ($cell) use ($sheet) {
                $sheet->getStyle($cell)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            });

            $index++;
        }

        $sheet->getColumnDimensionByColumn("A")->setAutoSize(true);
        $sheet->getColumnDimension("B")->setWidth(50);
        $sheet->getColumnDimension("C")->setWidth(25);
        $sheet->getColumnDimension("D")->setWidth(30);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        $xlsData = ob_get_contents();
        ob_end_clean();
        $response = 'data:application/vnd.ms-excel;base64,' . base64_encode($xlsData);
        return $response;
    }

    public static function setheadsAction(
        $heads = 'heads',
        $userId = 'userId',
        $oldHeads = 'oldHeads'
    ) {
        global $USER;
        $USER->Update($userId, ['UF_THIS_HEADS' => $heads]);

        if ($heads != '' || $oldHeads !='') {
            $arAction = [];
            $arMerge = array_unique(array_merge(explode('|', $heads), explode('|', $oldHeads)));

            foreach ($arMerge as $item) {
                if (empty($item)) {
                    continue;
                }

                if (in_array($item, explode('|', $oldHeads)) && !in_array($item, explode('|', $heads))) {
                    $arAction[$item] = 'delete';
                } elseif (in_array($item, explode('|', $oldHeads)) && in_array($item, explode('|', $heads))) {
                    continue;
                } else {
                    $arAction[$item] = 'add';
                }
            }

            $arMerge = implode('|', $arMerge);

            $arHeads = $USER->GetList($by = '', $order = '', ['ID' => $arMerge], ['SELECT' => ['UF_SUBORDINATE']]);

            while ($a = $arHeads->GetNext()) {
                if (isset($a['~UF_SUBORDINATE'])) {
                    $us = json_decode($a['~UF_SUBORDINATE']);
                    if ($arAction[$a['ID']] == 'add' && !in_array($userId, $us)) {
                        array_push($us, $userId);
                    } elseif ($arAction[$a['ID']] == 'delete' && in_array($userId, $us)) {
                        unset($us[array_search($userId, $us)]);
                    } else {
                        continue;
                    }
                } else {
                    $us = array_values([$userId]);
                }

                if (!isset($us)) {
                    $us = '';
                } else {
                    $us = json_encode(array_values($us));
                }

                $USER->Update($a['ID'], ['UF_SUBORDINATE' => $us]);
            }
        }
        return $us;
    }

    public static function getusersAction($word = 'word')
    {
        $arAllUsers = [];
        $obCache = new CPHPCache();
        if ($obCache->InitCache(86400, __METHOD__ . '1', '/citto/holiday.list/')) {
            $arAllUsers = $obCache->GetVars();
        } elseif ($obCache->StartDataCache()) {
            $arAllUsers = [];
            $orm = UserTable::getList([
                'select'    => ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME'],
                'filter'    => ['ACTIVE' => 'Y']
            ]);
            while ($arUser = $orm->fetch()) {
                if (!$arUser['SECOND_NAME']) {
                    continue;
                }
                $uName = implode(' ', [$arUser['LAST_NAME'], $arUser['NAME'], $arUser['SECOND_NAME']]);
                $arAllUsers[ $arUser['ID'] ] = [
                    'NAME' => $uName,
                    'SEARCH' => mb_strtoupper($uName),
                ];
            }
            $obCache->EndDataCache($arAllUsers);
        }
        $word = mb_strtoupper($word);
        $arUsers = [];
        foreach ($arAllUsers as $uId => $arUser) {
            if (false !== stripos($arUser['SEARCH'], $word)) {
                $arUsers[ $uId ] = ['fio' => $arUser['NAME']];
            }
        }
        return $arUsers;
    }

    private static function getNameMinistry($departmentID, $departmentList)
    {
        function getParent($departmentID)
        {
            $tt = CIBlockSection::GetList(array(), array('ID' => $departmentID));
            $as = $tt->GetNext();
            static $a;
            if ($as['DEPTH_LEVEL'] == 3) {
                $a = $as['NAME'];
            } else {
                getParent($as['IBLOCK_SECTION_ID']);
            }
            return $a;
        }

        if ($departmentList[$departmentID]['DEPTH_LEVEL'] < 3) {
            $sNameMinistry =  $departmentList[$departmentID]['NAME'];
        } else {
            $sNameMinistry = getParent($departmentID);
        }

        return $sNameMinistry;
    }

    public static function excelAction(
        $year = 'year',
        $introduction = 'introduction',
        $departmentList = [],
        $departmentID = 'departmentID',
        $recursive = 'recursive',
        $myWorkers = [],
        $sSelectUsers = 'sSelectUsers',
        $sSave = 'no'
    ) {
        global $USER;
        /*
         * 132 - Верёвкина
         * 4152 - Кургузиков
         * 3746 - Куценко
         * 5962 - Барановская
        */

        if (in_array($USER->getID(), [132, 4152, 3746, 5962])) {
            return self::excelActionCIT($recursive, $year, $introduction, $departmentList, $departmentID, $myWorkers, $sSelectUsers, $sSave);
        } else {
            return self::excelActionPTO($recursive, $year, $introduction, $departmentList, $departmentID, $myWorkers, $sSelectUsers, $sSave);
        }
    }

    public static function excelActionPTO(
        $recursive = 'recursive',
        $year = 'year',
        $introduction = 'introduction',
        $departmentList = 'departmentList',
        $departmentID = 'departmentID',
        $myWorkers = [],
        $sSelectUsers = 'sSelectUsers',
        $sSave = 'no'
    ) {
        global $USER;
        $sNameMinistry = self::getNameMinistry($departmentID, $departmentList);
        $sNameDepartment = $departmentList[$departmentID]['NAME'];
        $CUsers = HolidayList\CUsers::getInstance();
        $isAdmin = $CUsers->getRoles($departmentList)['ADMIN'];
        $default_height = 24;
        $arExcludeRows = [];
        $recursive = ($recursive == 1) ? true : false;
        $bForGOV = (!$recursive && $departmentID == 53);
        $iPrevYear = $year - 1;

        ['users' => $users, 'periods' => $data] = $CUsers->getUsers($myWorkers, $isAdmin, $departmentList, $departmentID, $recursive);

        $arSelectUsers = json_decode($sSelectUsers);

        if (!empty($arSelectUsers)) {
            foreach ($users as $iUserID => $arUser) {
                if (!in_array($iUserID, $arSelectUsers)) {
                    unset($users[$iUserID]);
                }
            }
        }

        usort(
            $data,
            function ($x, $y) {
                return ($x['PROPERTY_USER_VALUE'] > $y['PROPERTY_USER_VALUE']);
            }
        );

        $objPHPExcel = new PHPExcel();
        ob_start();
        $arBorderTop = ['C7'];
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Лист');
        $sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->SetPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

        $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);

        $sheet->mergeCells("A1:F1");
        $sheet->setCellValue("A1", "");

        $sheet->mergeCells("G1:I1");
        $sheet->setCellValue("G1", "УТВЕРЖДАЮ");

        $sheet->mergeCells("A2:F2");
        $sheet->setCellValue("A2", "");

        $sheet->mergeCells("G2:I2");
        if ($bForGOV) {
            $sheet->setCellValue("G2", "Губернатор Тульской области");
        } else {
            $sheet->setCellValue("G2", "Заместитель Губернатора Тульской области – руководитель аппарата правительства Тульской области - начальник главного управления государственной службы и кадров аппарата правительства Тульской области");
        }


        $sheet->mergeCells("A3:F3");
        $sheet->setCellValue("A3", "");

        $sheet->mergeCells("G3:I3");
        if ($bForGOV) {
            $sheet->setCellValue("G3", "____________________ А.Г. Дюмин");
        } else {
            $sheet->setCellValue("G3", "____________________ Г.И. Якушкина");
        }


        $sheet->mergeCells("A4:F4");
        $sheet->setCellValue("A4", "");

        $sheet->mergeCells("G4:I4");
        $sheet->setCellValue("G4", "\"____\" _____________________ $iPrevYear года");

        $sheet->mergeCells("A6:B6");
        $sheet->setCellValue("A6", "График отпусков");
        $sheet->getStyle("A6")->getFont()->setBold(true);
        $sheet->mergeCells("C6:H6");
        $sheet->setCellValue("C6", $sNameDepartment);
        $sheet->setCellValue("I6", "на $year год");
        $sheet->getStyle("I6")->getFont()->setBold(true);

        $sheet->mergeCells("C7:H7");
        $sheet->setCellValue("C7", "(наименование подразделения аппарата, органа исполнительной власти)");

        $sheet->mergeCells("A9:B11");
        $sheet->setCellValue("A9", "Фамилия, имя, отчество (полностью)");

        $sheet->mergeCells("C9:E11");
        $sheet->setCellValue("C9", "Должность по штатному расписанию");

        $sheet->mergeCells("F9:H9");
        $sheet->setCellValue("F9", "ОТПУСК");

        $sheet->mergeCells("F10:F11");
        $sheet->setCellValue("F10", "количество календарных дней");

        $sheet->mergeCells("G10:H11");
        $sheet->setCellValue("G10", "дата");


        $sheet->mergeCells("I9:I11");
        $sheet->setCellValue("I9", "Фактическое использование дней отпуска");

        $sheet->mergeCells("A12:B12");
        $sheet->setCellValue("A12", "1");

        $sheet->mergeCells("C12:E12");
        $sheet->setCellValue("C12", "2");

        $rows = ['F', 'G', 'I'];
        $cycle = 3;
        foreach ($rows as $i) {
            $sheet->setCellValue($i . "12", $cycle);
            $cycle++;
        }
        $sheet->mergeCells("G12:H12");

        $iRow = 13;

        foreach ($data as $v) {
            $arUserInfo = $users[$v["PROPERTY_USER_VALUE"]];

            if (!isset($arUserInfo) && !empty($arSelectUsers)) {
                continue;
            }

            if ($v['ACTIVE'] != 'N') {
                $position = $arUserInfo['WORK_POSITION'];
                $fio = $arUserInfo['LAST_NAME'] . ' ' . $arUserInfo['NAME'] . ' ' . $arUserInfo['SECOND_NAME'];
                $from = $v["ACTIVE_FROM"];
                $to = $v["ACTIVE_TO"];
                $count = $v["PERIOD"] / 86400;

                /*
                    $iCountUsers = 1 Нет совмещения
                    $iCountUsers = 2 Есть совмещения
                    Если у пользователя есть совмещение, дублируем его в таблице.
                */
                $iCountUsers = ($arUserInfo['UF_WORK_CROSS']) ? 2 : 1;
                for ($x = 1; $x <= $iCountUsers; $x++) {
                    $sheet->setCellValue("A$iRow", $fio);
                    $sheet->setCellValue("C$iRow", $position);

                    $sheet->setCellValue("F$iRow", $count);
                    $sheet->setCellValue("G$iRow", $from);

                    $sheet->setCellValue("A$iRow", $fio);

                    $tmp_height = $default_height;
                    if (iconv_strlen($fio) > 28) {
                        $tmp_height = (iconv_strlen($fio) / $default_height) * $default_height;
                    }

                    if (iconv_strlen($position) > 28) {
                        $tmp_height_pos = (iconv_strlen($position) / $default_height) * $default_height;
                        $tmp_height = ($tmp_height_pos > $tmp_height) ? $tmp_height_pos : $tmp_height;
                    }

                    $sheet->mergeCells("A$iRow:B$iRow");
                    $sheet->mergeCells("C$iRow:E$iRow");
                    $sheet->mergeCells("G$iRow:H$iRow");
                    $sheet->getRowDimension($iRow)->setRowHeight($tmp_height);

                    array_push($arExcludeRows, $iRow);
                    $iRow++;
                }
            }
        }

        $endVacations = $iRow - 1;

        $iRow = $iRow + 2;
        $iRow++;

        $arrSize10 = [];
        if (!$bForGOV) {
            $sheet->mergeCells("A$iRow:E$iRow");
            $sheet->setCellValue("A$iRow", "(наименование должности руководителя органа исполнительной власти, подразделения аппарата правительства Тульской области)");
            $sheet->setCellValue("G$iRow", '(подпись)');
            $arBorderTop = array_merge($arBorderTop, ["A$iRow", "G$iRow", "I$iRow"]);
            $sheet->setCellValue("I$iRow", '(расшифровка)');
            array_push($arrSize10, $iRow);
            $iRow++;

            $iRow = $iRow + 2;
            $sheet->mergeCells("A$iRow:E$iRow");
            $iRow++;
        }

        $sheet->mergeCells("A$iRow:E$iRow");
        $sheet->setCellValue("A$iRow", '(заместитель Губернатора Тульской области, заместитель председателя правительства Тульской области)');
        $sheet->setCellValue("G$iRow", '(подпись)');
        $sheet->setCellValue("I$iRow", '(расшифровка)');
        array_push($arrSize10, $iRow);

        $arBorderTop = array_merge($arBorderTop, ["A$iRow", "G$iRow", "I$iRow"]);
        foreach ($arBorderTop as $bordercell) {
            $border = array(
                'borders'=>array(
                    'top' => array(
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('rgb' => '000000')
                    )
                )
            );
            $sheet->getStyle($bordercell)->applyFromArray($border);
        }

        $iRow++;

        if ($sSave == 'yes') {
            $introduction = true;
        }

        // Стили
        $sheet->getStyle("E7")->applyFromArray(['borders'=>['top' => ['style' => PHPExcel_Style_Border::BORDER_THIN,'color' => ['rgb' => '000000']]]]);
        $sheet->getStyle("A9:I$endVacations")->applyFromArray(['borders'=>['allborders' => ['style' => PHPExcel_Style_Border::BORDER_THIN,'color' => ['rgb' => '000000']]]]);
        $sheet->getColumnDimension("H")->setWidth(15);
        $sheet->getStyle("H2")->getAlignment()->setWrapText(true);

        // Выравние и задаем шрифт
        $rows = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'];
        for ($i = 1; $i <= $iRow; $i++) {
            $size = 14;
            if ($i == 7) {
                $size = 10;
            }
            if ($i > 7) {
                $size = 12;
            }
            if (in_array($i, $arrSize10)) {
                $size = 10;
            }

            $style = array(
                'font' => array(
                    'name' => 'PT Astra Serif',
                    'size' => $size,
                )
            );
            foreach ($rows as $item) {
                $alignV = PHPExcel_Style_Alignment::VERTICAL_CENTER;
                $alignH = PHPExcel_Style_Alignment::HORIZONTAL_CENTER;

                if (in_array($item . $i, ['G1', 'E1', 'E7', 'G17', 'G21', 'K9', 'C9', 'A9', 'C7'])) {
                    $alignV = PHPExcel_Style_Alignment::VERTICAL_TOP;
                }
                if (in_array($item . $i, $arBorderTop)) {
                    $alignV = PHPExcel_Style_Alignment::VERTICAL_TOP;
                }
                if (in_array($item . $i, ['J16', 'J20'])) {
                    $alignV = PHPExcel_Style_Alignment::VERTICAL_BOTTOM;
                }
                if (in_array($item . $i, ['G2'])) {
                    $alignV = PHPExcel_Style_Alignment::VERTICAL_BOTTOM;
                }
                if (in_array($item . $i, ['A6'])) {
                    $alignH = PHPExcel_Style_Alignment::HORIZONTAL_RIGHT;
                }
                if (in_array($item . $i, ['I6'])) {
                    $alignH = PHPExcel_Style_Alignment::HORIZONTAL_LEFT;
                }

                $sheet->getStyle($item . $i)->applyFromArray($style);
                $sheet->getStyle($item . $i)->getAlignment()->setHorizontal($alignH);
                $sheet->getStyle($item . $i)->getAlignment()->setWrapText(true);
                $sheet->getStyle($item . $i)->getAlignment()->setVertical($alignV);
                if (!in_array($i, $arExcludeRows)) {
                    $sheet->getRowDimension($i)->setRowHeight($default_height);
                }
            }
        }

        foreach ($rows as $item) {
            if ($item == 'A' || $item == 'B') {
                $sheet->getColumnDimension($item)->setWidth(15);
            } elseif ($item == 'C' || $item == 'D') {
                $sheet->getColumnDimension($item)->setWidth(9);
            } elseif ($item == 'E') {
                $sheet->getColumnDimension($item)->setWidth(11);
            } elseif ($item == 'F') {
                $sheet->getColumnDimension($item)->setWidth(23);
            } elseif ($item == 'G' || $item == 'H') {
                $sheet->getColumnDimension($item)->setWidth(12);
            } elseif ($item == 'I') {
                $sheet->getColumnDimension($item)->setWidth(30);
            }
        }

        $sheet->getRowDimension("2")->setRowHeight(120);
        $sheet->getRowDimension("11")->setRowHeight(35);

        $arUsersCadrs = $CUsers->getUsersCadrs();
        if (!$USER->IsAdmin() && !in_array($USER->getID(), $arUsersCadrs)) {
            $objPHPExcel->getActiveSheet()->getProtection()->setSheet(true);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        $xlsData = ob_get_contents();
        ob_end_clean();

        if ($introduction == 'false') {
            $response = base64_encode($xlsData);
        } else {
            $response = 'data:application/vnd.ms-excel;base64,' . base64_encode($xlsData);
        }

        if ($sSave == 'yes') {
            return self::signatureAction(base64_encode($xlsData), $year);
        }

        return $response;
    }

    public static function excelActionCIT(
        $recursive = 'recursive',
        $year = 'year',
        $introduction = 'introduction',
        $departmentList = 'departmentList',
        $departmentID = 'departmentID',
        $myWorkers = [],
        $sSelectUsers = 'sSelectUsers',
        $sSave = 'no'
    ) {
        global $USER;
        $sNameMinistry = self::getNameMinistry($departmentID, $departmentList);
        $sNameDepartment = $departmentList[$departmentID]['NAME'];
        $CUsers = HolidayList\CUsers::getInstance();
        $isAdmin = $CUsers->getRoles($departmentList)['ADMIN'];
        $default_height = 24;
        $arExcludeRows = [];
        $recursive = ($recursive == 1) ? true : false;
        $bForGOV = (!$recursive && $departmentID == 53);
        $iPrevYear = $year - 1;

        ['users' => $users, 'periods' => $data] = $CUsers->getUsers($myWorkers, $isAdmin, $departmentList, $departmentID, $recursive);

        $arSelectUsers = json_decode($sSelectUsers);

        if (!empty($arSelectUsers)) {
            foreach ($users as $iUserID => $arUser) {
                if (!in_array($iUserID, $arSelectUsers)) {
                    unset($users[$iUserID]);
                }
            }
        }

        usort(
            $data,
            function ($x, $y) {
                return ($x['PROPERTY_USER_VALUE'] > $y['PROPERTY_USER_VALUE']);
            }
        );

        ob_start();
        $sFileTemplate = $_SERVER['DOCUMENT_ROOT'] . '/local/templates_xlsx/vacation_list.xls';
        $obXls = PHPExcel_IOFactory::load($sFileTemplate);
        $sheet = $obXls->getActiveSheet();

        $arStyle = array(
            'font' => array(
                'name' => 'Times New Roman',
                'size' => 10,
            )
        );

        /*
         * Обозначения колонок в таблице отпусков
         * A - Структурное подразделение
         * U - Должность (специальность, профессия)
         * AO - Фамилия, имя, отчество
         * CC - Табельный номер
         * CN - Количество календарных дней
         * DA - Запланированная дата отпуска
         * DL - Фактическая дата отпуска
         * DW - Основание (документ) перенесения отпуска
         * EJ - Дата предполагаемого отпуска (перенесения отпуска)
         * EX - Примечание
        */

        $iRow = 19;
        $arAllDepartments = CIntranetUtils::GetStructure();
        foreach ($data as $v) {
            $arUserInfo = $users[$v["PROPERTY_USER_VALUE"]];

            if (!isset($arUserInfo) && !empty($arSelectUsers)) {
                continue;
            }

            if ($v['ACTIVE'] != 'N') {
                $position = $arUserInfo['WORK_POSITION'];
                $fio = $arUserInfo['LAST_NAME'] . ' ' . $arUserInfo['NAME'] . ' ' . $arUserInfo['SECOND_NAME'];
                $from = $v["ACTIVE_FROM"];
                $to = $v["ACTIVE_TO"];
                $sDepartment = $arAllDepartments['DATA'][$arUserInfo['UF_DEPARTMENT'][0]]['NAME'];
                $count = $v["PERIOD"] / 86400;

                /*
                    $iCountUsers = 1 Нет совмещения
                    $iCountUsers = 2 Есть совмещения
                    Если у пользователя есть совмещение, дублируем его в таблице.
                */
                $iCountUsers = ($arUserInfo['UF_WORK_CROSS']) ? 2 : 1;
                for ($x = 1; $x <= $iCountUsers; $x++) {
                    $sheet->setCellValue("A$iRow", $sDepartment);
                    $sheet->setCellValue("AO$iRow", $fio);
                    $sheet->setCellValue("U$iRow", $position);
                    $sheet->setCellValue("CN$iRow", $count);
                    $sheet->setCellValue("DA$iRow", $from);

                    $sheet->mergeCells("A$iRow:T$iRow");
                    $sheet->mergeCells("U$iRow:AN$iRow");
                    $sheet->mergeCells("AO$iRow:CB$iRow");
                    $sheet->mergeCells("CC$iRow:CM$iRow");
                    $sheet->mergeCells("CN$iRow:CZ$iRow");
                    $sheet->mergeCells("DA$iRow:DK$iRow");
                    $sheet->mergeCells("DL$iRow:DV$iRow");
                    $sheet->mergeCells("DW$iRow:EI$iRow");
                    $sheet->mergeCells("EJ$iRow:EW$iRow");
                    $sheet->mergeCells("EX$iRow:FK$iRow");
                    $iRow++;
                }
            }
        }

        /* Формируем нижнюю часть */
        $iRow++;
        $sheet->setCellValue("A$iRow", "Руководитель кадровой службы");
        $sheet->mergeCells("AH$iRow:CI$iRow");
        $sheet->mergeCells("CN$iRow:DJ$iRow");
        $sheet->mergeCells("CN$iRow:DJ$iRow");
        $sheet->mergeCells("DO$iRow:FK$iRow");
        $iRow++;
        $sheet->mergeCells("AH$iRow:CI$iRow");
        $sheet->mergeCells("CN$iRow:DJ$iRow");
        $sheet->mergeCells("CN$iRow:DJ$iRow");
        $sheet->mergeCells("DO$iRow:FK$iRow");
        $sheet->setCellValue("AH$iRow", "(должность)");
        $sheet->setCellValue("CN$iRow", "(личная подпись)");
        $sheet->setCellValue("DO$iRow", "(расшифровка подписи)");

        $sheet->getDefaultStyle()->getFont()->setName('Times New Roman');
        $objWriter = PHPExcel_IOFactory::createWriter($obXls, 'Excel5');
        $objWriter->save('php://output');
        $xlsData = ob_get_contents();
        ob_end_clean();

        if ($introduction == 'false') {
            $response = base64_encode($xlsData);
        } else {
            $response = 'data:application/vnd.ms-excel;base64,' . base64_encode($xlsData);
        }

        if ($sSave == 'yes') {
            return self::signatureAction(base64_encode($xlsData), $year);
        }

        return $response;
    }

    public static function getZamgubStatAction($arUsers = 'arUsers', $arPeriods = 'arPeriods')
    {
        $arVacations = [];
        foreach ($arPeriods as $arPeriod) {
            $iUserID = $arPeriod["PROPERTY_USER_VALUE"];
            if ($arPeriod['ACTIVE'] == 'Y') {
                $iFrom =  strtotime($arPeriod["ACTIVE_FROM"]);
                $iTo =  strtotime($arPeriod["ACTIVE_TO"]);
                $arVacations[$iUserID]['VACATIONS'][] = [
                    'FROM' => $iFrom,
                    'TO' => $iTo
                ];
            }
        }
        if (empty($arVacations)) {
            return null;
        }

        $arCrossGovernment = unserialize(Bitrix\Main\Config\Option::get('bitrix.planner', "CROSS"));
        $arCrossUsers = [];
        foreach ($arCrossGovernment as $iUserID) {
            $arCross =  unserialize(Bitrix\Main\Config\Option::get('bitrix.planner', "USER_CROSS_$iUserID"));
            if ($arCross) {
                if (!in_array($iUserID, $arCross)) {
                    array_push($arCross, $iUserID);
                }
                $arTmp = [];
                foreach ($arCross as $iUser) {
                    if (isset($arVacations[$iUser])) {
                        $arTmp[$iUser] = $arVacations[$iUser];
                    }
                }
                $arCrossUsers[] = $arTmp;
            }
        }

        $objPHPExcel = new PHPExcel();
        ob_start();
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('Лист');
        $sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->SetPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
        $sheet->setTitle('Пересечения');

        $iRowUser = 1;
        $arBGGreen = array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => '61b84d')
            )
        );
        $arBGGrey = array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => 'e5e5e5')
            )
        );

        foreach ($arCrossUsers as $arVacations) {
            foreach ($arVacations as $iUserID => $arVacation) {
                $arAlphabet = range('A', 'Z');
                $iRow = 0;
                $iCell = 0;
                foreach ($arAlphabet as $sLetter) {
                    $sheet->getColumnDimension($sLetter)->setWidth(30);
                }

                $sheet->setCellValue($arAlphabet[0] . $iRowUser, $arUsers[$iUserID]['LAST_NAME']);
                $sheet->getStyle($arAlphabet[0] . $iRowUser)->applyFromArray($arBGGreen);

                foreach ($arVacation["VACATIONS"] as $iKey => $arPeriods) {
                    $arFIOs = [];
                    foreach (array_keys($arVacations) as $iID) {
                        $arFIOs[] = $arUsers[$iID]['LAST_NAME'];
                    }
                    $sheet->mergeCells('A'.$iRowUser .':B' . $iRowUser);
                    $sheet->setCellValue('A'.$iRowUser, implode(' - ', $arFIOs));

                    $iFrom = $arPeriods['FROM'];
                    $iTo = $arPeriods['TO'];
                    $sPeriod = date('d.m.Y', $iFrom) .' - '. date('d.m.Y', $iTo);
                    $iCell = $iKey + 1;

                    $sheet->setCellValue($arAlphabet[0] . ($iRowUser + 1), $arUsers[$iUserID]['LAST_NAME']);
                    $sheet->setCellValue($arAlphabet[$iCell] . ($iRowUser + 1), $sPeriod);
                    $sheet->getStyle($arAlphabet[$iCell] . ($iRowUser + 1))->applyFromArray($arBGGreen);

                    $iRow = $iRowUser + 2;
                    foreach ($arVacations as $iUserID_copy => $arVacation_copy) {
                        if ($iUserID_copy != $iUserID) {
                            foreach ($arVacation_copy["VACATIONS"] as $arPeriods_copy) {
                                $sheet->setCellValue($arAlphabet[0] . $iRow, $arUsers[$iUserID_copy]['LAST_NAME']);
                                if ($iFrom >= $arPeriods_copy['FROM'] && $iFrom <= $arPeriods_copy['TO'] || $iTo >= $arPeriods_copy['FROM'] && $iTo <= $arPeriods_copy['TO']) {
                                    $sDate = date('d.m.Y', $arPeriods_copy['FROM']) .' - '. date('d.m.Y', $arPeriods_copy['TO']);
                                    $sheet->setCellValue($arAlphabet[$iCell] . $iRow, $sDate);
                                    break;
                                }
                            }
                            $iRow++;
                        }
                    }
                }

                $arBorder = array(
                    'borders'=>array(
                        'allborders' => array(
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                            'color' => array('rgb' => '000000')
                        )
                    )
                );
                $sheet->getStyle("A$iRowUser:$arAlphabet[$iCell]" . ($iRow-1))->applyFromArray($arBorder);
                $sheet->getStyle("A".($iRowUser+1).":$arAlphabet[$iCell]" . ($iRow-1))->applyFromArray($arBGGrey);

                if ($iRow != 0) {
                    $iRowUser = $iRow + 2;
                }
            }
        }

        for ($x = $iRow; $x>=0; $x--) {
            $sheet->getRowDimension($x)->setRowHeight(20);
        }

        $sheet->getStyle("A1:H400")->getAlignment()
            ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        $xlsData = ob_get_contents();
        ob_end_clean();
        $response = 'data:application/vnd.ms-excel;base64,' . base64_encode($xlsData);
        return $response;
    }

    public static function getMinistersGraphsAction($arUsers = 'arUsers', $arPeriods = 'arPeriods', $iYear = 'iYear')
    {
        $arMonths = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

        $arVacations = [];
        foreach ($arPeriods as $arPeriod) {
            $iUserID = $arPeriod["PROPERTY_USER_VALUE"];
            if ($arPeriod['ACTIVE'] == 'Y') {
                $iFrom =  strtotime($arPeriod["ACTIVE_FROM"]);
                $iTo =  strtotime($arPeriod["ACTIVE_TO"]);
                for ($x = $iFrom; $x <= $iTo; $x = $x + 86400) {
                    $sMonth = date('F', $x);
                    $iDay = (int) date('d', $x);
                    $arVacations[$sMonth][$iUserID][] = $iDay;
                }
            }
        }

        $arBGGreen = array(
            'fill' => array(
                'type' => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => array('rgb' => '61b84d')
            )
        );
        $arBorder = array(
            'borders'=>array(
                'allborders' => array(
                    'style' => PHPExcel_Style_Border::BORDER_THIN,
                    'color' => array('rgb' => '000000')
                )
            )
        );

        $objPHPExcel = new PHPExcel();
        ob_start();
        $objPHPExcel->setActiveSheetIndex(0);
        $sheet = $objPHPExcel->getActiveSheet();
        $sheet->setTitle('January');
        $sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        $sheet->getPageSetup()->SetPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
        $objPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);

        $arAlphabet = range('A', 'Z');
        foreach ($arAlphabet as $sLetter) {
            $arAlphabet[] = $arAlphabet[0] . $sLetter;
        }

        $sheet->setCellValue('A1', 'ФИО');

        foreach ($arMonths as $iKey => $sMonth) {
            if ($sMonth != 'January') {
                $sheet = $objPHPExcel->createSheet($iKey);
                $sheet->setTitle($sMonth);
            }
            $sheet->setCellValue('A1', 'ФИО');
            $iMonth = $iKey + 1;
            $iCountDays = cal_days_in_month(CAL_GREGORIAN, $iMonth, $iYear);
            for ($x = 1; $x <= $iCountDays; $x++) {
                $sheet->setCellValue($arAlphabet[$x] . '1', $x);
            }

            $iRow = 2;
            foreach ($arUsers as $arUser) {
                $sheet->setCellValue('A' . $iRow, "{$arUser['LAST_NAME']}  {$arUser['NAME']}");
                foreach ($arVacations[$sMonth][$arUser['ID']] as $arVacation) {
                    $sheet->getStyle($arAlphabet[$arVacation] . $iRow, "1")->applyFromArray($arBGGreen);
                }
                $iRow++;
            }

            $sheet->getStyle("A1:".$arAlphabet[$iCountDays].($iRow-1))->applyFromArray($arBorder);
            $sheet->getColumnDimension("A")->setWidth(30);
        }

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        $xlsData = ob_get_contents();
        ob_end_clean();
        $response = 'data:application/vnd.ms-excel;base64,' . base64_encode($xlsData);
        return $response;
    }
}
