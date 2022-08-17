<?

use Bitrix\Main\Type\Date;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}

LocalRedirect('/citto/indicators_new/fill');

global $USER;

include_once dirname(dirname(__DIR__)).'/constants.php';
include_once $_SERVER['DOCUMENT_ROOT'].'/local/vendor/autoload.php';

CModule::IncludeModule("iblock");

if ($_REQUEST['format'] == 'csv') {
    $APPLICATION->RestartBuffer();
}

$section_id = CONTROLS_SECTION_ID_UIB;
$otdel_id = 0;
if (isset($_POST['control'])) {
    $section_id = $_POST['control'];
    $pcontrol = $_POST['control'];
} elseif ($_REQUEST['department']) {
    $section_id = intval($_REQUEST['department']);
    $pcontrol = $_REQUEST['department'];
}

if ($_REQUEST['otdel']) {
    $otdel_id = intval($_REQUEST['otdel']);
    $otdel = $_REQUEST['otdel'];
}

$arFilter = array('IBLOCK_ID' => IBLOCK_ID_CONTROLS, 'SECTION_ID' => false);

$arSelect = array('ID', 'NAME');
$rsSections = CIBlockSection::GetList([], $arFilter, false, $arSelect);
while ($arSection = $rsSections->Fetch()) {
    $arResult['INDICATORS']['CONTROLS'][$arSection['ID']] = $arSection['NAME'];
}

$arFilter2 = array('IBLOCK_ID' => IBLOCK_ID_CONTROLS, 'SECTION_ID' => $section_id);
$rsSections2 = CIBlockSection::GetList([], $arFilter2, false, $arSelect);

$arResult['INDICATORS']['DEPARTMENTS'][0] = 'все';
$childSect = [];
while ($arSection = $rsSections2->Fetch()) {
    $arResult['INDICATORS']['DEPARTMENTS'][$arSection['ID']] = $arSection['NAME'];
    if ($otdel_id == 0) {
        $childSect[] = $arSection['ID'];
    }
}

if ($otdel_id != 0) {
    $childSect[] = $otdel_id;
} elseif ($otdel_id == 0 && empty($childSect)) {
    $childSect[] = $pcontrol;
}

$arResult['USER_NAME'] = $USER->GetLastName(). ' '.$USER->GetFirstName(). ' '.$USER->GetSecondName();

$arSelect = array("ID", "IBLOCK_ID", "NAME", "IBLOCK_SECTION_ID", "DATE_ACTIVE_FROM", "PROPERTY_*");
$i = 0;

$arResult['INDICATORS']['BI_ID'] = $arResult['INDICATORS']['OTDEL'] = $arResult['INDICATORS']['FULL_NAME'] = $arResult['INDICATORS']['TARGET_VALUE'] = $arResult['INDICATORS']['SHORT_NAME'] = $arResult['INDICATORS']['BASE_SET'] = [];
foreach ($childSect as $sect_id) {
// выбираем эталонные значения по показателям
    $arFilter = array("IBLOCK_ID" => IBLOCK_ID_CONTROLS, "ACTIVE_DATE" => "Y", "ACTIVE" => "Y", "IBLOCK_SECTION_ID" => $sect_id);
    $res = CIBlockElement::GetList([], $arFilter, false, false, $arSelect);

    while ($ob = $res->GetNextElement()) {
        $arFields = $ob->GetFields();
        $arProps = $ob->GetProperties();

        if (trim($arFields['NAME']) == GetMessage('FIELD_INDICATOR_1')) {
            $arResult['INDICATORS']['FULL_NAME'] = array_merge($arResult['INDICATORS']['FULL_NAME'], $arProps['ATT_VALUE']['VALUE']);
            $arResult['INDICATORS']['TARGET_VALUE'] = array_merge($arResult['INDICATORS']['TARGET_VALUE'], $arProps['ATT_VALUE']['DESCRIPTION']);
            $arResult['INDICATORS']['OTDEL'] = array_merge($arResult['INDICATORS']['OTDEL'], array_fill(0, count($arProps['ATT_VALUE']['VALUE']), $sect_id));
        }
        if (trim($arFields['NAME']) == GetMessage('FIELD_INDICATOR_2')) {
            $arResult['INDICATORS']['SHORT_NAME'] = array_merge($arResult['INDICATORS']['SHORT_NAME'], $arProps['ATT_VALUE']['VALUE']);
            $arProps['ATT_VALUE']['DESCRIPTION'] = array_map(function ($i) use ($sect_id) {
                return IBLOCK_ID_CONTROLS.$sect_id.$i;
            }, $arProps['ATT_VALUE']['DESCRIPTION']);
            $arResult['INDICATORS']['BI_ID'] = array_merge($arResult['INDICATORS']['BI_ID'], $arProps['ATT_VALUE']['DESCRIPTION']);
        }
        if (trim($arFields['NAME']) == GetMessage('FIELD_INDICATOR_3')) {
            $arResult['INDICATORS']['BASE_SET'] = array_merge($arResult['INDICATORS']['BASE_SET'], $arProps['ATT_VALUE']['VALUE']);
        }
    }
}

$arResult['MES_NO_CHANGE_DATA'] = GetMessage("MES_NO_CHANGE_DATA");
$arResult['LABEL_DOWNLOAD_BY_CSV'] = GetMessage("LABEL_DOWNLOAD_BY_CSV");

if (isset($pcontrol) && $pcontrol != '') {
    $control = trim($arResult['INDICATORS']['CONTROLS'][$pcontrol]);
} else {
    $control = GetMessage("DEFAULT_SELECTED_DEPARTMENT");
}

if (!empty($otdel_id)) {
    $otdel_name = $arResult['INDICATORS']['DEPARTMENTS'][$otdel_id];
} else {
    $otdel_name = null;
}

if (!empty($arResult['INDICATORS']['CONTROLS'])) {
    $arResult['DEFAULT_CONTROL_TYPE'] = array_keys($arResult['INDICATORS']['CONTROLS'])[0];
} else {
    $arResult['DEFAULT_CONTROL_TYPE'] = '';
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

$arResult['boolAEdit'] = $boolAEdit;

// определяем интервал выборки данных предыдущего периода - чтоб для наглядности выбрать предыдущие значения показателей
if ($strCurrDay < 15) {
    $intervalLast = $strCurrDay + intval(cal_days_in_month(CAL_GREGORIAN, $strPrevMounth, $strYear) / 2);
    $currInterval = $strCurrDay;
} else {
    $intervalLast = ($strCurrDay - 15) + intval(cal_days_in_month(CAL_GREGORIAN, $strPrevMounth, $strYear) / 2);
    $currInterval = $strCurrDay - 15;
}

$connection = Bitrix\Main\Application::getConnection('base_for_bi');
$sqlHelper = $connection->getSqlHelper();
$dataOld = [];

// данные за текущий период
$data = $connection->query("SELECT MAX(`id`) AS `id`, `bi_id` FROM `bi` WHERE control = '".$control."' ".(is_null($otdel_name) ? '' : "AND `department`='".$otdel_name."'")." AND `date` BETWEEN       
DATE_SUB(CURDATE(), INTERVAL ".$currInterval." DAY) AND CURDATE() GROUP BY `bi_id`");

while ($arDataID = $data->fetch()) {
    $dataOld[] = $arDataID;
    $arData = $connection->query("SELECT * FROM `bi` WHERE `id` = '".$arDataID['id']."'")->fetch();
    $arData['flag'] = intval($boolAEdit); // флаг обновления значения bi

    $i = -1;
    foreach ($arResult['INDICATORS']['BI_ID'] as $fnk => $fnData) {
        if ($fnData == $arData['bi_id']) {
            $i = $fnk;
            break;
        }
    }

    if ($i != -1) {
        $arResult['DB'][$i] = $arData;
    }
}

if ($arResult['DB']) {
    $arResult['isUpdate'] = true;
}

if (!$arResult['DB']) { // если не найдено ничего, выбираем данные за пердшествующие периоды
    $arResult['isUpdate'] = false;

    $dataLast = $connection->query("SELECT MAX(`id`) AS `id`, `bi_id` FROM `bi` WHERE `control` = '".$control."' ".(is_null($otdel_name) ? '' : "AND `department`='".$otdel_name."'")." AND `date` < DATE_SUB(CURDATE(), INTERVAL ".$currInterval." DAY) GROUP BY `bi_id`");

    while ($arDataID = $dataLast->fetch()) {
        $dataOld[] = $arDataID;
        $arDataLast = $connection->query("SELECT * FROM `bi` WHERE `id` = '".$arDataID['id']."'")->fetch();
        $arDataLast['flag'] = intval($boolAEdit); // флаг добавления нового значения bi

        $i = -1;
        foreach ($arResult['INDICATORS']['BI_ID'] as $fnk => $fnData) {
            if ($fnData == $arDataLast['bi_id']) {
                $i = $fnk;
                break;
            }
        }

        if ($i != -1) {
            $arDataLast['state_value'] = '';
            $arResult['DB'][$i] = $arDataLast;
        }
    }
}

foreach ($dataOld as $arDataID) {
    $arDataOld = $connection->query("SELECT * FROM `bi` WHERE `id` = '".$arDataID['id']."'")->fetch();

    $i = -1;
    foreach ($arResult['INDICATORS']['BI_ID'] as $fnk => $fnData) {
        if ($fnData == $arDataOld['bi_id']) {
            $i = $fnk;
            break;
        }
    }

    if ($i != -1 && isset($arResult['DB'][$i])) {
        $arResult['DB'][$i]['state_value_old'] = $arDataOld['state_value'];
        $arResult['DB'][$i]['date_last_change'] = $arDataOld['date'];
    }
}

$arResult['control'] = $control;

// выгрузка в csv
if ($_REQUEST['format'] == 'csv') {
    $strContent = $APPLICATION->EndBufferContent();
    generateBICSVOutput($arResult['INDICATORS'], $arResult['DB'], $control, $arResult['INDICATORS']['DEPARTMENTS']);
    exit;
}

// информация по отчетному периоду
if (!$boolAEdit) {
    if (count($arResult['DB']) > 0) {
        $arDateList = [];
        $arResult['ACTUAL'] = [];

        foreach ($arResult['DB'] as $arActItem) {
            if (strtotime($arActItem['date_last_change']->toString()) <= (time() - TIME_EXCESS_LIMIT)) {
                $arResult['ACTUAL'][$arActItem['control']]['noact'][] = $arActItem['department'];
                $arResult['ACTUAL'][$arActItem['control']]['noact'] = array_unique($arResult['ACTUAL'][$arActItem['control']]['noact']);
            } else {
                $arResult['ACTUAL'][$arActItem['control']]['act'][] = $arActItem['department'];
                $arResult['ACTUAL'][$arActItem['control']]['act'] = array_unique($arResult['ACTUAL'][$arActItem['control']]['act']);
            }
        }
    }
}

foreach (array_column($arResult['DB'], 'date_last_change') as $arItemDate) {
    $arDateList[] = $arItemDate->toString();
}

$arResult['minDate'] = min(array_map(function ($i) {
    return strtotime($i);
}, $arDateList));
$oDate = new DateTime();
$arResult['minDateStamp'] = $oDate->setTimestamp($arResult['minDate']);

$arResult['minDate'] = $oDate->format("d.m.Y");

// сохранение данных при надлежащих разрешениях
if (isset($_POST['INDICATORS']) && isset($pcontrol) && $arResult['boolAEdit']) {
    foreach ($_POST['INDICATORS'] as $val) {
        $date = Date::createFromPhp(new \DateTime());
        $arFields = array(
            'full_name' =>      trim($val['NAME']),
            'bi_id'    =>       intval(trim($val['BI_ID'])),
            'short_name' =>     trim($val['ATT_SHORT_NAME']),
            'base_set' =>       trim($val['ATT_BASE_SET']),
            'target_value' =>   trim($val['ATT_TARGET_VALUE']),
            'state_value' =>    trim($val['ATT_STATE_VALUE']),
            'percent_exec' =>   (intval($val['ATT_PERCENT_EXEC']) > 100) ? 100 : intval($val['ATT_PERCENT_EXEC']),
            'comment' =>        trim($val['ATT_COMMENT']),
            'date' =>           $date,
            'control' =>        trim($arResult['INDICATORS']['CONTROLS'][$pcontrol]),
            'department' =>     trim($arResult['INDICATORS']['DEPARTMENTS'][$val['OTDEL']]),
            'fio' =>            trim($_POST['fio']),
        );

        if ($arResult['isUpdate']&&$val['ID']!='') {
            $arFieldsSQL = $sqlHelper->prepareUpdate('bi', $arFields);
            $connection->queryExecute('UPDATE bi SET ' . $arFieldsSQL[0] . ' WHERE `id`= "' . $val['ID'] . '"');
        } else {
            $arFieldsSQL = $sqlHelper->prepareInsert('bi', $arFields);
            //echo 'INSERT INTO `bi` (' . $arFieldsSQL[0] . ') ' . ' VALUES (' . $arFieldsSQL[1] . ')';
            //die();
            $connection->queryExecute('INSERT INTO `bi` (' . $arFieldsSQL[0] . ') ' . ' VALUES (' . $arFieldsSQL[1] . ')');
        }
    }

    $arResult['INDICATORS_SUCCESS'] = GetMessage("INDICATORS_SUCCESS");
    //header('Refresh: 3; URL=/services/requests/');
}
