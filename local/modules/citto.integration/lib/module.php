<?php

namespace Citto\Integration;

use Exception;
use Bitrix\Main\EventManager;

/**
 * Основной класс модуля
 *
 * @category This
 * @author CIT <cit@tularegion.ru>
 *
 */
class Module
{
    public const SERVER_CRIPTOPRO = "172.21.254.50";

    public static $strModuleId = 'citto.integration';

    public static function decodeSID($value)
    {
        # revision - 8bit unsigned int (C1)
        # count - 8bit unsigned int (C1)
        # 2 null bytes
        # ID - 32bit unsigned long, big-endian order
        $sid = @unpack('C1rev/C1count/x2/N1id', $value);
        $subAuthorities = [];

        if (!isset($sid['id']) || !isset($sid['rev'])) {
            throw new \UnexpectedValueException(
                'The revision level or identifier authority was not found when decoding the SID.'
            );
        }

        $revisionLevel = $sid['rev'];
        $identifierAuthority = $sid['id'];
        $subs = isset($sid['count']) ? $sid['count'] : 0;

        // The sub-authorities depend on the count, so only get as many as the count, regardless of data beyond it
        for ($i = 0; $i < $subs; $i++) {
            # Each sub-auth is a 32bit unsigned long, little-endian order
            $subAuthorities[] = unpack('V1sub', hex2bin(mb_substr(bin2hex($value), 16 + ($i * 8), 8)))['sub'];
        }

        # Tack on the 'S-' and glue it all together...
        return 'S-' . $revisionLevel . '-' . $identifierAuthority . implode(
            preg_filter('/^/', '-', $subAuthorities)
        );
    }

    /**
     * Обработчик начала отображения страницы
     *
     * @return void
     */
    public static function onPageStart()
    {
        self::setupEventHandlers();
        self::addStyles();
    }

    public static function addStyles()
    {
        global $APPLICATION;
        $APPLICATION->SetAdditionalCSS('/local/fonts/ptastrasans.css');
        $APPLICATION->SetAdditionalCSS('/local/modules/citto.integration/css/add.css');
        $APPLICATION->SetAdditionalCSS('/local/css/custom.css');
        if (!\CSite::InDir('/bitrix/')) {
            $APPLICATION->AddHeadScript('/local/js/custom.js');
        }
    }

    /**
     * Определяет вычисляемые константы модуля
     *
     * @return void
     */
    protected static function defineConstants()
    {
    }

    /**
     * Добавляет обработчики событий
     *
     * @return void
     */

    public static function setupEventHandlers()
    {
        $objEventManager = EventManager::getInstance();

        $objEventManager->addEventHandler(
            "ldap",
            "OnLdapUserFields",
            array('\Citto\Integration\Module', 'OnLdapUserFieldsHandler')
        );
        $objEventManager->addEventHandler(
            "main",
            "OnAfterUserLogin",
            array('\Citto\Integration\Module', 'OnAfterUserLoginHandler')
        );
    }

    public static function OnBeforePrologHandler()
    {
        return;
    }

    /**
     * @param $arFields
     * @throws \Bitrix\Main\LoaderException
     */
    public function OnAfterUserLoginHandler(&$arFields)
    {
        global $USER, $userFields;
        global $APPLICATION;

        \Bitrix\Main\Loader::includeModule("bizproc");
        \Bitrix\Main\Loader::includeModule("im");

        $strCurdir = $APPLICATION->GetCurDir();
        $arGroups = $USER->GetUserGroupArray();
        $arFilter = array();

        if ($USER->GetID() > 0 && ($arUData = $userFields($USER->GetID()))) {
            //self::_firstLoginNotificationUser($arUData);
        }
        /*
        if (
            !in_array(1, $USER->GetUserGroupArray()) &&
            !substr_count($strCurdir, '/planner/') &&
            !substr_count($strCurdir, '/mobile/') &&
            !substr_count($strCurdir, '/disk/') &&
            !substr_count($strCurdir, '/desktop_app/') &&
            !substr_count($strCurdir, '/services/') &&
            !substr_count($strCurdir, '/zvonok_gubernator/') &&
            !substr_count($strCurdir, '/podpis-fayla/') &&
            !substr_count($strCurdir, '/mobile/') &&
            !substr_count($strCurdir, '/mobileapp/') &&
            !substr_count($strCurdir, '/control-orders/') &&
            !substr_count($strCurdir, '/rest/') &&
            !substr_count($strCurdir, '/rpa/')
        ) {
            $objRsGroups = \CGroup::GetList(($by = "c_sort"), ($order = "desc"), $arFilter); // выбираем группы
            while ($arGroup = $objRsGroups->GetNext()) {
                $strNAME_DEP = explode(': Сотрудники', $arGroup['NAME']);
                if (count($strNAME_DEP) > 1) {
                    if (in_array($arGroup['ID'], $arGroups)) {
                        $objRsSites = \CSite::GetByID(explode('_', $arGroup['STRING_ID'])[1]);
                        $arSite = $objRsSites->Fetch();
                        localredirect($arSite['DIR']);
                    }
                }
            }
        }
        */
    }

    /**
     * First login note
     * @return void
     */
    private static function _firstLoginNotificationUser($arUData)
    {
        global $USER, $userFields;

        $listDepartSections = GetParentDepartmentstucture($USER->GetID());

        if (isset($listDepartSections)
            && ($listDepartSections[1] == '1710' || ($listDepartSections[1] == '1727' && count($listDepartSections) <= 2)) // только ОИВ и аппарт ПТО
        ) {

            if (empty($arUData['UF_FIRST_LOGIN'])) {


                    \Bitrix\Main\Mail\Event::send(
                        [
                            'EVENT_NAME' => 'BP_LIST_ADAPTATION_START',
                            'LID' => 's1',
                            'C_FIELDS' => [
                                'EMAIL_TO' => $arUData['EMAIL']
                            ]
                        ]
                    );

                    \CIMMessenger::Add(
                        [
                            "MESSAGE_TYPE" => "S",
                            "TO_USER_ID" => $USER->GetID(),
                            "FROM_USER_ID" => 1,
                            "MESSAGE" => "Уважаемый коллега, приветствуем Вас на Корпоративном портале. Напоминаем, что прежде, чем начать формирование Листа адаптации необходимо авторизоваться либо пройти регистрацию на <a href=\"https://university.tularegion.ru/\" target=\"_blank\">Корпоративном университете правительства ТО</a>! Если авторизация прошла успешно, переходите к следующему шагу - Формирование листа адаптации.",
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

                $objUser = new \CUser();
                $objUser->Update(
                    $USER->GetID(),
                    [
                        'UF_FIRST_LOGIN' => '1'
                    ]
                );

                return true;
            }

        }

        return false;
    }

    public function OnLdapUserFieldsHandler(&$arFields)
    {
        foreach ($arFields as $k => $v) {
            foreach ($v as $k2 => $v2) {
                if ($k2 == 'UF_SID') {
                    $arFields[$k][$k2] = self::decodeSID($v2);
                }
            }
        }
    }

    public function onModuleIncludeHandler()
    {
    }

    /**
     *  Расшифровка файла подписи
     * @param string $filename путь к файлу с подписью
     * @return Array поля подписи
     * @throws \Exception если:нет файла;ошибка на сервере
     */
    public static function decodeSigFile($filename)
    {
        $data = null;
        if (!file_exists($filename)) {
            throw new Exception("File not found");
        }

        return self::decodeSig(file_get_contents($filename));
    }

    /**
     *  Расшифровка подписи
     * @param string $data данные для расшифровки
     * @return Array поля подписи
     * @throws \Exception если ошибка на сервере
     */
    public static function decodeSig($data)
    {
        $signers = [];

        $ch = curl_init("http://" . self::SERVER_CRIPTOPRO . "/uverify.php");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, ['sign' => $data]);
        $resp = curl_exec($ch);
        curl_close($ch);

        if (empty($resp)) {
            throw new Exception("Ошибка сервера проверки подписи");
        }

        $resp = json_decode($resp, true);
        if (empty($resp)) {
            throw new Exception("Ошибка сервера проверки подписи");
        }

        if (empty($resp['data']) || empty($resp['data']['signers']) || !is_array($resp['data']['signers'])) {
            throw new Exception("Не удалось проверить подпись");
        }

        return $resp['data']['signers'];
    }
}
