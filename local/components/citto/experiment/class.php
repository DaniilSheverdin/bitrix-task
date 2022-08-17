<?php

namespace Citto\experiment;

use CUser;
use CJSCore;
use COption;
use PHPExcel;
use Exception;
use Bitrix\Main\UI;
use CIBlockElement;
use CIBlockSection;
use CBitrixComponent;
use Bitrix\Main\Loader;
use PHPExcel_IOFactory;
use Bitrix\Main\Web\Json;
use PHPExcel_Style_Border;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Page\Asset;
use PHPExcel_Cell_Hyperlink;
use PHPExcel_Style_Alignment;
use PHPExcel_Worksheet_PageSetup;
use Bitrix\Main\ArgumentException;
use Sprint\Migration\Helpers\IblockHelper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class Component extends CBitrixComponent
{
    /**
     * Запуск компонента
     *
     * @return null
     *
     * @throws LoaderException
     */
    public function executeComponent()
    {
        try {
            if (empty($this->arParams['DEPARTMENTS'])) {
                throw new Exception('Не найдены настройки подразделений');
            }

            CJSCore::Init(['jquery2', 'popup', 'ui']);
            Loader::includeModule('iblock');
            Loader::includeModule('intranet');
            Loader::includeModule('workflow');
            Loader::includeModule('bizproc');
            Loader::includeModule('citto.filesigner');
            Loader::includeModule('sprint.migration');

            $helper = new IblockHelper();
            $iblockId = $helper->getIblockId('bizproc_experiment', 'bitrix_processes');
            $iblockFiles = $helper->getIblockId('bizproc_experiment_simple', 'bitrix_processes');

            $arHiddenUsers = $this->getHiddenUsers();

            $obCache = Cache::createInstance();
            if ($obCache->initCache(3600, "getUserList_".md5(serialize($_REQUEST)), '/experiment/')) {
                $arCacheData    = $obCache->getVars();
                $allUsers       = $arCacheData['USERS'];
                $arNeed         = $arCacheData['NEED'];
            } elseif ($obCache->startDataCache()) {
                $arNeed = $this->arParams['DEPARTMENTS'];
                $arIds = [];
                foreach ($arNeed as $row) {
                    $arIds = array_merge($arIds, $row['IDS']);
                }

                $res = CIBlockSection::GetTreeList(
                    [
                        'IBLOCK_ID' => COption::GetOptionInt('intranet', 'iblock_structure', 0),
                        'ID'        => $arIds
                    ],
                    [
                        'ID', 'NAME', 'DEPTH_LEVEL'
                    ]
                );

                $allUsers = [];
                if (!isset($_REQUEST['filter_status']) || $_REQUEST['filter_status'] != 'hidden') {
                    $allUsers = $arHiddenUsers;
                }
                while ($row = $res->Fetch()) {
                    $key = -1;
                    foreach ($arNeed as $nKey => $nRow) {
                        if (in_array($row['ID'], $nRow['IDS'])) {
                            $key = $nKey;
                            break;
                        }
                    }

                    if ($key < 0) {
                        continue;
                    }
                    if (isset($arNeed[ $key ]['SKIP'])) {
                        if (in_array($row['ID'], $arNeed[ $key ]['SKIP'])) {
                            continue;
                        }
                    }
                    $arNeed[ $key ]['DEPTH_LEVEL'] = $row['DEPTH_LEVEL'];

                    $arFilter = [
                        'UF_DEPARTMENT' => $row['ID']
                    ];
                    if (!isset($_REQUEST['filter_status']) || $_REQUEST['filter_status'] != 'disabled') {
                        $arFilter['ACTIVE'] = 'Y';
                    }

                    $resUser = CUser::GetList($by = 'ID', $order = 'desc', $arFilter);
                    while ($rowUser = $resUser->Fetch()) {
                        if (in_array($rowUser['ID'], $allUsers)) {
                            continue;
                        }
                        if (empty($rowUser['LAST_NAME'])) {
                            continue;
                        }
                        $allUsers[ $rowUser['ID'] ] = $rowUser['ID'];
                        $arNeed[ $key ]['USERS'][ $rowUser['ID'] ] = $rowUser;
                    }
                }

                $obCache->endDataCache([
                    'USERS' => $allUsers,
                    'NEED'  => $arNeed,
                ]);
            }

            $this->arResult['EXPORT'] = [
                'TITLE'     => 'Уведомления (Ознакомление с экспериментом)',
                'FILENAME'  => 'Уведомления (Ознакомление с экспериментом)',
                'HEADERS'   => [
                    'USER'      => [
                        'NAME'  => 'Сотрудник',
                        'WIDTH' => 50,
                    ],
                    'STATUS'    => [
                        'NAME'  => 'Статус',
                        'WIDTH' => 50,
                    ],
                    'FILE'      => [
                        'NAME'  => 'Файл',
                        'WIDTH' => 70,
                    ],
                ],
                'ROWS'      => [],
            ];

            $arFiles        = [];
            $arFileNumbers  = [];
            $arFileStatus   = [];
            $arAllStatuses  = [];
            $arStep2        = [];

            $arFilter = [
                'IBLOCK_TYPE'   => 'bitrix_processes',
                'IBLOCK_ID'     => $iblockId,
                'ACTIVE'        => 'Y'
            ];
            $res = CIBlockElement::GetList(
                [],
                $arFilter,
                false,
                false,
                [
                    'ID',
                    'PROPERTY_FILES',
                    'PROPERTY_STATUS',
                ]
            );
            while ($row = $res->GetNext()) {
                if (is_string($row['PROPERTY_FILES_VALUE'])) {
                    $arCurFiles = [$row['PROPERTY_FILES_VALUE']];
                } else {
                    $arCurFiles = $row['PROPERTY_FILES_VALUE'];
                }

                foreach ($arCurFiles as $list) {
                    [$userId, $fileId, $number] = explode(':', $list);
                    $arFiles[ $userId ] = $fileId;
                    $arFileNumbers[ $userId ][ $fileId ] = $number;
                    $arFileStatus[ $userId ][ $fileId ] = $row['PROPERTY_STATUS_VALUE'];
                    if (!empty($row['PROPERTY_STATUS_VALUE'])) {
                        $arAllStatuses[ crc32($row['PROPERTY_STATUS_VALUE']) ] = $row['PROPERTY_STATUS_VALUE'];
                    }
                }
            }

            $res = CIBlockElement::GetList(
                [],
                array_merge($arFilter, ['IBLOCK_ID' => $iblockFiles]),
                false,
                false,
                [
                    'ID',
                    'PROPERTY_USER',
                    'PROPERTY_FILE',
                    'PROPERTY_STATUS',
                ]
            );
            while ($row = $res->GetNext()) {
                if (!empty($row['PROPERTY_STATUS_VALUE'])) {
                    $arFileStatus[ $row['PROPERTY_USER_VALUE'] ][ $row['PROPERTY_FILE_VALUE'] ] = $row['PROPERTY_STATUS_VALUE'];
                    $arAllStatuses[ crc32($row['PROPERTY_STATUS_VALUE']) ] = $row['PROPERTY_STATUS_VALUE'];
                }
                $arStep2[ $row['PROPERTY_USER_VALUE'] ] = $row['PROPERTY_STATUS_VALUE'];
            }

            $this->arResult['STATUSES']     = $arAllStatuses;
            $this->arResult['DEPARTMENTS']  = $arNeed;
            $this->arResult['FILES']        = $arFiles;
            $this->arResult['FILES_NUMBER'] = $arFileNumbers;
            $this->arResult['FILES_STATUS'] = $arFileStatus;
            $this->arResult['STEP2']        = $arStep2;
            $this->arResult['HIDDEN_USERS'] = $arHiddenUsers;

            $this->includeComponentTemplate();
        } catch (Exception $e) {
            ShowError($e->getMessage());
        }
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
    public function exportExcel(array $arResult): void
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
            if (isset($row['HEAD'])) {
                $cellIndex = $letters[ $i ] . $rowIndex;
                $sheet->setCellValue($cellIndex, $row['HEAD']);
                $mergeIndex = $letters[ $i ] . $rowIndex . ':' . $letters[ count($arResult['HEADERS'])-1 ] . $rowIndex;
                $sheet->mergeCells($mergeIndex);
                $sheet->getStyle($mergeIndex)
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
                $sheet->getStyle($mergeIndex)
                    ->getAlignment()
                    ->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER)
                    ->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER)
                    ->setWrapText(true);
                $sheet->getStyle($mergeIndex)
                    ->getFont()
                    ->setBold(true);
                $i++;
            } else {
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
                    $i++;
                }
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

    public function getHiddenUsers(): array
    {
        $file = $_SERVER['DOCUMENT_ROOT'] . '/experiment/hidden_users.json';
        if (file_exists($file)) {
            try {
                return Json::decode(file_get_contents($file));
            } catch (Exception | ArgumentException $e) {
                ShowError($e->getMessage());
            }
        }

        return [];
    }

    /**
     * Сортировка любого массива
     *
     * @param string $key Поле для сортировки
     *
     * @return Closure
     */
    public function buildSorter($key)
    {
        return function ($a, $b) use ($key) {
            return strnatcmp($a[$key], $b[$key]);
        };
    }
}
