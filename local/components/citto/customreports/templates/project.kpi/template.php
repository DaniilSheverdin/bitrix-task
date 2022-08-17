<?php

use CSocNetUser;
use CSocNetGroup;
use CSocNetUserToGroup;
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Main\UI\Extension;
use Citto\Tasks\ProjectInitiative;
use Citto\Tasks\ProjectInitiative\Kpi;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) {
    die();
}

global $USER;
Loader::includeModule('socialnetwork');

if (!$arParams['GROUP_ID']) {
    ShowError('Не указан проект');
    exit;
}

$arGroupInfo = CSocNetGroup::GetByID($arParams['GROUP_ID']);

if (!$arGroupInfo) {
    ShowError('Проект не найден');
    exit;
}

Asset::getInstance()->addCss('/bitrix/templates/.default/bootstrap.min.css');

Extension::load([
    'ui.buttons.icons',
    'ui.dialogs.messagebox',
    'ui.tooltip',
    'ui.forms',
]);

$obKpi = new Kpi();

if ($_REQUEST['do'] == 'update') {
    foreach ($_REQUEST['ADD'] as $row) {
        $row['UF_PROJECT'] = $arParams['GROUP_ID'];
        $obKpi->add($row);
    }
    foreach ($_REQUEST['UPDATE'] as $id => $row) {
        $obKpi->update($id, $row);
    }
    foreach ($_REQUEST['DELETE'] as $id) {
        $obKpi->delete($id);
    }
    LocalRedirect($APPLICATION->GetCurPageParam());
}

$arRows = $obKpi->getRows($arParams['GROUP_ID']);

$arPerms = CSocNetUserToGroup::InitUserPerms($USER->GetID(), $arGroupInfo, CSocNetUser::IsCurrentUserModuleAdmin());

if (!$arPerms['UserIsMember'] && !$USER->IsAdmin()) {
    ShowError('Доступ запрещён');
    exit;
}

$bCanEdit = (
    in_array($arPerms['UserRole'], [SONET_ROLES_OWNER, SONET_ROLES_MODERATOR]) ||
    $USER->IsAdmin() ||
    in_array($USER->GetID(), [41, 107, 570])
);

?>
<form method="POST" id="kpi-form">
    <input type="hidden" name="do" value="update" />
    <table class="table table-bordered kpi-table">
        <thead>
            <tr>
                <th>Название KPI</th>
                <th>Описание</th>
                <th>Текущее значение</th>
                <th>Целевое значение</th>
                <th>Процент достижения</th>
                <?if ($bCanEdit) : ?>
                <th>&nbsp;</th>
                <? endif; ?>
            </tr>
        </thead>

        <tbody>
            <? foreach ($arRows as $row) : ?>
            <tr class="kpi-row kpi-row-<?=$row['ID']?>" data-id="<?=$row['ID']?>">
                <td class="kpi-row-name">
                    <?if ($bCanEdit) : ?>
                        <input data-id="UF_NAME" name="UPDATE[<?=$row['ID']?>][UF_NAME]" value="<?=$row['UF_NAME']?>" class="form-control" required />
                    <? else : ?>
                        <?=$row['UF_NAME']?>
                    <? endif; ?>
                </td>
                <td class="kpi-row-description">
                    <?if ($bCanEdit) : ?>
                        <textarea data-id="UF_DESCRIPTION" name="UPDATE[<?=$row['ID']?>][UF_DESCRIPTION]" class="form-control" rows="1"><?=$row['UF_DESCRIPTION']?></textarea>
                    <? else : ?>
                        <?=$row['UF_DESCRIPTION']?>
                    <? endif; ?>
                </td>
                <td class="kpi-row-current">
                    <?if ($bCanEdit) : ?>
                        <input data-id="UF_CURRENT" name="UPDATE[<?=$row['ID']?>][UF_CURRENT]" value="<?=$row['UF_CURRENT']?>" class="form-control js-kpi-current-value" required />
                    <? else : ?>
                        <?=$row['UF_CURRENT']?>
                    <? endif; ?>
                </td>
                <td class="kpi-row-target">
                    <?if ($bCanEdit) : ?>
                        <input data-id="UF_TARGET" name="UPDATE[<?=$row['ID']?>][UF_TARGET]" value="<?=$row['UF_TARGET']?>" class="form-control js-kpi-target-value" required />
                    <? else : ?>
                        <?=$row['UF_TARGET']?>
                    <? endif; ?>
                </td>
                <td class="kpi-row-percent">
                    <?=$row['PERCENT']?>%
                </td>
                <?if ($bCanEdit) : ?>
                <td class="kpi-row-buttons">
                    <button class="ui-btn ui-btn-sm ui-btn-icon-remove ui-btn-danger js-kpi-row-remove" title="Удалить строку"></button>
                    <button class="ui-btn ui-btn-sm ui-btn-icon-business ui-btn-success js-kpi-row-restore d-none" title="Вернуть строку"></button>
                </td>
                <? endif; ?>
            </tr>
            <? endforeach; ?>
            <?if ($bCanEdit) : ?>
            <tr class="kpi-row kpi-row-template d-none" data-id="0">
                <td class="kpi-row-name">
                    <input data-id="UF_NAME" class="form-control js-required" />
                </td>
                <td class="kpi-row-description">
                    <textarea data-id="UF_DESCRIPTION" class="form-control" rows="1"></textarea>
                </td>
                <td class="kpi-row-current">
                    <input data-id="UF_CURRENT" class="form-control js-kpi-current-value js-required" />
                </td>
                <td class="kpi-row-target">
                    <input data-id="UF_TARGET" class="form-control js-kpi-target-value js-required"  />
                </td>
                <td class="kpi-row-percent"></td>
                <td class="kpi-row-buttons">
                    <button class="ui-btn ui-btn-sm ui-btn-icon-remove ui-btn-danger js-kpi-row-remove" title="Удалить строку"></button>
                    <button class="ui-btn ui-btn-sm ui-btn-icon-business ui-btn-success js-kpi-row-restore d-none" title="Вернуть строку"></button>
                </td>
            </tr>
            <tr>
                <td colspan="6">
                    <button type="submit" class="ui-btn ui-btn-icon-start ui-btn-success">Сохранить</button>
                    <button class="ui-btn ui-btn-icon-add ui-btn-primary js-kpi-row-add">Добавить строку</button>
                </td>
            </tr>
            <? endif; ?>
        </tbody>
    </table>
</form>
