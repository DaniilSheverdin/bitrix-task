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
CJSCore::Init(array("jquery", 'popup', 'ui'));

global $USER;
switch ($_REQUEST['action']) {
    case 'list':
    ?>
    <form method="get">
        <div class="row">
        <div class="col-6">
            <input
                class="form-control"
                type="text"
                name="date"
                value="<?=($_REQUEST['date']!='')?$_REQUEST['date']:date('d.m.Y');?>"
                onclick="BX.calendar({node: this, field: this, bTime: false});">
            <input type="hidden" name="action" value="list">
        </div>
        <div class="col-6">
            <button type="submit" class="ui-btn ui-btn-primary">Показать</button>
        </div>
    </div>
        
    </form>
    <h1 class="text-center mb-3">Данные по местоположению на <?=$arResult['DATE']?></h1>
    <table class="table table-bordered">
        <thead>
            <th colspan="2"><?=$arResult['SECTION']['NAME']?></th>
        </thead>
        <?php
        foreach ($arResult['USERS_SECTION'][$arResult['SECTION']['ID']] as $sKeyUser => $arUser) {
            ?>
            <tr <?=($arResult['LIST_TIMES'][$arUser['ID']]['PROPERTY_ADRESS_VALUE']!='')?'':'style="background-color:#ffbac0;"';?>>
                <td><?=$arUser['USER_FULLNAME']?></td>
                <td><?
                    if ($arResult['LIST_TIMES'][$arUser['ID']]['PROPERTY_ADRESS_VALUE']!='') {
                        echo $arResult['LIST_TIMES'][$arUser['ID']]['PROPERTY_ADRESS_VALUE'];
                    } else {
                        echo '-';
                    }
                ?></td>
            </tr>   
            <?php
        }

        foreach ($arResult['SECTION']['CHILDS'] as $sKey => $arSect) {
            ?>
            <tr><td colspan="2"><b><?=$arSect['NAME']?></b></td></tr>
                <tr>
                <td><b>Сотрудник<b></td>
                <td><b>Адрес</b></td>
            </tr>
            <?php
            foreach ($arResult['USERS_SECTION'][$arSect['ID']] as $sKeyUser => $arUser) {
                ?>
                <tr <?=($arResult['LIST_TIMES'][$arUser['ID']]['PROPERTY_ADRESS_VALUE']!='')?'':'style="background-color:#ffbac0;"';?>>
                    <td><?=$arUser['USER_FULLNAME']?></td>
                    <td><?
                        if ($arResult['LIST_TIMES'][$arUser['ID']]['PROPERTY_ADRESS_VALUE']!='') {
                            echo $arResult['LIST_TIMES'][$arUser['ID']]['PROPERTY_ADRESS_VALUE'];
                        } else {
                            echo '-';
                        }
                    ?></td>
                </tr>   
                <?php
            }
        }
        ?>
    </table>
        <?
        break;

    default:
        ?>
        <input type="hidden" class="js-adress-form" name="js-adress-form" value="active">
        <form method="POST" class="js-form-adress">
            <div class="row">
                <div class="col-12 mt-2 mb-2"><h3>Добавление данных</h3></div>
                <div class="col-md-6 mt-2 mb-2">
                    Сотрудник
                </div>
                <div class="col-md-6 mt-2 mb-2">
                    <input type="text" class="form-control" readonly="" name="USER_NAME" value="<?=$arResult['USER']['LAST_NAME'].' '.$arResult['USER']['NAME'].' '.$arResult['USER']['SECOND_NAME']?>">
                    <input type="hidden" name="USER" value="<?=$USER->GetID()?>">
                </div>
                <div class="col-md-6 mt-2 mb-2">
                    Дата
                </div>
                <div class="col-md-6 mt-2 mb-2">
                    <input type="text" class="form-control" readonly required="" name="DATE" value="<?=date('d.m.Y')?>">
                </div>
                <div class="col-md-6 mt-2 mb-2">
                    Адрес
                </div>
                <div class="col-md-6 mt-2 mb-2">
                    <input type="text" class="form-control adress-input w-80" style="width:80%;float:left;" name="ADRESS" value="">
                    <input type="hidden" class="latitude-input" name="LAT" value="">
                    <input type="hidden" class="longitude-input" name="LNG" value="">
                    <button class="ui-btn ui-btn-primary ui-btn-icon-business js-geoposition-search"></button>
                </div>
                <div class="col-12 mt-3 text-center">
                        <button type="submit" class="ui-btn ui-btn-success" name='submit' value="send">Добавить местоположение</button>
                </div>
            </div>
        </form>

        <h3>Данные:</h3>
        <table class="table table-bordered">
            <thead>
                <th>Сотрудник</th>
                <th>Дата</th>
                <th>Адрес</th>
            </thead>
            <?php
            foreach ($arResult['TIMES'] as $sKey => $arTime) {
                ?>
                <tr>
                    <td><?=$arTime['USER_FULLNAME']?></td>
                    <td><?=$arTime['PROPERTY_DATE_VALUE']?></td>
                    <td><?=$arTime['PROPERTY_ADRESS_VALUE']?></td>
                </tr>
                <?php
            }
        ?>
        </table>
        <?

        if ($arResult['SECTION']['ID']!='') {
            ?>
            <h1 class="text-center mr-3 mb-3">Данные по местоположению на <?=$arResult['DATE']?></h1>
            <table class="table table-bordered">
                
            <thead>
                <th colspan="2"><?=$arResult['SECTION']['NAME']?></th>
            </thead>
            <?php
            foreach ($arResult['USERS_SECTION'][$arResult['SECTION']['ID']] as $sKeyUser => $arUser) {
                ?>
                <tr <?=($arResult['LIST_TIMES'][$arUser['ID']]['PROPERTY_ADRESS_VALUE']!='')?'':'style="background-color:#ffbac0;"';?>>
                    <td><?=$arUser['USER_FULLNAME']?></td>
                    <td><?
                    if ($arResult['LIST_TIMES'][$arUser['ID']]['PROPERTY_ADRESS_VALUE']!='') {
                        echo $arResult['LIST_TIMES'][$arUser['ID']]['PROPERTY_ADRESS_VALUE'];
                    } else {
                        echo '-';
                    }
                    ?></td>
                </tr>   
                <?php
            }

            foreach ($arResult['SECTION']['CHILDS'] as $sKey => $arSect) {
                ?>
                <tr><td colspan="2"><b><?=$arSect['NAME']?></b></td></tr>
                    <tr>
                    <td><b>Сотрудник<b></td>
                    <td><b>Адрес</b></td>
                </tr>
                <?php
                foreach ($arResult['USERS_SECTION'][$arSect['ID']] as $sKeyUser => $arUser) {
                    ?>
                    <tr <?=($arResult['LIST_TIMES'][$arUser['ID']]['PROPERTY_ADRESS_VALUE']!='')?'':'style="background-color:#ffbac0;"';?>>
                        <td><?=$arUser['USER_FULLNAME']?></td>
                        <td><?
                        if ($arResult['LIST_TIMES'][$arUser['ID']]['PROPERTY_ADRESS_VALUE']!='') {
                            echo $arResult['LIST_TIMES'][$arUser['ID']]['PROPERTY_ADRESS_VALUE'];
                        } else {
                            echo '-';
                        }
                        ?></td>
                    </tr>   
                    <?php
                }
            }
            ?>
            </table>
            <?php
        }
        break;
}
