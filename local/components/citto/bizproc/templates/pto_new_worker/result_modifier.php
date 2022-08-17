<?php

use Monolog\Logger;
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use CIBlockElement;
use Bitrix\Bizproc\WorkflowInstanceTable;
use Monolog\Handler\RotatingFileHandler;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

global $APPLICATION, $USER, $userFields, $morphFunct;

$arUserFields = $userFields;
$arRequest = $arResult['REQUEST'];
Loader::includeModule("iblock");
Loader::includeModule('citto.filesigner');
Loader::includeModule('workflow');
Loader::includeModule("intranet");
Loader::includeModule('documentgenerator');

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/podrazdeleniya_tree.php';
CJSCore::Init(["date"]);

$APPLICATION->SetTitle("ПТО шапка выхода НС");

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

$obLogger = new Logger('sc_report');
$obLogger->pushHandler(
    new RotatingFileHandler(
        $_SERVER['DOCUMENT_ROOT'] . '/local/logs/pto_new_worker/actions.log',
        60
    )
);

$arResult['OTDELLIST'] = $treeFunc(IBLOCK_ID_STRUCTURE, 53, 5);

function getProperties($iBlockID)
{
    $obListWorkflow = new CList($iBlockID);
    $arListWorkflow = $obListWorkflow->getFields();

    $arProperties = [];
    $obProperties = CIBlockProperty::GetList(array("sort" => "asc", "name" => "asc"), array("ACTIVE" => "Y", "IBLOCK_ID" => $iBlockID));
    while ($arField = $obProperties->GetNext()) {
        if ($arField['USER_TYPE']) {
            $sType = mb_strtoupper($arField['USER_TYPE']);
        } elseif ($arField['PROPERTY_TYPE'] == 'L') {
            $sType = 'LIST';
        } elseif ($arField['PROPERTY_TYPE'] == 'S') {
            $sType = 'STRING';
        } elseif ($arField['PROPERTY_TYPE'] == 'F') {
            $sType = 'FILE';
        } elseif ($arField['PROPERTY_TYPE'] == 'N') {
            $sType = 'NUMBER';
        }

        if ($arWFSettins = $arListWorkflow["PROPERTY_" . $arField['ID']]['SETTINGS']) {
            $arField['SHOW'] = $arWFSettins['SHOW_ADD_FORM'];
        }

        if ($sType) {
            $arField['TYPE'] = $sType;
            $arProperties[$arField['ID']] = $arField;
        }
    }

    $obEnums = CIBlockPropertyEnum::GetList(array("DEF" => "DESC", "SORT" => "ASC"), array("IBLOCK_ID" => $iBlockID));
    while ($arEnum = $obEnums->GetNext()) {
        if ($arProperties[$arEnum['PROPERTY_ID']]) {
            $arProperties[$arEnum['PROPERTY_ID']]['ENUMS'][$arEnum['ID']] = $arEnum;
        }
    }

    return $arProperties;
}

$arResult['FIELDS'] = getProperties($arParams['ID_BIZPROC']);

if ($arRequest['pto_new_worker'] && $arRequest['pto_new_worker'] == 'add') {
    try {
        Loader::includeModule("workflow");
        Loader::includeModule("bizproc");

        if (!check_bitrix_sessid()) {
            throw new Exception('Проблема с сессией, обновите страницу');
        }

        $arProps = [];

        foreach ($arResult['FIELDS'] as $arField) {
            $sCode = $arField['CODE'];
            if ($arField['IS_REQUIRED'] == 'Y') {
                if (empty($arRequest[$sCode]) && empty($_FILES[$sCode]['name'][0])) {
                    throw new Exception("Заполните поле: '{$arField['NAME']}'");
                }
            }

            $sValue = $arRequest[$sCode];

            if ($arField['TYPE'] == 'DATE' && $sValue) {
                $arProps[$sCode] = (new DateTime($sValue))->format('d.m.Y');
            } elseif ($arField['TYPE'] == 'FILE' && $_FILES[$sCode]['name'][0]) {
                $arFilesInfo = $_FILES[$sCode];
                $arProps[$sCode] = $_FILES[$sCode];
            } else {
                $arProps[$sCode] = $sValue;
            }
        }
        //формирование служебной записки
        $needZap = true;
        if ($needZap) {
            $objFile = new Bitrix\Main\IO\File(
                $_SERVER["DOCUMENT_ROOT"] . '/local/templates_docx/shablon_zap.docx'
            );
            $objBody = new Bitrix\DocumentGenerator\Body\Docx($objFile->getContents());
            $objBody->normalizeContent();

            foreach ($arResult['OTDELLIST'] as $arOtdel)
            {
                if ($arOtdel['ID'] == $arRequest['OIV'])
                {
                    $arRequest['OIV'] = $arOtdel['NAME'];
                }
            }
            $arDocument = [
                'NAIMENOVANIE_OIV' => $arRequest['OIV'],
                'DOLZHNOST' => $arRequest['POSITION'],
                'FIO' => $arRequest['FIO'],
                'DATA_ROZHDENIA' => $arProps['BIRTHDAY'],
                'TELEFON' => $arRequest['PHONE'],
                'OKLAD' => $arRequest['SALARY'],
                'NADBAVKA' => $arRequest['SURCHARGE'],
                'POOSHRENIE' => $arRequest['PROMOTION']
            ];

            $objBody->setValues($arDocument);
            $objBody->process();
            $strContent = $objBody->getContent();

            $docPath = '/upload/bp/' . $intIBLOCK_ID . '/';
            $strFileName = 'EP_shablon_zap_' . crc32(serialize(microtime())) . '.docx';
            $strPathDoc = $_SERVER['DOCUMENT_ROOT'] . $docPath;
            if (!mkdir($strPathDoc, 0775, true) && !is_dir($strPathDoc)) {
                throw new RuntimeException('Directory "' . $strPathDoc . '" was not created');
            }
            $resCreate = file_put_contents($strPathDoc . $strFileName, $strContent);
            $arProps['NOTE'] = \CFile::MakeFileArray($strPathDoc . $strFileName);
        }

        $arLoadProductArray = [
            'MODIFIED_BY' => $USER->GetId(),
            'IBLOCK_SECTION_ID' => false,
            'IBLOCK_ID' => $arParams['ID_BIZPROC'],
            'PROPERTY_VALUES' => $arProps,
            'NAME' => $APPLICATION->GetTitle(),
            'ACTIVE' => "Y",
            'PREVIEW_TEXT' => '',
        ];

        $objEl = new CIBlockElement();
        $boolDocumentid = $objEl->Add($arLoadProductArray);
        $obLogger->info('ADD', ['ID' => $boolDocumentid, 'DATA' => $arLoadProductArray]);
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
        $arResult['message'] = "<p>Ваша заявка принята!</p>";
    } catch (Exception $exc) {
        $arResult['message'] = $exc->getMessage();
    }
} elseif (isset($arRequest['OIV'])) {
    $strIDajax = $arRequest['bxajaxid'];
    $intMODULEID = 'bizproc';

    $intOtdelID = CIntranetUtils::GetDepartmentManagerID($arRequest['OIV']);

    $NACHALNIC_OTDELA = ($intOtdelID) ? $intOtdelID : false;

    $strIDajax = $arRequest['bxajaxid'];

    $arResult['code'] = "OK";
    $arResult['message'] = '';
    $arResult['glava_id'] = $NACHALNIC_OTDELA ? $NACHALNIC_OTDELA : '';
    $arResult['ajaxid'] = $strIDajax;

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
