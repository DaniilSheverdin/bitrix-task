<?php

use Bitrix\Main\Page\Asset;
use Citto\Controlorders\Orders;
use Citto\ControlOrders\Settings;
use Bitrix\Main\UI\Filter\Options;
use Bitrix\Main\Grid\Panel\Actions;
use Bitrix\Main\Grid\Panel\Snippet\Onchange;
use Bitrix\Main\Grid\Options as GridOptions;
use Citto\ControlOrders\Main\AjaxController;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

Asset::getInstance()->addCss('/bitrix/css/main/grid/webform-button.css');

global $USER;

CModule::IncludeModule('iblock');

$list_id = 'control-orders-list';

$grid_options = new GridOptions($list_id);
$arUsedColumns = $grid_options->getUsedColumns();

$arUiFilter = [
    ['id' => 'NUMBER', 'name' => 'Номер', 'type' => 'text', 'default' => true],
    ['id' => 'DATE_CREATE', 'name' => 'Дата поручения', 'type' => 'date', 'default' => true],
    ['id' => 'DATE_ISPOLN', 'name' => 'Дата исполнения', 'type' => 'date', 'default' => true],
];

if (
    $arResult['PERMISSIONS']['controler'] ||
    $arResult['PERMISSIONS']['kurator'] ||
    $arResult['PERMISSIONS']['protocol'] ||
    $arResult['PERMISSIONS']['full_access']
) {
    $arIspolnitelItems = [];
    foreach ($arResult['ISPOLNITELTYPES'] as $sKey => $sValue) {
        $arIspolnitelItems[ 'all-' . $sValue['ID'] ] = $sValue['VALUE'];
        foreach ($arResult['ISPOLNITELS'] as $k => $v) {
            if ($v['PROPERTY_TYPE_ENUM_ID'] != $sValue['ID']) {
                continue;
            }
            $arIspolnitelItems[ $v['ID'] ] = $v['NAME'];
        }
    }

    $arUiFilter[] = [
        'id'        => 'ISPOLNITEL',
        'name'      => 'Исполнитель',
        'type'      => 'list',
        'params'    => ['multiple' => 'Y'],
        'items'     => $arIspolnitelItems,
    ];
    $arUiFilter[] = [
        'id'        => 'SUBEXECUTOR',
        'name'      => 'Соисполнитель',
        'type'      => 'list',
        'params'    => ['multiple' => 'Y'],
        'items'     => $arIspolnitelItems,
    ];
}

if ($arResult['PERMISSIONS']['ispolnitel']) {
    $arUiFilter[] = [
        'id'    => 'ISPOLNITEL_TYPE',
        'name'  => 'Мой статус',
        'type'  => 'list',
        'items' => [
            'MAIN'  => 'Исполнитель',
            'SUB'   => 'Соисполнитель',
        ]
    ];
}

$type_filter = ['id' => 'TYPE', 'name' => 'Тип поручения', 'type' => 'list', 'params' => ['multiple' => 'Y'], 'default' => true];
foreach ($arResult['TYPES_DATA'] as $key => $value) {
    $type_filter['items'][ $key ] = $value['UF_NAME'];
}
$arUiFilter[] = $type_filter;

if (!empty($arResult['PERMISSIONS']['ispolnitel_delegate_users'])) {
    $arUiFilter[] = [
        'id'        => 'DELEGATE',
        'name'      => 'Делегирование',
        'type'      => 'list',
        'params'    => ['multiple' => 'Y'],
        'default'   => true,
        'items'     => $arResult['PERMISSIONS']['ispolnitel_delegate_users']
    ];
}

if (!$_REQUEST['action_filter']) {
    $arUiFilter[] = [
        'id'        => 'STATUS',
        'name'      => 'Состояние поручения',
        'type'      => 'list',
        'default'   => true,
        'items'     => [
            1135 => 'Новое',
            1136 => 'На исполнении',
            1137 => 'Ждет контроля',
            1138 => 'Ждет решения',
            1140 => 'Архив',
        ]
    ];
    $arUiFilter[] = [
        'id'        => 'ARCHIVE',
        'name'      => 'С учетом архива',
        'type'      => 'list',
        'default'   => true,
        'items'     => [
            'Y' => 'Да',
            'N' => 'Нет',
        ]
    ];
}

$this->SetViewTarget('inside_pagetitle', 100);

$backUrl = rawurlencode($APPLICATION->GetCurPageParam('', ['sessid', 'internal', 'grid_id', 'grid_action', 'bxajaxid', 'apply_filter', 'clear_nav']));

$arButtons = [];
$changelogDate = filemtime(__DIR__ . '/changelog.php');
$arButtons['changelog'] = '<a class="ui-btn ui-btn-success ui-btn-icon-business mr-2 js-changelog" href="#" id="changelog-' . $changelogDate . '" title="История изменений"></a>';
$APPLICATION->IncludeComponent(
    'bitrix:spotlight',
    '',
    [
        'ID'            => 'changelog-' . $changelogDate,
        'USER_TYPE'     => 'ALL',
        'JS_OPTIONS'    => [
            'targetElement' => 'changelog-' . $changelogDate,
            'targetVertex'  => 'middle-center',
            'content'       => 'Последние доработки модуля «Контроль поручений»',
        ]
    ]
);

$arButtons['export'] = '<a class="ui-btn ui-btn-light-border ui-btn-icon-download js-save-to-pdf mr-2" title="Выгрузить отчет"></a>';
$arButtons['legend'] = '<a class="ui-btn ui-btn-light-border ui-btn-icon-info js-legend-show mr-2" title="Легенда"></a>';
if (
    $arResult['PERMISSIONS']['controler'] ||
    $arResult['PERMISSIONS']['kurator'] ||
    $arResult['PERMISSIONS']['protocol'] ||
    $GLOBALS['USER']->IsAdmin()
) {
    $arButtons['add'] = '<a class="ui-btn ui-btn-primary ui-btn-icon-add" href="/control-orders/?edit=0&back_url=' . $backUrl . '">Добавить поручение</a>';
    if (!$arResult['PERMISSIONS']['protocol']) {
        $arButtons['import'] = '<a class="ui-btn ui-btn-primary ui-btn-icon-cloud" title="Импорт из АСЭД" href="/control-orders/import/?back_url=' . $backUrl . '"></a>';
    }
}
?>
<div class="pagetitle-container pagetitle-align-right-container">
    <?=implode("\r\n", $arButtons) ?>
    <div class="legend-detail">
        <div class="p-3 mb-1 alert-white">На исполнении, срок исполнения не наступил</div>
        <div class="p-3 mb-1 alert-success">Поручение исполнено</div>
        <div class="p-3 mb-1 alert-info">Поручение исполнено, но требуется дополнительный контроль за дальнейшим исполнением</div>
        <a class="p-3 mb-1 d-block alert-danger" href="/control-orders/?from_stats=only-red&back_url=<?=$backUrl ?>">Поручение не выполнено</a>
        <a class="p-3 d-block bg-orange" href="/control-orders/?from_stats=only-orange&back_url=<?=$backUrl ?>">Информация об исполнении не представлена (истек срок)</a>
    </div>
</div>
<?php
$this->EndViewTarget();

$curUserId = $GLOBALS['USER']->GetID();
$arFilter = $this->__component->getListFilter($list_id, $_REQUEST['spage']??'');
$arUiFilterData = (new Options($list_id . '_filter'))->getFilter([]);
$arTags = $this->__component->getAvailableTags();

if (!empty($arTags)) {
    $arUiFilter[] = [
        'id'        => 'TAGS',
        'name'      => 'Теги',
        'type'      => 'list',
        'default'   => true,
        'params'    => ['multiple' => 'Y'],
        'items'     => $arTags,
    ];
}

$arObjects = [];
$resObjects = CIBlockElement::GetList(
    false,
    ['IBLOCK_ID' => 511, 'ACTIVE' => 'Y',],
    false,
    false,
    ['ID', 'NAME', 'DETAIL_TEXT',]
);
$arItems = [];
$arObjects = [];
while ($rowObject = $resObjects->GetNext()) {
    $rowObject['DADATA'] = json_decode($rowObject['~DETAIL_TEXT'], true);
    $arObjects[ $rowObject['ID'] ] = $rowObject['DADATA']['value'] ? $rowObject['DADATA']['value'] : $rowObject['NAME'];
}
asort($arObjects);

$arUiFilter[] = [
    'id'        => 'OBJECT',
    'name'      => 'Объект поручения',
    'type'      => 'list',
    'default'   => true,
    'params'    => ['multiple' => 'Y'],
    'items'     => $arObjects,
];

require($_SERVER['DOCUMENT_ROOT'] . $this->__component->__path . '/ajax.php');
CBitrixComponent::includeComponentClass('citto:checkorders');

$arThemes = [];
$arThemesTree = AjaxController::classificatorTreeAction(false)[0]['children'];
foreach ($arThemesTree as $category) {
    if ($category['delete_node']) {
        continue;
    }
    $arThemes[ $category['id'] ] = $category['text'];
    foreach ($category['children'] as $theme) {
        if ($theme['delete_node']) {
            continue;
        }
        $arThemes[ $theme['id'] ] = $category['text'] . ' \ ' . $theme['text'];
    }
}

$arUiFilter[] = [
    'id'        => 'THEME',
    'name'      => 'Тематика',
    'type'      => 'list',
    'params'    => ['multiple' => 'Y'],
    'default'   => true,
    'items'     => $arThemes
];

$arUiFilter[] = [
    'id'        => 'WORK_STATUS',
    'name'      => 'Промежуточный статус',
    'type'      => 'list',
    'default'   => true,
    'items'     => [
        '' => 'Все',
        $arParams['ENUM']['WORK_INTER_STATUS']['TOVISA']['ID'] => 'На визировании',
        $arParams['ENUM']['WORK_INTER_STATUS']['TOSIGN']['ID'] => 'На подписи',
    ]
];

$arAdditional = [
    'controler_reject'  => 'Отклоненные контролерами',
    'soon'              => 'Подходит срок',
    'expired'           => 'Просрочено',
];

if (
    $arResult['PERMISSIONS']['controler'] ||
    $arResult['PERMISSIONS']['kurator'] ||
    $arResult['PERMISSIONS']['protocol'] ||
    $GLOBALS['USER']->IsAdmin()
) {
    $arAdditional['vote'] = 'Опрос заявителя';
}

$arUiFilter[] = [
    'id'        => 'ADDITIONAL',
    'name'      => 'Требует внимания',
    'type'      => 'list',
    'default'   => true,
    'items'     => $arAdditional,
];

$sFilterViewTarget = $arParams['RENDER_FILTER_INTO_VIEW'] ?? 'inside_pagetitle';
$this->SetViewTarget($sFilterViewTarget, 10);
?><div class="pagetitle-container pagetitle-flexible-space">
<?$APPLICATION->IncludeComponent(
    'bitrix:main.ui.filter',
    '',
    [
        'FILTER_ID'          => $list_id . '_filter',
        'GRID_ID'            => $list_id,
        'FILTER'             => $arUiFilter,
        'ENABLE_LIVE_SEARCH' => true,
        'ENABLE_LABEL'       => true,
    ]
);?>
</div>
<?
$this->EndViewTarget();

$arColumns   = [];
$arColumns[] = ['id' => 'ID', 'name' => 'ID', 'sort' => 'ID', 'default' => false];
$arColumns[] = ['id' => 'NAME', 'name' => 'Наименование', 'sort' => 'NAME', 'default' => false];
$arColumns[] = ['id' => 'TEXT', 'name' => 'Содержание поручения', 'sort' => 'TEXT', 'default' => true];
$arColumns[] = ['id' => 'NUMBER', 'name' => 'Номер', 'sort' => 'NUMBER_INT', 'default' => true];
$arColumns[] = ['id' => 'DATE_CREATE', 'name' => 'Дата поручения', 'sort' => 'DATE_CREATE_TIMESTAMP', 'default' => true];
$arColumns[] = ['id' => 'DATE_ISPOLN', 'name' => 'Срок исполнения - план', 'sort' => 'DATE_ISPOLN_TIMESTAMP', 'default' => true];
$arColumns[] = ['id' => 'DATE_FACT_ISPOLN', 'name' => 'Дата отчета', 'sort' => 'DATE_FACT_ISPOLN_TIMESTAMP', 'default' => true];
$arColumns[] = ['id' => 'ISPOLNITEL', 'name' => 'Исполнитель', 'sort' => 'ISPOLNITEL_INT', 'default' => true];
$arColumns[] = ['id' => 'STATUS', 'name' => 'Состояние поручения', 'sort' => 'STATUS_INT', 'default' => !$_REQUEST['action_filter']];
$arColumns[] = ['id' => 'WORK_STATUS', 'name' => 'Промежуточный статус', 'sort' => false, 'default' => true];
$arColumns[] = ['id' => 'DELEGATE_USER', 'name' => 'Делегировано', 'sort' => 'DELEGATE_USER_ID', 'default' => true];
$arColumns[] = ['id' => 'SUBEXECUTOR_DATE', 'name' => 'Срок соисполнителя', 'sort' => 'SUBEXECUTOR_DATE_TIMESTAMP', 'default' => true];
$arColumns[] = ['id' => 'EXECUTOR_DATE', 'name' => 'Срок исполнителя', 'sort' => false, 'default' => true];

$bDateEditable = false;
if (
    ($arResult['PERMISSIONS']['controler'] && $_REQUEST['action_filter'] == 1137) ||
    ($arResult['PERMISSIONS']['kurator'] && $_REQUEST['action_filter'] == 1138)
) {
    $bDateEditable = true;
}

$arColumns[] = ['id' => 'ISPOLN_DATA', 'name' => 'Ход исполнения поручения', 'sort' => false, 'default' => true, 'editable' => $bDateEditable];

if (
    $_REQUEST['action_filter'] == 1135 &&
    !$arResult['PERMISSIONS']['full_access'] &&
    !$arResult['PERMISSIONS']['protocol']
) {
    foreach ($arFilter[0][-1]['PROPERTY_DELEGATION'] as $key => $value) {
        if (in_array($arResult['ISPOLNITELS'][ $value ]['PROPERTY_TYPE_CODE'], ['zampred', 'gubernator'])) {
            unset($arFilter[0][-1]['PROPERTY_DELEGATION'][ $key ]);
        }
    }
}

if (
    $arFilter['PROPERTY_ACTION'] != Settings::$arActions['ARCHIVE'] &&
    (!isset($arUiFilterData['ARCHIVE']) || $arUiFilterData['ARCHIVE'] != 'Y') &&
    !isset($_REQUEST['from_stats'])
) {
    $arFilter['!PROPERTY_ACTION'][] = Settings::$arActions['ARCHIVE'];
}

if (isset($_REQUEST['debug_filter'])) {
    pre($arFilter);
}
if (isset($_REQUEST['debug_perm'])) {
    pre($arResult['PERMISSIONS']);
}

// $arSelect = $this->__component->arOrderFields;
$arSelect = [
    'ID',
    'NAME',
    'IBLOCK_ID',
    'DETAIL_TEXT',
    'PROPERTY_POST',
    'PROPERTY_ISPOLNITEL',
    'PROPERTY_CONTROLER',
    'PROPERTY_NUMBER',
    'PROPERTY_DATE_CREATE',
    'PROPERTY_DATE_ISPOLN',
    'PROPERTY_ACTION',
    'PROPERTY_TYPE',
    'PROPERTY_VIEWS',
    'PROPERTY_DATE_FACT_ISPOLN',
    'PROPERTY_DOPSTATUS',
    'PROPERTY_NEWISPOLNITEL',
    'PROPERTY_DATE_ISPOLN_HIST',
    'PROPERTY_POSITION_TO',
    'PROPERTY_POSITION_FROM',
    'PROPERTY_DELEGATE_USER',
    'PROPERTY_NOT_STATS',
    'PROPERTY_SUBEXECUTOR_DATE',
    'PROPERTY_DELEGATION',
    'PROPERTY_DATE_ISPOLN_BAD',
    'PROPERTY_WORK_INTER_STATUS',
];

$obRes = CIBlockElement::GetList(
    ['id' => 'asc'],
    $arFilter,
    false,
    false,
    $arSelect
);

while ($arRow = $obRes->GetNext()) {
    if (
        isset($arUiFilterData['WORK_STATUS']) &&
        $arUiFilterData['WORK_STATUS'] != $arRow['PROPERTY_WORK_INTER_STATUS_ENUM_ID']
    ) {
        continue;
    }

    if (isset($_REQUEST['from_stats']) && $_REQUEST['from_stats'] == 'map_blue') {
        if (
            in_array('no_ispoln', $arRow['PROPERTY_TYPE_VALUE']) ||
            in_array('7qCIhAcZ', $arRow['PROPERTY_TYPE_VALUE'])
        ) {
            continue;
        }
    }

    if (empty($arRow['PROPERTY_NOT_STATS_ENUM_ID'])) {
        if (isset($_REQUEST['from_stats']) && $_REQUEST['from_stats'] == 'v_srok') {
            if (
                in_array('no_ispoln', $arRow['PROPERTY_TYPE_VALUE']) ||
                in_array('srok_narush', $arRow['PROPERTY_TYPE_VALUE'])
            ) {
                continue;
            }
        }

        if (
            isset($_REQUEST['from_stats']) &&
            $_REQUEST['from_stats'] == 'worked' &&
            in_array('no_ispoln', $arRow['PROPERTY_TYPE_VALUE'])
        ) {
            continue;
        }
    }

    $numberInt = (int)$arRow['PROPERTY_NUMBER_VALUE'];
    if ($numberInt == 0) {
        $numberInt = trim($arRow['PROPERTY_NUMBER_VALUE']);
    } elseif (0 == mb_strpos($arRow['PROPERTY_NUMBER_VALUE'], '0')) {
        $numberInt = trim($arRow['PROPERTY_NUMBER_VALUE']);
    }

    $arDates = [
        $arRow['PROPERTY_DATE_ISPOLN_VALUE']
    ];
    if (
        ($arResult['PERMISSIONS']['kurator'] || $GLOBALS['USER']->IsAdmin()) &&
        $_REQUEST['action_filter'] == 1138 &&
        $_REQUEST['resh'] == 1307
    ) {
        $arDates = array_merge($arDates, $arRow['PROPERTY_DATE_ISPOLN_HIST_VALUE']);
        if (count($arDates) > 1) {
            $arDates[0] = '<b>' . $arDates[0] . '</b>';
        }
    }

    $arrData = [
        'data'      => [
            'ID'                    => $arRow['ID'],
            'NAME'                  => $arRow['NAME'],
            'TEXT'                  => $arRow['~DETAIL_TEXT'],
            'NUMBER'                => $arRow['PROPERTY_NUMBER_VALUE'],
            'NUMBER_INT'            => $numberInt,
            'DATE_ISPOLN'           => $arRow['PROPERTY_DATE_ISPOLN_VALUE'] != $this->__component->disableSrokDate ?
                                        implode('<br/>', $arDates) :
                                        'Без срока',
            'DATE_ISPOLN_TIMESTAMP' => $arRow['PROPERTY_DATE_ISPOLN_VALUE'] != $this->__component->disableSrokDate ?
                                        strtotime($arRow['PROPERTY_DATE_ISPOLN_VALUE']) :
                                        0,
            'SUBEXECUTOR_DATE'           => $arRow['PROPERTY_SUBEXECUTOR_DATE_VALUE'] != $this->__component->disableSrokDate ?
                                        $arRow['PROPERTY_SUBEXECUTOR_DATE_VALUE'] :
                                        'Без срока',
            'SUBEXECUTOR_DATE_TIMESTAMP' => $arRow['PROPERTY_SUBEXECUTOR_DATE_VALUE'] != $this->__component->disableSrokDate ?
                                        strtotime($arRow['PROPERTY_SUBEXECUTOR_DATE_VALUE']) :
                                        0,
            'DATE_FACT_ISPOLN'           => $arRow['PROPERTY_DATE_FACT_ISPOLN_VALUE'],
            'DATE_FACT_ISPOLN_TIMESTAMP' => strtotime($arRow['PROPERTY_DATE_FACT_ISPOLN_VALUE']),
            'DATE_CREATE'           => $arRow['PROPERTY_DATE_CREATE_VALUE'],
            'DATE_CREATE_TIMESTAMP' => strtotime($arRow['PROPERTY_DATE_CREATE_VALUE']),
            'ISPOLNITEL'            => $arResult['ISPOLNITELS'][ $arRow['PROPERTY_ISPOLNITEL_VALUE'] ]['NAME'],
            'ISPOLNITEL_INT'        => $arRow['PROPERTY_ISPOLNITEL_VALUE'],
            'STATUS'                => $arRow['PROPERTY_ACTION_VALUE'],
            'STATUS_INT'            => $arRow['PROPERTY_ACTION_ENUM_ID'],
            'WORK_STATUS'           => $arRow['PROPERTY_WORK_INTER_STATUS_VALUE'],
            'WORK_STATUS_ID'        => $arRow['PROPERTY_WORK_INTER_STATUS_ENUM_ID'],
            'DELEGATE_USER_ID'      => $arRow['PROPERTY_DELEGATE_USER_VALUE'],
            'ISPOLN_DATA'           => '',
        ],
        'class'     => '',
        'actions'   => [
            [
                'text'    => 'Просмотр',
                'default' => true,
                'onclick' => 'openDetail("detail", ' . $arRow['ID'] . ', "", "' . $backUrl . '");',
            ],
        ],
        'rawdata'   => $arRow,
    ];

    if (
        ($arResult['PERMISSIONS']['kurator'] && in_array($arRow['PROPERTY_POST_VALUE'], [1112, $curUserId])) ||
        ($arResult['PERMISSIONS']['controler'] && $arRow['PROPERTY_CONTROLER_VALUE'] == $curUserId)
    ) {
        $arrData['actions'][] = [
            'text'    => 'Изменить',
            'default' => false,
            'onclick' => 'openDetail("edit", ' . $arRow['ID'] . ', "", "' . $backUrl . '");',
        ];
    }

    if (
        $arRow['PROPERTY_ACTION_ENUM_ID'] == 1138 &&
        $arResult['PERMISSIONS']['kurator'] &&
        in_array($arRow['PROPERTY_POST_VALUE'], [1112, $curUserId])
    ) {
        $arrData['actions'][] = [
            'text'    => 'Снять с контроля',
            'default' => false,
            'onclick' => 'openDetail("detail", ' . $arRow['ID'] . ', "action=add_kurator_comment&subaction=accept&from=list", "' . $backUrl . '");',
        ];
        $arrData['actions'][] = [
            'text'    => 'Направить на допконтроль',
            'default' => false,
            'onclick' => 'openDetail("detail", ' . $arRow['ID'] . ', "view=zametki_kurator", "' . $backUrl . '");',
        ];
    }

    $list[] = $arrData;
}

$ACTION_PANEL = [];

if ($_REQUEST['action_filter'] == 1137 && $arResult['PERMISSIONS']['controler']) {
    $obOnchange = new Onchange();
    $obOnchange->addAction(
        [
            'ACTION'    => Actions::CALLBACK,
            'DATA'      => [['JS' => 'add_action_position(self.parent)']]
        ]
    );
    $ACTION_PANEL = [
        'GROUPS' => [
            'TYPE' => [
                'ITEMS' => [
                    [
                        'ID'       => 'accept',
                        'TYPE'     => 'BUTTON',
                        'TEXT'     => 'Отправить на позицию',
                        'CLASS'    => 'save',
                        'ONCHANGE' => $obOnchange->toArray()
                    ]
                ],
            ]
        ],
    ];
} elseif ($_REQUEST['action_filter'] == 1138 && $arResult['PERMISSIONS']['kurator']) {
    switch ((int)$_REQUEST['resh']) {
        case 1276:
            $obOnchange = new Onchange();
            $obOnchange->addAction(
                [
                    'ACTION'    => Actions::CALLBACK,
                    'DATA'      => [['JS' => "KuratorAction('accept')"]]
                ]
            );
            $ACTION_PANEL=[
                'GROUPS' => [
                    'TYPE' => [
                        'ITEMS' => [
                            [
                                'ID'       => 'accept',
                                'TYPE'     => 'BUTTON',
                                'TEXT'     => 'Снять с контроля',
                                'CLASS'    => 'save',
                                'ONCHANGE' => $obOnchange->toArray()
                            ]
                        ],
                    ]
                ],
            ];
            break;
        case 1277:
            $obOnchange = new Onchange();
            $obOnchange->addAction(
                [
                    'ACTION'    => Actions::CALLBACK,
                    'DATA'      => [['JS' => "KuratorAction('reject')"]]
                ]
            );
            $ACTION_PANEL=[
                'GROUPS' => [
                    'TYPE' => [
                        'ITEMS' => [
                            [
                                'ID'       => 'accept',
                                'TYPE'     => 'BUTTON',
                                'TEXT'     => 'Направить на допконтроль',
                                'CLASS'    => 'save',
                                'ONCHANGE' => $obOnchange->toArray()
                            ]
                        ],
                    ]
                ],
            ];
            break;

        case 1307:
            $obOnchange = new Onchange();
            $obOnchange->addAction(
                [
                    'ACTION'    => Actions::CALLBACK,
                    'DATA'      => [['JS' => 'edit_action(self.parent)']]
                ]
            );
            $ACTION_PANEL = [
                'GROUPS' => [
                    'TYPE' => [
                        'ITEMS' => [
                            [
                                'ID'    => 'set-type',
                                'TYPE'  => 'DROPDOWN',
                                'NAME'  => 'type-action',
                                'ITEMS' => [
                                    ['VALUE' => '', 'NAME' => '- Выбрать -'],
                                    ['VALUE' => 'accept', 'NAME' => 'Снять с контроля'],
                                    ['VALUE' => 'reject', 'NAME' => 'Направить на допконтроль']
                                ]
                            ],
                            [
                                'ID'       => 'accept',
                                'TYPE'     => 'BUTTON',
                                'TEXT'     => 'Применить',
                                'CLASS'    => 'save',
                                'ONCHANGE' => $obOnchange->toArray()
                            ]
                        ],
                    ]
                ],
            ];
            break;
    }
} elseif (
    $_REQUEST['action_filter'] == 1134 &&
    ($arResult['PERMISSIONS']['controler'] || $arResult['PERMISSIONS']['kurator'])
) {
    $obOnchange = new Onchange();
    $obOnchange->addAction(
        [
            'ACTION'    => Actions::CALLBACK,
            'DATA'      => [['JS' => 'edit_action(self.parent)']]
        ]
    );

    $arActionItems = [
        ['VALUE' => '', 'NAME' => '- Выбрать -'],
        ['VALUE' => 'move_to_work', 'NAME' => 'Отправить на исполнение'],
    ];
    if ($arResult['PERMISSIONS']['kurator']) {
        $arActionItems[] = ['VALUE' => 'change_post', 'NAME' => 'Изменить куратора'];
    }
    if ($arResult['PERMISSIONS']['controler']) {
        $arActionItems[] = ['VALUE' => 'change_controler', 'NAME' => 'Изменить контроллера'];
    }
    $ACTION_PANEL = [
        'GROUPS' => [
            'TYPE' => [
                'ITEMS' => [
                    [
                        'ID'    => 'set-type',
                        'TYPE'  => 'DROPDOWN',
                        'NAME'  => 'type-action',
                        'ITEMS' => $arActionItems
                    ],
                    [
                        'ID'       => 'accept',
                        'TYPE'     => 'BUTTON',
                        'TEXT'     => 'Применить',
                        'CLASS'    => 'save',
                        'ONCHANGE' => $obOnchange->toArray()
                    ]
                ],
            ]
        ],
    ];

    $arColumns[] = ['id' => 'POST', 'name' => 'Куратор', 'default' => true, 'editable' => true];
    $arColumns[] = ['id' => 'CONTROLER', 'name' => 'Контролер', 'default' => true, 'editable' => true];

    $arChangeUsers = [];
    $orm = Bitrix\Main\UserGroupTable::getList([
        'select'    => ['USER_ID', 'USER', 'GROUP_ID', 'GROUP'],
        'filter'    => ['GROUP_ID' => [97, 98]]
    ]);
    $arHide = [
        74,
        580,
        570
    ];
    while ($arGroup = $orm->fetch()) {
        if (in_array($arGroup['USER_ID'], $arHide)) {
            continue;
        }
        $arChangeUsers[ $arGroup['GROUP_ID'] ][ $arGroup['USER_ID'] ] = $this->__component->getUserFullName($arGroup['USER_ID']);
    }
    asort($arChangeUsers[97]);
    asort($arChangeUsers[98]);

    if ($arResult['PERMISSIONS']['kurator']) {
        ?>
        <div class="d-none js-select-post">
            <select class="form-control">
                <?foreach ($arChangeUsers[98] as $key => $value) : ?>
                <option value="<?=$key ?>"><?=$value ?></option>
                <?endforeach;?>
            </select>
        </div>
        <?
    }

    if ($arResult['PERMISSIONS']['controler']) {
        ?>
        <div class="d-none js-select-controler">
            <select class="form-control">
                <?foreach ($arChangeUsers[97] as $key => $value) : ?>
                <option value="<?=$key ?>"><?=$value ?></option>
                <?endforeach;?>
            </select>
        </div>
        <?
    }
}

$bShowNewAccepting = false;
if (in_array(7770, $arResult['PERMISSIONS']['ispolnitel_ids'])) {
    $bShowNewAccepting = true;
} elseif (in_array(251527, $arResult['PERMISSIONS']['ispolnitel_ids'])) {
    $bShowNewAccepting = true;
} elseif (in_array(250530, $arResult['PERMISSIONS']['ispolnitel_ids'])) {
    $bShowNewAccepting = true;
}
$bShowNewAccepting = true;

if ($bShowNewAccepting && $_REQUEST['action_filter'] == 1135) {?>
<div class="box box-primary">
    <div class="box-body box-profile">
        <div id="view-switcher-container" class="calendar-view-switcher">
             <div class="view-switcher-list flex-wrap">
                    <a href="?action_filter=1135" <?=($_REQUEST['spage']=='')?'class="active"':'' ?> >Все (<?= (int)$arResult['COUNTERS_NEW']['FULL'] ?>)</a>
                    <a href="?action_filter=1135&spage=new_project" <?=($_REQUEST['spage']=='new_project')?'class="active"':'' ?>>Проекты (<?= (int)$arResult['COUNTERS_NEW']['PROJECT'] ?>)</a>
                    <a href="?action_filter=1135&spage=new_reject" <?=($_REQUEST['spage']=='new_reject')?'class="active"':'' ?>>Отклонённые (<?= (int)$arResult['COUNTERS_NEW']['REJECT'] ?>)</a>
             </div>
        </div>
     </div>
</div>
<?
} elseif (
        $_REQUEST['action_filter'] == 1136 ||
        $_REQUEST['action_filter'] == 4005
) {?>
<div class="box box-primary">
    <div class="box-body box-profile">
        <div id="view-switcher-container" class="calendar-view-switcher">
             <div class="view-switcher-list flex-wrap">
                    <a href="?action_filter=1136" <?=($_REQUEST['spage']=='')?'class="active"':'' ?> >Все (<?= (int)$arResult['COUNTERS_WORK']['FULL'] ?>)</a>
                    <a href="?action_filter=1136&spage=my" <?=($_REQUEST['spage']=='my')?'class="active"':'' ?>>На исполнении (<?= (int)$arResult['COUNTERS_WORK']['MY'] ?>)</a>
                    <a href="?action_filter=1136&spage=sub" <?=($_REQUEST['spage']=='sub')?'class="active"':'' ?>>Соисполнение (<?= (int)$arResult['COUNTERS_WORK']['SUB'] ?>)</a>
                    <?
                    if ($arResult['COUNTERS_WORK']['DELEGATE'] > 0) {
                        ?>
                        <a href="?action_filter=1136&spage=delegate" <?=($_REQUEST['spage']=='delegate')?'class="active"':'' ?>>Делегировано (<?= (int)$arResult['COUNTERS_WORK']['DELEGATE'] ?>)</a>
                        <?
                    }

                    if (
                        $arResult['PERMISSIONS']['ispolnitel_main'] ||
                        (
                            in_array($arResult['PERMISSIONS']['ispolnitel_data']['PROPERTY_TYPE_CODE'], ['zampred', 'gubernator']) &&
                            $arResult['PERMISSIONS']['ispolnitel_implementation']
                        )
                    ) {
                        ?>
                        <a href="?action_filter=1136&spage=sign" <?=($_REQUEST['spage']=='sign')?'class="active"':'' ?>>На подписи (<?= (int)$arResult['COUNTERS_WORK']['SIGN'] ?>)</a>
                        <?
                    }
                    if ($arResult['PERMISSIONS']['ispolnitel_submain']) {
                        ?>
                        <a href="?action_filter=1136&spage=sign_my" <?=($_REQUEST['spage']=='sign_my')?'class="active"':'' ?>>На подписи у меня (<?= (int)$arResult['COUNTERS_WORK']['SIGN_MY'] ?>)</a>
                        <a href="?action_filter=1136&spage=sign_other" <?=($_REQUEST['spage']=='sign_other')?'class="active"':'' ?>>На подписи другие (<?= (int)$arResult['COUNTERS_WORK']['SIGN_OTHER'] ?>)</a>
                        <?
                    }
                    ?>
                    <a href="?action_filter=1136&spage=visa" <?=($_REQUEST['spage']=='visa')?'class="active"':'' ?>>Визирование (<?= (int)$arResult['COUNTERS_WORK']['VISA'] ?>)</a>
                    <a href="?action_filter=4005&spage=curator_comments" <?=($_REQUEST['spage']=='curator_comments')?'class="active"':'' ?>>Замечания куратора (<?= (int)$arResult['COUNTERS_WORK']['CURATOR_COMMENTS'] ?>)</a>
             </div>
        </div>
     </div>
</div>
<?
} elseif (
    $_REQUEST['action_filter'] == 1138 &&
    (
        $arResult['PERMISSIONS']['controler'] ||
        $arResult['PERMISSIONS']['kurator'] ||
        $arResult['PERMISSIONS']['protocol'] ||
        $arResult['PERMISSIONS']['ispolnitel_main'] ||
        $arResult['PERMISSIONS']['ispolnitel_submain'] ||
        $arResult['PERMISSIONS']['ispolnitel_implementation'] ||
        $arResult['PERMISSIONS']['full_access']
    )
) {
?>
<div class="box box-primary">
    <div class="box-body box-profile">
        <div id="view-switcher-container" class="calendar-view-switcher">
             <div class="view-switcher-list">
                    <a href="?action_filter=1138" <?=($_REQUEST['resh']=='')?'class="active"':'' ?> >Все (<?= (int)$arResult['COUNTERS_RESH']['FULL'] ?>)</a>
                    <a href="?action_filter=1138&resh=1276" <?=($_REQUEST['resh']=='1276')?'class="active"':'' ?>>На снятие с контроля (<?= (int)$arResult['COUNTERS_RESH'][1276] ?>)</a>
                    <a href="?action_filter=1138&resh=1277" <?=($_REQUEST['resh']=='1277')?'class="active"':'' ?> >На допконтроль (<?= (int)$arResult['COUNTERS_RESH'][1277] ?>)</a>
                    <a href="?action_filter=1138&resh=1307" <?=($_REQUEST['resh']=='1307')?'class="active"':'' ?> >Не выполнено (<?= (int)$arResult['COUNTERS_RESH'][1307] ?>)</a>
                    <a href="?action_filter=1138&resh=1278" <?=($_REQUEST['resh']=='1278')?'class="active"':'' ?>>Требует внимания (<?= (int)$arResult['COUNTERS_RESH'][1278] ?>)</a>
             </div>
        </div>
     </div>
</div>
<?} elseif (
    $_REQUEST['action_filter'] == 1137 &&
    (
        $arResult['PERMISSIONS']['controler'] ||
        $arResult['PERMISSIONS']['kurator'] ||
        $arResult['PERMISSIONS']['protocol'] ||
        $arResult['PERMISSIONS']['ispolnitel_main'] ||
        $arResult['PERMISSIONS']['ispolnitel_submain'] ||
        $arResult['PERMISSIONS']['ispolnitel_implementation'] ||
        $arResult['PERMISSIONS']['full_access']
    )
) {?>
<div class="box box-primary">
    <div class="box-body box-profile">
        <div id="view-switcher-container" class="calendar-view-switcher">
             <div class="view-switcher-list">
                    <a href="?action_filter=1137" <?=($_REQUEST['cont_obr']=='')?'class="active"':'' ?> >Все (<?= (int)$arResult['COUNTERS_CONTROLER_STATUS']['FULL'] ?>)</a>
                    <a href="?action_filter=1137&cont_obr=on_accepting" <?=($_REQUEST['cont_obr']=='on_accepting')?'class="active"':'' ?>>Ждут подтверждения (<?= (int)$arResult['COUNTERS_CONTROLER_STATUS']['on_accepting'] ?>)</a>
                    <a href="?action_filter=1137&cont_obr=on_position" <?=($_REQUEST['cont_obr']=='on_position')?'class="active"':'' ?>>Ожидают позиции (<?= (int)$arResult['COUNTERS_CONTROLER_STATUS']['on_position'] ?>)</a>
                    <a href="?action_filter=1137&cont_obr=on_beforing" <?=($_REQUEST['cont_obr']=='on_beforing')?'class="active"':'' ?> >Не обработаны (<?= (int)$arResult['COUNTERS_CONTROLER_STATUS']['on_beforing'] ?>)</a>
             </div>
        </div>
     </div>
</div>
<?}

/**
 * Сортировка массива по ключу.
 * @param string $key   Ключ для сортировки.
 * @param string $order Направление сортировки.
 * @return Closure
 */
function build_sorter(string $key = 'ID', string $order = 'asc')
{
    return static function ($a, $b) use ($key, $order) {
        return $order == 'asc' ?
            strnatcmp('str-' . $a['data'][ $key ], 'str-' . $b['data'][ $key ]) :
            strnatcmp('str-' . $b['data'][ $key ], 'str-' . $a['data'][ $key ]);
    };
}
$sort = $grid_options->getSorting(
    [
        'sort' => [
            'DATE_CREATE_TIMESTAMP'   => 'asc',
        ],
        'vars' => [
            'by'    => 'by',
            'order' => 'order'
        ]
    ]
);

$by = array_keys($sort['sort'])[0];
$order = $sort['sort'][ $by ];
usort(
    $list,
    build_sorter($by, $order)
);

$cntRows = count($list);

$sDateNow = strtotime(date('Y-m-d 00:00:00'));

$navParams = $grid_options->GetNavParams();
$obList = new CDBResult();
$obList->InitFromArray($list);
$obList->NavStart($navParams['nPageSize']);
$arList = [];
$obOrders = new Orders();
while ($arrData = $obList->Fetch()) {
    $arViews = array_filter(
        explode(',', $arrData['rawdata']['PROPERTY_VIEWS_VALUE'])
    );
    if (!in_array($curUserId, $arViews)) {
        $arrData['class'] = 'font-weight-bold';
    }
    // if (substr_count($arrData['rawdata']['PROPERTY_VIEWS_VALUE'], ',' . $curUserId . ',') == 0) {
    //     $arrData['class'] = 'font-weight-bold';
    // }
    if (in_array('ISPOLN_DATA', $arUsedColumns)) {
        $obResIspoln = CIBlockElement::GetList(
            ['DATE_CREATE' => 'DESC'],
            [
                'IBLOCK_ID'         => (int)$arParams['IBLOCK_ID_ORDERS_COMMENT'],
                'PROPERTY_PORUCH'   => (int)$arrData['rawdata']['ID'],
                'PROPERTY_TYPE'     => 1132
            ],
            false,
            ['nPageSize' => 1],
            [
                'PREVIEW_TEXT',
                'DETAIL_TEXT',
            ]
        );
        if ($arIspoln = $obResIspoln->GetNext()) {
            if (empty($arIspoln['DETAIL_TEXT'])) {
                $arIspoln['DETAIL_TEXT'] = $arIspoln['PREVIEW_TEXT'];
                $arIspoln['~DETAIL_TEXT'] = $arIspoln['~PREVIEW_TEXT'];
            }
            if ($arrData['rawdata']['PROPERTY_DOPSTATUS_VALUE'] != '') {
                $arIspoln['~DETAIL_TEXT'] = 'Передача на исполнение: ' . $arResult['ISPOLNITELS'][ $arrData['rawdata']['PROPERTY_NEWISPOLNITEL_VALUE'] ]['NAME'] . '<br>' . $arIspoln['~DETAIL_TEXT'];
            }

            $arrData['data']['ISPOLN_DATA'] = $arIspoln['~DETAIL_TEXT'];
        }
    }

    if (in_array('WORK_STATUS', $arUsedColumns)) {
        $workStatusText = $arrData['rawdata']['PROPERTY_WORK_INTER_STATUS_VALUE'];
        $arDelegator = [];
        if (!empty($arrData['rawdata']['PROPERTY_DELEGATION_VALUE']) && (int)$arrData['rawdata']['PROPERTY_DELEGATION_VALUE'][0] > 0) {
            $arDelegator = $arResult['ISPOLNITELS'][ $arrData['rawdata']['PROPERTY_DELEGATION_VALUE'][0] ];
        }
        if (!empty($arDelegator) && !empty($workStatusText)) {
            $obResIspoln = CIBlockElement::GetList(
                ['DATE_CREATE' => 'DESC'],
                [
                    'IBLOCK_ID'         => (int)$arParams['IBLOCK_ID_ORDERS_COMMENT'],
                    'PROPERTY_PORUCH'   => (int)$arrData['rawdata']['ID'],
                    'PROPERTY_TYPE'     => 1131
                ],
                false,
                ['nPageSize' => 1],
                [
                    'PROPERTY_CURRENT_USER'
                ]
            );
            if ($arIspoln = $obResIspoln->GetNext()) {
                if (
                    $arIspoln['PROPERTY_CURRENT_USER_VALUE'] == $arDelegator['PROPERTY_RUKOVODITEL_VALUE']
                ) {
                    if ($arDelegator['PROPERTY_TYPE_CODE'] == 'zampred') {
                        $workStatusText .= ' заместителя председателя правительства';
                    } elseif ($arDelegator['PROPERTY_TYPE_CODE'] == 'gubernator') {
                        $workStatusText .= ' заместителя губернатора';
                    }
                }
            }
        }
        $arrData['data']['WORK_STATUS'] = $workStatusText;
    }

    if (in_array('EXECUTOR_DATE', $arUsedColumns)) {
        if ($arRow['PROPERTY_DATE_ISPOLN_VALUE'] != $this->__component->disableSrokDate) {
            $executorDate = (new Orders())->getSrok($arrData['rawdata']['ID'], (int)$arrData['rawdata']['PROPERTY_DELEGATE_USER_VALUE']);
            $arrData['data']['EXECUTOR_DATE'] = $executorDate;
            $arrData['data']['EXECUTOR_DATE_TIMESTAMP'] = strtotime($executorDate);
        }
    }

    $arrData['class_info'] = $obOrders->getColor(0, $arrData['rawdata']);

    $arrData['data']['DELEGATE_USER'] = $this->__component->getUserFullName((int)$arrData['rawdata']['PROPERTY_DELEGATE_USER_VALUE'], true);
    $arrData['data']['POST'] = $this->__component->getUserFullName((int)$arrData['rawdata']['PROPERTY_POST_VALUE'], true);
    $arrData['data']['CONTROLER'] = $this->__component->getUserFullName((int)$arrData['rawdata']['PROPERTY_CONTROLER_VALUE'], true);

    if (in_array('TEXT', $arUsedColumns)) {
        if (!empty($arrData['rawdata']['PROPERTY_POSITION_FROM_VALUE'])) {
            foreach ($arrData['rawdata']['PROPERTY_POSITION_FROM_VALUE'] as $positionId) {
                $arPositionInfo = $obOrders->getByID($positionId);
                if ($arPositionInfo['PROPERTY_ACTION_ENUM_ID'] != Settings::$arActions['ARCHIVE']) {
                    $arrData['data']['TEXT'] = '[На позицию] ' . $arrData['data']['TEXT'];
                    break;
                }
            }
        }

        if (!empty($arrData['rawdata']['PROPERTY_POSITION_TO_VALUE'])) {
            $arrData['data']['TEXT'] = '[Позиция] ' . $arrData['data']['TEXT'];
        }
    }

    unset($arrData['rawdata']);
    $arList[] = $arrData;
}

$APPLICATION->IncludeComponent(
    'bitrix:main.ui.grid',
    'modified',
    [
        'GRID_ID'                   => $list_id,
        'COLUMNS'                   => $arColumns,
        'ROWS'                      => $arList,
        'SHOW_ROW_CHECKBOXES'       => true,
        'NAV_OBJECT'                => $obList,
        'AJAX_MODE'                 => 'Y',
        'AJAX_ID'                   => CAjax::GetComponentID('bitrix:main.ui.grid', 'modified', ''),
        'PAGE_SIZES'                => [
            ['NAME' => '20', 'VALUE' => '20'],
            ['NAME' => '50', 'VALUE' => '50'],
            ['NAME' => '100', 'VALUE' => '100'],
            ['NAME' => '500', 'VALUE' => '500'],
            ['NAME' => 'Все', 'VALUE' => '99999'],
        ],
        'AJAX_OPTION_JUMP'          => 'Y',
        'SHOW_CHECK_ALL_CHECKBOXES' => true,
        'SHOW_ROW_ACTIONS_MENU'     => true,
        'ACTION_PANEL'              => $ACTION_PANEL,
        'SHOW_GRID_SETTINGS_MENU'   => true,
        'SHOW_NAVIGATION_PANEL'     => true,
        'SHOW_PAGINATION'           => true,
        'SHOW_SELECTED_COUNTER'     => true,
        'SHOW_TOTAL_COUNTER'        => true,
        'SHOW_PAGESIZE'             => true,
        'SHOW_ACTION_PANEL'         => true,
        'ALLOW_COLUMNS_SORT'        => true,
        'ALLOW_COLUMNS_RESIZE'      => true,
        'ALLOW_HORIZONTAL_SCROLL'   => true,
        'ALLOW_SORT'                => true,
        'ALLOW_PIN_HEADER'          => true,
        'AJAX_OPTION_HISTORY'       => 'Y',
        'TOTAL_ROWS_COUNT'          => $cntRows,
    ]
);
?>
<form method="post" action="" class="" id="form_add"></form>
<div class="d-none" id="text-kurator">
    <?$APPLICATION->IncludeComponent(
        'bitrix:fileman.light_editor',
        '',
        [
            'CONTENT' => '',
            'INPUT_NAME' => 'DETAIL_TEXT_KURATOR',
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
            'JS_OBJ_NAME' => 'DETAIL_TEXT'
        ]
    );?>
</div>
<?php

$this->SetViewTarget('sidebar', 100);
$GLOBALS['arCalendarFilter'] = $arFilter;
require 'list_calendar.php';
require 'list_stats.php';
$this->EndViewTarget();
