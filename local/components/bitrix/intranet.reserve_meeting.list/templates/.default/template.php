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

    <?php
    $APPLICATION->AddHeadScript('/bitrix/js/main/utils.js');
    $APPLICATION->AddHeadScript('/bitrix/components/bitrix/intranet.reserve_meeting.list/js/dialogs.js');
    ?>

    <script type="text/javascript">
        //<![CDATA[
        if (typeof (phpVars) != "object")
            phpVars = {};
        if (!phpVars.titlePrefix)
            phpVars.titlePrefix = '<?=CUtil::JSEscape(COption::GetOptionString("main", "site_name", $_SERVER["SERVER_NAME"]))?> - ';
        if (!phpVars.messLoading)
            phpVars.messLoading = '<?=CUtil::JSEscape(GetMessage("INTASK_C23T_LOAD"))?>';
        if (!phpVars.ADMIN_THEME_ID)
            phpVars.ADMIN_THEME_ID = '.default';

        if (typeof oObjectITS != "object")
            var oObjectITS = {};
        //]]>
    </script>
    <div class="list-table" id="#maintable">
        <div class="wrapper">
            <table cellpadding="0" cellspacing="0" border="0" width="100%" class="scroll intask-main data-table">
                <thead>
                <tr class="intask-row">
                    <th class="intask-cell" width="0%">&nbsp;</th>
                    <?php foreach ($arResult["ALLOWED_FIELDS"] as $key => $item) : ?>
                        <?php if ($key != 'ID') : ?>
                            <th class="intask-cell"><?= $arResult["ALLOWED_FIELDS"][$key]["NAME"] ?></th>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tr>
                </thead>
                <tbody>
                <?php
                if (count($arResult["MEETINGS_LIST"]) > 0) :?>
                    <?php
                    foreach ($arResult["MEETINGS_LIST"] as $arMeeting) :?>
                        <tr class="intask-row<?= (($iCount % 2) == 0 ? " selected" : "") ?>"
                            onmouseover="this.className+=' intask-row-over';"
                            onmouseout="this.className=this.className.replace(' intask-row-over', '');"
                            ondblclick="window.location='<?= $arMeeting["URI"] ?>'" title="<?= $arMeeting["NAME"] ?>">
                            <td class="intask-cell" valign="top" align="center">
                                <script>
                                    function HideThisMenuS<?= $arMeeting["ID"] ?>() {
                                        if (window.ITSDropdownMenu != null) {
                                            window.ITSDropdownMenu.ShowMenu(this, oObjectITS['intask_s<?= $arMeeting["ID"] ?>'], document.getElementById('intask_s<?= $arMeeting["ID"] ?>'))
                                            window.ITSDropdownMenu.PopupHide();
                                        } else {
                                            alert('NULL');
                                        }
                                    }

                                    oObjectITS['intask_s<?= $arMeeting["ID"] ?>'] = <?= CUtil::PhpToJSObject($arMeeting["ACTIONS"]) ?>;
                                </script>
                                <table cellpadding="0" cellspacing="0" border="0" class="intask-dropdown-pointer"
                                       onmouseover="this.className+=' intask-dropdown-pointer-over';"
                                       onmouseout="this.className=this.className.replace(' intask-dropdown-pointer-over', '');"
                                       onclick="if(window.ITSDropdownMenu != null){window.ITSDropdownMenu.ShowMenu(this, oObjectITS['intask_s<?= $arMeeting["ID"] ?>'], document.getElementById('intask_s<?= $arMeeting["ID"] ?>'))}"
                                       title="<?= GetMessage("INTDT_ACTIONS") ?>"
                                       id="intask_table_s<?= $arMeeting["ID"] ?>">
                                    <tr>
                                        <td>
                                            <div class="controls controls-view show-action">
                                                <a href="javascript:void(0);" class="action">
                                                    <div id="intask_s<?= $arMeeting["ID"] ?>" class="empty"></div>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                            <?php foreach ($arMeeting as $key => $item) : ?>
                                <?php if (isset($arResult["ALLOWED_FIELDS"][$key]) && $key != 'ID') : ?>
                                    <?php if ($arResult["ALLOWED_FIELDS"][$key]['TYPE'] == 'string' || $arResult["ALLOWED_FIELDS"][$key]['TYPE'] == 'double') : ?>
                                        <td class="intask-cell" valign="top">
                                            <?php if ($key == 'NAME') :
                                                ?><a
                                                href="<?= $arMeeting["URI"] ?>"><?= $arMeeting["NAME"] ?></a>
                                            <?php else : ?>
                                                <?php
                                                $sVal = explode('_', $key);
                                                array_pop($sVal);
                                                $sVal = implode('_', $sVal);

                                                if ($arMeeting[$sVal] || $sVal == 'UF') : ?>
                                                    <?= $arMeeting[$key] ?>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                    <?php elseif ($arResult["ALLOWED_FIELDS"][$key]['TYPE'] == 'boolean') : ?>
                                        <td class="intask-cell"
                                            valign="top"><?= $sYesNow = ($arMeeting[$key]) ? 'Да' : 'Нет' ?></td>
                                    <?php elseif ($arResult["ALLOWED_FIELDS"][$key]['TYPE'] == 'enumeration') : ?>
                                        <td class="intask-cell" valign="top">
                                            <?php
                                            $sVal = explode('_', $key);
                                            array_pop($sVal);
                                            $sVal = implode('_', $sVal);

                                            if ($arMeeting[$sVal] || $sVal == 'UF') : ?>
                                                <?= $arResult['SELECT_TYPE'][$arMeeting[$key]] ?>
                                            <?php endif; ?>
                                        </td>
                                    <?php elseif ($arResult["ALLOWED_FIELDS"][$key]['TYPE'] == 'file') : ?>
                                        <td class="intask-cell" valign="top">
                                            <?php if ($item) : ?>
                                                <a class="showFiles" data-files='<?= json_encode($item) ?>' href="#">Просмотр</a>
                                            <?php endif; ?>
                                        </td>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endforeach; ?>
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

    <script>
        setTimeout(
            function () {
                window.ITSDropdownMenu = new ITSDropdownMenu();
            },
            10
        );
    </script>
    <?php
}
