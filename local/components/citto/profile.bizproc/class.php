<?php

namespace Citto\Profile;

use CUser;
use CJSCore;
use Exception;
use CBitrixComponent;
use Bitrix\Main\Loader;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Config\Option;
use Citto\Integration\Source1C;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class MyBizproc extends CBitrixComponent
{
    /**
     * Запуск компонента
     *
     * @return null
     *
     * @throws LoaderException
     */
    public function executeComponent()
    {
        try {
            global $APPLICATION, $USER;

            $hasAccess = false;

            if ($USER->GetID() == $this->arParams['USER_ID']) {
                $hasAccess = true;
            }
            if ($USER->IsAdmin()) {
                $hasAccess = true;
            }
            if (!$hasAccess) {
                throw new Exception("Ошибка доступа");
            }
            Loader::includeModule('citto.integration');
            Extension::load(
                [
                    'ui.forms',
                    'ui.buttons',
                    'ui.buttons.icons',
                    'ui.dialogs',
                    'ui.dialogs.messagebox'
                ]
            );
            CJSCore::Init(['jquery3', 'popup', 'ui']);

            $template = '';
            $title = 'Бизнес-процессы';
            if (isset($_REQUEST['create'])) {
                $template = 'create';
                $title = 'Новый бизнес-процесс';
            }
            $this->includeComponentTemplate($template);
            $APPLICATION->SetTitle($title);
        } catch (Exception $e) {
            // ShowError($e->getMessage());
        }
    }
}
