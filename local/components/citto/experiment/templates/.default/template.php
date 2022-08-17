<?php

use Bitrix\Main\Data\Cache;
use Bitrix\Main\Page\Asset;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

Asset::getInstance()->addJs('/bitrix/templates/.default/bootstrap.min.js');
Asset::getInstance()->addCss('/bitrix/templates/.default/bootstrap.min.css');
Asset::getInstance()->addCss('/bitrix/components/bitrix/intranet.user.selector.new/templates/.default/style.css');

$bExport = (isset($_REQUEST['do']) && $_REQUEST['do'] == 'export');

$filterStatus = '';
if (isset($_REQUEST['filter_status'])) {
    $filterStatus = $_REQUEST['filter_status'];
}
?>
<div class="experiment">
    <form method="GET">
        <div class="row">
            <div class="col-4">
                Фильтр по статусу
                <br/>
                <select class="form-control" name="filter_status">
                    <option value="">Все</option>
                    <?
                    foreach ($arResult['STATUSES'] as $key => $value) {
                        $selected = '';
                        if ($key == $filterStatus) {
                            $selected = 'selected';
                        }
                        ?>
                        <option value="<?=$key?>" <?=$selected?>><?=$value?></option>
                        <?
                    }
                    $selected = '';
                    if ($filterStatus == 'hidden') {
                        $selected = 'selected';
                    }
                    ?>
                    <option value="hidden" <?=$selected?>>Пользователь скрыт из списка</option>
                    <?
                    $selected = '';
                    if ($filterStatus == 'disabled') {
                        $selected = 'selected';
                    }
                    ?>
                    <option value="disabled" <?=$selected?>>Пользователь неактивен</option>
                </select>
                <br/>
            </div>
            <div class="col-4">
                <br/>
                <button type="submit" name="do" value="filter" class="ui-btn ui-btn-icon-search ui-btn-primary">Фильтр</button>
                <button type="submit" name="do" value="export" class="ui-btn ui-btn-icon-download ui-btn-success">Выгрузить</button>
            </div>
        </div>
    </form>

    <table class="table table-bordered table-sm">
        <thead>
            <tr>
                <th width="2%"></th>
                <th width="25%">Сотрудник</th>
                <th width="25%">Статус</th>
                <th width="48%" colspan="2">Файл</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $arLabelMap = [
                'Подписано пользователем'               => 'success',
                'Подписано руководителем'               => 'warning',
                'Отклонено пользователем'               => 'danger',
                'Отправлено руководителю на подпись'    => 'danger',
                'Не сформирован'                        => 'info',
            ];
            foreach ($arResult['DEPARTMENTS'] as $id => $arDep) {
                if (empty($arDep['USERS'])) {
                    continue;
                }

                $bFirstRow = true;
                $firstRowHtml = '
                <tr class="table-primary js-show-users" data-id="' . $id . '">
                    <td>##CHECKBOX##</td>
                    <th colspan="4">
                        [' . $arDep["DATE"] . ']
                        ' . $arDep["NAME"] . '
                        (Портал: ' . count($arDep['USERS']) . ',
                        Штат: ' . $arDep['SHTAT'] . ',
                        Факт: ' . $arDep['FACT'] . ')
                        ##STATUSES##
                    </th>
                </tr>';

                uasort($arDep['USERS'], $this->__component->buildSorter('ID'));
                $arCurrentStatuses = [];
                foreach ($arDep['USERS'] as $arUser) {
                    $arFile = [];
                    if (isset($arResult['FILES'][ $arUser['ID'] ])) {
                        try {
                            $currentFileId = $arResult['FILES'][ $arUser['ID'] ];
                            $obCache = Cache::createInstance();
                            if ($obCache->initCache(600, "getFileSigns_" . $currentFileId, '/experiment/')) {
                                $arFileList = $obCache->getVars();
                            } elseif ($obCache->startDataCache()) {
                                $arFileList = \Citto\Filesigner\Signer::getFiles([ $currentFileId ]);
                                $obCache->endDataCache($arFileList);
                            }

                            $arFile = $arFileList[ $currentFileId ];
                        } catch (Exception $exc) {
                            ShowError($exc->getMessage());
                        }
                    }
                    if (empty($arFile)) {
                        $arCurrentStatuses['Не сформирован']++;
                        continue;
                    }
                    $arCurrentStatuses[ $arResult['FILES_STATUS'][ $arUser['ID'] ][ $arFile['ID'] ] ]++;
                }
                $arFileStatus = [];
                foreach ($arCurrentStatuses as $name => $count) {
                    $arFileStatus[] = '<span class="badge badge-' . $arLabelMap[ $name ] . '">' . $name . ': ' . $count . '</span>';
                }

                $firstRowHtml = str_replace(
                    '##STATUSES##',
                    $bExport ? '' : implode(' ', $arFileStatus),
                    $firstRowHtml
                );
                foreach ($arDep['USERS'] as $arUser) {
                    $arFile = [];
                    $class = '';
                    if (isset($arResult['FILES'][ $arUser['ID'] ])) {
                        try {
                            $currentFileId = $arResult['FILES'][ $arUser['ID'] ];
                            $obCache = Cache::createInstance();
                            if ($obCache->initCache(600, "getFileSigns_" . $currentFileId, '/experiment/')) {
                                $arFileList = $obCache->getVars();
                            } elseif ($obCache->startDataCache()) {
                                $arFileList = \Citto\Filesigner\Signer::getFiles([ $currentFileId ]);
                                $obCache->endDataCache($arFileList);
                            }

                            $arFile = $arFileList[ $currentFileId ];
                            if (count($arFile['SIGNS']) >= 2) {
                                $class = 'table-success';
                            } elseif (count($arFile['SIGNS']) > 0) {
                                $class = 'table-warning';
                            } else {
                                $class = 'table-danger';
                            }
                        } catch (Exception $exc) {
                            ShowError($exc->getMessage());
                        }
                    }
                    $bFiltered = false;
                    $bHiddenUser = false;
                    if ($filterStatus == 'hidden') {
                        if (!in_array($arUser['ID'], $arResult['HIDDEN_USERS'])) {
                            continue;
                        }
                        $bHiddenUser = true;
                        $bFiltered = true;
                    } elseif ($filterStatus == 'disabled') {
                        if ($arUser['ACTIVE'] == 'Y') {
                            continue;
                        }
                        if (empty($arFile)) {
                            continue;
                        }
                        $bHiddenUser = true;
                        $bFiltered = true;
                    } elseif (!empty($filterStatus)) {
                        if (empty($arFile)) {
                            continue;
                        }
                        if (crc32($arResult['FILES_STATUS'][ $arUser['ID'] ][ $arFile['ID'] ]) != $filterStatus) {
                            continue;
                        }
                        $bFiltered = true;
                    } elseif (!$bExport) {
                        $class .= ' d-none hidden';
                    }

                    /*
                     * Чтобы скрытые вручную сотрудники не попадали в экспорт отчета.
                     */
                    if ($bExport && $bHiddenUser && $filterStatus != 'hidden') {
                        continue;
                    }

                    if ($bFirstRow) {
                        $bFirstRow = false;
                        echo str_replace(
                            '##CHECKBOX##',
                            $bFiltered||$bExport ? '' : '<input type="checkbox" />',
                            $firstRowHtml
                        );

                        $arResult['EXPORT']['ROWS'][] = [
                            'HEAD' => $arDep["NAME"]
                        ];
                    }

                    $arData = [
                        'USER'   => [
                            'VALUE' => implode(' ', [$arUser['LAST_NAME'], $arUser['NAME'], $arUser['SECOND_NAME']]),
                            'LINK' => 'https://' . $_SERVER['SERVER_NAME'] . '/company/personal/user/' . $arUser['ID'] . '/'
                        ],
                    ];

                    ?>
                    <tr class="<?=$class;?> dep-<?=$id?>">
                        <td>
                        <?if (!$bFiltered && !$bExport) :?>
                            <input
                                class="js-user-checker"
                                type="checkbox"
                                value="<?=$arUser['ID']?>"
                                <?=empty($arFile)?'':'disabled'?>
                                />
                        <?endif;?>
                        </td>
                        <td
                            class="js-user-name"
                            data-id="<?=$arUser['ID'];?>">
                            <?=implode(' ', [$arUser['LAST_NAME'], $arUser['NAME'], $arUser['SECOND_NAME']]);?>
                        </td>
                        <?php
                        if (!empty($arFile)) {
                            $bHideButton = false;
                            if (crc32($arResult['FILES_STATUS'][ $arUser['ID'] ][ $arFile['ID'] ]) == 1398495989) {
                                // Отклонено пользователем
                                $bHideButton = true;
                            }
                            if (!empty($arFile['SIGNS']) && !array_key_exists($arUser['ID'], $arResult['STEP2'])) {
                                // БП Удален
                                $bHideButton = true;
                            }

                            echo '<td>' . $arResult['FILES_STATUS'][ $arUser['ID'] ][ $arFile['ID'] ] . '</td>';
                            echo '<td>';
                            echo '[' . $arResult['FILES_NUMBER'][ $arUser['ID'] ][ $arFile['ID'] ] . '] <a href="' . $arFile['SRC'] . '" download="' . $arFile['ORIGINAL_NAME'] . '">' . $arFile['ORIGINAL_NAME'] . '</a>';
                            $arPodpis = [
                                $arResult['FILES_NUMBER'][ $arUser['ID'] ][ $arFile['ID'] ]
                            ];
                            foreach ($arFile['SIGNS'] as $arSign) {
                                $signText = 'Подписано [' . $arSign['SIGNS'][0]['TIMESTAMP_X']->format('d.m.Y H:i:s') . '] ' . $arSign['SIGNER_NAME'];
                                echo '<br/>' . $signText;
                                $arPodpis[] = $signText;
                            }
                            if (count($arPodpis) == 1) {
                                $arPodpis[] = $arFile['ORIGINAL_NAME'];
                            }
                            if (!$bExport && $bHideButton) {
                                echo '<br/><input type="button" value="Удалить запись" class="js-remove-file ui-btn ui-btn-xs ui-btn-danger" data-user="' . $arUser['ID'] . '" data-file="' . $arFile['ID'] . '" />';
                            }
                            echo '</td>';
                            $arData['STATUS'] = [
                                'VALUE' => $arResult['FILES_STATUS'][ $arUser['ID'] ][ $arFile['ID'] ]
                            ];
                            $arData['FILE'] = [
                                'VALUE' => implode(PHP_EOL, $arPodpis),
                                'LINK' => 'https://' . $_SERVER['SERVER_NAME'] . $arFile['SRC']
                            ];
                        }

                        if ($bHiddenUser) {
                            echo '<td colspan="' . (!empty($arFile) ? 1 : 3) . '"><input type="button" value="Вернуть в список" class="js-show-user ui-btn ui-btn-xs ui-btn-primary" data-user="' . $arUser['ID'] . '" />
                            </td>';
                        } else {
                            echo '<td colspan="' . (!empty($arFile) ? 1 : 3) . '"><input type="button" value="Скрыть пользователя" class="js-hide-user ui-btn ui-btn-xs ui-btn-danger" data-user="' . $arUser['ID'] . '" />
                            </td>';
                        }
                        ?>
                    </tr>
                    <?php
                    $arResult['EXPORT']['ROWS'][] = $arData;
                }
            }
            ?>
        </tbody>
    </table>

    <div class="js-buttons d-none hidden">
        <div class="row m-0">
            <div class="col-3">
                <button type="button" class="btn btn-success js-step-1">Сформировать файлы <span id="users-count"></span></button>
            </div>
            <div class="col-3"></div>
            <div class="col-3"></div>
            <div class="col-3"></div>
        </div>
    </div>
</div>
<div
    class="modal"
    id="modalStep1"
    tabindex="-1"
    role="dialog"
    aria-labelledby="modalStep1"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5>Формирование уведомлений ЭТК</h5>
                <button
                    type="button"
                    class="close"
                    data-dismiss="modal"
                    aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form method="POST" class="js-create-step-1">
                <input type="hidden" name="CURRENT_USER" value="<?=$GLOBALS['USER']->GetID();?>" />
                <div class="modal-body">
                    <div class="row">
                        <div class="col-6">
                            Дата уведомления
                            <br/>
                            <input
                                class="form-control js-calendar"
                                name="DATE"
                                placeholder="<?=date('d.m.Y');?>"
                                required />
                        </div>
                        <div class="col-6 position-relative">
                            Согласующий
                            <br/>
                            <input
                                class="form-control"
                                name="RUKL_NAME"
                                id="RUKL_NAME"
                                value=""
                                onclick="$('#RUKL_selector_content').show();"
                                />
                            <div class="position-absolute user-selector-popup">
                                <script type="text/javascript" src="/bitrix/components/bitrix/intranet.user.selector.new/templates/.default/users.js"></script>
                                <script type="text/javascript">
                                    function hidePopup(){
                                        $('#RUKL_selector_content').hide();
                                    }
                                </script>
                                <?$APPLICATION->IncludeComponent(
                                    'bitrix:intranet.user.selector.new',
                                    '',
                                    array(
                                        'NAME'                  => "RUKL",
                                        'INPUT_NAME'            => "RUKL_NAME",
                                        'TEXTAREA_MIN_HEIGHT'   => 30,
                                        'TEXTAREA_MAX_HEIGHT'   => 60,
                                        'INPUT_VALUE'           => 1,
                                        'EXTERNAL'              => 'I',
                                        'POPUP'                 => 'Y',
                                        "MULTIPLE"              => "N",
                                        'SOCNET_GROUP_ID'       => '',
                                        'ON_SELECT'             => 'hidePopup'
                                    ),
                                    false
                                );?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <br/>
                            Список пользователей:
                            <br/>
                            <div class="users"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input
                        class="ui-btn ui-btn-primary"
                        type="submit"
                        value="Сформировать" />
                </div>
            </form>
        </div>
    </div>
</div>
<?php
if ($bExport) {
    $APPLICATION->RestartBuffer();
    /**
     * Посчитать Итого по статусам
     * @see https://corp.tularegion.local/company/personal/user/2440/tasks/task/view/46587/
     */
    $arRows = $arResult['EXPORT']['ROWS'];
    $arItogo = [];
    foreach ($arRows as $row) {
        if (isset($row['STATUS'])) {
            $arItogo[ $row['STATUS']['VALUE'] ]++;
        }
    }
    ksort($arItogo);
    $arItogoRows = [];
    foreach ($arItogo as $status => $count) {
        $arItogoRows[] = [
            'HEAD' => $status . ': ' . $count
        ];
    }
    $arResult['EXPORT']['ROWS'] = array_merge(
        $arItogoRows,
        $arResult['EXPORT']['ROWS']
    );

    $this->__component->exportExcel($arResult['EXPORT']);
    exit;
}
