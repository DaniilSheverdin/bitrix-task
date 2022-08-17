<?php

namespace Lnpa;

use Bitrix\Main\Loader, CUserTypeEntity, CUserFieldEnum, CIBlockElement, COption, CIBlockProperty, CIBlockPropertyEnum, CIntranetUtils, CFields, CCards, CFavourite, CSign;

Loader::includeModule('iblock');

class CMain
{
    private static $arStructure = null;
    private static $arRuleStructure = null;
    private static $sRole = null;
    public static $arCard = null;

    public static function getRequest()
    {
        $arRequest = $_REQUEST;

        foreach (['DRAFT', 'MODERATION', 'PUBLISH', 'FIND'] as $sAction) {
            if (isset($arRequest[$sAction])) {
                $arRequest['action'] = $sAction;
                break;
            }
        }

        return $arRequest;
    }

    public static function getRole()
    {
        if (empty(self::$sRole)) {
            global $USER;
            $sRole = 'USER';
            $arAdmins = unserialize(COption::GetOptionString('lnpa.module', 'ADMINS'));
            $arOperators = unserialize(COption::GetOptionString('lnpa.module', 'OPERATORS'));
            $arClerks = unserialize(COption::GetOptionString('lnpa.module', 'CLERKS'));
            if (in_array($USER->getID(), $arAdmins)) {
                $sRole = 'ADMIN';
            } else if (in_array($USER->getID(), $arOperators)) {
                $sRole = 'OPERATOR';
            } else if (in_array($USER->getID(), $arClerks)) {
                $sRole = 'CLERK';
            }

            self::$sRole = $sRole;
        }

        return self::$sRole;
    }

    public static function getAction()
    {
        $arRequest = self::getRequest();
        $sAction = mb_strtoupper($arRequest['action']);
        $arFormActions = ['DRAFT', 'MODERATION', 'PUBLISH', 'FIND'];

        if (!check_bitrix_sessid() && in_array($sAction, $arFormActions)) {
            $sAction = null;
        }

        return $sAction;
    }

    public static function actionCard($sAction)
    {
        global $USER;

        $arResult = [];
        $sRole = self::getRole();
        $arRequest = self::getRequest();
        $arAccessActions = [
            'OPERATOR' => ['DRAFT', 'MODERATION'],
            'ADMIN' => ['DRAFT', 'MODERATION', 'PUBLISH'],
        ];

        if ($sAction == 'FIND') {
            $iNav = ($arRequest['FIND'][0] > 0) ? $arRequest['FIND'][0] : 1;
            $arResult = \Lnpa\CCards::getCards(['PUBLISH'], 10, $iNav);
        } else if ($sAction == 'ADD_FAVOURITE') {
            \Lnpa\CFavourite::add($USER->getID(), $arRequest['id']);
        } else if ($sAction == 'DEL_FAVOURITE') {
            \Lnpa\CFavourite::delete($USER->getID(), $arRequest['id']);
        } else if ($sAction == 'DEL_DOC') {
            $arResult = \LNPA\CCards::delete($arRequest['id'], $sRole);
        } else if ($sRole != 'USER' && in_array($sAction, $arAccessActions[$sRole])) {
            $arDataFields = \LNPA\CFields::getDataFields($sAction);
            if (empty($arDataFields['ERRORS']) && $arRequest['mfi_mode'] != 'upload') {
                if ($arRequest['page'] == 'detail_edit') {
                    $arDataFields = \LNPA\CCards::update($arDataFields, $sAction);
                } else {
                    $arDataFields = \LNPA\CCards::add($arDataFields, $sAction);
                }
            }

            $arResult = $arDataFields;
        }

        return $arResult;
    }

    private static function getNameWithDepth($arStructureTree = [], $iID = null)
    {
        $sMargin = '';
        $sStructure = $sMargin . $arStructureTree['DATA'][$iID]['NAME'];

        return mb_substr($sStructure, 0, 150);
    }

    public static function getStructure($sAction = '')
    {
        if (empty(self::$arStructure)) {
            $arStructure = [];
            $arStructureTree = CIntranetUtils::GetStructure();

            foreach ($arStructureTree['TREE'] as $arIDs) {
                foreach ($arIDs as $iID) {
                    $fRescursiveDepth = function ($iID) use ($arStructureTree, &$arStructure, &$fRescursiveDepth) {
                        if (!isset($arStructure[$iID])) {
                            $arStructure[$iID] = self::getNameWithDepth($arStructureTree, $iID);

                            if (isset($arStructureTree['TREE'][$iID])) {
                                foreach ($arStructureTree['TREE'][$iID] as $iID) {
                                    $fRescursiveDepth($iID);
                                }
                            }
                        }
                    };

                    $fRescursiveDepth($iID);
                }
            }

            self::$arStructure = $arStructure;
        }

        return self::$arStructure;
    }

    public static function getCurrentStructure($sAction = '')
    {
        $arSelect = [
            "ID",
            "PROPERTY_UF_STRUCTURE",
        ];
        $arFilter = [
            'IBLOCK_ID' => IntVal(\LNPA\CBeforeInit::getIBlockLnpa()),
            'ACTIVE' => 'Y',
        ];
        $obCards = CIBlockElement::GetList([], $arFilter, false, [], $arSelect);
        $arCurrentStructureIDs = [];
        while ($arCard = $obCards->GetNext()) {
            foreach ($arCard["PROPERTY_UF_STRUCTURE_VALUE"] as $iStrucrureID) {
                if (!in_array($iStrucrureID, $arCurrentStructureIDs)) {
                    array_push($arCurrentStructureIDs, $iStrucrureID);
                }
            }
        }

        return $arCurrentStructureIDs;
    }

    public static function getRuleStructure($iUserID)
    {
        $arStructureIDs = [];

        if (!isset(self::$arRuleStructure)) {
            $arSuperAdmins = unserialize(COption::GetOptionString('lnpa.module', 'SUPER_ADMINS'));
            if (in_array($iUserID, $arSuperAdmins)) {
                $arStructureIDs = CIntranetUtils::GetIBlockSectionChildren([]);
            } elseif (!empty($arMyStructure = CIntranetUtils::GetUserDepartments($iUserID))) {
                $arStructureIDs = CIntranetUtils::GetIBlockSectionChildren($arMyStructure);
            }

            self::$arRuleStructure[$iUserID] = $arStructureIDs;
        }

        return self::$arRuleStructure[$iUserID];
    }

    public static function getURI()
    {
        $sURI = $_SERVER["DOCUMENT_URI"] . '?';

        foreach ($_GET as $sKey => $sValue) {
            $sURI .= "&$sKey=$sValue";
        }

        return $sURI;
    }

    public static function getNavManu()
    {
        $sRole = self::getRole();
        $arMenu = [];

        if ($sRole != 'USER') {
            if ($sRole == 'ADMIN') {
                $arMenu['PUBLISH'] = [
                    'URL' => 'page=moderation_view',
                    'NAME' => 'Опубликовать'
                ];

            }
            $arMenu['DOWNLOAD'] = [
                'URL' => 'page=last_doc',
                'NAME' => 'Загрузить'
            ];
        }
        $arMenu['FIND'] = [
            'URL' => '',
            'NAME' => 'Найти'
        ];

        $sPage = (empty($_GET['page'])) ? 'find' : $_GET['page'];

        if ($sPage == 'last_doc' && !empty($arMenu['DOWNLOAD'])) {
            $arMenu['DOWNLOAD']['ACTIVE'] = 'Y';
        } else if ($sPage == 'moderation_view' && !empty($arMenu['DOWNLOAD'])) {
            $arMenu['PUBLISH']['ACTIVE'] = 'Y';
        } else {
            $arMenu['FIND']['ACTIVE'] = 'Y';
        }

        return $arMenu;
    }

    public static function getUsersAlert($sAction = 'moderation')
    {
        $arDepartments = CIntranetUtils::GetDeparmentsTree(0, false);
        $arAdmins = unserialize(COption::GetOptionString('lnpa.module', 'ADMINS'));
        $arSuperAdmins = unserialize(COption::GetOptionString('lnpa.module', 'SUPER_ADMINS'));

        $arFields = \LNPA\CFields::getFields();
        $arStatusesIDs = [];
        foreach ($arFields['CODE']['UF_STATUS']['LIST'] as $arItem) {
            if ($arItem['XML_ID'] == 'MODERATION') {
                array_push($arStatusesIDs, $arItem['ID']);
            }
        }

        if ($sAction == 'moderation') {
            $arFilter = ['IBLOCK_ID' => COption::GetOptionInt('lnpa.module', 'IBLOCK_LNPA'), 'PROPERTY_UF_STATUS' => $arStatusesIDs];
        } else if ($sAction == 'relevance') {
            $arFilter = ['IBLOCK_ID' => COption::GetOptionInt('lnpa.module', 'IBLOCK_LNPA'), '<PROPERTY_UF_DATE_TRACKER' => date('Y-m-d')];
        } else if ($sAction == 'alert_sign') {
            $arFilter = array(
                "IBLOCK_ID" => COption::GetOptionInt('lnpa.module', 'IBLOCK_LNPA'),
                "ACTIVE_DATE" => "Y",
                "ACTIVE" => "Y",
                "PROPERTY_UF_ALERT_SIGN_VALUE" => "Y",
                "PROPERTY_UF_DATE_ALERT_SIGN" => (new \DateTime())->format('Y-m-d'),
            );
        }

        $obModeration = CIBlockElement::GetList([], $arFilter, false, [], ['ID', 'NAME', 'CREATED_BY', 'MODIFIED_BY', 'PROPERTY_UF_DATE_ALERT_SIGN', 'PROPERTY_UF_STRUCTURE', 'PROPERTY_UF_USER_ALERT_SIGN', 'NAME']);
        $arModerationItems = [];
        while ($arItem = $obModeration->GetNext()) {
            if ($sAction != 'alert_sign') {

                foreach ($arItem['PROPERTY_UF_STRUCTURE_VALUE'] as $iDepID) {
                    $arModerationItems[$iDepID]['USERS_ALERT'] = [];
                    $arModerationItems[$iDepID]['ITEMS'][] = $arItem;
                }

                if ($sAction == 'relevance') {
                    CIBlockElement::SetPropertyValuesEx($arItem['ID'], COption::GetOptionInt('lnpa.module', 'IBLOCK_LNPA'), ['UF_DATE_TRACKER' => null]);
                }

            } else {
                $iUserID = $arItem['PROPERTY_UF_USER_ALERT_SIGN_VALUE'];
                $arUserSign = \Lnpa\CSign::getUserSignElement($arItem['ID']);
                $sMessage = "Вы запустили сбор подписей на лист ознакомления по документу {$arItem['NAME']}\n";
                $sMessage .= "На текущий момент собрано {$arUserSign['COUNT_SIGNED']} из {$arUserSign['COUNT']}.\n";
                $sMessage .= "Пофамильная статистика подписей доступна по ссылке: https://corp.tularegion.local/lpa/?page=statistics&id={$arItem['ID']}.";

                if ($iUserID) {
                    $arModerationItems[$iUserID][] = [
                        'USER_ID' => $iUserID,
                        'MESSAGE' => $sMessage
                    ];
                }
            }
        }

        if ($sAction == 'alert_sign') {
            return $arModerationItems;
        }

        if (!empty($arAdmins)) {
            $obUsers = \CUser::GetList(($by = "NAME"), ($order = "desc"), ['ID' => implode('|', $arAdmins)], array("SELECT" => array("UF_DEPARTMENT")));
            while ($arUser = $obUsers->GetNext()) {
                if (!in_array($arUser['ID'], $arSuperAdmins)) {
                    foreach ($arUser['UF_DEPARTMENT'] as $iDepID) {
                        $arRecDeps = function ($arIDs) use (&$arRecDeps, &$arDepartments, &$arUser, &$arModerationItems) {
                            foreach ($arIDs as $iDepID) {
                                if (!empty($arModerationItems[$iDepID])) {
                                    $arModerationItems[$iDepID]['USERS_ALERT'][] = $arUser['ID'];
                                }
                                if (isset($arDepartments[$iDepID])) {
                                    $arRecDeps($arDepartments[$iDepID]);
                                }
                            }
                        };
                        $arRecDeps([$iDepID]);
                    }
                }
            }
        }

        $arUsersAlert = [];
        foreach ($arModerationItems as $iItemID => $arItem) {
            if (empty($arItem['USERS_ALERT'])) {
                foreach ($arSuperAdmins as $iUser) {
                    $arUsersAlert[$iUser]['ITEMS'][] = $arItem;
                }
            } else {
                foreach ($arItem['USERS_ALERT'] as $iUser) {
                    $arUsersAlert[$iUser]['ITEMS'][] = $arItem;
                }
            }
        }

        foreach ($arUsersAlert as $iUserID => $arInfo) {
            $iCount = 0;
            $arRecordsIDs = [];
            foreach ($arInfo["ITEMS"] as $arRecords) {
                foreach ($arRecords["ITEMS"] as $arRecord) {
                    $arRecordsIDs[$arRecord['ID']] = $arRecord['NAME'];
                    $iCount++;
                }
            }

            if ($sAction == 'relevance') {
                $arMessage = [];
                foreach ($arRecordsIDs as $iRecordID => $sRecordName) {
                    $arMessage[] = "У документа <a href='https://corp.tularegion.local/lpa/?page=detail_edit&id=$iRecordID'>$sRecordName</a> истёк срок актуальности.";
                }
                $arMessage[] = "\nПерейдите по ссылке, чтобы:\n• загрузить новую версию документа;\n• обновить таймер актуализации.\n\nЕсли этого не требуется, просто проигнорируйте сообщение.\n";
                $sMessage = implode(PHP_EOL, $arMessage);
            } else if ($sAction == 'moderation') {
                if ($iCount > 1) {
                    $sMessage = "Для публикации доступно <a href='https://corp.tularegion.local/lpa/?page=moderation_view'>$iCount</a> новых ЛПА";
                } else {
                    $sNameDoc = $arUsersAlert[$iUserID]['ITEMS'][0]['ITEMS'][0]['NAME'];
                    $sMessage = "Для публикации доступен 1 новый НПА: <a href='https://corp.tularegion.local/lpa/?page=moderation_view'>$sNameDoc</a>";
                }
            }

            $arUsersAlert[$iUserID]['MESSAGE'] = $sMessage;
        }

        return $arUsersAlert;
    }
}
