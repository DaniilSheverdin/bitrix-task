<?php
use Bitrix\Main\Localization\Loc;
$uri = new \Bitrix\Main\Web\Uri($APPLICATION->GetCurPAgeParam());
$params = [];
parse_str($uri->getQuery(), $params);
?>

<div class="settings-menu">
    <div class="settings-menu__item <?=$params['page'] == 'access' ? 'active' : ''?>">
      <a href="<?=SITE_DIR?>kpi/access"><p><?=Loc::getMessage('TITLE_TRUST')?></p></a>
    </div>
    <div class="settings-menu__item <?=$params['page'] == 'notify' ? 'active' : ''?>">
        <a href="<?=SITE_DIR?>kpi/notify"><p><?=Loc::getMessage('TITLE_SET_NOTIFIES')?></p></a>
    </div>

    <div class="settings-menu__item <?=$params['page'] == 'set_kp' ? 'active' : ''?>">
      <a href="<?=SITE_DIR?>kpi/set_kp"><p><?=Loc::getMessage('TITLE_SET_KP')?></p></a>
    </div>

</div>



<div class="settings-menu__item">
</div>
