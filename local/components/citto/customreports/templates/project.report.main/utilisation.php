<?php

use Bitrix\Main\Loader;
use Citto\Tasks\ProjectInitiative;
use Bitrix\Tasks\Internals\Task\LogTable;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) {
    die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$APPLICATION->SetTitle('Отчет по утилизации (ШЕ)');

Loader::includeModule('iblock');
Loader::includeModule('socialnetwork');
Loader::includeModule('tasks');
Loader::IncludeModule('forum');
Loader::IncludeModule('intranet');
CJSCore::Init("sidepanel");

$tsStart     = strtotime($_REQUEST['DATE_START'] ?? date('01.01.Y'));
$tsFinish    = strtotime($_REQUEST['DATE_FINISH'] ?? date('31.12.Y'));

$obDateStart    = new DateTimeImmutable(date('d.m.Y 00:00:00', $tsStart));
$obDateFinish   = new DateTimeImmutable(date('d.m.Y 23:59:59', $tsFinish));

$bRequireFonProjects = (!isset($_REQUEST['WITH_FON']) || $_REQUEST['WITH_FON'] == 'Y');

$arGroupInfo = [];
$resGroup = CSocNetGroup::GetList(
    ['DATE_CREATE' => 'ASC'],
    [
        'ACTIVE' => 'Y'
    ],
    false,
    false,
    ['*', 'UF_*']
);
while ($arGroup = $resGroup->Fetch()) {
    $arGroupInfo[ $arGroup['ID'] ] = $arGroup;
}

$arGroupInfo[800]['OWNER_ID'] = 5940; // Дицкова Ирина
$arGroupInfo[815]['OWNER_ID'] = 107; // Надежда Коняева

$arComplexity = [];
$iFonId = 0;
$res = CUserFieldEnum::GetList(
    [
        'DEF'  => 'DESC',
        'SORT' => 'ASC'
    ],
    [
        'USER_FIELD_ID' => 1413
    ]
);
while ($row = $res->GetNext()) {
    $arComplexity[ $row['ID'] ] = $row['VALUE'];
    if (0 === mb_strpos($row['VALUE'], 'D')) {
        $iFonId = $row['ID'];
    }
}

$arResult = [];
$res = CIntranetUtils::getDepartmentEmployees([100], true);
while ($row = $res->Fetch()) {
    $arResult[ $row['ID'] ] = [
        'OWNER_ID'      => $row['ID'],
        'OWNER_NAME'    => implode('&nbsp;', [$row['LAST_NAME'], $row['NAME']]),
        'PROJECTS'      => [],
        'FULL'          => 0,
    ];
}

foreach ($arGroupInfo as $arGroup) {
    if (!array_key_exists($arGroup['OWNER_ID'], $arResult)) {
        continue;
    }

    if (empty($arGroup['UF_UTILISATION_SU'])) {
        continue;
    }

    if (!$bRequireFonProjects && $arGroup['UF_COMPLEXITY'] == $iFonId) {
        continue;
    }

    $arTasksPercent = ProjectInitiative::calcTasksPercent($arGroup['ID']);
    $arPercent  = [
        0 => 0
    ];

    foreach ($arTasksPercent as $arTask) {
        if (!is_null($arTask['CLOSED_DATE_DT'])) {
            $arPercent[ $arTask['ID'] ] = $arTask['MAX_PERCENT'];
        }
    }

    if (!empty($arGroup['PROJECT_DATE_START'])) {
        $arGroup['PROJECT_DATE_START'] = new DateTimeImmutable($arGroup['PROJECT_DATE_START']);
    }

    if (!empty($arGroup['PROJECT_DATE_FINISH'])) {
        $arGroup['PROJECT_DATE_FINISH'] = new DateTimeImmutable($arGroup['PROJECT_DATE_FINISH']);
        if ($arGroup['PROJECT_DATE_FINISH'] < $obDateStart) {
            if ((int)ceil(array_sum($arPercent)) >= 100 || empty($arTasksPercent)) {
                continue;
            }
        }
        if ($arGroup['PROJECT_DATE_START'] > $obDateFinish) {
            continue;
        }
    }

    $arGroup['ROW_TYPE'] = 'Проект';
    $arGroup['LINK'] = $arGroup['SITE_ID']=='s1' ? '/' : '/citto/' . 'workgroups/group/' . $arGroup['ID'] . '/';
    $arGroup['UF_UTILISATION_SU'] = (float)$arGroup['UF_UTILISATION_SU'];
    if (empty($arGroup['UF_UTILISATION_SU'])) {
        continue;
    }
    $arResult[ $arGroup['OWNER_ID'] ]['PROJECTS'][] = $arGroup;
    $arResult[ $arGroup['OWNER_ID'] ]['FULL'] += $arGroup['UF_UTILISATION_SU'];
}

$res = CTasks::GetList([], ['GROUP_ID' => 612, 'PARENT_ID' => false]);
while ($row = $res->Fetch()) {
    if ($row['REAL_STATUS'] < 0 || $row['REAL_STATUS'] > 4) {
        continue;
    }
    if (!array_key_exists($row['RESPONSIBLE_ID'], $arResult)) {
        continue;
    }

    $arBizProc = ProjectInitiative::getBizProcByTaskId($row['ID']);
    if (!empty($arBizProc)) {
        $arUserFields = \Bitrix\Tasks\Util\UserField\Task::getScheme($row['ID']);

        if (empty($arUserFields['UF_UTILISATION_SU']['VALUE'])) {
            continue;
        }

        $bAdd = false;
        $arTasksPercent = ProjectInitiative::calcTasksPercent(0, $row['ID']);
        foreach ($arTasksPercent as $task) {
            if ($task['DEADLINE_DT'] >= $obDateStart) {
                $bAdd = true;
            }
            if ($task['CREATED_DATE_DT'] >= $obDateFinish) {
                $bAdd = false;
            }
        }
        if ($bAdd) {
            $arProject = [];
            $arProject['ROW_TYPE'] = 'Проектная инициатива';
            $arProject['NAME'] = $row['TITLE'];
            $arProject['UF_UTILISATION_SU'] = (float)$arUserFields['UF_UTILISATION_SU']['VALUE'];
            $arProject['LINK'] = '/workgroups/group/' . ProjectInitiative::$groupId . '/tasks/task/view/' . $row['ID'] . '/';
            $arResult[ $row['RESPONSIBLE_ID'] ]['FULL'] += $arProject['UF_UTILISATION_SU'];
            $arResult[ $row['RESPONSIBLE_ID'] ]['PROJECTS'][] = $arProject;
        }
    }
}

uasort(
    $arResult,
    function ($a, $b) {
        return strnatcmp($a['OWNER_NAME'], $b['OWNER_NAME']);
    }
);
$arRukl = $arResult[107];
unset($arResult[107]);
$arResult = array_merge(
    [107 => $arRukl],
    $arResult
);

/*
В выбранный период выводить:
- проекты, у которых дата окончания попадает в выбранный период или больше нее;
- проектные инициативы, у которых хотя бы один интервал (разница между датой постановки и крайним сроком подзадач «инициация» или «планирование») пересекается с выбранным периодом.
*/
?>
<form method="get" action="/workgroups/group/612/reporting/" class="m-3">
    <div class="row">
        <div class="col-2 ui-ctl ui-ctl-textbox ui-ctl-inline">
            <input
                class="ui-ctl-element"
                name="DATE_START"
                value="<?=date('d.m.Y', $tsStart);?>"
                placeholder="Начало интервала"
                onclick="BX.calendar({node:this,field:this,bTime:true});"
                required />
        </div>
        <div class="col-2 ui-ctl ui-ctl-textbox ui-ctl-inline">
            <input
                class="ui-ctl-element"
                name="DATE_FINISH"
                value="<?=date('d.m.Y', $tsFinish);?>"
                placeholder="Конец интервала"
                onclick="BX.calendar({node:this,field:this,bTime:true});"
                required />
        </div>
        <div class="col-3 ui-ctl ui-ctl-textbox ui-ctl-inline">
            <label class="ui-ctl ui-ctl-radio mh-0 my-1">
                <input type="radio" class="ui-ctl-element" name="WITH_FON" value="Y" <?=(empty($_REQUEST['WITH_FON']) || $_REQUEST['WITH_FON'] == 'Y') ? 'checked' : ''?> />
                <div class="ui-ctl-label-text">С учетом фоновых проектов</div>
            </label>
            <label class="ui-ctl ui-ctl-radio mh-0 my-1">
                <input type="radio" class="ui-ctl-element" name="WITH_FON" value="N" <?=($_REQUEST['WITH_FON'] == 'N') ? 'checked' : ''?> />
                <div class="ui-ctl-label-text">Без учета фоновых проектов</div>
            </label>
        </div>
        <div class="col-4">
            <input
                class="ui-btn ui-btn-primary"
                name="utilisation"
                value="Фильтр"
                type="submit" />
        </div>
    </div>
</form>

<table class="table table-bordered text-center">
    <thead>
        <tr>
            <th class="sorter-no" data-index="1" data-class=".sorting" data-reinit="true">ФИО</th>
            <th class="sorter-no" data-index="2" data-class=".sorting" data-reinit="true">Тип</th>
            <th class="sorter-no" data-index="3" data-class=".sorting" data-reinit="true">Название</th>
            <th class="sorter-no" data-index="4" data-class=".sorting" data-reinit="true">Сложность</th>
            <th class="sorter-no" data-index="5" data-class=".sorting" data-reinit="true">Утилизация&nbsp;(ШЕ)</th>
            <th class="sorter-no" data-index="6" data-class=".sorting" data-reinit="true">Итог&nbsp;(ШЕ)</th>
        </tr>
    </thead>
    <tbody>
        <?
        foreach ($arResult as $row) {
            $bFirst = true;

            foreach ($row['PROJECTS'] as $project) {
                if ($bFirst) {
                    ?>
                    <tr class="sorting bt-2">
                    <td rowspan="<?=count($row['PROJECTS'])?>"><?=$row['OWNER_NAME']?></td>
                    <?
                } else {
                    ?>
                    <tr class="sorting">
                    <?
                }
                ?>
                <td><?=$project['ROW_TYPE']?></td>
                <td><a href="<?=$project['LINK']?>" target="_blank"><?=$project['NAME']?></a></td>
                <td><?=$arComplexity[ (int)$project['UF_COMPLEXITY'] ]?></td>
                <td><?=$project['UF_UTILISATION_SU']?></td>
                <?
                if ($bFirst) {
                    ?>
                    <td rowspan="<?=count($row['PROJECTS'])?>"><?=$row['FULL']?></td>
                    </tr>
                    <?
                    $bFirst = false;
                }
                ?>
                </tr>
                <?
            }
        }
        ?>
    </tbody>
</table>
