<?php
use Bitrix\Main\Localization\Loc;
$APPLICATION->SetTitle(Loc::getMessage('SERVICE_NAME'));
global $USER;
?>
<div class="row">
  <div class="col-9 offset-1">


    <? if ($arResult['ACCESS_TO_NEW']): ?>


      <form action="" class="form_vaccination">

        <h3>Заполните поля для записи на вакцинацию</h3>

        <div class="input_group">
          <label for="fio">ФИО</label>
          <div class="ui-ctl ui-ctl-textbox">
            <input value="<?=$data['NAME'] ?? $USER->GetLastName().' '.$USER->GetFirstName().' '.$USER->GetSecondName()?>" name="fio" id="fio" type="text" class="ui-ctl-element required">
          </div>
        </div>

        <div class="input_group">
          <label for="birthday_date">Дата рождения</label>
          <div class="ui-ctl ui-ctl-after-icon ui-ctl-date">
            <div class="ui-ctl-after ui-ctl-icon-calendar"></div>
            <div class="ui-ctl-element"><input  id="birthday_date" class="required-change" type="text" value="" name="birthday_date" onclick="BX.calendar({node: this, field: this, bTime: false}); changeCalendar(this)"></div>
          </div>
        </div>

        <div class="input_group">
          <label for="snils">Номер СНИЛС</label>
          <div class="ui-ctl ui-ctl-textbox">
            <input value="" name="snils" id="snils" type="text" class="ui-ctl-element required">
          </div>
        </div>

        <div class="input_group">
          <label for="phone">Номер телефона</label>
          <div class="ui-ctl ui-ctl-textbox">
            <input value="" name="phone" id="phone" type="text" class="ui-ctl-element required">
          </div>
        </div>

        <h4>Паспорт</h4>

        <div class="input_group">
          <label for="passport_sn">Серия, номер</label>
          <div class="ui-ctl ui-ctl-textbox">
            <input value="" name="passport_sn" id="passport_sn" type="text" class="ui-ctl-element required">
          </div>
        </div>

        <div class="input_group">
          <label for="passport_issued_by">Кем выдан</label>
          <div class="ui-ctl ui-ctl-textbox">
            <input value="" name="passport_issued_by" id="passport_issued_by" type="text" class="ui-ctl-element required">
          </div>
        </div>

        <div class="input_group">
          <label for="passport_issued_date">Дата выдачи</label>
          <div class="ui-ctl ui-ctl-after-icon ui-ctl-date">
            <div class="ui-ctl-after ui-ctl-icon-calendar"></div>
            <div class="ui-ctl-element"><input id="passport_issued_date" class="required-change" type="text" value="" name="passport_issued_date" onclick="BX.calendar({node: this, field: this, bTime: false}); changeCalendar(this)"></div>
          </div>
        </div>

        <div class="input_group">
          <label for="passport_code">Код подразделения</label>
          <div class="ui-ctl ui-ctl-textbox">
            <input value="" name="passport_code" id="passport_code" type="text" class="ui-ctl-element required">
          </div>
        </div>

        <div class="input_group">
          <label for="passport_address">Адрес регистрации (по прописке)</label>
          <div class="ui-ctl ui-ctl-textbox">
            <input value="" name="passport_address" id="passport_address" type="text" class="ui-ctl-element required">
          </div>
        </div>

        <h4>Полис обязательного медицинского страхования</h4>

        <div class="input_group">
          <label for="oms_number">Номер</label>
          <div class="ui-ctl ui-ctl-textbox">
            <input value="" name="oms_number" id="oms_number" type="text" class="ui-ctl-element required">
          </div>
        </div>

        <div class="input_group">
          <label for="oms_service">Страховая служба</label>
          <div class="ui-ctl ui-ctl-textbox">
            <input value="" name="oms_service" id="oms_service" type="text" class="ui-ctl-element required">
          </div>
        </div>




        <h4 class="bold">Выберите дату и время вакцинации</h4>



        <div class="input_group">
          <label for="vac_date">Дата</label>
          <div class="ui-ctl ui-ctl-after-icon ui-ctl-date ">
            <div class="ui-ctl-after ui-ctl-icon-calendar"></div>
            <div class="ui-ctl-element "><input id="vac_date" class="required-change" type="text" value="" name="vac_date" onclick="BX.calendar({node: this, field: this, bTime: false}); changeCalendar(this)"></div>
          </div>
        </div>

        <div class="input_group">
          <label for="time">Время</label>
          <div class="ui-ctl ui-ctl-after-icon ui-ctl-time">
            <div class="ui-ctl-after ui-ctl-icon-calendar"></div>
            <select class="ui-ctl-element required-change" name="time" id="time">
              <option value="">...</option>
            </select>
          </div>
        </div>



          <div class="input_group">
            <label class="ui-ctl ui-ctl-checkbox ">
              <input type="checkbox" class="ui-ctl-element required-change" name="agree">
              <div class="ui-ctl-label-text">Даю свое согласие на обработку персональных данных</div>
            </label>
          </div>

        <div class="mt-3 d-flex">
          <button type="submit" class="ui-btn vaccination_send">Записаться</button>

          <? if ($arResult['ACCESS']): ?>
            <div class="ml-4">
              <a href="<?=SITE_DIR?>vaccination/data" class="ui-btn ui-btn-success">Отчет о записях</a>
            </div>
          <?endif;?>
        </div>



      </form>


    <? else: ?>
      <? LocalRedirect(SITE_DIR . 'vaccination'); ?>
    <?endif;?>



  </div>
</div>
