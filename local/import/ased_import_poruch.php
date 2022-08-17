<?

setlocale(LC_NUMERIC, 'C');
$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__) . "/../..");
$DOCUMENT_ROOT            = $_SERVER["DOCUMENT_ROOT"];

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS", true);
define('CHK_EVENT', true);
define('MODULE_NAME', 'citto.integration');
//Require
require $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php";
require $_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php";

//Uses
use Bitrix\Main\Config\Option;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Citto\Integration;
use Citto\Integration\Delo;
use Bitrix\Main\{Page\Asset, Web\Json, UI};
CModule::IncludeModule(MODULE_NAME);
$arModulesOptions = unserialize(Option::get(MODULE_NAME, "values"));

if ($arModulesOptions['log_level'] == '') {
    $arModulesOptions['log_level'] = 'INFO';
}
$oLogger = new Logger('ased_import');
// Now add some handlers

$oLogger->pushHandler(new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/local/logs/ased_import_poruch.log', constant('Logger::' . $arModulesOptions['log_level'])));
/*if ($_REQUEST['p'] != $arModulesOptions['ac_password']) {
    if ($argv[0] != '') {
        $oLogger->info('SBS: Started ASED Import');
        $started_name = 'SBS:';
    } else {
        $oLogger->error('WBS: Not password accepted', array('password' => $_REQUEST['ac_password']));
        die();
    }
} else {
    $started_name = 'WBS:';
    $oLogger->info('WBS: Started ASED Import');
}*/
$n=0;
$arResult['ISPOLNITELS'] = [];
$arSelect                = array("ID", "NAME", "DATE_ACTIVE_FROM", "PROPERTY_RUKOVODITEL", "PROPERTY_ZAMESTITELI", "PROPERTY_ISPOLNITELI", "PROPERTY_TYPE");
$arFilter                = array("IBLOCK_ID" => IntVal(508), "ACTIVE_DATE" => "Y", "ACTIVE" => "Y");
$res                     = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);
while ($arFields = $res->GetNext()) {
    $arResult['ISPOLNITELS_NAME'][$arFields['NAME']] = $arFields['ID'];
    $arResult['ISPOLNITELS'][$arFields['ID']] = $arFields;
}
$arIDsIspolnitels=
[
    '7836',
    '7822',
    '7834',
    '7830',
    '7825',
    '7829',
    '7835',
    '7826',
    '7818',
    '7814',
    '7813',
    '7810',
    '243308'
];
$csvFile = new CCSVData('R', true);
$csvFile->LoadFile($_SERVER['DOCUMENT_ROOT'].'/local/import/checkorders/test7.csv');
$csvFile->SetDelimiter(';');
$arAllProps = [
    'POST'      => 1112, // Куратор = Александр Бибиков
    'CONTROLER' => 1151, // Контролер = Дмитрий Сафронов
    'STATUS'    => 1141, // Статус = В работе
    'ACTION'    => 1134, // Состояние = Черновик
    'CATEGORY'  => 1279, // Категория = Поручения Губернатора
];
if (file_exists($_SERVER['DOCUMENT_ROOT'].'/local/import/checkorders/isn_created.json')) {
    $arIsnCreated=json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/local/import/checkorders/isn_created.json'), true);
}
if (empty($arIsnCreated)) {
    $arIsnCreated['IDS']=[];
    $arIsnCreated['DATA']=[];
}
while ($arRes = $csvFile->Fetch()) {
    if (intval($_REQUEST['count'])>0) {
        if ($n==$_REQUEST['count']) {
            break;
        }
    }
    $obDelo = new Delo();
    $arProtocol = $obDelo->getData(
        $arRes['0'],
        $arRes['1']
    );
    $arProtocols=[];
    if (!empty($arProtocol['error'])&&count($arProtocol['result'])>0) {
        foreach ($arProtocol['result'] as $sKey => $arProtocolIsn) {
            $obDelo = new Delo();
            $arProt = $obDelo->getData(
                $arProtocolIsn['FREE_NUM'],
                $arProtocolIsn['DOC_DATE'],
                $arProtocolIsn['ISN_DOC']
            );
            if (!empty($arProt['error'])) {
                $oLogger->critical($started_name . 'Data not loaaded', array('data'=>$arProtocolIsn,'error'=>$arProt['error']));
                if ($started_name=='WBS:') {
                    pre($arRes);
                    pre($arProt['error']);
                }
            } else {
                $arProtocols[]=$arProt;
            }
        }
    } elseif (!empty($arProtocol['error'])) {
        $oLogger->critical($started_name . 'Data not loaaded', array('data'=>$arRes,'error'=>$arProtocol['error']));
        if ($started_name=='WBS:') {
            pre($arRes);
            pre($arProtocol['error']);
        }
    } else {
        $arProtocols[]=$arProtocol;
    }
    foreach ($arProtocols as $sKey => $arProtocol) {
        $sUploadDir = '/upload/checkorders.import/';
        $sDocPath = $_SERVER['DOCUMENT_ROOT'] . $sUploadDir;
        $sFile = $sDocPath . $arProtocol['result'] . '.json';
        if (!file_exists($sFile)) {
            throw new Exception('Ошибка доступа');
        }

        try {
            $sContent = file_get_contents($sFile);
            $arProtocolData = Json::decode($sContent)['RC'];
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
        if (!empty($arProtocolData['files'])) {
            foreach ($arProtocolData['files'] as $id => $file) {
                $sFileExtension = pathinfo($file['NAME'], PATHINFO_EXTENSION);
                $sNewFileName = $arProtocol['result'] . '-' . $id . '.' . $sFileExtension;
                file_put_contents(
                    $sDocPath . $sNewFileName,
                    base64_decode($arProtocolData['file']['ANNOTAT'])
                );
                $arProtocolData['files'][ $id ]['LOCAL'] = $sUploadDir . $sNewFileName;
            }
        }

        $arFiles = [];
        if (!empty($arProtocolData['files'])) {
            foreach ($arProtocolData['files'] as $file) {
                $arFiles[] = [
                    'name' => $file['NAME'],
                    'MODULE_ID' => 'iblock',
                    'tmp_name' => $_SERVER['DOCUMENT_ROOT'] . $file['LOCAL']
                ];
            }
        }
        $arErrors=[];
        $arCreated=[];
        $arCreatedOrders=[];
        if ($started_name=='WBS:') {
            pre($arProtocolData);
        }
        foreach ($arProtocolData['resols'] as $k => $arResol) {
            $sExecutorName = $arResol['EXECUTORS'][0]['EXECUTOR']['PARENT']['NAME'];
            if ($sExecutorName=='Администрация муниципального образования город Тула') {
                $sExecutorName='Администрация МО г. Тула';
            }
            $iExecutorId = $arResult['ISPOLNITELS_NAME'][ $sExecutorName ] ?? 0;
            if ($iExecutorId <= 0 && false !== mb_strpos($sExecutorName, ' муниципального образования ')) {
                $sNewVal = str_replace(' муниципального образования ', ' МО ', $sExecutorName);
                $iExecutorId = $arResult['ISPOLNITELS_NAME'][ $sNewVal ] ?? $iExecutorId;
            }

            if (in_array($iExecutorId, $arIDsIspolnitels)) {
                //if (!in_array($arResol['ISN'], $arIsnCreated['IDS'])) {
                    $obElement = new CIBlockElement();
                    $arProps = $arAllProps;
                    $arProps['DATE_ISPOLN'] = $arResol['PLANDATE'];
                    $arProps['DATE_CREATE'] = $arProtocolData['DOC_DATE'];
                    $arProps['NUMBER']      = $arProtocolData['FREE_NUM'];
                    $arProps['ISPOLNITEL']  = $arResult['ISPOLNITELS_NAME'][ $sExecutorName ] ?? 0;
                    $arFields = [
                        'IBLOCK_ID'         => 509,
                        'IBLOCK_SECTION_ID' => false,
                        'PROPERTY_VALUES'   => $arProps,
                        'ACTIVE'            => 'Y',
                        'NAME'              => trim($arProtocolData['ANNOTAT']),
                        'DETAIL_TEXT'       => $arResol['ANNOTAT'],
                        'XML_ID'            => $arResol['ISN'],
                        'EXTERNAL_ID'       => $arResol['ISN'],
                    ];
                    if (!empty($arFiles)) {
                        $arFields['PROPERTY_VALUES']['DOCS'] = [];
                        foreach ($arFiles as $arFile) {
                            $iFileId = CFile::SaveFile($arFile, 'iblock');
                            if ($iFileId > 0) {
                                $arFields['PROPERTY_VALUES']['DOCS'][] = $iFileId;
                            }
                        }
                    }

                    if ($sIdNewOrder = $obElement->Add($arFields)) {
                        $arCreated[] = [
                            'ID'        => $sIdNewOrder,
                            'EXECUTOR'  => $arResult['ISPOLNITELS'][ $arProps['ISPOLNITEL'] ]['NAME']
                        ];
                        if ($arResol['SUMMARY']!='') {
                            $obElement = new CIBlockElement();

                            $PROP           = array();
                            $PROP['PORUCH'] = $sIdNewOrder;
                            $PROP['USER']   = $arProps['CONTROLER'];
                            $PROP['TYPE']   = 1132;
                            $arLoadProductArray = array(
                                "MODIFIED_BY"       => $USER->GetID(),
                                "IBLOCK_SECTION_ID" => false,
                                "IBLOCK_ID"         => 510,
                                "PROPERTY_VALUES"   => $PROP,
                                "NAME"              => $arProps['CONTROLER'] . '-' . $sIdNewOrder . '-' . date('d-m-Y_h:i:s'),
                                "ACTIVE"            => "Y", // активен
                                "DETAIL_TEXT"       => $arResol['SUMMARY'],
                            );
                            if ($PRODUCT_ID = $obElement->Add($arLoadProductArray)) {
                            }
                        }
                        $arCreatedOrders[]=$sIdNewOrder;
                        $arIsnCreated['IDS'][]=$arResol['ISN'];
                        $arIsnCreated['DATA'][$arResol['ISN']]=$sIdNewOrder;
                    } else {
                        $arErrors[]=['fields'=>$arFields,'error'=>$obElement->LAST_ERROR];
                    }
                //}
            }
        }
        $oLogger->info($started_name . 'Imported', array('data'=>$arRes,'Created'=>$arCreated,'errors'=>$arErrors));
        if ($started_name=='WBS:') {
            pre($arRes);
            pre($arCreated);
            pre($arErrors);
        }
        if (!empty($arCreatedOrders)) {
            foreach ($arCreatedOrders as $iOrderId) {
                CIBlockElement::SetPropertyValuesEx(
                    $iOrderId,
                    509,
                    [
                        'PORUCH' => array_diff($arCreatedOrders, [$iOrderId])
                    ]
                );
            }
        }
    }
    $n++;
}

file_put_contents($_SERVER['DOCUMENT_ROOT'].'/local/import/checkorders/isn_created.json', json_encode($arIsnCreated));
$oLogger->info($started_name . 'Stopped Ased Import Success', array());
