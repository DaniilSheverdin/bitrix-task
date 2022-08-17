<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

echo '<div id="lnpa">';
include "{$arResult['TEMPLATE_PATH']}/template-nav.php";
include "{$arResult['TEMPLATE_PATH']}/template-{$arResult['PAGE']}.php";
echo '</div>';
?>
<script>
    var iBlockID = <?=$arResult['IBLOCK']?>
</script>
