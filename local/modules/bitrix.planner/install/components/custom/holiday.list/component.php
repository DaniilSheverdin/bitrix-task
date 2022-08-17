<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/classes/general/xml.php');

use Bitrix\Main\Config\Option;

global $APPLICATION, $USER;

function vardump($vardump)
{
    echo "<pre>";
    var_dump($vardump);
    echo "</pre>";
}

if (!CModule::IncludeModule('iblock') || !CModule::IncludeModule('intranet') || !CModule::IncludeModule('im'))
    die(GetMessage("BITRIX_PLANNER_NE_USTANOVLENY_TREBU"));

$rConnect = \Citto\Integration\Source1C::Connect1C();
$arResult = array();

$arResult['USER_ID'] = $USER->getID();
$thisHeads = $USER->GetList($by = '', $order = '', ['ID' => $arResult['USER_ID']], ['SELECT' => ['UF_THIS_HEADS']])->getNext()['UF_THIS_HEADS'];

$arResult['MY_WORKERS'] = [];
$myWorkers = $USER->GetList($by = '', $order = '', ['ID' => $arResult['USER_ID']], ['SELECT' => ['UF_SUBORDINATE']])->getNext()['~UF_SUBORDINATE'];

if (json_decode($myWorkers) != 0 && count(json_decode($myWorkers)) > 0) {
    $myWorkers = $USER->GetList($by = '', $order = '', ['ID' => implode('|', json_decode($myWorkers))], ['SELECT' => ['UF_*']]);
    while ($a = $myWorkers->GetNext()) {
        $arResult['MY_WORKERS'][$a['ID']] = $a;
    }
}

$arResult['THIS_HEADS'] = [];
if (isset($thisHeads)) {
    $thisHeads = $USER->GetList($by = '', $order = '', ['ID' => $thisHeads]);
    while ($a = $thisHeads->GetNext()) {
        $arResult['THIS_HEADS'][$a['ID']] = "{$a['LAST_NAME']} {$a['NAME']} {$a['SECOND_NAME']}";
    }
}

if (isset($_POST['selectDelegation']))
    $_SESSION['delegate'] = $_POST['selectDelegation'];

if (isset($_SESSION['delegate']))
    $arResult['USER_ID'] = $_SESSION['delegate'];

$APPLICATION->SetTitle(GetMessage("BITRIX_PLANNER_PLANIROVANIE_OTPUSKO"));
$IBLOCK_ID = COption::GetOptionInt('bitrix.planner', "VACATION_RECORDS");
$arResult['MONTH'] = intval($_REQUEST['month']);
$arResult['YEAR'] = (empty($_REQUEST['year'])) ? date('Y') + 1 : intval($_REQUEST['year']);
$arResult['USERS_CADRS'] = unserialize(Option::get('bitrix.planner', "STAFF_DEPARTMENT"));
$arResult['SEL_USER_ID'] = $arResult['USER_ID'];
$arResult['ERROR'] = '';
$arResult['INFO'] = [];
$arResult['PERIOD'] = array();
$arResult['MARKER'] = array();
$arResult['SUMMARY'] = array();
$arResult['ADMIN'] = false;
$arResult['IBLOCK_ID'] = COption::GetOptionInt('intranet', 'iblock_structure');
$arResult['DEPARTMENT_LIST'] = $arResult['DEPARTMENT_IDS'] = [];
$arResult['DEPARTMENT_ID'] = intval($_REQUEST['department']);
$arResult['COUNT_DAYS'] = $arParams['COUNT_DAYS'] == 'Y';
$arResult['HR'] = in_array($arResult['USER_ID'], $arResult['USERS_CADRS']) || $arParams['HR_GROUP_ID'] && in_array($arParams['HR_GROUP_ID'], $USER->GetUserGroupArray());
$arResult['TYPES'] = ['VACATION' => GetMessage("BITRIX_PLANNER_OTPUSK")];
$arResult['DELEGATIONS'] = [];
$arResult['USERCADRS'] = (in_array($arResult['USER_ID'], $arResult['USERS_CADRS'])) ? 1 : 0;
$arResult['EXPORT'] = false;

// Проверка делегирования полномочий
foreach (json_decode(Option::get('bitrix.planner', "DELEGATION_ARR")) as $k => $v) {
    $k = explode('_', $k)[1];
    if ($k == $USER->getID()) {
        foreach ($v as $val) array_push($arResult['DELEGATIONS'], (int)$val);
    }
}

if (count($arResult['DELEGATIONS']) > 0) {
    $users = implode('|', $arResult['DELEGATIONS']);
    $rsUsers = CUser::GetList($by = "last_name", $order = "desc", ['ID' => $users], ['FIELDS' => ['ID', 'LAST_NAME', 'NAME', 'SECOND_NAME']]);
    $arResult['DELEGATIONS'] = [];
    while ($u = $rsUsers->getNext())
        $arResult['DELEGATIONS'][$u['ID']] = [$u['LAST_NAME'] . ' ' . $u['NAME'] . ' ' . $u['SECOND_NAME']];
}

$rs = CIBlockPropertyEnum::GetList($arOrder = array("SORT" => "ASC", "VALUE" => "ASC"), $arFilter = array('IBLOCK_ID' => $IBLOCK_ID, 'PROPERTY_ID' => 'ABSENCE_TYPE'));
while ($f = $rs->Fetch()) {
    $arResult['TYPES'][$f['XML_ID']] = $f['VALUE'];
    $arResult['ABSENCE_TYPES'][$f['XML_ID']] = $f['ID'];
    $arResult['ABSENCE_TYPES'][$f['ID']] = $f['XML_ID'];
}

// Получаем праздники для производственного календаря
function download_page($path)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $path);
    curl_setopt($ch, CURLOPT_FAILONERROR, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    $retValue = curl_exec($ch);
    curl_close($ch);
    return $retValue;
}

$sXML = download_page('http://xmlcalendar.ru/data/ru/' . $arResult['YEAR'] . '/calendar.xml');
$xml = new SimpleXMLElement($sXML);

$arResult['holidays'] = [];
foreach ($xml->days->day as $item) {

    if (isset($item['h'])) {
        $item = explode('.', $item['d']);
        $item = $item[1] . '.' . $item[0] . '.' . $arResult['YEAR'];
        array_push($arResult['holidays'], strtotime($item));
    }
}

$arResult['PREV_YEAR'] = $arResult['NEXT_YEAR'] = $arResult['YEAR'];
$arResult['NEXT_MONTH'] = $arResult['MONTH'] + 1;
$arResult['PREV_MONTH'] = $arResult['MONTH'] - 1;

if ($arResult['NEXT_MONTH'] > 12) {
    $arResult['NEXT_MONTH'] = 1;
    $arResult['NEXT_YEAR'] = $arResult['YEAR'] + 1;
}

if ($arResult['PREV_MONTH'] < 1) {
    $arResult['PREV_MONTH'] = 12;
    $arResult['PREV_YEAR'] = $arResult['YEAR'] - 1;
}

$arResult['RECURSIVE'] = $_REQUEST['recursive'] ? 1 : 0;
$arResult['LAST_DAY'] = date('t', mktime(1, 1, 1, $arResult['MONTH'], 1, $arResult['YEAR']));

// Проверяем на существование пользовательские поля. Если их нет, то создаём
// Поле: Отпуска всех пользователей подтверждены?
$UF_COUNT_ALL_EMP = CUserTypeEntity::GetList(array($by => $order), array('ENTITY_ID' => 'IBLOCK_' . $arResult['IBLOCK_ID'] . '_SECTION', 'FIELD_NAME' => 'UF_COUNT_ALL_EMP'));
if (!$UF_COUNT_ALL_EMP->arResult) {
    $arFields = Array(
        "ENTITY_ID" => 'IBLOCK_' . $arResult['IBLOCK_ID'] . '_SECTION',
        "FIELD_NAME" => "UF_COUNT_ALL_EMP",
        "USER_TYPE_ID" => "string",
        "EDIT_FORM_LABEL" => Array("ru" => "Отпуска всех пользователей подтверждены?", "en" => "UF_COUNT_ALL_EMP")
    );
    $obUserField = new CUserTypeEntity;
    $obUserField->Add($arFields);
}

// Поле: Отправлено ли министерство на согласование с отделом кадров?
$UF_TO_CADRS = CUserTypeEntity::GetList(array($by => $order), array('ENTITY_ID' => 'IBLOCK_' . $arResult['IBLOCK_ID'] . '_SECTION', 'FIELD_NAME' => 'UF_TO_CADRS'));
if (!$UF_TO_CADRS->arResult) {
    $arFields = Array(
        "ENTITY_ID" => 'IBLOCK_' . $arResult['IBLOCK_ID'] . '_SECTION',
        "FIELD_NAME" => "UF_TO_CADRS",
        "USER_TYPE_ID" => "string",
        "EDIT_FORM_LABEL" => Array("ru" => "Отправлена ли структура на согласование с отделом кадров?", "en" => "UF_TO_CADRS")
    );
    $obUserField = new CUserTypeEntity;
    $obUserField->Add($arFields);
}

// Выводим структуру организаций согласно ID работника/руководителя
$ID_OIV = COption::GetOptionInt('bitrix.planner', "ID_OIV");
$res_section = CIBlockSection::GetByID($ID_OIV); // ID категории ОИВов

if ($ar_res = $res_section->GetNext())
    $parent_sec = ['LEFT_MARGIN' => $ar_res['LEFT_MARGIN'], 'RIGHT_MARGIN' => $ar_res['RIGHT_MARGIN']];

$arFilter = array('IBLOCK_ID' => $arResult['IBLOCK_ID'], 'ACTIVE' => 'Y', 'LEFT_MARGIN' => $parent_sec['LEFT_MARGIN'], 'RIGHT_MARGIN' => $parent_sec['RIGHT_MARGIN']);

if ($arResult['HR']) {
    $arResult['ADMIN'] = true;
} elseif (($ar = CIntranetUtils::GetSubordinateDepartments($arResult['USER_ID'], true))) {
    if (in_array($arResult['DEPARTMENT_ID'], $ar))
        $arResult['ADMIN'] = true;
    if ($arParams['SHOW_ALL'] != 'Y')
        $arFilter['ID'] = $ar;
    if (!$arResult['DEPARTMENT_ID'])
        $arResult['DEPARTMENT_ID'] = $ar[0];
} else {
    $f = CUser::GetList($by = 'ID', $order = 'ASC', array('ID' => $arResult['USER_ID']), array('SELECT' => array('UF_DEPARTMENT')))->Fetch();

    if (!$f || !is_array($f['UF_DEPARTMENT']) || count($f['UF_DEPARTMENT']) == 0)
        return;
    if (!$arResult['DEPARTMENT_ID'])
        $arResult['DEPARTMENT_ID'] = $f['UF_DEPARTMENT'][0];
    if ($arParams['SHOW_ALL'] != 'Y')
        $arFilter['ID'] = $f['UF_DEPARTMENT'];
}

$arResult['ALLOW_DAYS_ADD'] = $arResult['HR'] || $arResult['ADMIN'] && $arParams['MANAGER_ADD_DAYS'] != 'N';

CModule::IncludeModule('iblock');

$rs = CIBlockSection::GetList($arOrder = array('left_margin' => 'asc'), $arFilter, true, array('LEFT_BORDER', 'RIGHT_BORDER', 'UF_TO_CADRS', 'UF_HIDEDEP', 'DEPTH_LEVEL', 'LEFT_MARGIN', 'RIGHT_MARGIN', 'UF_HEAD', 'UF_COUNT_ALL_EMP', 'DEPTH_NAME', 'ID', 'IBLOCK_SECTION_ID', 'NAME'));

$UF_HIDEDEP_MARGIN = 0;
$arResult['TO_CADRS'] = false;
$arResult['HEADS'] = [];
while ($f = $rs->GetNext()) {
    if ($f['DEPTH_LEVEL'] == 1) continue;

    if ($f['UF_HIDEDEP']) {
        $UF_HIDEDEP_MARGIN = ($f['RIGHT_MARGIN'] - $f['LEFT_MARGIN']);
        $UF_HIDEDEP_MARGIN = ($UF_HIDEDEP_MARGIN - 1) / 2 + 1;
    }

    if ($UF_HIDEDEP_MARGIN > 0) {
        $UF_HIDEDEP_MARGIN--;
        continue;
    }

    if ($f['UF_HEAD'] == $arResult['USER_ID'])
        $arResult['ADMIN'] = true;

    if ($arResult['ADMIN'] && $f['DEPTH_LEVEL'] == 3)
        $arResult['TO_CADRS'] = true;

    if (isset($f['UF_HEAD'])) array_push($arResult['HEADS'], $f['UF_HEAD']);
    $f['DEPTH_NAME'] = str_repeat('. ', ($f['DEPTH_LEVEL'] - 1)) . $f['NAME'];
    $arResult['DEPARTMENT_LIST'][$f['ID']] = $f;
}

if (isset($_GET['department'])) {
    $nav = CIBlockSection::GetNavChain(false, $_GET['department']);
    while ($n = $nav->getNext()) {
        if ($n['DEPTH_LEVEL'] == 3) {
            $res_section = CIBlockSection::GetByID($n['ID']); // ID категории ОИВов
            if ($ar_res = $res_section->GetNext())
                $parent_sec = ['LEFT_MARGIN' => $ar_res['LEFT_MARGIN'], 'RIGHT_MARGIN' => $ar_res['RIGHT_MARGIN']];
            $arFilter = array('IBLOCK_ID' => $arResult['IBLOCK_ID'], 'ACTIVE' => 'Y', 'LEFT_MARGIN' => $parent_sec['LEFT_MARGIN'], 'RIGHT_MARGIN' => $parent_sec['RIGHT_MARGIN']);
            $rs = CIBlockSection::GetList($arOrder = array('left_margin' => 'asc'), $arFilter, true, array('UF_TO_CADRS', 'DEPTH_LEVEL', 'LEFT_MARGIN', 'RIGHT_MARGIN', 'UF_HEAD', 'UF_COUNT_ALL_EMP', 'DEPTH_NAME', 'ID', 'IBLOCK_SECTION_ID', 'NAME'));
            while ($f = $rs->GetNext()) {
                $arResult['DEPARTMENT_IDS'][$f['ID']] = ['UF_COUNT_ALL_EMP' => $f['UF_COUNT_ALL_EMP'], 'UF_TO_CADRS' => json_decode($f['~UF_TO_CADRS'])];
            }
            break;
        }
    }
}

if (!$arResult['DEPARTMENT_LIST'][$arResult['DEPARTMENT_ID']]) {
    $tmp = array_keys($arResult['DEPARTMENT_LIST']);
    $arResult['DEPARTMENT_ID'] = $tmp[0];
}

$set_user_id = intval($_REQUEST['set_user_id']);
$arResult['BASE_URL'] = '?year=' . $arResult['YEAR'] . '&month=' . $arResult['MONTH'] . '&set_user_id=' . $set_user_id . '&department=' . $arResult['DEPARTMENT_ID'] . '&recursive=' . $arResult['RECURSIVE'];

$tmp = array();
if (!$arResult['ADMIN']) $arResult['RECURSIVE'] = 0;

$rs = CIntranetUtils::GetDepartmentEmployees($arResult['DEPARTMENT_ID'], $arResult['RECURSIVE'], $bSkipSelf = false);

$obCache = new CPHPCache();
function returnResultCache($timeSeconds, $cacheId, $callback, $arCallbackParams = '', $obCache)
{
    $cachePath = '/' . SITE_ID . '/' . $cacheId;
    if ($obCache->InitCache($timeSeconds, 'allusers1s', '/' . SITE_ID . '/allusers1s')) {
        $vars = $obCache->GetVars();
        $result = $vars['result'];
    } else {
        if ($obCache->InitCache($timeSeconds, $cacheId, $cachePath)) {
            $vars = $obCache->GetVars();
            $result = $vars['result'];
        } elseif ($obCache->StartDataCache()) {
            $result = $callback($arCallbackParams);
            $obCache->EndDataCache(array('result' => $result));
        }
    }
    return $result;
}

function fRespone($arParams)
{
    $rConnect = $arParams['rConnect'];
    $xml_ids = $arParams['SIDorINNList'];
    $rRespone = \Citto\Integration\Source1C::GetArray($rConnect, 'VacationLeftovers', ['SIDorINNList' => $xml_ids]);
    $rRespone = $rRespone['Data']['VacationLeftovers']['EmployeeVacationLeftovers'];
    $sids = [];

    if (isset($rRespone['SID'])) {
        foreach ($rRespone as $item)
            if (is_array($item)) $sids[$rRespone['SID']]['item'] = $item;
    } else {
        foreach ($rRespone as $item) {
            $sid = $item['SID'];
            $sids[$sid]['item'] = $item;
        }
    }

    return $sids;
}

$xml_ids = $xml_ids_revert = [];


while ($f = $rs->Fetch()) {

    if ($arResult['ADMIN'] && $set_user_id) {
        if ($f['ID'] == $set_user_id) {
            $arResult['SEL_USER_ID'] = $set_user_id;
            $set_user_id = 0;
        }
    }

    // Если пользователь есть в 1с, то берём его в массив пользователей
    if (!empty($f['XML_ID'])) {
        array_push($xml_ids, $f['XML_ID']);
        $xml_ids_revert[$f['XML_ID']] = $f['ID'];
        $arResult['USERS'][$f['ID']] = $f;
        if ($f['ID'] == $arResult['SEL_USER_ID'])
            $APPLICATION->SetTitle(' [' . $f['LAST_NAME'] . ' ' . $f['NAME'] . ']');
    }
}

if ($arResult['MY_WORKERS']) {
    foreach ($arResult['MY_WORKERS'] as $item) {
        if ($arResult['ADMIN'] && $set_user_id) {
            if ($item['ID'] == $set_user_id) {
                $arResult['SEL_USER_ID'] = $set_user_id;
                $set_user_id = 0;
            }
        }

        // Если пользователь есть в 1с, то берём его в массив пользователей
        if (!empty($item['XML_ID'])) {
            array_push($xml_ids, $item['XML_ID']);
            $xml_ids_revert[$item['XML_ID']] = $item['ID'];
            $arResult['USERS'][$item['ID']] = $item;
            if ($item['ID'] == $arResult['SEL_USER_ID'])
                $APPLICATION->SetTitle(' [' . $item['LAST_NAME'] . ' ' . $item['NAME'] . ']');
        }
    }
}

$rRespone = \Citto\Integration\Source1C::GetArray($rConnect, 'VacationLeftovers', ['SIDorINNList' => $xml_ids]);
$rRespone = $rRespone['Data']['VacationLeftovers']['EmployeeVacationLeftovers'];

if (count($xml_ids) > 0)
    $rRespone = returnResultCache(86400, $arResult['DEPARTMENT_ID'] . $_GET['recursive'], 'fRespone', array('rConnect' => $rConnect, 'SIDorINNList' => $xml_ids), $obCache);

// Подсчёт количества дней отпуска на планируемый год, исходя из двух рабочих периодов
function ratioVacation($from, $to, $fromCount, $toCount, $nextYear)
{
    if (!$nextYear) return $fromCount;
    else {
        $daysInFrom = date('z', mktime(0, 0, 0, 12, 31, date('Y', $from))) + 1;
        $daysInTo = date('z', mktime(0, 0, 0, 12, 31, date('Y', $to))) + 1;
        $restFrom = date('z', $from);
        $restTo = date('z', $to);
        $fromCount = $fromCount - (($daysInFrom - $restFrom) * ($fromCount / $daysInFrom));
        $toCount = ($daysInTo - $restTo) * ($toCount / $daysInTo);
        return round($toCount + $fromCount, 0, PHP_ROUND_HALF_DOWN);
    }
}

$title = $APPLICATION->GetTitle();
foreach ($xml_ids as $it) {
    // Если данных из 1С об отпусках пользователя нет, то присваиваем ему отпуск в 28 дней
    if (empty($rRespone[$it])) {
        $idus = $xml_ids_revert[$it];
        $arResult['USERS'][$idus]['day_left'] = $arResult['USERS'][$idus]['total_days'] = 28;
    }

    foreach ($rRespone[$it] as $item) {
        $days_count = 0;
        $workPeriods = '';
        if (is_array($item)) {
            if (count($xml_ids) == 1 && count($item) == 1) $item['WorkingPeriodsLeftovers'] = $item;

            $arrCDays = [];
            $years = [$arResult['YEAR'] - 1, $arResult['YEAR'], $arResult['YEAR'] + 1];

            foreach ($item['WorkingPeriodsLeftovers']['WorkingPeriodLeftovers'] as $key) {
                // Если один период
                if (empty($key['WorkingPeriod'])) {
                    $explodeYear = $key['DateEnd'];
                    if (in_array(explode('-', $explodeYear)[0], $years)) {
                        $from = $key['DateStart'];
                        $to = $key['DateEnd'];
                    }
                    foreach ($key['Leftover'] as $k => $v) {
                        if (is_array($v)) $days_count = $days_count + $v['AvailableAtEndOfPeriod'];
                        elseif ($k == 'AvailableAtEndOfPeriod') $days_count = $days_count + $v;
                    }
                } // Если несколько периодов
                else {
                    $explodeYear = $key['WorkingPeriod']['DateEnd'];
                    if (in_array(explode('-', $explodeYear)[0], $years)) {
                        $days_count = 0;
                        foreach ($key['Leftovers']['Leftover'] as $k => $v) {
                            if (is_array($v)) $days_count = $days_count + $v['AvailableAtEndOfPeriod'];
                            elseif ($k == 'AvailableAtEndOfPeriod') $days_count = $days_count + $v;
                        }
                        $from = $key['WorkingPeriod']['DateStart'];
                        $to = $key['WorkingPeriod']['DateEnd'];
                        $arrCDays[] = ['from' => $from, 'to' => $to, 'count' => $days_count];
                    }
                    unset($from, $to);
                }
            }
            if (isset($from)) $arrCDays[] = ['from' => $from, 'to' => $to, 'count' => $days_count];

            if ($arrCDays) {
                $from = $to = 0;
                $fromCount = $toCount = 0;
                $nextYear = false;

                $from_prev = $to_prev = 0;
                $fromCount_prev = $toCount_prev = 0;
                $nextYear_prev = false;

                foreach ($arrCDays as $val) {
                    // Текущий год
                    if (explode('-', $val['from'])[0] == $arResult['YEAR'] - 1) {
                        $workPeriods .= date('d.m.Y', strtotime($val['from'])) . '-' . date('d.m.Y', strtotime($val['to']));
                        $from = strtotime($val['from']);
                        $fromCount = $val['count'];
                    }
                    if (explode('-', $val['from'])[0] == $arResult['YEAR']) {
                        if ($workPeriods != '') $workPeriods .= '; ';
                        $workPeriods .= date('d.m.Y', strtotime($val['from'])) . '-' . date('d.m.Y', strtotime($val['to']));
                        $to = strtotime($val['from']);
                        $toCount = $val['count'];
                    }
                    if (explode('-', $val['to'])[0] == $arResult['YEAR'] + 1) {
                        $nextYear = true;
                    }

                    // Предыдущий год
                    if (explode('-', $val['from'])[0] == $arResult['YEAR'] - 2) {
                        $from_prev = strtotime($val['from']);
                        $fromCount_prev = $val['count'];
                    }
                    if (explode('-', $val['from'])[0] == $arResult['YEAR'] - 1) {
                        $to_prev = strtotime($val['from']);
                        $toCount_prev = $val['count'];
                    }
                    if (explode('-', $val['to'])[0] == $arResult['YEAR']) {
                        $nextYear_prev = true;
                    }
                }

                $days_prev = ratioVacation($from_prev, $to_prev, $fromCount_prev, $toCount_prev, $nextYear_prev);
                $days_count = ratioVacation($from, $to, $fromCount, $toCount, $nextYear);

                // Если в следующем рабочем периоде у работника меньше дней, чем в предыдущем (в 1С не сразу обновляется информация по работникам)
                if($days_count < $days_prev) $days_count = $days_prev;
            }
        }

        if ($days_count == 0 || $days_count < 28) $days_count = 28;

        if (count($xml_ids) == 1) {
            $idus = $xml_ids_revert[$it];
            if (empty($idus)) $idus = $xml_ids_revert[$item['SID']];
        } else $idus = $xml_ids_revert[$item['SID']];

        if ($idus != null) {
            $arResult['USERS'][$idus] += ['day_left' => $days_count, 'total_days' => $days_count];

            if ($arResult['SEL_USER_ID'] == $idus) {
                $APPLICATION->SetTitle($title . " Рабочие периоды: $workPeriods");
            }
            $arResult['USERS'][$idus]['WORKPERIODS']['HUMAN'] = explode('; ', $workPeriods);
            [$p1, $p2] = explode('; ', $workPeriods);
            $p1 = explode('-', $p1);
            $p1 = (strtotime($p1[1]) - strtotime('01.01.' . $arResult['YEAR'])) / 86400;
            $p2 = (isset($p2)) ? true : false;
            $arResult['USERS'][$idus]['WORKPERIODS']['PERIODS'] = ['p1' => $p1, 'p2' => $p2];
        }
        $days_count = 0;
    }
}

$map_from = mktime(0, 0, 0, $arResult['MONTH'], 1, $arResult['YEAR']);
$map_to = 86400 + mktime(0, 0, 0, $arResult['MONTH'], $arResult['LAST_DAY'], $arResult['YEAR']);
$arFilter = array(
    'IBLOCK_ID' => $IBLOCK_ID,
    'PROPERTY_USER' => array_keys($arResult['USERS'])
);

$rs = CIBlockElement::GetList($by = array('ACTIVE_FROM' => 'ASC'), $arFilter, false, false, array('*', 'PROPERTY_USER', 'PROPERTY_UF_WHO_APPROVE', 'PROPERTY_ABSENCE_TYPE'));
while ($f = $rs->GetNext()) {
    $uid = intval($f['PROPERTY_USER_VALUE']);
    if (!$uid || !$arResult['USERS'][$uid])
        continue;

    if (!$f['ACTIVE_FROM'] || !$f['ACTIVE_TO'])
        continue;

    $from = MakeTimeStamp($f['ACTIVE_FROM']);
    $to = MakeTimeStamp($f['ACTIVE_TO']);

    $f['CODE'] = $arResult['ABSENCE_TYPES'][$f['PROPERTY_ABSENCE_TYPE_ENUM_ID']];
    if (!$arResult['TYPES'][$f['CODE']])
        $f['CODE'] = 'VACATION';

    if ($f['CODE'] == 'VACATION') {
        $from = TimestampRemoveTime($from);
        $to = TimestampRemoveTime($to);
    }

    if ($to < $from)
        continue;

    if ($_REQUEST['action'] == 'delete' && $_REQUEST['id'] == $f['ID'])
        continue;

    $to_fixed = $to;
    if (date('His', $to) == 0)
        $to_fixed += 86400;

    $from_visible = max(array($from, $map_from));
    $to_visible = min(array($to_fixed, $map_to));
    $period = $to_fixed - $from;

    for ($i = $from; $i <= $to_fixed; $i += 86400) {
        if (in_array($i, $arResult['holidays'])) $period -= 86400;
    }

    $visible_period = $to_visible > $from_visible ? $to_visible - $from_visible : 0;

    $f['PERIOD'] = $period;
    $f['VISIBLE_PERIOD'] = $visible_period;

    $f['HUMAN_TIME'] = MakeHumanTime($period);
    $f['WHO_APPROVE'] = (isset($f['PROPERTY_UF_WHO_APPROVE_VALUE'])) ? json_decode($f['~PROPERTY_UF_WHO_APPROVE_VALUE']) : [];

    $f['PARTIAL'] = $period < 86400 && (date('His', $from) > 0 || date('His', $to) > 0);

    $f['TITLE'] = $arResult['TYPES'][$f['CODE']] . ' ' . $f['ACTIVE_FROM'] . ' - ' . $f['ACTIVE_TO'] . ' (' . $f['HUMAN_TIME'] . ')' . ($f['ACTIVE'] != 'Y' ? ' - ' . GetMessage("BITRIX_PLANNER_NE_PODTVERJDENO") : '') . ($f['PREVIEW_TEXT'] ? ' [' . htmlspecialcharsbx($f['~PREVIEW_TEXT']) . ']' : '');

    $arResult['PERIOD'][] = $f;
    $from0 = $from_visible;
    $last_date = date('Ymd', $to_visible - 1); // 23:59:59

    while ($visible_period) {
        if (date('Ymd', $from0) > $last_date)
            break;
        $d = date('j', $from0);
        $arResult['MARKER'][$uid][$d] = $f;
        if (count($arResult['MARKER'][$uid]) == 1)
            $arResult['MARKER'][$uid][$d]['FIRST_DAY'] = true;
        $from0 += 86400;
    }

    if (date('Y', strtotime($f['ACTIVE_TO'])) == $arResult['YEAR'])
        $arResult['USERS'][$uid]['day_left'] = $arResult['USERS'][$uid]['day_left'] - ($period / 86400);

    if ($f['ACTIVE'] == 'N') $status = 'n_class';
    elseif (in_array($f["MODIFIED_BY"], $arResult['USERS_CADRS'])) $status = 'cadrs_class';
    else $status = 'head_class';
    $arResult['USERS'][$uid]['VACATION'][$from] = ['ID_RECORD' => $f['ID'], 'STATUS' => $status, 'PERIOD' => $period = $to_fixed - $from];
    $arResult['USERS'][$uid]['DETAIL'] = ($f['DETAIL_TEXT']) ? $f['DETAIL_TEXT'] : '';
}

if ($_REQUEST['action'] == 'add' || $_REQUEST['action'] == 'edit') {
    $d_left = $arResult['USERS'][$arResult['SEL_USER_ID']]['day_left'];

    if ($_REQUEST['action'] == 'edit') {
        $getRecord = CIBlockElement::GetList(Array(), ['ID' => $_REQUEST['id']], false, false, ['IBLOCK_ID', 'ID', 'ACTIVE_FROM', 'ACTIVE_TO'])->getNext();
        for ($i = strtotime($getRecord['ACTIVE_FROM']); $i <= strtotime($getRecord['ACTIVE_TO']); $i += 86400)
            if (!in_array($i, $arResult['holidays'])) $d_left++;
    }

    // Проверяем, отгулял ли сотрудник отведённые для него дни
    $d_left_plan = 0; // То самое количество дней, которое необходимо отгулять работнику в планируемом году
    $firstPeriod = 0; // Начало рабочего периода на планируемый год
    $xml_id = $arResult['USERS'][$arResult['SEL_USER_ID']]['XML_ID'];
    $rDcount = \Citto\Integration\Source1C::GetArray($rConnect, 'PersonalData', ['EmployeeID' => $xml_id]);
//    vardump($rDcount['Data']['PersonalData']['Vacation']['DaysCount']);
//    die;
//    if ($rDcount['Data']['PersonalData']['Vacation']['DaysCount'] != $rDcount['Data']['PersonalData']['Vacation']['DaysUsed'] && $d_left == $rDcount['Data']['PersonalData']['Vacation']['DaysCount']) {
    $rRespone = \Citto\Integration\Source1C::GetArray($rConnect, 'PersonalData', ['EmployeeID' => $xml_id]);
    $startPeriod = strtotime($rRespone['Data']['PersonalData']['Vacation']['WorkingPeriod']['DateStart']);
    $firstPeriod = strtotime($rRespone['Data']['PersonalData']['Vacation']['WorkingPeriod']['DateEnd']);

    $rRespone = \Citto\Integration\Source1C::GetArray($rConnect, 'VacationSchedule', ['EmployeeID' => $xml_id]);

    foreach ($rRespone['Data']['VacationSchedule']['VacationScheduleRecord'] as $item) {
        $q = strtotime($item['DateStart']) + 86400 * $item['DateCount'];
        if ($startPeriod < $q)
            $d_left_plan = $d_left_plan + $item['DaysCount'];
    }
    $d_left_plan = $rDcount['Data']['PersonalData']['Vacation']['DaysCount'] - $d_left_plan;

    // Если сотрудник не отгулял 28 дней за предыдущий период, то выводим уведомление
    $d_left_plan = 28 - $rDcount['Data']['PersonalData']['Vacation']['DaysUsed'];
    if (date('Y', $firstPeriod) == $arResult['YEAR'] && $d_left_plan != 0) {
        array_push($arResult['INFO'], "Обратите внимание, до " . date('Y-m-d', $firstPeriod) . " Вы не использовали $d_left_plan дней");
        $sDayVacation = "До " . date('Y-m-d', $firstPeriod) . " необходимо использовать $d_left_plan дней";
    } else $sDayVacation = "";

//    }


    $t0 = MakeTimeStamp($_REQUEST['day_to']);
    $t1 = MakeTimeStamp($_REQUEST['day_from']);
    $period = 1 + ceil(($t0 - $t1) / 86400);

    for ($i = $t1; $i <= $t0; $i += 86400) {
        if (in_array($i, $arResult['holidays'])) $period--;
    }

    if (!$arResult['TYPES'][$type = $_REQUEST['event_type']])
        $type = 'VACATION';
    $name = $arResult['TYPES'][$type];

    if ($t0 >= $t1 && (!$arResult['COUNT_DAYS'] || $type != 'VACATION' || $d_left >= $period)) {

        $match_vacation = false;
        foreach ($arResult['USERS'][$arResult['SEL_USER_ID']]['VACATION'] as $key => $value) {
            $curFrom = $key;
            $curTo = $key + $value['PERIOD'] - 86400;
            if ($t1 >= $curFrom && $t1 <= $curTo || $t0 >= $curFrom && $t0 <= $curTo) {
                $arResult['ERROR'] = "Вы не можете взять отпуск на такой период";
                $match_vacation = true;
            }

            // Проверяем период между отпусками (Должен составлять не менее 30 дней)
            if (($t1 - $curTo) / (3600 * 24) - 1 >= 0 && ($t1 - $curTo) / (3600 * 24) - 1 <= 29) {
                array_push($arResult['INFO'], GetMessage("BITRIX_PLANNER_VY_NE_MOJETE_VZATQ_O_PERIOD"));
            }
            if (($curFrom - $t0) / (3600 * 24) - 1 >= 0 && ($curFrom - $t0) / (3600 * 24) - 1 <= 29) {
                array_push($arResult['INFO'], GetMessage("BITRIX_PLANNER_VY_NE_MOJETE_VZATQ_O_PERIOD"));
            }
        }

        // Если год в планируемом периоде не совпадает с текущим
        if (date('Y', $t0) != $arResult['YEAR'] || date('Y', $t1) != $arResult['YEAR']) {
            $arResult['ERROR'] = "Вы не можете взять отпуск на такой период, так как в нём должен содержаться только " . $arResult['YEAR'] . " год";
            $match_vacation = true;
        }

        if (!$match_vacation) {
            // Первый отпуск должен составлять 14 дней
            if ((count($arResult['USERS'][$arResult['SEL_USER_ID']]["VACATION"]) == 0) && $period < 14) {
                $arResult['ERROR'] = "Первый распланированный отпуск должен составлять не менее 14 дней";
            } elseif ((count($arResult['USERS'][$arResult['SEL_USER_ID']]["VACATION"]) > 0)) {
                foreach ($arResult['USERS'][$arResult['SEL_USER_ID']]["VACATION"] as $val) {
                    if ($val["PERIOD"] / 86400 >= 14) {
                        $match_vacation = false;
                        break;
                    } elseif ($val["PERIOD"] / 86400 < 14 && $period < 14) {
                        $arResult['ERROR'] = "Один из распланированных отпусков должен составлять не менее 14 дней";
                        $match_vacation = true;
                    }
                }
                if (!$match_vacation) {
                    if ($period > 14)
                        array_push($arResult['INFO'], "Обратите внимание, Ваш планируемый период превышает 14 дней");
                    if ($period % 7 != 0 && $d_left - $period != 0)
                        array_push($arResult['INFO'], "Обратите внимание, Ваш планируемый период не кратен 7 дням");
                    if ($d_left - $period < 7 && $d_left - $period != 0)
                        array_push($arResult['INFO'], "Обратите внимание, остаток для распланирования составляет менее 7 дней");
                    if ($period < 7)
                        array_push($arResult['INFO'], "Обратите внимание, Ваш планируемый период менее 7 дней");
                    $add = true;
                }
            } else $add = true;
        }
        if (!$match_vacation && $add) {
            $el = new CIBlockElement;
            if ($_REQUEST['action'] == 'edit') {
                $recId = $el->Update($_REQUEST['id'],
                    array(
                        'NAME' => $name,
                        'CODE' => $type,
                        'ACTIVE_FROM' => $_REQUEST['day_from'],
                        'ACTIVE_TO' => $_REQUEST['day_to'],
                        'PREVIEW_TEXT' => $_REQUEST['PREVIEW_TEXT'],
                        'PROPERTY_VALUES' => array(
                            'USER' => $arResult['SEL_USER_ID'],
                            'ABSENCE_TYPE' => $arResult['ABSENCE_TYPES'][$type]
                        )
                    )
                );
                if ($recId) $recId = $_REQUEST['id'];
                LocalRedirect($APPLICATION->GetCurPage() . $arResult['BASE_URL']);
            } elseif ($_REQUEST['action'] == 'add') {
                $recId = $el->Add(array(
                        'IBLOCK_ID' => $IBLOCK_ID,
                        'NAME' => $name,
                        'CODE' => $type,
                        'ACTIVE' => 'N',
                        'ACTIVE_FROM' => $_REQUEST['day_from'],
                        'ACTIVE_TO' => $_REQUEST['day_to'],
                        'DETAIL_TEXT' => $sDayVacation,
                        'PREVIEW_TEXT' => $_REQUEST['PREVIEW_TEXT'],
                        'PROPERTY_VALUES' => array(
                            'USER' => $arResult['SEL_USER_ID'],
                            'ABSENCE_TYPE' => $arResult['ABSENCE_TYPES'][$type]
                        )
                    )
                );
                if (isset($sDayVacation)) {
                    $arResult['USERS'][$arResult['SEL_USER_ID']]['DETAIL'] = $sDayVacation;
                }
            }

            if ($recId) {
                $curPeriod = CIBlockElement::GetList(Array(), Array('IBLOCK_ID' => $IBLOCK_ID, "ID" => $recId), false, false, ['*', 'PROPERTY_USER', 'PROPERTY_ABSENCE_TYPE'])->Fetch();
                $curPeriod['PERIOD'] = strtotime($_REQUEST['day_to']) - strtotime($_REQUEST['day_from']) + 86400;
                $period = $curPeriod['PERIOD'];
                for ($i = strtotime($_REQUEST['day_from']); $i <= strtotime($_REQUEST['day_to']); $i += 86400) {
                    if (in_array($i, $arResult['holidays'])) $curPeriod['PERIOD'] -= 86400;
                }
                $arResult['PERIOD'][] = $curPeriod;
                $arResult['USERS'][$arResult['SEL_USER_ID']]['VACATION'][strtotime($_REQUEST['day_from'])] = ['ID_RECORD' => $recId, 'STATUS' => 'n_class', 'PERIOD' => $period];
                $arResult['USERS'][$arResult['SEL_USER_ID']]['day_left'] = $arResult['USERS'][$arResult['SEL_USER_ID']]['day_left'] - $curPeriod['PERIOD'] / 86400;

                $comment = $_REQUEST['PREVIEW_TEXT'] ? ' [' . $_REQUEST['PREVIEW_TEXT'] . ']' : '';
                $ar = CIntranetUtils::GetUserDepartments($arResult['USER_ID']);
                if ($id = CIntranetUtils::GetDepartmentManagerID($ar[0]))
                    ImNotify($arResult['SEL_USER_ID'], $id, GetMessage("BITRIX_PLANNER_DOBAVLENO") . $name, GetMessage("BITRIX_PLANNER_DOBAVLNO") . $name . ' [' . $arResult['USERS'][$arResult['USER_ID']]['NAME'] . ' ' . $arResult['USERS'][$arResult['USER_ID']]['LAST_NAME'] . '] ' . $_REQUEST['day_from'] . ' - ' . $_REQUEST['day_to'] . $comment);
            } else {
                $arResult['ERROR'] = $el->LAST_ERROR;
            }
        }
    } else $arResult['ERROR'] = "Вы не можете взять отпуск на такой период";
} elseif ($_REQUEST['action'] == 'delete') {
    if ($_REQUEST['action'] == 'delete') {
        $ids_del = json_decode($_REQUEST['id']);
        foreach ($ids_del as $item) {
            $rs = CIBlockElement::GetList($by = array('ACTIVE_FROM' => 'ASC'), $arFilter = array('IBLOCK_ID' => $IBLOCK_ID, 'ACTIVE' => 'N', 'ID' => $item), false, false, array('*', 'PROPERTY_USER', 'PROPERTY_ABSENCE_TYPE'));
            while ($f = $rs->Fetch()) {
                $uid = intval($f['PROPERTY_USER_VALUE']);
                if ($uid == $arResult['USER_ID'] || $arResult['ADMIN']) {
                    CIBlockElement::Delete($f['ID']);
                    ImNotify($arResult['USER_ID'], $uid, GetMessage("BITRIX_PLANNER_ZAPISQ_UDALENA"), GetMessage("BITRIX_PLANNER_ZAPISQ_UDALENA1") . $arResult['TYPES'][$f['CODE']] . ' [' . $f['ACTIVE_FROM'] . ' - ' . $f['ACTIVE_TO'] . ']');
                }
            }
        }
        LocalRedirect($APPLICATION->GetCurPage() . $arResult['BASE_URL']);
    }
} elseif ($_REQUEST['action'] == 'approve' || $_REQUEST['action'] == 'unapprove') {
    $reqId = json_decode($_REQUEST['id']);
    $rs = CIBlockElement::GetList($by = array('ACTIVE_FROM' => 'ASC'), $arFilter = array('IBLOCK_ID' => $IBLOCK_ID, 'ID' => $reqId), false, false, array('*', 'PROPERTY_USER', 'PROPERTY_UF_WHO_APPROVE', 'PROPERTY_ABSENCE_TYPE'));
    if (isset($rs) && !empty($reqId)) {
        while ($f = $rs->Fetch()) {
            $arHeads = explode('|', $arResult['USERS'][$f['PROPERTY_USER_VALUE']]['UF_THIS_HEADS']);
            if (in_array($arResult['USER_ID'], $arHeads) || $arResult['USERCADRS']) {
                $el = new CIBlockElement;
                $boolInCadrs = $arResult['USERCADRS'];
                $active = 'N';
                if (isset($f["PROPERTY_UF_WHO_APPROVE_VALUE"])) {
                    $whoApprove = json_decode($f["PROPERTY_UF_WHO_APPROVE_VALUE"]);
                    if ($_REQUEST['action'] == 'unapprove') {
                        if ($boolInCadrs || in_array($arResult['USER_ID'], $whoApprove)) {
                            $arUsDep = $arResult['USERS'][$f['PROPERTY_USER_VALUE']]["UF_DEPARTMENT"];
                            $arUsDep = CIBlockSection::GetList("", ['IBLOCK_ID' => COption::GetOptionInt('intranet', 'iblock_structure'), 'ID' => $arUsDep], false, ['ID', 'IBLOCK_ID', 'UF_TO_CADRS']);
                            while ($a = $arUsDep->getNext()) {
                                $jsonCadrs = json_decode($a["~UF_TO_CADRS"]);
                                if (isset($jsonCadrs)) {
                                    $yorn = [];
                                    foreach ($jsonCadrs as $k => $v) {
                                        if ($v != $arResult['YEAR']) $yorn[$k] = $v;
                                    }
                                    $yorn[$arResult['YEAR']] = 'N';
                                } else $yorn = [$arResult['YEAR'] => 'N'];
                                $yorn = json_encode($yorn);
                                $el_dep = new CIBlockSection;
                                $el_dep->Update($a['ID'], array('UF_TO_CADRS' => $yorn));
                            }
                        }

                        if ($boolInCadrs) $whoApprove = [];
                        else if (in_array($arResult['USER_ID'], $whoApprove)) unset($whoApprove[array_search($arResult['USER_ID'], $whoApprove)]);
                        else continue;
                    } else {
                        if (!in_array($arResult['USER_ID'], $whoApprove)) {
                            $active = 'Y';
                            array_push($whoApprove, $arResult['USER_ID']);
                            foreach ($arHeads as $val) {
                                if (!in_array($val, $whoApprove)) $active = 'N';
                            }
                            $active = ($boolInCadrs) ? 'Y' : $active;
                        } else continue;
                    }
                    $whoApprove = array_values($whoApprove);

                    $el->Update($f['ID'], array('MODIFIED_BY' => $arResult['USER_ID'], 'ACTIVE' => $active));
                    $whoApprove = json_encode($whoApprove);
                    CIBlockElement::SetPropertyValueCode($f['ID'], "UF_WHO_APPROVE", $whoApprove);
                } else {
                    if ($_REQUEST['action'] == 'approve') {
                        $whoApprove = json_encode([$arResult['USER_ID']]);
                        CIBlockElement::SetPropertyValueCode($f['ID'], "UF_WHO_APPROVE", $whoApprove);
                        $active = ((count($arHeads) == 1 || $boolInCadrs) ? 'Y' : 'N');
                        $el->Update($f['ID'], array('MODIFIED_BY' => $arResult['USER_ID'], 'ACTIVE' => $active));
                    }

                    if ($_REQUEST['action'] == 'unapprove') {
                        $arUsDep = $arResult['USERS'][$f['PROPERTY_USER_VALUE']]["UF_DEPARTMENT"];
                        $arUsDep = CIBlockSection::GetList("", ['IBLOCK_ID' => COption::GetOptionInt('intranet', 'iblock_structure'), 'ID' => $arUsDep], false, ['ID', 'IBLOCK_ID', 'UF_TO_CADRS']);
                        while ($a = $arUsDep->getNext()) {
                            $jsonCadrs = json_decode($a["~UF_TO_CADRS"]);
                            if (isset($jsonCadrs)) {
                                $yorn = [];
                                foreach ($jsonCadrs as $k => $v) {
                                    if ($v != $arResult['YEAR']) $yorn[$k] = $v;
                                }
                                $yorn[$arResult['YEAR']] = 'N';
                            } else $yorn = [$arResult['YEAR'] => 'N'];
                            $yorn = json_encode($yorn);
                            $el_dep = new CIBlockSection;
                            $el_dep->Update($a['ID'], array('UF_TO_CADRS' => $yorn));
                        }
                        $el->Update($f['ID'], array('MODIFIED_BY' => $arResult['USER_ID'], 'ACTIVE' => 'N'));
                    }
                }

                if ($active == 'Y') ImNotify($arResult['USER_ID'], $uid, GetMessage("BITRIX_PLANNER_PODTVERJDENO") . $name, GetMessage("BITRIX_PLANNER_PODTVERJDNO") . $name . ' [' . $f['ACTIVE_FROM'] . ' - ' . $f['ACTIVE_TO'] . ']');

            } else
                $arResult['ERROR'] = GetMessage("BITRIX_PLANNER_NET_PRAV_NA_OPERACIU");
        }
        LocalRedirect($APPLICATION->GetCurPage() . $arResult['BASE_URL']);
    } else
        $arResult['ERROR'] = GetMessage("BITRIX_PLANNER_ZAPISQ_NE_NAYDENA");
}

$arResult['EXPORT'] = ($arResult['ADMIN']) ? true : false;

// Если пользователь является главой министерства($arResult['TO_CADRS']) и все отпуска утверждены, то добавляем опцию согласования с отделом кадров
if ($arResult['TO_CADRS']) {
    $arResult['SHOW_BUTTON'] = false;
    $iCount = 0;
    foreach ($arResult['DEPARTMENT_IDS'] as $s => $key) {
        foreach ($key["UF_TO_CADRS"] as $k => $v) {
            if ($k == $arResult['YEAR'] && $v == 'Y') $iCount++;
        }
    }
    if ($_GET['recursive'] == 1 && $_GET['department'] == array_keys($arResult['DEPARTMENT_IDS'])[0]) $arResult['SHOW_BUTTON'] = (count($arResult['DEPARTMENT_IDS']) == $iCount) ? false : true;

    if ($_REQUEST['to_cadrs'] == 'yes' && $arResult['TO_CADRS']) {
        foreach ($arResult['DEPARTMENT_IDS'] as $key => $value) {
            if (isset($value["UF_TO_CADRS"])) {
                $yorn = [];
                foreach ($value["UF_TO_CADRS"] as $k => $v) {
                    if ($v != $arResult['YEAR']) $yorn[$k] = $v;
                }
                $yorn[$arResult['YEAR']] = 'Y';
            } else $yorn = [$arResult['YEAR'] => 'Y'];
            $yorn = json_encode($yorn);
            $el_dep = new CIBlockSection;
            $el_dep->Update($key, array('UF_TO_CADRS' => $yorn));
        }
        foreach ($arResult['USERS_CADRS'] as $item) {
            ImNotify($arResult['USER_ID'], $item, $arResult['DEPARTMENT_LIST'][array_keys($arResult['DEPARTMENT_IDS'])[0]]['NAME'], '<a href="' . $_SERVER['REQUEST_URI'] . '">' . $arResult['DEPARTMENT_LIST'][array_keys($arResult['DEPARTMENT_IDS'])[0]]['NAME'] . ' - График отпусков отправлен на согласование</a>');
        }
        LocalRedirect($APPLICATION->GetCurPage() . $arResult['BASE_URL']);
    }
}

CUtil::InitJSCore(array("tooltip"));
$this->IncludeComponentTemplate();

function MakeHumanTime($t)
{
    $w = floor($t / 86400 / 7);
    $d = floor($t % (86400 * 7) / 86400);
    $h = floor($t % 86400 / 3600);
    $m = floor($t % 3600 / 60);

    $res = '';
    if ($w)
        $res .= $w . ' ' . GetMessage("BITRIX_PLANNER_NED");
    if ($d)
        $res .= $d . ' ' . GetMessage("BITRIX_PLANNER_DN1");
    if ($h)
        $res .= $h . ' ' . GetMessage("BITRIX_PLANNER_C");
    if ($m)
        $res .= $m . ' ' . GetMessage("BITRIX_PLANNER_MIN");
    return trim($res);
}

function ImNotify($from, $to, $subject, $body)
{
    if ($from != $to)
        return CIMMessenger::Add(array(
            'TITLE' => $subject,
            'MESSAGE' => $body,
            'TO_USER_ID' => $to,
            'FROM_USER_ID' => $from,
            'MESSAGE_TYPE' => 'S', # P - private chat, G - group chat, S - notification
            'NOTIFY_MODULE' => 'intranet',
            'NOTIFY_TYPE' => 2,  # 1 - confirm, 2 - notify single from, 4 - notify single
        ));
}

function GetPeriod($f)
{
    $diff = MakeTimeStamp($f['ACTIVE_TO']) - MakeTimeStamp($f['ACTIVE_FROM']);
    if ($diff % 86400 == 0)
        $diff += 86400;
    return $diff;
}

function TimestampRemoveTime($time)
{
    return $time - date('H', $time) * 3600 - date('i', $time) * 60 - date('s', $time);
}
