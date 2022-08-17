<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<?

use Bitrix\Main\Page\Asset;

$APPLICATION->SetTitle("Голосование");
Asset::getInstance()->addCss('/bitrix/templates/.default/bootstrap.min.css');
?>
<? if ($arResult['STATUS'] == 'SUCCESS'): ?>
    <div class="alert alert-success" role="alert">
        Спасибо за участие в голосовании!
    </div>
<? elseif ($arResult['IS_VOTED']): ?>
    <div class="alert alert-danger" role="alert">
        Вы уже участвовали в голосовании!
    </div>
<? else: ?>
    <form action="<? echo $APPLICATION->GetCurPageParam() ?>" method="POST" class="mb-3">
        <?= bitrix_sessid_post() ?>
        <? foreach ($arResult['VOTES'] as $iVoteID => $arItem): ?>
            <div class="mb-4">
                <span><strong><?= $arItem['NAME'] ?><span class="text-danger">*</span></strong></span>

                <? if ($arItem['REQUIRED']): ?>
                    <span class="text-danger">- <?= $arItem['REQUIRED'] ?></span>
                <? endif; ?>

                <? foreach ($arItem['VARIABLES'] as $iVarID => $sVariable): ?>
                    <div class="form-check">
                        <input
                                class="form-check-input"
                                name="<?= $iVoteID ?>[]"
                                type="<?= ($arItem['MULTIPLE'] == 'Y') ? 'checkbox' : 'radio' ?>"
                                id="<?= $iVoteID ?>-<?= $iVarID ?>"
                                value="<?= $iVarID ?>"
                            <?= (in_array($iVarID, $arItem['CHECKED'])) ? 'checked' : '' ?>
                        >
                        <label class="form-check-label" for="<?= $iVoteID ?>-<?= $iVarID ?>">
                            <?= $sVariable ?>
                        </label>
                    </div>
                <? endforeach; ?>
            </div>
        <? endforeach; ?>
        <button name="submit" value="y" type="submit" class="btn btn-primary">голосовать</button>
    </form>
<? endif; ?>

<? if ($arResult['SHOW_RESULT']): ?>
    <a href="?page=results" class="btn btn-primary">результаты</a>
<? endif; ?>

