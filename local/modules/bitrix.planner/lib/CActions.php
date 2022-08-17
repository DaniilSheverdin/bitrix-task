<?php

namespace HolidayList;

use HolidayList;
use COption;
use CIBlockSection;
use CIntranetUtils;
use CUser;
use CIBlockElement;
use SetPropertyValueCode;

class CActions
{
    private $users;
    private $vacations;
    private $structure;

    private $baseURL;
    private $arUsers;
    private $userID;
    private $bInCadrs;
    private $arRoles;

    public function __construct($arParams, $baseURL = '', $arRoles = [], $arUsers = [], $bInCadrs = false)
    {
        $this->vacations = new HolidayList\CVacations();
        $this->users = HolidayList\CUsers::getInstance();
        $this->structure = HolidayList\CStructure::getInstance($arParams);

        $this->baseURL = $baseURL;
        $this->arRoles = $arRoles;
        $this->arUsers = $arUsers;
        $this->userID = $this->users->getID();
        $this->bInCadrs = $bInCadrs;
    }

    public function run()
    {
        if (!empty($_REQUEST['action'])) {
            $action = $_REQUEST['action'];
            $this->$action();
        }
    }

    public function add()
    {
    }

    public function edit()
    {
    }

    public function adminapprove()
    {
    }

    public function approve()
    {
//        global $APPLICATION, $USER;
//
//        $reqId = json_decode($_REQUEST['id']);
//        $rs = CIBlockElement::GetList($by = array('ACTIVE_FROM' => 'ASC'), $arFilter = array('IBLOCK_ID' => $this->vacations->iBlockVacation, 'ID' => $reqId), false, false, array('*', 'PROPERTY_USER', 'PROPERTY_UF_WHO_APPROVE', 'PROPERTY_ABSENCE_TYPE'));
//        if (isset($rs) && !empty($reqId)) {
//            while ($f = $rs->Fetch()) {
//                $arHeads = explode('|', $this->arUsers[$f['PROPERTY_USER_VALUE']]['UF_THIS_HEADS']);
//                if (in_array($this->userID, $arHeads) || $arResult['USERCADRS'] || $arResult['SECRETARY']) {
//                    $el = new CIBlockElement;
//
//                    if (($_REQUEST['action'] == 'adminapprove' && $USER->IsAdmin()) || $arResult['SECRETARY']) {
//                        $el->Update($f['ID'], array('MODIFIED_BY' => $arResult['USER_ID'], 'ACTIVE' => 'Y'));
//                        continue;
//                    }
//
//                    $boolInCadrs = $arResult['USERCADRS'];
//                    $active = 'N';
//                    if (isset($f["PROPERTY_UF_WHO_APPROVE_VALUE"])) {
//                        $whoApprove = json_decode($f["PROPERTY_UF_WHO_APPROVE_VALUE"]);
//                        if (!in_array($arResult['USER_ID'], $whoApprove)) {
//                            $active = 'Y';
//                            array_push($whoApprove, $arResult['USER_ID']);
//                            foreach ($arHeads as $val) {
//                                if (!in_array($val, $whoApprove)) $active = 'N';
//                            }
//                            $active = ($boolInCadrs) ? 'Y' : $active;
//                        } else continue;
//                        $whoApprove = array_values($whoApprove);
//
//                        $el->Update($f['ID'], array('MODIFIED_BY' => $arResult['USER_ID'], 'ACTIVE' => $active));
//                        $whoApprove = json_encode($whoApprove);
//                        CIBlockElement::SetPropertyValueCode($f['ID'], "UF_WHO_APPROVE", $whoApprove);
//                    } else {
//                        $whoApprove = json_encode([$arResult['USER_ID']]);
//                        CIBlockElement::SetPropertyValueCode($f['ID'], "UF_WHO_APPROVE", $whoApprove);
//                        $active = ((count($arHeads) == 1 || $boolInCadrs) ? 'Y' : 'N');
//                        $el->Update($f['ID'], array('MODIFIED_BY' => $arResult['USER_ID'], 'ACTIVE' => $active));
//                    }
//
//                    $uid = intval($f['PROPERTY_USER_VALUE']);
//                    $email = $this->arUsers[$uid]["EMAIL"];
//                    if ($active == 'Y') {
//                        $vacations->ImNotify($arResult['USER_ID'], $uid, GetMessage("BITRIX_PLANNER_PODTVERJDENO") . $name, GetMessage("BITRIX_PLANNER_PODTVERJDNO") . $name . ' [' . $f['ACTIVE_FROM'] . ' - ' . $f['ACTIVE_TO'] . ']', $email);
//                    }
//                } else
//                    $arResult['ERROR'] = GetMessage("BITRIX_PLANNER_NET_PRAV_NA_OPERACIU");
//            }
//            LocalRedirect($APPLICATION->GetCurPage() . $arResult['BASE_URL']);
//        } else
//            $arResult['ERROR'] = GetMessage("BITRIX_PLANNER_ZAPISQ_NE_NAYDENA");
    }

    public function unapprove()
    {
        global $APPLICATION;
        $reqId = json_decode($_REQUEST['id']);
        $arTypes = $this->vacations->getAbsences($this->vacations->iBlockVacation)['TYPES'];
        $sComment = ($_REQUEST['comment']) ? $_REQUEST['comment'] : '';

        $rs = CIBlockElement::GetList(
            $by = array('ACTIVE_FROM' => 'ASC'),
            $arFilter = array('IBLOCK_ID' => $this->vacations->iBlockVacation, 'ID' => $reqId),
            false,
            false,
            array('*', 'PROPERTY_USER', 'PROPERTY_UF_WHO_APPROVE', 'PROPERTY_ABSENCE_TYPE')
        );
        if (isset($rs) && !empty($reqId)) {
            while ($f = $rs->Fetch()) {
                $arHeads = explode('|', $this->arUsers[ $f['PROPERTY_USER_VALUE'] ]['UF_THIS_HEADS']);
                if (
                    in_array($this->userID, $arHeads) ||
                    $this->userID == 33 ||
                    $this->bInCadrs ||
                    $this->arRoles['SECRETARY']
                ) {
                    $el = new CIBlockElement();
                    $el->Update($f['ID'], array('MODIFIED_BY' => $this->userID, 'ACTIVE' => 'N', 'PREVIEW_TEXT' => $sComment));

                    if (isset($f["PROPERTY_UF_WHO_APPROVE_VALUE"])) {
                        $whoApprove = json_decode($f["PROPERTY_UF_WHO_APPROVE_VALUE"]);
                        if (
                            $this->bInCadrs ||
                            in_array($this->userID, $whoApprove) ||
                            $this->userID == 33
                        ) {
                            $arUsDep = $this->arUsers[$f['PROPERTY_USER_VALUE']]["UF_DEPARTMENT"];
                            $arUsDep = CIBlockSection::GetList(
                                "",
                                ['IBLOCK_ID' => COption::GetOptionInt('intranet', 'iblock_structure'), 'ID' => $arUsDep],
                                false,
                                ['ID', 'IBLOCK_ID', 'UF_TO_CADRS']
                            );
                            while ($a = $arUsDep->getNext()) {
                                $jsonCadrs = json_decode($a["~UF_TO_CADRS"]);
                                if (isset($jsonCadrs)) {
                                    $yorn = [];
                                    foreach ($jsonCadrs as $k => $v) {
                                        if ($v != $this->vacations->year) {
                                            $yorn[$k] = $v;
                                        }
                                    }
                                    $yorn[$this->vacations->year] = 'N';
                                } else {
                                    $yorn = [$this->vacations->year => 'N'];
                                }
                                $yorn = json_encode($yorn);
                                $el_dep = new CIBlockSection();
                                $el_dep->Update($a['ID'], array('UF_TO_CADRS' => $yorn));
                            }
                        }

                        if ($this->bInCadrs || $this->userID == 33) {
                            $whoApprove = [];
                        } elseif (in_array($this->userID, $whoApprove)) {
                            unset($whoApprove[array_search($this->userID, $whoApprove)]);
                        } else {
                            continue;
                        }
                        $whoApprove = array_values($whoApprove);

                        $el->Update($f['ID'], array('MODIFIED_BY' => $this->userID, 'ACTIVE' => 'N', 'PREVIEW_TEXT' => $sComment));
                        $whoApprove = json_encode($whoApprove);
                        CIBlockElement::SetPropertyValueCode($f['ID'], "UF_WHO_APPROVE", $whoApprove);
                    } else {
                        $arUsDep = $this->arUsers[$f['PROPERTY_USER_VALUE']]["UF_DEPARTMENT"];
                        $arUsDep = CIBlockSection::GetList("", ['IBLOCK_ID' => $this->vacations->iBlockVacation, 'ID' => $arUsDep], false, ['ID', 'IBLOCK_ID', 'UF_TO_CADRS']);
                        while ($a = $arUsDep->getNext()) {
                            $jsonCadrs = json_decode($a["~UF_TO_CADRS"]);
                            if (isset($jsonCadrs)) {
                                $yorn = [];
                                foreach ($jsonCadrs as $k => $v) {
                                    if ($v != $this->vacations->year) {
                                        $yorn[$k] = $v;
                                    }
                                }
                                $yorn[$this->vacations->year] = 'N';
                            } else {
                                $yorn = [$this->vacations->year => 'N'];
                            }
                            $yorn = json_encode($yorn);
                            $el_dep = new CIBlockSection();
                            $el_dep->Update($a['ID'], array('UF_TO_CADRS' => $yorn));
                        }
                        $el->Update($f['ID'], array('MODIFIED_BY' => $this->userID, 'ACTIVE' => 'N', 'PREVIEW_TEXT' => $sComment));
                    }

                    $uid = intval($f['PROPERTY_USER_VALUE']);
                    $email = $this->arUsers[$uid]["EMAIL"];

                    $this->vacations->ImNotify(
                        $this->userID,
                        $uid,
                        GetMessage("BITRIX_PLANNER_PODTVERJDENO") . $arTypes[$f['CODE']],
                        'Снято подтверждение:' . $arTypes[$f['CODE']] . ' [' . $f['ACTIVE_FROM'] . ' - ' . $f['ACTIVE_TO'] . ']', $email
                    );
                } else {
                    $arResult['ERROR'] = GetMessage("BITRIX_PLANNER_NET_PRAV_NA_OPERACIU");
                }
            }
            LocalRedirect($APPLICATION->GetCurPage() . $this->baseURL);
        } else {
            $arResult['ERROR'] = GetMessage("BITRIX_PLANNER_ZAPISQ_NE_NAYDENA");
        }
    }

    public function delete()
    {
        global $APPLICATION;
        $iBlockVacation = $this->vacations->iBlockVacation;
        $arTypes = $this->vacations->getAbsences($iBlockVacation)['TYPES'];

        $arDelIDs = json_decode($_REQUEST['id']);
        foreach ($arDelIDs as $item) {
            $rs = CIBlockElement::GetList($by = array('ACTIVE_FROM' => 'ASC'), $arFilter = array('IBLOCK_ID' => $iBlockVacation, 'ACTIVE' => 'N', 'ID' => $item), false, false, array('*', 'PROPERTY_USER', 'PROPERTY_ABSENCE_TYPE'));
            while ($f = $rs->Fetch()) {
                $userID = intval($f['PROPERTY_USER_VALUE']);
                if ($userID == $this->userID || $this->arRoles['ADMIN']) {
                    CIBlockElement::Delete($f['ID']);
                    $this->vacations->ImNotify($this->userID, $userID, GetMessage("BITRIX_PLANNER_ZAPISQ_UDALENA"), GetMessage("BITRIX_PLANNER_ZAPISQ_UDALENA1") . $arTypes[$f['CODE']] . ' [' . $f['ACTIVE_FROM'] . ' - ' . $f['ACTIVE_TO'] . ']', $this->arUsers[$userID]['EMAIL']);
                }
            }
        }
        LocalRedirect($APPLICATION->GetCurPage() . $this->baseURL);
    }
}
