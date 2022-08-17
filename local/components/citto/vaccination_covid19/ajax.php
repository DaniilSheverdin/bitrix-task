<?php

namespace Citto\Vaccinationcovid19;

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
use Citto\Vaccinationcovid19\Component as MainComponent;
use Bitrix\DocumentGenerator\DataProvider\ArrayDataProvider;
use Citto\Mentoring\Users as MentoringUsers;
use CIntranetUtils;

CBitrixComponent::includeComponentClass('citto:vaccination_covid19');

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
            'getStatDetartments' => $arParams,
            'getStatCit' => $arParams,
        ];
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

        $obSheet->setCellValue("A1", "ФИО");
        $obSheet->setCellValue("B1", "Отдел");
        $obSheet->setCellValue("C1", "Есть сведения");
        $obSheet->setCellValue("D1", "Номер сертификата");
        $obSheet->setCellValue("E1", "Вид прививки");
        $obSheet->setCellValue("F1", "Дата последней вакцинации");
        $obSheet->setCellValue("G1", "Информация о перенесенном заболевании");
        $obSheet->setCellValue("H1", "Дата выздоровления");
        $obSheet->setCellValue("I1", "Медотвод");
        $obSheet->setCellValue("J1", "Срок окончания медотвода");
        $obSheet->setCellValue("K1", "Сертификат о вакцинации");
        $obSheet->setCellValue("L1", "Медотвод (документ)");
        $obSheet->setCellValue("M1", "Дата ревакцинации");

        $iRow = 2;
        foreach ($arRecords['ITEMS'] as $arRecord) {
            $arItem = $arRecord['data'];

            $sDateRevaccination = $obComponent->getDateRevaccination(
                [
                    strtotime($arItem['DATE_VACCINATION']),
                    strtotime($arItem['DATE_RECOVERY']),
                ]
            );

            if (strtotime($sDateRevaccination) < strtotime($arItem['DATE_END_MEDOTVOD'])) {
                $sDateRevaccination = $arItem['DATE_END_MEDOTVOD'];
            }

            $obSheet->setCellValue("A{$iRow}", $arItem['FIO']);
            $obSheet->setCellValue("B{$iRow}", $arItem['DEPARTMENT']);
            $obSheet->setCellValue("C{$iRow}", $arItem['STATUS']);
            $obSheet->setCellValue("D{$iRow}", $arItem['CRT_NUMBER']);
            $obSheet->setCellValue("E{$iRow}", $arItem['TYPE_VACCINATION']);
            $obSheet->setCellValue("F{$iRow}", $arItem['DATE_VACCINATION']);
            $obSheet->setCellValue("G{$iRow}", $arItem['INFO_DISEASE']);
            $obSheet->setCellValue("H{$iRow}", $arItem['DATE_RECOVERY']);
            $obSheet->setCellValue("I{$iRow}", $arItem['MEDOTVOD']);
            $obSheet->setCellValue("J{$iRow}", $arItem['DATE_END_MEDOTVOD']);
            $obSheet->setCellValue("K{$iRow}", $arItem['CRT_FILE']);
            $obSheet->setCellValue("L{$iRow}", $arItem['MEDOTVOD_FILE']);
            $obSheet->setCellValue("M{$iRow}", $sDateRevaccination);
            $iRow++;
        }

        $obWriter = \PHPExcel_IOFactory::createWriter($obPHPExcel, 'Excel5');
        $obWriter->save('php://output');
        $xlsData = ob_get_contents();
        ob_end_clean();
        $sResponse = 'data:application/vnd.ms-excel;base64,' . base64_encode($xlsData);
        return $sResponse;
    }

    public function getStat($arAllUsers = [])
    {
        $obComponent = new MainComponent();

        $arSelect = [
            "ID",
            "NAME",
            "DATE_CREATE",
            "CREATED_BY",
            "PROPERTY_CRT_NUMBER",
            "PROPERTY_TYPE_VACCINATION",
            "PROPERTY_DATE_VACCINATION",
            "PROPERTY_INFO_DISEASE",
            "PROPERTY_DATE_RECOVERY",
            "PROPERTY_MEDOTVOD",
            "PROPERTY_DATE_END_MEDOTVOD",
            "PROPERTY_CRT_FILE",
            "PROPERTY_MEDOTVOD_FILE",
        ];

        $arFilter = ["IBLOCK_CODE" => "vaccination_covid19", "ACTIVE_DATE" => "Y", "ACTIVE" => "Y"];
        $obRecords = CIBlockElement::GetList([], $arFilter, false, [], $arSelect);

        while ($arRecord = $obRecords->fetch()) {
            $iUserID = $arRecord['CREATED_BY'];
            $arAllUsers[$iUserID]['VACCINATION'] = [];

            $iDateVaccination = strtotime($arRecord['PROPERTY_DATE_VACCINATION_VALUE']) + 365/2 * 86400;
            $iDateRecovery = strtotime($arRecord['PROPERTY_DATE_RECOVERY_VALUE']) + 365/2 * 86400;

            if ($iDateVaccination > $iDateRecovery) {
                $arAllUsers[$iUserID]['VACCINATION']['DATE_VACCINATION'] = $arRecord['PROPERTY_DATE_VACCINATION_VALUE'];
                $arAllUsers[$iUserID]['VACCINATION']['EXPIRED_VACCINATION'] = (time() > $iDateVaccination) ? 'Y' : 'N';
            } elseif ($iDateRecovery && $iDateVaccination < $iDateRecovery) {
                $arAllUsers[$iUserID]['VACCINATION']['DATE_RECOVERY'] = $arRecord['PROPERTY_DATE_RECOVERY_VALUE'];
                $arAllUsers[$iUserID]['VACCINATION']['EXPIRED_RECOVERY'] = (time() > $iDateRecovery) ? 'Y' : 'N';
            } elseif ($iDateVaccination == $iDateVaccination && $iDateVaccination > 0) {
                $arAllUsers[$iUserID]['VACCINATION']['DATE_VACCINATION'] = $arRecord['PROPERTY_DATE_VACCINATION_VALUE'];
                $arAllUsers[$iUserID]['VACCINATION']['DATE_RECOVERY'] = $arRecord['PROPERTY_DATE_RECOVERY_VALUE'];
                $arAllUsers[$iUserID]['VACCINATION']['EXPIRED_VACCINATION'] = (time() > $iDateVaccination) ? 'Y' : 'N';
                $arAllUsers[$iUserID]['VACCINATION']['EXPIRED_RECOVERY'] = (time() > $iDateRecovery) ? 'Y' : 'N';
            }

            $arDates = [
                'DATE_VACCINATION' => strtotime($arRecord['PROPERTY_DATE_VACCINATION_VALUE']),
                'DATE_RECOVERY' => strtotime($arRecord['PROPERTY_DATE_RECOVERY_VALUE']),
                'DATE_END_MEDOTVOD' => strtotime($arRecord['PROPERTY_DATE_END_MEDOTVOD_VALUE']),
            ];

            $iDateRevaccination = strtotime($obComponent->getDateRevaccination(
                [
                    $arDates['DATE_VACCINATION'],
                    $arDates['DATE_RECOVERY'],
                    $arDates['DATE_END_MEDOTVOD'],
                ]
            ));

            if ($iDateRevaccination <= time() && $iDateRevaccination > 0) {
                $arAllUsers[$iUserID]['COUNT_REVACCINATION'] = 'Y';
            } else {
                foreach ($arDates as $sItemDate => $iTime) {
                    $obVaccination = new \DateTime(date('d.m.Y H:i:s', $iTime));
                    $iDate6Mounts = $obVaccination->add(new \DateInterval('P6M'))->format('U');
                    if ($iTime > 0 && $iDate6Mounts > time()) {
                        if ($arAllUsers[$iUserID]['DEPARTMENT']['NAME'] == 'Главное управление государственной службы и кадров') {
                            // pre("COUNT_" . $sItemDate);
                        }

                        $arAllUsers[$iUserID]["COUNT_" . $sItemDate] = 'Y';
                        break;
                    }
                }
            }
        }

        $arDepartments = [];

        foreach ($arAllUsers as $iUserID => $arUser) {
            if ($arUser['DEPARTMENT']['PODVED'] == 'Y') {
                unset($arAllUsers[$iUserID]);
            } else {
                $iDepartmentID = $arUser['DEPARTMENT']['NAME'];

                if (!$arDepartments[$iDepartmentID]) {
                    $arDepartments[$iDepartmentID] = [
                        'NAME' => $arUser['DEPARTMENT']['NAME'],
                        'COUNT_USERS' => 0,
                        'DATA_Y' => 0,
                        'DATA_N' => 0,
                        'VAC_CERT_ACTUAL' => 0,
                        'VAC_CERT_EXPIRED' => 0,
                        'RECOVERY_ACTUAL' => 0,
                        'RECOVERY_EXPIRED' => 0,
                        'COUNT_DATE_VACCINATION' => 0,
                        'COUNT_DATE_RECOVERY' => 0,
                        'COUNT_DATE_END_MEDOTVOD' => 0,
                        'COUNT_REVACCINATION' => 0,
                    ];
                }

                if (isset($arUser['COUNT_DATE_VACCINATION'])) {
                    $arDepartments[$iDepartmentID]['COUNT_DATE_VACCINATION']++;
                } elseif (isset($arUser['COUNT_DATE_RECOVERY'])) {
                    $arDepartments[$iDepartmentID]['COUNT_DATE_RECOVERY']++;
                }  elseif (isset($arUser['COUNT_DATE_END_MEDOTVOD'])) {
                    $arDepartments[$iDepartmentID]['COUNT_DATE_END_MEDOTVOD']++;
                }  elseif (isset($arUser['COUNT_REVACCINATION'])) {
                    $arDepartments[$iDepartmentID]['COUNT_REVACCINATION']++;
                }

                $arDepartments[$iDepartmentID]['COUNT_USERS']++;

                if ($arUser['VACCINATION']) {
                    $arDepartments[$iDepartmentID]['DATA_Y']++;
                } else {
                    $arDepartments[$iDepartmentID]['DATA_N']++;
                }

                if ($arUser['VACCINATION']['DATE_VACCINATION']) {
                    if ($arUser['VACCINATION']['EXPIRED_VACCINATION'] == 'Y') {
                        $arDepartments[$iDepartmentID]['VAC_CERT_EXPIRED']++;
                    } else {
                        $arDepartments[$iDepartmentID]['VAC_CERT_ACTUAL']++;
                    }
                }

                if ($arUser['VACCINATION']['DATE_RECOVERY']) {
                    if ($arUser['VACCINATION']['EXPIRED_RECOVERY'] == 'Y') {
                        $arDepartments[$iDepartmentID]['RECOVERY_EXPIRED']++;
                    } else {
                        $arDepartments[$iDepartmentID]['RECOVERY_ACTUAL']++;
                    }
                }
            }
        }

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

        $obSheet->setCellValue("A1", "Подразделение");
        $obSheet->setCellValue("B1", "Численность");
        $obSheet->setCellValue("C1", "Ввели данные");
        $obSheet->setCellValue("D1", "Не ввели данные");
        $obSheet->setCellValue("E1", "Вакцинировано (сертификат действует)");
        $obSheet->setCellValue("F1", "Заболевание (сертификат действует)");
        $obSheet->setCellValue("G1", "Медотвод");
        $obSheet->setCellValue("H1", "Истекшие сертификаты");
        $obSheet->setCellValue("I1", "Вакцинировано");
        $obSheet->setCellValue("J1", "Переболели");
        $obSheet->setCellValue("K1", "Медотвод");
        $obSheet->setCellValue("L1", "Подлежат ревакцинации");
        $iRow = 2;

        foreach ($arDepartments as $arItem) {
            $iCertExperied = $arItem['VAC_CERT_EXPIRED'] + $arItem['RECOVERY_EXPIRED'];
            $iNotCerts = $arItem['DATA_Y'] - ($arItem['COUNT_DATE_VACCINATION'] + $arItem['COUNT_DATE_RECOVERY'] + $arItem['COUNT_DATE_END_MEDOTVOD'] + $arItem['COUNT_REVACCINATION']);

            $obSheet->setCellValue("A{$iRow}", $arItem['NAME']);
            $obSheet->setCellValue("B{$iRow}", "");
            $obSheet->setCellValue("C{$iRow}", $arItem['DATA_Y']);
            $obSheet->setCellValue("D{$iRow}", "");
            $obSheet->setCellValue("E{$iRow}", $arItem['VAC_CERT_ACTUAL']);
            $obSheet->setCellValue("F{$iRow}", $arItem['RECOVERY_ACTUAL']);
            $obSheet->setCellValue("G{$iRow}", $arItem['COUNT_DATE_END_MEDOTVOD']);
            $obSheet->setCellValue("H{$iRow}", $iCertExperied);

            $obSheet->setCellValue("I{$iRow}", $arItem['COUNT_DATE_VACCINATION']);
            $obSheet->setCellValue("J{$iRow}", $arItem['COUNT_DATE_RECOVERY']);
            $obSheet->setCellValue("K{$iRow}", $arItem['COUNT_DATE_END_MEDOTVOD']);
            $obSheet->setCellValue("L{$iRow}", $arItem['COUNT_REVACCINATION'] + $iNotCerts);
            $iRow++;
        }

        $obWriter = \PHPExcel_IOFactory::createWriter($obPHPExcel, 'Excel5');
        $obWriter->save('php://output');
        $xlsData = ob_get_contents();
        ob_end_clean();
        $sResponse = 'data:application/vnd.ms-excel;base64,' . base64_encode($xlsData);

        return $sResponse;
    }

    public function getStatDetartmentsAction()
    {
        $arAllUsers = MentoringUsers::getUsersWithStrcuture();

        return $this->getStat($arAllUsers);
    }

    public function getStatCitAction()
    {
        $arDepartmentsCIT['TREE'] = array_merge([57], CIntranetUtils::GetDeparmentsTree(57, true));
        $arDepartmentsCIT['DATA'] = CIntranetUtils::GetDepartmentsData($arDepartmentsCIT['TREE']);

        $arAllUsers = [];
        $obUsers = \CUser::GetList($by = 'LAST_NAME', $order = 'ASC', ['UF_DEPARTMENT' => $arDepartmentsCIT['TREE']], ['SELECT' => ['UF_DEPARTMENT']]);
        while ($arUser = $obUsers->getNext()) {
            foreach ($arUser['UF_DEPARTMENT'] as $iDepartment) {
                if (in_array($iDepartment, $arDepartmentsCIT['TREE'])) {
                    $arAllUsers[$arUser['ID']] = [
                        'DEPARTMENT' => [
                            'ID' => $iDepartment,
                            'NAME' => $arDepartmentsCIT['DATA'][$iDepartment]
                        ]
                    ];
                    break;
                }
            }
        }

        return $this->getStat($arAllUsers);
    }
}
