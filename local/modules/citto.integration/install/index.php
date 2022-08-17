<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

Loc::loadMessages(__FILE__);

if (class_exists('citto_integration')) {
    return;
}

class citto_integration extends CModule
{
    /** @var string */
    public $MODULE_ID;

    /** @var string */
    public $MODULE_VERSION;

    /** @var string */
    public $MODULE_VERSION_DATE;

    /** @var string */
    public $MODULE_NAME;

    /** @var string */
    public $MODULE_DESCRIPTION;

    /** @var string */
    public $MODULE_GROUP_RIGHTS;

    /** @var string */
    public $PARTNER_NAME;

    /** @var string */
    public $PARTNER_URI;

    public function __construct()
    {
        $this->MODULE_ID           = 'citto.integration';
        $this->MODULE_VERSION      = '0.0.1';
        $this->MODULE_VERSION_DATE = '2019-03-05 13:00:00';
        $this->MODULE_NAME         = Loc::getMessage('CITTO_INTEGRATION__MODULE_NAME');
        $this->MODULE_DESCRIPTION  = Loc::getMessage('CITTO_INTEGRATION__MODULE_DESCRIPTION');
        $this->MODULE_GROUP_RIGHTS = 'Y';
        $this->PARTNER_NAME        = "CITTO";
        $this->PARTNER_URI         = "http://www.cit71.ru";
        $this->eventHandlers       = [
            [
                'main',
                'OnPageStart',
                '\Citto\Integration\Module',
                'onPageStart',
            ],
            [
                'rest',
                'OnRestServiceBuildDescription',
                '\Citto\Integration\Delo\RestService',
                'getDescription'
            ],
            [
                'rest',
                'OnRestServiceBuildDescription',
                '\Citto\Integration\Itilium\RestService',
                'getDescription'
            ],
        ];
    }

    public function doInstall()
    {
        ModuleManager::registerModule($this->MODULE_ID);
        $this->InstallFiles();
        $this->Agents('add');
    }

    public function doUninstall()
    {
        $this->UnInstallFiles();
        ModuleManager::unregisterModule($this->MODULE_ID);
        $this->Agents('del');
    }

    /**
     * Регистрируем обработчики событий
     *
     * @return bool
     */
    public function InstallEvents()
    {
        $eventManager = EventManager::getInstance();

        foreach ($this->eventHandlers as $handler) {
            $eventManager->registerEventHandler(
                $handler[0],
                $handler[1],
                $this->MODULE_ID,
                $handler[2],
                $handler[3]
            );
        }

        return true;
    }

    /**
     * Удаляем обработчики событий
     * @return bool
     */
    public function UnInstallEvents()
    {
        $eventManager = EventManager::getInstance();

        foreach ($this->eventHandlers as $handler) {
            $eventManager->unRegisterEventHandler(
                $handler[0],
                $handler[1],
                $this->MODULE_ID,
                $handler[2],
                $handler[3]
            );
        }

        return true;
    }

    /**
     * Установка файлов
     * @return bool
     */
    public function InstallFiles()
    {
        CopyDirFiles(__DIR__ . "/admin", $_SERVER['DOCUMENT_ROOT'] . "/bitrix/admin");
    }

    /**
     * Удаление файлов
     * @return bool
     */
    public function UnInstallFiles()
    {
        DeleteDirFiles(__DIR__ . "/admin", $_SERVER['DOCUMENT_ROOT'] . "/bitrix/admin");
        return true;
    }

    public function Agents($action)
    {
        if ($action == 'add') {
            CAgent::AddAgent(
                "\Citto\Integration\CBitrixSCUD::AgentSyncParsec();",
                $this->MODULE_ID,
                "N",
                86400,
                "",
                "Y",
                "",
                30
            );
        } elseif ($action == 'del') {
            CAgent::RemoveAgent(
                "\Citto\Integration\CBitrixSCUD::AgentSyncParsec;",
                $this->MODULE_ID
            );
        }
    }
}
