<?php

use CSite;
use CIntranetUtils;
use Bitrix\Main\Loader;
use Bitrix\Main\UserTable;
use Bitrix\Main\Page\Asset;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) {
    die();
}

global $APPLICATION;

$APPLICATION->AddHeadString('<script src="/local/js/select2.min.js"></script>');
Asset::getInstance()->addJs('/bitrix/templates/.default/bootstrap.min.js');
Asset::getInstance()->addCss('/bitrix/templates/.default/bootstrap.min.css');
Asset::getInstance()->addCss('/local/css/select2.css');

\Bitrix\Main\UI\Extension::load("ui.forms");


if (!isset($arParams['ID'])) {
    LocalRedirect('/');
}

Loader::includeModule('blog');
Loader::includeModule('intranet');
Loader::includeModule('socialnetwork');
$arPost = CBlogPost::GetByID($arParams['ID']);
$bAccess = (CSite::InGroup([131]) || $GLOBALS['USER']->IsAdmin());

if (!$bAccess) {
    ShowError('Доступ запрещён');
} else {
    $arPostFields = $GLOBALS['USER_FIELD_MANAGER']->GetUserFields('BLOG_POST', $arPost['ID'], LANGUAGE_ID);

    if ($arPostFields['UF_BLOG_POST_IMPRTNT']['VALUE'] != 1) {
        ShowError('Пост не является важным');
    } else {
        function getDepRow($id)
        {
            $arReturn = [];
            if (!empty($id)) {
                $arDep = \Bitrix\Iblock\SectionTable::getRow([
                    'filter'  => ['ID' => $id],
                    'select'  => ['ID', 'IBLOCK_SECTION_ID', 'XML_ID']
                ]);
                if ($arDep) {
                    $arReturn = [
                        $arDep['ID']
                    ];
                    if ($arDep['IBLOCK_SECTION_ID']) {
                        $arReturn = array_merge($arReturn, getDepRow($arDep['IBLOCK_SECTION_ID']));
                    }
                }
            }
            return $arReturn;
        }

        $socnetRights = CBlogPost::GetSocNetPermsCode($arPost["ID"]);
        $arUsers = [];
        if (in_array('G2', $socnetRights) || in_array('UA', $socnetRights)) {
            $socnetRights[] = 'UA';
            $orm = UserTable::getList([
                'select'    => ['ID', 'LID', 'LAST_NAME'],
                'filter'    => [
                    'ACTIVE'            => 'Y',
                    '!UF_DEPARTMENT'    => false,
                    // '!UF_SID'           => false,
                    // '!XML_ID'           => false,
                ]
            ]);
            while ($arUser = $orm->fetch()) {
                if (empty($arUser['LAST_NAME'])) {
                    continue;
                }
                if (in_array($arUser['LID'], ['gi', 'nh'])) {
                    continue;
                }
                $arUsers[] = $arUser['ID'];
            }
        } else {
            $arUsers = CSocNetLogDestination::GetDestinationUsers($socnetRights);
        }

        global $USER_FIELD_MANAGER;
        $arRead = [];
        $res = CBlogUserOptions::GetList(
            [
                'ID' => 'DESC'
            ],
            [
                'POST_ID'   => $arPost['ID'],
                'NAME'      => 'BLOG_POST_IMPRTNT',
                'VALUE'     => 'Y',
            ],
            [
                'SELECT' => [
                    'ID',
                    'USER_ID',
                ]
            ]
        );
        while ($row = $res->Fetch()) {
            $aUserField = $USER_FIELD_MANAGER->GetUserFields(
                'BLOG_POST_PARAM',
                $row['ID']
            );
            if (in_array($row['USER_ID'], $arUsers)) {
                $arRead[ $row['USER_ID'] ] = [
                    'USER_ID' => $row['USER_ID'],
                    'DATE' => $aUserField['UF_DATE']['VALUE']
                ];
            }
        }

        $arFilterSect = [
            'IBLOCK_ID' => COption::GetOptionInt('intranet', 'iblock_structure', 0),
            'ACTIVE'    => 'Y',
        ];
        if (isset($_REQUEST['UF_DEPARTMENT']) && (int)$_REQUEST['UF_DEPARTMENT'] > 0) {
            $arReturn = CIntranetUtils::GetDeparmentsTree($_REQUEST['UF_DEPARTMENT'], true);
            $arReturn[] = $_REQUEST['UF_DEPARTMENT'];
            $arFilterSect['ID'] = $arReturn;
        }
        $res = CIBlockSection::GetList(
            ['LEFT_MARGIN' => 'ASC', 'NAME' => 'ASC'],
            $arFilterSect,
            false,
            ['ID', 'NAME', 'IBLOCK_SECTION_ID', 'DEPTH_LEVEL', 'XML_ID']
        );
        $arResult = [
            'COUNT' => [],
            'READ'  => [],
            'DEPS'  => [],
        ];
        $minLevel = 999;
        while ($row = $res->Fetch()) {
            if ($row['DEPTH_LEVEL'] > 1 && empty($row['XML_ID'])) {
                continue;
            }
            if (false !== mb_strpos($row['XML_ID'], 'control_poruch')) {
                continue;
            }
            if (false !== mb_strpos($row['XML_ID'], 'MFC_')) {
                continue;
            }
            if ($row['XML_ID'] === $row['ID']) {
                continue;
            }
            $arFilter = [
                'ACTIVE'        => 'Y',
                'UF_DEPARTMENT' => $row['ID'],
            ];
            $resUser = CUser::GetList(
                $by = 'LAST_NAME',
                $order = 'asc',
                $arFilter
            );
            $arCurUsers = [];
            $arReaded = [];
            while ($rowUser = $resUser->Fetch()) {
                if (empty($rowUser['LAST_NAME'])) {
                    continue;
                }
                if (!in_array($rowUser['ID'], $arUsers)) {
                    continue;
                }
                if (in_array($rowUser['LID'], ['gi', 'nh'])) {
                    continue;
                }
                $arCurUsers[ $rowUser['ID'] ] = [
                    'NAME'      => $rowUser['LAST_NAME'] . ' ' . $rowUser['NAME'],
                    'GENDER'    => $rowUser['PERSONAL_GENDER'],
                ];
                if (array_key_exists($rowUser['ID'], $arRead)) {
                    $arReaded[ $rowUser['ID'] ] = $rowUser['ID'];
                }
            }

            $arResult['DEPS'][ $row['ID'] ] = [
                'ID'            => $row['ID'],
                'NAME'          => $row['NAME'],
                'DEPTH_LEVEL'   => $row['DEPTH_LEVEL'],
                'PARENT'        => (int)$row['IBLOCK_SECTION_ID'],
                'USERS'         => $arCurUsers,
                'READ'          => $arReaded,
            ];
            $minLevel = ($row['DEPTH_LEVEL'] < $minLevel ? $row['DEPTH_LEVEL'] : $minLevel);
            if (!isset($arResult['COUNT'][ $row['ID'] ])) {
                $arResult['COUNT'][ $row['ID'] ] = [];
            }
            if (!isset($arResult['READ'][ $row['ID'] ])) {
                $arResult['READ'][ $row['ID'] ] = [];
            }

            $arRows = getDepRow($row['ID']);
            foreach ($arRows as $depId) {
                $arResult['COUNT'][ $depId ] = array_merge($arResult['COUNT'][ $depId ], array_keys($arCurUsers));
                $arResult['READ'][ $depId ] = array_merge($arResult['READ'][ $depId ], array_keys($arReaded));
                $arResult['COUNT'][ $depId ] = array_unique($arResult['COUNT'][ $depId ]);
                $arResult['READ'][ $depId ] = array_unique($arResult['READ'][ $depId ]);
            }
        }
        if (in_array($minLevel, [1, 999])) {
            $minLevel = 0;
        }
        ?>
        <h1><a href="<?=str_replace('#post_id#', $arPost['ID'], $arPost['PATH'])?>" target="_blank"><?=$arPost['TITLE']?></a></h1>
        <b>Опубликован: <?=$arPost['DATE_PUBLISH']?></b><br/>
        <form method="get" class="m-3">
            <input type="hidden" name="ID" value="<?=$arParams['ID']?>" />
            <div class="row">
                <div class="col-5 px-0">
                    <?
                    $arUserFields = $GLOBALS['USER_FIELD_MANAGER']->GetUserFields("USER", $GLOBALS['USER']->GetID(), LANGUAGE_ID);
                    $arUserFields['UF_DEPARTMENT']['VALUE'] = $_REQUEST['UF_DEPARTMENT'];
                    $arUserFields['UF_DEPARTMENT']['MULTIPLE'] = 'N';
                    $arUserFields['UF_DEPARTMENT']['SETTINGS']['LIST_HEIGHT'] = 1;
                    unset($arUserFields['UF_DEPARTMENT']['USER_TYPE']['USE_FIELD_COMPONENT']);
                    $GLOBALS["APPLICATION"]->IncludeComponent(
                        "bitrix:system.field.edit",
                        'iblock_section',
                        array(
                            "arUserField"       => $arUserFields['UF_DEPARTMENT'],
                            'ADDITIONAL_CLASS'  => 'select2',
                            'SKIP_EMPTY_XML_ID' => 'Y',
                        ),
                        null,
                        array("HIDE_ICONS"=>"Y")
                    );
                    ?>
                </div>
                <div class="col-7">
                    <input
                        class="ui-btn ui-btn-primary"
                        name="filter"
                        value="Фильтр"
                        type="submit" />
                </div>
            </div>
        </form>
        <?
        $fileName = 'Прочитали пост';
        if (!empty($_REQUEST['UF_DEPARTMENT']) && isset($arResult['DEPS'][ $_REQUEST['UF_DEPARTMENT'] ])) {
            $fileName = $arResult['DEPS'][ $_REQUEST['UF_DEPARTMENT'] ]['NAME'];
        }
        ?>
        <button onclick="tableToExcel('blogread', '<?=$fileName?>')" class="btn btn-success">Выгрузить</button>
        <br/><br/>

        <table class="table table-bordered table-sm" id="blogread">
            <tr class="d-none">
                <th colspan="3"><a href="https://corp.tularegion.local<?=str_replace('#post_id#', $arPost['ID'], $arPost['PATH'])?>" target="_blank"><?=$arPost['TITLE']?></a></th>
            </tr>
            <?
            foreach ($arResult['DEPS'] as $depId => $row) {
                // if (count($arResult['COUNT'][ $depId ]) <= 0) {
                //     continue;
                // }
                $classes = '';
                if ($row['PARENT'] > 0 && $row['ID'] != $_REQUEST['UF_DEPARTMENT']) {
                    $classes = 'd-none';
                }
                ?>
                <tr
                    class="<?=$classes?> dep-<?=$row['PARENT']?>"
                    data-id="<?=$depId;?>"
                    data-parent="<?=$row['PARENT'];?>"
                    >
                    <th
                        data-id="<?=$row['ID']?>"
                        data-parent="<?=$row['PARENT']?>"
                        class="level-<?=$row['DEPTH_LEVEL']-$minLevel?>"
                        >
                        <span class="toggle-button plus-button"></span>
                        <?=$row['NAME'];?>
                    </th>
                    <th colspan="2">
                        Ознакомлены: <?=count($arResult['READ'][ $depId ]);?> из <?=count($arResult['COUNT'][ $depId ]);?>
                    </th>
                </tr>
                <?
                foreach ($row['USERS'] as $userId => $arUser) {
                    ?>
                    <tr
                        class="d-none user-<?=$userId?> dep-<?=$depId?>"
                        >
                        <td
                            width="50%"
                            class="level-<?=($row['DEPTH_LEVEL']+1-$minLevel)?>"
                            >
                            <?=$arUser['NAME']?>
                        </td>
                        <td width="25%"><?
                            if (array_key_exists($userId, $arRead)) {
                                echo '<font color="green"><b>Ознакомлен(а)</b></font>';
                            }
                        ?></td>
                        <td width="25%"><?
                            if (array_key_exists($userId, $arRead)) {
                                echo $arRead[ $userId ]['DATE'];
                            }
                        ?></td>
                    </tr>
                    <?
                }
            }
            ?>
        </table>
        <?
    }
}
