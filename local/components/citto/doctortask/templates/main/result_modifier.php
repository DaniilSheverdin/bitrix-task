<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<?
/**
 * @var $arResult
 */
header('Content-type: application/json');
echo json_encode($arResult['JSON']);
exit;
?>
