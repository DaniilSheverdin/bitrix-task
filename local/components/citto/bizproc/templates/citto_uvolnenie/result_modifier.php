<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Citto\Filesigner\ShablonyTable;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * @global var $APPLICATION
 * @global var $USER
 * @var array $arResult
 * @var array $arParams
 */

global $APPLICATION, $USER, $userFields, $morphFunct;

$arUserFields = $userFields;
$arRequest = $arResult['REQUEST'];

Loader::includeModule("iblock");
Loader::includeModule("intranet");
Loader::includeModule('citto.filesigner');
Loader::includeModule("workflow");
Loader::includeModule("bizproc");

CJSCore::Init(["date"]);

$APPLICATION->SetTitle("Увольнение сотрудника ЦИТ");

Asset::getInstance()->addJs('/bitrix/templates/.default/jquery.min.js');
Asset::getInstance()->addJs("/local/js/bootstrap-plugin/popper.min.js");
Asset::getInstance()->addJs('/bitrix/templates/.default/bootstrap.min.js');
Asset::getInstance()->addJs('/' . basename(__DIR__) . '/index.js');
Asset::getInstance()->addJs('/local/js/bootstrap-plugin/bootstrap-select.min.js');

Asset::getInstance()->addJs('/local/activities/custom/docsignactivity/js/es6-promise.min.js');
Asset::getInstance()->addJs('/local/activities/custom/docsignactivity/js/ie_eventlistner_polyfill.js');
Asset::getInstance()->addJs("/local/activities/custom/docsignactivity/js/cadesplugin_api.js");
Asset::getInstance()->addJs("/local/activities/custom/docsignactivity/js/plugin.js");
Asset::getInstance()->addJs("/local/components/citto/bizproc/js/docsignactivity.js");

$APPLICATION->SetAdditionalCSS('/bitrix/templates/.default/bootstrap.min.css');
$APPLICATION->SetAdditionalCSS('/' . basename(__DIR__) . '/main.css');
$APPLICATION->SetAdditionalCSS('/local/css/bootstrap-plugin/bootstrap-select.min.css');

$strMODULEID = 'bizproc';

$objPropsPrichina = CIBlockPropertyEnum::GetList(
    [
        "SORT" => "ASC",
        "VALUE" => "ASC"
    ],
    [
        'IBLOCK_ID' => $arParams['ID_BIZPROC'],
        'CODE' => 'PROSHU_RASTORGNUT_TRUDOVOY_DOGOVOR'
    ]
);

while ($arVal = $objPropsPrichina->Fetch()) {
    $arResult['PROSHU_RASTORGNUT_TRUDOVOY_DOGOVOR'][$arVal['ID']] = $arVal;
}

if ($arRequest['uved_citto_uvolnenie'] && $arRequest['uved_citto_uvolnenie'] == 'add') {
    try {
        $strIDajax = $arRequest['bxajaxid'];
        $intMODULEID = 'bizproc';

        if (!check_bitrix_sessid()) {
            throw new Exception('Проблема с сессией, обновите страницу');
        }

        $idSotrudnic = $arRequest['DM_SOTRUDNIK'];
        $strDateUvolnenie = $arRequest['DM_DATA_UVOLNENIYA'];
        $intPrichina = $arRequest['PROSHU_RASTORGNUT_TRUDOVOY_DOGOVOR'];

        if (empty($idSotrudnic)) {
            throw new Exception('Укажите сотрудника');
        }
        if (empty($intPrichina)) {
            throw new Exception('Укажите причину увольнения');
        }
        if (empty($strDateUvolnenie)) {
            throw new Exception('Укажите дату увольнения');
        }

        if (empty($_FILES['DM_REQUEST_SCAN']['name']) && $arResult['PROSHU_RASTORGNUT_TRUDOVOY_DOGOVOR'][$intPrichina]['XML_ID'] != '4486a1e759e095ca7713eb41de1f3934') {
            throw new Exception('Укажите файл отсканированного заявления в бумажном виде');
        }

        $arMimeTypes = [
            'application/pdf',
            'application/x-rar-compressed',
            'application/zip',
            'application/x-zip-compressed',
            'multipart/x-zip',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];
        if (!empty($_FILES['DM_REQUEST_SCAN']['name']) && !in_array($_FILES['DM_REQUEST_SCAN']['type'], $arMimeTypes)) {
            throw new Exception('Файл заявления может быть только в форматах pdf, doc, docx, rar, zip');
        }

        $arUserInfo = $arUserFields($idSotrudnic);

        if (isset($arUserFields($idSotrudnic)['UF_DEPARTMENT'][0])) {
            $intDep = $arUserFields($idSotrudnic)['UF_DEPARTMENT'][0];
            $arDeptopStruct = CIntranetUtils::GetDepartmentManager([$intDep]);
            $arDeptopStructList = array_pop($arDeptopStruct);
            $intRucDep = $arDeptopStructList['ID'];

            $arStructDeparts = array_reverse(GetParentDepartmentstucture($idSotrudnic));
            if ($arStructDeparts[0] == SECTION_ID_CITTO_STRUCTURE) {
                $intRucUpr = $intRucDep;
            } elseif ($arStructDeparts[1] == SECTION_ID_CITTO_STRUCTURE) {
                $arCurr = CIntranetUtils::GetDepartmentManager([$arStructDeparts[0]]);
                $intRucUprData = array_pop($arCurr);
                $intRucUpr = $intRucUprData['ID'];
            } else {
                throw new Exception('Нельзя уволить сотрудника уровня начальника управления и выше');
            }
        } else {
            throw new Exception('Невозможно определить подразделение сотрудника');
        }

        if (!empty($_FILES['DM_REQUEST_SCAN']['name'])) {
            $arFileArray = array_merge(
                $_FILES['DM_REQUEST_SCAN'],
                [
                    'MODULE_ID' => $strMODULEID
                ]
            );

            $intfileSaveId = CFile::SaveFile(
                $arFileArray,
                'bp/' . $arParams['ID_BIZPROC']
            );
        } else {
            $intfileSaveId = 0;
        }

        $arLoadProductArray = [
            'MODIFIED_BY' => $USER->GetId(),
            'IBLOCK_SECTION_ID' => false,
            'IBLOCK_ID' => $arParams['ID_BIZPROC'],
            'PROPERTY_VALUES' => [
                'DM_FIO' => $arUserInfo['FIO'],
                'DM_DOLZHNOST' => $arUserInfo['DOLJNOST'],
                'DM_SOTRUDNIK' => $idSotrudnic,
                'PROSHU_RASTORGNUT_TRUDOVOY_DOGOVOR' => $intPrichina,
                'DM_DATA_UVOLNENIYA' => (new DateTime($strDateUvolnenie))->format('d.m.Y'),
                'DM_DEPART_LIDER' => $intRucDep,
                'DM_UPRAVLENIE_LIDER' => $intRucUpr,
                'DM_REQUEST_SCAN' => $intfileSaveId
            ],
            'NAME' => $APPLICATION->GetTitle(),
            'ACTIVE' => "Y",
            'PREVIEW_TEXT' => '',
        ];

        $arPrpList = CIBlock::GetProperties(
            $arParams['ID_BIZPROC'],
            [],
            ['CODE' => 'DM_ZAYAVLENIE']
        )->Fetch();

        $strContentObhodList = '';
        $arPrpList = CIBlock::GetProperties(
            $arParams['ID_BIZPROC'],
            [],
            ['CODE' => 'DM_OBKHODNOY_LIST']
        )->Fetch();

        $strSHABLON = ShablonyTable::getScalar(
            [
                'filter' => ['=CODE' => $arParams['ID_BIZPROC'] . "_" . $arPrpList['ID']],
                'limit' => 1,
                'select' => ['SHABLON']
            ]
        );

        $strContentObhodList = str_replace(
            [
                '#DM_FIO#',
                '#DM_DOLZHNOST#',
                '#DM_DATA_UVOLNENIYA#'
            ],
            [
                $morphFunct($arUserInfo['FIO'], 'Р'),
                $morphFunct($arUserInfo['DOLJNOST'], 'Р'),
                FormatDate("j F Y", strtotime($strDateUvolnenie))
            ],
            $strSHABLON
        );

        $objEl = new CIBlockElement();
        $intDocumentid = $objEl->Add($arLoadProductArray);
        if (!$intDocumentid) {
            throw new Exception($objEl->LAST_ERROR);
        }

        $msg = '';
        /*if (!$GLOBALS['setElementPDFValue']($intDocumentid, 'DM_ZAYAVLENIE', $strContentZayavlenie, "Заявление на увольнение сотрудника " . $arUserInfo['FIO'], $msg, $docGenId)) {
            CIBlockElement::Delete($intDocumentid);
            throw new Exception("Не удалось создать файл заявления");
        }*/

        if (!$GLOBALS['setElementPDFValue']($intDocumentid, 'DM_OBKHODNOY_LIST', $strContentObhodList, "Обходной лист сотрудника " . $arUserInfo['FIO'])) {
            CIBlockElement::Delete($intDocumentid);
            throw new Exception("Не удалось создать обходной лист");
        }

        if($intfileSaveId > 0) {
            $arResult['ajaxid'] = $strIDajax;
            $arResult['file_id'] = $intfileSaveId;
            $arResult['code'] = "OK";
            $arResult['message'] = '<p>Бизнес-процесс запущен!</p>';
            $arResult['sessid'] = bitrix_sessid();
            $arResult['documentid'] = $intDocumentid;
            $arErrorsTmp = [];

            $strWfId = CBPDocument::StartWorkflow(
                $arParams['ID_TEMPLEATE'],
                ["lists", "BizprocDocument", $intDocumentid],
                ['TargetUser' => "user_" . $USER->GetID()],
                $arErrorsTmp
            );

        } else {
            $arErrorsTmp = [];

            $strWfId = CBPDocument::StartWorkflow(
                $arParams['ID_TEMPLEATE'],
                ["lists", "BizprocDocument", $intDocumentid],
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
            $arResult['message'] = '<p>Бизнес-процесс запущен!</p>';
        }

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
                $sUserInfo = "$sUserInfo";
            }
            $arRuc['USER_INFO'] = $sUserInfo;
            $arResult['USERS'][] = $arRuc;
        }
    }
}

$arResult["__TEMPLATE_FOLDER"] = $this->__folder;
$this->__component->arResult = $arResult;
