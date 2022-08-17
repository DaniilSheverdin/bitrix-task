<?php
class citto_filesigner extends \CModule
{


    public $MODULE_ID = 'citto.filesigner';
    public $MODULE_VERSION = '1.0.0';
    public $MODULE_VERSION_DATE = '2020-01-08 11:00:00';
    public $MODULE_NAME = 'citto.filesigner';
    public $MODULE_DESCRIPTION = '';

    public function __construct()
    {
        $this->PARTNER_NAME = 'citto';
        $this->PARTNER_URI = '';
    }

    public function doInstall()
    {

        \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
    }
    public function doUninstall()
    {
        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
    }

    public function InstallDB()
    {
    }
}