<?

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
use Bitrix\Main\Loader;
use Bitrix\Highloadblock as HL;
Loader::includeModule("highloadblock");

define("PUBLIC_AJAX_MODE", true);
define("NO_AGENT_CHECK", true);
define("DisableEventsCheck", true);
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
CBitrixComponent::includeComponentClass("citto:scud");

IncludeModuleLangFile(__FILE__);

$arParams = $_REQUEST['arParams'];

$iblockID = isset($_REQUEST['IBLOCK_ID'])
    ? intval($_REQUEST['IBLOCK_ID'])
    : (is_array($arParams)
        ? intval($arParams['IBLOCK_ID'])
        : 0
    );
if ($iblockID <= 0) {
    $iblockID = COption::GetOptionInt("intranet", "iblock_absence");
}

$bIblockChanged = $iblockID != COption::GetOptionInt('intranet', 'iblock_absence');

$iHlBlockScudID = HL\HighloadBlockTable::getList([
    'filter' => ['=NAME' => 'SCUD']
])->fetch()['ID'];
if (!$iHlBlockScudID) {
    $iHlBlockScudID = (HL\HighloadBlockTable::add(array(
        'NAME' => 'SCUD',
        'TABLE_NAME' => 'tbl_scud',
    )))->getId();
}
$obHlblock = HL\HighloadBlockTable::getById($iHlBlockScudID)->fetch();
$obEntity = HL\HighloadBlockTable::compileEntity($obHlblock);
$sClassScud = $obEntity->getDataClass();

function getEventFieldID()
{
    global $iHlBlockScudID;

    $obTypesAbsence = CUserFieldEnum::GetList(array(), array(
        "ENTITY_ID" => "HLBLOCK_$iHlBlockScudID",
        'USER_FIELD_NAME' => 'UF_TYPE_EVENT'
    ));
    while ($arType = $obTypesAbsence->getNext()) {
        if ($arType['XML_ID'] == 'OTHER') {
            $ID = $arType['ID'];
        }
    }

    return $ID;
}

function getFieldsAbsence($absenceID = 0)
{
    global $sClassScud;

    $rsData = $sClassScud::getList([
        "select" => ['*'],
        "filter" => [
            "ID" => $absenceID,
        ]
    ])->Fetch();
    
    return $rsData;
}

function CanAdd($userID)
{
    global $USER;

    $role = $_SESSION['SESS_AUTH']['ROLE_SCUD'];
    $bCan = false;

    if ($role == 'ADMIN') {
        $bCan = true;
    } elseif ($role == 'EMPLOYEE' && $USER->GetID() == $userID) {
        $bCan = true;
    } elseif (in_array($role, ['SECRETARY', 'HEAD'])) {
        $rsUsers = CIntranetUtils::getDepartmentEmployees($_SESSION['SESS_AUTH']['STRUCTURE_IDS_SCUD'], $bRecursive = true);
        while ($user = $rsUsers->getNext()) {
            if ($userID == $user['ID']) {
                $bCan = true;
                break;
            }
        }
    }

    if ($USER->GetID() == $userID) {
        $bCan = true;
    }

    return $bCan;
}

function CanEditORDelete($absenceID = null, $action = null, $currUser = null)
{
    global $USER;

    $arRoles = ['SECRETARY', 'HEAD', 'EMPLOYEE', 'ADMIN'];
    $role = $_SESSION['SESS_AUTH']['ROLE_SCUD'];
    $fieldsAbsence = getFieldsAbsence($absenceID);
    $userID = $fieldsAbsence['UF_USER'];
    $bCan = false;

    if (!empty($absenceID) && in_array($action, ['edit', 'delete']) && in_array($role, $arRoles)) {
        if (getEventFieldID() == $fieldsAbsence['UF_TYPE_EVENT']) {
            if ($role == 'ADMIN') {
                $bCan = true;
            } elseif ($role == 'EMPLOYEE' && $USER->GetID() == $userID) {
                if ($USER->GetID() != $currUser && $action == 'edit') {
                    $bCan = false;
                } else {
                    $bCan = true;
                }
            } elseif (in_array($role, ['SECRETARY', 'HEAD'])) {
                $rsUsers = CIntranetUtils::getDepartmentEmployees($_SESSION['SESS_AUTH']['STRUCTURE_IDS_SCUD'], $bRecursive = true);
                while ($user = $rsUsers->getNext()) {
                    if ($userID == $user['ID']) {
                        $bCan = true;
                        break;
                    }
                }
            }
        }
    }

    if ($action == 'delete' && $bCan && !checkDateEvent($fieldsAbsence['UF_ACTIVE_FROM'])) {
        $bCan = 'INTR_DATE_ERR';
    }

    return $bCan;
}

function checkDateEvent($dateFrom)
{
    $role = $_SESSION['SESS_AUTH']['ROLE_SCUD'];
    $dateFrom = strtotime($dateFrom);
    if (in_array($role, ['ADMIN', 'HEAD'])) {
        return true;
    } elseif ($dateFrom >= time()) {
        return true;
    } else {
        return false;
    }
}

function AddAbsence($arFields)
{
    global $DB, $iblockID, $sClassScud;
    $obElement = new CIBlockElement();

    if (CModule::IncludeModule('iblock')) {
        $arFields["ABSENCE_TYPE"] = getEventFieldID();
        $obElement->LAST_ERROR = '';

        if (!empty($arFields['UF_ACTIVE_FROM']) && !empty($arFields['UF_ACTIVE_TO'])) {
            if ($DB->isDate($arFields['UF_ACTIVE_FROM'], false, LANG, 'FULL') && $DB->isDate($arFields['UF_ACTIVE_TO'], false, LANG, 'FULL')) {
                if (makeTimeStamp($arFields['UF_ACTIVE_FROM']) > makeTimeStamp($arFields['UF_ACTIVE_TO'])) {
                    $obElement->LAST_ERROR .= getTranslate('INTR_ABSENCE_FROM_TO_ERR') . '<br>';
                }
            }
        }
        if (empty($arFields['USER_ID'])) {
            $obElement->LAST_ERROR .= getTranslate('INTR_USER_ERR') . '<br>';
        }
        if (empty($arFields['REASON'])) {
            $obElement->LAST_ERROR .= getTranslate('INTR_REASON_ERR') . '<br>';
        }
        if (empty($arFields['HEAD_FIO'])) {
            $obElement->LAST_ERROR .= getTranslate('INTR_HEAD_FIO_ERR') . '<br>';
        }

        if (empty($arFields['UF_ACTIVE_FROM']) || empty($arFields['UF_ACTIVE_TO'])) {
            $obElement->LAST_ERROR .= getTranslate('INTR_PERIOD_ERR') . '<br>';
        } elseif (!checkDateEvent($arFields['UF_ACTIVE_FROM'])) {
            $obElement->LAST_ERROR .= getTranslate('INTR_DATE_ERR') . '<br>';
        }

        if (!CanAdd($arFields['USER_ID']) && !empty($arFields['USER_ID'])) {
            $obElement->LAST_ERROR = getTranslate('INTR_CANNOT_RULES') . '<br>';
        }

        if (empty($obElement->LAST_ERROR)) {
            $arNewFields = [
                'UF_USER' => $arFields["USER_ID"],
                'UF_REASON_ABSENCE' => $arFields["REASON"],
                'UF_ACTIVE_FROM' => $arFields["UF_ACTIVE_FROM"],
                'UF_ACTIVE_TO' => $arFields["UF_ACTIVE_TO"],
                'UF_TYPE_EVENT' => getEventFieldID(),
                'UF_HEAD_CONFIRM' => $arFields['HEAD_FIO'],
                'UF_DATE_CREATE' => date('d.m.Y H:i:s', time()),
            ];
            $ID = ($sClassScud::add($arNewFields))->getId();
        }
    }

    if (empty($ID)) {
        $arErrors = preg_split("/<br>/", $obElement->LAST_ERROR);
        return $arErrors;
    } else {
        return $ID;
    }
}

function EditAbsence($arFields)
{
    global $DB, $sClassScud;
    $obElement = new CIBlockElement();

    if (CanEditORDelete($arFields['absence_element_id'], 'edit', $arFields['USER_ID'])) {
        if (CModule::IncludeModule('iblock')) {
            $arFields["ABSENCE_TYPE"] = getEventFieldID();
            $obElement->LAST_ERROR = '';

            if (!empty($arFields['UF_ACTIVE_FROM']) && !empty($arFields['UF_ACTIVE_TO'])) {
                if ($DB->isDate($arFields['UF_ACTIVE_FROM'], false, LANG, 'FULL') && $DB->isDate($arFields['UF_ACTIVE_TO'], false, LANG, 'FULL')) {
                    if (makeTimeStamp($arFields['UF_ACTIVE_FROM']) > makeTimeStamp($arFields['UF_ACTIVE_TO'])) {
                        $obElement->LAST_ERROR .= getTranslate('INTR_ABSENCE_FROM_TO_ERR') . '<br>';
                    }
                }
            }
            if (empty($arFields['USER_ID'])) {
                $obElement->LAST_ERROR .= getTranslate('INTR_USER_ERR') . '<br>';
            }
            if (empty($arFields['REASON'])) {
                $obElement->LAST_ERROR .= getTranslate('INTR_REASON_ERR') . '<br>';
            }
            if (empty($arFields['HEAD_FIO'])) {
                $obElement->LAST_ERROR .= getTranslate('INTR_HEAD_FIO_ERR') . '<br>';
            }

            if (empty($arFields['UF_ACTIVE_FROM']) || empty($arFields['UF_ACTIVE_TO'])) {
                $obElement->LAST_ERROR .= getTranslate('INTR_PERIOD_ERR') . '<br>';
            } elseif (!checkDateEvent($arFields['UF_ACTIVE_FROM'])) {
                $obElement->LAST_ERROR .= getTranslate('INTR_DATE_ERR') . '<br>';
            }

            if (empty($obElement->LAST_ERROR)) {
                $arNewFields = [
                    'UF_USER' => $arFields["USER_ID"],
                    'UF_REASON_ABSENCE' => $arFields["REASON"],
                    'UF_ACTIVE_FROM' => $arFields["UF_ACTIVE_FROM"],
                    'UF_ACTIVE_TO' => $arFields["UF_ACTIVE_TO"],
                    'UF_TYPE_EVENT' => getEventFieldID(),
                    'UF_HEAD_CONFIRM' => $arFields['HEAD_FIO'],
                    'UF_DATE_CREATE' => date('d.m.Y H:i:s', time()),
                ];
                $ID = $sClassScud::update($arFields['absence_element_id'], $arNewFields);
                $ID = $ID->getId();
            }
        }
    } else {
        $obElement->LAST_ERROR = getTranslate('INTR_CANNOT_RULES') . '<br>';
    }

    if (empty($ID)) {
        $arErrors = preg_split("/<br>/", $obElement->LAST_ERROR);
        return $arErrors;
    } else {
        return $ID;
    }
}

if (!CModule::IncludeModule('iblock')) {
    echo getTranslate("INTR_ABSENCE_BITRIX24_MODULE");
} else {
    if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["action"]) && $_POST["action"] == "delete" && check_bitrix_sessid()) {
        $result = CanEditORDelete($_POST["absenceID"], 'delete');
        $error = '';
        if (!$result) {
            $error = 'INTR_CANNOT_RULES';
        } elseif (is_string($result)) {
            $error = $result;
        } else {
            $sClassScud::Delete(intval($_POST["absenceID"]));
        }

        if (!empty($error)) {
            $error = 'error:<li>' . getTranslate($error) . '</li>';
        }
        die($error);
    }

    $ID = 1;
    if ($_SERVER["REQUEST_METHOD"] === "POST" && check_bitrix_sessid()) {
        if (isset($_POST['absence_element_id'])) {
            $ID = EditAbsence($_POST);
        } elseif (!isset($_POST['absence_element_id'])) {
            $ID = AddAbsence($_POST);
        } else {
            die('error:<li>' . getTranslate('INTR_USER_ERR_NO_RIGHT') . '</li>');
        }

        if (is_array($ID)) {
            $arErrors = $ID;
            foreach ($arErrors as $key => $val) {
                if (mb_strlen($val) <= 0) {
                    unset($arErrors[$key]);
                }
            }
            $ID = 0;
            die('error:<li>' . implode('</li><li>', array_map('htmlspecialcharsbx', $arErrors))) . '</li>';
        } elseif (isset($_POST['absence_element_id'])) {
            $absence = getFieldsAbsence($ID);
            $user = CUser::GetByID($absence['UF_USER'])->Fetch();
            $arJson = [
                'action' => 'edit',
                'id' => $ID,
                'user' => "{$user['LAST_NAME']} {$user['NAME']} {$user['SECOND_NAME']}",
                'dateRecord' => date("d.m.Y H:i:s", $absence['UF_DATE_CREATE']->format('U')),
                'dateAbsence' => "{$absence['UF_ACTIVE_FROM']} - {$absence['UF_ACTIVE_TO']}",
                'headConfirm' => $absence['UF_HEAD_CONFIRM'],
                'reason' => $absence['UF_REASON_ABSENCE']
            ];
            die(json_encode($arJson));
        }
    }
    ?>

    <div style="width: 450px; ">
        <? if ($ID > 1) { ?>
            <span id="jsonresult" style="display:none">
                    <?
                    $absence = getFieldsAbsence($ID);
                    $user = CUser::GetByID($absence['UF_USER'])->Fetch();
                    $arJson = [
                        'action' => 'add',
                        'id' => $ID,
                        'user' => "{$user['LAST_NAME']} {$user['NAME']} {$user['SECOND_NAME']}",
                        'dateRecord' => date("d.m.Y H:i:s", $absence['UF_DATE_CREATE']->format('U')),
                        'dateAbsence' => "{$absence['UF_ACTIVE_FROM']} - {$absence['UF_ACTIVE_TO']}",
                        'headConfirm' => $absence['UF_HEAD_CONFIRM'],
                        'reason' => $absence['UF_REASON_ABSENCE']
                    ];
                    echo json_encode($arJson);
                    die;
                    ?>
                </span>
            <p><?= getTranslate("INTR_ABSENCE_SUCCESS") ?></p>
        <form method="POST"
              action="<? echo "/local/components/citto/scud/tools/absence.php" . ($bIblockChanged ? "?IBLOCK_ID=" . $iblockID : "") ?>"
              id="ABSENCE_FORM">
                <input type="hidden" name="reload" value="Y">
            </form><?
        } else {
            $arElement = array();
            if (isset($arParams["ABSENCE_ELEMENT_ID"]) && intval($arParams["ABSENCE_ELEMENT_ID"]) > 0) {
                $rsElement = CIBlockElement::GetList(array(), array("ID" => intval($arParams["ABSENCE_ELEMENT_ID"]), "IBLOCK_ID" => $iblockID), false, false, array("ID", "NAME", "UF_ACTIVE_FROM", "UF_ACTIVE_TO", "IBLOCK_ID", "PROPERTY_ABSENCE_TYPE", "PROPERTY_USER"));
                $arElement = getFieldsAbsence($arParams["ABSENCE_ELEMENT_ID"]);
            }

            $controlName = "Single_" . RandString(6);
            ?>
            <form method="POST" action="<? echo "/local/components/citto/scud/tools/absence.php" ?>" id="ABSENCE_FORM">
                <? if (isset($_POST['absence_element_id']) || isset($arElement["ID"])) : ?>
                    <input type="hidden"
                           value="<?= (isset($_POST['absence_element_id'])) ? htmlspecialcharsbx($_POST['absence_element_id']) : $arElement['ID'] ?>"
                           name="absence_element_id"><?
                endif; ?>
                <?
                if ($bIblockChanged) :
                    ?>
                    <input type="hidden" name="IBLOCK_ID" value="<?= $iblockID ?>"/>
                <?
                endif;
                ?>

                <table width="100%" cellpadding="5">
                    <tr valign="bottom">
                        <td colspan="2">
                            <div style="font-size:14px;font-weight:bold;"><label
                                        for="USER_ID"><?= getTranslate("INTR_ABSENCE_USER") ?></label></div>
                            <?
                            $UserName = "";
                            if (isset($_POST['USER_ID']) || isset($arElement["UF_USER"])) {
                                $UserID = isset($_POST['USER_ID']) ? $_POST['USER_ID'] : $arElement["UF_USER"];
                                $dbUser = CUser::GetList($b = "", $o = "", array("ID" => intval($UserID)));
                                if ($arUser = $dbUser->Fetch()) {
                                    $UserName = CUser::FormatName(CSite::GetNameFormat(false), $arUser, true);
                                }
                            }

                            $userIdValue = '';
                            if (isset($_POST["USER_ID"])) {
                                $userIdValue = htmlspecialcharsbx($_POST["USER_ID"]);
                            } elseif (isset($arElement["UF_USER"])) {
                                $userIdValue = htmlspecialcharsbx($arElement["UF_USER"]);
                            }
                            ?>
                            <input type="hidden" id="user_id"
                                   value="<?=$userIdValue?>"
                                   name="USER_ID"
                                   style="width:35px;font-size:14px;border:1px #c8c8c8 solid;">
                            <span id="uf_user_name"><?= $UserName ?></span>

                            <? CUtil::InitJSCore(array('popup')); ?>
                            <a href="javascript:void(0)" onclick="ShowSingleSelector"
                               id="single-user-choice"><?= getTranslate("INTR_USER_CHOOSE") ?></a>
                            <script type="text/javascript"
                                    src="/bitrix/components/bitrix/intranet.user.selector.new/templates/.default/users.js"></script>
                            <script type="text/javascript">BX.loadCSS('/bitrix/components/bitrix/intranet.user.selector.new/templates/.default/style.css');</script>
                            <script type="text/javascript">// user_selector:
                                var multiPopup, singlePopup, taskIFramePopup;

                                function onSingleSelect(arUser) {
                                    BX("user_id").value = arUser.id;
                                    BX("uf_user_name").innerHTML = BX.util.htmlspecialchars(arUser.name);
                                    singlePopup.close();
                                }

                                function ShowSingleSelector(e) {

                                    if (!e) e = window.event;

                                    if (!singlePopup) {
                                        singlePopup = new BX.PopupWindow("single-employee-popup", this, {
                                            offsetTop: 1,
                                            autoHide: true,
                                            content: BX("<?=CUtil::JSEscape($controlName)?>_selector_content"),
                                            zIndex: 3000
                                        });
                                    } else {
                                        singlePopup.setContent(BX("<?=CUtil::JSEscape($controlName)?>_selector_content"));
                                        singlePopup.setBindElement(this);
                                    }

                                    if (singlePopup.popupContainer.style.display != "block") {
                                        singlePopup.show();
                                    }

                                    return BX.PreventDefault(e);
                                }

                                function Clear() {
                                    O_<?=CUtil::JSEscape($controlName)?>.setSelected();
                                }

                                BX.ready(function () {
                                    BX.bind(BX("single-user-choice"), "click", ShowSingleSelector);
                                    BX.bind(BX("clear-user-choice"), "click", Clear);
                                });
                            </script>
                            <? $name = $APPLICATION->IncludeComponent(
                                "bitrix:intranet.user.selector.new",
                                ".default",
                                array(
                                    'MULTIPLE' => 'N',
                                    'NAME' => $controlName,
                                    'VALUE' => 1,
                                    'POPUP' => 'Y',
                                    'ON_SELECT' => 'onSingleSelect',
                                    'SITE_ID' => SITE_ID,
                                    'SHOW_EXTRANET_USERS' => \CModule::includeModule('extranet') && \CExtranet::isExtranetSite() ? 'ALL' : 'NONE',
                                ),
                                null,
                                array("HIDE_ICONS" => "Y")
                            ); ?>
                        </td>
                    </tr>
                    <tr valign="bottom">
                        <td>
                            <div style="font-size:14px;font-weight:bold;margin-top:8px"><label
                                        for="REASON"><?= getTranslate("INTR_ABSENCE_REASON") ?></label></div>
                            <input type="text" name="REASON" id="REASON"
                                   style="width:100%;font-size:14px;border:1px #c8c8c8 solid;"
                                   value="<?= htmlspecialchars($arElement["UF_REASON_ABSENCE"]) ?>">
                        </td>
                    </tr>
                    <tr valign="bottom">
                        <td>
                            <div style="font-size:14px;font-weight:bold;margin-top:8px">
                                <label for="HEAD_FIO"><?= getTranslate("INTR_HEAD_CONFIRM") ?></label>
                            </div>
                            <input type="text" name="HEAD_FIO" id="HEAD_FIO"
                                   style="width:100%;font-size:14px;border:1px #c8c8c8 solid;"
                                   value="<?= htmlspecialchars($arElement["UF_HEAD_CONFIRM"]) ?>">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div style="font-size:14px;font-weight:bold;margin-top:8px"><?= getTranslate("INTR_ABSENCE_PERIOD") ?></div>
                        </td>
                    </tr>
                    <tr valign="bottom">
                        <td>
                            <table cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td width="100px">
                                        <label for="UF_ACTIVE_FROM"><?= getTranslate("INTR_ABSENCE_UF_ACTIVE_FROM") ?></label>
                                    </td>
                                    <td>
                                        <?
                                        $input_value_from = "";
                                        if (isset($arElement["UF_ACTIVE_FROM"]) || isset($_POST["UF_ACTIVE_FROM"])) {
                                            $input_value_from = (isset($arElement["UF_ACTIVE_TO"])) ? htmlspecialcharsbx(FormatDateFromDB($arElement["UF_ACTIVE_FROM"])) : htmlspecialcharsbx(FormatDateFromDB($_POST["UF_ACTIVE_FROM"]));
                                        }
                                        $APPLICATION->IncludeComponent(
                                            "bitrix:main.calendar",
                                            "",
                                            array(
                                                "SHOW_INPUT" => "Y",
                                                "FORM_NAME" => "",
                                                "INPUT_NAME" => "UF_ACTIVE_FROM",
                                                "INPUT_VALUE" => $input_value_from,
                                                "SHOW_TIME" => "Y",
                                                "HIDE_TIMEBAR" => "Y"
                                            )
                                        ); ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                    <tr valign="bottom">
                        <td>
                            <table cellpadding="0" cellspacing="0" border="0">
                                <tr>
                                    <td width="100px">
                                        <label for="UF_ACTIVE_TO"><?= getTranslate("INTR_ABSENCE_UF_ACTIVE_TO") ?></label>
                                    </td>
                                    <td>
                                        <?
                                        $input_value_to = "";
                                        if (isset($arElement["UF_ACTIVE_TO"]) || isset($_POST["UF_ACTIVE_TO"])) {
                                            $input_value_to = (isset($arElement["UF_ACTIVE_TO"])) ? htmlspecialcharsbx(FormatDateFromDB($arElement["UF_ACTIVE_TO"])) : htmlspecialcharsbx(FormatDateFromDB($_POST["UF_ACTIVE_TO"]));
                                        }
                                        $APPLICATION->IncludeComponent(
                                            "bitrix:main.calendar",
                                            "",
                                            array(
                                                "SHOW_INPUT" => "Y",
                                                "FORM_NAME" => "",
                                                "INPUT_NAME" => "UF_ACTIVE_TO",
                                                "INPUT_VALUE" => $input_value_to,
                                                "SHOW_TIME" => "Y",
                                                "HIDE_TIMEBAR" => "Y"
                                            )
                                        ); ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
                <? echo bitrix_sessid_post() ?>
            </form>
            <?
        }
        ?>
        <script type="text/javascript">
            var myBX;
            if (window.BX)
                myBX = window.BX;
            else if (window.top.BX)
                myBX = window.top.BX;
            else
                myBX = null;

            var myPopup = myBX.AbsenceCalendar.popup;
            var myButton = myPopup.buttons[0];
            <?if (isset($arParams["ABSENCE_ELEMENT_ID"]) && intval($arParams["ABSENCE_ELEMENT_ID"]) > 0 || isset($_POST['absence_element_id'])) :?>
            myButton.setName('<?=\CUtil::jsEscape(getTranslate('INTR_ABSENCE_EDIT')) ?>');
            myPopup.setTitleBar('<?=\CUtil::jsEscape(getTranslate('INTR_EDIT_TITLE')) ?>');
            <?elseif ($ID > 1) :?>
            myButton.setName('<?=\CUtil::jsEscape(getTranslate('INTR_ABSENCE_ADD_MORE')) ?>');
            myPopup.setTitleBar('<?=\CUtil::jsEscape(getTranslate('INTR_ADD_TITLE')) ?>');
            <?else :?>
            myButton.setName('<?=\CUtil::jsEscape(getTranslate('INTR_ABSENCE_ADD')) ?>');
            myPopup.setTitleBar('<?=\CUtil::jsEscape(getTranslate('INTR_ADD_TITLE')) ?>');
            <?endif?>

            myPopup = null;
            myButton = null;
            myBX = null;
        </script>
    </div>
    <?
}
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin_js.php");

function getTranslate($string)
{
    $MESS["INTR_ABSENCE_IBLOCK_MODULE"] = "Модуль информационных блоков не установлен.";
    $MESS["INTR_ADD_TITLE"] = "Добавить отсутствие";
    $MESS["INTR_EDIT_TITLE"] = "Редактировать отсутствие";
    $MESS["INTR_ABSENCE_REASON"] = "Причина отсутствия *";
    $MESS["INTR_ABSENCE_NO_TYPE"] = "(не установлено)";
    $MESS["INTR_ABSENCE_USER"] = "Выберите отсутствующего сотрудника *";
    $MESS["INTR_ABSENCE_TYPE"] = "Тип отсутствия";
    $MESS["INTR_ABSENCE_PERIOD"] = "Период отсутствия *";
    $MESS["INTR_ABSENCE_UF_ACTIVE_FROM"] = "Начало:";
    $MESS["INTR_ABSENCE_UF_ACTIVE_TO"] = "Окончание:";
    $MESS["INTR_ABSENCE_SUCCESS"] = "Отсутствие было добавлено";
    $MESS["INTR_ABSENCE_ADD"] = "Добавить";
    $MESS["INTR_ABSENCE_EDIT"] = "Редактировать";
    $MESS["INTR_ABSENCE_ADD_MORE"] = "Добавить еще";
    $MESS["INTR_USER_CHOOSE"] = "Выбрать из структуры";
    $MESS["INTR_USER_ERR_NO_RIGHT"] = "Недостаточно прав для изменений";
    $MESS["INTR_ABSENCE_FROM_TO_ERR"] = "Начало не может быть позже окончания";
    $MESS["INTR_HEAD_CONFIRM"] = "Фамилия руководителя, давшего разрешение на убытие гражданского служащего (работника) *";
    $MESS["INTR_HEAD_FIO_ERR"] = "Укажите фамилию руководителя";
    $MESS["INTR_NAME_ERR"] = "Укажите причину отсутствия";
    $MESS["INTR_USER_ERR"] = "Укажите отсутствующего сотрудника";
    $MESS["INTR_PERIOD_ERR"] = "Укажите период отстутсвия";
    $MESS["INTR_REASON_ERR"] = "Укажите причину отстутсвия";
    $MESS["INTR_CANNOT_RULES"] = "Не хватает прав. Обратитесь к администратору";
    $MESS["INTR_DATE_ERR"] = "Момент отсутствия сотрудника уже наступил";
    return $MESS[$string];
}
