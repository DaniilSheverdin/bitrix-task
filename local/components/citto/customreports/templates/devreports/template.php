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

Extension::load("ui.forms");

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

function humanifyTime($seconds) {
    $h = floor(abs($seconds) / 3600);
    $m = floor(abs($seconds) % 3600) / 60;
    $s = abs($seconds) % 60;

    return sprintf('%1$02d:%2$02d:%3$02d', $h, $m, $s);
}

if (isset($_REQUEST['sprints'])) {
    ?>
    <ul>
        <li><a href="<?=$APPLICATION->GetCurPageParam('', ['sprints', 'sprint', 'sprintId'])?>">К списку отчетов</a></li>
    </ul>
    <?
    include __DIR__ . '/sprints.php';
} elseif (isset($_REQUEST['sprint']) || isset($_REQUEST['sprintId'])) {
    ?>
    <ul>
        <li><a href="<?=$APPLICATION->GetCurPageParam('sprints', ['sprints', 'sprint', 'sprintId'])?>">К списку спринтов</a></li>
    </ul>
    <?
    include __DIR__ . '/sprint.php';
} else {
	?>
	<ul>
		<li><a href="<?=$APPLICATION->GetCurPageParam('sprints', ['sprints', 'sprint', 'sprintId'])?>">Отчет о загруженности отдела разработки в спринте</a></li>
	</ul>
	<?
}
