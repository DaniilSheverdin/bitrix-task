<?

define('NEED_AUTH', true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Подписание файлов");
$APPLICATION->AddHeadScript("/podpis-fayla/index.js", false);
if (!check_bitrix_sessid()) {
	die("Обновите страницу. Сессия устарела");
}

$APPLICATION->IncludeComponent("citto:filesigner", "", [
    'FILES'	=> $_REQUEST['FILES']   ?? [],
    'POS'	=> isset($_REQUEST['POS'])
                        ? (string)$_REQUEST['POS']
                        : "",
    'CLEARF'=> isset($_REQUEST['CLEARF']) && is_array($_REQUEST['CLEARF'])
                        ? $_REQUEST['CLEARF']
                        : [],
    'CHECK_SIGN'=> ($_REQUEST['CHECK_SIGN'] == 'Y' ? 'Y' : 'N'),
], false);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
