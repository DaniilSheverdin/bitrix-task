<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page;

$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass.' ' : '').'no-all-paddings no-background');
\Bitrix\Main\UI\Extension::load(array("ui.buttons", "ui.alerts", "ui.tooltip", "ui.hint"));
\CJSCore::Init("loader");

Page\Asset::getInstance()->addJs($templateFolder.'/js/utils.js');
Page\Asset::getInstance()->addJs($templateFolder.'/js/stresslevel.js');
Page\Asset::getInstance()->addJs($templateFolder.'/js/grats.js');
Page\Asset::getInstance()->addJs($templateFolder.'/js/profilepost.js');
Page\Asset::getInstance()->addJs($templateFolder.'/js/tags.js');
Page\Asset::getInstance()->addJs($templateFolder.'/js/tags-users-popup.js');
Page\Asset::getInstance()->addJs($templateFolder.'/js/form-entity.js');
Page\Asset::getInstance()->addCss('/bitrix/components/bitrix/socialnetwork.blog.blog/templates/.default/style.css');
Page\Asset::getInstance()->addCss('/local/templates/.default/components/bitrix/intranet.user.profile/.default/style.css');

$this->SetViewTarget('inside_pagetitle');
?>
<span
    <?/*onclick="BX.SidePanel.Instance.open('<?=SITE_DIR?>bizproc/processes/?livefeed=y&list_id=530&element_id=0', {width: 1100});"*/?>
    onclick="window.open('<?=SITE_DIR?>bizproc/processes/?livefeed=y&list_id=530&element_id=0');"
    class="ui-btn ui-btn-light-border ui-btn-themes"
>
    Изменить данные
</span>
<?
$this->EndViewTarget();
?>

<div class="intranet-user-profile" id="intranet-user-profile-wrap">
    <div class="intranet-user-profile-column-left">
        <div class="intranet-user-profile-container">
            <div class="intranet-user-profile-container-header">
                <div class="intranet-user-profile-container-title">Паспортные данные</div>
            </div>

            <?php
            $arPassportShowFields = [
                'Series' => 'Серия',
                'Number' => 'Номер',
                'IssuedBy' => 'Выдан',
                'DateOfIssue' => 'Дата выдачи',
                'DivisionCode' => 'Код подразделения',
            ];
            foreach ($arPassportShowFields as $key => $value) {
                if (!isset($arResult['PERSONAL_DATA']['Passport'][ $key ]) || empty($arResult['PERSONAL_DATA']['Passport'][ $key ])) {
                    continue;
                }
                ?>
                <div class="intranet-user-profile-container-body">
                    <div class="intranet-user-profile-container-body-title">
                        <?=$value;?>
                    </div>
                    <div class="intranet-user-profile-container-body-text">
                        <?=$arResult['PERSONAL_DATA']['Passport'][ $key ]?>
                    </div>
                </div>
                <?php
            }

            if (!empty($arResult['PERSONAL_DATA']['AddressOfRegistration'])) {
                ?>
                <div class="intranet-user-profile-container-body">
                    <div class="intranet-user-profile-container-body-title">
                        Адрес по прописке
                    </div>
                    <div class="intranet-user-profile-container-body-text">
                        <?=$arResult['PERSONAL_DATA']['AddressOfRegistration']?>
                    </div>
                </div>
                <?php
            }

            if (!empty($arResult['PERSONAL_DATA']['AddressOfResidence'])) {
                ?>
                <div class="intranet-user-profile-container-body">
                    <div class="intranet-user-profile-container-body-title">
                        Адрес места проживания
                    </div>
                    <div class="intranet-user-profile-container-body-text">
                        <?=$arResult['PERSONAL_DATA']['AddressOfResidence']?>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
    </div>

    <div class="intranet-user-profile-column-right">
        <div class="intranet-user-profile-container">
            <div class="intranet-user-profile-container-header">
                <div class="intranet-user-profile-container-title">Образование</div>
            </div>
            <?php
            if (is_array($arResult['PERSONAL_DATA']['EducationList'])) {
                if (is_array($arResult['PERSONAL_DATA']['EducationList']['Education'][0])) {
                    $arEducationList = $arResult['PERSONAL_DATA']['EducationList']['Education'];
                } else {
                    $arEducationList = $arResult['PERSONAL_DATA']['EducationList'];
                }

                $arEducationShowFields = [
                    'Type' => 'Уровень образования',
                    'Speciality' => 'Специальность по диплому',
                    'Iinstitution' => 'Образовательное учреждение',
                    'YearOfEnd' => 'Год окончания обучения',
                ];
                $iEducationsCnt = count($arEducationList);

                foreach ($arEducationList as $i => $arEducation) {
                    foreach ($arEducationShowFields as $key => $value) {
                        if (!isset($arEducation[ $key ]) || empty($arEducation[ $key ])) {
                            continue;
                        }
                        ?>
                        <div class="intranet-user-profile-container-body">
                            <div class="intranet-user-profile-container-body-title">
                                <?=$value;?>
                            </div>
                            <div class="intranet-user-profile-container-body-text">
                                <?=$arEducation[ $key ]?>
                            </div>
                        </div>
                        <?php
                    }
                    echo (($i+1) < $iEducationsCnt) ? '<hr/>' : '';
                }
            }
            ?>
        </div>

        <?php
        if (false && is_array($arResult['PERSONAL_DATA']['Relatives']['Relative'])) {
            ?>
        <div class="intranet-user-profile-container">
            <div class="intranet-user-profile-container-header">
                <div class="intranet-user-profile-container-title">Сведения о родственниках</div>
            </div>
            <?php
            if (is_array($arResult['PERSONAL_DATA']['Relatives']['Relative'][0])) {
                $arRelativeList = $arResult['PERSONAL_DATA']['Relatives']['Relative'];
            } else {
                $arRelativeList = $arResult['PERSONAL_DATA']['Relatives'];
            }

            $arRelativeShowFields = [
                'Kinship' => 'Тип родственной связи',
                'Name' => 'ФИО',
                'DateOfBirth' => 'Дата рождения',
                'DateOfMarriage' => 'Дата заключения брака',
                'PlaceOfWork' => 'Место работы',
                'AddressOfResidence' => 'Место проживания',
            ];
            $iRelativeCnt = count($arRelativeList);

            foreach ($arRelativeList as $i => $arRelative) {
                foreach ($arRelativeShowFields as $key => $value) {
                    if (!isset($arRelative[ $key ]) || empty($arRelative[ $key ])) {
                        continue;
                    }
                    ?>
                    <div class="intranet-user-profile-container-body">
                        <div class="intranet-user-profile-container-body-title">
                            <?=$value?>
                        </div>
                        <div class="intranet-user-profile-container-body-text">
                            <?=$arRelative[ $key ]?>
                        </div>
                    </div>
                    <?php
                }
                echo (($i+1) < $iRelativeCnt) ? '<hr/>' : '';
            }
            ?>
        </div>
            <?
        }
        ?>
    </div>
</div>
