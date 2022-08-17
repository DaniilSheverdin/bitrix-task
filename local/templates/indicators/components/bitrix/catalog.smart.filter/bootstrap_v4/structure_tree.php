<?

use Bitrix\Main\Loader;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
	die();
}

Loader::includeModule('intranet');
$arIds = CIntranetUtils::GetDeparmentsTree(57, true);

$arKeys = array_keys($arItem["VALUES"]);

$arFilter = array(
    'ACTIVE'		=> 'Y',
    'IBLOCK_ID'		=> 5,
    'GLOBAL_ACTIVE'	=> 'Y',
    'ID'			=> $arIds,
);
$arSelect = array('ID', 'NAME', 'DEPTH_LEVEL', 'IBLOCK_SECTION_ID');
$res = CIBlockSection::GetTreeList($arFilter, $arSelect);
$arSections = [];
while ($row = $res->Fetch()) {
	$arSections[] = [
		'ID'				=> $row['ID'],
		'NAME' 				=> $row['NAME'],
		'DEPTH_LEVEL' 		=> $row['DEPTH_LEVEL'],
		'IBLOCK_SECTION_ID' => $row['IBLOCK_SECTION_ID'],
	];
}

$arValues = $arItem['VALUES'];
?>
<ul class="section-list">
<?
foreach ($arSections as $section) {
	$showDep = false;
	if (!isset($arValues[ $section['ID'] ])) {
		if ($section['DEPTH_LEVEL'] >= 6) {
			continue;
		} else {
			$showDep = true;
		}
	} else {
		$ar = $arValues[ $section['ID'] ];
	}
	?>
	<li class="list list-<?=$section['DEPTH_LEVEL']-4?>">
		<?
		if ($showDep) {
			?>
			<input
				type="checkbox"
				id="<? echo $section["ID"] ?>"
				class="form-check-input"
				disabled
			/>
			<label data-role="label_<?=$section["ID"]?>" class="smart-filter-checkbox-text form-check-label" for="<? echo $section["ID"] ?>">
				<?=$section['NAME'];
				if ($arParams["DISPLAY_ELEMENT_COUNT"] !== "N") :
					?>&nbsp;(<span data-role="count_<?=$ar["ID"]?>">0</span>)<?
				endif;?>
			</label>
			<?
		} else {
			?>
			<input
				type="checkbox"
				value="<? echo $ar["HTML_VALUE"] ?>"
				name="<? echo $ar["CONTROL_NAME"] ?>"
				id="<? echo $ar["CONTROL_ID"] ?>"
				class="form-check-input"
				<? echo $ar["CHECKED"]? 'checked="checked"': '' ?>
				<? echo $ar["DISABLED"] ? 'disabled': '' ?>
				onclick="smartFilter.click(this)"
			/>
			<label data-role="label_<?=$ar["CONTROL_ID"]?>" class="smart-filter-checkbox-text form-check-label" for="<? echo $ar["CONTROL_ID"] ?>">
				<?=$ar["VALUE"];
				if ($arParams["DISPLAY_ELEMENT_COUNT"] !== "N" && isset($ar["ELEMENT_COUNT"])) :
					?>&nbsp;(<span data-role="count_<?=$ar["CONTROL_ID"]?>"><? echo $ar["ELEMENT_COUNT"]; ?></span>)<?
				endif;?>
			</label>
			<?
		}
		?>
	</li>
	<?
}
?>
</ul>