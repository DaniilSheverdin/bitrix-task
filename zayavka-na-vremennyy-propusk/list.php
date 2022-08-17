
<?php
use Bitrix\Main\SystemException;
// define('BX_PUBLIC_MODE', 1);
define('BX_COMPRESSION_DISABLED', true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin_tools.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/interface/init_admin.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_popup_admin.php");
require_once("zayavka.class.php");
require_once("zayavka.ajax.php");

CJSCore::Init(["date"]);

$APPLICATION->SetAdditionalCSS('/bitrix/templates/.default/bootstrap.min.css');
$APPLICATION->AddHeadScript('/bitrix/templates/.default/jquery.min.js');
$APPLICATION->AddHeadScript('/bitrix/templates/.default/bootstrap.min.js');
$APPLICATION->AddHeadScript($APPLICATION->GetCurDir().'list.js');
$APPLICATION->SetAdditionalCSS($APPLICATION->GetCurDir().'main.css');


$REQUEST				= \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$zayavka_view_excell	= (bool)$REQUEST->get('mode');
$zayavka_DATA_PRIYOMA	= new \DateTime($REQUEST->get('DATA_PRIYOMA')?:"NOW");
$zayavka_view			= new CAdminList("Reports");

$zayavka_view->AddHeaders(
	array_merge(
		[
			[
				'id'		=> "ID",
				'content'	=> "ID",
				'default'	=> "false",
			]
		],
		array_map(function($zayavka_prop){
			return [
				'id'		=> $zayavka_prop['CODE'],
				'content'	=> $zayavka_prop['NAME'],
				'default'	=> "false",
			];
		},Zayavka::getProps()),
		$zayavka_view_excell
			? []
			: [
				[
					'id'		=> "ACTIONS",
					'content'	=> "Действия",
					'default'	=> "false",
				]
			]
	)
);

$zayavki = [];
if(CSite::InGroup([1])){
	$APPLICATION->SetTitle('Все заявки');
	$zayavki = Zayavka::get($zayavka_DATA_PRIYOMA);
}else{
	$APPLICATION->SetTitle('Ваши заявки');
	$zayavki = Zayavka::get($zayavka_DATA_PRIYOMA, (int)$USER->GetID());
}


foreach($zayavki as $zayavka){
	$row = (array)$zayavka;
	if(!$zayavka_view_excell){
		if($zayavka->cancelable()){
			$row['ACTIONS'] = "#CANCEL#";
		}
	}
	$row['STATUS'] = $zayavka->STATUS_STR;
	$row['VID_DOCUMENTA'] = $zayavka->VID_DOCUMENTA_STR;
	$zayavka_view->AddRow($zayavka->ID,$row); 
}

?>
<form action="<?=$APPLICATION->GetCurPage(false)?>">
	<div class="form-group">
		<input type="text" class="form-control" value="<?=$zayavka_DATA_PRIYOMA->format(\Bitrix\Main\Type\Date::convertFormatToPhp(FORMAT_DATE))?>" name="DATA_PRIYOMA" onclick="BX.calendar({node: this, field: this, bTime: false, callback_after:function(){$(this.params.field).closest('form').submit();}});">
	</div>
</form>
<?
if($zayavka_view_excell){
	$APPLICATION->RestartBuffer();
	$zayavka_view->CheckListMode();
	$zayavka_view->DisplayList();
	die;
}else{
	$zayavka_view->DisplayList();
}
?>

<div class="py-4">
	<p><a href="?mode=excel&DATA_PRIYOMA=<?=$zayavka_DATA_PRIYOMA->format(\Bitrix\Main\Type\Date::convertFormatToPhp(FORMAT_DATE))?>" class="btn btn-primary">Выгрузить</a></p>
</div>
<div class="modal fade" id="zayavka-na-propusk-cancel" tabindex="-1" role="dialog" aria-labelledby="zayavka-na-propusk-cancel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Отмена заявки</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body">
		<form class="needs-validation" novalidate="" style="" action="<?=$APPLICATION->GetCurPage(false)?>" method="POST" autocomplete="Off">
			<input type="hidden" name="zayavka-na-vremennyy-propusk-action" value="cancel">
			<input type="hidden" name="ID" value="">
			<?=bitrix_sessid_post()?>
			<div class="alert" style="display:none"></div>
			<div class="form-group">
				<label>Введите Ваш пароль <small class="text-muted">(для отмены заявки)</small></label>
				<input type="text" name="password" class="form-control" required>
			</div>
			<button class="btn btn-primary" type="submit">Подтвердить</button>
		</form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
      </div>
    </div>
  </div>
</div>
<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>