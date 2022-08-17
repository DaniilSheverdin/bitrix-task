<?php

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin.php");

define('MODULE_NAME', 'citto.integration');

use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

if (!$USER->isAdmin()) {
    $APPLICATION->authForm('Nope');
}

Loc::loadMessages(__FILE__);

$APPLICATION->SetTitle(Loc::getMessage('CITTO_ESIA_SITE_OPTIONS__TITLE'));

if (!Loader::includeModule(MODULE_NAME)) {
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");
    ShowMessage('"' . MODULE_NAME . '" module is not installed!');
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
}

$errors = array();
$notes = array();
$request = Application::getInstance()->getContext()->getRequest();

if ($request->isPost() && $request->get("save") && check_bitrix_sessid()) {
    $options = $request->getPost("options");

    if (count($options)) {
        Option::set(MODULE_NAME, "values", serialize($options));
    }
}

if (!empty($errors)) {
    CAdminMessage::ShowMessage(join("\n", $errors));
} elseif (!empty($notes)) {
    CAdminMessage::ShowNote(join("\n", $notes));
}

$aTabs = array(
    array(
        "DIV" => "settings",
        "TAB" => Loc::getMessage("CITTO_ESIA_SITE_OPTIONS__TAB_TITLE_1"),
        "TITLE" => Loc::getMessage("CITTO_ESIA_SITE_OPTIONS__TAB_CONTENT_1"),
    ),
    array(
        "DIV" => "settings_parsec",
        "TAB" => Loc::getMessage("CITTO_ESIA_SITE_OPTIONS__TAB_TITLE_2"),
        "TITLE" => Loc::getMessage("CITTO_ESIA_SITE_OPTIONS__TAB_CONTENT_2"),
    ),
    array(
        "DIV" => "settings_delo",
        "TAB" => Loc::getMessage("CITTO_ESIA_SITE_OPTIONS__TAB_TITLE_3"),
        "TITLE" => Loc::getMessage("CITTO_ESIA_SITE_OPTIONS__TAB_CONTENT_3"),
    ),
);

$tabControl = new \CAdminTabControl("tabControl", $aTabs, true, true);
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

$tabControl->Begin();
$tabControl->BeginNextTab();

$options = unserialize(Option::get(MODULE_NAME, "values"));
?>
    <form method="POST" action="<?= sprintf('%s?mid=%s&lang=%s', $request->getRequestedPage(), urlencode($mid), LANGUAGE_ID) ?>">
        <?= bitrix_sessid_post(); ?>
        <tr>
            <td colspan="2" align="center">
                <table class="internal" style="width: 80%;">
                    <tbody>
                    <tr class="heading">
                        <td colspan="8">Настройки для Структуры модуля</td>
                    </tr>
                      <tr>
                        <td width="30%" class="adm-detail-content-cell-l">
                            <label for="options[NoPersonalDataMessage]">
                                Ошибка для личного кабинета в случае не доступности 1С:
                            </label>
                        </td>
                        <td>
                            <textarea style="resize: none; width:80%; height:200px;" name="options[NoPersonalDataMessage]"><?=$options['NoPersonalDataMessage']?></textarea>
                        </td>

                    </tr>
                    <tr>
                        <td width="30%" class="adm-detail-content-cell-l">
                            <label for="wsdl_path">
                                Активировать пользователей которые пришли из 1С:
                            </label>
                        </td>
                        <td>
                            <select style="width:50%;" name="options[ActivateUser]">
                                <option <?=($options['ActivateUser']=='Y')?'selected':'';?> value="Y">Да</option>
                                <option <?=($options['ActivateUser']=='N')?'selected':'';?> value="N">Нет</option>
                            </select>
                            
                        </td>

                    </tr>
                    <tr>
                        <td width="30%" class="adm-detail-content-cell-l">
                            <label for="wsdl_path">
                                Пароль для доступа по ключу:
                            </label>
                        </td>
                        <td>
                            <input type="text" name="options[ac_password]" id="ac_password"
                                   value="<?= $options['ac_password'] ?>"
                                   style="width: 50%"/>
                            
                        </td>

                    </tr>
                    <tr>
                        <td width="30%" class="adm-detail-content-cell-l">
                            <label for="wsdl_path">
                                Пользователь для авторизации скрипта:
                            </label>
                        </td>
                        <td>
                            <input type="text" name="options[user_id]" id="user_id"
                                   value="<?= $options['user_id'] ?>"
                                   style="width: 50%"/>
                            
                        </td>

                    </tr>
                    <tr>
                        <td width="30%" class="adm-detail-content-cell-l">
                            <label for="wsdl_path">
                                Уровень детализации логов:
                            </label>
                        </td>
                        <td>
                            <select style="width:50%;" name="options[log_level]">
                                <option <?=($options['log_level']=='DEBUG')?'selected':'';?> value="DEBUG">DEBUG</option>
                                <option <?=($options['log_level']=='INFO')?'selected':'';?> value="INFO">INFO</option>
                                <option <?=($options['log_level']=='NOTICE')?'selected':'';?> value="NOTICE">NOTICE</option>
                                <option <?=($options['log_level']=='WARNING')?'selected':'';?> value="WARNING">WARNING</option>
                                <option <?=($options['log_level']=='ERROR')?'selected':'';?> value="ERROR">ERROR</option>
                                <option <?=($options['log_level']=='CRITICAL')?'selected':'';?> value="CRITICAL">CRITICAL</option>
                                
                            </select>
                            
                        </td>

                    </tr>
                    <tr class="heading">
                        <td colspan="8">Настройки для Структуры модуля</td>
                    </tr>
                    <tr>
                        <td width="30%" class="adm-detail-content-cell-l">
                            <label for="wsdl_path">
                                WSDL-path:
                            </label>
                        </td>
                        <td>
                            <input type="text" name="options[wsdl_path]" id="wsdl_path"
                                   value="<?= $options['wsdl_path'] ?>"
                                   style="width: 50%"/>
                        </td>
                    </tr>
                    <tr>
                        <td width="30%" class="adm-detail-content-cell-l">
                            <label for="username">
                                username:
                            </label>
                        </td>
                        <td>
                            <input type="text" name="options[username]" id="username"
                                   value="<?= $options['username'] ?>"
                                   style="width: 50%"/>
                        </td>
                    </tr>
                    <tr>
                        <td width="30%" class="adm-detail-content-cell-l">
                            <label for="password">
                                password:
                            </label>
                        </td>
                        <td>
                            <input type="text" name="options[password]" id="password"
                                   value="<?= $options['password'] ?>"
                                   style="width: 50%"/>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        <?
        $tabControl->BeginNextTab(); ?>
        <tr>
            <td colspan="2" align="center">
                <table class="internal" style="width: 80%;">
                    <tbody>
                    <tr class="heading">
                        <td colspan="8">Настройки для Parsec</td>
                    </tr>
                    <tr>
                        <td width="30%" class="adm-detail-content-cell-l">
                            <label for="wsdl_path_parsec">
                                WSDL API:
                            </label>
                        </td>
                        <td>
                            <input type="text" name="options[wsdl_path_parsec]" id="wsdl_path_parsec"
                                   value="<?= $options['wsdl_path_parsec'] ?>"
                                   style="width: 50%"/>
                        </td>
                    </tr>
                    <tr>
                        <td width="30%" class="adm-detail-content-cell-l">
                            <label for="domain_parsec">
                                Domain API:
                            </label>
                        </td>
                        <td>
                            <input type="text" name="options[domain_parsec]" id="domain_parsec"
                                   value="<?= $options['domain_parsec'] ?>"
                                   style="width: 50%"/>
                        </td>
                    </tr>
                    <tr>
                        <td width="30%" class="adm-detail-content-cell-l">
                            <label for="user_parsec">
                                User API:
                            </label>
                        </td>
                        <td>
                            <input type="text" name="options[user_parsec]" id="user_parsec"
                                   value="<?= $options['user_parsec'] ?>"
                                   style="width: 50%"/>
                        </td>
                    </tr>
                    <tr>
                        <td width="30%" class="adm-detail-content-cell-l">
                            <label for="password_parsec">
                                Password API:
                            </label>
                        </td>
                        <td>
                            <input type="text" name="options[password_parsec]" id="password_parsec"
                                   value="<?= $options['password_parsec'] ?>"
                                   style="width: 50%"/>
                        </td>
                    </tr>
                    <tr>
                        <td width="30%" class="adm-detail-content-cell-l">
                            <label for="host_parsec">
                                Host DB (localhost, 1234):
                            </label>
                        </td>
                        <td>
                            <input type="text" name="options[host_parsec]" id="host_parsec"
                                   value="<?= $options['host_parsec'] ?>"
                                   style="width: 50%"/>
                        </td>
                    </tr>
                    <tr>
                        <td width="30%" class="adm-detail-content-cell-l">
                            <label for="namedb_parsec">
                                Name DB:
                            </label>
                        </td>
                        <td>
                            <input type="text" name="options[namedb_parsec]" id="namedb_parsec"
                                   value="<?= $options['namedb_parsec'] ?>"
                                   style="width: 50%"/>
                        </td>
                    </tr>
                    <tr>
                        <td width="30%" class="adm-detail-content-cell-l">
                            <label for="userdb_parsec">
                                User DB:
                            </label>
                        </td>
                        <td>
                            <input type="text" name="options[userdb_parsec]" id="userdb_parsec"
                                   value="<?= $options['userdb_parsec'] ?>"
                                   style="width: 50%"/>
                        </td>
                    </tr>
                    <tr>
                        <td width="30%" class="adm-detail-content-cell-l">
                            <label for="passwordb_parsec">
                                Password DB:
                            </label>
                        </td>
                        <td>
                            <input type="text" name="options[passwordb_parsec]" id="passwordb_parsec"
                                   value="<?= $options['passwordb_parsec'] ?>"
                                   style="width: 50%"/>
                        </td>
                    </tr>
                    <tr>
                        <td width="30%" class="adm-detail-content-cell-l">
                            <label for="passwordb_parsec">
                                Date sync from (timestamp):
                            </label>
                        </td>
                        <td>
                            <input type="number" name="options[date_from_parsec]" id="date_from_parsec"
                                   value="<?= $options['date_from_parsec'] ?>"
                                   style="width: 50%"/>
                        </td>
                    </tr>
                    <tr>
                        <td width="30%" class="adm-detail-content-cell-l">
                            <label for="passwordb_parsec">
                                Date sync to (timestamp):
                            </label>
                        </td>
                        <td>
                            <input type="number" name="options[date_to_parsec]" id="date_to_parsec"
                                   value="<?= $options['date_to_parsec'] ?>"
                                   style="width: 50%"/>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        <?
        $tabControl->BeginNextTab(); ?>
        <tr>
            <td colspan="2" align="center">
                <table class="internal" style="width: 80%;">
                    <tbody>
                    <tr>
                        <td width="30%" class="adm-detail-content-cell-l">
                            <label for="wsdl_path_parsec">
                                WSDL UsersApi:
                            </label>
                        </td>
                        <td>
                            <input type="text" name="options[wsdl_path_delo]" id="wsdl_path_delo"
                                   value="<?= $options['wsdl_path_delo'] ?>"
                                   style="width: 50%"/>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
        <?
        $tabControl->Buttons(array('btnApply' => false));
        $tabControl->End();
        ?>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php"); ?>

