<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;

global $APPLICATION, $USER, $userFields, $morphFunct;

$arUserFields = $userFields;
$arRequest = $arResult['REQUEST'];
Loader::includeModule("iblock");
Loader::includeModule('citto.filesigner');
CJSCore::Init(["date"]);

$APPLICATION->SetTitle("Выписка сведений из ЭТК");

Asset::getInstance()->addJs('/bitrix/templates/.default/jquery.min.js');
Asset::getInstance()->addJs("/local/js/bootstrap-plugin/popper.min.js");
Asset::getInstance()->addJs('/bitrix/templates/.default/bootstrap.min.js');
Asset::getInstance()->addJs('/' . basename(__DIR__) . '/index.js');

Asset::getInstance()->addJs('/local/activities/custom/docsignactivity/js/es6-promise.min.js');
Asset::getInstance()->addJs('/local/activities/custom/docsignactivity/js/ie_eventlistner_polyfill.js');
Asset::getInstance()->addJs("/local/activities/custom/docsignactivity/js/cadesplugin_api.js");
Asset::getInstance()->addJs("/local/activities/custom/docsignactivity/js/plugin.js");
Asset::getInstance()->addJs("/local/components/citto/bizproc/js/docsignactivity.js");

$APPLICATION->SetAdditionalCSS('/bitrix/templates/.default/bootstrap.min.css');
$APPLICATION->SetAdditionalCSS('/' . basename(__DIR__) . '/main.css');

$arUser = $arUserFields($USER->GetID());

$arResult['FORMA'] = [];
$obForms = CIBlockProperty::GetPropertyEnum('FORMA', [], ['IBLOCK_ID' => $arParams['ID_BIZPROC']]);
while ($arForms = $obForms->GetNext()) {
    $arResult['FORMA'][$arForms['ID']] = [
        'ID' => $arForms['ID'],
        'NAME' => $arForms['VALUE']
    ];
}

$arResult['DEFAULT_MAIL'] = $arUser['EMAIL'];

if ($arRequest['vipiska_iz_etk'] && $arRequest['vipiska_iz_etk'] == 'add') {
    try {
        Loader::includeModule("workflow");
        Loader::includeModule("bizproc");

        $strIDajax = $arRequest['bxajaxid'];
        $intMODULEID = 'bizproc';

        if (!check_bitrix_sessid()) {
            throw new Exception('Проблема с сессией, обновите страницу');
        }

        $sForm = $arRequest['FORMA'];
        $sPrichina = $arRequest['PRICHINA'];
        $sEmail = $arRequest['MAIL'];

        if (empty($sForm)) {
            throw new Exception('Укажите форму выписки');
        }
        if (empty($sPrichina)) {
            throw new Exception('Укажите причину');
        }
        if (empty($sEmail)) {
            throw new Exception('Введите электронную почту');
        }

        if ($arResult['FORMA'][$sForm]['NAME'] == 'Электронная') {
            $sText = "Прошу предоставить сведения о моей трудовой деятельности в форме электронного документа для ($sPrichina) и направить их на адрес электронной почты: $sEmail";
        } else {
            $sText = "Прошу предоставить сведения о моей трудовой деятельности на бумажном носителе для ($sPrichina)";
        }

        $arProps = [
            'FIO_ROD' => $arUser['FIO_ROD'],
            'DOLJNOST_ROD' => $arUser['DOLJNOST_ROD'],
            'TEXT' => $sText,
            'DATE' => date('d.m.Y'),
        ];

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

        $objEl = new CIBlockElement();

        $arLoadProductArray = [
            'MODIFIED_BY' => $USER->GetId(),
            'IBLOCK_SECTION_ID' => false,
            'IBLOCK_ID' => $arParams['ID_BIZPROC'],
            'PROPERTY_VALUES' => [
                'FORMA' => $sForm,
                'PRICHINA' => $sPrichina,
                'MAIL' => $sEmail
            ],
            'NAME' => $APPLICATION->GetTitle(),
            'ACTIVE' => "Y",
            'PREVIEW_TEXT' => '',
        ];

        $boolDocumentid = $objEl->Add($arLoadProductArray);
        if (!$boolDocumentid) {
            throw new Exception($objEl->LAST_ERROR);
        }

        $msg = '';
        $docGenId = 0;
        if (!$GLOBALS['setElementPDFValue']($boolDocumentid, 'ZAYAVLENIE', $sContent, "заявление", $msg, $docGenId)) {
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
} elseif ($arRequest['vipiska_iz_etk'] && $arRequest['vipiska_iz_etk'] == 'signed') {
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
    $oblistUsersAll = (new CUser())->GetList(
        $by = "LAST_NAME",
        $order = "ASC",
        ["ACTIVE" => 'Y', "!LAST_NAME" => null, "!NAME" => null, 'ID' => $USER->GetID()],
        ['SELECT' => ['UF_WORKBOOK_ELECTRONIC']]
    );

    $arResult['WORKBOOK_ELECTRONIC'] = $oblistUsersAll->Fetch()['UF_WORKBOOK_ELECTRONIC'];
}

$arResult["__TEMPLATE_FOLDER"] = $this->__folder;
$this->__component->arResult = $arResult;
