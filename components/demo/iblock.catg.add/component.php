<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();
$arResult['TITLE_FOR_FORM'] = $arParams["NAME_OF_SECTION"];

// process POST data
if (check_bitrix_sessid() && (!empty($_REQUEST["iblock_submit"]) || (!empty($_REQUEST["iblock_delete"])))) {
	$name = htmlspecialcharsbx($_REQUEST["title"]);
	if (!empty($_REQUEST["iblock_submit"])) {
		static $tbl = array(
		'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ж' => 'g', 'з' => 'z',
		'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p',
		'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'ы' => 'y', 'э' => 'e', 'А' => 'A',
		'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ж' => 'G', 'З' => 'Z', 'И' => 'I',
		'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R',
		'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Ы' => 'Y', 'Э' => 'E', 'ё' => "yo", 'х' => "h",
		'ц' => "ts", 'ч' => "ch", 'ш' => "sh", 'щ' => "shch", 'ъ' => "", 'ь' => "", 'ю' => "yu", 'я' => "ya",
		'Ё' => "YO", 'Х' => "H", 'Ц' => "TS", 'Ч' => "CH", 'Ш' => "SH", 'Щ' => "SHCH", 'Ъ' => "", 'Ь' => "",
		'Ю' => "YU", 'Я' => "YA", ' ' => "_", '№' => "", '«' => "<", '»' => ">", '—' => "-"
		);

		$CODE = strtr($name, $tbl); //получаем символьный код для раздела
		$bs = new CIBlockSection; //раздел
		$arFields = array(
			"ACTIVE" => "Y",
			"IBLOCK_ID" => $arParams['IBLOCK_ID'],
			"NAME" => $name,
			"CODE" => $CODE
		);

		$ID = $bs->Add($arFields);
		$res = ($ID > 0);
		if (!$res) {
			$arResult["ERRORS"][] = $bs->LAST_ERROR;
		}
		else
			$arResult['MESSAGE'] = 'Категория успешно добавлена';
	}
	elseif (!empty($_REQUEST["iblock_delete"])) {
		// выборка только активных разделов из инфоблока 1
		$arFilter = array('IBLOCK_ID' => $arParams['IBLOCK_ID'], 'ACTIVE' => 'Y', 'NAME' => $name);
		$db_list = CIBlockSection::GetList(array($by => $order), $arFilter, true);

		while ($ar_result = $db_list->GetNext()) {
			if (!CIBlockSection::Delete($ar_result['ID'])) {
				$arResult["ERRORS"][] = 'Произошла ошибка удаления';
			}
			else
				$arResult['MESSAGE'] = 'Категория успешно удалена!';
		}
	}
}

$this->includeComponentTemplate();
?>