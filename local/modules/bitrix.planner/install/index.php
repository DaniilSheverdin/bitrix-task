<?
IncludeModuleLangFile(__FILE__);

Class bitrix_planner extends CModule
{
    const MODULE_ID = 'bitrix.planner';
    var $MODULE_ID = 'bitrix.planner';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $MODULE_CSS;
    var $strError = '';

    function __construct()
    {
        $arModuleVersion = array();
        include(dirname(__FILE__) . "/version.php");
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = GetMessage("bitrix.planner_MODULE_NAME");
        $this->MODULE_DESCRIPTION = GetMessage("bitrix.planner_MODULE_DESC");
        $this->PARTNER_NAME = GetMessage("bitrix.planner_PARTNER_NAME");
        $this->PARTNER_URI = GetMessage("bitrix.planner_PARTNER_URI");
    }

    function InstallDB($arParams = array())
    {
//		RegisterModuleDependences('main', 'OnBuildGlobalMenu', self::MODULE_ID, 'CBitrixPlanner', 'OnBuildGlobalMenu');
        return true;
    }

    function UnInstallDB($arParams = array())
    {
        UnRegisterModuleDependences('main', 'OnBuildGlobalMenu', self::MODULE_ID, 'CBitrixPlanner', 'OnBuildGlobalMenu');
        return true;
    }

    function InstallEvents()
    {
        return true;
    }

    function UnInstallEvents()
    {
        return true;
    }

    function InstallFiles($arParams = array())
    {
        if (is_dir($p = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/admin')) {
            if ($dir = opendir($p)) {
                while (false !== $item = readdir($dir)) {
                    if ($item == '..' || $item == '.' || $item == 'menu.php')
                        continue;
                    file_put_contents($file = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/' . self::MODULE_ID . '_' . $item,
                        '<' . '? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/' . self::MODULE_ID . '/admin/' . $item . '");?' . '>');
                }
                closedir($dir);
            }
        }
        if (is_dir($p = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/components')) {
            if ($dir = opendir($p)) {
                while (false !== $item = readdir($dir)) {
                    if ($item == '..' || $item == '.')
                        continue;
                    CopyDirFiles($p . '/' . $item, $_SERVER['DOCUMENT_ROOT'] . '/local/components/citto/holiday.list/' . $item, $ReWrite = True, $Recursive = True);
                }
                closedir($dir);
            }
        }
        if (!file_exists($f = $_SERVER['DOCUMENT_ROOT'] . '/planner/index.php'))
            file_put_contents($f, '<?
									require($_SERVER[\'DOCUMENT_ROOT\']."/bitrix/header.php");
									$APPLICATION->ShowPanel = true;
									?><?$APPLICATION->IncludeComponent(
										"citto:holiday.list",
										"",
										Array(
											"COUNT_DAYS" => "Y",
											"HR_GROUP_ID" => "0",
											"IBLOCK_ID" => $_REQUEST["ID"],
											"IBLOCK_TYPE" => "news",
											"MANAGER_ADD_DAYS" => "Y",
											"SHOW_ALL" => "N",
											"SHOW_TIME" => "N"
										)
									);?><?
									require($_SERVER[\'DOCUMENT_ROOT\']."/bitrix/footer.php");
									?>');
        return true;
    }

    function UnInstallFiles()
    {
        if (is_dir($p = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/admin')) {
            if ($dir = opendir($p)) {
                while (false !== $item = readdir($dir)) {
                    if ($item == '..' || $item == '.')
                        continue;
                    unlink($_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin/' . self::MODULE_ID . '_' . $item);
                }
                closedir($dir);
            }
        }
        if (is_dir($p = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . self::MODULE_ID . '/install/components')) {
            if ($dir = opendir($p)) {
                while (false !== $item = readdir($dir)) {
                    if ($item == '..' || $item == '.' || !is_dir($p0 = $p . '/' . $item))
                        continue;

                    $dir0 = opendir($p0);
                    while (false !== $item0 = readdir($dir0)) {
                        if ($item0 == '..' || $item0 == '.')
                            continue;
                        DeleteDirFilesEx('/bitrix/components/' . $item . '/' . $item0);
                    }
                    closedir($dir0);
                }
                closedir($dir);
            }
        }
        return true;
    }

    function Agents($action)
    {
    	if($action == 'add') {
			CAgent::AddAgent(
				"CBitrixPlanner::Agent1sUsers();", // имя функции
				$this->MODULE_ID,                          // идентификатор модуля
				"N",                                  // агент не критичен к кол-ву запусков
				86400,                                // интервал запуска - 1 сутки
				"07.04.2020 20:03:26",                // дата первой проверки на запуск
				"Y",                                  // агент активен
				"07.04.2020 20:03:26",                // дата первого запуска
				30);
		}
    	elseif($action == 'del') {
			CAgent::RemoveAgent("CBitrixPlanner::Agent1sUsers;", $this->MODULE_ID);
		}
    }

    function DoInstall()
    {
        global $APPLICATION;
        $this->InstallFiles();
        $this->InstallDB();
        RegisterModule(self::MODULE_ID);
		$this->Agents('add');
    }

    function DoUninstall()
    {
        global $APPLICATION;
        UnRegisterModule(self::MODULE_ID);
        $this->UnInstallDB();
        $this->UnInstallFiles();
		$this->Agents('del');
    }
}

?>
