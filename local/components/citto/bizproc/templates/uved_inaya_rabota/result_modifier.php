<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;

global $APPLICATION, $USER, $userFields, $morphFunct;

$arUserFields = $userFields;
$arRequest = $arResult['REQUEST'];
Loader::includeModule("iblock");
Loader::includeModule('citto.filesigner');
CJSCore::Init(["date"]);

$APPLICATION->SetTitle("Уведомление об иной оплачиваемой работе");

Asset::getInstance()->addJs('/bitrix/templates/.default/jquery.min.js');
Asset::getInstance()->addJs("/local/js/bootstrap-plugin/popper.min.js");
Asset::getInstance()->addJs('/bitrix/templates/.default/bootstrap.min.js');
Asset::getInstance()->addJs('/' . basename(__DIR__) . '/index.js');

Asset::getInstance()->addJs('/local/activities/custom/docsignactivity/js/es6-promise.min.js');
Asset::getInstance()->addJs('/local/activities/custom/docsignactivity/js/ie_eventlistner_polyfill.js');
Asset::getInstance()->addJs("/local/activities/custom/docsignactivity/js/cadesplugin_api.js");
Asset::getInstance()->addJs("/local/activities/custom/docsignactivity/js/plugin.js");
Asset::getInstance()->addJs("/local/components/citto/bizproc/js/docsignactivity.js");
Asset::getInstance()->addJs('/local/js/bootstrap-plugin/bootstrap-select.min.js');


$APPLICATION->SetAdditionalCSS('/local/css/bootstrap-plugin/bootstrap-select.min.css');
$APPLICATION->SetAdditionalCSS('/bitrix/templates/.default/bootstrap.min.css');
$APPLICATION->SetAdditionalCSS('/' . basename(__DIR__) . '/main.css');

$arUser = $arUserFields($USER->GetID());

$arResult['VID_DEYATELNOSTI'] = [];
$arDeyat = CIBlockProperty::GetPropertyEnum('VID_DEYATELNOSTI', ['SORT' => 'asc'], ['IBLOCK_ID' => $arParams['ID_BIZPROC']]);
while ($arDeyatItem = $arDeyat->GetNext()) {
    $arResult['VID_DEYATELNOSTI'][$arDeyatItem['ID']] = [
        'ID' => $arDeyatItem['ID'],
        'NAME' => $arDeyatItem['VALUE']
    ];
}

$arResult['OSNOVANIE'] = [];
$arOsn = CIBlockProperty::GetPropertyEnum('OSNOVANIE_VOZNIKNOVENIYA_PRAVOVYKH_OTNOSHENIY', ['SORT' => 'asc'], ['IBLOCK_ID' => $arParams['ID_BIZPROC']]);
while ($arOsnItem = $arOsn->GetNext()) {
    $arResult['OSNOVANIE'][$arOsnItem['ID']] = [
        'ID' => $arOsnItem['ID'],
        'NAME' => $arOsnItem['VALUE']
    ];
}

if ($arRequest['uved_inaya_rabota'] && $arRequest['uved_inaya_rabota'] == 'add') {
    try {
        Loader::includeModule("workflow");
        Loader::includeModule("bizproc");

        $strIDajax = $arRequest['bxajaxid'];
        $intMODULEID = 'bizproc';

        if (!check_bitrix_sessid()) {
            throw new Exception('Проблема с сессией, обновите страницу');
        }

        $sDateFrom = $arRequest['DATE_FROM'];
        if (empty($sDateFrom)) {
            throw new Exception('Укажите дату начала');
        }

        $sDateTo = $arRequest['DATE_TO'];
        $sOrganisation = "{$arRequest['NAIMENOVANIE_YURIDICHESKOGO_LITSA_V_KOTOROM_PLANIR']}, {$arRequest['MESTONAKHOZHDENIE_YURIDICHESKOGO_LITSA'] }";
        $sTypeAction = $arResult['VID_DEYATELNOSTI'][$arRequest['VID_DEYATELNOSTI']]['NAME'];
        $sTypeContract = $arResult['OSNOVANIE'][$arRequest['OSNOVANIE_VOZNIKNOVENIYA_PRAVOVYKH_OTNOSHENIY']]['NAME'];

        if ($sTypeAction == 'иная') {
            $sTypeAction = $arRequest['DRUGOY_VID_DEYATELNOSTI'];
        }

        if ($sTypeContract == 'иное') {
            $sTypeContract = $arRequest['DRUGOE_OSNOVANIE_VOZNIKNOVENIYA_PRAVOVYKH_OTNOSHEN'];
        }

        $sDate = (empty($arRequest['DATE'])) ? date('d.m.Y') : $arRequest['DATE'];
        $sDate = date('d.m.Y', strtotime($sDate));

        if (empty($sDateTo)) {
            $sPeriod = "с " . date('d.m.Y', strtotime($sDateFrom)). " бессрочно" ;
        } else {
            $sPeriod = "с " . date('d.m.Y', strtotime($sDateFrom)) . " по " . date('d.m.Y', strtotime($sDateTo));
        }

        $obPropertyPred = CIBlockProperty::GetList([], ['IBLOCK_ID' => $arParams['ID_BIZPROC'], 'CODE' => 'PREDSTAVITEL_NANIMATELYA']);
        $iPredstavitel = $obPropertyPred->getNext()['DEFAULT_VALUE'];
        $arPredstavitel = $arUserFields($iPredstavitel);

        if ($arResult['VID_DEYATELNOSTI'][$arRequest['VID_DEYATELNOSTI']]['NAME'] == 'иная') {
            $sText = "заниматься иной оплачиваемой деятельностью: $sTypeAction";
        } else {
            $sText = "заниматься $sTypeAction оплачиваемой деятельностью";
        }

        $arProps = [
            'PERIOD' => $sPeriod,
            'VID_DEYATELNOSTI' => $sTypeAction,
            'VID_DOGOVORA' => $sTypeContract,
            'ORGANISATSIYA' => $sOrganisation,
            'FIO_GOSSLUJ' => $arUser['FIO'],
            'DOLJNOST_GOSSLUJ' => mb_strtolower(mb_substr($arUser['DOLJNOST'], 0, 1)) . mb_substr($arUser['DOLJNOST'], 1),
            'CURRENT_DATE' => $sDate,
            'DOLJNOST_NANIMATELYA' => $arPredstavitel['DOLJNOST_DAT'],
            'FIO_NANIMATELYA' => $arPredstavitel['FIO_DAT'],
            'TEXT' => $sText
        ];

        if ($iPredstavitel == 581) {
            $arProps['DOLJNOST_NANIMATELYA'] = 'Заместителю Губернатора Тульской области - руководителю аппарата правительства Тульской области - начальнику главного управления государственной службы и кадров аппарата правительства Тульской области';
        }

        $objEl = new CIBlockElement();
        $arLoadProductArray = [
            'MODIFIED_BY' => $USER->GetId(),
            'IBLOCK_SECTION_ID' => false,
            'IBLOCK_ID' => $arParams['ID_BIZPROC'],
            'PROPERTY_VALUES' => [
                'DATE_FROM' => date('d.m.Y H:i', strtotime($sDateFrom)),
                'DATE_TO' => !empty($sDateTo) ? date('d.m.Y H:i', strtotime($sDateTo)) : '',
                'DATE' => date('d.m.Y H:i', strtotime($sDate)),
                'VID_DEYATELNOSTI' => $arRequest['VID_DEYATELNOSTI'],
                'OSNOVANIE_VOZNIKNOVENIYA_PRAVOVYKH_OTNOSHENIY' => $arRequest['OSNOVANIE_VOZNIKNOVENIYA_PRAVOVYKH_OTNOSHENIY'],
                'DRUGOY_VID_DEYATELNOSTI' => $arRequest['DRUGOY_VID_DEYATELNOSTI'],
                'DRUGOE_OSNOVANIE_VOZNIKNOVENIYA_PRAVOVYKH_OTNOSHEN' => $arRequest['DRUGOE_OSNOVANIE_VOZNIKNOVENIYA_PRAVOVYKH_OTNOSHEN'],
                'NAIMENOVANIE_YURIDICHESKOGO_LITSA_V_KOTOROM_PLANIR' => $arRequest['NAIMENOVANIE_YURIDICHESKOGO_LITSA_V_KOTOROM_PLANIR'],
                'MESTONAKHOZHDENIE_YURIDICHESKOGO_LITSA' => $arRequest['MESTONAKHOZHDENIE_YURIDICHESKOGO_LITSA'],
                'RUKOVODITEL_OIV' => $arRequest['RUKOVODITEL_OIV'],
                'KOPII_DOKUMENTOV' => $_POST['KOPII_DOKUMENTOV'],
                'PREDSTAVITEL_NANIMATELYA' => $iPredstavitel,
                'FIELDS_FILE' => serialize($arProps)
            ],
            'NAME' => $APPLICATION->GetTitle(),
            'ACTIVE' => "Y",
            'PREVIEW_TEXT' => '',
        ];

        $boolDocumentid = $objEl->Add($arLoadProductArray);
        if (!$boolDocumentid) {
            throw new Exception($objEl->LAST_ERROR);
        }

        $sContent = str_replace(
            array_map(
                function ($item) {
                    return "#" . $item . "#";
                },
                array_keys($arProps)
            ),
            $arProps,
            file_get_contents(__DIR__ . '/tpl/index.html')
        );

        $msg = '';
        $docGenId = 0;
        if (!$GLOBALS['setElementPDFValue']($boolDocumentid, 'FAYL_S_UVEDOMLENIEM', $sContent, "Уведомление об иной оплачиваемой работе", $msg, $docGenId)) {
            CIBlockElement::Delete($boolDocumentid);
            throw new Exception("Не удалось создать файл");
        }

        $arResult['ajaxid'] = $strIDajax;
        $arResult['file_id'] = $docGenId;
        $arResult['code'] = "ReadySign";
        $arResult['message'] = 'Ready to sign';
        $arResult['sessid'] = bitrix_sessid();
        $arResult['documentid'] = $boolDocumentid;
    } catch (Exception $exc) {
        $arResult['message'] = $exc->getMessage();
    }
} elseif ($arRequest['uved_inaya_rabota'] && $arRequest['uved_inaya_rabota'] == 'signed') {
    try {
        $boolDocumentid = $arRequest['documentid'];

        $arErrorsTmp = [];

        $strWfId = CBPDocument::StartWorkflow(
            $arParams['ID_TEMPLEATE'],
            ["lists", "BizprocDocument", $boolDocumentid],
            ['TargetUser' => "user_" . $USER->GetID()],
            $arErrorsTmp
        );

        if (count($arErrorsTmp) > 0) {
            throw new Exception(
                array_reduce(
                    $arErrorsTmp,
                    function ($strCarry, $arItem) {
                        return $strCarry . "." . $arItem['message'];
                    },
                    ''
                )
            );
        }

        $arResult['code'] = "OK";
        $arResult['message'] = "<p>Бизнес-процесс запущен!</p>";
    } catch (Exception $exc) {
        $arResult['message'] = $exc->getMessage();
    }
} else {
    $oblistUsersAll = (new CUser())->GetList($by = "LAST_NAME", $order = "ASC", ["ACTIVE" => 'Y', "!LAST_NAME" => null, "!NAME" => null]);
    while ($arRuc = $oblistUsersAll->Fetch()) {
        if (!empty($arRuc['LAST_NAME']) && !empty($arRuc['SECOND_NAME'])) {
            $arResult['USERS'][] = $arRuc;
        }
    }
}

$arResult["__TEMPLATE_FOLDER"] = $this->__folder;
$this->__component->arResult = $arResult;
