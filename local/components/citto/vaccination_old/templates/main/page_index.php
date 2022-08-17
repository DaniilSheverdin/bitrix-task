<?php
use Bitrix\Main\Localization\Loc;
$APPLICATION->SetTitle(Loc::getMessage('SERVICE_NAME'));
global $USER;
?>
<div class="row">
  <div class="col-9 offset-1">

      <? if (!$arResult['ISSET']['EDITABLE'] && !$arResult['ACCESS']): ?>

        <? $arLastWrite = end($arResult['ISSET']['DATA']) ?>

        <div class="mt-5">

          <p>Вы записались на вакцинацию!<br>
            Вам необходимо скачать документы для вакцинации. Заполните их и принесите с собой на прием:</p>

          <h4 class="bold mt-2">Документы для скачивания</h4>
          <div>
              <? foreach ($arLastWrite['FILES'] as $key => $filePath): ?>
                <b class="bold"><a href="<?=$filePath?>"><?=$key == 0 ? 'Анкета пациента.docx' : 'Согласие пациента на вакцинацию.docx' ?></a></b><br>
              <?endforeach;?>
          </div>

          <p class="mt-4">Необходимо прийти в медицинский кабинет по адресу: Тула, проспект Ленина, 2  к  <?=$arLastWrite['VAC_TIME'] . ' ' . $arLastWrite['VAC_DATE']?>   <br>
            Не забудьте взять с собой оформленные документы для вакцинации!"
          </p>

        </div>
      <?endif;?>

    <? if ($arResult['ACCESS'] && count($arResult['ISSET']['DATA'])): ?>

      <div id="accordion">

        <? foreach($arResult['ISSET']['DATA'] as $id => $data): ?>

          <div class="mt-5">

            <p>Вы записались на вакцинацию!<br>
              Вам необходимо скачать документы для вакцинации. Заполните их и принесите с собой на прием:</p>

            <h4 class="bold mt-2">Документы для скачивания</h4>
            <div>
              <? foreach ($data['FILES'] as $key => $filePath): ?>
                <b class="bold"><a href="<?=$filePath?>"><?=$key == 0 ? 'Анкета пациента.docx' : 'Согласие пациента на вакцинацию.docx' ?></a></b><br>
              <?endforeach;?>
            </div>

            <p class="mt-4">Необходимо прийти в медицинский кабинет по адресу: Тула, проспект Ленина, 2  к  <?=$arResult['ISSET']['DATA']['VAC_TIME'] . ' ' . $arResult['ISSET']['DATA']['VAC_DATE']?>   <br>
              Не забудьте взять с собой оформленные документы для вакцинации!"
            </p>

          </div>


        <div class="card">
          <div class="card-header" id="headingOne-<?=$id?>">
            <h5 class="mb-0">
              <button class="btn btn-link" data-toggle="collapse" data-target="#form_vaccination-<?=$id?>" aria-expanded="true" aria-controls="form_vaccination-<?=$id?>">
                <?=$data['NAME']?>
                <? if ($data['ID'] > 0): ?>
                <button data-id="<?=$data['ID']?>" type="button" class="vaccination_delete ui-btn ui-btn-danger ml-4">Отменить запись</button>
                <?endif;?>
              </button>
            </h5>
          </div>




            <form action="" id="form_vaccination-<?=$id?>" class="form_vaccination collapse" aria-labelledby="headingOne-<?=$id?>" data-parent="#accordion">
              <div class="card-body">

              <h3>Данные записи</h3>

              <div class="input_group">
                <label for="fio">ФИО</label>
                <div class="ui-ctl ui-ctl-textbox">
                  <input <?=$arResult['ISSET']['EDITABLE'] ? '' : 'disabled'?> value="<?=$data['NAME']?>" name="fio" id="fio" type="text" class="ui-ctl-element required">
                </div>
              </div>

              <div class="input_group">
                <label for="birthday_date">Дата рождения</label>
                <div class="ui-ctl ui-ctl-after-icon ui-ctl-date">
                  <div class="ui-ctl-after ui-ctl-icon-calendar"></div>
                  <div class="ui-ctl-element"><input <?=$arResult['ISSET']['EDITABLE'] ? '' : 'disabled'?> id="birthday_date" class="required-change" type="text" value="<?=$data['BIRTHDAY_DATE'] ?>" name="birthday_date" onclick="BX.calendar({node: this, field: this, bTime: false}); changeCalendar(this)"></div>
                </div>
              </div>

              <div class="input_group">
                <label for="snils">Номер СНИЛС</label>
                <div class="ui-ctl ui-ctl-textbox">
                  <input <?=$arResult['ISSET']['EDITABLE'] ? '' : 'disabled'?> value="<?=$data['SNILS']?>" name="snils" id="snils" type="text" class="ui-ctl-element required">
                </div>
              </div>

              <div class="input_group">
                <label for="phone">Номер телефона</label>
                <div class="ui-ctl ui-ctl-textbox">
                  <input <?=$arResult['ISSET']['EDITABLE'] ? '' : 'disabled'?> value="<?=$data['PHONE']?>" name="phone" id="phone" type="text" class="ui-ctl-element required">
                </div>
              </div>

              <h4>Паспорт</h4>

              <div class="input_group">
                <label for="passport_sn">Серия, номер</label>
                <div class="ui-ctl ui-ctl-textbox">
                  <input <?=$arResult['ISSET']['EDITABLE'] ? '' : 'disabled'?> value="<?=$data['PASSPORT_SN']?>" name="passport_sn" id="passport_sn" type="text" class="ui-ctl-element required">
                </div>
              </div>

              <div class="input_group">
                <label for="passport_issued_by">Кем выдан</label>
                <div class="ui-ctl ui-ctl-textbox">
                  <input <?=$arResult['ISSET']['EDITABLE'] ? '' : 'disabled'?> value="<?=$data['PASSPORT_ISSUED_BY']?>" name="passport_issued_by" id="passport_issued_by" type="text" class="ui-ctl-element required">
                </div>
              </div>

              <div class="input_group">
                <label for="passport_issued_date">Дата выдачи</label>
                <div class="ui-ctl ui-ctl-after-icon ui-ctl-date">
                  <div class="ui-ctl-after ui-ctl-icon-calendar"></div>
                  <div class="ui-ctl-element"><input <?=$arResult['ISSET']['EDITABLE'] ? '' : 'disabled'?> id="passport_issued_date" class="required-change" type="text" value="<?=$data['PASSPORT_ISSUED_DATE']?>" name="passport_issued_date" onclick="BX.calendar({node: this, field: this, bTime: false}); changeCalendar(this)"></div>
                </div>
              </div>

              <div class="input_group">
                <label for="passport_code">Код подразделения</label>
                <div class="ui-ctl ui-ctl-textbox">
                  <input <?=$arResult['ISSET']['EDITABLE'] ? '' : 'disabled'?> value="<?=$data['PASSPORT_CODE']?>" name="passport_code" id="passport_code" type="text" class="ui-ctl-element required">
                </div>
              </div>

              <div class="input_group">
                <label for="passport_address">Адрес регистрации (по прописке)</label>
                <div class="ui-ctl ui-ctl-textbox">
                  <input <?=$arResult['ISSET']['EDITABLE'] ? '' : 'disabled'?> value="<?=$data['PASSPORT_ADDRESS']?>" name="passport_address" id="passport_address" type="text" class="ui-ctl-element required">
                </div>
              </div>

              <h4>Полис обязательного медицинского страхования</h4>

              <div class="input_group">
                <label for="oms_number">Номер</label>
                <div class="ui-ctl ui-ctl-textbox">
                  <input <?=$arResult['ISSET']['EDITABLE'] ? '' : 'disabled'?> value="<?=$data['OMS_NUMBER']?>" name="oms_number" id="oms_number" type="text" class="ui-ctl-element required">
                </div>
              </div>

              <div class="input_group">
                <label for="oms_service">Страховая служба</label>
                <div class="ui-ctl ui-ctl-textbox">
                  <input <?=$arResult['ISSET']['EDITABLE'] ? '' : 'disabled'?> value="<?=$data['OMS_SERVICE']?>" name="oms_service" id="oms_service" type="text" class="ui-ctl-element required">
                </div>
              </div>




              <h4 class="bold">Выберите дату и время вакцинации</h4>



              <div class="input_group">
                <label for="vac_date">Дата</label>
                <div class="ui-ctl ui-ctl-after-icon ui-ctl-date ">
                  <div class="ui-ctl-after ui-ctl-icon-calendar"></div>
                  <div class="ui-ctl-element "><input <?=$arResult['ISSET']['EDITABLE'] ? '' : 'disabled'?> id="vac_date" class="required-change" type="text" value="<?=$data['VAC_DATE']?>" name="vac_date" onclick="BX.calendar({node: this, field: this, bTime: false}); changeCalendar(this)"></div>
                </div>
              </div>

              <div class="input_group">
                <label for="time">Время</label>
                <div class="ui-ctl ui-ctl-after-icon ui-ctl-time">
                  <div class="ui-ctl-after ui-ctl-icon-calendar"></div>
                  <select <?=$arResult['ISSET']['EDITABLE'] ? '' : 'disabled'?> class="ui-ctl-element required-change" name="time" id="time">
                    <option value="<?=$data['VAC_TIME'] ?? '' ?>"><?=$data['VAC_TIME'] ?? '...' ?></option>

                  </select>
                </div>
              </div>


              <? if ($arResult['ISSET']['EDITABLE']): ?>
                <div class="input_group">
                  <label class="ui-ctl ui-ctl-checkbox ">
                    <input <?=$arResult['ISSET']['EDITABLE'] ? '' : 'disabled'?> type="checkbox" class="ui-ctl-element required-change" name="agree">
                    <div class="ui-ctl-label-text">Даю свое согласие на обработку персональных данных</div>
                  </label>
                </div>
              <?endif;?>


              </div>

            </form>


        </div>
        <?endforeach;?>

      </div>

      <div class="mt-4">
        <a href="<?=SITE_DIR?>vaccination/data" class="ui-btn ui-btn-success">Отчет о записях</a>
        <a href="<?=SITE_DIR?>vaccination/write" class="ui-btn ui-btn-primary">Добавить новую запись</a>
      </div>

    <?else:?>

    <?if ($arResult['ISSET']['DATA']): ?>

    <? foreach($arResult['ISSET']['DATA'] as $id => $data): ?>

        <? if ($data['ID'] > 0): ?>
          <button data-id="<?=$data['ID']?>" type="button" class="vaccination_delete ui-btn ui-btn-danger mt-5">Отменить запись</button>
        <?endif;?>

      <form action="" class="form_vaccination">

        <h3>Заполните поля для записи на вакцинацию</h3>

        <div class="input_group">
          <label for="fio">ФИО</label>
          <div class="ui-ctl ui-ctl-textbox">
            <input <?=$arResult['ISSET']['EDITABLE'] ? '' : 'disabled'?> value="<?=$data['NAME'] ?? $USER->GetLastName().' '.$USER->GetFirstName().' '.$USER->GetSecondName()?>" name="fio" id="fio" type="text" class="ui-ctl-element required">
          </div>
        </div>

        <div class="input_group">
          <label for="birthday_date">Дата рождения</label>
          <div class="ui-ctl ui-ctl-after-icon ui-ctl-date">
            <div class="ui-ctl-after ui-ctl-icon-calendar"></div>
            <div class="ui-ctl-element"><input <?=$arResult['ISSET']['EDITABLE'] ? '' : 'disabled'?> id="birthday_date" class="required-change" type="text" value="<?=$data['BIRTHDAY_DATE'] ?>" name="birthday_date" onclick="BX.calendar({node: this, field: this, bTime: false}); changeCalendar(this)"></div>
          </div>
        </div>

        <div class="input_group">
          <label for="snils">Номер СНИЛС</label>
          <div class="ui-ctl ui-ctl-textbox">
            <input <?=$arResult['ISSET']['EDITABLE'] ? '' : 'disabled'?> value="<?=$data['SNILS']?>" name="snils" id="snils" type="text" class="ui-ctl-element required">
          </div>
        </div>

        <div class="input_group">
          <label for="phone">Номер телефона</label>
          <div class="ui-ctl ui-ctl-textbox">
            <input <?=$arResult['ISSET']['EDITABLE'] ? '' : 'disabled'?> value="<?=$data['PHONE']?>" name="phone" id="phone" type="text" class="ui-ctl-element required">
          </div>
        </div>

        <h4>Паспорт</h4>

        <div class="input_group">
          <label for="passport_sn">Серия, номер</label>
          <div class="ui-ctl ui-ctl-textbox">
            <input <?=$arResult['ISSET']['EDITABLE'] ? '' : 'disabled'?> value="<?=$data['PASSPORT_SN']?>" name="passport_sn" id="passport_sn" type="text" class="ui-ctl-element required">
          </div>
        </div>

        <div class="input_group">
          <label for="passport_issued_by">Кем выдан</label>
          <div class="ui-ctl ui-ctl-textbox">
            <input <?=$arResult['ISSET']['EDITABLE'] ? '' : 'disabled'?> value="<?=$data['PASSPORT_ISSUED_BY']?>" name="passport_issued_by" id="passport_issued_by" type="text" class="ui-ctl-element required">
          </div>
        </div>

        <div class="input_group">
          <label for="passport_issued_date">Дата выдачи</label>
          <div class="ui-ctl ui-ctl-after-icon ui-ctl-date">
            <div class="ui-ctl-after ui-ctl-icon-calendar"></div>
            <div class="ui-ctl-element"><input <?=$arResult['ISSET']['EDITABLE'] ? '' : 'disabled'?> id="passport_issued_date" class="required-change" type="text" value="<?=$data['PASSPORT_ISSUED_DATE']?>" name="passport_issued_date" onclick="BX.calendar({node: this, field: this, bTime: false}); changeCalendar(this)"></div>
          </div>
        </div>

        <div class="input_group">
          <label for="passport_code">Код подразделения</label>
          <div class="ui-ctl ui-ctl-textbox">
            <input <?=$arResult['ISSET']['EDITABLE'] ? '' : 'disabled'?> value="<?=$data['PASSPORT_CODE']?>" name="passport_code" id="passport_code" type="text" class="ui-ctl-element required">
          </div>
        </div>

        <div class="input_group">
          <label for="passport_address">Адрес регистрации (по прописке)</label>
          <div class="ui-ctl ui-ctl-textbox">
            <input <?=$arResult['ISSET']['EDITABLE'] ? '' : 'disabled'?> value="<?=$data['PASSPORT_ADDRESS']?>" name="passport_address" id="passport_address" type="text" class="ui-ctl-element required">
          </div>
        </div>

        <h4>Полис обязательного медицинского страхования</h4>

        <div class="input_group">
          <label for="oms_number">Номер</label>
          <div class="ui-ctl ui-ctl-textbox">
            <input <?=$arResult['ISSET']['EDITABLE'] ? '' : 'disabled'?> value="<?=$data['OMS_NUMBER']?>" name="oms_number" id="oms_number" type="text" class="ui-ctl-element required">
          </div>
        </div>

        <div class="input_group">
          <label for="oms_service">Страховая служба</label>
          <div class="ui-ctl ui-ctl-textbox">
            <input <?=$arResult['ISSET']['EDITABLE'] ? '' : 'disabled'?> value="<?=$data['OMS_SERVICE']?>" name="oms_service" id="oms_service" type="text" class="ui-ctl-element required">
          </div>
        </div>




        <h4 class="bold">Выберите дату и время вакцинации</h4>



        <div class="input_group">
          <label for="vac_date">Дата</label>
          <div class="ui-ctl ui-ctl-after-icon ui-ctl-date ">
            <div class="ui-ctl-after ui-ctl-icon-calendar"></div>
            <div class="ui-ctl-element "><input <?=$arResult['ISSET']['EDITABLE'] ? '' : 'disabled'?> id="vac_date" class="required-change" type="text" value="<?=$data['VAC_DATE']?>" name="vac_date" onclick="BX.calendar({node: this, field: this, bTime: false}); changeCalendar(this)"></div>
          </div>
        </div>

        <div class="input_group">
          <label for="time">Время</label>
          <div class="ui-ctl ui-ctl-after-icon ui-ctl-time">
            <div class="ui-ctl-after ui-ctl-icon-calendar"></div>
            <select <?=$arResult['ISSET']['EDITABLE'] ? '' : 'disabled'?> class="ui-ctl-element required-change" name="time" id="time">
              <option value="<?=$data['VAC_TIME'] ?? '' ?>"><?=$data['VAC_TIME'] ?? '...' ?></option>

            </select>
          </div>
        </div>


        <? if ($arResult['ISSET']['EDITABLE']): ?>
          <div class="input_group">
            <label class="ui-ctl ui-ctl-checkbox ">
              <input <?=$arResult['ISSET']['EDITABLE'] ? '' : 'disabled'?> type="checkbox" class="ui-ctl-element required-change" name="agree">
              <div class="ui-ctl-label-text">Даю свое согласие на обработку персональных данных</div>
            </label>
          </div>
        <?endif;?>

        <div class="mt-3 d-flex">
          <? if ($data['ID'] > 0): ?>
          <?else:?>
            <button type="submit" class="ui-btn vaccination_send">Записаться</button>
          <?endif;?>

          <? if ($arResult['ACCESS']): ?>
            <div class="ml-4">
              <a href="<?=SITE_DIR?>vaccination/data" class="ui-btn ui-btn-success">Отчет о записях</a>
            </div>
          <?endif;?>

          <? if (count($arResult['ISSET']['DATA']) == 1): ?>
            <div class="ml-4">
              <a href="<?=SITE_DIR?>vaccination/write" class="ui-btn ui-btn-success">Записаться на второй этап</a>
            </div>
          <?endif;?>

        </div>

      </form>

    <?endforeach;?>

    <?else:?>
      <? LocalRedirect(SITE_DIR.'vaccination/new'); ?>
    <?endif;?>

    <?endif;?>
















  </div>
</div>
