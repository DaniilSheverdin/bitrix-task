<?php

namespace HolidayList;

use HolidayList;
use COption;
use CIBlockSection;
use CIntranetUtils;
use CUser;

class CStructure
{
    private static $_instance = null;

    public $arParams;
    public $iBlockStructure;
    public static $arDepartmentList = null;

    public static function getInstance($arParams = [])
    {
        $_instance = (self::$_instance == null) ? new self($arParams) : self::$_instance;
        return $_instance;
    }

    private function __construct($arParams = [])
    {
        $this->iBlockStructure = COption::GetOptionInt('intranet', 'iblock_structure');
        $this->arParams = $arParams;
    }

    private function canSeeDepartments($iUserID, $iDepartmentID)
    {
        $arDepartmens = [];
        $res_section = CIBlockSection::GetByID($iDepartmentID);
        if ($ar_res = $res_section->GetNext()) {
            $parent_sec = ['LEFT_MARGIN' => $ar_res['LEFT_MARGIN'], 'RIGHT_MARGIN' => $ar_res['RIGHT_MARGIN']];
        }
        $arFilter = array('IBLOCK_ID' => 5, 'ACTIVE' => 'Y', 'LEFT_MARGIN' => $parent_sec['LEFT_MARGIN'], 'RIGHT_MARGIN' => $parent_sec['RIGHT_MARGIN']);
        $rs = CIBlockSection::GetList($arOrder = array('left_margin' => 'asc'), $arFilter, true, array('UF_HIDEDEP', 'UF_TO_CADRS', 'UF_OTV_KADR', 'DEPTH_LEVEL', 'LEFT_MARGIN', 'RIGHT_MARGIN', 'UF_HEAD', 'UF_COUNT_ALL_EMP', 'DEPTH_NAME', 'ID', 'IBLOCK_SECTION_ID', 'NAME'));
        while ($f = $rs->GetNext()) {
            $arDepartmens[$f['ID']] = $f['ID'];
        }

        $data = CUser::GetList(($by="ID"), ($order="ASC"), ['ID' => $iUserID], ['SELECT' => ['UF_DEPARTMENT'], 'FIELDS' => ['WORK_POSITION'] ]);
        while ($arUser = $data->Fetch()) {
            foreach ($arUser['UF_DEPARTMENT'] as $item) {
                if (isset($arDepartmens[$item])) {
                    foreach ($arDepartmens as $iDep) {
                        $arDepartmens[$iDep] = $iUserID;
                    }
                    break;
                }
            }
        }

        return $arDepartmens;
    }

    public function getStructure($userID = 0, $departmentIDS = [])
    {
        global $USER;
        $arDepartmentList = [];
        $isAdmin = in_array($userID, HolidayList\CUsers::getInstance()->getUsersCadrs()) || $this->arParams['HR_GROUP_ID'] && in_array($this->arParams['HR_GROUP_ID'], $USER->GetUserGroupArray());
        $isSecretary = false;
        $arSecretaryDeps = [];
        $ID_OIV = COption::GetOptionInt('bitrix.planner', "ID_OIV");
        $res_section = CIBlockSection::GetByID($ID_OIV); // ID категории ОИВов
        if ($ar_res = $res_section->GetNext()) {
            $parent_sec = ['LEFT_MARGIN' => $ar_res['LEFT_MARGIN'], 'RIGHT_MARGIN' => $ar_res['RIGHT_MARGIN']];
        }
        if (!$isAdmin) {
            $arLeftRight = [];
            foreach ($departmentIDS as $id => $department) {
                if ($userID == $department['UF_OTV_KADR']) {
                    $isSecretary = true;
                    $arSecretaryDeps[] = $id;
                    if (empty($arLeftRight)) {
                        $arLeftRight = ['LEFT' => $department['LEFT_MARGIN'], 'RIGHT' => $department['RIGHT_MARGIN']];
                    } else {
                        if ($department['LEFT_MARGIN'] < $arLeftRight['LEFT']) {
                            $arLeftRight['LEFT'] = $department['LEFT_MARGIN'];
                        }
                        if ($department['RIGHT_MARGIN'] > $arLeftRight['RIGHT']) {
                            $arLeftRight['RIGHT'] = $department['RIGHT_MARGIN'];
                        }
                    }
                }
            }
            if ($isSecretary) {
                $parent_sec = [
                    'LEFT_MARGIN' => $arLeftRight['LEFT'],
                    'RIGHT_MARGIN' => $arLeftRight['RIGHT']
                ];
            }
        }

        $arFilter = array(
            'IBLOCK_ID' => $this->iBlockStructure,
            'ACTIVE' => 'Y',
            'LEFT_MARGIN' => $parent_sec['LEFT_MARGIN'],
            'RIGHT_MARGIN' => $parent_sec['RIGHT_MARGIN']
        );

        if (!$isAdmin && !$isSecretary) {
            /*
             * Если Конченко Светлана Михайловна или Карпухина Екатерина Валерьевна
             * зашли под Якушкиной
             * показать только их отдел
             */
            if (
                $userID == 581 &&
                in_array($GLOBALS['USER']->GetID(), [1690, 1569])
            ) {
                $f = CUser::GetList($by = 'ID', $order = 'ASC', array('ID' => $GLOBALS['USER']->GetID()), array('SELECT' => array('UF_DEPARTMENT')))->Fetch();
                if ($this->arParams['SHOW_ALL'] != 'Y') {
                    $arFilter['ID'] = $f['UF_DEPARTMENT'];
                }
            } elseif ($ar = CIntranetUtils::GetSubordinateDepartments($userID, true)) {
                if ($this->arParams['SHOW_ALL'] != 'Y') {
                    $arSecretaryDeps = array_merge(
                        $arSecretaryDeps,
                        $ar,
                        CIntranetUtils::GetUserDepartments($userID)
                    );
                }
            } else {
                $f = CUser::GetList($by = 'ID', $order = 'ASC', array('ID' => $userID), array('SELECT' => array('UF_DEPARTMENT')))->Fetch();
                if ($this->arParams['SHOW_ALL'] != 'Y') {
                    $arFilter['ID'] = $f['UF_DEPARTMENT'];
                }
            }
        }

        if (!empty($arSecretaryDeps)) {
            $arNeedDeps = [];
            foreach ($arSecretaryDeps as $depId) {
                $arNeedDeps[] = $depId;
                $arNeedDeps = array_merge(
                    $arNeedDeps,
                    CIntranetUtils::GetDeparmentsTree($depId, true)
                );
            }
            $arFilter['ID'] = $arNeedDeps;
        }

        $rs = CIBlockSection::GetList(
            $arOrder = array('left_margin' => 'asc'),
            $arFilter,
            true,
            array(
                'LEFT_BORDER', 'RIGHT_BORDER',
                'UF_TO_CADRS', 'UF_HIDEDEP',
                'DEPTH_LEVEL', 'LEFT_MARGIN',
                'RIGHT_MARGIN', 'UF_HEAD',
                'UF_COUNT_ALL_EMP', 'DEPTH_NAME',
                'ID', 'IBLOCK_SECTION_ID', 'NAME',
                'UF_PODVED'
            )
        );
        $UF_HIDEDEP_MARGIN = 0;
        $arCanSeeDepartments = $this->canSeeDepartments($userID, 57);
        $arExcludesDepartmentsPodved = [];
        while ($f = $rs->GetNext()) {

            if ($_REQUEST['podved'] == 'true' && $f['UF_PODVED'] == true) {
                array_push($arExcludesDepartmentsPodved, $f['ID']);
            }

            if ($arCanSeeDepartments[$f['ID']] != $userID) {
                if ($f['UF_HIDEDEP']) {
                    $UF_HIDEDEP_MARGIN = ($f['RIGHT_MARGIN'] - $f['LEFT_MARGIN']);
                    $UF_HIDEDEP_MARGIN = ($UF_HIDEDEP_MARGIN - 1) / 2 + 1;
                }
                if ($UF_HIDEDEP_MARGIN > 0) {
                    $UF_HIDEDEP_MARGIN--;
                    continue;
                }
            }

            $f['DEPTH_NAME'] = str_repeat('. ', ($f['DEPTH_LEVEL'] - 1)) . $f['NAME'];
            $arDepartmentList[$f['ID']] = $f;
            $arDepartmentList[$f['ID']]['SECRETARY'] = ($isSecretary) ? true : false;
        }

        if ($arExcludesDepartmentsPodved) {
            $arExcludesDepartmentsPodved = CIntranetUtils::GetIBlockSectionChildren($arExcludesDepartmentsPodved);
            foreach ($arExcludesDepartmentsPodved as $iDepID) {
                unset($arDepartmentList[$iDepID]);
            }
        }

        return $arDepartmentList;
    }

    public function getDepartmentID($userID = 0, $departmentList = 0)
    {
        $departmentID = key($departmentList);
        $iDepartmentID = (isset($_GET['department'])) ? $_GET['department'] : $departmentID;
        if (empty($_GET['department']) && isset($_GET['recursive'])) {
            $iDepartmentID = $departmentID;
        }
        return $iDepartmentID;
    }

    public function getDepartmentIDS($userID = 0)
    {
        $DEPARTMENT_IDS = [];
        $departmentID = CUser::GetList(($by="ID"), ($order="desc"), array("ID"=>$userID), ['SELECT' => ['UF_*']])->getNext()['UF_DEPARTMENT'][0];
        if (isset($_GET['department'])) {
            $departmentID = $_GET['department'];
        }

        $nav = CIBlockSection::GetNavChain(false, $departmentID);
        while ($n = $nav->getNext()) {
            if ($n['DEPTH_LEVEL'] == 1) {
                $res_section = CIBlockSection::GetByID($n['ID']); // ID категории ОИВов
                if ($ar_res = $res_section->GetNext()) {
                    $parent_sec = ['LEFT_MARGIN' => $ar_res['LEFT_MARGIN'], 'RIGHT_MARGIN' => $ar_res['RIGHT_MARGIN']];
                }
                $arFilter = array(
                    'IBLOCK_ID' => $this->iBlockStructure,
                    'ACTIVE' => 'Y',
                    'LEFT_MARGIN' => $parent_sec['LEFT_MARGIN'],
                    'RIGHT_MARGIN' => $parent_sec['RIGHT_MARGIN'],
                );
                $rs = CIBlockSection::GetList($arOrder = array('left_margin' => 'asc'), $arFilter, true, array('UF_PODVED', 'UF_HIDEDEP', 'UF_TO_CADRS', 'UF_OTV_KADR', 'DEPTH_LEVEL', 'LEFT_MARGIN', 'RIGHT_MARGIN', 'UF_HEAD', 'UF_COUNT_ALL_EMP', 'DEPTH_NAME', 'ID', 'IBLOCK_SECTION_ID', 'NAME'));
                $arExcludesDepartmentsPodved = [];
                while ($f = $rs->GetNext()) {
                    if ($_REQUEST['podved'] == 'true' && $f['UF_PODVED'] == true) {
                        array_push($arExcludesDepartmentsPodved, $f['ID']);
                    }
                    $DEPARTMENT_IDS[$f['ID']] = [
                        'LEFT_MARGIN' => $f['LEFT_MARGIN'],
                        'RIGHT_MARGIN' => $f['RIGHT_MARGIN'],
                        'UF_HIDEDEP' => $f['UF_HIDEDEP'],
                        'UF_OTV_KADR' => $f['UF_OTV_KADR'],
                        'UF_COUNT_ALL_EMP' => $f['UF_COUNT_ALL_EMP'],
                        'UF_TO_CADRS' => json_decode($f['~UF_TO_CADRS']),
                    ];
                }
                if ($arExcludesDepartmentsPodved) {
                    $arExcludesDepartmentsPodved = CIntranetUtils::GetIBlockSectionChildren($arExcludesDepartmentsPodved);
                    foreach ($arExcludesDepartmentsPodved as $iDepID) {
                        unset($DEPARTMENT_IDS[$iDepID]);
                    }
                }
                break;
            }
        }
        return $DEPARTMENT_IDS;
    }

    public function getRecursive($boolAdmin)
    {
        if (!$boolAdmin) {
            return 0;
        } else {
            return ($_REQUEST['recursive']) ? 1 : 0;
        }
    }
}
