<?php

use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;

function vardump($vardump)
{
    echo "<pre>";
    var_dump($vardump);
    echo "</pre>";
}

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
global $APPLICATION, $USER;
Loader::includeModule('intranet');
Loader::includeModule('bitrix.planner');
HolidayList\CBeforeInit::start();
$APPLICATION->SetTitle(GetMessage("BITRIX_PLANNER_PLANIROVANIE_OTPUSKO"));

$arCitDepartments = CIntranetUtils::GetDeparmentsTree(57, true);
$arDepartmentsJudge = CIntranetUtils::GetIBlockSectionChildren(2229);
$arCitDepartments[] = 57;
/*
 * Исключить ЕКЦ из утверждения Зениным
 */
//$arEKCDepartments = CIntranetUtils::GetDeparmentsTree(105, true);
//$arEKCDepartments[] = 105;
//$arCitDepartments = array_diff($arCitDepartments, $arEKCDepartments);

$arCitUsers = [];
$orm = UserTable::getList([
    'select'    => ['ID', 'UF_DEPARTMENT'],
    'filter'    => ['ACTIVE' => 'Y']
]);
while ($arUser = $orm->fetch()) {
    $arDiff = array_intersect($arUser['UF_DEPARTMENT'], $arCitDepartments);
    if (!empty($arDiff)) {
        $arCitUsers[] = $arUser['ID'];
    }

    $arDiffJudge = array_intersect($arUser['UF_DEPARTMENT'], $arDepartmentsJudge);
    if (!empty($arDiffJudge)) {
        $arJudgeUsers[] = $arUser['ID'];
    }
}

$vacations = new HolidayList\CVacations();
$users = HolidayList\CUsers::getInstance();
$structure = HolidayList\CStructure::getInstance($arParams);

$arResult = [];
$arResult['USER_ID'] = $users->getID();
$arResult['MY_WORKERS'] = $users->getMyWorkers();
$arResult['THIS_HEADS'] = $users->getMyHeads();
$arResult['YEAR'] = $vacations->year;
$arResult['USERS_CADRS'] = $users->getUsersCadrs();
$arResult['ERROR'] = '';
$arResult['INFO'] = [];
$arResult['IBLOCK_ID'] = $structure->iBlockStructure;
$arResult['DELEGATIONS'] = $users->getDelegations();
$arResult['USERCADRS'] = (in_array($arResult['USER_ID'], $arResult['USERS_CADRS'])) ? 1 : 0;
$arResult['EXPORT'] = false;
$arResult['PROBLEM_USERS'] = [];
$arResult['CITTO'] = [
    'DEPS'      => $arCitDepartments,
    'USERS'     => $arCitUsers,
    'APPROVE'   => 33, // Зенин Игорь
];

[
    'holydays' => $arResult['HOLIDAYS'],
    'weekends' => $arResult['WEEKENDS'],
    'shortdays' => $arResult['SHORTDAYS']
] = $vacations->getHolidays();

$arResult['DEPARTMENT_IDS'] = $structure->getDepartmentIDS($arResult['USER_ID']);
$arResult['DEPARTMENT_LIST'] = $structure->getStructure($arResult['USER_ID'], $arResult['DEPARTMENT_IDS']);
$arResult['DEPARTMENT_ID'] = $structure->getDepartmentID($arResult['USER_ID'], $arResult['DEPARTMENT_LIST']);

[
    'ADMIN' => $arResult['ADMIN'],
    'TO_CADRS' => $arResult['TO_CADRS'],
    'SECRETARY' => $arResult['SECRETARY']
] = $users->getRoles($arResult['DEPARTMENT_LIST']);

[
    'TYPES' => $arResult['TYPES'],
    'ABSENCE' => $arResult['ABSENCE_TYPES']
] = $vacations->getAbsences($vacations->iBlockVacation);

$arResult['RECURSIVE'] = $structure->getRecursive($arResult['ADMIN']);
$arResult['BASE_URL'] = $vacations->getBaseUrl($arResult['DEPARTMENT_ID'], $arResult['RECURSIVE']);

[
    'users' => $arResult['USERS'],
    'confirmed' => $arResult['LIST_CONFIRMED'],
    'periods' => $arResult['PERIOD']
] = $users->getUsers(
    $arResult['MY_WORKERS'],
    $arResult['ADMIN'],
    $arResult['DEPARTMENT_LIST'],
    $arResult['DEPARTMENT_ID'],
    $arResult['RECURSIVE']
);

$arResult['FIO_APPROVE'] = $users->getFioApprove($arResult['USERS']);
$tmpApprove = CUser::GetList($by = "NAME", $order = "desc", ['ID' => $arResult['CITTO']['APPROVE']]);
while ($f = $tmpApprove->getNext()) {
    $arResult['FIO_APPROVE'][ $f['ID'] ] = "{$f['LAST_NAME']} {$f['NAME']} {$f['SECOND_NAME']}";
}
$arThisHeads = $users->getThisHeads($arResult['USERS'][$users->getSelUserID()]);

if (!empty($_REQUEST['action'])) {
    $actions = new HolidayList\CActions(
        $arParams,
        $arResult['BASE_URL'],
        $users->getRoles($arResult['DEPARTMENT_LIST']),
        $arResult['USERS'],
        $arResult['USERCADRS']
    );
    $actions->run();
}

$bIsMinistr = ($arResult['USERS'][$users->getSelUserID()]['ROLE'] == 'ASSISTANT');
$bIsJudge = (in_array($users->getSelUserID(), $arJudgeUsers));

if ($_REQUEST['action'] == 'add' || $_REQUEST['action'] == 'edit') {
    $iPlannedDays = $arResult['USERS'][$users->getSelUserID()]["total_days"] - $arResult['USERS'][$users->getSelUserID()]['day_left'];
    $d_left = $arResult['USERS'][$users->getSelUserID()]['day_left'];

    if ($_REQUEST['action'] == 'edit') {
        $getRecord = CIBlockElement::GetList(
            array(),
            ['ID' => $_REQUEST['id']],
            false,
            false,
            ['IBLOCK_ID', 'ID', 'ACTIVE_FROM', 'ACTIVE_TO']
        )->getNext();
        for ($i = strtotime($getRecord['ACTIVE_FROM']); $i <= strtotime($getRecord['ACTIVE_TO']); $i += 86400) {
            if (!in_array($i, $arResult['HOLIDAYS'])) {
                $d_left++;
            }
        }
    }

    // Проверяем, отгулял ли сотрудник отведённые для него дни
    $d_left_plan = 0; // То самое количество дней, которое необходимо отгулять работнику в планируемом году
    $firstPeriod = 0; // Начало рабочего периода на планируемый год

    $daysUsed = $arResult['USERS'][$users->getSelUserID()]["DAYSLEFT"]['left'];
    $firstPeriod = $arResult['USERS'][$users->getSelUserID()]["DAYSLEFT"]['to'] - 86400;

    // Если сотрудник не отгулял какое-то количество дней за предыдущий период, то выводим уведомление
    $totalDays = $arResult['USERS'][$users->getSelUserID()]["total_days"];
    $d_left_plan = $arResult['USERS'][$users->getSelUserID()]["total_days"] - $daysUsed;
    if ($arResult['YEAR'] != date('Y', $firstPeriod)) {
        $d_left_plan = $totalDays;
    }
    $sDayVacation = "";

    $iVacationTo = MakeTimeStamp($_REQUEST['day_to']);
    $iVacationFrom = MakeTimeStamp($_REQUEST['day_from']);
    $period = 1 + ceil(($iVacationTo - $iVacationFrom) / 86400);
    $daysForLeft = 0; // Количество запланированных дней до конца р.п.

    for ($i = $iVacationFrom; $i <= $iVacationTo; $i += 86400) {
        if (in_array($i, $arResult['HOLIDAYS'])) {
            $period--;
        }
        if (!in_array($i, $arResult['HOLIDAYS']) && $i < $firstPeriod) {
            $daysForLeft++;
        }
    }

    /*
    if (!$bIsMinistr && isset($arResult['USERS'][$users->getSelUserID()]['days_info'])) {
        foreach ($arResult['USERS'][$users->getSelUserID()]['days_info'] as $infoRow) {
            if ($iVacationFrom >= $infoRow['from_ts'] && $iVacationTo <= $infoRow['to_ts']) {
                if ($infoRow['free'] < $period) {
                    $d_left_plan = 0;
                    $d_left = 0;
                    $daysForLeft = 0;
                }
            }
        }
    }
    */

    $minDays = ((28 - $daysUsed) > 0 ? 28 - $daysUsed : 0);
    $maxDays = $d_left_plan;

    if (!$arResult['TYPES'][$type = $_REQUEST['event_type']]) {
        $type = 'VACATION';
    }

    $name = $arResult['TYPES'][ $type ];

    if ($iVacationTo >= $iVacationFrom && $d_left >= $period) {
        $match_vacation = false;
        $less30info = '';

        $holidaysPeriod = 0;
        $countAllDays = 0;
        foreach ($arResult['USERS'][$users->getSelUserID()]['VACATION'] as $key => $value) {
            for ($i = $key; $i < $key + $value['PERIOD']; $i += 86400) {
                if ($i <= $firstPeriod && in_array($i, $arResult['HOLIDAYS'])) {
                    $holidaysPeriod++;
                }
                if ($i <= $firstPeriod) {
                    $countAllDays++;
                }
            }

            $curFrom = $key;
            $curTo = $key + $value['PERIOD'] - 86400;
            if (
                $iVacationFrom >= $curFrom && $iVacationFrom <= $curTo ||
                $iVacationTo >= $curFrom && $iVacationTo <= $curTo
            ) {
                $arResult['ERROR'] = GetMessage('CANT_VACATION');
                $match_vacation = true;
            }
            // Проверяем период между отпусками (Должен составлять не менее 30 дней)
            if (($iVacationFrom - $curTo) / (3600 * 24) - 1 >= 0 && ($iVacationFrom - $curTo) / (3600 * 24) - 1 <= 29) {
                $less30info = GetMessage('LESS_30_DAYS');
            }
            if (($curFrom - $iVacationTo) / (3600 * 24) - 1 >= 0 && ($curFrom - $iVacationTo) / (3600 * 24) - 1 <= 29) {
                $less30info = GetMessage('LESS_30_DAYS');
            }
        }

        if (
            $arResult['USERS'][$users->getSelUserID()]['ROLE'] != 'SERVANT' &&
            !$arResult['USERCADRS']
        ) {
            if (date('Y', $firstPeriod) == $arResult['YEAR'] && $d_left_plan >= 0) {
                $leftAll = $minDays - ($countAllDays - $holidaysPeriod) - $daysForLeft; // останется для обязательного распланирования
                $maxLeftAll = $maxDays - ($countAllDays - $holidaysPeriod) - $daysForLeft;
                $firstPeriod = date('d.m.Y', $firstPeriod + 86400);

                if ($leftAll < 0) {
                    if (($minDays - $maxDays) <= $leftAll) {
                        $match_vacation = false;
                    } else {
                        $sumLeft = $period + $maxLeftAll;
                        $arResult['ERROR'] = GetMessage('MORE_WORKPERIOD', ['#sumLeft#' => $sumLeft, '#firstPeriod#' => $firstPeriod]);
                        $match_vacation = true;
                    }
                } else {
                    if ($leftAll >= 0 && ($arResult['USERS'][$users->getSelUserID()]['day_left'] - $period) >= $leftAll) {
                        $match_vacation = false;
                        if ($leftAll > 0) {
                            array_push($arResult['INFO'], GetMessage("NEED_MIN_PERIOD", ['#firstPeriod#' => $firstPeriod, '#leftAll#' => $leftAll, '#maxLeftAll#' => $maxLeftAll]));
                            $sDayVacation = GetMessage("NEED_MIN_PERIOD", ['#firstPeriod#' => $firstPeriod, '#leftAll#' => $leftAll, '#maxLeftAll#' => $maxLeftAll]);
                        }
                    } else {
                        $match_vacation = true;
                        $leftAll = $minDays - ($countAllDays - $holidaysPeriod);
                        $arResult['ERROR'] = GetMessage("FIRST_MIN_DAYS", ['#firstPeriod#' => $firstPeriod, '#leftAll#' => $leftAll]);
                    }
                }
            }
        }

        if (!empty($less30info)) {
            array_push($arResult['INFO'], $less30info);
        }

        // Если год в планируемом периоде не совпадает с текущим
        if (date('Y', $iVacationTo) != $arResult['YEAR'] || date('Y', $iVacationFrom) != $arResult['YEAR']) {
            $arResult['ERROR'] = GetMessage('ONLY_CURR_YEAR', ['#year#' => $arResult['YEAR']]);
            $match_vacation = true;
        }

        // Если окончание отпуска выходит за пределы рабочего периода
        $iDaysFirstPeriod = $arResult['USERS'][$users->getSelUserID()]['WORKPERIODS']['PERIODS']['p1'];
        $iAdditionDays = $arResult['USERS'][$users->getSelUserID()]['count_by_type']['Other']['avail'];
        $iAdditionDays = 7;

        if (
            $iPlannedDays < 26 &&
            date('z', $iVacationFrom) - 1 < $iDaysFirstPeriod &&
            date('z', $iVacationTo) - 1 >= ($iDaysFirstPeriod + $iAdditionDays)
        ) {
            $arResult['ERROR'] = 'Окончание отпуска выходит за пределы рабочего периода';
            $match_vacation = true;
        }

        if (!$match_vacation && (!$arResult['USERCADRS'] || $arResult['USERS'][$users->getSelUserID()]['ROLE'] != 'ASSISTANT')) {
            // Первый отпуск должен составлять 14 дней
            // if ((count($arResult['USERS'][$users->getSelUserID()]["VACATION"]) == 0) && $period != 14) {
            //     $arResult['ERROR'] = GetMessage('FIRST_VACATION_14');
            // } else
            if (count($arResult['USERS'][$users->getSelUserID()]["VACATION"]) > 0) {
                $used = 0;
                $bFind14 = false;
                foreach ($arResult['USERS'][$users->getSelUserID()]["VACATION"] as $val) {
                    $days = ($val["PERIOD"] / 86400);
                    $used += $days;
                    if ($days == 14) {
                        $bFind14 = true;
                    }
                }

                if ($period >= 14) {
                    $bFind14 = true;
                }

                foreach ($arResult['USERS'][$users->getSelUserID()]["VACATION"] as $val) {
                    if ($val["PERIOD"] / 86400 >= 14) {
                        $arResult['ERROR'] = "";
                        $match_vacation = false;
                        break;
                    } elseif ($val["PERIOD"] / 86400 < 14 && $used >= 14 && !$bFind14) {
                        $arResult['ERROR'] = GetMessage('ANY_VACATION_14');
                        $match_vacation = true;
                    }
                }
                if (!$match_vacation) {
                    if ($period > 14) {
                        array_push($arResult['INFO'], GetMessage('PERIOD_MORE_14'));
                        // $arResult['ERROR'] = GetMessage('PERIOD_MORE_14');
                    }
                    if ($period % 7 != 0 && $d_left - $period != 0) {
                        array_push($arResult['INFO'], GetMessage('PERIOD_DIVIDE_7'));
                        // $arResult['ERROR'] = GetMessage('PERIOD_DIVIDE_7');
                    }
                    if ($d_left - $period < 7 && $d_left - $period != 0) {
                        array_push($arResult['INFO'], GetMessage('REST_LESS_7'));
                        // $arResult['ERROR'] = GetMessage('REST_LESS_7');
                    }
                    if ($period < 7) {
                        array_push($arResult['INFO'], GetMessage('PERIOD_LESS_7'));
                        // $arResult['ERROR'] = GetMessage('PERIOD_LESS_7');
                    }
                    $add = true;
                }
            } else {
                $add = true;
            }
        }

        if (!$match_vacation && ($arResult['USERCADRS'] || $arResult['USERS'][$users->getSelUserID()]['ROLE'] == 'ASSISTANT')) {
            $add = true;
            $arResult['ERROR'] = "";
        }

        if (!$match_vacation && $add) {
            $el = new CIBlockElement();
            if ($_REQUEST['action'] == 'edit') {
                $recId = $el->Update(
                    $_REQUEST['id'],
                    array(
                        'NAME' => $name,
                        'CODE' => $type,
                        'ACTIVE_FROM' => $_REQUEST['day_from'],
                        'ACTIVE_TO' => $_REQUEST['day_to'],
                        'PREVIEW_TEXT' => $_REQUEST['PREVIEW_TEXT'],
                        'PROPERTY_VALUES' => array(
                            'USER' => $users->getSelUserID(),
                            'ABSENCE_TYPE' => $arResult['ABSENCE_TYPES'][$type]
                        )
                    )
                );
                if ($recId) {
                    $recId = $_REQUEST['id'];
                }
                LocalRedirect($APPLICATION->GetCurPage() . $arResult['BASE_URL']);
            } elseif ($_REQUEST['action'] == 'add') {
                $sPreview = $_REQUEST['PREVIEW_TEXT'];

                $sCrossVacation = $vacations->getCrossVacations($users->getSelUserID(), $_REQUEST['day_from'], $_REQUEST['day_to']);

                if ($sCrossVacation['CROSS'] == 'Y') {
                    $sPreview = $sCrossVacation['TEXT'];
                }

                $recId = $el->Add(
                    array(
                        'IBLOCK_ID' => $vacations->iBlockVacation,
                        'NAME' => $name,
                        'CODE' => $type,
                        'ACTIVE' => 'N',
                        'ACTIVE_FROM' => $_REQUEST['day_from'],
                        'ACTIVE_TO' => $_REQUEST['day_to'],
                        'DETAIL_TEXT' => $sDayVacation,
                        'PREVIEW_TEXT' => $sPreview,
                        'PROPERTY_VALUES' => array(
                            'USER' => $users->getSelUserID(),
                            'ABSENCE_TYPE' => $arResult['ABSENCE_TYPES'][$type]
                        )
                    )
                );
                if (isset($sDayVacation)) {
                    $arResult['USERS'][$users->getSelUserID()]['DETAIL'] = $sDayVacation;
                }
            }

            if ($recId) {
                $curPeriod = CIBlockElement::GetList(array(), array('IBLOCK_ID' => $vacations->iBlockVacation, "ID" => $recId), false, false, ['*', 'PROPERTY_USER', 'PROPERTY_ABSENCE_TYPE'])->Fetch();
                $curPeriod['PERIOD'] = strtotime($_REQUEST['day_to']) - strtotime($_REQUEST['day_from']) + 86400;
                $period = $curPeriod['PERIOD'];
                for ($i = strtotime($_REQUEST['day_from']); $i <= strtotime($_REQUEST['day_to']); $i += 86400) {
                    if (in_array($i, $arResult['HOLIDAYS'])) {
                        $curPeriod['PERIOD'] -= 86400;
                    }
                }
                $arResult['PERIOD'][] = $curPeriod;
                $arResult['USERS'][$users->getSelUserID()]['VACATION'][strtotime($_REQUEST['day_from'])] = [
                    'ID_RECORD' => $recId,
                    'STATUS' => 'n_class',
                    'PERIOD' => $period
                ];
                $arResult['USERS'][$users->getSelUserID()]['day_left'] = $arResult['USERS'][$users->getSelUserID()]['day_left'] - $curPeriod['PERIOD'] / 86400;
                $comment = $_REQUEST['PREVIEW_TEXT'] ? ' [' . $_REQUEST['PREVIEW_TEXT'] . ']' : '';

                if (!empty($arThisHeads)) {
                    foreach ($arThisHeads as $user_id => $email) {
                        $vacations->ImNotify(
                            $users->getSelUserID(),
                            $user_id,
                            GetMessage("BITRIX_PLANNER_DOBAVLENO") . $name,
                            GetMessage("BITRIX_PLANNER_DOBAVLNO") . $name . ' [' . $arResult['USERS'][$arResult['USER_ID']]['NAME'] . ' ' . $arResult['USERS'][$arResult['USER_ID']]['LAST_NAME'] . '] ' . $_REQUEST['day_from'] . ' - ' . $_REQUEST['day_to'] . $comment,
                            ''//$email // Отключил письмо руководителю о новом событии
                        );
                    }
                }
            } else {
                $arResult['ERROR'] = $el->LAST_ERROR;
            }
        }
    } else {
        $arResult['ERROR'] = GetMessage('CANT_VACATION');
    }
} elseif ($_REQUEST['action'] == 'approve' || $_REQUEST['action'] == 'adminapprove') {
    $reqId = json_decode($_REQUEST['id']);
    $rs = CIBlockElement::GetList(
        $by = array('ACTIVE_FROM' => 'ASC'),
        $arFilter = array(
            'IBLOCK_ID' => $vacations->iBlockVacation,
            'ID' => $reqId
        ),
        false,
        false,
        array(
            '*',
            'PROPERTY_USER',
            'PROPERTY_UF_WHO_APPROVE',
            'PROPERTY_ABSENCE_TYPE'
        )
    );
    if (isset($rs) && !empty($reqId)) {
        while ($f = $rs->Fetch()) {
            $arHeads = explode('|', $arResult['USERS'][ $f['PROPERTY_USER_VALUE'] ]['UF_THIS_HEADS']);
            $boolCitUser = in_array($f['PROPERTY_USER_VALUE'], $arResult['CITTO']['USERS']);
            if ($boolCitUser) {
                $arHeads[] = $arResult['CITTO']['APPROVE'];
            }
            $arHeads = array_unique($arHeads);
            if (
                in_array($arResult['USER_ID'], $arHeads) ||
                $arResult['USERCADRS'] ||
                $arResult['SECRETARY']
            ) {
                $el = new CIBlockElement();

                if (
                    ($_REQUEST['action'] == 'adminapprove' && $USER->IsAdmin()) ||
                    ($arResult['SECRETARY'] && $_REQUEST['action'] == 'approve')
                ) {
                    $el->Update($f['ID'], array('MODIFIED_BY' => $arResult['USER_ID'], 'ACTIVE' => 'Y', 'PREVIEW_TEXT' => ''));
                    continue;
                }

                $boolInCadrs = $arResult['USERCADRS'];
                $active = 'N';
                if (isset($f["PROPERTY_UF_WHO_APPROVE_VALUE"])) {
                    $whoApprove = json_decode($f["PROPERTY_UF_WHO_APPROVE_VALUE"]);
                    if (
                        !in_array($arResult['USER_ID'], $whoApprove) ||
                        $boolInCadrs
                    ) {
                        $active = 'Y';
                        array_push($whoApprove, $arResult['USER_ID']);
                        foreach ($arHeads as $val) {
                            if (!in_array($val, $whoApprove) && $val != $arResult['CITTO']['APPROVE']) {
                                $active = 'N';
                            }
                        }
                        $active = ($boolInCadrs) ? 'Y' : $active;
                    } else {
                        continue;
                    }
                    $whoApprove = array_values($whoApprove);

                    $el->Update($f['ID'], array('MODIFIED_BY' => $arResult['USER_ID'], 'ACTIVE' => $active, 'PREVIEW_TEXT' => ''));
                    $whoApprove = json_encode($whoApprove);
                    CIBlockElement::SetPropertyValueCode($f['ID'], "UF_WHO_APPROVE", $whoApprove);
                } else {
                    if ($_REQUEST['action'] == 'approve') {
                        $whoApprove = json_encode([$arResult['USER_ID']]);
                        CIBlockElement::SetPropertyValueCode($f['ID'], "UF_WHO_APPROVE", $whoApprove);
                        $active = ((count($arHeads) == 1 || $boolInCadrs) ? 'Y' : 'N');
                        if (
                            $active == 'Y' &&
                            $boolCitUser &&
                            $arResult['USER_ID'] != $arResult['CITTO']['APPROVE']
                        ) {
                            $active = 'N';
                        }

                        $el->Update($f['ID'], array('MODIFIED_BY' => $arResult['USER_ID'], 'ACTIVE' => $active, 'PREVIEW_TEXT' => ''));
                    }
                }

                $uid = intval($f['PROPERTY_USER_VALUE']);
                $email = $arResult['USERS'][ $uid ]["EMAIL"];
                if ($active == 'Y') {
                    $vacations->ImNotify(
                        $arResult['USER_ID'],
                        $uid,
                        GetMessage("BITRIX_PLANNER_PODTVERJDENO") . $name,
                        GetMessage("BITRIX_PLANNER_PODTVERJDNO") . $name . ' [' . $f['ACTIVE_FROM'] . ' - ' . $f['ACTIVE_TO'] . ']',
                        $email
                    );
                }
                /*
                else {
                    $vacations->ImNotify(
                        $arResult['USER_ID'],
                        $uid,
                        GetMessage("BITRIX_PLANNER_PODTVERJDENO") . $name,
                        'Снято подтверждение:' . $name . ' [' . $f['ACTIVE_FROM'] . ' - ' . $f['ACTIVE_TO'] . ']',
                        $email
                    );
                }
                */
            } else {
                $arResult['ERROR'] = GetMessage("BITRIX_PLANNER_NET_PRAV_NA_OPERACIU");
            }
        }
        LocalRedirect($APPLICATION->GetCurPage() . $arResult['BASE_URL']);
    } else {
        $arResult['ERROR'] = GetMessage("BITRIX_PLANNER_ZAPISQ_NE_NAYDENA");
    }
}

[
    'users' => $arResult['USERS'],
    'confirmed' => $arResult['LIST_CONFIRMED'],
    'periods' => $arResult['PERIOD']
] = $users->getUsers(
    $arResult['MY_WORKERS'],
    $arResult['ADMIN'],
    $arResult['DEPARTMENT_LIST'],
    $arResult['DEPARTMENT_ID'],
    $arResult['RECURSIVE']
);

$now = time();
global $declOfNum;

$arCurrPeriod = [];
$arNextPeriod = [];
if (!$bIsMinistr && !$bIsJudge && isset($arResult['USERS'][$users->getSelUserID()]['days_info'])) {
    $bMinusData = false;
    $dayData = $arResult['USERS'][$users->getSelUserID()]['days_info'];
    foreach ($arResult['USERS'][$users->getSelUserID()]['VACATION'] as $key => $value) {
        $cntDays = $value['PERIOD'] / 86400;
        $arResult['USERS'][$users->getSelUserID()]['PERIOD_USED'] += $cntDays;
        foreach ($dayData as $dayId => $infoRow) {
            $infoRow['to_ts'] += 86400;
            if ($value['FROM_TS'] >= $infoRow['from_ts'] && $value['TO_TS'] <= $infoRow['to_ts']) {
                if ($bMinusData || $infoRow['totalused'] >= 0) {
                    $bMinusData = true;
                    $dayData[ $dayId ]['totalused'] += $cntDays;
                    $dayData[ $dayId ]['free'] -= $cntDays;
                }
            }
        }
    }

    foreach ($dayData as $dayId => $infoRow) {
        if ($infoRow['to_ts'] < $now) {
            unset($dayData[ $dayId ]);
        } elseif (
            (
                $infoRow['from_ts'] <= $now ||
                $infoRow['from_ts'] <= strtotime(date('31.12.Y'))
            ) &&
            $infoRow['to_ts'] >= strtotime(date('01.01.'.$arResult['YEAR']))
        ) {
            $arCurrPeriod = $infoRow;
            $arCurrPeriod['req_all'] = 28;
            $arCurrPeriod['req_free'] = 0;
            if ($infoRow['totalused'] < 28) {
                $arCurrPeriod['req_free'] = 28 - $infoRow['totalused'];
            }
        } elseif (
            $infoRow['from_ts'] > $now &&
            $arResult['YEAR'] == date('Y', $infoRow['from_ts'])
        ) {
            $arNextPeriod = $infoRow;
            $arNextPeriod['req_all'] = 28;
            $arNextPeriod['req_free'] = 0;
            if ($infoRow['totalused'] < 28) {
                $arNextPeriod['req_free'] = 28 - $infoRow['totalused'] - $arResult['USERS'][$users->getSelUserID()]['PERIOD_USED'];
            }
        }
    }

    foreach ($arResult['PERIOD'] as $arPeriod) {
        if (
            strtotime($arPeriod['ACTIVE_FROM']) <= $arCurrPeriod['to_ts'] &&
            strtotime($arPeriod['ACTIVE_TO']) >= $arNextPeriod['from_ts']
        ) {
            $iCurrentReq = ($arCurrPeriod['to_ts'] + 86400 - strtotime($arPeriod['ACTIVE_FROM'])) / 86400;
            $iNextReq = (strtotime($arPeriod['ACTIVE_TO']) + 86400 - strtotime($arPeriod['ACTIVE_TO'])) / 86400;
            $arCurrPeriod['req_free'] -= $iCurrentReq;
        }
    }
    $arResult['USERS'][$users->getSelUserID()]['CURR_REQ_FREE'] = $arCurrPeriod['req_free'];

    $arResult['USERS'][$users->getSelUserID()]['days_info'] = $dayData;
    if (!empty($arCurrPeriod)) {
        if ($arCurrPeriod['req_free'] > 0) {
            array_push($arResult['INFO'], 'Сначала запланируйте ' . $arCurrPeriod['req_free'] . ' ' . $declOfNum($arCurrPeriod['req_free'], ['день', 'дня', 'дней']) . ' до ' . date('d.m.Y', $arCurrPeriod['to_ts']));
        }
    }

    // if (!empty($arNextPeriod)) {
    //     if ($arNextPeriod['req_free'] > 0) {
    //         array_push($arResult['INFO'], 'Сначала запланируйте ' . $arNextPeriod['req_free'] . ' ' . $declOfNum($arNextPeriod['req_free'], ['день', 'дня', 'дней']) . ' с ' . date('d.m.Y', $arNextPeriod['from_ts']) . ' до ' . date('31.12.Y', $arNextPeriod['from_ts']));
    //     }
    // }
}

$arResult['USERS'][$users->getSelUserID()]['CURR_PERIOD'] = $arCurrPeriod;
$arResult['USERS'][$users->getSelUserID()]['NEXT_PERIOD'] = $arNextPeriod;

$arResult['EXPORT'] = ($arResult['ADMIN']) ? true : false;

// Если пользователь является главой министерства($arResult['TO_CADRS'])
// и все отпуска утверждены, то добавляем опцию согласования с отделом кадров
if ($arResult['TO_CADRS'] || $arResult['SECRETARY']) {
    $arResult['SHOW_BUTTON'] = false;
    $iCount = 0;
    foreach ($arResult['DEPARTMENT_IDS'] as $s => $key) {
        foreach ($key["UF_TO_CADRS"] as $k => $v) {
            if ($k == $arResult['YEAR'] && $v == 'Y') {
                $iCount++;
            }
        }
    }

    if ($_GET['recursive'] == 1 && $_GET['department'] == array_keys($arResult['DEPARTMENT_IDS'])[0]) {
        $arResult['SHOW_BUTTON'] = (count($arResult['DEPARTMENT_IDS']) == $iCount) ? false : true;
    }

    if ($_REQUEST['to_cadrs'] == 'yes') {
        foreach ($arResult['DEPARTMENT_IDS'] as $key => $value) {
            if (isset($value["UF_TO_CADRS"])) {
                $yorn = [];
                foreach ($value["UF_TO_CADRS"] as $k => $v) {
                    if ($v != $arResult['YEAR']) {
                        $yorn[$k] = $v;
                    }
                }
                $yorn[$arResult['YEAR']] = 'Y';
            } else {
                $yorn = [$arResult['YEAR'] => 'Y'];
            }
            $yorn = json_encode($yorn);
            $el_dep = new CIBlockSection();
            $el_dep->Update($key, array('UF_TO_CADRS' => $yorn));
        }

        $arCadrs = [];
        $rsUsers = CUser::GetList(
            $by = "NAME",
            $order = "desc",
            ['ID' => implode('|', $arResult['USERS_CADRS'])]
        );
        while ($arUser = $rsUsers->Fetch()) {
            $arCadrs[ $arUser['ID'] ] = $arUser['EMAIL'];
        }

        foreach ($arCadrs as $uid => $mail) {
            $vacations->ImNotify(
                $arResult['USER_ID'],
                $uid,
                $arResult['DEPARTMENT_LIST'][array_keys($arResult['DEPARTMENT_IDS'])[0]]['NAME'],
                '<a href="' . $_SERVER['REQUEST_URI'] . '">' . $arResult['DEPARTMENT_LIST'][array_keys($arResult['DEPARTMENT_IDS'])[0]]['NAME'] . ' - График отпусков отправлен на согласование</a>',
                $mail
            );
        }
        LocalRedirect($APPLICATION->GetCurPage() . $arResult['BASE_URL']);
    }
}

$arResult['USERS'] = $users->usersSort(
    $arResult['USERS'],
    $arResult['DEPARTMENT_IDS'],
    $arResult['DEPARTMENT_LIST'],
    ($_GET['sort'] == 'ASC') ? 'ASC' : 'HEADS'
);

$arResult['PERIOD'] = $vacations->getSortPeriods($arResult['USERS'], $arResult['PERIOD']);

$arResult['SEL_USER_ID'] = $users->getSelUserID();

CUtil::InitJSCore(array("tooltip"));
$this->InitComponentTemplate();
$arResult['TEMPLATE'] = & $this->GetTemplate()->GetFolder();
$this->IncludeComponentTemplate();
