<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

class CCatg extends CBitrixComponent
{
	//Родительский метод проходит по всем параметрам переданным в $APPLICATION->IncludeComponent
	//и применяет к ним функцию htmlspecialcharsex. В данном случае такая обработка избыточна.
	//Переопределяем.
	public function onPrepareComponentParams($arParams)
	{
		$result = array(
			"IBLOCK_TYPE" => $arParams["IBLOCK_TYPE"],
			"IBLOCK_ID" => $arParams['IBLOCK_ID'],
			"NAME_OF_SECTION" => $arParams["NAME_OF_SECTION"],
		);
		return $result;
	}
	public function add($name, $block_id)
	{
		$params = array(
			"max_len" => "100", // обрезает символьный код до 100 символов
			"change_case" => "L", // буквы преобразуются к нижнему регистру
			"replace_space" => "_", // меняем пробелы на нижнее подчеркивание
			"replace_other" => "_", // меняем левые символы на нижнее подчеркивание
			"delete_repeat_replace" => "true", // удаляем повторяющиеся нижние подчеркивания
			"use_google" => "false", // отключаем использование google
		);

		$bs = new CIBlockSection; //раздел
		$arFields = array(
			"ACTIVE" => "Y",
			"IBLOCK_ID" => $block_id,
			"NAME" => $name,
			"CODE" => CUtil::translit($name, "ru", $params)
		);

		$bs->Add($arFields);
		return ($bs->lAST_ERROR);

	}

	public function delete($name)
	{
		// выборка только активных разделов из инфоблока 
		$arFilter = array('IBLOCK_ID' => $arParams['IBLOCK_ID'], 'ACTIVE' => 'Y', 'NAME' => $name);
		$db_list = CIBlockSection::GetList(array($by => $order), $arFilter, true);

		while ($ar_result = $db_list->GetNext()) {
			$result[] = CIBlockSection::Delete($ar_result['ID']);
		}
		return $result;
	}
}?>