<?php

namespace HolidayList;

use HolidayList;
use Bitrix\Main\Config\Option;
use CUser;
use CIntranetUtils;
use CIBlockSection;

class CUsers
{
    private static $_instance = null;
    private static $selUserID = null;

    public static function getInstance($arParams = [])
    {
        $_instance = (self::$_instance == null ) ? new self($arParams) : self::$_instance;
        return $_instance;
    }

    public function getUsersCadrs()
    {
        return unserialize(Option::get('bitrix.planner', "STAFF_DEPARTMENT"));
    }

    public function getID()
    {
        global $USER;
        $id = $USER->getID();
        if (isset($_POST['selectDelegation'])) {
            $_SESSION['delegate'] = $_POST['selectDelegation'];
        }
        if (isset($_SESSION['delegate'])) {
            $id = $_SESSION['delegate'];
        }

        return $id;
    }

    public function getSelUserID()
    {
        if (self::$selUserID == null) {
            $this->setSelUserID($this->getID());
        }
        return self::$selUserID;
    }

    public function setSelUserID($id)
    {
        self::$selUserID = $id;
    }

    public function getMyWorkers()
    {
        global $USER;
        $workers = [];
        $myWorkers = $USER->GetList($by = '', $order = '', ['ID' => $this->getID()], ['SELECT' => ['UF_SUBORDINATE']])->getNext()['~UF_SUBORDINATE'];

        if (json_decode($myWorkers) != 0 && count(json_decode($myWorkers)) > 0) {
            $myWorkers = $USER->GetList($by = '', $order = '', ['ID' => implode('|', json_decode($myWorkers)), 'ACTIVE' => 'Y'], ['SELECT' => ['UF_*']]);
            while ($a = $myWorkers->GetNext()) {
                $workers[$a['ID']] = $a;
            }
        }
        return $workers;
    }

    /**
     * @return array
     * Получаем текущих и уволенных руководителей
     */
    public function getMyHeads()
    {
        global $USER;
        $thisHeads = $USER->GetList($by = '', $order = '', ['ID' => $this->getID()], ['SELECT' => ['UF_THIS_HEADS']])->getNext()['UF_THIS_HEADS'];
        $arHeads = [
            'ACTIVE' => [],
            'UNACTIVE' => [],
        ];
        if (isset($thisHeads)) {
            $thisHeads = $USER->GetList($by = '', $order = '', ['ID' => $thisHeads]);
            while ($arItem = $thisHeads->GetNext()) {
                $sFIO = "{$arItem['LAST_NAME']} {$arItem['NAME']} {$arItem['SECOND_NAME']}";
                if ($arItem['ACTIVE'] == 'Y') {
                    $arHeads['ACTIVE'][$arItem['ID']] = $sFIO;
                } else {
                    $arHeads['UNACTIVE'][$arItem['ID']] = $sFIO;
                }
            }
        }
        return $arHeads;
    }

    public function getDelegations()
    {
        global $USER;
        $delegations = [];
        foreach (json_decode(Option::get('bitrix.planner', "DELEGATION_ARR")) as $k => $v) {
            $k = explode('_', $k)[1];
            if ($k == $USER->getID()) {
                foreach ($v as $val) {
                    array_push($delegations, (int)$val);
                }
            }
        }
        if (count($delegations) > 0) {
            $users = implode('|', $delegations);
            $rsUsers = CUser::GetList($by = "last_name", $order = "desc", ['ID' => $users], ['FIELDS' => ['ID', 'LAST_NAME', 'NAME', 'SECOND_NAME']]);
            $delegations = [];
            while ($u = $rsUsers->getNext()) {
                $delegations[$u['ID']] = [$u['LAST_NAME'] . ' ' . $u['NAME'] . ' ' . $u['SECOND_NAME']];
            }
        }
        return $delegations;
    }

    public function getRoles($arDepartmentList = [])
    {
        global $USER;
        $arRoles['ADMIN'] = false;
        $arRoles['TO_CADRS'] = false;
        $arRoles['SECRETARY'] = false;
        $userID = $this->getID();
        $isAdmin = in_array($userID, HolidayList\CUsers::getInstance()->getUsersCadrs()) || $this->arParams['HR_GROUP_ID'] && in_array($this->arParams['HR_GROUP_ID'], $USER->GetUserGroupArray());
        $arRoles = [];
        $departmentID = HolidayList\CStructure::getInstance()->getDepartmentID($userID);

        if (!$isAdmin && $ar = CIntranetUtils::GetSubordinateDepartments($userID, true)) {
            if (in_array($departmentID, $ar)) {
                $arRoles['ADMIN'] = true;
            }
        } else {
            $arRoles['ADMIN'] = true;
        }

        foreach ($arDepartmentList as $id => $department) {
            if ($department['UF_HEAD'] == $userID) {
                $arRoles['ADMIN'] = true;
            }
            if ($isAdmin && $department['DEPTH_LEVEL'] == 3) {
                $arRoles['TO_CADRS'] = true;
            }
            if ($department['SECRETARY']) {
                $arRoles['SECRETARY'] = true;
            }
        }
        return $arRoles;
    }

    public function getUsers(
        $myWorkers = [],
        $isAdmin = false,
        $departmentList = [],
        $depertmentID = null,
        $recursive = false
    ) {
        $rRespone = [];
        $vacations = new CVacations();

        if (empty($myWorkers) || is_array(current($myWorkers))) {
            $employees = CIntranetUtils::GetDepartmentEmployees($depertmentID, $recursive, $bSkipSelf = false);
        } else {
            if (is_array($myWorkers)) {
                $employees = CUser::GetList($by = 'LAST_NAME', $order = 'ASC', ['ID' => implode('|', $myWorkers)], []);
            }
            else {
                $employees = $myWorkers;
            }
        }

        if ($_GET['myworkers'] == 1) {
            $depertmentID .= $this->getID();
            $employees = $myWorkers;
        }

        [
            'xml_ids'           => $xml_ids,
            'xml_ids_revert'    => $xml_ids_revert,
            'users'             => $arUsers
        ] = $this->getXML($employees, $isAdmin, $departmentList);

        if (count($xml_ids) > 0) {
            $rRespone = $vacations->returnResultCache(86400, $depertmentID . $_GET['recursive'], $xml_ids);
        }

        $arUsers = $vacations->getWorksPeriods($xml_ids, $xml_ids_revert, $arUsers, $rRespone);
        $arUsers = $vacations->getPeriods($arUsers, $this->getUsersCadrs());

        return $arUsers;
    }

    public function usersSort($users = [], $departmentIDS = [], $departmentList = [], $sSort = 'ASC')
    {
        $arTmpUsers = [];
        if (empty($_REQUEST['myworkers'])) {
            $arGubernator = []; // губернатор тульской области
            $arPerZamGubPP = []; // председатель правительства
            $arPerZamGub = []; // первый заместитель
            $arZamGubCadrs = []; // начальник главного управления государственной службы и кадров
            $arZamGub = []; // заместитель губернатора
            $arZamPred = []; // заместитель председателя
            $arOrderZams = [
                'arGubernator',
                'arPerZamGubPP',
                'arPerZamGub',
                'arZamGubCadrs',
                'arZamGub',
                'arZamPred',
                'arOthers'
            ];
            $arZamsGub = [];

            $tmpdepartmentIDS = [];
            foreach ($users as $iUserID => $arUser) {
                $sPosition = mb_strtolower($arUser['WORK_POSITION']);
                if (strstr($sPosition, 'губернатор тульской') !== false) {
                    $arGubernator[$iUserID] = $arUser;
                } elseif (strstr($sPosition, 'председатель правительства') !== false) {
                    $arPerZamGubPP[$iUserID] = $arUser;
                } elseif (strstr($sPosition, 'первый заместитель') !== false) {
                    $arPerZamGub[$iUserID] = $arUser;
                } elseif (strstr($sPosition, 'начальник главного управления государственной службы и кадров') !== false) {
                    $arZamGubCadrs[$iUserID] = $arUser;
                } elseif (strstr($sPosition, 'заместитель губернатора') !== false) {
                    $arZamGub[$iUserID] = $arUser;
                } elseif (strstr($sPosition, 'заместитель председателя') !== false) {
                    $arZamPred[$iUserID] = $arUser;
                } else {
                    foreach ($departmentIDS as $id => $department) {
                        if (in_array($id, $arUser['UF_DEPARTMENT'])) {
                            $tmpdepartmentIDS[$id][$iUserID] = $arUser;
                        }
                    }
                }
            }

            foreach ($tmpdepartmentIDS as $idDep => $user) {
                $tmpusers = [];
                foreach ($user as $iduser => $val) {
                    if (is_numeric($iduser) && count($val) > 0) {
                        $tmpusers[$iduser] = $val;
                    }
                }
                if (count($tmpusers) ==  0) {
                    continue;
                }
                uasort($tmpusers, function ($a, $b) {
                    if (isset($a) && isset($b)) {
                        return ($a['LAST_NAME'] > $b['LAST_NAME']) ? 1 : -1;
                    }
                });
                if ($sSort != 'ASC') {
                    $idHead = $departmentList[$idDep]["UF_HEAD"];
                    $tmpHead[$idHead] = $tmpusers[$idHead];
                    unset($tmpusers[$idHead]);
                    $tmpusers = $tmpHead + $tmpusers;
                }

                $arTmpUsers += $tmpusers;
            }

            foreach ($arOrderZams as $sElement) {
                foreach ($$sElement as $iUserID => $arUser) {
                    $arUser['ZAMGUB'] = 'Y';
                    $arZamsGub[$iUserID] = $arUser;
                }
            }

            $arTmpUsers = $arZamsGub + $arTmpUsers;
        } else {
            uasort($users, function ($a, $b) {
                if (isset($a) && isset($b)) {
                    return ($a['LAST_NAME'] > $b['LAST_NAME']) ? 1 : -1;
                }
            });
            $arTmpUsers = $users;
        }

        if ($sSort == 'ASC') {
            uasort($arTmpUsers, function ($a, $b) {
                if (isset($a) && isset($b)) {
                    return ($a['LAST_NAME'] > $b['LAST_NAME']) ? 1 : -1;
                }
            });
        }

        return $arTmpUsers;
    }

    public function canSeeUsers($arDepartmentsID)
    {
        $arDepartments = [];
        $arCanSeeUsers = [];

        foreach ($arDepartmentsID as $iDepartmentID) {
            $res_section = CIBlockSection::GetByID($iDepartmentID); // ID категории ОИВов
            if ($ar_res = $res_section->GetNext()) {
                $parent_sec = [
                    'LEFT_MARGIN' => $ar_res['LEFT_MARGIN'],
                    'RIGHT_MARGIN' => $ar_res['RIGHT_MARGIN']
                ];
            }
            $arFilter = array(
                'IBLOCK_ID' => 5,
                'ACTIVE' => 'Y',
                'LEFT_MARGIN' => $parent_sec['LEFT_MARGIN'],
                'RIGHT_MARGIN' => $parent_sec['RIGHT_MARGIN']
            );
            $rs = CIBlockSection::GetList(
                $arOrder = array('left_margin' => 'asc'),
                $arFilter,
                true,
                array(
                    'UF_HIDEDEP', 'UF_TO_CADRS',
                    'UF_OTV_KADR', 'DEPTH_LEVEL',
                    'LEFT_MARGIN', 'RIGHT_MARGIN',
                    'UF_HEAD', 'UF_COUNT_ALL_EMP',
                    'DEPTH_NAME', 'ID',
                    'IBLOCK_SECTION_ID', 'NAME'
                )
            );
            while ($f = $rs->GetNext()) {
                $arDepartments[ $f['ID'] ] = $f['ID'];
            }

            $data = CUser::GetList(
                ($by="ID"),
                ($order="ASC"),
                ['ACTIVE' => 'Y'],
                [
                    'SELECT' => ['ID', 'UF_DEPARTMENT'],
                    'FIELDS' => ['ID', 'WORK_POSITION']
                ]
            );
            while ($arUser = $data->Fetch()) {
                foreach ($arUser['UF_DEPARTMENT'] as $item) {
                    if (isset($arDepartments[$item])) {
                        array_push($arCanSeeUsers, $arUser['ID']);
                        break;
                    }
                }
            }
        }

        return $arCanSeeUsers;
    }

    public function getXML($employees = [], $isAdmin = false, $departmentList = [])
    {
        global $APPLICATION;
        $arCanSeeUsers = $this->canSeeUsers([57,461]);
        $xml_ids = [];
        $xml_ids_revert = [];
        $arUsers = [];
        $set_user_id = intval($_REQUEST['set_user_id']);

        if (is_object($employees)) {
            while ($f = $employees->Fetch()) {
                if ($isAdmin && $set_user_id) {
                    if ($f['ID'] == $set_user_id) {
                        $this->setSelUserID($set_user_id);
                        $set_user_id = 0;
                    }
                }

                // Если пользователь есть в 1с, то берём его в массив пользователей
                if (in_array($f['ID'], $arCanSeeUsers) || $f['ID'] == 581 || (!empty($f['XML_ID']) && empty(array_diff($f['UF_DEPARTMENT'], array_keys($departmentList))))) {
                    $iLdapId = (!empty($f['XML_ID'])) ? $f['XML_ID'] : $f['UF_SID'];
                    if (empty($iLdapId)) $iLdapId = $f['LOGIN'];
                    array_push($xml_ids, $iLdapId);
                    $xml_ids_revert[$iLdapId] = $f['ID'];
                    $arUsers[ $f['ID'] ] = $f;
                    if ($f['ID'] == $this->getSelUserID()) {
                        $APPLICATION->SetTitle(' [' . $f['LAST_NAME'] . ' ' . $f['NAME'] . ']');
                    }
                }
            }
        }

        if (is_array($employees)) {
            foreach ($employees as $f) {
                if ($isAdmin && $set_user_id) {
                    if ($f['ID'] == $set_user_id) {
                        $this->setSelUserID($set_user_id);
                        $set_user_id = 0;
                    }
                }

                // Если пользователь есть в 1с, то берём его в массив пользователей
                if ($f['ID'] == 581 || (!empty($f['XML_ID'])) || in_array($f['ID'], $arCanSeeUsers)) {
                    $iLdapId = (!empty($f['XML_ID'])) ? $f['XML_ID'] : $f['UF_SID'];
                    if (empty($iLdapId)) $iLdapId = $f['LOGIN'];
                    array_push($xml_ids, $f['XML_ID']);
                    $xml_ids_revert[ $f['XML_ID'] ] = $f['ID'];
                    $arUsers[ $f['ID'] ] = $f;
                    if ($f['ID'] == $this->getSelUserID()) {
                        $APPLICATION->SetTitle(' [' . $f['LAST_NAME'] . ' ' . $f['NAME'] . ']');
                    }
                }
            }
        }

        return [
            'xml_ids'           => $xml_ids,
            'xml_ids_revert'    => $xml_ids_revert,
            'users'             => $arUsers
        ];
    }

    public function getFioApprove($arUsers = [])
    {
        $arFioApprove = [];
        foreach ($arUsers as $f) {
            if (!empty($f['UF_THIS_HEADS'])) {
                $arFioApprove[$f['UF_THIS_HEADS']] = '';
            }
        }

        if (!empty($arFioApprove)) {
            $tmpApprove = implode('|', array_keys($arFioApprove));
            $tmpApprove = CUser::GetList($by = "NAME", $order = "desc", ['ID' => $tmpApprove]);
            $arFioApprove = [];
            while ($f = $tmpApprove->getNext()) {
                $arFioApprove[ $f['ID'] ] = "{$f['LAST_NAME']} {$f['NAME']} {$f['SECOND_NAME']}";
            }
        }

        return $arFioApprove;
    }

    // Массив утвердивших отпуска (id, ФИО)
    public function getListConfirmed($arrConfirmed = [])
    {
        $arListConfirmed = [];
        if ($listConfirmed = implode('|', $arrConfirmed)) {
            $rsUsers = CUser::GetList($by = "NAME", $order = "desc", ['ID' => $listConfirmed]);
            while ($arUser = $rsUsers->Fetch()) {
                $fio = "{$arUser['LAST_NAME']} {$arUser['NAME']} {$arUser['SECOND_NAME']}";
                $arListConfirmed[$arUser['ID']] = $fio;
            }
        }
        return $arListConfirmed;
    }

    // Массив с почтой и id руководителей пользователя
    public function getThisHeads($userinfo = [])
    {
        $arThisHeads = [];
        if (!empty($userinfo["UF_THIS_HEADS"])) {
            $rsUsers = CUser::GetList($by = "NAME", $order = "desc", ['ID' => $userinfo["UF_THIS_HEADS"]]);
            while ($arUser = $rsUsers->Fetch()) {
                $arThisHeads[$arUser['ID']] = $arUser['EMAIL'];
            }
        }
        return $arThisHeads;
    }
}
