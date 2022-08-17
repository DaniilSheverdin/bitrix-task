<?php
use Bitrix\Main\Localization\Loc;
$APPLICATION->SetTitle(Loc::getMessage('TITLE_SET_KP'));
?>

<div class="row">
  <div class="col-12">
      <?include 'settings_menu.php'?>
  </div>
  <div class="col-12">
    <div class="kpi_rules">
      <div class="row align-items-start">
        <div class="col-md-12 small mt-5 pb-3"><h2><?=Loc::getMessage('SELECT_PRIORITY_PROJECTS')?></h2></div>
      </div>
      <div class="js-parent-pp">
        <? foreach ($arResult['ALL_PRIORITY_PROJECTS'] as $projects): ?>
          <div class="deleted-label projects mb-3">
            <div class="name"><?=$projects['NAME']?></div>
            <div data-id="<?=$projects['ID']?>" class="delete js-delete-pp"><img src="<?=$templatePath?>/icons/times-solid.svg" alt=""></div>
          </div>
        <?endforeach;?>
        <? if (!$arResult['ALL_PRIORITY_PROJECTS']): ?>
          <div class="deleted-label projects mb-3 d-none">
            <div class="name"></div>
            <div data-id="" class="delete js-delete-pp"><img src="<?=$templatePath?>/icons/times-solid.svg" alt=""></div>
          </div>
        <?endif;?>
      </div>
      <div class="row">
        <div class="col-md-6">
          <div class="select-department">
            <select id="select-projects" placeholder="..." name="project">
              <option></option>
                <? foreach ($arResult['ALL_PROJECTS'] as $key => $value): ?>
                  <option value="<?=$key?>"><?=$value?></option>
                <?endforeach;?>
            </select>
          </div>

        </div>
        <div class="col-md-3">
          <button class="js-add-project ui-btn btn-select ui-btn-primary-dark ui-btn-disabled">Добавить</button>
        </div>
        <div class="col-md-3">
          <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-w50 ml-auto flex-row align-items-center">
            <label class="mr-3" for="priority-projects-weight">Вес</label> <input id="priority-projects-weight" value="<?=$arResult['KP_SECTIONS_WEIGHT']['PP']?>" type="number" step="0.01" class="ui-ctl-element">
          </div>
        </div>
      </div>




      <div class="row align-items-start mt-5">
        <div class="col-md-12 small mt-5 pb-3"><h2><?=Loc::getMessage('SELECT_TOP_MANAGERS')?></h2></div>
      </div>
      <div class="js-parent-us">
          <? foreach ($arResult['ALL_TOP_MANAGERS'] as $projects): ?>
            <div class="deleted-label users mb-3">
              <div class="name"><?=$projects['NAME']?></div>
              <div data-id="<?=$projects['ID']?>" class="delete js-delete-pp"><img src="<?=$templatePath?>/icons/times-solid.svg" alt=""></div>
            </div>
          <?endforeach;?>
          <? if (!$arResult['ALL_TOP_MANAGERS']): ?>
            <div class="deleted-label users mb-3 d-none">
              <div class="name"></div>
              <div data-id="" class="delete js-delete-pp"><img src="<?=$templatePath?>/icons/times-solid.svg" alt=""></div>
            </div>
          <?endif;?>
      </div>
      <div class="row">
        <div class="col-md-6">
          <div class="select-department">
            <select id="select-all-users" placeholder="..." name="user">
              <option></option>
                <? foreach ($arResult['ALL_USERS'] as $key => $value): ?>
                  <option value="<?=$key?>"><?=$value['FULL_NAME']?></option>
                <?endforeach;?>
            </select>
          </div>

        </div>
        <div class="col-md-3">
          <button class="js-add-top-manager ui-btn btn-select ui-btn-primary-dark ui-btn-disabled">Добавить</button>
        </div>
        <div class="col-md-3">
          <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-w50 ml-auto flex-row align-items-center">
            <label class="mr-3" for="top-managers-weight">Вес</label> <input id="top-managers-weight" value="<?=$arResult['KP_SECTIONS_WEIGHT']['TM']?>"  type="number" step="0.01" class="ui-ctl-element">
          </div>
        </div>
      </div>


      <div class="row align-items-start mt-5">
        <div class="col-md-9 small  pb-3"><h2><?=Loc::getMessage('SELECT_BASE_TASKS')?></h2></div>
        <div class="col-md-3">
          <div class="ui-ctl ui-ctl-textbox ui-ctl-sm ui-ctl-w50 ml-auto flex-row align-items-center">
            <label class="mr-3" for="base-tasks-weight">Вес</label> <input id="base-tasks-weight" value="<?=$arResult['KP_SECTIONS_WEIGHT']['BT']?>"  type="number" step="0.01" class="ui-ctl-element">
          </div>
        </div>
      </div>


    </div>
  </div>
  <div id="set-kp" class="actions"></div>
  <div class="actions-messages">
    <div>Изменения сохранены</div>
  </div>
</div>
