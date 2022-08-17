<?php

namespace Citto\Components;

use Bitrix\Crm\Integration\IBlockElementProperty;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Application;
use CBitrixComponent;
use Bitrix\Main\Engine\Contract\Controllerable;
use CIBlockElement;
use CIBlock;
use CFile;
use CUser;
use Bitrix\Main\UserTable;
use CIntranetUtils;
use \Bitrix\Im\Integration\Intranet\Department as Department;
use Sprint\Migration\Helpers\IblockHelper;
use Bitrix\Main\Engine\ActionFilter;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

class Kmoppn extends \CBitrixComponent implements Controllerable
{
    const SECT_OMSU = 2331;
    const GROUP_ADD = 140;
    const GROUP_ACCESS = 141;

    private $_request = [];
    private $_department_id = null;
    private $_administrative_service = false;
    private $_accessable_service = false;

    private static $_usrID = null;

    private static $_protectedFields = ['OMSU_ID', 'DATE', 'LAST'];
    private static $_publickFields = [
        'PROPERTY_K_G',
        'PROPERTY_K_U',
        'PROPERTY_K_N',
        'PROPERTY_K_A',
        'PROPERTY_K_W',
        'PROPERTY_K_P',
        'PROPERTY_K_D',
        'PROPERTY_K_Pp',
        'PROPERTY_K_Pn',
        'PROPERTY_K_C',
        'PROPERTY_K_Pc',
        'PROPERTY_K_Pm',
        'PROPERTY_K_T',
        'PROPERTY_K_Tc',
        'PROPERTY_K_Tn',
        'PROPERTY_K_S',
        'PROPERTY_K_Sc',
        'PROPERTY_K_Sn'
    ];

    public function configureActions(): array
    {
        return [
            'prefilters' => [
                new ActionFilter\Authentication(),
                new ActionFilter\HttpMethod(
                    [
                        ActionFilter\HttpMethod::METHOD_GET,
                        ActionFilter\HttpMethod::METHOD_POST
                    ]
                ),
            ],
            'setSubmitData' => [
                'prefilters' => []
            ]
        ];
    }

    public function onPrepareComponentParams($arParams)
    {
        return $arParams;
    }

    public function setSubmitDataAction($post) {
        try {
            global $USER;

            $obLogger = new Logger('kmoppn');
            $obLogger->pushHandler(
                new StreamHandler(
                    $_SERVER['DOCUMENT_ROOT'] . '/local/logs/kmoppn/info_' . date("Y-m-d") . '.log',
                    Logger::INFO
                )
            );

            if(empty($post['__control_kmoppn'])) {
                throw new \Exception('Not valid form identifier hidden request');
            }
            unset($post['__control_kmoppn']);

            foreach($post as $k => $intItem) {
               if(empty($intItem) || (!is_numeric($intItem) && $intItem != '-')) {
                   throw new \Exception('Not valid input data field: "'.$k.'"');
               }
            }

            $this->_getDepartment();

            if(is_null($this->_department_id)) {
                throw new \Exception('Not permitted service for user');
            }

            $obElement = CIBlockElement::GetList(
                ['DATE_CREATE' => 'DESC'],
                [
                    'PROPERTY_OMSU_ID' => $this->_department_id,
                    'PROPERTY_LAST' => '1',
                    'IBLOCK_ID' => $this->getBlockId(),
                    'ACTIVE' => 'Y'
                ],
                false,
                false,
                array_merge(['ID', 'IBLOCK_ID', 'PROPERTY_DATE'], self::$_publickFields)
            );

            $arExistRecord = $obElement->Fetch();
            $obIBlockElemetn = new CIBlockElement();

            if(!$arExistRecord) {
                $obAddRes = $obIBlockElemetn->Add([
                    "MODIFIED_BY" => $USER->GetID(),
                    "IBLOCK_ID" => $this->getBlockId(),
                    "ACTIVE" => 'Y',
                    "NAME" => CIntranetUtils::GetDepartmentsData([$this->_department_id])[$this->_department_id].' - '.date('Y.m.d'),
                    "PROPERTY_VALUES" => array_merge([
                        'OMSU_ID' => $this->_department_id,
                        'DATE' => (new \DateTime())->format("d.m.Y"),
                        'LAST' => '1'
                    ], $post)
                ]);
                $action = 'add';
            } else {
                if(isset($arExistRecord['PROPERTY_DATE_VALUE']) && (new \DateTime())->format("m.Y") === (new \DateTime($arExistRecord['PROPERTY_DATE_VALUE']))->format("m.Y")) {
                    $obAddRes = $obIBlockElemetn->Update(
                        $arExistRecord['ID'],
                        [
                            "MODIFIED_BY" => $USER->GetID(),
                            "IBLOCK_ID" => $this->getBlockId(),
                            "ACTIVE" => 'Y',
                            "NAME" => CIntranetUtils::GetDepartmentsData([$this->_department_id])[$this->_department_id].' - '.date('Y.m.d'),
                            "PROPERTY_VALUES" => array_merge([
                                'OMSU_ID' => $this->_department_id,
                                'DATE' => (new \DateTime())->format("d.m.Y"),
                                'LAST' => '1'
                            ], $post)
                        ]
                    );
                    $action = 'update';
                } else {
                    $obAddRes = $obIBlockElemetn->Add([
                        "MODIFIED_BY" => $USER->GetID(),
                        "IBLOCK_ID" => $this->getBlockId(),
                        "ACTIVE" => 'Y',
                        "NAME" => CIntranetUtils::GetDepartmentsData([$this->_department_id])[$this->_department_id].' - '.date('Y.m.d'),
                        "PROPERTY_VALUES" => array_merge([
                            'OMSU_ID' => $this->_department_id,
                            'DATE' => (new \DateTime())->format("d.m.Y"),
                            'LAST' => '1'
                        ], $post)
                    ]);
                    $action = 'add';

                    if(isset($arExistRecord['ID'])) {
                        $obIBlockElemetn->Update(
                            $arExistRecord['ID'],
                            [
                                "MODIFIED_BY" => $USER->GetID(),
                                "IBLOCK_ID" => $this->getBlockId(),
                                "ACTIVE" => 'Y',
                                "PROPERTY_VALUES" => [
                                    'LAST' => '0'
                                ]
                            ]
                        );
                    }
                }
            }

            $obLogger->addInfo(json_encode(
                array_merge([
                    'OMSU_ID' => $this->_department_id,
                    'DATE' => (new \DateTime())->format("d.m.Y"),
                    'LAST' => '1'
                ], $post, ['USER_ID' => $USER->GetID(), 'ACTION' => $action])
            ));

            return empty($obIBlockElemetn->LAST_ERROR) ? ['data' => $obAddRes] : ['data' => $obIBlockElemetn->LAST_ERROR];

        } catch (\Exception $e) {
            echo $e->getMessage();
            exit;
        }
    }

    private function getBlockId() {
        Loader::includeModule('sprint.migration');
        $obHelper = new IblockHelper();
        return $obHelper->getIblockId('kmoppn', 'moppn');
    }

    private function _getFieldsList($department_id = null) {
        $iblockID = $this->getBlockId();
        $arListProps = CIBlock::GetProperties(
            $iblockID,
            ['SORT' => 'ASC'],
            ['ACTIVE' => 'Y']
        );

        $obElement = CIBlockElement::GetList(
            ['DATE_CREATE' => 'DESC'],
            [
                'PROPERTY_OMSU_ID' => $department ?? $this->_department_id,
                'PROPERTY_LAST' => '1',
                'IBLOCK_ID' => $iblockID,
                'ACTIVE' => 'Y'
            ],
            false,
            false,
            array_merge(['ID', 'IBLOCK_ID', 'PROPERTY_DATE'], self::$_publickFields)
        );
        $arExistRecord = $obElement->Fetch();

        while($arItem = $arListProps->Fetch()) {
            if(!in_array($arItem['CODE'], self::$_protectedFields)) {
                $arItem['NAME'] = htmlentities($arItem['NAME']);
                $arItem['VALUE'] = ($arExistRecord['PROPERTY_'.strtoupper($arItem['CODE']).'_VALUE']) ?? '';
                $arResult[] = $arItem;
            }
        }

        return $arResult;
    }

    private function _getUserData() {
        global $USER;
        self::$_usrID = $USER->GetID();
        $arUser = CUser::GetById(self::$_usrID)->Fetch();

        return $arUser['UF_DEPARTMENT'];
    }

    private function _getDepartment() {
        global $USER;

        $this->arResult['LIST_DEPARTMENTS'] = $this->_getUserData();
        $arListGroups = CUser::GetUserGroup(self::$_usrID);

        $this->_administrative_service = in_array(self::GROUP_ADD, $arListGroups) || $USER->IsAdmin();
        $this->_accessable_service = in_array(self::GROUP_ACCESS, $arListGroups);

        $OMSU_IDS = CIntranetUtils::getSubDepartments(self::SECT_OMSU);
        $intIDomsu = array_intersect($this->arResult['LIST_DEPARTMENTS'], $OMSU_IDS);
        if(count($intIDomsu) > 0) {
            $this->_department_id = intval($intIDomsu[0]);
        } elseif($this->_administrative_service || $this->_accessable_service) {
            $arDep = CIntranetUtils::GetUserDepartments(self::$_usrID);
            if(count($arDep) > 0) {
                $this->_department_id = $arDep[0];
            }
        } elseif($USER->IsAdmin()) {
            $this->_department_id = intval($this->arResult['LIST_DEPARTMENTS'][0]);
        }
    }

    private function getXls() {
        global $APPLICATION, $PHPEXCELPATH, $USER;

        try {
            $APPLICATION->RestartBuffer();

            $this->_getDepartment();

            if(!$this->_administrative_service && !$USER->IsAdmin()) {
                throw new \Exception('Not permitted service for user');
            }

            Loader::includeModule("nkhost.phpexcel");
            require_once($PHPEXCELPATH . '/PHPExcel/IOFactory.php');

            $iblockID = $this->getBlockId();
            $obElementObj = CIBlockElement::GetList(
                ['DATE_CREATE' => 'DESC'],
                [
                    'PROPERTY_LAST' => '1',
                    'IBLOCK_ID' => $iblockID,
                    'ACTIVE' => 'Y'
                ],
                false,
                false,
                ['ID', 'IBLOCK_ID', 'PROPERTY_DATE', 'PROPERTY_OMSU_ID']
            );

            $arAllData = [];
            $OMSU_IDS = CIntranetUtils::getSubDepartments(self::SECT_OMSU);

            while ($arDataRow = $obElementObj->Fetch()) {
                if(!empty($arDataRow['PROPERTY_OMSU_ID_VALUE']) && in_array($arDataRow['PROPERTY_OMSU_ID_VALUE'], $OMSU_IDS)) {
                    $arDataList = $this->_getFieldsList($arDataRow['PROPERTY_OMSU_ID_VALUE']);
                    $strDEPARTMENT_NAME = CIntranetUtils::GetDepartmentsData([$arDataRow['PROPERTY_OMSU_ID_VALUE']])[$arDataRow['PROPERTY_OMSU_ID_VALUE']];

                    foreach($arDataList as $k => $arRow) {
                        $arDataList[$k]['DEPARTMENT'] = $strDEPARTMENT_NAME;
                    }
                    $arAllData = array_merge($arAllData, $arDataList);
                }
            }

            $obPHPExcel = new \PHPExcel();
            $obPHPExcel->setActiveSheetIndex(0);
            $obSheet = $obPHPExcel->getActiveSheet();
            $obSheet->setTitle('Критерии МО ППН');
            $obSheet->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
            $obSheet->getPageSetup()->SetPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

            $obPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
            $obPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
            $obPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);

            $obSheet->setCellValue('A1', "Город или муниципалитет");
            $obPHPExcel->getActiveSheet()->getStyle('A1')->applyFromArray(['fill' => [
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'color' => [
                    'rgb' => '#CFCFCF'
                ]]
            ]);
            $obSheet->setCellValue('B1', "Показатель");
            $obPHPExcel->getActiveSheet()->getStyle('B1')->applyFromArray(['fill' => [
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'color' => [
                    'rgb' => '#CFCFCF'
                ]]
            ]);
            $obSheet->setCellValue('C1', "Значение");
            $obPHPExcel->getActiveSheet()->getStyle('C1')->applyFromArray(['fill' => [
                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                'color' => [
                    'rgb' => '#CFCFCF'
                ]]
            ]);

            $obSheet->getColumnDimension('A')
                ->setWidth(40);
            $obSheet->getColumnDimension('B')
                ->setWidth(80);
            $obSheet->getColumnDimension('C')
                ->setWidth(20);



            $iRow = 2;
            foreach ($arAllData as $arRecord) {
                $obSheet->setCellValue("A{$iRow}", $arRecord['DEPARTMENT']);
                $obSheet->setCellValue("B{$iRow}", $arRecord['NAME']);
                $obSheet->setCellValue("C{$iRow}", $arRecord['VALUE']);
                $iRow++;
            }

            $obWriter = \PHPExcel_IOFactory::createWriter($obPHPExcel, 'Excel5');

            header('Content-type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename="'.$APPLICATION->GetTitle().'.xls"');

            $obWriter->save('php://output');
            exit;

        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function executeComponent()
    {
        $this->_request = Application::getInstance()->getContext()->getRequest();

        $this->_getDepartment();
        $this->arResult['FIELDS'] = $this->_getFieldsList();

        if(is_null($this->_department_id)) {
            $this->arResult['NOT_PERMITTED'] = true;
        } else {
            $this->arResult['NOT_PERMITTED'] = false;
        }

        $this->arResult['SHOW_LINK_REPORT'] = $this->_administrative_service;

        $this->arResult['DEPARTMENT_NAME'] = CIntranetUtils::GetDepartmentsData([$this->_department_id])[$this->_department_id];

        if(!empty($this->_request->getQueryList()->getValues()) && $this->_request->getQuery('download') == 'xls') {
            $this->getXls();
        } else {
            $this->includeComponentTemplate();
        }
    }
}
