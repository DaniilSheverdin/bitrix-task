<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
        die();
}

if (!function_exists('buildSorter')) {
    /**
     * @param $key
     *
     * @return callable
     */
    function buildSorter($key): callable
    {
        return static function ($a, $b) use ($key) {
            return strnatcmp($a[ $key ], $b[ $key ]);
        };
    }

    $arExecutors = [];
    foreach ($arResult['ISPOLNITELS'] as $arIspolnitel) {
        if (empty($arIspolnitel['PROPERTY_TYPE_VALUE'])) {
            continue;
        }
        $arExecutors[] = [
            'ID' => $arIspolnitel['ID'],
            'NAME' => '[' . ($arIspolnitel['PROPERTY_TYPE_VALUE']??'Другое') . '] ' . $arIspolnitel['NAME'],
        ];
    }

    uasort($arExecutors, buildSorter('NAME'));
}
?>
<div class="box box-danger">
    <div class="box-header with-border">
        <h3 class="box-title">Фильтрация по дате</h3>
    </div>
    <div class="box-body">
        <form method="GET">
            <input type="hidden" name="stats" value="table" />
            <input type="hidden" name="ISPOLNITEL" value="<?=($_REQUEST['ISPOLNITEL']!='')?$_REQUEST['ISPOLNITEL']:'' ?>" />
            <div class="row">
                <div class="col-md-4">От <input
                    class="form-control"
                    type="text"
                    name="FROM"
                    value="<?=($_REQUEST['FROM']!='')?$_REQUEST['FROM']:'' ?>"
                    onclick="BX.calendar({node: this, field: this, bTime: false});"></div>
                <div class="col-md-4">До <input
                    class="form-control"
                    type="text"
                    name="TO"
                    value="<?=($_REQUEST['TO']!='')?$_REQUEST['TO']:'' ?>"
                    onclick="BX.calendar({node: this, field: this, bTime: false});"></div>
                <div class="col-md-4">
                    <br>
                    <button type="submit" class="ui-btn ui-btn-success">Показать</button>
                    <?if (!empty($_REQUEST['FROM']) || !empty($_REQUEST['TO'])) : ?>
                    <button type="button" class="ui-btn ui-btn-success js-clear-date">Сбросить</button>
                    <?endif;?>
                </div>
            </div>    
        </form>
    </div>
</div>
<div class="box box-danger">
    <div class="box-header with-border">
        <h3 class="box-title">Фильтрация по исполнителю</h3>
    </div>
    <div class="box-body">
        <form method="GET">
            <input type="hidden" name="stats" value="table" />
            <input type="hidden" name="FROM" value="<?=($_REQUEST['FROM']!='')?$_REQUEST['FROM']:'' ?>" />
            <input type="hidden" name="TO" value="<?=($_REQUEST['TO']!='')?$_REQUEST['TO']:'' ?>" />
            <div class="row">
                <div class="col-md-8">
                    <select class="form-control" name="ISPOLNITEL" required>
                        <option value="0">Все</option>
                        <option value="all_1283" <?=('all_1283'==$_REQUEST['ISPOLNITEL']?'selected':'')?>>Все ОМСУ</option>
                        <option value="all_1282" <?=('all_1282'==$_REQUEST['ISPOLNITEL']?'selected':'')?>>Все ОИВ</option>
                        <?foreach ($arExecutors as $arIspolnitel) : ?>
                            <option
                            value="<?=$arIspolnitel['ID'] ?>"
                            <?=($arIspolnitel['ID']==$_REQUEST['ISPOLNITEL']?'selected':'')?>
                            ><?=$arIspolnitel['NAME'] ?></option>
                        <?endforeach;?>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="ui-btn ui-btn-success">Показать</button>
                </div>
            </div>    
        </form>
    </div>
</div>

<div class="box box-primary">
    <div class="box-header with-border">
        <h3 class="box-title">Таблица</h3>
    </div>

    <div class="box-body">
        <h1 class="text-center">Информация</h1>
        <p class="text-center">о результатах контроля за исполнением поручений Губернатора Тульской области и иных поручений</p>

        <table class="table table-striped" cellpadding="0" cellspacing="0">
            <thead>
                <th class="text-center">Исполнитель</th>
                <th class="text-center">Всего поручений</th>
                <th class="text-center">Выполнено в срок</th>
                <th class="text-center">Выполнено с нарушением сроков</th>
                <th class="text-center">Не выполнено</th>
                <th class="text-center">На исполнении</th>
                <?/*<th class="text-center">Срок исполнения продлевается</th>*/?>
                <th class="text-center">Уровень исполнения</th>
            </thead>
            <tbody>
            <?php
            if (!empty($_REQUEST['ISPOLNITEL']) && false !== mb_strpos($_REQUEST['ISPOLNITEL'], 'all_')) {
                unset($_REQUEST['ISPOLNITEL']);
            }
            foreach ($arResult['STATS_DATA']['ISPOLNITELS_DISCIPLIN'] as $key => $value) {
                if (!empty($_REQUEST['ISPOLNITEL']) && $_REQUEST['ISPOLNITEL'] != $key) {
                    continue;
                }

                // Уровень исполнения = 100 – ((Выполнено с нарушением сроков + Не выполнено) / (Всего поручений – На исполнении))*100.
                $value['percent'] = 100-((($value['srok_narush']+$value['no_ispoln'])/($value['full']-$value['worked']))*100);
                ?>
                <tr>
                    <td><?=$arResult['ISPOLNITELS'][$key]['NAME']?></td>
                    <td class="text-center"><?=$value['full']?></td>
                    <td class="text-center"><?=$value['v_srok']?></td>
                    <td class="text-center"><?=$value['srok_narush']?></td>
                    <td class="text-center"><?=$value['no_ispoln']?></td>
                    <td class="text-center"><?=$value['worked']?></td>
                    <?/*<td class="text-center"><?=$value['in_trust']?></td>*/?>
                    <td class="text-center"><?=is_nan($value['percent'])?'-':number_format($value['percent'], 2)?></td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
    </div>
</div>