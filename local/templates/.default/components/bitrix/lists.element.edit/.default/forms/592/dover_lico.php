<?php

define('NEED_AUTH', true);

require_once $_SERVER['DOCUMENT_ROOT'] .
    '/bitrix/modules/main/include/prolog_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] .
    '/local/vendor/autoload.php';

use \Bitrix\Main\Loader;
use \Citto\Integration\Source1C;

global $userFields, $APPLICATION;

$resp = (object)[
    'status' => 'ERROR',
    'status_message' => '',
    'data' => (object)[]
];

try {
    Loader::includeModule('iblock');
    Loader::includeModule('citto.filesigner');

    $REQUEST = $_REQUEST;
    $strSOTRUDNIK = $REQUEST['userid'];
    preg_match("/(\d+)/i", $strSOTRUDNIK, $arMatches);
    $intSotr = $arMatches[1];

    $arDover = [];
    if ($intSotr > 0) {
        $arDover = CUser::GetByID($intSotr)->Fetch();
    }

    if (!is_null($arDover['UF_SID'])) {
        $objConnect1C = Source1C::Connect1C();

        $arResC1 = Source1C::GetArray(
            $objConnect1C,
            'PersonalData',
            ['EmployeeID' => $arDover['UF_SID']]
        );

        $APPLICATION->RestartBuffer();
        $resp->status = "ERROR";
        if ($arResC1['result'] && $arResC1['Data']['PersonalData']) {
            $arResC1['Data']['PersonalData']['Passport']['DateOfIssue']
                = (new DateTime($arResC1['Data']['PersonalData']['Passport']['DateOfIssue']))
                   ->format('d.m.Y');
            $resp->data->fields->passport
                = (object)$arResC1['Data']['PersonalData']['Passport'];
            $resp->data->fields->placelive
                = (object)$arResC1['Data']['PersonalData']['AddressOfResidence'];
            $resp->status = "OK";
        } else {
            $resp->data->fields = (object)[];
        }
    }

} catch (Exception $exc) {
    $resp->status_message = $exc->getMessage();
}

header('Content-Type: application/json');
echo json_encode($resp);
exit;
