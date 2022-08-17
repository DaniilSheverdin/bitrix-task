<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UserTable, CIntranetUtils;


global $APPLICATION, $USER, $userFields, $morphFunct;

$arUserFields = $userFields;
$arRequest = $arResult['REQUEST'];
Loader::includeModule("iblock");
Loader::includeModule('citto.filesigner');
CJSCore::Init(["date"]);

$APPLICATION->SetTitle("Увольнение (Правительство)");

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


if ($arRequest['action'] == 'get_users') {
    $iUserID = (int) $arRequest['POLZOVATEL'];

    function recDepartment($iID, $arDepartments)
    {
        $iParentID = $arDepartments[$iID]['PARENT_ID'];
        $iDepth = $arDepartments[$iID]['DEPTH'];

        if ($iDepth == 3) {
            return $arDepartments[$iID];
        } else if ($iDepth > 3) {

            return recDepartment($iParentID, $arDepartments);
        } else {
            return $arDepartments[$iID];
        }
    }

    $arDepartments = [];
    $obDepartments = CIBlockSection::GetList([], ["IBLOCK_ID" => 5, 'ACTIVE' => 'Y', '>DEPTH_LEVEL' => 1, ''], false, ['ID', 'NAME', 'IBLOCK_SECTION_ID', 'DEPTH_LEVEL', 'UF_HEAD', 'UF_HEAD2', 'UF_PODVED', 'UF_HEAD_HELPERS']);

    while ($arDep = $obDepartments->GetNext()) {
        $iID = $arDep['ID'];
        $sName = $arDep['NAME'];
        $arDepartments[$iID] = [
            'NAME' => $sName,
            'UF_HEAD' => $arDep['UF_HEAD'],
            'UF_HEAD2' => $arDep['UF_HEAD2'],
            'UF_HEAD_HELPERS' => $arDep['UF_HEAD_HELPERS'],
            'DEPTH' => (int )$arDep['DEPTH_LEVEL'],
            'PARENT_ID' => $arDep['IBLOCK_SECTION_ID']
        ];
    }

    foreach ($arDepartments as $iDepID => $arDepartment) {
        if ($arDepartment['DEPTH'] > 3) {
            $arRecDepartment = recDepartment($iDepID, $arDepartments);
            $arDepartments[$iDepID]['NAME'] = $arRecDepartment['NAME'];
            $arDepartments[$iDepID]['UF_HEAD'] = $arRecDepartment['UF_HEAD'];
            $arDepartments[$iDepID]['UF_HEAD2'] = $arRecDepartment['UF_HEAD2'];
            $arDepartments[$iDepID]['UF_HEAD_HELPERS'] = $arRecDepartment['UF_HEAD_HELPERS'];

        }

        unset($arDepartments[$iDepID]['PARENT_ID'], $arDepartments[$iDepID]['DEPTH']);
    }

    $obUsers = UserTable::getList([
        'select' => ['ID', 'LOGIN', 'XML_ID', 'UF_SID', 'EMAIL', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'UF_DEPARTMENT', 'UF_WORK_POSITION',],
        'filter' => ['ID' => $iUserID]
    ]);

    $arUsersOIV = [];
    while ($arUser = $obUsers->fetch()) {
        $iDepartment = current($arUser['UF_DEPARTMENT']);
        if (!empty($arDepartments[$iDepartment])) {
            $sHead = ($arDepartments[$iDepartment]['UF_HEAD']);
            $arHeplers = ($arDepartments[$iDepartment]['UF_HEAD_HELPERS']);

            $arUsersOIV[$arUser['ID']] = [
                'HEAD' => 0,
                'HELPERS' => []
            ];
            if ($sHead) {
                $arUsersOIV[$arUser['ID']]['HEAD'] = $sHead;
            }

            if ($arHeplers) {
                foreach ($arHeplers as $sHelper) {
                    array_push($arUsersOIV[$arUser['ID']]['HELPERS'], $sHelper);
                }
            }
        }
    }

    $arResult['EMPLOYEE'] = [
        'HEAD' => null,
        'HELPERS' => []
    ];

    if (!empty($arUsersOIV[$iUserID]['HELPERS'])) {
        $arResult['EMPLOYEE']['HELPERS'] = $arUsersOIV[$iUserID]['HELPERS'];
    }

    if (!empty($arUsersOIV[$iUserID]['HEAD'])) {
        $arResult['EMPLOYEE']['HEAD'] = $arUsersOIV[$iUserID]['HEAD'];
    }

    $arResult['code'] = "OK";
    $arResult['message'] = '';

} else if ($arRequest['uved_inaya_rabota'] && $arRequest['uved_inaya_rabota'] == 'add') {
    try {
        Loader::includeModule("workflow");
        Loader::includeModule("bizproc");

        $strIDajax = $arRequest['bxajaxid'];
        $intMODULEID = 'bizproc';

        if (!check_bitrix_sessid()) {
            throw new Exception('Проблема с сессией, обновите страницу');
        }

        $sDateUvolneniya = $arRequest['DATA_UVOLNENIYA'];
        $iUserID = $arRequest['POLZOVATEL'];
        $iHeadUserID = $arRequest['HEAD_OIV'];
        $arHelperUserID = $arRequest['HELPER_OIV'];

        if (empty($sDateUvolneniya)) {
            throw new Exception('Укажите дату увольнения');
        }
        if (empty($iUserID)) {
            throw new Exception('Укажите пользователя');
        }
        if (empty($iHeadUserID)) {
            throw new Exception('Укажите руководителя ОИВ');
        }
        if (empty($arHelperUserID)) {
            throw new Exception('Укажите помощника ОИВ');
        }

        $arLoadProductArray = [
            'MODIFIED_BY' => $USER->GetId(),
            'IBLOCK_SECTION_ID' => false,
            'IBLOCK_ID' => $arParams['ID_BIZPROC'],
            'PROPERTY_VALUES' => [
                'SOTRUDNIK' => $iUserID,
                'HEAD_OIV' => $iHeadUserID,
                'HELPER_OIV' => $arHelperUserID,
                'DATA_UVOLNENIYA' => date('d.m.Y', strtotime($sDateUvolneniya))
            ],
            'NAME' => $APPLICATION->GetTitle(),
            'ACTIVE' => "Y",
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

        $arResult['code'] = "OK";
        $arResult['message'] = "<p>Бизнес-процесс запущен!</p>";
    } catch (Exception $exc) {
        $arResult['message'] = $exc->getMessage();
    }
} else {
    $oblistUsersAll = (new CUser())->GetList($by = "LAST_NAME", $order = "ASC", ["ACTIVE" => 'Y', "!LAST_NAME" => null, "!NAME" => null], ['SELECT' => ['UF_WORK_POSITION']]);
    while ($arRuc = $oblistUsersAll->Fetch()) {
        if (!empty($arRuc['LAST_NAME'])) {
            $sWorkPosition = mb_substr($arRuc['UF_WORK_POSITION'], 0, 60);
            $sUserInfo = "{$arRuc['LAST_NAME']} {$arRuc['NAME']} {$arRuc['SECOND_NAME']}";
            if (!empty($sWorkPosition)) {
                $sUserInfo = "$sUserInfo ($sWorkPosition...)";
            }
            $arRuc['USER_INFO'] = $sUserInfo;
            $arResult['USERS'][] = $arRuc;
        }
    }
}

$arResult["__TEMPLATE_FOLDER"] = $this->__folder;
$this->__component->arResult = $arResult;
