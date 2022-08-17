<?php
use CFile;
use CIBlockElement;
use CIntranetUtils;
use CBitrixComponent;
use \Bitrix\Main\Loader;
use Bitrix\Main\UserTable;
use \Bitrix\Main\Application;


if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class Component extends CBitrixComponent
{
    public function executeComponent()
    {
        $this->_request = Application::getInstance()->getContext()->getRequest();
        $this->InitComponentTemplate();
        $this->arResult['TEMPLATE'] = & $this->GetTemplate()->GetFolder();
        $this->includeComponentTemplate();
    }
}
