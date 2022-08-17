<?php

namespace HolidayList;

use COption;
use SimpleXMLElement;
use CUser;
use CIBlockPropertyEnum;
use CPHPCache;
use CEvent;
use CIMMessenger;
use CIBlockElement;
use HolidayList;
use CIBlockSection;
use Citto\Integration\Source1C;
use Bitrix\Main\UserTable;

class CVacations
{
    public $year;
    public $iBlockVacation;
    private static $connect1C = null;
    private static $arHolidays = null;

    public function __construct()
    {
        $this->year = (empty($_REQUEST['year'])) ? date('Y') + 1 : intval($_REQUEST['year']);
        $this->iBlockVacation = COption::GetOptionInt('bitrix.planner', 'VACATION_RECORDS');
    }

    public function getConnect1C()
    {
        if (self::$connect1C == null) {
            self::$connect1C = Source1C::Connect1C();
        }
        return self::$connect1C;
    }


    public function getBaseUrl($departmentID = null, $recursive = false)
    {
        $set_user_id = $set_user_id = intval($_REQUEST['set_user_id']);
        $sGetZam = ($_GET['getzam'] == 'true') ? 'true' : 'false';
        $sHidePodved = ($_GET['podved'] == 'true') ? 'true' : 'false';
        $url = "?podved={$sHidePodved}&getzam={$sGetZam}&year={$this->year}&set_user_id={$set_user_id}&department={$departmentID}&recursive={$recursive}&myworkers=" . ($_REQUEST['myworkers']);
        return $url;
    }

    public function getHolidays($iYear = null)
    {
        if (self::$arHolidays == null) {
            $iYear = (empty($iYear)) ? $this->year : $iYear;

            $path = 'http://xmlcalendar.ru/data/ru/' . $iYear . '/calendar.xml';
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $path);
            curl_setopt($ch, CURLOPT_FAILONERROR, 1);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            $retValue = curl_exec($ch);
            curl_close($ch);

            if ($retValue) {
                $xml = new SimpleXMLElement($retValue);
                $holidays = [];
                foreach ($xml->days->day as $item) {
                    if (isset($item['h'])) {
                        $tmp = explode('.', $item['d']);
                        $tmp = $tmp[1] . '.' . $tmp[0] . '.' . $iYear;
                        $holidays['holydays'][] = strtotime($tmp);
                    }
                    if ($item['t'] == 1 && !isset($item['h'])) {
                        $tmp = explode('.', $item['d']);
                        $tmp = $tmp[1] . '.' . $tmp[0] . '.' . $iYear;
                        $holidays['weekends'][] = strtotime($tmp);
                    }
                    if ($item['t'] == 2 && !isset($item['h'])) {
                        $tmp = explode('.', $item['d']);
                        $tmp = $tmp[1] . '.' . $tmp[0] . '.' . $iYear;
                        $holidays['shortdays'][] = strtotime($tmp);
                    }
                }
                self::$arHolidays = $holidays;
            } else {
                self::$arHolidays = 'error';
            }
        }
        return self::$arHolidays;
    }

    public function getAbsences($iBlockVacation = 0)
    {
        $arTypes = [];
        $arAbsence = [];
        $rs = CIBlockPropertyEnum::GetList(
            $arOrder = array("SORT" => "ASC", "VALUE" => "ASC"),
            $arFilter = array('IBLOCK_ID' => $iBlockVacation, 'PROPERTY_ID' => 'ABSENCE_TYPE')
        );
        while ($f = $rs->Fetch()) {
            $arTypes[$f['XML_ID']] = $f['VALUE'];
            $arAbsence[$f['XML_ID']] = $f['ID'];
            $arAbsence[$f['ID']] = $f['XML_ID'];
        }
        return ['TYPES' => $arTypes, 'ABSENCE' => $arAbsence];
    }

    public function ratioVacation($from, $to, $fromCount, $toCount, $nextYear)
    {
        if (!$nextYear) {
            return $fromCount;
        } else {
            $daysInFrom = date('z', mktime(0, 0, 0, 12, 31, date('Y', $from))) + 1;
            $daysInTo = date('z', mktime(0, 0, 0, 12, 31, date('Y', $to))) + 1;
            $restFrom = date('z', $from);
            $restTo = date('z', $to);
            $fromCount = $fromCount - (($daysInFrom - $restFrom) * ($fromCount / $daysInFrom));
            $toCount = ($daysInTo - $restTo) * ($toCount / $daysInTo);
            return round($toCount + $fromCount, 0, PHP_ROUND_HALF_DOWN);
        }
    }

    public function returnResultCache($timeSeconds, $cacheId, $xml_ids = [])
    {
        $obCache = new CPHPCache();
        $cacheId = 'VacationLeftovers2';
        $xml_ids = '';
        $cachePath = "/bitrix/cache/{$cacheId}";
        if ($obCache->InitCache($timeSeconds, 'allusers1s', $cachePath)) {
            $vars = $obCache->GetVars();
            $result = $vars['result'];
        } else {
            if ($obCache->InitCache($timeSeconds, $cacheId, $cachePath)) {
                $vars = $obCache->GetVars();
                $result = $vars['result'];
            } elseif ($obCache->StartDataCache()) {
                $result = $this->getResponeVacation($xml_ids);
                $obCache->EndDataCache(array('result' => $result));
            }
        }
        return $result;
    }

    public function getResponeVacation($xml_ids = [])
    {
        $rRespone = Source1C::GetArray($this->getConnect1C(), 'VacationLeftovers', ['SIDorINNList' => $xml_ids]);
        $rRespone = $rRespone['Data']['VacationLeftovers']['EmployeeVacationLeftovers'];
        $sids = [];
        if (isset($rRespone['SID'])) {
            foreach ($rRespone as $item) {
                if (is_array($item)) {
                    $sids[ $rRespone['SID'] ]['item'] = $item;
                }
            }
        } else {
            foreach ($rRespone as $item) {
                $sid = $item['SID'];
                $sids[ $sid ]['item'] = $item;
            }
        }
        return $sids;
    }

    public function getWorksPeriods(
        $xml_ids = [],
        $xml_ids_revert = [],
        $arUsers = [],
        $rRespone = []
    ) {
        global $APPLICATION;
        $users = CUsers::getInstance();
        $arProblemUsers = [];
        $title = $APPLICATION->GetTitle();
        $arMinisters = [];
        $arUsers = $this->getCitVacations($arUsers);

        foreach ($xml_ids as $it) {
            // Если данных из 1С об отпусках пользователя нет, то присваиваем ему отпуск в 28 дней
            $idus = $xml_ids_revert[$it];
            if (empty($rRespone[ $it ])) {
                $iDefaultDaysVacation = 28;
                if (!empty($arUsers[ $idus ]['CIT_DAYS'])) {
                    $iDefaultDaysVacation = $arUsers[ $idus ]['CIT_DAYS'];
                }
                $arUsers[ $idus ]['day_left'] = $arUsers[ $idus ]['total_days'] = $iDefaultDaysVacation;
                array_push($arProblemUsers, $idus);
            }

            $iDopDays = $arUsers[$idus]['UF_VACATION_DOP_DAYS'];

            if ($iDopDays > 0 ) {
                $arUsers[$idus]['day_left'] += $iDopDays;
                $arUsers[$idus]['total_days'] = $arUsers[$idus]['day_left'];
            }

            foreach ($rRespone[ $it ] as $item) {
                $days_count = 0;
                $totalUsed = 0;
                $workPeriods = [];

                if (is_array($item)) {
                    if (count($xml_ids) == 1 && count($item) == 1) {
                        $item['WorkingPeriodsLeftovers'] = $item;
                    }

                    $arrCDays = [];
                    foreach ($item['WorkingPeriodsLeftovers']['WorkingPeriodLeftovers'] as $key) {
                        // Если один период
                        if (empty($key['WorkingPeriod'])) {
                            if (!empty($key['DateStart'])) {
                                $from = $key['DateStart'];
                                $to = $key['DateEnd'];
                            }

                            if (
                                !isset($key['Leftover'][0]) &&
                                isset($key['Leftover']['TotalUsed'])
                            ) {
                                $key['Leftover'] = [
                                    $key['Leftover']
                                ];
                            }
                            foreach ($key['Leftover'] as $k => $v) {
                                if (is_array($v)) {
                                    $days_count = $days_count + $v['AvailableAtEndOfPeriod'];
                                    $totalUsed = $totalUsed + $v['TotalUsed'];
                                    $dayType = $v['TypeOfVacation'] == 'Основной' ? 'Main' : 'Other';
                                    $dayTypes[ $dayType ]['avail'] += $v['AvailableAtEndOfPeriod'];
                                    $dayTypes[ $dayType ]['used'] += $v['TotalUsed'];
                                }
                            }
                            $arrCDays[date('Y', strtotime($to))] = [
                                'from'          => $from,
                                'to'            => $to,
                                'from_ts'       => strtotime($from),
                                'to_ts'         => strtotime($to),
                                'count'         => $days_count,
                                'count_by_type' => $dayTypes,
                                'totalused'     => $totalUsed,
                                'free'          => ($days_count - $totalUsed),
                            ];
                        } else {
                            // Если несколько периодов
                            $days_count = 0;
                            $totalUsed = 0;
                            $dayTypes = [];

                            if (
                                !isset($key['Leftovers']['Leftover'][0]) &&
                                isset($key['Leftovers']['Leftover']['TotalUsed'])
                            ) {
                                $key['Leftovers']['Leftover'] = [
                                    $key['Leftovers']['Leftover']
                                ];
                            }
                            foreach ($key['Leftovers']['Leftover'] as $k => $v) {
                                $days_count = $days_count + $v['AvailableAtEndOfPeriod'];
                                $totalUsed = $totalUsed + $v['TotalUsed'];
                                $dayType = $v['TypeOfVacation'] == 'Основной' ? 'Main' : 'Other';
                                $dayTypes[ $dayType ]['avail'] += $v['AvailableAtEndOfPeriod'];
                                $dayTypes[ $dayType ]['used'] += $v['TotalUsed'];
                            }
                            $from = $key['WorkingPeriod']['DateStart'];
                            $to = $key['WorkingPeriod']['DateEnd'];
                            $arrCDays[date('Y', strtotime($to))] = [
                                'from'          => $from,
                                'to'            => $to,
                                'from_ts'       => strtotime($from),
                                'to_ts'         => strtotime($to),
                                'count'         => $days_count,
                                'count_by_type' => $dayTypes,
                                'totalused'     => $totalUsed,
                                'free'          => ($days_count - $totalUsed),
                            ];
                            unset($from, $to);
                        }
                    }

                    if ($arrCDays) {
                        $needNextYear = date('Y')+1;
                        if (isset($arrCDays[ $needNextYear ]) && !isset($arrCDays[ $needNextYear+1 ])) {
                            $needNextYear++;
                        }
                        if (
                            empty($arrCDays[ $needNextYear ]) &&
                            date('d.m', $arrCDays[ $needNextYear ]['from_ts']) != '01.01'
                        ) {
                            $arNewPeriod = $arrCDays[ $needNextYear-1 ];
                            $arNewPeriod['from'] = date('Y-m-d', strtotime($arNewPeriod['from'] . ' + 1 YEAR'));
                            $arNewPeriod['from_ts'] = strtotime($arNewPeriod['from']);
                            $arNewPeriod['to'] = date('Y-m-d', strtotime($arNewPeriod['to'] . ' + 1 YEAR'));
                            $arNewPeriod['to_ts'] = strtotime($arNewPeriod['to']);
                            $arNewPeriod['free'] = $arNewPeriod['count'];
                            $arNewPeriod['totalused'] = 0;
                            $arrCDays[ $needNextYear ] = $arNewPeriod;
                            $totalUsed = 0;
                            unset($from, $to);
                        }
                    }

                    // if (isset($from)) {
                    //     $arrCDays[ date('Y', strtotime($to)) ] = [
                    //         'from'          => $from,
                    //         'to'            => $to,
                    //         'count'         => $days_count,
                    //         'count_by_type' => $dayTypes,
                    //         'totalused'     => $totalUsed,
                    //         'free'          => ($days_count - $totalUsed),
                    //         'from_ts'       => strtotime($from),
                    //         'to_ts'         => strtotime($to),
                    //     ];
                    // }

                    if ($arrCDays) {
                        [
                            'workperiods'   => $workPeriods,
                            'from'          => $from,
                            'to'            => $to,
                            'fromcount'     => $fromCount,
                            'tocount'       => $toCount,
                            'nextyear'      => $nextYear,
                            'fromprev'      => $from_prev,
                            'toprev'        => $to_prev,
                            'fromcountprev' => $fromCount_prev,
                            'tocountprev'   => $toCount_prev,
                            'nextyearprev'  => $nextYear_prev,
                            'count_by_type' => $countByType,
                        ] = $this->getRightPeriods($this->year, $arrCDays);

                        $days_prev = $this->ratioVacation(
                            $from_prev,
                            $to_prev,
                            $fromCount_prev,
                            $toCount_prev,
                            $nextYear_prev
                        );
                        $days_count = $this->ratioVacation(
                            $from,
                            $to,
                            $fromCount,
                            $toCount,
                            $nextYear
                        );

                        $arDaysLeft = [
                            'from'  => $from,
                            'to'    => $to,
                            'left'  => $totalUsed,
                        ];
                        // if (date('Y', $to) > $this->year) $arDaysLeft = ['from' => $from_prev, 'to' => $to_prev, 'left' => ($totalUsed)];

                        // Если в следующем рабочем периоде у работника меньше дней, чем в предыдущем
                        // (в 1С не сразу обновляется информация по работникам)
                        if ($days_count < $days_prev) {
                            $days_count = $fromCount_prev;
                        } elseif ($days_count != $days_prev) {
                            // Посчитаю количество дней в периодах.
                            // Если в предыдущем больше - то берем оттуда
                            $prevCount = ($to_prev - strtotime(date('01.01.Y', $to_prev))) / 86400;
                            $currCount = (strtotime(date('31.12.Y 23:59:59', $from)) - $from) / 86400;
                            if ($prevCount > $currCount) {
                                $days_count = $days_prev;
                            }
                        }
                    }

                    // ob_start();
                    // $rVacationResponse = Source1C::GetArray(
                    //     self::$connect1C,
                    //     'VacationSchedule',
                    //     [
                    //         'EmployeeID' => $item['SID']
                    //     ],
                    //     true
                    // );
                    // ob_end_clean();
                    // if ($rVacationResponse['result'] == 1) {
                    //     pre($rVacationResponse['Data']['VacationSchedule']);
                    // }
                }

                if ($days_count == 0 || $days_count < 28) {
                    $days_count = 28;
                }

                if (count($xml_ids) == 1) {
                    $idus = $xml_ids_revert[$it];
                    if (empty($idus)) {
                        $idus = $xml_ids_revert[$item['SID']];
                    }
                } else {
                    $idus = $xml_ids_revert[$item['SID']];
                }

                if ($idus != null) {
                    foreach (array_keys($arrCDays) as $k) {
                        if ($k <= date('Y')-1) {
                            unset($arrCDays[ $k ]);
                        }
                    }
                    $arUsers[ $idus ] += [
                        'day_left'      => $days_count,
                        'total_days'    => $days_count,
                        'count_by_type' => $countByType,
                        'days_info'     => $arrCDays,
                    ];
                    $arUsers[ $idus ]['DAYSLEFT'] = $arDaysLeft;

                    $workPeriods = implode('; ', $workPeriods);

                    if ($users->getSelUserID() == $idus) {
                        $APPLICATION->SetTitle($title . " Рабочие периоды: $workPeriods");
                    }
                    $arUsers[ $idus ]['WORKPERIODS']['HUMAN'] = explode('; ', $workPeriods);

                    [$p1, $p2] = explode('; ', $workPeriods);

                    if (empty($p1)) {
                        array_push($arProblemUsers, $idus);
                    }

                    $p1 = explode('-', $p1);
                    $p1 = (strtotime($p1[1]) - strtotime('01.01.' . $this->year)) / 86400;
                    $p2 = (isset($p2)) ? true : false;

                    $arUsers[ $idus ]['WORKPERIODS']['PERIODS'] = ['p1' => $p1, 'p2' => $p2];
                    $arUsers[ $idus ]['ROLE'] = $this->getPositionType($item['PositionType']);

                    if ($arUsers[ $idus ]['ROLE'] == 'ASSISTANT') {
                        $arMinisters[ $idus ] = $arUsers[ $idus ];
                    }
                }
            }
        }

        if ($_GET['getzam'] == 'true') {
            $arUsers = $arMinisters;
        }

        return $arUsers;
    }

    private function getPositionType($positionType = '')
    {
        switch ($positionType) {
            case "НеГосслужащие":
                return 'NOT_SERVANT';
            case "ГосСлужащие":
                return 'SERVANT';
            case "Заместители":
                return 'ASSISTANT';
            default:
                return null;
        }
    }

    private function getRightPeriods($year, $arrCDays)
    {
        ksort($arrCDays);

        /* на случай, если у работника отображается раб. период за текущий год, но отсутствует за предыдущий */
        if ($year < key($arrCDays)) {
            $arrCDays[$year] = current($arrCDays);
            $arrCDays[$year]['from'] = (new \DateTime(current($arrCDays)['from']))->modify('-1 year')->format('Y-m-d');
            $arrCDays[$year]['to'] = (new \DateTime(current($arrCDays)['to']))->modify('-1 year')->format('Y-m-d');
        }

        if (!isset($arrCDays[ $year ]) && isset($arrCDays[ $year - 1 ])) {
            ['from' => $tmpFrom, 'to' => $tmpTo] = $arrCDays[ $year - 1 ];
            $tmpFrom = date('Y-m-d', strtotime($tmpTo) + 86400);
            $tmpTo = explode('-', $tmpTo);
            $tmpTo[0]++;
            $tmpTo = implode('-', $tmpTo);
            $arrCDays[ $year ] = $arrCDays[$year-1];
            $arrCDays[ $year ]['from'] = $tmpFrom;
            $arrCDays[ $year ]['to'] = $tmpTo;
            unset($tmpFrom, $tmpTo);
        }

        $workPeriods = [];
        $fromYear = explode('-', $arrCDays[ $year ]['from'])[0];
        $toYear = explode('-', $arrCDays[ $year ]['to'])[0];

        if ($fromYear == $year && $toYear == $year) {
            $curEach = $year;
        } elseif ($toYear == $year && !isset($arrCDays[$year + 1])) {
            $curEach = $year;
            $prevEach = $year - 1;
        } elseif ($toYear == $year && isset($arrCDays[$year + 1])) {
            $curEach = $year + 1;
            $prevEach = $year;
        }

        $val = $arrCDays[$curEach];
        $from = explode('-', $val['from']);
        $to = explode('-', $val['to']);
        $fromYear = $from[0];
        $toYear = $to[0];
        $fromCount = $val['count'];
        $toCount = $val['count'];

        if (!($fromYear == $year && $toYear == $year)) {
            $from[0] = $year - 1;
        }
        $nextYear = (isset($arrCDays[$curEach + 1])) ? true : false;
        $from = strtotime($val['from']);
        $to = strtotime($val['to']);

        if (isset($prevEach)) {
            $valprev = $arrCDays[$prevEach];
            $fromprev = explode('-', $valprev['from']);
            $toprev = explode('-', $valprev['to']);
            $fromYearprev = $fromprev[0];
            $toYearprev = $toprev[0];
            $fromCountprev = $valprev['count'];
            $toCountprev = $valprev['count'];

            if (!($fromYearprev == $year && $toYearprev == $year)) {
                $fromprev[0] = $year - 1;
            }
            $nextYearprev = (isset($arrCDays[$prevEach + 1])) ? true : false;
            $fromprev = strtotime($valprev['from']);
            $toprev = strtotime($valprev['to']);
            if ($toYearprev == $year) {
                array_push($workPeriods, date('d.m.Y', strtotime($valprev['from'])) . '-' . date('d.m.Y', strtotime($valprev['to'])));
            }
        }
        array_push($workPeriods, date('d.m.Y', strtotime($val['from'])) . '-' . date('d.m.Y', strtotime($val['to'])));

        return [
            'workperiods'   => $workPeriods,
            'from'          => $from,
            'to'            => $to,
            'fromcount'     => $fromCount,
            'tocount'       => $toCount,
            'nextyear'      => $nextYear,
            'fromprev'      => $fromprev,
            'toprev'        => $toprev,
            'fromcountprev' => $fromCountprev,
            'tocountprev'   => $toCountprev,
            'nextyearprev'  => $nextYearprev,
            'count_by_type' => $arrCDays[ $year ]['count_by_type'],
        ];
    }

    public function getHumanTime($t)
    {
        $w = floor($t / 86400 / 7);
        $d = floor($t % (86400 * 7) / 86400);
        $h = floor($t % 86400 / 3600);
        $m = floor($t % 3600 / 60);

        $res = '';
        if ($w) {
            $res .= $w . ' ' . GetMessage("BITRIX_PLANNER_NED");
        }
        if ($d) {
            $res .= $d . ' ' . GetMessage("BITRIX_PLANNER_DN1");
        }
        if ($h) {
            $res .= $h . ' ' . GetMessage("BITRIX_PLANNER_C");
        }
        if ($m) {
            $res .= $m . ' ' . GetMessage("BITRIX_PLANNER_MIN");
        }
        return trim($res);
    }

    public function ImNotify($from, $to, $subject, $body, $emailto = '')
    {
        if ($from != $to) {
            $arExcludeMail = ['Ekaterina.Petrova@tularegion.ru'];
            if ($emailto != '' && !in_array($emailto, $arExcludeMail)) {
                CEvent::Send("ADV_PLANNER_EVENT", SITE_ID, ['DESCRIPTION' => $body, 'EMAIL_TO' => $emailto]);
            }
            return CIMMessenger::Add(array(
                'TITLE'             => $subject,
                'MESSAGE'           => $body,
                'MESSAGE_OUT'       => '#SKIP#',
                'EMAIL_TEMPLATE'    => 'some',
                'TO_USER_ID'        => $to,
                'FROM_USER_ID'      => $from,
                'MESSAGE_TYPE'      => 'S', # P - private chat, G - group chat, S - notification
                'NOTIFY_MODULE'     => 'intranet',
                'NOTIFY_TYPE'       => 2,  # 1 - confirm, 2 - notify single from, 4 - notify single
            ));
        }
    }

    public function getPeriods($arUsers, $arUsersCadrs)
    {
        $users = new CUsers();
        $arConfirmed = [];
        $arPeriod = [];

        $arFilter = array(
            'IBLOCK_ID' => $this->iBlockVacation,
            'PROPERTY_USER' => array_keys($arUsers),
            'CODE' => ['VACATION', 'VACATION_ADD']
        );

        $rs = CIBlockElement::GetList(
            $by = array('ACTIVE_FROM' => 'ASC'),
            $arFilter,
            false,
            false,
            array('*', 'PROPERTY_USER', 'PROPERTY_UF_WHO_APPROVE', 'PROPERTY_ABSENCE_TYPE')
        );
        while ($f = $rs->GetNext()) {
            foreach (json_decode($f['~PROPERTY_UF_WHO_APPROVE_VALUE']) as $user) {
                if (!in_array($user, $arConfirmed)) {
                    array_push($arConfirmed, $user);
                }
            }

            if (date('Y', strtotime($f["ACTIVE_TO"])) != $this->year) {
                continue;
            }

            $userID = intval($f['PROPERTY_USER_VALUE']);
            if (!$userID || !$arUsers[$userID]) {
                continue;
            }

            if (!$f['ACTIVE_FROM'] || !$f['ACTIVE_TO']) {
                continue;
            }

            $from = MakeTimeStamp($f['ACTIVE_FROM']);
            $to = MakeTimeStamp($f['ACTIVE_TO']);

            if ($to < $from) {
                continue;
            }

            if ($_REQUEST['action'] == 'delete' && $_REQUEST['id'] == $f['ID']) {
                continue;
            }

            $to = (date('His', $to) == 0) ? $to + 86400 : $to;
            $period = $to - $from;

            for ($i = $from; $i < $to; $i += 86400) {
                if (in_array($i, $this->getHolidays()['holydays'])) {
                    $period -= 86400;
                }
            }

            $f['PERIOD'] = $period;
            $f['HUMAN_TIME'] = $this->getHumanTime($period);
            $f['WHO_APPROVE'] = (isset($f['PROPERTY_UF_WHO_APPROVE_VALUE'])) ? json_decode($f['~PROPERTY_UF_WHO_APPROVE_VALUE']) : [];

            $arPeriod[] = $f;

            if (date('Y', strtotime($f['ACTIVE_TO'])) == $this->year) {
                $arUsers[$userID]['day_left'] = $arUsers[$userID]['day_left'] - ($period / 86400);
            }

            switch ($f) {
                case $f['ACTIVE'] == 'N':
                    $status = 'n_class';
                    break;
                case in_array($f["MODIFIED_BY"], $arUsersCadrs):
                    $status = 'cadrs_class';
                    break;
                case in_array((int)$f["MODIFIED_BY"], [33]):
                    $status = 'cadrs_class';
                    break;
                default:
                    $status = 'head_class';
            }

            $arUsers[ $userID ]['VACATION'][$from] = [
                'ID_RECORD' => $f['ID'],
                'STATUS'    => $status,
                'PERIOD'    => $period = $to - $from,
                'FROM'      => $f['ACTIVE_FROM'],
                'FROM_TS'   => $from,
                'TO'        => $f['ACTIVE_TO'],
                'TO_TS'     => $to,
                'PREVIEW_TEXT' => $f['PREVIEW_TEXT'],
            ];
            $arUsers[ $userID ]['DETAIL'] = ($f['DETAIL_TEXT']) ? $f['DETAIL_TEXT'] : '';
        }
        $arConfirmed = $users->getListConfirmed($arConfirmed);

        return [
            'users'     => $arUsers,
            'confirmed' => $arConfirmed,
            'periods'   => $arPeriod
        ];
    }

    public function getSortPeriods($arUsers, $arPeriods)
    {
        foreach ($arPeriods as $arPeriod) {
            $arUsers[$arPeriod['PROPERTY_USER_VALUE']]['PERIODS'][] = $arPeriod;
        }

        $arNewPeriods = [];
        foreach ($arUsers as $arUser) {
            foreach ($arUser['PERIODS'] as $arPeriod) {
                $arNewPeriods[] = $arPeriod;
            }
        }

        return $arNewPeriods;
    }

    public function getVacations1C($sEmployeeID)
    {
        $rRespone = Source1C::GetArray($this->getConnect1C(), 'VacationSchedule', ['EmployeeID' => $sEmployeeID]);
        $arVacations = $rRespone['Data']['VacationSchedule']['VacationScheduleRecord'];
        return $arVacations;
    }

    public function updateVacations1C($sDateFromOld, $arOldVacations = [], $arNewVacations = [])
    {
        /* Пример массива $arOldVacations и $arNewVacations
        $arVacations = [
            [
                'EmployeeID' => 'S-1-5-21-3257783013-1731373831-2674042523-5753',
                'DateStart' => '2019-06-10',
                'DaysCount' => 7,
                'EmployeeName' => 'Иванов Иван Иванович'
            ],
            [
                'EmployeeID' => 'S-1-5-21-3257783013-1731373831-2674042523-5753',
                'DateStart' => '2019-09-01',
                'DaysCount' => 7,
                'EmployeeName' => 'Иванов Иван Иванович'
            ],
        ];
        */

        $arVacations = [];
        $iYear = date('Y', strtotime($sDateFromOld));

        foreach ($arOldVacations as $iKey => $arValue) {
            if (strtotime($arValue['DateStart']) != strtotime($sDateFromOld)) {
                array_push($arVacations, $arValue);
            }
        }

        foreach ($arNewVacations as $arValue) {
            $arVacations[] = $arValue;
        }

        $rRespone = Source1C::GetArray($this->getConnect1C(), 'UpdateVacationSchedule', [
            'VacationScheduleUpdate' => [
                'Year' => $iYear,
                'VacationSchedule' => $arVacations
            ]
        ], false);

        if ($rRespone['result'] && !empty($arVacations)) {
            HolidayList\CEditVacations::setNewVacations($iYear, $arVacations);
        }

        return $rRespone['result'];
    }

    public function getCitVacations($arUsers = [])
    {
        $arDepartmentsCit = [
            'Группа управления каталогом ИТ-услуг и научной деятельности' => ['Заместитель начальника отдела - руководитель группы' => 5],
            'Отдел "Диспетчерский центр"' => ['Начальник отдела' => 5],
            'Отдел "Удостоверяющий центр"' => ['Начальник отдела' => 5],
            'Отдел аналитики, статистики и развития проекта "Ситуационный центр"' => ['Начальник отдела' => 5],
            'Отдел аттестации информационных систем' => ['Начальник отдела' => 5],
            'Отдел бухгалтерского учета и материально-технического обеспечения' => ['Главный бухгалтер - начальник отдела' => 5],
            'Отдел информационного сопровождения' => [
                'Начальник отдела' => 5,
                'Ведущий специалист' => 3,
                'Консультант' => 3,
            ],
            'Отдел инфраструктурных сервисов' => ['Начальник отдела' => 5],
            'Отдел кадрового и документационного обеспечения' => ['Начальник отдела' => 5],
            'Отдел поддержки 1С' => [
                'Начальник отдела' => 5,
                'Заместитель начальника отдела' => 5
            ],
            'Отдел поддержки пользователей' => ['Заместитель начальника отдела' => 5],
            'Отдел проектного управления' => ['Начальник отдела' => 5],
            'Отдел продаж' => ['Начальник отдела' => 5],
            'Отдел развития государственных и муниципальных услуг' => ['Начальник отдела' => 5],
            'Отдел развития проекта ГИС' => ['Начальник отдела' => 5],
            'Отдел развития РИСЗ ТО' => ['Заместитель начальника отдела' => 5],
            'Отдел разработки' => ['Начальник отдела' => 5],
            'Отдел сопровождения мероприятий и оперативной полиграфии' => [
                'Начальник отдела' => 5,
                'Консультант' => 3,
                'Ведущий специалист' => 3
            ],
            'Отдел телекоммуникационных сервисов' => ['Начальник отдела' => 5],
            'Отдел технической поддержки' => ['Начальник отдела' => 5],
            'Отдел технической поддержки и сопровождения РИСЗ ТО' => ['Начальник отдела' => 5],
            'Отдел финансирования и закупочной деятельности' => ['Начальник отдела' => 5],
            'Отдел цифровой трансформации' => ['Начальник отдела' => 5],
            'Управление дистанционного обслуживания (единый Контактный центр)' => ['Начальник управления' => 5],
            'Управление информационной безопасности' => ['Начальник управления' => 5],
            'Управление информационных систем' => ['Заместитель начальника управления - начальник отдела развития РИСЗ ТО' => 5],
            'Управление сервиса и эксплуатации' => ['Заместитель начальника управления - начальник отдела поддержки пользователей' => 5],
            'Финансово-экономический отдел' => ['Начальник отдела' => 5],
            'Юридический отдел' => ['Начальник отдела' => 5]
        ];

        $arCitPositions = [
            'Заместитель директора - начальник управления по обеспечению деятельности и развитию',
            'Заместитель директора - начальник управления сервиса и эксплуатации',
            'Заместитель директора по вопросам цифровой трансформации',
            'Заместитель директора - начальник управления информационных систем',
            'Директор'
        ];

        $arUsers30days = [157, 50, 3607, 4013];
        $arUsers31days = [5979, 6244];
        $arUsers33days = [66];

        $arDepartmentsCitIDs = [];
        $arFilter = Array('IBLOCK_ID' => 5, 'NAME' => array_keys($arDepartmentsCit));
        $obSections = CIBlockSection::GetList([], $arFilter, true, ['ID', 'NAME']);
        while($arSection = $obSections->GetNext())
        {
            $arDepartmentsCitIDs[$arSection['ID']] = $arDepartmentsCit[$arSection['~NAME']];
        }

        foreach ($arUsers as &$arUser) {
            $iDaysCit = 28;
            $sPosition = $arUser['WORK_POSITION'];
            if (in_array($arUser['ID'], $arUsers30days)) {
                $iDaysCit = 30;
            } else if (in_array($arUser['ID'], $arUsers31days)) {
                $iDaysCit = 31;
            } else if (in_array($sPosition, $arCitPositions) || in_array($arUser['ID'], $arUsers33days)) {
                $iDaysCit = 33;
            } else {
                foreach ($arUser["UF_DEPARTMENT"] as $iDep) {
                    if(!empty($arDepartmentsCitIDs[$iDep][$sPosition])) {
                        $iDaysCit += $arDepartmentsCitIDs[$iDep][$sPosition];
                        break;
                    }
                }
            }
            $arUser['CIT_DAYS'] = $iDaysCit;
        }

        return $arUsers;
    }

    public function getCrossVacations($iUserID, $sDateFrom, $sDateTo)
    {
        $arResult = [
            'CROSS' => 'N',
            'TEXT' => ''
        ];

        $iMyFuncUnit = 0;
        $arFuncUnit = [];

        $obFuncUnit = CIBlockElement::GetList(
            [],
            ["IBLOCK_CODE"=>'kpi_data_users', "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y"],
            false,
            [],
            ['PROPERTY_ATT_WORK_POSITION', 'CODE']
        );

        while($arUser = $obFuncUnit->GetNext()) {
            $iFuncUnit = $arUser['PROPERTY_ATT_WORK_POSITION_VALUE'];

            if ($arUser['CODE'] == $iUserID) {
                $iMyFuncUnit = $iFuncUnit;
            } else {
                $arFuncUnit[$iFuncUnit][] = $arUser['CODE'];
            }
        }

        if ($iMyFuncUnit > 0) {
            $obVacations = CIBlockElement::GetList(
                [],
                [
                    "IBLOCK_ID"=> COption::GetOptionInt('bitrix.planner', 'VACATION_RECORDS'),
                    'PROPERTY_USER' => $arFuncUnit,
                    [
                        'LOGIC' => 'OR',
                        '><DATE_ACTIVE_FROM' => [$sDateFrom, $sDateTo],
                        '><DATE_ACTIVE_TO' => [$sDateFrom, $sDateTo],
                    ]
                ],
                false,
                [],
                ['ID', 'ACTIVE_FROM', 'ACTIVE_TO', 'PROPERTY_USER']
            );

            $arCrossUsers = [];

            while($arVacation = $obVacations->getNext()) {
                if ($iUserID != $arVacation['PROPERTY_USER_VALUE']) {
                    $arCrossUsers[$arVacation['PROPERTY_USER_VALUE']] = '';
                }
            }

            if ($arCrossUsers) {
                $orm = UserTable::getList([
                    'select'    => ['ID', 'NAME', 'SECOND_NAME', 'LAST_NAME'],
                    'filter'    => ['ACTIVE' => 'Y', 'ID' => array_keys($arCrossUsers)]
                ]);
                while ($arUser = $orm->fetch()) {
                    $arCrossUsers[$arUser['ID']] = "{$arUser['LAST_NAME']} {$arUser['NAME']} {$arUser['SECOND_NAME']}";
                }

                $arResult['TEXT'] = 'Пересечение отпусков с пользователями: ' . implode(', ', $arCrossUsers) . '.';
                $arResult['CROSS'] = 'Y';
            }
        }

        return $arResult;
    }
}
