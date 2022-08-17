<?php

namespace Citto\Profile;

use Exception;
use CBitrixComponent;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Highloadblock as HL;
use Bitrix\Main\Data\Cache;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class MyKPI extends CBitrixComponent implements Controllerable
{


    /**
     * Конфигурация аякс запросов
     *
     * @return array
     */
    public function configureActions(): array
    {
        return [
            'getNextLabel'                      => ['prefilters' => []],


        ];
    }

    /** @var array */
    private $arMonth                            = [];

    /** @var integer */
    public const HLBLOCK_ID_KPI_RETRO           = HLBLOCK_ID_KPI_RETRO;

    /** @var integer */
    public const IBLOCK_ID_KPI                  = IBLOCK_ID_KPI;

    /** @var integer */
    public const IBLOCK_ID_KPI_WORK_POSITIONS   = IBLOCK_ID_KPI_WORK_POSITIONS;

    /** @var array */
    private $arMonthDefault                     =
        [
            'january' => 'Январь',
            'february' => 'Февраль',
            'march' => 'Март',
            'april' => 'Апрель',
            'may' => 'Май',
            'june' => 'Июнь',
            'july' => 'Июль',
            'august' => 'Август',
            'september' => 'Сентябрь',
            'october' => 'Октябрь',
            'november' => 'Ноябрь',
            'december' => 'Декабрь',
        ];



    /**
     * Возвращает данные KPI за отчетный период.
     *
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getRetroUserData()
    {
        $arRes = [];
        global $USER;


        if (isset($_REQUEST['date']) && in_array($_REQUEST['date'], array_keys($this->arMonth))) {

            $strMonth = explode('-', $_REQUEST['date'])[0];
            $strYear = explode('-', $_REQUEST['date'])[1];

            $hlblock = HL\HighloadBlockTable::getById(self::HLBLOCK_ID_KPI_RETRO)->fetch();
            $entity = HL\HighloadBlockTable::compileEntity($hlblock);
            $entity_data_class = $entity->getDataClass();

            $date = new \DateTimeImmutable('now');
            $strFirstDay = $date->modify("first day of $strMonth $strYear");
            $strLastDay = $date->modify("last day of $strMonth $strYear");


            $arFilter = [
                '>=UF_HL_KPI_DATE' => $strFirstDay->format('d.m.Y'),
                '<=UF_HL_KPI_DATE' => $strLastDay->format('d.m.Y'),
                'UF_HL_KPI_USER_ID' => $USER->GetID()
            ];

            $rsData = $entity_data_class::getList(
                array(
                    "select" => ['*'],
                    "order" => ['ID' => 'ASC'],
                    "filter" => $arFilter,
                ));

            while ($arData = $rsData->fetch()) {


                if ($arData['UF_HL_KPI_FE']) {

                    $arRes['FORMULA'] = $arData['UF_HL_KPI_FE_FORMULA'];
                    $rsUser = \CUser::GetByID(intval($arData['UF_HL_KPI_USER_ID']));
                    $arUser = $rsUser->Fetch();
                    $strFIO = $arUser['LAST_NAME'] . ' ' . $arUser['NAME'] . ' ' . $arUser['SECOND_NAME'];
                    $departmentID = $arUser['UF_DEPARTMENT'][0];

                    $arSelectWP = ['ID', 'IBLOCK_ID', 'CODE', 'NAME', 'PROPERTY_ATT_DEPARTMENT', 'PROPERTY_ATT_SALARY'];
                    $arFilterWP = [
                        'IBLOCK_ID' => self::IBLOCK_ID_KPI_WORK_POSITIONS,
                        'ACTIVE' => 'Y',
                        'NAME' => $arUser['WORK_POSITION'],
                        'PROPERTY_ATT_DEPARTMENT' => $departmentID,
                    ];
                    $rsWP = \CIBlockElement::GetList(['sort' => 'asc'], $arFilterWP, false, false, $arSelectWP);
                    while($arFieldsWP = $rsWP->GetNext()) {

                        $arRes['SUM'] = intval($arFieldsWP['PROPERTY_ATT_SALARY_VALUE']) * $arData['UF_HL_KPI_RESULT'] / 100 * intval($arData['UF_HL_KPI_RATE']);
                    }


                    $arRes['POSITION'] = $arUser['WORK_POSITION'];

                    $resD = \CIBlockSection::GetByID($departmentID);
                    if($arD = $resD->GetNext()) {
                        $arRes['DEPARTMENT'] = $arD['NAME'];
                    }


                    $arRes['FIO'] = $strFIO;
                    $arRes['RATE'] = $arData['UF_HL_KPI_RATE'];
                    foreach ($arData['UF_HL_KPI_KPIS'] as $count => $kpi) {

                        $strKPIValue = explode('///', $kpi)[0];
                        $strKPIName = explode('///', $kpi)[1];

                        if (!in_array($strKPIName, $arRes['KPI_NAMES'])) {
                            $arRes['KPI_NAMES'][] = $strKPIName;

                        }

                        $arRes['KPIS'][$count]['VALUE'] = $strKPIValue;
                        $arRes['KPIS'][$count]['NAME'] = $strKPIName;

                        $arSelect = ['ID', 'IBLOCK_ID', 'NAME', 'PROPERTY_ATT_WEIGHT', 'PROPERTY_ATT_TARGET_VALUE'];
                        $arFilter = ['IBLOCK_ID' => self::IBLOCK_ID_KPI, 'ACTIVE' => 'Y', 'NAME' => $strKPIName];
                        $rs = \CIBlockElement::GetList(['sort' => 'asc'], $arFilter, false, false, $arSelect);
                        while($arFields = $rs->GetNext()) {
                            $arRes['KPIS'][$count]['TARGET'] = $arFields['PROPERTY_ATT_TARGET_VALUE_VALUE'];;
                            $arRes['KPIS'][$count]['WEIGHT'] = $arFields['PROPERTY_ATT_WEIGHT_VALUE'];
                        }



                    }
                    $arRes['CRITICAL'] = $arData['UF_HL_KPI_CRITICAL'];
                    $strProgress = '';
                    if ($arData['UF_HL_KPI_PROGRESS'] == 'Y') {
                        $strProgress = 'Активирован';
                    }
                    $arRes['PROGRESS'] = $strProgress;
                    $arRes['COMMENT'] = $arData['UF_HL_KPI_COMMENT'];
                    $arRes['RESULT'] = $arData['UF_HL_KPI_RESULT'];

                    break;
                }
            }
        }

        return $arRes;

    }

    /**
     * Записывает в массив $this->arMonth месяца в которых есть данные.
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    public function getFillRetroDataDate ()
    {

        $arFilledDateYear = [];

        $hlblock = HL\HighloadBlockTable::getById(self::HLBLOCK_ID_KPI_RETRO)->fetch();
        $entity = HL\HighloadBlockTable::compileEntity($hlblock);
        $entity_data_class = $entity->getDataClass();


        $rsData = $entity_data_class::getList(
            array(
                "select" => ['UF_HL_KPI_DATE'],
                "order" => ['ID' => 'ASC'],
                "filter" => [],
            ));


        while ($arData = $rsData->fetch()) {



            $month = strtolower($arData['UF_HL_KPI_DATE']->format('F'));
            $year = $arData['UF_HL_KPI_DATE']->format('Y');



            $strMonthYear = $month . '-'. $year;

            $arFilledDateYear[$strMonthYear] = $this->arMonthDefault[$month] . ' ' . $year;

        }

        $this->arMonth = $arFilledDateYear;

    }






    /**
     * Для работы компонента
     *
     * @return array|mixed
     * @throws Exception Коментарий.
     */
    public function executeComponent()
    {
        global $APPLICATION;
        global $USER;

        $cacheId = implode('|', [
            SITE_ID,
            $APPLICATION->GetCurPage(),
            $USER->GetGroups()
        ]);

        foreach ($this->arParams as $k => $v) {
            if (strncmp('~', $k, 1)) {
                $cacheId .= ',' . $k . '=' . $v;
            }
        }

        $cacheDir = '/' . SITE_ID . $this->GetRelativePath();
        $cache    = Cache::createInstance();

        $templateCachedData = $this->GetTemplateCachedData();

        if ($cache->startDataCache($this->arParams['CACHE_TIME'], $cacheId, $cacheDir)) {
            $APPLICATION->SetTitle('Мой KPI');
            $this->getFillRetroDataDate();
            $this->arResult['PERIOD'] = $this->arMonth;
            $this->arResult['USER_DATA'] = $this->getRetroUserData();



            $this->IncludeComponentTemplate();

            $cache->endDataCache([
                'arResult'           => $this->arResult,
                'templateCachedData' => $templateCachedData,
            ]);
        } else {
            extract($cache->GetVars());
            $this->SetTemplateCachedData($templateCachedData);
        }

        $this->strTemplatePath = $this->__template->GetFolder();

        return $this->arResult;
    }


}
