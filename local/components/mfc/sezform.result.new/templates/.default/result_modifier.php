<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/**
 * @global $USER
 * @var $arResult
 */
if(isset($arResult['QUESTIONS']['FIO'])) {
    $strUserName = $USER->GetLastName().' '.$USER->GetFirstName().' '.$USER->GetSecondName();
    $arResult['QUESTIONS']['FIO']['VALUE'] = $strUserName;
    $arResult['QUESTIONS']['FIO']['HTML_CODE'] = str_replace('value=""', 'value="'.$strUserName.'"', $arResult['QUESTIONS']['FIO']['HTML_CODE']);
}
?>