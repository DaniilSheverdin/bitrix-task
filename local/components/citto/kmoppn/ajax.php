<?php

namespace Citto\Instructions;

use CFile;
use CPHPCache;
use CUserOptions;
use Bitrix\Main\IO;
use CIBlockElement;
use CIBlockSection;
use CBitrixComponent;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use Bitrix\DocumentGenerator;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter;
use Citto\Instructions\Component as MainComponent;
use Bitrix\DocumentGenerator\DataProvider\ArrayDataProvider;
use CIntranetUtils;


if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

require $_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php";
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

if (Loader::includeModule("nkhost.phpexcel")) {
    global $PHPEXCELPATH;
    require_once($PHPEXCELPATH . '/PHPExcel/IOFactory.php');
}

class AjaxController extends Controller
{
    public function configureActions(): array
    {
        $arParams = [
            'prefilters' => [
                new ActionFilter\Authentication(),
                new ActionFilter\HttpMethod(
                    [ActionFilter\HttpMethod::METHOD_POST]
                ),
                new ActionFilter\Csrf(),
            ],
            'postfilters' => []
        ];

        return [
            'getExport' => $arParams,
        ];
    }

    public function getExportAction($arElementsID = [])
    {
        CBitrixComponent::includeComponentClass('citto:instructions');
        $obComponent = new MainComponent();
        $arRecords = $obComponent->getRecords($arElementsID);

        $obPHPExcel = new \PHPExcel();
        ob_start();
        $obPHPExcel->setActiveSheetIndex(0);
        $obSheet = $obPHPExcel->getActiveSheet();
        $obSheet->setTitle('Лист');
        $obSheet->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        $obSheet->getPageSetup()->SetPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

        $obPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
        $obPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
        $obPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);

        $obSheet->setCellValue("A1", "ФИО");
        $obSheet->setCellValue("B1", "Отдел");
        $obSheet->setCellValue("C1", "Должность");
        $obSheet->setCellValue("D1", "Дата");
        $obSheet->setCellValue("E1", "Д/И DOC");
        $obSheet->setCellValue("F1", "Д/И PDF");

        $iRow = 2;
        foreach ($arRecords['ITEMS'] as $arRecord) {
            $arItem = $arRecord['data'];
            $obSheet->setCellValue("A{$iRow}", $arItem['FIO']);
            $obSheet->setCellValue("B{$iRow}", $arItem['DEPARTMENT']);
            $obSheet->setCellValue("C{$iRow}", $arItem['POSITION']);
            $obSheet->setCellValue("D{$iRow}", $arItem['DATE']);
            $obSheet->setCellValue("E{$iRow}", $arItem['PDF_URL']);
            $obSheet->setCellValue("F{$iRow}", $arItem['DOC_URL']);
            $iRow++;
        }

        $obWriter = \PHPExcel_IOFactory::createWriter($obPHPExcel, 'Excel5');
        $obWriter->save('php://output');
        $xlsData = ob_get_contents();
        ob_end_clean();
        $sResponse = 'data:application/vnd.ms-excel;base64,' . base64_encode($xlsData);
        return $sResponse;
    }
}
