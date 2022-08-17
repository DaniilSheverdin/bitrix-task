<<<<<<< HEAD
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/timeman/index.php");
$APPLICATION->SetTitle('Система контроля управления доступом');
$APPLICATION->IncludeComponent("citto:scud", ".default", Array(
        "FILTER_NAME"	=>	"absence",
        "FILTER_SECTION_CURONLY"	=>	"N",
        "AJAX_MODE" => "Y",
        "AJAX_OPTION_JUMP" => "N",
        "AJAX_OPTION_STYLE" => "Y",
        "AJAX_OPTION_HISTORY" => "N"
    )
);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
//require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
//IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/timeman/index.php");
//$APPLICATION->SetTitle(GetMessage("COMPANY_TITLE"));
//$APPLICATION->IncludeComponent("bitrix:intranet.absence.calendar", ".default", Array(
//	"FILTER_NAME"	=>	"absence",
//	"FILTER_SECTION_CURONLY"	=>	"N"
//	)
//);
//require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
=======
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/timeman/index.php");
$APPLICATION->SetTitle('Система контроля управления доступом');
$APPLICATION->IncludeComponent("citto:scud", ".default", Array(
        "FILTER_NAME"	=>	"absence",
        "FILTER_SECTION_CURONLY"	=>	"N",
        "AJAX_MODE" => "Y",
        "AJAX_OPTION_JUMP" => "N",
        "AJAX_OPTION_STYLE" => "Y",
        "AJAX_OPTION_HISTORY" => "N"
    )
);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
//require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
//IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/timeman/index.php");
//$APPLICATION->SetTitle(GetMessage("COMPANY_TITLE"));
//$APPLICATION->IncludeComponent("bitrix:intranet.absence.calendar", ".default", Array(
//	"FILTER_NAME"	=>	"absence",
//	"FILTER_SECTION_CURONLY"	=>	"N"
//	)
//);
//require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
>>>>>>> e0a0eba79 (init)
?>