<?php

use CTaskTags;
use CIntranetUtils;
use Bitrix\Main\Loader;
use Citto\Tasks\DevSprints;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) {
    die();
}

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

$APPLICATION->SetTitle('Отчет о загруженности отдела разработки в спринте');

Loader::includeModule('tasks');
Loader::includeModule('intranet');

$obSprint = new DevSprints();
$arSprints = $obSprint->getList();

$arTags = [];
$arSkip = [
    'E1E1E1',
];
foreach ($arSprints as $arSprint) {
    $arSkip = array_merge($arSkip, $arSprint['UF_TAGS']);
}
$arSkip = array_filter($arSkip);

$arUsers = [];
$res = CIntranetUtils::getDepartmentColleagues($USER->getId());
while ($row = $res->Fetch()) {
    $dbRes = CTaskTags::getTagsNamesByUserId($row['ID']);
    while ($tag = $dbRes->GetNext()) {
        if (in_array($tag['~NAME'], $arSkip)) {
            continue;
        }
        $arUsers[ $row['ID'] ] = $row['ID'];
        $arTags[ $tag['~NAME'] ] = $tag['~NAME'];
    }
}
sort($arTags);
?>
<b>Активные спринты</b>
<ul>
    <?
    foreach ($arSprints as $arSprint) {
        if ($arSprint['UF_CLOSE']) {
            continue;
        }
        ?>
        <li>
            <a href="<?=$APPLICATION->GetCurPageParam('sprintId=' . $arSprint['ID'], ['sprints', 'sprint', 'sprintId'])?>"><?=$arSprint['NAME']?></a>
            <?if ($GLOBALS['USER']->IsAdmin()) : ?><small>[<a href="/bitrix/admin/highloadblock_row_edit.php?ENTITY_ID=<?=$obSprint->hlId?>&ID=<?=$arSprint['ID']?>&lang=ru" target="_blank">Изменить</a>]</small><?endif;?>
        </li>
        <?
    }
    ?>
    <?if ($GLOBALS['USER']->IsAdmin()) : ?>
    <li>
        <a href="/bitrix/admin/highloadblock_row_edit.php?ENTITY_ID=<?=$obSprint->hlId?>&lang=ru" target="_blank">Добавить новый</a>
    </li>
    <?endif;?>
</ul>
<b>Завершенные спринты</b>
<ul>
    <?
    foreach ($arSprints as $arSprint) {
        if (!$arSprint['UF_CLOSE']) {
            continue;
        }
        ?>
        <li>
            <a href="<?=$APPLICATION->GetCurPageParam('sprintId=' . $arSprint['ID'], ['sprints', 'sprint', 'sprintId'])?>"><?=$arSprint['NAME']?></a>
        </li>
        <?
    }
    ?>
</ul>
<?if (!empty($arTags)) : ?>
<b>Остальные теги</b>
<ul>
    <?
    foreach ($arTags as $tag) {
        $res = CTasks::GetList(
            [],
            [
                'TAG' => $tag,
            ]
        );
        if ($res->SelectedRowsCount() <= 0) {
            continue;
        }
        ?>
        <li>
            <a href="<?=$APPLICATION->GetCurPageParam('sprint=' . base64_encode($tag), ['sprints', 'sprint'])?>"><?=$tag?></a>
        </li>
        <?
    }
    ?>
</ul>
<?endif;?>
