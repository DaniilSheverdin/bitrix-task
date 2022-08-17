<?

use CTaskItem;
use CIntranetUtils;
use Bitrix\Main\Loader;
use Bitrix\Tasks\Manager;
use Bitrix\Main\UserTable;
use Bitrix\Main\Page\Asset;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
	die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$bAccess = (in_array($GLOBALS['USER']->GetID(), [41, 566]));
$bAccess = ($bAccess || $GLOBALS['USER']->IsAdmin());
if (!$bAccess) {
    LocalRedirect('/');
}

if (!isset($arParams['DEPARTMENT'])) {
	ShowError('Не указан отдел');
	return;
}

$arTypes = [
    'CREATED_BY'        => 'Постановщик',
    'RESPONSIBLE_ID'    => 'Ответственный',
    'AUDITORS'          => 'Наблюдатель',
    'ACCOMPLICES'       => 'Соисполнитель',
];

if (
    check_bitrix_sessid() &&
    isset($_POST['change_user']) &&
    $_POST['NEW_USER_ID'] > 0 &&
    array_key_exists($_POST['ROLE'], $arTypes)
) {
    foreach ($_POST['ID'] as $taskId) {
        try {
            $oTaskItem = CTaskItem::getInstance($taskId, $GLOBALS['USER']->GetID());
            $arTask = $oTaskItem->getData();
            $newData = $arTask[ $_POST['ROLE'] ];
            if (!is_array($newData)) {
                $newData = $_POST['NEW_USER_ID'];
            } else {
                foreach ($newData as $k => $value) {
                    if ($value == $_POST['OLD_USER']) {
                        $newData[ $k ] = $_POST['NEW_USER_ID'];
                    }
                }
            }
            if (md5(serialize($arTask[ $_POST['ROLE'] ])) != md5(serialize($newData))) {
                $oTaskItem->Update([$_POST['ROLE'] => $newData]);
            }
        } catch (Exception $exc) {
            ShowError($exc->GetMessage());
        }
    }
    LocalRedirect($APPLICATION->GetCurPage());
}

Loader::includeModule('tasks');
Loader::includeModule('intranet');
$arDepartments = CIntranetUtils::GetDeparmentsTree($arParams['DEPARTMENT'], true);
$arDepartments[] = $arParams['DEPARTMENT'];

$orm = UserTable::getList([
    'select'    => ['ID', 'NAME', 'LAST_NAME', 'UF_DEPARTMENT'],
    'filter'    => ['ACTIVE' => 'N']
]);
$arInactiveUsers = [];
while ($arUser = $orm->fetch()) {
    $arDiff = array_intersect($arUser['UF_DEPARTMENT'], $arDepartments);
    if (!empty($arDiff)) {
        $arInactiveUsers[ $arUser['ID'] ] = $arUser['LAST_NAME'] . ' ' . $arUser['NAME'];
    }
}

$parameters = [
    'MAKE_ACCESS_FILTER' => true,
];
$getListParameters = [
    'select' => [
        'ID',
        'TITLE',
        'CREATED_BY',
        'RESPONSIBLE_ID',
        'AUDITORS',
        'ACCOMPLICES',
    ],
    'legacyFilter' => ['<REAL_STATUS' => 4],
];
$mgrResult = Manager\Task::getList(1, $getListParameters, $parameters);
$arResult = [];
foreach ($mgrResult['DATA'] as $arTask) {
    if (array_key_exists($arTask['CREATED_BY'], $arInactiveUsers)) {
        $arResult[ $arTask['CREATED_BY'] ]['CREATED_BY'][ $arTask['ID'] ] = $arTask['TITLE'];
    }
    if (array_key_exists($arTask['RESPONSIBLE_ID'], $arInactiveUsers)) {
        $arResult[ $arTask['RESPONSIBLE_ID'] ]['RESPONSIBLE_ID'][ $arTask['ID'] ] = $arTask['TITLE'];
    }
    foreach ($arTask['AUDITORS'] as $uId) {
        if (array_key_exists($uId, $arInactiveUsers)) {
            $arResult[ $uId ]['AUDITORS'][ $arTask['ID'] ] = $arTask['TITLE'];
        }
    }
    foreach ($arTask['ACCOMPLICES'] as $uId) {
        if (array_key_exists($uId, $arInactiveUsers)) {
            $arResult[ $uId ]['ACCOMPLICES'][ $arTask['ID'] ] = $arTask['TITLE'];
        }
    }
}
ksort($arResult);
Asset::getInstance()->addCss('/bitrix/templates/.default/bootstrap.min.css');
Asset::getInstance()->addCss('/bitrix/components/bitrix/intranet.user.selector.new/templates/.default/style.css');
Asset::getInstance()->addJs('/bitrix/components/bitrix/intranet.user.selector.new/templates/.default/users.js');
?>
<input type="hidden" name="NEW_USER_FAKE" id="NEW_USER_FAKE" />
<div class="position-absolute user-selector-popup d-none">
    <button type="button" class="close">
        <span aria-hidden="true">&times;</span>
    </button>
    <?$APPLICATION->IncludeComponent(
        'bitrix:intranet.user.selector.new',
        '',
        array(
            'NAME'                  => 'NEW_USER_FAKE',
            'INPUT_NAME'            => 'NEW_USER_FAKE',
            'TEXTAREA_MIN_HEIGHT'   => 30,
            'TEXTAREA_MAX_HEIGHT'   => 60,
            'INPUT_VALUE'           => 1,
            'EXTERNAL'              => 'I',
            'POPUP'                 => 'Y',
            "MULTIPLE"              => "N",
            'SOCNET_GROUP_ID'       => '',
            'ON_SELECT'             => "hidePopup"
        ),
        false
    );?>
</div>
<table class="table table-bordered">
    <thead>
        <tr>
            <th width="20%">Пользователь</th>
            <th>Задачи</th>
        </tr>
    </thead>
    <tbody>
        <? foreach ($arResult as $uId => $taskTypes) : ?>
        <tr>
            <td><?=$arInactiveUsers[ $uId ]?></td>
            <td>
                <? foreach ($taskTypes as $type => $arTasks) : ?>
                    <a class="table-toggle" data-id="tasks-<?=$uId?>-<?=$type?>" href="javascript:void(0);"><b><?=$arTypes[ $type ]?></b>: <?=count($arTasks)?> шт.</a><br/>
                    <form method="POST" class="d-none" id="tasks-<?=$uId?>-<?=$type?>">
                        <?=bitrix_sessid_post()?>
                        <input type="hidden" name="OLD_USER" value="<?=$uId?>" />
                        <input type="hidden" name="ROLE" value="<?=$type?>" />
                        <input type="hidden" name="NEW_USER_ID" id="NEW_USER-<?=$uId?>-<?=$type?>_ID" required />
                        <table class="table table-bordered">
                            <? foreach ($arTasks as $id => $name) : ?>
                            <tr>
                                <td width="5%">
                                    <input type="checkbox" name="ID[]" value="<?=$id?>" checked />
                                </td>
                                <td>
                                    <a href="/citto/company/personal/user/<?=$uId?>/tasks/task/view/<?=$id?>/" target="_blank"><?=$name?></a>
                                </td>
                            </tr>
                            <? endforeach; ?>
                        </table>
                        <input
                            class="form-control"
                            name="NEW_USER"
                            id="NEW_USER-<?=$uId?>-<?=$type?>"
                            placeholder="Новый пользователь"
                            autocomplete="off"
                            required
                            />
                        <br/>
                        <input
                            type="submit"
                            name="change_user"
                            value="Перенести"
                            class="ui-btn ui-btn-success"
                            />
                        <br/>
                        <br/>
                    </form>
                <? endforeach; ?>
            </td>
        </tr>
        <? endforeach; ?>
    </tbody>
</table>
