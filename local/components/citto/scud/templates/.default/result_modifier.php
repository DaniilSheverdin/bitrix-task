<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Page\Asset;

global $USER;

Asset::getInstance()->addJs($arResult['TEMPLATE'] . "/js/jquery-1.12.4.min.js");
Asset::getInstance()->addJs($arResult['TEMPLATE'] . "/js/bootstrap4_1.min.js");

$arRequest = $_POST;

$arUserFields = SCUD::getUserFields();

$arEvent = [];
$arViolations = [];
$sChooseUser = 'all';
$bSubUsers = isset($arRequest['subusers']);
$bPodved = isset($arRequest['podved']);
$sUserRole = $arUserFields['role'];
$arStructureIDs = $arUserFields['structureIDs'];
$arUsersIDs = SCUD::getUsersIDs($sUserRole, $arStructureIDs, $arRequest, $bSubUsers);
$arStructure = SCUD::getStructure($sUserRole, $arStructureIDs);
$iUserID = $USER->GetID();

$sPage = SCUD::getPage();
$sExport = $arRequest['export'];

[
    'from' => $iDateFrom,
    'from' => $iRecordFrom,
    'to' => $iDateTo,
    'to' => $iRecordTo
] = SCUD::getCurrentDate();

if ($sPage != 'journal') {
    $iDateFrom = $iDateFrom - 86400;
    $iDateTo = $iDateTo - 86400;
} else {
    $iDateFrom = null;
    $iDateTo = null;
}

if (isset($arRequest['find']) || isset($arRequest['export'])) {
    foreach (['from', 'to', 'absence_from', 'absence_to'] as $iDate) {
        if (isset($arRequest[$iDate])) {
            $arRequest[$iDate] = strtotime($arRequest[$iDate]);
        } else {
            $arRequest[$iDate] = null;
        }
    }

    [
        'fio' => $iUserID,
        'structure' => $sStructureID,
        'from' => $iDateFrom,
        'to' => $iDateTo,
        'absence_from' => $iRecordFrom,
        'absence_to' => $iRecordTo,
        'event' => $arEvent,
        'VIOLATION' => $sViolation,
        'VIOLATION_3' => $sViolations3,
        'VIOLATION_POSITIVE' => $sViolations_positive,
    ] = $arRequest;

    if (isset($sViolation)) {
        array_push($arViolations, $sViolation);
    }
    if (isset($sViolations3)) {
        array_push($arViolations, $sViolations3);
    }
    if (isset($sViolations_positive)) {
        array_push($arViolations, $sViolations_positive);
    }

    if ($iUserID != 'all' && !empty($iUserID)) {
        $sChooseUser = $iUserID;
    }
    if ($sStructureID != 'all' && !empty($sStructureID)) {
        $arStructureIDs = [$sStructureID];
        $sChooseStructure = $sStructureID;
    }

    $arEvent = (empty($arEvent)) ? [] : [$arEvent];
}

$arResult['USERS'] = SCUD::getUsers($arUsersIDs);
$arResult['USERS_SELECT'] = SCUD::getUsersSelect($sUserRole, $arStructureIDs);
$arUsers = ($sChooseUser == 'all') ? $arResult['USERS'] : [$sChooseUser => $arResult['USERS'][$sChooseUser]];

$arResult['ABSENCE'] = SCUD::getAbsence($iDateFrom, $iDateTo, $arUsers, $arViolations, $arEvent, $sPage, $iRecordFrom, $iRecordTo, $sExport);
$arResult['EVENT'] = $arEvent[0];

$arResult['DATETIME'] = [
    'from' => $iDateFrom,
    'to' => $iDateTo,
    'record_from' => $iRecordFrom,
    'record_to' => $iRecordTo
];

foreach ($arResult['DATETIME'] as $sTimePoint => $iDate) {
    if (empty($iDate)) {
        $arResult['DATETIME'][$sTimePoint] = null;
    } else {
        $arResult['DATETIME'][$sTimePoint] = date('d.m.Y H:i:s', $iDate);
    }
}

$arResult['VIOLATION_POSITIVE'] = in_array('VIOLATION_POSITIVE', $arViolations);
$arResult['VIOLATION'] = in_array('VIOLATION', $arViolations);
$arResult['VIOLATION_3'] = in_array('VIOLATION_3', $arViolations);
$arResult['CHOOSE_USER'] = $sChooseUser;
$arResult['CHOOSE_STRUCTURE'] = $sChooseStructure;
$arResult['STRUCTURE'] = $arStructure;
$arResult['ROLE'] = $sUserRole;
$arResult['PAGE'] = $sPage;
$arResult['SUBUSERS'] = $bSubUsers;
$arResult['PODVED'] = $bPodved;

if (SCUD::isUserGugsic($iUserID) ||
    $USER->IsAdmin() ||
    in_array($USER->GetID(), [
        4985, /* директор ГУЗ ТО "ТОМИАЦ" Донец Е.В. */
        6304, /* специалист по кадрам ГУЗ ТО "ТОМИАЦ" Фролова К.А. */
        1801, /* Тамбовская Е. Ю. */
    ])
) {
    $arResult['SEE_ANALYTICS'] = 'Y';
} else {
    $arResult['SEE_ANALYTICS'] = 'N';
}

$arResult["__TEMPLATE_FOLDER"] = $this->__folder;
$this->__component->arResult = $arResult;
