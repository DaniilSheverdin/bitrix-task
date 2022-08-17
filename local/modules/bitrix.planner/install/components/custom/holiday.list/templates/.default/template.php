<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<?
$arMonths = array('', GetMessage("BITRIX_PLANNER_ANVARQ"), GetMessage("BITRIX_PLANNER_FEVRALQ"), GetMessage("BITRIX_PLANNER_MART"), GetMessage("BITRIX_PLANNER_APRELQ"), GetMessage("BITRIX_PLANNER_MAY"), GetMessage("BITRIX_PLANNER_IUNQ"), GetMessage("BITRIX_PLANNER_IULQ"), GetMessage("BITRIX_PLANNER_AVGUST"), GetMessage("BITRIX_PLANNER_SENTABRQ"), GetMessage("BITRIX_PLANNER_OKTABRQ"), GetMessage("BITRIX_PLANNER_NOABRQ"), GetMessage("BITRIX_PLANNER_DEKABRQ"));
?>
<div id="sel_year">
    <?
    for ($i = date('Y'); $i <= date('Y') + 1; $i++) {
        echo $i == $arResult['YEAR'] ? '<b>' . $i . '</b>' : '<a href="' . $arResult['BASE_URL'] . '&year=' . $i . '">' . $i . '</a>';
        echo ' &nbsp;  &nbsp; ';
    }
    ?>
</div>
<?
if (count($arResult['DEPARTMENT_LIST']) > 1) {
    ?><select class="custom-select" onchange="RefreshList(this.value)"><?
    foreach ($arResult['DEPARTMENT_LIST'] as $f) {
        echo '<option value=' . $f['ID'] . ($arResult['DEPARTMENT_ID'] == $f['ID'] ? ' selected' : '') . '>' . htmlspecialcharsbx($f['DEPTH_NAME']) . '</option>';
    }
    ?></select>
    <label><input type="checkbox" <? if ($_GET['recursive']): ?>checked<? endif; ?>
                  onchange="document.location='?year=2020&amp;month=8&amp;set_user_id=0&amp;department=<?= $_GET['department'] ?>&amp;recursive=0&amp;recursive=' + (this.checked ? 1 : 0)">
        показать подотделы </label>
    <?
} else {
    $f = reset($arResult['DEPARTMENT_LIST']);
    echo htmlspecialcharsbx($f['NAME']);
}
?>

<? if (count($arResult['DELEGATIONS']) > 0): ?>
    <div id="delegation" class="float-right">
        <form action="/planner/" method="POST" name="selectDelegationForm">
            <select name="selectDelegation" id="selectDelegation">
                <option value="none">Выберите пользователя, делегирующего Вам полномочия</option>
                <option value="<?= $USER->getID() ?>">Выбрать себя</option>
                <? foreach ($arResult['DELEGATIONS'] as $k => $v): ?>
                    <option value="<?= $k ?>" id=""><?= $v[0] ?></option>
                <? endforeach; ?>
            </select>
        </form>
    </div>
<? endif; ?>

<? if (isset($arResult['USERS'])): ?>
    <div class="flex clearfix">
        <div id="selectHead" class="showorhide text-left open-modal">
            <a href="#">Выбор руководителей (<span class="count"><?= count($arResult['THIS_HEADS']) ?></span>)</a>
        </div>

        <div class="text-right showorhide" data-edit="calendar"
             style="display: <?= (count($arResult['THIS_HEADS']) == 0) ? 'none' : '' ?>">
            <a href="#">Календарь</a>
        </div>
    </div>

    <div class="modal-overlay"></div>
    <div class="modal-wrapper">
        <div id="myHeads" class="modal js-blur">
            <button class="close-modal">×</button>
            <h2 class="text-left">Мои руководители</h2>
            <form>
                <div class="form-group row">
                    <div class="col-sm-12 ui-widget">
                        <input class="form-control" id="tags">
                    </div>
                </div>
                <div class="heads">
                    <? foreach ($arResult['THIS_HEADS'] as $k => $item): ?>
                        <div class="alert alert-info" role="alert" data-head="<?= $k ?>">
                            <a href="#" class="close">✕</a>
                            <?= $item ?>
                        </div>
                    <? endforeach; ?>
                </div>
                <button type="button" id="save_heads" class="btn btn-info float-right">Сохранить</button>
            </form>
        </div>
    </div>

    <? if (count($arResult['THIS_HEADS']) == 0): ?>
        <div class="alert alert-warning" role="alert">
            Для доступа к сервису Вам необходимо выбрать руководителей
        </div>
    <? endif; ?>
    <div class="outer mb-3" id="calendar" style="display: <?= (count($arResult['THIS_HEADS']) == 0) ? 'none' : '' ?>">
        <div class="inner">
            <table class="table table-bordered table-vacation">
                <thead>
                <tr>
                    <th scope="col">Сотрудник</th>
                    <? foreach ($arResult['monthsList'] as $key => $value): ?>
                        <? $arResult['daysList'][$key] = cal_days_in_month(CAL_GREGORIAN, $key, $arResult['YEAR']); ?>
                        <th scope="col" colspan= <?= $arResult['daysList'][$key]; ?>><?= $value ?></th>
                    <? endforeach; ?>
                </tr>
                <tr rowspan="2">
                    <th scope="col">Дни</th>
                    <? foreach ($arResult['monthsList'] as $key => $value): ?>
                        <? $arResult['daysList'][$key] = cal_days_in_month(CAL_GREGORIAN, $key, $arResult['YEAR']); ?>
                        <? for ($i = 1; $i <= $arResult['daysList'][$key]; $i++): ?>
                            <? setlocale(LC_ALL, 'ru_RU.UTF-8');
                            $date = strtotime($arResult['YEAR'] . '-' . $key . '-' . $i); ?>
                            <? if (strftime('%a', $date) == 'Пн'): ?>
                                <th class="table_day" colspan="7" scope="col"><?= strftime('%a', $date); ?>
                                    /<?= $i ?></th>
                                <? $i = $i + 6; ?>
                            <? elseif ($key == 1): ?>
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
                <? foreach ($arResult['USERS'] as $u => $item): ?>
                    <?
                    $boolCurrentUser = (isset($_GET['set_user_id']) && $arResult['ADMIN'] && $arResult['SEL_USER_ID'] == $item['ID']) || (!isset($_GET['set_user_id']) && $arResult['USER_ID'] == $item['ID']);
                    if($item['day_left']>0) $arResult['SHOW_BUTTON'] == false;
                    ?>
                    <tr>
                        <th tooltip="" <? if($boolCurrentUser) echo "class = 'current-user';"; ?>
                            scope="row" id="system_person_<?= $item['ID'] ?>"><a class="text-danger hideUser" href="#">&#10008;</a>
                            <? if ($item['ID'] == $arResult['USER_ID'] || in_array($arResult['USER_ID'], $arResult['USERS_CADRS']) || in_array($arResult['USER_ID'], explode('|', $item["UF_THIS_HEADS"]))): ?>
                            <a class="fiouser" data-wstart="<?= $item['DETAIL'] ?>" href="<?= $arResult['BASE_URL'] ?>&set_user_id=<?= $item['ID'] ?>">
                                <? endif; ?>
                                <?= $item['LAST_NAME'] . ' ' . $item['NAME'] . ' ' . $item['SECOND_NAME']; ?></a>
                            (дней: <?= $item['total_days'] ?>
                            / <span class="left"><?= $item['day_left'] ?></span>)
                        </th>
                        <? $jump = 0;
                        $class_vac = '';
                        ?>
                        <? foreach ($arResult['daysList'] as $key => $value): ?>
                            <? for ($i = 1; $i <= $value; $i++): ?>
                                <?
                                $id = $item['ID'] == $arResult['SEL_USER_ID'] ? ' data-date="' . strtotime($arResult["YEAR"] . '-' . $key . '-' . $i) . '" id="day_' . $i . '"' : '';
                                $style = '';
                                if ($i == $value) $style = 'border-right: 1px dashed #dee2e6;';
                                $u = $item['VACATION'][mktime(0, 0, 0, $key, $i, $arResult['YEAR'])];
                                if (isset($u)) {
                                    $idRecord = $u['ID_RECORD'];
                                    $class_vac = $u['STATUS'];

                                    for ($x = 1; $x <= $u['PERIOD'] / 86400; $x++) {
                                        $holiday = '';
                                        if ($i == $value) $style = 'border-right: 1px dashed #dee2e6;';

                                        $date = strtotime($arResult['YEAR'] . '-' . $key . '-' . $i);
                                        if (in_array($date, $arResult['holidays']))
                                            $holiday = 'bg-hol';

                                        echo "<td data-rec-vacation = {$idRecord} style = '" . $style . "' class='$holiday $class_vac'></td>";
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
                                        if (in_array($date, $arResult['holidays']))
                                            $style = 'background:#dc3545;';

                                        if ($i == $value) $style = 'border-right: 1px dashed #dee2e6;';
                                        echo "<td data-rec-vacation = {$idRecord} style = '{$style}' class='$class_vac'></td>";
                                        $i++;
                                    }
                                    $i--;
                                    $jump = 0;
                                } else {
                                    $date = strtotime($arResult['YEAR'] . '-' . $key . '-' . $i);
                                    $holiday = '';
                                    if (in_array($date, $arResult['holidays']))
                                        $style .= 'background:#dc3545;';

                                    if ($i == 1 || date('D', $date) == 'Mon') $d_period = 'from';
                                    elseif ($i == $value || date('D', $date) == 'Sun') $d_period = 'to';
                                    else $d_period = '';

                                    if ($boolCurrentUser)
                                        echo "<td style = '" . $style . "' class='$d_period current-user " . date('D', $date) . "' data-week='" . date('W', $date) . '-' . $key . "' " . $id . "></td>";
                                    else echo "<td style = '" . $style . "' class=" . date('D', $date) . "></td>";
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
    <div class="flex" style="display: <?= (count($arResult['THIS_HEADS']) == 0) ? 'none' : '' ?>">
        <div class="showhideusers" style="display: <?= (count($arResult['THIS_HEADS']) == 0) ? 'none' : '' ?>">
            <a href="#">Отобразить скрытых сотрудников (<span>0</span>)</a>
        </div>
        <? if (count($arResult['PERIOD']) > 0): ?>
            <div class="text-right showorhide" data-edit="employes">
                <a href="#">Сотрудники</a>
            </div>
        <? endif; ?>
    </div>
    <div style="display: <?= (count($arResult['THIS_HEADS']) == 0) ? 'none' : '' ?>">
        <?
        if ($arResult['ERROR'])
            echo '<div class="error_res">' . $arResult['ERROR'] . '</div>';
        elseif ($arResult['INFO']) {
            foreach ($arResult['INFO'] as $info) {
                echo '<div class="info_res">' . $info . '</div><br>';
            }
        }

        if (count($arResult['PERIOD']) > 0) {
            echo '<div id="employes"><form><table id="example" class="table-striped table table-bordered">';
            echo '<thead><tr><td>Сотрудник</td><td>Статус</td><td>Начало</td><td>Окончание</td><td>Период</td><td>Примечание</td><td>Рабочие периоды</td><td>Изменить статус</td></tr></thead>';
            foreach ($arResult['PERIOD'] as $f) {
                $uid = intval($f['PROPERTY_USER_VALUE']);
                $arThisHeads = explode('|', $arResult['USERS'][$uid]['UF_THIS_HEADS']);
                $boolInCadrs = $arResult['USERCADRS'];
                $boolApprove = in_array($arResult['USER_ID'], $f['WHO_APPROVE']);
                $boolThisHead = in_array($arResult['USER_ID'], $arThisHeads);
                // Если текущий пользователь не представитель отдела кадров, то он увидит не все графики отпусков пользователей
                if (!$boolInCadrs){
                    if($uid != $arResult['SEL_USER_ID'] && !$boolThisHead)
                        continue;
                }

                if ($f['ACTIVE'] == 'Y' && date('Y', MakeTimeStamp($f['ACTIVE_TO'])) != $arResult['YEAR'])
                    continue;

                $arActions = array();
                if ($f['ACTIVE'] == 'Y') {
                    $class = 'mark-head';
                    $status = 'Подтверждено руководством';
                    if ($boolInCadrs || $boolApprove)
                        $arActions[] = '<a href="#" data-id = "' . $f['ID'] . '" class="link-disapprove">Снять подтверждение</a>';
                    if ($boolInCadrs)
                        $arActions[] = '<a href="#" data-id = "' . $f['ID'] . '" class="link-approve">' . 'Утверждаю' . '</a>';

                    foreach($arResult['USERS_CADRS'] as $item){
                        if(in_array($item, $f['WHO_APPROVE'])) {
                            $class = "mark-vacation";
                            $status = 'Утверждено отделом кадров';
                        }
                    }
                }
                else {
                    $arResult['SHOW_BUTTON'] = false;
                    $class = 'day-saved';
                    $status = 'Не подтверждено';
                    if ((count($f['WHO_APPROVE']) > 0 && $boolInCadrs) || $boolApprove)
                        $arActions[] = '<a href="#" data-id = "' . $f['ID'] . '" class="link-disapprove">Снять подтверждение</a>';

                    if (($boolThisHead && !$boolApprove) || $boolInCadrs)
                        $arActions[] = '<a href="#" data-id = "' . $f['ID'] . '" class="link-approve">Подтвердить</a>';

                    if (($uid == $arResult['SEL_USER_ID'] && count($f['WHO_APPROVE']) == 0) || $boolInCadrs || $boolThisHead) {
                        $arActions[] = '<a href = "#" data-id = "' . $f['ID'] . '" class="link-remove">Удалить</a>';
                    }
                }
                echo '<tr' . ($boolCurrentUser ? ' class="current-user"' : '') . '>';
                echo '<td class="system_person_' . $uid . '"><input type="checkbox" data-id = "' . $f['ID'] . '" class="select_emp"><a href="' . $arResult['BASE_URL'] . '&set_user_id=' . $uid . '">' . htmlspecialcharsbx($arResult['USERS'][$uid]['LAST_NAME'] . ' ' . $arResult['USERS'][$uid]['NAME'] . ' ' . $arResult['USERS'][$uid]['SECOND_NAME']) . '</a></td>';
                echo '<td class="' . $class . '">' . $status . '</td>';
                echo '<td data-order=' . MakeTimeStamp($f['ACTIVE_FROM']) . '><a href="' . $arResult['BASE_URL'] . '&year=' . date('Y', $t = MakeTimeStamp($f['ACTIVE_FROM'])) . '&month=' . date('n', $t) . '&set_user_id=' . $uid . '">' . $f['ACTIVE_FROM'] . '</a></td>';
                echo '<td data-order=' . MakeTimeStamp($f['ACTIVE_TO']) . '><a href="' . $arResult['BASE_URL'] . '&year=' . date('Y', $t = MakeTimeStamp($f['ACTIVE_TO'])) . '&month=' . date('n', $t) . '&set_user_id=' . $uid . '">' . $f['ACTIVE_TO'] . '</a></td>';
                echo '<td>' . $f['PERIOD'] / 86400 . ' дней</td>';
                echo '<td>' . $f['PREVIEW_TEXT'] . '</td>';
                echo '<td>' . implode('<br>', $arResult['USERS'][$uid]['WORKPERIODS']['HUMAN']) . '</td>';
                echo '<td class="statusUser">' . implode(' | ', $arActions) . '</td>';
                echo '</tr>';
            }
            echo '</table>
                    <div class="actions mb-3">
                    <select name="mass_action">
                    <option value="description">Массовые действия</option>
                    <option value="add">Подтвердить</option>
                    <option value="unadd">Снять подтверждение</option>
                    <option value="del">Удалить</option>
                    </select><a href="#" class="ml-2 selectall">Выделить всё</a><a href="#" class="ml-2 unselectall">Снять всё</a>';
            if ($arResult['EXPORT']) echo '<div class="showhideusers"><a class="float-right" href="#" download="" id="excel">Экспорт таблицы</a></div>';

            echo '</div>
                   </div>
                   </form>
                   ';
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
                if (!$arResult['SUMMARY'][$uid])
                    continue;
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
        <div>
            <table class="bcollapse">
                <tr>
                    <td class="day-saved" width=20></td>
                    <td>Не подтверждено</td>
                </tr>
                <tr>
                    <td class="bg-ruk" width=20></td>
                    <td>Подтверждено руководством</td>
                </tr>
                <tr>
                    <td class="bg-ok" width=20></td>
                    <td>Утверждено отделом кадров</td>
                </tr>
                <tr>
                    <td class="bg-hol" width=20></td>
                    <td>Праздничные дни</td>
                </tr>
                <tr>
                    <td class="bg-user" width=20></td>
                    <td><b>Иванов Иван (дней: всего / осталось распланировать)</b></td>
                </tr>
            </table>
            <? if($arResult['USERCADRS']): ?>
                <div id="violations" class="showorhide text-left mt-2">
                    <a href="#">Экспорт нарушений</a>
                </div>
            <? endif; ?>
            <? if ($arResult['SHOW_BUTTON']): ?>
                <a href="#" id="to_cadrs" class="btn btn-success mt-1" onclick="to_cadrs(this); return false;">Отправить
                    на
                    согласование в отдел кадров</a>
                <p class="text-center text-secondary mb-0">Отпуска всех работников согласованы</p>
            <? endif; ?>
        </div>
    </div>

    <div id="date_edit_form">
        <form method=post name=add_form>
            <input type=hidden name=action>
            <input type=hidden name=id>
            <input type=hidden name=holidays value="<?= json_encode($arResult['holidays']); ?>">
            <table cellpadding=4>
                <tr>
                    <td colspan=2 id="date_edit_title"></td>
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
                        <div><select size="12" name="sel"></select></div>
                        <div class="text-left docsign-actions">
                            <button type="submit" id="aboutExcel" name = "aboutExcel" class="btn btn-info">Ознакомиться</button>
                            <button type="submit" id="docsign__sign-files" name = "docsign__sign-files" class="btn btn-success float-right">Подписать и скачать</button>
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
    var last_day = <?=$arResult['LAST_DAY']?>;
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
</script>
