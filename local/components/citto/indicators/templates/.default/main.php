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

global $USER;
global $arrFilter;

?>
<section class="performance-block">
    <div class="wrapper">
        <div class="container-fluid">
            <div class="performance-block__menu">
                <h2 class="menu__header">Показатели</h2>
                <div class="menu__accordions accordion" id="accordion">
                <?
                foreach ($arResult['CATEGORY'] as $sKey => $arValue) {?>
                  <div class="menu__accordion card">
                    <div class="card-header" id="heading<?=$arValue['ID']?>">
                        <h2 class="mb-0">
                            <?if (count($arValue['CHILD'])>0) {?>
                            <button class="btn btn-link btn-block text-left collapsed"
                                    type="button" data-toggle="collapse"
                                    data-parent="#accordion"
                                    data-target="#collapse<?=$arValue['ID']?>"
                                    aria-expanded="false"
                                    aria-controls="collapse<?=$arValue['ID']?>"
                                >
                                <?=$arValue['NAME']?>
                                <?
                                $className = 'success';
                                if (intval($arValue['PERCENT'])<30) {
                                    $className = 'fail';
                                } elseif (intval($arValue['PERCENT']) > 30 && intval($arValue['PERCENT']) < 90) {
                                    $className = 'normal';
                                }
                                ?>
                                <span class="px-2 percent percent--<?=$className?>"><?=$arValue['PERCENT']?>%</span>
                            </button>
                            <?} else {?>
                                <a class="btn btn-link" href="?show=list&filter[CATEGORY][]=<?=$arValue['ID']?>">
                                    <?=$arValue['NAME']?>
                                    <?
                                    $className = 'success';
                                    if (intval($arValue['PERCENT'])<30) {
                                        $className = 'fail';
                                    } elseif (intval($arValue['PERCENT']) > 30 && intval($arValue['PERCENT']) < 90) {
                                        $className = 'normal';
                                    }
                                    ?>
                                    <span class="px-2 percent percent--<?=$className?>"><?=$arValue['PERCENT']?>%</span>
                                </a>
                            <?}?>
                        </h2>
                    </div>

                    <div id="collapse<?=$arValue['ID']?>" class="collapse" aria-labelledby="heading<?=$arValue['ID']?>" data-parent="#accordionExample">
                        <div class="card-body">
                            <?
                            foreach ($arValue['CHILD'] as $sKey2 => $arValue2) {
                                $link = '?show=list&filter[CATEGORY][]=' . $arValue2['ID'];
                                // if ($arValue2['PASSPORT']) {
                                //     $link = '?show=passport&filter[CATEGORY][]=' . $arValue2['ID'];
                                // }
                                ?>
                                <div class="category_field">
                                    <a href="<?=$link?>"><?=$arValue2['NAME']?></a>
                                    <?
                                    $className = 'success';
                                    if (intval($arValue2['PERCENT']) < 30) {
                                        $className = 'fail';
                                    } elseif (intval($arValue2['PERCENT']) > 30 && intval($arValue2['PERCENT']) < 90) {
                                        $className = 'normal';
                                    }
                                    ?>
                                    <span class="px-2 percent percent--<?=$className?>"><?=$arValue2['PERCENT']?>%</span>
                                </div>
                                <?
                            }
                            ?>
                        </div>
                    </div>
                </div>
                <?}
                ?>
                <div class="menu__accordion card">
                    <div class="card-header" id="heading<?=$arValue['ID']?>">
                        <h2 class="mb-0">
                            <a class="btn btn-link" href="?show=list&set_filter=y&arrFilter_2635=<?=abs(crc32(1661))?>">Статистические данные</a>
                        </h2>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="departments-block">
    <div class="wrapper">
        <div class="container-fluid">
            <h2 class="departments-block__header">Подразделения</h2>

            <div class="row">
                <div class="col-12">

                    <div class="departments-block__accordions" id="departments-accordion">
                        <?
                        foreach ($arResult['DEPARTMENTS'] as $sKey => $arValue) {?>
                            <div class="card">
                            <div class="card-header" id="headingDepartment<?=$sKey?>">
                                <h5 class="mb-0">
                                    <?
                                    $link = '?show=list&set_filter=y';
                                    $link .= '&arrFilter_2637_' . abs(crc32($sKey)) . '=Y';
                                    if (count($arValue['CHILD']) > 0) {
                                        foreach ($arValue['CHILD'] as $child) {
                                            $link .= '&arrFilter_2637_' . abs(crc32($child['ID'])) . '=Y';
                                        }
                                    }
                                    ?>
                                    <a class="btn btn-link" href="<?=$link?>"><?=$arValue['NAME']?></a>
                                </h5>
                            </div>
                            <?if (count($arValue['CHILD'])>0) {?>
                            <div id="department<?=$sKey?>" class="collapse" aria-labelledby="headingDepartment<?=$sKey?>" data-parent="#departments-accordion">
                                <div class="card-body">
                                    <ul>
                                    <?
                                    foreach ($arValue['CHILD'] as $sKey2 => $arValue2) {
                                        ?>
                                        <li>
                                            <a href="?show=list&set_filter=y&arrFilter_2637_<?=abs(crc32($sKey2))?>=Y"><?=$arValue2['NAME']?></a>
                                        </li>
                                        <?
                                    }
                                    ?>
                                    </ul>
                                </div>
                            </div>
                            <?}?>
                        </div>
                        <?
                        }
                        ?>
                </div>
            </div>
        </div>
    </div>
</section>
