<?php

define('NEED_AUTH', true);
require $_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php";
require "zayavka.class.php";
require "zayavka.ajax.php";

\Bitrix\Main\Loader::includeModule("iblock");

$APPLICATION->SetAdditionalCSS('/bitrix/templates/.default/bootstrap.min.css');
$APPLICATION->AddHeadScript('/bitrix/templates/.default/jquery.min.js');
$APPLICATION->AddHeadScript('/bitrix/templates/.default/bootstrap.min.js');
$APPLICATION->AddHeadScript('/bitrix/templates/.default/jquery.mask.js');
$APPLICATION->AddHeadScript('/zayavka-na-vremennyy-propusk/index.js');
$APPLICATION->SetAdditionalCSS('/zayavka-na-vremennyy-propusk/main.css');
$APPLICATION->SetTitle("Заказ разового пропуска");

$DATA_PRIYOMA = Zayavka::dateFromGet();

function getDepartmentName($id){
     $department_name = NULL;
     if(empty($id)) throw new \Exception("Не передан ID");

     $department = \Bitrix\Iblock\SectionTable::getRow([
          'filter'  => ['ID'=>$id],
          'select'  => ['IBLOCK_SECTION_ID','NAME']
     ]);
     if($department){
          $department_name = [$department['NAME']];
          if($department['IBLOCK_SECTION_ID']){
               $department_name = array_merge($department_name,getDepartmentName($department['IBLOCK_SECTION_ID']));
          }
     }

     return $department_name;
};

$arUser = $GLOBALS['userFields']($USER->GetID());

?><div class="alert alert-info">
	Прием и выдача временных пропусков по заявке осуществляется с 09:00 до 17:30 (в пятницу до 16:30) по адресу: проспект Ленина, д. 2, подъезд № 10 (вход с ул. Советской)
</div>

<form class="needs-validation" novalidate="" style="" id="zayavka-na-vremennyy-propusk-action" action="<?=$APPLICATION->GetCurPage(false)?>" method="POST" autocomplete="Off">
     <input type="hidden" name="zayavka-na-vremennyy-propusk-action" value="add">
     <?=bitrix_sessid_post()?>
	<div class="alert" style="display:none">
	</div>
	<div class="form-group row">
 <label class="col-sm-2 col-form-label col-form-label-sm">Дата выдачи</label>
		<div class="col-sm-10">
 <strong><?=$DATA_PRIYOMA->format('d.m.Y')?></strong>
		</div>
	</div>
	<div class="form-group row">
 <label class="col-sm-2 col-form-label ">Ф.И.О., кому выдается пропуск</label>
		<div class="col-sm-10">
 <input type="text" class="form-control" name="FIO_KOMU" required>
			<div class="invalid-feedback">
				 Необходимо заполнить
			</div>
		</div>
	</div>
	<div class="form-group row" style="display:none">
 <label class="col-sm-2 col-form-label ">Вид и номер документа</label>
		<div class="col-sm-2">
			<select class="custom-select form-control-sm" name="VID_DOCUMENTA" required>
				<option value="<?=Zayavka::$VID_DOCUMENTA_PASPORT_RF?>" data-pattern="^[0-9]{2} [0-9]{2} [0-9]{6}$" data-placeholder="00 00 000000" selected>Паспорт гражданина РФ</option>
                <option value="<?=Zayavka::$VID_DOCUMENTA_PASPORT_FOREIGN?>" data-pattern="" data-placeholder="">Паспорт иностранного гражданина</option>
			</select>
		</div>
		<div class="col-sm-8">
			<input type="text" class="form-control" name="NOMER_DOCUMENTA" pattern="^[0-9]{2} [0-9]{2} [0-9]{6}$" placeholder="00 00 000000" required value="00 00 000000">
			<div class="invalid-feedback">
				 Необходимо заполнить. Формат: 00 00 000000
			</div>
		</div>
	</div>
	<div class="form-group row">
 <label class="col-sm-2 col-form-label ">Ф.И.О., подавшего заявку</label>
		<div class="col-sm-10">
 <input type="text" class="form-control" name="FIO_PODAVSHEGO" value="<?=$USER->getFullName()?>" readonly required>
		</div>
	</div>
	<div class="form-group row">
 <label class="col-sm-2 col-form-label ">Должность подавшего заявку</label>
		<div class="col-sm-10">
 <input type="text" class="form-control" name="DOLJNOST_PODAVSHEGO" value="<?=$arUser['WORK_POSITION']?>" required>
			<div class="invalid-feedback">
				 Необходимо заполнить
			</div>
		</div>
	</div>
	<div class="form-group row">
 <label class="col-sm-2 col-form-label ">К кому на приeм</label>
		<div class="col-sm-10">
 <input type="text" class="form-control" name="K_KOMU" required>
			<div class="invalid-feedback">
				 Необходимо заполнить
			</div>
		</div>
	</div>
	<div class="form-group row">
 <label class="col-sm-2 col-form-label ">Кабинет №</label>
		<div class="col-sm-10">
 <input type="text" class="form-control" name="KABINET" required>
			<div class="invalid-feedback">
				 Необходимо заполнить
			</div>
		</div>
	</div>
	<div class="form-group row">
 <label class="col-sm-2 col-form-label ">Время приема</label>
		<div class="col-sm-10">
 <input class="form-control " type="text" name="VREMYA" pattern="^[0-9]{2}:[0-9]{2}$" required> <input type="hidden" name="DATE" value="<?=$DATA_PRIYOMA->format('Y-m-d')?>">
			<div class="invalid-feedback">
				 Необходимо заполнить
			</div>
		</div>
	</div>
 <button class="btn btn-primary" type="submit">Отправить заявку</button>
</form><?php require $_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php";?>