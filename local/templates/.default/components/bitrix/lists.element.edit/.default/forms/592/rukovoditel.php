<?php

define('NEED_AUTH', true);

require_once $_SERVER['DOCUMENT_ROOT'] .
    '/bitrix/modules/main/include/prolog_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] .
    '/local/vendor/autoload.php';

use \Bitrix\Main\Loader;
use Sprint\Migration\Helpers\IblockHelper;

global $userFields, $APPLICATION;

$helper = new IblockHelper();
$iblockIdOrg = $helper->getIblockId('departments', 'structure');

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
        $arDover = $GLOBALS['userFields']($intSotr);

        $strORGANIZATION_DEP = $arDover['DEPARTMENTS'][0];
        $strORGANIZATION_RUC = $arDover['FIO'];
        $strORGANIZATION_RUC_SHORT = empty($arDover['FIO_INIC_REV']) ? $arDover['FIO_INIC_DAT_REV'] : $arDover['FIO_INIC_REV'];
        $strORGANIZATION_RUC_DOLZHNOST
            = empty($arDover['WORK_POSITION_CLEAR']) ? $arDover['DOLJNOST_CLEAR'] : $arDover['WORK_POSITION_CLEAR'];

        $arrSectDataExt = CIBlockSection::GetList(
            ['SORT' => 'ASC'],
            [
                'ID' => intval($arDover['UF_DEPARTMENT'][0]),
                'GLOBAL_ACTIVE' => 'Y',
                'IBLOCK_ID' => $iblockIdOrg
            ],
            false,
            [
                'UF_ORG_LEGAL_ADDRESS'
            ]
        )->GetNext();

        $strORGANIZATION_Osnovanie = strval($arrSectDataExt['UF_ORG_LEGAL_ADDRESS']);

        $resp->data->deraptmentname = $strORGANIZATION_DEP;
        $resp->data->glava = $strORGANIZATION_RUC;
        $resp->data->glava_short = $strORGANIZATION_RUC_SHORT;
        $resp->data->dolzhnost_glavy = $strORGANIZATION_RUC_DOLZHNOST;
        $resp->data->osnovanie = $strORGANIZATION_Osnovanie;
        $resp->status = 'OK';
    }

} catch (Exception $exc) {
    $resp->status_message = $exc->getMessage();
}

header('Content-Type: application/json');
echo json_encode($resp);
exit;
