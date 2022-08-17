<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
CModule::IncludeModule("citto.integration");
CBitrixComponent::includeComponentClass("citto:scud");

use Bitrix\Main\Engine\Controller, Bitrix\Highloadblock as HL;

class ScudAjaxController extends Controller
{
    public static function deleteAbsenceAction($sAbsenceID = 'absenceID')
    {
        global $USER;
        $iHlBlockScudID = HL\HighloadBlockTable::getList([
            'filter' => ['=NAME' => 'SCUD']
        ])->fetch()['ID'];
        if (!$iHlBlockScudID) {
            $iHlBlockScudID = (HL\HighloadBlockTable::add(array(
                'NAME' => 'SCUD',
                'TABLE_NAME' => 'tbl_scud',
            )))->getId();
        }
        $obHlblock = HL\HighloadBlockTable::getById($iHlBlockScudID)->fetch();
        $obEntity = HL\HighloadBlockTable::compileEntity($obHlblock);
        $sClassScud = $obEntity->getDataClass();

        $sResult = 'error';
        if ($USER->IsAdmin()) {
            $arTypesAbsence = SCUD::getTypesAbsence();
            $arTypesEvent = SCUD::getEventsList();
            $iAbsenceID = explode('_', $sAbsenceID)[1];

            $obRes = $sClassScud::getList([
                "filter" => ['ID' => $iAbsenceID]
            ])->fetch();
            $arTypeEvent = $arTypesAbsence['ID'][$obRes['UF_TYPE_EVENT']]['XML_ID'];

            if (in_array($arTypeEvent, ['VIOLATION', 'VIOLATION_POSITIVE'])) {
                $sTypeEventValue = $arTypesEvent['ID'][$obRes['UF_EVENT_TOURN']]['XML_ID'];
                $iTypeEventID = $arTypesAbsence['XML_ID'][$sTypeEventValue]['ID'];

                $arData = array(
                    'UF_REASON_ABSENCE'=>'NOT',
                    'UF_TYPE_EVENT' => $iTypeEventID,
                    'UF_USER' => $obRes['UF_USER'],
                    'UF_TOURNIQUET' => $obRes['UF_TOURNIQUET'],
                    'UF_EVENT_TOURN' => $obRes['UF_EVENT_TOURN'],
                    'UF_DATE_CREATE' => $obRes['UF_DATE_CREATE'],
                    'UF_ACTIVE_FROM' => $obRes['UF_ACTIVE_FROM'],
                    'UF_ACTIVE_TO' => $obRes['UF_ACTIVE_TO'],
                );
                if ($sClassScud::update($iAbsenceID, $arData)) {
                    $sResult = 'ok';
                }
            }
        }

        return $sResult;
    }
}
