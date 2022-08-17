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

$APPLICATION->SetTitle('Отчет по руководителям проектов');

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
}
// $arComplexity[0] = 'Без категории';
$arComplexityCount = [];
$arResult = [
    107 => [], // Надежда Коняева
    566 => [], // Евгения Гаврилина 
];
foreach (array_keys($arResult) as $uId) {
    $arUserData = $GLOBALS['userFields']($uId);
    $arResult[ $uId ] = [
        'OWNER_ID'              => $uId,
        'OWNER_NAME'            => implode('&nbsp;', [$arUserData['LAST_NAME'], $arUserData['NAME']]),
        'TASKS_COUNT'           => 0,
        'TASKS_CLOSED'          => 0,
        'TASKS_EXPIRE'          => 0,
        'TASKS_EXPIRE_REAL'     => 0,
        'TASKS_EXPIRE_REAL_IDS' => [],
    ];
}
$res = CIntranetUtils::getDepartmentEmployees([100], true);
while ($row = $res->Fetch()) {
    $arResult[ $row['ID'] ] = [
        'OWNER_ID'              => $row['ID'],
        'OWNER_NAME'            => implode('&nbsp;', [$row['LAST_NAME'], $row['NAME']]),
        'TASKS_COUNT'           => 0,
        'TASKS_CLOSED'          => 0,
        'TASKS_EXPIRE'          => 0,
        'TASKS_EXPIRE_REAL'     => 0,
        'TASKS_EXPIRE_REAL_IDS' => [],
    ];

    $resTasks = CTasks::GetList(
        [],
        [
            'RESPONSIBLE_ID' => $row['ID'],
            [
                'LOGIC' => 'AND',
                '>=CLOSED_DATE' => $obDateStart->format('d.m.Y H:i:s'),
                '<=CLOSED_DATE' => $obDateFinish->format('d.m.Y H:i:s'),
            ]
        ]
    );
    while ($rowTasks = $resTasks->Fetch()) {
        if (!in_array($rowTasks['REAL_STATUS'], [4,5])) {
            continue;
        }
        $arResult[ $rowTasks['RESPONSIBLE_ID'] ]['TASKS_CLOSED']++;
        if (!empty($rowTasks['DEADLINE'])) {
            if (strtotime($rowTasks['DEADLINE']) < strtotime($rowTasks['CLOSED_DATE'])) {
                $arResult[ $rowTasks['RESPONSIBLE_ID'] ]['TASKS_EXPIRE']++;
            }
        }

        // Достать из истории изменения статуса
        $oTaskLog = LogTable::getList([
            'filter' => [
                'TASK_ID'   => $rowTasks['ID'],
                'FIELD'     => 'STATUS',
            ],
            'order' => [
                'ID' => 'ASC'
            ]
        ]);
        $arStatusHistory = [];
        while ($rowLog = $oTaskLog->fetch()) {
            $arStatusHistory[ $rowLog['TO_VALUE'] ] = $rowLog['CREATED_DATE']->format('d.m.Y H:i:s');
        }

        $dateComplete = null;
        if (isset($arStatusHistory[4])) {
            $dateComplete = new DateTimeImmutable($arStatusHistory[4]);
        } elseif (isset($arStatusHistory[5])) {
            $dateComplete = new DateTimeImmutable($arStatusHistory[5]);
        }

        if ($dateComplete >= $obDateStart && $dateComplete <= $obDateFinish) {
            if (!is_null($dateComplete) && !empty($rowTasks['DEADLINE'])) {
                $deadline = new DateTimeImmutable($rowTasks['DEADLINE']);

                if ($dateComplete > $deadline) {
                    $arResult[ $rowTasks['RESPONSIBLE_ID'] ]['TASKS_EXPIRE_REAL']++;
                    $arResult[ $rowTasks['RESPONSIBLE_ID'] ]['TASKS_EXPIRE_REAL_IDS'][] = $rowTasks['ID'];
                }
            }
        }
    }
}

$res = CTasks::GetList([], ['GROUP_ID' => 612, 'PARENT_ID' => false]);
while ($row = $res->Fetch()) {
    if ($row['REAL_STATUS'] < 0 || $row['REAL_STATUS'] > 4) {
        continue;
    }
    if (!array_key_exists($row['RESPONSIBLE_ID'], $arResult)) {
        continue;
    }
    $arResult[ $row['RESPONSIBLE_ID'] ]['TASKS_COUNT']++;
}

foreach ($arGroupInfo as $arGroup) {
    if (!array_key_exists($arGroup['OWNER_ID'], $arResult)) {
        continue;
    }

    if (empty($arGroup['UF_COMPLEXITY'])) {
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

    if (!empty($arGroup['PROJECT_DATE_FINISH']) && $arGroup['UF_COMPLEXITY'] != 387) {
        if (strtotime($arGroup['PROJECT_DATE_FINISH']) < time()) {
            if ((int)ceil(array_sum($arPercent)) >= 100) {
                continue;
            }
        }
    }

    $arResult[ $arGroup['OWNER_ID'] ]['COMPLEXITY_' . $arGroup['UF_COMPLEXITY'] ][ $arGroup['ID'] ] = [ $arGroup['ID'] ];

    $expire = 0;
    foreach ($arTasksPercent as $rowTasks) {
        // Достать из истории изменения статуса
        $oTaskLog = LogTable::getList([
            'filter' => [
                'TASK_ID' => $rowTasks['ID'],
                'FIELD' => 'STATUS',
            ],
            'order' => [
                'ID' => 'ASC'
            ]
        ]);
        $arStatusHistory = [];
        while ($rowLog = $oTaskLog->fetch()) {
            $arStatusHistory[ $rowLog['TO_VALUE'] ] = $rowLog['CREATED_DATE']->format('d.m.Y H:i:s');
        }

        $dateComplete = null;
        if (isset($arStatusHistory[4])) {
            $dateComplete = new DateTimeImmutable($arStatusHistory[4]);
        } elseif (isset($arStatusHistory[5])) {
            $dateComplete = new DateTimeImmutable($arStatusHistory[5]);
        }

        if (is_null($dateComplete) && !empty($rowTasks['DEADLINE'])) {
            $deadline = new DateTimeImmutable($rowTasks['DEADLINE']);

            if ((new DateTimeImmutable()) > $deadline) {
                $expire++;
            }
        }
    }

    $arResult[ $arGroup['OWNER_ID'] ]['PROJECTS'][ $arGroup['ID'] ] = [
        'ID'            => $arGroup['ID'],
        'NAME'          => $arGroup['NAME'],
        'CATEGORY_NAME' => $arComplexity[ (int)$arGroup['UF_COMPLEXITY'] ],
        'CATEGORY_ID'   => $arGroup['UF_COMPLEXITY'],
        'EXPIRE'        => $expire,
        'DEVIATION'     => ProjectInitiative::calcProjectDeviation($arGroup['ID']),
        'PERCENT'       => array_sum($arPercent),
        'FINISH'        => !empty($arGroup['PROJECT_DATE_FINISH']) ? date('d.m.Y', strtotime($arGroup['PROJECT_DATE_FINISH'])) : 'Без срока',
    ];
}

$arTotal = [];
foreach ($arResult as $userId => $row) {
    unset(
        $row['OWNER_ID'],
        $row['OWNER_NAME'],
        $row['PROJECTS'],
        $row['TASKS_EXPIRE_REAL_IDS']
    );
    foreach ($arComplexity as $complexKey => $complexValue) {
        $arResult[ $userId ]['COMPLEXITY_' . $complexKey ] = count($row['COMPLEXITY_' . $complexKey ]);
        $row['COMPLEXITY_' . $complexKey ] = (int)count($row['COMPLEXITY_' . $complexKey ]);
        if (!isset($arTotal['COMPLEXITY_' . $complexKey ])) {
            $arTotal['COMPLEXITY_' . $complexKey ] = 0;
        }
        $arTotal['COMPLEXITY_' . $complexKey ] += $row['COMPLEXITY_' . $complexKey ];
    }
    if (array_sum($row) <= 0) {
        unset($arResult[ $userId ]);
    }
}

/*
 * @see https://corp.tularegion.local/company/personal/user/2440/tasks/task/view/67010/
 */
unset($arResult[5244]);
/*
 * Надежда Коняева, [15.09.21 12:13] Привет! Убери, пжл , Раева из отчёта по руководителям проектов
 */
unset($arResult[5815]);
/*
 * [08.11.2021] Карташов Евгений попросил исключить его из отчёта руководителей
 */
unset($arResult[3707]);
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
        <div class="col-7">
            <input
                class="ui-btn ui-btn-primary"
                name="managers"
                value="Фильтр"
                type="submit" />
        </div>
    </div>
</form>
<table class="table table-bordered text-center">
    <thead>
        <tr>
            <th rowspan="2" colspan="2" class="sorter" data-index="1" data-class=".sorting" data-reinit="true">ФИО</th>
            <th colspan="<?=count($arComplexity);?>">Проекты</th>
            <th rowspan="2" class="sorter" data-index="6" data-class=".sorting" data-reinit="true">Кол-во задач в&nbsp;проработке</th>
            <th rowspan="2" class="sorter" data-index="7" data-class=".sorting" data-reinit="true">Закрыто задач всего</th>
            <th rowspan="2" class="sorter" data-index="8" data-class=".sorting" data-reinit="true">Закрыто задач с&nbsp;просрочкой</th>
        </tr>
        <tr>
            <?
            $i = 2;
            foreach ($arComplexity as $complexKey => $complexValue) : ?>
            <th class="sorter" data-index="<?=($i++)?>" data-class=".sorting">Кол-во проектов <?=$complexKey>0?'категории ':''?><?=$complexValue;?></th>
            <?endforeach;?>
        </tr>
    </thead>
    <tbody>
        <?foreach ($arResult as $row) : ?>
        <tr class="sorting">
            <td><a class="ui-btn ui-btn-xs ui-btn-primary ui-btn-icon-add js-collapse-table" data-id="<?=$row['OWNER_ID']?>"></a></td>
            <td><?=$row['OWNER_NAME']?></td>
            <?foreach ($arComplexity as $complexKey => $complexValue) : ?>
            <td><?=(int)$row['COMPLEXITY_' . $complexKey ]?></td>
            <?endforeach;?>
            <td data-sorttext="<?=$row['TASKS_COUNT']?>">
                <a
                    href="/workgroups/group/612/tasks/?STATUS[]=2&STATUS[]=3&STATUS[]=4&RESPONSIBLE_ID=<?=$row['OWNER_ID']?>&apply_filter=Y"
                    target="_blank"
                    title="Количество открытых (не отложенных) задач из группы 'Проектные инициативы'">
                    <?=$row['TASKS_COUNT']?>
                </a>
            </td>
            <td data-sorttext="<?=$row['TASKS_CLOSED'];?>">
                <a
                    href="/company/personal/user/<?=$row['OWNER_ID']?>/tasks/?CLOSED_DATE_datesel=RANGE&CLOSED_DATE_from=<?=date('d.m.Y', $tsStart);?>&CLOSED_DATE_to=<?=date('d.m.Y', $tsFinish);?>&RESPONSIBLE_ID=<?=$row['OWNER_ID']?>&apply_filter=Y"
                    target="_blank"
                    title="Сколько всего задач закрыто за период"
                    >
                    <?=$row['TASKS_CLOSED'];?>
                </a>
            </td>
            <?/*
            <td data-sorttext="<?=$row['TASKS_EXPIRE'];?>">
                <a
                    href="/company/personal/user/<?=$row['OWNER_ID']?>/tasks/?CLOSED_DATE_datesel=RANGE&CLOSED_DATE_from=<?=date('d.m.Y', $tsStart);?>&CLOSED_DATE_to=<?=date('d.m.Y', $tsFinish);?>&RESPONSIBLE_ID=<?=$row['OWNER_ID']?>&PARAMS[]=OVERDUED&apply_filter=Y"
                    target="_blank"
                    title="Сколько всего задач закрыли с просрочкой за период"
                    >
                    <?=$row['TASKS_EXPIRE'];?>
                </a>
            </td>
            */?>
            <td data-sorttext="<?=$row['TASKS_EXPIRE_REAL'];?>">
                <a href="#" class="js-task-list" data-id="<?=implode(',', $row['TASKS_EXPIRE_REAL_IDS'])/*CUtil::PhpToJSObject($row['TASKS_EXPIRE_REAL_IDS'])*/?>">
                    <?=$row['TASKS_EXPIRE_REAL'];?>
                </a>
            </td>
        </tr>
        <tr class="d-none user-detail-table" id="user-table-<?=$row['OWNER_ID']?>">
           <td colspan="9">
               <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>ФИО</th>
                            <th class="sorter" data-class=".sorting<?=$row['OWNER_ID']?>">Название проекта</th>
                            <th class="sorter" data-class=".sorting<?=$row['OWNER_ID']?>">Категория проекта</th>
                            <th class="sorter" data-class=".sorting<?=$row['OWNER_ID']?>">KPI</th>
                            <th class="sorter" data-class=".sorting<?=$row['OWNER_ID']?>">Отклонение от плана</th>
                            <th class="sorter" data-class=".sorting<?=$row['OWNER_ID']?>">Текущие просроченные задачи</th>
                            <th class="sorter" data-class=".sorting<?=$row['OWNER_ID']?>">Процент реализации</th>
                            <th class="sorter" data-class=".sorting<?=$row['OWNER_ID']?>">Дата окончания</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?foreach ($row['PROJECTS'] as $project) : ?>
                        <?
                        $arFeaturesTmp = [];
                        $dbResultTmp = \CSocNetFeatures::getList(
                            array(),
                            array("ENTITY_ID" => $project['ID'], "ENTITY_TYPE" => SONET_ENTITY_GROUP)
                        );
                        while ($arResultTmp = $dbResultTmp->GetNext()) {
                            $arFeaturesTmp[ $arResultTmp["FEATURE"] ] = $arResultTmp;
                        }
                        ?>
                        <tr class="sorting<?=$row['OWNER_ID']?>">
                            <td><?=$row['OWNER_NAME']?></td>
                            <td><a href="/citto/workgroups/group/<?=$project['ID']?>/" target="_blank"><?=$project['NAME']?></a></td>
                            <td><?=$project['CATEGORY_NAME']?></td>
                            <td>
                                <?
                                if ($arFeaturesTmp['group_kpi']['ACTIVE'] == 'Y') {
                                    echo (new ProjectInitiative\Kpi())->calc($project['ID']) . '%';
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                            <td title="<?=$project['DEVIATION']['tooltip_html']?>" data-toggle="tooltip" data-placement="top" data-html="true"><?=$project['DEVIATION']['sum']?></td>
                            <td>
                                <a
                                    href="/citto/workgroups/group/<?=$project['ID']?>/tasks/?STATUS[]=2&STATUS[]=3&DEADLINE_datesel=RANGE&DEADLINE_to=<?=date('d.m.Y');?>&apply_filter=Y"
                                    target="_blank"
                                    title="Текущие просроченные задачи"
                                    >
                                    <?=$project['EXPIRE'];?>
                                </a>
                            </td>
                            <td data-sorttext="<?=sprintf("%'03d", (int)$project['PERCENT'])?>">
                                <?=number_format($project['PERCENT'], 2, ',', '') . '%'?>
                            </td>
                            <td data-sorttext="<?=$project['FINISH']=='Без срока'?999999999:strtotime($project['FINISH'])?>">
                                <?=$project['FINISH']?>
                            </td>
                        </tr>
                        <?endforeach;?>
                    </tbody>
                </table>
           </td>
        </tr>
        <?endforeach;?>
        <tr>
            <td></td>
            <td>Всего проектов</td>
            <?foreach ($arComplexity as $complexKey => $complexValue) : ?>
            <td><?=(int)$arTotal['COMPLEXITY_' . $complexKey ]?></td>
            <?endforeach;?>
            <td colspan="3"></td>
        </tr>
    </tbody>
</table>
