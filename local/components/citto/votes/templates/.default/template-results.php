<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<?

use Bitrix\Main\Page\Asset;

$APPLICATION->SetTitle("Голосование");
Asset::getInstance()->addCss('/bitrix/templates/.default/bootstrap.min.css');
?>

<? if ($arResult['SHOW_RESULT']): ?>
    <? foreach ($arResult['VOTES'] as $iVoteID => $arItem): ?>

        <span><strong><?= $arItem['NAME'] ?></strong></span>
        <ul>
            <? foreach ($arItem['VARIABLES'] as $iVarID => $sVariable): ?>
                <li><?= $sVariable ?>
                    - <?= ($arResult['RESULTS']['ITEMS'][$iVoteID][$iVarID]) ? (100 * $arResult['RESULTS']['ITEMS'][$iVoteID][$iVarID]) / $arResult['RESULTS']['COUNT'] : 0 ?></li>
            <? endforeach; ?>
        </ul>
    <? endforeach; ?>
<? endif; ?>
