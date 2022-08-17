<?php
use Bitrix\Main\Localization\Loc;
$APPLICATION->SetTitle(Loc::getMessage('TITLE_TRUST'));
?>

<div class="row">
  <div class="col-12">
    <?include 'settings_menu.php'?>
  </div>
  <div class="col-12">
    <div class="kpi_rules">
      <form id="notify-form">
        <? foreach ($arResult['ACCESS_DEPARTMENT']['ACCESS_DEFAULT'] as $departmentID => $department):  ?>
        <? if ($department['NAME']): ?>
            <?
              $strSelectedUserAccess = $arResult['ACCESS_DEPARTMENT']['ACCESS_DEFAULT'][$departmentID]['ACCESS'];
              $strSelectedUserAssistant = $arResult['ACCESS_DEPARTMENT']['ACCESS_DEFAULT'][$departmentID]['ASSISTANTS'];
              if ($arResult['ACCESS_DEPARTMENT']['ACCESS'][$departmentID]['ACCESS']) {
                  $strSelectedUserAccess = $arResult['ACCESS_DEPARTMENT']['ACCESS'][$departmentID]['ACCESS'];
              }

            ?>
          <div class="row mt-5 ">
            <div class="col-md-8 mt-3 small"><h2><?=$department['NAME']?></h2></div>
            <div class="col-md-4 my-3">
              <div class="d-flex align-items-center">
                <div class="select-access">
                  <select data-department-id="<?=$departmentID?>" placeholder="..." name="UF_KPI_ACCESS_TO_DEPARTMENT">
                    <option></option>
                      <? foreach ($arResult['ACCESS_DEPARTMENT']['ALL_USERS'] as $id => $value): ?>
                        <option <?=$strSelectedUserAccess == $id ? 'selected' : ''?> value="<?=$id?>"><?=$value['FULL_NAME']?></option>
                      <?endforeach;?>
                  </select>
                </div>
              </div>
                  <div class="d-flex align-items-center">
                    <div class="select-assistants">
                      <select data-department-id="<?=$departmentID?>" placeholder="..." name="UF_KPI_ASSISTANT_TO_DEPARTMENT">
                        <option></option>
                          <? foreach ($arResult['ACCESS_DEPARTMENT']['ALL_USERS'] as $id => $value): ?>
                            <option <?=$strSelectedUserAssistant == $id ? 'selected' : ''?> value="<?=$id?>"><?=$value['FULL_NAME']?></option>
                          <?endforeach;?>
                      </select>
                    </div>
                    <div class="ml-2">Помощник</div>
                  </div>
            </div>
          </div>
        <?endif;?>
        <?endforeach;?>
      </form>
    </div>
  </div>
  <div id="access" class="actions"></div>
  <div class="actions-messages">
    <div>Изменения сохранены</div>
  </div>
</div>
