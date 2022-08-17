<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

$pageId = "group_risks";
include("util_group_menu.php");
include("util_group_profile.php");

$groupId = (int)$arResult['VARIABLES']['group_id'];

$APPLICATION->SetTitle('Риски');

$APPLICATION->IncludeComponent(
    "citto:customreports",
    'project.risks',
    [
        'GROUP_ID' => $groupId
    ],
    false
);
