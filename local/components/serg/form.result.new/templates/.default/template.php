<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}
?>
<div class="bi-position-relative">
<h3>Показатели</h3>

<? if (!$arResult['boolAEdit']) { ?>
    <div class="ui-alert ui-alert-danger">
        <?=$arResult['MES_NO_CHANGE_DATA']?>
    </div>
<? } ?>

<? if ($arResult['INDICATORS_SUCCESS']) { ?>
    <div class="ui-alert ui-alert-success">
        <?=$arResult['INDICATORS_SUCCESS']?>
    </div>
<? } ?>
<div id="act-info">
    <? if (!$arResult['boolAEdit']) { ?>
        <? if (empty($arResult['ACTUAL'][$arResult['control']]['noact'])) { ?>
            <div class="input-group ui-alert ui-alert-success">
                <p>Данные по управлению "<?=$arResult['control']?>" актуализированы.</p>
            </div>
        <? } elseif ($arResult['ACTUAL'][$arResult['control']]['noact'][0] == '') { ?>
            <div class="input-group ui-alert ui-alert-danger">
                <p>Данные по управлению не были актуализированы.</p>
            </div>
        <? } else { ?>
            <div class="input-group ui-alert ui-alert-danger">
                <div>
                    <p>Данные по управлению "<?=$arResult['control']?>" актуализированы частично, кроме:</p>
                    <ul>
                        <? foreach ($arResult['ACTUAL'][$arResult['control']]['noact'] as $item) { ?>
                            <li><?=$item?>;</li>
                        <? } ?>
                    </ul>
                </div>
            </div>
        <? } ?>
    <? } ?>
    <div class="input-group control-input">
        Дата последнего обновления: <strong><?=$arResult['minDate']?> г.</strong>
    </div>
</div>

<form id="form-bi" class="form-bi" action="" method="POST">

    <div class="input-group control-input">
        <div class="input-group-label">
            <label for="control">Выберите управление:</label>
        </div>
        <div class="input-group-field">
            <select type="text" name="control" id="control">
                <? foreach ($arResult['INDICATORS']['CONTROLS'] as $key => $control) : ?>
                    <option value="<?=$key?>"
                        <?=(isset($_POST['control']) && $_POST['control'] == $key) ? 'selected' : '' ?>
                    ><?=$control?></option>
                <? endforeach; ?>
            </select>
        </div>
    </div>
    <div class="input-group control-input">
        <div class="input-group-label">
            <label for="control">Выберите отдел:</label>
        </div>
        <div class="input-group-field">
            <select type="text" name="otdel" id="otdel">
                <? foreach ($arResult['INDICATORS']['DEPARTMENTS'] as $key => $department) { ?>
                    <option value="<?=$key?>"
                        <?=(isset($_REQUEST['otdel']) && $_REQUEST['otdel'] == $key) ? 'selected' : '' ?>
                    ><?=$department?></option>
                <? } ?>
            </select>
        </div>
    </div>
    <div class="input-group control-input">
        <a id="indicators-csv" href="<?=$APPLICATION->GetCurPage()?>?department=<?=$_POST['control'] ? $_POST['control'] : $arResult['DEFAULT_CONTROL_TYPE']?>&otdel=<?=$_POST['otdel'] ? $_POST['otdel'] : '0'?>&format=csv" target="_blank"><?=$arResult['LABEL_DOWNLOAD_BY_CSV']?></a>
    </div>

    <input type="hidden" name="fio" value="<?=$arResult['USER_NAME']?>">
    <div class="bi_table">
        <div class="bi_table_header">
            <div class="full-name">Полное наименование показателя</div>
            <div class="short-name">Краткое наименование показателя</div>
            <div class="base-set">Основание установления целевого показателя</div>
            <div class="target-value">Целевое значение</div>
            <div class="state-value">Текущее значение</div>
            <div class="percent_exec">% исполнения</div>
            <div class="last-value">Значение за предыдущий период</div>
            <div class="comment">Примечание</div>
            <div class="date">Дата</div>
            <div class="date-last-change">Дата последнего измененния</div>
        </div>
        <div class="bi_table_body">
            <? for ($i=0; $i < count($arResult['INDICATORS']['FULL_NAME']); $i++) { ?>
            <div class="string">
                <input type="hidden" name="INDICATORS[<?=$i?>][NAME]" id="full_name" value="<?=$arResult['INDICATORS']['FULL_NAME'][$i]['TEXT']?>">
                <div class="full-name"><?=$arResult['INDICATORS']['FULL_NAME'][$i]['TEXT']?></div>

                <input type="hidden" name="INDICATORS[<?=$i?>][ATT_SHORT_NAME]" id="short_name" value="<?=$arResult['INDICATORS']['SHORT_NAME'][$i]['TEXT']?>">
                <div class="short-name"><?=$arResult['INDICATORS']['SHORT_NAME'][$i]['TEXT']?></div>

                <input type="hidden" name="INDICATORS[<?=$i?>][ATT_BASE_SET]" id="base_set" value="<?=$arResult['INDICATORS']['BASE_SET'][$i]['TEXT']?>">
                <div class="base-set"><?=$arResult['INDICATORS']['BASE_SET'][$i]['TEXT']?></div>

                <input type="hidden" name="INDICATORS[<?=$i?>][ID]" id="id" value="<?=$arResult['DB'][$i]['id']?>">
                <input type="hidden" name="INDICATORS[<?=$i?>][BI_ID]" id="bi_id" value="<?=$arResult['INDICATORS']['BI_ID'][$i]?>">
                <input type="hidden" name="INDICATORS[<?=$i?>][OTDEL]" id="otdel" value="<?=$arResult['INDICATORS']['OTDEL'][$i]?>">

                <div class="target-value">
                    <input type="text"
                           name="INDICATORS[<?=$i?>][ATT_TARGET_VALUE]"
                           id="target_value"
                           value="<?=$arResult['INDICATORS']['TARGET_VALUE'][$i]?>"
                           readonly
                    >
                </div>

                <div class="state-value">
                    <input type="text"
                           name="INDICATORS[<?=$i?>][ATT_STATE_VALUE]"
                           placeholder="Введите значение"
                           value="<?=($_POST['INDICATORS'][$i]['ATT_STATE_VALUE']) ? $_POST['INDICATORS'][$i]['ATT_STATE_VALUE'] : $arResult['DB'][$i]['state_value']?>"
                           id="state_value"
                           <?=($arResult['boolAEdit']) ? '' : ' readonly'?>
                    >
                </div>
                <div class="percent_exec">
                    <input type="text"
                           name="INDICATORS[<?=$i?>][ATT_PERCENT_EXEC]"
                           value="<?=($_POST['INDICATORS'][$i]['ATT_PERCENT_EXEC']) ? $_POST['INDICATORS'][$i]['ATT_PERCENT_EXEC'] : $arResult['DB'][$i]['percent_exec']?>"
                           id="percent_exec"
                           readonly
                    >
                </div>
                <div class="last-value">
                    <span><?=(!empty($arResult['DB'][$i]['state_value_old']) ? $arResult['DB'][$i]['state_value_old'] : '-')?></span>
                </div>
                <div class="comment">
                    <textarea name="INDICATORS[<?=$i?>][ATT_COMMENT]"
                              id="comment" cols="30"
                              rows="5"
                              <?=($arResult['boolAEdit']) ? '' : ' readonly'?>
                    ><?=(isset($_POST['INDICATORS'][$i]['ATT_COMMENT']) ? $_POST['INDICATORS'][$i]['ATT_COMMENT'] : $arResult['DB'][$i]['comment'])?></textarea>
                </div>

                <div class="date">
                    <input type="text"
                           readonly
                           name="INDICATORS[<?=$i?>][ATT_DATE]"
                           value="<?=((!$arResult['boolAEdit'] && !is_null($arResult['DB'][$i]['date'])) ? $arResult['DB'][$i]['date']->toString() : date('d.m.Y'))?>"
                           id="date">
                </div>
                <div class="date-last-change">
                    <span><?=((!is_null($arResult['DB'][$i]['date_last_change'])) ? $arResult['DB'][$i]['date_last_change']->toString() . ' г.' : '-')?></span>
                </div>
            </div>
            <? } ?>
        </div>
    </div>
    <div class="action-list-buttons" style="<?=(!$arResult['boolAEdit']) ? 'display: none' : ''?>">
        <button class="ui-btn ui-btn-success" type="submit" id="send-form-bi">Сохранить</button>
    </div>
</form>
</div>
