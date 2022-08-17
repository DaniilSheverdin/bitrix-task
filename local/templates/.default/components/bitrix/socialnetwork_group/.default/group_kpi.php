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

$pageId = "group_kpi";
include("util_group_menu.php");
include("util_group_profile.php");

$APPLICATION->SetTitle('KPI');

if ($arResult['VARIABLES']['group_id'] > 0) {
	$arFeaturesTmp = [];
	$dbResultTmp = \CSocNetFeatures::getList(
	    array(),
	    array("ENTITY_ID" => $arResult['VARIABLES']['group_id'], "ENTITY_TYPE" => SONET_ENTITY_GROUP)
	);
	while ($arResultTmp = $dbResultTmp->GetNext()) {
	    $arFeaturesTmp[ $arResultTmp["FEATURE"] ] = $arResultTmp;
	}

	if ($arFeaturesTmp['group_kpi']['ACTIVE'] == 'Y') {
		$APPLICATION->SetTitle($arFeaturesTmp['group_kpi']['FEATURE_NAME'] ?? 'KPI');
	} else {
		LocalRedirect(str_replace('#group_id#', $arResult['VARIABLES']['group_id'], $arResult['PATH_TO_GROUP']));
		exit;
	}
}

$APPLICATION->IncludeComponent(
    "citto:customreports",
    'project.kpi',
    [
    	'GROUP_ID' => $arResult['VARIABLES']['group_id'] ?? 0,
    ],
    false
);
