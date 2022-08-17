<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI\Extension;
use Citto\Tasks\ProjectInitiative;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) {
    die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$css = [
    '/bitrix/templates/.default/bootstrap.min.css',
    '/local/js/jstree/themes/default/style.min.css',
    '/bitrix/css/main/grid/webform-button.css',
    '/local/js/adminlte/css/AdminLTE.min.css',
    '/local/js/adminlte/css/skins/_all-skins.min.css',
];
array_walk(
    $css,
    static function ($path) {
        Asset::getInstance()
            ->addCss($path);
    }
);

Asset::getInstance()->addJs("/local/js/bootstrap-plugin/popper.min.js");
Asset::getInstance()->addJs('/bitrix/templates/.default/bootstrap.min.js');

Extension::load(['ui.forms', 'ui.notification']);

/**
 * Ссылка на задачу
 *
 * @param array $arTask
 *
 * @return string
 */
function makeTaskLink(array $arTask = []): string
{
    return 'https://' . $_SERVER['SERVER_NAME'] .
            '/workgroups/group/' .
            ProjectInitiative::$groupId .
            '/tasks/task/view/' .
            $arTask['ID'] . '/';
}

/**
 * Выгрузка в ексель
 *
 * @param array $arResult
 *
 * @return void
 *
 * @todo Вынести это в единое место, откуда использовать
 */
function exportExcel(array $arResult): void
{
    Loader::IncludeModule('nkhost.phpexcel');
    global $PHPEXCELPATH, $APPLICATION;
    require_once $PHPEXCELPATH . '/PHPExcel/IOFactory.php';
    $obExcel = new PHPExcel();
    $obExcel->setActiveSheetIndex(0);
    $sheet = $obExcel->getActiveSheet();
    $sheet->getPageSetup()
        ->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE)
        ->SetPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4)
        ->setFitToPage(true)
        ->setFitToWidth(1)
        ->setFitToHeight(0);

    $sheet->setTitle($arResult['TITLE']);

    $letters = range('A', 'Z');
    $rowIndex = 1;
    $i = 0;
    foreach ($arResult['HEADERS'] as $header) {
        $cellIndex = $letters[ $i ] . $rowIndex;
        $sheet->setCellValue($cellIndex, $header['NAME']);

        $sheet->getRowDimension($rowIndex)
            ->setRowHeight(20);

        if (isset($header['WIDTH'])) {
            $sheet->getColumnDimension($letters[ $i ])
                ->setWidth($header['WIDTH']);
        }

        $sheet->getStyle($cellIndex)
            ->applyFromArray(
                [
                    'borders' => [
                        'outline' => [
                            'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                            'color' => ['rgb' => '000000']
                        ]
                    ]
                ]
            )
            ->getFont()
            ->setBold(true);

        $sheet->getStyle($cellIndex)
            ->getAlignment()
            ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
        $i++;
    }
    $rowIndex = 2;
    foreach ($arResult['ROWS'] as $row) {
        $i = 0;
        foreach (array_keys($arResult['HEADERS']) as $header) {
            $cellIndex = $letters[ $i ] . $rowIndex;
            $sheet->setCellValue($cellIndex, $row[ $header ]['VALUE']);
            if (isset($row[ $header ]['LINK'])) {
                $link = new PHPExcel_Cell_Hyperlink($row[ $header ]['LINK']);
                $sheet->setHyperlink($cellIndex, $link);
            }
            $sheet->getStyle($cellIndex)
                ->applyFromArray(
                    [
                        'borders' => [
                            'outline' => [
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => ['rgb' => '000000']
                            ]
                        ]
                    ]
                );

            $sheet->getStyle($cellIndex)
                ->getAlignment()
                ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
                ->setWrapText(true);

            $color = '000000';
            if (isset($row[ $header ]['COLOR'])) {
                $color = $row[ $header ]['COLOR'];
            }

            $sheet->getStyle($cellIndex)
                ->applyFromArray(
                    [
                        'font'    => array(
                            'color'     => array(
                                'rgb' => $color
                            )
                        ),
                    ]
                );

            $bgColor = 'FFFFFF';
            if (isset($row[ $header ]['BGCOLOR'])) {
                $bgColor = $row[ $header ]['BGCOLOR'];
            }
            $sheet->getStyle($cellIndex)
                ->applyFromArray(
                    [
                        'fill'    => array(
                            'type' => PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array(
                                'rgb' => $bgColor
                            )
                        ),
                    ]
                );

            $i++;
        }
        $rowIndex++;
    }

    $APPLICATION->RestartBuffer();
    header('Expires: Mon, 1 Apr 1974 05:00:00 GMT');
    header('Last-Modified: ' . gmdate('D,d M YH:i:s') . ' GMT');
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Content-type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $arResult['FILENAME'] . '.xls"');
    $obWriter = PHPExcel_IOFactory::createWriter($obExcel, 'Excel5');
    $obWriter->save('php://output');
}


if (isset($_REQUEST['tasks'])) {
    include __DIR__ . '/tasks.php';
} elseif (isset($_REQUEST['projects'])) {
    include __DIR__ . '/projects.php';
} elseif (isset($_REQUEST['dates'])) {
    include __DIR__ . '/dates.php';
} elseif (isset($_REQUEST['managers'])) {
    include __DIR__ . '/managers.php';
} elseif (isset($_REQUEST['utilisation'])) {
    include __DIR__ . '/utilisation.php';
} elseif (isset($_REQUEST['task-list'])) {
    include __DIR__ . '/task-list.php';
} else {
    include __DIR__ . '/main.php';
}
