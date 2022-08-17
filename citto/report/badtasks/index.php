<?

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle('Задачи на уволенных сотрудниках');
$APPLICATION->IncludeComponent(
    'citto:customreports',
    'badtasks',
    [
        'DEPARTMENT' => 57
    ]
);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
