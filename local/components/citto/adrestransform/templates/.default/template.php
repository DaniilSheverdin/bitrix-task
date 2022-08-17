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
        
        ?>
        <?
        if($_REQUEST['status']=='ok'){?>
            <div class="card text-white bg-success mb-3" >
              <div class="card-header">Задача доабвлена в очередь на обработку данных #<?=($_REQUEST['file'])?></div>
            </div>
        <?}
        ?>
        <form method="POST" enctype="multipart/form-data" class="js-form-adress">
            <div class="row">
                <div class="col-12 mt-2 mb-2"><h3>Добавление данных</h3></div>
                <div class="col-md-6 mt-2 mb-2">
                    Файл
                </div>
                <div class="col-md-6 mt-2 mb-2">
                    <input type="file" required="" name="FILE">
                </div>
            
                <div class="col-12 mt-3 text-center">
                        <button type="submit" class="ui-btn ui-btn-success" name='submit' value="send">Добавить</button>
                </div>
            </div>
        </form>
        <h3>Данные:</h3>
            <table class="table table-bordered">
                <thead>
                    <th>Задача</th>
                    <th>Исходник</th>
                    <th>Полученный файл</th>
                </thead>
            <?
            foreach ($arResult['FILES'] as $sKey => $arValue) {?>
                <tr>
                <td><?=$sKey?></td>
                <td><a href="<?=$arValue['ish']?>">Скачать</a></td>
                <td>
                    <?
                    if($arValue['progress']=='ok'){?>
                        <a href="<?=$arValue['result']?>">Скачать</a>
                    <?}else{?>
                        В процессе
                    <?}
                    ?>
                    </td>
            </tr>
            <?
            }
            ?>
            
            </table>

