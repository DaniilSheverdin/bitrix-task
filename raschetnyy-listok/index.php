<?
define('NEED_AUTH', true);
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
$APPLICATION->SetTitle('Расчетный листок');

try {
    if (!check_bitrix_sessid() || !$USER->IsAuthorized()) {
        throw new Exception('Ошибка сессии');
    }
    if (!isset($_GET['DateS']) || !preg_match("/\d{2}\.\d{2}\.\d{4}/", $_GET['DateS'])) {
        throw new Exception('Дата начала введена неверно');
    }
    if (!isset($_GET['DateE']) || !preg_match("/\d{2}\.\d{2}\.\d{4}/", $_GET['DateE'])) {
        throw new Exception('Дата окончания введена неверно');
    }
    $arUser= $USER->GetByID($USER->GetID())->GetNext();
    $DateS = new DateTime($_GET['DateS'].' 00:00');
    $DateE = new DateTime($_GET['DateE'].' 00:00');
    $arUser['UF_INN'] = trim($arUser['UF_INN']);
    if (empty($arUser['UF_INN'])) {
        throw new Exception('Не удалось загрузить расчетный листок для Вашей учетной записи');
    }

    $paySlipDir = $_SERVER['DOCUMENT_ROOT'] . '/../newcorp_arch/PaySlip/' . $arUser['ID'] . '/';
    if (!is_dir($paySlipDir)) {
        mkdir($paySlipDir);
        chmod($paySlipDir, 0775);
    }
    $paysl_filename = $paySlipDir . 'PaySlip_' . $arUser['ID'] . '_' . $DateS->format('U') . '_' . $DateE->format('U') . '.pdf';
    if (!file_exists($paysl_filename) || filemtime($paysl_filename) < time()-72*60*60){
        \Bitrix\Main\Loader::includeModule('citto.integration');
        $rConnect = \Citto\Integration\Source1C::Connect1C('http://s-1c-app04.tularegion.local/zbu30/ws/integrationws.1cws?wsdl', [
            'login'     => 'Integration',
            'password'  => '/011015/'
        ]);
        $rRespone = \Citto\Integration\Source1C::Get($rConnect, 'PaySlip', [
            'SID'   => $arUser['UF_INN'],
            'DateS' => $DateS->format('Y-m-d'),
            'DateE' => $DateE->format('Y-m-d')
        ]);
        if (empty($rRespone->return->Data)) {
            throw new Exception('Не удалось загрузить данные, попробуйте позже');
        }

        file_put_contents($paysl_filename, $rRespone->return->Data);
    }
    if (!file_exists($paysl_filename)) {
        throw new Exception('Не удалось загрузить данные, попробуйте позже');
    }

    $APPLICATION->RestartBuffer();
    header('Content-Type: ' . mime_content_type($paysl_filename));
    header('Content-Disposition: attachment; filename="'.('Расчетный листок ' . htmlspecialchars($USER->GetFullName())).'.pdf"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($paysl_filename));
    readfile($paysl_filename);
    die;
} catch (Exception $exc){
    echo $exc->getMessage();
}

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
