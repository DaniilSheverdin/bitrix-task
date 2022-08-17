<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}

global $USER;
use Bitrix\Main\Localization\Loc;
$templatePath = $this->__folder;
Bitrix\Main\Page\Asset::getInstance()->addCss($templatePath.'/selectize.css');
Bitrix\Main\Page\Asset::getInstance()->addJs($templatePath.'/selectize.min.js');

$APPLICATION->SetPageProperty('BodyClass', ($bodyClass ? $bodyClass.' ' : '').'no-all-paddings no-background');
\Bitrix\Main\UI\Extension::load(array("ui.buttons", "ui.alerts", "ui.tooltip", "ui.hint"));
\CJSCore::Init("loader");

$this->SetViewTarget('inside_pagetitle');

$arPopupOptions = [
    'width' => 950,
    'allowChangeHistory' => false,
    'cacheable' => false,
    'requestMethod' => 'post'
];

$this->EndViewTarget();


?>
<div class="kpi_user_wrapper">

  <div class="select-container">
  <? if (!isset($_REQUEST['date'])):?>
  <div class="empty-select">Выберите период</div>
  <?endif;?>
  <div class="select-department">
      <select id="select-date" placeholder="..." name="date">
        <option></option>
          <? foreach ($arResult['PERIOD'] as $key => $value): ?>
              <option <?=$_REQUEST['date'] == $key ? 'selected' : ''?> value="<?=$key?>"><?=$value?></option>
          <?endforeach;?>
      </select>
  </div>
  </div>
    <? if (isset($_REQUEST['date'])):?>
    <div class="bg-white user">
      <div class="fio"><?=$arResult['USER_DATA']['FIO']?></div>
      <div class="department"><?=$arResult['USER_DATA']['DEPARTMENT']?>,</div>
      <div><?=$arResult['USER_DATA']['POSITION']?></div>
    </div>
    <div class="bg-white">
      <div>KPI = <?=$arResult['USER_DATA']['FORMULA']?></div>
      <div class="table">
        <div class="table-head">
          <div class="name">Наименование показателя</div>
          <div class="weight">Вес</div>
          <div class="target">Плановое значение</div>
          <div class="value">Фактическое значение</div>
        </div>
        <div class="table-body">
          <? foreach ($arResult['USER_DATA']['KPIS'] as $kpi): ?>
          <div class="kpi-row">
            <div class="name"><?=$kpi['NAME']?></div>
            <div class="weight"><?=$kpi['WEIGHT']?></div>
            <div class="target"><?=$kpi['TARGET']?></div>
            <div class="value"><?=$kpi['VALUE']?></div>
          </div>
          <?endforeach;?>
        </div>
      </div>
    </div>
    <div class="bg-white-container">
      <div class="bg-white indicators">
        <div class="title">
          Дополнительные показатели
        </div>
        <div class="d-kpi">
          <div class="subtitle">Критический KPI</div> <div><span class="<?=$arResult['USER_DATA']['CRITICAL'] ? 'red' : 'green'?>" ><?=$arResult['USER_DATA']['CRITICAL'] ?? 'Не активирован'?></span></div></div>
        <div class="d-kpi"><div class="subtitle">KPI развития </div> <div><span class="<?=$arResult['USER_DATA']['PROGRESS'] ? 'green' : ''?>" ><?=$arResult['USER_DATA']['PROGRESS'] ?? 'Не активирован'?></span></div></div>
        <div class="d-kpi"><div class="subtitle">Комментарий </div> <div><span><?=$arResult['USER_DATA']['COMMENT']?></span></div></div>
      </div>
      <div class="bg-white final">
        <div class="title">
          Итог
        </div>
        <div class="d-kpi">
          <div class="subtitle-sum">Ставка</div><div><?=$arResult['USER_DATA']['RATE']?></div>
        </div>
        <div class="d-kpi">
          <div class="subtitle-sum">% от оклада</div><div><?=$arResult['USER_DATA']['RESULT']?></div>
        </div>
        <div class="d-kpi">
          <div class="subtitle-sum">Сумма выплаты</div><div><?=$arResult['USER_DATA']['SUM']?></div>
        </div>
      </div>
    </div>
  <?endif;?>


</div>