<?php

use Bitrix\Main\Page\Asset;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

Asset::getInstance()->addJs('/bitrix/templates/.default/bootstrap.min.js');
Asset::getInstance()->addCss('/bitrix/templates/bitrix24/css/sidebar.css');
Asset::getInstance()->addCss('/local/components/citto/checkorders/templates/.default/style.css');

$this->SetViewTarget('inside_pagetitle', 100);

$bMayEdit = $arResult['DETAIL']['ALLOW_EDIT'];
$bMaySync = $arResult['DETAIL']['ALLOW_SYNC'];
$readonly = '';
$fieldName = 'REQUIRED_FIELD';
if (!$bMayEdit) {
    $readonly = ' readonly="readonly"';
    $fieldName = 'PLAIN_FIELD';
}

?>
<div class="pagetitle-container pagetitle-align-right-container">
    <a class="ui-btn ui-btn-light-border mr-3" href="/control-orders/protocol/">
        Возврат к списку
    </a>
</div>
<?php
$this->EndViewTarget();
?>
<form method="POST">
    <input type="hidden" name="do" value="update" />
    <input
        type="hidden"
        name="id"
        value="<?=$arResult['DETAIL']['ID'];?>" />
    <div class="row">
        <div class="col-9">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Основная информация</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-3">
                            <?=GetMessage($fieldName, ['#NAME#' => 'Номер']);?>
                            <input
                                class="form-control"
                                placeholder="1"
                                name="NUMBER"
                                value="<?=$arResult['DETAIL']['NUMBER']?>"
                                <?=$readonly;?>
                                required />
                        </div>
                        <div class="col-3">
                            <?=GetMessage($fieldName, ['#NAME#' => 'Дата']);?>
                            <input
                                class="form-control <?=$bMayEdit ? 'js-calendar' : ''?>"
                                name="DATE"
                                value="<?=$arResult['DETAIL']['DATE']?>"
                                placeholder="<?=date('d.m.Y');?>"
                                <?=$readonly;?>
                                required />
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <?=GetMessage($fieldName, ['#NAME#' => 'Название']);?>
                            <textarea
                                class="form-control"
                                name="NAME"
                                placeholder="Перечень поручений №..."
                                <?=$readonly;?>
                                required
                                ><?=$arResult['DETAIL']['NAME']?></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            if ($arResult['DETAIL']['ID'] > 0) {
                $bShowFiles = !empty($arResult['DETAIL']['FILES']);
                $bShowChangeLog = !empty($arResult['DETAIL']['CHANGELOG']);
                ?>
                <div class="nav-tabs-custom">
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <a
                            class="nav-item nav-link active"
                            id="nav-protocol-tab"
                            data-toggle="tab"
                            href="#nav-protocol"
                            role="tab"
                            aria-controls="nav-protocol"
                            aria-selected="true">Поручения протокола</a>
                        <a
                            class="nav-item nav-link"
                            id="nav-orders-tab"
                            data-toggle="tab"
                            href="#nav-orders"
                            role="tab"
                            aria-controls="nav-orders"
                            aria-selected="false">Созданные поручения</a>
                        <?php
                        if ($bShowFiles) {
                            ?>
                        <a
                            class="nav-item nav-link"
                            id="nav-delo-tab"
                            data-toggle="tab"
                            href="#nav-delo"
                            role="tab"
                            aria-controls="nav-delo"
                            aria-selected="false">Проекты в АСЭД Дело</a>
                            <?php
                        }

                        if ($bShowChangeLog) {
                            ?>
                        <a
                            class="nav-item nav-link"
                            id="nav-changelog-tab"
                            data-toggle="tab"
                            href="#nav-changelog"
                            role="tab"
                            aria-controls="nav-changelog"
                            aria-selected="false">История изменений</a>
                            <?php
                        }
                        ?>
                    </div>
                    <div class="tab-content p-2" id="nav-tabContent">
                        <div
                            class="tab-pane fade show active"
                            id="nav-protocol"
                            role="tabpanel"
                            aria-labelledby="nav-protocol-tab">
                            <div class="row">
                                <div class="col-12">
                                    <?=$arResult['DETAIL']['ORDERS_TABLE'];?>
                                </div>
                            </div>
                            <?php
                            if ($bMayEdit) {
                                ?>
                            <div class="row">
                                <div class="col-12">
                                    <a
                                        class="ui-btn ui-btn-primary ui-btn-icon-add js-add-order"
                                        href="javascript:void(0);">
                                        <?=GetMessage('ADD_ORDER');?>
                                    </a>
                                </div>
                            </div>
                                <?php
                            }
                            ?>
                        </div>
                        <div
                            class="tab-pane fade"
                            id="nav-orders"
                            role="tabpanel"
                            aria-labelledby="nav-orders-tab">
                            <div class="row">
                                <div class="col-12">
                                    <?=$arResult['DETAIL']['REAL_ORDERS_TABLE'];?>
                                </div>
                            </div>
                        </div>
                        <?php
                        if ($bShowFiles) {
                            ?>
                        <div
                            class="tab-pane fade"
                            id="nav-delo"
                            role="tabpanel"
                            aria-labelledby="nav-delo-tab">
                            <div class="row">
                                <div class="col-12">
                                    <table
                                        class="table table-bordered">
                                        <thead class="thead-light">
                                            <tr>
                                                <th scope="col" width="15%">Дата</th>
                                                <th scope="col" width="15%">Файл</th>
                                                <th scope="col" width="70%">Информация</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        foreach ($arResult['DETAIL']['FILES'] as $row) {
                                            $version = 'неизвестна';
                                            if (isset($row['VERSION'])) {
                                                $version = $row['VERSION'];
                                            }
                                            ?>
                                            <tr>
                                                <td><?=$row['DATE'] ? date('d.m.Y H:i:s', $row['DATE']) : ''?></td>
                                                <td>
                                                    <a href="<?=$row['src']?>" download="Перечень поручений.docx">
                                                        Версия проекта <?=$version;?>
                                                    </a>
                                                </td>
                                                <td>
                                                    <?php
                                                    if ($row['EXECUTOR']) {
                                                        ?>
                                                        <b>Исполнитель</b>:<br/>
                                                        <?php
                                                        if (isset($arResult['DELO_USERS'][ $row['EXECUTOR'] ])) {
                                                            echo $arResult['DELO_USERS'][ $row['EXECUTOR'] ]['UF_NAME'];
                                                        } else {
                                                            echo GetMessage('UNKNOWN_USER');
                                                        }
                                                        ?>
                                                        <br/>
                                                        <?php
                                                    }
                                                    if ($row['VISAS']) {
                                                        ?>
                                                        <b>Визы</b>:<br/>
                                                        <?php
                                                        $showUsers = [];
                                                        foreach ($row['VISAS'] as $val) {
                                                            if (isset($arResult['DELO_USERS'][ $val ])) {
                                                                $rowUser = $arResult['DELO_USERS'][ $val ];
                                                                $text = $rowUser['UF_NAME'];
                                                                if (isset($row['SIGN_DATA'][ $rowUser['UF_ISN'] ])) {
                                                                    $arSign = $row['SIGN_DATA'][ $rowUser['UF_ISN'] ];
                                                                    $text .= ' - ' . $arSign['TEXT'];
                                                                    if (!empty($arSign['COMMENT'])) {
                                                                        $text .= '<br/>' . $arSign['COMMENT'] . '<br/>';
                                                                    }
                                                                }
                                                                $showUsers[] = $text;
                                                            } else {
                                                                $showUsers[] = GetMessage('UNKNOWN_USER');
                                                            }
                                                        }
                                                        echo implode('<br/>', $showUsers);
                                                        ?>
                                                        <br/>
                                                        <?php
                                                    }
                                                    if ($row['SIGNER']) {
                                                        ?>
                                                        <b>Подписант</b>:<br/>
                                                        <?php
                                                        if (isset($arResult['DELO_USERS'][ $row['SIGNER'] ])) {
                                                            $rowUser = $arResult['DELO_USERS'][ $row['SIGNER'] ];
                                                            echo $rowUser['UF_NAME'];
                                                            if (isset($row['SIGN_DATA'][ $rowUser['UF_ISN'] ])) {
                                                                $arSign = $row['SIGN_DATA'][ $rowUser['UF_ISN'] ];
                                                                echo ' - ' . $arSign['TEXT'];
                                                                if (!empty($arSign['COMMENT'])) {
                                                                    echo '<br/>' . $arSign['COMMENT'] . '<br/>';
                                                                }
                                                            }
                                                        } else {
                                                            echo GetMessage('UNKNOWN_USER');
                                                        }
                                                        ?>
                                                        <br/>
                                                        <?php
                                                    }

                                                    if (!empty($row['CHANGELOG'])) {
                                                        echo '<br/><b>Последнее изменение</b>: ' . $row['CHANGELOG_DATE'] . '<br/>';
                                                        ?>
                                                    <button
                                                        class="ui-btn ui-btn-primary ui-btn-icon-list collapsed my-2"
                                                        type="button"
                                                        data-toggle="collapse"
                                                        data-target="#collapseHistory-<?=$row['ID'];?>"
                                                        aria-expanded="false"
                                                        aria-controls="collapseHistory-<?=$row['ID'];?>">
                                                    История изменений
                                                    </button>
                                                    <ul
                                                        id="collapseHistory-<?=$row['ID'];?>"
                                                        class="collapse pl-3"
                                                        aria-labelledby="collapseHistory-<?=$row['ID'];?>">
                                                        <li><?=implode('</li><li>', $row['CHANGELOG']);?></li>
                                                    </ul>
                                                        <?php
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                            <?php
                        }

                        if ($bShowChangeLog) {
                            ?>
                        <div
                            class="tab-pane fade"
                            id="nav-changelog"
                            role="tabpanel"
                            aria-labelledby="nav-changelog-tab">
                            <div class="row">
                                <div class="col-12">
                                    <table
                                        class="table table-changelog table-bordered">
                                        <thead class="thead-light">
                                            <tr>
                                                <th scope="col">Дата</th>
                                                <th scope="col">Автор</th>
                                                <th scope="col">Где изменилось</th>
                                                <th scope="col">Изменение</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        foreach ($arResult['DETAIL']['CHANGELOG'] as $row) {
                                            ?>
                                            <tr>
                                                <td><?=$row['DATE']?></td>
                                                <td><?=$row['USER_NAME']?></td>
                                                <td><?=$row['FIELD']?></td>
                                                <?php
                                                if (isset($row['OLD']) || isset($row['NEW'])) {
                                                    ?>
                                                    <td><?=$row['OLD'] . ($row['NEW'] ? ' → ' . $row['NEW'] : '')?></td>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <td>&nbsp;</td>
                                                    <?php
                                                }
                                                ?>
                                            </tr>
                                            <?php
                                        }
                                        ?>
                                        </tbody>
                                        <?php
                                        if (!empty($arResult['DETAIL']['CHANGELOG_NAV'])) {
                                            ?>
                                        <tfoot>
                                            <tr>
                                                <th colspan="4"><?=$arResult['DETAIL']['CHANGELOG_NAV'];?></th>
                                            </tr>
                                        </tfoot>
                                            <?php
                                        }
                                        ?>
                                    </table>
                                </div>
                            </div>
                        </div>
                            <?php
                        }
                        ?>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
        <div class="col-3">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Согласование</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-12">
                            <b>Статус протокола</b><br/>
                            <input type="hidden" name="STATUS" value="<?=$arResult['DETAIL']['STATUS']?>" />
                            <?php
                            foreach ($arResult['STATUS_LIST'] as $status) {
                                if ($arResult['DETAIL']['STATUS'] == $status['ID']) {
                                    echo $status['VALUE'];
                                    break;
                                }
                            }
                            ?>
                        </div>
                        <?php
                        if (!empty($arResult['DETAIL']['PROPERTY_DELO_STATUS_VALUE'])) {
                            ?>
                        <div class="col-12">
                            <br/>
                            <b>Статус проекта в АСЭД Дело</b><br/>
                            <?=$arResult['DETAIL']['PROPERTY_DELO_STATUS_VALUE'];?>
                        </div>
                            <?php
                        }

                        if (!empty($arResult['DETAIL']['PROPERTY_DELO_NUMBER_VALUE'])) {
                            ?>
                        <div class="col-12">
                            <br/>
                            <b>Номер проекта в АСЭД Дело</b><br/>
                            <?=$arResult['DETAIL']['PROPERTY_DELO_NUMBER_VALUE'];?>
                        </div>
                            <?php
                        }

                        if (!empty($arResult['DETAIL']['PROPERTY_DELO_DATE_VALUE'])) {
                            ?>
                        <div class="col-12">
                            <br/>
                            <b>Дата проекта в АСЭД Дело</b><br/>
                            <?=$arResult['DETAIL']['PROPERTY_DELO_DATE_VALUE'];?>
                        </div>
                            <?php
                        }
                        ?>
                        <div class="col-12">
                            <br/>
                            <?=GetMessage($fieldName, ['#NAME#' => 'Исполнитель']);?>
                            <br/>
                            <div class="row">
                                <?php
                                if ($bMayEdit) {
                                    ?>
                                <div class="col-2 mr-2">
                                    <a
                                        href="javascript:void(0);"
                                        class="ui-btn ui-btn-primary ui-btn-icon-add js-user-select"
                                        data-field="EXECUTOR"></a>
                                </div>
                                    <?php
                                }
                                ?>
                                <div
                                    class="col-8 user-list d-flex align-items-center"
                                    id="EXECUTOR">
                                    <ul>
                                        <input type="hidden" name="EXECUTOR" value="<?=$arResult['DETAIL']['EXECUTOR']?>" />
                                        <li><?php
                                        if ($arResult['DETAIL']['EXECUTOR'] > 0) {
                                            if (isset($arResult['DELO_USERS'][ $arResult['DETAIL']['EXECUTOR'] ])) {
                                                echo $arResult['DELO_USERS'][ $arResult['DETAIL']['EXECUTOR'] ]['UF_NAME'];
                                            } else {
                                                echo GetMessage('UNKNOWN_USER');
                                            }
                                        }
                                        ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <br/>
                            <?=GetMessage($fieldName, ['#NAME#' => 'Визы']);?>
                            <br/>
                            <div class="row">
                                <?php
                                if ($bMayEdit) {
                                    ?>
                                <div class="col-2 mr-2">
                                    <a
                                        href="javascript:void(0);"
                                        class="ui-btn ui-btn-primary ui-btn-icon-add js-user-select"
                                        data-field="VISAS"
                                        data-multi="true"></a>
                                </div>
                                    <?php
                                }
                                ?>
                                <div
                                    class="col-8 user-list d-flex align-items-center"
                                    id="VISAS">
                                    <ul>
                                        <?php
                                        if (empty($arResult['DETAIL']['VISAS'])) {
                                            ?>
                                        <input type="hidden" name="VISAS[]" />
                                            <?php
                                        }

                                        foreach ($arResult['DETAIL']['VISAS'] as $val) {
                                            ?>
                                        <input
                                            type="hidden"
                                            name="VISAS[]"
                                            value="<?=$val?>" />
                                        <li><?php
                                        if (isset($arResult['DELO_USERS'][ $val ])) {
                                            echo $arResult['DELO_USERS'][ $val ]['UF_NAME'];
                                        } else {
                                            echo GetMessage('UNKNOWN_USER');
                                        }
                                        ?></li>
                                            <?php
                                        }
                                        ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <br/>
                            <?=GetMessage($fieldName, ['#NAME#' => 'Подписант']);?>
                            <br/>
                            <div class="row">
                                <?php
                                if ($bMayEdit) {
                                    ?>
                                <div class="col-2 mr-2">
                                    <a
                                        href="javascript:void(0);"
                                        class="ui-btn ui-btn-primary ui-btn-icon-add js-user-select"
                                        data-field="SIGNER"></a>
                                </div>
                                    <?php
                                }
                                ?>
                                <div
                                    class="col-8 user-list d-flex align-items-center"
                                    id="SIGNER">
                                    <ul>
                                        <input
                                            type="hidden"
                                            name="SIGNER"
                                            value="<?=$arResult['DETAIL']['SIGNER']?>" />
                                        <li><?php
                                        if ($arResult['DETAIL']['SIGNER'] > 0) {
                                            if (isset($arResult['DELO_USERS'][ $arResult['DETAIL']['SIGNER'] ])) {
                                                echo $arResult['DELO_USERS'][ $arResult['DETAIL']['SIGNER'] ]['UF_NAME'];
                                            } else {
                                                echo GetMessage('UNKNOWN_USER');
                                            }
                                        }
                                        ?></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <br/><br/>
                    <div class="row">
                        <div class="col-12 text-center">
                            <?php
                            if ($bMayEdit) {
                                ?>
                            <input
                                class="ui-btn ui-btn-primary"
                                type="submit"
                                value="Сохранить протокол" />
                            <br/><br/>
                                <?php
                            }
                            ?>
                            <?php
                            if ($arResult['DETAIL']['ID'] > 0) {
                                $disabled = (empty($arResult['DETAIL']['ROWS'])) ?
                                    'ui-btn-disabled' :
                                    'ui-btn-primary';

                                if (!$bMayEdit && $bMaySync) {
                                    ?>
                                <input
                                    class="ui-btn ui-btn-primary js-start-edit"
                                    type="button"
                                    data-id="<?=$arResult['DETAIL']['ID'];?>"
                                    value="Начать редактирование" />
                                <br/><br/>
                                    <?php
                                }
                                ?>
                                <button
                                    class="ui-btn ui-btn-icon-download js-download-file <?=$disabled;?>"
                                    type="button"
                                    data-id="<?=$arResult['DETAIL']['ID'];?>"
                                    disabled>Выгрузить протокол</button>

                                <?php
                                if ($bMaySync) {
                                    $send = 'На согласование';
                                    if (!empty($arResult['DETAIL']['PROPERTY_DELO_ISN_VALUE'])) {
                                        $send = 'Отправить заново';
                                    }
                                    ?>
                                    <br/><br/>
                                    <button
                                        class="ui-btn ui-btn-icon-start js-delo-sync <?=$disabled;?>"
                                        type="button"
                                        data-id="<?=$arResult['DETAIL']['ID'];?>"
                                        disabled><?=$send;?></button>
                                    <?php
                                }
                            }
                            ?>
                            <br/><br/>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</form>

<div
    class="modal"
    id="modalCreate"
    tabindex="-1"
    role="dialog"
    aria-labelledby="modalCreate"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5><?=GetMessage('ADD_ORDER');?></h5>
                <button
                    type="button"
                    class="close"
                    data-dismiss="modal"
                    aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" class="js-create-order-form">
                <div class="modal-body">
                    <input
                        type="hidden"
                        name="PROP[PROTOCOL_ID]"
                        value="<?=$arResult['DETAIL']['ID']?>" />
                    <input
                        type="hidden"
                        name="PROP[DATE_CREATE]"
                        value="<?=$arResult['DETAIL']['DATE']?>" />
                    <input
                        type="hidden"
                        name="PROP[ACTION]"
                        value="1134" />
                    <input
                        type="hidden"
                        name="PROP[STATUS]"
                        value="1141" />
                    <input
                        type="hidden"
                        name="PROP[NUMBER]"
                        value="<?=$arResult['DETAIL']['NUMBER']?>" />
                    <div class="row">
                        <div class="col-12">
                            <?=GetMessage($fieldName, ['#NAME#' => 'Название поручения:']);?>
                            <br/>
                            <textarea
                                class="form-control"
                                name="NAME"
                                placeholder="Рекомендовать администрациям..."
                                required></textarea>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <?=GetMessage($fieldName, ['#NAME#' => 'Исполнитель:']);?>
                            <br/>
                            <select
                                class="form-control"
                                name="PROP[ISPOLNITEL]"
                                required>
                                <option value="">(Не выбран)</option>
                                <?php
                                foreach ($arResult['EXECUTORS'] as $k => $v) {
                                    ?>
                                    <option value="<?=$k;?>">
                                        <?=$v['NAME'];?>
                                    </option>
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-2 js-accomplice">
                        <div class="col-12">
                            <?=GetMessage('PLAIN_FIELD', ['#NAME#' => 'Соисполнитель:']);?>
                            <br/>
                            <div class="row js-accomplice-row" data-id="0">
	                            <div class="col-1">
	                            	<a
	                            		href="javascript:void(0);"
	                            		class="ui-btn ui-btn-primary ui-btn-icon-add js-accomplice-add"
	                            		title="Добавить соисполнителя"
	                            		></a>
	                            	<a
	                            		href="javascript:void(0);"
	                            		class="ui-btn ui-btn-danger ui-btn-icon-remove js-accomplice-remove d-none"
	                            		title="Удалить соисполнителя"
	                            		></a>
	                            </div>
	                            <div class="col-9">
		                            <select
		                                class="form-control"
		                                name="PROP[ACCOMPLICE][0]">
		                                <option value="">(Не выбран)</option>
		                                <?php
		                                $arAccomplice = $arResult['EXECUTORS'];
		                                foreach (array_keys($arAccomplice) as $key) {
		                                    if (0 === mb_strpos($key, 'all_')) {
		                                        unset($arAccomplice[ $key ]);
		                                    }
		                                }
		                                foreach ($arAccomplice as $k => $v) {
		                                    ?>
		                                    <option value="<?=$k;?>">
		                                        <?=$v['NAME'];?>
		                                    </option>
		                                    <?php
		                                }
		                                ?>
		                            </select>
		                        </div>
		                        <div class="col-2 form-check d-flex align-items-center">
		                            <input
		                                type="hidden"
		                                name="PROP[DISABLE_DATE_ISPOLN][0]"
		                                value="N" />
		                            <input
		                                id="DISABLE_DATE_ISPOLN"
		                                class="form-check-input mt-0"
		                                type="checkbox"
		                                name="PROP[DISABLE_DATE_ISPOLN][0]"
		                                value="Y" />
		                            <label class="form-check-label" for="DISABLE_DATE_ISPOLN">
		                                Без&nbsp;срока
		                            </label>
		                        </div>
	                        </div>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-7">
                            <?=GetMessage($fieldName, ['#NAME#' => 'Срок исполнения:']);?>
                            <br/>
                            <input
                                class="form-control"
                                name="SROK"
                                placeholder="<?=FormatDate('f Y');?>"
                                required />
                        </div>
                        <div class="col-3">
                            <?=GetMessage($fieldName, ['#NAME#' => 'Дата исполнения:']);?>
                            <br/>
                            <input
                                class="form-control js-calendar"
                                name="PROP[DATE_ISPOLN]"
                                placeholder="<?=date('d.m.Y');?>"
                                required />
                        </div>
                        <div class="col-2 form-check d-flex align-items-center mt-3">
                            <br/>
                            <input
                                type="hidden"
                                name="PROP[NOT_CONTROL]"
                                value="N" />
                            <input
                                id="ADD_NOT_CONTROL"
                                class="form-check-input mt-0"
                                type="checkbox"
                                name="PROP[NOT_CONTROL]"
                                value="Y" />
                            <label class="form-check-label" for="ADD_NOT_CONTROL">
                                Не&nbsp;учитывать на&nbsp;контроле
                            </label>
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-12">
                            <?=GetMessage($fieldName, ['#NAME#' => 'Содержание поручения:']);?>
                            <br/>
                            <?php
                            $APPLICATION->IncludeComponent(
                                'bitrix:fileman.light_editor',
                                '',
                                [
                                    'CONTENT' => '',
                                    'INPUT_NAME' => 'DETAIL_TEXT',
                                    'INPUT_ID' => '',
                                    'WIDTH' => '100%',
                                    'HEIGHT' => '200px',
                                    'RESIZABLE' => 'Y',
                                    'AUTO_RESIZE' => 'N',
                                    'VIDEO_ALLOW_VIDEO' => 'N',
                                    'USE_FILE_DIALOGS' => 'N',
                                    'ID' => '',
                                    'JS_OBJ_NAME' => 'DETAIL_TEXT',
                                ]
                            );
                            ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input
                        class="ui-btn ui-btn-primary"
                        type="submit"
                        value="<?=GetMessage('ADD_ORDER');?>" />
                </div>
            </form>
        </div>
    </div>
</div>

<div
    class="modal"
    id="modalEdit"
    tabindex="-1"
    role="dialog"
    aria-labelledby="modalEdit"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5><?=GetMessage('UPDATE_ORDER');?></h5>
                <button
                    type="button"
                    class="close"
                    data-dismiss="modal"
                    aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST">
                <div class="modal-body">

                </div>
                <div class="modal-footer">
                    <input
                        class="ui-btn ui-btn-primary"
                        type="submit"
                        value="<?=GetMessage('UPDATE_ORDER');?>" />
                </div>
            </form>
        </div>
    </div>
</div>

<div
    class="modal"
    id="modalSelectUser"
    tabindex="-1"
    role="dialog"
    aria-labelledby="modalSelectUser"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Выбор пользователя</h5>
                <input
                    class="form-control js-search-user"
                    placeholder="Поиск..." />
                <button
                    type="button"
                    class="close"
                    data-dismiss="modal"
                    aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button
                    type="button"
                    class="ui-btn ui-btn-primary ui-btn-icon-add js-change-users">
                    Выбрать
                </button>
            </div>
        </div>
    </div>
</div>
