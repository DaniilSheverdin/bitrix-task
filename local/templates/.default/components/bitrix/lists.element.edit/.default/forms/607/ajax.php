<?php

define('NEED_AUTH', true);
define('ED_VYPLATU', "12fa3dc93215b0600c5882223cb12fe8");

use \Bitrix\Main\Loader;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

global $userFields;
$objResp = (object)[
    'status' => "ERROR",
    'status_message' => "",
    'data' => (object)[]
];

try {
    Loader::includeModule('iblock');
    Loader::includeModule('citto.filesigner');

    $arREQUEST = json_decode(file_get_contents('php://input'), true);
    $arSOTRUDNIK = $userFields($USER->GetId());
    $intIBLOCK_ID = $arREQUEST['iblock_id'] ?? null;
    $strTIP_DOLZHNOSTI = $arREQUEST['tip_dolzhnosti'] ?? null;
    $intPROSHU_PREDOSTAVIT = $arREQUEST['proshu_predostavit'] ?? null;
    $arProshuPredostavit = null;


    if ($arREQUEST['sessid'] != bitrix_sessid()) {
        throw new Exception('Ошибка. Обновите страницу');
    }
    if (empty($intIBLOCK_ID)) {
        throw new Exception('IBLOCK_ID не найден');
    }

    if (empty($strTIP_DOLZHNOSTI)) {
        throw new Exception('Заполните "Тип должности"');
    }

    if (empty($intPROSHU_PREDOSTAVIT)) {
        throw new Exception('Заполните "Прошу предоставить"');
    }

    $arProshuPredostavit = CIBlockProperty::GetPropertyEnum('PROSHU_PREDOSTAVIT', [], ['IBLOCK_ID' => $intIBLOCK_ID, 'ID' => (int)$intPROSHU_PREDOSTAVIT])->fetch();
    if (empty($arProshuPredostavit)) {
        throw new Exception('Неверно "Прошу предоставить"');
    }

    if ($arProshuPredostavit['XML_ID'] == ED_VYPLATU) {
        if (empty(trim($arREQUEST['data_prikaza']))) {
            throw new Exception("Введите Дата приказа");
        }
        if (empty(trim($arREQUEST['nomer_prikaza']))) {
            throw new Exception("Введите Номер приказа");
        }
        if (empty(trim($arREQUEST['data_nachala_otpuska']))) {
            throw new Exception("Введите Дата начала отпуска");
        }
    }

    $strSHABLON = \Citto\Filesigner\ShablonyTable::getScalar(
        [
            'filter' => ['=CODE' => $intIBLOCK_ID . "_" . $arProshuPredostavit['ID']],
            'limit' => 1,
            'select' => ['SHABLON']
        ]
    );

    if ($strSHABLON) {
        if ($filesSigned) {
            $objResp->status = "OK";
            $objResp->data->fields = [
                'tsel' => $TSEL,
            ];
        } else {
            $arProps = [
                '#FIO#' => $arSOTRUDNIK['FIO_ROD'],
                '#DOLJNOST#' => $GLOBALS['mb_lcfirst']($arSOTRUDNIK['DOLJNOST_ROD']),
                '#ORGAN#' => $arSOTRUDNIK['DEPARTMENT'],
                '#DATE#' => date('d.m.Y'),
                '#TIP_DOLZHNOSTI#' => $strTIP_DOLZHNOSTI,
                '#PROSHU_PREDOSTAVIT#' => $arProshuPredostavit['VALUE'],
            ];

            $strDoc_content = str_replace(
                array_keys($arProps),
                array_values($arProps),
                $strSHABLON
            );

            $objPdfile = new \Citto\Filesigner\PDFile();
            $objPdfile->setClearf(['#PODPIS1#', '#PODPIS2#']);
            $objPdfile->setName("Заявление.pdf");
            $objPdfile->insert($strDoc_content);
            $objPdfile->save();

            $strSrc = '/podpis-fayla/?' . http_build_query(
                [
                    'FILES' => [$objPdfile->getId()],
                    'POS' => "#PODPIS1#",
                    'CLEARF' => ['#PODPIS1#', '#PODPIS2#'],
                    'sessid' => bitrix_sessid()
                ]
            );

            $objResp->data->location = $strSrc;
            $objResp->data->fields = [
                'zayavlenie_fayl_id' => $objPdfile->getId(),
            ];
            $objResp->status = "REDIRECT";
        }

    } else {
        $objResp->status = "OK";
        $objResp->data->fields = [
            'zayavlenie_fayl_id' => "0"
        ];
    }
} catch (Exception $objExc) {
    $objResp->status_message = $objExc->getMessage();
}
header('Content-Type: application/json');
echo json_encode($objResp);
die;