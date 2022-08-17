<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page;

$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass.' ' : '').'no-all-paddings no-background');
\Bitrix\Main\UI\Extension::load(array("ui.buttons", "ui.alerts", "ui.tooltip", "ui.hint"));

CJSCore::Init("loader", 'jquery', 'date', 'ui');

Page\Asset::getInstance()->addJs($templateFolder.'/js/utils.js');
Page\Asset::getInstance()->addJs($templateFolder.'/js/stresslevel.js');
Page\Asset::getInstance()->addJs($templateFolder.'/js/grats.js');
Page\Asset::getInstance()->addJs($templateFolder.'/js/profilepost.js');
Page\Asset::getInstance()->addJs($templateFolder.'/js/tags.js');
Page\Asset::getInstance()->addJs($templateFolder.'/js/tags-users-popup.js');
Page\Asset::getInstance()->addJs($templateFolder.'/js/form-entity.js');
Page\Asset::getInstance()->addCss('/bitrix/components/bitrix/socialnetwork.blog.blog/templates/.default/style.css');
Page\Asset::getInstance()->addCss('/local/templates/.default/components/bitrix/intranet.user.profile/.default/style.css');

function pluralForm($n, $form1, $form2, $form5)
{
    $n = abs(intval($n)) % 100;
    $n1 = $n % 10;
    if ($n > 10 && $n < 20) {
        return $n.' '.$form5;
    }
    if ($n1 > 1 && $n1 < 5) {
        return $n.' '.$form2;
    }
    if ($n1 == 1) {
        return $n.' '.$form1;
    }
    return $n.' '.$form5;
}

?>

<div class="intranet-user-profile" id="intranet-user-profile-wrap">
    <div class="intranet-user-profile-column-left">
        <div class="intranet-user-profile-container">
            <div class="intranet-user-profile-container-header">
                <div class="intranet-user-profile-container-title">Карьера</div>
            </div>

            <? if (isset($arResult['PERSONAL_DATA']['OrderRank'])): ?>
            <div class="intranet-user-profile-container-body">
                <div class="intranet-user-profile-container-body-title">
                    Классный чин
                </div>
                <div class="intranet-user-profile-container-body-text">
                    <?=$arResult['PERSONAL_DATA']['OrderRank']?>
                </div>
            </div>
            <? endif; ?>

            <? if (isset($arResult['PERSONAL_DATA']['Experience']['General']['Years'])): ?>
            <div class="intranet-user-profile-container-body">
                <div class="intranet-user-profile-container-body-title">
                    Общий трудовой стаж
                </div>
                <div class="intranet-user-profile-container-body-text">
                    <?=pluralForm($arResult['PERSONAL_DATA']['Experience']['General']['Years'], 'год', 'года', 'лет') . ' '
                    . pluralForm($arResult['PERSONAL_DATA']['Experience']['General']['Months'], 'месяц', 'месяца', 'месяцев') . ' '
                    . pluralForm($arResult['PERSONAL_DATA']['Experience']['General']['Days'], 'день', 'дня', 'дней')?>
                </div>
            </div>
            <? endif; ?>

            <? if (isset($arResult['PERSONAL_DATA']['Experience']['State']['Years'])): ?>
                <div class="intranet-user-profile-container-body">
                    <div class="intranet-user-profile-container-body-title">
                        Стаж на надбавку за выслугу лет (ГС)
                    </div>
                    <div class="intranet-user-profile-container-body-text">
                        <?=pluralForm($arResult['PERSONAL_DATA']['Experience']['State']['Years'], 'год', 'года', 'лет') . ' '
                        . pluralForm($arResult['PERSONAL_DATA']['Experience']['State']['Months'], 'месяц', 'месяца', 'месяцев') . ' '
                        . pluralForm($arResult['PERSONAL_DATA']['Experience']['State']['Days'], 'день', 'дня', 'дней')?>
                    </div>
                </div>
            <? endif; ?>

            <? if (isset($arResult['PERSONAL_DATA']['Experience']['NotState']['Years'])): ?>
                <div class="intranet-user-profile-container-body">
                    <div class="intranet-user-profile-container-body-title">
                        Стаж на надбавку за выслугу лет (не ГС)
                    </div>
                    <div class="intranet-user-profile-container-body-text">
                        <?=pluralForm($arResult['PERSONAL_DATA']['Experience']['NotState']['Years'], 'год', 'года', 'лет') . ' '
                        . pluralForm($arResult['PERSONAL_DATA']['Experience']['NotState']['Months'], 'месяц', 'месяца', 'месяцев') . ' '
                        . pluralForm($arResult['PERSONAL_DATA']['Experience']['NotState']['Days'], 'день', 'дня', 'дней')?>
                    </div>
                </div>
            <? endif; ?>

            <?
            /*
            if ($arResult['HAS_PAYSLIP']) {
                ?>
                <div class="intranet-user-profile-container-body">
                    <div class="intranet-user-profile-container-body-title">
                        Расчетный листок
                    </div>
                    <div class="intranet-user-profile-container-body-text">
                        <button class="ui-btn ui-btn-success ui-btn-themes js-raschetnyy-listok">Запросить</button>
                        <?include(__DIR__ . '/raschetnyy-listok.php');?>
                    </div>
                </div>
                <?php
            }
            */
            ?>
        </div>
    </div>

    <div class="intranet-user-profile-column-right">
        <div class="intranet-user-profile-container">
            <div class="intranet-user-profile-container-header">
                <div class="intranet-user-profile-container-title">Информация об отпусках</div>
            </div>

            <?php
            if (is_array($arResult['PERSONAL_DATA']['VacationList']['VacationScheduleRecord'])) {
                ?>
            <div class="intranet-user-profile-container-body">
                <div class="intranet-user-profile-container-body-title">
                    График отпусков
                </div>
                <div class="intranet-user-profile-container-body-text">
                    <?php
                    if (is_array($arResult['PERSONAL_DATA']['VacationList']['VacationScheduleRecord'][0])) {
                        $arVacationList = $arResult['PERSONAL_DATA']['VacationList']['VacationScheduleRecord'];
                    } else {
                        $arVacationList = $arResult['PERSONAL_DATA']['VacationList'];
                    }
                    foreach ($arVacationList as $arVacation) {
                        echo date('d.m.Y', strtotime($arVacation['DateStart'])) . ' / ' . $arVacation['DaysCount'] . '<br/>';
                    }
                    ?>
                </div>
            </div>
                <?php
            }

            if (!empty($arResult['PERSONAL_DATA']['Vacation'])) {
                ?>
            <div class="intranet-user-profile-container-body">
                <div class="intranet-user-profile-container-body-title">
                    Количество положенных дней отпуска
                </div>
                <div class="intranet-user-profile-container-body-text">
                    <?=pluralForm($arResult['PERSONAL_DATA']['Vacation']['DaysCount'], 'день', 'дня', 'дней');?>
                </div>
            </div>

            <div class="intranet-user-profile-container-body">
                <div class="intranet-user-profile-container-body-title">
                    Использовано
                </div>
                <div class="intranet-user-profile-container-body-text">
                    <?=pluralForm($arResult['PERSONAL_DATA']['Vacation']['DaysUsed'], 'день', 'дня', 'дней') . ' '
                    . ' за период '
                    . date('d.m.Y', strtotime($arResult['PERSONAL_DATA']['Vacation']["WorkingPeriod"]['DateStart']))
                    . ' - ' . date('d.m.Y', strtotime($arResult['PERSONAL_DATA']['Vacation']["WorkingPeriod"]['DateEnd']));?>
                </div>
            </div>

            <div class="intranet-user-profile-container-body">
                <div class="intranet-user-profile-container-body-title">
                    Необходимо использовать
                </div>
                <div class="intranet-user-profile-container-body-text">
                    <?=pluralForm(intval($arResult['PERSONAL_DATA']['Vacation']['DaysCount']-$arResult['PERSONAL_DATA']['Vacation']['DaysUsed']), 'день', 'дня', 'дней');?> до  <?=date('d.m.Y', strtotime($arResult['PERSONAL_DATA']['Vacation']["WorkingPeriod"]['DateEnd']))?>
                </div>
            </div>
                <?php
            }

            if (count($arResult['PERSONAL_DATA']['VacationLeftovers']) > 0) {
                ?>
            <div class="intranet-user-profile-container-body">
                <div class="intranet-user-profile-container-body-title">
                    Остатки неиспользованного отпуска
                </div>
                <div class="intranet-user-profile-container-body-text">
                    <?php
                    $arVacations = [];
                    foreach ($arResult['PERSONAL_DATA']['VacationLeftovers'] as $arVacation) {
                        $arVacations[] =  $arVacation['WorkingPeriod']['Representation'] . ' / ' . pluralForm(intval($arVacation['WorkingPeriod']['NotUsed']), 'день', 'дня', 'дней');
                    }
                    echo implode('<br/>', $arVacations);
                    ?>
                </div>
            </div>
                <?php
            }
            ?>
            <div class="intranet-user-profile-container-body">
                <div class="intranet-user-profile-container-body-text">
                    <a class="ui-btn ui-btn-success" href="/planner/" target="_blank">Планирование отпусков</a>
                </div>
            </div>
        </div>

        <?php
        if (is_array($arResult['PERSONAL_DATA']['Awards']['Award'])) {
            if (is_array($arResult['PERSONAL_DATA']['Awards']['Award'][0])) {
                $arAwardList = $arResult['PERSONAL_DATA']['Awards']['Award'];
            } else {
                $arAwardList = $arResult['PERSONAL_DATA']['Awards'];
            }

            $arAwardShowFields = [
                'OrderNumber' => 'Номер приказа',
                'OrderDate' => 'Дата приказа',
                'DocType' => 'Вид документа',
                'CertificateNumber' => 'Номер удостоверения',
                'AwardNumber' => 'Номер награды',
            ];
            ?>
            <div class="intranet-user-profile-container">
                <div class="intranet-user-profile-container-header">
                    <div class="intranet-user-profile-container-title">Мои Награды</div>
                </div>

            <?php
            $iAwardCnt = count($arAwardList);
            foreach ($arAwardList as $i => $arAward) {
                ?>
                <div class="intranet-user-profile-container-body">
                    <div class="intranet-user-profile-container-body-text">
                        <?=$arAward['Name']?>
                    </div>
                </div>
                <?php
                foreach ($arAwardShowFields as $key => $value) {
                    if (!isset($arAward[ $key ]) || empty($arAward[ $key ])) {
                        continue;
                    }
                    ?>
                    <div class="intranet-user-profile-container-body">
                        <div class="intranet-user-profile-container-body-title">
                            <?=$value?>
                        </div>
                        <div class="intranet-user-profile-container-body-text">
                            <?=$arAward[ $key ]?>
                        </div>
                    </div>
                    <?php
                }
                echo (($i+1) < $iAwardCnt) ? '<hr/>' : '';
            }
            ?>
            </div>
            <?php
        }
        ?>
    </div>
</div>
