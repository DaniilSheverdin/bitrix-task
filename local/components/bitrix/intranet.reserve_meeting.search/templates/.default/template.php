<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

if ($arResult["FatalError"] != '') {
    ?>
    <span class='errortext'><?= $arResult["FatalError"] ?></span><br/><br/>
    <?php
} else {
    if ($arResult["ErrorMessage"] != '') {
        ?>
        <span class='errortext'><?= $arResult["ErrorMessage"] ?></span><br/><br/>
        <?php
    } ?>

    <div class="container">
        <div class="item" id="maintable">
            <div class="wrapper">
            <table width="100%" class="scroll intask-main data-table">
                <thead>
                <tr class="intask-row">
                    <th class="intask-cell">Комната</th>
                    <?php foreach ($arResult["CUSTOM_FIELDS"] as $item) : ?>
                        <th class="intask-cell"><?= $item['NAME'] ?></th>
                    <?php endforeach; ?>
                    <th class="intask-cell"><?= GetMessage("INTASK_C31T_FRESERVE") ?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                if (count($arResult["ITEMS"]) > 0) :?>
                    <?php foreach ($arResult["ITEMS"] as $arItem) : ?>
                        <tr class="intask-row<?= (($iCount % 2) == 0 ? " selected" : "") ?>"
                            onmouseover="this.className+=' intask-row-over';"
                            onmouseout="this.className=this.className.replace(' intask-row-over', '');"
                            ondblclick="window.location='<?= CUtil::addslashes($arItem["URI"]) ?>'">
                            <td class="intask-cell"
                                valign="top"><?= $arResult["MEETINGS_LIST"][$arItem["MEETING_ID"]]["NAME"] ?></td>
                            <?php foreach ($arResult["CUSTOM_FIELDS"] as $k => $item) : ?>
                                <?php
                                $sVal = explode('_', $k);
                                array_pop($sVal);
                                $sVal = implode('_', $sVal); ?>
                                <?php if ($arResult["MEETINGS_LIST"][$arItem["MEETING_ID"]][$sVal] || $sVal == 'UF') : ?>
                                    <?php if ($item['TYPE'] == 'boolean') : ?>
                                        <td class="intask-cell" valign="top"
                                            align="right"><?= $res = ($arResult["MEETINGS_LIST"][$arItem["MEETING_ID"]][$k]) ? 'Да' : 'Нет' ?></td>
                                    <?php elseif ($item['TYPE'] == 'enumeration') : ?>
                                        <?php foreach ($arResult['SELECT_TYPE'][$k] as $it) : ?>
                                            <?php if ($it['ID'] == $arResult["MEETINGS_LIST"][$arItem["MEETING_ID"]][$k]) : ?>
                                                <td class="intask-cell" valign="top"
                                                    align="right"><?= $it['VALUE'] ?></td>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <td class="intask-cell" valign="top"
                                            align="right"><?= $arResult["MEETINGS_LIST"][$arItem["MEETING_ID"]][$k] ?></td>
                                    <?php endif; ?>
                                <?php else : ?>
                                    <td></td>
                                <?php endif; ?>
                            <?php endforeach; ?>
                            <td class="intask-cell" valign="top" align="center"><a href="<?= $arItem["URI"] ?>"><img
                                            src="/bitrix/components/bitrix/intranet.reserve_meeting.search/templates/.default/images/element_edit.gif"
                                            width="20" height="20" border="0" alt=""></a></td>
                        </tr>
                        <?php
                        $iCount++; ?>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr class="intask-row">
                        <td class="intask-cell" colspan="6" valign="top"><?= GetMessage("INTDT_NO_TASKS") ?></td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
        <form method="post" class="item" action="<?= POST_FORM_ACTION_URI ?>" name="rms_filter_form">
            <table cellpadding="0" cellspacing="0" border="0" width="100%" class="intask-main data-table">
                <thead>
                <tr class="intask-row">
                    <th colspan="2" class="intask-cell"><?= GetMessage("INTASK_C31T_SEARCH") ?></th>
                </tr>
                </thead>
                <tbody>
                <tr class="intask-row">
                    <td class="intask-cell" align="right"><?= GetMessage("INTASK_C31T_ROOM") ?>:<br>
                        <select name="flt_id">
                            <option value=""><?= GetMessage("INTASK_C31T_ANY") ?></option>
                            <?php
                            foreach ($arResult["MEETINGS_ALL"] as $key => $value) :?>
                                <option value="<?= $key ?>"<?= ($key == $_REQUEST["flt_id"] ? " selected" : "") ?>><?= $value['NAME'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </td>
                </tr>

                <?php foreach ($arResult["CUSTOM_FIELDS"] as $k => $item) : ?>
                    <?php if ($item['TYPE'] != 'string') : ?>
                        <tr>
                            <td class="intask-cell" align="right"><?= $item['NAME'] ?>:<br>

                                <?php if ($item['TYPE'] == 'boolean') : ?>
                                    <select name="flt_<?= mb_strtolower($k) ?>">
                                        <option
                                            <?=($_REQUEST["flt_" . mb_strtolower($k)] == "NULL")?'selected':''?>
                                            value=NULL>Не имеет значения
                                        </option>
                                        <option
                                            <?=($_REQUEST["flt_" . mb_strtolower($k)] == "1")?'selected':''?>
                                            value="1">Да
                                        </option>
                                        <option
                                            <?=($_REQUEST["flt_" . mb_strtolower($k)] == "0")?'selected':''?>
                                            value="0">Нет
                                        </option>
                                    </select>
                                <?php elseif ($item['TYPE'] == 'enumeration') : ?>
                                    <select name="flt_<?= mb_strtolower($k) ?>">
                                        <option value=NULL>Не имеет значения</option>
                                        <?php foreach ($arResult['SELECT_TYPE'][$k] as $value) : ?>
                                            <?php
                                            if (($_REQUEST["flt_" . mb_strtolower($k)]) == $value['ID']) : ?>
                                                <option selected
                                                        value=<?= $value['ID'] ?>><?= $value['VALUE'] ?></option>
                                                <?php
                                                continue;
                                            endif; ?>
                                            <option value=<?= $value['ID'] ?>><?= $value['VALUE'] ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                <?php elseif ($item['TYPE'] == 'double') : ?>
                                    <div class="double">
                                        <input class="there" type="hidden" name="flt_<?= mb_strtolower($k) ?>" value="<?= $_REQUEST["flt_" . mb_strtolower($k)] ?>">
                                        <input type="number" placeholder="От" class="amount" name="from" value="">
                                        <input type="number" placeholder="До" class="amount" name="to" value="">
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
                </tbody>
            </table>
            <table width="100%">
                <tr>
                    <td align="center"><input type="submit" value="<?= GetMessage("INTASK_C31T_SEARCH") ?>"></td>
                </tr>
            </table>
        </form>
    </div>
    <?php
}
