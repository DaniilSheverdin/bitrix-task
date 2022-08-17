<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

if($arResult["PROCESSES"] && $arResult["USE_COMMENTS"])
	\Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/bizproc/tools.js');

$arToolbar = array();

// if ($arResult["CAN_ADD_ELEMENT"])
	// $arToolbar[] = array(
	// 	"TEXT"  => $arResult["IBLOCK"]["ELEMENT_ADD"],
	// 	"TITLE" => GetMessage("CT_BLL_TOOLBAR_ADD_ELEMENT_TITLE"),
	// 	"LINK"  => $arResult["LIST_NEW_ELEMENT_URL"],
	// 	"ICON"  => "btn-add-element",
	// );

// if ($arResult["CAN_EDIT_SECTIONS"])
	// $arToolbar[] = array(
	// 	"TEXT"  => GetMessage("CT_BLL_TOOLBAR_EDIT_SECTION"),
	// 	"TITLE" => GetMessage("CT_BLL_TOOLBAR_EDIT_SECTION_TITLE"),
	// 	"LINK"  => $arResult["LIST_SECTION_URL"],
	// 	"ICON"  => "btn-edit-sections",
	// );

// if ($arParams["CAN_EDIT"])
// {
// 	if (count($arToolbar))
// 		$arToolbar[] = array("SEPARATOR" => true);

// 	if ($arResult["IBLOCK"]["BIZPROC"] == "Y" && $arParams["CAN_EDIT_BIZPROC"])
// 	{
// 		// $arToolbar[] = array(
// 		// 	"TEXT"  => GetMessage("CT_BLL_TOOLBAR_BIZPROC"),
// 		// 	"TITLE" => GetMessage("CT_BLL_TOOLBAR_BIZPROC_TITLE"),
// 		// 	"LINK"  => $arResult["BIZPROC_WORKFLOW_ADMIN_URL"],
// 		// 	"ICON"  => "btn-list-bizproc",
// 		// );
// 	}

// 	if($arParams["IBLOCK_TYPE_ID"] == COption::GetOptionString("lists", "livefeed_iblock_type_id"))
// 	{
// 		$text = GetMessage("CT_BLL_TOOLBAR_PROCESS");
// 		$title = GetMessage("CT_BLL_TOOLBAR_PROCESS_TITLE");
// 	}
// 	else
// 	{
// 		$text = GetMessage("CT_BLL_TOOLBAR_LIST");
// 		$title = GetMessage("CT_BLL_TOOLBAR_LIST_TITLE");
// 	}

	// $arToolbar[] = array(
	// 	"TEXT"=>$text,
	// 	"TITLE"=>$title,
	// 	"LINK"=>$arResult["LIST_EDIT_URL"],
	// 	"ICON"=>"btn-edit-list",
	// );
// }

if ($arResult["CAN_READ"])
{
	$arToolbar[] = array(
		"TEXT"  => GetMessage("CT_BLL_EXPORT_EXCEL"),
		"TITLE" => GetMessage("CT_BLL_EXPORT_EXCEL_TITLE"),
		"LINK"  => CHTTP::urlAddParams((mb_strpos($APPLICATION->GetCurPageParam(), "?") == false) ?
			$arResult["EXPORT_EXCEL_URL"] : $arResult["EXPORT_EXCEL_URL"].substr($APPLICATION->GetCurPageParam(),
				mb_strpos($APPLICATION->GetCurPageParam(), "?")), array("ncc" => "y")),
		"ICON"  => "btn-list-excel",
	);
}

// if (
// 	!IsModuleInstalled('bitrix24')
// 	&& IsModuleInstalled('intranet')
// 	&& CBXFeatures::IsFeatureEnabled('intranet_sharepoint')
// )
// {
// 	if ($arIcons = $APPLICATION->IncludeComponent('bitrix:sharepoint.link', '', array(
// 		'IBLOCK_ID' => $arParams['IBLOCK_ID'],
// 		'OUTPUT'    => 'N',
// 	), null, array('HIDE_ICONS' => 'Y'))
// 	)
// 	{
// 		if (count($arIcons['LINKS']) > 0)
// 		{
// 			$arMenu = array();
// 			foreach ($arIcons['LINKS'] as $link)
// 			{
// 				$arMenu[] = array(
// 					'TEXT'      => $link['TEXT'],
// 					'ONCLICK'   => $link['ONCLICK'],
// 					'ICONCLASS' => $link['ICON']
// 				);
// 			}

			// $arToolbar[] = array(
			// 	'TEXT' => 'SharePoint',
			// 	'ICON' => 'bx-sharepoint',
			// 	'MENU' => $arMenu,
			// );
// 		}
// 	}
// }

if (count($arToolbar))
{
	$APPLICATION->IncludeComponent(
		"bitrix:main.interface.toolbar",
		"",
		array(
			"BUTTONS" => $arToolbar,
		),
		$component, array("HIDE_ICONS" => "Y")
	);
}
$arActions = array();
$arActions["custom_html"] = '<button type="button" class="btn btn-sm btn-primary" onclick="bp_mp_start()">Запустить бизнес процесс</button>';
if (empty($arActions))
	$arActions = false;

foreach ($arResult["FILTER"] as $i => $arFilter) {
	if ($arFilter["type"] == "E") :
		$FIELD_ID = $arFilter["id"];
		$arField = $arFilter["value"];
		$values = CIBlockPropertyElementAutoComplete::GetValueForAutoCompleteMulti($arField, $arResult["GRID_FILTER"][$FIELD_ID]);
		ob_start();
		?><input type="hidden" name="<? echo $FIELD_ID ?>" value=""><? //This will emulate empty input
		$control_id = $APPLICATION->IncludeComponent(
			"bitrix:main.lookup.input",
			"elements",
			array(
				"INPUT_NAME"         => $FIELD_ID,
				"INPUT_NAME_STRING"  => "inp_".$FIELD_ID,
				"INPUT_VALUE_STRING" => is_array($values) ? htmlspecialcharsback(current($values)) : "",
				"START_TEXT"         => "",
				"MULTIPLE"           => "N",
				//These params will go throught ajax call to ajax.php in template
				"IBLOCK_TYPE_ID"     => $arParams["~IBLOCK_TYPE_ID"],
				"IBLOCK_ID"          => $arField["LINK_IBLOCK_ID"],
				"FILTER"             => "Y",
			), $component, array("HIDE_ICONS" => "Y")
		);
		$html = ob_get_contents();
		ob_end_clean();

		$arResult["FILTER"][$i]["type"] = "custom";
		$arResult["FILTER"][$i]["value"] = $html;
		$arResult["FILTER"][$i]["filtered"] = isset($_REQUEST[$FIELD_ID]) && (is_array($_REQUEST[$FIELD_ID]) || mb_strlen($_REQUEST[$FIELD_ID]));
		$arResult["FILTER"][$i]["filter_value"] = $_REQUEST[$FIELD_ID];
		$arResult["FILTER"][$i]["enable_settings"] = false;

	endif;
}
$arResult["ELEMENTS_HEADERS"][] = [
	'id' => "HISTORY",
	'name' => "История",
	'default' => 1,
	'sort' => 1000,
	'hideName' => 1,
	'iconCls' => "bp-comments-icon"
];

foreach($arResult["ELEMENTS_ROWS"] as &$arItem){
	$arItem['data']['HISTORY'] = "";
	$arItem['columns']['HISTORY'] = '<a href="javascript:void(0)" onclick="cbp_history_show(\''.SITE_DIR.'bizproc/processes/'.$arItem['data']['IBLOCK_ID'].'/bp_log/'.$arItem['data']['WORKFLOW_ID'].'/\')">Подробнее</a>';
}
unset($arItem);
$APPLICATION->IncludeComponent(
	"bitrix:main.interface.grid",
	"lists",
	array(
		"GRID_ID"              => $arResult["GRID_ID"],
		"HEADERS"              => $arResult["ELEMENTS_HEADERS"],
		"ROWS"                 => $arResult["ELEMENTS_ROWS"],
		"ACTIONS"              => $arActions,
		"NAV_OBJECT"           => $arResult["NAV_OBJECT"],
		"SORT"                 => $arResult["SORT"],
		"FILTER"               => $arResult["FILTER"],
		"FOOTER"               => array(
			array("title" => GetMessage("CT_BLL_SELECTED"), "value" => $arResult["NAV_OBJECT"]->SelectedRowsCount())
		),
		"AJAX_MODE"            => "Y",
		"AJAX_OPTION_JUMP"     => "N",
		"FILTER_TEMPLATE_NAME" => "lists"
	),
	$component, array("HIDE_ICONS" => "Y")
);

$sectionId = $arResult["SECTION_ID"] ? $arResult["SECTION_ID"] : 0;
$socnetGroupId = $arParams["SOCNET_GROUP_ID"] ? $arParams["SOCNET_GROUP_ID"] : 0;
?>

<script type="text/javascript">
	BX(function () {
		BX['ListClass_<?= $arResult["RAND_STRING"] ?>'] = new BX.ListClass({
			randomString: '<?= $arResult["RAND_STRING"] ?>',
			iblockTypeId: '<?= $arParams["IBLOCK_TYPE_ID"] ?>',
			iblockId: '<?= $arResult["IBLOCK_ID"] ?>',
			sectionId: '<?= $sectionId ?>',
			socnetGroupId: '<?=$socnetGroupId?>'
		});

		BX.viewElementBind(
			'<?=$arResult["GRID_ID"]?>',
			{showTitle: true},
			{attr: 'data-bx-viewer'}
		);
	});
	function cbp_history_show(url){
		jQuery.get(url+'?IS_AJAX', function(data){
			var wrapper = BX.create('div', {style: {width: '800px'}});
			wrapper.innerHTML = data;
			wrapper.className = "history-popup";


			var popup = new BX.PopupWindow("bp-wfi-popup-" + Math.round(Math.random() * 100000), null, {
				content: wrapper,
				closeIcon: true,
				titleBar: "История бизнес-процесса",
				contentColor: 'white',
				contentNoPaddings : true,
				zIndex: -100,
				offsetLeft: 0,
				offsetTop: 0,
				closeByEsc: true,
				draggable: {restrict: false},
				overlay: {backgroundColor: 'black', opacity: 30},
				events: {
					onPopupClose: function (popup)
					{
						popup.destroy();
					}
				}
			});
			popup.show();
		})
	}
</script>
