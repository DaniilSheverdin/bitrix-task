<?use Bitrix\Main\Localization\Loc;
$APPLICATION->SetTitle(Loc::getMessage('TITLE_SHOW_KPI'));

?>
<div class="row">
    <div class="col-12">
        <div class="kpi_rules">
            <div class="row mt-5">
                <div class="col-md-4 mb-3">
                    <div class="small"><h2><?=Loc::getMessage('SELECT_SHOW_KPI_GOVERNMENT')?></h2></div>
                </div>
                <div class="col-md-8">
                    <div class="select-department">
                        <select id="select-show-kpi-gov" placeholder="..." name="government">
                            <option></option>
                            <? foreach ($arResult['GOVERNMENTS'] as $key => $value): ?>
                                <option <?=$_REQUEST['government'] == $key ? 'selected' : ''?> value="<?=$key?>"><?=$value?></option>
                            <?endforeach;?>
                        </select>
                    </div>

                </div>
            </div>
            <? if ($arResult['DEPARTMENTS']): ?>
            <div class="row mt-2">
                <div class="col-md-4 mb-3">
                    <div class="small"><h2><?=Loc::getMessage('SELECT_SHOW_KPI_DEPARTMENT')?></h2></div>
                </div>
                <div class="col-md-8">
                    <div class="select-department">
                        <select id="select-department" placeholder="..." name="department">
                            <option></option>
                            <? foreach ($arResult['DEPARTMENTS'] as $key => $value): ?>
                                <option <?=$_REQUEST['department'] == $key ? 'selected' : ''?> value="<?=$key?>"><?=$value?></option>
                            <?endforeach;?>
                        </select>
                    </div>

                </div>
            </div>
            <?endif;?>
            <? if ($arResult['DEPARTMENTS']): ?>
                <div class="row mt-2">
                    <div class="col-md-4 mb-3">
                        <div class="small"><h2><?=Loc::getMessage('SELECT_SHOW_KPI_MONTH')?></h2></div>
                    </div>
                    <div class="col-md-2">
                        <div class="select-department">
                            <select id="select-date" placeholder="..." name="date">
                                <option></option>
                                <? foreach ($arResult['PERIOD'] as $key => $value): ?>
                                    <option <?=$_REQUEST['date'] == $key ? 'selected' : ''?> value="<?=$key?>"><?=$value?></option>
                                <?endforeach;?>
                            </select>
                        </div>

                    </div>
                </div>
            <?endif;?>
            <div class="mt-5">
                <? if ($arResult['DEPARTMENT_DATA']): ?>

                <? foreach ($arResult['DEPARTMENT_DATA'] as $id => $data): ?>
                    <? $intCountFE = 0 ?>
                    <? foreach ($data as $feName => $fe): ?>
                    <div class="row">
                        <div class="col-12 d-flex">
                            <div class="button_work_position js-switch-icon" type="button" data-toggle="collapse" data-target="#collapseExample<?=$intCountFE?>" aria-expanded="true" aria-controls="collapse">
                                <?=$feName?>
                            </div>
                            <div class="arrow_work_position"><img src="<?=$templatePath?>/icons/angle-up-solid.svg" alt=""></div>
                        </div>
                        <div class="col-12">
                            <div class="collapse show" id="collapseExample<?=$intCountFE?>">
                                <div class="mb-4">
                                    <b><?=Loc::getMessage('TITLE_FORMULA_STATE')?>:</b> <?=$fe['FORMULA']?>
                                </div>
                                <div class="js-staff-form" style="overflow-x: scroll;">
                                    <div class="kpi_table_head">
                                        <div class="column">ФИО</div>
                                        <div class="column width-50">Ставка</div>
                                        <? foreach($fe['KPI_NAMES'] as $label): ?>
                                            <div class="column"><?=$label?></div>
                                        <?endforeach;?>
                                            <div class="column">Критический KPI</div>
                                        <div class="column">KPI развития</div>
                                        <div class="column">Комментарий</div>
                                        <div class="column">Итоговое значение KPI</div>
                                    </div>
                                    <? foreach ($fe['USERS'] as $userID => $user): ?>
                                        <form id="<?=$userID?>" data-actions-id="<?=$id?>" class="kpi_table_body js-staff-form">
                                            <div class="column"><?=$user['FIO']?></div>
                                            <div class="column width-50"><?=$user['RATE']?></div>

                                            <? foreach ($user['KPIS'] as $kpi): ?>
                                                <div class="column <?=$kpiValue['IS_RED'] == 'Y' ? 'red' : ''?>"><?=$kpi['VALUE']?></div>
                                            <?endforeach;?>

                                            <div class="column"><?=$user['CRITICAL']?></div>
                                            <div class="column"><?=$user['PROGRESS']?></div>
                                            <div class="column"><?=$user['COMMENT']?></div>

                                            <div class="column"><?=$user['RESULT']?></div>
                                        </form>
                                    <?endforeach;?>
                                </div>
                                <div class="actions-row mt-3" id="<?=$id?>"></div>

                            </div>
                        </div>
                    </div>
                        <? $intCountFE++ ?>
                    <?endforeach;?>
                <?endforeach;?>

                <?else:?>
                <?if (isset($_REQUEST['government']) && isset($_REQUEST['date'])):?>
                <div class="col-12">Нет данных</div>
                <?endif;?>
                <?endif?>
            </div>


        </div>
    </div>
</div>

