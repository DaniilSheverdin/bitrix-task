<?php

namespace Citto\Integration;

use CUser;
use CEvent;
use CModule;
use Monolog\Logger;
use CUserTypeEntity;
use CBitrixComponent;
use Bitrix\Main\UserTable;
use Monolog\Handler\RotatingFileHandler;
use Sprint\Migration\Helpers\HlblockHelper;
use Bitrix\Main\{Config\Option, Event, Loader};
use Citto\Vaccinationcovid19\Component as Vaccination;
use Bitrix\Highloadblock\HighloadBlockTable as HLTable;


if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * Базовый каталог модуля
 */
const BASE_DIR = __DIR__;

$obEevent = new Event('citto.integration', 'onModuleInclude');
CBitrixComponent::includeComponentClass('citto:vaccination_covid19');
CBitrixComponent::includeComponentClass('citto:register_heads');
Loader::includeModule('highloadblock');
Loader::includeModule("workflow");

$obEevent->send();

Loader::registerAutoLoadClasses(
    'citto.integration',
    [
        '\Citto\Integration\Source1C' => '/lib/source1c.php',

        '\SCUD\CEventsScud'        => '/lib/scud/CEventsScud.php',
        '\SCUD\CUsersCorp'         => '/lib/scud/CUsersCorp.php',
        '\SCUD\CUsersParsec'       => '/lib/scud/CUsersParsec.php',
        'Citto\Integration\Docx'   => '/lib/docx.php',
        'Citto\Integration\Email'  => '/lib/email.php',
        'Citto\Integration\Module' => '/lib/module.php',

        'Citto\Integration\Delo'             => '/lib/delo.php',
        'Citto\Integration\Delo\Sync'        => '/lib/delo/sync.php',
        'Citto\Integration\Delo\Users'       => '/lib/delo/users.php',
        'Citto\Integration\Delo\BpSign'      => '/lib/delo/bp_sign.php',
        'Citto\Integration\Delo\RestService' => '/lib/delo/rest_service.php',

        'Citto\Integration\Itilium'             => '/lib/itilium.php',
        'Citto\Integration\Itilium\File'        => '/lib/itilium/file.php',
        'Citto\Integration\Itilium\Sync'        => '/lib/itilium/sync.php',
        'Citto\Integration\Itilium\Task'        => '/lib/itilium/task.php',
        'Citto\Integration\Itilium\Project'     => '/lib/itilium/project.php',
        'Citto\Integration\Itilium\Mock'        => '/lib/itilium/mock.php',
        'Citto\Integration\Itilium\User'        => '/lib/itilium/user.php',
        'Citto\Integration\Itilium\Agent'       => '/lib/itilium/agent.php',
        'Citto\Integration\Itilium\Message'     => '/lib/itilium/message.php',
        'Citto\Integration\Itilium\Incident'    => '/lib/itilium/incident.php',
        'Citto\Integration\Itilium\RestService' => '/lib/itilium/rest_service.php',
    ]
);

class CBitrixSCUD
{
    public function AgentSyncParsec()
    {
        if (!defined("NOT_WORK_PARSEC")) {
            $logger = new Logger('default');
            $logger->pushHandler(
                new RotatingFileHandler(
                    $_SERVER['DOCUMENT_ROOT'] . '/local/logs/scud/sync_events_parsec.log',
                    90
                ));

            try {
                $arModuleOptions = unserialize(Option::get('citto.integration', 'values'));
                if ((!empty($arModuleOptions['date_from_parsec']) && !empty($arModuleOptions['date_to_parsec']))) {
                    $iDateFrom = $arModuleOptions['date_from_parsec'];
                    $iDateTo = $arModuleOptions['date_to_parsec'];
                } else {
                    $iCurrentDate = strtotime(date('Y-m-d'));
                    $iDateFrom = $iCurrentDate - 86400;
                    $iDateTo = $iCurrentDate;
                }
                $CEvents = new \SCUD\CEventsScud();
                $CEvents->runEvents((int)$iDateFrom, (int)$iDateTo);
            } catch (\Exception $e) {
                $logger->error('Ошибка: ', [$e->faultcode, $e->faultstring]);
            }
        }

        return "\Citto\Integration\CBitrixSCUD::AgentSyncParsec();";
    }

    /**
     * Оповещает ответственных об истечении срока действия пропуска
     * @return string
     */
    public function AgentAlertExpiresAccess() : string
    {

        $logger = new Logger('default');
        $logger->pushHandler(
            new RotatingFileHandler(
                $_SERVER['DOCUMENT_ROOT'] . '/local/logs/scud/alert_expires_access.log',
                90
            ));

        function inner(int $iErrorRestart, object $logger)
        {
            try {
                $obUsersParsec = new \SCUD\CUsersParsec();

                $obNextMonth = (new DateTime())->add(new DateInterval("P1M"));
                $iCountDaysNextMonth = gmdate("t", gmmktime(0, 0, 0, $obNextMonth->format('m'), 1, $obNextMonth->format('y')));

                $sDateFrom = $obNextMonth->format('Y-m-01');
                $sDateTo  = $obNextMonth->format("Y-m-$iCountDaysNextMonth");

                $arOrganizations = $obUsersParsec->getOrganizations();
                $arExpiresPersonal =  $obUsersParsec->getExpiresPersonal($sDateFrom, $sDateTo);
                $arUsersParsec = $obUsersParsec->getAllUsers();
                $arUsersSID = (function() {
                    $arUsers = [];
                    $obUsers = CUser::GetList(
                        $by = "",
                        $order = "",
                        [],
                        [
                            'FIELDS' => ['ID'],
                            'SELECT' => ['UF_SID', 'UF_WORK_POSITION']
                        ]
                    );

                    while ($arUser = $obUsers->getNext()) {
                        if ($arUser['UF_SID']) {
                            $arUsers[$arUser['UF_SID']] = $arUser['UF_WORK_POSITION'];
                        }
                    }

                    return $arUsers;
                })();

                $arNotResponsible = [];
                $arData = [];
                foreach ($arExpiresPersonal as $sPersID => &$arInfo) {
                    $sSID = $arUsersParsec['SID'][$sPersID]['SID'];
                    $arInfo['POSITION'] = $arUsersSID[$sSID];
                    $arInfo['ACCEPTER'] = $arOrganizations[$arInfo['ORG_ID']]['UF_ACCEPTER'];
                    $iResponsibleID = $arOrganizations[$arInfo['ORG_ID']]['UF_RESPONSIBLE'];

                    if (!$iResponsibleID && !in_array($arInfo['ORG_NAME'], $arNotResponsible)) {
                        $arNotResponsible[] = $arInfo['ORG_NAME'];
                    } else if ($iResponsibleID) {
                        $arData[$iResponsibleID][] = $arInfo;
                    }
                }

                foreach ($arData as $iUserID => $arInfo) {
                    $arLoadProductArray = [
                        'MODIFIED_BY' => $iUserID,
                        'IBLOCK_SECTION_ID' => false,
                        'IBLOCK_ID' => 705,
                        'PROPERTY_VALUES' => ['DATA' => json_encode($arInfo)],
                        'NAME' => date('d.m.Y H:i:s'),
                        'ACTIVE' => "Y",
                        'PREVIEW_TEXT' => '',
                    ];

                    $objEl = new CIBlockElement();
                    $boolDocumentid = $objEl->Add($arLoadProductArray);

                    $strWfId = CBPDocument::StartWorkflow(
                        1766,
                        ["lists", "BizprocDocument", $boolDocumentid],
                        ['TargetUser' => "user_" . $iUserID],
                        $arErrorsTmp
                    );
                }


            } catch (\Exception $e) {
                if ($iErrorRestart == 5) {
                    $logger->error('Ошибка: ', [$e->getMessage()]);
                } else {
                    $iErrorRestart++;
                    sleep(30);
                    return inner($iErrorRestart, $logger);
                }
            }
        }

        inner(0, $logger);

        return "\Citto\Integration\CBitrixSCUD::AgentAlertExpiresAccess();";
    }
}

class BusinessProcesses
{
    /**
     * @return string
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\ArgumentOutOfRangeException
     * БП Удалённая работа
     * Оповещение сотрудника, уходящего на удаленную работу, за день до ухода о том, что документы,
     * дающие основание для работы удаленно, не подписаны.
     */
    public function AgentAlertRemoteWork()
    {
        $sDateTomorrow = (new \DateTime())->modify('+1 day')->format('d.m.Y');
        $arSelect = array("ID", "NAME", "DATE_ACTIVE_FROM", "PROPERTY_PROCESS_END", "PROPERTY_DATA_NACHALA", "PROPERTY_SOTRUDNIK");
        $arFilter = array("IBLOCK_CODE" => "remote_work", "ACTIVE_DATE" => "Y", "ACTIVE" => "Y", "!PROPERTY_PROCESS_END_VALUE" => "Да");
        $obRes = \CIBlockElement::GetList([], $arFilter, false, [], $arSelect);
        $arUsers = [];

        while ($obElement = $obRes->GetNext()) {
            if ($obElement['PROPERTY_DATA_NACHALA_VALUE'] == $sDateTomorrow) {
                $iUserID = $obElement['PROPERTY_SOTRUDNIK_VALUE'];
                $arUsers[] = $iUserID;
            }
        }

        if ($arUsers) {
            $sMessage = "Документы, дающие основание для работы удаленно, не подписаны";

            $arUsersMail = [];
            $obUsers = UserTable::getList([
                'select' => ['ID', 'EMAIL'],
                'filter' => ['ID' => $arUsers],
            ]);

            while ($arItem = $obUsers->fetch()) {
                $arUsersMail[$arItem['ID']] = $arItem['EMAIL'];
            }

            foreach ($arUsers as $iUserID) {
                \CIMMessenger::Add(array(
                    'TITLE'         => 'Напоминание о запущенном сборе подписей',
                    'MESSAGE'       => $sMessage,
                    'TO_USER_ID'    => $iUserID,
                    'FROM_USER_ID'  => 2661,
                    'MESSAGE_TYPE'  => 'S',
                    'NOTIFY_MODULE' => 'intranet',
                    'NOTIFY_TYPE'   => 2,
                ));

                if ($sEmailUser = $arUsersMail[$iUserID]) {
                    CEvent::Send("ALERT_LPA", 's1', ['TEXT' => $sMessage, 'THEME' => 'БП Удаленная работа', 'EMAIL_TO' => $sEmailUser]);
                }
            }
        }

        return "\Citto\Integration\BusinessProcesses::AgentAlertRemoteWork();";
    }

    /**
     * @return string
     * Оповещение за 3 дня ответственных лиц о ревакцинации
     */
    public function AgentAlertUsersRevaccination()
    {
        $logger = new Logger('default');
        $logger->pushHandler(
            new RotatingFileHandler(
                $_SERVER['DOCUMENT_ROOT'] . '/local/logs/itilium/agent/syncStatusInc.log',
                90
            ));
        //Вакнцинация
        $obVaccinationcovid19 = new \Citto\Vaccinationcovid19\Component();
        $obVaccinationcovid19->alertUsersRevaccination();

        return "\Citto\Integration\BusinessProcesses::AgentAlertUsersRevaccination();";
    }

    /**
     * @return string
     * БП Реестр руководителей
     * Оповещение за 1 месяц и за 3 меясца ответственных лиц
     */
    public function AgentAlertUsersContract()
    {
        $logger = new Logger('default');
        $logger->pushHandler(
            new RotatingFileHandler(
                $_SERVER['DOCUMENT_ROOT'] . '/local/logs/bizproc/reestr_heads_agent.log',
                90
            ));

        function inner(int $iErrorRestart, object $logger)
        {
            try {
                $obRegisterHeads = new \Citto\RegisterHeads\Component();
                $obRegisterHeads->alertUsersContract();
            } catch (\Exception $e) {
                if ($iErrorRestart == 5) {
                    $logger->error('Ошибка: ', [$e->getMessage()]);
                } else {
                    $iErrorRestart++;
                    sleep(30);
                    return inner($iErrorRestart, $logger);
                }
            }
        }

        inner(0, $logger);

        return "\Citto\Integration\BusinessProcesses::AgentAlertUsersContract();";
    }

    /**
     * @return string
     * БП Уведомление об иной оплачиваемой работе
     * Оповещение госслужащих
     */
    public function AgentAlertOtherWork()
    {
        $arAlertUsers = [
            'ALL'        => [],
            '2_WEEKS'    => [],
            'INDEFINITE' => []
        ];

        /* Получаем абсолютно всех госслужащих */

        $arUsersGov = [];
        $obUsers = UserTable::getList([
            'select' => ['ID', 'ACTIVE', 'LOGIN', 'XML_ID', 'UF_SID', 'EMAIL', 'UF_GOV_EMPLOYEE'],
            'filter' => [
                'ACTIVE'          => 'Y',
                'UF_GOV_EMPLOYEE' => true
            ]
        ]);

        while ($arUser = $obUsers->fetch()) {
            $arUsersGov[$arUser['ID']] = $arUser;
        }

        if (in_array(date('d.m'), ['15.01', '01.07'])) {
            $arAlertUsers['ALL'] = $arUsersGov;
        }

        /* Получаем абсолютно все записи БП */

        $arBPRecords = [];

        $obBPRecords = \CIBlockElement::GetList(
            ["PROPERTY_DATE_TO" => "DESC"],
            [
                "IBLOCK_CODE" => "uved_inaya_rabota",
                "ACTIVE_DATE" => "Y",
                "ACTIVE"      => "Y",
                [
                    "LOGIC" => "OR",
                    [
                        ">=PROPERTY_DATE_TO" => date('Y-m-d'),
                    ],
                    [
                        "PROPERTY_DATE_TO" => "1970-01-01",
                    ],
                ],
            ],
            false,
            [],
            [
                "ID",
                "CREATED_BY",
                "PROPERTY_VID_DEYATELNOSTI",
                "PROPERTY_DRUGOE_OSNOVANIE_VOZNIKNOVENIYA_PRAVOVYKH_OTNOSHEN",
                "PROPERTY_NAIMENOVANIE_YURIDICHESKOGO_LITSA_V_KOTOROM_PLANIR",
                "PROPERTY_DATE_TO"
            ]
        );

        while ($arRecord = $obBPRecords->GetNext()) {
            $sType = $arRecord['PROPERTY_VID_DEYATELNOSTI_VALUE'];
            $sOtherType = $arRecord['PROPERTY_DRUGOE_OSNOVANIE_VOZNIKNOVENIYA_PRAVOVYKH_OTNOSHEN_VALUE'];
            $arRecord['WORK_TYPE'] = ($sOtherType) ? $sOtherType : $sType;
            $arRecord['WORK_PLACE'] = $arRecord['PROPERTY_NAIMENOVANIE_YURIDICHESKOGO_LITSA_V_KOTOROM_PLANIR_VALUE'];
            $arBPRecords[$arRecord['ID']] = $arRecord;
        }

        /* Получаем ID незавершенных/прерванных БП и удаляем их их массива $arBPRecords */

        global $DB;
        $obBizprocState = $DB->Query("select * from b_bp_workflow_state where STATE_TITLE = 'Отправлено в отдел кадров' and DOCUMENT_ID IN ( " . implode(',', array_keys($arBPRecords)) . ")", false);
        while ($arItem = $obBizprocState->fetch()) {
            $arStatesID[$arItem['DOCUMENT_ID']] = $arBPRecords[$arItem['DOCUMENT_ID']];
        }

        $arBPRecords = array_intersect_key($arBPRecords, $arStatesID);

        /* Формируем окончательный массив катгорий пользователей */

        foreach ($arBPRecords as $arRecord) {
            $iUserID = $arRecord['CREATED_BY'];
            $obNowDate = new \DateTime();
            $obEndDate = new \DateTime($arRecord['PROPERTY_DATE_TO_VALUE']);
            $iDiffDays = (int)$obNowDate->diff($obEndDate)->format('%R%a');

            if ($obEndDate->format('d.m.Y') == '01.01.1970') {
                $arAlertUsers['INDEFINITE'][] = $arRecord;
                unset($arAlertUsers['ALL'][$iUserID]);
            } elseif ($iDiffDays <= 14 && $iDiffDays >= 12) {
                $arAlertUsers['2_WEEKS'][] = $arRecord;
                unset($arAlertUsers['ALL'][$iUserID]);
            }
        }

        /* Отправляем оповещения тем, у кого через 2 недели заканчивается другая работа */

        foreach ($arAlertUsers['2_WEEKS'] as $arUser) {
            $sMessage = "Если Вы планируете продолжить заниматься иной оплачиваемой деятельностью ({$arUser['WORK_TYPE']}) в организации: {$arUser['WORK_PLACE']}, 
            не забудьте подать новое уведомление представителю нанимателя через доступный на корпоративном портале сервис 
            «Уведомление об иной оплачиваемой работе», расположенный в разделе «Моя страница» - «Бизнес-процессы» - «Профилактика коррупции».";

            if ($sEmailUser = $arUsersGov[$arUser['CREATED_BY']]['EMAIL']) {
                CEvent::Send("ALERT_LPA", 's1', ['TEXT' => $sMessage, 'THEME' => 'Уведомление об иной оплачиваемой работе', 'EMAIL_TO' => $sEmailUser]);
            }
        }

        /* Отправляем оповещения остальным госслужащим */

        foreach ($arAlertUsers['ALL'] as $arUser) {
            $sMessage = "Если Вы планируете заниматься иной оплачиваемой деятельностью, не забудьте своевременно уведомить представителя 
            нанимателя через доступный на корпоративном портале сервис «Уведомление об иной оплачиваемой работе», расположенный 
            в разделе «Моя страница» - «Бизнес-процессы» - «Профилактика коррупции».";

            if ($sEmailUser = $arUser['EMAIL']) {
                CEvent::Send("ALERT_LPA", 's1', ['TEXT' => $sMessage, 'THEME' => 'Уведомление об иной оплачиваемой работе', 'EMAIL_TO' => $sEmailUser]);
            }
        }

        return "\Citto\Integration\BusinessProcesses::AgentAlertOtherWork();";
    }
}
