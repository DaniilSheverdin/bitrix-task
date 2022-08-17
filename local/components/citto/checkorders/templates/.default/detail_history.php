<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<div class="box box-primary col-10 col-xl-12">
    <div class="box-header with-border">
        <h3 class="box-title">История</h3>
    </div>
    <div class="box-body box-profile">
        <ul class="timeline timeline-inverse">
            <?
            $arHistory = $arResult['DETAIL_DATA']['HISTORY'];
            foreach ($arHistory as $key => $arData) {
                if (empty($arData['TEXT']) && empty($arData['DATA'])) {
                    continue;
                }
                $arData['DATE'] = explode(' ', $arData['DATE']);
                if ($arData['DATE'][0] != $date_now) {
                    ?>
                    <li class="time-label"><span class="bg-green"><?=$arData['DATE'][0]?></span></li>
                    <?
                    $date_now = $arData['DATE'][0];
                }
                ?>
                <li>
                    <i class="fa fa-envelope bg-blue"></i>
                    <div class="timeline-item">
                        <span class="time"><i class="fa fa-clock-o"></i> <?=$arData['DATE'][1]?></span>
                        <h3 class="timeline-header"><span bx-tooltip-user-id="<?=$arData['USER_ID']?>"><?=$arData['USER_NAME']?></span> - <?=$arData['TEXT']?></h3>
                        <div class="timeline-body">
                            <p><?=$arData['DATA']?></p>
                        </div>
                    </div>
                </li>
                <?
            }
            ?>
            <li><i class="fa fa-clock-o bg-gray"></i></li>
        </ul>
    </div>
</div>
