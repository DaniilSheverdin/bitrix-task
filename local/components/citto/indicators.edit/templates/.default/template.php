<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */
$this->setFrameMode(true);
CJSCore::Init([
    'jquery3',
    'popup',
    'ui',
    // 'amcharts',
    // 'amcharts_serial',
    // 'amcharts_pie',
    // 'amcharts_xy',
    // 'amcharts_radar',
    // 'amcharts_export',
]);

$bIsAdmin = $USER->IsAdmin();
?>
<section class="official">
    <div class="wrapper">
        <div class="container-fluid">
            <h1 class="official__header">Заполнение данных</h1>
        </div>
    </div>
</section>
<?

switch ($_REQUEST['show']) {
    case 'list':
        ?>
        <main class="main">
            <section class="table-section">
                <div class="wrapper">
                    <div class="row">
                        <div class="col-xl-12">
                            <form action="" class="table-section__filter js-form-department-change">
                                <input type="hidden" name="show" value="list">
                                <?
                                if ($bIsAdmin || !empty($arResult['MY_DEPARTMENTS'])) {
                                    ?>
                                    <div class="table-section__select-group">
                                        <label class="select-group__label" for="departments-select">Отделы</label>
                                        <select name="filter[DEPARTMENT][]" class="custom-select select-group__select" id="departments-select">
                                            <option value="">Выберите отдел...</option>
                                            <?
                                            $html = '';
                                            foreach ($arResult['DEPARTMENTS'] as $sKey => $arValue) {
                                                if (count($arValue['CHILD']) > 0) {
                                                    $subHtml = '<optgroup label="' . $arValue['NAME'] . '">';
                                                    $subHtml2 = '';
                                                    if (
                                                        $arValue['ACTIVE_INDICATOR'] &&
                                                        ($bIsAdmin || in_array($sKey, $arResult['MY_DEPARTMENTS']))
                                                    ) {
                                                        $subHtml2 = '<option ' . (in_array($sKey, $_REQUEST['filter']['DEPARTMENT'])?'selected':'') . ' value="' . $arValue['ID'] . '">Без отдела</option>';
                                                    }
                                                    $bAddSubHtml = false;
                                                    foreach ($arValue['CHILD'] as $sKey2 => $arValue2) {
                                                        if ($bIsAdmin || in_array($sKey2, $arResult['MY_DEPARTMENTS'])) {
                                                            if (!$bAddSubHtml) {
                                                                $html .= $subHtml . $subHtml2;
                                                                $bAddSubHtml = true;
                                                            }
                                                            $html .= '<option ' . (in_array($sKey2, $_REQUEST['filter']['DEPARTMENT'])?'selected':'') . ' value="' . $arValue2['ID'] . '">' . $arValue2['NAME'] . '</option>';
                                                        }
                                                        foreach ($arValue2['CHILD'] as $sKey3 => $arValue3) {
                                                            if ($bIsAdmin || in_array($sKey3, $arResult['MY_DEPARTMENTS'])) {
                                                                $html .= '<option ' . (in_array($sKey3, $_REQUEST['filter']['DEPARTMENT'])?'selected':'') . ' value="' . $arValue3['ID'] . '">' . $arValue3['NAME'] . '</option>';
                                                            }
                                                            foreach ($arValue3['CHILD'] as $sKey4 => $arValue4) {
                                                                if ($bIsAdmin || in_array($sKey4, $arResult['MY_DEPARTMENTS'])) {
                                                                    $html .= '<option ' . (in_array($sKey4, $_REQUEST['filter']['DEPARTMENT'])?'selected':'') . ' value="' . $arValue4['ID'] . '">' . $arValue4['NAME'] . '</option>';
                                                                }
                                                            }
                                                        }
                                                    }

                                                    if ($bAddSubHtml) {
                                                        $html .= '</optgroup>';
                                                    } elseif (!empty($subHtml2)) {
                                                        $html .= $subHtml . $subHtml2 . '</optgroup>';
                                                    }
                                                } else {
                                                    if ($bIsAdmin || in_array($sKey, $arResult['MY_DEPARTMENTS'])) {
                                                        $html .= '<option ' . (in_array($sKey, $_REQUEST['filter']['DEPARTMENT'])?'selected':'') . ' value="' . $arValue['ID'] . '">' . $arValue['NAME'] . '</option>';
                                                    }
                                                }
                                            }
                                            echo $html;
                                            ?>
                                        </select>
                                    </div>
                                    <?
                                }
                                ?>
                                <?/*
                                <div class="table-section__select-group">
                                    <label class="select-group__label" for="period-select">Период показателей</label>
                                    <select class="custom-select select-group__select" name="CURRENT_PERIOD" id="period-select">
                                        <?
                                        $prevmonth = '';
                                        foreach ($arResult['PERIODS'] as $period) {
                                            $tsFrom = strtotime($period['from']);
                                            if ($tsFrom >= time()) {
                                                continue;
                                            }
                                            $tsTo = strtotime($period['to']);
                                            $tsMid = strtotime($period['mid']);
                                            $month = FormatDate('f Y', $tsFrom);
                                            if ($month != $prevmonth) {
                                                if (!empty($prevmonth)) {
                                                    ?>
                                                    </optgroup>
                                                    <?
                                                }
                                                ?>
                                                <optgroup label="<?=$month?>">
                                                <?
                                                $prevmonth = $month;
                                            }
                                            $selected = '';
                                            if ($_REQUEST['CURRENT_PERIOD'] == $tsMid) {
                                                $selected = 'selected';
                                            }
                                            ?>
                                            <option value="<?=$tsMid?>" <?=$selected?>>
                                                <?
                                                echo date('d.m.y', $tsFrom);
                                                echo ' - ';
                                                echo date('d.m.y', $tsTo);
                                                if (!$period['edited']) {
                                                    echo ' (Показатели не заполнены)';
                                                }
                                                ?>
                                            </option>
                                            <?
                                        }
                                        ?>
                                    </select>
                                </div>
                                */?>
                                <?
                                if (is_array($arResult['EDITED']) && !empty($arResult['INDICATORS'])) {
                                    if (!$arResult['EDITED']['PREV_DATA']) {
                                        ?>
                                        <p class="text-right">
                                            <i>Последнее изменение &ndash; <?=$arResult['EDITED']['UPDATE'] ?> (<?=$arResult['EDITED']['FIO']?>)</i>
                                            <?
                                            if (
                                                !empty($arResult['CURRENT_PERIOD'])
                                            ) {
                                                ?>
                                                <br/>
                                                <i>Текущий период &ndash; с <?=date('d.m.Y', strtotime($arResult['CURRENT_PERIOD']['from']))?> по <?=date('d.m.Y', strtotime($arResult['CURRENT_PERIOD']['to']))?></i>
                                                <br/>
                                                <?
                                                if (
                                                    strtotime($arResult['EDITED']['DATE']) >= strtotime($arResult['CURRENT_PERIOD']['from']) &&
                                                    strtotime($arResult['EDITED']['DATE']) <= strtotime($arResult['CURRENT_PERIOD']['to'])
                                                ) {
                                                    ?>
                                                    <i style="color:green">Показатели заполнены</i>
                                                    <?
                                                } else {
                                                    ?>
                                                    <i style="color:red">Показатели не заполнены</i>
                                                    <?
                                                }
                                            }
                                            ?>
                                        </p>
                                        <?
                                    } else {
                                        ?>
                                        <p class="text-right">
                                            <?
                                            if (
                                                !empty($arResult['CURRENT_PERIOD'])
                                            ) {
                                                ?>
                                                <br/>
                                                <i>Текущий период &ndash; с <?=date('d.m.Y', strtotime($arResult['CURRENT_PERIOD']['from']))?> по <?=date('d.m.Y', strtotime($arResult['CURRENT_PERIOD']['to']))?></i>
                                                <br/>
                                                <br/>
                                                <i style="color:red">Показатели не заполнены</i>
                                                <?
                                                if (is_array($arResult['EDITED']['PERIOD'])) {
                                                    ?>
                                                    <br/>
                                                    <br/>
                                                    <i>Подставлены показатели за период с <?=date('d.m.Y', strtotime($arResult['EDITED']['PERIOD']['from']))?> по <?=date('d.m.Y', strtotime($arResult['EDITED']['PERIOD']['to']))?></i>
                                                    <?
                                                }
                                            }
                                            ?>
                                        </p>
                                        <?
                                    }
                                }
                                ?>
                            </form>
                        </div>
                        <div class="col-xl-12">
                            <form method="POST">
                               <div class="table-section__table table-section__table--equal-p table-responsive">
                                    <table class="table table-bordered mb-0">
                                        <thead class="table__head--dark">
                                            <tr>
                                                <th scope="col" class="border-left-0 border-top-0 border-bottom-0">№</th>
                                                <th scope="col" class="border-top-0 border-bottom-0">Показатель</th>
                                                <th scope="col" class="border-top-0 border-bottom-0">Месячный план</th>
                                                <th scope="col" class="border-top-0 border-bottom-0">Годовой план</th>
                                                <th scope="col" class="border-top-0 border-bottom-0">Достижение (%)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?
                                            $n = 0;
                                            foreach ($arResult['INDICATORS'] as $sKey => $arValue) {
                                                $n++;
                                                $strClass = 'success';
                                                if ((int)$arValue['BI_DATA']['percent_exec'] < 30) {
                                                    $strClass = 'failed';
                                                } elseif (
                                                    (int)$arValue['BI_DATA']['percent_exec'] > 30 &&
                                                    (int)$arValue['BI_DATA']['percent_exec'] < 90
                                                ) {
                                                    $strClass = 'normal';
                                                }
                                                ?>
                                            <tr id="table-row-1" class="table__indicator--<?=$strClass?>">
                                                <input type="hidden" name="INDICATORS[<?=$arValue['XML_ID']?>][id]" value="<?=$arValue['ID']?>">
                                                <input type="hidden" name="INDICATORS[<?=$arValue['XML_ID']?>][bi_id]" value="<?=$arValue['XML_ID']?>">
                                                <input type="hidden" name="INDICATORS[<?=$arValue['XML_ID']?>][full_name]" value="<?=$arValue['NAME']?>">
                                                <input type="hidden" name="INDICATORS[<?=$arValue['XML_ID']?>][short_name]" value="<?=$arValue['PREVIEW_TEXT']?>">
                                                <input type="hidden" class="js-target-value" name="INDICATORS[<?=$arValue['XML_ID']?>][target_value]" value="<?=$arValue['PROPERTY_TARGET_VALUE_VALUE']?>">
                                                <input type="hidden" class="js-monthly-target-value" name="INDICATORS[<?=$arValue['XML_ID']?>][monthly_target_value]" value="<?=$arValue['PROPERTY_MONTHLY_TARGET_VALUE_VALUE']?>">
                                                <input type="hidden" class="js-target-value-min" name="INDICATORS[<?=$arValue['XML_ID']?>][target_value_min]" value="<?=$arValue['PROPERTY_TARGET_VALUE_MIN_VALUE']?>">
                                                <input type="hidden" class="js-percent-exec-value" name="INDICATORS[<?=$arValue['XML_ID']?>][percent_exec]" value="<?=$arValue['BI_DATA']['percent_exec']?>">
                                                <td scope="row" class="border-left-0 text-left" ><?=$n?></td>
                                                <td scope="row" class="text-left" data-id="<?=$arValue['XML_ID']?>">
                                                    <b><?=$arValue['NAME']?></b>
                                                    <br>
                                                    <i style="margin-top:5px;"><?=$arResult['CATEGORY_NAMES'][$arValue['IBLOCK_SECTION_ID']] ?></i>
                                                    <br>
                                                    <input
                                                        class="form-control js-state-value"
                                                        name="INDICATORS[<?=$arValue['XML_ID']?>][state_value]"
                                                        <?//=(!$arResult['boolAEdit'])?'disabled':'' ?>
                                                        type="text"
                                                        value="<?=$arValue['BI_DATA']['state_value']?>"
                                                        data-inverted="<?=!empty($arValue['PROPERTY_INVERTED_ENUM_ID'])?'true':'false'?>"
                                                        />
                                                    <label>Комментарий:</label>
                                                    <textarea name="INDICATORS[<?=$arValue['XML_ID']?>][comment]" <?//=(!$arResult['boolAEdit'])?'disabled':'' ?> class="form-control"><?=$arValue['BI_DATA']['comment']?></textarea>
                                                </td>
                                                <td class="js-monthly-target"><?=$arValue['PROPERTY_MONTHLY_TARGET_VALUE_VALUE'] ?? '-'?></td>
                                                <td>
                                                    <?
                                                    if (!empty($arValue['PROPERTY_TARGET_VALUE_MIN_VALUE'])) {
                                                        echo $arValue['PROPERTY_TARGET_VALUE_MIN_VALUE'] . '-' . $arValue['PROPERTY_TARGET_VALUE_VALUE'];
                                                    } else {
                                                        echo $arValue['PROPERTY_TARGET_VALUE_VALUE'];
                                                    }
                                                    ?>
                                                </td>
                                                <td class="table__indicator js-percent-exec_view"><?=$arValue['BI_DATA']['percent_exec']?>%</td>
                                            </tr>
                                            <?}?>
                                        </tbody>
                                    </table>
                                </div>
                                <?
                                //if ($arResult['boolAEdit']) {
                                    ?>
                                    <button class="btn table-section__show-result-button" type="submit" name="send" value="Y">Сохранить</button>
                                    <?
                                //}
                                ?>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        </main>
        <?
        break;
    default:
        ?>
        <section class="departments-block">
            <div class="wrapper">
                <div class="container-fluid">
                    <h2 class="departments-block__header">Подразделения</h2>

                    <div class="row">
                        <div class="col-12">
                            <div class="departments-block__accordions" id="departments-accordion">
                                <?
                                foreach ($arResult['DEPARTMENTS'] as $sKey => $arValue) {
                                    ?>
                                    <div class="card">
                                        <div class="card-header" id="headingDepartment<?=$sKey?>">
                                            <h5 class="mb-0">
                                                <?
                                                if (count($arValue['CHILD']) > 0) {
                                                    ?>
                                                    <button class="btn btn-link btn-block text-left collapsed" type="button" data-toggle="collapse" data-parent="#departments-accordion" data-target="#department<?=$sKey?>" aria-expanded="false" aria-controls="department<?=$sKey?>">
                                                        <?=$arValue['NAME']?>
                                                    </button>
                                                    <?
                                                } else {
                                                    ?>
                                                    <a class="btn btn-link" href="?show=list&filter[DEPARTMENT]=<?=$sKey?>"><?=$arValue['NAME']?></a>
                                                    <?
                                                }
                                                ?>
                                            </h5>
                                        </div>
                                        <?
                                        if (count($arValue['CHILD']) > 0) {
                                            ?>
                                            <div id="department<?=$sKey?>" class="collapse" aria-labelledby="headingDepartment<?=$sKey?>" data-parent="#departments-accordion">
                                                <div class="card-body">
                                                    <ul>
                                                        <?
                                                        if ($arValue['ACTIVE_INDICATOR']) {
                                                            ?>
                                                            <li><a href="?show=list&filter[DEPARTMENT][]=<?=$sKey?>">Без отдела</a></li>
                                                            <?
                                                        }
                                                        foreach ($arValue['CHILD'] as $sKey2 => $arValue2) {
                                                            if ($sKey < 0 || $arValue2['ACTIVE_INDICATOR']) {
                                                                ?>
                                                                <li><a href="?show=list&filter[DEPARTMENT][]=<?=$sKey2?>"><?=$arValue2['NAME']?></a></li>
                                                                <?
                                                            }
                                                        }
                                                        ?>
                                                    </ul>
                                                </div>
                                            </div>
                                            <?
                                        }
                                        ?>
                                    </div>
                                    <?
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?
        break;
}
