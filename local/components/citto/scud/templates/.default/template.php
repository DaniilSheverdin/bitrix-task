<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
$this->addExternalCss("/bitrix/js/ui/bootstrap4/css/bootstrap.css");
if ($arResult['PAGE'] == 'journal') {
    $arAbsenceParams = [
        "MESS" => [
            "INTR_ABSENCE_TITLE" => "Добавить отсутствие",
            "INTR_ABSENCE_BUTTON" => "Добавить",
            "INTR_CLOSE_BUTTON" => "Закрыть",
            "INTR_LOADING" => "Загрузка...",
        ],
        "ACTION" => "SHOW"
    ];
    $GLOBALS['INTRANET_TOOLBAR']->AddButton(array(
        'ONCLICK' => "BX.AbsenceCalendar.ShowForm(" . CUtil::PhpToJSObject($arAbsenceParams) . ")",
        "TEXT" => GetMessage('INTR_ABSC_TPL_ADD_ENTRY'),
        "ICON" => 'add',
        "SORT" => 1000,
    ));
}

global $USER;
?>
<form action="" id="toolbar" method="POST">
    <div class="row">
        <div class="col-md-2 form-group">
            <label for=""><?= ($arResult['PAGE'] != 'journal') ? 'Дата с:' : 'Отстутствие с:' ?></label>
            <input class="form-control" type="text" value="<?= $arResult['DATETIME']['from'] ?>" name="from"
                   onclick="BX.calendar({node: this, field: this, bTime: true});">

            <label for=""><?= ($arResult['PAGE'] != 'journal') ? 'Дата по:' : 'Отстутствие по:' ?></label>
            <input class="form-control" type="text" value="<?= $arResult['DATETIME']['to'] ?>" name="to"
                   onclick="BX.calendar({node: this, field: this, bTime: true});">
        </div>

        <div class="col-md-3 form-group">
            <label>ФИО:</label>
            <select class="form-control" name="fio">
                <option value="all">Все</option>
                <? foreach ($arResult['USERS_SELECT'] as $id => $fio): ?>
                    <option <? if ($arResult['CHOOSE_USER'] == $id) echo 'selected' ?>
                            value="<?= $id ?>"><?= $fio ?></option>
                <? endforeach; ?>
            </select>
            <label>Организация:</label>
            <select class="form-control" name="structure">
                <option value="all">Все</option>
                <? foreach ($arResult['STRUCTURE'] as $id => $name): ?>
                    <option <? if ($arResult['CHOOSE_STRUCTURE'] == $id) echo 'selected' ?>
                            value="<?= $id ?>"><?= $name ?></option>
                <? endforeach; ?>
            </select>
            <div class="row mt-1">
                <div class="col-6">
                    <input type="checkbox" id="subusers" name="subusers" <? if ($arResult['SUBUSERS']) echo 'checked' ?>>
                    <label for="subusers">Подотделы</label>
                </div>
                <div class="col-6">
                    <input type="checkbox" id="podved" name="podved" <? if ($arResult['PODVED']) echo 'checked' ?>>
                    <label for="podved">Подведы</label>
                </div>
            </div>
        </div>
        <div class="col-md-2 form-group">
            <? if ($arResult['PAGE'] == 'events') : ?>
                <label for="">Событие:</label>
                <select class="form-control" name="event">
                    <option <? if ($arResult['EVENT'] == '') echo 'selected' ?> value="ALL">Все</option>
                    <option <? if ($arResult['EVENT'] == 'ENTRY') echo 'selected' ?> value="ENTRY">Вход</option>
                    <option <? if ($arResult['EVENT'] == 'EXIT') echo 'selected' ?> value="EXIT">Выход</option>
                </select>
                <a href="?page=journal" class="form-control mt-2 text-center" id="journal">Журнал УРВ</a>
            <? else: ?>
                <label for="">Дата записи с:</label>
                <input class="form-control" type="text" value="<?= $arResult['DATETIME']['record_from'] ?>"
                       name="absence_from"
                       onclick="BX.calendar({node: this, field: this, bTime: true});">

                <label for="">Дата записи по:</label>
                <input class="form-control" type="text" value="<?= $arResult['DATETIME']['record_to'] ?>"
                       name="absence_to"
                       onclick="BX.calendar({node: this, field: this, bTime: true});">
                <a href="?page=events" class="form-control mt-2 text-center" id="events">События</a>
            <? endif; ?>
        </div>
        <div class="col-md-2 form-group mt-3 px-0">
            <? if ($arResult['PAGE'] == 'events'): ?>
                <div>
                    <input class="form-check-input" type="checkbox" name="VIOLATION" value="VIOLATION"
                           id="VIOLATION" <? if ($arResult['VIOLATION']) echo 'checked' ?>>
                    <label class="form-check-label">Только нарушения</label>
                </div>
                <div>
                    <input class="form-check-input" type="checkbox" name="VIOLATION_3" value="VIOLATION_3"
                           id="VIOLATION_3" <? if ($arResult['VIOLATION_3']) echo 'checked' ?>>
                    <label class="form-check-label">Более 3 нарушений</label>
                </div>
                <div>
                    <input class="form-check-input" type="checkbox" name="VIOLATION_POSITIVE" value="VIOLATION_POSITIVE"
                           id="VIOLATION_POSITIVE" <? if ($arResult['VIOLATION_POSITIVE']) echo 'checked' ?>>
                    <label class="form-check-label">Положительные нарушения</label>
                </div>
            <? endif; ?>
        </div>
        <div class="col-md-3 form-group mt-3">
            <button class="form-control btn btn-success" id="export" type="submit" form="toolbar" value="export" name="export">Экспорт
            </button>
            <? if ($arResult['SEE_ANALYTICS'] == 'Y'): ?>
			 <button class="form-control mt-2" id="analytics_user" type="submit" form="toolbar" value="analytics_user" name="export">Аналитика по пользователям
            </button>
            <button class="form-control mt-2" id="analytics_department" type="submit" form="toolbar" value="analytics_department" name="export">Аналитика по отделам
            </button>
            <? endif; ?>
            <button class="form-control mt-2" id="find" type="submit" form="toolbar" value="find" name="find">Поиск
            </button>
        </div>
    </div>
</form>

<table id="absences">
    <thead>
    <tr>
        <th>ФИО</th>
        <th><?= ($arResult['PAGE'] != 'journal') ? 'Турникет' : 'Фамилия руководителя, давшего разрешение<br>на убытие гражданского служащего (работника)' ?></th>
        <th><?= ($arResult['PAGE'] != 'journal') ? 'Событие' : 'Дата записи' ?></th>
        <th><?= ($arResult['PAGE'] != 'journal') ? 'Дата' : 'Отсутствие' ?></th>
        <th><?= ($arResult['PAGE'] != 'journal') ? 'Нарушения' : 'Цель и место убытия' ?></th>
    </tr>
    </thead>
    <tbody>
    <? foreach ($arResult['ABSENCE'] as $iUserID => $arAbsences) : ?>
        <? foreach ($arAbsences as $arField): ?>
            <tr id="absence_<?= $arField['ID'] ?>"
                <? if ($arResult['PAGE'] != 'events'): ?>
                    class="absence_event"
                    onclick="BX.AbsenceCalendar.ShowForm({
                            'ACTION':'EDIT',
                            'MESS':{
                            'INTR_ABSENCE_TITLE':'Редактировать отсутствие',
                            'INTR_CLOSE_BUTTON':'Закрыть',
                            },
                            'HEAD_CONFIRM':'<?= htmlspecialchars($arField['HEAD_CONFIRM']) ?>',
                            'REASON':'<?= htmlspecialchars($arField['VIOLATION']) ?>',
                            'ABSENCE_ELEMENT_ID':<?= $arField['ID'] ?>
                            })"
                <? endif ?>
            >
                <td class="user"><?= $arField['FIO'] ?></td>

                <? if ($arResult['PAGE'] != 'journal'): ?>
                    <td><?= $arField['TOURNIQUET'] ?></td>
                <? else: ?>
                    <td class="headConfirm"><?= $arField['HEAD_CONFIRM'] ?></td>
                <? endif; ?>

                <? if ($arResult['PAGE'] != 'journal'): ?>
                    <td><?= $arField['EVENT']['VALUE'] ?></td>
                <? else: ?>
                    <td class="dateRecord"><?= $arField['DATE_RECORD'] ?></td>
                <? endif; ?>

                <td class="dateAbsence"><?= $arField['DATE'] ?></td>
                <td class="reason"
                    style="color:
                    <? if ($arField['TYPE_VIOLATION'] == 'NEGATIVE'): ?>
                        #f1361b
                    <? elseif ($arField['TYPE_VIOLATION'] == 'POSITIVE'): ?>
                        #46a524
                    <? else: ?>
                        #535c68
                    <? endif; ?>
                        ">
                    <?= $arField['VIOLATION'] ?>
                </td>
            </tr>
        <? endforeach ?>
    <? endforeach; ?>
    </tbody>
</table>

<!-- Modal -->
<div class="modal fade" id="delete_absence_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Удалить нарушение?</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-event="yes" data-dismiss="modal">Да</button>
                <button type="button" class="btn btn-secondary" data-event="no" data-dismiss="modal">Нет</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Result-->
<div class="modal fade" id="result_absence_modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom: unset;">
                <h5 class="modal-title result"></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    var isAdmin = '<?=$USER->IsAdmin()?>';
    var page = "<?=$arResult['PAGE']?>";
</script>
