<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global $APPLICATION
 * @global $USER
 * @var array $arResult
 * @var array $arParams
 */

define('VUEJS_DEBUG', true);

if(!defined('SECTION_ID_CITTO_UIS_STRUCTURE')) {
    define('SECTION_ID_CITTO_UIS_STRUCTURE', 79);
}

use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use CIntranetUtils;
use Bitrix\Main\UI\Extension;
use CUtil;

global $APPLICATION, $USER, $userFields, $morphFunct;

$arUserFields = $userFields($USER->GetId());
$arRequest = $arResult['REQUEST'];

Loader::includeModule('iblock');
Loader::includeModule('citto.filesigner');
Loader::includeModule("intranet");

CJSCore::Init(['date']);
CUtil::InitJSCore(['bp_user_selector']);
Extension::load('ui.vue');

$arBlockData = CIBlock::GetByID($arParams['ID_BIZPROC'])->Fetch();
$APPLICATION->SetTitle($arBlockData['NAME']);

Asset::getInstance()->addJs('/bitrix/templates/.default/jquery.min.js');
Asset::getInstance()->addJs("/local/js/bootstrap-plugin/popper.min.js");
Asset::getInstance()->addJs('/bitrix/templates/.default/bootstrap.min.js');
Asset::getInstance()->addJs('/' . basename(__DIR__) . '/index.js');
Asset::getInstance()->addJs('/local/js/bootstrap-plugin/bootstrap-select.min.js');

$APPLICATION->SetAdditionalCSS('/local/css/bootstrap-plugin/bootstrap-select.min.css');
$APPLICATION->SetAdditionalCSS('/bitrix/templates/.default/bootstrap.min.css');
$APPLICATION->SetAdditionalCSS('/' . basename(__DIR__) . '/main.css');

$arSotrudnicUserData = $userFields($arUserFields['ID']);
if(isset($arSotrudnicUserData['UF_DEPARTMENT'][0])) {
    $arListUpDeps = GetParentDepartmentstucture($arUserFields['ID']);
} else {
    $arListUpDeps = [];
}

$intDataRuc = CIntranetUtils::GetDepartmentManagerID($arSotrudnicUserData['UF_DEPARTMENT'][0]);
$arResult['RUCUSER'] = $userFields($intDataRuc);

$arResult['FIELDS_LIST'] = $this->getComponent()->getFieldsList(
    $arParams['ID_BIZPROC'],
    [
        'OTDEL_PRIVYAZKA_K_PODRAZDELENIYU'
    ],
    [
        'RUKOVODITEL_SOTRUDNIKA' => ['DEFAULT_VALUE' => $arResult['RUCUSER']['NAME'].' '.$arResult['RUCUSER']['LAST_NAME'].' ['.$arResult['RUCUSER']['ID'].']']
    ]
);

if(empty($arResult['FIELDS_LIST']['OTDEL']['value'])) {
    $arResult['FIELDS_LIST']['OTDEL']['value'] = $arUserFields['DEPARTMENT'];
}

$arResult['FIELDS_LIST'] = json_encode($arResult['FIELDS_LIST']);

if ($arRequest['stazhirovka_cit'] && $arRequest['stazhirovka_cit'] == 'add') {
    try {
        Loader::includeModule("workflow");
        Loader::includeModule("bizproc");

        $strIDajax = $arRequest['bxajaxid'];
        $intMODULEID = 'bizproc';

        if (!check_bitrix_sessid()) {
            throw new Exception('Проблема с сессией, обновите страницу');
        }

        $arMatches = [];
        preg_match("/(\d+)/is", $arRequest['FIO_SOTRUDNIKA'], $arMatches);

        /**
         * Выбор сотрудника и данных по отделу
         */
        $intFIO_SOTRUDNIKA = ($arMatches[1]) ?? $arRequest['FIO_SOTRUDNIKA'];
        $strOTDEL = $arRequest['OTDEL'];
        $strROL_V_KOMANDE = $arRequest['ROL_V_KOMANDE'];

        // выбор наставника
        $arMatches = [];
        preg_match("/(\d+)/is", $arRequest['NASTAVNIK'], $arMatches);
        $intNASTAVNIK = ($arMatches[1]) ?? $arRequest['NASTAVNIK'];

        // выбор руководителя
        $arMatches = [];
        preg_match("/(\d+)/is", $arRequest['RUKOVODITEL_SOTRUDNIKA'], $arMatches);
        $intRucovoditel = ($arMatches[1]) ?? $arRequest['RUKOVODITEL_SOTRUDNIKA'];

        $arSotrudnicUserData = $userFields($intFIO_SOTRUDNIKA);
        if(isset($arSotrudnicUserData['UF_DEPARTMENT'][0])) {
            $arListUpDeps = GetParentDepartmentstucture($arUserFields['ID']);
        } else {
            $arListUpDeps = [];
        }

        $arNastUserData = $userFields($intNASTAVNIK);
        if(isset($arNastUserData['UF_DEPARTMENT'][0])) {
            $arListUpDepsNast = GetParentDepartmentstucture($intNASTAVNIK);
        } else {
            $arListUpDepsNast = [];
        }

        if (empty($intFIO_SOTRUDNIKA)) {
            throw new Exception('Укажите ФИО сотрудника');
        }

        if(!in_array(SECTION_ID_CITTO_UIS_STRUCTURE, $arListUpDeps)) {
            throw new Exception('Пользователь не является сотрудником подразделений УИС');
        }

        if (strlen($strOTDEL) == 0) {
            throw new Exception('Укажите отдел сотрудника');
        }
        if (strlen($strROL_V_KOMANDE) == 0) {
            throw new Exception('Укажите роль сотрудника в команде');
        }
        if (empty($intNASTAVNIK)) {
            throw new Exception('Укажите наставника для стажировки');
        }
        if (empty($intRucovoditel)) {
            throw new Exception('Укажите руководителя стажируемого');
        }

        $arRucUserDataGet = $userFields($intRucovoditel);
        if(isset($arRucUserDataGet['UF_DEPARTMENT'][0])) {
            $arListUpDepsRuc = GetParentDepartmentstucture($intRucovoditel);
        } else {
            $arListUpDepsRuc = [];
        }


        if(!in_array(SECTION_ID_CITTO_UIS_STRUCTURE, $arListUpDepsNast)) {
            throw new Exception('Выбранный наставник не является сотрудником подразделений УИС');
        }

        if(!in_array(SECTION_ID_CITTO_UIS_STRUCTURE, $arListUpDepsRuc)) {
            throw new Exception('Выбранный руководитель не является сотрудником подразделений УИС');
        }

        $arLoadProductArray = [
            'MODIFIED_BY' => $USER->GetId(),
            'IBLOCK_SECTION_ID' => false,
            'IBLOCK_ID' => $arParams['ID_BIZPROC'],
            'PROPERTY_VALUES' => [
                'FIO_SOTRUDNIKA' => $intFIO_SOTRUDNIKA,
                'OTDEL' => trim($strOTDEL),
                'ROL_V_KOMANDE' => trim($strROL_V_KOMANDE),
                'NASTAVNIK' => $intNASTAVNIK,
                'RUKOVODITEL_SOTRUDNIKA' => $intRucovoditel,
                'OTDEL_PRIVYAZKA_K_PODRAZDELENIYU' => $arSotrudnicUserData['UF_DEPARTMENT'][0]
            ],
            'NAME' => $APPLICATION->GetTitle(),
            'ACTIVE' => 'Y',
            'PREVIEW_TEXT' => '',
        ];

        $objEl = new CIBlockElement();
        $boolDocumentid = $objEl->Add($arLoadProductArray);
        if (!$boolDocumentid) {
            throw new Exception($objEl->LAST_ERROR);
        }

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

        $arResult['code'] = 'OK';
        $arResult['message'] = "Бизнес-процесс \"{$APPLICATION->GetTitle()}\" запущен! Перейти к <a href=\"/citto/bizproc/processes/{$arParams['ID_BIZPROC']}/view/0/?list_section_id=\">списку процессов</a>.";
    } catch (Exception $exc) {
        $arResult['message'] = $exc->getMessage();
    }
} else {
    $oblistUsersAll = (new CUser())->GetList($by = "LAST_NAME", $order = "ASC", ["ACTIVE" => 'Y', "!LAST_NAME" => null, "!NAME" => null], ['SELECT' => ['UF_WORK_POSITION']]);
    while ($arRuc = $oblistUsersAll->Fetch()) {
        if (!empty($arRuc['LAST_NAME'])) {
            $sWorkPosition = mb_substr($arRuc['UF_WORK_POSITION'], 0, 60);
            $arResult['USERS'][] = $arRuc;
        }
    }

    $arResult['USERS'] = json_encode($arResult['USERS']);
}

$arResult["__TEMPLATE_FOLDER"] = $this->__folder;
$this->__component->arResult = $arResult;
