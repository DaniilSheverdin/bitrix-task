<?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle('Отчёт по ознакомлению с документом');

$APPLICATION->IncludeComponent(
    'serg:super.component',
    'mfc.acquaintance',
    [
        'wf'   => $_REQUEST['wf']
    ]
);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
