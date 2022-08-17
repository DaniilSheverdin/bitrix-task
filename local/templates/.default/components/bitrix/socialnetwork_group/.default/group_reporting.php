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

$pageId = "group_reporting";
include("util_group_menu.php");
include("util_group_profile.php");


$groupId = (int)$arResult['VARIABLES']['group_id'];

if ($groupId != \Citto\Tasks\ProjectInitiative::$groupId) {
	LocalRedirect(str_replace('#group_id#', $groupId, $arResult['PATH_TO_GROUP']));
	exit;
}

$APPLICATION->SetTitle($groupId === 612 ? 'Отчёт по созданным инициативам' : 'Отчёт по проекту');

$APPLICATION->IncludeComponent(
    "citto:customreports",
    'project.report.main',
    [],
    false
);
