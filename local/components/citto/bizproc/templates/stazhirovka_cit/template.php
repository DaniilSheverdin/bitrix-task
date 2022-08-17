<?php
/**
 * @var $arResult
 * @var $arParams
 * @global $APPLICATION
 */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}
?>
<div class="w-100 position-relative" id="ja-bp-container" v-cloak>

</div>
<script>
    const BPInput = {
        action: '<?=$APPLICATION->GetCurPage()?>',
        id:     'js-stazhirovka_cit',
        sessid: '<?=bitrix_sessid()?>',
        backlink: '/bizproc/processes/<?=$arParams['ID_BIZPROC']?>/view/0/?list_section_id=',
        jquery: jQuery,
        fields: <?=$arResult['FIELDS_LIST']?>,
        users: <?=$arResult['USERS']?>
    }
</script>
