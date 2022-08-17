<?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle('Отчеты отдела разработки');
$APPLICATION->IncludeComponent(
    'citto:customreports',
    'devreports',
    []
);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
