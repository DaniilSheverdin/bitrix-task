<?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle('Отчёт по ознакомлению с новостью');

$APPLICATION->IncludeComponent(
    'citto:customreports',
    'blogread',
    [
        'ID'   => $_REQUEST['ID']
    ]
);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
