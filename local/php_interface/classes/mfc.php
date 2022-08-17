<?php

namespace Citto;

use CSite;
use CUser;
use CGroup;
use CIBlockSection;
use CIntranetUtils;
use Monolog\Logger;
use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;
use Monolog\Handler\RotatingFileHandler;

/**
 * Класс обработки данных для МФЦ.
 */
class Mfc
{
    public static $siteId = 'gi';

    public static $structureSectionId = 58;

    public static $uMfcSectionId = 2698;

    /**
     * Обработчик после построения списка контактов для чата.
     * @param array &$arContactList
     * @return array
     */
    public function handleAfterContactListGetList(&$arContactList)
    {
        global $USER;
        $arCurrentUser = UserTable::getList([
            'select' => ['ID', 'LID'],
            'filter' => ['ID' => $USER->GetID()]
        ])->fetch();
        if ($arCurrentUser['LID'] == self::$siteId) {
            /**
             * Оставить только юзеров МФЦ
             */
            $arMfcUsers = self::getUserList();
            if (!empty($arMfcUsers)) {
                foreach (array_keys($arContactList['users']) as $uid) {
                    if (!in_array($uid, $arMfcUsers)) {
                        unset($arContactList['users'][$uid]);
                    }
                }
            }

            /**
             * Оставить только отделы МФЦ и укоротить их названия
             */
            $arMfcDepartments = self::getDepartmentList();
            if (!empty($arMfcDepartments)) {
                $parentName = explode(' / ', $arContactList['groups'][self::$structureSectionId]['name'])[0];
                foreach ($arContactList['groups'] as $gid => $arGroup) {
                    if (!in_array($gid, $arMfcDepartments)) {
                        unset($arContactList['groups'][$gid]);
                    } else {
                        $arName = explode(' / ', $arGroup['name']);
                        $last = -1;
                        foreach ($arName as $id => $name) {
                            if ($name == $parentName) {
                                $last = $id;
                            }
                            if ($last >= 0 && $id > $last) {
                                unset($arName[$id]);
                            }
                        }
                        $arContactList['groups'][$gid]['name'] = implode(' / ', $arName);
                    }
                }
            }
        }
    }

    /**
     * Событие поиска пользователей соцсети, для МФЦ искать только МФЦ.
     * @param array $arSearchValue
     * @param array &$filter
     * @param array &$select
     * @return void
     */
    public function handleOnSocNetLogDestinationSearchUsers($arSearchValue, &$filter, &$select)
    {
        global $USER;
        $arCurrentUser = UserTable::getList([
            'select' => ['ID', 'LID'],
            'filter' => ['ID' => $USER->GetID()]
        ])->fetch();
        if (
            $arCurrentUser['LID'] == self::$siteId &&
            !CSite::InGroup([129])
        ) {
            $filter['UF_DEPARTMENT'] = self::getDepartmentList();
        }
    }

    /**
     * Список ID отделов МФЦ.
     * @return array
     */
    public static function getDepartmentList()
    {
        $arReturn = [];

        Loader::includeModule('intranet');
        $arReturn = CIntranetUtils::GetDeparmentsTree(self::$structureSectionId, true);
        $arReturn[] = self::$structureSectionId;

        return $arReturn;
    }

    /**
     * Список отделов МФЦ.
     * @return array
     */
    public static function getDepartmentNames()
    {
        $arReturn = [];

        Loader::includeModule('iblock');
        $arMfcDepartments = self::getDepartmentList();
        $objTree = CIBlockSection::GetTreeList(
            [
                'IBLOCK_ID' => 5,
                'ACTIVE' => 'Y'
            ],
            ['ID', 'NAME', 'DEPTH_LEVEL']
        );
        while ($depItem = $objTree->GetNext()) {
            if (!in_array($depItem['ID'], $arMfcDepartments)) {
                continue;
            }

            $arReturn[$depItem['ID']] = str_repeat(' . ', $depItem['DEPTH_LEVEL'] - 3) . $depItem['NAME'];
        }
        return $arReturn;
    }

    /**
     * Список пользователей МФЦ
     *
     * @return array
     */
    public static function getUserList($bActive = true)
    {
        $arReturn = [];
        $arMfcDepartments = self::getDepartmentList();
        $arFilter = [
            'ACTIVE' => 'Y'
        ];
        if (!$bActive) {
            $arFilter = [];
        }
        $orm = UserTable::getList([
            'select' => ['ID', 'LID', 'UF_DEPARTMENT'],
            'filter' => $arFilter
        ]);
        while ($arUser = $orm->fetch()) {
            if (empty($arUser['UF_DEPARTMENT'])) {
                continue;
            }
            $arDiff = array_intersect($arUser['UF_DEPARTMENT'], $arMfcDepartments);
            if (!empty($arDiff)) {
                $arReturn[] = $arUser['ID'];
            }
        }

        return $arReturn;
    }

    /**
     * Достать название МФЦ из инфоблока
     *
     * @return string
     */
    public static function getName()
    {
        Loader::includeModule('intranet');
        $arReturn = CIntranetUtils::GetDepartmentsData([self::$structureSectionId]);
        return $arReturn[self::$structureSectionId];
    }

    /**
     * Запретить редактировать чужих пользователей
     *
     * @param array $arFields
     */
    public function handleOnBeforeUserUpdate(&$arFields)
    {
        global $APPLICATION, $USER;
        $arMfcUsers = self::getUserList(false);
        if ($USER->IsAdmin() && in_array($USER->GetID(), $arMfcUsers) && !in_array($arFields['ID'], $arMfcUsers)) {
            $APPLICATION->ThrowException('Редактирование запрещено.');
            return false;
        }
    }

    /**
     * При добавлении админом МФЦ добавить сайт, отдел и авторизацию
     *
     * @param array $arFields
     */
    public function handleOnBeforeUserAdd(&$arFields)
    {
        global $APPLICATION, $USER;
        $arMfcUsers = self::getUserList();
        if (in_array($USER->GetID(), $arMfcUsers)) {
            $arFields['UF_DEPARTMENT'] = array_filter($arFields['UF_DEPARTMENT']);
            if (empty($arFields['UF_DEPARTMENT'])) {
                $arFields['UF_DEPARTMENT'] = [
                    self::$structureSectionId
                ];
            }

            if ($arFields['LID'] != self::$siteId) {
                $arFields['LID'] = self::$siteId;
            }

            if (empty($arFields['EXTERNAL_AUTH_ID'])) {
                $arFields['EXTERNAL_AUTH_ID'] = 'LDAP#1';
            }

            if (!in_array(120, $arFields['GROUP_ID'])) {
                $arFields['GROUP_ID'][] = 120;
            }
        } elseif (in_array(85, $arFields['GROUP_ID'])) {
            /*
             * ЦИТ
             */
            $arFields['LID'] = 'nh';
        }
    }

    /**
     * При добавлении записи в ЖЛ
     * @param array $arFields
     */
    public function handleOnBeforeSocNetLogAdd(&$arFields)
    {
        global $APPLICATION, $USER;
        $arMfcUsers = self::getUserList();
        if (in_array($USER->GetID(), $arMfcUsers)) {
            if ($arFields['EVENT_ID'] == 'timeman_entry') {
                return false;
            }
        }
    }

    /**
     * @param $arFields
     * @return bool
     */
    private static function _tasksDirectControl($arFields)
    {
        global $USER;

        if (isset($arFields['CREATED_BY'], $arFields['RESPONSIBLE_ID']) && ($arFields['CREATED_BY'] != $arFields['RESPONSIBLE_ID'])) {
            require $_SERVER['DOCUMENT_ROOT'] . '/local/vendor/autoload.php';
            $logger = new Logger('default');
            $logger->pushHandler(
                new RotatingFileHandler(
                    $_SERVER['DOCUMENT_ROOT'] . '/local/logs/tasks/mfc.log',
                    30
                )
            );

            $arSubDepartCreater = CIntranetUtils::GetUserDepartments($arFields['CREATED_BY']);
            $arSubDepartRespons = CIntranetUtils::GetUserDepartments($arFields['RESPONSIBLE_ID']);

            $arHighDepartResp = GetParentDepartmentstucture($arFields['RESPONSIBLE_ID']);
            $arHighDepartCreat = GetParentDepartmentstucture($arFields['CREATED_BY']);

            $arGroupsTask = [];
            $arUserGroups = CUser::GetUserGroup($arFields['CREATED_BY']);
            $obMfcTasksGroups = CGroup::GetList ($by = "c_sort", $order = "asc", Array ("STRING_ID" => 'MFC_TASKS|MFC_TASKS_GOD'));
            while ($arItem = $obMfcTasksGroups->GetNext()) {
                if ($sCode = $arItem['STRING_ID']) {
                    $arGroupsTask[$sCode] = $arItem['ID'];
                }
            }

            if (
                isset($arSubDepartCreater[0], $arSubDepartRespons[0])
                && ($arSubDepartCreater[0] == self::$structureSectionId || in_array(self::$structureSectionId, $arHighDepartCreat))
                && ($arSubDepartRespons[0] == self::$structureSectionId || in_array(self::$structureSectionId, $arHighDepartResp))
            ) {
                if (
                    $USER->IsAuthorized() &&
                    $USER->GetID() != $arFields['CREATED_BY'] &&
                    !in_array($arGroupsTask['MFC_TASKS'], $arUserGroups)
                ) {
                    $errorMsg = 'Вы не можете поставить задачу от имени другого пользователя';
                    $logger->info($errorMsg, $arFields);
                    $GLOBALS['APPLICATION']->throwException($errorMsg);
                    return false;
                }

                $arManagersTop = CIntranetUtils::GetDepartmentManager([self::$structureSectionId]);
                $arListDirector = array_shift($arManagersTop)['ID'];
                $arListDirector = [$arListDirector];

                $arListZamDirector = array_filter(
                    [3150, 3614],
                    function ($user_id) {
                        $arDep = CIntranetUtils::GetUserDepartments($user_id);
                        return ($arDep[0] == self::$structureSectionId);
                    }
                );

                $arDepOtdeleiya = CIntranetUtils::getSubDepartments(self::$structureSectionId);
                $arDepOtdeleiya = array_diff($arDepOtdeleiya, [2698, 2650, 2652, 2651]);
                $arListOtdeleiyaGlav = array_column(CIntranetUtils::GetDepartmentManager($arDepOtdeleiya), 'ID');

                $arDepOtdels = CIntranetUtils::getSubDepartments(2698);
                $arListOtdelsGlav = array_column(CIntranetUtils::GetDepartmentManager($arDepOtdels), 'ID');
                array_push($arListOtdelsGlav, 3116, 3317, 2978);

                if (
                    in_array($arFields['CREATED_BY'], $arListDirector) ||
                    in_array($arGroupsTask['MFC_TASKS_GOD'], $arUserGroups)
                ) {
                    return true;
                }

                if (in_array($arFields['RESPONSIBLE_ID'], $arListZamDirector) && !in_array($arFields['CREATED_BY'], $arListDirector)) {
                    $errorMsg = 'Заместителям директора задачи может ставить только директор';
                    $logger->info($errorMsg, $arFields);
                    $GLOBALS['APPLICATION']->throwException($errorMsg);
                    return false;
                } elseif (in_array($arFields['RESPONSIBLE_ID'], $arListDirector) && !in_array($arFields['CREATED_BY'], $arListDirector)) {
                    $errorMsg = 'Вы не можете ставить задачи директору МФЦ';
                    $logger->info($errorMsg, $arFields);
                    $GLOBALS['APPLICATION']->throwException($errorMsg);
                    return false;
                } elseif (in_array($arFields['RESPONSIBLE_ID'], $arListOtdeleiyaGlav)) {
                    if (
                        !in_array($arFields['CREATED_BY'], $arListDirector)
                        && !in_array($arFields['CREATED_BY'], $arListZamDirector)
                        && !in_array($arFields['CREATED_BY'], $arListOtdelsGlav)
                    ) {
                        $errorMsg = 'Вы не можете ставить задачи руководителю отделения МФЦ';
                        $logger->info($errorMsg, $arFields);
                        $GLOBALS['APPLICATION']->throwException($errorMsg);
                        return false;
                    }
                } elseif (in_array($arFields['RESPONSIBLE_ID'], $arListOtdelsGlav)) {
                    if (
                        !in_array($arFields['CREATED_BY'], $arListDirector)
                        && !in_array($arFields['CREATED_BY'], $arListZamDirector)
                        && !in_array($arFields['CREATED_BY'], $arListOtdelsGlav)
                        && !in_array($arFields['CREATED_BY'], $arListOtdeleiyaGlav)
                    ) {
                        $errorMsg = 'Вы не можете ставить задачи руководителю отдела УМФЦ';
                        $logger->info($errorMsg, $arFields);
                        $GLOBALS['APPLICATION']->throwException($errorMsg);
                        return false;
                    }
                } else {
                    $intDepRespons = $arSubDepartRespons[0];
                    $intDepCreater = $arSubDepartCreater[0];
                    $arGlavaID = array_column(CIntranetUtils::GetDepartmentManager([$intDepRespons]), 'ID');

                    $intGlavaID = 0;
                    if (!empty($arGlavaID)) {
                        $intGlavaID = (int)$arGlavaID[0];
                    }

                    if (in_array($intGlavaID, $arListOtdelsGlav) && $arFields['CREATED_BY'] != $intGlavaID) {
                        $errorMsg = 'Задачи сотрудникам отделов УМФЦ может ставить только руководитель отдела';
                        $logger->info($errorMsg, $arFields);
                        $GLOBALS['APPLICATION']->throwException($errorMsg);
                        return false;
                    }

                    if (in_array($intGlavaID, $arListOtdeleiyaGlav) && $arFields['CREATED_BY'] != $intGlavaID) {
                        $errorMsg = 'Задачи сотрудникам отделений может ставить только руководитель отделения';
                        $logger->info($errorMsg, $arFields);
                        $GLOBALS['APPLICATION']->throwException($errorMsg);
                        return false;
                    }

                    if (
                        !in_array($arFields['CREATED_BY'], $arListOtdelsGlav)
                        && !in_array($arFields['CREATED_BY'], $arListOtdeleiyaGlav)
                        && !in_array($arFields['CREATED_BY'], $arListDirector)
                        && !in_array($arFields['CREATED_BY'], $arListZamDirector)
                    ) {
                        $errorMsg = 'Вы не можете ставить задачи другим сотрудникам отделов и отделений МФЦ';
                        $logger->info($errorMsg, $arFields);
                        $GLOBALS['APPLICATION']->throwException($errorMsg);
                        return false;
                    }
                }
            }
        }

        return true;
    }

    /**
     * @param $ID
     * @param $arFields
     * @return bool
     */
    public static function handlerTasksDirectControlAdd(&$arFields)
    {
        return self::_tasksDirectControl($arFields);
    }

    /**
     * @param $ID
     * @param $arFields
     * @return bool
     */
    public static function handlerTasksDirectControlUpdate($ID, &$arFields, &$arTaskCopy)
    {
        return self::_tasksDirectControl($arFields);
    }


    public static function getFormGroupAdmins($WEB_FORM_ID, $PERMISSION_VAL = 20) {
        global $DB;

        $obResult = $DB->Query("SELECT `GROUP_ID` AS `group` FROM `b_form_2_group` WHERE `FORM_ID`='{$WEB_FORM_ID}' AND `PERMISSION`='{$PERMISSION_VAL}'");
        $arListRes = [];
        while($arRes = $obResult->Fetch()) {
            $arListRes[] = $arRes;
        }

        return $arListRes;
    }

    public static function getFormModeratorListForMailer($arPermsList, $arResult, $addDeclar = false) {
        $arUserEmailsAdmin = [];
        if(!empty($arPermsList)) {
            $intGropAdmin = $arPermsList[0]['group'];

            if(is_numeric($intGropAdmin)) {
                $arUserListAdmin = CGroup::GetGroupUser($intGropAdmin);

                if(!empty($arUserListAdmin)) {
                    foreach($arUserListAdmin as $arUserItem) {
                        $arUserEmailsAdmin[] = CUser::GetByID($arUserItem)->Fetch()['EMAIL'];
                    }

                    if($arResult['arResultData']['USER_ID'] && $addDeclar) {
                        $arUserEmailsAdmin[] = CUser::GetByID($arResult['arResultData']['USER_ID'])->Fetch()['EMAIL'];
                    }
                }
            }
        }

        return $arUserEmailsAdmin;
    }
}
