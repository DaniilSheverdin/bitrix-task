<?php

namespace Citto;

use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;

class Bizproc
{
    /**
     * Получить список выходных.
     *
     * @return array
     */
    public static function getHolidays()
    {
        Loader::includeModule('bitrix.planner');
        $obCalc = new \HolidayList\CVacations();
        $arAllHolidays = $obCalc->getHolidays();
        return array_map(
            function ($timestamp) {
                return date('d.m', $timestamp);
            },
            array_merge(
                $arAllHolidays['holydays'],
                $arAllHolidays['weekends']
            )
        );
    }

    /**
     * Проверка даты на выходной.
     *
     * @param integer $date TS Даты.
     *
     * @return boolean
     */
    public static function isHoliday(int $date)
    {
        if (in_array(date('w', $date), [0, 6])) {
            return true;
        }
        $arHolidays = self::getHolidays();
        return (in_array(date('d.m', $date), $arHolidays, true));
    }

    /**
     * Добавить N рабочих дней к дате.
     *
     * @param integer $date TS Даты.
     * @param integer $days Количество дней.
     *
     * @return integer
     */
    public static function addWorkDay(int $date = 0, int $days = 0)
    {
        $delta = 86400;
        if ($days < 0) {
            $delta *= -1;
        }

        $days = abs($days);
        $iterations = 0;

        if ($days == 0) {
            while (self::isHoliday($date) && $iterations < 1000) {
                ++$iterations;
                $date += $delta;
            }
        }

        while ($days > 0 && $iterations < 1000) {
            ++$iterations;
            $date += $delta;

            if (self::isHoliday($date)) {
                continue;
            }
            --$days;
        }

        return $date;
    }


    /**
     * Получить всех согласовавших и подписавших из бизнес процесса
     * SIGN - подписавшие
     * VISA - согласовавшие
     *
     * @param int $iElementID
     *
     * @return array
     */
    public static function getApprovedUsers(int $iElementID)
    {
        $arApprovers = [
            'SIGN' => [],
            'VISA' => []
        ];

        $arUsers = [];
        $obUsers = UserTable::getList([
            'select' => ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'UF_DEPARTMENT', 'UF_WORK_POSITION'],
        ]);

        while ($arUser = $obUsers->fetch()) {
            $sFIO = "{$arUser['LAST_NAME']} {$arUser['NAME']} {$arUser['SECOND_NAME']}";
            $arUsers[$arUser['ID']] = [
                'FIO' => $sFIO,
                'WORK_POSITION' => $arUser['UF_WORK_POSITION']
            ];
        }

        $obConnection = \Bitrix\Main\Application::getConnection();

        $sql = "select TASK_USER.ID, TASK_USER.USER_ID, TASK_USER.DATE_UPDATE, TASK.ACTIVITY from b_bp_workflow_state WS
        JOIN b_bp_task TASK ON TASK.WORKFLOW_ID = WS.ID
        JOIN b_bp_task_user TASK_USER ON TASK_USER.TASK_ID = TASK.ID
        where WS.DOCUMENT_ID = {$iElementID} AND TASK.STATUS = 1";

        $obRecordset = $obConnection->query($sql);

        while ($arRecord = $obRecordset->fetch()) {
            $iUserID = $arRecord['USER_ID'];
            $sAction = ($arRecord['ACTIVITY'] == 'ApproveActivity') ? 'VISA' : 'SIGN';
            $arApprovers[$sAction][] = [
                'FIO' => $arUsers[$iUserID]['FIO'],
                'WORK_POSITION' => $arUsers[$iUserID]['WORK_POSITION'],
                'DATE_APPROVE' => $arRecord['DATE_UPDATE']->format('d.m.Y H:i:s')
            ];
        }

        return $arApprovers;
    }
}
