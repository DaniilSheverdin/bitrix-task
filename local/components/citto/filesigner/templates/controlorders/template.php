<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

/** @var array $arParams */
/** @var array $arResult */

CJSCore::Init(["jquery"]);

if (empty($arResult['FILES'])) {
    echo '<div class="alert alert-danger">Файлы не найдены</div>';
    return;
}

foreach ($arResult['FILES'] as $file) {
    ?>
    <a
        class="label label-danger mr-2"
        href="<?=$file['SRC']?>"
        target="_blank"
        download="<?=htmlentities($file['ORIGINAL_NAME'])?>"
        ><?=$file['EXTENSION']=='pdf'?'PDF':htmlentities($file['ORIGINAL_NAME'])?></a>
    <?
    if ($file['SIGNS']) {
        foreach ($file['SIGNS'] as $signer) {
            foreach ($signer['SIGNS'] as $sign) {
                $signerName = $signer['SIGNER_NAME'];
                if ($GLOBALS['USER']->GetID() == $signer['SIGNER_ID']) {
                    $signerName = $arParams['SIGNED_BY_YOU_MSG'] ?: 'Подписано Вами';
                }
                ?>
                <a
                    class="label label-success mr-2"
                    href="<?=$sign['SRC']?>"
                    target="_blank"
                    title="Подписано <?=$sign['TIMESTAMP_X']->format("d.m.Y H:i:s");?>"
                    download="<?=htmlentities($sign['ORIGINAL_NAME'])?>"
                    ><?=$signerName;?></a>
                <?
            }
        }
    }
}
