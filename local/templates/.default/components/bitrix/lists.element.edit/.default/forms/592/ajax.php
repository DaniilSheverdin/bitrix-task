<?php

define('NEED_AUTH', true);
define('HAS_VALID_SIGN_DA', "7ac04da6b38ca6e3bb79f695fae02939");
define('BP_POLUCHAET_LICHNO_DA', "7296da8e18e0538517e5fffd09ec57cf");
define('ZAYAVITEL_RUKOVODITEL', "d5463629c45bee6d0fb86c348b458ea8");

require_once $_SERVER['DOCUMENT_ROOT'] .
    '/bitrix/modules/main/include/prolog_before.php';
require_once $_SERVER['DOCUMENT_ROOT'] .
    '/local/vendor/autoload.php';

use \Bitrix\Main\Loader;
use Bitrix\Main\IO\File;
use Bitrix\DocumentGenerator;

global $userFields;

$resp = (object)[
    'status' => 'ERROR',
    'status_message' => '',
    'data' => (object)[]
];

try {
    Loader::includeModule('iblock');
    Loader::includeModule('citto.filesigner');
    Loader::includeModule('documentgenerator');

    $REQUEST = $_REQUEST;

    $IBLOCK_ID = intval($REQUEST['iblock_id']);
    $iPoruchLichnoID_Yes = CIBlockPropertyEnum::GetList([], ['IBLOCK_ID' => $IBLOCK_ID, 'CODE' => 'POLUCHENIE_LICHNO', 'VALUE' => 'Да'])->fetch()['ID'];
    $arSOTRUDNIK = $userFields($USER->GetId());
    $strFIOSE = $REQUEST['ep_familiya_imya_otchestvo_short'] ?? null;
    $intIBLOCK_ID = $REQUEST['iblock_id'] ?? null;
    $intHAS_VALID_SIGN = $REQUEST['has_valid_sign'] ?? null;
    $intPOLUCHENIE_LICHNO = $iPoruchLichnoID_Yes ?? null;
    $intZAYAVITEL_YAVLYAETSYA_RUKOVODITELEM
        = $REQUEST['zayavitel_yavlyaetsya_rukovoditelem'] ?? null;
    $strRUKOVODITEL = $REQUEST['rukovoditel'] ?? null;
    $strRukShort = $REQUEST['ep_glava_organizatsii_short'] ?? null;

    $strDoverennoeLico = $REQUEST['ep_doverennoe_litso'] ?? null;
    $strNaimenovanieOrganizatsii = $REQUEST['ep_naimenovanie_organizatsii'] ?? null;
    $strGlavaOrganizatsii = $REQUEST['ep_glava_organizatsii'] ?? null;
    $strDolzhnostGlavyOrganizatsii
        = $REQUEST['ep_dolzhnost_glavy_organizatsii'] ?? null;
    $strOsnovanieDeystviyaOrganizatsii
        = $REQUEST['ep_osnovanie_deystviya_organizatsii'] ?? null;
    $strDolzhnostZayavitela
        = $REQUEST['ep_dolzhnost_zayavitelya'] ?? null;
    $strFIO
        = $REQUEST['ep_familiya_imya_otchestvo'] ?? null;
    $strPodrazdeleniya
        = $REQUEST['ep_naimenovanie_podrazdeleniya'] ?? null;
    $strGorod
        = $REQUEST['ep_gorod'] ?? null;
    $strOblast
        = $REQUEST['oblast'] ?? null;
    $strEmail
        = $REQUEST['ep_adres_elektronnoy_pochty'] ?? null;
    $strINN
        = $REQUEST['ep_inn_yuridicheskogo_litsa'] ?? null;
    $strOGRN
        = $REQUEST['ep_ogrn_yuridicheskogo_litsa'] ?? null;
    $strUraddress
        = $REQUEST['ep_yuridicheskiy_adres_organizatsii'] ?? null;
    $strSNILS
        = $REQUEST['ep_snils_vladeltsa_sertifikata'] ?? null;
    $strSeriyaiNomerPassporta
        = $REQUEST['ep_seriya_i_nomer_pasporta'] ?? null;
    $strKemVidanPassport
        = $REQUEST['ep_kem_vydan'] ?? null;
    $strKogdaVidanPassport
        = $REQUEST['ep_kogda_vydan'] ?? null;
    $strAdressProzhivaniya = $REQUEST['ep_adres_prozhivaniya_zayavitelya'] ?? null;

    $strOblastPrimineniya
        = empty($REQUEST['ep_oblast_primeneniya_klyucha_ep']) ? [] : explode(',', $REQUEST['ep_oblast_primeneniya_klyucha_ep']);

    $strDrugieOblastPrimineniya
        = $REQUEST['ep_drugie_oblasti_v_kotorykh_nuzhna_podpis'];

    $strIzgotovlenKlucha = $REQUEST['izgotovlenie_klyuchey_ep_na_sredstvakh_uts'];
    $strUstanovlenoKripto
        = $REQUEST['ep_na_rabochem_meste_polzovatelya_ustanovleno'];

    $strSeriyaNomerPassporDovLitco
        = $REQUEST['ep_seriya_i_nomer_pasporta_dov_litsa'] ?? null;

    $strKemVidanPassporDovLitco
        = $REQUEST['ep_kem_vydan_pasport_dov_litsa'] ?? null;

    $strKogdaVidanPassporDovLitco
        = $REQUEST['ep_kogda_vydan_pasport_dov_litsa'] ?? null;

    $strAdressProzhivanDovLitco
        = $REQUEST['ep_adres_fakticheskogo_prozhivaniya_doverennogo_li'] ?? null;

    $strDataRozhdeniya
        = $REQUEST['data_rozhdeniya'] ?? null;

    $bSoglasie = $REQUEST['ep_soglashenie'] ?? null;

    if (!empty($strDoverennoeLico)) {
        $arDovLico = $GLOBALS['userFields']($strDoverennoeLico);

        $strDoverennoeLico = $arDovLico['FIO'];
        $strDoverennoeLicoShort = empty($arDovLico['FIO_INIC_REV']) ? $arDovLico['FIO_INIC_DAT_REV'] : $arDovLico['FIO_INIC_REV'];
        $strDoverennoeLicoSerPassport = $REQUEST['ep_seriya_i_nomer_pasporta_dov_litsa'] ? mb_substr(str_replace('', ' ', $REQUEST['ep_seriya_i_nomer_pasporta_dov_litsa']), 0, 4) : null;
        $strDoverennoeLicoNumPassport = $REQUEST['ep_seriya_i_nomer_pasporta_dov_litsa'] ? mb_substr(str_replace('', ' ', $REQUEST['ep_seriya_i_nomer_pasporta_dov_litsa']), 4) : null;
        $strDoverennoeLicoKemVidPassport = $REQUEST['ep_kem_vydan_pasport_dov_litsa'] ?? null;
        $strDoverennoeLicoKogdaVidPassport = $REQUEST['ep_kogda_vydan_pasport_dov_litsa'] ?? null;
        $strDoverennoeLicoAddress = $REQUEST['ep_adres_fakticheskogo_prozhivaniya_doverennogo_li'] ?? null;
    }

    $files = [];

    if ($REQUEST['sessid'] != bitrix_sessid()) {
        throw new Exception('Ошибка. Обновите страницу');
    }
    if (empty($intIBLOCK_ID)) {
        throw new Exception('IBLOCK_ID не найден');
    }
    if (empty($intHAS_VALID_SIGN)) {
        throw new Exception('Заполните "Есть действующая ЭП"');
    }

    if (empty($strNaimenovanieOrganizatsii)) {
        throw new Exception('Заполните "Наименование организации"');
    }

    if (empty($strGlavaOrganizatsii)) {
        throw new Exception('Заполните "Глава организации"');
    }

    if (empty($strDolzhnostGlavyOrganizatsii)) {
        throw new Exception('Заполните "Должность главы организации"');
    }

    if (empty($strOsnovanieDeystviyaOrganizatsii)) {
        throw new Exception('Заполните "Основание действия организации"');
    }

    if (empty($strDolzhnostZayavitela)) {
        throw new Exception('Заполните "Должность заявителя"');
    }

    if (empty($strFIO)) {
        throw new Exception('Заполните "Фамилия Имя Отчество"');
    }

    if (empty($strGorod)) {
        throw new Exception('Заполните "Город"');
    }

    if (empty($strOblast)) {
        throw new Exception('Заполните "Область"');
    }

    if (empty($strEmail) || !filter_var($strEmail, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Заполните корректно "Адрес электронной почты"');
    }

    if (empty($strINN) || mb_strlen($strINN) < 10 || mb_strlen($strINN) > 12) {
        throw new Exception('Заполните корректно "ИНН юридического лица"');
    }

    if (empty($strOGRN) || mb_strlen($strOGRN) != 13) {
        throw new Exception('Заполните корректно "ОГРН юридического лица"');
    }

    if (empty($strUraddress)) {
        throw new Exception('Заполните "Юридический адрес организации"');
    }

    if (empty($strSNILS)) {
        throw new Exception('Заполните "СНИЛС владельца сертификата"');
    }

    if (
        empty($strSeriyaiNomerPassporta)
        || mb_strlen($strSeriyaiNomerPassporta) < 10
        || mb_strlen($strSeriyaiNomerPassporta) > 11
    ) {
        throw new Exception('Заполните корректно "Серия и номер паспорта"');
    }

    if (empty($strKemVidanPassport)) {
        throw new Exception('Заполните "Кем выдан"');
    }

    if (empty($strKogdaVidanPassport)) {
        throw new Exception('Заполните "Когда выдан"');
    }

    if (empty($strAdressProzhivaniya)) {
        throw new Exception('Заполните "Адрес проживания заявителя"');
    }

    if (empty($strIzgotovlenKlucha)) {
        throw new Exception('Заполните "Изготовление ключей ЭП на средствах УЦ"');
    }

    if (empty($strUstanovlenoKripto)) {
        throw new Exception('Заполните "На рабочем месте пользователя установлено"');
    }

    if (empty($strDataRozhdeniya)) {
        throw new Exception('Заполните "Дата рождения"');
    }

    if (empty($bSoglasie)) {
        throw new Exception('Подтвердите согласие на обработку персональных данных');
    }

    if(!empty($strOblastPrimineniya)
        && in_array(1618, $strOblastPrimineniya)
        && empty($strDrugieOblastPrimineniya)) {
        throw new Exception('Укажите "Другие области в которых нужна подпись"');
    }

    $objCurrDate = new DateTime();
    $objCurrDate->modify('+396 day');
    $timestampEP = $objCurrDate->getTimestamp();

    $objOBLAST_PRIMENENIYA_KLYUCHA = CIBlockProperty::GetPropertyEnum(
        'EP_OBLAST_PRIMENENIYA_KLYUCHA_EP',
        [],
        [
            'IBLOCK_ID' => $intIBLOCK_ID
        ]
    );

    $arOBLAST_PRIMENENIYA_KLYUCHA = [];
    while ($arOblItem = $objOBLAST_PRIMENENIYA_KLYUCHA->Fetch()) {
        $arOBLAST_PRIMENENIYA_KLYUCHA[] = $arOblItem;
    }

    $arOblastPrimineniyaStr = [];
    foreach ($arOBLAST_PRIMENENIYA_KLYUCHA as $pitem) {
        if (in_array($pitem['ID'], $strOblastPrimineniya)) {
            $arOblastPrimineniya['obl_primen_' . $pitem['ID']] = 'x';
            $arOblastPrimineniyaStr[] = $pitem['VALUE'];
        } else {
            $arOblastPrimineniya['obl_primen_' . $pitem['ID']] = '';
        }
    }

    if (!empty($strDrugieOblastPrimineniya)) {
        $arOblastPrimineniyaStr[] = $strDrugieOblastPrimineniya;
    }

    $objIZGOTOVLENIE_KLYUCHEY = CIBlockProperty::GetPropertyEnum(
        'IZGOTOVLENIE_KLYUCHEY_EP_NA_SREDSTVAKH_UTS',
        [],
        [
            'IBLOCK_ID' => $intIBLOCK_ID
        ]
    );

    $arIZGOTOVLENIE_KLYUCHEY = [];
    while ($arOblItem = $objIZGOTOVLENIE_KLYUCHEY->Fetch()) {
        $arIZGOTOVLENIE_KLYUCHEY[] = $arOblItem;
    }

    foreach ($arIZGOTOVLENIE_KLYUCHEY as $pitem) {
        if (in_array($pitem['ID'], [$strIzgotovlenKlucha])) {
            $arIzgotovlenKlucha['izgotovlenie_kluchey_' . $pitem['ID']] = 'x';
        } else {
            $arIzgotovlenKlucha['izgotovlenie_kluchey_' . $pitem['ID']] = '';
        }
    }

    $objUSTANOVLEN_KLUCHY = CIBlockProperty::GetPropertyEnum(
        'EP_NA_RABOCHEM_MESTE_POLZOVATELYA_USTANOVLENO',
        [],
        [
            'IBLOCK_ID' => $intIBLOCK_ID
        ]
    );

    $arUSTANOVLEN_KLUCHY = [];
    while ($arOblItem = $objUSTANOVLEN_KLUCHY->Fetch()) {
        $arUSTANOVLEN_KLUCHY[] = $arOblItem;
    }

    foreach ($arUSTANOVLEN_KLUCHY as $pitem) {
        if (in_array($pitem['ID'], [$strUstanovlenoKripto])) {
            $arUstanovlenoKripto['ustanov_krypt_' . $pitem['ID']] = 'x';
        } else {
            $arUstanovlenoKripto['ustanov_krypt_' . $pitem['ID']] = '';
        }
    }

    $boolHAS_VALID_SIGN = (bool)CIBlockProperty::GetPropertyEnum(
        'HAS_VALID_SIGN',
        [],
        [
            'IBLOCK_ID' => $intIBLOCK_ID,
            'ID' => (int)$intHAS_VALID_SIGN,
            'EXTERNAL_ID' => HAS_VALID_SIGN_DA
        ]
    )->fetch();

    if (empty($intPOLUCHENIE_LICHNO)) {
        throw new Exception(
            'Заполните "Получение лично"'
        );
    }

    $boolPOLUCHENIE_LICHNO = (bool)CIBlockProperty::GetPropertyEnum(
        'POLUCHENIE_LICHNO',
        [],
        [
            'IBLOCK_ID' => $intIBLOCK_ID,
            'ID' => (int)$intPOLUCHENIE_LICHNO,
            'EXTERNAL_ID' => BP_POLUCHAET_LICHNO_DA
        ]
    )->fetch();


    if (empty($intZAYAVITEL_YAVLYAETSYA_RUKOVODITELEM)) {
        throw new Exception('Заполните "Заявитель является руководителем организации/ОИВ"');
    }

    $boolZAYAVITEL_YAVLYAETSYA_RUKOVODITELEM
        = (bool)CIBlockProperty::GetPropertyEnum(
        'ZAYAVITEL_YAVLYAETSYA_RUKOVODITELEM',
        [],
        [
            'IBLOCK_ID' => $intIBLOCK_ID,
            'ID' => (int)$intZAYAVITEL_YAVLYAETSYA_RUKOVODITELEM,
            'EXTERNAL_ID' => ZAYAVITEL_RUKOVODITEL
        ]
    )
        ->fetch();

    if (!$boolZAYAVITEL_YAVLYAETSYA_RUKOVODITELEM && empty($strRUKOVODITEL)) {
        throw new Exception(
            'Заполните "Укажите руководителя Вашей организации/ОИВ"'
        );
    }

    if (!$boolPOLUCHENIE_LICHNO) {
        if (empty($strDoverennoeLico)) {
            throw new Exception(
                'Заполните "Доверенное лицо"'
            );
        }

        if (empty($strSeriyaNomerPassporDovLitco)
            || mb_strlen($strSeriyaNomerPassporDovLitco) < 10
            || mb_strlen($strSeriyaNomerPassporDovLitco) > 11
        ) {
            throw new Exception(
                'Заполните "Серия и номер паспорта доверенного лица"'
            );
        }

        if (empty($strKemVidanPassporDovLitco)) {
            throw new Exception(
                'Заполните "Кем выдан паспорт доверенного лица"'
            );
        }

        if (empty($strKogdaVidanPassporDovLitco)) {
            throw new Exception(
                'Заполните "Когда выдан паспорт доверенного лица"'
            );
        }

        if (empty($strAdressProzhivanDovLitco)) {
            throw new Exception(
                'Заполните "Адрес фактического проживания доверенного лица"'
            );
        }
    }

    $needZayavlenie = true;
    $needDoverennostSert = (!$boolPOLUCHENIE_LICHNO);
    $needDoverennostUp = (!$boolZAYAVITEL_YAVLYAETSYA_RUKOVODITELEM);

    if ($needZayavlenie) {
        $objFile = new File(
            $_SERVER["DOCUMENT_ROOT"] . '/local/templates_docx/bp/' . $intIBLOCK_ID . '/Zayavka_na_izgotovlenie_sertificata.docx'
        );
        $objBody = new DocumentGenerator\Body\Docx($objFile->getContents());
        $objBody->normalizeContent();

        $arDocument = [
            'NAIMENOVANIE_ORGANIZATSII' => $strNaimenovanieOrganizatsii,
            'DOLZHNOST_GLAVY_ORGANIZATSII' => $strDolzhnostGlavyOrganizatsii,
            'DOLZHNOST_GLAVY_ORGANIZATSII_ROD'
            => $morphFunct($strDolzhnostGlavyOrganizatsii, 'Р'),
            'GLAVA_ORGANIZATSII' => $morphFunct($strGlavaOrganizatsii, 'Р'),
            'GLAVA_ORGANIZATSII_SE' => $strRukShort,
            'OSNOVANIE_DEYSTVIYA_ORGANIZATSII'
            => $morphFunct($strOsnovanieDeystviyaOrganizatsii, 'Р'),
            'DOLZHNOST_ZAYAVITELYA' => $strDolzhnostZayavitela,
            'FAMILIYA_IMYA_OTCHESTVO' => $strFIO,
            'FIO_SE' => $strFIOSE,
            'NAIMENOVANIE_PODRAZDELENIYA' => $strPodrazdeleniya,
            'OBLAST' => $strOblast,
            'GOROD' => $strGorod,
            'ADRES_ELEKTRONNOY_POCHTY' => $strEmail,
            'INN_YURIDICHESKOGO_LITSA' => $strINN,
            'OGRN_YURIDICHESKOGO_LITSA' => $strOGRN,
            'SNILS_VLADELTSA_SERTIFIKATA' => $strSNILS,
            'SER_PASS'
            => mb_substr(str_replace('', ' ', $strSeriyaiNomerPassporta), 0, 4),
            'NUM_PASS'
            => mb_substr(str_replace('', ' ', $strSeriyaiNomerPassporta), 4),
            'KEM_VYDAN' => $strKemVidanPassport,
            'KG_FULL' => date("d.m.Y", strtotime($strKogdaVidanPassport)),
            'YURIDICHESKIY_ADRES_ORGANIZATSII'
            => $strUraddress,
            'OBL_PRIMEN_1615' => $arOblastPrimineniya['obl_primen_1615'],
            'OBL_PRIMEN_1616' => $arOblastPrimineniya['obl_primen_1616'],
            'OBL_PRIMEN_1617' => $arOblastPrimineniya['obl_primen_1617'],
            'OBL_PRIMEN_1614' => $arOblastPrimineniya['obl_primen_1614'],
            'OBL_PRIMEN_1618' => $arOblastPrimineniya['obl_primen_1618'],
            'DRUGIE_OBLASTI_V_KOTORYKH_NUZHNA_PODPIS'
            => $strDrugieOblastPrimineniya,
            'IK_1620' => $arIzgotovlenKlucha['izgotovlenie_kluchey_1620'],
            'IK_1619' => $arIzgotovlenKlucha['izgotovlenie_kluchey_1619'],
            'ES_1622' => $arUstanovlenoKripto['ustanov_krypt_1622'],
            'ES_1621' => $arUstanovlenoKripto['ustanov_krypt_1621'],
            'ADRES_PROZHIVANIYA' => $strAdressProzhivaniya,
            'DATA_ROZHDENIYA' => $strDataRozhdeniya
        ];

        $objBody->setValues($arDocument);
        $objBody->process();
        $strContent = $objBody->getContent();

        $docPath = '/upload/bp/' . $intIBLOCK_ID . '/';
        $strFileName = 'EP_zayavlenie_' . crc32(serialize(microtime())) . '.docx';
        $strPathDoc = $_SERVER['DOCUMENT_ROOT'] . $docPath;
        if (!mkdir($strPathDoc, 0775, true) && !is_dir($strPathDoc)) {
            throw new RuntimeException('Directory "' . $strPathDoc . '" was not created');
        }
        $resCreate = file_put_contents($strPathDoc . $strFileName, $strContent);
        $files['zayavka'] = \CFile::MakeFileArray($strPathDoc . $strFileName);
    }

    if ($needDoverennostUp) {
        $objFile = new File(
            $_SERVER["DOCUMENT_ROOT"] . '/local/templates_docx/bp/' . $intIBLOCK_ID . '/Doverennost_upolnomochennogo_polzovatela.docx'
        );
        $objBody = new DocumentGenerator\Body\Docx($objFile->getContents());
        $objBody->normalizeContent();

        $arDocument = [
            'CR_D' => FormatDate("j", time()),
            'CR_M' => $morphFunct(FormatDate("F", time()), 'Р'),
            'CR_Y' => FormatDate("Y", time()),
            'NAIMENOVANIE_ORGANIZATSII' => $strNaimenovanieOrganizatsii,
            'DOLZHNOST_GLAVY_ORGANIZATSII' => $strDolzhnostGlavyOrganizatsii,
            'DOLZHNOST_GLAVY_ORGANIZATSII_ROD' =>
                $morphFunct($strDolzhnostGlavyOrganizatsii, 'Р'),
            'GLAVA_ORGANIZATSII' => $morphFunct($strGlavaOrganizatsii, 'Р'),
            'GLAVA_ORGANIZATSII_SE' => $strRukShort,
            'YURIDICHESKIY_ADRES_ORGANIZATSII' => $strUraddress,
            'OSNOVANIE_DEYSTVIYA_ORGANIZATSII'
            => $morphFunct($strOsnovanieDeystviyaOrganizatsii, 'Р'),
            'DOLZHNOST_ZAYAVITELYA' => $strDolzhnostZayavitela,
            'FAMILIYA_IMYA_OTCHESTVO' => $strFIO,
            'FIO_SE' => $strFIOSE,
            'SER_NUM_PASS' => $strSeriyaiNomerPassporta,
            'KEM_VYDAN' => $strKemVidanPassport,
            'KG_FULL' => date("d.m.Y", strtotime($strKogdaVidanPassport)),
            'OBLASTY_PRIMENENIYA_KEY' => implode(', ', $arOblastPrimineniyaStr),
            'EPK_D' => FormatDate("j", $timestampEP),
            'EPK_M' => $morphFunct(FormatDate("F", $timestampEP), 'Р'),
            'EPK_Y' => FormatDate("Y", $timestampEP)
        ];

        $objBody->setValues($arDocument);
        $objBody->process();
        $strContent = $objBody->getContent();

        $docPath = '/upload/bp/' . $intIBLOCK_ID . '/';
        $strFileName = 'EP_doverennost_up_' . crc32(serialize(microtime())) . '.docx';
        $strPathDoc = $_SERVER['DOCUMENT_ROOT'] . $docPath;
        if (!mkdir($strPathDoc, 0775, true) && !is_dir($strPathDoc)) {
            throw new RuntimeException('Directory "' . $strPathDoc . '" was not created');
        }
        $resCreate = file_put_contents($strPathDoc . $strFileName, $strContent);
        $files['doverennost'] = \CFile::MakeFileArray($strPathDoc . $strFileName);
    }

    if ($needDoverennostSert) {
        $objFile = new File(
            $_SERVER["DOCUMENT_ROOT"] . '/local/templates_docx/bp/' . $intIBLOCK_ID . '/Doverennost_na_poluchenie_sertificata.docx'
        );
        $objBody = new DocumentGenerator\Body\Docx($objFile->getContents());
        $objBody->normalizeContent();

        $arDocument = [
            'GOROD' => $strGorod,
            'CR_DATE' => (new DateTime())->format("d.m.Y"),
            'NAIMENOVANIE_ORGANIZATSII' => $strNaimenovanieOrganizatsii,
            'DOLZHNOST_GLAVY_ORGANIZATSII' => $strDolzhnostGlavyOrganizatsii,
            'DOLZHNOST_GLAVY_ORGANIZATSII_ROD' =>
                $morphFunct($strDolzhnostGlavyOrganizatsii, 'Р'),
            'GLAVA_ORGANIZATSII' => $morphFunct($strGlavaOrganizatsii, 'Р'),
            'GLAVA_ORGANIZATSII_SE' => $strRukShort,
            'OSNOVANIE_DEYSTVIYA_ORGANIZATSII'
            => $morphFunct($strOsnovanieDeystviyaOrganizatsii, 'Р'),
            'DOVERENNOE_LITSO_FIO' => $strDoverennoeLico,
            'FAMILIYA_IMYA_OTCHESTVO' => $strFIO,
            'DOVERENNOE_LITSO_FIO_SE' => $strDoverennoeLicoShort,
            'SER_PASS' => $strDoverennoeLicoSerPassport,
            'NUM_PASS' => $strDoverennoeLicoNumPassport,
            'KEM_VYDAN' => $strDoverennoeLicoKemVidPassport,
            'KG_D' => FormatDate("j", strtotime($strDoverennoeLicoKogdaVidPassport)),
            'KG_M' => $morphFunct(FormatDate("F", strtotime($strDoverennoeLicoKogdaVidPassport)), 'Р'),
            'KG_Y' => FormatDate("Y", strtotime($strDoverennoeLicoKogdaVidPassport)),
            'EPK_D' => FormatDate("j", $timestampEP),
            'EPK_M' => $morphFunct(FormatDate("F", $timestampEP), 'Р'),
            'EPK_Y' => FormatDate("Y", $timestampEP),
            'ADRES_DOVERENNOE_LITSO' => $strDoverennoeLicoAddress
        ];

        $objBody->setValues($arDocument);
        $objBody->process();
        $strContent = $objBody->getContent();

        $docPath = '/upload/bp/' . $intIBLOCK_ID . '/';
        $strFileName = 'EP_doverennost_getsert_' . crc32(serialize(microtime())) . '.docx';
        $strPathDoc = $_SERVER['DOCUMENT_ROOT'] . $docPath;
        if (!mkdir($strPathDoc, 0775, true) && !is_dir($strPathDoc)) {
            throw new RuntimeException('Directory "' . $strPathDoc . '" was not created');
        }
        $resCreate = file_put_contents($strPathDoc . $strFileName, $strContent);
        $files['doverennost_upolnomochennogo'] = \CFile::MakeFileArray($strPathDoc . $strFileName);
    }

    if ($boolHAS_VALID_SIGN) {
        if ($filesSigned) {
            $resp->status = "OK";
            $resp->data->fields = [
                'iblock_id' => $IBLOCK_ID,
            ];
        } else {

            foreach ($files as &$file) {
                if (empty($file)) {
                    continue;
                }
                $file['MODULE_ID'] = "bp_{$IBLOCK_ID}";
                $file['external_id'] = uniqid(
                    "tosign_" . $IBLOCK_ID . "_" . $SOTRUDNIK['ID'] . "_"
                );
                $file['ID'] = CFile::SaveFile($file, "bp/{$IBLOCK_ID}", true);

                if (empty($file['ID'])) {
                    throw new Exception(
                        "Не удалось сохранить файл: " . htmlentities($file['name'])
                    );
                }
            }
            unset($file);

            $resp->data->fields = array_map(
                function ($file) {
                    return $file['ID'] ?? '';
                },
                $files
            );

            $resp->needRequest = 'Y';

            $src = '/podpis-fayla/?' . http_build_query(
                    [
                        'FILES' => array_values(array_filter($resp->data->fields)),
                        'POS' => "#PODPIS1#",
                        'CLEARF' => ['#PODPIS1#', '#PODPIS2#'],
                        'sessid' => bitrix_sessid()
                    ]
                );

            $resp->data->location = $src;
            $resp->status = "REDIRECT";
        }
    } else {
        $resp->data->fields = [
            'iblock_id' => $IBLOCK_ID
        ];

        foreach ($files as $sFileName => $file) {
            if (empty($file)) {
                continue;
            }

            $file['ID'] = CFile::SaveFile($file, "bp/{$IBLOCK_ID}", true);

            if (empty($file['ID'])) {
                throw new Exception(
                    "Не удалось сохранить файл: " . htmlentities($file['name'])
                );
            } else {
                $resp->data->fields[$sFileName] = $file['ID'];
            }
        }
        $resp->status = "OK";
    }

} catch (Exception $exc) {
    $resp->status_message = $exc->getMessage();
}

header('Content-Type: application/json');
echo json_encode($resp);
exit;
