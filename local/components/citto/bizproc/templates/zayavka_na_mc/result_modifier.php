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
include_once __DIR__ . '/functions/index.php';
include_once __DIR__ . '/constants/index.php';
CJSCore::Init(["date"]);

$APPLICATION->SetTitle("Заявка на пропуск на вынос МЦ из здания");

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

if (isset($arRequest['zayavka-na-mc'])) {
    try {
        Loader::includeModule("workflow");
        Loader::includeModule("bizproc");

        if (!check_bitrix_sessid()) {
            throw new Exception('Проблема с сессией, обновите страницу');
        }

        $intDepartment = $arRequest['MC_VEDOMSTVO'];
        $intSotrudnic = $arRequest['MC_SOTRUDNIK'];
        $intRucovoditel = $arRequest['MC_RUKOVODITEL_SOGLASOVANIE_PROPUSK'];
        $intBuhRucovoditel = $arRequest['MC_BUKHGALTER'];
        $arrZamBuh = empty($arRequest['MC_ZAMESTITELI_GLAVNOGO_BUKHGALTERA']) ? [] : explode('|', $arRequest['MC_ZAMESTITELI_GLAVNOGO_BUKHGALTERA']);
        $intViborzdaniya = $arRequest['MC_VYBOR_ZDANIYA'];
        $strKontactTelefon = $arRequest['MC_KONTAKTNYY_TELEFON'];
        $intMatOtv = $arRequest['MC_MATERIALNO_OTVETSTVENNIY'];
        $datetimeVremavinosa = (new DateTime($arRequest['MC_DATA_VREMYA_VYNOSA']))->format("d.m.Y H:i:s");
        $strIDajax = $arRequest['bxajaxid'];

        $intKolichestvo = 0;
        if (!empty($arRequest['MC_NAIMENOVANIE_MC'])) {
            $strTableMCList = '<table border="1" cellpadding="3" cellspacing="0" style="border-color: #999; margin: 20px 0;">';
            $strTableMCList .= '<tr><td border="1" style="border-color: #999;">Название МЦ</td><td border="1" style="border-color: #999;">Инвентарный номер МЦ</td></tr>';
            $strNaimenovaniemc = '';
            foreach ($arRequest['MC_NAIMENOVANIE_MC'] as $ind => $item) {
                $intKolichestvo++;
                $strTableMCList .= '<tr><td border="1" style="border-color: #999;">' . $item . '</td><td border="1" style="border-color: #999;">' . $arRequest['MC_INVENTARNY_NOMER'][$ind] . '</td></tr>';
                $strNaimenovaniemc .= $item . ' (инв № ' . $arRequest['MC_INVENTARNY_NOMER'][$ind] . '), ' . PHP_EOL;
            }
            $strTableMCList .= '</table>';
        }

        $SPISOK['MODULE_ID'] = 'bizproc';

        $arrDepData = CIBlockSection::GetByID($intDepartment)->Fetch();

        $arProps = [
            'MC_VEDOMSTVO' => $arrDepData['NAME'] ?? $arrDepData['NAME'],
            'MC_SOTRUDNIK' => $intSotrudnic,
            'MC_RUKOVODITEL_SOGLASOVANIE_PROPUSK' => $intRucovoditel,
            'MC_BUKHGALTER' => $intBuhRucovoditel,
            'MC_VYBOR_ZDANIYA' => $intViborzdaniya,
            'MC_KONTAKTNYY_TELEFON' => $strKontactTelefon,
            'MC_NAIMENOVANIE_MC' => $strNaimenovaniemc,
            'MC_KOLICHESTVO_MC' => $intKolichestvo,
            'MC_DATA_VREMYA_VYNOSA' => $datetimeVremavinosa,
            'MC_ZAMESTITELI_GLAVNOGO_BUKHGALTERA' => $arrZamBuh,
            'MC_MATERIALNO_OTVETSTVENNIY' => $intMatOtv
        ];

        $arZayavka_file_props = $arProps;

        $arrZdaniya = CIBlockProperty::GetPropertyEnum('MC_VYBOR_ZDANIYA', ['value' => "ASC"], ['IBLOCK_ID' => IBLOCK_ID_ZNMC]);
        while ($arZdaniyeItem = $arrZdaniya->GetNext()) {
            $arZdaniyaList[$arZdaniyeItem['ID']] = [
                'ID' => $arZdaniyeItem['ID'],
                'NAME' => $arZdaniyeItem['VALUE']
            ];
        }

        $arrSotrudnicData = $userFields($intSotrudnic);
        $arZayavka_file_props['MC_DOLJNOST'] = mb_strlen($arrSotrudnicData['WORK_POSITION']) > 0 ? $arrSotrudnicData['WORK_POSITION'] : $arrSotrudnicData['DOLJNOST'];
        $arZayavka_file_props['MC_FIO'] = $arrSotrudnicData['LAST_NAME'] . ' ' . $arrSotrudnicData['NAME'];

        $arZayavka_file_props['MC_VYBOR_ZDANIYA'] = $arZdaniyaList[$intViborzdaniya]['NAME'] ?? '';
        $arZayavka_file_props['MC_DEPARTMENT_TEXT'] = $arZayavka_file_props['MC_VEDOMSTVO'];
        $arZayavka_file_props['MC_NAIMENOVANIE_MC'] = $strTableMCList;

        $arrRucovoditelData = $userFields($intRucovoditel);
        $arZayavka_file_props['MC_RUKOVODITEL_TEXT'] = $arrRucovoditelData ? $arrRucovoditelData['LAST_NAME'] . ' ' . $arrRucovoditelData['NAME'] : '';

        $arrMatOtvData = $userFields($intMatOtv);
        $arZayavka_file_props['MC_MATERIALNO_OTVETSTVENNIY_TEXT'] = $arrMatOtvData ? $arrMatOtvData['LAST_NAME'] . ' ' . $arrMatOtvData['NAME'] : '';

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

        $el = new CIBlockElement();
        $arLoadProductArray = [
            'MODIFIED_BY' => $USER->GetId(),
            'IBLOCK_SECTION_ID' => false,
            'IBLOCK_ID' => IBLOCK_ID_ZNMC,
            'PROPERTY_VALUES' => $arProps,
            'NAME' => $APPLICATION->GetTitle(),
            'ACTIVE' => "Y",
            'PREVIEW_TEXT' => '',
        ];

        $boolDocumentid = $el->Add($arLoadProductArray);
        if (!$boolDocumentid) {
            throw new Exception($el->LAST_ERROR);
        }

        if (!$GLOBALS['setElementPDFValue']($boolDocumentid, 'MC_FAYL_ZAYAVKI_NA_PROPUSK', $strContent, "Заявка на пропуск выноса МЦ для " . $arZayavka_file_props['MC_FIO'])) {
            CIBlockElement::Delete($boolDocumentid);
            throw new Exception("Не удалось создать файл");
        }

        $arErrorsTmp = [];

        $wfId = CBPDocument::StartWorkflow(
            BP_TEMPLATE_ID,
            ["lists", "BizprocDocument", $boolDocumentid],
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
    $arResult['ID_MC_SOTRUDNIK'] = $arUser['ID'];
    $arResult['MC_SOTRUDNIK_NAME'] = $arUser['LAST_NAME'] . ' ' . $arUser['NAME'];

    $arResult['OTDELLIST'] = array_merge(
        treeFunc(IBLOCK_ID_STRUCTURE, BP_SECTION_MIN_TRUD, 1, []),
        treeFunc(IBLOCK_ID_STRUCTURE, BP_SECTION_MIN_INF, 1, []),
        treeFunc(IBLOCK_ID_STRUCTURE, BP_SECTION_ORG_ISP_POWER, 0, []),
        treeFunc(IBLOCK_ID_STRUCTURE, BP_SECTION_ORG_ISP_POWER_2, 0, []),
        treeFunc(IBLOCK_ID_STRUCTURE, BP_SECTION_ORG_ISP_POWER_3, 0, [])
    );
    $arResult['DISABLED_OTDEL'] = false;
    $arResult['TOP_VED_LVL'] = CIBlockSection::GetList(
        ["SORT" => "ASC"],
        ['IBLOCK_ID' => IBLOCK_ID_STRUCTURE, 'GLOBAL_ACTIVE' => 'Y', 'SECTION_ID' => BP_SECTION_ROOT],
        false,
        ['UF_HEAD', 'UF_BUHGALTER', 'UF_BUHGALTER_ZAM']
    );

    while ($arTOP_VED_LVL = $arResult['TOP_VED_LVL']->GetNext()) {
        if (in_array($arTOP_VED_LVL['ID'], [BP_SECTION_ORG_SELF_POWER_1, BP_SECTION_ORG_SELF_POWER_2])) {
            $arResult['OTDELLIST'][] = $arTOP_VED_LVL;
        }
    }

    $arResult['DEFAULT_DEP_ID'] = 0;
    foreach ($arResult['OTDELLIST'] as $arDep) {
        if ($arDep['NAME'] == $arUser['DEPARTMENT']) {
            $bHasCITTO = false;
            $arChildsCITIDs = CIntranetUtils::GetIBlockSectionChildren(BP_SECTION_CITTO_DEPARTMENT);
            foreach ($arUser['UF_DEPARTMENT'] as $iDepID) {
                if (in_array($iDepID, $arChildsCITIDs)) {
                    $bHasCITTO = true;
                }
            }

            $arResult['DEFAULT_DEP_ID'] = $arDep['ID'];
            if ($bHasCITTO) {
                $objDepCITTO = CIBlockSection::GetList(
                    ["SORT" => "ASC"],
                    ['IBLOCK_ID' => IBLOCK_ID_STRUCTURE, 'GLOBAL_ACTIVE' => 'Y', 'ID' => BP_SECTION_CITTO_DEPARTMENT],
                    false,
                    ['UF_HEAD', 'UF_BUHGALTER', 'UF_BUHGALTER_ZAM']
                );
                $arDepAdd = $objDepCITTO->GetNext();

                $arResult['RUKOVODITEL_ID'] = $arDepAdd['UF_HEAD'];
                $arResult['BUHGALTER_ID'] = $arDepAdd['UF_BUHGALTER'];
                $arResult['BUHGALTER_ZAM_IDS'] = $arDepAdd['UF_BUHGALTER_ZAM'] ? implode('|', $arDepAdd['UF_BUHGALTER_ZAM']) : '';
            } else {
                $arResult['RUKOVODITEL_ID'] = $arDep['UF_HEAD'];
                $arResult['BUHGALTER_ID'] = $arDep['UF_BUHGALTER'];
                $arResult['BUHGALTER_ZAM_IDS'] = $arDep['UF_BUHGALTER_ZAM'] ? implode('|', $arDep['UF_BUHGALTER_ZAM']) : '';
            }
        }  else if ($arUser['DEPARTMENTS'][BP_SECTION_SIT_CENTR] && $arDep['ID'] == BP_SECTION_SIT_CENTR) {
            /* Ситуационный центр */
            $arResult['RUKOVODITEL_ID'] = $arDep['UF_HEAD'];
            $arResult['BUHGALTER_ID'] = $arDep['UF_BUHGALTER'];
            $arResult['BUHGALTER_ZAM_IDS'] = $arDep['UF_BUHGALTER_ZAM'] ? implode('|', $arDep['UF_BUHGALTER_ZAM']) : '';
            break;
        }  else if ($arUser['DEPARTMENTS'][BP_SECTION_CZN_DEPARTMENT] && $arDep['ID'] == BP_SECTION_CZN_DEPARTMENT) {
            /* ЦЗН */
            /* 4388 - Брысова Е.В. */
            $arResult['RUKOVODITEL_ID'] = $arDep['UF_HEAD'];
            $arResult['BUHGALTER_ID'] = ($arDep['UF_BUHGALTER']) ? $arDep['UF_BUHGALTER'] : 4388;
            $arResult['BUHGALTER_ZAM_IDS'] = $arDep['UF_BUHGALTER_ZAM'] ? implode('|', $arDep['UF_BUHGALTER_ZAM']) : '';
            $arResult['DEFAULT_DEP_ID'] = BP_SECTION_CZN_DEPARTMENT;
            break;
        }
    }


    if ($arResult['DEFAULT_DEP_ID'] == 0) {
        $iUserID = $USER->GetID();
        $arDepartments = [];
        $obDepartments = CIBlockSection::GetList([], ["IBLOCK_ID" => 5, 'ACTIVE' => 'Y', '>DEPTH_LEVEL' => 1, ''], false, ['ID', 'NAME', 'IBLOCK_SECTION_ID', 'DEPTH_LEVEL', 'UF_PODVED']);

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

        while ($arDep = $obDepartments->GetNext()) {
            $iID = $arDep['ID'];
            $sName = $arDep['NAME'];
            $arDepartments[$iID] = [
                'NAME' => $sName,
                'DEPTH' => (int)$arDep['DEPTH_LEVEL'],
                'PARENT_ID' => $arDep['IBLOCK_SECTION_ID'],
                'ID' => $arDep['ID'],
            ];
        }

        foreach ($arDepartments as $iDepID => $arDepartment) {
            if ($arDepartment['DEPTH'] > 3) {
                $arRecDepartment = recDepartment($iDepID, $arDepartments);
                $arDepartments[$iDepID]['NAME'] = $arRecDepartment['NAME'];
                $arDepartments[$iDepID]['ID'] = $arRecDepartment['ID'];
            }
        }

        $obUsers = UserTable::getList([
            'select' => ['ID', 'UF_DEPARTMENT'],
            'filter' => ['ID' => $iUserID]
        ]);

        $arUsers = [];
        while ($arUser = $obUsers->fetch()) {
            $iDepartment = current($arUser['UF_DEPARTMENT']);
            if (!empty($arDepartments[$iDepartment])) {
                $a = $arDepartments[$iDepartment];
                $arUsers[$arUser['ID']] = $arDepartments[$iDepartment]['ID'];
            }
        }

        $arResult['DEFAULT_DEP_ID'] = $arUsers[$iUserID];
    }

    foreach ($arResult['OTDELLIST'] as $arOtdel) {
        if ($arResult['DEFAULT_DEP_ID'] == $arOtdel['ID'] && $arOtdel['ID'] != 0) {
            $arResult['DISABLED_OTDEL'] = true;
        }
    }

    $arMatOtv = (isUserCit($iCitID = 57)) ? [138, 2650, 481] : [];

    $listUsersAll = (new CUser())->GetList($by = "LAST_NAME", $order = "ASC", ["ACTIVE" => 'Y', "!LAST_NAME" => null, "!NAME" => null]);

    while ($ruc = $listUsersAll->Fetch()) {
        if (!empty($ruc['LAST_NAME'])) {
            $arResult['RUKOVODITEL'][] = $ruc;
            if (in_array($ruc['ID'], $arMatOtv) && !empty($arMatOtv)) {
                $arResult['MATERIALNO_OTVETSTVENNIY'][] = $ruc;
            } elseif (empty($arMatOtv)) {
                $arResult['MATERIALNO_OTVETSTVENNIY'][] = $ruc;
            }
        }
    }

    $arResult['ZDANIYA'] = [];
    $arrZdaniya = CIBlockProperty::GetPropertyEnum('MC_VYBOR_ZDANIYA', ['value' => "ASC"], ['IBLOCK_ID' => IBLOCK_ID_ZNMC]);
    while ($arZdaniyeItem = $arrZdaniya->GetNext()) {
        $arResult['ZDANIYA'][$arZdaniyeItem['ID']] = [
            'ID' => $arZdaniyeItem['ID'],
            'NAME' => $arZdaniyeItem['VALUE']
        ];
    }
}

$arResult["__TEMPLATE_FOLDER"] = $this->__folder;
$this->__component->arResult = $arResult;
