<?php
class serg_bi extends \CModule
{


    public $MODULE_ID = 'serg.bi';
    public $MODULE_VERSION = '1.0.0';
    public $MODULE_VERSION_DATE = '2019-07-15 11:00:00';
    public $MODULE_NAME = 'REST BI';
    public $MODULE_DESCRIPTION = 'REST API for Contour BI';

    public function __construct()
    {
        $this->PARTNER_NAME = 'serg';
        $this->PARTNER_URI = 'https://latmi.ru';
    }

    public function doInstall()
    {

        \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
        \Bitrix\Main\EventManager::getInstance()->registerEventHandler(
            'rest',
            'OnRestServiceBuildDescription',
            'serg.bi',
            '\Serg\Bi\Rest\Service',
            'getDescription'
        );
    }
    public function doUninstall()
    {

        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
        \Bitrix\Main\EventManager::getInstance()->unRegisterEventHandler(
            'rest',
            'OnRestServiceBuildDescription',
            'serg.bi',
            '\Serg\Bi\Rest\Service',
            'getDescription'
        );
    }

    public function InstallDB()
    {
    }
}