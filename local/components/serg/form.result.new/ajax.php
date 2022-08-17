<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;
use Bitrix\Main\Request;

class IndicatorsAjaxController extends Controller
{
    /**
     * @param Request|null $request
     * @throws LoaderException
     */
    public function __construct(Request $request = null)
    {
        parent::__construct($request);
        Loader::includeModule('iblock');
    }

    /**
     * @return array
     */
    public function configureActions(): array
    {
        return [
            'chooseSection' => [
                'prefilters' => []
            ],
            'chooseSubsectionList' => [
                'prefilters' => []
            ],

        ];
    }

    /**
     * @param string $control
     * @return array
     * @throws \Bitrix\Main\Db\SqlQueryException
     */
    public function chooseSectionAction(
        string $control,
        string $otdel
    ): array {
        global $USER;

        include_once __DIR__.'/constants.php';

        $arResult['is_admin'] = $USER->IsAdmin();

        $arFilter = array('IBLOCK_ID' => IBLOCK_ID_CONTROLS, 'SECTION_ID' => false);
        $arSelect = array('ID', 'NAME');

        $rsSections = CIBlockSection::GetList(array(), $arFilter, false, $arSelect);
        while ($arSection = $rsSections->Fetch()) {
            $arResult['INDICATORS']['CONTROLS'][$arSection['ID']] = $arSection['NAME'];
        }

        $arFilter2 = array('IBLOCK_ID' => IBLOCK_ID_CONTROLS, 'SECTION_ID' => $control);
        $rsSections2 = CIBlockSection::GetList([], $arFilter2, false, $arSelect);

        $arResult['INDICATORS']['DEPARTMENTS'][0] = 'все';
        $childSect = [];

        while ($arSection = $rsSections2->Fetch()) {
            $arResult['INDICATORS']['DEPARTMENTS'][$arSection['ID']] = $arSection['NAME'];
            if ($otdel == 0) {
                $childSect[] = $arSection['ID'];
            }
        }

        if ($otdel != 0) {
            $childSect[] = $otdel;
        } elseif ($otdel == 0 && empty($childSect)) {
            $childSect[] = $control;
        }

        $arElements = [];
        $arElements['otdel_name'] = $arResult['INDICATORS']['CONTROLS'][$control];

        // выбираем эталонные значения по показателям
        $arSelect = array("ID", "IBLOCK_ID", "NAME", "IBLOCK_SECTION_ID", "DATE_ACTIVE_FROM","PROPERTY_*");

        $arResult['INDICATORS']['FULL_NAME'] = $arResult['INDICATORS']['TARGET_VALUE'] = $arResult['INDICATORS']['SHORT_NAME'] = $arResult['INDICATORS']['BASE_SET'] = [];

        foreach ($childSect as $otdel_id) {
            $arFilter = array("IBLOCK_ID" => IBLOCK_ID_CONTROLS, "ACTIVE_DATE" => "Y", "ACTIVE" => "Y", "IBLOCK_SECTION_ID" => $otdel_id);
            $res = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
            $count = 0;
            while ($ob = $res->GetNextElement()) {
                $arFields = $ob->GetFields();
                $arProps = $ob->GetProperties();

                if (empty($arEl['row'][$count]['BI_ID'])) {
                    $arEl['row'][$count]['BI_ID'] = [];
                }
                if (empty($arEl['row'][$count]['NAME'])) {
                    $arEl['row'][$count]['NAME'] = [];
                }
                if (empty($arEl['row'][$count]['PROP'])) {
                    $arEl['row'][$count]['PROP'] = [];
                }
                if (empty($arEl['row'][$count]['DESC'])) {
                    $arEl['row'][$count]['DESC'] = [];
                }
                if (empty($arEl['row'][$count]['BI_ID'])) {
                    $arEl['row'][$count]['BI_ID'] = [];
                }
                if (empty($arEl['row'][$count]['OTDEL'])) {
                    $arEl['row'][$count]['OTDEL'] = [];
                }

                $arEl['row'][$count]['ID'] = array_merge($arEl['row'][$count]['ID'], [$arFields['ID']]);
                $arEl['row'][$count]['OTDEL'] = array_merge($arEl['row'][$count]['OTDEL'], array_fill(0, count($arProps['ATT_VALUE']['VALUE']), $otdel_id));
                $arEl['row'][$count]['NAME'] = array_merge($arEl['row'][$count]['NAME'], [$arFields['NAME']]);
                $arEl['row'][$count]['PROP'] = array_merge($arEl['row'][$count]['PROP'], $arProps['ATT_VALUE']['VALUE']);
                $arEl['row'][$count]['DESC'] = array_merge($arEl['row'][$count]['DESC'], $arProps['ATT_VALUE']['DESCRIPTION']);
                if ($count == 1) {
                    $arProps['ATT_VALUE']['DESCRIPTION'] = array_map(function ($i) use ($otdel_id) {
                        return IBLOCK_ID_CONTROLS.$otdel_id.$i;
                    }, $arProps['ATT_VALUE']['DESCRIPTION']);
                    $arEl['row'][$count]['BI_ID'] = array_merge($arEl['row'][$count]['BI_ID'], $arProps['ATT_VALUE']['DESCRIPTION']);
                }

                $count++;
            }
        }

        // определяем текущую дату
        if (TEST_MODE) {
            $strCurrDay = 5;
        } else {
            $strCurrDay = (new DateTime())->format("d");
        }
        $strCurrMounth = (new DateTime())->format("m");
        $strCurrYear = (new DateTime())->format("Y");

        if ($strCurrMounth == 1) {
            $strPrevMounth = 12;
            $strYear = $strCurrYear - 1;
        } else {
            $strPrevMounth = $strCurrMounth - 1;
            $strYear = $strCurrYear;
        }

        // определяем возможность редактирования
        if (($strCurrDay >= 1 && $strCurrDay <= 10) || ($strCurrDay >= 15 && $strCurrDay <= 21)) { // основные дни заполнения
            $boolAEdit = true;
        } elseif ($strCurrMounth == 1 && $strCurrDay >= 1 && $strCurrDay <= 14) {
            $boolAEdit = true; // исключения для праздников НГ
        } else {
            $boolAEdit = false;
        }

        // определяем интервал выборки данных предыдущего периода - чтоб для наглядности выбрать предыдущие значения показателей
        if ($strCurrDay < 15) {
            $intervalLast = $strCurrDay + intval(cal_days_in_month(CAL_GREGORIAN, $strPrevMounth, $strYear) / 2);
            $currInterval = $strCurrDay;
        } else {
            $intervalLast = ($strCurrDay - 15) + intval(cal_days_in_month(CAL_GREGORIAN, $strPrevMounth, $strYear) / 2);
            $currInterval = $strCurrDay - 15;
        }

        $connection = Bitrix\Main\Application::getConnection('base_for_bi');

        $dataOld = [];
        // данные за текущий период
        $data = $connection->query("SELECT MAX(`id`) AS `id`, `bi_id` FROM `bi` WHERE control = '".$arResult['INDICATORS']['CONTROLS'][$control]."' ".(empty($otdel) ? '' : "AND `department`='".$arResult['INDICATORS']['DEPARTMENTS'][$otdel]."'")." AND `date` BETWEEN       
                                            DATE_SUB(CURDATE(), INTERVAL ".$currInterval." DAY) AND CURDATE() GROUP BY `bi_id`");

        while ($arDataID = $data->fetch()) {
            $dataOld[] = $arDataID;
            $arData = $connection->query("SELECT * FROM `bi` WHERE `id` = '".$arDataID['id']."'")->fetch();
            $arData['flag'] = intval($boolAEdit); // флаг обновления значения bi

            $i = -1;
            foreach ($arEl['row'][1]['BI_ID'] as $key => $element) {
                if ($element == trim($arData['bi_id'])) {
                    $i = $key;
                    break;
                }
            }

            if ($i != -1) {
                $db[$i] = $arData;
            }
        }

        if (!$db) { // если не найдено ничего, выбираем данные за пердшествующие периоды
            $dataLast = $connection->query("SELECT MAX(`id`) AS `id`, `bi_id` FROM `bi` WHERE `control` = '".$arResult['INDICATORS']['CONTROLS'][$control]."' ".(empty($otdel) ? '' : "AND `department`='".$arResult['INDICATORS']['DEPARTMENTS'][$otdel]."'")." AND `date` < DATE_SUB(CURDATE(), INTERVAL ".$currInterval." DAY) GROUP BY `bi_id`");

            while ($arDataID = $dataLast->fetch()) {
                $dataOld[] = $arDataID;
                $arDataLast = $connection->query("SELECT * FROM `bi` WHERE `id` = '".$arDataID['id']."'")->fetch();
                $arDataLast['flag'] = intval($boolAEdit); // флаг добавления нового значения bi

                $i = -1;
                foreach ($arEl['row'][1]['BI_ID'] as $key => $element) {
                    if ($element == trim($arDataLast['bi_id'])) {
                        $i = $key;
                        break;
                    }
                }

                if ($i != -1) {
                    $arDataLast['state_value'] = '';
                    $db[$i] = $arDataLast;
                }
            }
        }

        foreach ($dataOld as $arDataID) {
            $arDataOld = $connection->query("SELECT * FROM `bi` WHERE `id` = '".$arDataID['id']."'")->fetch();

            $i = -1;
            foreach ($arEl['row'][1]['BI_ID'] as $key => $element) {
                if ($element == trim($arDataOld['bi_id'])) {
                    $i = $key;
                    break;
                }
            }

            if ($i != -1 && isset($db[$i])) {
                $db[$i]['state_value_old'] = $arDataOld['state_value'];
                $db[$i]['date_last_change'] = $arDataOld['date']->toString();
            }
        }

        foreach ($db as $i => $dbItem) {
            $db[$i]['date'] = isset($dbItem) ? $dbItem['date']->toString() : null;
        }

        $arElements['db'] = $db;
        $arElements['boolaedit'] = $boolAEdit;

        foreach ($arEl['row'] as $key => $element) {
            for ($i=0; $i<count($element['PROP']); $i++) {
                $arElements['table'][$i]['prop'][] = $element['PROP'][$i]['TEXT'];
                if ($element['DESC'][$i] && $key == 0) {
                    $arElements['table'][$i]['target_value'] = $element['DESC'][$i];
                    $arElements['table'][$i]['otdel'] = $element['OTDEL'][$i];
                }
                if ($element['BI_ID'][$i] && $key == 1) {
                    $arElements['table'][$i]['bi_id'] = $element['BI_ID'][$i];
                }
            }
        }

        // информация по отчетному периоду
        if (!$boolAEdit) {
            if (count($db) > 0) {
                $arDateList = [];
                $arElements['ACTUAL'] = [];

                foreach ($db as $arActItem) {
                    if (strtotime($arActItem['date_last_change']) <= (time() - TIME_EXCESS_LIMIT)) {
                        $arElements['ACTUAL'][$arActItem['control']]['noact'][] = $arActItem['department'];
                        $arElements['ACTUAL'][$arActItem['control']]['noact'] = array_unique($arElements['ACTUAL'][$arActItem['control']]['noact']);
                    } else {
                        $arElements['ACTUAL'][$arActItem['control']]['act'][] = $arActItem['department'];
                        $arElements['ACTUAL'][$arActItem['control']]['act'] = array_unique($arElements['ACTUAL'][$arActItem['control']]['act']);
                    }
                }
            }
        }

        foreach (array_column($db, 'date_last_change') as $arItemDate) {
            $arDateList[] = $arItemDate;
        }

        $arElements['minDate'] = min(array_map(function ($i) {
            return strtotime($i);
        }, $arDateList));
        $oDate = new DateTime();
        $arElements['minDateStamp'] = $oDate->setTimestamp($arElements['minDate']);

        $arElements['minDate'] = $oDate->format("d.m.Y");

        $arElements['user_name'] = $USER->GetLastName(). ' '.$USER->GetFirstName(). ' '.$USER->GetSecondName();

        return $arElements;
    }

    public function chooseSubsectionListAction(string $control): array
    {
        $otdelList = [];
        $arSelect = array('ID', 'NAME');

        $arFilter2 = array('IBLOCK_ID' => IBLOCK_ID_CONTROLS, 'SECTION_ID' => $control);
        $rsSections2 = CIBlockSection::GetList([], $arFilter2, false, $arSelect);

        $otdelList[0] = 'все';

        while ($arSection = $rsSections2->Fetch()) {
            $otdelList[$arSection['ID']] = $arSection['NAME'];
        }

        return $otdelList;
    }
}
