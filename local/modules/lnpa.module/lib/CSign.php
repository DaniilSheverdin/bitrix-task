<?php

namespace Lnpa;

use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;
use CEvent;
use CFields;
use CIBlockElement;
use CIMMessenger;
use CIntranetUtils;
use CVersions;

Loader::includeModule('iblock');
Loader::includeModule('citto.filesigner');

class CSign {
    public static function isCollectionSignCard($iID)
    {
        $bIsCollectionSignCard = false;
        $arSelect = ["ID", "PROPERTY_UF_SIGN_USERS"];
        $obCard = CIBlockElement::GetList([], ["ID" => $iID], false, [], $arSelect);
        while($obItem = $obCard->GetNext())
        {
            if ($obItem['PROPERTY_UF_SIGN_USERS_VALUE']) {
                $bIsCollectionSignCard = true;
            }
        }

        return $bIsCollectionSignCard;
    }

    public static function getUserSignElement($iCardID)
    {
        global $USER;

        $arUserSign = [
            'IS_USER_SIGN' => false,
            'FILE_SIGN_ID' => 0,
            'USERS' => [],
            'COUNT_SIGNED' => 0,
            'COUNT_NOT_SIGNED' => 0,
            'COUNT' => 0
        ];

        if ($USER->getID()) {
            $arSelect = ["ID", "PROPERTY_UF_SIGN_USERS", "PROPERTY_UF_SIGNED_USERS", "PROPERTY_UF_DOCUMENT_SIGN", "PROPERTY_UF_HTML_TEMPLATE_SIGN"];
            $obCard = CIBlockElement::GetList([], ["ID" => $iCardID], false, [], $arSelect);

            while($obItem = $obCard->GetNext())
            {
                $arUsersSign = $obItem['PROPERTY_UF_SIGN_USERS_VALUE'];
                $arUsersSigned = $obItem['PROPERTY_UF_SIGNED_USERS_VALUE'];
                $iFileID = $obItem['PROPERTY_UF_HTML_TEMPLATE_SIGN_VALUE'];


                $arFilter['ID'] = array_merge($arUsersSign, $arUsersSigned);

                $obUsers = UserTable::getList([
                    'select' => ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'UF_WORK_POSITION', 'UF_DEPARTMENT'],
                    'filter' => $arFilter,
                    'order' => ['LAST_NAME']
                ]);

                $arDepartmentsIDs = [];
                while ($arUser = $obUsers->fetch()) {
                    $iDepartmentID = $arUser['UF_DEPARTMENT'][0];
                    $sFio = "{$arUser['LAST_NAME']} {$arUser['NAME']} {$arUser['SECOND_NAME']}";
                    $arDepartmentsIDs[] = $iDepartmentID;

                    if (in_array($arUser['ID'], $arUsersSigned)) {
                        $arUserSign['COUNT_SIGNED']++;
                        $sStatus = 'SIGNED';
                    } else {
                        $arUserSign['COUNT_NOT_SIGNED']++;
                        $sStatus = 'NOT_SIGNED';
                    }

                    $arUserSign['USERS'][$arUser['ID']] = [
                        'FIO' => $sFio,
                        'STATUS' => $sStatus,
                        'DEPARTMENT_ID' => $iDepartmentID
                    ];

                    $arUserSign['COUNT']++;
                }

                $arUserSign['DATA_DEPARTMENTS'] = CIntranetUtils::GetDepartmentsData($arDepartmentsIDs);

                if (in_array($USER->getID(), $arUsersSign)
                    && !in_array($USER->getID(), $arUsersSigned)
                    && $iFileID
                ) {
                    $arUserSign['IS_USER_SIGN'] = true;
                    $arUserSign['FILE_SIGN_ID'] = $iFileID;
                }
            }
        }

        return $arUserSign;
    }

    public static function getSignUsers($arDepartmentsAndUsers = [])
    {
        $arDepartments = [];
        $arSignUsers = [];

        foreach ($arDepartmentsAndUsers as $sItem) {
            $sType = preg_replace('/[^a-zA-Z\s]/', '', $sItem);
            $iItem = preg_replace('/[^0-9]/', '', $sItem);

            if ($sType == 'U' && !in_array($iItem, $arSignUsers)) {
                array_push($arSignUsers, $iItem);
            } else if ($sType == 'DR') {
                $arTreeDepartments = array_merge(CIntranetUtils::GetDeparmentsTree($iItem, true), [$iItem]);
                $arDepartments = array_merge($arDepartments, $arTreeDepartments);
            } else if ($sType == 'D') {
                $arDepartments = array_merge($arDepartments, [$iItem]);
            }
        }

        if ($arDepartments || $arSignUsers) {
            $arFilter = [
                'ACTIVE' => 'Y',
            ];

            if ($arDepartments) {
                $arFilter['UF_DEPARTMENT'] = $arDepartments;
            }

            if ($arSignUsers) {
                $arFilter['ID'] = $arSignUsers;
                $arSignUsers = [];
            }

            $obUsers = UserTable::getList([
                'select' => ['ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'UF_WORK_POSITION'],
                'filter' => $arFilter,
                'order' => ['LAST_NAME']
            ]);

            while ($arUser = $obUsers->fetch()) {
                $sFio = "{$arUser['LAST_NAME']} {$arUser['NAME']} {$arUser['SECOND_NAME']}";
                $arSignUsers[$arUser['ID']] = $sFio;
            }
        }

        return $arSignUsers;
    }

    private static function getTemplate($arSignUsers = [], $sTitle ='')
    {
        $sBody = '';
        foreach ($arSignUsers as $iID => $sFIO) {
            $sBody .= "<tr><td>{$sFIO}</td><td>#DATE_$iID#</td></tr>";
        }

        $sTemplate = "<!DOCTYPE html>
        <html>
        <head>
        <title>Служебная записка</title>
        <meta charset='UTF-8'>
        </head>
        <body style='font-size:14px; width: 500px;'>

        <table border='1'>
        <tr>
        <td colspan='2'><b>{$sTitle}</b></td>
        </tr>
        <tr>
        <td><b>ФИО работника</b></td>
        <td><b>Дата ознакомления</b></td>
        </tr>
        {$sBody}
        </table>

        </body>
        </html>";

        return $sTemplate;
    }

    public static function createCollectionSign($arSignUsers = [], $arRequest = [], $arItem = [])
    {
        global $USER;

        $iID = $arItem['ID'];
        $sTitle = $arRequest['title'];

        if ($sTitle && $arSignUsers && $iID) {
            $sTemplate = self::getTemplate($arSignUsers, $sTitle);
            $obSignPDF = new \Citto\Filesigner\PDFile();
            $obSignPDF->setName('Лист ознакомления');
            $obSignPDF->insert($sTemplate);
            $obSignPDF->save();

            $arFile = \CFile::MakeFileArray($obSignPDF->getId());
            $arFile['DESCRIPTION'] = '';

            $sVariableAlert = ($arRequest['alert_check'] == 'on') ? 'Y' : 'N';

            $arUpdate = [
                'UF_DOCUMENT_SIGN' => $arFile,
                'UF_HTML_TEMPLATE_SIGN' => $obSignPDF->getId(),
                'UF_ALERT_SIGN' => \LNPA\CFields::getEnumIDByField('UF_ALERT_SIGN', $sVariableAlert),
                'UF_USER_ALERT_SIGN' => $USER->GetID(),
                'UF_DATE_ALERT_SIGN' => (new \DateTime($arRequest['alert_date']))->format('d.m.Y'),
                'UF_SIGN_USERS' => array_keys($arSignUsers),
                'UF_SIGNED_USERS' => [0]
            ];

            CIBlockElement::SetPropertyValuesEx($iID, 0, $arUpdate);

            self::alertUsers(array_keys($arSignUsers), $arItem);
        }
    }

    public function alertUsersByElement($arItem = []) {
        $iElemementID = $arItem['ID'];

        if ($iElemementID) {
            $arUsersAlert = [];
            $arUsers = self::getUserSignElement($iElemementID)['USERS'];
            foreach ($arUsers as $ID => $arUser) {
                if ($arUser['STATUS'] == 'NOT_SIGNED') {
                    $arUsersAlert[] = $ID;
                }
            }

            self::alertUsers($arUsersAlert, $arItem);
        }
    }

    public function alertUsers($arUsers = [], $arItem = []) {
        if ($arUsers) {
            $sURL = 'https://' . $_SERVER['SERVER_NAME'] . $_SERVER['DOCUMENT_URI'] . '?page=detail_view&id=' . $arItem['ID'];

            $arUsersMail = [];
            $obUsers = UserTable::getList([
                'select' => ['ID', 'EMAIL'],
                'filter' => ['ID' => $arUsers],
            ]);

            while ($arItem = $obUsers->fetch()) {
                $arUsersMail[$arItem['ID']] = $arItem['EMAIL'];
            }

            foreach ($arUsers as $iUserID) {
                $sMessage = "Добрый день!\nНа Корпоративном Портале опубликован новый ЛПА {$arItem['UF_NAME']}, обязательный для ознакомления в течение 3 рабочих дней.\nЧтобы подтвердить ознакомление, перейдите по ссылке ниже и подпишите лист ознакомления с помощью ЭЦП:\n";
                $sTheme = 'Проверка актуальности ЛПА';

                CIMMessenger::Add(array(
                    'TITLE' => $sTheme,
                    'MESSAGE' => $sMessage . $sURL,
                    'TO_USER_ID' => $iUserID,
                    'FROM_USER_ID' => 2661,
                    'MESSAGE_TYPE' => 'S',
                    'NOTIFY_MODULE' => 'intranet',
                    'NOTIFY_TYPE' => 2,
                ));

                if ($sEmailUser = $arUsersMail[$iUserID]) {
                    CEvent::Send("ALERT_LPA", 's1', ['TEXT' => $sMessage . "<a href='$sURL'>$sURL</a>", 'THEME' => $sTheme, 'EMAIL_TO' => $sEmailUser]);
                }
            }
        }
    }
}
