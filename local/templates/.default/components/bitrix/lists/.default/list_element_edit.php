<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
\Bitrix\Main\Loader::includeModule('bizproc');
CJSCore::Init(['jquery']);

$iTemplateID = 0;
$arInfoIBlock = GetIBlock($arResult['VARIABLES']['list_id'], "bitrix_processes");
if (!empty($arInfoIBlock['CODE'])) {
    $bizprocAdd[$arInfoIBlock['ID']] = $arInfoIBlock['CODE'];

    $obBPloader = CBPWorkflowTemplateLoader::GetLoader();
    $obTemplateBP = CBPWorkflowTemplateLoader::GetList($arOrder = array("ID" => "DESC"), ['SYSTEM_CODE' => $arInfoIBlock['CODE'], 'DOCUMENT_TYPE' => ['lists', 'BizprocDocument', 'iblock_' . $arInfoIBlock['ID']]], false, false, ['ID', 'SYSTEM_CODE']);
    if (intval($obTemplateBP->SelectedRowsCount()) == 0) {
        $iTemplateID = $obBPloader->Add([
            'DOCUMENT_TYPE' => ['lists', 'BizprocDocument', 'iblock_' . $arInfoIBlock['ID']],
            'AUTO_EXECUTE' => '1',
            'NAME' => 'Шаблон бизнес-процесса',
            'TEMPLATE' => [
                [
                    'Type' => 'SequentialWorkflowActivity',
                    'Name' => 'Template',
                    'Properties' => [
                        'Title' => 'Последовательный бизнес-процесс',
                        'Permission' => [
                            "D" => [],
                            "E" => [],
                            "R" => [],
                            "S" => [],
                            "T" => [],
                            "U" => [],
                            "W" => [],
                            "X" => [],
                        ],
                        'Children' => [],
                    ]
                ]
            ],
            'SYSTEM_CODE' => $arInfoIBlock['CODE']
        ]);
    }
    else {
        $iTemplateID = $obTemplateBP->fetch()['ID'];
    }
}

if (empty($arResult["VARIABLES"]["element_id"])) {
    if ($arResult['VARIABLES']['list_id'] == 479) {
        include $_SERVER['DOCUMENT_ROOT'] . "/zayavka-na-izmenenie-pd/index.php";
        return;
    }
    if ($arResult['VARIABLES']['list_id'] == 491) {
        include $_SERVER['DOCUMENT_ROOT'] . "/propusk-massovy/index.php";
        return;
    }
    if ($arResult['VARIABLES']['list_id'] == 595) {
        include $_SERVER['DOCUMENT_ROOT'] . "/mfc/propusk-massovy/index.php";
        return;
    }
    if ($arResult['VARIABLES']['list_id'] == 492) {
        $_GET['BP_PROPUSK_VODY'] = 1;
        include $_SERVER['DOCUMENT_ROOT'] . "/propusk-massovy/index.php";
        return;
    }
    if ($arResult['VARIABLES']['list_id'] == 484) {
        include $_SERVER['DOCUMENT_ROOT']
            . "/zayavka-na-transport-za-predely-tulskoy-oblasti/index.php";
        return;
    }
    if ($arResult['VARIABLES']['list_id'] == 507) {
        include $_SERVER['DOCUMENT_ROOT'] . "/zayavka-na-parkovku/index.php";
        return;
    }
    if ($arResult['VARIABLES']['list_id'] == 125) {
        LocalRedirect(SITE_DIR . 'zayavka-na-vremennyy-propusk/');
        return;
    }

    if (in_array($arResult['VARIABLES']['list_id'], array_keys($bizprocAdd))) {
        $APPLICATION->IncludeComponent(
            'citto:bizproc',
            $bizprocAdd[$arResult['VARIABLES']['list_id']],
            [
                "ID_BIZPROC" => $arResult['VARIABLES']['list_id'],
                "ID_TEMPLEATE" => $iTemplateID,
                "CACHE_TYPE" => "N",
                "CACHE_TIME" => "3600",
                "SEF_MODE" => "Y",
                "AJAX_MODE" => "Y",
                "AJAX_OPTION_SHADOW" => "N",
                "AJAX_OPTION_JUMP" => "N",
                "AJAX_OPTION_STYLE" => "Y",
                "AJAX_OPTION_HISTORY" => "N"
            ],
            null
        );
        return;
    }
    if ($arResult['VARIABLES']['list_id'] == 526 && !$GLOBALS['USER']->IsAdmin()) {
        echo "Нет доступа";
        return;
    }
}
if (file_exists(__DIR__ . "/" . $arResult['VARIABLES']['list_id'] . ".js")) {
    $APPLICATION->AddHeadScript($this->GetFolder() . "/" . $arResult['VARIABLES']['list_id'] . ".js");
}
if (file_exists(__DIR__ . "/" . $arResult['VARIABLES']['list_id'] . ".css")) {
    $APPLICATION->SetAdditionalCSS($this->GetFolder() . "/" . $arResult['VARIABLES']['list_id'] . ".css");
}
$arUser = $GLOBALS['userFields']($GLOBALS['USER']->GetId());
?>
    <script>
        var USER_WORK_POSITION = "<?=$arUser ? addslashes($arUser['WORK_POSITION']) : "";?>";
        var USER_DEPARTMENT = "<?=$arUser ? addslashes($arUser['DEPARTMENT']) : "";?>";
        var USER_FIO = "<?=$arUser ? addslashes($GLOBALS['USER']->GetFullName()) : "";?>";
    </script>
    <style>
        body div.bx-form-notes, body table.bx-interface-grid td input, body table.bx-interface-grid td textarea, body table.bx-interface-grid td select, body table.bx-edit-table td.bx-field-value select,
        body table.bx-edit-table td.bx-field-name {
            font-size: 16px;
        }
    </style>
<? if ($arParams["IBLOCK_TYPE_ID"] == "bitrix_processes"):
    CJSCore::Init(["jquery"]);
    $APPLICATION->AddHeadScript($this->GetFolder() . "/script.js");
    ?>
    <style>
        .bx-edit-tabs {
            display: none;
        }

        input[name="apply"] {
            display: none;
        }
    </style>
<? endif; ?>
<?
$APPLICATION->IncludeComponent("bitrix:lists.element.edit", ".default", array(
    "IBLOCK_TYPE_ID" => $arParams["IBLOCK_TYPE_ID"],
    "IBLOCK_ID" => $arResult["VARIABLES"]["list_id"],
    "SECTION_ID" => $arResult["VARIABLES"]["section_id"],
    "ELEMENT_ID" => $arResult["VARIABLES"]["element_id"],
    "LISTS_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["lists"],
    "LIST_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["list"],
    "LIST_ELEMENT_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["list_element_edit"],
    "LIST_FILE_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["list_file"],
    "BIZPROC_LOG_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["bizproc_log"],
    "BIZPROC_WORKFLOW_START_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["bizproc_workflow_start"],
    "BIZPROC_TASK_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["bizproc_task"],
    "BIZPROC_WORKFLOW_DELETE_URL" => $arResult["FOLDER"] . $arResult["URL_TEMPLATES"]["bizproc_workflow_delete"],
    "CACHE_TYPE" => $arParams["CACHE_TYPE"],
    "CACHE_TIME" => $arParams["CACHE_TIME"],
),
    $component
);
