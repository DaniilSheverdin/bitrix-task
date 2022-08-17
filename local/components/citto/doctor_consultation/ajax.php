<?php

namespace Citto\DoctorConsultation;

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
use Citto\DoctorConsultation\Component as MainComponent;
use Bitrix\DocumentGenerator\DataProvider\ArrayDataProvider;
use Citto\Mentoring\Users as MentoringUsers;
use CIntranetUtils;
use CIBlockPropertyEnum;


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
            'setStatus' => $arParams,
        ];
    }

    public function getExportAction($arElementsID = [])
    {
        CBitrixComponent::includeComponentClass('citto:doctor_consultation');
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
        $obSheet->setCellValue("B1", "СНИЛС");
        $obSheet->setCellValue("C1", "Телефон");
        $obSheet->setCellValue("D1", "Дата записи");
        $obSheet->setCellValue("E1", "Дата изменения статуса");
        $obSheet->setCellValue("F1", "Статус");
        $obSheet->setCellValue("G1", "Причина");
        $obSheet->setCellValue("H1", "Сведения");

        $iRow = 2;
        foreach ($arRecords['ITEMS'] as $arRecord) {
            $arItem = $arRecord['data'];

            $obSheet->setCellValue("A{$iRow}", $arItem['FIO']);
            $obSheet->setCellValue("B{$iRow}", $arItem['SNILS']);
            $obSheet->setCellValue("C{$iRow}", $arItem['PHONE']);
            $obSheet->setCellValue("D{$iRow}", $arItem['DATE_CREATE']);
            $obSheet->setCellValue("E{$iRow}", $arItem['DATE_MODIFY_STATUS']);
            $obSheet->setCellValue("F{$iRow}", $arItem['STATUS']);
            $obSheet->setCellValue("G{$iRow}", $arItem['REASON']);
            $obSheet->setCellValue("H{$iRow}", $arItem['INFORMATION']);
            $iRow++;
        }

        $obWriter = \PHPExcel_IOFactory::createWriter($obPHPExcel, 'Excel5');
        $obWriter->save('php://output');
        $xlsData = ob_get_contents();
        ob_end_clean();
        $sResponse = 'data:application/vnd.ms-excel;base64,' . base64_encode($xlsData);
        return $sResponse;
    }

    public function setStatusAction($arElementsID = [], $sAction = 'sAction', $sReason = 'sReason')
    {
        CBitrixComponent::includeComponentClass('citto:doctor_consultation');
        $obComponent = new MainComponent();

        $sResponse = 'N';

        if ($arElementsID) {
            $obEnums = CIBlockPropertyEnum::GetList(Array("DEF"=>"DESC", "SORT"=>"ASC"), Array( "CODE"=>"STATUS", "XML_ID" => $sAction));
            $iValueEnum = $obEnums->getNext()['ID'];
            $sReason = ($sReason == 'sReason') ? '' : $sReason;
            $arProps = [
                'STATUS' => $iValueEnum,
                'REASON' => $sReason,
                'DATE_MODIFY_STATUS' => date('d.m.Y H:i:s')
            ];

            foreach ($arElementsID as $iID) {
                CIBlockElement::SetPropertyValuesEx($iID, false, $arProps);
            }

            $arRecords = $obComponent->getRecords($arElementsID);
            $arUsers = [];

            foreach ($arRecords['ITEMS'] as $arRecord) {
                $arItem = $arRecord['data'];
                $arUsers[$arItem['ID']] = [
                    'FIO' => $arItem['FIO'],
                    'CREATED_BY' => $arItem['CREATED_BY'],
                    'STATUS' => $arItem['STATUS'],
                    'DATE_CREATE' => $arItem['DATE_CREATE'],
                ];
            }

            $obComponent->alertUsers($arUsers);
            $sResponse = 'Y';
        }

        return $sResponse;
    }
}
