<?

use Bitrix\Main\Loader;
use Sprint\Migration\Helpers\IblockHelper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

global $userFields, $USER;

Loader::includeModule('iblock');
Loader::includeModule('sprint.migration');

$helper = new IblockHelper();
$iblockId = $helper->getIblockId('zayavi_o_sebe', 'services');

if (isset($_POST['do'])) {
    $el = new CIBlockElement;
    $arFieldsSec = [
        'ACTIVE'            => 'Y',
        'NAME'              => $_POST['name'],
        'IBLOCK_ID'         => $iblockId,
        'PROPERTY_VALUES'   => [
            'FILE'          => $_FILES['file'],
            'FIO'           => $_POST['name'],
            'COMMENTS'      => $_POST['comment'],
            'POST'          => $_POST['post'],
            'ACTIVITIES'    => $_POST['activities'],
            'INCOME'        => $_POST['income'],
            'DATE'          => date('d.m.Y', strtotime($_POST['date'])),
        ],
    ];

    if ($el->Add($arFieldsSec)) {
        echo '<div class="alert alert-success" role="alert">Ваша заявка успешно отправлена</div>' ;
    } else {
        echo '<div class="alert alert-warning" role="alert">' . $el->LAST_ERROR . '</div>';
    }
}

$arEnums = [];
$res = CIBlockPropertyEnum::GetList(
    [
        'DEF'  => 'DESC',
        'SORT' => 'ASC'
    ],
    [
        'IBLOCK_ID' => $iblockId
    ]
);
while ($row = $res->Fetch()) {
    $arEnums[ $row['PROPERTY_CODE'] ][ $row['ID'] ] = $row;
}
$arResult['ENUMS'] = $arEnums;
$arResult['USER'] = $userFields($USER->GetID());

$this->IncludeComponentTemplate();
