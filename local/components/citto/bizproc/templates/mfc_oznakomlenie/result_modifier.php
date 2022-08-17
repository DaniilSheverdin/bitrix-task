<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UserTable, CIntranetUtils;

global $APPLICATION, $USER, $userFields; 

$arRequest = $arResult['REQUEST'];
Loader::includeModule("iblock");
CJSCore::Init(["date"]);

$arBlockData = CIBlock::GetByID($arParams['ID_BIZPROC'])->Fetch();
$APPLICATION->SetTitle($arBlockData['NAME']);

Asset::getInstance()->addJs('/bitrix/templates/.default/jquery.min.js');
Asset::getInstance()->addJs("/local/js/bootstrap-plugin/popper.min.js");
Asset::getInstance()->addJs('/bitrix/templates/.default/bootstrap.min.js');
Asset::getInstance()->addJs('/bitrix/templates/.default/jquery.mask.js');
Asset::getInstance()->addJs('/' . basename(__DIR__) . '/index.js');
Asset::getInstance()->addJs('/local/js/bootstrap-plugin/bootstrap-select.min.js');

$APPLICATION->SetAdditionalCSS('/bitrix/templates/.default/bootstrap.min.css');
$APPLICATION->SetAdditionalCSS('/' . basename(__DIR__) . '/main.css');
$APPLICATION->SetAdditionalCSS('/local/css/bootstrap-plugin/bootstrap-select.min.css');

$arUser = $userFields($USER->GetID());

define('BP_TEMPLATE_ID', $arParams['ID_TEMPLEATE']);
if(!defined("SECTION_ID_MFC_STRUCTURE")) {
    define("SECTION_ID_MFC_STRUCTURE", 58);
}

$strMODULEID = 'bizproc';

$arPropsList = CIBlockPropertyEnum::GetList(
    [
        "SORT" => "ASC",
        "VALUE" => "ASC"
    ],
    [
        'IBLOCK_ID' => $arParams['ID_BIZPROC'],
        'CODE' => 'USERS'
    ]
);

while ($arVal = $arPropsList->Fetch()) {
    $arResult['ARUSERS'][$arVal['XML_ID']] = $arVal;
}

if (isset($arRequest['mfc-oznakomlenie'])) {
    try {
        Loader::includeModule("workflow");
        Loader::includeModule("bizproc");

        $intSotrudnic = $USER->GetID();

        if (!check_bitrix_sessid()) {
            throw new Exception('Проблема с сессией, обновите страницу');
        }

        $strNAME = $arRequest['NAME'];
        $intTYPE = $arRequest['TYPE'];
        $intUSERS = $arRequest['USERS'];
        $arUSER_LIST = $arRequest['USER_LIST'];
        $arGROUPS_LIST = $arRequest['GROUPS_LIST'];
        $strMessage = $arRequest['MESSAGE'];

        $strIDajax = $arRequest['bxajaxid'];

        if (empty($strNAME)) {
            throw new Exception('Укажите Название');
        }
        if (empty($intTYPE)) {
            throw new Exception('Укажите Тип');
        }
        if (empty($intUSERS)) {
            throw new Exception('Укажите Кто должен ознакомиться');
        }
        if (!empty($intUSERS) && empty($arUSER_LIST) && empty($arGROUPS_LIST) && $intUSERS == $arResult['ARUSERS']['756759d452224b78f6252206f72758fc']['ID']) {
            throw new Exception('Укажите Выбранных пользователей либо группы пользователей');
        }

        foreach($arGROUPS_LIST as $arItem) {
            $arUsetsData = CGroup::GetGroupUser(intval($arItem));
            $arUSER_LIST = array_merge($arUSER_LIST, $arUsetsData);
        }

        $arProps = [
            'NAME' => $strNAME,
            'TYPE' => $intTYPE,
            'USERS' => $intUSERS,
            'USER_LIST' => $arUSER_LIST
        ];

        if(strlen($_FILES['FILE']['name']) > 0) {
            $arFileArray = array_merge(
                $_FILES['FILE'],
                [
                    'MODULE_ID' => $strMODULEID
                ]
            );

            $intFileId = CFile::SaveFile(
                $arFileArray,
                'bp/' . $arParams['ID_BIZPROC']
            );
            $arProps['FILE'] = $intFileId;
        } else {
            $arProps['FILE'] = null;
        }

        $obIBElem = new CIBlockElement();
        $arLoadProductArray = [
            'MODIFIED_BY' => $USER->GetId(),
            'IBLOCK_SECTION_ID' => false,
            'IBLOCK_ID' => $arParams['ID_BIZPROC'],
            'PROPERTY_VALUES' => $arProps,
            'NAME' => $APPLICATION->GetTitle(),
            'ACTIVE' => 'Y',
            'PREVIEW_TEXT' => '',
            'DETAIL_TEXT' => htmlentities($strMessage),
            'DETAIL_TEXT_TYPE' => 'html'
        ];

        $intDocumentid = $obIBElem->Add($arLoadProductArray);
        if (!$intDocumentid) {
            throw new Exception($obIBElem->LAST_ERROR);
        }

        $arErrorsTmp = [];

        $wfId = CBPDocument::StartWorkflow(
            BP_TEMPLATE_ID,
            ["lists", "BizprocDocument", $intDocumentid],
            ['TargetUser' => "user_" . $intSotrudnic],
            $arErrorsTmp
        );

        if (count($arErrorsTmp) > 0) {
            throw new Exception(array_reduce($arErrorsTmp, function ($carry, $item) {
                return $carry . "." . $item['message'];
            }, ''));
        }

        $arResult['code'] = "OK";
        $arResult['message'] = "Бизнесс-процесс \"{$APPLICATION->GetTitle()}\" запущен.";
        $arResult['ajaxid'] = $strIDajax;
    } catch (Exception $exc) {
        $arResult['message'] = $exc->getMessage();
    }
} else {

    $obAllUsers = CUser::GetList($by = 'LAST_NAME', $ord = 'ASC', ['ACTIVE' => 'Y', '!=LAST_NAME' => null, '!=NAME' => null], ['SELECT' => ['UF_DEPARTMENT']]);
    while($arUser = $obAllUsers->GetNext()) {
        if (!empty($arUser['LAST_NAME']) && isset($arUser['UF_DEPARTMENT'][0])) {
            $arParentDeps = GetParentDepartmentstucture($arUser['ID']);
            if(in_array(SECTION_ID_MFC_STRUCTURE, $arParentDeps) || $arUser['UF_DEPARTMENT'][0] == SECTION_ID_MFC_STRUCTURE) {
                $arResult['ALL_USERS'][] = $arUser;
            }
        }
    }

    $obAllGroups = CGroup::GetList($by="c_sort", $order="desc", ['ACTIVE' => 'Y']);
    while($arGroup = $obAllGroups->GetNext()) {
        $arResult['ALL_GROUPS'][] = $arGroup;
    }

    $arPropsList = CIBlockPropertyEnum::GetList(
        [
            "SORT" => "ASC",
            "VALUE" => "ASC"
        ],
        [
            'IBLOCK_ID' => $arParams['ID_BIZPROC'],
            'CODE' => 'TYPE'
        ]
    );

    while ($arVal = $arPropsList->Fetch()) {
        $arResult['ARTYPE'][] = $arVal;
    }

}

$arResult["__TEMPLATE_FOLDER"] = $this->__folder;
$this->__component->arResult = $arResult;
