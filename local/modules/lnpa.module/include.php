<?php

use Bitrix\Main\Loader, Bitrix\Highloadblock as HL;
use Bitrix\Main\UserTable;
use CEvent;

$arRequiredModules = include(__DIR__ . '/install/require.php');

foreach ($arRequiredModules as $sModule) {
    \Bitrix\Main\Loader::includeModule($sModule);
}
CModule::AddAutoloadClasses('lnpa.module', array(
    '\Lnpa\CBeforeInit' => '/lib/CBeforeInit.php',
    '\Lnpa\CMain' => '/lib/CMain.php',
    '\Lnpa\CFields' => '/lib/CFields.php',
    '\Lnpa\CCards' => '/lib/CCards.php',
    '\Lnpa\CFavourite' => '/lib/CFavourite.php',
    '\Lnpa\CVersions' => '/lib/CVersions.php',
    '\Lnpa\CSign' => '/lib/CSign.php',
));

class LnpaEvents
{
    function AgentAlertUsers()
    {
        set_time_limit(0);
        $arUsersAlert = Lnpa\CMain::getUsersAlert('moderation');
        foreach ($arUsersAlert as $iUserID => $arUser) {
            CIMMessenger::Add(array(
                'TITLE' => 'Модерация ЛПА',
                'MESSAGE' => $arUser['MESSAGE'],
                'TO_USER_ID' => $iUserID,
                'FROM_USER_ID' => 2661,
                'MESSAGE_TYPE' => 'S', # P - private chat, G - group chat, S - notification
                'NOTIFY_MODULE' => 'intranet',
                'NOTIFY_TYPE' => 2,  # 1 - confirm, 2 - notify single from, 4 - notify single
            ));
        }

        return "LnpaEvents::AgentAlertUsers();";
    }

    function AgentAlertRelevance()
    {
        set_time_limit(0);
        $arUsersAlert = Lnpa\CMain::getUsersAlert('relevance');
        foreach ($arUsersAlert as $iUserID => $arUser) {
            CIMMessenger::Add(array(
                'TITLE' => 'Проверка актуальности ЛПА',
                'MESSAGE' => $arUser['MESSAGE'],
                'TO_USER_ID' => $iUserID,
                'FROM_USER_ID' => 2661,
                'MESSAGE_TYPE' => 'S', # P - private chat, G - group chat, S - notification
                'NOTIFY_MODULE' => 'intranet',
                'NOTIFY_TYPE' => 2,  # 1 - confirm, 2 - notify single from, 4 - notify single
            ));
        }

        return "LnpaEvents::AgentAlertRelevance();";
    }

    function AgentAlertUsersSign()
    {
        set_time_limit(0);
        $arUsersAlert = Lnpa\CMain::getUsersAlert('alert_sign');

        $arUsersMail = [];
        $obUsers = UserTable::getList([
            'select' => ['ID', 'EMAIL'],
            'filter' => ['ID' => array_keys($arUsersAlert)],
        ]);

        while ($arItem = $obUsers->fetch()) {
            $arUsersMail[$arItem['ID']] = $arItem['EMAIL'];
        }

        foreach ($arUsersAlert as $iUserID => $arUser) {
            foreach ($arUser as $arInfo) {
                CIMMessenger::Add(array(
                    'TITLE' => 'Напоминание о запущенном сборе подписей',
                    'MESSAGE' => $arInfo['MESSAGE'],
                    'TO_USER_ID' => $iUserID,
                    'FROM_USER_ID' => 2661,
                    'MESSAGE_TYPE' => 'S',
                    'NOTIFY_MODULE' => 'intranet',
                    'NOTIFY_TYPE' => 2,
                ));

                if ($sEmailUser = $arUsersMail[$iUserID]) {
                    CEvent::Send("ALERT_LPA", 's1', ['TEXT' => $arInfo['MESSAGE'], 'THEME' => 'Напоминание о запущенном сборе подписей', 'EMAIL_TO' => $sEmailUser]);
                }
            }
        }

        return "LnpaEvents::AgentAlertUsersSign();";
    }

    public static function OnBeforeUserTypeUpdateHandler()
    {
//        $iBlockLnpa = Lnpa\CBeforeInit::getIBlockLnpa();
//
//        if ($_REQUEST['ENTITY_ID'] == "HLBLOCK_$iHlBlockLnpaID" && $_REQUEST['SETTINGS']['DISPLAY'] == 'LIST') {
//            $arOldList = Lnpa\CMain::getValuesEnums(array_keys($_REQUEST['LIST']));
//            $arNewList = $_REQUEST['LIST'];
//            $arDiff = [];
//            foreach ($arNewList as $iID => $arItem) {
//                if ($arItem['XML_ID'] != $arOldList[$iID]['XML_ID'] || $arItem['DEL'] == 'Y') {
//                    $arDiff[$iID] = $arItem;
//                }
//            }
//
//            if (!empty($arDiff)) {
//                $arParams = [
//                    'filter' => [
//                        'UF_MAIN' => array_keys($arDiff)
//                    ]
//                ];
//
//                $arElements = Lnpa\CMain::getListHL($iHlBlockLnpaID, $arParams)->Fetch();
//                foreach ($arElements as $arField) {
//                    $iKey = $arField['UF_MAIN'];
//                    $_REQUEST['LIST'][$iKey]['XML_ID'] = $arOldList[$iKey]['XML_ID'];
//                }
//            }
//        }
    }
}
