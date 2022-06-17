<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();
$arResult['TITLE_FOR_FORM'] = $arParams["NAME_OF_SECTION"];

// process POST data
if (check_bitrix_sessid() && (!empty($_REQUEST["iblock_submit"]) || (!empty($_REQUEST["iblock_delete"])))) {
	$name = htmlspecialcharsbx($_REQUEST["title"]);
	if (!empty($_REQUEST["iblock_submit"])) {
		$message=$this->add($name, $arParams['IBLOCK_ID']);
		if ($message) {
			$arResult["ERRORS"][] = $message;
		}
		else
			$arResult['MESSAGE'] = 'Категория успешно добавлена';
	}
	elseif (!empty($_REQUEST["iblock_delete"])) {
		if (!$this->delete($name)){
			$arResult["ERRORS"][] = "Ошибка удаления";
		}
		else
			$arResult['MESSAGE'] = 'Категория успешно удалена';
	}
}

$this->includeComponentTemplate();
?>