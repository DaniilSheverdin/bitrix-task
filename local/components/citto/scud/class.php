<?php

use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;
use Bitrix\Highloadblock as HL;

Loader::includeModule('highloadblock');
CModule::IncludeModule('bitrix.planner');

if (CModule::IncludeModule('nkhost.phpexcel')) {
    global $PHPEXCELPATH;
    require_once($PHPEXCELPATH . '/PHPExcel/IOFactory.php');
}

include_once($GLOBALS['PHPEXCELPATH'] . '/PHPExcel/IOFactory.php');

class SCUD
{
    public static function getAbsence(
        $iStartDate = null,
        $iEndDate = null,
        $arUsers = [],
        $arViolations = [],
        $arEvent = [],
        $sPage = 'events',
        $iRecordFrom = 0,
        $iRecordTo = 0,
        $sExport = null,
        $sApiMode = false
    ) {
        $iHlBlockScudID = HL\HighloadBlockTable::getList([
            'filter' => ['=NAME' => 'SCUD']
        ])->fetch()['ID'];

        if (in_array('VIOLATION_3', $arViolations)) {
            array_push($arViolations, 'VIOLATION');
        }

        $arTypesIDs = [];
        $arEventIDs = [];
        $arTypesViolations = self::getTypesAbsence($iHlBlockScudID);
        $arEventsList = self::getEventsList($iHlBlockScudID);

        if ($sPage == 'journal') {
            foreach ($arTypesViolations['ID'] as $iKey => $arAbsence) {
                if ($arAbsence['XML_ID'] == 'OTHER') {
                    array_push($arTypesIDs, $iKey);
                }
            }
            $sFilterRecord = '><';
        } else {
            if (!empty($arViolations)) {
                foreach ($arViolations as $sType) {
                    array_push($arTypesIDs, $arTypesViolations['XML_ID'][$sType]['ID']);
                }
            } else {
                foreach ($arTypesViolations['ID'] as $iKey => $arAbsence) {
                    if (in_array($arAbsence['XML_ID'], ['VIOLATION', 'VIOLATION_POSITIVE', 'ENTRY', 'EXIT'])) {
                        array_push($arTypesIDs, $iKey);
                    }
                }
            }

            foreach ($arEvent as $sType) {
                if ($sType != 'ALL') {
                    array_push($arEventIDs, $arEventsList['XML_ID'][$sType]['ID']);
                }
            }

            $iRecordFrom = $iRecordTo = null;
            $sFilterRecord = '>';
        }

        $arAbsence = [];

        $obHlblock = HL\HighloadBlockTable::getById($iHlBlockScudID)->fetch();
        $obEntity = HL\HighloadBlockTable::compileEntity($obHlblock);
        $sClassScud = $obEntity->getDataClass();

        $sFilterEvents = empty($arEventIDs) ? '!' : '';
        $sActiveFrom = (empty($iStartDate)) ? '!' : '>=';
        $sActiveTo = (empty($iEndDate)) ? '!' : '<=';

        $iLimit =  ($sExport||$sApiMode) ? 0 : 12000;

        $obData = $sClassScud::getList([
            'filter' => [
                "{$sActiveFrom}UF_ACTIVE_FROM" => (empty($iStartDate)) ? '' : date('d.m.Y H:i:s', $iStartDate),
                "{$sActiveTo}UF_ACTIVE_TO" => (empty($iEndDate)) ? '' : date('d.m.Y H:i:s', $iEndDate),
                'UF_USER' => array_keys($arUsers),
                'UF_TYPE_EVENT' => $arTypesIDs,
                "{$sFilterEvents}UF_EVENT_TOURN" => $arEventIDs,
                "{$sFilterRecord}UF_DATE_CREATE" => [date('d.m.Y H:i:s', $iRecordFrom), date('d.m.Y H:i:s', $iRecordTo)],
            ],
            'order' => array('UF_USER' => 'ASC', 'UF_ACTIVE_FROM' => 'ASC'),
            'limit' => $iLimit,
        ]);

        $iStep = 0;
        $sFileName = 'scud_' . time() . '.xls';
        while ($arItem = $obData->Fetch()) {
            $sViolation = implode('<br>', explode('; ', $arItem['UF_REASON_ABSENCE']));
            $sViolation = ($sViolation == 'NOT') ? '' : $sViolation;

            $arEvent = [
                'VALUE' => $arEventsList['ID'][$arItem['UF_EVENT_TOURN']]['VALUE'],
                'XML_ID' => $arEventsList['ID'][$arItem['UF_EVENT_TOURN']]['XML_ID']
            ];

            $sAction = $arTypesViolations['ID'][$arItem['UF_TYPE_EVENT']]['XML_ID'];

            if (!in_array($sAction, ['VIOLATION', 'VIOLATION_POSITIVE', 'ENTRY', 'EXIT'])) {
                $sDate = "{$arItem['UF_ACTIVE_FROM']} - {$arItem['UF_ACTIVE_TO']}";
            } else {
                $sDate = $arItem['UF_ACTIVE_FROM'];
            }

            switch ($sAction) {
                case 'VIOLATION':
                    $sTypeViolation = 'NEGATIVE';
                    break;
                case 'VIOLATION_POSITIVE':
                    $sTypeViolation = 'POSITIVE';
                    break;
                default:
                    $sTypeViolation = 'DEFAULT';
            }

            $arAbsence[$arItem['UF_USER']][] = [
                'ID' => $arItem['ID'],
                'TOURNIQUET' => $arItem['UF_TOURNIQUET'],
                'FIO' => $arUsers[$arItem['UF_USER']],
                'DATE' => $sDate,
                'VIOLATION' => $sViolation,
                'EVENT' => $arEvent,
                'TYPE_VIOLATION' => $sTypeViolation,
                'DATE_RECORD' => $arItem['UF_DATE_CREATE'],
                'HEAD_CONFIRM' => $arItem['UF_HEAD_CONFIRM']
            ];

            if ($iStep > 10000 && $iPrevUserID != $arItem['UF_USER'] && $sExport) {
                foreach ($arAbsence as $iUserID => $arValue) {
                    if (in_array('VIOLATION_3', $arViolations)) {
                        $iCountViolations = 0;
                        foreach ($arValue as $arField) {
                            if (!empty($arField['EVENT']['XML_ID']) && !empty($arField['VIOLATION'])) {
                                $iCountViolations += count(explode('<br>', $arField['VIOLATION']));
                            }
                        }
                        if ($iCountViolations < 3) {
                            unset($arAbsence[$iUserID]);
                        }
                    }
                }

                if ($sExport == 'export') {
                    self::export($arAbsence, $sPage, $sFileName, false);
                } elseif ($sExport == 'analytics_user' || $sExport == 'analytics_department') {
                    $sTypeAnalytics = ($sExport == 'analytics_user') ? 'users' : 'department';
                    self::exportAnalytics($arAbsence, $sPage, $sFileName, false, $iStartDate, $iEndDate, $sTypeAnalytics);
                }

                unset($arAbsence);
                $arAbsence = [];
                $iStep = 0;
            }

            $iStep++;
            $iPrevUserID = $arItem['UF_USER'];
        }

        if ($sApiMode == true) {
            return $arAbsence;
        } elseif ($sExport == 'export') {
            self::export($arAbsence, $sPage, $sFileName);
        } elseif ($sExport == 'analytics_user' || $sExport == 'analytics_department') {
            $sTypeAnalytics = ($sExport == 'analytics_user') ? 'users' : 'department';
            self::exportAnalytics($arAbsence, $sPage, $sFileName, true, $iStartDate, $iEndDate, $sTypeAnalytics);
        } elseif (empty($sExport)) {
            foreach ($arUsers as $iUserID => $sUser) {
                if (!isset($arAbsence[$iUserID])) {
                    unset($arUsers[$iUserID]);
                } else {
                    $arUsers[$iUserID] = $arAbsence[$iUserID];
                    if (in_array('VIOLATION_3', $arViolations)) {
                        $iCountViolations = 0;
                        foreach ($arUsers[$iUserID] as $arField) {
                            if (!empty($arField['EVENT']['XML_ID']) && !empty($arField['VIOLATION'])) {
                                $iCountViolations += count(explode('<br>', $arField['VIOLATION']));
                            }
                        }

                        if ($iCountViolations < 3) {
                            unset($arUsers[$iUserID]);
                        }
                    }
                }
                unset($arAbsence[$iUserID]);
            }
        }

        return $arUsers;
    }

    public static function getTypesAbsence($iHlBlockScudID = 0)
    {
        $arTypesAbsence = [];
        $obTypesAbsence = CUserFieldEnum::GetList(array(), array(
            'ENTITY_ID' => 'HLBLOCK_$iHlBlockScudID',
            'USER_FIELD_NAME' => 'UF_TYPE_EVENT'
        ));
        while ($arType = $obTypesAbsence->getNext()) {
            $arTypesAbsence['ID'][$arType['ID']] = ['XML_ID' => $arType['XML_ID'], 'VALUE' => $arType['VALUE']];
            $arTypesAbsence['XML_ID'][$arType['XML_ID']] = ['ID' => $arType['ID'], 'VALUE' => $arType['VALUE']];
        }

        return $arTypesAbsence;
    }

    public static function getEventsList($iHlBlockScudID = 0)
    {
        $arEventTourn = [];
        $obEventTourn = CUserFieldEnum::GetList(array(), array(
            'ENTITY_ID' => 'HLBLOCK_$iHlBlockScudID',
            'USER_FIELD_NAME' => 'UF_EVENT_TOURN'
        ));
        while ($arType = $obEventTourn->getNext()) {
            $arEventTourn['ID'][$arType['ID']] = ['XML_ID' => $arType['XML_ID'], 'VALUE' => $arType['VALUE']];
            $arEventTourn['XML_ID'][$arType['XML_ID']] = ['ID' => $arType['ID'], 'VALUE' => $arType['VALUE']];
        }

        return $arEventTourn;
    }

    public static function getUsers($arUsersIDs = [])
    {
        $arFilter = [
            'ID' => implode('|', $arUsersIDs)
        ];
        if (count($arUsersIDs) >= 30) {
            $arFilter = [];
        }
        $arUsers = [];
        $by = 'LAST_NAME';
        $order = 'ASC';
        $obUsers = CUser::GetList(
            $by,
            $order,
            $arFilter,
            [
                'FIELDS' => ['ID', 'LAST_NAME', 'NAME', 'SECOND_NAME'],
                'SELECT' => ['UF_DEPARTMENT'],
            ]
        );
        while ($arUser = $obUsers->getNext()) {
            if (!in_array($arUser['ID'], $arUsersIDs)) {
                continue;
            }
            if (!empty($arUser['LAST_NAME']) && !empty($arUser['NAME']) && !empty($arUser['SECOND_NAME'])) {
                $arUsers[$arUser['ID']] = "{$arUser['LAST_NAME']} {$arUser['NAME']} {$arUser['SECOND_NAME']}";
            }
        }
        return $arUsers;
    }

    public static function getCurrentDate()
    {
        $obCurrentDate = new DateTime();
        $iDateFrom = $obCurrentDate->setTime(00, 00, 00)->format('U');
        $iDateTo = $obCurrentDate->setTime(23, 59, 59)->format('U');
        return ['from' => $iDateFrom, 'to' => $iDateTo];
    }

    private static function checkRoleField()
    {
        $obScudRole = CUserTypeEntity::GetList(array($by => $order), array('ENTITY_ID' => 'USER', 'FIELD_NAME' => 'UF_SCUD_ROLE'));
        $obEnum = new CUserFieldEnum();
        if (!$obScudRole->arResult) {
            $obUserField = new CUserTypeEntity();

            $arFields = array(
                'ENTITY_ID' => 'USER',
                'FIELD_NAME' => 'UF_SCUD_ROLE',
                'USER_TYPE_ID' => 'enumeration',
                'EDIT_FORM_LABEL' => array('ru' => 'Роль (СКУД)', 'en' => 'UF_SCUD_ROLE')
            );
            $iIDRole = $obUserField->Add($arFields);

            $arEnumRole = [
                'HEAD' => 'Руководитель',
                'SECRETARY' => 'Секретарь',
                'EMPLOYEE' => 'Сотрудник',
                'ADMIN' => 'Главный руководитель'
            ];

            $arAddEnum = [];
            $iStep = 0;
            foreach ($arEnumRole as $sRole => $sValue) {
                $arAddEnum['n' . $iStep] = array(
                    'VALUE' => $sValue,
                    'XML_ID' => $sRole,
                    'DEF' => ($sRole == 'EMPLOYEE') ? 'Y' : 'N'
                );
                $iStep++;
            }
            $obEnum->SetEnumValues($iIDRole, $arAddEnum);
        }
    }

    private static function checkStructureField()
    {
        $obScudStructure = CUserTypeEntity::GetList(array($by => $order), array('ENTITY_ID' => 'USER', 'FIELD_NAME' => 'UF_SCUD_STRUCTURE'));

        if (!$obScudStructure->arResult) {
            $obUserField = new CUserTypeEntity();
            $arFields = array(
                'ENTITY_ID' => 'USER',
                'FIELD_NAME' => 'UF_SCUD_STRUCTURE',
                'USER_TYPE_ID' => 'iblock_section',
                'SETTINGS' => [
                    'DISPLAY' => 'CHECKBOX',
                    'IBLOCK_TYPE_ID' => 'structure',
                    'IBLOCK_ID' => COption::GetOptionInt('intranet', 'iblock_structure'),
                ],
                'MULTIPLE' => 'Y',
                'EDIT_FORM_LABEL' => array('ru' => 'Структура (СКУД)', 'en' => 'UF_SCUD_STRUCTURE')
            );
            $obUserField->Add($arFields);
        }
    }

    private static function checkExceptionShowStructure()
    {

        $obShowException = CUserTypeEntity::GetList(array($by => $order), array('ENTITY_ID' => 'IBLOCK_' . COption::GetOptionInt('intranet', 'iblock_structure') . '_SECTION', 'FIELD_NAME' => 'UF_SHOW_EXCEPTION'));
        if (!$obShowException->arResult) {
            $arFields = array(
                'ENTITY_ID' => 'IBLOCK_' . COption::GetOptionInt('intranet', 'iblock_structure') . '_SECTION',
                'FIELD_NAME' => 'UF_SHOW_EXCEPTION',
                'USER_TYPE_ID' => 'boolean',
                'EDIT_FORM_LABEL' => array('ru' => 'Исключать пользователей на странице СКУД', 'en' => 'UF_SHOW_EXCEPTION')
            );
            $obUserField = new CUserTypeEntity();
            $obUserField->Add($arFields);
        }
    }

    public static function getUserFields()
    {
        global $USER;

        self::checkRoleField();
        self::checkStructureField();
        self::checkExceptionShowStructure();

        $obUsers = CUser::GetList($by, $order, ['ID' => $USER->GetID()], ['SELECT' => ['UF_SCUD_ROLE', 'UF_SCUD_STRUCTURE', 'UF_DEPARTMENT']]);
        $arFields = $obUsers->getNext();

        if ($USER->IsAdmin()) {
            $sRole = 'ADMIN';
        } else {
            $obEnum = new CUserFieldEnum();
            $iRoleID = $arFields['UF_SCUD_ROLE'];
            $sRole = (empty($iRoleID)) ? 'EMPLOYEE' : $obEnum->GetList(array(), array('ID' => $iRoleID))->getNext()['XML_ID'];
        }

        $arStructureIDs = (empty($arFields['UF_SCUD_STRUCTURE'])) ? $arFields['UF_DEPARTMENT'] : $arFields['UF_SCUD_STRUCTURE'];

        $_SESSION['SESS_AUTH']['ROLE_SCUD'] = $sRole;
        $_SESSION['SESS_AUTH']['STRUCTURE_IDS_SCUD'] = $arStructureIDs;

        return ['role' => $sRole, 'structureIDs' => $arStructureIDs];
    }

    public static function getUsersIDs(
        $sUserRole = 'EMPLOYEE',
        $arStructureIDs = [],
        $arRequest = [],
        $bSubUsers = false
    ) {
        global $USER;
        $arUsersIDs = [];

        $bRecursive = true;
        $sGetStructure = ($arRequest['structure'] == 'all') ? null : $arRequest['structure'];
        $arExceptionDepartments = [];
        $obExceptionDepartments = CIBlockSection::GetList(
            [$by=>$order],
            ['IBLOCK_ID'=>COption::GetOptionInt('intranet', 'iblock_structure')],
            false,
            ['ID', 'UF_SHOW_EXCEPTION', 'UF_PODVED']
        );
        while ($arDepartment = $obExceptionDepartments->getNext()) {
            if ($arDepartment['UF_SHOW_EXCEPTION']) {
                array_push($arExceptionDepartments, $arDepartment['ID']);
            }
            if (isset($_REQUEST['podved']) && !$arDepartment['UF_PODVED']) {
                array_push($arExceptionDepartments, $arDepartment['ID']);
            }
        }


        $arChildDepartments = CIntranetUtils::GetIBlockSectionChildren($arExceptionDepartments);

        if ($sUserRole == 'ADMIN') {
            $arStructureIDs = null;
        }
        if ($sUserRole == 'EMPLOYEE') {
            $bRecursive = false;
        }

        if ($sUserRole == 'EMPLOYEE') {
            $arUsersIDs = [$USER->GetID()];
        } else {
            if (!empty($sGetStructure) && !empty($sGetStructure)) {
                $bRecursive = $bSubUsers;
                if ($sUserRole == 'ADMIN') {
                    $arStructureIDs = $sGetStructure;
                } else {
                    $arStructureIDs = $sGetStructure;
                }
            }
            $obUsers = CIntranetUtils::getDepartmentEmployees($arStructureIDs, $bRecursive);
            while ($arUser = $obUsers->getNext()) {
                $bException = false;
                if (empty($sGetStructure) || !in_array($sGetStructure, $arChildDepartments)) {
                    foreach ($arUser['UF_DEPARTMENT'] as $iDepID) {
                        if (in_array($iDepID, $arChildDepartments)) {
                            $bException = true;
                            break;
                        }
                    }
                }
                if (!$bException) {
                    array_push($arUsersIDs, $arUser['ID']);
                }
            }
            if (empty($arUsersIDs)) {
                // Если нет пользователей, что показывать
//                $arUsersIDs = [$USER->GetID()];
            }
        }

        return $arUsersIDs;
    }

    public static function getUsersSelect($sUserRole = 'EMPLOYEE', $arStructureIDs = [])
    {
        global $USER;
        $arUsersIDs = [];

        if ($sUserRole == 'EMPLOYEE') {
            $arUsersIDs = [$USER->GetID()];
        } else {
            if ($sUserRole == 'ADMIN') {
                $arStructureIDs = [];
            }

            $obUsers = CIntranetUtils::getDepartmentEmployees($arStructureIDs, true);
            while ($arUser = $obUsers->getNext()) {
                array_push($arUsersIDs, $arUser['ID']);
            }
            if (empty($arUsersIDs)) {
                $arUsersIDs = [$USER->GetID()];
            }
        }

        return self::getUsers($arUsersIDs);
    }

    public static function getStructure($sUserRole = 'EMPLOYEE', $arStructureIDs = [])
    {
        $arStructure = [];
        $arStructureTree = CIntranetUtils::GetStructure();

        foreach ($arStructureTree['TREE'] as $arIDs) {
            foreach ($arIDs as $iID) {
                $bResult = false;

                $fRescursiveDepth = function ($iID) use ($arStructureTree, &$arStructure, &$fRescursiveDepth) {
                    if (!isset($arStructure[$iID])) {
                        $arStructure[$iID] = self::getNameWithDepth($arStructureTree, $iID);

                        if (isset($arStructureTree['TREE'][$iID])) {
                            foreach ($arStructureTree['TREE'][$iID] as $iID) {
                                $fRescursiveDepth($iID);
                            }
                        }
                    }
                };

                if ($sUserRole == 'ADMIN' || (in_array($iID, $arStructureIDs) && $sUserRole != 'ADMIN')) {
                    $bResult = true;
                }

                if ($bResult && $sUserRole != 'EMPLOYEE') {
                    $fRescursiveDepth($iID);
                } elseif ($bResult && $sUserRole == 'EMPLOYEE') {
                    $arStructure[$iID] = self::getNameWithDepth($arStructureTree, $iID);
                }
            }
        }

        return $arStructure;
    }

    public static function getNameWithDepth($arStructureTree = [], $iID = null)
    {
        $iDepth = $arStructureTree['DATA'][$iID]['DEPTH_LEVEL'];
        $sMargin = '';
        for ($i = 0; $i < $iDepth; $i++) {
            $sMargin .= ' · ';
        }
        return $sMargin . $arStructureTree['DATA'][$iID]['NAME'];
    }

    public static function getPage()
    {
        $sUrlPage = $_GET['page'];
        $sPage = ($sUrlPage == 'journal') ? 'journal' : 'events';
        return $sPage;
    }

    public static function getWorkHours($sDateFrom, $sDateTo) {
        $arWorkHours = [
            'HOURS' => 0,
            'DAYS' => []
        ];

        if (is_numeric($sDateFrom)) {
            $sDateFrom = date('d.m.Y', $sDateFrom);
        }

        if (is_numeric($sDateTo)) {
            $sDateTo = date('d.m.Y', $sDateTo);
        }

        $obVacations = new HolidayList\CVacations();
        $arHolidays = $obVacations->getHolidays(2021);

        $iDateFrom = strtotime((new DateTime($sDateFrom))->format('d.m.Y'));
        $iDateTo = strtotime((new DateTime($sDateTo))->format('d.m.Y'));

        if ($iDateTo >= $iDateFrom) {
            for ($iIter = $iDateFrom; $iIter <= $iDateTo; $iIter += 86400) {
                if (!in_array($iIter, $arHolidays['holydays']) && !in_array($iIter, $arHolidays['weekends'])) {
                    $iDayWeek = date('N', $iIter);
                    if ($iDayWeek != 6 && $iDayWeek != 7 && !in_array($iIter, $arHolidays['shortdays'])) {
                        $arWorkHours['HOURS'] += 8;
                        $arWorkHours['DAYS'][$iIter] = 8;
                    } else if (in_array($iIter, $arHolidays['shortdays'])) {
                        $arWorkHours['HOURS'] += 7;
                        $arWorkHours['DAYS'][$iIter] = 7;
                    }
                }
            }
        }

        return $arWorkHours;
    }

    private static function export(
        $arUsers = [],
        $sPage = 'events',
        $sFile = 'scud.xls',
        $bOutput = true
    ) {
        $sDirPath = sys_get_temp_dir();
        $bFileExists = file_exists("$sDirPath/$sFile");

        $obPHPExcel = (!$bFileExists) ? new PHPExcel() : \PHPExcel_IOFactory::load("$sDirPath/$sFile");

        $obPHPExcel->setActiveSheetIndex(0);
        $obSheet = $obPHPExcel->getActiveSheet();

        if (!$bFileExists) {
            $obSheet->setTitle('Лист');
            $obSheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
            $obSheet->getPageSetup()->SetPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

            $obPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
            $obPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
            $obPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);

            $obSheet->setCellValue('A1', 'ФИО');
            $obSheet->setCellValue('B1', ($sPage == 'events') ? 'Турникет' : 'Фамилия руководителя, давшего разрешение на убытие гражданского служащего (работника)');
            $obSheet->setCellValue('C1', ($sPage == 'events') ? 'Событие' : 'Дата записи');
            $obSheet->setCellValue('D1', ($sPage == 'events') ? 'Дата' : 'Отсутствие');
            $obSheet->setCellValue('E1', ($sPage == 'events') ? 'Нарушения' : 'Цель и место убытия');
        }

        $iRow = (!$bFileExists) ? 2 : $obSheet->getHighestRow();

        foreach ($arUsers as $arEvents) {
            foreach ($arEvents as $arUser) {
                $obSheet->setCellValue("A{$iRow}", $arUser['FIO']);
                $obSheet->setCellValue("B{$iRow}", ($sPage == 'events') ? $arUser['TOURNIQUET'] : $arUser['HEAD_CONFIRM']);
                $obSheet->setCellValue("C{$iRow}", ($sPage == 'events') ? $arUser['EVENT']['VALUE'] : $arUser['DATE_RECORD']);
                $obSheet->setCellValue("D{$iRow}", ($sPage == 'events') ? $arUser['DATE'] : $arUser['DATE']);
                $obSheet->setCellValue("E{$iRow}", ($sPage == 'events') ? $arUser['VIOLATION'] : $arUser['VIOLATION']);
                $iRow++;
            }
        }

        header('Content-type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename=' . $sFile);

        $obWriter = new PHPExcel_Writer_Excel5($obPHPExcel);

        if ($bOutput) {
            $obWriter->save('php://output');
            if ($bFileExists) {
                unlink("$sDirPath/$sFile");
            }
            die;
        } else {
            $obWriter->save("$sDirPath/$sFile");
        }
    }

    public static function exportAnalytics(
        $arUsers = [],
        $sPage = 'events',
        $sFile = 'scud_analytics.xls',
        $bOutput = true,
        $iStartDate = null,
        $iEndDate = null,
        $sTypeAnalytics = 'users',
        $sApiMode = false
    ) {
        if (is_numeric($iStartDate) && is_numeric($iEndDate)) {
            $sDirPath = sys_get_temp_dir();
            $bFileExists = file_exists("$sDirPath/$sFile");
            $obVacations = new HolidayList\CVacations();
            $arHolidays = $obVacations->getHolidays(date('Y'));
            $arWorkHours = self::getWorkHours($iStartDate, $iEndDate);
            $iWorkHours = $arWorkHours['HOURS'];
            $arDaysPeriod = [];
            $iWorkDays = 0;

            for ($iDay = $iStartDate; $iDay <= $iEndDate; $iDay += 86400) {
                $iDayWeek = date('N', $iDay);
                if (in_array($iDay, $arHolidays['holydays']) || in_array($iDay, $arHolidays['weekends'])) {
                    $arDaysPeriod[$iDay] = 'weekend';
                } elseif (in_array($iDay, $arHolidays['shortdays'])) {
                    $arDaysPeriod[$iDay] = 'shortday';
                    $iWorkDays++;
                } elseif ($iDayWeek == 6 || $iDayWeek == 7) {
                    $arDaysPeriod[$iDay] = 'weekend';
                } else {
                    $arDaysPeriod[$iDay] = 'workday';
                    $iWorkDays++;
                }
            }

            $arUsersWorkTime = [];

            foreach ($arUsers as $iUserID => $arEvents) {
                $arAbsenceHours = $arWorkHours['DAYS'];
                $arUnsetDays = [];

                $arCountAfter = [
                    'DAYS_AFTER_18' => [],
                    'DAYS_AFTER_20' => []
                ];

                $arUsersWorkTime[ $iUserID ] = [
                    'FIO' => '',
                    'WORK_HOURS' => 0,
                    'ABSENCE_HOURS' => 0,
                    'DAYS_AFTER_18' => 0,
                    'DAYS_AFTER_20' => 0,
                    'DAYS_LATE' => 0
                ];

                foreach ($arEvents as $iKey => $arEvent) {
                    $sCurrentDate = $arEvent['DATE']->format('d.m.Y');
                    $iCurrentDate = strtotime($sCurrentDate);

                    if ($arAbsenceHours[$iCurrentDate]) {
                        unset($arAbsenceHours[$iCurrentDate]);
                        array_push($arUnsetDays, $iCurrentDate);
                    }

                    // Записываем опоздания
                    if ($arEvent['TYPE_VIOLATION'] == 'NEGATIVE' && $arEvent['EVENT']['XML_ID'] == 'ENTRY') {
                        $arUsersWorkTime[ $iUserID ]['DAYS_LATE'] += 1;
                    }

                    // Пропускаем выходные дни и праздники
                    if ($arDaysPeriod[$iCurrentDate] == 'weekend') {
                        continue;
                    }

                    $iWorkSeconds = 0;
                    $sEndWorkTime = '18:30:00';

                    $sDiffSeconds = strtotime($arEvent['DATE']->format('d.m.Y H:i:s')) - strtotime("$sCurrentDate $sEndWorkTime");
                    $bProcessingWork = false;
                    $iHoursWorkDay = ($arDaysPeriod[$iCurrentDate] == 'shortdays') ? 7 : 8;

                    // Задержки на работе после 18-00 и 20-00
                    if ($sDiffSeconds > 0 && $sDiffSeconds < 3600 * 1.5 && !in_array($iCurrentDate, $arCountAfter['DAYS_AFTER_18'])) {
                        array_push($arCountAfter['DAYS_AFTER_18'], $iCurrentDate);
                    } elseif ($sDiffSeconds > 0 && $sDiffSeconds > 3600 * 1.5 && !in_array($iCurrentDate, $arCountAfter['DAYS_AFTER_20'])) {
                        array_push($arCountAfter['DAYS_AFTER_20'], $iCurrentDate);
                        if ($iDeleteKey = array_search($iCurrentDate, $arCountAfter['DAYS_AFTER_18'])) {
                            unset($arCountAfter['DAYS_AFTER_18'][$iDeleteKey]);
                        }
                    }

                    if (isset($arEvents[$iKey + 1])) {
                        $sNextDate = $arEvents[$iKey + 1]['DATE']->format('d.m.Y');
                        if ($sCurrentDate != $sNextDate) {
                            $bProcessingWork = true;
                        }
                    } else {
                        $bProcessingWork = true;
                        // Считаем задержки после 18-00 и 20-00
                        $a = count($arCountAfter['DAYS_AFTER_20']);
                        $arUsersWorkTime[ $iUserID ]['DAYS_AFTER_18'] = count($arCountAfter['DAYS_AFTER_18']);
                        $arUsersWorkTime[ $iUserID ]['DAYS_AFTER_20'] = count($arCountAfter['DAYS_AFTER_20']);
                    }

                    if ($bProcessingWork) {
                        if ($arEvent['EVENT']['XML_ID'] == 'EXIT') {
                            if ($sDiffSeconds < 0 && $arEvent['TYPE_VIOLATION'] == 'DEFAULT') {
                                $iWorkSeconds = 3600 * $iHoursWorkDay;
                            } else {
                                $iWorkSeconds = 3600 * $iHoursWorkDay + $sDiffSeconds;
                            }
                        } else {
                            $iWorkSeconds = 3600 * $iHoursWorkDay;
                        }
                    }



                    $arUsersWorkTime[ $iUserID ]['WORK_HOURS'] += $iWorkSeconds / 3600;
                    $arUsersWorkTime[ $iUserID ]['FIO'] = $arEvent['FIO'];
                }

                // Сколько рабочих часов, дней пропущено
                $arUsersWorkTime[ $iUserID ]['ABSENCE_HOURS'] = array_sum(array_values($arAbsenceHours));
                $arUsersWorkTime[ $iUserID ]['ABSENCE_DAYS'] = count($arAbsenceHours);

                unset($arAbsenceHours);
            }

            if (!function_exists('setParentDepHasUsers')) {
                function setParentDepHasUsers($arDeps, $id)
                {
                    $arDeps[ $id ]['HAS_USERS'] = 1;
                    if ($arDeps[ $id ]['PARENT'] > 0) {
                        $arDeps = setParentDepHasUsers($arDeps, $arDeps[ $id ]['PARENT']);
                    }
                    return $arDeps;
                }
            }

            $arFilterSect = [
                'IBLOCK_ID' => COption::GetOptionInt('intranet', 'iblock_structure', 0),
                'ACTIVE'    => 'Y',
            ];
            $res = CIBlockSection::GetList(
                ['LEFT_MARGIN' => 'ASC', 'NAME' => 'ASC'],
                $arFilterSect,
                false,
                ['ID', 'NAME', 'IBLOCK_SECTION_ID', 'DEPTH_LEVEL', 'XML_ID', 'UF_PODVED']
            );
            $arDepList = [];
            $arPodved = [];
            while ($row = $res->Fetch()) {
                if (!isset($_REQUEST['podved']) && $row['UF_PODVED']) {
                    $arPodved[] = $row['ID'];
                    continue;
                }
                $arFilter = [
                    'ACTIVE'        => 'Y',
                    'UF_DEPARTMENT' => $row['ID'],
                ];
                $resUser = CUser::GetList(
                    $by = 'LAST_NAME',
                    $order = 'asc',
                    $arFilter
                );
                $arCurUsers = [];
                $bHasUserRows = false;
                while ($rowUser = $resUser->Fetch()) {
                    if (empty($rowUser['LAST_NAME'])) {
                        continue;
                    }
                    // if ($rowUser['LID'] != 's1') {
                    //     continue;
                    // }
                    if (array_key_exists($rowUser['ID'], $arUsersWorkTime)) {
                        $bHasUserRows = true;
                    }
                    $arCurUsers[ $rowUser['ID'] ] = [
                        'NAME'      => $rowUser['LAST_NAME'] . ' ' . $rowUser['NAME'],
                        'GENDER'    => $rowUser['PERSONAL_GENDER'],
                        'HAS_ROWS'  => array_key_exists($rowUser['ID'], $arUsersWorkTime),
                    ];
                }
                $arDepList[ $row['ID'] ] = [
                    'ID'            => $row['ID'],
                    'NAME'          => $row['NAME'],
                    'DEPTH_LEVEL'   => $row['DEPTH_LEVEL'],
                    'PARENT'        => (int)$row['IBLOCK_SECTION_ID'],
                    'HAS_USERS'     => (int)$bHasUserRows,
                    'USERS'         => $arCurUsers,
                ];
                if ($bHasUserRows && $row['IBLOCK_SECTION_ID'] > 0) {
                    $arDepList = setParentDepHasUsers($arDepList, $row['IBLOCK_SECTION_ID']);
                }
            }

            $obPHPExcel = (!$bFileExists) ? new PHPExcel() : \PHPExcel_IOFactory::load("$sDirPath/$sFile");

            $obPHPExcel->setActiveSheetIndex(0);
            $obSheet = $obPHPExcel->getActiveSheet();

            if ($sTypeAnalytics == 'department') {
                $arUserIDs = array_keys($arUsers);
                if ($arUserIDs) {
                    $arDepartments = [];
                    $obUsers = UserTable::getList([
                        'select' => ['ID', 'UF_DEPARTMENT'],
                        'filter' => ['ID' => $arUserIDs]
                    ]);
                    $arUsersToDep = [];
                    $arChains = [];
                    while ($arItem = $obUsers->fetch()) {
                        if ($iCurDep = $arItem['UF_DEPARTMENT'][0]) {
                            if (in_array($iCurDep, $arPodved)) {
                                continue;
                            }
                            $arDepartments[ $iCurDep ]['USERS'][] = $arItem['ID'];

                            if (!isset($arChains[ $iCurDep ])) {
                                $arChains[ $iCurDep ] = CIBlockSection::GetNavChain(false, $iCurDep, ['ID', 'NAME'], true);
                            }

                            $arUsersToDep[ $iCurDep ][ $arItem['ID'] ] = $arItem['ID'];
                            foreach ($arChains[ $iCurDep ] as $chain) {
                                $arUsersToDep[ $chain['ID'] ][ $arItem['ID'] ] = $arItem['ID'];
                            }
                        }
                    }

                    $arDepartmentsName = CIntranetUtils::GetDepartmentsData(array_keys($arUsersToDep));
                    $arDepartmentsAnalytics = [];

                    foreach ($arDepartmentsName as $iDepID => $sNameDep) {
                        $iCountEmployees = count($arUsersToDep[ $iDepID ]);
                        $iDaysAfter18 = 0;
                        $iDaysAfter20 = 0;
                        $iCountEmployeesMore1_4 = 0;
                        $iActualFund = 0;
                        $iDaysAbsencesAverage = 0;
                        $iDaysLate = 0;

                        foreach ($arUsersToDep[ $iDepID ] as $iUserID) {
                            $iDaysAfter18 += $arUsersWorkTime[ $iUserID ]['DAYS_AFTER_18'];
                            $iDaysAfter20 += $arUsersWorkTime[ $iUserID ]['DAYS_AFTER_20'];
//                            $iActualFund += $arUsersWorkTime[ $iUserID ]['WORK_HOURS'];
                            $iActualFund += round($arUsersWorkTime[ $iUserID ]['WORK_HOURS'] / ($iWorkHours - $arUsersWorkTime[ $iUserID ]['ABSENCE_HOURS']), 2);

                            if (round($arUsersWorkTime[ $iUserID ]['WORK_HOURS'] / $iWorkHours, 2) > 1.4) {
                                $iCountEmployeesMore1_4++;
                            }

                            $iDaysAbsencesAverage += $arUsersWorkTime[ $iUserID ]['ABSENCE_DAYS'];
                            $iDaysLate += $arUsersWorkTime[ $iUserID ]['DAYS_LATE'];
                        }
//                        $iCoefficient = ($iWorkHours * $iCountEmployees) / $iActualFund;
                        $iCoefficient = $iActualFund / $iCountEmployees;

                        $arDepartmentsAnalytics[ $iDepID ] = [
                            'DEPARTMENT'        => $sNameDep,
                            'COUNT_EMP'         => $iCountEmployees,
                            'DAYS'              => $iWorkDays,
                            'COEFFICIENT'       => round($iCoefficient, 2),
                            'AFTER_20'          => round($iDaysAfter20 / $iCountEmployees, 2),
                            'AFTER_18'          => round($iDaysAfter18 / $iCountEmployees, 2),
                            'COEFF_MORE_1_4'    => round($iCountEmployeesMore1_4, 2),
                            'ABSENCE_DAYS' => round($iDaysAbsencesAverage / $iCountEmployees, 2),
                            'DAYS_LATE' => round($iDaysLate / $iCountEmployees, 2),
                        ];
                    }
                }
            }

            if ($sApiMode == true) {
                $arResult = [];

                if ($sTypeAnalytics == 'department') {
                    /*На всякий случай оставлю это условие*/
                    foreach ($arDepList as $depId => $arDepData) {
                        if (!array_key_exists($depId, $arDepartmentsAnalytics)) {
                            continue;
                        }
                        $arDepartment = $arDepartmentsAnalytics[ $depId ];

                        /*todo какой-то код))*/
                    }
                } else {
                    $arShowedUsers = [];
                    foreach ($arDepList as $arDepartment) {
                        if ($arDepartment['HAS_USERS']) {
                            foreach ($arDepartment['USERS'] as $userId => $arUserData) {
                                if (array_key_exists($userId, $arShowedUsers)) {
                                    continue;
                                }
                                if ($arUserData['HAS_ROWS'] && array_key_exists($userId, $arUsersWorkTime)) {
                                    $arShowedUsers[ $userId ] = $userId;
                                    $arUser = $arUsersWorkTime[ $userId ];

                                    $arResult[] = [
                                        'user_id' => $userId,
                                        'user_fio' => $arUser['FIO'],
                                        'department' => $arDepartment['NAME'],
                                        'coefficient' => round($arUser['WORK_HOURS'] / ($iWorkHours - $arUser['ABSENCE_HOURS']), 2)
                                    ];
                                }
                            }
                        }
                    }
                }

                return $arResult;
            }

            if (!$bFileExists) {
                $obSheet->setTitle('Лист');
                $obSheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
                $obSheet->getPageSetup()->SetPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);

                $obPHPExcel->getActiveSheet()->getPageSetup()->setFitToPage(true);
                $obPHPExcel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
                $obPHPExcel->getActiveSheet()->getPageSetup()->setFitToHeight(0);

                if ($sTypeAnalytics == 'department') {
                    $obSheet->setCellValue('A1', 'ОИВ/ПАП');
                    $obSheet->setCellValue('B1', 'Количество сотрудников');
                    $obSheet->setCellValue('C1', 'Количество рабочих дней в анализируемом периоде');
                    $obSheet->setCellValue('D1', 'Количество дней отсутствия (рабочих)');
                    $obSheet->setCellValue('E1', 'Средний коэффициент переработок');
                    $obSheet->setCellValue('F1', 'Среднее количество дней задержек на 1 сотрудника после 20.00');
                    $obSheet->setCellValue('G1', 'Среднее количество дней задержек на 1 сотрудника после 18.30');
                    $obSheet->setCellValue('H1', 'Количество сотрудников с коэффициентом задержки более 1, 4');
                    $obSheet->setCellValue('I1', 'Количество дней с опозданиями');
                } else {
                    $obSheet->setCellValue('A1', 'ФИО');
                    $obSheet->setCellValue('B1', 'Количество рабочих дней в анализируемом периоде');
                    $obSheet->setCellValue('C1', 'Количество дней отсутствия (рабочих)');
                    $obSheet->setCellValue('D1', 'Коэффициент переработок');
                    $obSheet->setCellValue('E1', 'Кол-во дней задержек после 18.30');
                    $obSheet->setCellValue('F1', 'Количество дней задержек после 20.00');
                    $obSheet->setCellValue('G1', 'Количество дней с опозданиями');
                }
            }

            $iRow = (!$bFileExists) ? 2 : $obSheet->getHighestRow();

            if ($sTypeAnalytics == 'department') {
                foreach ($arDepList as $depId => $arDepData) {
                    if (!array_key_exists($depId, $arDepartmentsAnalytics)) {
                        continue;
                    }
                    $arDepartment = $arDepartmentsAnalytics[ $depId ];
                    $obSheet->setCellValue("A{$iRow}", str_repeat('. ', $arDepData['DEPTH_LEVEL']-1) . ' ' . $arDepartment['DEPARTMENT']);
                    $obSheet->setCellValue("B{$iRow}", $arDepartment['COUNT_EMP']);
                    $obSheet->setCellValue("C{$iRow}", $arDepartment['DAYS']);
                    $obSheet->setCellValue("D{$iRow}", $arDepartment['ABSENCE_DAYS']);
                    $obSheet->setCellValue("E{$iRow}", $arDepartment['COEFFICIENT']);
                    $obSheet->setCellValue("F{$iRow}", $arDepartment['AFTER_20']);
                    $obSheet->setCellValue("G{$iRow}", $arDepartment['AFTER_18']);
                    $obSheet->setCellValue("H{$iRow}", $arDepartment['COEFF_MORE_1_4']);
                    $obSheet->setCellValue("I{$iRow}", $arDepartment['DAYS_LATE']);
                    $iRow++;
                }
            } else {
                $arShowedUsers = [];
                foreach ($arDepList as $arDepartment) {
                    if ($arDepartment['HAS_USERS']) {
                        $obSheet->setCellValue("A{$iRow}", str_repeat('. ', $arDepartment['DEPTH_LEVEL']-1) . ' ' . $arDepartment['NAME']);
                        $obSheet->getStyle("A{$iRow}")->getFont()->setBold(true);
                        $iRow++;

                        foreach ($arDepartment['USERS'] as $userId => $arUserData) {
                            if (array_key_exists($userId, $arShowedUsers)) {
                                continue;
                            }
                            if ($arUserData['HAS_ROWS'] && array_key_exists($userId, $arUsersWorkTime)) {
                                $arShowedUsers[ $userId ] = $userId;

                                $arUser = $arUsersWorkTime[ $userId ];
                                $obSheet->setCellValue("A{$iRow}", $arUser['FIO']);
                                $obSheet->setCellValue("B{$iRow}", $iWorkDays);
                                $obSheet->setCellValue("C{$iRow}", $arUser['ABSENCE_DAYS']);
                                $obSheet->setCellValue("D{$iRow}", round($arUser['WORK_HOURS'] / ($iWorkHours - $arUser['ABSENCE_HOURS']), 2));
                                $obSheet->setCellValue("E{$iRow}", $arUser['DAYS_AFTER_18']);
                                $obSheet->setCellValue("F{$iRow}", $arUser['DAYS_AFTER_20']);
                                $obSheet->setCellValue("G{$iRow}", $arUser['DAYS_LATE']);
                                $iRow++;
                            }
                        }
                    }
                }
            }

            header('Content-type: application/vnd.ms-excel');
            header('Content-Disposition: attachment; filename=' . $sFile);

            $obWriter = new PHPExcel_Writer_Excel5($obPHPExcel);

            if ($bOutput) {
                $obWriter->save('php://output');
                if ($bFileExists) {
                    unlink("$sDirPath/$sFile");
                }
                die;
            } else {
                $obWriter->save("$sDirPath/$sFile");
            }
        }
    }

    public static function isUserGugsic($iUserID)
    {
        $bIsUserGugsic = false;
        $iGugsicDeprtmentID = 453;
        $arGugsicDepartments = array_merge(CIntranetUtils::GetDeparmentsTree($iGugsicDeprtmentID, true), [$iGugsicDeprtmentID]);

        if ($iUserID) {
            $obOrm = UserTable::getList([
                'select' => ['ID', 'UF_DEPARTMENT'],
                'filter' => ['ID' => $iUserID]
            ]);

            $arUser = $obOrm->fetch();

            foreach ($arUser['UF_DEPARTMENT'] as $iDepartmentID) {
                if (in_array($iDepartmentID, $arGugsicDepartments)) {
                    $bIsUserGugsic = true;
                }
            }
        }

        return $bIsUserGugsic;
    }
}
