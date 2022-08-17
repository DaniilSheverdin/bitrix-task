<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

if (isset($arParams["COMPONENT_ENABLE"]) && $arParams["COMPONENT_ENABLE"] === false) {
    return;
}

// Режим разработки под админом
$bDesignMode = $GLOBALS["APPLICATION"]->GetShowIncludeAreas() && is_object($GLOBALS["USER"]) && $GLOBALS["USER"]->IsAdmin();

if (!$bDesignMode && $arParams["IS_AJAX"] == "Y") {
    $APPLICATION->RestartBuffer();
    header("Content-type: application/json; charset=" . LANG_CHARSET);
}

$arNavParams = CDBResult::GetNavParams();

// Дополнительно кешируем текущую страницу
$ADDITIONAL_CACHE_ID[] = $arNavParams["PAGEN"];
$ADDITIONAL_CACHE_ID[] = $arNavParams["SIZEN"];

$CACHE_PATH = "/" . SITE_ID . "/" . LANGUAGE_ID . $this->__relativePath;

// Подключается файл result-modifier.php
if ($this->StartResultCache($arParams["CACHE_TIME"], $ADDITIONAL_CACHE_ID, $CACHE_PATH)) {
    if ($arParams["IS_AJAX"] == "Y" && $bDesignMode) {
        ob_start();
        $this->IncludeComponentTemplate();
        $contents = ob_get_contents();
        ob_end_clean();

        echo $contents;
    } else {
        $this->IncludeComponentTemplate();
    }
}

if (!$bDesignMode && $arParams["IS_AJAX"] == "Y") {
    $EndBufferContentMan = $APPLICATION->EndBufferContentMan();
    echo json_encode(['content' => $EndBufferContentMan]);
    if (defined("HTML_PAGES_FILE") && !defined("ERROR_404")) {
        CHTMLPagesCache::writeFile(HTML_PAGES_FILE, $r);
    }
    exit;
}

if ($GLOBALS["APPLICATION"]->GetShowIncludeAreas() && $USER->isAdmin()) {
    // Подключение иконок редактирования файла .parameters.php
    $filename = ".parameters.php";
    $result_modifier_edit = "jsPopup.ShowDialog('/bitrix/admin/public_file_edit_src.php?site=" . SITE_ID . "&path=" . urlencode($arResult["__TEMPLATE_FOLDER"]) . "%2F" . $filename . "', {'width':'770', 'height':'570', 'resize':true })";

    $this->AddIncludeAreaIcon(
        array(
            'URL' => "javascript:" . $result_modifier_edit . ";",
            'SRC' => $this->GetPath() . '/images/edit.gif',
            'TITLE' => "Редактировать файл .parameters.php"
        )
    );

    // Подключение иконок редактирования файла result_modifier.php
    $filename = "result_modifier.php";
    $result_modifier_edit = "jsPopup.ShowDialog('/bitrix/admin/public_file_edit_src.php?site=" . SITE_ID . "&path=" . urlencode($arResult["__TEMPLATE_FOLDER"]) . "%2F" . $filename . "', {'width':'770', 'height':'570', 'resize':true })";

    $this->AddIncludeAreaIcon(
        array(
            'URL' => "javascript:" . $result_modifier_edit . ";",
            'SRC' => $this->GetPath() . '/images/edit.gif',
            'TITLE' => "Редактировать файл result_modifier.php"
        )
    );
}

// Возвращаемое значение
if (!empty($arResult["__RETURN_VALUE"])) {
    return $arResult["__RETURN_VALUE"];
}
