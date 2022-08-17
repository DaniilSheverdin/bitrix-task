<?php

namespace Citto\RegisterHeads;

use CBitrixComponent;
use Bitrix\Main\Loader;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter;
use \Citto\RegisterHeads\Component as MainComponent;

CBitrixComponent::includeComponentClass('citto:register_heads');

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
            'prefilters'  => [
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

    public function deleteAction($arElementsID = []) {
        $obComponent = new MainComponent();

        if ($obComponent->getRole() != 'USER' && $arElementsID) {
            foreach ($arElementsID as $ID) {
                \CIBlockElement::Delete($ID);
            }

            return ['result' => true];
        }
    }

    public function getExportAction($arElementsID = [])
    {
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

        $obSheet->setCellValue("A1", "ОИВ");
        $obSheet->setCellValue("B1", "Подведомственное учреждение");
        $obSheet->setCellValue("C1", "ФИО руководителя");
        $obSheet->setCellValue("D1", "Дата назначения");
        $obSheet->setCellValue("E1", "Срок назначения (лет)");
        $obSheet->setCellValue("F1", "Дата окончания контракта");
        $obSheet->setCellValue("G1", "ФИО отв. лица от ОИВ");
        $obSheet->setCellValue("H1", "Дата внесения");
        $obSheet->setCellValue("I1", "Назначение по конкурсу на вакансию");
        $obSheet->setCellValue("J1", "Назначение из резерва управленческих кадров");

        $iRow = 2;
        foreach ($arRecords['ITEMS'] as $arRecord) {
            $arItem = $arRecord['data'];

            $obSheet->setCellValue("A{$iRow}", $arItem['OIV']);
            $obSheet->setCellValue("B{$iRow}", $arItem['SUBORDINATE']);
            $obSheet->setCellValue("C{$iRow}", $arItem['FIO_HEAD']);
            $obSheet->setCellValue("D{$iRow}", $arItem['DATE_ASSIGN']);
            $obSheet->setCellValue("E{$iRow}", $arItem['YEARS_ASSIGN']);
            $obSheet->setCellValue("F{$iRow}", $arItem['DATE_END_CONTRACT']);
            $obSheet->setCellValue("G{$iRow}", $arItem['CREATED_BY']);
            $obSheet->setCellValue("H{$iRow}", $arItem['DATE_CREATE']);
            $obSheet->setCellValue("I{$iRow}", $arItem['CONTEST']);
            $obSheet->setCellValue("J{$iRow}", $arItem['RESERVE']);
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
