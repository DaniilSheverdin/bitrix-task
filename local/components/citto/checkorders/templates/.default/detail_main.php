<?php

use Citto\Controlorders\Orders;
use Citto\Controlorders\Settings;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

$arDelegationList = [];
foreach ($arElement['~PROPERTY_DELEGATION_VALUE'] as $key => $value) {
    $dateTs = strtotime($arElement['DATE_CREATE']);
    if (!empty($arElement['~PROPERTY_DELEGATION_DESCRIPTION'][ $key ])) {
        $arDelData = json_decode($arElement['~PROPERTY_DELEGATION_DESCRIPTION'][ $key ], true);
        if (isset($arDelData['DATE_ADD_TS'])) {
            $dateTs = $arDelData['DATE_ADD_TS'];
        }
    }
    $arDelegationList['DEP-' . $value] = [
        'DEP'   => $value,
        'DATE'  => date('d.m.Y H:i:s', $dateTs),
        'TS'    => $dateTs,
    ];
}
if (empty($arDelegationList)) {
    $arDelegationList['DEP-' . $arElement['PROPERTY_ISPOLNITEL_VALUE'] ] = [
        'DEP'   => $arElement['PROPERTY_ISPOLNITEL_VALUE'],
        'DATE'  => date('d.m.Y H:i:s', strtotime($arElement['DATE_CREATE'])),
        'TS'    => strtotime($arElement['DATE_CREATE']),
    ];
}

$lastDep = 0;
foreach ($arElement['~PROPERTY_DELEGATE_HISTORY_VALUE'] as $delegHistory) {
    $arDelData = json_decode($delegHistory, true);
    if (isset($arDelData['DELEGATE'])) {
        $curDep = 0;
        foreach ($arResult['ISPOLNITELS'] as $depId => $arIspolnitel) {
            if ($arDelData['CURRENT_USER'] == $arIspolnitel['PROPERTY_RUKOVODITEL_VALUE']) {
                $curDep = $depId;
                break;
            }
            if (in_array($arDelData['CURRENT_USER'], $arIspolnitel['PROPERTY_ZAMESTITELI_VALUE'])) {
                $curDep = $depId;
                break;
            }
            if (in_array($arDelData['CURRENT_USER'], $arIspolnitel['PROPERTY_IMPLEMENTATION_VALUE'])) {
                $curDep = $depId;
                break;
            }
            if (in_array($arDelData['CURRENT_USER'], $arIspolnitel['PROPERTY_ISPOLNITELI_VALUE'])) {
                $curDep = $depId;
                break;
            }
        }
        $bAddTime = false;
        if (
            $curDep > 0 &&
            $curDep != $lastDep &&
            isset($arDelegationList['DEP-' . $curDep])
        ) {
            $dateTs = $arDelegationList['DEP-' . $curDep]['TS'];
            ++$dateTs;
            $arDelegationList[] = [
                'USER'      => $arDelData['CURRENT_USER'],
                'DATE'      => date('d.m.Y H:i:s', $dateTs),
                'TS'        => $dateTs,
                'COMMENT'   => $arDelData['COMMENT'],
            ];
            $lastDep = $curDep;
            $bAddTime = true;
        }
        $dateTs = $arDelData['TIME'];
        if ($bAddTime) {
            $dateTs += 2;
        }
        $arDelegationList[] = [
            'USER'      => $arDelData['DELEGATE'],
            'DATE'      => date('d.m.Y H:i:s', $dateTs),
            'TS'        => $dateTs,
            'COMMENT'   => $arDelData['COMMENT'],
        ];
    }
}
usort(
    $arDelegationList,
    static function ($a, $b) {
        return strnatcmp($a['TS'], $b['TS']);
    }
);

$arHistory = $arResult['DETAIL_DATA']['HISTORY'];

?>
<div class="row">
    <div class="col-10 col-xl-9">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Основная информация</h3>
            </div>

            <div class="box-body box-profile">
                <div class="row">
                    <div class="col-12">
                        <?
                        if (count($arDetail['POSITION_DATA']) > 0) {
                            ?>
                            <b>Требования для позиции:</b>
                            <?
                        } else {
                            ?>
                            <b>Текст поручения:</b>
                            <?
                        }
                        ?>
                    </div>
                    <div class="col-12">
                        <?=$arElement['~DETAIL_TEXT']?>
                    </div>
                </div>
                <?
                if (!empty($arDetail['POSITION_FROM'])) {
                    ?>
                    <div class="row">
                        <div class="col-12">
                            <b>На позиции:</b>
                        </div>
                        <div class="col-12">
                            <? foreach ($arDetail['POSITION_FROM'] as $arPosition) : ?>
                            <p class="m-0">
                                <a href="?detail=<?=$arPosition['ID']?>" target="_blank">
                                    <?=$arResult['ISPOLNITELS'][ $arPosition['PROPERTY_ISPOLNITEL_VALUE'] ]['NAME']?>
                                    (<b><?=$arPosition['PROPERTY_ACTION_VALUE']?></b>)
                                </a>
                            </p>
                            <? endforeach; ?>
                        </div>
                    </div>
                    <?
                }
                ?>

                <div class="row mt-1">
                    <?
                    if (
                        count($arElement['PROPERTY_TYPE_VALUE']) > 0 ||
                        (
                            $arElement['ID'] == $arElement['XML_ID'] &&
                            count($arElement['PROPERTY_PORUCH_VALUE']) > 0
                        )
                    ) {
                        ?>
                        <div class="col-12 col-md-6"><b>Тип поручения:</b><br>
                            <?
                            $arElement['PROPERTY_TYPE_VALUE'] = array_unique($arElement['PROPERTY_TYPE_VALUE']);
                            /*
                             * @todo mlyamin 28.08.2020 Вынести в HL
                             */
                            $arClassMap = [
                                'no_ispoln' => 'label label-danger',
                                '7qCIhAcZ'  => 'label label-danger',
                                'PZi7LWqc'  => 'label label-danger',
                                '70O2unAF'  => 'label label-info',
                                '3xGApSr8'  => 'label label-warning',
                                '3q4B72gY'  => 'label label-success',
                            ];
                            foreach ($arElement['PROPERTY_TYPE_VALUE'] as $value) {
                                if (in_array($value, ['dopcontrol'])) {
                                    continue;
                                }
                                if (isset($arClassMap[ $value ])) {
                                    ?>
                                    <span class="<?=$arClassMap[ $value ]?>"><?=$arResult['TYPES_DATA'][ $value ]['UF_NAME'] ?></span>
                                    <?
                                } else {
                                    ?>
                                    <span class="label-<?=$value?>"><?=$arResult['TYPES_DATA'][ $value ]['UF_NAME'] ?></span>
                                    <?
                                }
                                echo '<br/>';
                            }

                            if (
                                $arElement['ID'] == $arElement['XML_ID'] &&
                                count($arElement['PROPERTY_PORUCH_VALUE']) > 0
                            ) {
                                ?>
                                <a class="label label-info label-badge">На несколько исполнителей</a>
                                <?
                            }
                            ?>
                        </div>
                        <?
                    }

                    if (count($arElement['PROPERTY_DOCS_VALUE']) > 0 && is_array($arElement['PROPERTY_DOCS_VALUE'][0])) {
                        ?>
                        <div class="col-12 col-md-6">
                            <b>Документы:</b><br>
                            <?
                            foreach ($arElement['PROPERTY_DOCS_VALUE'] as $aFile) {
                                ?>
                                <div>
                                    <?=$aFile['ORIGINAL_NAME']?> <a href="<?=$aFile['SRC']?>" target="_blank">Скачать</a>
                                </div>
                                <?
                            }
                            ?>
                        </div>
                        <?
                    }
                    ?>
                </div>

                <div class="row mt-1">
                    <?
                    if (!empty($arElement['~TAGS'])) {
                        ?>
                        <div class="col-12 col-md-6">
                            <b>Теги:</b><br>
                            <?=$arElement['~TAGS']?>
                        </div>
                        <?
                    }
                    ?>
                </div>
            </div>
        </div>

        <?
        if (count($arDetail['POSITION_DATA']) > 0) {
            ?>
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3>Поручения для позиции</h3>
                </div>
                <div class="box-body">
                    <table class="table">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">Наименование</th>
                                <th scope="col">Содержание</th>
                                <th scope="col">Исполнитель</th>
                                <th scope="col">Ход исполнения</th>
                                <?/*
                                <th scope="col">Требования для позиции</th>
                                */?>
                            </tr>
                        </thead>
                        <tbody>
                        <?
                        $arPositionIds = [];
                        foreach ($arDetail['POSITION_DATA'] as $value) {
                            $arPositionIds[] = $value['ID'];
                            ?>
                            <tr>
                                <td>
                                    <a href="?detail=<?=$value['ID']?>&back_url=<?=$backUrl?>" target="_blank">
                                        <?=$value['NAME']?> № <?=$value['PROPERTY_NUMBER_VALUE']?> от <?=$value['PROPERTY_DATE_CREATE_VALUE']?>
                                    </a>
                                </td>
                                <td><?=$value['~DETAIL_TEXT']?></td>
                                <td><?=$arResult['ISPOLNITELS'][ $value['PROPERTY_ISPOLNITEL_VALUE'] ]['NAME']?></td>
                                <td>
                                    <span class="js-text-toggler" title="Показать полностью">
                                        <?=$value['REPORT']?>
                                    </span>
                                </td>
                                <?/*
                                <td>
                                	<span class="js-text-toggler" title="Показать полностью">
                                		<?=$value['~PROPERTY_POSITION_ISPOLN_REQS_VALUE']['TEXT']?>
                                	</span>
                                </td>
                                */?>
                            </tr>
                            <?
                        }
                        ?>
                        </tbody>
                    </table>

                    <a href="#" data-ids="<?=implode(',', $arPositionIds)?>" class="ui-btn ui-btn-success js-doc-position-generate">Скачать в Word</a>
                </div>
            </div>
            <?
        }
        ?>

        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Ход исполнения</h3>
            </div>

            <div class="box-body">
                <?
                if (!empty($arDetail['POSITION_FROM'])) {
                    foreach ($arDetail['POSITION_FROM'] as $arPosition) {
                        if (empty($arPosition['POSITION'])) {
                            continue;
                        }
                        ?>
                        <div class="post clearfix">
                            <div class="box-header with-border">
                                <h3 class="box-title"><b>Позиция (<?=$arResult['ISPOLNITELS'][ $arPosition['PROPERTY_ISPOLNITEL_VALUE'] ]['NAME']?>):</b></h3>
                            </div>

                            <?=$this->__component->getUserBlock(
                                (int)$arPosition['POSITION']['PROPERTY_USER_VALUE'],
                                $arPosition['POSITION']['DATE_CREATE']??''
                            )?>

                            <div>
                                <p><?=$arPosition['POSITION']['~DETAIL_TEXT']?></p>
                                <?
                                if ($arPosition['POSITION']['PROPERTY_ECP_VALUE'] != '') {
                                    $APPLICATION->IncludeComponent(
                                        'citto:filesigner',
                                        'controlorders',
                                        [
                                            'FILES' => [$arPosition['POSITION']['PROPERTY_FILE_ECP_VALUE']]
                                        ],
                                        false
                                    );
                                }

                                if (count($arPosition['POSITION']['PROPERTY_DOCS_VALUE']) > 0) {
                                    ?>
                                    <p>
                                        <b>Документы:</b>
                                        <?
                                        foreach ($arPosition['POSITION']['PROPERTY_DOCS_VALUE'] as $aFile) {
                                            ?>
                                            <div>
                                                <?=$aFile['ORIGINAL_NAME']?> <a href="<?=$aFile['SRC']?>" target="_blank">Скачать</a>
                                            </div>
                                            <?
                                        }
                                        ?>
                                    </p>
                                    <?
                                }
                                ?>
                            </div>
                        </div>
                        <?
                    }
                }

                if ($arElement['PROPERTY_OLD_PORUCH_VALUE'] != '') {
                    ?>
                    <div class="post clearfix">
                        <div class="box-header with-border">
                            <h3 class="box-title"><b>Отчет предыдущего исполнителя:</b></h3>
                        </div>

                        <?=$this->__component->getUserBlock(
                            $arDetail['OLD_OTCHET']['PROPERTY_USER_VALUE'],
                            $arDetail['OLD_OTCHET']['DATE_CREATE']
                        )?>

                        <div>
                            <p><?=$arDetail['OLD_OTCHET']['~DETAIL_TEXT']?></p>
                            <?
                            if ($arDetail['OLD_OTCHET']['PROPERTY_ECP_VALUE'] != '') {
                                $APPLICATION->IncludeComponent(
                                    'citto:filesigner',
                                    'controlorders',
                                    [
                                        'FILES' => [$arDetail['OLD_OTCHET']['PROPERTY_FILE_ECP_VALUE']]
                                    ],
                                    false
                                );
                            }

                            if (count($arDetail['OLD_OTCHET']['PROPERTY_DOCS_VALUE']) > 0) {
                                ?>
                                <p>
                                    <b>Документы:</b>
                                    <?
                                    foreach ($arDetail['OLD_OTCHET']['PROPERTY_DOCS_VALUE'] as $k2 => $aFile) {
                                        ?>
                                        <div>
                                            <?=$aFile['ORIGINAL_NAME']?> <a href="<?=$aFile['SRC']?>" target="_blank">Скачать</a>
                                        </div>
                                        <?
                                    }
                                    ?>
                                </p>
                                <?
                            }
                            ?>
                        </div>
                    </div>
                    <?
                }

                if (($arPerm['kurator'] || $arPerm['controler']) && $arElement['PROPERTY_ACTION_ENUM_ID'] == 1134) {
                    ?>
                    <div class="card" style="height: auto;margin-bottom:5px;">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <form method="POST" action="?detail=<?=$_REQUEST['detail']?>&back_url=<?=$backUrl?>">
                                        <button class="ui-btn" type="submit" name="action" value="accept_to_work">Передать на исполнение</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?
                } elseif (
                    $arPerm['controler'] &&
                    $arElement['PROPERTY_ACTION_ENUM_ID'] == 1135 &&
                    $arResult['ISPOLNITELS'][ $arElement['PROPERTY_ISPOLNITEL_VALUE'] ]['PROPERTY_TYPE_CODE'] == 'external'
                ) {
                    ?>
                    <div class="card" style="height: auto;margin-bottom:5px;">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <form method="POST" action="?detail=<?=$_REQUEST['detail']?>&back_url=<?=$backUrl?>">
                                        <button class="ui-btn" type="submit" name="action" value="accept_to_real_work">Перевести на исполнение</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?
                } elseif (
                    in_array($arElement['PROPERTY_ACTION_ENUM_ID'], [1135, 1136]) &&
                    (
                        ($arPerm['ispolnitel_employee'] && $arElement['PROPERTY_DELEGATE_USER_VALUE'] == $GLOBALS['USER']->GetID()) ||
                        $arPerm['ispolnitel_main'] ||
                        $arPerm['ispolnitel_submain'] ||
                        $arPerm['ispolnitel_implementation']
                    ) &&
                    $arPerm['ispolnitel_data']['ID'] == $arElement['PROPERTY_ISPOLNITEL_VALUE']
                ) {
                    require(__DIR__ . '/detail_delegate.php');
                } elseif (($arPerm['kurator'] || $arPerm['controler']) && $arElement['PROPERTY_ACTION_ENUM_ID'] == 1140) {
                    ?>
                    <div class="card mb-2" style="height:auto">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <form action="?detail=<?=$_REQUEST['detail']?><?=($_REQUEST['view'] != 'summary') ? '&view=' . $_REQUEST['view'] : '' ?>&back_url=<?=$backUrl?>" method="POST">
                                        <input type="hidden" name="action" value="restore_from_archive" />
                                        <?if ($arElement['PROPERTY_DATE_ISPOLN_VALUE'] != $this->__component->disableSrokDate) : ?>
                                        <b><span class="required">*</span>Срок исполнения:</b><br>
                                        <input class="form-control" type="text" name="DATE_ISPOLN" value="" required onclick="BX.calendar({node: this, field: this, bTime: false});"><br>
                                        <?endif;?>
                                        <b>Комментарий для возврата:</b>
                                        <?$APPLICATION->IncludeComponent(
                                            'bitrix:fileman.light_editor',
                                            '',
                                            array(
                                                'CONTENT' => '',
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
                                        <button name="subaction" value="accept" class="ui-btn ui-btn-primary" type="submit">Отозвать из архива</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?
                }

                if (
                    $arPerm['controler'] &&
                    in_array(
                        $arElement['PROPERTY_ACTION_ENUM_ID'],
                        [Settings::$arActions['NEW'], Settings::$arActions['WORK']]
                    ) &&
                    !empty($arElement['PROPERTY_CONTROL_REJECT_VALUE'])
                ) {
                    ?>
                    <div class="card mb-2 h-auto">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12">
                                    <form action="?detail=<?=$_REQUEST['detail']?><?=($_REQUEST['view'] != 'summary') ? '&view=' . $_REQUEST['view'] : '' ?>&back_url=<?=$backUrl?>" method="POST">
                                        <input type="hidden" name="action" value="restore_from_reject" />
                                        <button name="subaction" value="accept" class="ui-btn ui-btn-primary" type="submit">Отозвать на контроль</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?
                }

                require(__DIR__ . '/detail_otchet_controler.php');

                require(__DIR__ . '/detail_otchet_ispolnitel_new.php');

                require(__DIR__ . '/detail_otchet_accomplience.php');

                require(__DIR__ . '/detail_zametki_kurator_new.php');

                /*
                if ($arPerm['kurator'] && $arElement['PROPERTY_ACTION_ENUM_ID'] == 1138) {
                    ?>
                    <div class="post clearfix">
                        <div class="box-header with-border">
                            <h3 class="box-title"><b>Заметки куратора:</b></h3>
                            <?
                            if (count($arComments['OTCHET_KURATOR']) > 1) {
                                ?>
                                <a href="?detail=<?=$_REQUEST['detail']?>&view=zametki_kurator&back_url=<?=$backUrl?>" class="float-right btn-box-tool">История</a>
                                <?
                            }
                            ?>
                        </div>

                        <?=$this->__component->getUserBlock()?>

                        <div class="row">
                            <div class="col-12">
                                <form action="?detail=<?=$_REQUEST['detail']?><?=($_REQUEST['view'] != 'summary') ? '&view=' . $_REQUEST['view'] : '' ?>&back_url=<?=$backUrl?>" method="POST">
                                    <input type="hidden" name="action" value="add_kurator_comment" />
                                    <?$APPLICATION->IncludeComponent(
                                        'bitrix:fileman.light_editor',
                                        '',
                                        array(
                                            'CONTENT' => '',
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
                                    <?$APPLICATION->IncludeComponent(
                                        'bitrix:main.file.input',
                                        'drag_n_drop',
                                        array(
                                            'INPUT_NAME' => 'FILES_KURATOR',
                                            'INPUT_VALUE' => $arComments['OTCHET_KURATOR'][0]['~PROPERTY_DOCS_VALUE'],
                                            'MULTIPLE' => 'Y',
                                            'MODULE_ID' => 'checkorders',
                                            'MAX_FILE_SIZE' => '',
                                            'ALLOW_UPLOAD' => 'A',
                                            'ALLOW_UPLOAD_EXT' => '',
                                        ),
                                        false
                                    );?>
                                    <?
                                    $GLOBALS['APPLICATION']->SetAdditionalCss('/local/activities/custom/docsignactivity_control/css/docsignactivity.css');
                                    $GLOBALS['APPLICATION']->AddHeadScript('/local/activities/custom/docsignactivity_control/js/es6-promise.min.js');
                                    $GLOBALS['APPLICATION']->AddHeadScript('/local/activities/custom/docsignactivity_control/js/ie_eventlistner_polyfill.js');
                                    $GLOBALS['APPLICATION']->AddHeadScript('/local/activities/custom/docsignactivity_control/js/cadesplugin_api.js');
                                    $GLOBALS['APPLICATION']->AddHeadScript('/local/activities/custom/docsignactivity_control/js/plugin.js');
                                    $GLOBALS['APPLICATION']->AddHeadScript($this->GetFolder() . '/docssign.js');

                                    $arControllerResh = $arParams['ENUM']['CONTROLER_RESH'][ $arElement['PROPERTY_CONTROLER_RESH_ENUM_ID'] ];
                                    ?>
                                    <br/>
                                    <button name="subaction" value="comment" class="ui-btn" type="submit">Оставить комментарий</button>
                                    <br/><br/>
                                    <?if ($arControllerResh['XML_ID'] != 'dop') : ?>
                                    <div class="docsign-form p-0 d-inline">
                                        <button class="ui-btn ui-btn-success js-sign-file-simple" data-id="<?=$_REQUEST['detail']?>" data-actionhead="kurator" id="docsign__sign-files">Снять с контроля</button>
                                        <input type="hidden" class="js-signed-data-id" name="sign_data_id" />
                                        <input type="hidden" class="js-signed-data" name="sign_data" />
                                        <input type="hidden" name="subaction" value="accept" />
                                    </div>
                                    <?endif;?>
                                    <?if ($arControllerResh['XML_ID'] != 'snyatie') : ?>
                                    <div class="docsign-form p-0 d-inline">
                                        <button class="ui-btn ui-btn-success js-sign-file-simple" data-id="<?=$_REQUEST['detail']?>" data-actionhead="kurator" id="docsign__sign-files">Отправить на доп контроль</button>
                                        <input type="hidden" class="js-signed-data-id" name="sign_data_id" />
                                        <input type="hidden" class="js-signed-data" name="sign_data" />
                                        <input type="hidden" name="subaction" value="reject" />
                                    </div>
                                    <?endif;?>
                                    <button name="subaction" value="zamechanie" class="ui-btn" type="submit">Замечание</button>
                                </form>
                            </div>
                        </div>
                    </div>
                    <?
                } elseif (count($arComments['OTCHET_KURATOR']) > 0 && ($arPerm['controler'] || $arPerm['kurator'])) {
                    ?>
                    <div class="post clearfix">
                        <div class="box-header with-border">
                            <h3 class="box-title"><b>Заметки куратора:</b></h3>
                            <?
                            if (count($arComments['OTCHET_KURATOR']) > 1) {
                                ?>
                                <a href="?detail=<?=$_REQUEST['detail']?>&view=zametki_kurator&back_url=<?=$backUrl?>" class="float-right btn-box-tool">История</a>
                                <?
                            }
                            ?>
                        </div>

                        <?=$this->__component->getUserBlock(
                            $arComments['OTCHET_KURATOR'][0]['PROPERTY_USER_VALUE'],
                            $arComments['OTCHET_KURATOR'][0]['DATE_CREATE']
                        )?>

                        <div class="row">
                            <div class="col-12">
                                <div>
                                    <p><?=$arComments['OTCHET_KURATOR'][0]['~DETAIL_TEXT']?></p>
                                    <?
                                    if ($arComments['OTCHET_KURATOR'][0]['PROPERTY_ECP_VALUE'] != '') {
                                        $APPLICATION->IncludeComponent(
                                            'citto:filesigner',
                                            'controlorders',
                                            [
                                                'FILES' => [$arComments['OTCHET_KURATOR'][0]['PROPERTY_FILE_ECP_VALUE']['ID']]
                                            ],
                                            false
                                        );
                                    }

                                    if (count($arComments['OTCHET_KURATOR'][0]['PROPERTY_DOCS_VALUE']) > 0) {
                                        ?>
                                        <p>
                                            <b>Документы:</b>
                                            <?
                                            foreach ($arComments['OTCHET_KURATOR'][0]['PROPERTY_DOCS_VALUE'] as $k2 => $aFile) {
                                                ?>
                                                <div>
                                                    <?=$aFile['ORIGINAL_NAME']?> <a href="<?=$aFile['SRC']?>" target="_blank">Скачать</a>
                                                </div>
                                                <?
                                            }
                                            ?>
                                        </p>
                                        <?
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?
                }
                */
                ?>
            </div>
        </div>

        <?
        require(__DIR__ . '/detail_zametki_controler.php');
        ?>
    </div>

    <div class="col-10 col-xl-3">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Состояние</h3>
            </div>

            <div class="box-body box-profile">
                <ul class="list-group list-group-unbordered">
                    <li class="list-group-item">
                        <b>Состояние</b> <a class="pull-right"><?=$arElement['PROPERTY_ACTION_VALUE']?></a>
                    </li>
                    <?
                    if (
                        $arElement['PROPERTY_ACTION_ENUM_ID'] == 1136 &&
                        !empty($arElement['PROPERTY_WORK_INTER_STATUS_VALUE'])
                    ) : ?>
                    <li class="list-group-item">
                        <b>Промежуточный статус</b>
                        <a class="pull-right">
                            <?
                            $workStatusText = '';
                            $arDelegator = [];
                            if (
                                !empty($arElement['PROPERTY_DELEGATION_VALUE']) &&
                                (int)$arElement['PROPERTY_DELEGATION_VALUE'][0] > 0
                            ) {
                                $arDelegator = $arResult['ISPOLNITELS'][ $arElement['PROPERTY_DELEGATION_VALUE'][0] ];
                            }
                            if (
                                !empty($arDelegator) &&
                                !empty($arComments['OTCHET_ISPOLNITEL'][0]['PROPERTY_ECP_VALUE'])
                            ) {
                                if ($arDelegator['PROPERTY_TYPE_CODE'] == 'zampred') {
                                    $workStatusText = ' заместителя председателя правительства';
                                } elseif ($arDelegator['PROPERTY_TYPE_CODE'] == 'gubernator') {
                                    $workStatusText = ' заместителя губернатора';
                                }
                            }
                            ?>
                            <?=$arElement['PROPERTY_WORK_INTER_STATUS_VALUE'] . $workStatusText?>
                        </a>
                    </li>
                    <?endif;?>
                    <li class="list-group-item">
                        <b>Дата поручения</b> <a class="pull-right"><?=$arElement['PROPERTY_DATE_CREATE_VALUE']?></a>
                    </li>
                    <li class="list-group-item">
                        <?
                        if (count($arElement['PROPERTY_DATE_ISPOLN_HIST_VALUE']) > 0 && $arElement['PROPERTY_DATE_ISPOLN_VALUE'] != $this->__component->disableSrokDate) {
                            ?>
                            <b>Срок исполнения</b> <a class="pull-right"><b><?=$arElement['PROPERTY_DATE_ISPOLN_VALUE']?></b><br>
                                <?
                                foreach ($arElement['PROPERTY_DATE_ISPOLN_HIST_VALUE'] as $sKey => $sValue) {
                                    echo $sValue.'<br>';
                                }
                                ?>
                            </a>
                            <?
                        } else {
                            ?>
                            <b>Срок исполнения</b> <a class="pull-right"><?=($arElement['PROPERTY_DATE_ISPOLN_VALUE'] != $this->__component->disableSrokDate ? $arElement['PROPERTY_DATE_ISPOLN_VALUE'] : 'Без срока')?> <i class="icon-chevron-down"></i></a>
                            <?
                        }
                        ?>
                    </li>
                    <?
                    if ($arElement['PROPERTY_DATE_FACT_ISPOLN_VALUE'] != '') {
                        ?>
                        <li class="list-group-item">
                            <b>Дата отчета</b> <a class="pull-right"><?=$arElement['PROPERTY_DATE_FACT_ISPOLN_VALUE']?></a>
                        </li>
                        <?
                    }

                    if (
                        $arElement['PROPERTY_DATE_REAL_ISPOLN_VALUE'] != '' &&
                        $arElement['PROPERTY_ACTION_ENUM_ID'] == Settings::$arActions['ARCHIVE']
                    ) {
                        ?>
                        <li class="list-group-item">
                            <b>Дата исполнения</b> <a class="pull-right"><?=$arElement['PROPERTY_DATE_REAL_ISPOLN_VALUE']?></a>
                        </li>
                        <?
                    }

                    if ($arElement['PROPERTY_DATE_FACT_SNYAT_VALUE'] != '') {
                        ?>
                        <li class="list-group-item">
                            <b>Дата снятия с контроля</b> <a class="pull-right"><?=$arElement['PROPERTY_DATE_FACT_SNYAT_VALUE']?></a>
                        </li>
                        <?
                    }

                    if ($arElement['PROPERTY_CATEGORY_VALUE'] != '') {
                        ?>
                        <li class="list-group-item">
                            <b>Категория поручения</b> <a class="pull-right"><?=$arElement['PROPERTY_CATEGORY_VALUE']?></a>
                        </li>
                        <?
                    }

                    if ((int)$arElement['PROPERTY_THEME_VALUE'] > 0) {
                        ?>
                        <li class="list-group-item">
                            <b>Тема поручения</b> <a class="pull-right"><?=$arResult['CLASSIFICATOR'][$arElement['PROPERTY_CAT_THEME_VALUE']]['NAME']?> - <?=$arResult['CLASSIFICATOR'][$arElement['PROPERTY_CAT_THEME_VALUE']]['THEMES'][$arElement['PROPERTY_THEME_VALUE']]['NAME']?></a>
                        </li>
                        <?
                    }

                    if ((int)$arElement['PROPERTY_OBJECT_VALUE'][0] > 0) {
                        ?>
                        <li class="list-group-item">
                            <b>Объект поручения</b> <a class="pull-right order-tags">
                                <?
                                foreach ($arElement['PROPERTY_OBJECT_VALUE'] as $value) {
                                    echo $this->__component->renderObject($value, false) . '<br/>';
                                }
                                ?>
                            </a>
                        </li>
                        <?
                    }

                    ?>
                </ul>
            </div>
        </div>

        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Ответственные</h3>
            </div>

            <div class="box-body box-profile">
                <ul class="list-group list-group-unbordered">
                    <?
                    if ($arElement['PROPERTY_ISPOLNITEL_VALUE'] > 0 && isset($arResult['ISPOLNITELS'][ $arElement['PROPERTY_ISPOLNITEL_VALUE'] ])) {
                        ?>
                        <li class="list-group-item">
                            <b>Исполнитель</b>
                            <ul class="px-0">
                                <li class="list-group-item text-right px-0 py-1 border-0">
                                <?
                                echo '<b>' . $arResult['ISPOLNITELS'][ $arElement['PROPERTY_ISPOLNITEL_VALUE'] ]['NAME'] . '</b>';

                                if (
                                    $arResult['ISPOLNITELS'][ $arElement['PROPERTY_ISPOLNITEL_VALUE'] ]['PROPERTY_RUKOVODITEL_VALUE'] > 0 &&
                                    in_array($arResult['ISPOLNITELS'][ $arElement['PROPERTY_ISPOLNITEL_VALUE'] ]['PROPERTY_TYPE_ENUM_ID'], $arEnabledEnum)
                                ) {
                                    echo '<br/>' . $this->__component->getUserFullName($arResult['ISPOLNITELS'][ $arElement['PROPERTY_ISPOLNITEL_VALUE'] ]['PROPERTY_RUKOVODITEL_VALUE'], true);
                                }

                                $iDelegate = (int)$arElement['PROPERTY_DELEGATE_USER_VALUE'];
                                if ($iDelegate > 0) {
                                    ?>
                                    <br/>
                                    <i>Делегировано <?=$this->__component->getUserFullName($iDelegate, true) ?></i>
                                    <?
                                }
                                ?>
                                </li>
                            </ul>
                        </li>
                        <?
                        $maxDelegateDate = (new Orders())->getSrok($arElement['ID'], (int)$arElement['PROPERTY_DELEGATE_USER_VALUE']);
                        if (
                            $arElement['PROPERTY_DATE_ISPOLN_VALUE'] != $this->__component->disableSrokDate &&
                            $maxDelegateDate != $arElement['PROPERTY_DATE_ISPOLN_VALUE']
                        ) : ?>
                        <li class="list-group-item">
                            <b>Срок для исполнителя</b>
                            <a class="pull-right">
                                <?=$maxDelegateDate?>
                            </a>
                        </li>
                        <?endif;?>
                        <?
                    }

                    $arElement['PROPERTY_SUBEXECUTOR_VALUE'] = array_filter($arElement['PROPERTY_SUBEXECUTOR_VALUE']);
                    $arElement['PROPERTY_ACCOMPLICES_VALUE'] = array_filter($arElement['PROPERTY_ACCOMPLICES_VALUE']);

                    $curExecutor = $arResult['ISPOLNITELS'][ $arElement['PROPERTY_ISPOLNITEL_VALUE'] ];
                    $arAccompliceData = [];
                    if (!empty($arElement['PROPERTY_ACCOMPLICES_VALUE'])) {
                        $arAccompliceData[] = [
                            'ID'            => $curExecutor['ID'],
                            'NAME'          => $curExecutor['NAME'],
                            'RUKOVODITEL'   => 0,
                            'DELEGATE'      => 0,
                            'USERS'         => array_merge(
                                [$curExecutor['PROPERTY_RUKOVODITEL_VALUE']],
                                $curExecutor['PROPERTY_ZAMESTITELI_VALUE'],
                                $curExecutor['PROPERTY_ISPOLNITELI_VALUE'],
                                $curExecutor['PROPERTY_IMPLEMENTATION_VALUE']
                            ),
                            'ACCOMPLICE'    => [],
                            'REQUIRED'      => false,
                        ];
                    }
                    $arElement['PROPERTY_SUBEXECUTOR_IDS'] = array_filter($arElement['PROPERTY_SUBEXECUTOR_IDS']);
                    foreach ($arElement['PROPERTY_SUBEXECUTOR_IDS'] as $keySE => $valueSE) {
                        $curExecutor = $arResult['ISPOLNITELS'][ $valueSE ];
                        $arAccRow = [
                            'ID'            => $curExecutor['ID'],
                            'NAME'          => $curExecutor['NAME'],
                            'RUKOVODITEL'   => $curExecutor['PROPERTY_RUKOVODITEL_VALUE'],
                            'DELEGATE'      => $arElement['PROPERTY_SUBEXECUTOR_USERS'][ $keySE ],
                            'USERS'         => array_merge(
                                [$curExecutor['PROPERTY_RUKOVODITEL_VALUE']],
                                $curExecutor['PROPERTY_ZAMESTITELI_VALUE'],
                                $curExecutor['PROPERTY_ISPOLNITELI_VALUE'],
                                $curExecutor['PROPERTY_IMPLEMENTATION_VALUE']
                            ),
                            'ACCOMPLICE'    => [],
                            'REQUIRED'      => in_array('I' . $curExecutor['ID'], $arElement['PROPERTY_REQUIRED_VISA_VALUE']),
                        ];
                        $arAccompliceData[] = $arAccRow;
                    }

                    foreach ($arAccompliceData as $id => $row) {
                        foreach ($arElement['PROPERTY_ACCOMPLICES_VALUE'] as $accKey => $accId) {
                            if (in_array($accId, $row['USERS'])) {
                                $arAccompliceData[ $id ]['ACCOMPLICE'][] = $accId;
                                unset($arElement['PROPERTY_ACCOMPLICES_VALUE'][ $accKey ]);
                            }
                        }
                    }

                    $arAccompliceData = array_filter($arAccompliceData);
                    $cntSubExecutors = count($arAccompliceData);
                    if ($cntSubExecutors > 0) {
                        ?>
                        <li class="list-group-item">
                            <b>Соисполнител<?=$cntSubExecutors>1?'и':'ь'?></b>
                            <ul class="px-0">
                                <?
                                foreach ($arAccompliceData as $accompliceRow) {
                                    ?>
                                    <li class="list-group-item text-right px-0 py-1 border-0">
                                        <?
                                        if ($accompliceRow['REQUIRED']) {
                                            echo '<span class="label label-warning mr-2" title="Обязательное визирование">ОВ</span>';
                                        }
                                        echo '<b>' . $accompliceRow['NAME'] . '</b>';

                                        if (
                                            $accompliceRow['RUKOVODITEL'] > 0 &&
                                            in_array($arResult['ISPOLNITELS'][ $accompliceRow['ID'] ]['PROPERTY_TYPE_ENUM_ID'], $arEnabledEnum)
                                        ) {
                                            echo '<br/>' . $this->__component->getUserFullName($accompliceRow['RUKOVODITEL'], true);
                                        }
                                        if ($accompliceRow['DELEGATE'] > 0) {
                                            ?>
                                            <br/>
                                            <i bx-tooltip-user-id="<?=$accompliceRow['DELEGATE']?>"><?=$this->__component->getUserFullName($accompliceRow['DELEGATE']) ?></i>
                                            <?
                                        }
                                        if ($accompliceRow['ACCOMPLICE'] > 0) {
                                            foreach ($accompliceRow['ACCOMPLICE'] as $uId) {
                                                ?>
                                                <br/>
                                                <i bx-tooltip-user-id="<?=$uId?>"><?=$this->__component->getUserFullName($uId) ?></i>
                                                <?
                                            }
                                        }
                                        ?>
                                    </li>
                                    <?
                                }
                                ?>
                            </ul>
                        </li>
                        <?
                        if (
                            !empty($arElement['PROPERTY_SUBEXECUTOR_DATE_VALUE']) &&
                            $arElement['PROPERTY_SUBEXECUTOR_DATE_VALUE'] != $this->__component->disableSrokDate
                        ) : ?>
                        <li class="list-group-item">
                            <b>Срок для соисполнителя</b>
                            <a class="pull-right">
                                <?=$arElement['PROPERTY_SUBEXECUTOR_DATE_VALUE']?>
                            </a>
                        </li>
                        <?endif;?>
                        <?
                    }

                    if (!empty($arElement['PROPERTY_CONTROLER_VALUE'])) {
                        ?>
                        <li class="list-group-item">
                            <b>Контролер</b> <a class="pull-right"><?=$this->__component->getUserFullName($arElement['PROPERTY_CONTROLER_VALUE'], true) ?></a>
                        </li>
                        <?
                    }

                    if (!empty($arElement['PROPERTY_POST_VALUE'])) {
                        ?>
                        <li class="list-group-item">
                            <b>Куратор</b> <a class="pull-right"><?=$this->__component->getUserFullName($arElement['PROPERTY_POST_VALUE'], true) ?></a>
                        </li>
                        <?
                    }
                    ?>
                </ul>
            </div>
        </div>

        <?if (count($arDelegationList) > 1) : ?>
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Делегирование</h3>
            </div>

            <div class="box-body box-profile">
                <ul class="timeline timeline-inverse">
                    <?
                    $prevValue = '';
                    foreach ($arDelegationList as $key => $value) {
                        if ($value['USER'] && $prevValue == $value['USER']) {
                            unset($arDelegationList[ $key ]);
                        }
                        $prevValue = $value['USER'];
                    }
                    $countDelegations = count($arDelegationList);
                    $maxShow = 0;
                    if ($countDelegations > 2) {
                        $maxShow = $countDelegations - 2;
                        ?>
                        <li>
                            <i class="fa fa-envelope js-show-delegate-history" title="Показать историю делегирования">...</i>
                            <br/><br/>
                        </li>
                        <?
                    }

                    $arMyUsers = array_merge(
                        [$curUserId],
                        [$arResult['PERMISSIONS']['ispolnitel_data']['PROPERTY_RUKOVODITEL_VALUE']],
                        $arResult['PERMISSIONS']['ispolnitel_data']['PROPERTY_ZAMESTITELI_VALUE'],
                        $arResult['PERMISSIONS']['ispolnitel_data']['PROPERTY_ISPOLNITELI_VALUE'],
                        $arResult['PERMISSIONS']['ispolnitel_data']['PROPERTY_IMPLEMENTATION_VALUE']
                    );

                    foreach ($arDelegationList as $key => $value) {
                        $bShowComment = false;
                        if (!empty($value['COMMENT'])) {
                            if (
                                $arResult['kurator'] ||
                                $arResult['full_access'] ||
                                $arResult['controler'] ||
                                $GLOBALS['USER']->IsAdmin()
                            ) {
                                $bShowComment = true;
                            } elseif (
                                $value['DEP'] &&
                                in_array($value['DEP'], $arResult['PERMISSIONS']['ispolnitel_ids'])
                            ) {
                                $bShowComment = true;
                            } elseif (
                                in_array($value['USER'], $arMyUsers)
                            ) {
                                $bShowComment = true;
                            }
                        }
                        ?>
                        <li class="<?=$key < $maxShow ? 'delegate-history d-none' : ''?>">
                            <i class="fa fa-envelope bg-blue">
                                <?
                                if ($key < $countDelegations-1) {
                                    echo '&darr;';
                                } else {
                                    echo '&rarr;';
                                }
                                ?>
                            </i>
                            <div class="timeline-item">
                                <? if ($key > 0) : ?>
                                <span class="time f-none"><i class="fa fa-clock-o"></i> <?=$value['DATE']??''?></span>
                                <?endif;?>
                                <div class="timeline-body <?=($key > 0)?'pt-0':''?>">
                                    <?=$value['DEP'] ?
                                        $arResult['ISPOLNITELS'][ $value['DEP'] ]['NAME'] :
                                        $this->__component->getUserFullName($value['USER'], true) ?>
                                    <?
                                    if ($bShowComment) {
                                        echo '<br/><i>' . $value['COMMENT'] . '</i>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </li>
                        <?
                    }
                    ?>
                </ul>
            </div>
        </div>
        <?endif;?>

        <?if (!empty($arHistory)) : ?>
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">История</h3>
            </div>

            <div class="box-body box-profile">
                <ul class="timeline timeline-inverse">
                    <?
                    $date_now = null;
                    foreach ($arHistory as $key => $arData) {
                        $arData['DATE'] = explode(' ', $arData['DATE']);
                        if ($arData['DATE'][0] != $date_now) {
                            ?>
                            <li class="time-label">
                                <span class="bg-green">
                                    <?=$arData['DATE'][0]?>
                                </span>
                            </li>
                            <?
                            $date_now = $arData['DATE'][0];
                        }
                        ?>
                        <li>
                            <i class="fa fa-envelope bg-blue"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fa fa-clock-o"></i> <?=$arData['DATE'][1]?></span>
                                <h3 class="timeline-header" bx-tooltip-user-id="<?=$arData['USER_ID']?>"><?=$arData['USER_NAME']?></h3>
                                <div class="timeline-body">
                                    <?=$arData['TEXT']?>
                                </div>
                            </div>
                        </li>
                        <?
                        if ($key == 2) {
                            break;
                        }
                    }
                    /*
                    $arElement['~PROPERTY_HISTORY_SROK_VALUE'] = array_reverse($arElement['~PROPERTY_HISTORY_SROK_VALUE']);
                    foreach ($arElement['~PROPERTY_HISTORY_SROK_VALUE'] as $key => $value) {
                        $arData         = json_decode($value, true);
                        $arData['DATE'] = explode(' ', $arData['DATE']);
                        if ($arData['DATE'][0] != $date_now) {
                            ?>
                            <li class="time-label">
                                <span class="bg-green">
                                    <?=$arData['DATE'][0]?>
                                </span>
                            </li>
                            <?
                            $date_now = $arData['DATE'][0];
                        }
                        ?>

                        <li>
                            <i class="fa fa-envelope bg-blue"></i>
                            <div class="timeline-item">
                                <span class="time"><i class="fa fa-clock-o"></i> <?=$arData['DATE'][1]?></span>
                                <h3 class="timeline-header" bx-tooltip-user-id="<?=$arData['USER_ID']?>"><?=$arData['USER_NAME']?></h3>
                                <div class="timeline-body">
                                    <?=$arData['TEXT']?>
                                </div>
                            </div>
                        </li>
                        <?
                        if ($key == 2) {
                            break;
                        }
                    }
                    */
                    if (count($arHistory) > 3 || $arPerm['controler'] || $arPerm['kurator'] || $GLOBALS['USER']->IsAdmin()) {
                        ?>
                        <li>
                            <a class="fa fa-clock-o bg-grey" href="?detail=<?=$_REQUEST['detail']?>&view=history&back_url=<?=$backUrl?>" title="Полная история">&rarr;</a>
                        </li>
                        <?
                    }
                    unset($arHistory);
                    ?>
                </ul>
            </div>
        </div>
        <?endif;?>
    </div>
</div>
