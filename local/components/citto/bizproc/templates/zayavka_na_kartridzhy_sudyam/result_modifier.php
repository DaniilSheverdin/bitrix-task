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
use Bitrix\Highloadblock\HighloadBlockTable as HL;

global $APPLICATION, $USER, $userFields;

$arRequest = $arResult['REQUEST'];
Loader::includeModule("iblock");
Loader::includeModule('highloadblock');

require_once $_SERVER['DOCUMENT_ROOT'] . '/local/php_interface/podrazdeleniya_tree.php';
require_once __DIR__ . '/constants/index.php';
CJSCore::Init(["date"]);

$APPLICATION->SetTitle("Заявка на картриджи для Мировых судей");

Asset::getInstance()->addJs('/bitrix/templates/.default/jquery.min.js');
Asset::getInstance()->addJs("/local/js/bootstrap-plugin/popper.min.js");
Asset::getInstance()->addJs('/bitrix/templates/.default/bootstrap.min.js');
Asset::getInstance()->addJs('/' . basename(__DIR__) . '/index.js');
Asset::getInstance()->addJs('/local/js/bootstrap-plugin/bootstrap-select.min.js');

$APPLICATION->SetAdditionalCSS('/bitrix/templates/.default/bootstrap.min.css');
$APPLICATION->SetAdditionalCSS('/' . basename(__DIR__) . '/main.css');
$APPLICATION->SetAdditionalCSS('/local/css/bootstrap-plugin/bootstrap-select.min.css');

$arUser = $userFields($USER->GetID());

$materialClass = HL::compileEntity(HL::getById(BP_HL_KARTRIDZY)->fetch())->getDataClass();
$arResult['PRINTER_MATERIALS'] = $materialClass::getList(
    [
        'select' => ["*"]
    ]
)->fetchAll();

$printerClass = HL::compileEntity(HL::getById(BP_HL_PRINTERY)->fetch())->getDataClass();
$arResult['PRINTERS'] = $printerClass::getList(
    [
        'select' => ["*"]
    ]
)->fetchAll();

$printerClass = HL::compileEntity(HL::getById(BP_HL_PK_LINK)->fetch())->getDataClass();
$arResult['PK_LINKS'] = $printerClass::getList(
    [
        'select' => ["*"]
    ]
)->fetchAll();

if (isset($arRequest['zayavka-na-kartridzhy-sudam'])) {
    try {
        Loader::includeModule("workflow");
        Loader::includeModule("bizproc");

        if (!check_bitrix_sessid()) {
            throw new Exception('Проблема с сессией, обновите страницу');
        }

        foreach ($arResult['PRINTER_MATERIALS'] as $arPrinterMaterial) {
            $arPRINTER_MATERIALS[$arPrinterMaterial['ID']] = $arPrinterMaterial['UF_WJL_MATERIAL_NAME'];
        }

        foreach ($arResult['PRINTERS'] as $arPrinter) {
            $arPRINTERS[$arPrinter['ID']] = $arPrinter['UF_WJL_PRINTER_NAME'];
        }

        $intUser = $arRequest['WJL_POLZOVATEL'];
        $strFio = $arRequest['WJL_FIO'];
        $strDolzhnost = $arRequest['WJL_DOLZHNOST'];
        $strPodrazhdelenie = $arRequest['WJL_PODRAZDELENE'];
        $datetimePodachaZayavki = (new DateTime($arRequest['WJL_DATA_PODACHI_ZAYAVKI']))->format("d.m.Y H:i:s");
        $strIDajax = $arRequest['bxajaxid'];

        if (!empty($arRequest['WJL_SODERZHANIE_PRINTER'])) {
            $strTableMCList = '<table border="1" cellpadding="3" cellspacing="0" style="border-color: #999; margin: 20px 0;">';
            $strTableMCList .= '<tr><td style="border: 1px solid #999;">Марка и модель МФУ/Принтера</td><td style="border: 1px solid #999;">Расходный материал</td><td style="border: 1px solid #999;">Количество</td></tr>';

            foreach ($arRequest['WJL_SODERZHANIE_PRINTER'] as $ind => $item) {
                if (empty($arPRINTERS[$item])) {
                    throw new Exception('Выберите принтер из выпадающего списка');
                }
                if (empty($arPRINTER_MATERIALS[$arRequest['WJL_SODERZHANIE_MATERIAL'][$ind]])) {
                    throw new Exception('Выберите картридж для принтера из выпадающего списка');
                }
                $strTableMCList .= '<tr><td style="border: 1px solid #999;">' . $arPRINTERS[$item] . '</td><td style="border: 1px solid #999;">' . $arPRINTER_MATERIALS[$arRequest['WJL_SODERZHANIE_MATERIAL'][$ind]] . '</td><td style="border: 1px solid #999;">' . $arRequest['WJL_SODERZHANIE_NUMBER'][$ind] . '</td></tr>';
            }
            $strTableMCList .= '</table>';
        }

        $SPISOK['MODULE_ID'] = 'bizproc';

        $arProps = [
            'WJL_POLZOVATEL' => $intUser,
            'WJL_FIO' => $strFio,
            'WJL_DOLZHNOST' => $strDolzhnost,
            'WJL_PODRAZDELENE' => $strPodrazhdelenie,
            'WJL_DATA_PODACHI_ZAYAVKI' => $datetimePodachaZayavki,
            'WJL_SODERZHANIE' => $strTableMCList
        ];

        $arZayavka_file_props = $arProps;

        $strContent = str_replace(
            array_map(
                function ($item) {
                    return "#" . $item . "#";
                },
                array_keys($arZayavka_file_props)
            ),
            $arZayavka_file_props,
            file_get_contents(__DIR__ . '/pdftpl/zayavka.html')
        );

        $arProps['WJL_SODERZHANIE'] = ['VALUE' => ['TEXT' => $arProps['WJL_SODERZHANIE'], 'TYPE' => 'HTML']];
        $el = new CIBlockElement();
        $arLoadProductArray = [
            'MODIFIED_BY' => $USER->GetId(),
            'IBLOCK_SECTION_ID' => false,
            'IBLOCK_ID' => IBLOCK_ID_ZNKS,
            'PROPERTY_VALUES' => $arProps,
            'NAME' => $APPLICATION->GetTitle(),
            'ACTIVE' => "Y",
            'PREVIEW_TEXT' => '',
        ];

        $intDocumentid = $el->Add($arLoadProductArray);
        if (!$intDocumentid) {
            throw new Exception($el->LAST_ERROR);
        }

        /*if (!$GLOBALS['setElementPDFValue']($intDocumentid, 'WJL_DOKUMENT_FORMY_ZAYAVKI', $strContent, "Заявка на расходные материалы к оргтехнике " . $arZayavka_file_props['WJL_FIO'])) {
            CIBlockElement::Delete($intDocumentid);
            throw new Exception("Не удалось создать файл");
        }*/

        $arErrorsTmp = [];

        $wfId = CBPDocument::StartWorkflow(
            BP_TEMPLATE_ID,
            ["lists", "BizprocDocument", $intDocumentid],
            ['TargetUser' => "user_" . $intUser],
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
    $arResult['WJL_POLZOVATEL'] = $arUser['ID'];
    $arResult['WJL_FIO'] = !empty($arUser['FIO']) ? $arUser['FIO'] : $arUser['LAST_NAME'] . ' ' . $arUser['NAME'];

    $arResult['WJL_DOLZHNOST'] = !empty($arUser['WORK_POSITION']) ? $arUser['WORK_POSITION'] : $arUser['DOLJNOST_CLEAR'];
    $arResult['WJL_PODRAZDELENE'] = $arUser['DEPARTMENTS'][0] ?? '';
    $arResult['WJL_DATA_PODACHI_ZAYAVKI'] = (new \DateTime())->format("d.m.Y H:i");

    foreach ($arResult['PRINTER_MATERIALS'] as $pmk => $ipmat) {
        foreach ($arResult['PK_LINKS'] as $lnk => $litem) {
            if ($ipmat['ID'] == $litem['UF_KARTRIDGE_ID']) {
                $arResult['PRINTER_MATERIALS'][$pmk]['printer'][] = $litem['UF_PRINTER_ID'];
            }
        }
    }

    foreach ($arResult['PRINTERS'] as $k => $iprint) {
        foreach ($arResult['PK_LINKS'] as $lnk => $litem) {
            if ($iprint['ID'] == $litem['UF_PRINTER_ID']) {
                $arResult['PRINTERS'][$k]['k_material'][] = $litem['UF_KARTRIDGE_ID'];
            }
        }
    }

    $listUsersAll = (new CUser())->GetList($by = "LAST_NAME", $order = "ASC", ["ACTIVE" => 'Y', "!LAST_NAME" => null, "!NAME" => null]);

    while ($curr = $listUsersAll->Fetch()) {
        if (!empty($curr['LAST_NAME'])) {
            $arResult['USERS'][] = $curr;
        }
    }
}

$arResult["__TEMPLATE_FOLDER"] = $this->__folder;
$this->__component->arResult = $arResult;
