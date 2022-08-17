<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
} ?>
<?php
if ($arResult["FatalError"] != '') {
    ?>
    <span class='errortext'><?= $arResult["FatalError"] ?></span><br/><br/>
    <?php
} else {
    if ($arResult["ErrorMessage"] != '') {
        ?>
        <span class='errortext'><?= $arResult["ErrorMessage"] ?></span><br/><br/>
        <?php
    }
    ?>

    <table cellpadding="0" cellspacing="0" border="0" width="30%">
        <?php foreach ($arResult["ALLOWED_FIELDS"] as $key => $item) : ?>
            <?php
            $sVal = explode('_', $key);
            array_pop($sVal);
            $sVal = implode('_', $sVal);

            if (($arResult['MEETING'][$sVal] || $sVal == 'UF') && isset($arResult['MEETING'][$key])) : ?>
                <tr>
                    <td><?= $item["NAME"] ?></td>
                    <?php if ($item['TYPE'] == 'boolean') : ?>
                        <td><?= $res = ($arResult['MEETING'][$key]) ? 'Да' : 'Нет' ?></td>
                    <?php elseif ($item['TYPE'] == 'enumeration') : ?>
                        <?php foreach ($arResult['SELECT_TYPE'] as $k => $it) : ?>
                            <?php if ($k == $arResult['MEETING'][$key]) : ?>
                                <td><?= $it ?></td>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php elseif ($item['TYPE'] == 'file') : ?>
                        <td>
                            <a class="showFiles" data-files='<?= json_encode($arResult['MEETING'][$key]) ?>' href="#">Просмотр</a>
                        </td>
                    <?php else : ?>
                        <td><?= $arResult['MEETING'][$key] ?></td>
                    <?php endif; ?>
                </tr>
            <?php endif; ?>
        <?php endforeach; ?>
    </table>
    <br>

    <table cellpadding="0" cellspacing="0" border="0" width="100%">
        <tr>
            <td align="left" valign="bottom"><a
                        href="<?= $arResult["PRIOR_WEEK_URI"] ?>">&lt;&lt; <?= GetMessage("INTASK_C25T_PRIOR_WEEK") ?></a>
            </td>
            <form method="GET" action="<?= $arResult["MEETING_URI"] ?>" name="meeting_date_select">
                <input type="hidden" name="<?= $arParams["PAGE_VAR"] ?>" value="meeting">
                <input type="hidden" name="<?= $arParams["MEETING_VAR"] ?>" value="<?= $arResult["MEETING"]["ID"] ?>">
                <td align="center">
                    <?php
                    $GLOBALS["APPLICATION"]->IncludeComponent(
                        'bitrix:main.calendar',
                        '',
                        [
                            'SHOW_INPUT' => 'Y',
                            'FORM_NAME' => "meeting_date_select",
                            'INPUT_NAME' => "week_start",
                            'INPUT_VALUE' => $arResult["WEEK_START"],
                            'SHOW_TIME' => 'N',
                            'INPUT_ADDITIONAL_ATTR' => $strAdd,
                        ],
                        null,
                        ['HIDE_ICONS' => 'Y']
                    );
                    ?> &nbsp;<input type="submit" value="<?= GetMessage("INTASK_C25T_SET") ?>"></td>
            </form>
            <td align="right" valign="bottom"><a
                        href="<?= $arResult["NEXT_WEEK_URI"] ?>"><?= GetMessage("INTASK_C25T_NEXT_WEEK") ?> &gt;&gt;</a>
            </td>
        </tr>
    </table>
    <br>

    <table cellpadding="0" cellspacing="0" border="0" width="100%" class="intask-main data-table">
        <thead>
        <tr class="intask-row">
            <th class="intask-cell" align="center" width="0%">&nbsp;</th>
            <?php
            $ar = [GetMessage("INTASK_C25T_D1"), GetMessage("INTASK_C25T_D2"), GetMessage("INTASK_C25T_D3"), GetMessage("INTASK_C25T_D4"), GetMessage("INTASK_C25T_D5"), GetMessage("INTASK_C25T_D6"), GetMessage("INTASK_C25T_D7")]; ?>
            <?php
            for ($i = 0; $i < 7; $i++) :?>
                <?php
                if (in_array($i, $arParams["WEEK_HOLIDAYS"])) {
                    continue;
                } ?>
                <th class="intask-cell" align="center"
                    width="<?= intval(100 / (7 - count($arParams["WEEK_HOLIDAYS"]))) ?>%"><?= $ar[$i] ?></th>
            <?php endfor; ?>
        </tr>
        <tr class="intask-row">
            <th align="center" class="intask-cell"><?= GetMessage("INTASK_C25T_TIME") ?></th>
            <?php
            for ($i = 0; $i < 7; $i++) :?>
                <?php
                if (in_array($i, $arParams["WEEK_HOLIDAYS"])) {
                    continue;
                } ?>
                <th align="center"
                    class="intask-cell"><?= FormatDate($GLOBALS["DB"]->DateFormatToPHP(FORMAT_DATE), mktime(0, 0, 0, $arResult["WEEK_START_ARRAY"]["m"], $arResult["WEEK_START_ARRAY"]["d"] + $i, $arResult["WEEK_START_ARRAY"]["Y"])) ?></th>
            <?php endfor; ?>
        </tr>
        </thead>
        <tbody>
        <?php
        for ($i = $arResult["LIMITS"]["FROM"]; $i < $arResult["LIMITS"]["TO"]; $i++) :?>
            <tr class="intask-row">
                <td class="intask-cell selected" nowrap><?= __RM_MkT($i) ?></td>
                <?php
                for ($j = 1; $j <= 7; $j++) {
                    if (in_array($j - 1, $arParams["WEEK_HOLIDAYS"])) {
                        continue;
                    }

                    $currentDay = ConvertTimeStamp(time()) == ConvertTimeStamp(mktime(0, 0, 0, $arResult["WEEK_START_ARRAY"]["m"], $arResult["WEEK_START_ARRAY"]["d"] + $j - 1, $arResult["WEEK_START_ARRAY"]["Y"]));

                    if ($arResult["ITEMS_MATRIX"][$j][$i]) {
                        if ($i == 0 || !$arResult["ITEMS_MATRIX"][$j][$i - 1] || $arResult["ITEMS_MATRIX"][$j][$i - 1] != $arResult["ITEMS_MATRIX"][$j][$i]) {
                            $cnt = 0;
                            for ($k = $i; $k < 48; $k++) {
                                if ($arResult["ITEMS_MATRIX"][$j][$i] == $arResult["ITEMS_MATRIX"][$j][$k]) {
                                    $cnt++;
                                } else {
                                    break;
                                }
                            } ?>
                        <td class="intask-cell reserved<?= $currentDay ? ' current' : '' ?>" valign="top"
                            rowspan="<?= $cnt ?>">
                            <a href="<?= $arResult["ITEMS"][$arResult["ITEMS_MATRIX"][$j][$i]]["VIEW_ITEM_URI"] ?>"><b><?= $arResult["ITEMS"][$arResult["ITEMS_MATRIX"][$j][$i]]["NAME"] ?></b></a>
                            <br/>
                            (<?= $arResult["ITEMS"][$arResult["ITEMS_MATRIX"][$j][$i]]["DATE_ACTIVE_FROM_TIME"] ?> -
                            <?= $arResult["ITEMS"][$arResult["ITEMS_MATRIX"][$j][$i]]["DATE_ACTIVE_TO_TIME"] ?>)<br/>
                            <?= GetMessage("INTASK_C25T_RESERVED_BY") ?>:
                            <?php
                            $APPLICATION->IncludeComponent(
                                "bitrix:main.user.link",
                                '',
                                [
                                    "ID" => $arResult["ITEMS"][$arResult["ITEMS_MATRIX"][$j][$i]]["CREATED_BY"],
                                    "HTML_ID" => "reserve_meeting_meeting" . $arResult["ITEMS"][$arResult["ITEMS_MATRIX"][$j][$i]]["CREATED_BY"],
                                    "NAME" => htmlspecialcharsback($arResult["ITEMS"][$arResult["ITEMS_MATRIX"][$j][$i]]["CREATED_BY_FIRST_NAME"]),
                                    "LAST_NAME" => htmlspecialcharsback($arResult["ITEMS"][$arResult["ITEMS_MATRIX"][$j][$i]]["CREATED_BY_LAST_NAME"]),
                                    "SECOND_NAME" => htmlspecialcharsback($arResult["ITEMS"][$arResult["ITEMS_MATRIX"][$j][$i]]["CREATED_BY_SECOND_NAME"]),
                                    "LOGIN" => htmlspecialcharsback($arResult["ITEMS"][$arResult["ITEMS_MATRIX"][$j][$i]]["CREATED_BY_LOGIN"]),
                                    "USE_THUMBNAIL_LIST" => "N",
                                    "INLINE" => "Y",
                                    "PATH_TO_SONET_USER_PROFILE" => $arParams["~PATH_TO_USER"],
                                    "PATH_TO_SONET_MESSAGES_CHAT" => $arParams["~PM_URL"],
                                    "PATH_TO_VIDEO_CALL" => $arParams["~PATH_TO_VIDEO_CALL"],
                                    "PATH_TO_CONPANY_DEPARTMENT" => $arParams["~PATH_TO_CONPANY_DEPARTMENT"],
                                    "DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
                                    "SHOW_YEAR" => $arParams["SHOW_YEAR"],
                                    "NAME_TEMPLATE" => $arParams["NAME_TEMPLATE_WO_NOBR"],
                                    "SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
                                ],
                                false,
                                ["HIDE_ICONS" => "Y"]
                            ); ?>
                            <br/>
                            <?php
                            if (mb_strlen($arResult["ITEMS"][$arResult["ITEMS_MATRIX"][$j][$i]]["EDIT_ITEM_URI"]) > 0) :?>
                                <br/>
                                <a href="<?= $arResult["ITEMS"][$arResult["ITEMS_MATRIX"][$j][$i]]["EDIT_ITEM_URI"] ?>"><?= GetMessage("INTASK_C25T_EDIT") ?></a>
                            <?php endif; ?>
                            <?php
                            if (mb_strlen($arResult["ITEMS"][$arResult["ITEMS_MATRIX"][$j][$i]]["CLEAR_URI"]) > 0) :?>
                                <br/>
                                <a onclick="if(confirm('<?= GetMessage("INTASK_C25T_CLEAR_CONF") ?>'))window.location='<?= $arResult["ITEMS"][$arResult["ITEMS_MATRIX"][$j][$i]]["CLEAR_URI"] ?>';"
                                   href="javascript:void(0)"><?= GetMessage("INTASK_C25T_CLEAR") ?></a>
                            <?php endif; ?>
                            </td><?php
                        }
                    } else {
                        ?>
                    <td class="intask-cell notreserved<?= $currentDay ? ' current' : '' ?>"
                        title="<?= GetMessage("INTASK_C25T_DBL_CLICK") ?>"
                        ondblclick="window.location='<?= CUtil::addslashes($arResult["CellClickUri"]) ?>start_date=<?= date($GLOBALS["DB"]->DateFormatToPHP(FORMAT_DATE), mktime(0, 0, 0, $arResult["WEEK_START_ARRAY"]["m"], $arResult["WEEK_START_ARRAY"]["d"] + $j - 1, $arResult["WEEK_START_ARRAY"]["Y"])) ?>&amp;start_time=<?php
                        $h1 = intval($i / 2);
                        if ($h1 < 10) {
                            $h1 = "0" . $h1;
                        }
                        $i1 = ($i % 2 != 0 ? "30" : "00");
                        echo $h1 . ":" . $i1; ?>'">&nbsp;</td><?php
                    }
                } ?>
            </tr>
        <?php endfor; ?>
        </tbody>
    </table>
    <br/>

    <div id="openModal" class="modal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <a href="#" class="close">×</a>
                </div>
                <div class="modal-body">
                    <div class="flexslider">
                        <ul class="slides">
                            <li></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}
