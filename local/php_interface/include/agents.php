<?php

use Monolog\Logger;
use Bitrix\Main\Entity;
use Bitrix\Main\Loader;
use Bitrix\Highloadblock as HL;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\RotatingFileHandler;
use Dompdf\Dompdf;
use Bitrix\Main\UserTable;
use \Bitrix\Main\Mail\Event;

require $_SERVER["DOCUMENT_ROOT"] . "/local/vendor/autoload.php";

Loader::includeModule("highloadblock");
Loader::includeModule("iblock");
Loader::includeModule('im');
function c_site_dir()
{
    $ar_sites = [];
    $ar_sites_file = $_SERVER['DOCUMENT_ROOT']."/bitrix/c_site_dir.php";

    $rsSites = CSite::GetList($by="sort", $order="desc", []);
    while ($arSite = $rsSites->Fetch()) {
        $ar_sites[] = $arSite['DIR'];
    }
    file_put_contents($ar_sites_file, "<?php return ".var_export($ar_sites, true).";");
    return "c_site_dir();";
}

function urlrewrite_extra()
{
    include $_SERVER['DOCUMENT_ROOT']."/urlrewrite.php";
    if (
        !isset($arUrlRewriteExtra) &&
        isset($arUrlRewrite) &&
        is_array($arUrlRewrite) &&
        count($arUrlRewrite) > 20
    ) {
        copy($_SERVER['DOCUMENT_ROOT']."/urlrewrite.php", $_SERVER['DOCUMENT_ROOT']."/urlrewrite_arch.php");
        file_put_contents($_SERVER['DOCUMENT_ROOT']."/urlrewrite.php", PHP_EOL.'include __DIR__."/urlrewrite_extra.php";'.PHP_EOL, FILE_APPEND);
    }
    return "urlrewrite_extra();";
}

function generateBICSV()
{
    global $DB;
    if (CModule::IncludeModule("iblock")) {
        $arHeader = [
            'Название',
            'Краткое наименование показателя',
            'Основание установления целевого показателя',
            'Целевое значение',
            'Текущее значение',
            '% исполнения',
            'Примечание',
            'Отдел',
            'Дата',
            'Флаг',
        ];

        $arIndicators = array();
        $arCSV = array();
        $arCSV[0] = $arHeader;
        $count = 0;
        $arSelect = array("ID", "IBLOCK_ID", "NAME", "DATE_ACTIVE_FROM", "PROPERTY_*");
        $arFilter = array("IBLOCK_ID" => 524, "ACTIVE_DATE" => "Y", "ACTIVE" => "Y", '>=DATE_ACTIVE_FROM' => date($DB->DateFormatToPHP(CLang::GetDateFormat("SHORT")), strtotime('-7 day')));
        $res = CIBlockElement::GetList(array(), $arFilter, false, false, $arSelect);

        while ($ob = $res->GetNextElement()) {
            $arFields = $ob->GetFields();
            $arIndicators[$count]['NAME'] = $arFields['NAME'];
            $arProps = $ob->GetProperties();
            $arIndicators[$count]['SHORT_NAME'] = $arProps['ATT_SHORT_NAME']['VALUE'];
            $arIndicators[$count]['BASE_SET'] = $arProps['ATT_BASE_SET']['VALUE'];
            $arIndicators[$count]['TARGET_VALUE'] = $arProps['ATT_TARGET_VALUE']['VALUE'];
            $arIndicators[$count]['STATE_VALUE'] = $arProps['ATT_STATE_VALUE']['VALUE'];
            $arIndicators[$count]['PERCENT_EXEC'] = $arProps['ATT_PERCENT_EXEC']['VALUE'];
            $arIndicators[$count]['COMMENT'] = $arProps['ATT_COMMENT']['VALUE']['TEXT'];
            $arIndicators[$count]['DEPARTMENT'] = $arProps['ATT_DEPARTMENT']['VALUE'];
            $arIndicators[$count]['DATE'] = $arProps['ATT_DATE']['VALUE'];
            $arIndicators[$count]['FLAG'] = $arProps['ATT_FLAG']['VALUE'];

            $count++;
        }

        $cnt = 1;
        foreach ($arIndicators as $value) {
            $arCSV[$cnt] = array(
                $value['NAME'],
                $value['SHORT_NAME'],
                $value['BASE_SET'],
                $value['TARGET_VALUE'],
                $value['STATE_VALUE'],
                $value['PERCENT_EXEC'],
                $value['COMMENT'],
                $value['DEPARTMENT'],
                $value['DATE'],
                $value['FLAG'],
            );
            $cnt++;
        }

        $fp = fopen($_SERVER['DOCUMENT_ROOT'].'/upload/bi.csv', 'w');

        fputs($fp, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

        foreach ($arCSV as $fields) {
            fputcsv($fp, $fields, ';');
        }

        fclose($fp);
    }

    return "generateBICSV();";
}

function getAddressForViolators()
{
    function getAddressFromCoordinates($arCoordinates)
    {
        $url = 'https://covid.tularegion.ru/local/ajax/address.php';
        $cURLConnection = curl_init();
        curl_setopt($cURLConnection, CURLOPT_URL, $url.'?lat='.$arCoordinates[0].'&lon='.$arCoordinates[1]);
        curl_setopt($cURLConnection, CURLOPT_RETURNTRANSFER, true);
        $address = curl_exec($cURLConnection);
        curl_close($cURLConnection);
        $address = json_decode($address);

        if ($address->response->GeoObjectCollection->metaDataProperty->GeocoderResponseMetaData->found > 0) {
            $data = $address->response->GeoObjectCollection->featureMember[0]->GeoObject->metaDataProperty->GeocoderMetaData->Address;
        }
        if ($data) {
            $strArea = '';
            $strAddress = $data->formatted;

            foreach ($data->Components as $value) {
                if ($value->kind == 'province') {
                    $strArea = $value->name;
                }
            }
            return [$strArea, $strAddress];
        } else {
            return ['нет адреса', 'нет адреса'];
        }
    }

    $hlblockViolators = HL\HighloadBlockTable::getById(HLBLOCK_ID_VIOLATORS)->fetch();
    $entityViolators = HL\HighloadBlockTable::compileEntity($hlblockViolators);
    $entity_data_class_violators = $entityViolators->getDataClass();

    $rsDataViolators = $entity_data_class_violators::getList(array(
        "select" => array("ID", "UF_COORDINATES"),
        "order" => ['id' => 'asc'],
        "filter" => ['UF_ADDRESS_VIOLATION' => false],
    ));

    while ($arDataViolators = $rsDataViolators->Fetch()) {
        $arDataAddress = getAddressFromCoordinates(explode(', ', $arDataViolators['UF_COORDINATES']));
        $strAddress = $arDataAddress[1];

        if ($arDataAddress[0] == 'Тульская область') {
            $arUpdateElement = ['UF_ADDRESS_VIOLATION' => $strAddress];
            $entity_data_class_violators::update($arDataViolators['ID'], $arUpdateElement);
        } elseif ($arDataAddress[0] == 'нет адреса') {
            $arUpdateElement = ['UF_ADDRESS_VIOLATION' => $strAddress];
            $entity_data_class_violators::update($arDataViolators['ID'], $arUpdateElement);
        } else {
            $entity_data_class_violators::Delete($arDataViolators['ID']);
        }
    }

    return "getAddressForViolators();";
}

/**
 * Метод генерации отчёта для людей, работающих удалённо.
 * Отчёты генерируются за прошлую неделю.
 * @see https://corp.tularegion.ru/company/personal/user/5745/tasks/task/view/136500/
 * @return void
 * @throws TasksException
 * @throws \PhpOffice\PhpWord\Exception\Exception
 */
function getReportDirectWork()
{
    CModule::IncludeModule('tasks');

    \Bitrix\Main\Loader::includeModule('nkhost.phpexcel');

    $arUsersTaskItilium = (function () {
        $obUsersItilium = new Citto\Integration\Itilium\User;
        $arUsersItilium = $obUsersItilium->getEmployees();

        foreach ($arUsersItilium as $iKey => $arItem) {
            if ($arItem['EMail']) {
                $arItem['TASKS'] = [];
                $arUsersItilium['EMAIL'][$arItem['EMail']] = $arItem;
                $arUsersItilium['UID'][$arItem['UID']] = $arItem;
                unset($arUsersItilium[$iKey]);
            }
        }

        $lastMonday = new DateTime(date('d.m.Y', strtotime('last week Monday')));

        $obTask = new Citto\Integration\Itilium\Task();
        $arFilter = [
            '>=DateEnd' => $lastMonday->format('Y-m-d\TH:i:s'),
        ];
        $arTasks = $obTask->getList($arFilter);

        foreach ($arTasks as $arItem) {
            $sUIDExecutor = $arItem['Executor']['UID'];
            $sTaskTitle = $arItem['Subject'];
            $sUserEmail = $arUsersItilium['UID'][$sUIDExecutor]['EMail'];
            if ($sUserEmail) {
                $arUsersItilium['EMAIL'][$sUserEmail]['TASKS'][] = $sTaskTitle;
            }
        }
        return $arUsersItilium['EMAIL'];
    })();

    $arSelect = [
        "ID",
        "NAME",
        "DATE_CREATE",
        "CREATED_BY",
        "PROPERTY_REESTR_FIO",
        "PROPERTY_REESTR_FIO_HEAD",
        "PROPERTY_COUNT_DAY",
        "PROPERTY_COUNT_WEEK",
    ];
    $arFilter = [
        "IBLOCK_CODE" => "sc_reestr"
    ];
    $arOrder = [];

    $obElems = CIBlockElement::GetList($arOrder, $arFilter, false, [], $arSelect);
    $arElements = [];
    while ($arElems = $obElems->Fetch()) {
        array_push($arElements, $arElems);
    }

    $lastMonday = new DateTime(date('d.m.Y', strtotime('last week Monday')));
    $lastFriday = new DateTime(date('d.m.Y', strtotime('last week Friday')));

    $arDates = [
        $lastMonday,
        $lastFriday
    ];

    foreach ($arElements as $elemData) {
        $arUser = CUser::GetByID($elemData['PROPERTY_REESTR_FIO_VALUE'])->Fetch();
        $arUserLead = CUser::GetByID($elemData['PROPERTY_REESTR_FIO_HEAD_VALUE'])->Fetch();

        $strUserFIO = "{$arUser['LAST_NAME']} {$arUser['NAME']} {$arUser['SECOND_NAME']}";
        $strUserLeadFIO = "{$arUserLead['LAST_NAME']} {$arUserLead['NAME']} {$arUserLead['SECOND_NAME']}";

        $strUserWP = $arUser['WORK_POSITION'];
        $strUserLeadWP = $arUserLead['WORK_POSITION'];

        $arTasks = [];
        $arOrder = [];
        $arSelect = [];
        $arFilter = [
            'RESPONSIBLE_ID' => $arUser['ID']
        ];

        $arTasks = [];
        $obTasks = CTasks::GetList($arOrder, $arFilter, $arSelect);
        while ($arTask = $obTasks->Fetch()) {
            $newDate = new DateTime(stristr($arTask['STATUS_CHANGED_DATE'], ' ', true));
            if ($newDate >= $lastMonday && $newDate <= $lastFriday) {
                array_push($arTasks, $arTask);
            }
        }
        foreach ($arUsersTaskItilium[$arUser['EMAIL']]['TASKS'] as $sTask) {
            $arTasks[] = ['TITLE' => $sTask];
        }

        $arDefaultTasks = [
            "Распределение нагрузки и корректировка приоритезации задач",
            "Контроль сроков исполнения поручений",
            "Организация, проведение и участие в рабочих встречах",
            "Мониторинг и анализ показателей"
        ];

        $arDefWP = [
            'Руководитель отдела',
            'Руководитель управления',
            'Руководитель группы'
        ];

        $sHeader = '<span>УТВЕРЖДАЮ</span>
        <br>
        <span>' . $strUserLeadWP . '</span>
        <br>
        <span>(должность руководителя структурного подразделения)</span>
        <br>
        <span>' . $strUserLeadFIO . ' </span>
        <br>
        <span>(ФИО)</span>';

        $sMainDoc = '
        <!DOCTYPE html>
        <html>

        <head>
            <title>Служебная записка</title>
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <style>
                body {
                font-family: DejaVu Sans, sans-serif;
                }
            </style>
        </head>

        <body style="font-size:14px; width: 600px;">
            <div style="text-align: right; font-weight: bold">
                ' . $sHeader . '
            </div>
            <p style="text-align: justify; margin: 0; "> Отчет о работе ' . $strUserWP . ', ' . $strUserFIO . ' за период с ' . $lastMonday->format('d.m.Y') . ' по ' . $lastFriday->format('d.m.Y') . '
            <br>
            <table border="1">
            <tr>
                <th>№ п/п
                <th>Наименование работы
                <th>Затраченное время, час./мин.';

            foreach ($arDates as $date) {
                $resDate = $date->format('d.m.Y');
                $sMainDoc .= '
                <tr>
                    <td colspan=2 style="text-align: center;">' . $resDate . '
                    <td style="text-align: center;"> ' . $elemData['PROPERTY_COUNT_DAY_VALUE'] . '
                </tr>';
            if (count($arTasks) <= 5 && in_array($strUserWP, $arDefWP)) {
                foreach ($arDefaultTasks as $iKey -> $arItem) {
                    $taskKey = $iKey + 1;
                    $sMainDoc .= '<tr>
                    <td>' . $taskKey . '
                    <td>' . $arItem['TITLE'] . '
                </tr>';
                }
            } else {
                foreach ($arTasks as $iKey => $arItem) {
                    $taskKey = $iKey + 1;
                    $sMainDoc .= '<tr>
                    <td>' . $taskKey . '
                    <td>' . $arItem['TITLE'] . '
                </tr>';
                }
            }
        }
            $sMainDoc .= '<tr>
                <td colspan=2 style="text-align: center;">Итого:
                <td style="text-align: center;">' . $elemData['PROPERTY_COUNT_WEEK_VALUE'] . '
                </tr>';
            $sMainDoc .= '
            </table>';

        $dompdf = new DOMPDF();
        $dompdf->set_option('isHtml5ParserEnabled', true);
        $dompdf->load_html($sMainDoc);
        $dompdf->render();
        $output = $dompdf->output();
        $filePath = $_SERVER['DOCUMENT_ROOT'] . '/upload/report_pdf/otchet_' . $arUser['ID'] . '_' . $lastMonday->format('d.m.Y') . '.pdf';


        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/upload/report_pdf/otchet_' . $arUser['ID'] . '_' . $lastMonday->format('d.m.Y') . '.pdf', $output);
        $path = CFile::MakeFileArray($filePath);

        $arProps = [
            'DIRECT_WORK_FILE' => $path,
            'DIRECT_WORK_LEAD' => $arUserLead['ID'],
            'DATE_START'       => $lastMonday->format('d.m.Y'),
            'DATE_END'         => $lastFriday->format('d.m.Y'),
            'SOTRUDNIK'        => $arUser['ID'],
        ];

        $el = new CIBlockElement();
        $arLoadProductArray = [
            'CREATED_BY' => $arUser['ID'],
            'MODIFIED_BY' => $arUser['ID'],
            'IBLOCK_SECTION_ID' => false,
            'IBLOCK_ID' => 698,
            'PROPERTY_VALUES' => $arProps,
            'NAME' => 'Отчёт по удалённой работе - ' . $strUserFIO,
            'ACTIVE' => "Y",
            'PREVIEW_TEXT' => "",
        ];
        $documentId = $el->Add($arLoadProductArray);
        if (!$documentId) {
            throw new Exception($el->LAST_ERROR);
        }

        $arErrorsTmp = array();
        $wfId = CBPDocument::StartWorkflow(
            1676,
            ["lists", "BizprocDocument", $documentId],
            ['TargetUser' => "user_" . $arUser['ID']],
            $arErrorsTmp
        );
    }
        return "getReportDirectWork();";
}

function addAddressComponentsIsolationT2()
{
    $dadataKey = '202ef02ba212fda90bb83c1957d4f84c1d14aea8';

    function getArea($sApiKey, $address, $debug = false)
    {
        $arResult = [];
        $area = [];
        if ($oCurl = curl_init("http://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address")) {
            curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($oCurl, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Token ' . $sApiKey
            ]);
            curl_setopt($oCurl, CURLOPT_POST, 1);
            curl_setopt($oCurl, CURLOPT_POSTFIELDS, json_encode(['query' => $address, 'locations' => [['region_fias_id' => 'd028ec4f-f6da-4843-ada6-b68b3e0efa3d']]]));
            $sResult = curl_exec($oCurl);
            $arResult = json_decode($sResult, true);
            if ($arResult['suggestions'][0]['data']['city_district_with_type']) {
                $area['area'] = $arResult['suggestions'][0]['data']['city_district_with_type'];
                if ($arResult[0]['settlement_type_full'] == 'поселок') {
                    $area['city'] = 'п. '.$arResult['suggestions'][0]['data']['settlement'];
                } elseif ($arResult[0]['settlement_type_full'] == 'село') {
                    $area['city'] = 'с. '.$arResult['suggestions'][0]['data']['settlement'];
                } elseif ($arResult[0]['settlement_type_full'] == 'деревня') {
                    $area['city'] = 'д. '.$arResult['suggestions'][0]['data']['settlement'];
                } else {
                    $area['city'] = $arResult['suggestions'][0]['data']['city'];
                }
            } elseif ($arResult['suggestions'][0]['data']['area_with_type']) {
                $area['area'] = $arResult['suggestions'][0]['data']['area_with_type'];
                if ($arResult['suggestions'][0]['data']['settlement_type_full'] == 'поселок') {
                    $area['city'] = 'п. '.$arResult['suggestions'][0]['data']['settlement'];
                } elseif ($arResult['suggestions'][0]['data']['settlement_type_full'] == 'село') {
                    $area['city'] = 'с. '.$arResult['suggestions'][0]['data']['settlement'];
                } elseif ($arResult['suggestions'][0]['data']['settlement_type_full'] == 'деревня') {
                    $area['city'] = 'д. '.$arResult['suggestions'][0]['data']['settlement'];
                } else {
                    $area['city'] = $arResult['suggestions'][0]['data']['city'];
                }
            } elseif ($arResult['suggestions'][0]['data']['region_with_type']) {
                $area['area'] = $arResult['suggestions'][0]['data']['region_with_type'];
                if ($arResult['suggestions'][0]['data']['settlement_type_full'] == 'поселок') {
                    $area['city'] = 'п. '.$arResult['suggestions'][0]['data']['settlement'];
                } elseif ($arResult['suggestions'][0]['data']['settlement_type_full'] == 'село') {
                    $area['city'] = 'с. '.$arResult['suggestions'][0]['data']['settlement'];
                } elseif ($arResult['suggestions'][0]['data']['settlement_type_full'] == 'деревня') {
                    $area['city'] = 'д. '.$arResult['suggestions'][0]['data']['settlement'];
                } else {
                    $area['city'] = $arResult['suggestions'][0]['data']['city'];
                }
            } else {
                $area['area'] = 'нет района';
                $area['city'] = 'нет города';
            }

            curl_close($oCurl);
        }

        if ($debug) {
            return ['area' => $area, 'rs' => $arResult];
        } else {
            return $area;
        }
    }

    function addAddressComponentsAgent(&$arProperties, $enumCode, $iblockID, $kind, $dadataKey)
    {
        Loader::IncludeModule('iblock');
        $logger = new Logger('ADD ADDRESS COMPONENTS');
        $logger->pushHandler(new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/local/logs/isolation_contact_table_address.log', Logger::INFO));

        $arArea = getArea($dadataKey, $arProperties['ATT_ADDRESS']);

        $logger->info('Добавление адресных компонентов: ', $arArea);

        foreach ($enumCode as $key => $enum) {
            $arProperties[$enum] = false;

            $propArea = \CIBlockProperty::GetList(
                [],
                [
                    'IBLOCK_ID' => $iblockID,
                    'CODE' => $enum
                ]
            )->Fetch();


            $property_enums = CIBlockPropertyEnum::GetList(array("DEF"=>"DESC", "SORT"=>"ASC"), array("IBLOCK_ID"=>$iblockID, "CODE"=>$enum, "VALUE" => $arArea[$kind[$key]]));
            if ($property_enums->SelectedRowsCount() > 0) {
                while ($enum_fields = $property_enums->GetNext()) {
                        $arProperties[$enum] = $enum_fields['ID'];
                        $logger->info('Найден: ' .$enum. ' с ID: '.$enum_fields['ID']);
                }
            } else {
                $rsEnum = new \CIBlockPropertyEnum();
                $valueId = $rsEnum->Add([
                    'PROPERTY_ID' => $propArea['ID'],
                    'VALUE' => $arArea[$kind[$key]],
                ]);

                $arProperties[$enum] = $valueId;
                $logger->info('Добавлен: ' .$enum. ' с ID: '.$valueId);
            }
        }
    }

    $logger = new Logger('UPDATE ADDRESS COMPONENTS ELEMENT');
    $logger->pushHandler(new StreamHandler($_SERVER['DOCUMENT_ROOT'] . '/local/logs/isolation_contact_table_update_address.log', Logger::INFO));

    $arSelect = array(
        "ID",
        "IBLOCK_ID",
        "PROPERTY_ATT_AREA",
        "PROPERTY_ATT_ADDRESS",
        "PROPERTY_ATT_CITY",
    );
    $arFilter = array(
        "IBLOCK_ID"=>IBLOCK_ID_MIGRATION_DOCS,
        "ACTIVE_DATE"=>"Y",
        "ACTIVE"=>"Y",
        "IBLOCK_SECTION_ID" => SECTION_ID_MIGRATION_CONT,
        "PROPERTY_ATT_AREA_VALUE" => false,
        "PROPERTY_ATT_CITY_VALUE" => false,
        "!PROPERTY_ATT_ADDRESS" => false
    );

    $res = CIBlockElement::GetList(array('id' => 'asc'), $arFilter, false, ['nPageSize' => 1, 'nTopCount' => 1000], $arSelect);
    pre($res->SelectedRowsCount());
    while ($arFields = $res->GetNext()) {
        $arProps['ATT_ADDRESS'] = $arFields['PROPERTY_ATT_ADDRESS_VALUE'];

        addAddressComponentsAgent($arProps, ['ATT_AREA', 'ATT_CITY'], IBLOCK_ID_MIGRATION_DOCS, ['area', 'city'], '202ef02ba212fda90bb83c1957d4f84c1d14aea8');

        CIBlockElement::SetPropertyValuesEx($arFields['ID'],IBLOCK_ID_MIGRATION_DOCS , array('ATT_AREA' => $arProps['ATT_AREA']));
        CIBlockElement::SetPropertyValuesEx($arFields['ID'],IBLOCK_ID_MIGRATION_DOCS , array('ATT_CITY' => $arProps['ATT_CITY']));

        $logger->info('Обновлен элемент с ID: '.$arFields['ID'], $arProps);
    }

    return "addAddressComponentsIsolationT2();";
}

/**
 * Приветственное письмо + запуск БП "Лист адаптации" для новых сотрудников
 * @return string
 * @throws \Bitrix\Main\ArgumentException
 * @throws \Bitrix\Main\ObjectPropertyException
 * @throws \Bitrix\Main\SystemException
 */
function alertWelcomeMail()
{
    $logger = new Logger("welcome_mail");
    $logger->pushHandler(
        new RotatingFileHandler(
            $_SERVER['DOCUMENT_ROOT'] . '/local/logs/welcome_mail/welcome_mail.log',
            60
        )
    );
    
    $arUsersCreatedBP = [];
    $obElemsBP = CIBlockElement::GetList([], ['IBLOCK_CODE' => 'formirovanie_listka_adaptaciy'], false, [], ['CREATED_BY']);
    while ($arItem = $obElemsBP->Fetch()) {
        $iUserID = $arItem['CREATED_BY'];
        if (!in_array($iUserID, $arUsersCreatedBP)) {
            $arUsersCreatedBP[] = $arItem['CREATED_BY'];
        }
    }

    $arUsersForSendMail = [];
    $orm = UserTable::getList([
        'select' => ['ID', 'UF_WELCOME_MAIL', 'DATE_REGISTER', 'EMAIL'],
        'filter' => ['ACTIVE' => 'Y', '!ID' => $arUsersCreatedBP]
    ]);

    while ($arUser = $orm->fetch()) {
        $arUser['DATE_REGISTER'] = $arUser['DATE_REGISTER']->format('d.m.Y H:i:s');
        $arInfo = json_decode($arUser['UF_WELCOME_MAIL']);

        if ($arInfo->RUN_BP != 'Y') {
            if (!$arInfo->DATE_SEND_MAIL) {
                $arUsersForSendMail[] = $arUser;
                $logger->info("USER_{$arUser['ID']}", ["STATUS" => "Первое оповещение", "INFO" => $arUser]);
            } elseif (strtotime($arInfo->DATE_SEND_MAIL) < time() - 86400 * 7) {
                $arUsersForSendMail[] = $arUser;
                $logger->info("USER_{$arUser['ID']}", ["STATUS" => "Повторное оповещение", "INFO" => $arUser]);
            }
        }
    }

    $obUser = new CUser;
    foreach ($arUsersForSendMail as $arUser) {
        $arFields = [
            "UF_WELCOME_MAIL" => json_encode(['DATE_SEND_MAIL' => date('d.m.Y H:i:s')]),
        ];

        /* Тестовая приблуда для оповещения */
        (function () use ($arUser) {
            $arTestUsers = [
                570 /* Саушкин Р.А. */
            ];

            if (in_array($arUser['ID'], $arTestUsers)) {
                if (!empty($arUser['EMAIL'])) {
                    Event::send(
                        [
                            'EVENT_NAME' => 'BP_LIST_ADAPTATION_START',
                            'LID' => 's1',
                            'C_FIELDS' => [
                                'EMAIL_TO' => $arUser['EMAIL']
                            ]
                        ]
                    );
                }

                CIMMessenger::Add(
                    [
                        "MESSAGE_TYPE" => "S",
                        "TO_USER_ID" => $arUser['ID'],
                        "FROM_USER_ID" => 1,
                        "MESSAGE" => file_get_contents("./bizproz/list_adaptaciy/message-corp.html"),
                        "AUTHOR_ID" => 1,
                        "EMAIL_TEMPLATE" => "some",
                        "NOTIFY_TYPE" => IM_NOTIFY_CONFIRM,
                        "NOTIFY_BUTTONS" => [
                            [
                                'TITLE' => 'Перейти',
                                'VALUE' => 'Y',
                                'TYPE' => 'accept',
                                'URL' => '/bizproc/processes/600/element/0/0/?list_section_id='
                            ]
                        ]
                    ]
                );
            }
        })();

        $obUser->update($arUser['ID'], $arFields);
    }

    return "alertWelcomeMail();";
}
