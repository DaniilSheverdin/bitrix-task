<?

use Bitrix\Main\Loader;
use Bitrix\Highloadblock as HL;
use Bitrix\Main\Entity;
use Bitrix\Main\Type\DateTime;

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}

if ($arResult['CUR_PAGE'] == 'ADD') {

    function getFormatPhoneNumber($phone, $trim = true)
    {
        $phone = preg_replace("/[^0-9A-Za-z]/", "", $phone);

        if ($trim == true && mb_strlen($phone)>11) {
            $phone = mb_substr($phone, 0, 11);
        }
        if (mb_strlen($phone) == 10) {
            return preg_replace("/([0-9a-zA-Z]{3})([0-9a-zA-Z]{3})([0-9a-zA-Z]{2})([0-9a-zA-Z]{2})/", "+7($1)$2$3$4", $phone);
        } elseif (mb_strlen($phone) == 11) {
            if ($phone[0]==8) {
                $phone[0]=7;
            }
            return preg_replace("/([0-9a-zA-Z]{1})([0-9a-zA-Z]{3})([0-9a-zA-Z]{3})([0-9a-zA-Z]{2})([0-9a-zA-Z]{2})/", "$2$3$4$5", $phone);
        }
        return $phone;
    }

    function formatDateToFullYear(string $date)
    {
        $dmy = explode(' ', $date)[0];
        $time = explode(' ', $date)[1];
        if (stristr($date, '/') == true) {
            $day = explode('/', $dmy)[0];
            $month = explode('/', $dmy)[1];
            $year = explode('/', $dmy)[2];

            if (mb_strlen($year) == 2) {
                $year = '2020';
            }
            return $day.'.'.$month.'.'.$year.' '.$time;
        } else {
            $day = explode('.', $dmy)[0];
            $month = explode('.', $dmy)[1];
            $year = explode('.', $dmy)[2];

            if (mb_strlen($year) == 2) {
                $year = '2020';
            }
            return $day.'.'.$month.'.'.$year.' '.$time;
        }
    }

    /**
     * @param $strCoordinates // 47,2931 39,8872 // 53.764306,38.005111 // 54.1901 37.6259 // 54.774649, 37.971714
     * @return array
     */
    function getFormatCoordinates($strCoordinates)
    {
        $strCoordinates = trim($strCoordinates);

        if (stristr($strCoordinates, ', ') == true) {
            $arC = explode(', ', $strCoordinates);
            $lat = trim($arC[0]);
            $lon = trim($arC[1]);
            return [$lat, $lon];
        } elseif (stristr($strCoordinates, ' ') == true) {
            $arC = explode(' ', $strCoordinates);
            $lat = trim(str_replace(',', '.', $arC[0]));
            $lon = trim(str_replace(',', '.', $arC[1]));
            return [$lat, $lon];
        } elseif (stristr($strCoordinates, ' ') == false) {
            $arC = explode(',', $strCoordinates);
            $lat = $arC[0];
            $lon = $arC[1];
            return [$lat, $lon];
        } else {
            return ['0', '1'];
        }
    }

    if ($_REQUEST['TYPE'] == 'VIOLATORS') {
        Loader::includeModule("highloadblock");
        require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/classes/general/csv_data.php");
        $strExtFile = pathinfo($_FILES['file']['name'])['extension'];

        function parseFile(string $delimiter, $file)
        {
            $arCSVData = $arPhones = [];
            $csvFile = new CCSVData('R', true);
            $csvFile->LoadFile($file);

            $csvFile->SetDelimiter($delimiter);
            while ($arRes = $csvFile->Fetch()) {
                if ($arRes[0] != '') {
                    mb_detect_order("UTF-8,WINDOWS-1251");
                    $sDetectEncoding = mb_detect_encoding($arRes[0]);
                    $arRes = $GLOBALS["APPLICATION"]->ConvertCharsetArray($arRes, $sDetectEncoding, SITE_CHARSET);

                    $phoneFromFile = getFormatPhoneNumber($arRes[2]);
                    $arPhones[$phoneFromFile] = $arRes[0];

                    $arCSVData[] = [
                        'operator' => $arRes[0],
                        'phone' => $phoneFromFile,
                        'data_otcheta' => $arRes[1],
                        'prichina_isolyacii' => $arRes[3],
                        'reestr_data' => $arRes[4],
                        'gosudarstvo_vizita' => $arRes[5],
                        'region_address_user' => $arRes[7],
                        'first_night' => implode(', ', getFormatCoordinates($arRes[8])),
                        'data_isolation' => $arRes[9],
                        'coordinates' => getFormatCoordinates($arRes[10])
                    ];
                }
            }

            if (!empty($arCSVData) && !empty($arPhones)) {
                return [
                    'CSV_DATA' => $arCSVData,
                    'PHONES' => $arPhones,
                ];
            } else {
                return null;
            }
        }

        function updateElementsHLBViolators($arPhones, $arDataType)
        {
            $intCountIsolation = 0;
            $intCountCCMIS = 0;
            $hlblock_violators = HL\HighloadBlockTable::getById(HLBLOCK_ID_VIOLATORS)->fetch();
            $entity_violators = HL\HighloadBlockTable::compileEntity($hlblock_violators);
            $entity_data_class_violators = $entity_violators->getDataClass();

            $arSelect = array(
                "ID",
                "IBLOCK_ID",
                "NAME",
                "PROPERTY_ATT_PHONE",
                "PROPERTY_ATT_NAME",
                "PROPERTY_ATT_ADDRESS",
                "PROPERTY_ATT_DATE_SVEDENO",
                "PROPERTY_ATT_DATE_BIRTHDAY",

            );

            $arFilter = array("IBLOCK_ID"=>IBLOCK_ID_MIGRATION_DOCS, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y", "PROPERTY_ATT_PHONE" => $arPhones );
            $res = CIBlockElement::GetList(array('sort' => 'asc'), $arFilter, false, false, $arSelect);
            while ($arFields = $res->GetNext()) {
                $strFIO = $arFields['NAME'];

                if ($arFields['PROPERTY_ATT_NAME_VALUE']) {
                    $strFirstName = explode(' ', $arFields['PROPERTY_ATT_NAME_VALUE'])[0];
                    $strSecondName = explode(' ', $arFields['PROPERTY_ATT_NAME_VALUE'])[1];
                    $strFIO .= ' '.$strFirstName.' '.$strSecondName;
                }
                $dateBirthday = $arFields['PROPERTY_ATT_DATE_SVEDENO_VALUE'] ?? $arFields['PROPERTY_ATT_DATE_BIRTHDAY_VALUE'];



                $rsData = $entity_data_class_violators::getList(array(
                    "select" => array("ID"),
                    "order" => ['id' => 'asc'],
                    "filter" => ['UF_PHONE' => $arFields['PROPERTY_ATT_PHONE_VALUE']],
                ));
                if ($arData = $rsData->Fetch()) {
                    $arUpdateElement = [

                        'UF_FIO' => $strFIO,
                        'UF_ADDRESS' => $arFields['PROPERTY_ATT_ADDRESS_VALUE'],
                        'UF_DATE_BIRTHDAY' => $dateBirthday,
                        'UF_DATA_TYPE' => $arDataType['RPN'],

                    ];

                    if ($result = $entity_data_class_violators::update($arData['ID'], $arUpdateElement)) {
                        $intCountIsolation++;
                    }
                }
            }

            $hlblock = HL\HighloadBlockTable::getById(HLBLOCK_ID_VIOLATORS_CCMIS)->fetch();
            $entity = HL\HighloadBlockTable::compileEntity($hlblock);
            $entity_data_class = $entity->getDataClass();

            $rsData = $entity_data_class::getList(array(
                "select" => array("*"),
                "order" => ['id' => 'asc'],
                "filter" => ['UF_PHONE' => $arPhones],
            ));

            while ($arData = $rsData->Fetch()) {
                $arUpdateElement = [

                    'UF_FIO' => $arData['UF_FIO'],
                    'UF_ADDRESS' => $arData['UF_ADDRESS'],
                    'UF_DATE_BIRTHDAY' => $arData['UF_DATE_BIRTHDAY'],
                    'UF_DATA_TYPE' => $arDataType['EXT'],

                ];

                $rsDataViolators = $entity_data_class_violators::getList(array(
                    "select" => array("ID"),
                    "order" => ['id' => 'asc'],
                    "filter" => ['UF_PHONE' => $arData['UF_PHONE']],
                ));
                if ($arDataViolators = $rsDataViolators->Fetch()) {
                    if ($result = $entity_data_class_violators::update($arDataViolators['ID'], $arUpdateElement)) {
                        $intCountCCMIS++;
                    }
                }
            }

            return ['ISOLATION' => $intCountIsolation, 'CCMIS' => $intCountCCMIS];
        }

        function addElementsHLB(array $fileData)
        {


            $intCountRows = 0;
            $intCountHLB = 0;
            $rsUField = CUserFieldEnum::GetList(array(), array("USER_FIELD_NAME" => 'UF_DATA_TYPE'));
            $arDataType = [];
            while ($arUField = $rsUField->GetNext()) {
                $arDataType[$arUField['XML_ID']] = $arUField['ID'];
            }

            foreach ($fileData['CSV_DATA'] as $dataRow) {
                $arLoadHLBData = [
                    'UF_FIO' => '',
                    'UF_ADDRESS' => '',
                    'UF_PHONE' => $dataRow['phone'],
                    'UF_DATE_BIRTHDAY' => '',
                    'UF_DATE_VIOLATION' => formatDateToFullYear($dataRow['data_isolation']),
                    'UF_COORDINATES' => implode(', ', $dataRow['coordinates']),
                    'UF_COORDINATES_FIRST' => implode(', ',$dataRow['first_night']),
                    'UF_DATA_TYPE' => '',
                    'UF_OPERATOR' => $dataRow['operator'],
                    'UF_REASON_ISOLATION' => $dataRow['prichina_isolyacii'],
                    'UF_SERIALIZE_DATA' => serialize($dataRow),
                ];


                $arHLBData = [];
                $hlblock1 = HL\HighloadBlockTable::getById(HLBLOCK_ID_VIOLATORS)->fetch();
                $entity1 = HL\HighloadBlockTable::compileEntity($hlblock1);
                $entity_data_class1 = $entity1->getDataClass();
                $rsData1 = $entity_data_class1::getList(array(

                    "select" => array("*"),
                    "order" => ['id' => 'asc'],
                    "filter" => [
                        'UF_DATE_VIOLATION' => formatDateToFullYear($dataRow['data_isolation']),
                        'UF_PHONE' => $dataRow['phone']
                    ],

                ));


                if ($arData1 = $rsData1->Fetch()) {
                    $arHLBData[] = $arData1['ID'];
                    $intCountHLB++;
                }

                if (empty($arHLBData)) {
                    $hlblock = HL\HighloadBlockTable::getById(HLBLOCK_ID_VIOLATORS)->fetch();
                    $entity = HL\HighloadBlockTable::compileEntity($hlblock);
                    $entity_data_class = $entity->getDataClass();

                    if ($result = $entity_data_class::add($arLoadHLBData)) {
                        $intCountRows++;
                    }
                }
            }

            if ($intCountRows > 0) {
                $intResultUpdateCount = updateElementsHLBViolators(array_keys($fileData['PHONES']), $arDataType);
            }


            if ($intCountRows > 0) {
                echo '<div class="message"><span class="message_success">' . GetMessage("ADD_COUNT") . $intCountRows . '</span></div>';
                echo '<div class="message"><span class="message_success">' . GetMessage("ISOLATION_COUNT") . $intResultUpdateCount['ISOLATION'] . '</span></div>';
                echo '<div class="message"><span class="message_success">' . GetMessage("CCMIS_COUNT") . $intResultUpdateCount['CCMIS'] . '</span></div>';
                echo '<div class="message"><span class="message_error">' . GetMessage("IDENT_COUNT") . $intCountHLB . '</span></div>';
            } elseif ($intResultUpdateCount['ISOLATION'] > 0 || $intResultUpdateCount['CCMIS'] > 0) {
                if ($intResultUpdateCount['ISOLATION'] > 0) {
                    echo '<div class="message"><span class="message_success">' . GetMessage("ISOLATION_COUNT") . $intResultUpdateCount['ISOLATION'] . '</span></div>';
                }
                if ($intResultUpdateCount['CCMIS'] > 0) {
                    echo '<div class="message"><span class="message_success">' . GetMessage("CCMIS_COUNT") . $intResultUpdateCount['CCMIS'] . '</span></div>';
                }
                if ($intCountHLB) {
                    echo '<div class="message"><span class="message_error">' . GetMessage("IDENT_COUNT") . $intCountHLB . '</span></div>';
                }
                echo '<div class="message"><span class="message_error">' . GetMessage("NOTHING_ADD_ERROR") . '</span></div>';
            } elseif ($intCountHLB > 0) {
                echo '<div class="message"><span class="message_error">' . GetMessage("IDENT_COUNT") . $intCountHLB . '</span></div>';
                echo '<div class="message"><span class="message_error">' . GetMessage("NOTHING_ADD_ERROR") . '</span></div>';
            } else {
                echo '<div class="message"><span class="message_error">' . GetMessage("ADD_ERROR") . '</span></div>';
            }
        }

        switch ($strExtFile) {
            case 'csv':
                $data = parseFile(';', $_FILES['file']['tmp_name']);
                addElementsHLB($data);
                break;
            case 'xls':
                $fileID = CFile::SaveFile($_FILES['file'], "xls2csv");

                if (intval($fileID > 0)) {
                    $arFileXls = CFile::GetFileArray($fileID);
                    $fileNameCsv = str_replace('.xls', '.csv', $arFileXls['SRC']);
                    shell_exec('convertxls2csv -x "' . $_SERVER["DOCUMENT_ROOT"] . $arFileXls['SRC'] . '" -b WINDOWS-1251 -c "' . $_SERVER["DOCUMENT_ROOT"] . $fileNameCsv . '" -a UTF-8');
                    $arFileCsv = CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"] . $fileNameCsv);

                    $data = parseFile(',', $arFileCsv['tmp_name']);
                    addElementsHLB($data);
                }
                break;
            case 'xlsx':
                $fileID = CFile::SaveFile($_FILES['file'], "xlsx2csv");

                if (intval($fileID > 0)) {
                    $arFileXls = CFile::GetFileArray($fileID);
                    $fileNameCsv = str_replace('.xlsx', '.csv', $arFileXls['SRC']);
                    shell_exec('xlsx2csv -d ";" -f "%d.%m.%Y %H:%M" ' . $_SERVER["DOCUMENT_ROOT"] . $arFileXls['SRC'] . ' ' . $_SERVER["DOCUMENT_ROOT"] . $fileNameCsv);
                    $arFileCsv = CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"] . $fileNameCsv);

                    $data = parseFile(';', $arFileCsv['tmp_name']);
                    addElementsHLB($data);
                }
                break;
        }
    } elseif ($_REQUEST['TYPE'] == 'VIOLATORS_CCMIS') {
        Loader::includeModule("highloadblock");
        require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/classes/general/csv_data.php");
        $strExtFile = pathinfo($_FILES['file']['name'])['extension'];

        function parseFile(string $delimiter, $file)
        {
            $rsUField = CUserFieldEnum::GetList(array(), array("USER_FIELD_NAME" => 'UF_TYPE'));
            $arDataType = [];
            while ($arUField = $rsUField->GetNext()) {
                $arDataType[$arUField['VALUE']] = $arUField['ID'];
            }

            $arCSVData = $arPhones = [];
            $csvFile = new CCSVData('R', true);
            $csvFile->LoadFile($file);

            $csvFile->SetDelimiter($delimiter);
            while ($arRes = $csvFile->Fetch()) {
                if ($arRes[0] != '') {
                    mb_detect_order("UTF-8,WINDOWS-1251");
                    $sDetectEncoding = mb_detect_encoding($arRes[0]);
                    $arRes = $GLOBALS["APPLICATION"]->ConvertCharsetArray($arRes, $sDetectEncoding, SITE_CHARSET);

                    $arCSVData[] = [
                        'fio' => $arRes[0],
                        'birthday' => $arRes[1],
                        'address' => $arRes[2],
                        'phone' => getFormatPhoneNumber($arRes[3]),
                        'type' => $arDataType[$arRes[4]]
                    ];
                }
            }

            if (!empty($arCSVData)) {
                return $arCSVData;
            } else {
                return null;
            }
        }

        function addElementsHLB(array $fileData)
        {

            $intCountRows = 0;
            $intCountViolators = 0;

            $hlblock = HL\HighloadBlockTable::getById(HLBLOCK_ID_VIOLATORS_CCMIS)->fetch();
            $entity = HL\HighloadBlockTable::compileEntity($hlblock);
            $entity_data_class = $entity->getDataClass();

            $rsUField = CUserFieldEnum::GetList(array(), array("USER_FIELD_NAME" => 'UF_DATA_TYPE'));
            $arDataType = [];
            while ($arUField = $rsUField->GetNext()) {
                $arDataType[$arUField['XML_ID']] = $arUField['ID'];
            }

            foreach ($fileData as $value) {
                $arLoadHLBData = [
                    'UF_FIO' => $value['fio'],
                    'UF_ADDRESS' => $value['address'],
                    'UF_PHONE' => $value['phone'],
                    'UF_DATE_BIRTHDAY' => $value['birthday'],
                    'UF_TYPE' => $value['type'],
                ];

                $arHLBData = [];
                $rsData = $entity_data_class::getList(array(
                    "select" => array("*"),
                    "order" => ['id' => 'asc'],
                    "filter" => ['UF_FIO' => $value['fio'], 'UF_DATE_BIRTHDAY' => $value['birthday']],
                ));

                while ($arData = $rsData->Fetch()) {
                    $arHLBData[] = $arData['ID'];
                }

                if (empty($arHLBData)) {
                    if ($result = $entity_data_class::add($arLoadHLBData)) {
                        $intCountRows++;
                    }
                }

                $hlblockViolators = HL\HighloadBlockTable::getById(HLBLOCK_ID_VIOLATORS)->fetch();
                $entityViolators = HL\HighloadBlockTable::compileEntity($hlblockViolators);
                $entity_data_class_violators = $entityViolators->getDataClass();

                $rsDataViolators = $entity_data_class_violators::getList(array(
                    "select" => array("ID", "UF_PHONE"),
                    "order" => ['id' => 'asc'],
                    "filter" => ['UF_PHONE' => $value['phone']],
                ));

                while ($arDataViolators = $rsDataViolators->Fetch()) {
                    $arUpdateElement = [
                        'UF_FIO' => $value['fio'],
                        'UF_ADDRESS' => $value['address'],
                        'UF_DATE_BIRTHDAY' => $value['birthday'],
                        'UF_DATA_TYPE' => $arDataType['EXT']
                    ];

                    if ($result = $entity_data_class_violators::update($arDataViolators['ID'], $arUpdateElement)) {
                        $intCountViolators++;
                    }
                }
            }

            if ($intCountRows > 0) {
                echo '<div class="message"><span class="message_success">' . GetMessage("ADD_COUNT") . $intCountRows . '</span></div>';
                if ($intCountViolators > 0) {
                    echo '<div class="message"><span class="message_success">' . GetMessage("VIOLATORS_COUNT") . $intCountViolators . '</span></div>';
                }
            } else {
                echo '<div class="message"><span class="message_error">' . GetMessage("ADD_ERROR") . '</span></div>';
            }
        }

        switch ($strExtFile) {
            case 'csv':
                $data = parseFile(';', $_FILES['file']['tmp_name']);
                addElementsHLB($data);
                break;
            case 'xls':
                $fileID = CFile::SaveFile($_FILES['file'], "xls2csv");
                if (intval($fileID > 0)) {
                    $arFileXls = CFile::GetFileArray($fileID);
                    $fileNameCsv = str_replace('.xls', '.csv', $arFileXls['SRC']);
                    shell_exec('convertxls2csv -x "' . $_SERVER["DOCUMENT_ROOT"] . $arFileXls['SRC'] . '" -b WINDOWS-1251 -c "' . $_SERVER["DOCUMENT_ROOT"] . $fileNameCsv . '" -a UTF-8');
                    $arFileCsv = CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"] . $fileNameCsv);

                    $data = parseFile(',', $arFileCsv['tmp_name']);
                    addElementsHLB($data);
                }
                break;
            case 'xlsx':
                $fileID = CFile::SaveFile($_FILES['file'], "xlsx2csv");
                if (intval($fileID > 0)) {
                    $arFileXls = CFile::GetFileArray($fileID);
                    $fileNameCsv = str_replace('.xlsx', '.csv', $arFileXls['SRC']);
                    shell_exec('xlsx2csv -d ";" -f %d.%m.%Y ' . $_SERVER["DOCUMENT_ROOT"] . $arFileXls['SRC'] . ' ' . $_SERVER["DOCUMENT_ROOT"] . $fileNameCsv);
                    $arFileCsv = CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"] . $fileNameCsv);

                    $data = parseFile(';', $arFileCsv['tmp_name']);
                    addElementsHLB($data);
                }
                break;
        }
    } elseif (array_key_exists($_REQUEST['TYPE'], $arResult['SECTIONS'])) {
        require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/classes/general/csv_data.php");
        $strExtFile = pathinfo($_FILES['file']['name'])['extension'];

        $intCountIdx = 1;

        $arIdxDateFormat = [];
        foreach ($arResult['PROPERTY_LIST'][$_REQUEST['TYPE']] as $propertyCode) {
            $res = CIBlockProperty::GetByID($propertyCode, IBLOCK_ID_MIGRATION_DOCS, false);
            if ($arProperty = $res->GetNext()) {
                if ($arProperty['USER_TYPE'] == 'Date' || $arProperty['USER_TYPE'] == 'DateTime') {
                    $arIdxDateFormat[] = $intCountIdx;
                }
            }
            $intCountIdx++;
        }

        function nformat_phone($phone = '', $convert = false, $trim = true)
        {

            if (empty($phone)) {
                return '';
            }

            $phone = preg_replace("/[^0-9A-Za-z]/", "", $phone);

            if ($convert == true) {
                $replace = array('2'=>array('a','b','c'),
                    '3'=>array('d','e','f'),
                    '4'=>array('g','h','i'),
                    '5'=>array('j','k','l'),
                    '6'=>array('m','n','o'),
                    '7'=>array('p','q','r','s'),
                    '8'=>array('t','u','v'), '9'=>array('w','x','y','z'));

                foreach ($replace as $digit=>$letters) {
                    $phone = str_ireplace($letters, $digit, $phone);
                }
            }

            if ($trim == true && mb_strlen($phone)>11) {
                $phone = mb_substr($phone, 0, 11);
            }
            if (mb_strlen($phone) == 7) {
                return preg_replace("/([0-9a-zA-Z]{3})([0-9a-zA-Z]{4})/", "$1-$2", $phone);
            } elseif (mb_strlen($phone) == 10) {
                return preg_replace("/([0-9a-zA-Z]{3})([0-9a-zA-Z]{3})([0-9a-zA-Z]{2})([0-9a-zA-Z]{2})/", "+7($1)$2$3$4", $phone);
            } elseif (mb_strlen($phone) == 11) {
                if ($phone[0]==8) {
                    $phone[0]=7;
                }
                return preg_replace("/([0-9a-zA-Z]{1})([0-9a-zA-Z]{3})([0-9a-zA-Z]{3})([0-9a-zA-Z]{2})([0-9a-zA-Z]{2})/", "+$1 ($2)$3$4$5", $phone);
            }
            return $phone;
        }
        function addElements(string $delimiter, $file, array $propertyList, array $idxDate, int $sectionID)
        {
            global $USER;

            $csvFile = new CCSVData('R', true);
            $csvFile->LoadFile($file);

            $csvFile->SetDelimiter($delimiter);
            $intCountRows = 0;
            $intCountRowsAll = 1;
            $intStartColumnNumber = 1;

            $arRowErrors = [];

            while ($arRes = $csvFile->Fetch()) {
                if ($arRes[$intStartColumnNumber - 1] != '') {
                    mb_detect_order("UTF-8,WINDOWS-1251");
                    $sDetectEncoding = mb_detect_encoding($arRes[0]);
                    $arRes = $GLOBALS["APPLICATION"]->ConvertCharsetArray($arRes, $sDetectEncoding, SITE_CHARSET);

                    $el = new CIBlockElement;
                    $arProp = [];

                    for ($i = $intStartColumnNumber; $i < count($arRes); $i++) {


                        $csvValue = $arRes[$i];
                        if (in_array($i, $idxDate)&&trim($csvValue)!='') {
                            $csvValue = formatDateFromXLS($arRes[$i]);
                        }
                        if ($propertyList[$i - $intStartColumnNumber]=='ATT_PHONE') {
                            $csvValue = nformat_phone($csvValue);
                        }
                        $arProp[$propertyList[$i - $intStartColumnNumber]] = $csvValue;
                    }

                    if ($_REQUEST['TYPE'] == 'CONT') {
		                    $md5Sum = md5($arRes[$intStartColumnNumber - 1].';'.implode(';', $arProp));
		                    $arProp['ATT_CHECK_SUM'] = $md5Sum;

		                    $arSelect = Array("ID", "IBLOCK_ID", "PROPERTY_ATT_CHECK_SUM");
		                    $arFilter = Array("IBLOCK_ID"=>IBLOCK_ID_MIGRATION_DOCS, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y", "IBLOCK_SECTION_ID" => SECTION_ID_MIGRATION_CONT, "PROPERTY_ATT_CHECK_SUM" => $md5Sum);
		                    $res = CIBlockElement::GetList(Array('sort' => 'asc'), $arFilter, false, false, $arSelect);
		                    if ($res->SelectedRowsCount() > 0) {
				                    $arRowErrors['UNIQUE'][$intCountRowsAll] = 'Запись под номером: '. $intCountRowsAll. ' уже существует';
		                    }

		                    if ($arProp['ATT_DATE_PERESECHENIYA'] == '') {
				                    $arRowErrors['REQUIRED'][$intCountRowsAll] = 'Запись под номером: '.$intCountRowsAll.' не добавлена - отсутствует дата контакта';
		                    }

		                    if (!isset($arRowErrors['UNIQUE'][$intCountRowsAll]) && !isset($arRowErrors['REQUIRED'][$intCountRowsAll])) {
				                    addAddressComponents($arProp, ['ATT_AREA', 'ATT_CITY'], IBLOCK_ID_MIGRATION_DOCS, ['area', 'city'], '202ef02ba212fda90bb83c1957d4f84c1d14aea8');
		                    }
                    }

                    pre($arProp);
                    pre($arRowErrors);
                    pre(count($arRowErrors['UNIQUE']));



		                if (!isset($arRowErrors['UNIQUE'][$intCountRowsAll]) && !isset($arRowErrors['REQUIRED'][$intCountRowsAll])) {

				                $arLoadPeoples = array(
						                "MODIFIED_BY" => $USER->GetID(),
						                "IBLOCK_SECTION_ID" => $sectionID,
						                "IBLOCK_ID" => IBLOCK_ID_MIGRATION_DOCS,
						                "PROPERTY_VALUES" => $arProp,
						                "NAME" => $arRes[$intStartColumnNumber - 1],
						                "ACTIVE" => "Y",
				                );

				                if ($ID = $el->Add($arLoadPeoples)) {
						                $intCountRows++;
				                } else {
						                echo "Error: " . $el->LAST_ERROR;
				                }
		                }


                }
		            $intCountRowsAll++;
            }

            if ($intCountRows > 0) {
                echo '<div class="message"><span class="message_success">' . GetMessage("ADD_COUNT") . $intCountRows . '</span></div>';
                if (count($arRowErrors['UNIQUE']) > 0) {
		                echo '<div class="message"><span class="message_error">' . implode("; ", $arRowErrors['UNIQUE']) . '</span></div>';
                }
		            if (count($arRowErrors['REQUIRED']) > 0) {
				            echo '<div class="message"><span class="message_error">' . implode("; ", $arRowErrors['REQUIRED']) . '</span></div>';
		            }
            } else {
		            if (count($arRowErrors['UNIQUE']) > 0) {
				            echo '<div class="message"><span class="message_error">' . implode("; ", $arRowErrors['UNIQUE']) . '</span></div>';
		            }
		            if (count($arRowErrors['REQUIRED']) > 0) {
				            echo '<div class="message"><span class="message_error">' . implode("; ", $arRowErrors['REQUIRED']) . '</span></div>';
		            }
                echo '<div class="message"><span class="message_error">' . GetMessage("ADD_ERROR") . '</span></div>';
            }
        }

        switch ($strExtFile) {
            case 'csv':
                addElements(';', $_FILES['file']['tmp_name'], $arResult['PROPERTY_LIST'][$_REQUEST['TYPE']], $arIdxDateFormat, intval($arResult['SECTIONS'][$_REQUEST['TYPE']]));
                break;
            case 'xls':
                $fileID = CFile::SaveFile($_FILES['file'], "xls2csv");
                if (intval($fileID > 0)) {
                    $arFileXls = CFile::GetFileArray($fileID);
                    $fileNameCsv = str_replace('.xls', '.csv', $arFileXls['SRC']);
                    shell_exec('convertxls2csv -x "' . $_SERVER["DOCUMENT_ROOT"] . $arFileXls['SRC'] . '" -b WINDOWS-1251 -c "' . $_SERVER["DOCUMENT_ROOT"] . $fileNameCsv . '" -a UTF-8');
                    $arFileCsv = CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"] . $fileNameCsv);

                    addElements(',', $arFileCsv['tmp_name'], $arResult['PROPERTY_LIST'][$_REQUEST['TYPE']], $arIdxDateFormat, intval($arResult['SECTIONS'][$_REQUEST['TYPE']]));
                }
                break;
            case 'xlsx':
                $fileID = CFile::SaveFile($_FILES['file'], "xlsx2csv");
                if (intval($fileID > 0)) {
                    $arFileXls = CFile::GetFileArray($fileID);
                    $fileNameCsv = str_replace('.xlsx', '.csv', $arFileXls['SRC']);
                    shell_exec('xlsx2csv -d ";" -f %d.%m.%Y ' . $_SERVER["DOCUMENT_ROOT"] . $arFileXls['SRC'] . ' ' . $_SERVER["DOCUMENT_ROOT"] . $fileNameCsv);
                    $arFileCsv = CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"] . $fileNameCsv);

                    addElements(';', $arFileCsv['tmp_name'], $arResult['PROPERTY_LIST'][$_REQUEST['TYPE']], $arIdxDateFormat, intval($arResult['SECTIONS'][$_REQUEST['TYPE']]));
                }
                break;
        }
    } else {
        LocalRedirect('/isolation');
    }
}
