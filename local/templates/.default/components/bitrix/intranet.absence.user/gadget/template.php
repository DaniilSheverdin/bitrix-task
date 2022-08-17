<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if ((is_array($arResult['ENTRIES']) && count($arResult['ENTRIES']) > 0 && $GLOBALS['USER']->GetID() == $arParams['ID']) || $USER->IsAdmin()):?>
<?/*
    <div class="user-profile-right-block">
        <div class="user-right-bl-img user-absence-img"></div>
        <div class="user-right-bl-title"><?= GetMessage("SONET_USER_ABSENCE") ?></div>
        <?
        foreach ($arResult['ENTRIES'] as $arEntry):
            $typeEntry = $arResult['ENTRIS_TYPES']['ID'][$arEntry['PROPERTY_ABSENCE_TYPE_ENUM_ID']]['XML_ID'];
            if (in_array($typeEntry, ['OTHER', 'ENTRY', 'EXIT', 'VIOLATION', 'VIOLATION_POSITIVE']))
                continue;
            $ts_start = MakeTimeStamp($arEntry['DATE_ACTIVE_FROM']);
            $ts_finish = MakeTimeStamp($arEntry['DATE_ACTIVE_TO']);
            $ts_now = time();
            $bNow = $ts_now >= $ts_start && $ts_now <= $ts_finish;
            ?>
            <div class="user-right-bl-item">
            <?
            echo GetMessage('INTR_IAU_TPL' . ($bNow ? '_TO' : '_FROM')) ?><?
            echo FormatDate($DB->DateFormatToPHP(FORMAT_DATETIME), MakeTimeStamp($arEntry['DATE_ACTIVE' . ($bNow ? '_TO' : '_FROM')])) ?>
            <br>
            <?
            echo htmlspecialcharsbx($arEntry['TITLE']) ?>
            </div><?
            $bFirst = false;
        endforeach;
        ?>
    </div>
*/?>
    <div class="bx-user-absence-layout">
    <?
    $bFirst = true;
    foreach ($arResult['ENTRIES'] as $arEntry):
        $ts_start = MakeTimeStamp($arEntry['DATE_ACTIVE_FROM']);
        $ts_finish = MakeTimeStamp($arEntry['DATE_ACTIVE_TO']);
        $ts_now = time();
        $bNow = $ts_now >= $ts_start && $ts_now <= $ts_finish;
        if (!$bFirst):
            ?><br /><?
        endif;
        ?>
        <div class="bx-user-absence-entry<?echo $bNow ? ' bx-user-absence-now' : ''?>">
        <span class="bx-user-absence-entry-date"><?echo GetMessage('INTR_IAU_TPL'.($bNow ? '_TO' : '_FROM'))?> <?echo FormatDate($DB->DateFormatToPHP(FORMAT_DATETIME), MakeTimeStamp($arEntry['DATE_ACTIVE'.($bNow ? '_TO' : '_FROM')]))?></span><br>
        <span class="bx-user-absence-entry-title"><?echo htmlspecialcharsbx($arEntry['TITLE'])?></span>
        </div><?
        $bFirst = false;
    endforeach;
    ?></div>
<?
elseif ($GLOBALS['USER']->GetID() != $arParams['ID']): echo GetMessage('NOT_PERMISSION');
else: echo GetMessage('INTR_IAU_TPL_NOT_FOUND');
endif;
?>