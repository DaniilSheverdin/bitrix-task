<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;

global $APPLICATION, $USER, $userFields;

$arUserFields = $userFields;
$arRequest = $arResult['REQUEST'];
Loader::includeModule("iblock");
require_once __DIR__ . '/constants/index.php';
CJSCore::Init(["date"]);

$APPLICATION->SetTitle("Формирование листа адаптации");

Asset::getInstance()->addJs('/bitrix/templates/.default/jquery.min.js');
Asset::getInstance()->addJs("/local/js/bootstrap-plugin/popper.min.js");
Asset::getInstance()->addJs('/bitrix/templates/.default/bootstrap.min.js');
Asset::getInstance()->addJs('/' . basename(__DIR__) . '/index.js');
Asset::getInstance()->addJs('/local/js/bootstrap-plugin/bootstrap-select.min.js');

$APPLICATION->SetAdditionalCSS('/bitrix/templates/.default/bootstrap.min.css');
$APPLICATION->SetAdditionalCSS('/' . basename(__DIR__) . '/main.css');
$APPLICATION->SetAdditionalCSS('/local/css/bootstrap-plugin/bootstrap-select.min.css');

$arUser = $arUserFields($USER->GetID());

if (isset($arRequest['formirovanie-listka-adaptaciy'])) {
    try {
        Loader::includeModule("workflow");
        Loader::includeModule("bizproc");

        if (!check_bitrix_sessid()) {
            throw new Exception('Проблема с сессией, обновите страницу');
        }

        $strFIO = $arRequest['FLA_FIO'];
        $strPODRAZDELENIE = $arRequest['FLA_PODRAZDELENIE'];
        $intRucovoditel = $arRequest['FLA_RUKOVODITEL'];
        $strDOLZHNOST = $arRequest['FLA_DOLZHNOST'];
        $intGOGS = $arRequest['FLA_GOGS'];
        $arKURSY_ADAPTACIY = $arRequest['FLA_KURSY_ADAPTACIY'];
        $arOBYAZANNOSTY = $arRequest['FLA_OBYAZ_NS'];

        if (empty($strFIO)) {
            throw new Exception('Укажите ФИО');
        }
        if (empty($strPODRAZDELENIE)) {
            throw new Exception('Укажите Подразделение');
        }
        if (empty($intRucovoditel)) {
            throw new Exception('Укажите Руководителя');
        }
        if (empty($strDOLZHNOST)) {
            throw new Exception('Укажите Должность');
        }
        if (empty($intGOGS)) {
            throw new Exception('Укажите Государственный / гражданский служащий');
        }
        if (count($arKURSY_ADAPTACIY) <= 0) {
            throw new Exception('Выберите желаемые курсы');
        }

        if (count($arOBYAZANNOSTY) <= 0) {
            throw new Exception('Выберите обязанности сотрудника');
        }

        $strIDajax = $arRequest['bxajaxid'];

        $intKolichestvo = 0;
        $intMODULEID = 'bizproc';

        $arProps = [
            'FLA_FIO' => $strFIO,
            'FLA_PODRAZDELENIE' => $strPODRAZDELENIE,
            'FLA_RUKOVODITEL' => $intRucovoditel,
            'FLA_DOLZHNOST' => $strDOLZHNOST,
            'FLA_GOGS' => $intGOGS,
            'FLA_KURSY_ADAPTACIY' => $arKURSY_ADAPTACIY,
            'FLA_OBYAZ_NS' => $arOBYAZANNOSTY
        ];

        $objEl = new CIBlockElement();
        $arLoadProductArray = [
            'MODIFIED_BY' => $USER->GetId(),
            'IBLOCK_SECTION_ID' => false,
            'IBLOCK_ID' => IBLOCK_ID_FLA,
            'PROPERTY_VALUES' => $arProps,
            'NAME' => $APPLICATION->GetTitle(),
            'ACTIVE' => "Y",
            'PREVIEW_TEXT' => '',
        ];

        $boolDocumentid = $objEl->Add($arLoadProductArray);
        if (!$boolDocumentid) {
            throw new Exception($objEl->LAST_ERROR);
        }

        $arErrorsTmp = [];
        $strWfId = CBPDocument::StartWorkflow(
            BP_TEMPLATE_ID,
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
        $arResult['message'] = "<p>Уважаемый коллега!</p>
        <p>В течении нескольких дней Вам будет определен набор электронных курсов, которые нужно будет изучить в период адаптации.</p>
        <p>После утверждения электронных курсов руководителями, Вам придет уведомление и электронные курсы будут Вам назначены в Вашем Личном кабинете на Корпоративном Университете.</p>
        <p>Просим ответственно подойти к изучению предложенных материалов. Это сделает Ваш процесс адаптации максимально легким и быстрым!!!</p>
        <p>С уважением, Ваш Электронный Наставник.</p>";
        $arResult['ajaxid'] = $strIDajax;
    } catch (Exception $exc) {
        $arResult['message'] = $exc->getMessage();
    }
} else {
    $arResult['ID_FLA_USER'] = $arUser['ID'];
    $arResult['FLA_FIO'] = empty($arUser['FIO']) ? $arUser['LAST_NAME'] . ' ' . $arUser['NAME'] . ' ' . $arUser['MIDDLE_NAME'] : $arUser['FIO'];
    $arResult['FLA_PODRAZDELENIE'] = empty($arUser['PODRAZDELENIE']) ? implode(', ', $arUser['DEPARTMENTS']) : implode(', ', $arUser['PODRAZDELENIE']);

    $intDEPARTID = array_shift($arUser['UF_DEPARTMENT']);

    $arPropsList = CIBlockPropertyEnum::GetList(
        [
            "SORT" => "ASC",
            "VALUE" => "ASC"
        ],
        [
            'IBLOCK_ID' => IBLOCK_ID_FLA,
            'CODE' => 'FLA_GOGS'
        ]
    );

    while ($arVal = $arPropsList->Fetch()) {
        $arResult['FLA_GOGS'][] = $arVal;
    }

    $arPropsList = CIBlockPropertyEnum::GetList(
        [
            "SORT" => "ASC",
            "VALUE" => "ASC"
        ],
        [
            'IBLOCK_ID' => IBLOCK_ID_FLA,
            'CODE' => 'FLA_KURSY_ADAPTACIY'
        ]
    );

    while ($arVal = $arPropsList->Fetch()) {
        $arResult['FLA_KURSY_ADAPTACIY'][] = $arVal;
    }

    $arPropsList = CIBlockPropertyEnum::GetList(
        [
            "SORT" => "ASC",
            "VALUE" => "ASC"
        ],
        [
            'IBLOCK_ID' => IBLOCK_ID_FLA,
            'CODE' => 'FLA_OBYAZ_NS'
        ]
    );

    while ($arVal = $arPropsList->Fetch()) {
        $arResult['FLA_OBYAZ_NS'][] = $arVal;
    }

    $arPropsList = CIBlockPropertyEnum::GetList(
        [
            "SORT" => "ASC",
            "VALUE" => "ASC"
        ],
        [
            'IBLOCK_ID' => IBLOCK_ID_FLA,
            'CODE' => 'FLA_OSNOVNYE_KURSY'
        ]
    );

    while ($arVal = $arPropsList->Fetch()) {
        $arResult['FLA_OSNOVNYE_KURSY'][] = $arVal;
    }

    $objDepCITTO = CIBlockSection::GetList(
        ["SORT" => "ASC"],
        [
            'IBLOCK_ID' => BP_IBLOCK_STRUCTURE,
            'GLOBAL_ACTIVE' => 'Y',
            'ID' => $intDEPARTID
        ],
        false,
        ['UF_HEAD']
    );
    $arDepAdd = $objDepCITTO->GetNext();

    if (isset($arDepAdd)) {
        $arResult['RUKOVODITEL_ID'] = $arDepAdd['UF_HEAD'];
    } else {
        $arResult['RUKOVODITEL_ID'] = 0;
    }

    $arResult['FLA_DOLZHNOST']
        = empty($arUser['WORK_POSITION_CLEAR']) ? $arUser['DOLJNOST_CLEAR'] : $arUser['WORK_POSITION_CLEAR'];

    $objlistUsersAll = (new CUser())->GetList($by = "LAST_NAME", $order = "ASC", ["ACTIVE" => 'Y', "!LAST_NAME" => null, "!NAME" => null]);

    while ($arRuc = $objlistUsersAll->Fetch()) {
        if (!empty($arRuc['LAST_NAME'])) {
            $arResult['RUKOVODITEL'][] = $arRuc;
        }
    }
}

$arResult["__TEMPLATE_FOLDER"] = $this->__folder;
$this->__component->arResult = $arResult;
