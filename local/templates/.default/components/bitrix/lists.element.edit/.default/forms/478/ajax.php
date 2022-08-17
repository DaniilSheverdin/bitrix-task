<?php
define('NEED_AUTH', true);
define('PRICHINA_INOE', "e82962cfdd7c090e480813453653118c");
define('PRICHINA_SOPR', "97a8321184f8054a8dbe54562de88911");
define('PRICHINA_NET', "fde414306bb4fb8f8a190684f21b63c3");
define('ZAP_SAM_DA', "1178");
define('ZAP_SAM_NET', "1179");
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

global $userFields, $getUserOtvpoKadram, $USER;
$resp = (object)['status' => "ERROR", 'status_message' => "", 'data' => (object)[]];
try {
    \Bitrix\Main\Loader::includeModule('iblock');
    \Bitrix\Main\Loader::includeModule('citto.filesigner');
    \Bitrix\Main\Loader::includeModule('intranet');
    $REQUEST = json_decode(file_get_contents('php://input'), true);

    $IBLOCK_ID = $REQUEST['iblock_id'] ?? null;
    $OTPUSK__FROM = $REQUEST['otpusk__from'] ?? null;
    $OTPUSK__DAYS = $REQUEST['otpusk__days'] ?? null;
    $RUKOVODITEL_OIV = $REQUEST['rukovoditel_oiv'] ?? null;
    $PRICHINA_V_SVYAZI_S = $REQUEST['prichina_v_svyazi_s'] ?? null;
    $PRICHINA = $REQUEST['prichina'] ?? null;
    $filesSigned = $REQUEST['filesSigned'] ?? null;
    $is_uvedomlenie = $REQUEST['is_uvedomlenie'] ?? null;
    $SOTRUDNIK = $userFields($USER->GetId());
    $SHABLON = null;
    $prichinaVSvyaziS = null;
    $rukovoditelOiv = null;

    $IBLOCK_ID = (int)$IBLOCK_ID;
    $OTPUSK__FROM = DateTime::createFromFormat('d.m.Y', $OTPUSK__FROM);
    $OTPUSK__DAYS = (int)$OTPUSK__DAYS;
    $RUKOVODITEL_OIV = (int)$RUKOVODITEL_OIV;

    $arDepartmentsJudge = CIntranetUtils::GetIBlockSectionChildren(2229);
    $el = new CIBlockElement;
    $arUsersJudge = [];
    $obUsers = CUser::GetList($by = 'LAST_NAME', $order = 'ASC', ['ID' => $USER->GetId()], ['SELECT' => ['UF_DEPARTMENT']]);
    while ($arUser = $obUsers->getNext()) {
        foreach ($arUser['UF_DEPARTMENT'] as $iDepartID) {
            if (in_array($iDepartID, $arDepartmentsJudge)) {
                array_push($arUsersJudge, $arUser['ID']);
            }
        }
    }
    $bIsJudge = (in_array($USER->GetId(), $arUsersJudge));

    if ($REQUEST['sessid'] != bitrix_sessid()) {
        throw new Exception('Ошибка. Обновите страницу');
    }
    if (!$IBLOCK_ID) {
        throw new Exception('IBLOCK_ID не найден');
    }
    if ($OTPUSK__DAYS < 1) {
        throw new Exception('Заполните "Длительность(количество дней)"');
    }
    if (!$OTPUSK__FROM) {
        throw new Exception('Укажите "Дата начала"');
    }
    if ($OTPUSK__FROM->format('U') < strtotime("+0 day")) {
        throw new Exception('Укажите правильную "Дата начала"');
    }
    if (!$RUKOVODITEL_OIV) {
        throw new Exception('Укажите "Руководитель организации/ОИВ"');
    }

    $rukovoditelOiv = $userFields($RUKOVODITEL_OIV);

    if (!$is_uvedomlenie) {
        if (empty($PRICHINA_V_SVYAZI_S)) {
            throw new Exception('Заполните "Причина (в связи с)"');
        }

        $prichinaVSvyaziS = CIBlockProperty::GetPropertyEnum(
            'PRICHINA_V_SVYAZI_S',
            [],
            [
                'IBLOCK_ID' => $IBLOCK_ID,
                'ID' => $PRICHINA_V_SVYAZI_S
            ]
        )->fetch();

        if (empty($prichinaVSvyaziS)) {
            throw new Exception('Неверно "Причина (в связи с)"');
        }

        if (in_array($prichinaVSvyaziS['XML_ID'], [PRICHINA_INOE, PRICHINA_SOPR])) {
            if (empty($PRICHINA)) {
                throw new Exception('Заполните "Укажите подробнее причину"');
            }
        } else {
            if ($prichinaVSvyaziS['XML_ID'] == PRICHINA_NET) {
                $PRICHINA = '';
            } else {
                $PRICHINA = $prichinaVSvyaziS['VALUE'];
            }
        }
    }

    if ($filesSigned) {
        $resp->status = "OK";
        $resp->data->fields = [
            'prichina' => $PRICHINA,
        ];
    } else {
        $SHABLON = \Citto\Filesigner\ShablonyTable::getScalar(
            [
                'filter' => ['=CODE' => $IBLOCK_ID . ($is_uvedomlenie ? "_uvedomlenie" : "_zayavlenie")],
                'limit' => 1,
                'select' => ['SHABLON']
            ]
        );
        $arFields = [
            '#DATE#'                        => date('d.m.Y'),
            '#SOTRUDNIK__DOLJNOST_DAT#'     => $mb_ucfirst($SOTRUDNIK['DOLJNOST_DAT']),
            '#SOTRUDNIK__DOLJNOST_ROD#'     => $mb_ucfirst($SOTRUDNIK['DOLJNOST_ROD']),
            '#SOTRUDNIK__FIO_ROD#'          => $SOTRUDNIK['FIO_ROD'],
            '#SOTRUDNIK__FIO_INIC_DAT#'     => $SOTRUDNIK['FIO_INIC_DAT'],
            '#SOTRUDNIK__FIO_INIC_DAT_REV#' => $SOTRUDNIK['FIO_INIC_DAT_REV'],
            '#SOTRUDNIK__OBRASHENIE#'       => $SOTRUDNIK['OBRASHENIE'],
            '#SOTRUDNIK__UVEDOMLEN#'        => $SOTRUDNIK['UVEDOMLEN'],
            '#SOTRUDNIK__FIRST_NAME#'       => $SOTRUDNIK['FIRST_NAME'],
            '#SOTRUDNIK__MIDDLE_NAME#'      => $SOTRUDNIK['MIDDLE_NAME'],
            '#SOTRUDNIK__FIO#'              => $SOTRUDNIK['FIO'],
            '#OTPUSK__FROM#'                => $OTPUSK__FROM->format('d.m.Y'),
            '#OTPUSK__DAYS#'                => $OTPUSK__DAYS . " " . $declOfNum($OTPUSK__DAYS, ['календарный день', 'календарных дня', 'календарных дней']),
            '#RUKOVODITEL_DOLJNOST#'        => $mb_ucfirst($rukovoditelOiv['UF_WORK_POSITION']),
            '#RUKOVODITEL_FIO_INICT#'       => $rukovoditelOiv['FIO_INIC'],
            '#PRICHINA_V_SVYAZI_S#'         => ($PRICHINA ? ', ' . htmlentities($PRICHINA) : '') . '.',
        ];

        /*if ($bIsJudge) {
            $arFields['#RUKOVODITEL_OIV#'] = '';
            if ($is_uvedomlenie) {
                $arFields['#CADR_DOLJNOST#'] = 'Начальник отдела<br>контрольно-организационной,<br>аналитической и кадровой работы<br>комитета по делам записи актов<br>гражданского состояния и обеспечению деятельности<br>мировых судей в Тульской области';
                $arFields['#CADR_FIO#'] = 'И.С. Иванова';
            }
            else {
                $arFields['#HEADER_DOLJNOST#'] = 'Председателю комитета<br>по делам записи актов гражданского состояния<br>и обеспечению деятельности мировых судей<br>в Тульской области';
                $arFields['#HEADER_FIO#'] = 'Абросимовой Т.А.';
            }
        }
        else {*/
        $arFields['#RUKOVODITEL_OIV#'] = '
        <table style="overflow: hidden;margin-bottom: 30px;width:500px;font-size: 14px">
            <tr>
                <td style="width:250px;line-height: normal;text-align: center;float: left;">
                    <b>'.$mb_ucfirst($rukovoditelOiv['UF_WORK_POSITION']).'</b>
                </td>
                <td style="text-align: right;line-height: normal;vertical-align: bottom;">
                    <b>'.$rukovoditelOiv['FIO_INIC'].'</b>
                </td>
            </tr>
        </table>';

        if ($is_uvedomlenie) {
            $arFields['#CADR_DOLJNOST#'] = 'Консультант отдела кадров<br/>главного управления<br/>государственной службы и кадров<br/>аппарата Правительства Тульской области';
            $arFields['#CADR_FIO#'] = 'Е.С. Балашова';
        }
        else {
            $arFields['#HEADER_DOLJNOST#'] = 'Заместителю Губернатора<br>Тульской области – руководителю<br>аппарата Правительства Тульской<br>области – начальнику главного<br>управления государственной<br>службы и кадров аппарата<br>Правительства Тульской области';
            $arFields['#HEADER_FIO#'] = 'Якушкиной Г.И.';
        }
        /*}*/

        $doc_content = str_replace(
            array_keys($arFields),
            array_values($arFields),
            $SHABLON
        );

        $pdfile1 = new \Citto\Filesigner\PDFile();
        $pdfile1->setClearf(['#PODPIS1#', '#PODPIS2#']);
        $pdfile1->setName(($is_uvedomlenie ? "Уведомление об отпуске " : "Заявление на отпуск ") . $SOTRUDNIK['FIO_INIC']);
        $pdfile1->insert($doc_content);
        $pdfile1->save();

        $src = '/podpis-fayla/?' . http_build_query(
            [
                'FILES' => [$pdfile1->getId()],
                'POS' => "#PODPIS1#",
                'CLEARF' => ['#PODPIS1#', '#PODPIS2#'],
                'sessid' => bitrix_sessid()
            ]
        );
        $resp->data->location = $src;
        $resp->data->fields = [
            'prichina' => $PRICHINA,
            'zayavlenie_fayl_id' => $pdfile1->getId(),
            'zapushcheno_samostoyatelno' => $is_uvedomlenie ? ZAP_SAM_NET : ZAP_SAM_DA,
            'otvetstvenny_oiv' => $getUserOtvpoKadram($SOTRUDNIK['ID']) ?: ""
        ];
        $resp->status = "REDIRECT";
    }
} catch (Exception $exc) {
    $resp->status_message = $exc->getMessage();
}

header('Content-Type: application/json');
echo json_encode($resp);
die;