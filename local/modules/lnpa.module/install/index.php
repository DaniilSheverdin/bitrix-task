<?php
use Bitrix\Main\Application;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

IncludeModuleLangFile(__FILE__);

class lnpa_module extends CModule
{
    var $MODULE_ID = 'lnpa.module';
    protected $installPath = '';

    public $requiredModules = [];

    function __construct()
    {
        $arModuleVersion = array();
        $this->installPath = __DIR__;
        include(__DIR__ . '/version.php');
        $this->requiredModules = include(__DIR__.'/require.php');
        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
        {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
            $this->MODULE_NAME = $arModuleVersion['NAME'];
            $this->MODULE_DESCRIPTION = $arModuleVersion['DESCRIPTION'];
            $this->PARTNER_NAME = $arModuleVersion['PARTNER_NAME'];
        }
    }

    public function Agents($action)
    {
        if($action == 'add') {
            CAgent::AddAgent(
                "LnpaEvents::AgentAlertUsers();",
                $this->MODULE_ID,
                "N",
                86400,
                "07.04.2020 20:03:26",
                "Y",
                "07.04.2020 20:03:26",
                30);
        }
        elseif($action == 'del') {
            CAgent::RemoveAgent("LnpaEvents::AgentAlertUsers;", $this->MODULE_ID);
        }
    }

    public function DoInstall()
    {
        $this->checkDependencies();
        ModuleManager::registerModule($this->MODULE_ID);
        Loader::includeModule($this->MODULE_ID);
        RegisterModuleDependences('main', 'OnBeforeUserTypeUpdate', 'lnpa.module', 'LnpaEvents', 'OnBeforeUserTypeUpdateHandler');

        $this->installFiles();
        $this->Agents('add');
    }

    public function DoUninstall()
    {
        global $USER, $DB, $APPLICATION, $step, $module_id;
        $step = (int)$step;
        $module_id = $this->MODULE_ID;
        UnRegisterModuleDependences('main', 'OnBeforeUserTypeUpdate', 'lnpa.module', 'LnpaEvents', 'OnBeforeUserTypeUpdateHandler');

        if (!$USER->IsAdmin()) {
            return;
        }

        if ($step < 2) {
            $APPLICATION->IncludeAdminFile(
                GetMessage('ESTATE_UNINSTALL_TITLE'),
                $this->installPath . '/uninstall/step1.php'
            );

            return;
        }

        Loader::includeModule($this->MODULE_ID);
        $this->UnInstallDB([
            "delete_tables" => $_REQUEST["delete_tables"],
        ]);
        $this->unInstallFiles();

        ModuleManager::unRegisterModule($this->MODULE_ID);
        $this->Agents('del');
    }

    public function UnInstallDB($arParams = [])
    {
        if ($arParams['delete_tables'] == 'Y') {
            $connection = Application::getConnection();
            //$connection->dropTable();
        }
    }

    public function installFiles()
    {

    }

    public function unInstallFiles()
    {

    }

    protected function checkDependencies(){
        $result = [];
        foreach ($this->requiredModules as $module){
            if (!Loader::includeModule($module)){
                $result[] = $module;
            }
        }
        if (!empty($result)){
            $this->showError($this->installPath . '/install/modules_not_installed.php', ['modules'=>$result]);
        }
        return true;
    }

    protected function showError($file, $arVariables, $strTitle=''){
        //define all global vars
        $keys = array_keys($GLOBALS);
        $keys_count = count($keys);
        for($i=0; $i<$keys_count; $i++)
            if($keys[$i]!="i" && $keys[$i]!="GLOBALS" && $keys[$i]!="strTitle" && $keys[$i]!="filepath")
                global ${$keys[$i]};

        //title
        $APPLICATION->SetTitle($strTitle);
        include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");
        include($file);
        include($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
        die();
    }

    /**
     * @return \Bitrix\Main\DB\Connection
     */
    protected function _getConnection()
    {
        return Application::getConnection();
    }
}