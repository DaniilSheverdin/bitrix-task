<?php

namespace HolidayList;

use HolidayList;
use COption;
use CIBlockSection;
use CIntranetUtils;
use CUser;
use CIBlockElement;
use SetPropertyValueCode;

class CEditVacations
{
    public static function setNewVacations($iYear, $arVacations = [])
    {
        $sEmployeeID = $arVacations[0]['EmployeeID'];
        $filter = ["XML_ID" => $sEmployeeID];
        $rsUsers = CUser::GetList(($by=""), ($order=""), $filter);
        $iUserID = 0;
        while ($arUser = $rsUsers->NavNext(true, "f_")) {
            $iUserID = $arUser['ID'];
            break;
        }

        if ($iUserID != 0) {
            $obVacation = new HolidayList\CVacations();
            $arFilter = array(
                'IBLOCK_ID' => IntVal($obVacation->iBlockVacation),
                'PROPERTY_USER' => $iUserID,
                'CODE' => 'VACATION',
                '>=ACTIVE_FROM' => "01.01.$iYear",
                '<=ACTIVE_TO' => "31.12.$iYear",
            );
            $rs = CIBlockElement::GetList($by = array('ACTIVE_FROM' => 'ASC'), $arFilter, false, false, array('*', 'PROPERTY_USER', 'PROPERTY_UF_WHO_APPROVE', 'PROPERTY_ABSENCE_TYPE'));
            while ($arVacation = $rs->GetNext()) {
                CIBlockElement::Delete($arVacation["ID"]);
            }

            $arHolidays = $obVacation->getHolidays()["holydays"];
            function recFunction($while, $DateStart)
            {
                global $arHolidays;
                $while--;
                if (in_array($DateStart, $arHolidays)) {
                    $while++;
                }

                if ($while > 0) {
                    return recFunction($while, $DateStart += 86400);
                } else {
                    return $DateStart;
                }
            }
            $el = new CIBlockElement();
            foreach ($arVacations as $arVacation) {
                $iCount = $arVacation['DaysCount'];
                $DateStart = strtotime(date('Y-m-d', strtotime($arVacation['DateStart'])));
                $DateEnd = strtotime(date('Y-m-d', recFunction($iCount, $DateStart)));
                $el->Add(array(
                    'IBLOCK_ID' => $obVacation->iBlockVacation,
                    'NAME' => 'отпуск ежегодный',
                    'CODE' => 'VACATION',
                    'ACTIVE' => 'Y',
                    'ACTIVE_FROM' => date('d.m.Y', $DateStart),
                    'ACTIVE_TO' => date('d.m.Y', $DateEnd),
                    'DETAIL_TEXT' => '',
                    'PREVIEW_TEXT' => '',
                    'PROPERTY_VALUES' => array(
                        'USER' => $iUserID,
                        'ABSENCE_TYPE' => 8,
                        'UF_WHO_APPROVE' => json_encode([570])
                    ),
                    'MODIFIED_BY' => 570
                ));
            }
        }
    }
}
