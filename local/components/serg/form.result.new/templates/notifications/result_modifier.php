<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}

include_once dirname(dirname(__DIR__)).'/constants.php';

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$cDate = new DateTime();
$strDay = $cDate->format('d');
$strYear = $cDate->format('Y');
$strMounth = $cDate->format('m');

$resMonolog = new Logger('bi_mailed');
$resMonolog->pushHandler(new StreamHandler($_SERVER["DOCUMENT_ROOT"].'/local/logs/mailed.log', Logger::INFO));

$rsSections = CIBlockSection::GetList([], ['IBLOCK_ID' => IBLOCK_ID_CONTROLS, 'SECTION_ID' => false], false, ['ID', 'NAME']);
while ($arSection = $rsSections->Fetch()) {
    $arDEPS[$arSection['ID']] = $arSection['NAME'];
}

$boolPos1 = ($strMounth == 1 && ($strDay == 9 || $strDay == 16));
$boolPos2 = ($strMounth == 5 && ($strDay == 3 || $strDay == 16));
$boolPos3 = ($strDay == 1 || $strDay == 16);

$intLastDay = cal_days_in_month(CAL_GREGORIAN, $strMounth, $strYear);
if ($strDay > 15) {
    $intDateFrom = 16;
    $intDateTo = $intLastDay;
} else {
    if ($boolPos1) {
        $intDateFrom = 9;
    } elseif ($boolPos2) {
        $intDateFrom = 3;
    } else {
        $intDateFrom = 1;
    }
    $intDateTo = 15;
}

echo 'Start mailed!<br>';
$resMonolog->addWarning('Start mailed!');
if ($boolPos1 || $boolPos2 || $boolPos3) {
    foreach ($arDEPS as $intK => $arDepItem) {
        if (isset($arEmailsDep[$intK]) && filter_var($arEmailsDep[$intK], FILTER_VALIDATE_EMAIL)) {
            $arSendparams = [
                'SEND_TO' => $arEmailsDep[$intK],
                'DATE_FROM' => str_pad($intDateFrom, 2, '0', STR_PAD_LEFT) . '.' . str_pad($strMounth, 2, '0', STR_PAD_LEFT) . '.' . $strYear,
                'DATE_TO' => str_pad($intDateTo, 2, '0', STR_PAD_LEFT) . '.' . str_pad($strMounth, 2, '0', STR_PAD_LEFT) . '.' . $strYear,
                'DEPARTMENT' => $arDepItem,
            ];

            $resIntMail = CEvent::Send('BI_NOTIFCATION_SENDER', SITE_ID, $arSendparams);
            if (IS_LOCAL) {
                $resMonolog->addWarning("Mailed to ".$arSendparams['SEND_TO']);
            }

            $arSendparams['SEND_TO'] = DUPLICATE_BI_EMAIL_NOTIFICATION;
            $resIntMail = CEvent::Send('BI_NOTIFCATION_SENDER', SITE_ID, $arSendparams);
            if (IS_LOCAL) {
                $resMonolog->addWarning("Mailed to ".$arSendparams['SEND_TO']);
            }

            if ($resIntMail) {
                echo "Mailed to ".$arEmailsDep[$intK]."<br>";
                $resMonolog->addWarning("Mailed to ".$arEmailsDep[$intK]);
            }
        }
    }
} else {
    $resMonolog->addWarning("NoEmail!");
    echo "NoEmail!<br>";
}

$resMonolog->addWarning("End mailed!");
echo 'End mailed!<br>';
