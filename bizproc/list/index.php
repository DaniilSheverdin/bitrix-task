<?
define('IBLOCK_TYPE',"bitrix_processes");
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/intranet/public/bizproc/processes/index.php");
$APPLICATION->SetTitle("Статусы бизнес процессов");
$APPLICATION->AddHeadScript('/bitrix/templates/.default/jquery.min.js');
$APPLICATION->SetAdditionalCSS('/bitrix/templates/.default/bootstrap.min.css');
$APPLICATION->AddHeadScript('/bitrix/templates/.default/bootstrap.min.js');
$APPLICATION->AddHeadScript('/bitrix/templates/.default/jquery.mask.js');
$APPLICATION->AddHeadScript($APPLICATION->GetCurDir().'index.js');
$APPLICATION->SetAdditionalCSS($APPLICATION->GetCurDir().'main.css');

\Bitrix\Main\Loader::includeModule("iblock");

$selected_iblock = $_GET['IBLOCK_ID'] ?? NULL;
$IBLOCKs = [];
$res = CIBlock::GetList([],[
		'TYPE'		=> IBLOCK_TYPE, 
		'ACTIVE'	=> 'Y',
]);
while($ar_res = $res->Fetch()){
	$IBLOCKs[$ar_res['ID']] = $ar_res['NAME'];
}
?>
<?
if($selected_iblock && isset($IBLOCKs[$selected_iblock])){
	if($selected_iblock == 492){
		LocalRedirect(SITE_DIR."bizproc/list/?IBLOCK_ID=491&apply_filter=Y&PROPERTY_1692=".urlencode("пропуск ТМЦ (вода, канцтовары и т.п.)"));
	}
	if($selected_iblock == 491 && empty($_REQUEST['PROPERTY_1692'])){
		LocalRedirect(SITE_DIR."bizproc/list/?IBLOCK_ID=491&apply_filter=Y&PROPERTY_1692=массовый");
	}
?>

<div class="card mb-4">
	<div class="card-body">
		<a href="<?=$APPLICATION->GetCurDir()?>">&larr; Назад к списку бизнес процессов</a>
	</div>
</div>
<?

	$form_element_filter_newf = [];
	$form_element_filter = CUserOptions::GetOption("form", "form_element_".$selected_iblock);
	if(empty($form_element_filter['tabs'])){
		$form_element_filter['tabs'] = ["edit1--#--Элемент"];
	}
	$form_element_filter__tabs = explode("--,--",$form_element_filter['tabs']);
	if(!in_array("CREATED_BY--#--Пользователь",$form_element_filter__tabs)){
		$form_element_filter_newf[] = "CREATED_BY--#--Пользователь";
	}
	if(!in_array("DATE_CREATE--#--Дата",$form_element_filter__tabs)){
		$form_element_filter_newf[] = "DATE_CREATE--#--Дата";
	}
	if($form_element_filter_newf){
		$form_element_filter__tabs = array_merge([$form_element_filter__tabs[0]], $form_element_filter_newf, array_slice($form_element_filter__tabs,1));
		$form_element_filter['tabs'] = implode("--,--",$form_element_filter__tabs);
		
		CUserOptions::SetOption("form", "form_element_".$selected_iblock,$form_element_filter,true,0);
	}

	

	$arResult = [
		'VARIABLES' =>[
			'section_id'=>0
		],
		'FOLDER' => '/bizproc/processes/',
		'URL_TEMPLATES' => [
			"lists" => "",
			"list" => "#list_id#/view/#section_id#/",
			"list_sections" => "#list_id#/edit/#section_id#/",
			"list_edit" => "#list_id#/edit/",
			"list_fields" => "#list_id#/fields/",
			"list_field_edit" => "#list_id#/field/#field_id#/",
			"list_element_edit" => "#list_id#/element/#section_id#/#element_id#/",
			"list_file" => "#list_id#/file/#section_id#/#element_id#/#field_id#/#file_id#/",
			"bizproc_log" => "#list_id#/bp_log/#document_state_id#/",
			"bizproc_workflow_start" => "#list_id#/bp_start/#element_id#/",
			"bizproc_task" => "#list_id#/bp_task/#section_id#/#element_id#/#task_id#/",
			"bizproc_workflow_admin" => "#list_id#/bp_list/",
			"bizproc_workflow_edit" => "#list_id#/bp_edit/#ID#/",
			"bizproc_workflow_vars" => "#list_id#/bp_vars/#ID#/",
			"bizproc_workflow_constants" => "#list_id#/bp_constants/#ID#/",
			"list_export_excel" => "#list_id#/excel/",
			"catalog_processes" => "catalog_processes/",
		]
	];
	$APPLICATION->IncludeComponent("citto:lists.list", "lists", array(
		"IBLOCK_TYPE_ID" => IBLOCK_TYPE,
		"IBLOCK_ID" => $selected_iblock,
		"SECTION_ID" => $arResult["VARIABLES"]["section_id"],
		"LISTS_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["lists"],
		"LIST_EDIT_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["list_edit"],
		"LIST_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["list"],
		"LIST_SECTIONS_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["list_sections"],
		"LIST_ELEMENT_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["list_element_edit"],
		"LIST_FILE_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["list_file"],
		"LIST_FIELDS_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["list_fields"],
		"EXPORT_EXCEL_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["list_export_excel"],
		"BIZPROC_LOG_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["bizproc_log"],
		"BIZPROC_TASK_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["bizproc_task"],
		"BIZPROC_WORKFLOW_START_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["bizproc_workflow_start"],
		"BIZPROC_WORKFLOW_ADMIN_URL" => $arResult["FOLDER"].$arResult["URL_TEMPLATES"]["bizproc_workflow_admin"],
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600",
		),
		false
	);
	if($selected_iblock == 478){
		if(isset($_GET['PROPERTY_1863']) && is_array($_GET['PROPERTY_1863']) && current($_GET['PROPERTY_1863']) == 1179){
			$APPLICATION->SetTitle("Уведомления об отпуске");
		}
	}
}else{
?>

<div class="card mb-4">
	<div class="card-body">
		<h4 class="mb-4">Выберите бизнес процесс</h4>
		<ul class="list-group list-group-flush">
			<?foreach($IBLOCKs as $iblock_id=>$iblock_name):?>
				<?if($iblock_id == 478):?>
					<li class="list-group-item"><a href="<?=$APPLICATION->GetCurPageParam('apply_filter=Y&PROPERTY_1863[]=1179&IBLOCK_ID='.$iblock_id,['IBLOCK_ID'])?>">Уведомление об отпуске</a></li>
					<li class="list-group-item"><a href="<?=$APPLICATION->GetCurPageParam('apply_filter=Y&PROPERTY_1863[]=1178&IBLOCK_ID='.$iblock_id,['IBLOCK_ID'])?>"><?=$iblock_name?></a></li>
				<?
				continue;
				endif;
				?>
				<li class="list-group-item"><a href="<?=$APPLICATION->GetCurPageParam('IBLOCK_ID='.$iblock_id,['IBLOCK_ID'])?>"><?=$iblock_name?></a></li>
			<?endforeach;?>
		</ul>
	</div>
</div>
<?
}

?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>