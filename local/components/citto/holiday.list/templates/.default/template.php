<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

global $USER, $declOfNum;
$bIsMinistr = ($arResult['USERS'][ $arResult['USER_ID'] ]['ROLE'] == 'ASSISTANT');
$arMonths = array('', GetMessage("BITRIX_PLANNER_ANVARQ"), GetMessage("BITRIX_PLANNER_FEVRALQ"), GetMessage("BITRIX_PLANNER_MART"), GetMessage("BITRIX_PLANNER_APRELQ"), GetMessage("BITRIX_PLANNER_MAY"), GetMessage("BITRIX_PLANNER_IUNQ"), GetMessage("BITRIX_PLANNER_IULQ"), GetMessage("BITRIX_PLANNER_AVGUST"), GetMessage("BITRIX_PLANNER_SENTABRQ"), GetMessage("BITRIX_PLANNER_OKTABRQ"), GetMessage("BITRIX_PLANNER_NOABRQ"), GetMessage("BITRIX_PLANNER_DEKABRQ"));
?>


<div class="row mb-20 vacation-head">
    <div class="offset-md-6 col-md-6 text-right" id="sel_year">
        <? for ($iYear = date('Y'); $iYear <= date('Y') + 1; $iYear++): ?>
            <span>
            <? if ($iYear == $arResult['YEAR']): ?>
                <b><?= $iYear ?></b>
            <? else: ?>
                <? $sUrl = str_replace('&year=' . $arResult['YEAR'], '', $arResult['BASE_URL']) . "&year=$iYear" ?>
                <a href="<?= $sUrl ?>"><?= $iYear ?></a>
            <? endif; ?>
            </span>
        <? endfor; ?>
    </div>
    <div class="col-md-12 mt-3 alert-heads"
        <? if ($arResult['THIS_HEADS']['ACTIVE'] && !$arResult['THIS_HEADS']['UNACTIVE']) : ?>
            style="display: none"
        <? endif; ?>
    >
        <? if (!$arResult['THIS_HEADS']['ACTIVE']): ?>
            <div class="alert alert-danger" role="alert">
                Необходимо выбрать руководителей
            </div>
        <? endif; ?>
        <? if ($arResult['THIS_HEADS']['UNACTIVE']): ?>
            <div class="alert alert-danger" role="alert">
                Необходимо заменить уволенных руководителей: <?= implode(';',
                    array_values($arResult['THIS_HEADS']['UNACTIVE'])) ?>
            </div>
        <? endif; ?>
    </div>
</div>

<div class="vacation-wrapper">
    <? if (empty($arResult['HOLIDAYS'])) : ?>
        <h5 class='text-center mt-2'>Вы не можете распланировать отпуск на <?= $arResult['YEAR'] ?> год. Выберите другой.</h5>
        <script>
            var jsonUsers = <?=json_encode($arResult['USERS'])?>;
            var holidaysJson = <?= json_encode($arResult['HOLIDAYS']); ?>;
        </script>
        <? return; ?>
    <? endif; ?>

    <div class="row mb-15">
        <div class="col-md-6">
            <div class="row mb-15">
                <div class="col-md-12">
                    <? if (count($arResult['DEPARTMENT_LIST']) > 1) : ?>
                        <select class="custom-select selectpicker form-control" data-live-search="true" onchange="RefreshList(this.value)">
                            <?
                            foreach ($arResult['DEPARTMENT_LIST'] as $f) {
                                $sClass = ($f['DEPTH_LEVEL']) <= 3 ? 'font-weight-bold' : '';
                                $sDepName = mb_substr(htmlspecialcharsbx($f['DEPTH_NAME']), 0, 150);
                                echo '<option class = "' . $sClass . '" value=' . $f['ID'] . ($arResult['DEPARTMENT_ID'] == $f['ID'] ? ' selected' : '') . '>' . $sDepName . '</option>';
                            }
                            ?>
                        </select>
                    <? else : ?>
                        <?= htmlspecialcharsbx(reset($arResult['DEPARTMENT_LIST'])); ?>
                    <? endif; ?>
                </div>
            </div>
            <div class="row">
                <? if ($arResult['USERCADRS'] || $arResult['ADMIN']) : ?>
                    <div class="col-md-4">
                        <input type="checkbox" id="id_zam" name="id_zam" onchange="GetZam(this.checked)" <?= ($_GET['getzam'] == 'true') ? 'checked' : '' ?>>
                        <label for="id_zam">Заместители губернатора</label>
                    </div>
                <? endif; ?>
                <? if (count($arResult['DEPARTMENT_LIST']) > 1) : ?>
                    <div class="col-md-4">
                        <input id="subdeps" type="checkbox" <? if ($_GET['recursive']) : ?>checked<? endif; ?>
                               onchange="document.location='<?= $arResult['BASE_URL'] ?>&amp;recursive=0&amp;recursive=' + (this.checked ? 1 : 0)"/>
                        <label for="subdeps">Показать подотделы</label>
                    </div>
                <? endif; ?>
                <div class="col-md-4">
                    <input type="checkbox" id="id_podved" name="id_podved"
                           onchange="HidePodved(this.checked)" <?= ($_GET['podved'] == 'true') ? 'checked' : '' ?>>
                    <label for="id_podved">Скрыть подведы</label>
                </div>
                <div class="col-md-6">
                    <? if (count($arResult['DELEGATIONS']) > 0) : ?>
                        <div id="delegation" class="float-right">
                            <form action="<?= $_SERVER["SCRIPT_NAME"] ?>" method="POST" name="selectDelegationForm" id="selectDelegationForm">
                                <select name="selectDelegation" id="selectDelegation" class="">
                                    <option value="none">Выберите пользователя, делегирующего Вам полномочия</option>
                                    <option value="<?= $USER->getID() ?>">Выбрать себя</option>
                                    <? foreach ($arResult['DELEGATIONS'] as $k => $v) : ?>
                                        <option value="<?= $k ?>" id=""><?= $v[0] ?></option>
                                    <? endforeach; ?>
                                </select>
                            </form>
                        </div>
                    <? endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div id="myHeads">
                <form id="js-heads">
                    <div class="ui-widget">
                        <input id="tags" class="input-find" placeholder="Выбор руководителей">
                        <label for="tags"></label>
                    </div>
                    <div class="heads tags">
                        <? foreach ($arResult['THIS_HEADS'] as $arHeads) : ?>
                            <? foreach ($arHeads as $iUserID => $sFIO) : ?>
                                <div class="item" data-head="<?= $iUserID ?>">
                                    <a href="#" class="title"><?= $sFIO ?></a>
                                    <a href="#" class="close">✕</a>
                                </div>
                            <? endforeach; ?>
                        <? endforeach; ?>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <? if (isset($arResult['USERS'])) : ?>
        <div class="row">
            <div class="col-md-3">
                <div class="mb-10">
                    <input type="text" id="find_employer" class="input-find" placeholder="Фильтр по сотруднику">
                    <label for="find_employer"></label>
                </div>
                <div class="mb-10">
                    <? if ($_GET['sort'] != 'ASC') : ?>
                        <a href="<?= $arResult['BASE_URL'] ?>&sort=ASC">Сортировка по алфавиту <i class="arrow-up"></i></a>
                    <? else : ?>
                        <a href="<?= $arResult['BASE_URL'] ?>&sort=other">Сортировка по должности <i class="arrow-up"></i></a>
                    <? endif; ?>
                </div>
                <div class="mb-10">
                    <a href="<?= $componentPath ?>/images/calendar/image.png" target="_blank">Производственный календарь</a>
                </div>
            </div>
            <div class="col-md-9 text-right">
                <? if ($arResult['EXPORT'] || $arResult['USERCADRS']) : ?>
                    <? if ($arResult['USERCADRS']) : ?>
                        <div id="violations" class="mb-10">
                            <a href="#"><i class="arrow-down"></i> Экспорт нарушений</a>
                        </div>
                    <? endif; ?>
                <? endif; ?>
                <? if ($arResult['EXPORT']): ?>
                    <div class="mb-10">
                        <a href="#" id="excel"><i class="arrow-down"></i> Экспорт таблицы</a>
                    </div>
                <? endif; ?>
                <? if ($_GET['getzam'] == 'true' && $_GET['recursive'] == 1 && ($arResult['USERCADRS'] || $arResult['ADMIN'])) : ?>
                    <div id="perzam" class="showorhide mb-10">
                        <a href="#">Пересечения ЗПГ</a>
                    </div>
                <? endif; ?>
                <? if ($_GET['getzam'] == 'true' && $_GET['recursive'] == 1 && ($arResult['USERCADRS'] || $arResult['ADMIN'])) : ?>
                    <div id="ministers" class="showorhide mb-10">
                        <a href="#">Члены правительства</a>
                    </div>
                <? endif; ?>
                <? if (count($arResult['MY_WORKERS']) > 0) : ?>
                    <div class="mb-10">
                        <? if (empty($_REQUEST['myworkers'])) : ?>
                            <a href="?myworkers=1">Мои подчинённые</a>
                        <? else : ?>
                            <a href="?myworkers=0">График отпусков</a>
                        <? endif; ?>
                    </div>
                <? endif; ?>
            </div>
        </div>
    <? endif; ?>

    <? if (isset($arResult['USERS'])) : ?>
        <div class="find_employers tags"></div>
        <div>
            <div class="outer mb-3 mt-3" id="calendar">
                <div class="inner">
                    <table class="table table-bordered table-vacation">
                        <thead>
                        <tr>
                            <th scope="col">
                                <span>Сотрудник</span>
                                <span class="showhideusers float-right">
                                <a href="#">Отобразить скрытых сотрудников (<span>0</span>)</a>
                            </span>
                            </th>
                            <? foreach ($arResult['monthsList'] as $key => $value): ?>
                                <? $arResult['daysList'][$key] = cal_days_in_month(CAL_GREGORIAN, $key, $arResult['YEAR']); ?>
                                <th scope="col" colspan= <?= $arResult['daysList'][$key]; ?>><?= $value ?></th>
                            <? endforeach; ?>
                        </tr>
                        <tr rowspan="2">
                            <th scope="col">Дни</th>
                            <? foreach ($arResult['monthsList'] as $key => $value) : ?>
                                <? $arResult['daysList'][$key] = cal_days_in_month(CAL_GREGORIAN, $key, $arResult['YEAR']); ?>
                                <? for ($i = 1; $i <= $arResult['daysList'][$key]; $i++) : ?>
                                    <? setlocale(LC_ALL, 'ru_RU.UTF-8');
                                    $date = strtotime($arResult['YEAR'] . '-' . $key . '-' . $i); ?>
                                    <? if (strftime('%a', $date) == 'Пн') : ?>
                                        <th class="table_day" colspan="7" scope="col"><?= strftime('%a', $date); ?>
                                            /<?= $i ?></th>
                                        <? $i = $i + 6; ?>
                                    <? elseif ($key == 1) : ?>
                                        <? $minus = date('w', strtotime($arResult['YEAR'] . '-' . $key . '-' . $i));
                                        if ($minus == 0) $minus = 7; ?>
                                        <? $colspan = 8 - $minus; ?>
                                        <?
                                        if (($colspan + $i) > $arResult['daysList'][$key]) {
                                            $i = $arResult['daysList'][$key];
                                        };
                                        ?>
                                        <th class="table_day" colspan="<?= $colspan ?>"
                                            scope="col"><?= strftime('%a', $date); ?>/<?= $i ?></th>
                                        <? $i = $i + $colspan - 1; ?>
                                    <? endif; ?>
                                <? endfor; ?>
                            <? endforeach; ?>
                        </tr>
                        </thead>
                        <tbody>
                        <? $numEach = 1; ?>
                        <? foreach ($arResult['USERS'] as $u => $item) : ?>
                            <? if ($item['ACTIVE'] != 'Y') continue; ?>

                            <? if (in_array($item['ID'], $arResult['PROBLEM_USERS'])) : ?>
                                <tr>
                                    <th><?="{$item['LAST_NAME']} {$item['NAME']} {$item['SECOND_NAME']}"?></th>
                                    <td colspan="365">Планирование недоступно. Обратитесь в отдел кадров либо к администратору</td>
                                    <? continue; ?>
                                </tr>
                            <? endif; ?>

                            <?
                            $boolCurrentUser = (isset($_GET['set_user_id']) && $arResult['ADMIN'] && $arResult['SEL_USER_ID'] == $item['ID']);
                            if (
                                empty($_GET['set_user_id']) && $arResult['USER_ID'] == $item['ID'] ||
                                isset($_GET['set_user_id']) && $_GET['set_user_id'] == $item['ID']
                            ) {
                                $boolCurrentUser = true;
                            }
                            if ($item['day_left']>0) {
                                $arResult['SHOW_BUTTON'] = false;
                            }
                            ?>
                            <tr>
                                <th
                                        tooltip=""
                                    <?=($boolCurrentUser?'class="current-user"':'');?>
                                        scope="row"
                                        id="system_person_<?= $item['ID'] ?>"
                                >
                                    <a class="hideUser" href="#"></a>
                                    <?
                                    $bShowLink = (
                                        $item['ID'] == $arResult['USER_ID'] ||
                                        $arResult['SECRETARY'] ||
                                        in_array($arResult['USER_ID'], $arResult['USERS_CADRS']) ||
                                        in_array($arResult['USER_ID'], explode('|', $item["UF_THIS_HEADS"]))
                                    );
                                    ?>
                                    <?if ($bShowLink) : ?>
                                    <a
                                            class="fiouser"
                                            data-wstart="<?= $item['DETAIL'] ?>"
                                            href="<?= $arResult['BASE_URL'] ?>&set_user_id=<?= $item['ID'] ?>">
                                        <? endif; ?>
                                        <?="$numEach. {$item['LAST_NAME']} {$item['NAME']} {$item['SECOND_NAME']}"?>
                                        <?if ($bShowLink) : ?>
                                    </a>
                                <? endif; ?>
                                    <?
                                    $title = '';
                                    if ($bShowLink) {
                                        if (!empty($item['CURR_PERIOD'])) {
                                            $title .= date('d.m.Y', $item['CURR_PERIOD']['from_ts']) . ' - ' . date('d.m.Y', $item['CURR_PERIOD']['to_ts']) . ':';
                                            $free = $item['CURR_PERIOD']['free'];
                                            if ($item['CURR_PERIOD']['free'] < 0) {
                                                $free = 0;
                                            }
                                            $title .= ' ' . ($item['CURR_PERIOD']['free']+$item['CURR_PERIOD']['totalused']) . '/' . $free;
                                            $title .= PHP_EOL . PHP_EOL;
                                        }
                                        if (!empty($item['NEXT_PERIOD'])) {
                                            $title .= date('d.m.Y', $item['NEXT_PERIOD']['from_ts']) . ' - ' . date('d.m.Y', $item['NEXT_PERIOD']['to_ts']) . ':';
                                            $free = $item['NEXT_PERIOD']['free'];
                                            if ($item['NEXT_PERIOD']['free'] < 0) {
                                                $free = 0;
                                            }
                                            $title .= ' ' . ($item['NEXT_PERIOD']['free']+$item['NEXT_PERIOD']['totalused']) . '/' . $free;
                                            $title .= PHP_EOL . PHP_EOL;
                                        }

                                        if (!empty($title)) {
                                            $title = 'Доступно:' . PHP_EOL . PHP_EOL . $title;
                                        }
                                    }
                                    ?>
                                    <span class="usual-grey">
                                (дней: <span tooltip="<?=$title?>"><?= $item['total_days'] ?></span>
                                / <span class="left"><?= $item['day_left'] ?></span>)
                                </span>
                                    <? if ($item['UF_WORK_CROSS']): ?>
                                    совмещение
                                    <? endif; ?>
                                </th>
                                <?
                                $numEach++;
                                $jump = 0;
                                $class_vac = '';
                                $userDayLeft = 0;
                                $iCurrentDays = 0;
                                foreach ($arResult['daysList'] as $key => $value) : ?>
                                    <? for ($i = 1; $i <= $value; $i++) : ?>
                                        <?
                                        $date = strtotime($arResult["YEAR"] . '-' . $key . '-' . $i);
                                        $id = $item['ID'] == $arResult['SEL_USER_ID'] ? ' data-date="' . $date . '" id="day_' . $i . '"' : '';
                                        $style = '';
                                        if ($i == $value) {
                                            $style = 'border-right: 1px dashed #dee2e6;';
                                        }
                                        $u = $item['VACATION'][ mktime(0, 0, 0, $key, $i, $arResult['YEAR']) ];
                                        if (isset($u)) {
                                            $idRecord = $u['ID_RECORD'];
                                            $class_vac = $u['STATUS'];

                                            for ($x = 1; $x <= $u['PERIOD'] / 86400; $x++) {
                                                $holiday = '';
                                                if ($i == $value) {
                                                    $style = 'border-right: 1px dashed #dee2e6;';
                                                }

                                                $date = strtotime($arResult['YEAR'] . '-' . $key . '-' . $i);
                                                if (in_array($date, $arResult['HOLIDAYS'])) {
                                                    $holiday = 'bg-hol';
                                                }

                                                echo '<td tooltip = "'.$u['PREVIEW_TEXT'].'" data-rec-vacation="' . $idRecord . '" data-date="' . $date . '" style="' . $style . '" class="' . $holiday . ' ' . $class_vac . '"></td>';
                                                $iCurrentDays++;
                                                if ($value == $i) {
                                                    $i = 0;
                                                    $jump = ($u['PERIOD'] / 86400 - $x);
                                                    continue 3;
                                                }
                                                $i++;
                                            }
                                            $i--;
                                        } elseif ($jump > 0) {
                                            for ($x = 1; $x <= $jump; $x++) {
                                                $date = strtotime($arResult['YEAR'] . '-' . $key . '-' . $i);
                                                $style = '';
                                                if (in_array($date, $arResult['HOLIDAYS'])) {
                                                    $style = 'background:#FFB4B4;';
                                                }
                                                if (in_array($date, $arResult['WEEKENDS'])) {
                                                    $style = 'background:#F3D0FF;';
                                                }
                                                if (in_array($date, $arResult['SHORTDAYS'])) {
                                                    $style = 'background:#FFEEB4;';
                                                }

                                                if ($i == $value) {
                                                    $style = 'border-right: 1px dashed #dee2e6;';
                                                }
                                                echo '<td tooltip = "'.$u['PREVIEW_TEXT'].'" data-rec-vacation="' . $idRecord . '" data-date="' . $date . '" style="' . $style . '" class="' . $class_vac . '"></td>';
                                                $iCurrentDays++;
                                                $i++;
                                            }
                                            $i--;
                                            $jump = 0;
                                        } else {
                                            $date = strtotime($arResult['YEAR'] . '-' . $key . '-' . $i);
                                            $holiday = '';
                                            if (in_array($date, $arResult['HOLIDAYS'])) {
                                                $style .= 'background:#FFB4B4;';
                                            }
                                            if (in_array($date, $arResult['WEEKENDS'])) {
                                                $style .= 'background:#F3D0FF;';
                                            }
                                            if (in_array($date, $arResult['SHORTDAYS'])) {
                                                $style .= 'background:#FFEEB4;';
                                            }

                                            if ($i == 1 || date('D', $date) == 'Mon') {
                                                $d_period = 'from';
                                            } elseif ($i == $value || date('D', $date) == 'Sun') {
                                                $d_period = 'to';
                                            } else {
                                                $d_period = '';
                                            }

                                            if ($iCurrentDays > 0) {
                                                $sSpanDays = "<span class='i_days' style='left:calc(-5.5px*$iCurrentDays)!important;'>$iCurrentDays</span>";
                                                $iCurrentDays = 0;
                                            } else {
                                                $sSpanDays = '';
                                            }

                                            if ($boolCurrentUser) {
                                                $class = '';
                                                if (!$bIsMinistr && !empty($item['CURR_PERIOD'])) {
                                                    if ($item['day_left'] > 0) {
                                                        if (
                                                            $date >= $item['CURR_PERIOD']['from_ts'] &&
                                                            $date <= $item['CURR_PERIOD']['to_ts'] &&
                                                            (
                                                                $item['CURR_PERIOD']['req_free'] > 0 ||
                                                                $item['PERIOD_USED'] >= 14
                                                            )
                                                        ) {
                                                            $class = 'current-user';
                                                        }

                                                        if (!empty($item['NEXT_PERIOD']) || $item['CURR_REQ_FREE'] <= 0) {
                                                            if (
                                                                $item['CURR_REQ_FREE'] <= 0 ||
                                                                ($date >= $item['NEXT_PERIOD']['from_ts'] &&
                                                                    $date <= $item['NEXT_PERIOD']['to_ts'] &&
                                                                    $item['CURR_PERIOD']['totalused'] >= 28 &&
                                                                    $item['NEXT_PERIOD']['free'] > 0)
                                                            ) {
                                                                $class = 'current-user';
                                                            }
                                                        }

                                                        if ($date > $item['CURR_PERIOD']['to_ts'] && $item['CURR_PERIOD']['req_free'] == 0) {
                                                            $class = 'current-user';
                                                        }

                                                    }
                                                } else {
                                                    $class = 'current-user';
                                                }

                                                echo "<td style = '" . $style . "' class='$d_period $class " . date('D', $date) . "' data-week='" . date('W', $date) . '-' . $key . "' " . $id . ">$sSpanDays</td>";
                                            } else {
                                                echo "<td style = '" . $style . "' class=" . date('D', $date) . ">$sSpanDays</td>";
                                            }
                                        }
                                        ?>
                                    <? endfor; ?>
                                <? endforeach; ?>
                            </tr>
                        <? endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <? if (count($arResult['PERIOD']) > 0) : ?>
                    <div class="showorhide" data-edit="employes">
                        <a href="#">Скрыть сотрудников</a>
                    </div>
                <? endif; ?>
            </div>
            <div class="col-md-6">
                <div class="text-right showorhide" data-edit="calendar"
                     style="display: <?= (count($arResult['THIS_HEADS']) == 0) ? 'none' : '' ?>">
                    <a href="#">Скрыть календарь</a>
                </div>
            </div>
        </div>
        <div>
            <?
            if ($arResult['ERROR']) {
                echo '<div class="error_res">' . $arResult['ERROR'] . '</div>';
            } elseif ($arResult['INFO']) {
                foreach ($arResult['INFO'] as $info) {
                    echo '<div class="info_res">' . $info . '</div><br>';
                }
            }

            if (count($arResult['PERIOD']) > 0) {
                echo '<div class="mt-3" id="employes"><form><table class="table table-bordered">';
                echo '<thead><tr><td>Сотрудник</td><td>Статус</td><td>Начало</td><td>Окончание</td><td>Период</td><td>Примечание</td><td>Рабочие периоды</td><td>Изменить статус</td><td>Подтвердили</td><td>Не подтвердили</td></tr></thead>';
                foreach ($arResult['PERIOD'] as $f) {
                    $uid = intval($f['PROPERTY_USER_VALUE']);
                    if (empty($arResult['USERS'][ $uid ])) {
                        continue;
                    }

                    $arThisHeads  = explode('|', $arResult['USERS'][ $uid ]['UF_THIS_HEADS']);
                    $boolInCadrs  = $arResult['USERCADRS'];
                    $boolApprove  = in_array($arResult['USER_ID'], $f['WHO_APPROVE']);
                    $boolThisHead = in_array($arResult['USER_ID'], $arThisHeads);
                    $boolIsCit    = in_array($arResult['USER_ID'], $arResult['CITTO']['USERS']);

                    // Если текущий пользователь не представитель отдела кадров, то он увидит не все графики отпусков пользователей
                    $hideSelect = false;
                    if (!$boolInCadrs) {
                        if (
                            $arResult['ADMIN'] &&
                            !$boolThisHead
                        ) {
                            $hideSelect = true;
                        }
                        if (
                            $uid != $arResult['SEL_USER_ID'] &&
                            !$boolThisHead &&
                            !$arResult['ADMIN']
                        ) {
                            continue;
                        }
                    }

                    if ($f['ACTIVE'] == 'Y' && date('Y', MakeTimeStamp($f['ACTIVE_TO'])) != $arResult['YEAR']) {
                        continue;
                    }

                    $arActions = array();
                    if ($f['ACTIVE'] == 'Y') {
                        $class = 'mark-head';
                        $status = 'Подтверждено руководством';

                        foreach ($arResult['USERS_CADRS'] as $item) {
                            if (in_array($item, $f['WHO_APPROVE'])) {
                                $class = "mark-vacation";
                                $status = 'Подтверждено отделом кадров';
                            }
                        }

                        if ($boolIsCit) {
                            if (
                                ($boolThisHead && !$boolApprove) ||
                                ($boolInCadrs && !$boolApprove) ||
                                ($arResult['CITTO']['APPROVE'] == $arResult['USER_ID'] && !$boolApprove) ||
                                $arResult['SECRETARY']
                            ) {
                                $text = 'Подтвердить';
                                if ($boolIsCit && $arResult['CITTO']['APPROVE'] == $arResult['USER_ID']) {
                                    $text = 'Утвердить';
                                }
                                $arActions[] = '<a href="#" data-id = "' . $f['ID'] . '" class="link-approve">' . $text . '</a>';
                            }
                        }

                        if (in_array($arResult['CITTO']['APPROVE'], $f['WHO_APPROVE'])) {
                            $class = "mark-vacation";
                            $status = 'Утвердил Зенин И.В.';
                        }

                        if (
                            $boolInCadrs ||
                            $boolApprove ||
                            $arResult['SECRETARY']
                        ) {
                            $arActions[] = '<a href="#" data-id = "' . $f['ID'] . '" class="link-disapprove">Снять подтверждение</a>';
                        }
                        if (
                            $boolInCadrs &&
                            $class != "mark-vacation"
                        ) {
                            $arActions[] = '<a href="#" data-id = "' . $f['ID'] . '" class="link-approve">Утверждаю</a>';
                        }
                    } else {
                        $arResult['SHOW_BUTTON'] = false;
                        $class = 'day-saved';
                        $status = 'Не подтверждено';

                        if ($boolIsCit && !array_diff($arThisHeads, $f['WHO_APPROVE'])) {
                            $class = 'mark-head';
                            $status = 'Подтверждено руководством';
                        }

                        foreach ($arResult['USERS_CADRS'] as $item) {
                            if ($boolIsCit && in_array($item, $f['WHO_APPROVE'])) {
                                $status = 'Подтверждено отделом кадров';
                            }
                        }
                        if (
                            in_array($arResult['CITTO']['APPROVE'], $f['WHO_APPROVE'])
                        ) {
                            $class = "mark-vacation";
                            $status = 'Утвердил Зенин И.В.';
                        }

                        if ((count($f['WHO_APPROVE']) > 0 && $boolInCadrs) || $boolApprove) {
                            $arActions[] = '<a href="#" data-id = "' . $f['ID'] . '" class="link-disapprove">Снять подтверждение</a>';
                        }

                        if (
                            ($boolThisHead && !$boolApprove) ||
                            ($boolInCadrs && !$boolApprove) ||
                            ($arResult['CITTO']['APPROVE'] == $arResult['USER_ID'] && !$boolApprove) ||
                            $arResult['SECRETARY']
                        ) {
                            $text = 'Подтвердить';
                            if ($boolIsCit && $arResult['CITTO']['APPROVE'] == $arResult['USER_ID']) {
                                $text = 'Утвердить';
                            }
                            $arActions[] = '<a href="#" data-id = "' . $f['ID'] . '" class="link-approve">' . $text . '</a>';
                        }

                        if (
                            ($uid == $arResult['SEL_USER_ID'] && count($f['WHO_APPROVE']) == 0) ||
                            $boolInCadrs ||
                            $boolThisHead ||
                            $arResult['SECRETARY']
                        ) {
                            $arActions[] = '<a href = "#" data-id = "' . $f['ID'] . '" class="link-remove">Удалить</a>';
                        }
                    }

                    // Кем утверждён отпуск сотрудника
                    $whoNotApprove = [];
                    $whoApprove = [];
                    $arHeads = explode('|', $arResult['USERS'][ $uid ]['UF_THIS_HEADS']);
                    if ($class == "mark-vacation") {
                        foreach ($arResult['USERS_CADRS'] as $item) {
                            if (in_array($item, $f['WHO_APPROVE'])) {
                                $whoApprove[] = 'Отдел кадров';
                            }
                        }
                        if (
                            in_array($arResult['CITTO']['APPROVE'], $f['WHO_APPROVE'])
                        ) {
                            $whoApprove[] = $arResult['FIO_APPROVE'][ $arResult['CITTO']['APPROVE'] ];
                        }
                    } else {
                        $ids = json_decode($f["~PROPERTY_UF_WHO_APPROVE_VALUE"]);
                        foreach ($ids as $apprId) {
                            if (in_array($apprId, $arResult['USERS_CADRS'])) {
                                array_push($whoApprove, 'Отдел кадров');
                            }
                        }
                        if ($boolIsCit) {
                            $arHeads[] = $arResult['CITTO']['APPROVE'];
                        }
                        foreach ($arHeads as $head) {
                            if (in_array($head, $ids)) {
                                $whoApprove[ $head ] = $arResult['FIO_APPROVE'][ $head ];
                            } else {
                                $whoNotApprove[ $head ] = $arResult['FIO_APPROVE'][ $head ];
                            }
                        }
                        if ($boolIsCit) {
                            unset($whoNotApprove[ $arResult['CITTO']['APPROVE'] ]);
                        }
                    }
                    $whoNotApprove = implode("<br>", $whoNotApprove);
                    $whoApprove = implode("<br>", $whoApprove);

                    $days = $f['PERIOD'] / 86400;

                    // -- Кем утверждён отпуск сотрудника
                    $input = ($hideSelect) ? '': '<input type="checkbox" id = "' . $f['ID'] . '" data-id = "' . $f['ID'] . '" class="select_emp"><label for = "' . $f['ID'] . '"></label>';
                    echo '<tr' . ($boolCurrentUser ? ' class="current-user"' : '') . ' data-user="'.$uid.'">';
                    echo '<td class="system_person_' . $uid . '">'.$input.'<a href="' . $arResult['BASE_URL'] . '&set_user_id=' . $uid . '">' . htmlspecialcharsbx($arResult['USERS'][$uid]['LAST_NAME'] . ' ' . $arResult['USERS'][$uid]['NAME'] . ' ' . $arResult['USERS'][$uid]['SECOND_NAME']) . '</a></td>';
                    echo '<td class="' . $class . '">' . $status . '</td>';
                    echo '<td data-order=' . MakeTimeStamp($f['ACTIVE_FROM']) . '><a href="' . $arResult['BASE_URL'] . '&year=' . date('Y', $t = MakeTimeStamp($f['ACTIVE_FROM'])) . '&month=' . date('n', $t) . '&set_user_id=' . $uid . '">' . $f['ACTIVE_FROM'] . '</a></td>';
                    echo '<td data-order=' . MakeTimeStamp($f['ACTIVE_TO']) . '><a href="' . $arResult['BASE_URL'] . '&year=' . date('Y', $t = MakeTimeStamp($f['ACTIVE_TO'])) . '&month=' . date('n', $t) . '&set_user_id=' . $uid . '">' . $f['ACTIVE_TO'] . '</a></td>';
                    echo '<td class="totaldays">' . $days . ' ' . $declOfNum($days, ['день', 'дня', 'дней']) . '</td>';
                    echo '<td>' . $f['PREVIEW_TEXT'] . '</td>';
                    echo '<td>' . implode('<br>', $arResult['USERS'][$uid]['WORKPERIODS']['HUMAN']) . '</td>';
                    echo '<td class="statusUser">' . implode(' | ', $arActions) . '</td>';
                    echo "<td class=''>{$whoApprove}</td>";
                    echo "<td class=''>{$whoNotApprove}</td>";
                    echo '</tr>';
                    unset($whoApprove);
                }

                $adminApprove = ($USER->IsAdmin()) ? '<option value="adminApprove">Подтвердить всех под админом</option>' : '';
                echo "</table>";
                ?>
                <div class="row actions">
                    <div class="col-md-6">
                        <div class="row align-items-baseline">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <select name="mass_action" class="form-control">
                                        <option value="description">Массовые действия</option>
                                        <option value="add">Подтвердить</option>
                                        <option value="unadd">Снять подтверждение</option>
                                        <option value="del">Удалить</option><?= $adminApprove ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3 text-center">
                                <a href="#" class="selectall ">Выделить всё</a>
                            </div>
                            <div class="col-md-3">
                                <a href="#" class="unselectall">Снять всё</a>
                            </div>
                        </div>
                    </div>
                </div>
                <?
                echo "</div> </form>";
            }

            if ($arResult['SUMMARY']) {
                $colspan = count($arResult['TYPES']) + 1;
                echo '<div class="float-left pl-2"><table class="bcollapse">';
                echo '<tr><td colspan=' . $colspan . ' <b>' . GetMessage("BITRIX_PLANNER_ITOGO_ZA") . $arMonths[$arResult['MONTH']] . ' ' . $arResult['YEAR'] . '</b>&nbsp;<div class="float-right"><a href="' . $arResult['BASE_URL'] . '&export=summary">' . GetMessage("BITRIX_PLANNER_EKSPORT_V") . '</a></div></td></tr>';
                echo '<tr><th></th>';
                foreach ($arResult['TYPES'] as $type) {
                    echo '<th>' . htmlspecialcharsbx($type) . '</th>';
                }
                echo '</tr>' . "\n";

                foreach ($arResult['USERS'] as $f) {
                    $uid = intval($f['ID']);
                    if (!$arResult['SUMMARY'][$uid]) {
                        continue;
                    }
                    echo '<tr' . ($boolCurrentUser ? ' class="current-user"' : '') . '>';
                    echo '<td><a href="' . $arResult['BASE_URL'] . '&set_user_id=' . $uid . '">' . htmlspecialcharsbx($arResult['USERS'][$uid]['LAST_NAME'] . ' ' . $arResult['USERS'][$uid]['NAME']) . '</a></td>';
                    foreach ($arResult['TYPES'] as $type_id => $type) {
                        $time = $arResult['SUMMARY'][$uid][$arResult['ABSENCE_TYPES'][$type_id]];
                        echo '<td>' . MakeHumanTime($time) . '</td>';
                    }
                }
                echo '</table></div>';
            }

            include_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/interface/admin_lib.php');
            $uid = $arResult['USER_ID'];
            ?>
            <div class="row" id="legend">
                <div class="col-md-6">
                    <div class="item">
                        <span class="day-saved"></span>
                        <span>Не подтверждено</span>
                    </div>
                    <div class="item">
                        <span class="bg-ruk"></span>
                        <span>Подтверждено руководством</span>
                    </div>
                    <div class="item">
                        <span class="bg-ok"></span>
                        <span>Подтверждено отделом кадров</span>
                    </div>
                    <div class="item">
                        <span class="bg-user"></span>
                        <span><b>Иванов Иван (дней: всего / осталось распланировать)</b></span>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="item">
                        <span class="bg-hol"></span>
                        <span>Праздничные дни</span>
                    </div>
                    <div class="item">
                        <span class="bg-weekends"></span>
                        <span>Выходные дни</span>
                    </div>
                    <div class="item">
                        <span class="bg-shortdays"></span>
                        <span>Сокращенные дни</span>
                    </div>
                </div>

                <? if ($arResult['SHOW_BUTTON']): ?>
                    <a href="#" id="to_cadrs" class="btn btn-success mt-1" onclick="to_cadrs(this); return false;">Отправить
                        на
                        согласование в отдел кадров</a>
                    <p class="text-center text-secondary mb-0">Отпуска всех работников согласованы</p>
                <? endif; ?>
            </div>
        </div>

        <div id="date_edit_form">
            <form method="post" name="add_form">
                <input type="hidden" name="action">
                <input type="hidden" name="id">
                <input type="hidden" name="holidays" value="<?= json_encode($arResult['HOLIDAYS']); ?>">
                <table cellpadding="4">
                    <tr>
                        <td colspan="2" id="date_edit_title"></td>
                    </tr>
                    <tr>
                        <td><?= GetMessage("BITRIX_PLANNER_SOTRUDNIK") ?></td>
                        <td><?= htmlspecialcharsbx($arResult['USERS'][$uid]['LAST_NAME'] . ' ' . $arResult['USERS'][$uid]['NAME']) ?></td>
                    </tr>
                    <tr>
                        <td><?= GetMessage("BITRIX_PLANNER_DATA_NACALA") ?></td>
                        <td><input name=day_from size=16 autocomplete="off">
                            <? $APPLICATION->IncludeComponent("bitrix:main.calendar", "", Array(
                                    "SHOW_INPUT" => "N",
                                    "FORM_NAME" => "add_form",
                                    "INPUT_NAME" => "day_from",
                                    "INPUT_NAME_FINISH" => "",
                                    "INPUT_VALUE" => "",
                                    "INPUT_VALUE_FINISH" => "",
                                    "SHOW_TIME" => $arParams['SHOW_TIME'],
                                    "HIDE_TIMEBAR" => "N"
                                )
                            ); ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?= GetMessage("BITRIX_PLANNER_DATA_KONCA") ?></td>
                        <td><input name=day_to size=16 autocomplete="off">
                            <? $APPLICATION->IncludeComponent("bitrix:main.calendar", "", Array(
                                    "SHOW_INPUT" => "N",
                                    "FORM_NAME" => "add_form",
                                    "INPUT_NAME" => "day_to",
                                    "INPUT_NAME_FINISH" => "",
                                    "INPUT_VALUE" => "",
                                    "INPUT_VALUE_FINISH" => "",
                                    "SHOW_TIME" => $arParams['SHOW_TIME'],
                                    "HIDE_TIMEBAR" => "N"
                                )
                            ); ?>
                        </td>
                    </tr>
                    <tr>
                        <td><?= GetMessage("BITRIX_PLANNER_TIP_ZAPISI") ?></td>
                        <td>
                            <select name=event_type size=<?= count($arResult['TYPES']) ?>>
                                <?
                                foreach ($arResult['TYPES'] as $k => $v)
                                    echo '<option value="' . htmlspecialcharsbx($k) . '">' . htmlspecialcharsbx($v) . '</option>';
                                ?>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>Количество дней</td>
                        <td><input type="number" id="DAYS_B" name=DAYS_B value="1"></td>
                    </tr>
                    <tr>
                        <td>Праздников</td>
                        <td><input type="number" id="HOLIDAYS_B" name=HOLIDAYS_B value="0" disabled></td>
                    </tr>
                    <tr>
                        <td><?= GetMessage("BITRIX_PLANNER_PRIMECANIE") ?></td>
                        <td><input id="PREVIEW_TEXT" name=PREVIEW_TEXT></td>
                    </tr>
                    <tr>
                        <td colspan=2>
                            <div class="webform-button-accept planner-ok-button"
                                 onclick="document.forms.add_form.submit()"><?= GetMessage("BITRIX_PLANNER_SOHRANITQ") ?></div>
                            <div class="planner-esc-button"
                                 onclick="BX('date_edit_form').style.display='none'"><?= GetMessage("BITRIX_PLANNER_OTMENA") ?></div>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <div data-id id="popupMenu">
            <a href="#" id="closeMenu">✕</a>
            <div class="actions">
            </div>
        </div>
    <? endif; ?>
</div>

<div class="modal-overlay"></div>
<div class="modal-wrapper">
    <div id="ecp" class="modal js-blur">
        <button class="close-modal">×</button>
        <h2 class="text-left">Экспорт отпусков</h2>
        <div class="docsign-form">
            <div class="docsign-form__status"><span></span></div>
            <div class="docsign-cryptoplugin">
                <div class="docsign-cryptoplugin__certs">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div id="js-users-multi">
                            <?
                            $APPLICATION->IncludeComponent(
                                'bitrix:main.user.selector',
                                ' ',
                                [
                                    "ID" => "mail_client_config_queue",
                                    "API_VERSION" => 3,
                                    "INPUT_NAME" => "fields[crm_queue][]",
                                    "USE_SYMBOLIC_ID" => true,
                                    'OPEN_DIALOG_WHEN_INIT' => false,
                                    'BUTTON_SELECT_CAPTION' => 'пользователь',
                                    "SELECTOR_OPTIONS" =>
                                        [
                                            "departmentSelectDisable" => "Y",
                                            'context' => 'MAIL_CLIENT_CONFIG_QUEUE',
                                            'contextCode' => 'U',
                                            'enableAll' => 'N',
                                            'userSearchArea' => 'I',
                                        ]
                                ]
                            );
                            ?>
                        </div>
                        <div class="text-left docsign-actions">
                            <button type="submit" id="aboutExcel" name="aboutExcel" class="btn btn-info">Скачать реестр</button>
                            <a href="/bizproc/processes/618/element/0/0/?list_section_id=" target="_blank" class="float-right mt-2">Запустить БП "подпись графика отпусков"</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var month = '<?=sprintf('%02d', $arResult['MONTH'])?>';
    var year = <?=$arResult['YEAR']?>;
    var userId = <?=$arResult['USER_ID']?>;
    var day_left = <?=intval($arResult['USERS'][$arResult['USER_ID']]['day_left'])?>;
    var BITRIX_PLANNER_DOBAVLENIE_ZAPISI = '<?=GetMessageJS("BITRIX_PLANNER_DOBAVLENIE_ZAPISI")?>';
    var BITRIX_PLANNER_IZMENENIE_ZAPISI = '<?=GetMessageJS("BITRIX_PLANNER_IZMENENIE_ZAPISI")?>';
    var type = '<?=!$arResult['COUNT_DAYS'] || $arResult['USERS'][$arResult['USER_ID']]['day_left'] > 0 ? 'VACATION' : ''?>';
    var BITRIX_PLANNER_PRODOLJITELQNOSTQ = '<?=GetMessageJS("BITRIX_PLANNER_PRODOLJITELQNOSTQ")?>';
    var BITRIX_PLANNER_DN = ' <?=GetMessageJS("BITRIX_PLANNER_DN")?>';
    var BASE_URL = '<?=$arResult['BASE_URL']?>';
    var BITRIX_PLANNER_UDALITQ_ZAPISQ = '<?=GetMessageJS("BITRIX_PLANNER_UDALITQ_ZAPISQ")?>';
    var BITRIX_PLANNER_PODTVERDITQ_ZAPISQ = '<?=GetMessageJS("BITRIX_PLANNER_PODTVERDITQ_ZAPISQ")?>';
    var BITRIX_PLANNER_VERNUTQ_STATUS_NEPOD = '<?=GetMessageJS("BITRIX_PLANNER_VERNUTQ_STATUS_NEPOD")?>';
    var jsonExcel = <?=json_encode($arResult['PERIOD'])?>;
    var jsonUsers = <?=json_encode($arResult['USERS'])?>;
    var usercadrs = <?=$arResult['USERCADRS']?>;
    var excludeUsers = <?=json_encode($arResult['excludeUsers'])?>;
    var departmentID = <?=$arResult['DEPARTMENT_ID']?>;
    var departmentList = <?=json_encode($arResult['DEPARTMENT_LIST'])?>;
    var myWorkers = <?=json_encode($arResult['MY_WORKERS'])?>;
    var recursive = <?=json_encode($arResult['RECURSIVE'])?>;
</script>
