<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<form action="?edit=0&action=add_position&back_url=<?=rawurlencode($APPLICATION->GetCurPageParam())?>" method="post">
    <div class="row">
        <div class="col-md-9">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Основная информация</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-12 col-md-12">
                            <b><span class="required">*</span>Наименование для позиции:</b><br>
                            <input class="form-control" required="" type="text"  name="NAME" value="<?=$_REQUEST['NAME']?>" size="30">
                        </div>
                        <div class="col-12">
                            <b><span class="required">*</span>Пояснение для позиции:</b><br>
                            <div class="card">
                                <?$APPLICATION->IncludeComponent(
                                    'bitrix:fileman.light_editor',
                                    '',
                                    array(
                                        'CONTENT' => $_REQUEST['DETAIL_TEXT'],
                                        'INPUT_NAME' => 'DETAIL_TEXT',
                                        'INPUT_ID' => '',
                                        'WIDTH' => '100%',
                                        'HEIGHT' => '300px',
                                        'RESIZABLE' => 'Y',
                                        'AUTO_RESIZE' => 'Y',
                                        'VIDEO_ALLOW_VIDEO' => 'Y',
                                        'VIDEO_MAX_WIDTH' => '640',
                                        'VIDEO_MAX_HEIGHT' => '480',
                                        'VIDEO_BUFFER' => '20',
                                        'VIDEO_LOGO' => '',
                                        'VIDEO_WMODE' => 'transparent',
                                        'VIDEO_WINDOWLESS' => 'Y',
                                        'VIDEO_SKIN' => '/bitrix/components/bitrix/player/mediaplayer/skins/bitrix.swf',
                                        'USE_FILE_DIALOGS' => 'Y',
                                        'ID' => '',
                                        'JS_OBJ_NAME' => 'DETAIL_TEXT',
                                    )
                                );?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?
            if (!empty($arResult['POSITION_DATA'])) {
                ?>
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Поручения для позиции</h3>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <table class="table">
                                <thead class="thead-light">
                                    <tr>
                                        <th scope="col">Наименование</th>
                                        <th scope="col">Содержание</th>
                                        <th scope="col">Исполнитель</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?
                                foreach ($arResult['POSITION_DATA'] as $key => $value) {
                                    ?>
                                    <tr>
                                        <td>
                                            <input type="hidden" name="add[]" value="<?=$value['ID']?>">
                                            <input type="hidden" name="PROP[POSITION_TO][]" value="<?=$value['ID']?>">
                                            <a href="?detail=<?=$value['ID']?>&back_url=<?=rawurlencode($APPLICATION->GetCurPageParam())?>" target='_blank'><?=$value['NAME']?> № <?=$value['PROPERTY_NUMBER_VALUE']?> от <?=$value['PROPERTY_DATE_CREATE_VALUE']?></a>
                                        </td>
                                        <td><?=$value['~DETAIL_TEXT']?></td>
                                        <td><?=$arResult['ISPOLNITELS'][$value['PROPERTY_ISPOLNITEL_VALUE']]['NAME']?></td>
                                    </tr>
                                    <?
                                }
                                ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <?
            }
            ?>
        </div>

        <div class="col-md-3">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Состояние</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-12">
                            <input type="hidden" name="PROP[ACTION]" value="1135">
                        </div>
                        <div class="col-12">
                            <b>Дата позиции:</b><br>
                            <input class="form-control" type="text" name="PROP[DATE_CREATE]" value="<?=($_REQUEST['PROP']['DATE_CREATE'] != '') ? $_REQUEST['PROP']['DATE_CREATE'] : date('d.m.Y')?>" onclick="BX.calendar({node: this, field: this, bTime: false});">
                        </div>
                        <div class="col-12 js-date-ispoln">
                            <b><span class="required">*</span>Срок исполнения:&nbsp;&nbsp;&nbsp;</b>&nbsp;&nbsp;&nbsp;<span>
                                <input type="hidden" name="DISABLE_DATE_ISPOLN" value="N" />
                                <input class="form-check-input" type="checkbox" name="DISABLE_DATE_ISPOLN" value="Y" id="DisableDateIspoln">
                                <label class="form-check-label" for="DisableDateIspoln">
                                Без&nbsp;срока
                                </label>
                            </span><br>
                            <input
                            	class="form-control"
                            	name="PROP[DATE_ISPOLN]"
                            	value="<?=$_REQUEST['PROP']['DATE_ISPOLN']?>"
                            	required
                            	onclick="BX.calendar({node: this, field: this, bTime: false});"
                            	/>
                        </div>
                        <div class="col-12">
                        <?
                        foreach ($arResult['CATEGORIES'] as $key => $value) {
                            if ($value['DEF'] == 'Y') {
                                ?>
                                <input type="hidden" name="PROP[CATEGORY]" value="<?=$key?>">
                                <?
                            }
                        }
                        ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Ответственные</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-12">
                            <b><span class="required">*</span>Исполнитель:</b><br>
                            <select  class="form-control" required name="PROP[ISPOLNITEL]">
                                <option value="">(Не выбран)</option>
                                <?
                                foreach ($arResult['ISPOLNITELTYPES'] as $sKey => $sValue) {
                                    if ($sValue['CNT'] > 0) {
                                        ?>
                                        <optgroup label="<?=$sValue['VALUE']?>">
                                        <?
                                        foreach ($arResult['ISPOLNITELS'] as $k => $v) {
                                            if ($v['PROPERTY_TYPE_ENUM_ID'] != $sValue['ID']) {
                                                continue;
                                            }
                                            ?>
                                            <option
                                                <?=($v['ID'] == $_REQUEST['PROP']['ISPOLNITEL']) ? 'selected' : '' ?>
                                                value="<?=$v['ID']?>"
                                                ><?=$v['NAME']?></option>
                                            <?
                                        }
                                        ?>
                                        </optgroup>
                                        <?
                                    }
                                }
                                ?>
                            </select>
                        </div>

                        <div class="col-12">
                            <b><span class="required">*</span>Контролер:</b><br>
                            <?$GLOBALS['APPLICATION']->IncludeComponent(
                                'bitrix:intranet.user.selector',
                                '',
                                array(
                                    'INPUT_NAME'            => 'CONTROLER',
                                    'INPUT_NAME_SUSPICIOUS' => 'CONTROLER_SUP',
                                    'INPUT_NAME_STRING'     => 'CONTROLER_STRING',
                                    'TEXTAREA_MIN_HEIGHT'   => 30,
                                    'TEXTAREA_MAX_HEIGHT'   => 60,
                                    'INPUT_VALUE'           => ($_REQUEST['CONTROLER'] != '') ? $_REQUEST['CONTROLER'] : 1151,
                                    'EXTERNAL'              => 'A',
                                    'MULTIPLE' => 'N',
                                    'SOCNET_GROUP_ID'       => ($arParams['TASK_TYPE'] == 'group' ? $arParams['OWNER_ID'] : ''),
                                )
                            );?>
                        </div>

                        <div class="col-12">
                            <b><span class="required">*</span>Куратор:</b><br>
                            <?$GLOBALS['APPLICATION']->IncludeComponent(
                                'bitrix:intranet.user.selector',
                                '',
                                array(
                                    'INPUT_NAME'            => 'POST',
                                    'INPUT_NAME_SUSPICIOUS' => 'POST_SUP',
                                    'INPUT_NAME_STRING'     => 'POST_STRING',
                                    'INPUT_VALUE'           => ($_REQUEST['POST'] != '') ? $_REQUEST['POST'] : 1112,
                                    'TEXTAREA_MIN_HEIGHT'   => 30,
                                    'TEXTAREA_MAX_HEIGHT'   => 60,
                                    'EXTERNAL'              => 'A',
                                    'MULTIPLE' => 'N',
                                    'SOCNET_GROUP_ID'       => ($arParams['TASK_TYPE'] == 'group' ? $arParams['OWNER_ID'] : ''),
                                )
                            );?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <button class="ui-btn ui-btn-primary" type="submit" name="input" value="add">Отправить на позицию</button>
</form>