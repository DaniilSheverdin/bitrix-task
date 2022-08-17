<?

use Citto\ControlOrders\Executors;
use Citto\Controlorders\GroupExecutors;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
global $APPLICATION;

$APPLICATION->SetTitle('Редактирование списков - Группы исполнителей');

$obGroupExecutors = new GroupExecutors();

if (isset($_POST['action'])) {
    if ($_POST['action'] == 'update') {
        if ($_POST['ID'] > 0) {
            $obGroupExecutors->table::update(
                $_POST['ID'],
                [
                    'UF_NAME' => $_POST['UF_NAME'],
                    'UF_SORT' => $_POST['UF_SORT'],
                    'UF_LIST' => $_POST['UF_LIST'],
                ]
            );
        } else {
            $obElement = $obGroupExecutors->table::add([
                'UF_NAME' => $_POST['UF_NAME'],
                'UF_SORT' => $_POST['UF_SORT'],
                'UF_LIST' => $_POST['UF_LIST'],
            ]);
            LocalRedirect($APPLICATION->GetCurPageParam('group=' . $obElement->getId(), ['group']));
        }
    } elseif ($_POST['action'] == 'remove') {
        if ($_POST['ID'] > 0) {
            $obGroupExecutors->table::delete($_POST['ID']);
            LocalRedirect($APPLICATION->GetCurPageParam('', ['group']));
        }
    }
}

$arTypes = Executors::getTypesList();
$arExecutors = Executors::getList();
$arGroups = $obGroupExecutors->getList();

$arGroups[0] = [
    'ID'        => 0,
    'UF_NAME'   => 'Добавить группу',
    'UF_SORT'   => 500,
    'UF_LIST'   => [''],
];

$selected = -1;
if (isset($_REQUEST['group'])) {
    $selected = (int)$_REQUEST['group'];
}
?>
<ul>
    <?foreach ($arGroups as $group) : ?>
    <li>
        <?if ($selected == $group['ID']) : ?>
        <b><?=$group['UF_NAME']?></b>
        <?else : ?>
        <a href="<?=$APPLICATION->GetCurPageParam('group=' . $group['ID'], ['group'])?>">
            <?=$group['UF_NAME']?>
        </a>
        <?endif;?>
    </li>
    <?endforeach;?>
</ul>
<?
if ($selected >= 0) {
    ?>
    <div class="row">
        <div class="col-8">
            <form method="POST">
                <input type="hidden" name="ID" value="<?=$selected?>" />
                <input type="hidden" name="action" value="update" />
                <div class="form-group row my-3">
                    <label class="col-sm-4 col-form-label" for="UF_NAME">Название группы</label>
                    <div class="col-sm-8">
                        <input
                            id="UF_NAME"
                            class="form-control"
                            name="UF_NAME"
                            value="<?=$selected>0?$arGroups[ $selected ]['UF_NAME']:''?>"
                            placeholder="Название группы"
                            required
                            />
                    </div>
                </div>
                <div class="form-group row my-3">
                    <label class="col-sm-4 col-form-label" for="UF_SORT">Сортировка</label>
                    <div class="col-sm-8">
                        <input
                            id="UF_SORT"
                            class="form-control"
                            name="UF_SORT"
                            value="<?=$arGroups[ $selected ]['UF_SORT']?>"
                            placeholder="Сортировка"
                            required
                            />
                    </div>
                </div>
                <div class="form-group row my-3">
                    <label class="col-sm-4 col-form-label" for="UF_LIST">Исполнители</label>
                    <div class="col-sm-8">
                        <?foreach ($arGroups[ $selected ]['UF_LIST'] as $key => $value) : ?>
                        <div class="list mb-2 row">
                            <div class="col-11">
                                <select
                                    id="UF_LIST"
                                    class="form-control"
                                    name="UF_LIST[]"
                                    placeholder="Исполнители"
                                    required
                                >
                                    <option value="">(Не выбран)</option>
                                    <?
                                    foreach ($arTypes as $sValue) {
                                        if ($sValue['CNT'] > 0) {
                                            ?>
                                            <option
                                                <?=($sValue['EXTERNAL_ID'] == $value) ? 'selected' : '' ?>
                                                value="<?=$sValue['EXTERNAL_ID']?>"
                                                >Все <?=$sValue['VALUE']?></option>
                                            <?
                                        }
                                    }

                                    foreach ($arTypes as $sValue) {
                                        if ($sValue['CNT'] > 0) {
                                            ?>
                                            <optgroup label="<?=$sValue['VALUE']?>">
                                            <?
                                            foreach ($arExecutors as $k => $v) {
                                                if ($v['PROPERTY_TYPE_ENUM_ID'] != $sValue['ID']) {
                                                    continue;
                                                }
                                                ?>
                                                <option
                                                    <?=($v['ID'] == $value) ? 'selected' : '' ?>
                                                    value="<?=$v['ID']?>"
                                                    ><?=$v['NAME']?></option>
                                                <?
                                            }
                                            ?>
                                            </optgroup>
                                            <?
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-1 pl-1 m-auto">
                                <a href="#" class="js-add-list <?=$key>0?'d-none':''?>ui-btn ui-btn-primary ui-btn-xs ui-btn-icon-add"></a>
                                <a href="#" class="js-remove-list <?=$key<=0?'d-none':''?> ui-btn ui-btn-danger ml-0 ui-btn-xs ui-btn-icon-remove"></a>
                            </div>
                        </div>
                        <?endforeach;?>
                    </div>
                </div>
                <div class="form-group row my-3">
                    <div class="col-sm-12">
                        <input
                            class="btn btn-success mr-4"
                            type="submit"
                            value="<?=$selected<=0?'Добавить':'Сохранить'?>"
                            />
                        <?if ($selected > 4) : ?>
                        <input
                            class="btn btn-danger mr-4 js-remove-group"
                            type="submit"
                            value="Удалить"
                            />
                        <?endif;?>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script type="text/javascript">
        $(document).ready(function(){
            $('body').on('click', '.js-remove-group', function(e) {
                if (confirm('Вы уверены, что хотите удалить группу пользователей?')) {
                    $(this)
                        .closest('form')
                        .find('[name=action]')
                        .val('remove');
                } else {
                    e.preventDefault();
                    return false;
                }
            });
            $('body').on('click', '.js-add-list', function(e) {
                $('div.list')
                    .last()
                    .clone()
                    .insertAfter($('div.list').last());

                let newField = $('div.list').last();

                newField
                    .find('select')
                    .val('');

                newField
                    .find('.js-add-list')
                    .addClass('d-none');

                newField
                    .find('.js-remove-list')
                    .removeClass('d-none');

                return false;
            });
            $('body').on('click', '.js-remove-list', function(e) {
                $(this).closest('div.list').remove();
                return false;
            });
        });
    </script>
    <?
}