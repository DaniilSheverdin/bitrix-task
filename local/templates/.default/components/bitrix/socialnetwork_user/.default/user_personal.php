<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;

$pageId = 'user_personal';
include('util_menu.php');
include('util_profile.php');

Loc::loadLanguageFile($_SERVER['DOCUMENT_ROOT'].$this->getFolder().'/result_modifier.php');

$APPLICATION->IncludeComponent(
    'bitrix:ui.sidepanel.wrapper',
    '',
    array(
        'POPUP_COMPONENT_NAME' => 'citto:profile.personal',
        'POPUP_COMPONENT_TEMPLATE_NAME' => '',
        'POPUP_COMPONENT_PARAMS' => array(
            'USER_ID' => $arResult['VARIABLES']['user_id'],
            'TEMPLATE' => 'personal'
        ),
        'POPUP_COMPONENT_PARENT' => $component
    )
);